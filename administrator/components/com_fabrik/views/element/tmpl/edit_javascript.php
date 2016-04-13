<?php
/**
 * Admin Element Edit:javascript Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Text;

echo JHtml::_('tabs.panel', Text::_('COM_FABRIK_JAVASCRIPT'), 'settings');
?>
<div id="javascriptActions" class="accordion"></div>
<a class="addButton" href="#" id="addJavascript"><?php echo Text::_('COM_FABRIK_ADD'); ?></a>
