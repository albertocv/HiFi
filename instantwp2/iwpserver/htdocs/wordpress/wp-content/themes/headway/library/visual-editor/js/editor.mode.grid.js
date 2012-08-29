(function($) {
	
visualEditorModeGrid = function() {				
		
	
	this.init = function() {
				
		this.bindPreviewButton();
		this.bindGridWizard();
				
	}	
		
				
	this.iframeCallback = function() {
								
		Headway.iframe.grid('destroy');
		
		var columns = Headway.gridColumns;
		var columnWidth = parseInt($('div#input-column-width input').val());
		var gutterWidth = parseInt($('div#input-gutter-width input').val());	
						
		Headway.iframe.grid({
			columns: columns,
			container: 'div.grid-container',
			defaultBlockClass: 'block',
			columnWidth: columnWidth,
			gutterWidth: gutterWidth
		});
		
		addBlockControls(true, true);
		initBlockTypePopup();
		
		gridStylesheet = new ITStylesheet({document: Headway.iframe.contents()[0], href: Headway.homeURL + '/?headway-trigger=compiler&file=grid-iframe'}, 'find');
		
		//Update the grid width input in accordance to the sliders
		$('div#input-grid-width input').val( ( columnWidth * columns + ((columns - 1) * gutterWidth) ) );
		
		//Grid length buttons
		$i('span#grid-height-decrease').click(function() {
						
			var existingHeight = $i('div.grid-container').height();	
			var newHeight = existingHeight - 100;
						
			if ( existingHeight == 800 )
				return false;
			
			if ( newHeight <= 800 )
				$(this).addClass('grid-height-button-disabled');
			
			gridStylesheet.update_rule('div#grid div.grid-column', {height: newHeight + 'px'});			
			gridStylesheet.update_rule('div.grid-container', {height: newHeight + 'px'});
			
			//Send the new grid height to the database
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'change_grid_height',
				grid_height: newHeight,
				security: Headway.security
			});
			
		});
		
		$i('span#grid-height-increase').click(function() {
			
			var existingHeight = $i('div.grid-container').height();	
			var newHeight = existingHeight + 100;
			
			gridStylesheet.update_rule('div#grid div.grid-column', {height: newHeight + 'px'});			
			gridStylesheet.update_rule('div.grid-container', {height: newHeight + 'px'});
			
			$i('span#grid-height-decrease').removeClass('grid-height-button-disabled');
			
			//Send the new grid height to the database
			$.post(Headway.ajaxURL, {
				action: 'headway_visual_editor',
				method: 'change_grid_height',
				grid_height: newHeight,
				security: Headway.security
			});
			
		});
		
		//Reset preview button if necessary
		if ( $('span#preview-button').hasClass('preview-active') )
			$('span#preview-button').trigger('click');
		
	}
	
	
	this.bindPreviewButton = function() {
		
		/* Preview Button */
		$('span#preview-button').bind('click', function() {

			if ( !$(this).hasClass('preview-active') ) {

				iframeURL = Headway.homeURL 
					+ '?ve-iframe=true&ve-iframe-layout=' 
					+ Headway.currentLayout 
					+ '&preview=true'
					+ '&unsaved=' + encodeURIComponent($('div#visual-editor-hidden-inputs input').serialize())
					+ '&rand=' + Math.floor(Math.random()*100000001);

				/* Show loading indicator */
					$(this).text('Loading...');

					createCog($('div#iframe-loading-overlay'), true);

					$('div#iframe-loading-overlay').fadeIn(500);

				/* Load preview */
					$('iframe#preview').src(iframeURL, function() {

						Headway.iframe.fadeOut(300);
						$('iframe#preview').fadeIn(300);

						$('div#iframe-loading-overlay').fadeOut(300).html('');
						$('span#preview-button').addClass('preview-active').text('Show Grid');

					});				

			} else {

				$('iframe#preview').fadeOut(300);
				Headway.iframe.fadeIn(300);

				$(this).removeClass('preview-active').text('Preview');

			}

		});
		
	}
	

	this.bindGridWizard = function() {
		
		/* Presets */
			var gridWizardPresets = {
				'right-sidebar': [
					{
						top: 0,
						left: 0,
						width: 24,
						height: 130,
						type: 'header'
					},
				
					{
						top: 140,
						left: 0,
						width: 24,
						height: 40,
						type: 'navigation'
					},
				
					{
						top: 190,
						left: 0,
						width: 18,
						height: 320,
						type: 'content'
					},
				
					{
						top: 190,
						left: 18,
						width: 6,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-1'
					},
				
					{
						top: 520,
						left: 0,
						width: 24,
						height: 70,
						type: 'footer'
					},
				],
			
				'left-sidebar': [
					{
						top: 0,
						left: 0,
						width: 24,
						height: 130,
						type: 'header'
					},
				
					{
						top: 140,
						left: 0,
						width: 24,
						height: 40,
						type: 'navigation'
					},
				
					{
						top: 190,
						left: 0,
						width: 6,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-1'
					},
				
					{
						top: 190,
						left: 6,
						width: 18,
						height: 320,
						type: 'content'
					},
				
					{
						top: 520,
						left: 0,
						width: 24,
						height: 70,
						type: 'footer'
					}
				],
			
				'two-right': [
					{
						top: 0,
						left: 0,
						width: 24,
						height: 130,
						type: 'header'
					},
				
					{
						top: 140,
						left: 0,
						width: 24,
						height: 40,
						type: 'navigation'
					},
				
					{
						top: 190,
						left: 0,
						width: 16,
						height: 320,
						type: 'content'
					},
				
					{
						top: 190,
						left: 16,
						width: 4,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-1'
					},
				
					{
						top: 190,
						left: 20,
						width: 4,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-2'
					},
				
					{
						top: 520,
						left: 0,
						width: 24,
						height: 70,
						type: 'footer'
					}
				],
			
				'two-both': [
					{
						top: 0,
						left: 0,
						width: 24,
						height: 130,
						type: 'header'
					},
				
					{
						top: 140,
						left: 0,
						width: 24,
						height: 40,
						type: 'navigation'
					},
				
					{
						top: 190,
						left: 0,
						width: 4,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-1'
					},
				
					{
						top: 190,
						left: 4,
						width: 16,
						height: 320,
						type: 'content'
					},
				
					{
						top: 190,
						left: 20,
						width: 4,
						height: 270,
						type: 'widget-area',
						mirroringOrigin: 'sidebar-2'
					},
				
					{
						top: 520,
						left: 0,
						width: 24,
						height: 70,
						type: 'footer'
					}
				],
			
				'all-content': [
					{
						top: 0,
						left: 0,
						width: 24,
						height: 130,
						type: 'header'
					},
				
					{
						top: 140,
						left: 0,
						width: 24,
						height: 40,
						type: 'navigation'
					},
				
					{
						top: 190,
						left: 0,
						width: 24,
						height: 320,
						type: 'content'
					},
				
					{
						top: 520,
						left: 0,
						width: 24,
						height: 70,
						type: 'footer'
					}
				]
			}


			$('div#boxes').delegate('div#box-grid-wizard span.layout-preset', 'mousedown', function() {
			
				$('div#box-grid-wizard span.layout-preset-selected').removeClass('layout-preset-selected');
				$(this).addClass('layout-preset-selected');
			
			});


			$('div#boxes').delegate('span#grid-wizard-button-preset-next', 'click', function() {
			
				/* Populate the step 2 panel with the proper select boxes */
				var selectedPreset = $('div#box-grid-wizard span.layout-preset-selected').attr('id').replace('layout-', '');
								
				switch ( selectedPreset ) {
					
					case 'right-sidebar':
					
						$('div#grid-wizard-presets-mirroring-select-sidebar-1').show();
						$('div#grid-wizard-presets-mirroring-select-sidebar-2').hide();
						
						$('div#grid-wizard-presets-mirroring-select-sidebar-1 h5').text('Right Sidebar');
						
					break;
					
					
					case 'left-sidebar':
					
						$('div#grid-wizard-presets-mirroring-select-sidebar-1').show();
						$('div#grid-wizard-presets-mirroring-select-sidebar-2').hide();
						
						$('div#grid-wizard-presets-mirroring-select-sidebar-1 h5').text('Left Sidebar');
					
					break;
					
					
					case 'two-right':
					
						$('div#grid-wizard-presets-mirroring-select-sidebar-1').show();
						$('div#grid-wizard-presets-mirroring-select-sidebar-2').show();
						
						$('div#grid-wizard-presets-mirroring-select-sidebar-1 h5').text('Left Sidebar');
						$('div#grid-wizard-presets-mirroring-select-sidebar-2 h5').text('Right Sidebar');
					
					break;
					
					
					case 'two-both':
					
						$('div#grid-wizard-presets-mirroring-select-sidebar-1').show();
						$('div#grid-wizard-presets-mirroring-select-sidebar-2').show();
						
						$('div#grid-wizard-presets-mirroring-select-sidebar-1 h5').text('Left Sidebar');
						$('div#grid-wizard-presets-mirroring-select-sidebar-2 h5').text('Right Sidebar');
					
					break;
					
					
					case 'all-content':
					
						$('div#grid-wizard-presets-mirroring-select-sidebar-1').hide();
						$('div#grid-wizard-presets-mirroring-select-sidebar-2').hide();
					
					break;
					
				}
				
			
				/* Change the buttons around */
				$(this).hide(); //Next button
				
				$('span#grid-wizard-button-preset-previous').show();
				$('span#grid-wizard-button-preset-use-preset').show(); 
				
				
				/* Change the content that's being displayed */
				$('div#grid-wizard-presets-step-1').hide();
				$('div#grid-wizard-presets-step-2').show();
				
			});
			
			
			$('div#boxes').delegate('span#grid-wizard-button-preset-previous', 'click', function() {
			
				/* Change the buttons around */
				$(this).hide(); //Previous button
				$('span#grid-wizard-button-preset-use-preset').hide();
				
				$('span#grid-wizard-button-preset-next').show();
				
				
				/* Change the content that's being displayed */
				$('div#grid-wizard-presets-step-2').hide();
				$('div#grid-wizard-presets-step-1').show();
				
			});
			

			$('div#boxes').delegate('span#grid-wizard-button-preset-use-preset', 'click', function() {
			
				var selectedPreset = $('div#box-grid-wizard span.layout-preset-selected').attr('id').replace('layout-', '');
			
				//Delete any blocks that are on the grid already
				$i('.block').each(function() {
				
					deleteBlock(this);
				
				});
			
				//Put the new blocks on the layout
				var blockIDBatch = getAvailableBlockIDBatch(gridWizardPresets[selectedPreset].length);
						
				$.each(gridWizardPresets[selectedPreset], function() {
								
					var addBlockArgs = $.extend({}, this, {
						id: blockIDBatch[0]
					});
					
					delete addBlockArgs.mirroringOrigin;
		
					/* Handle Mirroring */
					var mirroringOrigin = (typeof this.mirroringOrigin != 'undefined') ? this.mirroringOrigin : this.type;
					var mirroringSelectVal = $('div#grid-wizard-presets-mirroring-select-' + mirroringOrigin + ' select').val();
																				
					if ( mirroringSelectVal !== '' ) {
						
						addBlockArgs.settings = {}
						addBlockArgs.settings['mirror-block'] = mirroringSelectVal;
											
					}

					/* Add the block to the grid */
					grid.addBlock(addBlockArgs);
					
					/* Remove the ID that was just used from the patch */
					blockIDBatch.splice(0, 1);
				
				});
				
				/* Force the available block ID to be refreshed */
				getAvailableBlockID();
			
				return closeBox('grid-wizard');
			
			});


		/* Layout Cloning */
			$('div#boxes').delegate('span#grid-wizard-button-clone-page', 'click', function() {
				
				var layoutToClone = $('select#grid-wizard-pages-to-clone').val();
				
				if ( layoutToClone === '' )
					return alert('Please select a page to clone.');
					
				if ( $(this).hasClass('button-depressed') )
					return;
					
				$(this).text('Cloning...').addClass('button-depressed').css('cursor', 'default');
			
				var request = $.ajax(Headway.ajaxURL, {
					type: 'POST',
					async: true,
					data: {
						action: 'headway_visual_editor',
						method: 'get_layout_blocks_in_json',
						security: Headway.security,
						layout: layoutToClone
					},
					success: function(data, textStatus) {
						
						if ( textStatus == false )
							return false;

						//Delete any blocks that are on the grid already
						$i('.block').each(function() {

							deleteBlock(this);

						});

						var blocks = $.parseJSON(data.replace(/&quot;/g, '"'));
						var numberOfBlocks = Object.keys(blocks).length;
						var blockIDBatch = getAvailableBlockIDBatch(numberOfBlocks);

						$.each(blocks, function() {
														
							var blockToMirror = this.settings['mirror-block'] ? this.settings['mirror-block'] : this.id;

							var addBlockArgs = {
								id: blockIDBatch[0],
								type: this.type,
								top: this.position.top,
								left: this.position.left,
								width: this.dimensions.width,
								height: this.dimensions.height,
								settings: $.extend({}, this.settings, {'mirror-block': blockToMirror})
							};	

							grid.addBlock(addBlockArgs);

							//Remove the ID that was just used from the patch
							blockIDBatch.splice(0, 1);

						});

						setupTooltips('iframe');

						//Force the available block ID to be refreshed
						getAvailableBlockID();
						
						return closeBox('grid-wizard');
						
					}
				});
								
			});
		
		
		/* Template Assigning */
			$('div#boxes').delegate('span#grid-wizard-button-assign-template', 'click', function() {
				
				var templateToAssign = $('select#grid-wizard-assign-template').val().replace('template-' , '');
				
				if ( templateToAssign === '' )
					return alert('Please select a template to assign.');
				
				//Do the AJAX request to assign the template
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'assign_template',
					security: Headway.security,
					template: templateToAssign,
					layout: Headway.currentLayout
				}, function(response) {

					if ( typeof response === 'undefined' || response == 'failure' ) {
						showNotification('Error: Could not assign template.', 6000, true);

						return false;
					}

					$('div#layout-selector li.layout-selected').removeClass('layout-item-customized');
					$('div#layout-selector li.layout-selected').addClass('layout-item-template-used');

					$('div#layout-selector li.layout-selected span.status-template').text(response);

					//Reload iframe

						//Add loading indicator
						createCog($('div#iframe-loading-overlay'), true);

						$('div#iframe-loading-overlay').fadeIn(500);
						//End loading indicator stuff

						//Change title to loading
						changeTitle('Visual Editor: Assigning Template');
						startTitleActivityIndicator();

						Headway.currentLayoutTemplate = 'template-' + templateToAssign;

						//Reload iframe and new layout
						headwayIframeLoadNotification = 'Template assigned successfully!';

						loadIframe(Headway.instance.iframeCallback);

					//End reload iframe

				});

				layoutSelectorRevertCheck();

				return closeBox('grid-wizard');
				
			});
		
		
		/* Empty Grid */
			$('div#boxes').delegate('span.grid-wizard-use-empty-grid', 'click', function() {
			
				//Empty the grid out
				$i('.block').each(function() {
				
					deleteBlock(this);
				
				});
			
				closeBox('grid-wizard');
			
			});

	}
	

}


