<?php
class ManageSpacingPanel extends HeadwayVisualEditorPanelAPI {
	
	public $id = 'spacing';
	public $name = 'Spacing';
	public $mode = 'manage';
	
	public $tabs = array(
		'blocks' => 'Blocks'
	);
	
	public $tab_notices = array(
		'blocks' => 'These settings are <strong>global</strong> and are not customized on a per-layout basis.'
	);
	
	public $inputs = array(		
		'blocks' => array(
			'block-bottom-margin' => array(
				'type' => 'slider',
				'name' => 'block-bottom-margin',
				'label' => 'Block Bottom Margin',
				'default' => 10,
				'tooltip' => 'Adjusting this will change the amount of space below every block.  Please note, this will not be reflected in the layout mode.',
				'unit' => 'px',
				'slider-min' => 0,
				'slider-max' => 30,
				'slider-interval' => 5,
				'callback' => 'stylesheet.update_rule(".block", {"margin-bottom": value + "px"});'
			)
		)
	);

}
headway_register_visual_editor_panel('ManageSpacingPanel');