<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;

if (!$d->popup):
	if ($d->icons) :
		$j2img = JHtml::_('image', 'system/emailButton.png', FText::_('JGLOBAL_EMAIL'), null, true);
		$image = FabrikWorker::j3() ? FabrikHelperHTML::icon('icon-envelope') . ' ' : $j2img;
	else:
		$image = '&nbsp;' . FText::_('JGLOBAL_EMAIL');
	endif;
?>

<a href="#" onclick="window.open('<?php echo $d->link;?>','win2','<?php echo $d->status;?>;');return false;"
title="<?php echo FText::_('JGLOBAL_EMAIL'); ?>"><?php echo $image;?></a>

<?php
endif;

