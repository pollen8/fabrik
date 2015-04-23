<?php
defined('JPATH_BASE') or die;
$d = $displayData;
?>

<?php
if ($d->askReceipt) :
	?>
	<label class="checkbox">
		<input type="checkbox" name="fabrik_email_copy" class="contact_email_copy" value="1" />
		<?php echo $d->label;?>
	</label>
<?php
endif;
?>

