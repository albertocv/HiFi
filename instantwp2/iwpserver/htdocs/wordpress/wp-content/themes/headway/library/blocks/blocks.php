<?php
class HeadwayBlocks {
	
	
	public static $block_actions = array(
		'init' => array(),
		'enqueue' => array(),
		'dynamic-js' => array(),
		'dynamic-css' => array()
	);
	
	public static $core_blocks = array(
		'core' => array(
			'header',
			'navigation',
			'breadcrumbs',
			'content',
			'widget-area',
			'footer',
			'media',
			'custom-code',
			'text'
		),
		
		'plugins' => array(
			'gravity-forms'
		)
	);
	
	
	public static function init() {
		
		Headway::load(array(
			'api/api-block'
		));
		
		self::load_core_blocks();
		
		add_action('init', array(__CLASS__, 'register_block_types'), 8);
		
		add_action('init', array(__CLASS__, 'process_registered_blocks'), 9);
		
		/* Handle block-specific actions */
		add_action('init', array(__CLASS__, 'setup_block_actions'), 10);
	
		add_action('init', array(__CLASS__, 'run_block_init_actions'), 11);
		add_action('wp', array(__CLASS__, 'run_block_enqueue_actions'));
		add_action('wp', array(__CLASS__, 'enqueue_block_dynamic_js_file'));
		/* End block-specific actions */
		
		add_action('headway_register_elements', array(__CLASS__, 'register_block_element_instances'), 11);
		
		add_action('headway_block_content_unknown', array(__CLASS__, 'unknown_block_content'));
		
	}
	
	
	public static function register_block_types() {

		global $headway_unregistered_block_types;

		foreach ( $headway_unregistered_block_types as $class => $block_type_url ) {

			if ( !class_exists($class) )
				return new WP_Error('block_class_does_not_exist', __('The block class being registered does not exist.', 'headway'), $class);

			$block = new $class();

			if ( $block_type_url )
				$block->block_type_url = untrailingslashit($block_type_url);

			$block->register();

			unset($block);

		}

		unset($headway_unregistered_block_types);

		return true;

	}
	
	
	public static function process_registered_blocks() {
		
		do_action('headway_register_blocks');
		
	}
	
	
	public static function load_core_blocks() {
		
		//Load blocks from core folder
		foreach ( apply_filters('headway_block_types_core', self::$core_blocks['core']) as $block )
			require_once HEADWAY_LIBRARY_DIR . '/blocks/core/' . $block . '/' . $block . '.php';
					
		//Load blocks from plugins folder
		foreach ( apply_filters('headway_block_types_plugins', self::$core_blocks['plugins']) as $block )
			require_once HEADWAY_LIBRARY_DIR . '/blocks/plugins/' . $block . '/' . $block . '.php';
			
	}
	
	
	public static function setup_block_actions() {
				
		$block_types = self::get_block_types();
		
		foreach ($block_types as $block_type => $block_type_options) {
			
			//Make sure that the block type has at least one of the following: init_action, enqueue_action, or dynamic_js
			if ( 
				!method_exists($block_type_options['class'], 'init_action') 
				&& !method_exists($block_type_options['class'], 'enqueue_action') 
				&& !(method_exists($block_type_options['class'], 'dynamic_js') || method_exists($block_type_options['class'], 'js_content'))
				&& !method_exists($block_type_options['class'], 'dynamic_css') 
			) 
				continue;
				
			$blocks = HeadwayBlocksData::get_blocks_by_type($block_type);
			
			/* If there are no blocks for this type, skip it */
			if ( !is_array($blocks) || count($blocks) === 0 )
				continue;
					
			/* Go through each type and add a flag if the method exists */			
			foreach ( $blocks as $block_id => $layout_id ) {

				/* Init */
					if ( method_exists($block_type_options['class'], 'init_action') ) {
					
						if ( !isset(self::$block_actions['init'][$block_type]) )
							self::$block_actions['init'][$block_type] = array();
					
						self::$block_actions['init'][$block_type][$block_id] = array(
							'class' => $block_type_options['class'],
							'block_id' => $block_id,
							'layout' => $layout_id
						);
					
					}
				/* End Init */
					
				/* Enqueue */
					if ( method_exists($block_type_options['class'], 'enqueue_action') ) {
					
						if ( !isset(self::$block_actions['enqueue'][$block_type]) )
							self::$block_actions['enqueue'][$block_type] = array();
						
						self::$block_actions['enqueue'][$block_type][$block_id] = array(
							'class' => $block_type_options['class'],
							'block_id' => $block_id,
							'layout' => $layout_id
						);
					
					}
				/* End Enqueue */
				
				/* Dynamic JS */	
					if ( method_exists($block_type_options['class'], 'dynamic_js') || method_exists($block_type_options['class'], 'js_content') ) {
					
						if ( !isset(self::$block_actions['dynamic-js'][$block_type]) )
							self::$block_actions['dynamic-js'][$block_type] = array();
						
						self::$block_actions['dynamic-js'][$block_type][$block_id] = array(
							'class' => $block_type_options['class'],
							'block_id' => $block_id,
							'layout' => $layout_id
						);
					
					}
				/* End JS Content */
				
				/* Dynamic CSS */	
					if ( method_exists($block_type_options['class'], 'dynamic_css') ) {
					
						if ( !isset(self::$block_actions['dynamic-css'][$block_type]) )
							self::$block_actions['dynamic-css'][$block_type] = array();
						
						self::$block_actions['dynamic-css'][$block_type][$block_id] = array(
							'class' => $block_type_options['class'],
							'block_id' => $block_id,
							'layout' => $layout_id
						);
					
					}
				/* End JS Content */
				
			}	
			
		} 
		
	}
	
	
	public static function run_block_init_actions() {
		
		foreach ( self::$block_actions['init'] as $block_type => $blocks ) {
			
			foreach ( $blocks as $block_id => $block_options ) {
				
				$block = HeadwayBlocksData::get_block($block_id);
				
				/* Do not run the init action for mirrored blocks. */
				if ( HeadwayBlocksData::is_block_mirrored($block) )
					continue;
				/* End mirrored block conditional. */
				
				call_user_func(array($block_options['class'], 'init_action'), $block['id']);
				
			}
			
		}
		
	}
	
	
	public static function run_block_enqueue_actions() {
		
		//Do not run these if it's the admin page or the visual editor is open
		if ( is_admin() || HeadwayRoute::is_visual_editor() )
			return false;
				
		$layout = HeadwayLayout::get_current_in_use();
		
		foreach ( self::$block_actions['enqueue'] as $block_type => $blocks ) {
			
			foreach ( $blocks as $block_id => $block_options ) {
				
				if ( $layout !== $block_options['layout'] )
					continue;
					
				$block = HeadwayBlocksData::get_block($block_id, true);
				
				call_user_func(array($block_options['class'], 'enqueue_action'), $block['id']);
				
			}
			
		}
		
	}


