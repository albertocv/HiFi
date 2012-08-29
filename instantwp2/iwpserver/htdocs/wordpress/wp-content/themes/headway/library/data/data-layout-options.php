<?php
/**
 * Functions to get, update, and delete data from the database.
 *
 * @package Headway
 * @subpackage Data Handling
 * @author Clay Griffiths
 **/

class HeadwayLayoutOption {
	
	
	/**
	 * Set the default group for all of the database functions to get, set, and delete from.
	 **/
	protected static $default_group = 'general';


	/**
	 * Group suffix.  Used for things like previewing, etc.  If previewing, use '_preview' as the suffix.
	 **/
	public static $group_suffix = null;


	public static function init() {

		if ( headway_get('preview') && HeadwayCapabilities::can_user_visually_edit() ) {

			HeadwayOption::$group_suffix = '_preview';
			HeadwayLayoutOption::$group_suffix = '_preview';

		}

	}
	
	
	public static function format_layout_id($layout) {
		
		//Create array to analyze last part of layout string
		$fragments = explode('-', $layout);
	
		//If it's a single layout
		if ( strpos($layout, 'single') !== false && is_numeric(end($fragments)) )
			$layout = (int)end($fragments);
			
		//If the layout is numeric, check that it's not the blog index or front page 
		if ( is_numeric($layout) && get_option('page_for_posts') == $layout )
			return 'index';
		elseif ( is_numeric($layout) && get_option('page_on_front') == $layout )
			return 'front_page';
		
		return $layout;
		
	}
	
	
	public static function get($layout = false, $option = null, $group_name = false, $default = null) {
		
		//If there's no option to retrieve, then we have nothing to retrieve.
		if ( $option === null )
			return null;
		
		//If there's no group defined, define it using the default
		if ( !$group_name ) 
			$group_name = self::$default_group;
		
		//Make sure there is a layout to use
		if ( !$layout ) 
			$layout = HeadwayLayout::get_current();
			
		//Format layout ID
		$layout = self::format_layout_id($layout);
			
		$options = get_option('headway_layout_options_' . str_replace('-', '_', $layout) . self::$group_suffix);

		if ( self::$group_suffix && !$options )
			$options = get_option('headway_layout_options_' . str_replace('-', '_', $layout));
							
		//Option does not exist	
		if ( !$options || !isset($options[$group_name][$option]) || !is_array($options) ) 
			return $default;
			
		//Option exists, let's format it
		$data = headway_fix_data_type($options[$group_name][$option]);
			
		return $data;
		
	}
	
	
	public static function set($layout = false, $option = null, $value = null, $group_name = false) {
				
		//If there's no option, we can't set anything.
		if ( $option === null )
			return false;
		
		//If there's no value, there's nothing to set.
		if ( $value === null )
			return false;
		
		//If there's no group defined, define it using the default
		if ( !$group_name ) 
			$group_name = self::$default_group;
		
		//Make sure there is a layout to use
		if ( !$layout ) 
			$layout = HeadwayLayout::get_current();
			
		//Format layout ID
		$layout = self::format_layout_id($layout);
														
		//Handle boolean values
		if ( is_bool($value) )
			$value = ( $value === true ) ? 'true' : 'false';
			
		//Change hyphens to underscores
		$layout = str_replace('-', '_', $layout);
		
		//Retrieve existing options
		$options = get_option('headway_layout_options_' . $layout);
		
		//Get layout options catalog
		$catalog = get_option('headway_layout_options_catalog');
					
		//Make sure layout exists in catalog
		if ( !is_array($catalog) )
			$catalog = array();
		
		if ( !in_array($layout, $catalog) )
			$catalog[] = $layout;
						
		//If options aren't set, make it an array
		if( !is_array($options) ) $options = array($group_name => array());
		
		//Make sure group exists
		if ( !isset($options[$group_name]) )
			$options[$group_name] = array();
		
		//Update data on array
		$options[$group_name][$option] = $value;	
																								
		//Send data to DB	
		update_option('headway_layout_options_' . $layout . self::$group_suffix, $options);

		if ( !self::$group_suffix )
			update_option('headway_layout_options_catalog', $catalog);
					
		return true;
						
		
	}
	
	
	public static function delete($layout, $option = null, $group_name = false) {
		
		//No deleting to be done if we don't have an option to delete
		if ( $option === null )
			return false;
		
		//If there's no group defined, define it using the default
		if ( !$group_name ) 
			$group_name = self::$default_group;
		
		//Make sure there is a layout to use
		if ( !$layout ) 
			$layout = HeadwayLayout::get_current();
			
		//Format layout ID
		$layout = self::format_layout_id($layout);	
				
		//Retrieve options array from DB
		$options = get_option('headway_layout_options_' . str_replace('-', '_', $layout));
			
		//If DB option doesn't exist, make a default array
		if( !is_array($options) ) 
			$options = array();
				
		//Option or group doesn't exist
		if ( !isset($options[$group_name]) || !isset($options[$group_name][$option]) )	
			return false;
			
		//If option exists, delete the sucker
		unset($options[$group_name][$option]);
		
		//If group is empty, delete it too
		if ( count($options[$group_name]) === 0 )
			unset($options[$group_name]);
						
		//If the options array is empty, delete the entire option and remove it from catalog
		if ( count($options) === 0 && !self::$group_suffix ) {
			
			$removal = array($layout);
			$catalog = array_diff(get_option('headway_layout_options_catalog'), $removal);
							
			delete_option('headway_layout_options_' . $layout);
			update_option('headway_layout_options_catalog', $catalog);
							
			return true;				
							
		}
					
		update_option('headway_layout_options_' . $layout . self::$group_suffix, $options);
		
		return true;
			
		
	}

	
}