<?php
class HeadwayContentBlockDisplay {
		
	var $count = 0;	
		
	var $query = array();
	
	
	function __construct($block) {
		
		$this->block = $block;
		
		/* Bring in the WordPress pagination variable. */
		$this->paged = get_query_var('paged') ? get_query_var('paged') : 1;
		
		$this->add_hooks();

	}
	
	
	/**
	 * Created this function to make the call a little shorter.
	 **/
	function get_setting($setting, $default = null) {
		
		return HeadwayBlockAPI::get_setting($this->block, $setting, $default);
		
	}
	
	
	function add_hooks() {
				
		if ( !class_exists('pluginbuddy_loopbuddy') ) {
			
			add_filter('the_content_more_link', array($this, 'more_link'));		
		
			add_filter('excerpt_more', '__return_false');		
			add_filter('get_the_excerpt', array($this, 'add_excerpt_more_link'));
			
		}
			
		add_filter('the_content', array($this, 'nofollow_links_in_post'));				
				
	}
	

	function remove_hooks() {
		
		remove_filter('the_content_more_link', array($this, 'more_link'));			
		remove_filter('excerpt_more', '__return_false');
		remove_filter('get_the_excerpt', array($this, 'add_excerpt_more_link'));				
		remove_filter('the_content', array($this, 'nofollow_links_in_post'));
		
	}

	
	function display($args = array()) {
		
		//Since it's impossible to get the $wp_query in its correct form when loading the content from admin-ajax.php, we will display this notice.
		if ( headway_get('ve-live-content-query', $this->block, false) || HeadwayRoute::is_visual_editor_iframe() ) 
			echo '<div class="alert alert-yellow" style="margin: 5px;"><p><strong>Please note:</strong> What\'s being displayed here in the Content Block may not be correct.  When viewing the site outside of the Visual Editor, you will see the correct content.</p></div>';
				
		//If LoopBuddy is activated, we'll strictly rely on it for the query setup and how the content is displayed.
		if (class_exists('pluginbuddy_loopbuddy')) {
			
			global $pluginbuddy_loopbuddy;
			
			$loopbuddy_query = $this->get_setting('loopbuddy-query', -1);
			$loopbuddy_layout = $this->get_setting('loopbuddy-layout', -1);
						
			if ( isset($pluginbuddy_loopbuddy) && $loopbuddy_query !== -1 ) {
				echo $pluginbuddy_loopbuddy->render_loop($loopbuddy_query, $loopbuddy_layout);

				$this->remove_hooks();
				
				return;
			}
							
		}
		
		//Display the 404 text if it's a 404 (has to be default behavior)
		if ( is_404() && $this->get_setting('mode', 'default') == 'default' && !headway_get('ve-live-content-query', $this->block, false) ) {
			$this->remove_hooks();

			return $this->display_404();
		}
							
		//Display loop if all else fails
		$this->loop($args);
		$this->remove_hooks();
		
	}
	
	
	function loop($args = array()) {
		
		$defaults = array('archive' => false);
		extract($defaults);
		extract($args, EXTR_OVERWRITE);
						
		if ( !dynamic_loop() ) {
			
			$this->setup_query();
			
			$this->show_query_title();
			
			echo '<div class="loop">';	
			
				while ( $this->query->have_posts() ) {
				
					$this->query->the_post();
					
					$this->count++;
		
					$this->display_entry(array('count' => $this->count));
				
				}
									
			echo '</div>';
			
			$this->display_pagination();
			
		}
							
	}
	
	
	function show_query_title($echo = true) {
		
		/* Stop this function if it's a custom query, index, front page, or singular. */
		if ( $this->get_setting('mode', 'default') != 'default' || is_home() || is_front_page() || is_singular() )
			return;
			
		$queried_object = get_queried_object();
			
		$return = '';	
		
		/* Date Archives */
		if ( is_date() ) {
		
			$return .= '<h1 class="archive-title date-archive-title">';
			
				if ( is_day() )
					$return .= apply_filters('headway_archive_title', sprintf( __( 'Daily Archives: %s', 'headway' ), '<span>' . get_the_date() . '</span>'));
				
				elseif ( is_month() )
					$return .= apply_filters('headway_archive_title', sprintf( __( 'Monthly Archives: %s', 'headway' ), '<span>' . get_the_date('F Y') . '</span>'));
				
				elseif ( is_year() )
					$return .= apply_filters('headway_archive_title', sprintf( __( 'Yearly Archives: %s', 'headway' ), '<span>' . get_the_date('Y') . '</span>' ));
				
				else 
					$return .= apply_filters('headway_archive_title', __( 'Blog Archives', 'headway'));
					
			$return .= '</h1><!-- .archive-title -->';
						
		}
		
		/* Category Archives */
		if ( is_category() ) {
			
			$return .= '<h1 class="archive-title category-title">';
				$return .= apply_filters('headway_category_title', sprintf(__('Category Archives: %s', 'headway'), '<span>' . single_cat_title('', false) . '</span>'));
			$return .= '</h1><!-- .archive-title -->';
			
			$category_description = category_description();
			if ( !empty($category_description) )
				$return .= apply_filters('headway_category_archive_meta', '<div class="archive-meta category-archive-meta">' . $category_description . '</div>');
			
		}
		
		/* Author Archives */
		if ( is_author() ) {

			$author = get_queried_object();						
			$author_url = esc_url(get_the_author_meta('google_profile', $author->ID));
			
			$return .= '<h1 class="archive-title author-title">';
			
				if ( strpos($author_url, 'http') === 0 )
					$return .= sprintf(__( 'Author Archives: %s', 'headway'), '<span class="vcard"><a class="url fn n" href="' . $author_url . '" title="' . esc_attr($author->display_name) . '" rel="author">' . $author->display_name . '</a></span>');
					
				else
					$return .= sprintf(__( 'Author Archives: %s', 'headway'), '<span class="vcard">' . $author->display_name . '</span>');
				
			$return .= '</h1><!-- .archive-title -->';
			
		}
		
		/* Search */
		if ( is_search() ) {
			
			$return .= '<h1 class="archive-title search-title">';
				$return .= apply_filters('headway_search_title', sprintf(__('Search Results for: %s', 'headway'), '<span>' . get_search_query() . '</span>'));
			$return .= '</h1><!-- .archive-title -->';
			
		}
		
		/* Tag Archives */
		if ( is_tag() ) {
			
			$return .= '<h1 class="archive-title search-title">';
				$return .= apply_filters('headway_tag_title', sprintf(__('Tag Archives: %s', 'headway'), '<span>' . single_tag_title('', false) . '</span>'));
			$return .= '</h1><!-- .archive-title -->';

			$tag_description = tag_description();
			if ( !empty($tag_description) )
				echo apply_filters('headway_tag_archive_meta', '<div class="archive-meta tag-archive-meta">' . $tag_description . '</div>');
		
		}
		
		/* Custom Post Type Archives */
		if ( is_post_type_archive() ) {
						
			$return .= '<h1 class="archive-title post-type-archive-title">';
				$return .= apply_filters('headway_post_type_archive_title', $queried_object->labels->name);
			$return .= '</h1><!-- .archive-title -->';
			
		}
		
		/* Custom Taxonomy Archives */
		if ( is_tax() ) {
			
			$taxonomy = get_taxonomy($queried_object->taxonomy);
			$term = get_term($queried_object->term_id, $queried_object->taxonomy);
			
			$return .= '<h1 class="archive-title taxonomy-archive-title">';
				$return .= apply_filters('headway_taxonomy_archive_title', $taxonomy->labels->singular_name . ': <span>' . $term->name . '</span>');
			$return .= '</h1><!-- .archive-title -->';			
			
		}
		
		if ( !$echo )
			return apply_filters('headway_query_title', $return);
		else
			echo apply_filters('headway_query_title', $return);
		
	}
	
	
	function setup_query() {
				
		//If the query mode is default, just use $wp_query global and do what WordPress would normally do.
		//ALSO: if a block is brand new and just created in the visual editor (and hasn't been saved), then we'll force it into 
		//custom query.
		if ( $this->get_setting('mode', 'default') == 'default' && !headway_get('ve-live-content-query', $this->block, false) ) {
			
			global $wp_query;

			$this->query = $wp_query;

		//The mode is custom query so we have to set it all up.
		} else {
						
			/* Setup Query Options */
			$query_options = array();
			
			if ( $this->get_setting('mode', 'default') == 'custom-query' ) {
				
				//If we're just fetching a page, we can simply do that.  Otherwise, we have to use all of the query filters.
				if ( $this->get_setting('fetch-page-content', false) ) {

					$query_options['page_id'] = $this->get_setting('fetch-page-content', false);

				} else {

					//Categories
					if($this->get_setting('categories-mode', 'include') == 'include') 
						$query_options['category__in'] = $this->get_setting('categories', array());

					if($this->get_setting('categories-mode', 'include') == 'exclude') 
						$query_options['category__not_in'] = $this->get_setting('categories', array());	
					//Categories

					$query_options['post_type'] = $this->get_setting('post-type', false);

					//Post Limit
						$query_options['posts_per_page'] = $this->get_setting('number-of-posts', 10);
					//End Post Limit

					if ( is_array($this->get_setting('author')) )
						$query_options['author'] = trim(implode(',', $this->get_setting('author')), ', ');

					//Order by
					$query_options['orderby'] = $this->get_setting('order-by', 'date');
					$query_options['order'] = $this->get_setting('order', 'desc');
					//End order by

					$query_options['offset'] = $this->get_setting('offset', 0);

					if ( $this->get_setting('paginate', true) ) {
						
						$query_options['paged'] = $this->paged;

						if ($this->get_setting('offset', 0) >= 1 && $query_options['paged'] > 1){
							$query_options['offset'] = $this->get_setting('offset', 0) + $this->get_setting('number-of-posts', 10) * ($query_options['paged'] - 1);
						}
						
					}

				} //End else conditional for either page fetching or custom query filters
				
			//End if conditional checking that the mode is custom query.
			//If the mode isn't a custom query, then this is a ve-live-content-query so we can just simulate $wp_query.
			} else {
				
				$query_options = array(
					'showposts' => 10,
					'posts_per_page' => 10
				);
				
			}
			
			//Initiate query instance
			$this->query = new WP_Query($query_options);
			
		}
		
	}
	
		
	function display_entry($args = array()) {
		
		global $post;
		
		$defaults = array(
			'count' => false, 
			'single' => false
		);
		
		$args = array_merge($defaults, $args);
		
		$post_id = get_the_id();
		$post_class = $this->entry_class();
		$post_permalink = get_permalink();
		$post_title_tooltip = sprintf( esc_attr__( 'Permalink to %s', 'headway' ), the_title_attribute( 'echo=0' ) );
		$post_type = get_post_type();
		
		$alternate_title = HeadwayLayoutOption::get($post_id, 'alternate-title', false, false);
		$hide_title = HeadwayLayoutOption::get($post_id, 'hide-title', false, false);

		$post_title = ( $post_type == 'page' && $alternate_title ) ? $alternate_title : get_the_title();
		
		$entry_meta_above = $this->parse_meta($this->get_setting('entry-meta-above', 'Posted on %date% by %author% &bull; %comments%'));
		$entry_utility_below = $this->parse_meta($this->get_setting('entry-utility-below', 'Filed Under: %categories%'));

		//Show <h1> for titles if it's a singlular page, use <h3> for archives, and <h2> for everything else.
		if ( is_singular() && $this->get_setting('mode', 'default') == 'default' )
			$title_tag = 'h1';
		elseif ( is_archive() || is_search() )
			$title_tag = 'h3';
		else
			$title_tag = 'h2';
		
		//If the post is singular or the post type is a page being displayed through content fetching, don't put a link in the title.
		if ( is_singular() && $this->get_setting('mode', 'default') != 'custom-query' )
			$post_title_link = $post_title;	
		else
			$post_title_link = '<a href="' . $post_permalink . '" title="' . $post_title_tooltip . '" rel="bookmark">' . $post_title . '</a>';	
		
		if ( $this->get_setting('show-entry', true) ) {
	
			do_action('headway_before_entry', $args);		

			echo '<div id="post-' . $post_id . '" class="' . $post_class . '">';

					do_action('headway_entry_open', $args);		

					//Show post thumbnail
					$this->display_thumbnail($post, 'above-title');

					do_action('headway_before_entry_title', $args);			

					//Show the title based on the Show Titles option
					if ( 
						$this->get_setting('show-titles', true) 
						&& !($hide_title == 'singular' && $title_tag == 'h1') 
						&& !($hide_title == 'list' && $title_tag != 'h1') 
						&& !($hide_title == 'both')
					) {

						echo '<' . $title_tag . ' class="entry-title">';
							
							echo $post_title_link;

							if ( apply_filters('headway_show_edit_link', $this->get_setting('show-edit-link', true)) )
								edit_post_link('Edit Entry');

						echo '</' . $title_tag . '>';

					}

					do_action('headway_after_entry_title', $args);			

					$entry_meta_display_post_types = $this->get_setting('show-entry-meta-post-types', array('post'));

					//Only show meta on posts and do not show the meta if it is empty.
					if ( is_array($entry_meta_display_post_types) && in_array($post_type, $entry_meta_display_post_types) && $entry_meta_above )
						echo '<div class="entry-meta entry-meta-above">' . $entry_meta_above . '</div><!-- .entry-meta -->';

					$this->display_thumbnail($post, 'above-content');

					$this->display_entry_content($args);

					//Only show meta on posts and do not show it if the meta is empty.
					if ( is_array($entry_meta_display_post_types) && in_array($post_type, $entry_meta_display_post_types) && $entry_utility_below )
						echo '<div class="entry-utility entry-utility-below entry-meta">' . $entry_utility_below . '</div><!-- .entry-utility -->';

					do_action('headway_entry_close', $args);			

				echo '</div><!-- #post-' . $post_id . ' -->';

				do_action('headway_after_entry', $args);
				
				$this->display_post_navigation();		
		
		} //show-entry check			
	
		$this->display_comments($args);

	}
	
	
	function display_entry_content($args) {
		
		$entry_content_display = $this->get_setting('entry-content-display', 'normal');
		
		$show_full_entries = false;
		$show_excerpts = false;
	
		if ( $entry_content_display == 'hide' )
			return null;
		
		/* Figure out whether the full entry or excerpt should be displayed */
			if ( $entry_content_display == 'full-entries' ) {
				
				$show_full_entries = true;
			
			} elseif ( $entry_content_display == 'excerpts' ) {
				
				$show_excerpts = true;
				
			} elseif ( $args['count'] > $this->get_setting('featured-posts', 1) && !(is_singular() && $this->get_setting('mode', 'default') == 'default') ) {
				
			 	$show_excerpts = true;
			
			} elseif ( is_archive() || is_search() || $this->paged > 1 ) {
				
				$show_excerpts = true;
				
			} else {
				
				$show_full_entries = true;
				
			}
		
		
		do_action('headway_before_entry_content', $args);
		
		if ( $show_full_entries ) {

			echo '<div class="entry-content">';

				the_content();

				wp_link_pages(array( 'before' => '<div class="page-link">' . __( 'Pages:', 'headway' ), 'after' => '</div>' ));

			echo '</div><!-- .entry-content -->';

		} elseif ( $show_excerpts ) {

			echo '<div class="entry-summary entry-content">';

				the_excerpt();

			echo '</div><!-- .entry-summary.entry-content -->';

		}
		
		do_action('headway_after_entry_content', $args);
		
	}
	
	
	function display_404() {
		
		$args = array(
			'404' => true
		);
		
		$post_id = 'system-404';
		$post_class = 'page system-page system-404 hentry';
		
		do_action('headway_before_entry', $args);		

		echo '<div id="post-' . $post_id . '" class="' . $post_class . '">';

			do_action('headway_entry_open', $args);		

			do_action('headway_before_entry_title', $args);			

				echo '<h1 class="entry-title">' . __('Whoops!  Page Not Found', 'headway') . '</h1>';

			do_action('headway_after_entry_title', $args);			

			do_action('headway_before_entry_content', $args);			

				echo '<div class="entry-content">';

					echo __('<p>Don\'t fret, you didn\'t do anything wrong.  It appears that the page you are looking for does not exist or has been moved elsewhere.</p>', 'headway');
					
					echo sprintf(__('<p>If you keep ending up here, please head back to our <a href="%s">homepage</a> or try the search form below.</p>', 'headway'), home_url());
										
					get_search_form(true);

				echo '</div><!-- .entry-content -->';

			do_action('headway_after_entry_content', $args);

			do_action('headway_entry_close', $args);			

			echo '</div><!-- #post-' . $post_id . ' -->';

		do_action('headway_after_entry', $args);
		
	}
	
	
	function display_comments($hook_args) {		
				
		/* If the block is set to always hide the comments, then don't do any more checks. */
		if ( $this->get_setting('comments-visibility', 'auto') == 'hide' )
			return false;
		
		
		/* Only do these checks if the visibility is set to auto. */
		if ( $this->get_setting('comments-visibility', 'auto') == 'auto' ) {
			
			global $post;
			$post_type = get_post_type();
			
			if ( !is_singular() )
			 	return false;

			if ( $post_type != 'post' )
				return false;

			if ( $this->get_setting('mode', 'default') == 'custom-query' )
				return false;
			
		}
		
		/* We're all good.  Show the comments. */
		do_action('headway_before_entry_comments', $hook_args);		
		
		global $withcomments;
		$withcomments = true;
		
		comments_template();
		
		do_action('headway_after_entry_comments', $hook_args);		
	
	}
	
	
	function display_pagination($position = 'below') {
						
	 	if ( $this->query->max_num_pages <= 1 )
			return;
					
		echo '<div id="nav-' . $position . '" class="loop-navigation loop-utility loop-utility-' . $position . '">';
			
			/* If wp_pagenavi() plugin is activated, just use it. */
			if ( function_exists('wp_pagenavi') ) {
				
				wp_pagenavi();
				
			} else {
				
				$older_posts_text = __('<span class="meta-nav">&larr;</span> Older posts', 'headway');
				$newer_posts_text = __('Newer posts <span class="meta-nav">&rarr;</span>', 'headway');
				
				echo '<div class="nav-previous">' . get_next_posts_link($older_posts_text, $this->query->max_num_pages) . '</div>';
				echo '<div class="nav-next">' . get_previous_posts_link($newer_posts_text) . '</div>';
				
			}
		
		echo '</div><!-- #nav-' . $position . ' -->';

		
	}


