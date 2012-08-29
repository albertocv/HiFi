<?php
class HeadwayDisplay {
	
	
	public static function init() {
		
		Headway::load(array(
			'display/head' => true,
			'display/grid-renderer'
		));
				
		if ( HeadwayRoute::is_visual_editor_iframe() ) {

			Headway::load('visual-editor/preview', 'VisualEditorPreview');

			HeadwayAdminBar::remove_admin_bar();

		}
		
	}
	
	
	public static function layout() {
	
		get_header();
		
		echo "\n\n";
						
			if ( current_theme_supports('headway-grid') ) {
		
				$layout = new HeadwayGridRenderer;
				$layout->display();
						
			} else {
			
				echo '<div class="alert alert-yellow"><p>The Headway Grid is not supported in this Child Theme.</p></div>';
			
			}
			
		echo "\n\n";
						
		get_footer();
		
	}
	
	
	/**
	 * Assembles the classes for the body element.
	 *
	 * @global object $wp_query
	 * @global object $current_user
	 * 
	 * @return string $c The body classes.
	 **/
	public static function body_class() {
		
		global $wp_query, $current_user;
		
		//Create the array and immediately put the custom class in.
		$c = array('custom');

		is_front_page()  ? $c[] = 'home'         : null;
		is_home()        ? $c[] = 'blog'         : null;
		is_date() 	     ? $c[] = 'archive'      : null;
		is_date()        ? $c[] = 'date'         : null;
		is_search()      ? $c[] = 'search'       : null;
		is_paged()       ? $c[] = 'paged'        : null;
		is_attachment()  ? $c[] = 'attachment'   : null;
		is_404()         ? $c[] = 'four04'		 : null;
		is_tag()		 ? $c[] = 'tag-archive'  : null;

		if ( !HeadwayCompiler::is_plugin_caching() ) {
			
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
			/* IE */
			if ( $ie_version = headway_is_ie() ) {
								
				$c[] = 'ie';
				$c[] = 'ie' . $ie_version;
				
			}
			
			/* Modern Browsers */
			if ( stripos($user_agent, 'Safari') !== false )
				$c[] = 'safari';
				
			elseif ( stripos($user_agent, 'Firefox') !== false )
				$c[] = 'firefox';
				
			elseif ( stripos($user_agent, 'Chrome') !== false )
				$c[] = 'chrome';
				
			elseif ( stripos($user_agent, 'Opera') !== false )
				$c[] = 'opera';

			/* Rendering Engines */
			if ( stripos($user_agent, 'WebKit') !== false )
				$c[] = 'webkit';
				
			elseif ( stripos($user_agent, 'Gecko') !== false )
				$c[] = 'gecko';
				
			/* Mobile */
			if ( stripos($user_agent, 'iPhone') !== false )
				$c[] = 'iphone';
			
			elseif ( stripos($user_agent, 'iPod') !== false )
				$c[] = 'ipod';
			
			elseif ( stripos($user_agent, 'iPad') !== false )
				$c[] = 'ipad';
				
			elseif ( stripos($user_agent, 'Android') !== false )
				$c[] = 'android';
			
		}
				

		if ( is_single() ) {
			
			$postID = $wp_query->post->ID;
			the_post();

			$c[] = 'single';

			if ( $cats = get_the_category() )
				foreach ( $cats as $cat )
					$c[] = 's-category-' . $cat->slug;

			$c[] = 's-author-' . sanitize_title_with_dashes(strtolower(get_the_author_meta('login')));
			
			//Add the custom classes from the meta box
			if ( $custom_css_class = HeadwayLayoutOption::get($postID, 'css-class', null) ) {
				
				$custom_css_classes = str_replace('  ', ' ', str_replace(',', ' ', htmlspecialchars(strip_tags($custom_css_class))));

				$c = array_merge($c, array_filter(explode(' ', $custom_css_classes)));
				
			}
			
			rewind_posts();
			
		} elseif ( is_author() ) {
			
			$author = $wp_query->get_queried_object();
			
			$c[] = 'author';
			$c[] = 'author-' . $author->user_nicename;
			
		} elseif ( is_category() ) {
			
			$cat = $wp_query->get_queried_object();
			
			$c[] = 'category';
			$c[] = 'category-' . $cat->slug;
			
		} elseif ( is_page() ) {
			
			$pageID = $wp_query->post->ID;
			$page_children = wp_list_pages("child_of=$pageID&echo=0");
			
			the_post();
			
			$c[] = 'page';
			$c[] = 'pageid-' . $pageID;
			$c[] = 'page-author-' . sanitize_title_with_dashes(strtolower(get_the_author_meta('login')));
			
			if ( $page_children != '' )
				$c[] = 'page-parent';
			if ( $wp_query->post->post_parent )
				$c[] = 'page-child parent-pageid-' . $wp_query->post->post_parent;
				
			//Add the slug
			$c[] = 'page-slug-' . $wp_query->post->post_name;
			
			//Add the custom classes from the meta box
			if ( $custom_css_class = HeadwayLayoutOption::get($pageID, 'css-class', null) ) {
				
				$custom_css_classes = str_replace('  ', ' ', str_replace(',', ' ', htmlspecialchars(strip_tags($custom_css_class))));

				$c = array_merge($c, array_filter(explode(' ', $custom_css_classes)));
				
			}
			
			rewind_posts();
			
		}
		
		if ( is_singular() )
			$c[] = 'post-type-' . $wp_query->post->post_type;

		if ( $current_user->ID )
			$c[] = 'loggedin';

		$c[] = 'layout-' . HeadwayLayout::get_current();
		$c[] = 'layout-using-' . HeadwayLayout::get_current_in_use();

		if ( HeadwayRoute::is_visual_editor_iframe() )
			$c[] = 've-iframe';
		
		if ( headway_get('ve-iframe-mode') && HeadwayRoute::is_visual_editor_iframe() )
			$c[] = 'visual-editor-mode-' . headway_get('ve-iframe-mode');

		if ( !current_theme_supports('headway-design-editor') )
			$c[] = 'design-editor-disabled';

		$c = join( ' ', apply_filters( 'body_class',  $c ) );

		return $c;
		
	}

	
	public static function html_open() {
				
		echo apply_filters('headway_doctype', '<!DOCTYPE HTML>');
		echo '<html '; language_attributes(); echo '>' . "\n";
		
		do_action('headway_html_open');
		
		echo "\n" . '<head>' . "\n";
		
	}


	public static function html_close() {
		
		echo "\n\n";
		
		do_action('headway_html_close');

		echo "\n" . '</html>';
		
	}
	
	
	public static function body_open() {	
			
		echo "\n" . '</head><!-- End <head> -->' . "\n\n";
		
		echo '<body class="' . self::body_class() . '">' . "\n\n";

		do_action('headway_body_open');

		echo "\n" . '<div id="whitewrap">' . "\n";
		
		do_action('headway_whitewrap_open');

		do_action('headway_page_start');
		
	}


	public static function body_close() {
		
		echo "\n\n";
		
		do_action('headway_whitewrap_close');

		echo '</div><!-- #whitewrap -->' . "\n";
		
		do_action('headway_body_close');
		
		echo "\n" . '</body>';
			
	}
	
	
}