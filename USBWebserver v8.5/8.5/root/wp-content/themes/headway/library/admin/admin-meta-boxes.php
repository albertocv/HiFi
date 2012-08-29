<?php
headway_register_admin_meta_box('HeadwayMetaBoxTemplate');
class HeadwayMetaBoxTemplate extends HeadwayAdminMetaBoxAPI {
	
	protected $id = 'template';
	
	protected $name = 'Template';
				
	protected $context = 'side';
			
	protected $inputs = array(
		'template' => array(
			'id' => 'template',
			'type' => 'select',
			'options' => array(),
			'description' => 'Assign a Headway template to this entry.  Templates can be added and modified in the Headway Visual Editor.',
			'blank-option' => '&ndash; Do Not Use Template &ndash;'
		)
	);

	protected function modify_arguments() {

		$this->inputs['template']['options'] = HeadwayLayout::get_templates();

	}
	
}

headway_register_admin_meta_box('HeadwayMetaBoxTitleControl');
class HeadwayMetaBoxTitleControl extends HeadwayAdminMetaBoxAPI {
	
	protected $id = 'alternate-title';
	
	protected $name = 'Title Control';
				
	protected $context = 'side';
			
	protected $inputs = array(
		'hide-title' => array(
			'id' => 'hide-title',
			'name' => 'Hide Title',
			'type' => 'select',
			'blank-option' => '&ndash; Do Not Hide Title &ndash;',
			'options' => array(
				'singular' => 'Hide on Single View',
				'list' => 'Hide in Index and Archives',
				'both' => 'Hide on Single View, Index, and Archives'
			),
			'description' => 'Choose whether or not you would like to hide the title for this entry.  This can be useful if you have advanced formatting in this entry.',
		),

		'alternate-title' => array(
			'id' => 'alternate-title',
			'name' => 'Alternate Title',
			'type' => 'text',
			'description' => 'Using the alternate page title, you can override the title that\'s displayed in the Content Block of the page.  Doing this, you can have a shorter page title in the navigation menu and <code>&lt;title&gt;</code>, but have a longer and more descriptive title in the actual page content.'
		)
	);
	
}


headway_register_admin_meta_box('HeadwayMetaBoxDisplay');
class HeadwayMetaBoxDisplay extends HeadwayAdminMetaBoxAPI {
	
	protected $id = 'display';
	
	protected $name = 'Display';
							
	protected $inputs = array(
		'css-class' => array(
			'id' => 'css-class',
			'name' => 'Custom CSS Class(es)',
			'type' => 'text',
			'description' => 'If you are familiar with <a href="http://www.w3schools.com/css/" target="_blank">CSS</a> and would like to style this entry by targeting a certain CSS class (or classes), then you may enter them in here.  The class will be added to the <strong>entry container\'s class</strong> along with the <strong>body class</strong> if only this entry is being viewed (i.g. single post or page view). Classes can be separated with spaces and/or commas.'
		)
	);
	
}


headway_register_admin_meta_box('HeadwayMetaBoxPostThumbnail');
class HeadwayMetaBoxPostThumbnail extends HeadwayAdminMetaBoxAPI {
	
	protected $id = 'post-thumbnail';
	
	protected $name = 'Featured Image Position';
			
	protected $context = 'side';

	protected $priority = 'low';
			
	protected $inputs = array(
		'position' => array(
			'id' => 'position',
			'name' => 'Featured Image Position',
			'type' => 'radio',
			'options' => array(
				'' => 'Use Block Default',
				'left' => 'Left',
				'right' => 'Right',
				'above-title' => 'Above Title',
				'above-content' => 'Above Content'
			),
			'description' => 'Set the position of the featured image for this entry.',
			'default' => '',
			'group' => 'post-thumbnail'
		),
	);
	
}


if ( !HeadwaySEO::plugin_active() )
	headway_register_admin_meta_box('HeadwayMetaBoxSEO');
class HeadwayMetaBoxSEO extends HeadwayAdminMetaBoxAPI {
	
	protected $id = 'seo';
	
	protected $name = 'Search Engine Optimization (SEO)';
		
	protected $post_type_supports_id = 'headway-seo';
	
	protected $priority = 'high';
			
