<?php
class HeadwayAdminWrite {
	
	
	public static function init() {
		
		/* Load the default meta boxes */
		Headway::load('admin/admin-meta-boxes');
				
		add_action('publish_page', array(__CLASS__, 'publish_post'));
		add_action('delete_post', array(__CLASS__, 'delete_post'));

		add_filter('get_sample_permalink_html', array(__CLASS__, 'open_in_visual_editor_button'), 10, 4);
		
	}
	
	
	public static function publish_post() {
		
		HeadwayCompiler::flush_cache();
		
	}
	
	
	public static function delete_post($postid) {
		
		$post = get_post($postid);
		$post_type = get_post_type_object($post->post_type);
		
		/* If the post type is a revision then don't do anything. */
		if ( $post->post_type == 'revision' )
			return false;
		
		/* Flush the Headway cache */
		HeadwayCompiler::flush_cache();
		
		/* Figure out the layout ID */
			//If it has no parents, just stop.
			if ( $post->post_parent === 0 ) {
			
				$layout_id = 'single-' . $post_type->name . '-' . $post->ID;
		
			//Otherwise, figure out parents and grandparents	
			} else {
			
				//Set up variables 
				$posts = array(
					$post->ID
				);
			
				$parents_str = '';
			
				//Get to business
				while ($post->post_parent != 0) {
				
					$posts[] = $post->post_parent;
				
					$post = get_post($post->post_parent);
				
				}
			
				foreach (array_reverse($posts) as $post_id) {
				
					$layout_id = 'single-' . $post_type->name . '-' . $parents_str . $post_id;
				
					$parents_str .= $post_id . '-'; 
				
				}
							
			}
		
		//Delete the blocks for the page/post
		HeadwayBlocksData::delete_by_layout($layout_id);
				
	}


	public static function open_in_visual_editor_button($return, $id, $new_title, $new_slug) {

		global $post;

		if ( !isset($post->ID) || !is_numeric($post->ID) || $post->post_status != 'publish' || !HeadwayCapabilities::can_user_visually_edit() )
			return $return;

		$layout_id = 'single-' . $post->post_type . '-' . $post->ID;
		$visual_editor_url = home_url('/?visual-editor=true#layout=' . $layout_id);

		$return .= '<span id="view-post-btn" style="margin-right: 3px;"><a href="' . $visual_editor_url . '" class="button button-primary" target="_blank">Open in Visual Editor</a></span>';

		return $return;

	}
	
	
}