	public static function output_block_dynamic_js($layout = false) {
						
		$layout = !$layout ? headway_get('layout-in-use') : $layout;
		
		$data = '';
				
		foreach ( self::$block_actions['dynamic-js'] as $block_type => $blocks ) {
			
			foreach ( $blocks as $block_id => $block_options ) {
								
				if ( $layout !== $block_options['layout'] )
					continue;	
					
				$block = HeadwayBlocksData::get_block($block_id, true);
				
				if ( method_exists($block_options['class'], 'dynamic_js') )
					$data .= call_user_func(array($block_options['class'], 'dynamic_js'), $block['id']);
				
				elseif ( method_exists($block_options['class'], 'js_content') )
					$data .= call_user_func(array($block_options['class'], 'js_content'), $block['id']);
				
			}
			
		}
				
		return $data;
		
	}
	
	
	public static function output_block_dynamic_css() {
		
		$data = '';
				
		foreach ( self::$block_actions['dynamic-css'] as $block_type => $blocks ) {
			
			foreach ( $blocks as $block_id => $block_options ) {
					
				$block = HeadwayBlocksData::get_block($block_id, true);
				
				$data .= call_user_func(array($block_options['class'], 'dynamic_css'), $block['id']);
				
			}
			
		}
				
		return $data;
		
	}
	

