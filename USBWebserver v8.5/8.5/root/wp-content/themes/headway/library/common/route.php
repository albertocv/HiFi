<?php
class HeadwayRoute {
	
	
	public static function init() {

		//Parse request runs before 'wp', but does not have $post or query set up.  This speeds things up and keeps 404s from happening
		add_action('parse_request', array(__CLASS__, 'maybe_run_trigger'));
		
		//We use 'wp' on this so $post is set up so we can query meta
		add_action('wp', array(__CLASS__, 'maybe_redirect_301'));

		add_filter('template_include', array(__CLASS__, 'direct'));
		
	}
	
	
	/**
	 * Direct index.php to the appropriate function
	 * 
	 * @return bool
	 **/
	public static function direct($template) {
		
		//If viewing the visual editor, stop the template loading and show the visual editor.
		if ( self::is_visual_editor() ) {
			
			//If user is logged in and can't visually edit, loop them back to normal template.
			if ( is_user_logged_in() && !HeadwayCapabilities::can_user_visually_edit() ) {
							
				wp_die('You have insufficient permissions to use the Headway Visual Editor.<br /><br /><a href="' . home_url() . '">Return to Home</a>');			
								
				return false;
				
			//If the user isn't logged in at all, log 'em in and loop back to visual editor as long as debug mode isn't active
			} elseif ( !is_user_logged_in() && !HeadwayOption::get('debug-mode') ) {
				
				return auth_redirect();
								
			}
			
			HeadwayVisualEditor::display();
			
			//Return false so the template loader doesn't load anything
			return false;
			
		//Theme Preview
		} elseif ( self::is_theme_preview() ) {
			
			wp_die('Headway and Headway Child Themes cannot be previewed.  Please activate the theme if you wish to see how it looks.');
			
			//Return false so the template loader doesn't load anything
			return false;
			
		} elseif ( self::is_grid() ) {
			
			HeadwayGrid::show();
			
			//Return false so the template loader doesn't load anything
			return false;
			
		}
						
		// If it's a regular display or anything else, just grab the template.
		return $template;

	}
	
	
	public static function maybe_run_trigger() {
		
		if ( !self::is_trigger() )
			return;

		//Deactivate redirect so the weird 301's don't happen
		remove_action('template_redirect', 'redirect_canonical');
		add_filter('wp_redirect', '__return_false', 12);

		//Cycle through
		switch ( headway_get('headway-trigger') ) {
			
			case 'compiler':				
				HeadwayCompiler::output_trigger();
			break;

			case 'layout-redirect':
				self::redirect_to_layout();
			break;
			
		}

		exit;
		
	}

	
	/**
	 * If a post, page, or any other singular item has the 301 Redirect set, then do the redirect.
	 **/
	public static function maybe_redirect_301() {
		
		global $post;
		
		//Don't try redirecting if the headers are already sent.  Otherwise, it'll result in an error and no redirect.
		if ( headers_sent() )
			return false;
				
		//Make sure that it's a single post and that $post is a valid object.
		if ( !is_object($post) || !is_singular() )
			return false;
			
		//Do not try redirecting if it's the visual editor or admin
		if ( is_admin() || self::is_visual_editor() || self::is_visual_editor_iframe() )
			return false;

		//If the redirect URL isn't set, then don't try anything.
		if ( !($redirect_url = HeadwayLayoutOption::get($post->ID, 'redirect-301', 'seo', false)) )
			return false;

		//If there is no HTTP or HTTPS in the URL, add it.
		if ( strpos($redirect_url, 'http://') !== 0 && strpos($redirect_url, 'https://') !== 0 )
			$redirect_url = 'http://' . $redirect_url;
			
		wp_redirect($redirect_url, 301);
		die();
		
	}
	
	
	/**
	 * Determine whether or not the site is being viewed in normal display mode.
	 * 
	 * @return bool
	 **/
	public static function is_display() {
		
		if ( self::is_visual_editor() )
			return false;
			
		if ( self::is_trigger() )
			return false;
			
		if ( is_admin() )
			return false;
			
		return true;
		
	}
	
	
	/**
	 * Checks if the visual editor is open.
	 * 
	 * @return bool
	 **/
	public static function is_visual_editor() {
				
		return headway_get('visual-editor', false);
		
	}
	
	
	public static function is_trigger() {
		
		return ( headway_get('headway-trigger') ) ? true : false;
		
	}
	
	
	public static function is_theme_preview() {
		
		return headway_get('preview') == 1 && headway_get('preview_iframe') == 1;
		
	}
	
	
	public static function is_visual_editor_iframe() {
		
		return headway_get('ve-iframe') && HeadwayCapabilities::can_user_visually_edit();
		
	}
	
	
	public static function is_grid() {

		return self::is_visual_editor_iframe() && headway_get('ve-iframe-mode') == 'grid';

	}


	/**
	 * Used for when a user clicks View Site in the Visual Editor
	 **/
	public static function redirect_to_layout() {

		remove_filter('wp_redirect', '__return_false', 12);

		if ( headway_get('debug') && HeadwayCapabilities::can_user_visually_edit() )
			wp_die(HeadwayLayout::get_url(headway_get('layout')));

		return wp_safe_redirect(HeadwayLayout::get_url(headway_get('layout')));

	}
 	

}