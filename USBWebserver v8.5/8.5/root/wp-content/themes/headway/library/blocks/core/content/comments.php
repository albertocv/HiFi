<?php
/**
 * Callbacks functions and filters for the comment display.
 *
 * @package Headway
 * @subpackage Comments
 **/

class HeadwayComments {

	
	public static function add_comment_class_to_all_types($classes) {
		
		if ( !is_array($classes) )
			$classes = implode(' ', trim($classes));
				
		$classes[] = 'comment';
		
		return array_filter(array_unique($classes));
		
	}

	
	public static function maybe_password_protected_message() {
		
		if ( post_password_required() ) { 
			
			echo '<p class="nocomments">' . __('This post is password protected.  Please enter the password to view the comments.', 'headway') . '</p>';
			
			return;
			
		}

	}
	
	
	public static function show_comments() {
		
		global $post;
		
		if ( have_comments() ) {

			echo '<h3 id="comments">';
				comments_number(__('No Responses', 'headway'), __('One Response', 'headway'), __('% Responses', 'headway'));
				echo ' ' . __('to', 'headway') . ' <em>' . get_the_title() . '</em>';
			echo '</h3>';
			
			echo '<ol class="commentlist">';
			
				wp_list_comments(apply_filters('headway_comments_args', array(
					'avatar_size' => 44
				))); 

			echo '</ol><!-- .commentlist -->';

			echo '<div class="comments-navigation">';
				echo '<div class="alignleft">';
					paginate_comments_links();
				echo '</div>';
			echo '</div>';

		} else {

			if ( $post->comment_status != 'open' ) {

				if ( is_single() ) {
					
					$comments_closed = apply_filters('headway_comments_closed', __('Sorry, comments are closed for this post.', 'headway'));
					
					echo '<p class="comments-closed">' . $comments_closed . '</p>';
					
				}

			}

		}
		
	}

}