<?php
class HeadwayHead {
	
	/** 
	 * Set up hooks for <head>
	 **/
	public static function init() {
		
		if ( !HeadwayRoute::is_display() )
			return false;
		
		add_filter('wp_title', array('HeadwaySEO', 'output_title'), 9);
		
		//Remove actions
		remove_action('wp_head', 'wp_print_styles', 8);
		remove_action('wp_head', 'wp_print_head_scripts', 9);
		remove_action('wp_head', 'rel_canonical');
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);
				
		//Set Up Actions
		add_action('wp_head', array(__CLASS__, 'print_title'), 1);
		
		add_action('wp', array(__CLASS__, 'register_files'));
		add_action('wp', array(__CLASS__, 'enqueue_scripts'));
		
		add_action('wp_head', array('HeadwaySEO', 'output_meta'), 2);
		add_action('wp_head', array(__CLASS__, 'print_stylesheets'), 7);
		add_action('wp_head', array(__CLASS__, 'print_scripts'), 8);
		add_action('wp_head', array(__CLASS__, 'extras'), 9);
		
		add_action('headway_stylesheets', 'wp_print_styles');
		add_action('headway_stylesheets', array(__CLASS__, 'child_theme_stylesheet'), 11);
		add_action('headway_stylesheets', array(__CLASS__, 'visual_editor_live_css'), 12);
		
		add_action('headway_scripts', 'wp_print_head_scripts');
		add_action('headway_scripts', array(__CLASS__, 'add_standards_compliance_js'));
		add_action('headway_scripts', array(__CLASS__, 'header_scripts'));
		
		add_action('headway_seo_meta', 'rel_canonical');
		
		add_action('headway_head_extras', 'feed_links');
		add_action('headway_head_extras', 'feed_links_extra');
		
		add_action('headway_body_close', array(__CLASS__, 'footer_scripts'), 15);
		
		add_action('wp_head', array(__CLASS__, 'favicon'), 9);
		
