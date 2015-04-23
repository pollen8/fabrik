<?php
defined('JPATH_BASE') or die;
$d = $displayData;
?>
<ul id="fabrik-comment-list">
	<?php
	if (empty($d->comments)) :
		?>
		<li class="empty-comment">&nbsp;</li>
	<?php
	else :
		foreach ($d->comments as $comment) :
			$depth = (int) $comment->depth * 20;
			?>
			<li class="usergroup-x" id="comment_<?php echo $comment->id; ?>"
				style="margin-left:<?php echo $depth; ?>px">
				<?php echo $comment->data;?>
			</li>
		<?php
		endforeach;
	endif;
	?>
</ul>
