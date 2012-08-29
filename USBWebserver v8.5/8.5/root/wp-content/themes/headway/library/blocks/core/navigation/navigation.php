<?php
headway_register_block('HeadwayNavigationBlock', headway_url() . '/library/blocks/core/navigation');

class HeadwayNavigationBlock extends HeadwayBlockAPI {
	
	
	public $id = 'navigation';
	
	public $name = 'Navigation';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayNavigationBlockOptions';
	
	public $fixed_height = false;
	
	public $html_tag = 'nav';
	
	public $description = 'The navigation is the menu that will display all of the pages in your site.';

	protected $show_content_in_grid = true;
	
	/* Use this to pass the block from static function to static function */
	static public $block = null;

	
	function init_action($block_id) {
		
		$block = HeadwayBlocksData::get_block($block_id);
						
		$name = HeadwayBlocksData::get_block_name($block) . ' &mdash; ' . 'Layout: ' . HeadwayLayout::get_name($block['layout']);
		
		register_nav_menu('navigation_block_' . $block_id, $name);

		wp_register_script('jquery-hoverintent', headway_url() . '/library/media/js/jquery.hoverintent.js', array('jquery'));
		
	}
	
	
	function enqueue_action($block_id) {
		
		//If there are no sub menus in the navigation, then do not enqueue all of the JS.
		if ( !self::does_menu_have_subs('navigation_block_' . $block_id) )
			return false;
		
		$dependencies = array('jquery');

		if ( parent::get_setting($block_id, 'hover-intent', true) )
			$dependencies[] = 'jquery-hoverintent';
		
		wp_enqueue_script('headway-superfish', headway_url() . '/library/blocks/core/navigation/js/jquery.superfish.js', $dependencies);
		
	}
	
	
	function content($block) {
		
		self::$block = $block;
		
		/* Add filter to add home link */
		add_filter('wp_nav_menu_items', array(__CLASS__, 'home_link_filter'));
		add_filter('wp_list_pages', array(__CLASS__, 'home_link_filter'));
		add_filter('wp_page_menu', array(__CLASS__, 'fix_legacy_nav'));
		
		/* Variables */
		$vertical = parent::get_setting($block, 'vert-nav-box', false);
		$alignment = parent::get_setting($block, 'alignment', 'left');
		
		$search = parent::get_setting($block, 'enable-nav-search', false);
		$search_position = parent::get_setting($block, 'nav-search-position', 'right');
		
		/* Classes */
		$nav_classes = array();
		
		$nav_classes[] = $vertical ? 'nav-vertical' : 'nav-horizontal';
		$nav_classes[] = 'nav-align-' . $alignment;
		
		if ( $search && !$vertical ) {
			
			$nav_classes[] = 'nav-search-active';
			$nav_classes[] = 'nav-search-position-' . $search_position;
			
		}
			
		$nav_classes = trim(implode(' ', array_unique($nav_classes)));
		$nav_location = 'navigation_block_' . $block['id'];
		
		echo '<div class="' . $nav_classes . '">';
		
				$nav_menu_args = array(
					'theme_location' => $nav_location,
					'container' => false,
				);
				
				if ( HeadwayRoute::is_grid() || headway_get('ve-live-content-query', $block) ) {
					
					$nav_menu_args['link_before'] = '<span>';
					$nav_menu_args['link_after'] = '</span>';
					
				}
			
				wp_nav_menu(apply_filters('headway_navigation_block_query_args', $nav_menu_args, $block));
				
				if ( $search && !$vertical ) {
				
					echo '<div class="nav-search">';
						echo get_search_form();
					echo '</div>';
					
				}
		
		echo '</div><!-- .' . $nav_classes . ' -->';		
				
		/* Remove filter for home link so other non-navigation blocks are modified */
		remove_filter('wp_nav_menu_items', array(__CLASS__, 'home_link_filter'));
		remove_filter('wp_list_pages', array(__CLASS__, 'home_link_filter'));
		remove_filter('wp_page_menu', array(__CLASS__, 'fix_legacy_nav'));
		
	}
	
	
	function dynamic_css($block_id) {
		
		$block = HeadwayBlocksData::get_block($block_id);
		
		$block_height = HeadwayBlocksData::get_block_height($block);
		
		return '
			#block-' . $block_id . ' .nav-horizontal ul.menu > li > a, 
			#block-' . $block_id . ' .nav-search-active .nav-search { 
				height: ' . $block_height . 'px; 
				line-height: ' . $block_height . 'px; 
			}';
		
	}
	
	
	function dynamic_js($block_id) {
		
		//If there are no sub menus in the navigation, then do not output the Superfish JS.
		if ( !self::does_menu_have_subs('navigation_block_' . $block_id) )
			return false;
		
		switch ( parent::get_setting($block_id, 'effect', 'fade') ) {
			case 'none':
				$animation = '{height:"show"}';
				$speed = '0';
			break;

			case 'fade':
				$animation = '{opacity:"show"}';
				$speed = "'fast'";
			break;

			case 'slide':
				$animation = '{height:"show"}';
				$speed = "'fast'";
			break;
		}

		return 'jQuery(document).ready(function(){ 
		jQuery("#block-' . $block_id . '").find("ul.menu").superfish({
			delay: 200,
			animation: ' . $animation . ',
			speed: ' . $speed . ',
			onBeforeShow: function() {
				var parent = jQuery(this).parent();
				
				var subMenuParentLink = jQuery(this).siblings(\'a\');
				var subMenuParents = jQuery(this).parents(\'.sub-menu\');

				if ( subMenuParents.length > 0 || jQuery(this).parents(\'.nav-vertical\').length > 0 ) {
					jQuery(this).css(\'marginLeft\',  parent.outerWidth());
					jQuery(this).css(\'marginTop\',  -subMenuParentLink.outerHeight());
				}
			}
		});		
});' . "\n\n";
		
	}
	
	
	function does_menu_have_subs($location) {
		
		$menu = wp_nav_menu(array(
			'theme_location' => $location,
			'echo' => false
		));	
				
		if ( preg_match('/class=[\'"]sub-menu[\'"]/', $menu) || preg_match('/class=[\'"]children[\'"]/', $menu) )
			return true;
			
		return false;
		
	}
	
	
	function setup_elements() {

		$this->register_block_element(array(
			'id' => 'menu-item',
			'name' => 'Menu Item',
			'selector' => 'ul.menu li a',
			'properties' => array('fonts' => array('font-family', 'font-size', 'color', 'font-styling', 'capitalization', 'letter-spacing', 'text-decoration'), 'background', 'borders', 'padding', 'rounded-corners', 'box-shadow', 'text-shadow'),
			'states' => array(
				'Selected' => 'ul.menu li.current_page_item a', 
				'Hover' => 'ul.menu li a:hover', 
				'Clicked' => 'ul.menu li a:active'
			)
		));
		
		
		$this->register_block_element(array(
			'id' => 'sub-nav-menu',
			'name' => 'Sub Navigation Menu',
			'selector' => 'ul.sub-menu',
			'properties' => array('background', 'borders', 'padding', 'rounded-corners', 'box-shadow')
		));
		
	}
	

	function home_link_filter($menu) {
		
		$block = self::$block;
		
		if ( parent::get_setting($block, 'hide-home-link') )
			return $menu;
		
		if ( get_option('show_on_front') == 'posts' ) {

			$current = (is_home() || is_front_page()) ? ' current_page_item' : null;
			$home_text = ( parent::get_setting($block, 'home-link-text') ) ? parent::get_setting($block, 'home-link-text') : 'Home';

			/* If it's not the grid, then do not add the extra <span>'s */
			if ( !HeadwayRoute::is_grid() && !headway_get('ve-live-content-query', $block) )
				$home_link = '<li class="menu-item-home' . $current . '"><a href="' . home_url() . '">' . $home_text . '</a></li>';
			
			/* If it IS the grid, add extra <span>'s so it can be automatically vertically aligned */
			else
				$home_link = '<li class="menu-item-home' . $current . '"><a href="' . home_url() . '"><span>' . $home_text . '</span></a></li>';
			
		} else {
			
			$home_link = null;
			
		}

		return $home_link . $menu;
		
	}
	
	
	function fix_legacy_nav($menu) {
		
		$menu = preg_replace('/<ul class=[\'"]children[\'"]/', '<ul class="sub-menu"', trim($menu)); //Change sub menu class
		$menu = preg_replace('/<div class=[\'"]menu[\'"]>/', '', $menu, 1); //Remove opening <div>
		$menu = str_replace('<ul>', '<ul class="menu">', $menu); //Add menu class to main <ul>
				
		return substr(trim($menu), 0, -6); //Remove the closing </div>
		
	}
	
	
}


class HeadwayNavigationBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'nav-menu-content' => 'Content',
		'search' => 'Search',
		'home-link' => 'Home Link',
		'orientation' => 'Orientation',
		'effects' => 'Effects'
	);

	public $inputs = array(
		'search' => array(
			'enable-nav-search' => array(
				'type' => 'checkbox',
				'name' => 'enable-nav-search',
				'label' => 'Enable Navigation Search',
				'default' => false,
				'tooltip' => 'If you wish to have a simple search form in the navigation bar, then check this box.  <em><strong>Note:</strong> the search form will not show if the Vertical Navigation option is enabled for this block.</em>'
			),
			
			'nav-search-position' => array(
				'type' => 'select',
				'name' => 'nav-search-position',
				'label' => 'Search Position',
				'default' => 'right',
				'options' => array(
					'left' => 'Left',
					'right' => 'Right'
				),
				'tooltip' => 'If you would like the navigation search input to snap to the left instead of the right, you can use this option.'
			),
		),
		
		'home-link' => array(
			'hide-home-link' => array(
				'type' => 'checkbox',
				'name' => 'hide-home-link',
				'label' => 'Hide Home Link',
				'default' => false
			),
			
			'home-link-text' => array(
				'name' => 'home-link-text',
				'label' => 'Home Link Text',
				'type' => 'text',
				'tooltip' => 'If you would like the link to your homepage to say something other than <em>Home</em>, enter it here!',
				'default' => 'Home'
			)
		),
		
		'orientation' => array(
			'alignment' => array(
				'type' => 'select',
				'name' => 'alignment',
				'label' => 'Alignment',
				'default' => 'left',
				'options' => array(
					'left' => 'Left',
					'right' => 'Right',
					'center' => 'Center'
				)
			),
			
			'vert-nav-box' => array(
				'type' => 'checkbox',
				'name' => 'vert-nav-box',
				'label' => 'Vertical Navigation',
				'default' => false,
				'tooltip' => 'Instead of showing navigation horizontally, you can make the navigation show vertically.  <em><strong>Note:</strong> You may have to resize the block to make the navigation items fit correctly.</em>'
			)
		),

		'effects' => array(
			'effect' => array(
				'type' => 'select',
				'name' => 'effect',
				'label' => 'Drop Down Effect',
				'default' => 'fade',
				'options' => array(
					'none' => 'No Effect',
					'fade' => 'Fade',
					'slide' => 'Slide'
				),
				'tooltip' => 'This is the effect that will be used when the drop downs are shown and hidden.'
			),

			'hover-intent' => array(
				'type' => 'checkbox',
				'name' => 'hover-intent',
				'label' => 'Hover Intent',
				'default' => true,
				'tooltip' => 'Hover Intent makes it so if a navigation item with a drop down is hovered then the drop down will only be shown if the visitor has their mouse over the item for more than a split second.<br /><br />This reduces drop-downs from sporatically showing if the visitor makes fast movements over the navigation.'
			)
		)
	);
	
	
	function modify_arguments($args) {
		
		$this->tab_notices['nav-menu-content'] = 'To add items to this navigation menu, go to <a href="' . admin_url('nav-menus.php') . '" target="_blank">WordPress Admin &raquo; Appearance &raquo; Menus</a>.  Then, create a menu and assign it to <em>' . HeadwayBlocksData::get_block_name($args['block_id']) . '</em> in the <strong>Theme Locations</strong> box.';
		
	}
	
}