	protected $inputs = array(
		'seo-preview' => array(
			'id' => 'seo-preview',
			'type' => 'seo-preview'
		),
		
		'title' => array(
			'id' => 'title',
			'group' => 'seo',
			'name' => 'Title',
			'type' => 'text',
			'description' => 'Custom <code>&lt;title&gt;</code> tag'
		),
		
		'description' => array(
			'id' => 'description',
			'group' => 'seo',
			'name' => 'Description',
			'type' => 'textarea',
			'description' => 'Custom <code>&lt;meta&gt;</code> description'
		),
		
		'noindex' => array(
			'id' => 'noindex',
			'group' => 'seo',
			'name' => '<code>noindex</code> this entry.',
			'type' => 'checkbox',
			'description' => 'Index/NoIndex tells the engines whether the entry should be crawled and kept in the engines\' index for retrieval. If you check this box to opt for <code>noindex</code>, the entry will be excluded from the engines.  <strong>Note:</strong> if you\'re not sure what this does, do not check this box.'
		),
		
		'nofollow' => array(
			'id' => 'nofollow',
			'group' => 'seo',
			'name' => '<code>nofollow</code> links in this entry.',
			'type' => 'checkbox',
			'description' => 'Follow/NoFollow tells the engines whether links on the entry should be crawled. If you check this box to employ "nofollow," the engines will disregard the links on the entry both for discovery and ranking purposes.  <strong>Note:</strong> if you\'re not sure what this does, do not check this box.'
		),
		
		'noarchive' => array(
			'id' => 'noarchive',
			'group' => 'seo',
			'name' => '<code>noarchive</code> links in this entry.',
			'type' => 'checkbox',
			'description' => 'Noarchive is used to restrict search engines from saving a cached copy of the entry. By default, the engines will maintain visible copies of all pages they indexed, accessible to searchers through the "cached" link in the search results.  Check this box to restrict search engines from storing cached copies of this entry.'
		),
		
		'nosnippet' => array(
			'id' => 'nosnippet',
			'group' => 'seo',
			'name' => '<code>nosnippet</code> links in this entry.',
			'type' => 'checkbox',
			'description' => 'Nosnippet informs the engines that they should refrain from displaying a descriptive block of text next to the entry\'s title and URL in the search results.'
		),
		
		'noodp' => array(
			'id' => 'noodp',
			'group' => 'seo',
			'name' => '<code>noodp</code> links in this entry.',
			'type' => 'checkbox',
			'description' => 'NoODP is a specialized tag telling the engines not to grab a descriptive snippet about a page from the Open Directory Project (DMOZ) for display in the search results.'
		),
		
		'noydir' => array(
			'id' => 'noydir',
			'group' => 'seo',
			'name' => '<code>noydir</code> links in this entry.',
			'type' => 'checkbox',
			'description' => 'NoYDir, like NoODP, is specific to Yahoo!, informing that engine not to use the Yahoo! Directory description of a page/site in the search results.'
		),
		
		'redirect-301' => array(
			'id' => 'redirect-301',
			'group' => 'seo',
			'name' => '301 Permanent Redirect',
			'type' => 'text',
			'description' => 'The 301 Permanent Redirect can be used to forward an old post or page to a new or different location.  If you ever move a page or change a page\'s permalink, use this to forward your visitors to the new location.<br /><br /><em>Want more information?  Read more about <a href="http://support.google.com/webmasters/bin/answer.py?hl=en&answer=93633" target="_blank">301 Redirects</a>.</em>'
		),
		
	);
	
	
	protected function input_seo_preview() {
		
		global $post;
		
		$date = get_the_time('M j, Y') ? get_the_time('M j, Y') : mktime('M j, Y');
		$date_text = ( $post->post_type == 'post' ) ? $date . ' ... ' : null;
		
		echo '<h4 id="seo-preview-title">Search Engine Result Preview</h4>';
			
			echo '<div id="seo-preview">';
				
				echo '<h4 title="Click To Edit">' . get_bloginfo('name') . '</h4>';
				echo '<p id="seo-preview-description" title="Click To Edit">' . $date_text . '<span id="text"></span></p>';
				
				echo '<p id="seo-preview-bottom"><span id="seo-preview-url">' . str_replace('http://', '', home_url()) . '</span> - <span>Cached</span> - <span>Similar</span></p>';
			
			echo '</div><!-- #seo-preview -->';
			
		echo '<small id="seo-preview-disclaimer">Remember, this is only a predicted search engine result preview.  There is no guarantee that it will look exactly this way.  However, it will look similar.</small>';
		
	}
	
	
	protected function input_text_with_counter($input) {
		
		echo '
			<tr class="label">
				<th valign="top" scope="row">
					<label for="' . $input['attr-id'] . '">' . $input['name'] . '</label>
				</th>
			</tr>

			<tr>
				<td>
					<input type="text" value="' . htmlspecialchars($input['value']) . '" id="' . $input['attr-id'] . '" name="' . $input['attr-name'] . '" />
				</td>
			</tr>
			
			<tr class="character-counter">
				<td>
					<span>130</span><div class="character-counter-box"><div class="character-counter-inside"></div></div>
				</td>
			</tr>
		';
			
	}
	
	
	protected function modify_arguments($post = false) {
		
		//Do not use this box if the page being edited is the front page since they can edit the setting in the configuration.
		if ( get_option('page_on_front') == headway_get('post') && get_option('show_on_front') == 'page' ) {
			
			$this->info = '<strong>Configure the SEO settings for this page (Front Page) in the Headway Search Engine Optimization settings tab in <a href="' . admin_url('admin.php?page=headway-options#tab-seo-content') . '" target="_blank">Headway &raquo; Configuration</a>.</strong>';
			
			$this->inputs = array();
			
			return;

		}
		
		//Setup the defaults for the title and checkboxes
		$current_screen = get_current_screen();
		$seo_templates_query = HeadwayOption::get('seo-templates', 'general', HeadwaySEO::output_layouts_and_defaults());
		$seo_templates = headway_get('single-' . $current_screen->id, $seo_templates_query, array());
		
		$title_template = str_replace(array('%sitename%', '%SITENAME%'), get_bloginfo('name'), headway_get('title', $seo_templates));
				
		echo '<input type="hidden" id="title-seo-template" value="' . $title_template . '" />';
				
		$this->inputs['noindex']['default'] = headway_get('noindex', $seo_templates);
		$this->inputs['nofollow']['default'] = headway_get('nofollow', $seo_templates);
		$this->inputs['noarchive']['default'] = headway_get('noarchive', $seo_templates);
		$this->inputs['nosnippet']['default'] = headway_get('nosnippet', $seo_templates);
		$this->inputs['noodp']['default'] = headway_get('noodp', $seo_templates);
		$this->inputs['noydir']['default'] = headway_get('noydir', $seo_templates);
		
		
	}

}