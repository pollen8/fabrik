<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;

$image = '<img src="' . $d->defaultImage . '" alt="' . $d->value . '" ' . $d->float . ' class="imagedisplayor"/>';
?>
<div class="fabrikSubElementContainer" id="<?php echo $d->id; ?>">
	<?php
	if ($d->canSelect) :
		?>
		<img src="<?php echo $d->defaultImage; ?>" alt="<?php echo $d->value; ?>" <?php echo $d->float; ?> class="imagedisplayor" />
		<br />
		<?php echo JHTML::_('select.genericlist', $d->images, $d->imageName, 'class="inputbox imageselector" ', 'value', 'text', $d->image);
		echo Html::folderAjaxSelect($d->folders);
		?>
		<input type="hidden" name="<?php echo $d->name; ?>" value="<?php echo $d->value; ?>" class="fabrikinput hiddenimagepath folderpath" />
	<?php
	else :
		if ($d->linkURL) :
			?>
			<a href="<?php echo $d->linkURL; ?>" target="_blank"><?php echo $image; ?></a>
		<?php
		else :
			echo $image;
		endif;
		?>
		<input type="hidden" name="<?php echo $d->name; ?>" value="<?php echo $d->value; ?>" class="fabrikinput hiddenimagepath folderpath" />
	<?php
	endif;
	?>
</div>