<?php
class HeadwayVisualEditorPreview {


	public static function init() {

		if ( !headway_get('preview') || !HeadwayCapabilities::can_user_visually_edit() )
			return;

		add_action('init', array(__CLASS__, 'remove_preview_options'));
		add_action('init', array(__CLASS__, 'save_preview_options'));

	}


	public static function remove_preview_options() {

		if ( !HeadwayCapabilities::can_user_visually_edit() )
			return;

		//Fetch all options in wp_options and remove the preview-specific options
		foreach ( wp_load_alloptions() as $option => $option_value ) {
						
			//This if statement is incredibly important and must not be tampered with and needs to be triple-checked if changed.
			if ( preg_match('/^headway_(.*)?_preview$/', $option) && strpos($option, 'headway_') === 0 && strpos($option, '_preview') !== false ) {
				delete_option($option);
			}
			
		}

	}


	public static function save_preview_options() {

		$current_layout = HeadwayLayout::get_current();
		$mode = 'grid';

		//Set up options
		parse_str(headway_get('unsaved'), $options);
		
		//Handle triple slash bullshit
		if ( get_magic_quotes_gpc() === 1 )
			$options = array_map('stripslashes_deep', $options);
								
		$blocks = isset($options['blocks']) ? $options['blocks'] : null;
		$options_inputs = isset($options['options']) ? $options['options'] : null;
		
		//Set the current layout to customized if it's the grid mode
		if ( $mode == 'grid' )
			HeadwayLayoutOption::set($current_layout, 'customized', true); 
						
		//Handle blocks
		if ( $blocks ) {
			
			foreach ( $blocks as $id => $methods ) {
			
				foreach ( $methods as $method => $value ) {
				
					switch ( $method ) {
					
						case 'new':
					
							if ( HeadwayBlocksData::get_block($id) )
								continue;
								
							$dimensions = explode(',', $blocks[$id]['dimensions']);	
							$position = explode(',', $blocks[$id]['position']);		
							
							$settings = isset($blocks[$id]['settings']) ? $blocks[$id]['settings'] : array();
								
							$args = array(
								'id' => $id,
								'type' => $value,
								'position' => array(
									'left' => $position[0],
									'top' => $position[1]
								),
								'dimensions' => array(
									'width' => $dimensions[0],
									'height' => $dimensions[1]
								),
								'settings' => $settings
							);
								
							HeadwayBlocksData::add_block($current_layout, $args);
					
						break;
					
						case 'delete':
					
							HeadwayBlocksData::delete_block($current_layout, $id);
					
						break;
					
						case 'dimensions':
						
							$dimensions = explode(',', $value);	
						
							$args = array(
								'dimensions' => array(
									'width' => $dimensions[0],
									'height' => $dimensions[1]
								)
							);
							
							HeadwayBlocksData::update_block($current_layout, $id, $args);
														
						break;
					
						case 'position':
						
							$position = explode(',', $value);	
						
							$args = array(
								'position' => array(
									'left' => $position[0],
									'top' => $position[1]
								)
							);
							
							HeadwayBlocksData::update_block($current_layout, $id, $args);
					
						break;
					
						case 'settings':
													
							//Retrieve all blocks from layout
							$layout_blocks = HeadwayBlocksData::get_blocks_by_layout($current_layout);
							
							//Get the block from the layout
							$block = headway_get($id, $layout_blocks);
							
							//If block doesn't exist, we can't do anything.
							if ( !$block )
								continue;
								
							//If there aren't any options, then don't do anything either	
							if ( !is_array($value) || count($value) === 0 )
								continue;	
								
							$block['settings'] = array_merge($block['settings'], $value);
							
							HeadwayBlocksData::update_block($current_layout, $id, $block);
						
						break;
					
					}
				
				}
			
			}
			
		}
		
		//Handle options
		if ( $options_inputs ) {
			
			foreach ( $options_inputs as $group => $options ) {

				foreach ( $options as $option => $value ) {							
					HeadwayOption::set($option, $value, $group);
				}

			}
			
		}
		
	}


}