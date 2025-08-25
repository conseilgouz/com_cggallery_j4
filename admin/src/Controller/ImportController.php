<?php
/**
 * @component     CG Gallery for Joomla 4.x/5.x
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2025 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz
**/

namespace ConseilGouz\Component\CGGallery\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\String\StringHelper;

class ImportController extends FormController
{
    /**
     * @var		string	The prefix to use with controller messages.
     * @since	1.6
     */
    protected $text_prefix = 'COM_CGGALLERY_IMPORT';

    public function add($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $app = Factory::getApplication();
        $input = $app->getInput();
        $pks = $input->post->get('cid', array(), 'array');
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        foreach ($pks as $id) {
            $result = $db->setQuery(
                $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('id') . ' = ' . (int)$id)
            )->loadAssocList();
            if (count($result) != 1) {
                $this->setMessage(Text::sprintf('CG_GAL_MODULE_SELECT_ERROR', $id), 'warning');
                $this->setRedirect(Route::_('index.php?option=com_cggallery&view=import', false));
                return false;
            }
            $data = new \StdClass();
            $data->intro           = '';
            $data->bottom          = '';
            $data->articles        = 'articles';
            $data->id       = 0;
            $data->title    = $this->check_title($result[0]['title']);
            $data->state    = $result[0]['published'];
            $data->language = $result[0]['language'];

            foreach (json_decode($result[0]['params']) as $key => $value) {
                if ($key == 'slideslist') {
                    $data->slides = json_encode($value);
                } else {
                    $data->$key = $value;
                }
            }
            $ret = $db->insertObject('#__cggallery_page', $data, 'id');
            if (!$ret) {
                $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $ret), 'warning');
                $this->setRedirect(Route::_('index.php?option=com_cggallery&view=import', false));
                return false;
            }
        }
        $this->setMessage(Text::sprintf('CG_GAL_MODULE_IMPORTED', count($pks)), 'notice');
        $this->setRedirect(Route::_('index.php?option=com_cggallery&view=import', false));
        return false;
    }
    public function check_title($title)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        do {
            $result = $db->setQuery(
                $db->getQuery(true)
                ->select("count('*')")
                ->from($db->quoteName('#__cggallery_page'))
                ->where($db->quoteName('title') . ' like ' . $db->quote($title) .' AND state in (0,1)')
            )->loadResult();
            if ($result > 0) {
                $title = StringHelper::increment($title);
            }
        } while ($result > 0);
        return $title;
    }

}
