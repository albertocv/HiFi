<?php
class HeadwayVisualEditorAJAX {
	
	
	/* Saving methods */
	public static function secure_method_save_options() {
		
		$current_layout = headway_post('layout');
		$mode = headway_post('mode');
		
		//Set up options
		parse_str(headway_post('options'), $options);
		
		//Handle triple slash bullshit
		if ( get_magic_quotes_gpc() === 1 )
			$options = array_map('stripslashes_deep', $options);
								
		$blocks = isset($options['blocks']) ? $options['blocks'] : null;
		$options_inputs = isset($options['options']) ? $options['options'] : null;
		$design_editor_inputs = isset($options['design-editor']) ? $options['design-editor'] : null;
		
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
		
		//Handle design editor inputs
		if ( $design_editor_inputs ) {			
			
			//Loop through to get every element and its properties
			foreach ( $design_editor_inputs as $element_id => $element_types ) {
								
				//Loop through element types and get the children meta
				foreach ( $element_types as $element_type => $element_type_metas ) {
					
					//Loop through the metas (ID of state, instance, etc)
					foreach ( $element_type_metas as $element_type_meta => $properties ) {
						
						//Go through properties now
						foreach ( $properties as $property_id => $value ) {
							
							//If the element_type is regular, then use the traditional method
							if ( $element_type == 'regular' )
								HeadwayElementsData::set_property($element_id, $property_id, $value);
							else
								HeadwayElementsData::set_special_element_property($element_id, $element_type, $element_type_meta, $property_id, $value);
							
						}
						
					}
					
				}
				
			}
			
		}

		//Clear out preview options
		HeadwayVisualEditorPreview::remove_preview_options();
		
		//Let's flush the compiler cache just to make things easier
		HeadwayCompiler::flush_cache();
		
		//Allow plugins to perform functions upon visual editor save
		do_action('headway_visual_editor_save');
		
		echo 'success';
		
	}
	
	
	/* Block methods */
	public static function method_get_available_block_id() {

		$block_id_blacklist = headway_post('block_id_blacklist', array());

		echo HeadwayBlocksData::get_available_block_id($block_id_blacklist);

	}


	public static function method_get_available_block_id_batch() {
		
		$block_id_blacklist = headway_post('block_id_blacklist', array());
		$number_of_ids = headway_post('number_of_ids', 10);
		
		if ( !is_numeric($number_of_ids) )
			$number_of_ids = 10;

		$block_ids = array();

		for ( $i = 1; $i <= $number_of_ids; $i++ ) {

			$available_block_id = HeadwayBlocksData::get_available_block_id($block_id_blacklist);

			$block_ids[] = $available_block_id;
			$block_id_blacklist[] = $available_block_id;

		}

		echo json_encode($block_ids);

	}
	
	
	public static function method_get_layout_blocks_in_json() {
		
		$layout = headway_post('layout', false);
		$layout_status = HeadwayLayout::get_status($layout);
		
		if ( $layout_status['customized'] != true )
			return false;
		
		echo json_encode(HeadwayBlocksData::get_blocks_by_layout($layout));
		
	}
	
	
	public static function method_load_block_content() {
		
		$layout = headway_post('layout');
		$block_origin = headway_post('block_origin');
		$block_default = headway_post('block_default', false);
		
		$unsaved_block_settings = headway_post('unsaved_block_settings', false);
		
		/* If the block origin is a string or ID, then get the object from DB. */
		if ( is_numeric($block_origin) || is_string($block_origin) )
			$block = HeadwayBlocksData::get_block($block_origin);
			
		/* Otherwise use the object */
		else
			$block = $block_origin;
									
		/* If the block doesn't exist, then use the default as the origin.  If the default doesn't exist... We're screwed. */
		if ( !$block && $block_default )
			$block = $block_default;
						
		/* If the block settings is an array, merge that into the origin.  But first, make sure the settings exists for the origin. */
		if ( !isset($block['settings']) )
			$block['settings'] = array();
		
		if ( is_array($unsaved_block_settings) )
			$block = headway_array_merge_recursive_simple($block, $unsaved_block_settings);	
			
		/* If the block is set to mirror, then get that block. */
		if ( $mirrored_block = HeadwayBlocksData::is_block_mirrored($block) )
			$block = $mirrored_block;
					
		/* Add a flag into the block so we can check if this is coming from the visual editor. */
		$block['ve-live-content-query'] = true;
		
		/* Show the content */		
		do_action('headway_block_content_' . $block['type'], $block);
		
	}
	
	
	public static function method_load_block_options() {
		
		$layout = headway_post('layout');
		$block_id = headway_post('block_id');
		$unsaved_options = headway_post('unsaved_block_options', array());
	
		$block = HeadwayBlocksData::get_block($block_id);
		
		//If block is new, set the bare basics up
		if ( !$block ) {
			
			$block = array(
				'type' => headway_post('block_type'),
				'new' => true,
				'id' => $block_id,
				'layout' => $layout
			);
		
		}
				
		//Merge unsaved options in
		if ( is_array($unsaved_options) )
			$block['settings'] = is_array(headway_get('settings', $block)) ? array_merge($block['settings'], $unsaved_options) : $unsaved_options;
							
		do_action('headway_block_options_' . $block['type'], $block, $layout);
		
	}
	
	
	/* Box methods */
	public static function method_load_box_ajax_content() {
		
		$layout = headway_post('layout');
		$box_id = headway_post('box_id');
				
		do_action('headway_visual_editor_ajax_box_content_' . $box_id);
		
	}
	
	
	/* Layout methods */
	public static function method_get_layout_name() {
				
		$layout = headway_post('layout');
		
		echo HeadwayLayout::get_name($layout);
		
	}
	
	
	public static function secure_method_revert_layout() {
		
		$layout = headway_post('layout_to_revert');
		
		//Delete the blocks
		HeadwayBlocksData::delete_by_layout($layout);
		
		//Remove the customized flag
		HeadwayLayoutOption::set($layout, 'customized', false);
		
		echo 'success';
		
	}


