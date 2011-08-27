<?php
 /*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

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
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('label')->from('#__{package}_lists')->where('id = '.$id);
		$db->setQuery($query);
		$list = $db->loadResult();
		print_r($list);
	}?>
		<input type="hidden" name="listid" value="<?php echo $id ;?>" />

	<fieldset class="adminform">
		<ul>
		<?php foreach ($this->form->getFieldset('details') as $field) :?>
			<li>
				<?php echo $field->label; ?><?php echo $field->input; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php $more = $id === 0 ? 'creation' : 'append';?>
	<fieldset class="adminform">
		<ul>
		<?php foreach ($this->form->getFieldset($more) as $field) :?>
			<li>
				<?php echo $field->label; ?><?php echo $field->input; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>

	<input type="hidden" name="task" value="" />
  	<?php echo JHTML::_('form.token');
	echo JHTML::_('behavior.keepalive'); ?>
	</div>
</form>