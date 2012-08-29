<?php
headway_register_block('HeadwayHeaderBlock', headway_url() . '/library/blocks/core/header');

class HeadwayHeaderBlock extends HeadwayBlockAPI {
	
	
	public $id = 'header';
	
	public $name = 'Header';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayHeaderBlockOptions';
	
	public $fixed_height = true;
	
	public $html_tag = 'header';

	public $description = 'Display your banner, logo, or site title and tagline.  This typically goes at the top of your website.';
	
	protected $show_content_in_grid = true;
	
	
	function setup_elements() {
		
		$this->register_block_element(array(
			'id' => 'site-title',
			'name' => 'Site Title',
			'selector' => 'span.banner a',
			'properties' => array('fonts', 'borders', 'text-shadow'),
			'inherit-location' => 'default-heading',
			'states' => array(
				'Hover' => 'span.banner a:hover',
				'Clicked' => 'span.banner a:active'
			)
		));

		$this->register_block_element(array(
			'id' => 'site-tagline',
			'name' => 'Site Tagline',
			'selector' => '.tagline',
			'properties' => array('fonts', 'borders', 'text-shadow'),
			'inherit-location' => 'default-sub-heading'
		));
		
	}
	
	
	function content($block) {
		
		//Use header image if there is one	
		if ( parent::get_setting($block, 'header-image') ) {
		
			do_action('headway_before_header_link');
		
			if ( parent::get_setting($block, 'resize-header-image', true) ) {
				
				$block_width = HeadwayBlocksData::get_block_width($block);
				$block_height = HeadwayBlocksData::get_block_height($block);
				
				$header_image_url = headway_resize_image(parent::get_setting($block, 'header-image'), $block_width, $block_height);
				
			} else {
				
				$header_image_url = parent::get_setting($block, 'header-image');
				
			}

			echo '<a href="' . home_url() . '" class="banner-image"><img src="' . $header_image_url . '" alt="' . get_bloginfo('name') . '" /></a>';
			
			do_action('headway_after_header_link');
			
			
		//No image present	
		} else {
			
			do_action('headway_before_header_link');
			
			echo '<span class="banner"><a href="' . home_url() . '">' . get_bloginfo('name') . '</a></span>';
			
			do_action('headway_after_header_link');

			if ( !parent::get_setting($block, 'hide-tagline', false) ) {

				if ( (is_front_page() || is_home()) && get_option('show_on_front') != 'page' ) {

					echo '<h1 class="tagline">' . get_bloginfo('description') . '</h1>' . "\n";

				} else {

					echo '<span class="tagline">' . get_bloginfo('description') . '</span>' . "\n";

				}
				
				do_action('headway_after_tagline');

			}
			
		}
		
	}
	
}


class HeadwayHeaderBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'general' => 'General'
	);

	public $inputs = array(
		'general' => array(
			'header-image' => array(
				'type' => 'image',
				'name' => 'header-image',
				'label' => 'Header Image',
				'default' => null
			),
			
			'resize-header-image' => array(
				'name' => 'resize-header-image',
				'label' => 'Automatically Resize Header Image',
				'type' => 'checkbox',
				'tooltip' => 'If you would like Headway to automatically scale and crop your header image to the correct dimensions, keep this checked.',
				'default' => true
			),
			
			'hide-tagline' => array(
				'name' => 'hide-tagline',
				'label' => 'Hide Tagline',
				'type' => 'checkbox',
				'tooltip' => 'Check this to hide the tagline in your header.  The tagline will sit beneath your site title.  <em><strong>Important:</strong> The tagline will <strong>NOT</strong> show if a Header Image is added.</em>',
				'default' => false
			)
		)
	);
	
}