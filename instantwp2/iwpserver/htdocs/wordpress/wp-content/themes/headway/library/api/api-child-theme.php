<?php
class HeadwayChildThemeAPI {
	
	
	public static $block_styles = array();
	
	
	public static function init() {

		/* Set the HEADWAY_CHILD_THEME_ID constant to the stylesheet option if it hasn't been set. */
		if ( !defined('HEADWAY_CHILD_THEME_ID') )
			define('HEADWAY_CHILD_THEME_ID', get_option('stylesheet'));

	}

	
	public static function register_block_style(array $args) {
				
		$defaults = array(
			'id' => null,
			'name' => null,
			'class' => null,
			'block-types' => 'all'
		);
		
		$block_style = array_merge($defaults, $args);
		
		//Add the block style to the main $block_styles property
		self::$block_styles[$block_style['id']] = $block_style;
		
		return true;
				
	}
	
	
	public static function get_block_style_classes() {
		
		$block_style_classes = array();
		
		foreach ( self::$block_styles as $block_style_id => $block_style )
			$block_style_classes[$block_style_id] = $block_style['class'];
			
		return $block_style_classes;
		
	}
		
	
}