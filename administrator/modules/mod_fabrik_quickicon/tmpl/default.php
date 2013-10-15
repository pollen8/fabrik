<?php
/**
 * Administrator QuickIcon
 *
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<?php if (!empty($buttons)): ?>
	<div class="cpanel">
		<?php foreach ($buttons as $button) :?>
			<div class="row-striped">
				<div class="row-fluid" <?php echo empty($button['id']) ? '' : ' id="' . $button['id'] . '"'?>>
					<div class="span12">
						<a href="<?php  echo $button['link']; ?>"
						<?php
						echo empty($button['target']) ? '' : ' target="' . $button['target'] . '" ';
						echo empty($button['onclick']) ? '' : ' onclick="' . $button['onclick'] . '" ';
						echo empty($button['title']) ? '' : ' title="' . htmlspecialchars($button['title']) . '" ';
						?>
						>
						<img style="width:16px" src="<?php echo JURI::base(true) . $button['image']; ?>" />
						<?php echo empty($button['text']) ? '' : '<span>' . $button['text'] . '</span>';?>
						</a>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
<?php endif;?>
