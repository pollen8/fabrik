<?php
/**
 * Layout: List filters
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;

$cols = array();
foreach ($d->filters as $key => $filter) :
	if ($key !== 'all') :
		$required = $filter->required == 1 ? ' notempty' : '';
		$col      = '<div data-filter-row="' . $key . '" class="fabrik_row oddRow' . $required . '">';
		$col .= $filter->label . '<br />' . $filter->element;
		$col .= '</div>';
		$cols[] = $col;
	endif;
endforeach;

$showClearFilters = false;
foreach ($d->filters as $key => $filter) :
	if ($filter->displayValue !== '') :
		$showClearFilters = true;
	endif;
endforeach;

?>
<?php if ($showClearFilters) : ?>
	<div>
		<?php echo Text::_('COM_FABRIK_FILTERS_ACTIVE') ?>
		<?php
		foreach ($d->filters as $key => $filter) :
			if ($filter->displayValue !== '') :
				?>
				<span class="label label-inverse">
				<?php echo $filter->label . ': ' . $filter->displayValue . ' '; ?>
					<a data-filter-clear="<?php echo $key; ?>" href="#" style="color: white;">
						<?php echo Html::icon('icon-cancel', '', 'style="text-align: right; "'); ?>
					</a>
			</span>
				<?php
			endif;
		endforeach;
		?>
	</div>
	<?php
endif;
?>
<div class="fabrikFilterContainer modal hide fade" id="filter_modal">

	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><?php echo Html::icon('icon-filter', Text::_('COM_FABRIK_FILTER')); ?></h3>
	</div>
	<div class="modal-body">
		<table class="table table-stripped">
			<?php
			echo implode("\n", Html::bootstrapGrid($cols, $d->filterCols));
			?>
		</table>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal">Close</a>
		<?php
		if ($d->showClearFilters) : ?>
			<a class="btn clearFilters" href="#">
				<?php echo Html::icon('icon-refresh', Text::_('COM_FABRIK_CLEAR')); ?>
			</a>
		<?php endif ?>
		<?php
		if ($d->filter_action != 'onchange') :
			?>
			<input type="button" class="btn btn-primary fabrik_filter_submit"
				value="<?php echo Text::_('COM_FABRIK_GO'); ?>" name="filter">
			<?php
		endif;
		?>
	</div>
</div>