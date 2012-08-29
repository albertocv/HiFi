<?php
class HeadwayGrid {
	
		
	public static $columns = 24;
	
	public static $column_width;
	
		public static $default_column_width = 20;

		public static $column_width_min;

		public static $column_width_max;
	
	public static $gutter_width;
	
		public static $default_gutter_width = 20;

		public static $gutter_width_min;

		public static $gutter_width_max;

	public static $block_bottom_margin;
	
		public static $default_block_bottom_margin = 10;

	
	public static function init() {
		
		self::$column_width = apply_filters('headway_column_width', HeadwayOption::get('column-width', 'general', self::$default_column_width));
		self::$gutter_width = apply_filters('headway_gutter_width', HeadwayOption::get('gutter-width', 'general', self::$default_gutter_width));

		self::$block_bottom_margin = apply_filters('headway_block_bottom_margin', HeadwayOption::get('block-bottom-margin', 'general', self::$default_block_bottom_margin));

		/* Check minimums and maximums */
			/* Column Width */
			self::$column_width_min = apply_filters('headway_column_width_min', false);
			self::$column_width_max = apply_filters('headway_column_width_max', false);

			if ( self::$column_width_min && self::$column_width < self::$column_width_min )
				self::$column_width = self::$column_width_min;

			if ( self::$column_width_max && self::$column_width > self::$column_width_max )
				self::$column_width = self::$column_width_min;

			/* Gutter Width */
			self::$gutter_width_min = apply_filters('headway_gutter_width_min', false);
			self::$gutter_width_max = apply_filters('headway_gutter_width_max', false);

			if ( self::$gutter_width_min && self::$gutter_width < self::$gutter_width_min )
				self::$gutter_width = self::$column_width_min;

			if ( self::$gutter_width_max && self::$gutter_width > self::$gutter_width_max )
				self::$gutter_width = self::$column_width_min;
				
	}
	
	
	public static function get_grid_width() {
		
		return (self::$column_width * self::$columns) + ((self::$columns - 1) * self::$gutter_width);
		
	}
	
	
	public static function display_grid_lines() {
		
		echo '<div id="grid" class="grid-grey">';
		
			for ( $i = 1; $i <= HeadwayGrid::$columns; $i++ )
				echo '<div class="grid-column grid-width-1"></div>';

		echo '</div><!-- #grid -->';
		
	}
	
	
	public static function display_grid_blocks() {
		
		echo '<div class="grid-container">';

			$blocks = HeadwayBlocksData::get_blocks_by_layout(HeadwayLayout::get_current_in_use());
				
			if ( is_array($blocks) ) {

				foreach ($blocks as $block_id => $block) {
						
					HeadwayBlocks::display_block($block, 'grid');
	
				}

			}	
	
		echo '</div><!-- .grid-container -->';
		
	}
	
	
	public static function display_grid_buttons() {
		
		$grid_height_decrease_disable = HeadwayOption::get('grid-height', false, 1500) <= 800 ? 'grid-height-button-disabled ' : null;
		
		echo '
			<div id="grid-height-buttons">
				<span id="grid-height-decrease" class="' . $grid_height_decrease_disable . 'grid-height-adjustment tooltip" title="Decrease grid height">-</span>
				<span id="grid-height-increase" class="grid-height-adjustment tooltip" title="Increase grid height">+</span>
			</div><!-- #grid-height-buttons -->
		';
		
	}
	
	
	public static function display_canvas() {

		echo '
		<!DOCTYPE HTML>
		<html lang="en">

		<head>

		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta http-equiv="cache-control" content="no-cache" />

		<title>Visual Editor Grid: ' . wp_title(false, false) . '</title>
		';

		do_action('headway_grid_iframe_head');

		echo '
		</head><!-- /head -->

		<body class="visual-editor-iframe-grid">
		
			<div id="whitewrap">
		';

			echo '<div id="wrapper-1" class="wrapper fixed-grid grid-active">';
	
				self::display_grid_lines();

				self::display_grid_blocks();
				
				self::display_grid_buttons();
		
			echo '</div><!-- #wrapper-1 -->';
	
		do_action('headway_grid_iframe_footer');
			
		echo '
		
			</div><!-- #whitewrap -->
		</body>
		</html>
		';

	}
	

	public static function show() {
		
		add_action('headway_grid_iframe_head', array(__CLASS__, 'print_styles'), 12);
		add_action('headway_grid_iframe_styles', array(__CLASS__, 'enqueue_canvas_assets'));
		
		self::display_canvas();
		
	}
	
	
	public static function enqueue_canvas_assets() {

		$grid_css_fragments = array(
			HEADWAY_LIBRARY_DIR . '/media/css/reset.css',
			HEADWAY_LIBRARY_DIR . '/media/css/grid.css',
			HEADWAY_LIBRARY_DIR . '/media/css/block-basics.css',
			HEADWAY_LIBRARY_DIR . '/media/css/content-styling.css',
			HEADWAY_LIBRARY_DIR . '/media/css/alerts.css',
			HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-mixins.less',
			HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-iframe.less',
			HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-tooltips.less',
			HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-iframe-grid.less',
			HEADWAY_LIBRARY_DIR . '/visual-editor/css/editor-iframe-grid-block-content.css',
			array('HeadwayDynamicStyle', 'fixed_grid'),
			array('HeadwayDynamicStyle', 'visual_editor_grid'),
			array('HeadwayDynamicStyle', 'block_heights'),
			array('HeadwayDynamicStyle', 'wrapper')
		);

		HeadwayCompiler::register_file(array(
			'name' => 'grid-iframe',
			'format' => 'less',
			'fragments' => $grid_css_fragments,
			'dependencies' => array(
				HEADWAY_LIBRARY_DIR . '/media/dynamic/style.php'
			)
		));

	}
	
	
	public static function print_styles() {
		
		global $wp_styles;
		$wp_styles = null;
		
		do_action('headway_grid_iframe_styles');
		
		wp_print_styles();
		
	}


}