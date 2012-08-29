<?php
class ManageContentPanel extends HeadwayVisualEditorPanelAPI {
	
	public $id = 'content';
	public $name = 'Content';
	public $mode = 'manage';
	
	public $tabs = array(
		'search' => 'Search'
	);
	
	public $tab_notices = array(
		'search' => 'These settings are <strong>global</strong> and are not customized on a per-layout basis.'
	);
	
	public $inputs = array(		
		'search' => array(
			'search-placeholder' => array(
				'type' => 'text',
				'name' => 'search-placeholder',
				'label' => 'Search Placeholder',
				'default' => 'Type to search, then press enter',
				'tooltip' => 'The placeholder will be displayed if nothing is entered into the search box.  Once the visitor focuses their cursor into the search field, the placeholder will be removed.',
				'callback' => '$i(\'form#searchform input.field\').attr(\'placeholder\', value);'
			)
		)
	);

}
headway_register_visual_editor_panel('ManageContentPanel');