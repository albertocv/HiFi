<?php
echo '<div id="comments">';
	
	HeadwayComments::maybe_password_protected_message();

	HeadwayComments::show_comments();

	comment_form(apply_filters('headway_comment_form_args',
		array(
			'comment_notes_before' => null,
			'comment_notes_after' => null,
			'cancel_reply_link' => __('Discard Reply', 'headway')
		)
	));

echo '</div><!-- #comments -->';