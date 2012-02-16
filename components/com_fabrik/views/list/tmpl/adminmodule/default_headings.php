<tr class="fabrik___heading">
<?php foreach ($this->headings as $key=>$heading) {
	$filterFound = array_key_exists($key, $this->filters);
	?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php echo $this->headingClass[$key]['style']?>">
		
		<?php 
	
			echo '<span class="heading">' . $heading . '</span>';
		?>
		<?php if ($filterFound) {
				$filter = $this->filters[$key];
				$required = $filter->required == 1 ? ' notempty' : '';
				echo '<div class="filter '.$required.'">
				<span>'.$filter->element.'</span></div>';
		}?>
	</th>
	<?php }?>
</tr>
<?php $doc = JFactory::getDocument();
$doc->addScript(JURI::root(true).'/media/com_fabrik/js/filtertoggle.js');
$doc->addScriptDeclaration("
head.ready(function(){
	new FabFilterToggle('".$this->list->renderid."');
});
"); ?>
