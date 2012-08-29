<?php
function headway_register_visual_editor_panel($class) {

	add_action('headway_visual_editor_init', create_function('', 'return headway_register_visual_editor_panel_callback(\'' . $class . '\');'), 999);
	
}


function headway_register_visual_editor_panel_callback($class) {

	if ( !class_exists($class) )
		return new WP_Error('panel_class_does_not_exist', __('Error: The panel class being registered does not exist.', 'headway'), $class);
	
	$panel = new $class();
	$panel->register();
	
	return true;
	
}


abstract class HeadwayVisualEditorPanelAPI {
	
	
	/**
	 *	Slug/ID of panel.  Will be used for HTML IDs and whatnot.
	 **/
	public $id;
	
	
	/**
	 * Name of panel.  This will be shown in the tabs.
	 **/
	public $name;
	
	
	/**
	 * Sub tabs.  This is not always used.
	 **/
	public $tabs;
	
	
	/**
	 * Inputs.  This is not always used.
	 **/
	public $inputs;
	
	
	/**
	 * Which mode to display the panel on.
	 **/
	public $mode;
	
	
	/**
	 * Which options group to save in by default
	 **/
	public $options_group = 'general';
	
	
	/** 
	 * Define whether or not this panel is for a block or not
	 **/
	public $is_block = false;
	
	
	/**
	 * If it's a block, then we need to also have ID of block
	 **/
	public $block_id = null;
	
	
	
