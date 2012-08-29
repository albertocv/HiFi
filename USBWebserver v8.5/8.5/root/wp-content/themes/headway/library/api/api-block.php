<?php
/**
 * Headway blocks API.
 *
 * @package Headway
 * @subpackage API
 **/
function headway_register_block($class, $block_type_url = false) {	
	
	global $headway_unregistered_block_types;
	
	if ( !is_array($headway_unregistered_block_types) )
		$headway_unregistered_block_types = array();
	
	$headway_unregistered_block_types[$class] = $block_type_url;
	
	return true;

}


abstract class HeadwayBlockAPI {
			
			
	public $id;
	
	
	public $name;
		
	
	public $block_type_url;
	
	
	public $options_class = 'HeadwayBlockOptionsAPI';
		
		
	public $fixed_height = false;
	
	
	public $html_tag = 'div';


	public $description = false;
	
	
	/* System Properties (DO NOT USE OR TOUCH) */
	protected $core_block = false;
	
	
	protected $options;
	
	
	protected $show_content_in_grid = false;
	
	
	/* System Methods (DO NOT EXTEND OR MODIFY) */
	public function register() {
				
		global $headway_block_types;
		
		//If the Headway blocks array doesn't exist, create it.
		if ( !is_array($headway_block_types) ) {
			
			$headway_block_types = array(
				'core' => array(), 
				'plugins' => array()
			);
			
		}
		
		//Add block to array.  This array will be used for checking if certain blocks exist, the block type selector and so on.
		if ( $this->core_block ) {
						
			$headway_block_types['core'][$this->id] = array(
				'name' => $this->name,
				'url' => $this->block_type_url,
				'class' => get_class($this),
				'fixed-height' => $this->fixed_height,
				'html-tag' => $this->html_tag,
				'show-content-in-grid' => $this->show_content_in_grid,
				'description' => $this->description
			);
			
		} else {
			
			$headway_block_types['plugins'][$this->id] = array(
				'name' => $this->name,
				'url' => $this->block_type_url,
				'class' => get_class($this),
				'fixed-height' => $this->fixed_height,
				'html-tag' => $this->html_tag,
				'show-content-in-grid' => $this->show_content_in_grid,
				'description' => $this->description
			);
			
		}
		
		//Add the element for the block itself
		add_action('headway_register_elements', array($this, 'setup_main_block_element'));
				
		//Run init method if it exists
		if ( method_exists($this, 'init') )
			$this->init();
		
		//Run setup_elements if it exists
		if ( method_exists($this, 'setup_elements') )
			add_action('headway_register_elements', array($this, 'setup_elements'));
		
		add_action('headway_block_content_' . $this->id, array($this, 'content'));
		add_action('headway_block_options_' . $this->id, array($this, 'options_panel'), 10, 2);
		
	}
	
	
	public function setup_main_block_element() {
		
		HeadwayElementAPI::register_element(array(
			'group' => 'blocks',
			'id' => 'block-' . $this->id,
			'name' => $this->name,
			'selector' => '.block-type-' . $this->id,
			'properties' => array('background', 'borders', 'fonts', 'padding', 'rounded-corners', 'box-shadow', 'overflow'),
			'supports_instances' => true,
			'inherit-location' => 'default-block'
		));
		
	}
	
	
	public function options_panel($block, $layout) {
							
		if ( !class_exists($this->options_class) )
			return new WP_Error('block_options_class_does_not_exist', __('Error: The block options class being registered does not exist.', 'headway'), $this->options_class);
			
		//Initiate options class
		$options = new $this->options_class();
		$options->display($block, $layout);
				
	}
	
	
	/* Methods to extend (you can modify these!) */
	public function content($block) {
		
	}
	
	
	/**
	 * The following are commented out so they are not detected 
	 * 
	 *  public function enqueue_action($block_id) {
	 *  
	 *  }
	 *
	 *
	 *  public function init_action($block_id) {
	 *  
	 *  }
	 *
	 *
	 *  public function dynamic_css($block_id) {
	 *  
	 *  }
 	 *
     *
	 *  public function dynamic_js($block_id) {
	 *  
	 *  }
	 * 
	 **/
	
	
	/* Methods to use, but not modify! */
	public function register_block_element($args) {
				
		/* Add the selector prefix to the selector and even handle commas */
		$selector_prefix = '.block-type-' . $this->id . ' ';
		
		$selector_array = explode(',', $args['selector']);
		
		foreach ( $selector_array as $selector_index => $selector ) {
						
			if ( strpos($selector_array[$selector_index], $selector_prefix) === 0 )
				continue;
			
			$selector_array[$selector_index] = $selector_prefix . trim($selector);
			
		}
		
		$modified_selector = implode(',', $selector_array);	
		/* End Selector Modification */
		
		$defaults = array(
			'group' => 'blocks',
			'parent' => 'block-' . $this->id,
			'id' => 'block-' . $this->id . '-' . $args['id'],
			'name' => $args['name'],
			'selector' => $modified_selector
		);
		
		//Unset the following so they don't overwrite the defaults
		unset($args['id']);
		unset($args['name']);
		unset($args['selector']);
		
		$element = array_merge($defaults, $args);
		
		//Go through states and add the selector prefix to each state
		if ( isset($element['states']) && is_array($element['states']) ) {
			
			foreach ( $element['states'] as $state_name => $state_selector ) {
				
				$state_selector_array = explode(',', $state_selector);

				foreach ( $state_selector_array as $selector_index => $selector ) {

					if ( strpos($state_selector_array[$selector_index], $selector_prefix) === 0 )
						continue;

					$state_selector_array[$selector_index] = $selector_prefix . trim($selector);

				}

				$modified_state_selector = implode(',', $state_selector_array);
				
				$element['states'][$state_name] = $modified_state_selector;
				
			}
				
		}
		
		return HeadwayElementAPI::register_element($element);
		
	}
	

