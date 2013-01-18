<?php
/**
 * Fabrik List Template: AdminModule Filter
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="fabrikFilterContainer">
<?php echo $this->clearFliterLink;?>
<?php if ($this->filter_action != 'onchange') {?>
<input type="button" class="fabrik_filter_submit button" value="<?php echo JText::_('COM_FABRIK_GO');?>"
			name="filter" />
			<?php }?>
</div>

