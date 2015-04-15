<?php
defined('JPATH_BASE') or die;
$d     = $displayData;
$class = $d->j3 ? 'table table-striped' : 'fabrikList';
?>
<form id="update_col<?php echo $d->listRef; ?>">

	<table class="<?php echo $class; ?>" style="width:100%">
		<thead>
		<tr>
			<th><?php echo FText::_('COM_FABRIK_ELEMENT'); ?></th>
			<th><?php echo FText::_('COM_FABRIK_VALUE'); ?></th>
			<th>
				<a class="btn add button btn-primary" href="#">
					<?php echo $d->addImg; ?>
				</a>
			</th>
		<tr>
		</thead>
		<tbody>
		<tr>
			<td><?php echo $d->elements; ?></td>
			<td class="update_col_value">
			</th>
			<td>
				<div class="btn-group">
					<a class="btn add button btn-primary" href="#">
						<?php echo $d->addImg; ?>
					</a>
					<a class="btn button delete" href="#">
						<?php echo $d->delImg; ?>
					</a>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<input class="button btn button-primary" value="<?php echo FText::_('COM_FABRIK_APPLY'); ?>" type="button">
</form>