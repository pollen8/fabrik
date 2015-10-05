<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

$d = $displayData;
$buttonProperties = array('class' => 'fabrikTip', 'opts' => "{notice:true}", 'title' => '<span>' . FText::_('COM_FABRIK_PDF') . '</span>',
	'alt' => FText::_('COM_FABRIK_PDF'));
?>
<a href="<?php echo $d->pdfURL; ?>">
	<?php echo FabrikHelperHTML::image('pdf.png', 'list', $this->tmpl, $buttonProperties);?>
</a>