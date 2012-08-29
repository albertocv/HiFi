<?php
class HeadwayElementsData {
	
	
	/* Mass Get */
	public static function get_all_elements() {
		
		$default_data = self::get_default_data();
		
		//Set up the main array to be returned
		$elements = array();
		
		//Get all of the design editor option groups by looking at the option groups catalog
		$option_groups = get_option('headway_option_groups');
		
		//Pull out only the design editor groups.  Since headway_option_groups uses true for every value 
		//and the group is actually the key, we must pull the keys out using array_keys
		$design_editor_groups = array_filter(array_keys($option_groups), create_function('$group', 'return (strpos($group, \'design-editor-group-\') !== false);'));

		//Loop through all of the groups and get every element and its properties
		foreach ( $design_editor_groups as $design_editor_group ) {
			
			$group = get_option('headway_option_group_' . $design_editor_group);
			
			//Merge the current group into the array to be returned
			$elements = array_merge($elements, array_map('maybe_unserialize', $group));
			
		}
		
		//Merge in the default element data
		if ( is_array($default_data) )
			$elements = headway_array_merge_recursive_simple($default_data, $elements);
			
		//Move default elements to the top
		foreach ( $elements as $element_id => $element_options ) {
			
			$element = HeadwayElementAPI::get_element($element_id);
			
			if ( !isset($element['default-element']) || $element['default-element'] === false )
				continue;
				
			$temp_id = $element_id;
			$temp_options = $element_options;
			
			unset($elements[$element_id]);
			
			$elements = array_merge(array($temp_id => $temp_options), $elements);
			
		}
					
		return $elements;
		
	}
	
	
	public static function get_element_properties($element_id) {
		
		$default_data = self::get_default_data();
		
		//Make sure the element is registered
		if ( !isset(HeadwayElementAPI::$element_paths[$element_id]) )
			return new WP_Error('element_not_registered', __('The element ID is not registered.', 'headway'), $element_id);
			
		//Set vars up and get stuff from database
		$group = HeadwayElementAPI::$element_paths[$element_id]['group'];
		$element = HeadwayOption::get($element_id, 'design-editor-group-' . $group, array('properties' => array()));
		
		if ( !isset($element['properties']) || !is_array($element['properties']) )
			$element['properties'] = array();
		
		//If there are default properties for the element we're on, use them.
		if ( is_array($default_data) && isset($default_data[$element_id]) )
			$properties = array_merge($default_data[$element_id]['properties'], $element['properties']);
		else
			$properties = $element['properties'];
		
		//Fetch the property
		return ( is_array($properties) && count($properties) > 0 ) ? $properties : array();
		
	}
	
	
	public static function get_special_element_properties($element_id, $se_type, $se_meta) {
		
		$default_data = self::get_default_data();
		
		//Make sure the element is registered
		if ( !isset(HeadwayElementAPI::$element_paths[$element_id]) )
			return new WP_Error('element_not_registered', __('The element ID is not registered.', 'headway'), $element_id);
			
		//Set vars up and get stuff from database
		$group = HeadwayElementAPI::$element_paths[$element_id]['group'];
		
		$element = HeadwayOption::get($element_id, 'design-editor-group-' . $group, array(
			'special-element-' . $se_type => array(
				$se_meta => array()
			)
		));

		if ( !isset($element['special-element-' . $se_type][$se_meta]) || !is_array($element['special-element-' . $se_type][$se_meta]) )
			$element['special-element-' . $se_type][$se_meta] = array();
		
		$properties =& $element['special-element-' . $se_type][$se_meta];
		
		//If there are default properties for the element we're on, use them.
		if ( is_array($default_data) && isset($default_data[$element_id]['special-element-' . $se_type][$se_meta]) )
			$properties = array_merge($default_data[$element_id]['special-element-' . $se_type][$se_meta], $properties);
			
		//Return the data
		return ( is_array($properties) && count($properties) > 0 ) ? $properties : array();
		
	}
	