/* GRID INPUT CALLBACKS */
	gridInputCallbackColumnWidth = function(value) {

		var iframe = Headway.iframe;

		var gutterWidth = parseInt($('div#input-gutter-width input').val());
		var columns = 24;

		var oldColumnWidth = $i('.grid-width-1').width();
		var oldGutterWidth = $i('.grid-width-1').css('marginLeft').replace('px', '') * 2;

		//Modify every grid class
		for ( i = 1; i <= columns; i++ ) {

			var width = value * i + ((i - 1) * gutterWidth);
			var left = (value + gutterWidth) * i;

			gridStylesheet.update_rule('.grid-width-' + i, {'width': width + 'px'});
			gridStylesheet.update_rule('.grid-left-' + i, {'left': left + 'px'});

		}

		//Calculate full content width by getting largest grid numbers width
		var contentWidth = value * columns + ((columns - 1) * gutterWidth);

		//Update wrapper input and wrapper itself
		$('div#input-grid-width input').val(contentWidth);

		gridStylesheet.update_rule('div.wrapper', {width: contentWidth + 'px'});
		gridStylesheet.update_rule('div.grid-container', {width: (contentWidth + 1) + 'px'});

		//Update layout widget options
		iframe.grid('option', 'columnWidth', value);
		iframe.grid('option', 'minWidth', value);
		iframe.grid('option', 'gutterWidth', gutterWidth);

		//Reset draggable and resizables
		iframe.grid('resetDraggableResizable');

	}


	gridInputCallbackGutterWidth = function(value) {

		var iframe = Headway.iframe;

		var columnWidth = parseInt($('div#input-column-width input').val());
		var columns = 24;

		var oldColumnWidth = $i('.grid-width-1').width();
		var oldGutterWidth = $i('.grid-width-1').css('marginLeft').replace('px', '') * 2;

		//Modify every grid class
		for ( i = 1; i <= columns; i++ ) {

			var width = columnWidth * i + ((i - 1) * value);
			var left = (columnWidth + value) * i;

			gridStylesheet.update_rule('.grid-width-' + i, {'width': width + 'px'});
			gridStylesheet.update_rule('.grid-left-' + i, {'left': left + 'px'});

		}

		//Update column margins ... The 1 is for the borders on the columns
		var leftMargin = Math.ceil((value / 2) - 1);
		var rightMargin = Math.floor((value / 2) - 1);

		gridStylesheet.update_rule('div#grid div.grid-column', {'margin': '0 ' + rightMargin + 'px 0 ' + leftMargin + 'px'});

		//Calculate full content width by getting largest grid numbers width
		var contentWidth = columnWidth * columns + ( (columns - 1) * value );

		$('div#input-grid-width input').val(contentWidth);

		gridStylesheet.update_rule('div.wrapper', {width: contentWidth + 'px'});
		gridStylesheet.update_rule('div.grid-container', {width: (contentWidth + 1) + 'px'});

		//Update layout widget options
		Headway.iframe.grid('option', 'columnWidth', columnWidth);
		Headway.iframe.grid('option', 'minWidth', columnWidth);
		Headway.iframe.grid('option', 'gutterWidth', value);

		//Reset draggable and resizables
		iframe.grid('resetDraggableResizable');

	}
