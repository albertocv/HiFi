<?php
class HeadwayMaintenance {
	
	
	/**
	 * Over time, there may be issues to be corrected between updates or naming conventions to be changed between updates.
	 * All of that will be processed here.
	 **/
	public static function db_upgrade($db_version) {
		
		/* Pre-3.0.3 */
			if ( version_compare($db_version, '3.0.3', '<') ) {
				
				self::fix_serialization_in_db();
				self::repair_blocks();
				
			}

		/**
		 * Pre-3.2.3
		 * 
		 * Change the old wrapper-horizontal-padding and wrapper-vertical-padding to design editor values
		 **/
			if ( version_compare($db_version, '3.2.3', '<') ) {

				$horizontal_padding = HeadwayOption::get('wrapper-horizontal-padding', 'general', 15);
				$vertical_padding = HeadwayOption::get('wrapper-vertical-padding', 'general', 15);

				HeadwayElementsData::set_property('wrapper', 'padding-top', $vertical_padding, 'structure');
				HeadwayElementsData::set_property('wrapper', 'padding-bottom', $vertical_padding, 'structure');

				HeadwayElementsData::set_property('wrapper', 'padding-left', $horizontal_padding, 'structure');
				HeadwayElementsData::set_property('wrapper', 'padding-right', $horizontal_padding, 'structure');

			}

		/* Flush the cache upon update */
		HeadwayCompiler::flush_cache();
		
		/* Update the version here. */
		$headway_settings = get_option('headway', array('version' => 0));
		$headway_settings['version'] = HEADWAY_VERSION;

		update_option('headway', $headway_settings);
		
		return true;
		
	}
	
	
	/**
	 * This will remove all of the funky serialized strings that were other serialized strings in the database.
	 * 
	 * The main reason for fixing this was to insure compatibility with BackupBuddy migrations.
	 **/
	public static function fix_serialization_in_db() {
		
		//Fetch all options in wp_options and fix the Headway-specific options
		foreach ( wp_load_alloptions() as $option => $option_value ) {
						
			//Make sure the option is one to be removed.  
			//This if statement is incredibly important and must not be tampered with and needs to be triple-checked if changed.
			if ( strpos($option, 'headway_option_') === false && strpos($option, 'headway_layout_options_') === false )
				continue;
							
			//If the option isn't an array for some reason, skip it.	
			if ( !is_serialized($option_value) )
				continue;
				
			$option_value = unserialize($option_value);
							
			$fixed_option_value = array_map(array(__CLASS__, 'fix_serialization_in_db_callback'), $option_value);
			
			update_option($option, $fixed_option_value);
			
		}
		
		return true;
		
	}
	
	
		/**
		 * Used in conjunction with the method above.  This is the callback for the array_map reference.
		 * 
		 * Note: The is a self-referencing/looping function.
		 **/
		public static function fix_serialization_in_db_callback($value) {
		
			//Unserialized the serialized strings when it loops back into this function
			if ( is_serialized($value) )
				return unserialize($value);
		
			//Handle arrays	
			if ( is_array($value) )
				return array_map(array(__CLASS__, 'fix_serialization_in_db_callback'), $value);
		
			return $value;
		
		}
	
	
	/**
	 * For some reason, the 'blocks-by-id', 'blocks-by-type', and 'blocks-by-layout' options become blank.  This will restore them.
	 **/
	public static function repair_blocks() {
				
		$catalog = get_option('headway_layout_options_catalog');
		
		$top_level_layouts = array(
			'index',
			'front_page',
			'single',
			'archive',
			'four04'
		);
		
		//If the catalog doesn't even exist, then this function doesn't need to be ran.  (i.g. new installation)
		if ( !$catalog || !is_array($catalog) )
			return false;
			
		//If the catalog doesn't have any top level layout in it (only templates), then do not run this at all.
		if ( array_diff($top_level_layouts, $catalog) === $top_level_layouts )
			return false;
		
		$blocks_by_id = array();
		$blocks_by_type = array();
		$blocks_by_layout = array();
		
		foreach ( $catalog as $layout ) {
			
			$layout_options = get_option('headway_layout_options_' . $layout);		
						
			//If there are no blocks, then skip the layout
			if ( !isset($layout_options['general']['blocks']) || !is_array($layout_options['general']['blocks']) )
				continue;
								
			$layout_blocks = $layout_options['general']['blocks'];
										
			//If the layout is a template, then skip these two conditionals
			if ( strpos($layout, 'template_') === false ) {
								
				//If the layout doesn't have any blocks, then remove the customized flag if it exists.			
				if ( !isset($layout_blocks) || !is_array($layout_blocks) || count($layout_blocks) === 0 ) {

					HeadwayLayoutOption::delete($layout, 'customized');

					continue;

				}

				//If the layout isn't customized and doesn't have a template assigned, 
				//then nuke those blocks from the layout options and do not include them in the main block options
				if ( 
					(!isset($layout_options['general']['customized']) || $layout_options['general']['customized'] !== 'true')
					&& (!isset($layout_options['general']['template']) || $layout_options['general']['template'] === 'false')
				) {

					HeadwayLayoutOption::delete($layout, 'blocks');

					continue;

				}
				
			}
			
			foreach ( $layout_blocks as $block_id => $block ) {
								
				/* Blocks by ID */
				$blocks_by_id[$block['id']] = array(
					'layout' => $layout,
					'type' => $block['type']
				);
				
				/* Blocks by type */
				if ( !isset($blocks_by_type[$block['type']]) )
					$blocks_by_type[$block['type']] = array();
				
				$blocks_by_type[$block['type']][$block['id']] = $layout;
				
				/* Blocks by layout */
				if ( !isset($blocks_by_layout[$layout]) )
					$blocks_by_layout[$layout] = array();
					
				$blocks_by_layout[$layout][$block['id']] = true;
				
			}
						
		}
		
		HeadwayOption::set('blocks-by-type', $blocks_by_type, 'blocks');
		HeadwayOption::set('blocks-by-id', $blocks_by_id, 'blocks');
		HeadwayOption::set('blocks-by-layout', $blocks_by_layout, 'blocks');
				
		return true;		
				
	}	
	
	
}