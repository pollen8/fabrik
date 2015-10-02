<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;
?>
<form method="post" action="index.php" name="frontendForm">
	<table>
		<tr>
			<td><label for="email"><?php echo FText::_('COM_FABRIK_YOUR_FRIENDS_EMAIL') ?>:</label>
			</td>
			<td><input class="input" type="text" size="25" name="email" id="email" /></td>
		</tr>
		<tr>
			<td><label for="yourname"><?php echo FText::_('COM_FABRIK_YOUR_NAME'); ?>:</label>
			</td>
			<td><input class="input" type="text" size="25" name="yourname" id="yourname" /></td>
		</tr>
		<tr>
			<td><label for="youremail"><?php echo FText::_('COM_FABRIK_YOUR_EMAIL'); ?>:</label>
			</td>
			<td><input class="input" type="text" size="25" name="youremail" id="youremail" /></td>
		</tr>
		<tr>
			<td><label for="subject"><?php echo FText::_('COM_FABRIK_MESSAGE_SUBJECT'); ?>:</label>
			</td>
			<td><input class="input" type="text" size="40" maxlength="40" name="subject"
					id="subject" /></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="submit" class="button btn btn-primary"
					value="<?php echo FText::_('COM_FABRIK_SEND_EMAIL'); ?>" />
				<?php

				if (!$d->j3) :?>
				<input type="button" name="cancel"
					value="<?php echo FText::_('COM_FABRIK_CANCEL'); ?>" class="button btn"
					onclick="window.close();" /></td>
			<?php
			endif;
			?>
		</tr>
	</table>
	<input name="referrer"
		value="<?php echo $d->referrer; ?>"
		type="hidden" /> <input type="hidden" name="option"
		value="com_<?php echo $d->package; ?>" /> <input type="hidden"
		name="view" value="emailform" /> <input type="hidden" name="tmpl"
		value="component" />

	<?php echo JHTML::_('form.token'); ?>
</form>
