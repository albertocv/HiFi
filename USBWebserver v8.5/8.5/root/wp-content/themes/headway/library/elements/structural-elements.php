<?php
add_action('headway_register_elements', 'headway_register_structural_elements');
function headway_register_structural_elements() {
	
	//Structure
	HeadwayElementAPI::register_group('structure', 'Structure');

		HeadwayElementAPI::register_element(array(
			'group' => 'structure',
			'id' => 'body',
			'name' => 'Body',
			'selector' => 'body',
			'properties' => array('background')
		));

		HeadwayElementAPI::register_element(array(
			'group' => 'structure',
			'id' => 'wrapper',
			'name' => 'Wrapper',
			'selector' => 'div.wrapper',
			'properties' => array('background', 'borders', 'padding', 'rounded-corners', 'box-shadow'),
			'supports_instances' => true
		));

	//Blocks
	HeadwayElementAPI::register_group('blocks', 'Blocks');
	
}