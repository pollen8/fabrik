<?php
/**
 * Bootstrap List Template - Buttons
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

?>
<div class="btn-group">
	<?php if ($this->canGroupBy) :?>
		<div class="btn-group">
			<a href="#" class="btn dropdown-toggle groupBy" data-toggle="dropdown">
				<?php echo Html::icon('icon-list-view', Text::_('COM_FABRIK_GROUP_BY'));?>
			</a>
			<ul class="dropdown-menu">
				<?php foreach ($this->groupByHeadings as $url => $obj) {?>
					<li><a data-groupby="<?php echo $obj->group_by?>" href="<?php echo $url?>"><?php echo $obj->label?></a></li>
				<?php
				}?>
			</ul>
		</div>
	<?php endif ?>

	<?php if ($this->showAdd) {?>
		<a class="addbutton btn addRecord" href="<?php echo $this->addRecordLink;?>">
			<?php echo Html::icon('icon-plus', $this->addLabel);?>
		</a>
	<?php }?>

	<?php if ($this->showClearFilters) :?>
		<a class="clearFilters btn" href="#">
			<?php echo Html::icon('icon-refresh', Text::_('COM_FABRIK_CLEAR')); ?>
		</a>
	<?php endif ?>

	<?php if ($this->showCSV) {?>
		<a href="#" class="btn csvExportButton">
			<?php echo Html::icon('icon-upload', Text::_('COM_FABRIK_EXPORT_TO_CSV')); ?>
		</a>
	<?php }?>

	<?php if ($this->advancedSearch !== '') : ?>
		<a href="<?php echo $this->advancedSearchURL?>" class="btn advanced-search-link">
			<?php echo Html::icon('icon-search', Text::_('COM_FABRIK_ADVANCED_SEARCH')); ?>
		</a>
	<?php endif?>

	<?php if ($this->showCSVImport) {?>
		<a href="<?php echo $this->csvImportLink;?>" class="btn csvImportButton">
			<?php echo Html::icon('icon-download', Text::_('COM_FABRIK_IMPORT_FROM_CSV')); ?>
		</a>
	<?php }?>

	<?php if ($this->showRSS) {?>
		<a href="<?php echo $this->rssLink;?>" class="btn feedButton">
			<?php echo Html::image('feed.png', 'list', $this->tmpl);?>
			<?php echo Text::_('COM_FABRIK_SUBSCRIBE_RSS');?>
		</a>
	<?php }
	if ($this->showPDF) {?>
			<a href="<?php echo $this->pdfLink;?>" class="btn pdfButton">
				<?php echo Html::icon('icon-file', Text::_('COM_FABRIK_PDF')); ?>
			</a>
	<?php }?>


</div>
<p></p>
