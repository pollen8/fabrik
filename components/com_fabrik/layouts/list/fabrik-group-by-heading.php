<?php
/**
 * Layout: List group by headings
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;

$d = $displayData;
$imgProps = array('alt' => FText::_('COM_FABRIK_TOGGLE'), 'data-role' => 'toggle', 'data-expand-icon' => 'icon-arrow-down', 'data-collapse-icon' => 'icon-arrow-right');
?>

<?php if ($d->emptyDataMessage != '') : ?>
<a href="#" class="toggle">
	<?php else: ?>
	<a href="#" class="toggle fabrikTip" title="<?php echo $d->emptyDataMessage ?>" opts='{trigger: "hover"}'>
		<?php endif; ?>
		<?php echo Html::image('arrow-down.png', 'list', $d->tmpl, $imgProps); ?>
		<span class="groupTitle">
			<?php echo $d->title; ?> 
			<?php $d->group_by_show_count = isset($d->group_by_show_count) ? $d->group_by_show_count : '1'; 
			if ($d->group_by_show_count) : ?>
				<span class="groupCount">( <?php echo $d->count ?> )</span>
			<?php endif; ?>			
		</span>
	</a>

