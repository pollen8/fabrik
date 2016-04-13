<?php
/**
 * Bootstrap Details Template: Repeat group rendered as standard form
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$group = $this->group;
foreach ($group->subgroups as $subgroup) :
	?>
	<div class="fabrikSubGroup">
	<?php
		// Add the add/remove repeat group buttons
		if ($group->editable) : ?>
			<div class="fabrikGroupRepeater pull-right">
				<?php if ($group->canAddRepeat) :?>
				<a class="addGroup" href="#">
					<?php echo Html::image('plus.png', 'form', $this->tmpl, array('class' => 'fabrikTip tip-small', 'opts' => '{trigger: "hover"}', 'title' => Text::_('COM_FABRIK_ADD_GROUP')));?>
				</a>
				<?php
				endif;
				if ($group->canDeleteRepeat) :?>
				<a class="deleteGroup" href="#">
					<?php echo Html::image('minus.png', 'form', $this->tmpl, array('class' => 'fabrikTip tip-small', 'opts' => '{trigger: "hover"}', 'title' => Text::_('COM_FABRIK_DELETE_GROUP')));?>
				</a>
				<?php endif;?>
			</div>
		<?php
		endif;
		?>
		<div class="fabrikSubGroupElements">
			<?php

			// Load each group in a <ul>
			$this->elements = $subgroup;
			echo $this->loadTemplate('group');
			?>
		</div><!-- end fabrikSubGroupElements -->
	</div><!-- end fabrikSubGroup -->
	<?php
endforeach;
