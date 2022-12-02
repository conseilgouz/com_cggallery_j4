<?php
/**
 * @component     CG Gallery
 * Version			: 2.3.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\Table;
\defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

class PageTable extends Table implements VersionableTableInterface
{
	/**
	 * An array of key names to be json encoded in the bind function
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $_jsonEncode = ['params', 'metadata', 'urls', 'images'];

	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;
	
	function __construct(DatabaseDriver $_db)
	{
		parent::__construct('#__cggallery_page', 'id', $_db);
		$this->created = Factory::getDate()->toSql();
	}

	function check()
	{
		jimport('joomla.filter.output');
		return true;
	}
	function store($key = 0)
	{
        $db    = Factory::getDBo();
        $table = $this->_tbl;
        $key   = empty($this->id) ? $key : $this->id;

        // Check if key exists
        $result = $db->setQuery(
            $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName($this->_tbl))
                ->where($db->quoteName('id') . ' = ' . $db->quote($key))
        )->loadResult();

        $exists = $result > 0 ? true : false;

        // Prepare object to be saved
        $data = new \stdClass();
        $data->id   = $key;
        $data->title = $this->title;
        $data->slides = $this->slides;
		$input = Factory::getApplication()->input;
        $task = $input->get('task');
        if (($task == "save") || ($task == "apply")) {
			$compl = $input->getVar('jform', array(), 'post', 'array');
			$page_params = [];
			$page_params['ug_compression'] = $compl['ug_compression'];
			$page_params['ug_type'] = $compl['ug_type'];
			$page_params['ug_tiles_type'] = $compl['ug_tiles_type'];
			$page_params['ug_tile_width'] = $compl['ug_tile_width'];
			$page_params['ug_min_columns'] = $compl['ug_min_columns'];
			$page_params['ug_tile_height'] = $compl['ug_tile_height'];
			$page_params['ug_grid_num_rows'] = $compl['ug_grid_num_rows'];
			$page_params['ug_space_between_rows'] = $compl['ug_space_between_rows'];
			$page_params['ug_space_between_cols'] = $compl['ug_space_between_cols'];
			$page_params['ug_carousel_autoplay_timeout'] = $compl['ug_carousel_autoplay_timeout'];
			$page_params['ug_carousel_scroll_duration'] = $compl['ug_carousel_scroll_duration'];
			$page_params['ug_texte'] = $compl['ug_texte'];
			$page_params['ug_text_lgth'] = $compl['ug_text_lgth'];
			$page_params['ug_link'] = $compl['ug_link'];
			$page_params['ug_lightbox'] = $compl['ug_lightbox'];
			$page_params['ug_zoom'] = $compl['ug_zoom'];
			$page_params['ug_dir_or_image'] = $compl['ug_dir_or_image'];
			$page_params['ug_autothumb'] = $compl['ug_autothumb'];
			$page_params['ug_big_dir'] = $compl['ug_big_dir'];
			$page_params['ug_full_dir'] = $compl['ug_full_dir'];
			$page_params['ug_file_nb'] = $compl['ug_file_nb'];
			$page_params['ug_articles'] = $compl['ug_articles'];
			$page_params['ug_grid_thumbs_pos'] = $compl['ug_grid_thumbs_pos'];
			$page_params['ug_grid_show_icons'] =$compl['ug_grid_show_icons'];
	// texteara with special html tags to keep	
			$perso = $input->getRaw('jform', 'perso', 'post', 'array');		
			$page_params['perso'] = $perso['perso'];
			$page_params['intro'] = $perso['intro'];
			$page_params['bottom'] = $perso['bottom']; 
			$data->page_params =  json_encode($page_params);
			if ($page_params['ug_articles'] == 'articles') {
				$data->slides = json_encode($compl['slideslist']);
			} else {
				$data->slides = json_encode($compl['slideslist_k2']);
			}
		}
		$data->state = $this->state;
		$data->language = $this->language;
		
        if ($exists) { // update
            return $db->updateObject($table, $data, 'id');
        }
		// insert a new object
		$ret = $db->insertObject($table, $data,'id');
		$this->id = $data->id;
		return $ret;
	}
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			else {
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}
		$table = Table::getInstance('PageTable', __NAMESPACE__ . '\\', array('dbo' => $db));
		foreach ($pks as $pk)
		{
			if(!$table->load($pk))
			{
				$this->setError($table->getError());
			}
			if($table->checked_out==0 || $table->checked_out==$userId)
			{
				$table->state = $state;
				$table->checked_out=0;
				$table->checked_out_time=0;
				$table->check();
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}
		return count($this->getErrors())==0;
	}
	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
	}
	
}