	/**
	 * Register the panel.
	 * 
	 * @param string Name of panel to be displayed
	 * @param string ID of panel for HTML and options
	 **/
	public function register() {
		
		$mode = HeadwayVisualEditor::get_current_mode();
		
		if ( strtolower($this->mode) !== strtolower($mode) )
			return false;
		
		/* Since there is a message that's displayed if there is no panel content, we have to tell it not to display the message now that
		 * we're registering a panel */
		remove_action('headway_visual_editor_panel_top', array('HeadwayVisualEditorDisplay', 'add_default_panel_link'));
		remove_action('headway_visual_editor_content', array('HeadwayVisualEditorDisplay', 'add_default_panel'));
			
		add_action('headway_visual_editor_panel_top', array($this, 'panel_link'));
		add_action('headway_visual_editor_content', array($this, 'build_panel'));
					
	}
	
	
	public function modify_arguments($args = false) {
		
		//Allow developers to modify the properties of the class and use functions since doing a property 
		//outside of a function will not allow you to.
		
	}
	
	
	public function parse_function_args($array) {
		
		if ( !is_array($array) || count($array) === 0 )
			return $array;
			
		foreach ( $array as $key => $value ) {
			
			if ( !is_string($value) )
				continue;
			
			//Check if it's a function
			if ( preg_match("/^[a-z0-9_]*(\(\))$/", $value) ) {				
				$array[$key] = call_user_func(array($this, str_replace('()', '', $value)));
			} else {
				continue;
			}
			
		}
		
		return $array;
		
	}
	
	
	public function panel_link() {
		
		echo '<li><a href="#' . $this->id . '-tab">' . $this->name . '</a></li>';
		
	}
	
	
	public function build_panel($id) {
		
		$class = ($this->tabs) ? ' sub-tab' : null;
			
		echo '<div id="' . $this->id . '-tab" class="panel' . $class . '">';
		
			$this->panel_content();
		
		echo '</div>';
					
	}
	
	
	public function panel_content($args = false) {

		//Allow developers to modify the properties of the class and use functions since doing a property 
		//outside of a function will not allow you to.
		$this->modify_arguments($args);
		
		if ( $this->tabs && $this->inputs ) {
			
			echo '<ul class="sub-tabs">';
			
				foreach ($this->tabs as $id => $name) {
					
					echo '<li id="sub-tab-' . $id . '"><a href="#sub-tab-' . $id . '-content">' . $name . '</a></li>';
					
				}
			
			echo '</ul>';
			
			echo '<div class="sub-tabs-content-container">';
			
			foreach ($this->tabs as $id => $name) {
				
				echo '<div class="sub-tabs-content" id="sub-tab-' . $id . '-content">';
					
					//Display notice for tab if one exists.
					if ( isset($this->tab_notices[$id]) )
						echo '<p class="sub-tab-notice">' . $this->tab_notices[$id] . '</p>';
				
					$this->sub_tab_content($id, $name);
				
				echo '</div><!-- div#sub-tab-' . $id . '-content -->';
				
			}
			
			echo '</div><!-- .sub-tabs-content-container -->';
						
		}
		
	}
	
	
	public function sub_tab_content($id, $name = false) {
		
		$this->create_inputs($id);
				
	}
	
	
	public function create_inputs($tab) {
		
		$inputs = $this->inputs;
		
		if ( isset($inputs) && is_array($inputs) ) {

			if ( !isset($inputs[$tab]) || !is_array($inputs[$tab]) )
				return new WP_Error('panel_no_inputs', __('There are no inputs registered for this tab.', 'headway'), $tab);

			foreach($inputs[$tab] as $name => $input) {
				
				//Fill defaults
				$defaults = array(
					'tooltip' => false,
					'default' => false,
					'callback' => null
				);
				
				//Merge defaults
				$input = array_merge($defaults, $input);
				
				//Fix up inputs
				$input = $this->parse_function_args($input);
				
				if ( !isset($input['name']) || !isset($input['type']) )
					continue;
				
				$input['tooltip'] = (isset($input['tooltip']) && $input['tooltip'] != false) ? $input['tooltip'] : false;
				$input['name'] = strtolower($input['name']);
								
				if ( method_exists($this, 'input_' . str_replace('-', '_', $input['type'])) ) {
					
					echo '<div class="input input-' . $input['type'] . '" id="input-' . $input['name'] . '">';
										
						if ( $input['tooltip'] )
							echo '<div class="tooltip-button" title="' . htmlspecialchars($input['tooltip']) . '"></div>';

						//Put this code here to reduce repetition of code throughout input methods
						$input['default'] = ( isset($input['default']) ) ? $input['default'] : null;
						$input['group'] = ( isset($input['group']) ) ? $input['group'] : $this->options_group;
						
						//Set up all of the name attributes
						$block_id = ($this->is_block && $this->block_id) ? ' block_id="' . $this->block_id . '"' : null;
						$is_block = ($block_id !== null) ? 'true' : 'false';
						$input_id = ( $is_block == 'true' ) ? 'input-' . $this->block_id . '-' . $input['name'] : 'input-' . $input['group'] . '-' . $input['name'];
												
						//Figure out if it's a regular option, layout option, or block setting
						if ( $this->is_block && isset($this->block) && !isset($input['value']) )
							$input['value'] = HeadwayBlocksData::get_block_setting($this->block, $input['name'], $input['default']);
						elseif ( !isset($input['value']) )
							$input['value'] = HeadwayOption::get($input['name'], $input['group'], $input['default']);

						//Set up the callback
						$block_js_var = ($this->is_block && $this->block_id) ? 'var block = $i(\'.block[data-id="' . $this->block_id . '"]\');' : 'var block = null;';
						$callback = 'callback="' . htmlspecialchars('(function(args){var input=args.input;var value=args.value;' . $block_js_var . $input['callback'] . '})') . '"';
												
						//Fill attributes for the hidden input
						$input['attributes'] = 'id="' . $input_id . '" is_block="' . $is_block . '"' . $block_id . ' name="' . $input['name'] . '" group="' . $input['group'] . '" ' . $callback;
					
						call_user_func(array($this, 'input_' . str_replace('-', '_', $input['type'])), $input);

					echo '</div><!-- #input-' . $input['name'] . ' -->';

					
				}

			}

		}
		
	}
	
	
	public function input_checkbox($input) {
		
		$checked_class = ( (bool)$input['value'] === true ) ? ' class="checkbox-checked"' : null;

		echo '
			<div class="input-left">
				<label' . $checked_class . '>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<img src="'.get_template_directory_uri() . '/library/visual-editor/images/checkmark-white.png" alt=""' . $checked_class . ' />
				<input ' . $input['attributes'] . ' type="hidden" value="' . ($input['value'] ? 'true' : 'false') . '" />
			</div>
		';
		
	}
	
	
	public function input_text($input) {
	
		$readonly = ( isset($input['readonly']) && $input['readonly'] === true )  ? ' disabled' : null;
		
		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<input type="text" ' . $input['attributes'] . ' value="' . stripslashes(htmlspecialchars($input['value'])) . '" class="text"' . $readonly . ' />';
				
			if ( isset($input['suffix']) ) echo '<span class="suffix">' . $input['suffix'] . '</span>';

		echo '
			</div>
		';

	}
	
	
	public function input_textarea($input) {

		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<span class="textarea-open tooltip" title="View Textarea"></span>
				<div class="textarea-container">
					<textarea ' . $input['attributes'] . '>' . stripslashes(htmlspecialchars($input['value'])) . '</textarea>
				</div>
			</div>
		';
		
	}


	public function input_wysiwyg($input) {

		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<span class="wysiwyg-open tooltip" title="View Editor"></span>
				<div class="wysiwyg-container">
					<textarea ' . $input['attributes'] . '>' . stripslashes(htmlspecialchars($input['value'])) . '</textarea>
				</div>
			</div>
		';
		
	}
	

