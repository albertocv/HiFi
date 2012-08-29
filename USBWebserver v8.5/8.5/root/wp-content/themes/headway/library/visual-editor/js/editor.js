(function($) {
$(document).ready(function() {
			
	/* INIT */
		/* Make the title talk */
		startTitleActivityIndicator();
		
		/* If the layout is in the URL as a hash, retrieve it, change the active layout in layout selector, and change the currently editing */
		if ( window.location.hash.indexOf('#layout=') !== -1 ) {
		
			var layoutID = window.location.hash.replace('#layout=', '');
			var layoutSelectorNode = $('div#layout-selector span.layout[layout_id="' + layoutID + '"]');

			switchToLayout(layoutSelectorNode, false);
		
		}
		
		setLoadingBar(20, 'Initiating');
		
		/* Create the new object and initiate the mode and the iframe */
		Headway.iframe = $('iframe#content');
		Headway.instance = new window['visualEditorMode' + Headway.mode.capitalize()]();
		
		if ( typeof Headway.instance.init == 'function' )
			Headway.instance.init();

		/* iframe focusing and blurring */
		Headway.iframe.bind('mouseleave', function() {
			$(this).trigger('blur');
		});

		Headway.iframe.bind('mouseenter mousedown', function() {
			//If there is another textarea/input that's focused, don't focus the iframe.
			if ( $('textarea:focus, input:focus').length === 1 )
				return;

			$(this).trigger('focus');
		});

		/* Load the iframe */
		loadIframe(Headway.instance.iframeCallback);
		
		/* Parse the JSON in the Headway l10n array */
		Headway.blockTypeURLs = $.parseJSON(Headway.blockTypeURLs.replace(/&quot;/g, '"'));
		Headway.coreBlockTypes = $.parseJSON(Headway.coreBlockTypes.replace(/&quot;/g, '"'));
		Headway.allBlockTypes = $.parseJSON(Headway.allBlockTypes.replace(/&quot;/g, '"'));
		Headway.ranTour = $.parseJSON(Headway.ranTour.replace(/&quot;/g, '"'));
	/* END INIT */
	

	/* MODE SWITCHING */
		$('ul#modes li a').bind('click', function(){
			$(this).attr('href', $(this).attr('href') + '#layout=' + Headway.currentLayout);
		});
	/* END MODE SWITCHING */


	/* VIEW SITE BUTTON */
		$('#menu-link-view-site a').bind('click', function(){
			$(this).attr('href', Headway.homeURL + '/?headway-trigger=layout-redirect&layout=' + Headway.currentLayout);
		});
	/* END MODE SWITCHING */
	
	
	/* SAVE BUTTON */
		$('span#inactive-save-button').click(function() {
			
			if ( Headway.iframe.grid('blockIntersectCheck', $i('.block')[0]) == false )
				showNotification('There are overlapping/touching blocks.  Please separate all blocks.', 4000, true);
			
			event.preventDefault();
			
		});
	
	
		$('span#save-button').click(function() {
			
			save();
		
			return false;
			
		});
	
	
		$('span#save-button').hover(function() {
					
			/* If currently saving, do not animate. */
			if ( (typeof currentlySaving !== 'undefined' && currentlySaving === true) || (isSavingAllowed === false) ) {
				return false;
			}
		
			$('span#save-button').stop(true).animate({boxShadow: '0 0 10px #00ffde'}, 250);
		
		}, function(){
				
			/* If currently saving, do not animate. */
			if ( (typeof currentlySaving !== 'undefined' && currentlySaving === true) || (isSavingAllowed === false) ) {
				return false;
			}
		
			$('span#save-button').stop(true).animate({boxShadow: '0 0 0 #00ffde'}, 250);
		
		});
	
	
		saveAnimationLoop = function() {
		
			$('span#save-button').animate({boxShadow: '0 0 15px #00ffde'}, 500, function(){ 
			
				$('span#save-button').animate({boxShadow: '0 0 0 #00ffde'}, 500, function(){ 
					saveAnimationLoop();
				});
			
			});
		
		}
	/* END SAVE BUTTON */


	/* TOOLTIPS */
		/* Hide any tooltips in the iframe if the iframe is blurred/unfocused */
		Headway.iframe.bind('blur', hideTooltipsIframeBlur);
	/* END TOOLTIPS */

	
	/* BOXES */
		setupStaticBoxes();	
		
		/* Make clicking box overlay close visible box for lazy people like me. */
		$('div.black-overlay').live('click', function(){
			
			var id = $(this).attr('id').replace('black-overlay-', '');
			
			if ( $('#' + id).length === 0 )
				return;

			if ( $('#ui-tooltip-tour').is(':visible') )
				return;
						
			closeBox(id);
			
		});		
	/* END BOXES */

	
	/* LAYOUT SWITCHER */
		/* Make open do cool stuff */
		$('span#layout-selector-toggle').click(function(){
		
			toggleLayoutSelector();
			
			return false;

		});

	
		/* Search */
		$('input#layout-selector-search').bind('focus', function(){
			
			if ( $(this).val() == 'Type to find a layout...' ) {
				$(this).val('');
			}
			
		});
		
		$('input#layout-selector-search').bind('blur', function(){
			
			if ( $(this).val() == '' ) {
				$(this).val('Type to find a layout...');
			}
			
		});
		
		
		/* Tabs */
		$('div#layout-selector').tabs();
		
		
		/* Handle Scrolling */
		$('div#layout-selector-pages').scrollbarPaper();
		$('div#layout-selector-templates').scrollbarPaper();

		
		/* Make buttons work */
		$('div#layout-selector').delegate('span.edit', 'click', function(event){
									
			if ( typeof allowVECloseSwitch !== 'undefined' && allowVECloseSwitch === false ) {
				
				if ( !confirm('You have unsaved changes, are you sure you want to switch layouts?') ) {
					return false;
				} else {
					disallowSaving();
				}
				
			}
			
			//Add loading indicator
			createCog($('div#iframe-loading-overlay'), true);
			
			$('div#iframe-loading-overlay').fadeIn(500);
			//End loading indicator stuff
			
			//Switch layouts
			switchToLayout($(this).parents('span.layout'));

			event.preventDefault();
			
		});
		
		$('div#layout-selector').delegate('span.revert', 'click', function(event){
						
			if ( !confirm('Are you sure you wish to revert this layout?  All blocks and content will be removed from this layout.') ) {
				return false;
			}
			
			var revertedLayout = $(this).parents('span.layout');
			var revertedLayoutID = revertedLayout.attr('layout_id');
			var revertedLayoutName = revertedLayout.find('strong').text();
			
			/* Add loading indicators */
			createCog($('div#iframe-loading-overlay'), true);
			
			$('div#iframe-loading-overlay').fadeIn(500);
			
			changeTitle('Visual Editor: Reverting ' + revertedLayoutName);
			startTitleActivityIndicator();
			
			/* Remove customized status from current layout */
			revertedLayout.parent().removeClass('layout-item-customized');
			
			/* Find the layout that's customized above this one */
			var parentCustomizedLayout = $(revertedLayout.parents('.layout-item-customized:not(.layout-selected)')[0]);
			var parentCustomizedLayoutID = parentCustomizedLayout.find('> span.layout').attr('layout_id');
			
			var topLevelCustomized = $($('div#layout-selector-pages > ul > li.layout-item-customized')[0]);
			var topLevelCustomizedID = topLevelCustomized.find('> span.layout').attr('layout_id');
						
			var selectedLayout = parentCustomizedLayoutID ? parentCustomizedLayout : topLevelCustomized;
			var selectedLayoutID = parentCustomizedLayoutID ? parentCustomizedLayoutID : topLevelCustomizedID;
			
			/* If the user gets on a revert frenzy and reverts all pages, then it should fall back to the blog index or front page (if active) */
			if ( typeof selectedLayoutID == 'undefined' || !selectedLayoutID ) {
				
				selectedLayoutID = Headway.frontPage == 'posts' ? 'index' : 'front_page';
				selectedLayout = $('div#layout-selector-pages > ul > li > span[layout_id="' + selectedLayoutID + '"]').parent();
				
			}
						
			/* Switch to the next higher-up layout */
			switchToLayout(selectedLayout, true, false);
			
			/* Delete everything from the reverted layout */
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'revert_layout',
				security: Headway.security,
				layout_to_revert: revertedLayoutID
			}, function(response) {
				
				if ( response === 'success' ) {
					showNotification('<em>' + revertedLayoutName + '</em> successfully reverted!');
				} else {
					showNotification('Error: Could not revert layout.', 6000, true);
				}
				
			});
			
			layoutSelectorRevertCheck();

			return false;
			
		});
		
		$('div#layout-selector').delegate('span#add-template', 'click', function(event) {

			var templateName = $('#template-name-input').val();

			//Do the AJAX request for the new template
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'add_template',
				security: Headway.security,
				layout: Headway.currentLayout,
				template_name: templateName
			}, function(response) {
				
				if ( typeof response === 'undefined' || !response ) {
					showNotification('Error: Could not add template.', 6000, true);
					
					return false;
				}
					
				//Need to add the new template BEFORE the add button
				var newTemplateNode = $('<li class="layout-item">\
					<span layout_id="template-' + response.id + '" class="layout layout-template">\
						<strong class="template-name">' + response.name + '</strong>\
						\
						<span class="delete-template" title="Delete Template">Delete</span>\
						\
						<span class="status status-currently-editing">Currently Editing</span>\
						\
						<span class="assign-template layout-selector-button">Use Template</span>\
						<span class="edit layout-selector-button">Edit</span>\
					</span>\
				</li>');	
				
				newTemplateNode.appendTo('div#layout-selector-templates ul');
				
				//Hide the no templates warning if it's visible
				$('li#no-templates(:visible)', 'div#layout-selector').hide();
				
				//We're all good!
				showNotification('Template added!');
				
			}, 'json');

			return false;
			
		});
		
		$('div#layout-selector').delegate('span.delete-template', 'click', function(event){

			var templateLi = $($(this).parents('li')[0]);
			var templateSpan = $(this).parent();
			var template = templateSpan.attr('layout_id');
			var templateID = template.replace('template-', '');
			var templateName = templateSpan.find('strong').text();
			
			if ( !confirm('Are you sure you wish to delete this template?') )
				return false;
			
			//Do the AJAX request for the new template
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'delete_template',
				security: Headway.security,
				template_to_delete: templateID
			}, function(response) {
				
				if ( typeof response === 'undefined' || response == 'failure' ) {
					showNotification('Error: Could not delete template.', 6000, true);
					
					return false;
				}
				
				//Delete the template from DOM	
				templateLi.remove();
				
				//Show the no templates message if there are no more templates
				if ( $('span.layout-template', 'div#layout-selector').length === 0 ) {
					$('li#no-templates(:visible)', 'div#layout-selector').show();
				} 
				
				//We're all good!
				showNotification('Template <em>' + templateName + '</em> successfully deleted!');

				//If the template that was removed was the current one, then send the user back to the blog index or front page
				if ( template === Headway.currentLayout ) {
					
					var defaultLayout = Headway.frontPage == 'posts' ? 'index' : 'front_page';

					switchToLayout($('div#layout-selector span.layout[layout_id="' + defaultLayout + '"]'), true, false);
					
				}
				
			});

			return false;
			
		});
		
		$('div#layout-selector').delegate('span.assign-template', 'click', function(event){

			var templateNode = $($(this).parents('li')[0]);
			var template = $(this).parent().attr('layout_id').replace('template-', '');

			//If the current layout being edited is a template trigger an error.
			if ( Headway.currentLayout.indexOf('template-') === 0 ) {
				alert('You cannot assign a template to another template.');
				
				return false;
			}
						
			//Do the AJAX request to assign the template
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'assign_template',
				security: Headway.security,
				template: template,
				layout: Headway.currentLayout
			}, function(response) {
				
				if ( typeof response === 'undefined' || response == 'failure' ) {
					showNotification('Error: Could not assign template.', 6000, true);
					
					return false;
				}
				
				$('li.layout-selected', 'div#layout-selector').removeClass('layout-item-customized');
				$('li.layout-selected', 'div#layout-selector').addClass('layout-item-template-used');
				
				$('li.layout-selected span.status-template', 'div#layout-selector').text(response);
				
				//Reload iframe
				
					//Add loading indicator
					createCog($('div#iframe-loading-overlay'), true);

					$('div#iframe-loading-overlay').fadeIn(500);
					//End loading indicator stuff

					//Change title to loading
					changeTitle('Visual Editor: Assigning Template');
					startTitleActivityIndicator();
					
					Headway.currentLayoutTemplate = 'template-' + template;
					
					//Reload iframe and new layout
					headwayIframeLoadNotification = 'Template assigned successfully!';
					
					loadIframe(Headway.instance.iframeCallback);

				//End reload iframe
				
			});
			
			layoutSelectorRevertCheck();

			return false;
			
		});
		
		$('div#layout-selector').delegate('span.remove-template', 'click', function(event){

			var layoutNode = $($(this).parents('li')[0]);
			var layoutID = $(this).parent().attr('layout_id');
						
			//Do the AJAX request to assign the template
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'remove_template_from_layout',
				security: Headway.security,
				layout: layoutID
			}, function(response) {
				
				if ( typeof response === 'undefined' || response == 'failure' ) {
					showNotification('Error: Could not remove template from layout.', 6000, true);
					
					return false;
				}
				
				layoutNode.removeClass('layout-item-template-used');
				
				if ( response === 'customized' ) {
					layoutNode.addClass('layout-item-customized');
				}
				
				//If the current layout is the one with the template that we're unassigning, we need to reload the iframe.
				if ( layoutID == Headway.currentLayout ) {
					
					//Add loading indicator
					createCog($('div#iframe-loading-overlay'), true);

					$('div#iframe-loading-overlay').fadeIn(500);
					//End loading indicator stuff

					//Change title to loading
					changeTitle('Visual Editor: Removing Template From Layout');
					startTitleActivityIndicator();

					Headway.currentLayoutTemplate = false;

					//Reload iframe and new layout
					headwayIframeLoadNotification = 'Template removed from layout successfully!';
					
					loadIframe(Headway.instance.iframeCallback);

					return true;
					
				}
				
				//We're all good!
				return true;
				
			});

			return false;
			
		});
		
		/* Handle Collapsing Stuff */
		$('div#layout-selector').delegate('span', 'click', function(event){
			
			if ( $(this).hasClass('layout-open') ) {
				
				$(this).removeClass('layout-open');
				$(this).siblings('ul').hide();
				
			} else {
				
				$(this).addClass('layout-open');
				$(this).siblings('ul').show();
				
			}
			
		});
	
		/* Hide revert button if there is only one customized layout. */
		layoutSelectorRevertCheck();
	/* END PAGE SWITCHER */

	
	/* PANEL */
		$('ul#modes li').live('click', function(){
			$(this).siblings('li').removeClass('current-mode');
			$(this).addClass('current-mode');
		});
		
		$('div#panel').tabs({
			tabTemplate: "<li><a href='#{href}'>#{label}</a></li>",
			add: function(event, ui, content) {
				
				$(ui.panel).append(content);
																				
			},
			select: function(event, ui) {
								
				var tabID = $(ui.tab).attr('href').replace('#', '').replace('-tab', '');

				$i('.block-selected').removeClass('block-selected block-hover');

				if ( tabID.indexOf('block-') === 0 )
					$i('#' + tabID).addClass('block-selected block-hover');
												
			}
		});
		
		$('ul#panel-top li a').bind('click', showPanel);
		
		$('div.sub-tab').tabs();
				
		/* PANEL RESIZING */
			var panelMinHeight = 120;
			var panelMaxHeight = function() { return $(window).height() - 275; };
		
			var resizePanel = function(panelHeight, resizingWindow) {
								
				if ( typeof panelHeight == 'undefined' || panelHeight == false )
					var panelHeight = $('div#panel').height();
				
				if ( panelHeight > panelMaxHeight() )
					panelHeight = (panelMaxHeight() > panelMinHeight) ? panelMaxHeight() : panelMinHeight;
								
				if ( panelHeight < panelMinHeight )
					panelHeight = panelMinHeight;
									
				if ( typeof resizingWindow != 'undefined' && resizingWindow && panelHeight < panelMaxHeight() )
					return;

				$('div#panel').css('height', panelHeight);

				var iframeBottomPadding = $('div#panel').hasClass('panel-hidden') ? $('ul#panel-top').outerHeight() : $('div#panel').outerHeight();
				var layoutSelectorBottomPadding = $('div#panel').hasClass('panel-hidden') ? $('ul#panel-top').outerHeight()  + $('div#layout-selector-tabs').height() : $('div#panel').outerHeight() + $('div#layout-selector-tabs').height();

				$('iframe.content').css({'paddingBottom': iframeBottomPadding});
				$('div#layout-selector-offset').css({paddingBottom: layoutSelectorBottomPadding});

				if ( $('div#panel').hasClass('panel-hidden') )
					$('div#panel').css({'bottom': -$('div#panel').height()});
				
				$.cookie('panel-height', panelHeight);
								
			}
		
			/* Resize the panel according to the cookie right on VE load */
			if ( $.cookie('panel-height') )
				resizePanel($.cookie('panel-height'));
		
			/* Make the resizing handle actually work */
			$('div#panel').resizable({
				maxHeight: panelMaxHeight(),
				minHeight: 120,
				handles: 'n',
				resize: function(event, ui) {
																
					$(this).css({
						width: '100%',
						position: 'fixed',
						bottom: 0,
						top: ''
					});	
				
					$('iframe.content').css({'paddingBottom': $('div#panel').outerHeight()});
					$('div#layout-selector-offset').css({paddingBottom: $('div#panel').outerHeight() + $('div#layout-selector-tabs').height()});
				
					/* Refresh iframe overlay size so it continues to cover iframe */
					showIframeOverlay();		
			
				},
				start: showIframeOverlay,
				stop: function() {
				
					$.cookie('panel-height', $(this).height());
				
					hideIframeOverlay();
				
				},
			});
			
			/* The max height option on the resizable must be updated if the window is resized. */
			$(window).bind('resize', function(event) {
				
				/* For some reason jQuery UI resizable triggers window resize so only fire if window is truly the target. */
				if ( event.target != window )
					return;
							
				$('div#panel').resizable('option', {maxHeight: panelMaxHeight()});
				
				resizePanel(false, true);
				
			});
		
			$('div#panel > .ui-resizable-handle.ui-resizable-n')
				.attr('id', 'panel-top-handle')
				.html('<span></span><span></span><span></span>');
		/* END PANEL RESIZING */

		/* PANEL OPTIONS */
			/* Position menu */
			$('ul#panel-top li#options ul').css({
				top: -($('ul#panel-top li#options ul').height() + 3)
			});

			/* Bind button */
			$('ul#panel-top li#options span').bind('click', function(){

				/* If it's open, close it */
				if ( $(this).hasClass('active') ) {

					$(this).siblings('ul').hide();
					$(this).removeClass('active');

					$(document).unbind('click', hideOptions);
					Headway.iframe.contents().unbind('click', hideOptions);

				} else {

					$(this).siblings('ul').show();
					$(this).addClass('active');

					$(document).bind('click', hideOptions);
					Headway.iframe.contents().bind('click', hideOptions);

				}

			});

			hideOptions = function(event) {

				if ( $(event.target).parents('li#options').length === 0 ) {

					$('ul#panel-top li#options ul').hide();
					$('ul#panel-top li#options span').removeClass('active');

					$(document).unbind('click', hideOptions);
					Headway.iframe.contents().unbind('click', hideOptions);

				}

			}

			/* Make buttons in menu close menu when clicked */
			$('ul#panel-top li#options ul li').bind('click', function(){

				var list = $(this).parent();
				var button = list.siblings('span');

				list.hide();
				button.removeClass('active');

				$(document).unbind('click', hideOptions);
				Headway.iframe.contents().unbind('click', hideOptions);

			});

			/* Bind specific options */
			$('ul#panel-top li#options ul li#menu-link-grid-wizard').bind('click', function(){

				hidePanel();

				openBox('grid-wizard');

			});

			$('ul#panel-top li#options ul li#menu-link-tour').bind('click', function(){
				startTour();
			});

			$('ul#panel-top li#options ul li#menu-link-live-css').bind('click', function(){

				openBox('live-css');

				//If Live CSS hasn't been set up then initiate CodeMirror or Tabby
				if ( typeof liveCSSInit == 'undefined' || liveCSSInit == false ) {

					//Set up CodeMirror
					if ( Headway.disableCodeMirror != true ) {						
						liveCSSEditor = CodeMirror.fromTextArea($('textarea#live-css')[0], {
							lineWrapping: true,
							tabMode: 'shift',
							mode: 'css',
							lineNumbers: true,
							onCursorActivity: function() {
								liveCSSEditor.setLineClass(hlLine, null);
								hlLine = liveCSSEditor.setLineClass(liveCSSEditor.getCursor().line, "activeline");
							},
							onChange: function(instance) {

								var value = instance.getValue();

								updatePanelInputHidden({input: $('textarea#live-css'), value: value});
								$i('style#live-css-holder').html(value);

								allowSaving();

							},
							undoDepth: 80
						});

						liveCSSEditor.setValue($('textarea#live-css').val());

						var hlLine = liveCSSEditor.setLineClass(0, "activeline");

					//Set up Tabby and the text area if CodeMirror is disabled
					} else {

						$('textarea#live-css').tabby();

						$('textarea#live-css').bind('keyup', function(){

							updatePanelInputHidden({input: $(this), value: $(this).val()});

							$i('style#live-css-holder').html($(this).val());

							allowSaving();

						});

					}

					liveCSSInit = true;

				}

			});

			$('ul#panel-top li#options ul li#menu-link-clear-cache').bind('click', function(){

				/* Set up parameters */
				var parameters = {
					action: 'headway_visual_editor',
					method: 'clear_cache',
					security: Headway.security
				};

				/* Do the stuff */
				$.post(Headway.ajaxURL, parameters, function(response){

					if ( response === 'success' ) {

						showNotification('The cache was successfully cleared!');

					} else {

						showNotification('Error: Could not clear cache.', 6000, true);

					}

				});

			});
		/* END PANEL OPTIONS */

		/* PANEL TOGGLE */
			$('ul#panel-top').bind('dblclick', function(event) {

				if ( event.target.id != 'panel-top' )
					return false;

				togglePanel();

			});

			$('ul#panel-top li#minimize span').bind('click', function(event) {

				togglePanel();

				return false;

			});

			/* Check for cookie */
			if ( $.cookie('hide-panel') === 'true' ) {

				hidePanel(true);

			}
		/* END PANEL TOGGLE */
		
		/* PANEL SCROLLING */
			addPanelScrolling();
		/* END PANEL SCROLLING */
		
		setLoadingBar(40, 'Setting Up Panel');
	/* END PANEL */

	
	/* INPUTS */		
		/* Run the function */
		setUpPanelInputs();
		
		/* Not an input */
		setLoadingBar(45, 'Setting Up Inputs');
	/* END INPUTS */


	/* START TOUR */
		if ( Headway.ranTour[Headway.mode] == false && Headway.ranTour.legacy == false ) {
			startTour();
		}
	/* END START TOUR */


	setLoadingBar(65, 'Waiting for iframe');
	
});
})(jQuery);