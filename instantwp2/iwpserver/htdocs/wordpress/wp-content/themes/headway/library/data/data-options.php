<?php
/**
 * Functions to get, update, and delete data from the database.
 *
 * @package Headway
 * @subpackage Data Handling
 * @author Clay Griffiths
 **/

class HeadwayOption {
	
	
	/**
	 * Initiate the option groups variable.
	 **/
	protected static $option_groups = null;
	
	
	/**
	 * Set the default group for all of the database functions to get, set, and delete from.
	 **/
	protected static $default_group = 'general';


	/**
	 * Group suffix.  Used for things like previewing, etc.  If previewing, use '_preview' as the suffix.
	 **/
	public static $group_suffix = null;
	
	

	public static function init() {

		self::check_option_groups();

		if ( headway_get('preview') && HeadwayCapabilities::can_user_visually_edit() ) {

			HeadwayOption::$group_suffix = '_preview';
			HeadwayLayoutOption::$group_suffix = '_preview';

		}

	}


	/**
	 * Create options group variable and DB entry if it doesn't exist.
	 **/
	public static function check_option_groups() {
								
		$option_groups =& self::$option_groups;		
		
		//Stop the function if everything is OK
		if ( is_array($option_groups) ) 
			return;				
								
		//Retrieve option groups from DB that way we can keep track of all of the option groups and delete them in the future
		if ( $option_groups === null ) 
			$option_groups = get_option('headway_option_groups');
				
		//If option groups doesn't exist in DB, let's create it.
		if ( $option_groups === false ) {
			
			$option_groups = array();
			
			update_option('headway_option_groups', $option_groups);
			
		}
				
	}
	
	
	/**
	 * Retrieve a value from the database.
	 * 
	 * @param string Option to retrieve
	 * @param string Option group to fetch from
	 * @param mixed Default value to be returned.  This will be returned if the requested option does not exist.
	 * 
	 * @return mixed
	 **/
	public static function get($option = null, $group_name = false, $default = null) {
		
		if ( $option === null )
			return false;
							
		if ( !$group_name ) 
			$group_name = self::$default_group;
				
		$group_data = get_option('headway_option_group_' . $group_name . self::$group_suffix);

		if ( self::$group_suffix && !$group_data )
			$group_data = get_option('headway_option_group_' . $group_name);
							
		//If option doesn't exist, return default.
		if ( !isset($group_data[$option]) ) 
			return $default;
			
		//Start formatting if it exists
		$data = headway_fix_data_type($group_data[$option]);
		
		return $data;
		
	}
	
	
	/**
	 * Add or update an option on the database.
	 * 
	 * @param string Option to set
	 * @param mixed Value to attach to option
	 * @param string Group to add/update the option to
	 * 
	 * @return bool
	 **/
	public static function set($option = null, $value = null, $group_name = false) {
		
		if ( $option === null )
			return false;
		
		if ( $value === null )
			return false;
				
		if ( !$group_name ) 
			$group_name = self::$default_group;
			
		$option_groups =& self::$option_groups;
		
		$group_data = get_option('headway_option_group_' . $group_name . self::$group_suffix);

		if ( self::$group_suffix && !$group_data )
			$group_data = get_option('headway_option_group_' . $group_name);
				
		//Create option group if it doesn't exist
		if ( !isset($option_groups[$group_name]) ) {
						
			$option_groups[$group_name] = true;
			$group_data = array();
			
			update_option('headway_option_groups', $option_groups);
			
		}
		
		//Handle boolean values
		if ( is_bool($value) )
			$value = ( $value === true ) ? 'true' : 'false';
		
		//Add option
		$group_data[$option] = $value;
		
		//Send group option to DB
		update_option('headway_option_group_' . $group_name . self::$group_suffix, $group_data);
		
		return true;
		
	}
	
	
	/**
	 * Delete option from database.
	 * 
	 * @param string Option to delete
	 * @param string Group to delete from
	 * 
	 * @return bool
	 **/
	public static function delete($option = null, $group_name = false) {
		
		if ( $option === null )
			return false;
				
		if ( !$group_name ) 
			$group_name = self::$default_group;
		
		$option_groups =& self::$option_groups;
		$group_data = get_option('headway_option_group_' . $group_name);
		
		//If the group isn't in the DB or the option doesn't exist
		if( !is_array($group_data) || !isset($group_data[$option]) )
			return false;
			
		//Delete option from group
		unset($group_data[$option]);
		
		//If the array is still fine and not empty, just update the group on the DB
		if ( count($group_data) !== 0 ) {
			
			update_option('headway_option_group_' . $group_name . self::$group_suffix, $group_data);
			
			return true;
			
		} elseif ( !self::$group_suffix ) {
			
			//Remove group from DB
			delete_option('headway_option_group_' . $group_name);
			
			//Remove group from option groups listing
			unset($option_groups[$group_name]);
			update_option('headway_option_groups', $option_groups);
			
			return true;
			
		}
		
	}

	
}