	public function input_integer($input) {

		$readonly = ( isset($input['readonly']) && $input['readonly'] === true )  ? ' disabled' : null;
		
		echo '<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<input type="text" ' . $input['attributes'] . ' value="' . (int)$input['value'] . '" class="text"'. $readonly .' />';
				
			if ( isset($input['unit']) ) echo '<span class="suffix">' . $input['unit'] . '</span>';
			
		echo '
			</div>
		';
						
	}
	
	
	public function input_select($input) {
				
		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
		';
		
				
		echo '<div class="input-right">';
			
			echo '<select ' . $input['attributes'] . '>';

			foreach($input['options'] as $value => $text) {
				
				$selected = ( $input['value'] === $value ) ? ' selected' : null;
						
				echo '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
				
			}

			echo '</select>';

		echo '</div>';
										
	}
	
	
	public function input_multi_select($input) {
				
		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
		';				
				
		echo '<div class="input-right">';
	
			echo '<span class="multi-select-open tooltip" title="View Options"></span>';
			echo '<div class="multi-select-container">';
						
				echo '<select ' . $input['attributes'] . ' multiple="multiple" class="tooltip" title="Hold Ctrl (Windows) or Command (Mac) to select multiple options.">';

				foreach ( $input['options'] as $value => $text ) {
					
					$selected = ( is_array($input['value']) && in_array($value, $input['value']) ) ? ' selected' : null;
		
					echo '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
					
				}

				echo '</select>';
			
			echo '</div><!-- .multi-select-container -->';
	
		echo '</div>';
										
	}

	
	public function input_colorpicker($input) {
		
		$input['value'] = headway_format_color_hex($input['value']);

		$transparent_color_class = ( $input['value'] == 'transparent' ) ? ' colorpicker-color-transparent' : null;
		
		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<div class="colorpicker-box"><div class="colorpicker-color' . $transparent_color_class . '" style="background-color:' . $input['value'] . ';"></div></div>
				<input ' . $input['attributes'] . ' type="hidden" value="' . $input['value'] . '" />
			</div>
		';
		
	}
	
	
	public function input_image($input) {
		
		$src_visibility = ( $input['value'] !== null && is_string($input['value']) ) ? '' : ' style="display:none;"';
		
		echo '<label>' . $input['label'] . '</label>';		
				
		echo '<span class="src"' . $src_visibility . '>' . end(explode('/', $input['value'])) . '</span>
		<span class="delete-image"' . $src_visibility . '>Delete</span>';
						
		echo '<span class="button">Choose Image</span>
			<input ' . $input['attributes'] . ' type="hidden" value="' . $input['value'] . '" />';
		
	}
	
	
	public function input_multi_image($input) {
		
		$image_urls = $input['value'];		
				
		if ( !isset($image_urls) || !is_array($image_urls) )
			$image_urls = array();
			
		$images = '';	
					
		foreach ( $image_urls as $image_url ) {
			
			$filename = end(explode('/', $image_url));
			
			$images .= '<li class="image"><span class="src" url="' . $image_url . '">' . $filename .  '</span><span class="delete-image">Delete</span></li>';
			
		}

		echo '
			<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div>
			
			<div class="input-right">
				<span class="multi-image-container-open tooltip" title="View Images"></span>
				<div class="multi-image-container">
				
					<ul>
						' . $images. '
						
						<li class="add-image">
							<span class="button">Add Image</span>
						</li>
					</ul>
										
					<input ' . $input['attributes'] . ' type="hidden" value="' . $input['value'] . '" />
					
				</div>
			</div>
		';
		
	}


	public function input_slider($input) {
				
		$input['slider-interval'] = (isset($input['slider-interval'])) ? $input['slider-interval'] : 1;
			
		echo '<div class="input-left">
				<label>' . $input['label'] . '</label>
			</div><!-- .input-left -->
	
			<div class="input-right">
				<div class="input-slider-bar" slider_min="' . $input['slider-min'] . '" slider_max="' . $input['slider-max'] . '" slider_interval="' . $input['slider-interval'] . '"></div><!-- .input-slider-bar -->
			
				<div class="input-slider-bar-text">
					<span class="slider-value">' . $input['value'] . '</span>';
	
		if ( isset($input['unit']) && $input['unit'] !== false ) echo '<span class="slider-unit">' . $input['unit'] . '</span>';
		
		echo '</div><!-- .input-slider-bar-text -->';
		echo '</div><!-- .input-right -->';

		echo '<input type="hidden" value="' . $input['value'] . '" ' . $input['attributes'] . ' class="input-slider-bar-hidden" />';

	}


}