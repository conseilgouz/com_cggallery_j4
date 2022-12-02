<?php
/**
 * @component     CG Gallery
 * Version			: 2.3.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\Model;
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Form\Form;

class PageModel extends AdminModel {

    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        parent::preprocessForm($form, $data, $group);
    }
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_cggallery.page', 'page', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()  {
		$data = Factory::getApplication()->getUserState('com_cggallery.edit.item.data', array());
		if (empty($data)) $data = $this->getItem();
        // split general parameters
		$compl = new Registry($data->page_params);
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
        $data->ug_texte = $compl['ug_texte'];
        $data->ug_text_lgth = $compl['ug_text_lgth'];
        $data->ug_link = $compl['ug_link'];
        $data->ug_lightbox = $compl['ug_lightbox'];
		$data->ug_zoom = $compl['ug_zoom'};
        $data->ug_dir_or_image = $compl['ug_dir_or_image'];
        $data->ug_autothumb = $compl['ug_autothumb'];
        $data->ug_big_dir = $compl['ug_big_dir'];
        $data->ug_full_dir = $compl['ug_full_dir'];
        $data->ug_file_nb = $compl['ug_file_nb'];
		$data->ug_grid_thumbs_pos = $compl['ug_grid_thumbs_pos'];
		$data->ug_grid_show_icons = $compl['ug_grid_show_icons'];
		if (!$compl['ug_articles']) { // not defined : assume articles
			$data->ug_articles = 'articles';
		} else {
			$data->ug_articles = $compl['ug_articles'];
		}
		if ($data->ug_articles == 'articles') {
			$data->slideslist = $data->slides;
		} else {
			$data->slideslist_k2 = $data->slides;
		}
		return $data;
    }
    /**
     *  Method to validate form data.
     */
    public function validate($form, $data, $group = null)
    {
        $name = $data['name'];
        unset($data["name"]);

        return array(
            'name'   => $name,
            'params' => json_encode($data)
        );
    }
}
