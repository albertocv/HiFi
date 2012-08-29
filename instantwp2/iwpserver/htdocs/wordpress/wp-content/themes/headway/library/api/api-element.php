<?php
class HeadwayElementAPI {
	
		
	protected static $elements = array();
	
	protected static $groups = array();
	
	
	public static $element_paths = array();
	
	
	public static function init() {
		
		add_action('wp_loaded', array(__CLASS__, 'register_elements_hook'));
		
	}
	
	
	public static function register_elements_hook() {
				
		//Add a central action where we can register all elements to.  This will be performance increase in the long run 
		//since elements will only be registered when they need to be.
		do_action('headway_register_elements');
		
	}
	
	
	public static function get_all_elements() {
		
		return self::$elements;
		
	}

	
	public static function get_groups() {
		
		return self::$groups;
		
	}
	
	
	public static function get_main_elements($group) {
		
		return self::$elements[$group];
		
	}
	
	
	public static function get_sub_elements($element) {
		
		$path = self::$element_paths[$element];
		
		if ( $path['parent'] !== null ) {
			return self::$elements[$path['group']][$path['parent']][$element]['children'];
		} else {
			return self::$elements[$path['group']][$element]['children'];			
		}
		
	}
	
	
	public static function get_default_elements() {
		
		return self::$elements['default-elements'];
		
	}
	
	
	public static function get_element($element) {
		
		if ( !isset(self::$element_paths[$element]) )
			return null;
			
		$path = self::$element_paths[$element];
				
		if ( $path['parent'] !== null ) {
			return self::$elements[$path['group']][$path['parent']]['children'][$element];
		} else {
			return self::$elements[$path['group']][$element];			
		}
		
	}
	