	public static function enqueue_block_dynamic_js_file() {
		
		//Do not run these if it's the admin page or the visual editor is open
		if ( is_admin() || HeadwayRoute::is_visual_editor() )
			return false;

		$current_layout_in_use = HeadwayLayout::get_current_in_use(); 
		$script_name = 'block-dynamic-js-layout-' . HeadwayLayout::get_current_in_use();

		//Instead of doing foreachs, simply encode the JS content array into JSON and search for a string.  No need to do recursive searches.
		$json_encoded_layout_check = json_encode(self::$block_actions['dynamic-js']);

		//Make sure that this layout (or inherited layout) used the block JS content file.
		if ( strpos($json_encoded_layout_check, '"layout":"' . $current_layout_in_use . '"') === false )
			return;
			
		HeadwayCompiler::register_file(array(
			'name' => $script_name,
			'format' => 'js',
			'fragments' => array(
				array('HeadwayBlocks', 'output_block_dynamic_js')
			),
			'enqueue' => false
		));
				
		if ( strlen((string)self::output_block_dynamic_js($current_layout_in_use)) > 1 )
			wp_enqueue_script($script_name, HeadwayCompiler::get_url($script_name));
		
	}
	
	
	public static function register_block_element_instances() {
		
		if ( !($blocks = HeadwayBlocksData::get_all_blocks()) )
			return false;
								
		foreach ( $blocks as $block ) {
						
			$default_name = self::block_type_nice($block['type']) . ' #' . $block['id'];
			$name = headway_get('alias', $block['settings'], $default_name);			
						
			HeadwayElementAPI::register_element_instance(array(
				'group' => 'blocks',
				'element' => 'block-' . $block['type'],
				'id' => $block['type'] . '-block-' . $block['id'],
				'name' => $name,
				'selector' => '#block-' . $block['id'],
				'layout' => $block['layout']
			));
			
		}
		
	}
	
	
	public static function display_block($block, $where = null) {
		
		//We'll allow this function to take either an integer argument to look up the block or to use the existing
		if ( !is_array($block) )
			$block = HeadwayBlocksData::get_block($block);
			
		//Check that the block exists
		if ( !is_array($block) || !$block )
			return false;
		
		$block_types = HeadwayBlocks::get_block_types();
		
		//Set the original block ID for future use
		$original_block_id = $block['id'];
		
		//Set the block style to null so we don't get an ugly notice down the road if it's not used.
		$block_style_attr = null;
						
		//Check if the block type exists
		if ( !$block_type_settings = headway_get($block['type'], $block_types, array()) ) {
			
			$block['requested-type'] = $block['type'];
			$block['type'] = 'unknown';
			
		}
																	
		//Get the custom CSS classes and change commas to spaces and remove double spaces and remove HTML
		$custom_css_classes = str_replace('  ', ' ', str_replace(',', ' ', htmlspecialchars(strip_tags(headway_get('css-classes', $block['settings'], '')))));
		
		$block_classes = array_filter(explode(' ', $custom_css_classes));
		
		$block_classes[] = 'block';
		$block_classes[] = 'block-type-' . $block['type'];
		
		$block_classes[] = ( headway_get('fixed-height', $block_type_settings, false) !== true ) ? 'block-fluid-height' : 'block-fixed-height';
		
		//Block Styles
		if ( HEADWAY_CHILD_THEME_ACTIVE && $block_style = headway_get(HEADWAY_CHILD_THEME_ID . '-block-style', $block['settings']) ) {
						
			$block_style_classes = explode(' ', headway_get('class', headway_get($block_style, HeadwayChildThemeAPI::$block_styles)));
			
			foreach ( $block_style_classes as $block_style_class )
				$block_classes[] = $block_style_class;
			
		}
		
		//If the block is being displayed in the Grid, then we need to make it work with absolute positioning.
		if ( $where == 'grid' ) {
			
			$block_classes[] = 'grid-width-' . $block['dimensions']['width'];							
			$block_classes[] = 'grid-left-' . $block['position']['left'];

			$block_style_attr = ' style="height: ' . $block['dimensions']['height'] . 'px; top: ' . $block['position']['top'] . 'px;"';
			
		}
		
		//If the responsive grid is active, then add the responsive block hiding classes
		if ( HeadwayResponsiveGrid::is_active() ) {
			
			$responsive_block_hiding = headway_get('responsive-block-hiding', $block['settings'], array());
			
			if ( is_array($responsive_block_hiding) && count($responsive_block_hiding) > 0 ) {
				
				foreach ( $responsive_block_hiding as $device )
					$block_classes[] = 'responsive-block-hiding-device-' . $device;
					
			}
			
		}
		
		//If it's a mirrored block, change $block to the mirrored block
		if ( $mirrored_block = HeadwayBlocksData::is_block_mirrored($block) ) {
			
			$block = $mirrored_block;
			
			//Add Classes for the mirroring
			$block_classes[] = 'block-mirrored';
			$block_classes[] = 'block-mirroring-' . $mirrored_block['id'];
			$block_classes[] = 'block-original-' . $original_block_id;
				
		}
		
		//Fetch the HTML tag for the block
		$block_tag = ( $html_tag = headway_get('html-tag', $block_type_settings) ) ? $html_tag : 'div';
		
		//The ID attribute for the block.  This will change if mirrored.
		$block_id_for_id_attr = $block['id'];

		//Original block ID to be used in the Visual Editor
		if ( HeadwayRoute::is_visual_editor_iframe() ) {

			$block_data_attrs = implode(' ', array(
				'data-id="' . str_replace('block-', '', $original_block_id) . '"',
				'data-grid-left="' . $block['position']['left'] . '"',
				'data-grid-top="' . $block['position']['top'] . '"',
				'data-width="' . $block['dimensions']['width'] . '"',
				'data-height="' . $block['dimensions']['height'] . '"'
			));

		} else {

			$block_data_attrs = null;

		}
			
		//The grid will display blocks entirely differently and not use hooks.
		if ( $where != 'grid' ) {

			do_action('headway_before_block', $block);	
			do_action('headway_before_block_' . $block['id'], $block);	
						
			echo '<' . $block_tag . ' id="block-' . $block_id_for_id_attr . '" class="' . implode(' ', array_filter(apply_filters('headway_block_class', $block_classes, $block))) . '"' . $block_style_attr . $block_data_attrs . '>';
	
				do_action('headway_block_open', $block);
				do_action('headway_block_open_' . $block['id'], $block);
	
				echo '<div class="block-content">';
			
					do_action('headway_block_content_open', $block);
					do_action('headway_block_content_open_' . $block['id'], $block);
																		
					do_action('headway_block_content_' . $block['type'], $block);
				
					do_action('headway_block_content_close', $block);
					do_action('headway_block_content_close_' . $block['id'], $block);
										
				echo '</div><!-- .block-content -->' . "\n";
	
				do_action('headway_block_close', $block);
				do_action('headway_block_close_' . $block['id'], $block);
	
			echo '</' . $block_tag . '><!-- #block-' . $block_id_for_id_attr . ' -->' . "\n";
		
			do_action('headway_after_block', $block);
			do_action('headway_after_block_' . $block['id'], $block);
		
		//Show the block in the grid
		} else {
			
			$show_content_in_grid = headway_get('show-content-in-grid', $block_type_settings, false);
			
			if ( !$show_content_in_grid )
				$block_classes[] = 'hide-content-in-grid';
															
			if ( !self::block_type_exists($block['type']) )
				$block_classes[] = 'block-error';
			
			echo '<' . $block_tag . ' id="block-' . $block_id_for_id_attr . '" class="' . implode(' ', array_filter($block_classes)) . '"' . $block_style_attr . $block_data_attrs . '>';
			
				echo '<div class="block-content-fade block-content">';
									
					if ( $show_content_in_grid || !self::block_type_exists($block['type']) ) {
						
						do_action('headway_block_content_' . $block['type'], $block);
						
					} else {
						
						echo '<p class="hide-content-in-grid-notice"><strong>Notice:</strong> <em>' . self::block_type_nice($block['type']) . '</em> blocks do not display in the Grid Mode.  Please switch to either the Manage or Design mode to see the content in this block.</p>';
						
					}
				
				echo '</div><!-- .block-content-fade -->' . "\n";
			
				echo '<h3 class="block-type"><span>' . HeadwayBlocks::block_type_nice($block['type']) . '</span></h3>';
							
			echo '</' . $block_tag . '><!-- #block-' . $block_id_for_id_attr . ' -->' . "\n";
			
		}
		
		//Spit the ID back out
		return $block['id'];
		
	}
	
	
	public static function get_block_types($merged = true) {
		
		global $headway_block_types;
		
		if ( !isset($headway_block_types) || empty($headway_block_types) )
			return null;
		
		if ( $merged )
			return array_merge($headway_block_types['core'], $headway_block_types['plugins']);
		else
			return $headway_block_types;
		
	}
	
	
	public static function block_type_nice($type) {
		
		return ucwords(str_replace('-', ' ', $type));
		
	}
	
	
	public static function block_type_exists($type) {
		
		$block_types = self::get_block_types();
		
		//If, for some reason, the blocks array isn't set, just return false.
		if ( !is_array($block_types) )
			return new WP_Error('blocks_array_does_not_exist', __('The Headway blocks array does not exist.', 'headway'), $this);
				
		//Check for the actual block type
		if ( isset($block_types[$type]) )
			return true;
		
		//Return false if everything else fails
		return false;
		
	}
	
	
	public static function unknown_block_content($block = null) {
		
		$block_type = self::block_type_nice($block['requested-type']);
		
		echo '<div class="alert alert-red block-type-unknown-notice"><p>The requested block type (' . $block_type . ') does not exist.  Please re-activate the block plugin or child theme if you wish to use this block again.</p></div>';
		
	}
	

}