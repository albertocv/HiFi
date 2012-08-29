<?php
class HeadwayBlocksData {
	
	
	protected static function schema_blocks_by_id() {
				
	 	return HeadwayOption::get('blocks-by-id', 'blocks', array());
		
	}
	
	
	protected static function schema_blocks_by_type() {
				
		return HeadwayOption::get('blocks-by-type', 'blocks', array());
		
	}
	
	
	protected static function schema_blocks_by_layout() {
				
		return HeadwayOption::get('blocks-by-layout', 'blocks', array());
				
	}
	
	
	protected static function schema_layout_blocks($layout_id) {
				
		return HeadwayLayoutOption::get($layout_id, 'blocks', false, array());
		
	}
	
	
	public static function add_block($layout_id, $args) {
		
		//Lots of defaults here.			
		$defaults = array(
			'type' => null,
			
			'position' => array(
				'top' => 0,
				'left' => 0
			),
			
			'dimensions' => array(
				'width' => 0,
				'height' => 0
			),
			
			'settings' => array()
		);
		
		//Merge defaults with arguments
		$block_settings = array_merge($defaults, $args);
		
		//Check requirements for block
		if ( $block_settings['type'] === $defaults['type'] )
			return false;
		
		//Figure out block ID
		$block_id = ( isset($block_settings['id']) && !self::block_exists($block_settings['id']) ) ? $block_settings['id'] : self::get_available_block_id();
		
		//Re-add block ID to array
		$block_settings['id'] = $block_id;
		
		//Get existing blocks from layout
		$layout_blocks = self::schema_layout_blocks($layout_id);
				
		//Fetch the big boy option that all blocks belong to
		$blocks_by_type = self::schema_blocks_by_type();		
		$blocks_by_id = self::schema_blocks_by_id();		
		$blocks_by_layout = self::schema_blocks_by_layout();		
		
		//Add the block to the layout's block array
		$layout_blocks[$block_id] = $block_settings;
		
		//Add block to global array(s)
		$blocks_by_type[$block_settings['type']][$block_id] = $layout_id;
		$blocks_by_id[$block_id] = array('layout' => $layout_id, 'type' => $block_settings['type']);
		$blocks_by_layout[$layout_id][$block_id] = true;
		
		//Update database
		HeadwayLayoutOption::set($layout_id, 'blocks', $layout_blocks);
		
		HeadwayOption::set('blocks-by-type', $blocks_by_type, 'blocks');
		HeadwayOption::set('blocks-by-id', $blocks_by_id, 'blocks');
		HeadwayOption::set('blocks-by-layout', $blocks_by_layout, 'blocks');
		
		//All done.  Spit back ID of newly created block.
		return $block_id;
		
	}
	
	
	public static function update_block($layout_id, $block_id, $args) {
		
		//Get existing blocks layout
		$blocks_by_type = self::schema_blocks_by_type();		
		$blocks_by_id = self::schema_blocks_by_id();
		
		$layout_blocks = self::schema_layout_blocks($layout_id);
		
		//If block doesn't exist, go false.
		if ( !isset($layout_blocks[$block_id]) )
			return false;
		
		//Pull out block settings from block we're gonna update.
		$old_block = $layout_blocks[$block_id];
		$updated_block = array_merge($old_block, $args);
		
		//Merge new block settings with old and update array
		$layout_blocks[$block_id] = $updated_block;
		
		//Since we're not sure if the type is being updated, we'll update it anyway for blocks-by-type and blocks-by-id
		if ( isset($blocks_by_type[$old_block['type']][$block_id]) )
			unset($blocks_by_type[$old_block['type']][$block_id]);
			
		$blocks_by_type[$updated_block['type']][$block_id] = $layout_id;
		
		$blocks_by_id[$block_id]['type'] = $updated_block['type'];
		
		//Push new arrays to DB
		HeadwayLayoutOption::set($layout_id, 'blocks', $layout_blocks);
		
		HeadwayOption::set('blocks-by-type', $blocks_by_type, 'blocks');
		HeadwayOption::set('blocks-by-id', $blocks_by_id, 'blocks');
		
		//Everything OK
		return true;
		
	}


