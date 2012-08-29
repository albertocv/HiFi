<?php
class HeadwayCapabilities {
		
		
	public static function init() {
		
		add_filter('members_get_capabilities', 'HeadwayCapabilities::register');
				
	}
	
	
	public static function register($capabilities) {
		
		$capabilities[] = 'headway_visual_editor';

		return apply_filters('headway_capabilities', $capabilities);
		
	}
	
	
	public static function can_user($capability) {
		
		if ( !function_exists('members_check_for_cap') )
			 return ( current_user_can('manage_options') || is_super_admin() );

		return current_user_can($capability);
		
	}
	
	
	/**
	 * Checks if the user can access the visual editor.
	 * 
	 * @uses headway_user_level()
	 * @uses HeadwayOption::get()
	 *
	 * @return bool
	 **/
	public static function can_user_visually_edit($ignore_debug_mode = false) {

		if ( !$ignore_debug_mode && HeadwayOption::get('debug-mode') )
			return true;

		return is_user_logged_in() && self::can_user('headway_visual_editor');
		
	}
	
	
}