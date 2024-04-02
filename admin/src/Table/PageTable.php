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

    public function __construct(DatabaseDriver $_db)
    {
        parent::__construct('#__cggallery_page', 'id', $_db);
        $this->created = Factory::getDate()->toSql();
    }

    public function check()
    {
        jimport('joomla.filter.output');
        return true;
    }
    public function store($key = 0)
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
        $input = Factory::getApplication()->input;
        $task = $input->get('task');
        if (($task == "save") || ($task == "apply")) {
            $data = $input->getVar('jform', array(), 'post', 'array');
            $this->slides = json_encode($data['slideslist']);
            // texteara with special html tags to keep
            $perso = $input->getRaw('jform', 'perso', 'post', 'array');
            $data['perso'] = $perso['perso'];
            $data['intro'] = $perso['intro'];
            $data['bottom'] = $perso['bottom'];
        }
        $data['id']   = $key;
        $data['title'] = $this->title;
        $data['slides'] = $this->slides;
        $data['state'] = $this->state;
        $data['language'] = $this->language;

        $data = (object) $data;
        if ($exists) { // update
            return $db->updateObject($table, $data, 'id');
        }
        // insert a new object
        $ret = $db->insertObject($table, $data, 'id');
        $this->id = $data->id;
        return $ret;
    }
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        $k = $this->_tbl_key;
        ArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            } else {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }
        $table = Table::getInstance('PageTable', __NAMESPACE__ . '\\', array('dbo' => $db));
        foreach ($pks as $pk) {
            if(!$table->load($pk)) {
                $this->setError($table->getError());
            }
            if($table->checked_out == 0 || $table->checked_out == $userId) {
                $table->state = $state;
                $table->checked_out = 0;
                $table->checked_out_time = 0;
                $table->check();
                if (!$table->store()) {
                    $this->setError($table->getError());
                }
            }
        }
        return count($this->getErrors()) == 0;
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
