<?php

/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
$url = JRoute::_('index.php');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
//@TODO load this from the JForm that we use in admin
?>
<form enctype="multipart/form-data" action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="fabrik-form" class="form-validate">
<div class="width-100 fltlft">
	<?php
	$id	= JRequest::getInt('listid', 0); // from list data view in admin
	$cid = JRequest::getVar('cid', array(0));// from list of lists checkbox selection
	JArrayHelper::toInteger($cid);
	if ($id === 0) {
		$id = $cid[0];
	}
	if (($id !== 0)) {
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('label')->from('#__{package}_lists')->where('id = '.$id);
		$db->setQuery($query);
		$list = $db->loadResult();
		print_r($list);
	}
	$fieldsets = array('details');
	$fieldsets[] = $id === 0 ? 'creation' : 'append';
	$fieldsets[] = 'format';
	?>
		<input type="hidden" name="listid" value="<?php echo $id ;?>" />
<?php foreach ($fieldsets as $fieldset) {?>
	<fieldset class="adminform">
		<ul>
		<?php foreach ($this->form->getFieldset($fieldset) as $field) :?>
			<li>
				<?php echo $field->label; ?><?php echo $field->input; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php }?>

	<input type="hidden" name="task" value="import.doimport" />
  	<?php echo JHTML::_('form.token');
	echo JHTML::_('behavior.keepalive'); ?>
	<input type="submit" value="<?php echo JText::_('COM_FABRIK_IMPORT_CSV')?>" />
	</div>
</form>