	function display_thumbnail($post, $area = 'above-title') {

		if ( !has_post_thumbnail() || !$this->get_setting('show-post-thumbnails', true) )
			return;

		$entry_thumbnail_position = $this->get_setting('use-entry-thumbnail-position', true) ? HeadwayLayoutOption::get($post->ID, 'position', 'post-thumbnail') : false;
		$position = $entry_thumbnail_position ? $entry_thumbnail_position : $this->get_setting('post-thumbnail-position', 'left');

		if ( ($area == 'above-content' && $position != 'above-content') || ($area == 'above-title' && $position == 'above-content') )
			return;

		/* Get the size for cropping */
			if ( $position == 'left' || $position == 'right' ) {
				$thumbnail_width = $this->get_setting('post-thumbnail-size', 125);
				$thumbnail_height = $thumbnail_width;
			} else {
				$thumbnail_width = HeadwayBlocksData::get_block_width($this->block);
				$thumbnail_height = $thumbnail_width * .35;
			}

		/* Get the image URL */
			if ( $this->get_setting('crop-post-thumbnails', true) ) {

				$thumbnail = apply_filters('headway_featured_image_src', wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'));  

				$thumbnail_url = apply_filters('headway_featured_image_url', headway_resize_image($thumbnail[0], $thumbnail_width, $thumbnail_height));

			} else {

				$thumbnail = apply_filters('headway_featured_image_src', wp_get_attachment_image_src(get_post_thumbnail_id(), array(
					$thumbnail_width, 
					$thumbnail_height
				)));  

				$thumbnail_url = apply_filters('headway_featured_image_url', $thumbnail[0]);
				$thumbnail_width = $thumbnail[1];
				$thumbnail_height = $thumbnail[2];

			}

		echo '
			<a href="' . get_permalink() . '" class="post-thumbnail post-thumbnail-' . $position . '">
				<img src="' . esc_url($thumbnail_url) . '" alt="' . get_the_title() . '" width="' . $thumbnail_width . '" height="' . $thumbnail_height . '" />
			</a>
		';	

	}
	
	
	function display_post_navigation() {
		
		if ( !is_single() )
			return false;
			
		if ( !$this->get_setting('show-single-post-navigation', true) )
			return false;
			
		if ( $this->get_setting('mode', 'default') == 'custom-query' )
			return false;
			
		echo '<div id="nav-below" class="loop-navigation single-post-navigation loop-utility loop-utility-below">';
		
			/* If wp_pagenavi() plugin is activated, just use it. */
			if ( function_exists('wp_pagenavi') ) {
				
				wp_pagenavi();
				
			} else {
				
				echo '<div class="nav-previous">';
					previous_post_link('%link', '<span class="meta-nav">&larr;</span> %title', false);
				echo '</div>';
				
				echo '<div class="nav-next">';
					next_post_link('%link', '%title <span class="meta-nav">&rarr;</span>', false);
				echo '</div>';
				
			}
		
		echo '</div><!-- #nav-below -->';
		
		
	}
	
	
	function parse_meta($meta) {
		
		global $post, $authordata;
		
		$date_format = $this->get_setting('date-format', 'wordpress-default');
		$date = ($date_format != 'wordpress-default') ? get_the_time($date_format) : get_the_date();

		$time_format = $this->get_setting('time-format', 'wordpress-default');
		$time = ($date_format != 'wordpress-default') ? get_the_time($time_format) : get_the_time();

		if ( (int)get_comments_number($post->ID) === 0 ) 
			$comments_format = stripslashes($this->get_setting('comment-format-0', '%num% Comments'));
		elseif ( (int)get_comments_number($post->ID) == 1 ) 
			$comments_format = stripslashes($this->get_setting('comment-format-1', '%num% Comment'));
		elseif ( (int)get_comments_number($post->ID) > 1 ) 
			$comments_format = stripslashes($this->get_setting('comment-format', '%num% Comments'));
		
		$comments = str_replace('%num%', get_comments_number($post->ID), $comments_format);
		$respond_format = stripslashes($this->get_setting('respond-format', 'Leave a comment!'));

		$date = '<span class="entry-date published" title="' . get_the_time('c') . '">' . $date . '</span>';
		$time = '<span class="entry-time">' . $time . '</span>';
		
		$comments_link = '<a href="'.get_comments_link() . '" title="'.get_the_title() . ' Comments" class="entry-comments">' . $comments . '</a>';
		$comments_no_link = $comments;
		$respond = '<a href="'.get_permalink() . '#respond" title="Respond to '.get_the_title() . '" class="entry-respond">' . $respond_format . '</a>';
		
		$author = '<a class="author-link fn nickname url" href="'.get_author_posts_url($authordata->ID) . '" title="View all posts by ' . $authordata->display_name . '">' . $authordata->display_name . '</a>';
		$author_no_link = $authordata->display_name;
		
		$categories = get_the_category_list(', ');
		$tags = (get_the_tags() != NULL) ? get_the_tag_list(__('<span class="tag-links"><span>Tags:</span> ', 'headway'),', ','</span>') : '';
		
		$meta = str_replace(array(
			'%date%',
			'%time%',
			'%comments%',
			'%comments_no_link%',
			'%respond%',
			'%author%',
			'%author_no_link%',
			'%categories%',
			'%tags%',
			'%edit%'
		), array(
			$date,
			$time,
			$comments_link,
			$comments_no_link,
			$respond,
			$author,
			$author_no_link,
			$categories,
			$tags,
			null
		), $meta);

		return apply_filters('headway_meta', $meta);
		
	}
	
	
	/**
	 * Assembles the classes for the posts.
	 *
	 * @global object $post
	 * @global int $blog_post_alt
	 * 
	 * @param bool $print Determines whether or not to echo the post classes.
	 * 
	 * @return bool|string If $print is true, then echo the classes, otherwise just return them as a string. 
	 **/
	function entry_class() {

		global $post, $blog_post_alt, $authordata;
		
		$c = get_post_class();

		if ( !isset($blog_post_alt) ) 
			$blog_post_alt = 1;

		$c[] = 'author-' . sanitize_title_with_dashes(strtolower($authordata->user_login));

		if ( ++$blog_post_alt % 2 )
			$c[] = 'alt';
			
		//Add the custom classes from the meta box
		if ( $custom_css_class = HeadwayLayoutOption::get(get_the_id(), 'css-class', null) ) {
			
			$custom_css_classes = str_replace('  ', ' ', str_replace(',', ' ', htmlspecialchars(strip_tags($custom_css_class))));

			$c = array_merge($c, array_filter(explode(' ', $custom_css_classes)));
			
		}

		$c = join(' ', $c);

		return $c;

	}
	
	
	function more_link($more_link) {
		
		global $post;
		
		$more_text = $this->get_setting('read-more-text', 'Continue Reading');
		$more_link = '<a href="'. get_permalink($post->ID) . '" class="more-link">' . $more_text . '</a>';
		
		return apply_filters('headway_more_link', '<span class="more-link-ellipsis">...  </span>' . $more_link);

	}
	
	
	function add_excerpt_more_link($excerpt) {
		
		$more_link = apply_filters('the_content_more_link', null);
		
		return $excerpt . $more_link;
		
	}
	
	
	function nofollow_links_in_post($text) {
		
		if ( !HeadwaySEO::is_seo_checkbox_enabled('nofollow', get_the_id()) )
			return $text;
		
		preg_match_all("/<a.*? href=\"(.*?)\".*?>(.*?)<\/a>/i", $text, $links);
		$match_count = count($links[0]);
		
		for ( $i=0; $i < $match_count; $i++ ) {
			
			if ( !preg_match("/rel=[\"\']*nofollow[\"\']*/",$links[0][$i]) ) {
				
				preg_match_all("/<a.*? href=\"(.*?)\"(.*?)>(.*?)<\/a>/i", $links[0][$i], $link_text);
				
				$text = str_replace('>' . $link_text[3][0] . '</a>', ' rel="nofollow">' . $link_text[3][0] . '</a>', $text);
				
			}
			
		}

		return $text;

	}
	
	
}