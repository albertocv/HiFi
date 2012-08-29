<?php
headway_register_block('HeadwayFooterBlock', headway_url() . '/library/blocks/core/footer');

class HeadwayFooterBlock extends HeadwayBlockAPI {
	
	
	public $id = 'footer';
	
	public $name = 'Footer';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayFooterBlockOptions';
	
	public $html_tag = 'footer';

	public $description = 'This typically goes at the bottom of your site and will display the copyright, and miscellaneous links.';
	
	protected $show_content_in_grid = true;
		
	
	function setup_elements() {
		
		$this->register_block_element(array(
			'id' => 'copyright',
			'name' => 'Copyright',
			'selector' => 'p.copyright',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text'
		));
		
		$this->register_block_element(array(
			'id' => 'headway-attribution',
			'name' => 'Headway Attribution',
			'selector' => 'p.footer-headway-link',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text'
		));
		
		$this->register_block_element(array(
			'id' => 'administration-panel',
			'name' => 'Administration Panel',
			'selector' => 'a.footer-admin-link',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text'
		));
		
		$this->register_block_element(array(
			'id' => 'go-to-top',
			'name' => 'Go To Top Link',
			'selector' => 'a.footer-go-to-top-link',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text'
		));
		
		$this->register_block_element(array(
			'id' => 'responsive-grid-link',
			'name' => 'Responsive Grid Toggle Link',
			'selector' => 'a.footer-responsive-grid-link',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text'
		));
		
	}
	
	
	function content($block) {
		
		//Add action for footer
		do_action('headway_before_footer');
		
		echo "\n" . '<div class="footer-container">' . "\n";
		
		echo "\n" . '<div class="footer">' . "\n";
		
		do_action('headway_footer_open');

		//Headway Attribution
		if ( parent::get_setting($block, 'hide-headway-attribution', false) == false )
			self::show_headway_link();
		
		//Go To Top Link
		if ( parent::get_setting($block, 'show-go-to-top-link', true) == true )
			self::show_go_to_top_link();
		
		//Admin Link
		if ( parent::get_setting($block, 'show-admin-link', true) == true )
			self::show_admin_link();
		 		
		//Copyright
		if ( parent::get_setting($block, 'show-copyright', true) == true )
			self::show_copyright(parent::get_setting($block, 'custom-copyright'));
		
		if ( parent::get_setting($block, 'show-responsive-grid-link', true) == true )
			self::show_responsive_grid_toggle_link();
		
		do_action('headway_footer_close');
		
		echo "\n" . '</div><!-- .footer -->';
		
		echo "\n" . '</div><!-- .footer-container -->';
		
		do_action('headway_after_footer');
		
	}
	
	
	/**
	 * Displays an admin link or admin login.
	 * 
	 * @uses HeadwayOption::get()
	 *
	 * @return void
	 **/
	public static function show_admin_link() {

		if ( is_user_logged_in() )
		    echo apply_filters('headway_admin_link', '<a href="' . admin_url() . '" class="footer-right footer-admin-link footer-link">'.__('Administration Panel', 'headway') . '</a>');
		else
		    echo apply_filters('headway_admin_link', '<a href="' . admin_url() . '" class="footer-right footer-admin-link footer-link">'.__('Administration Login', 'headway') . '</a>');

	}
	
	
	/**
	 * Echos the Powered By Headway link.
	 * 
	 * @uses HeadwayOption::get()
	 *
	 * @param string $text The name of the program to be displayed.  Defaults to Headway (obviously).
	 * 
	 * @return mixed
	 **/
	public static function show_headway_link() {

		if ( HeadwayOption::get('affiliate-link') )
			$headway_location = strip_tags(HeadwayOption::get('affiliate-link'));
		else
			$headway_location = 'http://headwaythemes.com/';	

		echo apply_filters('headway_link', '<p class="footer-left footer-headway-link footer-link">' . __('Powered by Headway, the ', 'headway') . ' <a href="' . $headway_location . '" title="Headway Premium WordPress Theme">drag and drop WordPress theme</a></p>');

	}


	/**
	 * Shows a simple copyright paragraph.
	 *
	 * @return mixed
	 **/
	public static function show_copyright($custom_copyright = false) {

		$default_copyright = __('Copyright', 'headway') . ' &copy; ' . date('Y') . ' ' . get_bloginfo('name');

		$copyright = $custom_copyright ? $custom_copyright : $default_copyright;

		echo apply_filters('headway_copyright', '<p class="copyright footer-copyright">' . $copyright . '</p>');

	}


	/**
	 * Shows a simple go to top link.
	 *
	 * @return mixed
	 **/
	public static function show_go_to_top_link() {

		echo apply_filters('headway_go_to_top_link', '<a href="#" class="footer-right footer-go-to-top-link footer-link">' . __('Go To Top', 'headway') . '</a>');

	}
	
	
	/**
	 * Shows a link to either view the full site or view the mobile site.
	 * 
	 * This will only show if the responsive grid is enabled.
	 **/
	public static function show_responsive_grid_toggle_link() {
		
		if ( !HeadwayResponsiveGrid::is_enabled() )
			return false;
			
		$current_url = headway_get_current_url();	
			
		if ( HeadwayResponsiveGrid::is_active() ) {
			
			$url = add_query_arg(array('full-site' => 'true'), $current_url);
			$classes = 'footer-responsive-grid-link footer-responsive-grid-disable footer-link';
			
			echo apply_filters('headway_responsive_disable_link', '<p class="footer-responsive-grid-link-container footer-responsive-grid-link-disable-container"><a href="' . $url . '" rel="nofollow" class="' . $classes . '">' . __('View Full Site', 'headway') . '</a></p>');
			
		} elseif ( HeadwayResponsiveGrid::is_user_disabled() ) {
			
			$url = add_query_arg(array('full-site' => 'false'), $current_url);
			$classes = 'footer-responsive-grid-link footer-responsive-grid-enable footer-link';
			
			echo apply_filters('headway_responsive_enable_link', '<p class="footer-responsive-grid-link-container footer-responsive-grid-link-enable-container"><a href="' . $url . '" rel="nofollow" class="' . $classes . '">' . __('View Mobile Site', 'headway') . '</a></p>');
			
		}
		
	}
	
	
}

class HeadwayFooterBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'nav-menu-content' => 'Content'
	);

	public $inputs = array(
		'nav-menu-content' => array(
			'show-admin-link' => array(
				'type' => 'checkbox',
				'name' => 'show-admin-link',
				'label' => 'Show Admin Link/Login',
				'default' => true
			),
			
			'show-go-to-top-link' => array(
				'name' => 'show-go-to-top-link',
				'label' => 'Show Go To Top Link',
				'type' => 'checkbox',
				'default' => true
			),
			
			'hide-headway-attribution' => array(
				'name' => 'hide-headway-attribution',
				'label' => 'Hide Headay Attribution',
				'type' => 'checkbox',
				'default' => false
			),
			
			'show-copyright' => array(
				'name' => 'show-copyright',
				'label' => 'Show Copyright',
				'type' => 'checkbox',
				'default' => true
			),
			
			'custom-copyright' => array(
				'name' => 'custom-copyright',
				'label' => 'Custom Copyright',
				'type' => 'text',
				'tooltip' => 'If you would like to change the copyright in the footer to say something different, enter it here.'
			)
		)
	);
		
}