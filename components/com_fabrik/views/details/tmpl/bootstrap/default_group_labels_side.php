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

$element = $this->element; ?>
<div class=" <?php echo $element->containerClass . $element->span; ?>" <?php echo $element->containerProperties?>>
	<div class="span4">
		<?php echo $element->label;?>
	</div>
	<div class="span8">
		<?php if ($this->tipLocation == 'above') : ?>
			<p class="help-block"><?php echo $element->tipAbove ?></p>
		<?php endif ?>

		<div class="fabrikElement">
			<?php echo $element->element;?>
		</div>

		<?php if ($this->tipLocation == 'side') : ?>
			<p class="help-block"><?php echo $element->tipSide ?></p>
		<?php endif ?>


	<?php if ($this->tipLocation == 'below') :?>
		<p class="help-block"><?php echo $element->tipBelow ?></p>
	<?php endif ?>
	</div>
</div><!--  end span -->
