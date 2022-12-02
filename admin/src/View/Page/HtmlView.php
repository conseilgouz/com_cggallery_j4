<?php
/**
 * @component     CG Gallery
 * Version			: 2.1.1
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2022 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
namespace ConseilGouz\Component\CGGallery\Administrator\View\Page;
// No direct access
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView {

    protected $form;
    protected $pagination;
    protected $state;
	protected $page;

    /**
     * Display the view
     */
    public function display($tpl = null) {

		$this->form		= $this->get('Form');
		$this->page		= $this->get('Item');
		$this->formControl = $this->form ? $this->form->getFormControl() : null;	
		$this->page_params  = new Registry($this->page->page_params);			
	
        $this->addToolbar();

        // $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar() {

        $state = $this->get('State');
        $canDo = ContentHelper::getActions('com_cggallery');

		$user		= Factory::getUser();
		$userId		= $user->get('id');
		if (!isset($this->page->id)) $this->page->id = 0;
		$isNew		= ($this->page->id == 0);

		ToolBarHelper::title($isNew ? Text::_('CG_GAL_ITEM_NEW') : Text::_('CG_GAL_ITEM_EDIT'), '#xs#.png');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit')) {
			ToolBarHelper::apply('page.apply');
			ToolBarHelper::save('page.save');
		}

		if (empty($this->page->id))  {
			ToolBarHelper::cancel('page.cancel');
		}
		else {
			ToolBarHelper::cancel('page.cancel', 'JTOOLBAR_CLOSE');
		}
		ToolbarHelper::inlinehelp();			
    }

}
