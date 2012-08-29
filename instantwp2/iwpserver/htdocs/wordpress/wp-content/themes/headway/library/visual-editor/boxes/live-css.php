<?php
if ( !HeadwayVisualEditor::is_mode('grid') && current_theme_supports('headway-live-css') )
	headway_register_visual_editor_box('HeadwayLiveCSSBox');

class HeadwayLiveCSSBox extends HeadwayVisualEditorBoxAPI {
	
	
	/**
	 *	Slug/ID of panel.  Will be used for HTML IDs and whatnot.
	 **/
	protected $id = 'live-css';
	
	
	/**
	 * Name of panel.  This will be shown in the title.
	 **/
	protected $title = 'Live CSS';
	
	protected $description = 'Enter custom CSS and it\'ll show instantly!';
	
	
	/**
	 * Which mode to put the panel on.
	 **/
	protected $mode = 'all';
	
	protected $center = true;
	
	protected $width = 500;
		
	protected $height = 300;
	
	protected $min_width = 350;
	
	protected $min_height = 200;
	
	protected $closable = true;
	
	protected $draggable = true;
	
	protected $resizable = true;
	
	
	public function content() {
		
		echo '<textarea id="live-css" name="live-css" group="general">' . HeadwayOption::get('live-css') . '</textarea>';
		
	}
	
	
}