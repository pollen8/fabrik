<?php

defined('JPATH_BASE') or die;

$d = $displayData;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

?>
<div class="<?php echo $d->name; ?>_container" id="<?php echo $d->id; ?>_container">
	<div class="row">
		<div class="span6 <?php echo $d->errorCSS; ?>">

			<?php echo Text::_('PLG_FABRIK_PICKLIST_FROM'); ?>:
			<ul id="<?php echo $d->id; ?>_fromlist" class="picklist well well-small fromList">

				<?php
				foreach ($d->from as $value => $label) :
					?>
					<li id="<?php echo $d->id; ?>_value_<?php echo $value;?>" class="picklist">
						<?php echo $label;?>
					</li>
				<?php
				endforeach;
				?>

				<li class="emptypicklist" style="display:none"><?php echo Html::icon('icon-move'); ?>
					<?php echo Text::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE'); ?>
				</li>
			</ul>
		</div>
		<div class="span6">
			<?php echo Text::_('PLG_FABRIK_PICKLIST_TO'); ?>:
			<ul id="<?php echo $d->id; ?>_tolist" class="picklist well well-small toList">

				<?php
				foreach ($d->to as $value => $label) :
					?>
					<li id="<?php echo $d->id; ?>_value_<?php echo $value;?>" class="<?php echo $value;?>">
						<?php echo $label;?>
					</li>
				<?php
				endforeach;
				?>

				<li class="emptypicklist" style="display:none"><?php echo Html::icon('icon-move'); ?>
					<?php echo Text::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE'); ?>
				</li>
			</ul>
		</div>
	</div>
	<input type="hidden" name="<?php echo $d->name; ?>" value="<?php echo htmlspecialchars($d->value, ENT_QUOTES); ?>" id="<?php echo $d->id; ?>" />
	<?php echo $d->addOptionsUi; ?>
</div>