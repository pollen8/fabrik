<?php
/**
 * Google Chart default filter tmpl
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.chart
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if ($this->showFilters) : ?>
<form method="post" name="filter">
<?php
	foreach ($this->filters as $table => $filters) :
		if (!empty($filters)) :
		?>
	<table class="filtertable table table-striped">
		<tbody>
	  	<?php
			$c = 0;
			foreach ($filters as $filter) :
			?>
	    <tr>
	    	<td><?php echo $filter->label ?></td>
	    	<td><?php echo $filter->element ?></td>
	  <?php
			endforeach;
	?>
	</tbody>
	<thead>
		<tr>
			<th colspan="2"><?php echo $table ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th colspan="2" style="text-align:right;">
  				<i class="icon-filter"></i>
  				<button type="submit" class="btn btn-primary">
  					<?php echo JText::_('GO') ?>
  				</button>
			</th>
		</tr>
	</tfoot>
</table>
  <?php
		endif;
	endforeach;
?>
</form>
<?php
endif;