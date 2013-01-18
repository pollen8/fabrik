<?php
/**
 * Fabrik List Template: F3 Header
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="fabrikHeader">
	<?php
	echo $this->loadTemplate('buttons');
	if ($this->showFilters) {
		echo $this->loadTemplate('filter');
	}?>
</div>