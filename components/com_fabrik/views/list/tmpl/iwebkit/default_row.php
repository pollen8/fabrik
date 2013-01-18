<?php
/**
 * Fabrik List Template: IWebKit Row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;
?>
<?php $params = $this->params;
$data = $this->_row->data;
$href = $params->get('mobile_link') == 'form' ? $data->fabrik_view_url : $data->fabrik_edit_url;
$liClass = $params->get('mobile_image') == '' ? '' : 'withimage';
$liClass= 'withimage';
$image = str_replace('.', '___', $params->get('mobile_image'));
$title = str_replace('.', '___', $params->get('mobile_title'));
$text = str_replace('.', '___', $params->get('mobile_text'));?>

<li class="<?php echo $liClass?> <?php echo $this->_row->class;?>" id="<?php echo $this->_row->id;?>">
		<a href="<?php echo $href?>" class="noeffect">
			<?php if ($image !== '') {
				echo $data->$image;
			}?>
			<span class="name">
			<?php if ($title !== '') {
				echo $data->$title;
			}?>
			</span>
			<span class="comment">
			<?php if ($text !== '') {
			echo $data->$text;
			}?>
			</span>
			<span class="arrow"></span>
		</a>
</li>