<tr class="fabrik___heading">
<?php foreach ($this->headings as $key=>$heading) {?>
	<th class="<?php echo $this->headingClass[$key]['class']?>" style="<?php $this->headingClass[$key]['style']?>">
		<span class="heading"><?php echo  $heading; ?></span>
		<?php if (array_key_exists($key, $this->filters)) {
			$filter = $this->filters[$key];
			$required = $filter->required == 1 ? ' notempty' : '';
			echo '<div class="filter '.$required.'">
			<span>'.$filter->element.'</span></div>';
		}?>
	</th>
	<?php }?>
</tr>

<script type="text/javascript">
head.ready(function(){
	var list = document.id('list_<?php echo $this->list->id?>');

	 window.addEvent('fabrik.list.update', function(l){
			if(l.id == <?php echo (int)$this->list->id?>){
				list.getElements('.fabrik___heading span.filter').hide();
			}
			return true;
	 });


	list.getElements('span.heading').each(function(h){
		var f = h.getNext();
		if (f) {
			h.setStyle('cursor', 'pointer');
			if(i = f.getElement('input')) {
				i.set('placeholder', h.get('text'));
			}
			f.hide();
		}
	});
	list.addEvent('click:relay(span.heading)', function(e){
		var f = e.target.getNext();
		if (f){
			f.toggle();
			//e.target.toggle();
		}
	});

});

</script>