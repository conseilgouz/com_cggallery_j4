<?php
/**
 * @component     CG Gallery for Joomla 4.x/5.x
 * Version			: 2.4.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2023 ConseilGouz. All Rights Reserved.
 * @author ConseilGouz 
**/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

// JHtml::_('behavior.tooltip');
HTMLHelper::_('behavior.multiselect');

$user		= Factory::getUser();
$userId		= $user->get('id');

$canOrder = ContentHelper::getActions('com_cggallery');
?>
<form action="<?php echo Route::_('index.php?option=com_cggallery&view=import'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>

	<div class="clr"> </div>
	<h2><?php echo Text::_('CG_GAL_IMPORT_ALREADY');?></h2>
    <?php if (empty($this->pages)) : ?>
        <div class="alert alert-no-items">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>   
    <ul>
		<?php foreach ($this->pages as $i => $page) :
			?>
				<li><?php echo $this->escape($page->title); ?>
                    <?php 
						$compl = new Registry($page->page_params);
						$text = "";
						$msg = "";
						if ($compl['ug_type'] == 'tiles') $msg = '('.$compl['ug_tiles_type'].')';
						$text .= $compl['ug_type'].' : '.$msg;
						if (strlen($text) > 70) $text = substr($text,0,70).'...';
						echo "--->".$text; ?>                     
				</li>
			<?php endforeach; ?>
	</ul>
<?php endif; ?>        
	<h2><?php echo Text::_('CG_GAL_IMPORT_TODO');?></h2>
        <table class="table table-striped" id="articleList">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="center">
					<?php echo Text::_('CG_GAL_TITLE'); ?>
				</th>
                                 <th width="15%">
                                    <?php echo Text::_( 'CG_GAL_LANGUAGE'); ?>
                                </th>
				<th width="5%">
					<?php echo Text::_('JSTATUS'); ?>
				</th>
				
			</tr>
		</thead>
		<tbody>
                    <?php foreach ($this->modules as $i => $module) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo HTMLHelper::_('grid.id', $i, $module['id']); ?>
				</td>
				<td class="center">
                    <?php echo $this->escape($module['title']); ?>                     
				</td>
                <td align="center">
                    <?php 
                        $lang = new stdClass();
                        $lang->language = $module['language'];
                        $lang->language_image = str_replace('-','_',strtolower($module['language']));
						$lang->language_title = $module['language'];
                        echo LayoutHelper::render('joomla.content.language', $lang); ?>
                </td>
				<td>
				      <?php echo HTMLHelper::_('jgrid.published', $module['published'], $i, 'import.', false, 'cb'); ?>                  
				</td>
			</tr>
                <?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="import" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</div>
</form>
