<?php
/**
 * Default Form Template: Repeat group rendered as an unordered list
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

$group = $this->group;
foreach ($group->subgroups as $subgroup) :
	?>
	<div class="fabrikSubGroup">
		<div class="fabrikSubGroupElements">
			<?php

			// Load each group in a <ul>
			$this->elements = $subgroup;
			echo $this->loadTemplate('group');
			?>
		</div>
	</div>
	<?php
endforeach;
