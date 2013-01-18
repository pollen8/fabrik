<?php
/**
 * Fabrik List Template: F3 Row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<li id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?>">
	<?php foreach ($this->headings as $heading=>$label) {
		$style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';?>
		<span class="<?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>
			<?php echo @$this->_row->data->$heading;?>
		</span>
	<?php }?>
</li>