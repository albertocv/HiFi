<?php
class GridSetupPanel extends HeadwayVisualEditorPanelAPI {
	
	public $id = 'setup';
	public $name = 'Setup';
	public $mode = 'grid';
	
	public $tabs = array(
		'grid' => 'Grid',
		'wrapper' => 'Wrapper',
		'responsive-grid' => 'Responsive Grid'
	);
	
	public $tab_notices = array(
		'grid' => '<strong>Note:</strong> the content in the grid above will not reflect how your site actually looks.  The content inside the blocks is to give you a general reference while you wireframe and build the layout to your site.<br /><br />The settings below are <strong>global</strong> and are not customized on a per-layout basis.',
		'wrapper' => 'These settings are <strong>global</strong> and are not customized on a per-layout basis.',
		'responsive-grid' => 'The Headway Responsive Grid allows the powerful grid in Headway Base to be custom-tailored depending on the device that the visitor is viewing the site from.  Please note: some sites may benefit from having the responsive grid enabled while other will not.  As the designer of the website, it is up to you to decide.  The responsive grid can be enabled or disabled at any time.'
	);
	
	public $inputs = array(
		'grid' => array(		
			'column-width' => array(
				'type' => 'slider',
				'name' => 'column-width',
				'label' => 'Column Width',
				'default' => 40,
				'tooltip' => 'The column width is the amount of space inside of each column.  This is represented by the grey regions on the grid.',
				'unit' => 'px',
				'slider-min' => 10,
				'slider-max' => 80,
				'slider-interval' => 1,
				'callback' => 'gridInputCallbackColumnWidth(value);'
			),
			
			'gutter-width' => array(
				'type' => 'slider',
				'name' => 'gutter-width',
				'label' => 'Gutter Width',
				'default' => 20,
				'tooltip' => 'The gutter width is the amount of space between each column.  This is the space between each of the grey regions on the grid.',
				'unit' => 'px',
				'slider-min' => 0,
				'slider-max' => 30,
				'slider-interval' => 1,
				'callback' => 'gridInputCallbackGutterWidth(value);'
			),
			
			'grid-width' => array(
				'type' => 'integer',
				'unit' => 'px',
				'default' => 940,
				'name' => 'grid-width',
				'label' => 'Grid Width',
				'readonly' => true
			)
		),
		
		'wrapper' => array(
			'wrapper-top-margin' => array(
				'type' => 'slider',
				'name' => 'wrapper-top-margin',
				'label' => 'Wrapper Top Margin',
				'default' => 30,
				'tooltip' => 'The wrapper top margin is the amount of space between the top of the browser window and the top of the wrapper.',
				'unit' => 'px',
				'slider-min' => 0,
				'slider-max' => 100,
				'slider-interval' => 5,
				'callback' => 'gridStylesheet.update_rule("div.wrapper", {"margin-top": value + "px"});'
			),
			
			'wrapper-bottom-margin' => array(
				'type' => 'slider',
				'name' => 'wrapper-bottom-margin',
				'label' => 'Wrapper Bottom Margin',
				'default' => 30,
				'tooltip' => 'The wrapper bottom margin is the amount of space between the bottom of the browser window and the bottom of the wrapper.',
				'slider-min' => 0,
				'slider-max' => 100,
				'slider-interval' => 5,
				'unit' => 'px',
				'callback' => 'gridStylesheet.update_rule("div.wrapper", {"margin-bottom": value + "px"});'
			)
		),
		
		'responsive-grid' => array(
			'enable-responsive-grid' => array(
				'type' => 'checkbox',
				'name' => 'enable-responsive-grid',
				'label' => 'Enable Responsive Grid',
				'default' => false,
				'tooltip' => 'If Headway\'s responsive grid is enabled, the grid will automatically adjust depending on the visitor\'s device (computer, iPhone, iPad, etc).  Enabling the responsive grid can be extremely beneficial for some websites, but may not be worthwhile for other websites.  If the responsive grid is enabled, the user will always have the option to disable the responsive grid via a link in the footer block.<br /><br /><strong>Please Note:</strong> with the responsive grid enabled, the exact pixel widths of blocks may differ very slightly from when it is <em>disabled</em>.'
			),
			
			'disable-wrapper-margin-for-smartphones' => array(
				'type' => 'checkbox',
				'name' => 'disable-wrapper-margin-for-smartphones',
				'label' => 'Disable Wrapper Margin For Smartphones',
				'default' => true,
				'tooltip' => 'If you wish to reduce vertical scrolling on smartphones, you can disable the wrapper margin for smartphones (and small tablets) only.<br /><br /><strong>Note:</strong> This setting will only take effect if the <em>Responsive Grid</em> is <strong>enabled</strong>.'
			),
			
			'responsive-video-resizing' => array(
				'type' => 'checkbox',
				'name' => 'responsive-video-resizing',
				'label' => 'Responsive Video Resizing',
				'default' => true,
				'tooltip' => 'If the Responsive Grid is enabled and the user visits the site when there are YouTube, Vimeo, or any other videos, then the videos will not resize properly unless then is checked.'
			)
		)
	);
	
	
	function modify_arguments() {
		
		/* Grid Settings */
			$this->inputs['grid']['column-width']['default'] = HeadwayGrid::$default_column_width; 
			$this->inputs['grid']['gutter-width']['default'] = HeadwayGrid::$default_gutter_width; 
			
			/* Minimum and Maximum Grid filters */
				/* Column Width */
				if ( HeadwayGrid::$column_width_min )
					$this->inputs['grid']['column-width']['slider-min'] = HeadwayGrid::$column_width_min;

				if ( HeadwayGrid::$column_width_max )
					$this->inputs['grid']['column-width']['slider-max'] = HeadwayGrid::$column_width_max;

				/* Gutter Width */
				if ( HeadwayGrid::$gutter_width_min )
					$this->inputs['grid']['gutter-width']['slider-min'] = HeadwayGrid::$gutter_width_min;

				if ( HeadwayGrid::$gutter_width_max )
					$this->inputs['grid']['gutter-width']['slider-max'] = HeadwayGrid::$gutter_width_max;

			/* Values */
				$this->inputs['grid']['column-width']['value'] = HeadwayGrid::$column_width;				
				$this->inputs['grid']['gutter-width']['value'] = HeadwayGrid::$gutter_width;				

			/* Grid Overrides */
				if ( is_numeric(apply_filters('headway_column_width', false)) ) { 

					$this->inputs['grid']['column-width']['type'] = 'integer';
					$this->inputs['grid']['column-width']['readonly'] = true;

				}

				if ( is_numeric(apply_filters('headway_gutter_width', false)) ) {

					$this->inputs['grid']['gutter-width']['type'] = 'integer';
					$this->inputs['grid']['gutter-width']['readonly'] = true;

				}
		/* End Grid Settings */


		/* If the child theme does not wish to support the responsive grid, then remove that tab. */
		if ( !current_theme_supports('headway-responsive-grid') ) {
			
			unset($this->inputs['responsive-grid']);
			unset($this->tabs['responsive-grid']);
			unset($this->tab_notices['responsive-grid']);
			
		}
			
		
	}
	
	
}
headway_register_visual_editor_panel('GridSetupPanel');