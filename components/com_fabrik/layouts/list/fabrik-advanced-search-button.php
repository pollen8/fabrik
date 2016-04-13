<?php
/**
 * List advanced search button layout
 */

defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Text;

$d = $displayData;
$title = '<span>' . Text::_('COM_FABRIK_ADVANCED_SEARCH') . '</span>';
$opts = array('alt' => Text::_('COM_FABRIK_ADVANCED_SEARCH'), 'class' => 'fabrikTip', 'opts' => "{notice:true}", 'title' => $title);

$img = Html::image('find.png', 'list', $d->tmpl, $opts);

?>
<a href="<?php echo $d->url; ?>" class="advanced-search-link"><?php echo $img;?></a>