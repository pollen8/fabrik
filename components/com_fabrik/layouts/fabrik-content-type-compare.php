<?php
/**
 * Content type compare view levels layout
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

$d                     = $displayData;
$viewLevels            = $d->viewLevels;
$contentTypeViewLevels = $d->contentTypeViewLevels;

$max = max(count($viewLevels), count($contentTypeViewLevels));

?>
	<hr />
<?php if (!empty($d->alteredGroups)) : ?>
	<div class="alert alert-warning"><span class="icon-stack"></span>
		<strong>Groups hierarchy mismatch.</strong> You can continue to save, but do check that your form behaves as expected.<hr />
		<p>The following groups are affected:</p>
		<div class="">
			<?php

			foreach ($d->alteredGroups as $group) :?>
				<span class="label label-warning"><?php echo $group['title']; ?></span>
				<?php
			endforeach ?>
		</div>
	</div>
	<?php
endif;?>
<hr />
<?php
if ($d->match) :
	?>
	<div class="alert alert-info"><span class="icon-ok"></span> ACL Matched</div>
	<?php
else:
	?>
	<div class="alert alert-warning"><span class="icon-warning"></span>
		<strong>ACL mismatch</strong> The content type you are importing contains an access level which does not existing in your site.
	</div>

	<div class="alert alert-info"><span class="icon-question"></span>
		Please select the most appropriate view level and we will update the imported element ACL to match
	</div>

	<table class="table table-striped">
		<thead>
		<tr>
			<th>Content Type Access level</th>
			<th class="muted">Groups</th>
			<th>Site Access level</th>
		</tr>
		<tbody>
		<?php
		for ($i = 0; $i < $max; $i++) :
			$viewLevel            = ArrayHelper::getValue($viewLevels, $i, array());
			$contentTypeViewLevel = ArrayHelper::getValue($contentTypeViewLevels, $i, array());
			$viewRules            = ArrayHelper::getValue($viewLevel, 'rules', '');
			$contentTypeViewRules = ArrayHelper::getValue($contentTypeViewLevel, 'rules', 'N/A');
			$matched              = $viewRules === $contentTypeViewRules;
			if (!$matched) :
				?>
				<tr>
					<td>
						<?php echo ArrayHelper::getValue($contentTypeViewLevel, 'title', 'N/A'); ?>
					</td>
					<td class="muted">
						<?php echo ArrayHelper::getValue($contentTypeViewLevel, 'rules_labels', 'N/A'); ?>
					</td>
					<td>
						<?php echo JHtml::_('access.level', 'aclMap[' . $contentTypeViewLevel['id'] . ']', '', '', array()); ?>
					</td>
				</tr>
				<?php
			endif;
		endfor;
		?>
		</tbody>
		</thead>
	</table>

<?php
endif;