		add_filter('style_loader_src', array(__CLASS__, 'remove_dependency_query_vars'));
		add_filter('script_loader_src', array(__CLASS__, 'remove_dependency_query_vars'));
		
	}
	
	
	public static function print_title() {
		
		echo "\n<!-- Title -->\n<title>" . wp_title(false, false) . '</title>';
		
	}
	
	
	/**
	 * All general CSS and JS used across the site will be registered and/or enqueued here.
	 * 
	 * @return void
	 **/
	public static function register_files() {
		
		$general_css_fragments = array();
		$general_css_dependencies = array();
		
		if ( current_theme_supports('headway-reset-css') )
			$general_css_fragments[] = HEADWAY_LIBRARY_DIR . '/media/css/reset.css';
		
		if ( current_theme_supports('headway-grid') )
			$general_css_fragments[] = HEADWAY_LIBRARY_DIR . '/media/css/grid.css';
					
		if ( current_theme_supports('headway-block-basics-css') )
			$general_css_fragments[] = HEADWAY_LIBRARY_DIR . '/media/css/block-basics.css';
			
		if ( current_theme_supports('headway-content-styling-css') ) {
			
			$general_css_fragments[] = HEADWAY_LIBRARY_DIR . '/media/css/content-styling.css';
			$general_css_fragments[] = HEADWAY_LIBRARY_DIR . '/media/css/alerts.css';
			
		}
				
		//If the grid is supported, then include the grid and wrapper CSS 
		if ( current_theme_supports('headway-grid') ) {
			
			$general_css_fragments[] = array('HeadwayDynamicStyle', 'grid');
			$general_css_fragments[] = array('HeadwayDynamicStyle', 'block_heights');
			$general_css_fragments[] = array('HeadwayDynamicStyle', 'wrapper');
						
		}
		
		//If no child theme is active, use the design editor.
		if ( current_theme_supports('headway-design-editor') )
			$general_css_fragments[] = array('HeadwayDynamicStyle', 'design_editor');
		
		//Output the CSS needed for blocks such as navigation block or any block that has per-block CSS
		if ( current_theme_supports('headway-dynamic-block-css') )
			$general_css_fragments[] = array('HeadwayBlocks', 'output_block_dynamic_css');
		
		//Live CSS
		if ( current_theme_supports('headway-live-css') && HeadwayOption::get('live-css') )
			$general_css_fragments[] = array('HeadwayDynamicStyle', 'live_css');
			
		//Dynamic style dependency
		if ( current_theme_supports('headway-grid') || current_theme_supports('headway-design-editor') || current_theme_supports('headway-live-css') )
			$general_css_dependencies[] = HEADWAY_LIBRARY_DIR . '/media/dynamic/style.php';
			
		//Allow filters to be applied to the general CSS fragments/dependencies before the count is made
		$general_css_fragments = apply_filters('headway_general_css', $general_css_fragments);
		$general_css_dependencies = array_unique(apply_filters('headway_general_css_dependencies', $general_css_dependencies));
			
		
		//Handle visual editor CSS
		if ( HeadwayRoute::is_visual_editor_iframe() ) {
			
			HeadwayCompiler::register_file(array(
				'name' => 'iframe',
				'format' => 'less',
				'fragments' => array(
					HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-mixins.less',
					HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-iframe.less',
					HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-tooltips.less'
				)
			));
			
		}
				
		//Enqueue general CSS
		if ( count($general_css_fragments) > 0 ) {
		
			//Register the general file
			HeadwayCompiler::register_file(array(
				'name' => 'general',
				'format' => 'css',
				'fragments' => $general_css_fragments,
				'dependencies' => $general_css_dependencies
			));
		
		}
		
		//Add the responsive grid CSS and JS
		if ( HeadwayResponsiveGrid::is_active() ) {
			
			//CSS
			HeadwayCompiler::register_file(array(
				'name' => 'responsive-grid',
				'format' => 'css',
				'fragments' => array(
					array('HeadwayResponsiveGridDynamicMedia', 'content')
				),
				'dependencies' => array(
					HEADWAY_LIBRARY_DIR . '/media/dynamic/responsive-grid.php'
				)
			));
			
			//JS
			if ( apply_filters('headway_responsive_fitvids', HeadwayOption::get('responsive-video-resizing', false, true)) ) {
				
				wp_enqueue_script('fitvids', headway_url() . '/library/media/js/jquery.fitvids.js', array('jquery'));
				
				HeadwayCompiler::register_file(array(
					'name' => 'responsive-grid-js',
					'format' => 'js',
					'fragments' => array(
						array('HeadwayResponsiveGridDynamicMedia', 'fitvids')
					),
					'dependencies' => array(
						HEADWAY_LIBRARY_DIR . '/media/dynamic/responsive-grid.php'
					)
				));
				
			}
			
		}
							
	}
	
	
	/**
	 * Add extra junk into <head>.
	 **/
	public static function extras() {
	?>

<!-- Extras -->
<link rel="alternate" type="application/rss+xml" href="<?php echo get_bloginfo('rss2_url'); ?>" title="<?php echo get_bloginfo('name')?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url') ?>" />
	<?php
		do_action('headway_head_extras');
	}
	

	/**
	 * Enqueues the Headway JS for leafs.
	 * 
	 * @uses wp_enqueue_script()
	 **/
	public static function enqueue_scripts() {

		if (
			is_singular() 
			&& comments_open(get_the_id())
			&& !(get_post_type() == 'page' && !HeadwayOption::get('allow-page-comments'))
		) 
			wp_enqueue_script('comment-reply');
		
	}
	

	public static function print_scripts() {
		echo "\n<!-- Scripts -->\n";

		do_action('headway_scripts');
	}


	public static function add_standards_compliance_js() {

		$standards_compliance_js = apply_filters('headway_standards_compliance_js', '
<!--[if lt IE 9]>
<script src="' . headway_url() . '/library/media/js/ie9.js"></script>
<![endif]-->');
		
		echo $standards_compliance_js;

	}

	
	/**
	 * Adds all of the links for the Headway stylesheets.
	 **/
	public static function print_stylesheets() {
		
		echo "\n\n" . '<!-- Stylesheets -->' . "\n";

		do_action('headway_stylesheets');
		
		echo "\n";
		
	}

	
	public static function child_theme_stylesheet() {
		
		/* If no child theme is active, then we won't use the style.css file. */
		if ( HEADWAY_CHILD_THEME_ACTIVE === false )
			return false;
			
		echo '<link rel="stylesheet" type="text/css" media="all" href="' . get_stylesheet_uri() . '" />';
			
		
	}

	
	public static function visual_editor_live_css() {
		
		if ( HeadwayRoute::is_visual_editor_iframe() )
			echo '<style id="live-css-holder">' . HeadwayOption::get('live-css') . '</style>';
		
	}
	

	/**
	 * Adds the link to the favicon to the <head>.
	 **/
	public static function favicon() {

		if ( !$favicon_url = HeadwayOption::get('favicon') )
			return null;
			
		if ( is_ssl() )
			$favicon_url = str_replace('http:', 'https:', $favicon_url);
			
		echo "\n\n<!-- Favicon -->\n" . '<link rel="shortcut icon" type="image/ico" href="' . $favicon_url . '" />' . "\n\n\n";
			
	}


	/**
	 * Callback function to be used for displaying the header scripts.
	 * 
	 * @uses headway_parse_php()
	 **/
	public static function header_scripts() {
		
		echo "\n" . headway_parse_php(HeadwayOption::get('header-scripts')) . "\n";
		
	}


	/**
	 * Callback function to be used for displaying the footer scripts.
	 * 
	 * @uses headway_parse_php()
	 **/
	public static function footer_scripts() {
		echo "\n" . headway_parse_php(HeadwayOption::get('footer-scripts')) . "\n";
	}
	
	
	/**
	 * To promote caching on browsers, Headway can tell WordPress to not put in the query variables on the style and script URLs.
	 **/
	public static function remove_dependency_query_vars($query) {
		
		if ( !HeadwayOption::get('remove-dependency-query-vars', 'general', false) && !HeadwayRoute::is_visual_editor_iframe() )
			return $query;
		
		return remove_query_arg('ver', $query);
		
	}


}