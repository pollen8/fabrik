<?php
/**
 * Google Chart default tmpl
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.chart
 * @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$row = $this->row;
?>
<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 0)) :?>
	<h1>
		<?php echo $row->label;?>
	</h1>
	<?php endif;?>
	<br />
	<p>
		<?php echo $row->intro_text;?>
	</p>
	<?php echo $this->loadTemplate( 'filter'); ?>
	<br />
	<?php echo $this->chart; ?>
</div>
