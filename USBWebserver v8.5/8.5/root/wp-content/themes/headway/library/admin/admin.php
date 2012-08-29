<?php
class HeadwayAdmin {
	
	
	public static function init() {
		
		self::setup_hooks();
		
		Headway::load(array(
			'api/api-admin-meta-box',
			'admin/admin-write' => true,
			'admin/admin-pages',
			'admin/api-admin-inputs'
		));
		
	}
	
	
	public static function setup_hooks() {

		/* Actions */
		add_action('admin_init', array(__CLASS__, 'activation'), 1);
		add_action('admin_init', array(__CLASS__, 'enqueue'));
		add_action('admin_init', array(__CLASS__, 'visual_editor_redirect'), 12);
		
		add_action('init', array(__CLASS__, 'form_action_save'), 12); // Init runs before admin_menu; admin_menu runs before admin_init
		add_action('init', array(__CLASS__, 'form_action_reset'), 12);
		add_action('init', array(__CLASS__, 'form_action_maintenance'), 12);
		
		add_action('admin_menu', array(__CLASS__, 'add_menus'));
				
		add_action('headway_admin_save_message', array(__CLASS__, 'save_message'));
		
		add_action('admin_notices', array(__CLASS__, 'notice_no_widgets_or_menus'));
		add_action('admin_notices', array(__CLASS__, 'notice_no_license'));

		add_filter('plugins_api', array(__CLASS__, 'get_addon_info'), 10, 3);
		add_filter('themes_api', array(__CLASS__, 'get_addon_info'), 10, 3);

		add_filter('page_row_actions', array(__CLASS__, 'row_action_visual_editor'), 10, 2);
		add_filter('post_row_actions', array(__CLASS__, 'row_action_visual_editor'), 10, 2);
		add_filter('tag_row_actions', array(__CLASS__, 'row_action_visual_editor'), 10, 2);


	}
	
	
	public static function form_action_save() {
		
		//Form action for all Headway configuration panels.  Not in function/hook so it can load before everything else.
		if ( !headway_post('headway-submit', false))
			return false;
			
		if ( !wp_verify_nonce(headway_post('headway-admin-nonce', false), 'headway-admin-nonce') ) {
			
			global $headway_admin_save_message;
			$headway_admin_save_message = 'Security nonce did not match.';
			
			return false;
			
		}

		foreach ( headway_post('headway-admin-input', array()) as $option => $value ) {
			
			HeadwayOption::set($option, $value);
			
		}
		
		global $headway_admin_save_message;
		$headway_admin_save_message = 'Settings saved.';
		
		return true;
		
	}
	
	
	public static function form_action_reset() {
		
		//Form action for all Headway configuration panels.  Not in function/hook so it can load before everything else.
		if ( !headway_post('reset-headway', false) )
			return false;
			
		//Verify the nonce so other sites can't maliciously reset a Headway installation.
		if ( !wp_verify_nonce(headway_post('headway-reset-nonce', false), 'headway-reset-nonce') ) {
			
			$GLOBALS['headway_admin_save_message'] = 'Security nonce did not match.';
			
			return false;
			
		}
		
		//Fetch all options in wp_options and remove the Headway-specific options
		foreach ( wp_load_alloptions() as $option => $option_value ) {
						
			//This if statement is incredibly important and must not be tampered with and needs to be triple-checked if changed.
			if ( strpos($option, 'headway_option_') === 0 || strpos($option, 'headway_layout_options_') === 0 || $option === 'headway' ) {
				delete_option($option);
			}	
			
		}
				

		$GLOBALS['headway_admin_save_message'] = 'Headway was successfully reset.';
		
		//This will hide the reset box if set to true.
		$GLOBALS['headway_reset_success'] = true;
		
		return true;
		
	}
	
	
	public static function form_action_maintenance() {
		
		//Form action for all Headway configuration panels.  Not in function/hook so it can load before everything else.
		if ( !headway_post('headway-maintenance-nonce', false) )
			return false;
			
		//Check the nonce for security
		if ( !wp_verify_nonce(headway_post('headway-maintenance-nonce', false), 'headway-maintenance-nonce') ) {
			
			$GLOBALS['headway_admin_save_message'] = 'Security nonce did not match.';
			return false;
			
		}
		
		//Load the maintenance class
		Headway::load('common/maintenance');
	
		//Handle block repair
		if ( headway_post('repair-blocks', false) ) {
			
			HeadwayMaintenance::repair_blocks();
			
			$GLOBALS['headway_admin_save_message'] = 'Blocks have successfully been repaired.';
			
		}
		
		return true;
		
	}
	
	
	public static function activation() {
		
		if ( !is_admin() || !headway_get('activated') )
			return false;
		
		global $pagenow;
		
		if ( $pagenow !== 'themes.php' )
			return false;
			
		//Track the version number in the DB
		$headway_settings = get_option('headway', array('version' => 0));
		
		if ( version_compare($headway_settings['version'], HEADWAY_VERSION, '<=') )
			$headway_settings['version'] = HEADWAY_VERSION;
			
		update_option('headway', $headway_settings);
				
		//Since they may be upgrading and files may change, let's clear the cache
		HeadwayCompiler::flush_cache();

		self::activation_redirect();
		
	}
	
	
	public static function activation_redirect() {
		
		do_action('headway_activation_redirect');
		
		//If a child theme has been activated rather than Headway, then don't redirect.
		//Let the child theme developer redirect if they want by using the hook above.
		if ( HEADWAY_CHILD_THEME_ACTIVE === true )
			return false;
			
		$parent_menu = self::parent_menu();
			
		//If header were sent, then don't do the redirect
		if ( headers_sent() )
			return false;
			
		//We're all good, redirect now
		wp_safe_redirect(admin_url('admin.php?page=headway-' . $parent_menu['id']));
		die();
		
	}
	
	
	public static function visual_editor_redirect() {
		
		if ( isset($_GET['page']) && strpos($_GET['page'], 'headway-visual-editor') !== false && !headers_sent() )
			wp_safe_redirect(home_url() . '/?visual-editor=true');
		
	}
	
	
	public static function add_admin_separator($position){
				
		global $menu;
				
		$menu[$position] = array('', 'read', 'separator-headway', '', 'wp-menu-separator headway-separator');
	
		ksort($menu);
		
	}
	
	
	public static function add_admin_submenu($name, $id, $callback) {
		
		$parent_menu = self::parent_menu();

		return add_submenu_page('headway-' . $parent_menu['id'], $name, $name, 'manage_options', $id, $callback);
		
	}
	
	
	public static function add_menus(){
		
		//If the hide menus constant is set to true, don't hide the menus!
		if (defined('HEADWAY_HIDE_MENUS') && HEADWAY_HIDE_MENUS === true)
		 	return false;
		
		//If user cannot access the admin panels, then don't bother running these functions
		if ( !HeadwayCapabilities::can_user_visually_edit() ) 
			return false;

		$menu_name = ( HeadwayOption::get('hide-menu-version-number') == true ) ? 'Headway' : 'Headway ' . HEADWAY_VERSION;
		$icon_url = headway_url() . '/library/admin/images/headway-16.png';
		
		$parent_menu = self::parent_menu();

		self::add_admin_separator(48);		
				
		add_menu_page($parent_menu['name'], $menu_name, 'manage_options', 'headway-' . $parent_menu['id'], $parent_menu['callback'], $icon_url, 49); 
				
			switch ( $parent_menu['id'] ) {
				
				case 'getting-started':
					self::add_admin_submenu('Getting Started', 'headway-getting-started', array('HeadwayAdminPages', 'getting_started'));
					self::add_admin_submenu('Visual Editor', 'headway-visual-editor', array('HeadwayAdminPages', 'visual_editor'));
					self::add_admin_submenu('Extend', 'headway-extend', array('HeadwayAdminPages', 'extend'));
					self::add_admin_submenu('Options', 'headway-options', array('HeadwayAdminPages', 'options'));
					self::add_admin_submenu('Tools', 'headway-tools', array('HeadwayAdminPages', 'tools'));
				break;
				
				case 'visual-editor':
					self::add_admin_submenu('Visual Editor', 'headway-visual-editor', array('HeadwayAdminPages', 'visual_editor'));
					self::add_admin_submenu('Extend', 'headway-extend', array('HeadwayAdminPages', 'extend'));
					self::add_admin_submenu('Options', 'headway-options', array('HeadwayAdminPages', 'options'));
					self::add_admin_submenu('Tools', 'headway-tools', array('HeadwayAdminPages', 'tools'));
				break;
				
				case 'options':
					self::add_admin_submenu('Options', 'headway-options', array('HeadwayAdminPages', 'options'));
					self::add_admin_submenu('Visual Editor', 'headway-visual-editor', array('HeadwayAdminPages', 'visual_editor'));
					self::add_admin_submenu('Extend', 'headway-extend', array('HeadwayAdminPages', 'extend'));
					self::add_admin_submenu('Tools', 'headway-tools', array('HeadwayAdminPages', 'tools'));
				break;
				
			}
		
	}
	
	
	public static function parent_menu() {
		
		$menu_setup = HeadwayOption::get('menu-setup', false, 'getting-started');
		
		/* Figure out the primary page */
		switch ( $menu_setup ) {
			
			case 'getting-started':
				$parent_menu = array(
					'id' => 'getting-started',
					'name' => 'Getting Started',
					'callback' => array('HeadwayAdminPages', 'getting_started')
				);
			break;
			
			case 'visual-editor':
				$parent_menu = array(
					'id' => 'visual-editor',
					'name' => 'Visual Editor',
					'callback' => array('HeadwayAdminPages', 'visual_editor')
				);
			break;
			
			case 'options':
				$parent_menu = array(
					'id' => 'options',
					'name' => 'Options',
					'callback' => array('HeadwayAdminPages', 'options')
				);
			break;
			
		}
		
		return $parent_menu;
		
	}
	
	
	public static function enqueue() {
		
		global $pagenow;

		/* Global */
		wp_enqueue_style('headway_admin_global', headway_url() . '/library/admin/css/admin-headway-global.css');
				
		/* General Headway admin CSS/JS */
		if ( strpos(headway_get('page'), 'headway') !== false ) {
			
			wp_enqueue_script('headway_jquery_scrollto', headway_url() . '/library/media/js/jquery.scrollto.js', array('jquery'));
			wp_enqueue_script('headway_jquery_tabby', headway_url() . '/library/media/js/jquery.tabby.js', array('jquery'));
			wp_enqueue_script('headway_jquery_qtip', headway_url() . '/library/media/js/jquery.qtip.js', array('jquery'));
			wp_enqueue_script('headway_admin_js', headway_url() . '/library/admin/js/admin-headway.js', array('jquery', 'headway_jquery_qtip'));
			
			wp_enqueue_style('headway_admin', headway_url() . '/library/admin/css/admin-headway.css');
			wp_enqueue_style('headway_alerts', headway_url() . '/library/media/css/alerts.css');
			
		}

		/* Extend */
		if ( headway_get('page') == 'headway-extend' )
			wp_enqueue_script('headway_jquery_masonry', headway_url() . '/library/media/js/jquery.masonry.js', array('jquery'));

		/* Meta Boxes */
		if ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) {
			
			wp_enqueue_style('headway_admin_write', headway_url() . '/library/admin/css/admin-write.css');
			wp_enqueue_style('headway_alerts', headway_url() . '/library/media/css/alerts.css');
			wp_enqueue_script('headway_admin_write', headway_url() . '/library/admin/js/admin-write.js', array('jquery'));
			
		}
		
