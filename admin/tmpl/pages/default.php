<?php
/**
 * @component     CG Gallery
 * Version			: 3.0.0
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @copyright (c) 2024 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz
**/
// no direct access
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

// JHtml::_('behavior.tooltip');
HtmlHelper::_('behavior.multiselect');

$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= ContentHelper::getActions('com_cggallery');
$saveOrder	= $listOrder == 'ordering';
?>
<form action="<?php echo Route::_('index.php?option=com_cggallery&view=pages'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>

	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>  
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('COM_CGGALLERY_SEARCH_IN_TITLE'); ?>" />
        </div>
        <div class="btn-group pull-left">            
			<button type="submit" class="btn hasTooltip"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn hasTooltip" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			
					<select name="filter_state" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo Text::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo HtmlHelper::_('select.options', HtmlHelper::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
					</select>
		</div>
	</div>
	<div class="clr"> </div>

    <?php if (empty($this->pages)) : ?>
        <div class="alert alert-no-items">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>   
    <table class="table table-striped" id="articleList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="5%" class="nowrap">
					<?php echo HtmlHelper::_('grid.sort', 'JGRID_HEADING_ID', 't.id', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HtmlHelper::_('grid.sort', 'CG_GAL_TITLE', 't.title', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HtmlHelper::_('grid.sort', 'CG_GAL_TYPE', 't.ug_type', $listDirn, $listOrder); ?>
				</th>
               <th width="15%">
                    <?php echo HtmlHelper::_('searchtools.sort', 'CG_GAL_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
				<th width="5%">
					<?php echo HtmlHelper::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
				</th>
				
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->pages as $i => $page) :
		    $ordering	= ($listOrder == 'ordering');
		    $canCreate	= $user->authorise('core.create');
		    $canEdit	= $user->authorise('core.edit');
		    $canCheckin	= $user->authorise('core.manage', 'com_checkin') || $page->checked_out == $userId || $page->checked_out == 0;
		    $canChange	= $user->authorise('core.edit.state') && $canCheckin;
		    ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo HtmlHelper::_('grid.id', $i, $page->id); ?>
				</td>
				<td class="center">
                    <a href="<?php echo Route::_('index.php?option=com_cggallery&task=page.edit&id='.(int) $page->id); ?>">
                    <?php echo $this->escape($page->id); ?>                     
					</a>
				</td>
				<td class="center">
					<a href="<?php echo Route::_('index.php?option=com_cggallery&task=page.edit&id='.(int) $page->id); ?>">
                    <?php echo $this->escape($page->title); ?>                     
					</a>
				</td>
				<td class="center">
                    <?php
		                $text = "";
		    $msg = "";
		    if ($page->ug_type == 'tiles') {
		        $msg = '('.$page->ug_tiles_type.')';
		    }
		    $text .= $page->ug_type.' : '.$msg;
		    if (strlen($text) > 70) {
		        $text = substr($text, 0, 70).'...';
		    }
		    echo $text; ?>                     
				</td>
                <td align="center">
                    <?php echo LayoutHelper::render('joomla.content.language', $page); ?>
                </td>
				<td class="center">
					<?php echo HtmlHelper::_('jgrid.published', $page->state, $i, 'pages.', $canChange, 'cb'); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
    <?php endif; ?> 
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HtmlHelper::_('form.token'); ?>
	</div>
	</div>
</form>
