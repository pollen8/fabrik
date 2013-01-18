<?php
/**
 * Fabrik Kaltura Viz Default Tmpl
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.kaltura
 * @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$row = $this->row;
$params = $this->params;
?>
<div id="<?php echo $this->containerId;?>" class="fabrik_visualization">
	<?php if ($this->params->get('show-title', 1)) {?>
	<h1>
		<?php echo $row->label;?>
	</h1>
	<?php }?>
	<?php echo $this->loadTemplate( 'filter'); ?>
	<div>
		<?php echo $row->intro_text;?>
	</div>
	<div id="table_map" style="width:<?php echo $params->get('fb_gm_mapwidth');?>px; height:<?php echo $params->get('fb_gm_mapheight');?>px"></div>
</div>