	public static function get_instances($element) {
		
		$element = self::get_element($element);
				
		return isset($element['instances']) ? $element['instances'] : false;
		
	}
	
	
	public static function get_states($element) {
		
		$element = self::get_element($element);
		
		return isset($element['states']) ? $element['states'] : false;
		
	}
	
	
	public static function get_inherit_location($element, $special_element_type = false, $special_element_meta = false) {
				
		$element_query = HeadwayElementAPI::get_element($element);
		
		//Check if element has a inherit location is set
		if ( headway_get('inherit-location', $element_query, false) ) {
			
			$inherit_location = HeadwayElementAPI::get_element($element_query['inherit-location']);
			
			return $inherit_location['id'];
			
		}
		
		return null;
		
	}
	
	
	public static function register_element($args) {
		
		if ( !is_array($args) )
			return new WP_Error('hw_elements_register_element_args_not_array', __('Error: Arguments must be an array for this element.', 'headway'), $args);
		
		$defaults = array(
			'group' => null,
			'parent' => null,
			'id' => null,
			'name' => null,
			'selector' => null,
			'properties' => array(),
			'states' => array(),
			'supports_instances' => false,
			'instances' => array(),
			'children' => array(),
			'default-element' => false,
			'inherit-location' => false
		);
		
		$item = array_merge($defaults, $args);
		
		//If the element is set to default, change the group to default
		if ( $item['default-element'] === true ) 
			$item['group'] = 'default-elements';
		
		//If requirements are not met, throw errors
		if ( $item['id'] === null )
			return new WP_Error('hw_elements_register_element_no_id', __('Error: An ID is required for this element.', 'headway'), $item);
			
		if ( $item['name'] === null )
			return new WP_Error('hw_elements_register_element_no_name', __('Error: A name is required for this element.', 'headway'), $item);	
			
		if ( $item['group'] === null && $item['default-element'] === false )
			return new WP_Error('hw_elements_register_element_no_group', __('Error: A group is required for this element.', 'headway'), $item);	
			
		if ( $item['selector'] === null  && $item['default-element'] === false )
			return new WP_Error('hw_elements_register_element_no_selector', __('Error: A CSS selector is required for this element.', 'headway'), $item);
			
		if ( $item['properties'] === array() )
			return new WP_Error('hw_elements_register_element_no_properties', __('Error: Properties are required for this element.', 'headway'), $item);	
			
		//Remove children if the element is not capable of bearing children
		if ( $item['parent'] !== null )
			unset($item['children']);

		//Figure out where the element will go in the elements array
		if ( $item['parent'] !== null )
			$destination =& self::$elements[$item['group']][$item['parent']]['children'][$item['id']];
		else
			$destination =& self::$elements[$item['group']][$item['id']];
		
		//Add the guts
		$destination = $item;
		
		//Remove the empty options
		if ( $destination['parent'] === null )
			unset($destination['parent']);
			
		if ( $destination['states'] === array() )
			unset($destination['states']);	
			
		if ( $destination['supports_instances'] === false ) {
			unset($destination['supports_instances']);
			unset($destination['instances']);
		}
		
		//Add the element to element paths so we can look the parent and group up
		self::$element_paths[$item['id']] = array('group' => $item['group'], 'parent' => $item['parent']);
		
		//The element is now registered!
		return $destination;
		
	}
	
	
	public static function register_element_instance($args) {
		
		if ( !is_array($args) )
			return new WP_Error('hw_elements_register_element_instance_args_not_array', __('Error: Arguments must be an array for this element instance.', 'headway'), $args);
		
		$defaults = array(
			'group' => null,
			'grandparent' => null,
			'element' => null,
			'id' => null,
			'name' => null,
			'selector' => null,
			'layout' => null
		);
		
		$item = array_merge($defaults, $args);
		
		//If requirements are not met, throw errors
		if ( $item['id'] === null )
			return new WP_Error('hw_elements_register_element_instance_no_id', __('Error: An ID is required for this element instance.', 'headway'), $item);
			
		if ( $item['name'] === null )
			return new WP_Error('hw_elements_register_element_instance_no_name', __('Error: A name is required for this element instance.', 'headway'), $item);	
			
		if ( $item['group'] === null )
			return new WP_Error('hw_elements_register_element_instance_no_group', __('Error: A group is required for this element instance.', 'headway'), $item);	
		
		if ( $item['element'] === null )
			return new WP_Error('hw_elements_register_element_instance_no_parent', __('Error: A parent element is required for this element instance.', 'headway'), $item);
			
		if ( $item['selector'] === null )
			return new WP_Error('hw_elements_register_element_instance_no_selector', __('Error: A CSS selector is required for this element instance.', 'headway'), $item);

		//Figure out where the element will go in the elements array
		if ( $item['grandparent'] !== null )
			$destination =& self::$elements[$item['group']][$item['grandparent']]['children'][$item['element']];
		else
			$destination =& self::$elements[$item['group']][$item['element']];
			
		//Check if element support instances
		if ( !isset($destination['supports_instances']) || $destination['supports_instances'] === false || !is_array($destination['instances']) )
			return new WP_Error('hw_elements_register_element_instance_not_support', __('Error: The element specified does not support instances.', 'headway'), $item);
		//Change destination to instances array
		else
			$destination =& $destination['instances'][$item['id']];
		
		//Add the guts
		$destination = $item;
		
		//Add the instance to element paths so we can look the parent, grandparent and group up
		self::$element_paths[$item['id']] = array('group' => $item['group'], 'parent' => $item['element'], 'grandparent' => $item['grandparent']);
		
		//Remove the extra options
		unset($destination['element']);
		unset($destination['grandparent']);	
		unset($destination['group']);
		
		//The element instance is now registered!
		return $destination;
		
	}
	
	
	public static function register_group($id, $name) {
		
		//Group already exists
		if ( isset(self::$groups[$id]) && isset(self::$elements[$id]) )
			return new WP_Error('hw_elements_register_group_already_exists', __('Error: The group being registered already exists.', 'headway'), $id);
			
		//Set up group that elements will go into
		self::$elements[$id] = array();
		
		//Place group in groups array so we can track name
		self::$groups[$id] = $name;
		
		return true;
		
	}
	
	
	public static function deregister_element($args) {
		
		if ( !is_array($args) )
			return new WP_Error('hw_elements_deregister_element_args_not_array', __('Error: Arguments must be an array for deregistering this element.', 'headway'), $args);
		
		$defaults = array(
			'group' => null,
			'parent' => null,
			'id' => null
		);
		
		$item = array_merge($defaults, $args);
		
		//If requirements are not met, throw errors
		if ( $item['group'] === null )
			return new WP_Error('hw_elements_deregister_element_no_group', __('Error: A group is required for deregistering this element.', 'headway'), $item);
			
		if ( $item['id'] === null )
			return new WP_Error('hw_elements_deregister_element_no_id', __('Error: An ID is required for deregistering this element.', 'headway'), $item);
			
		//Figure out where to delete the element from
		if ( $item['parent'] !== null )
			$destination =& self::$elements[$item['group']][$item['parent']]['children'][$item['id']];
		else
			$destination =& self::$elements[$item['group']][$item['item']];
			
		//Deregister the element
		unset($destination);
		
		//Remove the element from element paths
		unset(self::$element_paths[$item['id']]);
		
		return true;
		
	}
	
	
	public static function deregister_element_instance($args) {
		
		if ( !is_array($args) )
			return new WP_Error('hw_elements_deregister_element_instance_args_not_array', __('Error: Arguments must be an array for deregistering this element instance.', 'headway'), $args);
		
		$defaults = array(
			'group' => null,
			'grandparent' => null,
			'element' => null,
			'id' => null
		);
		
		$item = array_merge($defaults, $args);
		
		//If requirements are not met, throw errors
		if ( $item['group'] === null )
			return new WP_Error('hw_elements_deregister_element_instance_no_group', __('Error: A group is required for deregistering this element instance.', 'headway'), $item);
			
		if ( $item['id'] === null )
			return new WP_Error('hw_elements_deregister_element_instance_no_id', __('Error: An instance ID is required for deregistering this element instance.', 'headway'), $item);
			
		if ( $item['element'] === null )
			return new WP_Error('hw_elements_deregister_element_instance_no_element', __('Error: An element is required for deregistering this element instance.', 'headway'), $item);
			
		//Figure out where to delete the element from
		if ( $item['grandparent'] !== null )
			$destination =& self::$elements[$item['group']][$item['grandparent']]['children'][$item['element']]['instances'][$item['id']];
		else
			$destination =& self::$elements[$item['group']][$item['element']]['instances'][$item['id']];
			
		//Deregister the element
		unset($destination);
		
		return true;
		
	}
	
	
	public static function deregister_group($id) {
		
		//Check if group exists or not
		if ( !isset(self::$elements[$id]) && !isset(self::$groups[$id]) )
			return new WP_Error('hw_elements_deregister_group_does_not_exist', __('Error: The group being deregistered does not exist.', 'headway'), $id);
		
		//Remove group
		unset(self::$elements[$id]);
		unset(self::$groups[$id]);
		
		//Remove all elements from element paths that have this group
		foreach ( self::$element_paths as $element_id => $element_info ) {
			if ( $element_info['group'] === $id )
				unset(self::$element_paths[$element_id]);
			
			continue;
		}
		
		//We're done
		return true;
		
	}

	
}