<?php
add_action('headway_register_elements', 'headway_register_default_elements');
function headway_register_default_elements() {
	
	HeadwayElementAPI::register_element(array(
		'id' => 'default-text',
		'name' => 'Paragraph Text',
		'properties' => array('fonts'),
		'default-element' => true,
		'selector' => 'body'
	));

	HeadwayElementAPI::register_element(array(
		'id' => 'default-hyperlink',
		'name' => 'Hyperlink',
		'properties' => array('fonts'  => array('color', 'font-styling', 'capitalization'), 'text-shadow'),
		'default-element' => true,
		'selector' => 'a'
	));

	HeadwayElementAPI::register_element(array(
		'id' => 'default-heading',
		'name' => 'Heading',
		'properties' => array('fonts', 'text-shadow'),
		'default-element' => true
	));
	
	HeadwayElementAPI::register_element(array(
		'id' => 'default-sub-heading',
		'name' => 'Sub Heading',
		'properties' => array('fonts', 'text-shadow'),
		'default-element' => true
	));
	
	HeadwayElementAPI::register_element(array(
		'id' => 'default-block',
		'name' => 'Block',
		'properties' => array('background', 'borders', 'fonts', 'padding', 'rounded-corners', 'box-shadow', 'overflow'),
		'default-element' => true,
		'selector' => '.block'
	));
	
}