<?php
/**
 * Cascading drop down front end select layout
 */
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;

// If add and select put them in a button group.
if ($d->frontEndSelect && $d->frontEndAdd && $d->editable) :
	// Set position inherit otherwise btn-group blocks selection of checkboxes
?>
	<div class="btn-group" style="position:inherit">
<?php
endif;

if ($d->frontEndSelect && $d->editable) :
	Text::script('PLG_ELEMENT_DBJOIN_SELECT');
?>
	<a href="<?php echo $d->chooseUrl; ?>" class="toggle-selectoption btn" title="<?php echo Text::_('COM_FABRIK_SELECT'); ?>">
		<?php echo Html::image('search.png', 'form', @$d->tmpl, array('alt' => Text::_('COM_FABRIK_SELECT'))); ?>
	</a>
<?php
endif;

if ($d->frontEndAdd && $d->editable) :
	Text::script('PLG_ELEMENT_DBJOIN_ADD');
	?>
	<a href="<?php echo $d->addURL; ?>" title="<?php echo Text::_('COM_FABRIK_ADD');?>" class="toggle-addoption btn">
		<?php echo Html::image('plus.png', 'form', @$d->tmpl, array('alt' => Text::_('COM_FABRIK_SELECT'))); ?>
	</a>
<?php
endif;
// If add and select put them in a button group.
if ($d->frontEndSelect && $d->frontEndAdd && $d->editable) :
?>
	</div>
<?php
endif;
