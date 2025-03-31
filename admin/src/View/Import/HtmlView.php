<?php
/**
 * @component     CG Gallery
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2025 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\View\Import;
// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

class HtmlView extends BaseHtmlView
{
	protected $pages;
	protected $pagination;
	protected $state;
        protected $modules;
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->pages		= $this->get('Items');
        $this->modules          = $this->getModules();
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Factory::getApplication()->enqueueMessage(implode("\n", $errors),'error');
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
        $canDo = ContentHelper::getActions('com_cggallery');
		$user = Factory::getUser();
		ToolBarHelper::title(Text::_('COM_GALLERY_IMPORT'), 'page.png');

		if ($canDo->get('core.create') || (count($user->getAuthorisedCategories('com_cggallery', 'core.create'))) > 0 ) {
			ToolBarHelper::custom('import.add','checkbox-partial','','import');
		}

		if ($canDo->get('core.admin')) {
			ToolBarHelper::divider();
			ToolBarHelper::preferences('com_cggallery');			
		}
	}
        protected function getModules() {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $result = $db->setQuery(
                $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' like ' . $db->quote('mod_simple_unite_gallery'). ' OR '. $db->quoteName('module') . ' like ' . $db->quote('mod_cg_gallery'))
            )->loadAssocList();
            return $result;   
        }
}
