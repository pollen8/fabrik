<tr class="fabrik___heading">
<?php foreach ($this->headings as $key=>$heading) {?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php echo $this->headingClass[$key]['style']?>">
		<div class="heading"><?php echo  $heading; ?></div>
		<?php if (array_key_exists($key, $this->filters)) {
			$filter = $this->filters[$key];
			$required = $filter->required == 1 ? ' notempty' : '';
			echo '<div class="filter '.$required.'">
			<span>'.$filter->element.'</span></div>';
		}?>
	</th>
	<?php }?>
</tr>
<?php $doc = JFactory::getDocument();
$doc->addScriptDeclaration("
head.ready(function(){
	new AdminModuleHeadings('".$this->list->renderid."');
});
"); ?>
