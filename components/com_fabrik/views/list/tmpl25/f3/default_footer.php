<?php
/**
 * Fabrik List Template: F3 Footer
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="fabrikFooter">
<?php
	echo $this->loadTemplate('calcs');
	echo $this->nav;
	print_r($this->hiddenFields);
?>
</div>