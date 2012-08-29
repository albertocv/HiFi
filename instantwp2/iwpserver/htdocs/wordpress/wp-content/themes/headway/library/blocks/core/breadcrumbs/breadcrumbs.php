<?php
headway_register_block('HeadwayBreadcrumbsBlock', headway_url() . '/library/blocks/core/breadcrumbs');

class HeadwayBreadcrumbsBlock extends HeadwayBlockAPI {
	
	
	public $id = 'breadcrumbs';
	
	public $name = 'Breadcrumbs';
		
	public $core_block = true;
	
	public $fixed_height = true;

	public $description = 'Breadcrumbs aid in the navigation of your site by showing a visual hierarchy of where your visitor is.<br /><strong>Example:</strong> Home &raquo; Blog &raquo; Sample Blog Post';
	
	protected $show_content_in_grid = true;
	
	
	function setup_elements() {
		
		$this->register_block_element(array(
			'id' => 'text',
			'name' => 'Text',
			'selector' => 'p',
			'properties' => array('fonts'),
			'inherit-location' => 'default-text'
		));
		
		$this->register_block_element(array(
			'id' => 'hyperlinks',
			'name' => 'Hyperlinks',
			'selector' => 'p a',
			'properties' => array('fonts'),
			'inherit-location' => 'default-hyperlink'
		));
		
	}
	
	
	function content($block) {
		
		//If Yoast's breadcrumbs is activated, use it instead.
		if ( function_exists('yoast_breadcrumb') )
			return yoast_breadcrumb('<p class="breadcrumbs yoastbreadcrumb">', '</p>');
		
		//Set up variables
		global $post;
		
		$blog = null;
		$separator = '&raquo;';
		
		//If the site admin has the site homepage set to a static page, then the blog will be under a different page.  We need to set that up as a prefix.
		if ( get_option('show_on_front') == 'page' && get_option('page_for_posts') !== get_option('page_on_front') ) {
			
			if ( is_home() )
				$blog = ' ' . $separator . ' ' . get_the_title(get_option('page_for_posts'));
			else
				$blog = ' ' . $separator . ' <a href="' . get_page_link(get_option('page_for_posts')) . '">' . get_the_title(get_option('page_for_posts')) . '</a>';
			
		}
				
		//Start displaying the breadcrumbs
		echo '<p class="breadcrumbs">';
		
			echo '<span class="breadcrumbs-level-1">' . __('You Are Here', 'headway') . ':</span>&ensp;';
			
			//If the visitor is on the front page, then set the current location to Home without a hyperlink.
			if ( is_front_page() )
				echo __('Home', 'headway');
			else
				echo '<a href="' . home_url() . '">' . __('Home', 'headway') . '</a>';

			//Pages
			if ( is_page() ){
				
				$current_page = array($post);
				$parent = $post;

				if ( isset($parent->post_parent) ) {
					
					while ( $parent->post_parent ) {
						$parent = get_post($parent->post_parent);
						array_unshift($current_page, $parent);
					}
					
				}

				foreach ( $current_page as $page ) {
					
					if ( $page->ID != get_the_id() ) {
						
						$link_open[$page->ID] = '<a href="' . get_page_link( $page->ID ) . '">';
						$link_close[$page->ID] = '</a>';

						$page_title = $page->post_title;
						
					} else {
						
						$link_open[$page->ID] = false;
						$link_close[$page->ID] = false;

						$page_title = '<span id="current-breadcrumb">' . $page->post_title . '</span>';
						
					}

					echo ' ' . $separator . ' ' . $link_open[$page->ID] . $page_title . $link_close[$page->ID];
					
				}	
							
			}	
			
			//Categories	 
			elseif( is_category() )
				echo $blog . ' ' . $separator . ' <span id="current-breadcrumb">' . single_cat_title('', false) . '</span>';

			
			//Posts
			elseif ( is_single() && get_post_type() == 'post' )
				echo $blog . ' ' . $separator . ' ' . get_the_category_list(', ') . ' &raquo; <span id="current-breadcrumb">' . get_the_title() . '</span>'; 
			
			//Searches
			elseif ( is_search() )
				echo $blog . ' ' . $separator . ' <span id="current-breadcrumb">' . __('Search Results For:', 'headway') . ' ' . get_search_query() . '</span>'; 
			
			//Author Archives
			elseif ( is_author() ) {
				
				$author = get_queried_object();
									
				echo $blog . ' &raquo; <span id="current-breadcrumb">' . __('Author Archives:', 'headway') . ' ' . $author->display_name . '</span>';
				
			}
			
			//404's
			elseif ( is_404() )
				echo ' &raquo; <span id="current-breadcrumb">' . __('404 Error!', 'headway') . '</span>';
			
			//Tag Archives
			elseif ( is_tag() )
				echo $blog . ' &raquo; <span id="current-breadcrumb">' . __('Tag Archives:', 'headway') . ' ' . single_tag_title('', false) . '</span>'; 
			
			//Date Archives
			elseif (is_date() )
				echo $blog . ' &raquo; <span id="current-breadcrumb">' . __('Archives:', 'headway') . ' ' . get_the_time('F Y') . '</span>'; 

		//Close the breadcrumbs
		echo "\n</p>\n\n";

	}
	
	
}