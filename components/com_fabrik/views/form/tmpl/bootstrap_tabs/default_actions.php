<?php
/**
 * Bootstrap Tabs Form Template - actions
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.1
 */

$form = $this->form;
if ($this->hasActions) : ?>
<div class="fabrikActions form-actions">
	<div class="row-fluid">
		<div class="span4">
			<div class="btn-group">
			<?php
			echo $form->submitButton;
			echo $form->applyButton;
			echo $form->copyButton;
			?>
			</div>
		</div>
		<?php if ($form->gobackButton . $form->resetButton . $form->deleteButton !== '') : ?>
		<div class="span4">
			<div class="btn-group">
				<?php
				echo $form->gobackButton . ' ' . $this->message;
				echo $form->resetButton . ' ';
				echo $form->deleteButton;
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
<?php endif;
