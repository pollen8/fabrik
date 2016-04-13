<?php
/**
 * Email form layout
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;
?>
<a href="<?php echo $d->pdfURL; ?>" data-role="open-form-pdf" class="btn btn-default">
	<?php echo Html::icon('icon-file', Text::_('COM_FABRIK_PDF'));?>
</a>&nbsp;