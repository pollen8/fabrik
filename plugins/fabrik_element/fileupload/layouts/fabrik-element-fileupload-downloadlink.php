<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;

$class = $d->downloadImg !== '' ? '' : 'class="btn btn-primary button"';

?>

<?php if (!$d->canDownload) : ?>
	<img src="<?php echo $d->noAccessImage;?>"
		alt="<?php echo FText::_('PLG_ELEMENT_FILEUPLOAD_DOWNLOAD_NO_PERMISSION'); ?>" />
	<?php else :?>
<a href="<?php echo $d->href;?>" <?php echo $class; ?>>
	<?php if ($d->downloadImg !== '') : ?>
		<img src="<?php echo $d->downloadImg;?>" alt="<?php echo $d->title;?>" />
	<?php else :?>
		<?php echo Html::icon('icon-download icon-white') . ' ' . FText::_('PLG_ELEMENT_FILEUPLOAD_DOWNLOAD');?>
	<?php endif; ?>
</a>
<?php endif; ?>