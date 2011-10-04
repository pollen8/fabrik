<?php

// if ((int)$this->rowid !== 0) {?>
<?php 
$img = htmlentities("<img src=\"../{po_events_repeat_Event_flyer_web___Event_flyer_web}\" alt=\"flyer\" />", ENT_QUOTES);
$crop = str_replace('flyers/', 'flyers/crop/', $this->modeldata['po_events_repeat_Event_flyer_web___Event_flyer_web']);
$startDate = JFactory::getDate($this->modeldata['po_events___Event_date'])->format('d-m-y');
$startTime = JFactory::getDate($this->modeldata['po_events___Event_date'])->format('h a');
$endtDate = JFactory::getDate($this->modeldata['po_events___Event_date_end'])->format('d-m-y');
$endTime = JFactory::getDate($this->modeldata['po_events___Event_date_end'])->format('h a');
?>

<div class="event-overview form-sidebar">
	<dl class="tabs">
		<dd class="fabrikGroup">
		<em>event over view</em>
		<h1>{po_events___Event_title}</h1>
		<ul>
			<li>
				<?php echo FabrikHelperHTML::image('calendar.png', 'form', $this->tmpl, '');?>
				<?php echo $startDate.' to '.$endtDate?>
			</li>
			<li>
			<?php echo FabrikHelperHTML::image('time-grey.png', 'form', $this->tmpl, '');?>
			<?php echo $startTime.' to '.$endTime?>
			</li>
			<li>
			<?php echo FabrikHelperHTML::image('preview-grey.png', 'form', $this->tmpl, '');?>
			<?php echo $this->modeldata['po_events___published']?>
			</li>
			<li class="description">
			<?php echo FabrikHelperHTML::image('pen-grey.png', 'form', $this->tmpl, '');?>
			{po_events___Event_description_short}</li>
			<li class="image">
			<?php echo FabrikHelperHTML::image('image-grey.png', 'form', $this->tmpl, '');?>
			<a href="#" class="fabrikTip" opts="{position:'left'}" title="<?php echo $img?>">
			<img src="../<?php echo $crop?>" alt="flyer" />
			</a>
			</li>
			<li class="artists">
			<?php echo FabrikHelperHTML::image('acts.png', 'form', $this->tmpl, '');?>
			<?php $acts = $this->modeldata['join'][90];
			$names = JArrayHelper::getValue($acts, 'po_events_15_repeat___act_id', array());
			$ids = JArrayHelper::getValue($acts, 'po_events_15_repeat___act_id_raw', array());
			if (!empty($names)) {
				echo '<ul>';
				for ($i = 0; $i < count($names); $i++) {
					echo '<li><a href="index.php?option=com_fabrik&task=form.view&formid=6&rowid='.$ids[$i].'">'.$names[$i].'</a></li>';
				}
				echo '</ul>';
			}?>
			</li>
		</ul>
		</dd>
		<dt>overview</dt>
	</dl>
	
	</div>
	<?php //}?>