		/* Auto Updater */
		if ( $pagenow === 'update-core.php' ) {
			
			wp_enqueue_script('headway_admin', headway_url() . '/library/admin/js/admin-headway.js', array('jquery'));
			wp_enqueue_style('headway_admin', headway_url() . '/library/admin/css/admin-headway.css');
			wp_enqueue_style('headway_alerts', headway_url() . '/library/media/css/alerts.css');
			
			wp_localize_script('headway_admin', 'HeadwayUpdateInfo', array( 
				'backupReminder' => sprintf(__('<div class="headway-update-backup-reminder alert-red alert"><h3>Remember!</h3><p>Please backup before updating.  Headway Themes recommends <a href="%s" target="_blank"><strong>BackupBuddy</strong></a> for all backup and migration needs.</div>', 'headway'), 'http://bit.ly/do5AVC')
			));
			
		}
				
	}
	
		
	public static function save_message() {
		
		global $headway_admin_save_message;
		
		if ( !isset($headway_admin_save_message) || $headway_admin_save_message == false ) 
			return false;
		
		echo '<div class="alert alert-green save-message"><p>' . $headway_admin_save_message . '</p></div>';
		
	}
	
	
	public static function notice_no_widgets_or_menus() {
		
		global $pagenow;
		
		if ( $pagenow != 'widgets.php' && $pagenow != 'nav-menus.php' )
			return false;
			
		$grid_mode_url = add_query_arg(array('visual-editor' => 'true', 'visual-editor-mode' => 'grid'), home_url());
		
		//Show the widgets message if no widget blocks exist.
		if ( $pagenow == 'widgets.php' ) {
						
			$widget_area_blocks = HeadwayBlocksData::get_blocks_by_type('widget-area');
						
			if ( !empty($widget_area_blocks) )
				return;
				
			if ( !current_theme_supports('headway-grid') )
				return;
			
			echo '<div class="updated" style="margin-top: 15px;">
			       <p>Headway has detected that you have no Widget Area blocks.  If you wish to use the WordPress widgets system with Headway, please add a Widget Area block in the <a href="' . $grid_mode_url . '" target="_blank">Visual Editor: Grid</a>.</p>
			
					<style type="text/css">
						div.error.below-h2 { display: none; }
						div.error.below-h2 + p { display: none; }
					</style>
			    </div>';

		}
				
		//Show the navigation menus message if no navigation blocks exist.
		if ( $pagenow == 'nav-menus.php' ) {

			$navigation_blocks = HeadwayBlocksData::get_blocks_by_type('navigation');

			if ( !empty($navigation_blocks) )
				return;
				
			if ( !current_theme_supports('headway-grid') )
				return;

			echo '<div class="updated">
			       <p>Headway has detected that you have no Navigation blocks.  If you wish to use the WordPress menus system with Headway, please add a Navigation block in the <a href="' . $grid_mode_url . '" target="_blank">Visual Editor: Grid</a>.</p>
			    </div>';

		}
		
	}


	public static function notice_no_license() {

		global $pagenow;

		if ( $pagenow != 'update-core.php' )
			return;

		if ( !headway_get_license_key() ) {

			echo '
				<div id="update-nag">
			       <p><strong>Important!</strong> In order to receive Headway updates you must enter your license key.  <a href="' . admin_url('admin.php?page=headway-options') . '">Enter License Key &raquo;</a></p>
			    </div>
			';

		} elseif ( $license_validation_request = headway_validate_license_key() ) {

			if ( $error = headway_get('error', $license_validation_request) ) {

				echo '
					<div id="update-nag">
				       <p><strong>Headway License Error:</strong> ' . $error . '  Please note, you must have a valid license key to receive Headway updates.  <a href="' . admin_url('admin.php?page=headway-options') . '">Update License Key &raquo;</a></p>
				    </div>
				';

			}

		}

	}
	
	
	public static function show_header($title = false, $icon_id = 'icon-headway') {
		
		echo '<div class="wrap headway-page">';
		echo '<div class="icon32" id="' . $icon_id . '"><br /></div>';

		if ( $title ) 
			echo '<h2>' . $title . '</h2>';
			
	}


	public static function show_footer() {

		echo '</div><!-- #wrapper -->';

	}


	public static function get_addon_info($api, $action, $args) {

	    if ( !$api && headway_get('headway') ) {

	    	/* Output */
	    	$addon_info_request = wp_remote_post(add_query_arg(array('action' => 'addon-info'), HEADWAY_EXTEND_DATA_URL), array(
	    		'body' => array(
	    		    'slug' => headway_get('slug'),
	    		    'license_key' => headway_get_license_key()
	    		)
	    	));

	    	$addon_info = wp_remote_retrieve_body($addon_info_request);

			if ( !is_serialized($addon_info) || $addon_info_request['response']['code'] != 200 )
	    		return false;

	    	$addon_info = maybe_unserialize($addon_info);

	        $api = new stdClass();
	        $api->name = $addon_info['name'];
	        $api->version = $addon_info['version'];
	        $api->download_link = str_replace('{KEY}', headway_get_license_key(), $addon_info['download_url']);
	        
	    }

	    return $api;

	}


	public static function row_action_visual_editor($actions, $item) {

		if ( !HeadwayCapabilities::can_user_visually_edit() )
			return $actions;

		/* Post */
		if ( isset($item->post_status) ) {

			if ( $item->post_status != 'publish' )
				return $actions;

			$post_type = get_post_type_object($item->post_type);

			if ( !$post_type->public )
				return $actions;

			$layout_id = 'single-' . $item->post_type . '-' . $item->ID;

		/* Category */
		} elseif ( isset($item->term_id) && $item->taxonomy == 'category' ) {

			$layout_id = 'archive-category-' . $item->term_id;

		/* Post Tag */
		} elseif ( isset($item->term_id) && $item->taxonomy == 'post_tag' ) {

			$layout_id = 'archive-post_tag-' . $item->term_id;

		/* Taxonomy */
		} elseif ( isset($item->term_id) ) {

			$layout_id = 'archive-taxonomy-' . $item->taxonomy . '-' . $item->term_id;

		}

		$visual_editor_url = home_url('/?visual-editor=true#layout=' . $layout_id);

		$actions['hw-visual-editor'] = '<a href="' . $visual_editor_url . '" title="Open in Headway Visual Editor" rel="permalink" target="_blank">Open in Visual Editor</a>';

		return $actions;

	}

	
}