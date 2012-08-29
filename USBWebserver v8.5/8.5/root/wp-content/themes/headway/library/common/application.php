<?php
/**
 * All of the global functions to be used everywhere in Headway.
 *
 * @package Headway
 * @author Clay Griffiths
 **/

class Headway {
	
	
	public static $loaded_classes = array();
	
	
	/**
	 * Let's get Headway on the road!  We'll define constants here, run the setup function and do a few other fun things.
	 * 
	 * @return void
	 **/
	public static function init() {
				
		/* Define simple constants */
		define('THEME_FRAMEWORK', 'headway');
		define('HEADWAY_VERSION', '3.2.5');

		/* Define directories */
		define('HEADWAY_DIR', headway_change_to_unix_path(TEMPLATEPATH));
		define('HEADWAY_LIBRARY_DIR', headway_change_to_unix_path(HEADWAY_DIR . '/library'));

		/* Site URLs */
		define('HEADWAY_SITE_URL', 'http://headwaythemes.com/');
		define('HEADWAY_DASHBOARD_URL', HEADWAY_SITE_URL . 'dashboard');
		define('HEADWAY_EXTEND_URL', HEADWAY_SITE_URL . 'extend');

		/* Mothership URLs */
		define('HEADWAY_LICENSE_VALIDATION_URL', add_query_arg(array('surge-trigger' => 'license_validation'), HEADWAY_SITE_URL));
		define('HEADWAY_UPDATER_URL', add_query_arg(array('surge-trigger' => 'update_check'), HEADWAY_SITE_URL));
		define('HEADWAY_EXTEND_DATA_URL', add_query_arg(array('surge-trigger' => 'extend_data'), HEADWAY_SITE_URL));

		/* Handle child themes */
		if ( get_template_directory_uri() !== get_stylesheet_directory_uri() )
			define('HEADWAY_CHILD_THEME_ACTIVE', true);			
	 	else
			define('HEADWAY_CHILD_THEME_ACTIVE', false);

		/* Handle uploads directory and cache */
		$uploads = wp_upload_dir();
		
		define('HEADWAY_UPLOADS_DIR', headway_change_to_unix_path($uploads['basedir'] . '/headway'));		
		define('HEADWAY_CACHE_DIR', headway_change_to_unix_path(HEADWAY_UPLOADS_DIR . '/cache'));

		/* Make directories if they don't exist */
		if ( !is_dir(HEADWAY_UPLOADS_DIR) )
			wp_mkdir_p(HEADWAY_UPLOADS_DIR);
			
		if ( !is_dir(HEADWAY_CACHE_DIR) )
			wp_mkdir_p(HEADWAY_CACHE_DIR);
		
		/* Load locale */
		load_theme_textdomain('headway', headway_change_to_unix_path(HEADWAY_LIBRARY_DIR . '/languages'));
			
		/* Add support for WordPress features */
		add_action('after_setup_theme', array(__CLASS__, 'add_theme_support'), 1);
				
		/* Setup */
		add_action('after_setup_theme', array(__CLASS__, 'child_theme_setup'), 2);
		add_action('after_setup_theme', array(__CLASS__, 'load_dependencies'), 3);
		add_action('after_setup_theme', array(__CLASS__, 'maybe_db_upgrade'));
		add_action('after_setup_theme', array(__CLASS__, 'initiate_updater'));

		/* Gzip */
		add_action('wp', 'headway_gzip');
								
	}
	
	
	/**
	 * Loads all of the required core classes and initiates them.
	 * 
	 * Dependency array setup: class (string) => init (bool)
	 **/
	public static function load_dependencies() {
						
		//Load route right away so we can optimize dependency loading below
		Headway::load(array('common/route' => true));		
						
		//Core loading set
		$dependencies = array(
			'defaults/default-design-settings',

			'data/data-options' => 'Option',
			'data/data-layout-options' => 'LayoutOption',
			'data/data-blocks',
			
		  	'common/layout',
			'common/capabilities' => true,
			'common/grid' => true,
			'common/responsive-grid' => true,
			'common/seo' => true,
			'common/social-optimization' => true,
			'common/feed' => true,
			'common/fonts',
			'common/compiler',
						
			'admin/admin-bar' => true,		
			
			'api/api-panel',
			'api/api-updater',
				
			'blocks' => true,			
			'elements' => true,
						
			'display' => true,
			
			'widgets' => true
		);
		
		//Child theme API
		if ( HEADWAY_CHILD_THEME_ACTIVE === true )
			$dependencies['api/api-child-theme'] = 'ChildThemeAPI';
		
		//Visual editor classes
		if ( HeadwayRoute::is_visual_editor() || (defined('DOING_AJAX') && DOING_AJAX) )
			$dependencies['visual-editor'] = true;

		//Admin classes
		if ( is_admin() )
			$dependencies['admin'] = true;
			
		//Load stuff now
		Headway::load(apply_filters('headway_dependencies', $dependencies));
		
		do_action('headway_setup');

	}
	
	
	/**
	 * Tell WordPress that Headway supports its features.
	 **/
	public static function add_theme_support() {
				
		/* Headway Functionality */
		add_theme_support('headway-grid');
		add_theme_support('headway-responsive-grid');
		add_theme_support('headway-design-editor');
		
		/* Headway CSS */
		add_theme_support('headway-reset-css');
		add_theme_support('headway-live-css');
		add_theme_support('headway-block-basics-css');
		add_theme_support('headway-dynamic-block-css');
		add_theme_support('headway-content-styling-css');
				
		/* WordPress Functionality */
		add_theme_support('post-thumbnails');		
		add_theme_support('menus');
		add_theme_support('widgets');
		add_theme_support('editor-style');
		add_theme_support('automatic-feed-links');
		
		/* Loop Standard by PluginBuddy */
		require_once HEADWAY_LIBRARY_DIR . '/resources/dynamic-loop.php';
		add_theme_support('loop-standard');
				
	}
	
	
	/**
	 * @todo Document
	 **/
	public static function child_theme_setup() {
		
		if ( !HEADWAY_CHILD_THEME_ACTIVE )
			return false;
			
		do_action('headway_setup_child_theme');
		
	}
	
	
	/**
	 * This will process upgrades from one version to another.
	 **/
	public static function maybe_db_upgrade() {
		
		$headway_settings = get_option('headway', array('version' => 0));
		$db_version = $headway_settings['version'];
		
		/* If the version in the database is already up to date, then there are no upgrade functions to be ran. */
		if ( version_compare($db_version, HEADWAY_VERSION, '>=') )
			return false;
			
		Headway::load('common/maintenance');
		
		return HeadwayMaintenance::db_upgrade($db_version);
		
	}
	

