<?php
class HeadwaySEO {
	
	
	public static function init() {
		
		add_filter('get_comment_author_link', array(__CLASS__, 'comment_rel_nofollow'));
		
	}
	
	
	public static function is_private() {
		
		if ( get_option('blog_public') == '0' )
			return true;
		
		return false;
		
	}
	
	
	public static function plugin_active() {
	
		if ( defined('WPSEO_VERSION') )
			return 'wpseo';
		
		if ( class_exists('All_in_One_SEO_Pack') ) 
			return 'aioseop';
			
		return false;
		
	}
	
	
	/**
	 * Displays the title.  Parses the variables.
	 **/
	public static function output_title($title) {
		
		if ( is_feed() )
			return null;
				
		if ( self::plugin_active() )
			return $title;
		
		$seo_templates_query = HeadwayOption::get('seo-templates', 'general', self::output_layouts_and_defaults());
		$seo_templates = headway_get(HeadwaySEO::current_seo_layout(), $seo_templates_query, array());
		
		if ( HeadwayLayoutOption::get(false, 'title', 'seo', false) )
			$title = HeadwayLayoutOption::get(false, 'title', 'seo', false);
			
		elseif ( headway_get('title', $seo_templates) )
			$title = headway_get('title', $seo_templates);
			
		/* If the template is %tagline% | %sitename% and there is no tagline, then remove the tagline and pipe character */
		if ( $title === '%tagline% | %sitename%' && get_bloginfo('description') == false )
			$title = '%sitename%';
					
		return HeadwaySEO::parse_seo_variables($title);
		
	}
	
	
	public static function output_meta() {
		
		if ( self::plugin_active() ) 
			return false;

		$meta = '';
		
		$seo_templates = self::get_seo_templates(self::current_seo_layout());
										
		//Description
		if ( HeadwayLayoutOption::get(false, 'description', 'seo', null) !== null )
			$meta .= "\n" . '<meta name="description" content="' . self::parse_seo_variables(HeadwayLayoutOption::get(false, 'description', 'seo', false)) . '" />';
			
		elseif ( headway_get('description', $seo_templates) )
			$meta .= "\n" . '<meta name="description" content="' . self::parse_seo_variables(headway_get('description', $seo_templates)) . '" />';
			
		//Robots
		$robot_settings = array();
		
		if ( self::is_seo_checkbox_enabled('noindex') )
			$robot_settings[] = 'noindex';
			
		if ( self::is_seo_checkbox_enabled('noarchive') )
			$robot_settings[] = 'noarchive';
			
		if ( self::is_seo_checkbox_enabled('nosnippet') )
			$robot_settings[] = 'nosnippet';
			
		if ( self::is_seo_checkbox_enabled('noodp') )
			$robot_settings[] = 'noodp';
			
		if ( self::is_seo_checkbox_enabled('noydir') )
			$robot_settings[] = 'noydir';
		
		if ( count($robot_settings) !== 0 )
			$meta .= "\n" . '<meta name="robots" content="' . implode(',', $robot_settings) . '" />';		

		if ( strlen($meta) !== 0 )
			echo "\n\n" . '<!-- Headway SEO Juice -->' . $meta . "\n";

		do_action('headway_seo_meta');
		
	}
	
	
	public static function is_seo_checkbox_enabled($option, $layout = false) {
		
		$seo_templates = self::get_seo_templates(self::current_seo_layout());
		
		if ( HeadwayLayoutOption::get($layout, $option, 'seo', null) === null && !headway_get($option, $seo_templates) )
			return false;

		if ( HeadwayLayoutOption::get($layout, $option, 'seo', null) === false )
			return false;
			
		return true;
		
	}
	
	
	/**
	 * Filter that removes nofollow from the comment author URLs.
	 *
	 * @param string $url URL to be filtered.
	 * 
	 * @return string $url URL after being filtered.
	 **/
	public static function comment_rel_nofollow($url) {
		
		if ( HeadwayOption::get('nofollow-comment-author-url') ) 
			return $url;
		
		return str_replace("rel='external nofollow'", "rel='external'", $url);
		
	}
	
	
	public static function current_seo_layout() {
		
		/* Since the SEO templates are only at a certain level, checking the real layout against the SEO templates would not work. */
		
		$layout_hierarchy = HeadwayLayout::get_current_hierarchy();
				
		if ( count($layout_hierarchy) === 1 )
			return $layout_hierarchy[0];
		elseif ( count($layout_hierarchy) > 1 )
			return $layout_hierarchy[1];
			
		return null;
		
	}
	
	
	public static function parse_seo_variables($content) {
		
		$tagline = get_option('blogdescription');
		$sitename = get_option('blogname');
		
		$queried_object = get_queried_object();

		//Figure out the title variable
		if ( is_front_page() || ( is_home() && get_option('show_on_front') != 'page' ) )
			$title = 'Home';
			
		elseif ( is_home() && get_option('show_on_front') == 'page' )
			$title = 'Blog';
			
		elseif ( is_singular() )
			$title = get_the_title();

		elseif ( is_archive() ) {
			
			if ( is_category() ) {
			
				$title = single_cat_title('', false);
				
			} elseif ( is_date() ) {
				
				if ( is_day() )
					$title = get_the_time(get_option('date_format'));
			 	elseif ( is_month() )
					$title = get_the_time('F Y');
				elseif ( is_year() )
					$title = get_the_time('Y');
				
			} elseif ( is_author() ) {

				$author = get_queried_object();
				$title = $author->display_name;

			} elseif ( is_tag() ) {
				
				$title = single_tag_title('', false);
				
			} elseif ( is_tax() ) {
				
				$taxonomy = get_taxonomy($queried_object->taxonomy);
				$term = get_term($queried_object->term_id, $queried_object->taxonomy);
				
				$title = $taxonomy->labels->singular_name;
				$meta = $term->name;
								
			} elseif ( is_post_type_archive() ) {
				
				$post_type = get_post_type_object($queried_object->name);
									
				$title = $post_type->labels->singular_name;
				
			}
			
		} elseif ( is_search() )
			$title = get_search_query();
			
		elseif ( is_404() )
			$title = '404';
			
		elseif ( is_feed() )
			$title = 'Feed';
			
					
		if ( isset($title) ) {
			
			$search = array(
				'%title%',
				'%category%',
				'%archive%',
				'%search%',
				'%author%',
				'%tag%',
				'%post_type_plural%',
				'%taxonomy%'			
			);

			//Replace title variables
			$content = str_ireplace($search, $title, $content);
			
		}
		
		if ( isset($meta) ) {
			
			$search = array(
				'%meta%'
			);
			
			$content = str_ireplace($search, $meta, $content);
			
		}
		
		$content = str_ireplace('%sitename%', $sitename, $content);
		$content = str_ireplace('%tagline%', $tagline, $content);
		
		return htmlspecialchars(stripslashes(strip_tags($content)));
				
	}
	
	
	public static function get_seo_templates($layout = false) {
		
		$seo_templates_query = HeadwayOption::get('seo-templates', 'general', self::output_layouts_and_defaults());
		
		if ( $layout )
			return headway_get(self::current_seo_layout(), $seo_templates_query, array());
		else
			return $seo_templates_query;
				
	}
	
	
	public static function output_layouts_and_defaults() {
		
		if ( get_option('show_on_front') == 'page' ) {
		
			$pages = array(
				'front_page' => array(
					'title' => '%tagline% | %sitename%'
				),
				'index'	=> array(
					'title' => '%tagline% | %sitename%'
				),
			);
			
	 	} else {
			
			$pages = array(
				'index'	=> array(
					'title' => '%tagline% | %sitename%'
				)
			);
		
		} 
	
		//Custom Post Types
		$excluded_post_type_archives = array('post', 'page', 'attachment');
		$post_types = get_post_types(array('public' => true), 'objects');

		foreach($post_types as $post_type) {
			$pages['single-' . $post_type->name] = array(
				'title' => '%title% | %sitename%'
			);
		
			//If excluded post type archive, skip it
			if ( !in_array($post_type->name, $excluded_post_type_archives) )
				$pages['archive-post_type-' . $post_type->name] = array(
					'title' => '%post_type_plural% | %sitename%'
				);
		}

		//Archives
		$pages = array_merge($pages, array(
			'archive-category' => array(
				'title' => '%title% | %sitename%'
			),
			'archive-search' => array(
				'title' => 'Search: %title% | %sitename%',
				'noarchive' => true
			),
			'archive-date' => array(
				'title' => '%title% | %sitename%'
			),
			'archive-author' => array(
				'title' => '%title% | %sitename%'
			),
			'archive-post_tag' => array(
				'title' => 'Tag: %title% | %sitename%'
			),
			'archive-taxonomy' => array(
				'title' => '%title%: %meta% | %sitename%'
			),
			'four04' => array(
				'title' => 'Whoops! Page Not Found | %sitename%'
			)
		));
			
		return $pages;
	
	}

	
}