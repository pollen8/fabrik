<?php
/**
 * Fabrik List Template: F3 Calcs
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<ul class="list">
	<li class="fabrik_calculations">
		<?php
		foreach ($this->calculations as $cal) {
			echo "<span>";
			echo $cal->calc;
			echo  "</span>";
		}
		?>
	</li>
</ul>