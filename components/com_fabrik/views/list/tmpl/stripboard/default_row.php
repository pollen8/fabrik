<?php
$d = $this->_row->data;
//echo "<pre>";print_r($d);echo "</pre>";?>

<?php
$day = $d->cbx_scene___day_raw;
$ext = $d->cbx_scene___exterior_raw;
$key = $day.$ext;
//echo "key = $key<br>";
switch($key) {
	case 'DE':
		$timeColour = '#F3F4A8';
		break;
	case 'DI':
		$timeColour = '#fff';
		break;
	case 'NE':
		$timeColour = '#BCF4B6';
		break;
	case 'NI':
		$timeColour = '#C8D4F6';
		break;
}
?>

<li id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?>" style="background-color:<?echo $timeColour;?>">
	<?php foreach ($this->headings as $heading=>$label) {	?>
		<span class="<?php echo $this->cellClass[$heading]['class']?>">
			<?php echo @$this->_row->data->$heading;?>
		</span>
	<?php }?>
</li>
