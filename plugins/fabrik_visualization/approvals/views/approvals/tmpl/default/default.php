<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
//@TODO if we ever get calendars inside packages then the ids will need to be
//replaced with classes contained within a distinct id

$row =& $this->row;
?>


<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 1)) {?>
		<h1><?php echo $row->label;?></h1>
	<?php }?>
	<table class="fabrikList">
		<thead>
			<tr class="fabrik___heading">
				<th><?php echo 'Type';//JText::_('PLG_VIZ_APPROVALS_TYPE')?></th>
				<th><?php echo 'Title';//JText::_('PLG_VIZ_APPROVALS_TITLE')?></th>
				<th><?php echo 'User';//JText::_('PLG_VIZ_APPROVALS_USER')?></th>
				<th><?php echo 'Approve';//JText::_('PLG_VIZ_APPROVALS_APPROVE')?></th>
				<th><?php echo 'View';//JText::_('PLG_VIZ_APPROVALS_VIEW')?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			foreach ($this->rows as $row) {?>
				<tr class="fabrik_row oddRow<?php echo $i%2?>">
					<td><?php echo $row->type?></td>
					<td><?php echo $row->title?></td>
					<td><?php echo $row->user?></td>
					<td>
					<a href="#" class="approvalTip" title="test">
						<?php echo FabrikHelperHTML::image('attention2.png', 'list', '');?>
					</a>
					</td>
					<td>
					<a href="<?php echo $row->view?>">
						<?php echo FabrikHelperHTML::image('view.png', 'list', '');?>
					</a></td>
				</tr>
			<?php $i++;
			}?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
head.ready(function() {
	new FloatingTips('.approvalTip', {html:true, position:'right',
		balloon: true, 'className':'approvalTip',
		arrowSize:12});
});
</script>