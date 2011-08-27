<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
//@TODO if we ever get calendars inside packages then the ids will need to be
//replaced with classes contained within a distinct id

$row =& $this->row;
?>


<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 0)) {?>
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
						<a href="#" class="approvalTip">
							<?php echo FabrikHelperHTML::image('attention2.png', 'list', '');?>
						</a>
						<ul class="floating-tip" style="display:none">
							<li>
								<a class="approve" href="index.php?index.php?option=com_fabrik&format=raw&view=visualization&visualizationid=<?php echo $this->id?>&plugintask=approve&listid=<?php echo $row->listid?>&rowid=<?php echo $row->rowid?>">
									<?php echo FabrikHelperHTML::image('approve.png', 'visualization', '');?><span>approve</span>
								</a>
							</li>
							<li>
								<a class="disapprove"  href="index.php?index.php?option=com_fabrik&format=raw&view=visualization&visualizationid=<?php echo $this->id?>&plugintask=disapprove&listid=<?php echo $row->listid?>&rowid=<?php echo $row->rowid?>">
									<?php echo FabrikHelperHTML::image('disapprove.png', 'visualization', '');?><span>disapprove</span>
									</a>
							</li>
						</ul>
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

});
</script>