	/* Design editor methods */
	public static function method_get_element_inputs() {
		
		$element = headway_post('element');
		$special_element_type = headway_post('specialElementType', false);
		$special_element_meta = headway_post('specialElementMeta', false);
		
		$unsaved_values = headway_post('unsavedValues', false);
		
		//Make sure that the library is loaded
		Headway::load('visual-editor/panels/design/element-inputs');
	
		//Display the appropriate inputs and values depending on the element
		HeadwayElementInputs::display($element, $special_element_type, $special_element_meta, $unsaved_values);
	
	}
	
	
	public static function method_get_element_instances() {
		
		$element = headway_post('element');
		
		$instances = HeadwayElementAPI::get_instances($element);
				
		if ( !is_array($instances) )	
			return false;
		
		foreach ( $instances as $instance ) {
			
			//Get the layout so we can append that to the instance name
			$layout = (isset($instance['layout'])) ? '  &ndash;  ' . HeadwayLayout::get_name($instance['layout']) : null;
			
			echo '<option value="' . $instance['id'] . '">' . $instance['name'] . $layout . '</option>' . "\n";
			
		}
		
	}
	
	
	public static function method_get_element_states() {
		
		$element = headway_post('element');
		
		$states = HeadwayElementAPI::get_states($element);
				
		if ( !is_array($states) )	
			return false;
		
		foreach ( $states as $name => $selector ) {
						
			$id = strtolower($name);		
						
			echo '<option value="' . $id . '">' . $name . '</option>' . "\n";
			
		}
		
	}
	

