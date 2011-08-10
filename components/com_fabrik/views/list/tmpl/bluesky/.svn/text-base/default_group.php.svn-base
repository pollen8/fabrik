<?php
 /* this is the group template used by the html template and by the ajax updating of the table
if group_by is not used then this in only repeated once.
*/
//@TODO: the id here will be repeated if group_by is on - need to add a group identifier to it

foreach ($this->rows as $groupedby => $group) {
	if ($this->isGrouped) {
		echo $this->grouptemplates[$groupedby];
	}
	?>
	<table class="fabrikList" id="list_<?php echo $this->table->id;?>" >
		<thead><?php
			echo $this->headingstmpl;
			?>
			</thead>
			<tfoot>
				<tr class="fabrik_calculations">
				<?php
			foreach ($this->calculations as $cal) {
				echo "<td>";
				echo array_key_exists($groupedby, $cal->grouped ) ? $cal->grouped[$groupedby] : $cal->calc;
				echo  "</td>";
			}
				?>
			</tr>
			</tfoot>
			<?php
			foreach ($group as $this->_row) {
				echo $this->loadTemplate( 'row');
		 	}
		 	?>
	</table>
<?php }?>