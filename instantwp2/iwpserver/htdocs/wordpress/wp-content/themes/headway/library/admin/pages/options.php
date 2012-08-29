<h2 class="nav-tab-wrapper big-tabs-tabs">
	<a class="nav-tab" href="#tab-general">General</a>
	<a class="nav-tab" href="#tab-seo">Search Engine Optimization</a>
	<a class="nav-tab" href="#tab-scripts">Scripts/Analytics</a>
	<a class="nav-tab" href="#tab-visual-editor">Visual Editor</a>
	<a class="nav-tab" href="#tab-advanced">Advanced</a>
</h2>

<?php do_action('headway_admin_save_message'); ?>

<form method="post">
	<input type="hidden" value="<?php echo wp_create_nonce('headway-admin-nonce'); ?>" name="headway-admin-nonce" id="headway-admin-nonce" />

	<div class="big-tabs-container">
	
	
		<div class="big-tab" id="tab-general-content">
			
			<?php
			$license_validation_request = headway_validate_license_key();
			$license_validation = null;

			if ( $error = headway_get('error', $license_validation_request) )
				$license_validation = '<span class="red license-key-validation">' . $error . '</span>';
			elseif ( $success = headway_get('success', $license_validation_request) )
				$license_validation = '<span class="green license-key-validation">' . $success . '</span>';

			$form = array(	
				array(
					'id' => 'license-key',
					'size' => 'large',
					'value' => HeadwayOption::get('license-key'),
					'type' => 'text',
					'masked' => true, /* change to a password field */
					'label' => 'License Key',
					'description' => 'Please enter your license key here.  This will be used to authenticate your site so you can take advantage of the automatic updates, Headway Extend, and more.<br /><br />You may find your license key in the <a href="http://headwaythemes.com/dashboard" target="_blank">Headway Members Dashboard</a>.',
					'suffix' => $license_validation
				)
			);

			HeadwayAdminInputs::generate($form, 'form-table license-key-input-table');
			?>

			<h3 class="title title-hr">General</h3>
		
			<?php
			$form = array(	
				array(
					'id' => 'favicon',
					'size' => 'large',
					'type' => 'text',
					'label' => 'Favicon URL',
					'value' => HeadwayOption::get('favicon'),
					'description' => 'A favicon is the little image that sits next to your address in the favorites menu and on tabs.  If you do not know how to save an image as an icon you can go to <a href="http://www.favicon.cc/" target="_blank">favicon.cc</a> and draw or import an image.'
				),

				array(
					'id' => 'feed-url',
					'size' => 'large',
					'type' => 'text',
					'label' => 'Feed URL',
					'description' => 'If you use any service like <a href="http://feedburner.google.com/" target="_blank">FeedBurner</a>, type the feed URL here.',
					'value' => HeadwayOption::get('feed-url')
				)
			);
			
			HeadwayAdminInputs::generate($form);
			?>

			<h3 class="title title-hr">Admin Preferences</h3>
		
			<?php
			$form = array(	
				array(
					'id' => 'menu-setup',
					'type' => 'radio',
					'label' => 'Default Admin Page',
					'value' => HeadwayOption::get('menu-setup', false, 'getting-started'),
					'radios' => array(
						array(
							'value' => 'getting-started',
							'label' => 'Getting Started'
						),
					
						array(
							'value' => 'visual-editor',
							'label' => 'Visual Editor'
						),
					
						array(
							'value' => 'options',
							'label' => 'Options'
						)
					),
					'description' => 'Select which admin page you would like to be directed to when you click on "Headway" in the WordPress Admin.'
				)
			);
		
			HeadwayAdminInputs::generate($form);
			?>

			<h3 class="title title-hr">Affiliate Promotion</h3>
					
			<?php
			$form = array(	
				array(
					'id' => 'affiliate-link',
					'size' => 'large',
					'value' => HeadwayOption::get('affiliate-link'),
					'type' => 'text',
					'label' => 'Affiliate Link',
					'description' => 'If you are a member of the Headway Affiliate program (if not, you should definitely <a href="http://headwaythemes.com/affiliates/" target="_blank">sign up now!</a>), you can paste your affiliate link (found at the top of the affiliate panel) and earn money when someone purchases Headway through your affiliate link.  <strong>Do NOT put HTML in this field.</strong>'
				)
			);
 
			HeadwayAdminInputs::generate($form);
			?>
		
		</div><!-- #tab-general-content -->
	
		
		<div class="big-tab" id="tab-seo-content">
										
			<?php
			if ( HeadwaySEO::plugin_active() ) {

				switch ( HeadwaySEO::plugin_active() ) {

					case 'aioseop':
						echo '<div class="alert alert-yellow"><p>Headway has detected that you are using the All In One SEO pack plugin.  In order to reduce conflicts and save resources, Headway\'s SEO functionality has been disabled.</p></div>';
					break;

					case 'wpseo':
						echo '<div class="alert alert-yellow"><p>Headway has detected that you are using Yoast\'s WordPress SEO plugin.  In order to reduce conflicts and save resources, Headway\'s SEO functionality has been disabled.</p></div>';
					break;

				}

			} else {
			?>	
				
				<h3 class="title" id="seo-templates-title">SEO Templates</h3>	

				<div id="seo-templates">
					<div id="seo-templates-hidden-inputs">
						<?php
						/* SETUP THE TYPES OF SEO TEMPLATE INPUTS */
						$seo_template_inputs = array(
							'title',
							'description',
							'noindex',
							'nofollow',
							'noarchive',
							'nosnippet',
							'noodp',
							'noydir'
						);

						/* GENERATE HIDDEN INPUTS */
						$seo_options = HeadwayOption::get('seo-templates', 'general', array());

						foreach (HeadwaySEO::output_layouts_and_defaults() as $page => $defaults) {

							foreach ($seo_template_inputs as $input) {

								$name_attr = 'name="headway-admin-input[seo-templates][' . $page . '][' . $input . ']"';

								$default = isset($defaults[$input]) ? $defaults[$input] : null;

								$page_options = headway_get($page, $seo_options, array());
								$value = headway_get($input, $page_options, $default);

								echo '<input type="hidden" id="seo-' . $page . '-' . $input . '"' . $name_attr . ' value="' . stripslashes(htmlspecialchars($value)) . '" />';

							}

						}
						?>
					</div>

					<div id="seo-templates-header">
						<span>Select a Template:</span>
						<select>
							<option value="index">Blog Index</option>

							<?php
							if ( get_option('show_on_front') == 'page' )
								echo '<option value="front_page">Front Page</option>';
							?>

							<optgroup label="Single">
								<?php
								$post_types = get_post_types(array('public' => true), 'objects');

								foreach($post_types as $post_type)
									echo '<option value="single-' . $post_type->name . '">' . $post_type->label . '</option>';
								?>
							</optgroup>

							<optgroup label="Archive">
								<option value="archive-category">Category</option>
								<option value="archive-search">Search</option>
								<option value="archive-date">Date</option>
								<option value="archive-author">Author</option>
								<option value="archive-post_tag">Post Tag</option>
								<option value="archive-taxonomy">Taxonomy</option>
							</optgroup>

							<option value="four04">404</option>

						</select>
					</div><!-- #seo-templates-header -->

					<div id="seo-templates-inputs">

						<?php
						$form = array(	
							array(
								'id' => 'title',
								'type' => 'text',
								'size' => 'large',
								'label' => 'Title',
								'description' => 'The title is the main text that describes the page. It is the single most important on-page SEO element (behind overall content).  The title appears at the top of the web browser when viewing the page, in browser tabs, search engine results, and external websites.  <strong>Tip:</strong> it is best that the title stays below 70 characters.<br /><br /><a href="http://www.seomoz.org/learn-seo/title-tag" target="_blank">Learn more about Titles &raquo;</a>',
								'no-submit' => true
							),

							array(
								'id' => 'description',
								'type' => 'paragraph',
								'cols' => 60,
								'rows' => 3,
								'label' => '<code>&lt;meta&gt;</code> Description',
								'description' => 'Meta description tags, while not important to search engine rankings, are extremely important in gaining user click-through from search engine result pages (SERPs). These short paragraphs are your opportunity to advertise content to searchers and let them know exactly what the given page has with regard to what they’re looking for. <strong>Tip:</strong> a good description is around 150 characters.<br /><br /><a href="http://www.seomoz.org/learn-seo/meta-description" target="_blank">Learn more about &lt;meta&gt; Descriptions &raquo;</a>',
								'no-submit' => true
							)
						);

						HeadwayAdminInputs::generate($form);
						?>

						<div class="hr"></div>

						<p><strong>You may use the following variables in the Title and Description inputs above:</strong></p>

						<ul>
							<li><code>%title%</code> &mdash; Will retrieve the title of whatever post, archive, or page is being displayed.</li>
							<li><code>%sitename%</code> &mdash; Will retrieve the name of the site.  This can be set in <a href="<?php echo admin_url('options-general.php'); ?>" target="_blank">Settings &raquo; General</a>.</li>
							<li><code>%tagline%</code> &mdash; Will retrieve the tagline/description of the site.  This can be set in <a href="<?php echo admin_url('options-general.php'); ?>" target="_blank">Settings &raquo; General</a>.</li>
							<li><code>%meta%</code> &mdash; Used only on taxonomy archives to display the term name.</li>
						</ul>

						<h3 id="seo-templates-advanced-options-title" class="title title-hr">Advanced Options <span>Show &darr;</span></h3>

						<div id="seo-templates-advanced-options">
							<?php
							$form = array(	
								array(
									'type' => 'checkbox',
									'label' => 'Page Indexing',
									'checkboxes' => array(
										array(
											'id' => 'noindex',
											'label' => 'Enable <code>noindex</code>',
											'no-submit' => true
										)
									),
									'description' => 'Index/NoIndex tells the engines whether the page should be crawled and kept in the engines\' index for retrieval. If you check this box to opt for <code>noindex</code>, the page will be excluded from the engines.  <strong>Note:</strong> if you\'re not sure what this does, do not check this box.'
								),

								array(
									'type' => 'checkbox',
									'label' => 'Link Following',
									'checkboxes' => array(
										array(
											'id' => 'nofollow',
											'label' => 'Enable <code>nofollow</code>',
											'no-submit' => true
										)
									),
									'description' => 'Follow/NoFollow tells the engines whether links on the page should be crawled. If you check this box to employ "nofollow," the engines will disregard the links on the page both for discovery and ranking purposes.  <strong>Note:</strong> if you\'re not sure what this does, do not check this box.'
								),

								array(
									'type' => 'checkbox',
									'label' => 'Page Archiving',
									'checkboxes' => array(
										array(
											'id' => 'noarchive',
											'label' => 'Enable <code>noarchive</code>',
											'no-submit' => true
										)
									),
									'description' => 'Noarchive is used to restrict search engines from saving a cached copy of the page. By default, the engines will maintain visible copies of all pages they indexed, accessible to searchers through the "cached" link in the search results.  Check this box to restrict search engines from storing cached copies of this page.'
								),

								array(
									'type' => 'checkbox',
									'label' => 'Snippets',
									'checkboxes' => array(
										array(
											'id' => 'nosnippet',
											'label' => 'Enable <code>nosnippet</code>',
											'no-submit' => true
										)
									),
									'description' => 'Nosnippet informs the engines that they should refrain from displaying a descriptive block of text next to the page\'s title and URL in the search results.'
								),

								array(
									'type' => 'checkbox',
									'label' => 'Open Directory Project',
									'checkboxes' => array(
										array(
											'id' => 'noodp',
											'label' => 'Enable <code>NoODP</code>',
											'no-submit' => true
										)
									),
									'description' => 'NoODP is a specialized tag telling the engines not to grab a descriptive snippet about a page from the Open Directory Project (DMOZ) for display in the search results.'
								),

								array(
									'type' => 'checkbox',
									'label' => 'Yahoo! Directory',
									'checkboxes' => array(
										array(
											'id' => 'noydir',
											'label' => 'Enable <code>NoYDir</code>',
											'no-submit' => true
										)
									),
									'description' => 'NoYDir, like NoODP, is specific to Yahoo!, informing that engine not to use the Yahoo! Directory description of a page/site in the search results.'
								)
							);

							HeadwayAdminInputs::generate($form);
							?>
						</div><!-- #seo-templates-advanced-options -->

					</div><!-- #seo-templates-inputs -->
				</div><!-- #seo-templates-content -->

				<div id="seo-description" class="alert alert-yellow"><p>Unfamiliar with <em>Search Engine Optimization</em>?  <a href="http://www.seomoz.org/beginners-guide-to-seo/" target="_blank">Learn More &raquo;</a></p></div>			

				<h3 class="title title-hr">Content <code>nofollow</code> Links</h3>	

				<?php
				$form = array(	
					array(
						'type' => 'checkbox',
						'label' => 'Comment Authors\' URL',
						'checkboxes' => array(
							array(
								'id' => 'nofollow-comment-author-url',
								'label' => 'Add nofollow To Comment Authors\' URL',
								'checked' => HeadwayOption::get('nofollow-comment-author-url', 'general', false)
							)
						),
						'description' => 'Adding nofollow to the comment authors\' URLs will tell search engines to not visit their website and to stay on yours. Many bloggers frown upon this, which can sometimes discourage comments. Only enable this if you are 100% sure you know you want to.'
					)
				);

				HeadwayAdminInputs::generate($form);
				?>
			
			<?php	
			}
			?>
				
		</div><!-- #tab-seo -->
	
		
		<div class="big-tab" id="tab-scripts-content">
			
			<?php
			$form = array(	
				array(
					'id' => 'header-scripts',
					'type' => 'paragraph',
					'cols' => 90,
					'rows' => 8,
					'label' => 'Header Scripts',
					'description' => 'Anything here will go in the <code>&lt;head&gt;</code> of the website. If you are using <a href="http://google.com/analytics" target="_blank">Google Analytics</a>, paste the code provided here. <strong>Do not place plain text in this!</strong>',
					'allow-tabbing' => true,
					'value' => HeadwayOption::get('header-scripts')
				),
				
				array(
					'id' => 'footer-scripts',
					'type' => 'paragraph',
					'cols' => 90,
					'rows' => 8,
					'label' => 'Footer Scripts',
					'description' => 'Anything here will be inserted before the <code>&lt;/body&gt;</code> tag of the website. <strong>Do not place plain text in this!</strong>',
					'allow-tabbing' => true,
					'value' => HeadwayOption::get('footer-scripts')
				)
			);

			HeadwayAdminInputs::generate($form);
			?>
			
		</div><!-- #tab-scripts-content -->
	
	
		<div class="big-tab" id="tab-visual-editor-content">
					
			<?php
			$form = array(	
				array(
					'type' => 'checkbox',
					'label' => 'Tooltips',
					'checkboxes' => array(
						array(
							'id' => 'disable-visual-editor-tooltips',
							'label' => 'Disable Tooltips in the Visual Editor',
							'checked' => HeadwayOption::get('disable-visual-editor-tooltips', false, false)
						)
					),
					'description' => 'If you ever feel that the tooltips are too invasive in the visual editor, you can disable them here.  Tooltips are the black speech bubbles that appear to assist you when you are not sure what an option is or how it works.'
				),
				
				array(
					'type' => 'checkbox',
					'label' => 'Live CSS Editor Syntax Highlighting',
					'checkboxes' => array(
						array(
							'id' => 'disable-codemirror',
							'label' => 'Disable Syntax Highlighing for the Live CSS Editor',
							'checked' => HeadwayOption::get('disable-codemirror', false, false)
						)
					),
					'description' => 'Headway uses the CodeMirror syntax highlighting library to power the Live CSS editor.  If you find the Live CSS editor causing your browser to crash or if you simply prefer to go without the syntax highlighter, you can disable it here.'
				),
				
				array(
					'type' => 'checkbox',
					'label' => 'Layout Selector Safe Mode',
					'checkboxes' => array(
						array(
							'id' => 'layout-selector-safe-mode',
							'label' => 'Enable Layout Selector Safe Mode',
							'checked' => HeadwayOption::get('layout-selector-safe-mode', false, false)
						)
					),
					'description' => 'On sites that have a very high number of posts (1,000+), sometimes the Visual Editor will have difficultly loading due to the Layout Selector trying to populate itself with the posts and other pages.  Enable Layout Selector Safe Mode to exclude everything except for pages from the Layout Selector.<br /><br/><strong>Refrain from using this option unless you are certain it addresses the issue.</strong>'
				)
			);
		
			HeadwayAdminInputs::generate($form);
			?>
			
		</div>
		
	
		<div class="big-tab" id="tab-advanced-content">
			
			<h3 class="title">Caching &amp; Compression</h3>
			
			<?php
			$form = array(	
				array(
					'type' => 'checkbox',
					'label' => 'Asset Caching',
					'checkboxes' => array(
						array(
							'id' => 'disable-caching',
							'label' => 'Disable Headway Caching',
							'checked' => HeadwayOption::get('disable-caching', false, false)
						)
					),
					'description' => 'By default, Headway will attempt to cache all CSS and JavaScript that it generates.  However, there may be rare circumstances where disabling the cache will help with certain issues.<br /><br /><em><strong>Important:</strong> Disabling the Headway cache will cause an <strong>increase in page load times</strong> and <strong>increase the strain your web server</strong> will undergo on every page load.'
				),
				
				array(
					'type' => 'checkbox',
					'label' => 'Dependency Query Variables',
					'checkboxes' => array(
						array(
							'id' => 'remove-dependency-query-vars',
							'label' => 'Remove Query Variables from Dependency URLs',
							'checked' => HeadwayOption::get('remove-dependency-query-vars', false, false)
						)
					),
					'description' => 'To leverage browser caching, Headway can tell WordPress to not put query variables on static assets such as CSS and JavaScript files.'
				),

				array(
					'type' => 'checkbox',
					'label' => '<code>gzip</code> Compression',
					'checkboxes' => array(
						array(
							'id' => 'enable-gzip',
							'label' => 'Enable <code>gzip</code> Compression',
							'checked' => HeadwayOption::get('enable-gzip', false, true)
						)
					),
					'description' => 'By default, gzip compression is on.  <code>gzip</code> compression will greatly reduce the amount of time it takes to load your website.  However, there may be special circumstances where it is necessary to disable it.'
				)
			);
		
			HeadwayAdminInputs::generate($form);
			?>
						
			<h3 class="title title-hr">Admin</h3>
			
			<?php
			$form = array(	
				array(
					'type' => 'checkbox',
					'label' => 'Version Number',
					'checkboxes' => array(
						array(
							'id' => 'hide-menu-version-number',
							'label' => 'Hide Headway Version Number From Menu',
							'checked' => HeadwayOption::get('hide-menu-version-number', false, false)
						)
					),
					'description' => 'Check this if you wish to have the Menu say "Headway" instead of "Headway ' .HEADWAY_VERSION . '"'
				),
				
				array(
					'type' => 'checkbox',
					'label' => 'Update Notices',
					'checkboxes' => array(
						array(
							'id' => 'disable-update-notices',
							'label' => 'Disable Headway Update Notices',
							'checked' => HeadwayOption::get('disable-update-notices', false, false)
						)
					),
					'description' => 'If you wish to hide the notices that appear when an update is available for Headway, check this.'
				),
				
				array(
					'type' => 'checkbox',
					'label' => 'Editor Style',
					'checkboxes' => array(
						array(
							'id' => 'disable-editor-style',
							'label' => 'Disable Editor Style',
							'checked' => HeadwayOption::get('disable-editor-style', false, false)
						)
					),
					'description' => 'By default, Headway will take any settings in the Design Editor and add them to <a href="http://codex.wordpress.org/TinyMCE" target="_blank">WordPress\' TinyMCE editor</a> style.  Use this option to prevent that.'
				)
			);
		
			HeadwayAdminInputs::generate($form);
			?>
			
			<h3 class="title title-hr">Debugging</h3>
			
			<div class="alert alert-red"><p>The following option should only be checked if a member of the Headway Themes team asks you to do so.</p></div>
			
			<?php
			$form = array(	
				array(
					'type' => 'checkbox',
					'label' => 'Debug Mode',
					'checkboxes' => array(
						array(
							'id' => 'debug-mode',
							'label' => 'Enable Debug Mode',
							'checked' => HeadwayOption::get('debug-mode', false, false)
						)
					),
					'description' => 'Having Debug Mode enabled will allow the Headway Themes team to access the Visual Editor for support purposes, but <strong>will not allow changes to be saved<strong>.'
				)
			);
		
			HeadwayAdminInputs::generate($form);
			?>
			
		</div>
		
		
	</div>

	<div class="hr hr-submit" style="display: none;"></div>

	<p class="submit" style="display: none;">
		<input type="submit" name="headway-submit" value="Save Changes" class="button-primary headway-save" />
	</p>
	
</form>