<?php
/**
 * @component     CG Gallery for Joomla 4.x/5.x
 * Version			: 2.4.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\Controller;
\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\CMS\Router\Route;

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
        $input = $app->input;
		$pks = $input->post->get('cid', array(), 'array');
        $db    = Factory::getDbo();
		foreach ($pks as $id)	{
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
            $data->id= 0;
            $data->title = $this->check_title($result[0]['title']);
            $data->state = $result[0]['published'];
            $data->language = $result[0]['language'];            
            $page_params = [];
            $mod_params = json_decode($result[0]['params']);
            $page_params['ug_compression'] = $mod_params->ug_compression;
            $page_params['ug_type'] = $mod_params->ug_type;
            $page_params['ug_tiles_type'] = $mod_params->ug_tiles_type;
            $page_params['ug_tile_width'] = $mod_params->ug_tile_width;
            $page_params['ug_min_columns'] = $mod_params->ug_min_columns;
            $page_params['ug_tile_height'] = $mod_params->ug_tile_height;
            $page_params['ug_grid_num_rows'] = $mod_params->ug_grid_num_rows;
            $page_params['ug_space_between_rows'] = $mod_params->ug_space_between_rows;
            $page_params['ug_space_between_cols'] = $mod_params->ug_space_between_cols;
            $page_params['ug_carousel_autoplay_timeout'] = $mod_params->ug_carousel_autoplay_timeout;
            $page_params['ug_carousel_scroll_duration'] = $mod_params->ug_carousel_scroll_duration;
            $page_params['ug_texte'] = $mod_params->ug_texte;
            $page_params['ug_text_lgth'] = $mod_params->ug_text_lgth;
            $page_params['ug_link'] = $mod_params->ug_link;
            $page_params['ug_lightbox'] = $mod_params->ug_lightbox;
            $page_params['ug_zoom'] = $mod_params->ug_zoom;
            $page_params['ug_dir_or_image'] = $mod_params->ug_dir_or_image;
            $page_params['ug_autothumb'] = $mod_params->ug_autothumb;
            $page_params['ug_big_dir'] = $mod_params->ug_big_dir;
            $page_params['ug_full_dir'] = $mod_params->ug_full_dir;
            $page_params['ug_file_nb'] = $mod_params->ug_file_nb;
            $page_params['intro'] = '';
			$page_params['bottom'] = '';
			$page_params['articles'] = 'articles';
            $data->page_params =  json_encode($page_params);
            $slideslist = json_decode(str_replace("||", "\"", $mod_params->slideslist));
            $laliste = [];
            foreach ($slideslist as $item) {
				$obj = new \StdClass();
				$obj->file_desc = str_replace("||", "\"", $item->imgcaption);
				$obj->file_name = str_replace("||", "\"", $item->imgname);
				$obj->file_id = str_replace("||", "\"", $item->slidearticleid);
				$laliste[] = $obj;
            }
            $data->slides = json_encode($laliste);
            $ret = $db->insertObject('#__cggallery_page', $data,'id');
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
	function check_title($title) {
        $db    = Factory::getDbo();
        do {
			$result = $db->setQuery(
                $db->getQuery(true)
                ->select("count('*')")
                ->from($db->quoteName('#__cggallery_page'))
                ->where($db->quoteName('title') . ' like ' . $db->quote($title) .' AND state in (0,1)')
            )->loadResult();
			if ($result > 0) $title = StringHelper::increment($title);
		}
		while ($result > 0);
		return $title;
	}

}