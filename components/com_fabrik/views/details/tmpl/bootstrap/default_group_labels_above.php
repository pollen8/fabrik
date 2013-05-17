<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.1
 */

$element = $this->element;?>
<div class=" <?php echo $element->containerClass . $element->span; ?>" <?php echo $element->containerProperties?>>
	<?php echo $element->label;?>

	<?php if ($this->tipLocation == 'above') : ?>
		<span class="help-block"><?php echo $element->tipAbove ?></span>
	<?php endif ?>

	<div class="fabrikElement">
		<?php echo $element->element;?>
	</div>

	<div class="<?php echo $this->class?>">
		<?php echo $element->error ?>
	</div>

	<?php if ($this->tipLocation == 'side') : ?>
		<span class="help-block"><?php echo $element->tipSide ?></span>
	<?php endif ?>

	<?php if ($this->tipLocation == 'below') :?>
		<span class="help-block"><?php echo $element->tipBelow ?></span>
	<?php endif ?>
</div><!-- end control-group -->