	/**
	 * Initiate the HeadwayUpdaterAPI class for Headway itself.
	 **/
	public static function initiate_updater() {

		global $headway_updater;

		$headway_updater = new HeadwayUpdaterAPI(array(
			'slug' => 'headway-base',
			'path' => get_option('template'),
			'name' => 'Headway',
			'type' => 'theme',
			'current_version' => HEADWAY_VERSION,
			'notify_only' => false
		));

	}

	
	/**
	 * Here's our function to load classes and files when needed from the library.
	 **/
	public static function load($classes, $init = false) {
		
		//Build in support to either use array or a string
		if ( !is_array($classes) ) {
			$load[$classes] = $init;
		} else {
			$load = $classes;
		}
		
		$classes_to_init = array();
		
		//Remove already loaded classes from the array
		foreach ( Headway::$loaded_classes as $class ) {
			unset($load[$class]);
		}
				
		foreach ( $load as $file => $init ) {
			
			//Check if only value is used instead of both key and value pair
			if ( is_numeric($file) ){
				$file = $init;
				$init = false;
			} 
						
			//Handle anything with .php or a full path
			if ( strpos($file, '.php') !== false ) 
				require_once HEADWAY_LIBRARY_DIR . '/' . $file;
				
			//Handle main-helpers such as admin, data, etc.
			elseif ( strpos($file, '/') === false )
				require_once HEADWAY_LIBRARY_DIR . '/' . $file . '/' . $file . '.php';
				
			//Handle anything and automatically insert .php if need be
			elseif ( strpos($file, '/') !== false )
				require_once HEADWAY_LIBRARY_DIR . '/' . $file . '.php';
				
			//Add the class to the main variable so we know that it has been loaded
			Headway::$loaded_classes[] = $file;
			
			//Set up init, if init is true, just figure out the class name from filename.  If argument is string, use that.
			if ( $init === true ) {
				
				$class = array_reverse(explode('/', str_replace('.php', '', $file)));
				
				//Check for hyphens/underscores and CamelCase it
				$class = str_replace(' ', '', ucwords(str_replace('-', ' ', str_replace('_', ' ', $class[0]))));
				
				$classes_to_init[] = $class;
				
			} else if ( is_string($init) ) {
				
				$classes_to_init[] = $init;
				
			}
			
		}	
		
		//Init everything after dependencies have been loaded
		foreach($classes_to_init as $class){
			
			if ( method_exists('Headway' . $class, 'init') ) {
				
				call_user_func(array('Headway' . $class, 'init'));
				
			} else {
				
				trigger_error('Headway' . $class . '::init is not a valid method', E_USER_WARNING);
				
			}
			
		}
		
	}


	public static function get() {
		_deprecated_function(__FUNCTION__, '3.1.3', 'headway_get()');
		$args = func_get_args();
		return call_user_func_array('headway_get', $args);
	}


	public static function post() {
		_deprecated_function(__FUNCTION__, '3.1.3', 'headway_post()');
		$args = func_get_args();
		return call_user_func_array('headway_post', $args);
	}

	
}