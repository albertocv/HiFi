<?php
class HeadwayElements {
	
	
	public static function init() {
		
		Headway::load(array(
			'elements/properties',
			'data/data-elements',
			'api/api-element' => 'ElementAPI'
		));
		
		add_action('headway_elements_init', array(__CLASS__, 'load_elements'));
		
		do_action('headway_elements_init');
		
	}
	
	
	public static function load_elements() {

		include_once 'default-elements.php';
		include_once 'structural-elements.php';
		
	}

	
}