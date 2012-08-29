<?php
headway_register_visual_editor_box('HeadwayGridWizardBox');
class HeadwayGridWizardBox extends HeadwayVisualEditorBoxAPI {
	
	/**
	 *	Slug/ID of panel.  Will be used for HTML IDs and whatnot.
	 **/
	protected $id = 'grid-wizard';
	
	
	/**
	 * Name of panel.  This will be shown in the title.
	 **/
	protected $title = 'Grid Wizard';
	
	protected $description = 'Choose a preset or a page to clone';
	
	
	/**
	 * Which mode to put the panel on.
	 **/
	protected $mode = 'grid';
	
	protected $center = false;
	
	protected $width = 600;
		
	protected $height = 420;
		
	protected $closable = true;
	
	protected $draggable = false;
	
	protected $resizable = false;
	
	protected $black_overlay = true;
	
	protected $black_overlay_opacity = 0.3;
	
	protected $black_overlay_iframe = true;
	
	protected $load_with_ajax = true;
	
	protected $load_with_ajax_callback = '$(\'div#box-grid-wizard div.box-content\').tabs(\'destroy\');$(\'div#box-grid-wizard div.box-content\').tabs();';
	
	
	
	public function content() {
		
		$current_layout = headway_post('layout');

		$pages_to_clone_select_options = self::clone_pages_select_walker(HeadwayLayout::get_pages());
		$templates_to_assign_select_options = self::templates_to_assign_select_options();
		
?>
		<ul id="grid-wizard-tabs" class="tabs">
			<?php			
			if ( $pages_to_clone_select_options !== '' ) {

				echo '<li class="ui-tabs-selected"><a href="#grid-wizard-tab-clone-page">Clone Existing Page</a></li>';
				echo '<li><a href="#grid-wizard-tab-presets">Presets</a></li>';

			} else {

				echo '<li class="ui-tabs-selected"><a href="#grid-wizard-tab-presets">Presets</a></li>';

			}

			if ( $templates_to_assign_select_options !== '' && strpos($current_layout, 'template-') === false )
				echo '<li><a href="#grid-wizard-tab-assign-template">Assign Template</a></li>';
			?>
		</ul>
		
		<div id="grid-wizard-tab-presets" class="tab-content">
					
			<div id="grid-wizard-presets-step-1">	
				<div class="grid-wizard-presets-row">
					<span class="layout-preset layout-preset-selected" id="layout-right-sidebar" title="Content | Sidebar">
						<img src="<?php echo headway_url() . '/library/visual-editor/images/layouts/layout-right-sidebar.png'; ?>" alt="" />
					</span>
				
					<span class="layout-preset" id="layout-left-sidebar" title="Sidebar | Content">
						<img src="<?php echo headway_url() . '/library/visual-editor/images/layouts/layout-left-sidebar.png'; ?>" alt="" />
					</span>
				
					<span class="layout-preset" id="layout-two-right" title="Content | Sidebar 1 | Sidebar 2">
						<img src="<?php echo headway_url() . '/library/visual-editor/images/layouts/layout-two-right.png'; ?>" alt="" />
					</span>
				</div>

				<div class="grid-wizard-presets-row">
					<span class="layout-preset" id="layout-two-both" title="Sidebar 1 | Content | Sidebar 2">
						<img src="<?php echo headway_url() . '/library/visual-editor/images/layouts/layout-two-both.png'; ?>" alt="" />
					</span>
				
					<span class="layout-preset" id="layout-all-content" title="Content">
						<img src="<?php echo headway_url() . '/library/visual-editor/images/layouts/layout-all-content.png'; ?>" alt="" />
					</span>
				</div>
			</div><!-- #grid-wizard-presets-step-1 -->
			
			<div id="grid-wizard-presets-step-2">
				
				<h4>Select Which Blocks to Mirror</h4>
				
				<p class="grid-wizard-info">To save time, Headway allows you to "mirror" your blocks.  If you already have a widget area or sidebar that's configured, you may choose to use it by using the select boxes below.</p>
				
				<div id="grid-wizard-presets-mirroring-column-1" class="grid-wizard-presets-mirroring-column">
					<div id="grid-wizard-presets-mirroring-select-header">
						<h5>Header</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('header');
							?>
						</select>
					</div>
				
					<div id="grid-wizard-presets-mirroring-select-navigation">
						<h5>Navigation</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('navigation');
							?>
						</select>
					</div>
				
					<div id="grid-wizard-presets-mirroring-select-content">
						<h5>Content</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('content');
							?>
						</select>
					</div>
				</div>
				
				<div id="grid-wizard-presets-mirroring-column-2" class="grid-wizard-presets-mirroring-column">
					<div id="grid-wizard-presets-mirroring-select-sidebar-1">
						<h5>Sidebar 1</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('widget-area');
							?>
						</select>
					</div>
				
					<div id="grid-wizard-presets-mirroring-select-sidebar-2">
						<h5>Sidebar 2</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('widget-area');
							?>
						</select>
					</div>
				
					<div id="grid-wizard-presets-mirroring-select-footer">
						<h5>Footer</h5>
				
						<select>
							<option value="">&mdash; Do Not Mirror &mdash;</option>
							<?php
							echo self::get_blocks_select_options_for_mirroring('footer');
							?>
						</select>
					</div>
				</div>
				
			</div><!-- #grid-wizard-presets-step-2 -->
			
			<div class="grid-wizard-buttons">
				<span class="grid-wizard-use-empty-grid">Use Empty Grid</span>
				
				<?php
				if ( $pages_to_clone_select_options !== '' ) {
					
					$next_button_style = null;
					$use_button_style = ' style="display: none;"';
					
				} else {
					
					$next_button_style = ' style="display: none;"';
					$use_button_style = null;
					
				}

				echo '<span id="grid-wizard-button-preset-next" class="button grid-wizard-button-next"' . $next_button_style . '>Next &rarr;</span>';
				echo '<span id="grid-wizard-button-preset-use-preset" class="button grid-wizard-button-next"' . $use_button_style . '>Finish &rarr;</span>';
				echo '<span id="grid-wizard-button-preset-previous" class="button grid-wizard-button-previous" style="display: none;">&larr; Previous</span>';
				?>
			</div>
			
		</div><!-- #grid-wizard-tab-presets -->
		
		<?php
		if ( $pages_to_clone_select_options !== '' ) {
		?>
		<div id="grid-wizard-tab-clone-page" class="tab-content">
		
			<h4>Choose a Page to Clone</h4>
		
			<?php
			echo '<select id="grid-wizard-pages-to-clone">';

				echo '<option value="" disabled="disabled">&mdash; Select a Page &mdash;</option>';

				echo $pages_to_clone_select_options;

			echo '</select>';
			?>
			
			<div class="grid-wizard-buttons">
				<span class="grid-wizard-use-empty-grid">Use Empty Grid</span>
				
				<span id="grid-wizard-button-clone-page" class="button grid-wizard-button-next">Clone Page &rarr;</span>
			</div>
			
		</div><!-- #grid-wizard-tab-clone-page -->
		<?php
		}
		
		
		if ( $templates_to_assign_select_options !== '' && strpos($current_layout, 'template-') === false ) {
		?>
		<div id="grid-wizard-tab-assign-template" class="tab-content">
			
			<h4>Choose a Template</h4>
			
			<?php
			echo '<select id="grid-wizard-assign-template">';
			
				echo '<option value="" disabled="disabled">&mdash; Select a Template &mdash;</option>';

				echo $templates_to_assign_select_options;

			echo '</select>';
			?>
			
			<div class="grid-wizard-buttons">
				<span class="grid-wizard-use-empty-grid">Use Empty Grid</span>
				
				<span id="grid-wizard-button-assign-template" class="button grid-wizard-button-next">Assign Template &rarr;</span>
			</div>
			
		</div><!-- #grid-wizard-tab-assign-template -->
<?php
		}


	}
	
	
	static function get_blocks_select_options_for_mirroring($block_type) {
			
		$return = '';	
							
		$blocks = HeadwayBlocksData::get_blocks_by_type($block_type);
				
		//If there are no blocks, then just return the Do Not Mirror option.
		if ( !isset($blocks) || !is_array($blocks) )
			return $return;
		
		foreach ( $blocks as $block_id => $layout_id ) {
			
			//Get the block instance
			$block = HeadwayBlocksData::get_block($block_id);
			
			//If the block is mirrored, skip it
			if ( headway_get('mirror-block', $block['settings'], false) )
				continue;
								
			//If the block is in the same layout as the current block, then do not allow it to be used as a block to mirror.
			if ( $layout_id == headway_post('layout') )
				continue;
			
			//Create the default name by using the block type and ID
			$default_name = HeadwayBlocks::block_type_nice($block['type']) . ' #' . $block['id'];
			
			//If we can't get a name for the layout, then things probably aren't looking good.  Just skip this block.
			if ( !($layout_name = HeadwayLayout::get_name($layout_id)) )
				continue;
			
			//Get alias if it exists, otherwise use the default name
			$return .= '<option value="' . $block['id'] . '">' . headway_get('alias', $block['settings'], $default_name) . ' &ndash; ' . $layout_name . '</option>';  
			
		}
		
		return $return;
		
	}
	
	
	static function clone_pages_select_walker($pages, $depth = 0) {
		
		$return = '';
				
		foreach($pages as $id => $children) {
			
			$layout_id_fragments = explode('-', $id);		

			$status = HeadwayLayout::get_status($id);	
			
			/* Take care of layouts that are the front page or blog index */
			if ( get_option('show_on_front') === 'page' && (isset($layout_id_fragments[1]) && $layout_id_fragments[1] == 'page') ) {

				/* If the page is set as the static homepage or blog page, hide it if they don't have children. */
				if ( end($layout_id_fragments) == get_option('page_on_front') || end($layout_id_fragments) == get_option('page_for_posts') ) {

					/* Layout has children--add the no edit class and has children class. */
					if ( is_array($children) && count($children) !== 0 )
						$disabled = true;

					/* If the layout doesn't have children, then just hide it. */
					else
						continue;

				}

			}
							
			/* Handle layouts that aren't customized or have a template */
			if ( headway_get('customized', $status, false) === false || headway_get('template', $status, false) !== false ) {

				/* If there ARE customized children, add the no-edit class */
				if ( is_array($children) && count($children) !== 0 ) {

					/* Check if the children are customized. */
					if ( HeadwayVisualEditorDisplay::is_any_layout_child_customized($children) ) {

						$disabled = true;

					} else
						continue;

				/* If there aren't any children, do not display the node at all */
				} else
					continue;

			}		

			/* If the current layout is selected, then make it disabled. */
			if ( headway_post('layout') == $id )
				$disabled = true;

			/* Output Stuff */	
			$depth_display = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
			
			$disabled = ( isset($disabled) && $disabled === true ) ? ' disabled="disabled"' : null;

			$return .= '<option value="' . $id . '"' . $disabled .'>' . $depth_display . HeadwayLayout::get_name($id) . '</option>';

			if ( is_array($children) && count($children) !== 0 )						
				$return .= self::clone_pages_select_walker($children, $depth + 1);

		}
		
		return $return;		

	}
	
	
	static function templates_to_assign_select_options() {
		
		$templates = HeadwayLayout::get_templates();
		
		$return = '';
		
		foreach ( $templates as $id => $name) {
			
			$return .= '<option value="template-' . $id . '">' . $name . '</option>';
			
		}
		
		return $return;
		
	}
	
	
}