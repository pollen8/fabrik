<?php
/* this is the group template used by the html template and by the ajax updating of the table
 if group_by is not used then this in only repeated once.
 */
//@TODO: the id here will be repeated if group_by is on - need to add a group identifier to it

foreach( $this->rows as $groupedby => $this->group ) {
	if ($this->isGrouped) {
		echo $this->grouptemplates[$groupedby];
	}
	?>
<table class="adminlist fabrikList"
	id="list_<?php echo $this->table->renderid;?>">
	<thead>
		<tr class="fabrik___heading">
		<?php foreach ($this->headings as $key=>$heading) {?>
			<th class="<?php echo $this->headingClass[$key]['class']?>"
			 style="<?php echo $this->headingClass[$key]['style']?>">
				<?php echo $heading; ?>
			</th>
		<?php }?>
		</tr>
	</thead>

	<tbody>
	<?php echo $this->loadTemplate('row'); ?>
	<?php if ($this->hasCalculations) {?>
		<tr class="fabrik_calculations">
		<?php
		foreach ($this->calculations as $cal) {
			echo "<td>";
			echo array_key_exists($groupedby, $cal->grouped) ? $cal->grouped[$groupedby] : $cal->calc;
			echo  "</td>";
		}
		?>
		</tr>
		<?php }?>
	</tbody>
</table>
		<?php }?>