/* END GRID INPUT CALLBACKS */



/* SCRAPS */
	addInsertButton = function() {
	
		$('<span id="wrapper-splitter-button">Insert Wrapper Splitter</span>')
			.appendTo('body')
			.css({
				position: 'fixed',
				display: 'block',
				background: '#333',
				color: '#c1c1c1',
				height: '25px',
				lineHeight: '25px',
				padding: '0 10px',
				top: '50px',
				left: '15px',
				cursor: 'pointer',
				borderRadius: '5px'
			})
			.bind('click', function(){
						
				$('<div id="wrapper-splitter"></div>').appendTo('body');

				$('div#wrapper-splitter').css({
				    width: '100%',
				    position: 'absolute',
				    height: '5px',
				    display: 'block',
				    background: '#00f',
				    top: 50,
					cursor: 'move'
				});

				$('div#wrapper-splitter').draggable({
					axis: 'y'
				});
			
				$(this).hide();
				$('span#wrapper-splitter-split-button').show();
			
		});
	
	}


	addSplitButton = function() {
	
		$('<span id="wrapper-splitter-split-button">Split Wrapper</span>')
			.appendTo('body')
			.css({
				position: 'fixed',
				display: 'none',
				background: '#333',
				color: '#c1c1c1',
				height: '25px',
				lineHeight: '25px',
				padding: '0 10px',
				top: '50px',
				left: '15px',
				cursor: 'pointer',
				borderRadius: '5px'
			})
			.bind('click', function(){
			
				var position = $('div#wrapper-splitter').position();
				var wrapper1Height = $('div#wrapper-1').height();

				$('div#wrapper-1').height(position.top-65);

				$('<div class="wrapper" id="wrapper-2"></div>').appendTo('body').css({
					margin: '30px auto',
					height: wrapper1Height - (position.top-65),
					cursor: 'move'
				}).draggable({
					axis: 'y'
				});

				$('div#wrapper-splitter').remove();
			
				$(this).hide();
			
		});
	
	}
/* END SCRAPS */


})(jQuery);