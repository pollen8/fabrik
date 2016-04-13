<?php
defined('JPATH_BASE') or die;

use Fabrik\Helpers\Text;

$d = $displayData;
?>
<div id="fabrik-comments">
	<h3>
		<a href="#" name="comments">
			<?php
			if ($d->commentCount === 0) :
				echo Text::_('PLG_FORM_COMMENT_NO_COMMENTS');
			else:
				if ($d->showCountInTitle) :
					$data[] = $d->commentCount . ' ';
				endif;

				echo Text::_('PLG_FORM_COMMENT_COMMENTS');
			endif;
			?>
		</a>
	</h3>
<?php
echo $d->commnents;

if (!$d->commentsLocked) :
	if (!$d->userLoggedIn && $d->anonymous == 0) :
		?>
		<h3><?php echo Text::_('PLG_FORM_COMMENT_PLEASE_SIGN_IN_TO_LEAVE_A_COMMENT'); ?></h3>
	<?php
	else :
		?>
		<h3><?php echo Text::_('PLG_FORM_COMMENT_ADD_COMMENT') ;?></h3>
	<?php
	endif;
	echo $d->form;

endif;
?>
</div>