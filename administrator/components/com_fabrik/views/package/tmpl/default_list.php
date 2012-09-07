<?php
/**
 * Admin Package list Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die;
?>
<ul class="adminformlist">
<?php foreach ($this->listform->getFieldset('details') as $field): ?>
<li>
	<?php echo $field->label . $field->input; ?>
</li>
<?php endforeach; ?>
</ul>