	public static function method_get_inspector_elements() {

		$current_layout = headway_post('layout');
		$all_elements = HeadwayElementAPI::get_all_elements();

		$elements = array();
		$instances = array();
		$states = array();

		/* Assemble the arrays */
		foreach ( $all_elements as $group => $group_elements ) {

			/* Exclude default elements */
			if ( $group == 'default-elements' )
				continue;

			/* Move children elements to $group_elements so array is one level */
			foreach ( $group_elements as $element )
				$group_elements = array_merge($group_elements, headway_get('children', $element, array()));
			
			/* Build arrays */ 
			foreach ( $group_elements as $element ) {

				/* Normal Elements */
				$parent_element = headway_get('parent', $element) ? HeadwayElementAPI::get_element(headway_get('parent', $element)) : null;

				$elements[$element['id']] = array(
					'selector' => $element['selector'],
					'id' => $element['id'],
					'name' => $element['name'],
					'group' => headway_get('group', $element),
					'groupName' => headway_get(headway_get('group', $element), HeadwayElementAPI::get_groups()),
					'parent' => headway_get('id', $parent_element),
					'parentName' => headway_get('name', $parent_element)
				);

				/* Instances */
				foreach ( headway_get('instances', $element, array()) as $instance ) {

					if ( $instance['layout'] != $current_layout )
						continue;

					$instances[$instance['id']] = array(
						'selector' => $instance['selector'],
						'id' => $element['id'],
						'instance' => $instance['id'],
						'name' => $instance['name'],
						'parentName' => $element['name'],
						'groupName' => $elements[$element['id']]['groupName']
					);

				}

				/* States */
				foreach ( headway_get('states', $element, array()) as $state_name => $state )
					$states[$state] = $element['name'] . ' &ndash; ' . $state_name;

			}

		}

		/* Spit it all out */
		echo json_encode(array(
			'elements' => $elements,
			'instances' => $instances,
			'states' => $states,
		));

	}
	

	/* Template methods */
	public static function secure_method_add_template() {
		
		//Get the stuff from DB
		$templates = HeadwayOption::get('list', 'templates', array());
		$last_template_id = HeadwayOption::get('last-id', 'templates', 0);
		
		//Build name
		$id = $last_template_id + 1;
		$template_name = headway_post('template_name') ? headway_post('template_name') : 'Template ' . $id;
		
		//Add to array
		$templates[$id] = $template_name;
		
		//Send array to DB
		HeadwayOption::set('list', $templates, 'templates');
		HeadwayOption::set('last-id', $id, 'templates');
		
		//Send the template ID back to JavaScript so it can be added to the list
		echo json_encode(array('id' => $id, 'name' => $template_name));
		
	}
	
	
	public static function secure_method_delete_template() {
		
		//Retreive templates
		$templates = HeadwayOption::get('list', 'templates', array());
		
		//Unset the deleted ID
		$id = headway_post('template_to_delete');
		
		//Delete template if it exists and send array back to DB
		if ( isset($templates[$id]) ) {
			
			unset($templates[$id]);
			
			//Delete the blocks from the template
			HeadwayBlocksData::delete_by_layout('template-' . $id);
			
			HeadwayOption::set('list', $templates, 'templates');
			
			echo 'success';
			
		} else {
			
			echo 'failure';
			
		}
		
	}
	
	
	public static function secure_method_assign_template() {
		
		$layout = headway_post('layout');
		$template = str_replace('template-', '', headway_post('template'));
		
		//Add the template flag
		HeadwayLayoutOption::set($layout, 'template', $template);
		
		echo HeadwayLayout::get_name('template-' . $template);
		
	}
	
	
	public static function secure_method_remove_template_from_layout() {
		
		$layout = headway_post('layout');
		
		//Remove the template flag
		if ( !HeadwayLayoutOption::set($layout, 'template', false) ) {
			echo 'failure';
			
			return;
		}
		
		if ( HeadwayLayoutOption::get($layout, 'customized', false) === true ) {
			echo 'customized';
			
			return;
		}
			
		echo 'success';
		
	}
	
	
	/* Micellaneous methods */
	public static function method_clear_cache() {
		
		if ( HeadwayCompiler::flush_cache() )
			echo 'success';
		else
			echo 'failure';
		
	}

	
	public static function method_ran_tour() {
		
		$mode = headway_post('mode');

		HeadwayOption::set('ran-tour-' . $mode, true);
		
	}
	
	
	public static function method_change_grid_height() {
		
		$grid_height = headway_post('grid_height');		
		
		//Make sure the grid height is numeric and at least 800px
		if ( !is_numeric($grid_height) || $grid_height < 800 )
			return false;
						
		HeadwayOption::set('grid-height', $grid_height);
		
	}
	
	
	public static function method_get_font_stack() {
				
		$requested_font = headway_post('font');

		echo HeadwayFonts::get_stack($requested_font);
								
	}
	
	
}