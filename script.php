<?php
/**
* CG Gallery Component  - Joomla 4.x/5.x Component
* Version			: 3.0.3
* Package			: CG Gallery
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

class com_cggalleryInstallerScript
{
    private $min_joomla_version      = '4.0';
    private $min_php_version         = '8.0';
    private $name                    = 'CG Gallery';
    private $exttype                 = 'component';
    private $extname                 = 'cggallery';
    private $previous_version        = '';
    private $dir           = null;
    private $lang = null;
    private $installerName = 'cggalleryinstaller';
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->lang = Factory::getApplication()->getLanguage();
        $this->lang->load($this->extname);
    }
    public function preflight($type, $parent)
    {

        if (! $this->passMinimumJoomlaVersion()) {
            $this->uninstallInstaller();

            return false;
        }

        if (! $this->passMinimumPHPVersion()) {
            $this->uninstallInstaller();

            return false;
        }
        // To prevent installer from running twice if installing multiple extensions
        if (! file_exists($this->dir . '/' . $this->installerName . '.xml')) {
            return true;
        }
        $xml = simplexml_load_file(JPATH_ADMIN . '/components/com_'.$this->extname.'/'.$this->extname.'.xml');
        $this->previous_version = $xml->version;

    }

    public function postflight($type, $parent)
    {
        if (($type == 'install') || ($type == 'update')) { // remove obsolete dir/files
            $this->postinstall_cleanup();
        }
        switch ($type) {
            case 'install': $message = Text::_('CG_GAL_POSTFLIGHT_INSTALLED');
                break;
            case 'uninstall': $message = Text::_('CG_GAL_POSTFLIGHT_UNINSTALLED');
                break;
            case 'update': $message = Text::_('CG_GAL_POSTFLIGHT_UPDATED');
                break;
            case 'discover_install': $message = Text::_('CG_GAL_POSTFLIGHT_DISC_INSTALLED');
                break;
        }
        $message = '<h3>'.Text::sprintf('CG_GAL_POSTFLIGHT', $parent->getManifest()->name, $parent->getManifest()->version, $message).'</h3>';

        Factory::getApplication()->enqueueMessage($message.Text::_('COM_CGGALLERY'), 'notice');

        // Uninstall this installer
        $this->uninstallInstaller();

        return true;
    }
    private function postinstall_cleanup()
    {

        $obsoleteFiles = [
            JPATH_ADMINISTRATOR."/components/com_cggallery/updates.txt"
        ];
        foreach ($obsoleteFiles as $file) {
            if (@is_file($file)) {
                File::delete($file);
            }
        }
        // version 3.0.0 database update : remove page_params column
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        // MYSQL 8 : ALTER IGNORE deprecated
        $sql = "SHOW COLUMNS FROM #__cggallery_page";
        $db->setQuery($sql);
        $cols = @$db->loadObjectList("Field");

        if (array_key_exists("page_params", $cols)) {
            $pages = $db->setQuery(
                $db->getQuery(true)
                ->select('id,page_params')
                ->from('#__cggallery_page')
            )->loadObjectList();
            foreach ($pages as $onepage) {
                if ($onepage->page_params == "") {
                    continue;
                }
                $updateNulls = true;
                $data = new \StdClass();
                $compl = new Registry($onepage->page_params);
                $data->intro = $compl['intro'];
                $data->bottom = $compl['bottom'];
                $data->ug_compression = $compl['ug_compression'];
                $data->ug_type = $compl['ug_type'];
                $data->ug_tiles_type = $compl['ug_tiles_type'];
                $data->ug_tile_width = $compl['ug_tile_width'];
                $data->ug_min_columns = $compl['ug_min_columns'];
                $data->ug_tile_height = $compl['ug_tile_height'];
                $data->ug_grid_num_rows = $compl['ug_grid_num_rows'];
                $data->ug_space_between_rows = $compl['ug_space_between_rows'];
                $data->ug_space_between_cols = $compl['ug_space_between_cols'];
                $data->ug_carousel_autoplay_timeout = $compl['ug_carousel_autoplay_timeout'];
                $data->ug_carousel_scroll_duration = $compl['ug_carousel_scroll_duration'];
                $data->ug_text = $compl['ug_texte'];
                $data->ug_text_lgth = $compl['ug_text_lgth'];
                $data->ug_link = $compl['ug_link'];
                $data->ug_lightbox = $compl['ug_lightbox'];
                $data->ug_dir_or_image = $compl['ug_dir_or_image'];
                $data->ug_skin = $compl['ug_skin'];
                $data->ug_zoom = $compl['ug_zoom'];
                $data->ug_dir_or_image = $compl['ug_dir_or_image'];
                $data->ug_autothumb = $compl['ug_autothumb'];
                $data->ug_big_dir = $compl['ug_big_dir'];
                $data->ug_full_dir = $compl['ug_full_dir'];
                $data->ug_file_nb = $compl['ug_file_nb'];
                $data->ug_grid_thumbs_pos = $compl['ug_grid_thumbs_pos'];
                $data->ug_grid_show_icons = $compl['ug_grid_show_icons'];
                $data->ug_articles = 'articles';
                $data->id = $onepage->id;
                $data->page_params = "";
                $result = $db->updateObject('#__cggallery_page', $data, 'id', $updateNulls);
            }
            $sql = "ALTER TABLE #__cggallery_page DROP COLUMN page_params;";
            $db->setQuery($sql);
            $db->execute();
        }
        // version 3.0.3 : remove images/ from ug_big_dir
        $pages = $db->setQuery(
            $db->getQuery(true)
            ->select('id,ug_big_dir')
            ->from('#__cggallery_page')
        )->loadObjectList();
        foreach ($pages as $onepage) {
            if (($onepage->ug_big_dir == "") || !str_starts_with($onepage->ug_big_dir, 'images/')) {
                continue;
            }
            $updateNulls = true;
            $data = new \StdClass();
            $data->id = $onepage->id;
            $data->ug_big_dir = ltrim($onepage->ug_big_dir, 'images/');
            $result = $db->updateObject('#__cggallery_page', $data, 'id', $updateNulls);
        }
        // remove obsolete update sites
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%432473037d.url-de-test.ws/%"');
        $db->setQuery($query);
        $db->execute();
        // CG Gallery is now on Github
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%conseilgouz.com/updates/com_cggallery%"');
        $db->setQuery($query);
        $db->execute();

    }
    // Check if Joomla version passes minimum requirement
    private function passMinimumJoomlaVersion()
    {
        if (version_compare(JVERSION, $this->min_joomla_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible Joomla version : found <strong>' . JVERSION . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
                'error'
            );

            return false;
        }

        return true;
    }

    // Check if PHP version passes minimum requirement
    private function passMinimumPHPVersion()
    {

        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
                'error'
            );
            return false;
        }

        return true;
    }

    private function uninstallInstaller()
    {
        if (! is_dir(JPATH_PLUGINS . '/system/' . $this->installerName)) {
            return;
        }
        $this->delete([
            JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
            JPATH_PLUGINS . '/system/' . $this->installerName,
        ]);
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('_system');
    }

}
