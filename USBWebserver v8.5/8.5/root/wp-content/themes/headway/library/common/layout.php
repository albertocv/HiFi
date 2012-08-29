<?php
class HeadwayLayout {
	
	
	/**
	 * Returns current layout
	 * 
	 * @return mixed
	 **/
	public static function get_current() {
		
		//If the user is viewing the site through the iframe and the mode is set to Layout, then display that exact layout.
		if ( headway_get('ve-iframe-layout') && HeadwayRoute::is_visual_editor_iframe() ) 
			return headway_get('ve-iframe-layout');
		
		return end(self::get_current_hierarchy());
		
	}
	
	
	/**
	 * Traverses up the hierarchy tree to figure out which layout is being used.
	 * 
	 * @return mixed
	 **/
	public static function get_current_in_use() {
		
		//If the user is viewing the site through the iframe and the mode is set to Layout, then display that exact layout.
		if ( headway_get('ve-iframe-layout') && HeadwayRoute::is_visual_editor_iframe() ) 
			return headway_get('ve-iframe-layout');
		
		//Get hierarchy
		$hierarchy = array_reverse(self::get_current_hierarchy());
				
		//Loop through entire hierarchy to find which one is customized or has a template
		foreach ( $hierarchy as $layout ) {
			
			$status = self::get_status($layout);
			
			//If the layout isn't customized or using a template, skip to next, otherwise we return the current layout in the next line.
			if ( $status['customized'] === false && $status['template'] === false )
				continue;
				
			//If the layout has a template assigned to it, use the template.  Templates will take precedence over customized status.
			if ( $status['template'] )
				return 'template-' . $status['template'];	
				
			//If it's a customized layout, then use the layout itself after making sure there are blocks on the layout
			if ( $status['customized'] && count(HeadwayBlocksData::get_blocks_by_layout($layout)) > 0 )
				return $layout;
			
		}
		
		//If there's still not a customized layout, loop through the top-level layouts and find the first one that's customized.
		$top_level_layouts = array(
			'index',
			'single',
			'archive',
			'four04'
		);
				
		if ( get_option('show_on_front') == 'page' )
			$top_level_layouts[] = 'front_page';

		foreach ( $top_level_layouts as $top_level_layout ) {
						
			$status = self::get_status($top_level_layout);
			
			if ( $status['customized'] === false && $status['template'] === false )
				continue;
			
			//If the layout has a template assigned to it, use the template.  Templates will take precedence over customized status.
			if ( $status['template'] )
				return 'template-' . $status['template'];	
				
			//If it's a customized layout and the layout has blocks, then use the layout itself
			if ( $status['customized'] && count(HeadwayBlocksData::get_blocks_by_layout($top_level_layout)) > 0 )
				return $top_level_layout;
			
		}

		//If there STILL isn't a customized layout, just return the top level of the current layout.
		return end($hierarchy);
		
	}
	
	
	/**
	 * Returns name of the current layout being viewed.
	 * 
	 * @return string
	 **/
	public static function get_current_name() {
														
		return self::get_name(self::get_current());
		
	}
	
	
	/**
	 * Returns the current hierarchy. 
	 * 
	 * @return array
	 **/
	public static function get_current_hierarchy() {
				
		$current_layout = array();
		$queried_object = get_queried_object();
				
		//Now the fun begins
		if ( is_home() || ( get_option('show_on_front') == 'posts' && is_front_page() ) ) {
			
			$current_layout[] = 'index';
			
		} elseif ( is_front_page() && !is_home() ) {
									
			$current_layout[] = 'front_page';
						
		} elseif ( is_singular() ) {

			$post = $queried_object;
			$post_type = get_post_type_object($post->post_type);
			
			$current_layout[] = 'single';
			$current_layout[] = 'single-' . $post_type->name;

			$posts = array(
				$post->ID
			);

			while ( $post->post_parent != 0 ) {

				$post = get_post($post->post_parent);
				$posts[] = $post->ID;

			}

			foreach ( array_reverse($posts) as $post_id )
				$current_layout[] = 'single-' . $post_type->name . '-' . $post_id;
					
		} elseif ( is_archive() || is_search() ) {
			
			$current_layout[] = 'archive';
			
			if ( is_date() ) {
				
				$current_layout[] = 'archive-date';
				
			} elseif ( is_author() ) {
								
				$current_layout[] = 'archive-author';
				$current_layout[] = 'archive-author-' . $queried_object->ID;
				
			} elseif ( is_category() ) {

				$category = $queried_object;
				$ancestor_categories = array();
				
				$current_layout[] = 'archive-category';

				/* Ancestor categories */
					while ( $category->category_parent != 0 ) {
						$category = get_category($category->category_parent);
						$ancestor_categories[] = $category->term_id;
					}

					foreach ( array_reverse($ancestor_categories) as $ancestor_category_id )
						$current_layout[] = 'archive-category-' . $ancestor_category_id;

				/* Original queried category */
				$current_layout[] = 'archive-category-' . $queried_object->term_id;
				
			} elseif ( is_search() ) {
				
				$current_layout[] = 'archive-search';
				
			} elseif ( is_tag() ) {
				
				$current_layout[] = 'archive-post_tag';
				$current_layout[] = 'archive-post_tag-' . $queried_object->term_id;
				
			} elseif ( is_tax() ) {
				
				$current_layout[] = 'archive-taxonomy';
				$current_layout[] = 'archive-taxonomy-' . $queried_object->taxonomy;
				$current_layout[] = 'archive-taxonomy-' . $queried_object->taxonomy . '-' . $queried_object->term_id;
				
			} elseif ( is_post_type_archive() ) {
				
				$current_layout[] = 'archive-post_type';
				$current_layout[] = 'archive-post_type-' . $queried_object->name;
				
			}
			
		} elseif ( is_404() ) {

			$current_layout[] = 'four04';

		}		
		
		//I think we're finally done.
		return $current_layout;

	}
		
		
	/**
	 * Returns friendly name of the layout specified.
	 * 
	 * @return string
	 **/
	public static function get_name($layout) {
		
		if ( !$layout )
			return null;
		
		$layout_parts = explode('-', $layout);
		$id = end($layout_parts);
		
		switch($layout_parts[0]) {

			case 'front_page':
				return 'Front Page';
			break;
			
			case 'index':
				return 'Blog Index';
			break;
			
			case 'single':
				if ( $id == 'single' )
					return 'Single';
										
				if ( is_numeric($id) )
					return get_the_title($id) ? get_the_title($id) : '(No Title)';
				
				//If everything else hasn't triggered, then it's a post type
				$id = str_replace('single-', '', $layout);
				$post_type = get_post_type_object($id);
				
				return $post_type->labels->singular_name;
			break;
			
			case 'archive':
				if ( $id == 'archive' )
					return 'Archive';
						
				switch($layout_parts[1]) {
					
					case 'category':
						if ( $id == 'category' )
							return 'Category';
														
						$term = get_term($id, 'category');
							
						return $term->name ? $term->name : '(No Title)';
					break;
					
					case 'search':
						return 'Search';
					break;
					
					case 'date':
						return 'Date';
					break;
					
					case 'author':
						if ( $id == 'author' )
							return 'Author';
						
						$user_data = get_userdata($id);
					
						return $user_data->display_name;
					break;
					
					case 'post_tag':
						if ( $id == 'post_tag' ) 
							return 'Post Tag';
														
						$term = get_term($id, 'post_tag');
						
						return $term->name ? $term->name : '(No Title)';
					break;
					
					case 'taxonomy':
						if ( $id == 'taxonomy' ) 
							return 'Taxonomy';

						$taxonomy_fragments = explode('-', str_replace('archive-taxonomy-', '', $layout));

						if ( is_numeric(end($taxonomy_fragments)) ) {

							$term_id = array_pop($taxonomy_fragments);
														
							$term = get_term($term_id, implode('-', $taxonomy_fragments));
							
							return isset($term->name) ? $term->name : '(No Title)';
							
						} elseif ( $taxonomy = get_taxonomy(implode('-', $taxonomy_fragments)) ) {
														
							return $taxonomy->labels->singular_name ? $taxonomy->labels->singular_name : '(No Title)';
							
						}
					break;
					
					case 'post_type':
						if ( $id == 'post_type' )
							return 'Post Type';
													
						//If everything else hasn't triggered, then it's a post type
						$id = str_replace('archive-post_type-', '', $layout);
						$post_type = get_post_type_object($id);

						if ( !is_object($post_type) )
							return null;

						return $post_type->labels->singular_name;
					break;
					
					case 'post_format':
						if ( $id == 'post_format' )
							return 'Post Format';
														
						$term = get_term($id, 'post_format');
							
						return $term->name;
					break;
					
				}
			
			break;
			
			case 'four04':
				return '404 Layout';
			break;
			
			case 'template':
				$templates = self::get_templates();
				
				if ( isset($templates[$layout_parts[1]]) )
					return $templates[$layout_parts[1]];
				else
					return null;
			break;
			
		}
		
		return false;
				
	}
	
	
	/**
	 * Gets the status of the layout.  This will tell if it's customized, using a template, or none of the previous mentioned.
	 * 
	 * @return string
	 **/
	public static function get_status($layout) {
												
		$customized = ( HeadwayLayoutOption::get($layout, 'customized') === true ) ? true : false;	
		$template = ( HeadwayLayoutOption::get($layout, 'template') !== null ) ? HeadwayLayoutOption::get($layout, 'template') : false;
					
		$status = array(
			'customized' => $customized,
			'template' => $template
		);		

		return $status;
		
	}
	

