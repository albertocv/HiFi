<?php
class HeadwayFonts {


	public static $fonts = array(
		'Serif' => array(
			'georgia' 			=> 'Georgia',
			'cambria' 			=> 'Cambria',
			'palatino' 			=> 'Palatino',
			'times' 			=> 'Times',
			'times new roman' 	=> 'Times New Roman'
		),
		
		'Sans-Serif' => array(
			'arial' 			=> 'Arial',
			'arial black' 		=> 'Arial Black',
			'arial narrow' 		=> 'Arial Narrow',
			'century gothic' 	=> 'Century Gothic',
			'gill sans' 		=> 'Gill Sans',
			'helvetica' 		=> 'Helvetica',
			'impact' 			=> 'Impact',
			'lucida grande' 	=> 'Lucida Grande',
			'tahoma' 			=> 'Tahoma',
			'trebuchet ms' 		=> 'Trebuchet MS',
			'verdana' 			=> 'Verdana'
		),
		
		'Monospace' => array(
			'courier' 			=> 'Courier',
			'courier new' 		=> 'Courier New'
		),
		
		'Script' => array(
			'papyrus' 			=> 'Papyrus',
			'copperplate' 		=> 'Copperplate'
		)
	);


	public static $font_stacks = array(
		'georgia' 			=> 'georgia, serif',
		'cambria' 			=> 'cambria, georgia, serif',
		'palatino' 			=> 'palatino linotype, palatino, serif',
		'times' 			=> 'times, serif',
		'times new roman' 	=> 'times new roman, serif',
		'arial' 			=> 'arial, sans-serif',
		'arial black' 		=> 'arial black, sans-serif',
		'arial narrow' 		=> 'arial narrow, sans-serif',
		'century gothic' 	=> 'century gothic, sans-serif',
		'gill sans' 		=> 'gill sans, sans-serif',
		'helvetica' 		=> 'helvetica, sans-serif',
		'impact' 			=> 'impact, sans-serif',
		'lucida grande' 	=> 'lucida grande, sans-serif',
		'tahoma' 			=> 'tahoma,  sans-serif',
		'trebuchet ms' 		=> 'trebuchet ms,  sans-serif',
		'verdana' 			=> 'verdana, sans-serif',
		'courier' 			=> 'courier, monospace',
		'courier new' 		=> 'courier new, monospace',
		'papyrus' 			=> 'papyrus, fantasy',
		'copperplate' 		=> 'copperplate, copperplate gothic bold, fantasy'
	);


	public static function get_fonts() {

		return apply_filters('headway_fonts', self::$fonts); 

	}


	public static function get_stack($font_id) {

		return headway_get($font_id, apply_filters('headway_fonts_stacks', self::$font_stacks));

	}


	public static function register_font(array $args) {

		extract($args);

		/* Check args */
		if ( !isset($id) || !isset($stack) || !isset($name) )
			return new WP_Error('hw_fonts_register_font_invalid_args', __('To register a font, the argument array must include an "id", "stack", and "name".', 'headway'), $args);

		/* Add the font to the stacks first */
		self::$font_stacks[$id] = $stack;

		/* Add the font to the custom group in the main fonts array */
			/* Create Custom group if it doesn't exist */
			if ( !isset(self::$fonts['Custom']) )
				self::$fonts['Custom'] = array();

			/* Make sure font doesn't exist */
			if ( isset(self::$fonts['Custom'][$id]) )
				return false;

			/* Add the font */
			self::$fonts['Custom'][$id] = $name;

		return true;

	}


}