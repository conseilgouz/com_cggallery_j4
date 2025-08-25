<?php
/**
 * @component     CG Gallery
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2025 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz
**/

namespace ConseilGouz\Component\CGGallery\Site\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Model\ArticleModel;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentHelperRoute;

use ConseilGouz\Component\CGGallery\Site\Helper\CGHelper;

class PageModel extends ListModel
{
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 't.id',
                'title', 't.title',
                'state', 't.state',
                'sections', 't.sections',
                'page_params', 't.page_params'
            );
        }

        parent::__construct($config, $factory);
    }
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function getTable($name = 'Page', $prefix = 'Administrator', $options = array())
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getArticleInfos($id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('alias,catid')
            ->from('#__content')
            ->where(' id = ' .$id);
        $db->setQuery($query);
        return $db->loadObject();
    }

    public function getCatAlias($id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__categories')
            ->where(' id = ' .$id);
        $db->setQuery($query);
        return $db->loadObject();
    }

    public function getArticle(&$item)
    {
        $model     = new ArticleModel(array('ignore_request' => true));
        $app       = Factory::getApplication();
        $appParams = $app->getParams();
        $params = $appParams;
        $model->setState('params', $appParams);

        $model->setState('list.start', 0);
        $model->setState('list.limit', 1);
        // $model->setState('filter.published', 1);
        $model->setState('filter.featured', 'show');
        $model->setState('filter.category_id', array());

        // Access filter
        $access = ComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // Filter by language
        $model->setState('filter.language', $app->getLanguageFilter());
        // Ordering
        $model->setState('list.ordering', 'a.hits');
        $model->setState('list.direction', 'DESC');

        $onearticle = $model->getItem($item->file_id);

        $item->article = $onearticle;
        $item->article->text = $onearticle->introtext;
        $item->article->text = CGHelper::truncate($item->article->text, $params->get('ug_text_lgth', '100'), true, false);
        // set the item link to the article depending on the user rights
        if ($access || in_array($item->article->access, $authorised)) {
            // We know that user has the privilege to view the article
            $item->slug = $item->article->id . ':' . $item->article->alias;
            $item->catslug = $item->article->catid ? $item->article->catid . ':' . $item->article->category_alias : $item->article->catid;
            $link = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
        } else {
            $menu = $app->getMenu();
            $menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
            if (isset($menuitems[0])) {
                $Itemid = $menuitems[0]->id;
            } elseif (Factory::getApplication()->getInput()::getInt('Itemid') > 0) {
                $Itemid = Factory::getApplication()->getInput()::getInt('Itemid');
            }
            $link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
        }
        return $link;
    }
}