	/**
	 * Returns all pages and their hierarchy for listing.
	 * 
	 * @return array
	 **/
	public static function get_pages() {
		
		$layouts = array();
		
		if ( get_option('show_on_front') == 'page' )
			$layouts['front_page'] = array();
			
		$layouts['index'] = array();
		$layouts['single'] = array();
		$layouts['archive'] = array();
		$layouts['four04'] = array();
		
		//Queries
		$post_types = get_post_types(array('public' => true), 'objects');
		
		//Single			
			foreach($post_types as $post_type) {
				
				$layouts['single']['single-' . $post_type->name] = self::get_pages_posts($post_type->name);
				
			}
              
		//Archives
			$layouts['archive'] = array(
				'archive-category' => self::get_pages_terms('category'),
				'archive-search' => array(),
				'archive-date' => array(),
				'archive-author' => array(),
				'archive-post_tag' => self::get_pages_terms('post_tag'),
				'archive-taxonomy' => array(),
				'archive-post_type' => array(),
				'archive-post_format' => self::get_pages_terms('post_format')
			);
			
			
			//Authors
				$author_query = get_users(array('who' => 'author'));
				$authors = array();
				
				foreach($author_query as $author) {
			
					$layouts['archive']['archive-author']['archive-author-' . $author->ID] = array();
			
				}
			
			
			//Taxonomies and Terms
				$taxonomies_query = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
				$exclude = array('link_category');	
				$taxonomies = array();

				foreach($taxonomies_query as $slug => $taxonomy) {

					$layouts['archive']['archive-taxonomy']['archive-taxonomy-' . $slug] = self::get_pages_terms($slug, true);

				}
		
						
			//Post Types
				$excluded_post_types = array('post', 'page', 'attachment');
		
				foreach($post_types as $post_type) {
			
					//If excluded, skip it
					if ( in_array($post_type->name, $excluded_post_types) )
						continue;
		
					$layouts['archive']['archive-post_type']['archive-post_type' . '-' . $post_type->name] = array();
			
				}
			
					
		return $layouts;
                
	}	

		
		/** 
		 * Recursive function to find terms and their children.
		 * 
		 * @see HeadwayLayout::get_pages()
		 * 
		 * @return array
		 **/
		public static function get_pages_terms($taxonomy, $add_taxonomy_to_id = false, $term_parent = 0) {
							
			if ( HeadwayOption::get('layout-selector-safe-mode') && $taxonomy != 'category' )
				return null;
				
			$query = get_terms($taxonomy, array('parent' => $term_parent));
			$terms = array();
		
			foreach ($query as $term) {
			
				//Add taxonomy prefix if set
				$taxonomy_id = $add_taxonomy_to_id ? 'taxonomy-' . $taxonomy : $taxonomy;
			
				$terms['archive-' . $taxonomy_id . '-' . $term->term_id] = self::get_pages_terms($taxonomy, $add_taxonomy_to_id, $term->term_id);
			
			}
		
			return $terms;
		
		}
	
	
		/** 
		 * Recursive function to find posts/pages and their children.
		 * 
		 * @see HeadwayLayout::get_pages()
		 * 
		 * @return array
		 **/
		public static function get_pages_posts($post_type = 'post', $post_parent = 0) {
			
			if ( HeadwayOption::get('layout-selector-safe-mode') && $post_type != 'page' )
				return null;
		
			$query = get_posts(array(
				'post_type' => $post_type, 
				'post_parent' => $post_parent,
				'numberposts' => 99999
			));
			
			$posts = array();
				
			foreach ($query as $post)
				$posts['single-' . $post_type . '-' . $post->ID] = self::get_pages_posts($post_type, $post->ID);
		
			return $posts;
		
		}
	
	
	/** 
	 * Simple function to query for all Headway layout templates from the database.
	 * 
	 * @return array
	 **/
	public static function get_templates() {
		
		$templates = HeadwayOption::get('list', 'templates', array());
		
		return $templates;
		
	}