	public static function delete_block($layout_id, $block_id) {
		
		//Fetch options from DB
		$layout_blocks = self::schema_layout_blocks($layout_id);
		
		$blocks_by_type = self::schema_blocks_by_type();		
		$blocks_by_id = self::schema_blocks_by_id();
		$blocks_by_layout = self::schema_blocks_by_layout();
		
		//Find anomolies (going to ignore blocks by type array here)
		if ( !isset($layout_blocks[$block_id]) )
			return false;
			
		//Get block type
		$block_type = $blocks_by_id[$block_id]['type'];	
		
		//Strip block out of arrays
		unset($layout_blocks[$block_id]);
		
		unset($blocks_by_type[$block_type][$block_id]);
		unset($blocks_by_id[$block_id]);
		unset($blocks_by_layout[$layout_id][$block_id]);
		
		if ( count($blocks_by_type[$block_type]) === 0)
			unset($blocks_by_type[$block_type]);
			
		if ( count($blocks_by_layout[$layout_id]) === 0)
			unset($blocks_by_layout[$layout_id]);
		
		//Update database
		HeadwayLayoutOption::set($layout_id, 'blocks', $layout_blocks);
		
		HeadwayOption::set('blocks-by-type', $blocks_by_type, 'blocks');
		HeadwayOption::set('blocks-by-id', $blocks_by_id, 'blocks');
		HeadwayOption::set('blocks-by-layout', $blocks_by_layout, 'blocks');
		
		//Everything successful
		return true;
		
	}
	
	
	public static function delete_by_layout($layout_id) {
		
		//This function is only used when the grid is active.
		if ( !current_theme_supports('headway-grid') )
			return false;
		
		//Fetch options from DB
		$layout_blocks = self::schema_layout_blocks($layout_id);
		
		$blocks_by_type = self::schema_blocks_by_type();		
		$blocks_by_id = self::schema_blocks_by_id();
		$blocks_by_layout = self::schema_blocks_by_layout();
		
		foreach($layout_blocks as $block_id => $options) {
			
			//Strip block out of arrays
			unset($layout_blocks[$block_id]);

			unset($blocks_by_type[$options['type']][$block_id]);				
			unset($blocks_by_id[$block_id]);
			unset($blocks_by_layout[$layout_id][$block_id]);
			
			if ( count($blocks_by_type[$options['type']]) === 0)
				unset($blocks_by_type[$options['type']]);
			
			if ( count($blocks_by_layout[$layout_id]) === 0)
				unset($blocks_by_layout[$layout_id]);
			
		}
		
		//Update database
		HeadwayLayoutOption::set($layout_id, 'blocks', $layout_blocks);
		
		HeadwayOption::set('blocks-by-type', $blocks_by_type, 'blocks');
		HeadwayOption::set('blocks-by-id', $blocks_by_id, 'blocks');
		HeadwayOption::set('blocks-by-layout', $blocks_by_layout, 'blocks');
		
		//Everything successful
		return true;
		
		
	}
	
	
	public static function get_block($block, $use_mirrored = false) {
		
		/* If a block array is supplied, make sure it is legitimate. */
		if ( is_array($block) ) {
			
			if ( (!isset($block['id']) || !self::block_exists($block['id'])) && !headway_get('new', $block, false) )
				return null;
				
		/* Fetch the block based off of ID */
		} elseif ( is_numeric($block) ) {
			
			//Get the block from blocks-by-id to get the layout
			$blocks_by_id = self::schema_blocks_by_id();

			//If block doesn't exist, go false
			if ( !isset($blocks_by_id[$block]) )
				return false;

			//Retrieve all blocks from layout
			$layout_blocks = self::get_blocks_by_layout(headway_get('layout', $blocks_by_id[$block]));

			//Make sure that the block still exists once again on the layout.
			if ( !isset($layout_blocks[$block]) )
				return false;

			$block = $layout_blocks[$block];
		
		/* No valid argument provided. */	
		} else {
			
			return null;
			
		}
		
		/* Fetch the mirrored block if $use_mirrored is true */
		if ( $use_mirrored === true && $mirrored_block = self::is_block_mirrored($block) )
			$block = $mirrored_block;
				
		return $block;
		
	}
	
	
	public static function get_blocks_by_layout($layout_id) {
				
		//Retrieve all blocks from layout
		$layout_blocks = self::schema_layout_blocks($layout_id);
		
		//Add the layout ID to the block just to have it.
		foreach ( $layout_blocks as $block_id => $block )
			$layout_blocks[$block_id]['layout'] = $layout_id;
					
		return $layout_blocks;
				
	}
	
	
	public static function get_blocks_by_type($type = false) {
				
		//Get all blocks from DB
		$blocks_by_type = self::schema_blocks_by_type();
		
		//If no type, then return it all
		if ( !$type )
			return $blocks_by_type;
			
		return ( isset($blocks_by_type[$type]) ) ? $blocks_by_type[$type] : null;
		
	}
	
	
	public static function get_all_blocks() {
				
		//Get a list of layouts with blocks
		if ( !($block_by_layout = self::schema_blocks_by_layout()) )
			return false;
		
		$blocks = array();
				
		//Go through and get every layout then get the blocks for that layout and add them to the $blocks array
		foreach ( $block_by_layout as $layout => $unused_block_ids ) {
			
			$added_blocks = self::get_blocks_by_layout($layout);
			
			//Loop through the blocks and put in the layout ID
			foreach ( $added_blocks as $block_id => $block )
				$added_blocks[$block_id]['layout'] = $layout;
			
			//Add blocks to existing array
			$blocks = array_merge($blocks, $added_blocks);
			
		}
		
		return $blocks;
		
	}
	
	
	public static function get_block_name($block) {
		
		$block = self::get_block($block);
	
		//Create the default name by using the block type and ID
		$default_name = HeadwayBlocks::block_type_nice($block['type']) . ' #' . $block['id'];
		
		return headway_get('alias', $block['settings'], $default_name);
		
	}
	
	
	public static function get_block_width($block) {
		
		$block = self::get_block($block);
			
		$block_grid_width = headway_get('width', $block['dimensions'], null);
		
		if ( $block_grid_width === null )
			return null;
			
		return ( $block_grid_width * (HeadwayGrid::$column_width + HeadwayGrid::$gutter_width) ) - HeadwayGrid::$gutter_width;
		
	}
	
	
	public static function get_block_height($block) {
		
		$block = self::get_block($block);
			
		$block_grid_height = headway_get('height', $block['dimensions'], null);
		
		if ( $block_grid_height === null )
			return null;
			
		return $block_grid_height;
		
	}
	

	public static function get_block_setting($block, $setting, $default = null) {
		
		$block = self::get_block($block);
			
		//No block, no settings
		if ( !$block )
			return $default;
			
		if ( !isset($block['settings'][$setting]) )
			return $default;
			
		return headway_fix_data_type($block['settings'][$setting]);
		
	}
	
	
	public static function get_available_block_id($block_id_blacklist = array()) {
		
		$id = 1;
		
		while ( self::block_exists($id) || in_array((string)$id, $block_id_blacklist) ) {
			
			$id++;
			
		}
		
		return $id;
		
	}

	
	public static function is_block_mirrored($block, $return_block_id = false) {
		
		$block = self::get_block($block);
								
		if ( $block && $mirrored_block_id = headway_get('mirror-block', $block['settings']) ) {

			$mirrored_block = self::get_block($mirrored_block_id);

			if ( !$mirrored_block || headway_get('mirror-block', $mirrored_block['settings']) )
				return false;
				
			return $return_block_id ? $mirrored_block['id'] : $mirrored_block;
			
		}
		
		return false;
		
	}

	
	public static function block_exists($id) {
		
		$blocks_by_id = self::schema_blocks_by_id();
		
		return isset($blocks_by_id[$id]);
		
	}
	
	
}