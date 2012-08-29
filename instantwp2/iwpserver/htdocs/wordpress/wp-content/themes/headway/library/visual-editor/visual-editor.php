<?php
class HeadwayVisualEditor {


	protected static $modes = array();
	
	
	protected static $default_mode = 'grid';
	
	
	protected static $default_layout = 'index';
	
	
	public static function init() {
		
		if ( !HeadwayCapabilities::can_user_visually_edit() )
			return;
					
		//If no child theme is active or if a child theme IS active and the grid is supported, use the grid mode.
		if ( current_theme_supports('headway-grid') )
			self::$modes['Grid'] = 'Add blocks and arrange your website structure';		
		
		self::$modes['Manage'] = 'Change the settings for blocks and more';

		//If child theme is active, then there will not be a design mode at all.
		if ( current_theme_supports('headway-design-editor') )
			self::$modes['Design'] = 'Choose fonts, colors, and other styles';
		
		//If the grid is disabled, set Manage as the default mode.
		if ( !current_theme_supports('headway-grid') )
			self::$default_mode = 'manage';
		
		//Attempt to raise memory limit to max
		@ini_set('memory_limit', apply_filters('headway_memory_limit', WP_MAX_MEMORY_LIMIT));
		
		//Load libraries and content
		Headway::load('visual-editor/display', 'VisualEditorDisplay');
		Headway::load('visual-editor/preview', 'VisualEditorPreview');
		
		Headway::load('api/api-box');

		//Boxes
		require_once 'boxes/live-css.php';
		require_once 'boxes/grid-wizard.php';
		
		//Panels
		if ( current_theme_supports('headway-grid') ) {
			require_once 'panels/grid/setup.php';
			require_once 'panels/manage/spacing.php';
		}
	
		if ( current_theme_supports('headway-design-editor') ) {
			require_once 'panels/design/panel-editor.php';
			require_once 'panels/design/panel-default-elements.php';
		}
		
		require_once 'panels/manage/content.php';
		
		//Put in action so we can run top level functions
		do_action('headway_visual_editor_init');
				
		//Visual Editor AJAX		
		add_action('wp_ajax_headway_visual_editor', array(__CLASS__, 'ajax'));
		
		if ( HeadwayOption::get('debug-mode') )
			add_action('wp_ajax_nopriv_headway_visual_editor', array(__CLASS__, 'ajax'));
				
	}
	
	
	public static function ajax() {
				
		Headway::load('visual-editor/visual-editor-ajax');
		
		//Authenticate nonce
		check_ajax_referer('headway-visual-editor-ajax', 'security');
		
		$method = headway_post('method');

		//Check for a non-secure (something that doesn't save data) AJAX request first (let debug mode authentication pass through)
		if ( method_exists('HeadwayVisualEditorAJAX', 'method_' . $method) && HeadwayCapabilities::can_user_visually_edit() )
			call_user_func(array('HeadwayVisualEditorAJAX', 'method_' . $method));
						
		//Check for a secure (something that saves data) AJAX request and require genuine authentication
		elseif ( method_exists('HeadwayVisualEditorAJAX', 'secure_method_' . $method) && HeadwayCapabilities::can_user_visually_edit(true) )
			call_user_func(array('HeadwayVisualEditorAJAX', 'secure_method_' . $method));
			
		die();
						
	}

	
	public static function display() {
		
		self::check_if_ie();
		
		HeadwayVisualEditorDisplay::display();
		
	}


	public static function check_if_ie() {
		
		if ( !headway_is_ie() )
			return false;
			
		$message = '
			<span style="text-align: center;font-size: 26px;width: 100%;display: block;margin-bottom: 20px;">Error</span>

			Unfortunately, the Headway Visual Editor does not work with Internet Explorer due to its lack of modern features.<br /><br />

			Please upgrade to a modern browser such as <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a> or <a href="http://firefox.com" target="_blank">Mozilla Firefox</a>.<br /><br />

			If this message persists after upgrading to a modern browser, please visit <a href="http://support.headwaythemes.com" target="_blank">Headway Support</a>.
		';

		return wp_die($message);
		
	}

	
	public static function get_modes() {
				
		return apply_filters('headway_visual_editor_get_modes', self::$modes);
		
	}	
		
	
	public static function get_current_mode() {
		
		$mode = headway_get('visual-editor-mode');		
				
		if ( $mode ) {
			
			if ( array_search(strtolower($mode), array_map('strtolower', array_keys(self::$modes))) ) {
				
				return strtolower($mode);
				
			} 
		
		}
			
		return strtolower(self::$default_mode);
	
	}	
		
	
	public static function is_mode($mode) {
				
		if ( self::get_current_mode() === strtolower($mode) )
			return true;
			
		if ( !headway_get('visual-editor-mode') && strtolower($mode) === strtolower(self::$default_mode) )
			return true;
			
		return false;
		
	}
	
	
}