<?php
/**
 * @component     CG Gallery
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2025 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz
**/

namespace ConseilGouz\Component\CGGallery\Administrator\Model;

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

class PagesModel extends ListModel
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 't.id',
                'title', 't.title',
                'state', 't.state',
                'slides', 't.slides',
                'language','t.language',
                'ug_type', 't.ug_type',
                'ug_tiles_type','t.ug_tiles_type');
        }

        parent::__construct($config);
    }
    protected function getListQuery()
    {
        // Initialise variables.
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query	= $db->getQuery(true);

        // Select the required fields from the table.
        $query->select('t.*');
        // Join over the language
        $query->select('l.title AS language_title, l.image AS language_image')
            ->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON l.lang_code = t.language');
        $query->from('#__cggallery_page as t');
        // Filter by published state
        $published = $this->getState('filter.state');
        if (is_numeric($published)) {
            $query->where('t.state = '.(int) $published);
        } elseif ($published === '') {
            $query->where('(t.state IN (0, 1))');
        }
        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $searchLike = $db->Quote('%'.$db->escape($search, true).'%');
            $search = $db->Quote($db->escape($search, true));
            $query->where('(t.title = '.$search.' )');
        } //end search

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering');
        $orderDirn	= $this->state->get('list.direction');
        $query->order($db->escape($orderCol.' '.$orderDirn));
        return $query;
    }
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialise variables.
        $app = Factory::getApplication('administrator');
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);
        // List state information.
        parent::populateState('t.id', 'DESC');
    }
}
