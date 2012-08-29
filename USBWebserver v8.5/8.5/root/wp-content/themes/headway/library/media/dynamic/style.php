<?php
class HeadwayDynamicStyle {
	
	
	static function design_editor() {
		
		$elements = HeadwayElementsData::get_all_elements();
		
		$return = "/* DESIGN EDITOR STYLING */\n";
		
		foreach ( $elements as $element_id => $element_options ) {
			
			$element = HeadwayElementAPI::get_element($element_id);
			$selector = $element['selector'];
			
			//Continue to next element if the element/selector does not exist
			if ( !isset($selector) || $selector == false )
				continue;
			
			/* Regular Element */
			if ( isset($element_options['properties']) )
				$return .= HeadwayElementProperties::output_css($selector, $element_options['properties']);
			
			/* Layout-specific elements */
			if ( isset($element_options['special-element-layout']) && is_array($element_options['special-element-layout']) ) {
				
				//Handle every layout
				foreach ( $element_options['special-element-layout'] as $layout => $layout_properties ) {
					
					//Get the selector for the layout
					$layout_element_selector = 'body.layout-using-' . $layout . ' ' . $selector;
					
					//Since the layout selectors are targeted by the body element, we can't do anything body to style the actual body element.  Let's fix that.
					if ( $selector == 'body' )
						$layout_element_selector = str_replace(' body', '', $layout_element_selector); //The space inside str_replace is completely intentional.
					
					$return .= HeadwayElementProperties::output_css($layout_element_selector, $layout_properties);
					
				}
				
			}
			
			/* Instances */
			if ( isset($element_options['special-element-instance']) && is_array($element_options['special-element-instance']) ) {
				
				//Handle every instance
				foreach ( $element_options['special-element-instance'] as $instance => $instance_properties ) {
					
					//Make sure the instance exists
					if ( !isset($element['instances'][$instance]) )
						continue;
					
					//Get the selector for the instance
					$instance_selector = $element['instances'][$instance]['selector'];
					
					$return .= HeadwayElementProperties::output_css($instance_selector, $instance_properties);
					
				}
				
			}

			/* States */
			if ( isset($element_options['special-element-state']) && is_array($element_options['special-element-state']) ) {
				
				//Handle every instance
				foreach ( $element_options['special-element-state'] as $state => $state_properties ) {
					
					//Make sure the state exists
					if ( !isset($element['states'][ucwords($state)]) )
						continue;
					
					//Get the selector for the layout
					$state_selector = $element['states'][ucwords($state)];
					
					$return .= HeadwayElementProperties::output_css($state_selector, $state_properties);
					
				}
				
			}

		} //End main $elements foreach
		
		return $return;
		
	}
	
	
	static function grid() {
		
		return !HeadwayResponsiveGrid::is_enabled() ? self::fixed_grid() : self::responsive_grid();
		
	}
	
	
		static function fixed_grid() {
				
			$grid_number = HeadwayGrid::$columns;
			$column_width = HeadwayGrid::$column_width;
			$gutter_width = HeadwayGrid::$gutter_width;
			$block_bottom_margin = HeadwayGrid::$block_bottom_margin;
			
			$grid_wrapper_width = ($column_width * $grid_number) + ($grid_number * $gutter_width);
						
			/* Block bottom margins (and start the $return variable) */
			$return = '.block { margin-bottom: ' . ($block_bottom_margin) . 'px; }';
		
			/* Column left margins */
			$return .= '.column { margin-left: ' . ($gutter_width) . 'px; }';
		
			/* Widths and Lefts */
			for ( $i = 1; $i <= $grid_number; $i++ ) {
			
				/* Vars */
				$grid_width = $column_width * $i + (($i - 1) * $gutter_width);
				$grid_left_margin = (($column_width + $gutter_width) * $i) + $gutter_width;
		
				$return .= '.grid-width-' . $i . ' { width:' . ($grid_width) . 'px; }';
				$return .= '.grid-left-' . $i . ' { margin-left: ' . ($grid_left_margin) . 'px; }';
		
				/**
				 * If it's the first column in a row and the column doesn't start on the far left,
				 * then the additional gutter doesn't have to be taken into consideration
				 **/
				$return .= '.column-1.grid-left-' . $i . ' { margin-left: ' . ($grid_left_margin - $gutter_width) . 'px; }';				

			}
				
			return $return;
		
		}
	
		
		static function responsive_grid() {
			
			$round_precision = 9;

			$grid_number = HeadwayGrid::$columns;
			$column_width = HeadwayGrid::$column_width;
			$gutter_width = HeadwayGrid::$gutter_width;
			$block_bottom_margin = HeadwayGrid::$block_bottom_margin;
			
			$grid_wrapper_width = ($column_width * $grid_number) + ($grid_number * $gutter_width);
			
			$resp_width_ratio = ($column_width * $grid_number) / $grid_wrapper_width;
			$resp_gutter_ratio = ($gutter_width * $grid_number) / $grid_wrapper_width;
			$resp_single_column_width = (100 / $grid_number) * $resp_width_ratio;
			$resp_single_column_margin = (100 / $grid_number) * $resp_gutter_ratio;
						
			/* Block bottom margins (and start the $return variable) */
			$return = '.block { margin-bottom: ' . ($block_bottom_margin) . 'px; }';
			
			$return .= '.column { margin-left: ' . round($resp_single_column_margin, $round_precision) . '%; }';

			for ( $i = 1; $i <= $grid_number; $i++ ) {
								
				/* Vars */
				$resp_grid_width = ($resp_single_column_width * $i) + ($i * $resp_single_column_margin);
				$resp_grid_left_margin = (($resp_single_column_width + $resp_single_column_margin) * $i) + $resp_single_column_margin;
			
				$sub_column_single_width = ($resp_single_column_width / $resp_grid_width) * 100;
				$sub_column_single_margin = ($resp_single_column_margin / $resp_grid_width) * 100;
				
				/* Output */
				$return .= '.grid-width-' . $i . ' { width: ' . round($resp_grid_width - $resp_single_column_margin, $round_precision) . '%; }';					
				$return .= '.grid-width-' . $i . '.column-1 { width: ' . round($resp_grid_width, $round_precision) . '%; }';					
				
				if ( $i < 24 ) {
					
					$return .= '.grid-left-' . $i . ' { margin-left: ' . round($resp_grid_left_margin, $round_precision) . '%; }';
					$return .= '.grid-left-' . $i . '.column-1 { margin-left: ' . round($resp_grid_left_margin - $resp_single_column_margin, $round_precision) . '%; }';
					
				}
				
				/* Responsive Sub Columns */
				$return .= '.grid-width-' . $i . ' .sub-column { margin-left: ' . round($sub_column_single_margin, $round_precision) . '%; }';
				
				for ( $sub_column_i = 1; $sub_column_i < $i; $sub_column_i++ ) {
										
					$sub_column_width = ($sub_column_single_width * $sub_column_i) + ($sub_column_i * $sub_column_single_margin);
					$sub_column_margin = (($sub_column_single_width + $sub_column_single_margin) * $sub_column_i) + $sub_column_single_margin;
				
					$return .= '.grid-width-' . $i . ' .sub-column.grid-width-' . $sub_column_i . ' { width: ' . round($sub_column_width - $sub_column_single_margin, $round_precision) . '%; }';
					$return .= '.grid-width-' . $i . ' .sub-column.grid-width-' . $sub_column_i . '.column-1 { width: ' . round($sub_column_width, $round_precision) . '%; }';
					
					$return .= '.grid-width-' . $i . ' .sub-column.grid-left-' . $sub_column_i . ' { margin-left: ' . round($sub_column_margin, $round_precision) . '%; }';
					$return .= '.grid-width-' . $i . ' .sub-column.grid-left-' . $sub_column_i . '.column-1 { margin-left: ' . round($sub_column_margin - $sub_column_single_margin, $round_precision) . '%; }';
					
				}
								
			}
			
			return $return;
						
		}
		
		
		static function visual_editor_grid() {
			
			$grid_number = HeadwayGrid::$columns;
			$column_width = HeadwayGrid::$column_width;
			$gutter_width = HeadwayGrid::$gutter_width;

			$grid_height = HeadwayOption::get('grid-height', false, 1500);
			
			/* Little grid lines in VE ... Subtract two off for the border-right and border-left on the grid column */
			$left_margin = ceil(($gutter_width / 2) - 1);
			$right_margin = floor(($gutter_width / 2) - 1);

			$return = 'div.grid-container { height: ' . $grid_height . 'px; }';		
			$return .= 'div#grid div.grid-column { height: ' . $grid_height . 'px; margin: 0 ' . $right_margin . 'px 0 ' . $left_margin . 'px; }';
			
			/* Widths and Lefts */
			for ( $i = 1; $i <= $grid_number; $i++ ) {
				
				/* Visual Editor Grid Mode */
				$return .= '.grid-width-' . $i . '  { width: ' . ($column_width * $i + (($i - 1) * $gutter_width)) . 'px; }';			
				$return .= '.grid-left-' . $i . '  { left: ' . (($column_width + $gutter_width) * $i) . 'px; }';
				
			}
			
			/* Set width for container when in layout mode to make sure the draggable works properly. */
			$return .= 'div.grid-container { width: ' . (HeadwayGrid::get_grid_width() + 1) . 'px; }';
			
			return $return;
			
		}
		
		
		static function block_heights() {

			if ( !($blocks = HeadwayBlocksData::get_all_blocks()) )
				return false;

			$return = '';

			//Retrieve the blocks so we can check if the block type is fixed or fluid height
			$block_types = HeadwayBlocks::get_block_types();

			foreach ( $blocks as $block ) {

				//If it's a fluid block (which blocks ARE by default), then we need to use min-height.  Otherwise, if it's fixed, we use height.
				if ( headway_get('fixed-height', headway_get($block['type'], $block_types), false) !== true )
					$return .= '#block-' . $block['id'] . ' { min-height: ' . $block['dimensions']['height'] . 'px; }';
				else
					$return .= '#block-' . $block['id'] . ' { height: ' . $block['dimensions']['height'] . 'px; }';

			}

			return $return;

		}
	
	
	static function wrapper() {
		
		$grid_number = HeadwayGrid::$columns;
		$column_width = HeadwayGrid::$column_width;
		$gutter_width = HeadwayGrid::$gutter_width;
		$grid_width = HeadwayGrid::get_grid_width();
		
		$top = HeadwayOption::get('wrapper-top-margin', 'general', 30) . 'px';
		$bottom = HeadwayOption::get('wrapper-bottom-margin', 'general', 30) . 'px';

		/* Fixed */
		$return = 'div.wrapper {
			width: ' . $grid_width . 'px;
			margin: ' . $top . ' auto ' . $bottom . ';
		}';
		
		/* Responsive */
		if ( HeadwayResponsiveGrid::is_enabled() )
			$return .= 'div.wrapper.responsive-grid {
				width: auto;
				max-width: ' . $grid_width . 'px;
			}';
		
		return $return;
		
	}
	
	
	static function live_css() {
		
		if ( headway_get('visual-editor-open') )
			return null;
		
		return HeadwayOption::get('live-css');
		
	}
	
}