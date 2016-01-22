<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 21/01/2016
 * Time: 16:49
 */

$d = $displayData;
?>

<div id="<?php echo $d->id; ?>" class="fabrikWindow fabrikWindow-modal modal">
	<div class="modal-header">
		<h3 class="handlelabel"><?php echo $d->title; ?></h3>
		<a href="#" class="closeFabWin">
			<span class="icon-cancel icon-remove-sign"></span>
		</a>
	</div>
	<div class="contentWrapper">
		<div class="itemContent">
			<div class="itemContentPadder">
				<?php echo $d->content;?>
			</div>
		</div>
	</div>
</div>