	public static function get_setting($block, $setting, $default = null) {
				
		return HeadwayBlocksData::get_block_setting($block, $setting, $default);
		
	}
	
	
}


class HeadwayBlockOptionsAPI extends HeadwayVisualEditorPanelAPI {
	
	
	public $is_block = true;

	public $block = false;

	public $block_id = false;
	
	public $layout_id = false;
	
	public $open_js_callback = null;
	
	
	public function register() {
		
		return true;
		
	}
	
	
	public function add_standard_block_config() {
		
		if ( !isset($this->tabs) )
			$this->tabs = array();
			
		if ( !isset($this->inputs) )
			$this->inputs = array();
		
		//Add the tab
		$this->tabs['config'] = 'Config';
		
		/* Add the inputs */

		$this->inputs['config']['mirror-block'] = array(
			'type' => 'select',
			'name' => 'mirror-block',
			'label' => 'Mirror Block',
			'default' => '',
			'tooltip' => 'By using this option, you can tell a block to "mirror" another block and its content.  This option is useful if you are wanting to share a block&mdash;such as a header&mdash;across layouts on your site.  Select the block you wish to mirror the content from in the select box to the right.',
			'options' => 'get_blocks_select_options_for_mirroring()',
			'callback' => 'updateBlockMirrorStatus(input, block, value);'
		);

		if ( HEADWAY_CHILD_THEME_ACTIVE ) {
						
			$block_style_options = array('' => '&ndash; No Style &ndash;');
			
			foreach ( HeadwayChildThemeAPI::$block_styles as $block_style_id => $block_style ) {
								
				if ( is_array($block_style['block-types']) && !in_array($this->block['type'], $block_style['block-types']) )
					continue;
					
				if ( is_string($block_style['block-types']) && $block_style['block-types'] != 'all' && $block_style['block-types'] != $this->block['type'] )
					continue;
				
				$block_style_options[$block_style_id] = $block_style['name'];
				
			}	
			
			
			if ( count($block_style_options) > 1 ) {			
							
				$this->inputs['config'][HEADWAY_CHILD_THEME_ID . '-block-style'] = array(
					'type' => 'select',
					'name' => HEADWAY_CHILD_THEME_ID . '-block-style',
					'label' => 'Block Styles',
					'default' => '',
					'tooltip' => '',
					'options' => $block_style_options,
					'callback' => '$.each(Headway.blockStyles, function(key, value) { block.removeClass(value); }); if ( value ) { block.addClass(Headway.blockStyles[value]); }'
				);
			
			}
			
		}	
			
		$this->inputs['config']['css-classes'] = array(
			'type' => 'text',
			'name' => 'css-classes',
			'label' => 'Custom CSS Class(es)',
			'default' => '',
			'tooltip' => 'Need more finite control?  Enter the custom CSS class selectors here and they will be added to the block\'s class attribute. <strong>DO NOT</strong> put regular CSS in here.  Use the Live CSS editor for that.',
		);
		
		$this->inputs['config']['alias'] = array(
			'type' => 'text',
			'name' => 'alias',
			'label' => 'Block Alias',
			'default' => '',
			'tooltip' => 'Enter an easily recognizable name for the block alias and it will be used throughout your site admin.  For instance, if you add an alias to a widget area block, that alias will be used in the Widgets panel.',
		);
		
		if ( HeadwayResponsiveGrid::is_enabled() ) {
			
			$this->inputs['config']['responsive-block-hiding'] = array(
				'type' => 'multi-select',
				'name' => 'responsive-block-hiding',
				'label' => 'Responsive Grid Block Hiding',
				'default' => '',
				'tooltip' => 'If you have the responsive grid enabled and the user views your website on an iPhone (or equivalent device), the grid may be cluttered do to so many blocks being in a small area.  If you wish to limit the blocks that are shown on mobile devices, you can use this setting to hide certain blocks for the devices you choose.  <strong>If no options are selected, then responsive block hiding will not be active for this block.</strong>',
				'options' => array(
					'smartphones' => 'iPhone/Smartphones',
					'tablets-landscape' => 'iPad/Tablets (Landscape)',
					'tablets-portrait' => 'iPad/Tablets (Portrait)'
				)
			);
			
		}
		
	}
	
	
	public function get_blocks_select_options_for_mirroring() {
			
		$block_type = $this->block['type'];	
				
		$blocks = HeadwayBlocksData::get_blocks_by_type($block_type);
		
		$options = array('' => '&ndash; Do Not Mirror &ndash;');
		
		//If there are no blocks, then just return the Do Not Mirror option.
		if ( !isset($blocks) || !is_array($blocks) )
			return $options;
		
		foreach ($blocks as $block_id => $layout_id) {
			
			//Get the block instance
			$block = HeadwayBlocksData::get_block($block_id);
			
			//If the block is mirrored, skip it
			if ( HeadwayBlocksData::is_block_mirrored($block) )
				continue;
				
			//If the block is in the same layout as the current block, then do not allow it to be used as a block to mirror.
			if ( $this->block['layout'] == $layout_id )
				continue;
			
			//Create the default name by using the block type and ID
			$default_name = HeadwayBlocks::block_type_nice($block['type']) . ' #' . $block['id'];
			
			//If we can't get a name for the layout, then things probably aren't looking good.  Just skip this block.
			if ( !($layout_name = HeadwayLayout::get_name($layout_id)) )
				continue;
			
			//Get alias if it exists, otherwise use the default name
			$options[$block['id']] = headway_get('alias', $block['settings'], $default_name) . ' &ndash; ' . $layout_name;  
			
		}
		
		//Remove the current block from the list
		unset($options[$this->block_id]);
		
		return $options;
		
	}
	
	
	public function panel_content($args = false) {
		
		//Allow developers to modify the properties of the class and use functions since doing a property 
		//outside of a function will not allow you to.
		$this->modify_arguments($args);
		
		//Add the standard block config
		$this->add_standard_block_config();
		
		if ( $this->tabs ) {
			
			echo '<ul class="sub-tabs" open_js_callback="' . htmlspecialchars('(function(args){' . $this->open_js_callback . '})') . '">';
			
				foreach($this->tabs as $id => $name){
					
					echo '<li id="sub-tab-' . $id . '"><a href="#sub-tab-' . $id . '-content">' . $name . '</a></li>';
					
				}
			
			echo '</ul>';
			
			echo '<div class="sub-tabs-content-container">';
			
			foreach($this->tabs as $id => $name){
				
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
		
	
	public function display($block, $layout) {
		
		//Set block properties
		$this->block = $block;
		$this->block_id = $block['id'];
		
		//Transfer layout ID into object so we can query the block options
		$this->layout_id = $layout;
		
		//Display it
		$this->panel_content(array(
			'block' => $this->block,
			'block_id' => $this->block_id,
			'layout_id' => $this->layout_id
		));
		
	}
	
	
}