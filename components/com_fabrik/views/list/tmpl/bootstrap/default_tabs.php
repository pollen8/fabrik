<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;

if (!empty($this->tabs)) :
?>
<div>
	<?php
	echo Html::getLayout('fabrik-tabs')->render((object) array('tabs' => $this->tabs));
	?>
</div>
<?php
endif; ?>