	/* Single Get */
	public static function get_property($element_id, $property_id, $default = null) {
		
		$properties = self::get_element_properties($element_id);
		
		if ( $properties !== null && !is_wp_error($properties) && isset($properties[$property_id]) )
			return headway_fix_data_type($properties[$property_id]);
			
		else
			return $default;
		
	}
	
	
	public static function get_special_element_property($element_id, $se_type, $se_meta, $property_id, $default = null) {
		
		$properties = self::get_special_element_properties($element_id, $se_type, $se_meta);
		
		if ( $properties !== null && !is_wp_error($properties) && isset($properties[$property_id]) )
			return headway_fix_data_type($properties[$property_id]);
			
		else
			return $default;
		
	}
	
	
	public static function get_inherited_property($element_id, $property_id, $default = null) {
		
		//Check for normal property first.  Need this for recursion and for instances/states.
		if ( $normal_property = self::get_property($element_id, $property_id) )
			return $normal_property;
		
		//Check for inherit location right away.
		$inherit_location = HeadwayElementAPI::get_inherit_location($element_id);
		
		//If inherit location does not exist, go straight to default.
		if ( !$inherit_location )
			return $default;
		
		//If it does exist, loop this function through again	
		else
			return self::get_inherited_property($inherit_location, $property_id, $default);
			
	}
	
	
	/* Setting */
	public static function set_property($element_id, $property_id, $value, $forced_group = false) {
		
		/* Allow set_property to be ran during maintenance */
		if ( !$forced_group ) {

			//Make sure the element is registered
			if ( !isset(HeadwayElementAPI::$element_paths[$element_id]) )
				return new WP_Error('element_not_registered', __('The element ID is not registered.', 'headway'), $element_id);
			
			//Set vars up and get stuff from database
			$group = HeadwayElementAPI::$element_paths[$element_id]['group'];

		} else {

			$group = $forced_group;

		}

		$element = HeadwayOption::get($element_id, 'design-editor-group-' . $group, array('properties' => array()));
		
		//Set the property
		$element['properties'][$property_id] = $value;
		
		//Send it back to DB
		HeadwayOption::set($element_id, $element, 'design-editor-group-' . $group);
		
		return true;
		
	}
	
	
	public static function set_special_element_property($element_id, $special_element_type, $special_element_meta, $property_id, $value) {
		
		//Make sure the element is registered
		if ( !isset(HeadwayElementAPI::$element_paths[$element_id]) )
			return new WP_Error('element_not_registered', __('The element ID is not registered.', 'headway'), $element_id);
	
		//Set vars up and get stuff from database
		$group = HeadwayElementAPI::$element_paths[$element_id]['group'];
		$element = HeadwayOption::get($element_id, 'design-editor-group-' . $group, array(
			'special-element-' . $special_element_type => array(
				$special_element_meta => array()
			)
		));
		
		//Set the property
		$element['special-element-' . $special_element_type][$special_element_meta][$property_id] = $value;
		
		//Send it back to DB
		HeadwayOption::set($element_id, $element, 'design-editor-group-' . $group);
		
		return true;
		
	}
	
	
	/* Deleting */
	public static function delete_property($element_id, $property_id) {
		
		//Make sure the element is registered
		if ( !isset(HeadwayElementAPI::$element_paths[$element_id]) )
			return new WP_Error('element_not_registered', __('The element ID is not registered.', 'headway'), $element_id);
		
		//Set vars up and get stuff from database
		$group = HeadwayElementAPI::$element_paths[$element_id]['group'];
		$element = HeadwayOption::get($element_id, 'design-editor-group-' . $group, array('properties' => array()));
		
		//Remove the property or return false if it can't
		if ( isset($element['properties'][$property_id]) )
			unset($element['properties'][$property_id]);
		else
			return false;
		
		//Send it back to DB
		HeadwayOption::set($element_id, $element, 'design-editor-group-' . $group);
		
		return true;
		
	}


	/* Defaults */
	public static function get_default_data() {

		global $headway_default_element_data;

		return apply_filters('headway_element_data_defaults', $headway_default_element_data);

	}


}