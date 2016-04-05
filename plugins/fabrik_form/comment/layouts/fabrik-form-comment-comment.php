<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Html;

$d = $displayData;
?>

<div class="metadata muted">
	<small><?php echo Html::icon('icon-user'); ?>
		<?php echo $d->name; ?>, <?php echo FText::_('PLG_FORM_COMMENT_WROTE_ON'); ?> 
	</small>
	<?php echo Html::icon('icon-calendar'); ?>
	<small><?php echo JHTML::date($d->comment->time_date, $d->dateFormat, 'UTC'); ?></small>
	<?php
	if ($d->internalRating) :
	?>
	<div class="rating">
	<?php 
	$r = (int) $d->comment->rating;
	for ($i = 0; $i < $r; $i++) :
		if ($d->j3) :
			?>
			<?php echo Html::icon('icon-star'); ?>
		<?php
		else :
			?><img src="' . $d->insrc . '" alt="star" />
		<?php
		endif;
	endfor;
	?>
	</div>
	<?php 
	endif;
?>
</div>

<div class="comment" id="comment-<?php echo $d->comment->id; ?>">
	<div class="comment-content"><?php echo $d->comment->comment; ?></div>
	<div class="reply">
		<?php
		if ($d->canAdd) :
			?>
				<a href="#" class="replybutton btn btn-small btn-link"><?php echo FText::_('PLG_FORM_COMMENT_REPLY'); ?></a>
			<?php endif;

			if ($d->canDelete) :
				?>
				<a href="#" class="del-comment btn btn-danger btn-small"><?php echo FText::_('PLG_FORM_COMMENT_DELETE');?></a>
			<?php
				endif;
			if ($d->useThumbsPlugin) :
				echo $d->thumbs;
			endif;
			?>
	</div>
</div>

<?php
if (!$d->commentsLocked) :
	echo $d->form;
endif;

