<?php
/**
 * Administrator QuickIcon
 *
 * @package        Joomla.Administrator
 * @subpackage     mod_quickicon
 * @copyright      Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<?php if (!empty($buttons) && $menuLinks): ?>
	<div class="cpanel">
		<?php foreach ($buttons as $button) : ?>
			<div class="row-striped">
				<div class="row-fluid" <?php echo empty($button['id']) ? '' : ' id="' . $button['id'] . '"' ?>>
					<div class="span12">
						<a href="<?php echo $button['link']; ?>"
							<?php
							echo empty($button['target']) ? '' : ' target="' . $button['target'] . '" ';
							echo empty($button['onclick']) ? '' : ' onclick="' . $button['onclick'] . '" ';
							echo empty($button['title']) ? '' : ' title="' . htmlspecialchars($button['title']) . '" ';
							?>
						>
							<img style="width:16px" src="<?php echo JURI::base(true) . $button['image']; ?>" />
							<?php echo empty($button['text']) ? '' : '<span>' . $button['text'] . '</span>'; ?>
						</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<br>
<?php endif;
?>

<?php
$items = array();
foreach ($lists as $list) :
	$items[] = '<p>
		<a href="index.php?option=com_fabrik&task=list.view&listid=' . $list->id . '" style="font-size: 36px;">
			<span class="' . $list->icon . '"></span> <span style="margin-left:6px">' . $list->label . '</span>
		</a>
	</p>';
endforeach;

echo FabrikHelperHTML::bootstrapGrid($items, 2, '', true);