	/**
	 * Get the **BEST** URL for a layout ID
	 **/
	public static function get_url($layout) {

		$layout_fragments = explode('-', $layout);

		switch ( $layout_fragments[0] ) {

			/* Blog Index */
			case 'index':

				if ( get_option('show_on_front') == 'page' && get_option('page_for_posts') )
					return get_permalink(get_option('page_for_posts'));

				return home_url();

			break;


			/* Front Page */
			case 'front_page':

				return home_url();

			break;


			/* Singles */
			case 'single':

				/* If an ID is provided, go straight to it */
				if ( isset($layout_fragments[2]) && $permalink = get_permalink($layout_fragments[2]) )
					return $permalink;

				/* Otherwise, go to the post type (force post if post type isn't present... i.g. layout is just "single") */
				$post_type = isset($layout_fragments[1]) ? $layout_fragments[1] : 'post';

				$query = get_posts(array(
					'numberposts' => 1,
					'post_type' => $post_type
				));

				if ( $permalink = get_permalink($query[0]->ID) )
					return $permalink;

			break;


			/* Archives */
			case 'archive':

				$type = isset($layout_fragments[1]) ? $layout_fragments[1] : 'category';

				switch ( $type ) {

					case 'category':

						/* Category ID provided */
						if ( isset($layout_fragments[2]) && is_numeric($layout_fragments[2]) ) {

							$cat = $layout_fragments[2];

						/* No category ID provided, get one with posts */
						} else {

							$categories = get_terms('category', array(
								'orderby' => 'count',
								'order' => 'desc',
								'hide_empty' => true
							));

							$cat = $categories[0]->term_id;

						}

						return home_url('?cat=' . $cat);
					
					break;

					case 'date':

						return home_url('?m=' . date('Y'));

					break;

					case 'author':

						/* Author ID Provided */
						if ( isset($layout_fragments[2]) && is_numeric($layout_fragments[2]) ) {

							$author_id = $layout_fragments[2];

						/* Author ID not provided, use logged in user */
						} else {

							$current_user = wp_get_current_user();
							$author_id = $current_user->ID;

						}

						return home_url('?author=' . $author_id);

					break;

					case 'search':

						/* Provide a word that will likely pull up some content */
						return home_url('?s=and');

					break;

					case 'post_type':

						if ( isset($layout_fragments[2]) && $post_type = $layout_fragments[2] )
							return home_url('?post_type=' . $post_type);

					break;

					case 'taxonomy':

						/* Taxonomy provided */
						if ( isset($layout_fragments[2]) && $tax = $layout_fragments[2] ) {

							/* Term Provided */
							if ( isset($layout_fragments[3]) ) {

								$term = get_term($layout_fragments[3], $tax);
								$term_slug = $term->slug;

							/* No term provided */
							} else {

								$terms = get_terms($tax, array(
									'orderby' => 'count',
									'order' => 'desc',
									'hide_empty' => true
								));

								$term_slug = $terms[0]->slug;

							}

							return home_url('?' . $tax . '=' . $term_slug);

						}

					break;

				}

			break;


			/* 404 */
			case 'four04':

				if ( !get_option('permalink_structure') )
					return wp_die('You must have a permalink structure other than default to view and customize your 404 layout.<br /><br />You may modify your permalink settings in <a href="' . admin_url('options-permalink.php') . '" target="_blank">Settings &raquo; Permalinks</a>.');

				return home_url('404trigger-' . rand(100, 99999));

			break;

		} /* End $layout switch */

		/* Catch All Default */
		return home_url();

	}


}