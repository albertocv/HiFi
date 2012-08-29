(function($) {
setUpPanelInputs = function(context) {

	if ( typeof context === 'undefined' ) {
		context = 'div#panel';
	}

	/* Selects */	
	$('div.input-select select', context).bind('change', function() {
		
		updatePanelInputHidden({input: $(this), value: $(this).val()});
		
		allowSaving();
						
	});


	/* Text */
	$('div.input-text input', context).bind('keyup blur', function() {
		
		updatePanelInputHidden({input: $(this), value: $(this).val()});
		
		allowSaving();
		
	});
	
	
	/* Textarea */
		$('div.input-textarea textarea', context).bind('keyup blur', function() {
			
			updatePanelInputHidden({input: $(this), value: $(this).val()});
			
			allowSaving();
			
		});
		
		$('div.input-textarea span.textarea-open', context).bind('click', function() {
			
			var textareaContainer = $(this).siblings('.textarea-container');
			var textarea = textareaContainer.find('textarea');
			
			var inputContainerOffset = $(this).parents('.input').offset();
			
			textareaContainer.css({
				top: inputContainerOffset.top - textareaContainer.outerHeight(true),
				left: inputContainerOffset.left
			});
			
			/* Keep the sub tabs content container from scrolling */
			$('div.sub-tabs-content-container').css('overflow-y', 'hidden');

			if ( textareaContainer.data('visible') !== true ) {
			
				/* Show the textarea */
				textareaContainer.fadeIn(150);
				textareaContainer.data('visible', true);
			
				/* Put the cursor in the textarea */
				textarea.trigger('focus');
			
				/* Bind the document close */
				$(document).bind('mousedown', {textareaContainer: textareaContainer}, textareaClose);
				Headway.iframe.contents().bind('mousedown', {textareaContainer: textareaContainer}, textareaClose);
			
				$(window).bind('resize', {textareaContainer: textareaContainer}, textareaClose);
			
			} else {
				
				/* Hide the textarea */
				textareaContainer.fadeOut(150);
				textareaContainer.data('visible', false);
				
				/* Allow sub tabs content container to scroll again */
				$('div.sub-tabs-content-container').css('overflow-y', 'auto');

				/* Remove the events */
				$(document).unbind('mousedown', textareaClose);
				Headway.iframe.contents().unbind('mousedown', textareaClose);
				
				$(window).unbind('resize', textareaClose);
				
			}
			
		});
		
		textareaClose = function(event) {
							
			/* Do not trigger this if they're clicking the same button that they used to open the textarea */
			if ( $(event.target).parents('div.input-textarea div.input-right').length === 1 )
				return;
			
			var textareaContainer = event.data.textareaContainer;
			
			/* Hide the textarea */
			textareaContainer.fadeOut(150);
			textareaContainer.data('visible', false);
			
			/* Allow sub tabs content container to scroll again */
			$('div.sub-tabs-content-container').css('overflow-y', 'auto');
			
			/* Remove the events */
			$(document).unbind('mousedown', textareaClose);
			Headway.iframe.contents().unbind('mousedown', textareaClose);
			
			$(window).unbind('resize', textareaClose);
			
		}
	

	/* WYSIWYG */
		inputWYSIWYGChange = function(obj, event) {

			updatePanelInputHidden({input: obj.$el, value: obj.getCode()});
			
			allowSaving();

		}

		$('div.input-wysiwyg span.wysiwyg-open', context).bind('click', function() {
			
			var wysiwygContainer = $(this).siblings('.wysiwyg-container');
			
			var inputContainerOffset = $(this).parents('.input').offset();
			
			wysiwygContainer.css({
				top: inputContainerOffset.top - wysiwygContainer.outerHeight(true),
				left: inputContainerOffset.left
			});
			
			/* Keep the sub tabs content container from scrolling */
			$('div.sub-tabs-content-container').css('overflow-y', 'hidden');

			if ( wysiwygContainer.data('visible') !== true ) {

				/* Show the WYSWIWYG */
				wysiwygContainer.fadeIn(150);
				wysiwygContainer.data('visible', true);

				/* Function for setting up redactor */
					var setupRedactor = function() {

						wysiwygContainer.find('textarea').redactor({
							path: Headway.headwayURL + '/library/resources/redactor/',
							buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
								'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
								'table', 'link', '|',
								'fontcolor', 'backcolor', '|', 
								'alignleft', 'aligncenter', 'alignright', 'justify', '|',
								'horizontalrule'],
							keyupCallback: inputWYSIWYGChange,
							execCommandCallback: inputWYSIWYGChange
						});

						wysiwygContainer.find('textarea').setFocus();

						wysiwygContainer.data('setupRedactor', true);

					}

				/* Load redactor if it hasn't been before */
					if ( $('body').data('loadedRedactor') !== true ) {

						var style = $('<link>')
							.attr({
								rel: 'stylesheet',
								href: Headway.headwayURL + '/library/resources/redactor/css/redactor.css', 
								type: 'text/css',
								media: 'screen'
							})
							.appendTo($('head'));

						var script = jQuery.ajax({
							dataType: 'script',
							cache: true,
							url: Headway.headwayURL + '/library/resources/redactor/redactor.min.js',
							complete: setupRedactor
						});

						$('body').data('loadedRedactor', true);

				/* Otherwise just set up redactor if redactor has been loaded, but this input hasn't been setup */
					} else if ( $('body').data('loadedRedactor') === true && wysiwygContainer.data('setupRedactor') !== true ) {

						setupRedactor();

				/* Redactor has been loaded and set up, just focus it */
					} else {

						/* Focus the input */
						wysiwygContainer.find('textarea').setFocus();

					}

				/* Bind the document close */
					$(document).bind('mousedown', {wysiwygContainer: wysiwygContainer}, wysiwygClose);
					Headway.iframe.contents().bind('mousedown', {wysiwygContainer: wysiwygContainer}, wysiwygClose);
					
					$(window).bind('resize', {wysiwygContainer: wysiwygContainer}, wysiwygClose);

			} else {
				
				/* Hide the WYSIWYG */
				wysiwygContainer.fadeOut(150);
				wysiwygContainer.data('visible', false);
				
				/* Allow sub tabs content container to scroll again */
				$('div.sub-tabs-content-container').css('overflow-y', 'auto');

				/* Remove the events */
				$(document).unbind('mousedown', wysiwygClose);
				Headway.iframe.contents().unbind('mousedown', wysiwygClose);
				
				$(window).unbind('resize', wysiwygClose);
				
			}


		});


		wysiwygClose = function(event) {
							
			/* Do not trigger this if they're clicking the same button that they used to open the textarea */
			if ( 
				$(event.target).parents('div.input-wysiwyg div.input-right').length === 1 
				|| $(event.target).parents('.redactor_dropdown').length === 1
				|| $(event.target).parents('#redactor_modal').length === 1 
			)
				return;
			
			var wysiwygContainer = event.data.wysiwygContainer;
			
			/* Hide the WYSIWYG */
			wysiwygContainer.fadeOut(150);
			wysiwygContainer.data('visible', false);
			
			/* Allow sub tabs content container to scroll again */
			$('div.sub-tabs-content-container').css('overflow-y', 'auto');
			
			/* Remove the events */
			$(document).unbind('mousedown', wysiwygClose);
			Headway.iframe.contents().unbind('mousedown', wysiwygClose);
			
			$(window).unbind('resize', wysiwygClose);
			
		}

	
	/* Integer */
	$('div.input-integer input', context).bind('focus', function() {
		
		if ( typeof originalValues !== 'undefined' ) {
			delete originalValues;
		}
		
		originalValues = new Object;		
		originalValues[$(this).attr('name')] = $(this).val();
		
	});
	
	$('div.input-integer input', context).bind('keyup blur', function(event) {
		
		value = $(this).val();
		
		if ( event.type == 'keyup' && value == '-' )
			return;
		
		/* Validate the value and make sure it's a number */
		if ( isNaN(value) ) {

			/* Take the nasties out to make sure it's a number */
			value = value.replace(/[^0-9]*/ig, '');

			/* If the value is an empty string, then revert back to the original value */
			if ( value === '' ) {

				var value = originalValues[$(this).attr('name')];

			}
			
			/* Set the value of the input to the sanitized value */
			$(this).val(value);

		}

		/* Remove leading zeroes */
		if ( value.length > 1 && value[0] == 0 ) {

			value = value.replace(/^[0]+/g, '');
			
			/* Set the value of the input to the sanitized value */
			$(this).val(value);

		}
		
		updatePanelInputHidden({input: $(this), value: value});
		
		allowSaving();
		
	});
	
	
	/* Checkboxes */
	$('div.input-checkbox', context).bind('click', function() {
		
		var input = $(this).find('input');
		var label = $(this).find('label');
		var button = $(this).find('img, label');
		
		if ( label.hasClass('checkbox-checked') === true ) {

			button.removeClass('checkbox-checked');
			
			input.val(false);
			
			updatePanelInputHidden({input: input, value: false});

		} else {

			button.addClass('checkbox-checked');
			
			input.val(true);
			
			updatePanelInputHidden({input: input, value: true});

		}
		
		allowSaving();

	});


	/* Multi-select */
	$('div.input-multi-select select', context).bind('click', function() {

		updatePanelInputHidden({input: $(this), value: $(this).val()});
		
		allowSaving();	
					
	});
	
	$('div.input-multi-select span.multi-select-open', context).bind('click', function() {
		
		var multiSelectContainer = $(this).siblings('.multi-select-container');
		var multiSelect = multiSelectContainer.find('select');
		
		var inputContainerOffset = $(this).parents('.input').offset();
		
		multiSelectContainer.css({
			top: inputContainerOffset.top - multiSelectContainer.outerHeight(true),
			left: inputContainerOffset.left
		});
		
		/* Keep the sub tabs content container from scrolling */
		$('div.sub-tabs-content-container').css('overflow-y', 'hidden');
		
		if ( multiSelectContainer.data('visible') !== true ) {
		
			/* Show the multi-select */
			multiSelectContainer.fadeIn(150);
			multiSelectContainer.data('visible', true);
		
			/* Bind the document close */
			$(document).bind('mousedown', {multiSelectContainer: multiSelectContainer}, multiSelectClose);
			Headway.iframe.contents().bind('mousedown', {multiSelectContainer: multiSelectContainer}, multiSelectClose);
			
			$(window).bind('resize', {multiSelectContainer: multiSelectContainer}, multiSelectClose);
		
		} else {
			
			/* Hide the multi-select */
			multiSelectContainer.fadeOut(150);
			multiSelectContainer.data('visible', false);
			
			/* Allow sub tabs content container to scroll again */
			$('div.sub-tabs-content-container').css('overflow-y', 'auto');

			/* Remove the events */
			$(document).unbind('mousedown', multiSelectClose);
			Headway.iframe.contents().unbind('mousedown', multiSelectClose);
			
			$(window).unbind('resize', multiSelectClose);
			
		}
		
	});
	
	multiSelectClose = function(event) {
				
		/* Do not trigger this if they're clicking the same button that they used to open the multi-select */
		if ( $(event.target).parents('div.input-multi-select div.input-right').length === 1 )
			return;
		
		var multiSelectContainer = event.data.multiSelectContainer;
		
		/* Hide the multi-select */
		multiSelectContainer.fadeOut(150);
		multiSelectContainer.data('visible', false);
		
		/* Allow sub tabs content container to scroll again */
		$('div.sub-tabs-content-container').css('overflow-y', 'auto');
		
		/* Remove the events */
		$(document).unbind('mousedown', multiSelectClose);
		Headway.iframe.contents().unbind('mousedown', multiSelectClose);
		
		$(window).unbind('resize', multiSelectClose);
		
	}


	/* Sliders */
	$('div.input-slider div.input-slider-bar', context).each(function() {
		
		var self = this;

		var value = parseInt($(this).parents('.input-slider').find('input.input-slider-bar-hidden').val());

		var min = parseInt($(this).attr('slider_min'));
		var max = parseInt($(this).attr('slider_max'));
		var interval = parseInt($(this).attr('slider_interval'));

		$(this).slider({
			range: 'min',
			value: value,
			min: min,
			max: max,
			step: interval,
			slide: function( event, ui ) {
				
				/* Update visible output */
				$(this).siblings('div.input-slider-bar-text').find('span.slider-value').text(ui.value);

				/* Update hidden input */
				$(this).parents('.input-slider').find('input.input-slider-bar-hidden').val(ui.value);

				/* Handle hidden input */
				updatePanelInputHidden({input: $(this).parents('.input-slider').find('input.input-slider-bar-hidden'), value: ui.value});

				allowSaving();
				
			}
		});
		
	});


	/* Image Uploaders */
	$('div.input-image span.button', context).bind('click', function() {
		
		var self = this;
		
		openImageUploader(function(url, filename) {
						
			$(self).siblings('input').val(url);
			$(self).siblings('span.src').show().text(filename);

			$(self).siblings('span.delete-image').show();

			updatePanelInputHidden({input: $(self).siblings('input'), value: url, action: 'add'});	
			
		});

	});
	
	$('div.input-image span.delete-image', context).bind('click', function() {

		if ( !confirm('Are you sure you wish to remove this image?') ) {
			return false;
		}

		$(this).siblings('.src').hide();
		$(this).hide();

		$(this).siblings('input').val('');

		updatePanelInputHidden({input: $(this).siblings('input'), value: '', action: 'delete'});

		allowSaving();

	});


	/* Multi-Image Uploader */
	$('div.input-multi-image span.multi-image-container-open', context).bind('click', function(){
		
		var multiImageContainer = $(this).siblings('.multi-image-container');
		
		var inputContainerOffset = $(this).parents('.input').offset();
		
		multiImageContainer.css({
			top: inputContainerOffset.top - multiImageContainer.outerHeight(true),
			left: inputContainerOffset.left
		});
		
		/* Keep the sub tabs content container from scrolling */
		$('div.sub-tabs-content-container').css('overflow-y', 'hidden');
		
		if ( multiImageContainer.data('visible') !== true ) {
		
			/* Show the container */
			multiImageContainer.fadeIn(150);
			multiImageContainer.data('visible', true);
		
			/* Set up sortable */
			multiImageContainer.find('ul').sortable('destroy');
			
			multiImageContainer.find('ul').sortable({
				axis: 'y',
				items: 'li.image',
				update: function(event, ui) {
					
					var images = [];

					multiImageContainer.find('li.image span.src').each(function(){
						images.push($(this).attr('url'));
					});

					updatePanelInputHidden({input: multiImageContainer.find('input'), value: images, action: 'sort'});	
					
					allowSaving();
					
				}
			});
			
			$('span.delete-image', multiImageContainer).unbind('click').bind('click', {container: multiImageContainer}, deleteMultiImage);
		
			/* Bind the document close */
			$(document).bind('mousedown', {multiImageContainer: multiImageContainer}, multiImageContainerClose);
			Headway.iframe.contents().bind('mousedown', {multiImageContainer: multiImageContainer}, multiImageContainerClose);

			$(window).bind('resize', {multiImageContainer: multiImageContainer}, multiImageContainerClose);
		
		} else {
			
			/* Hide the textarea */
			multiImageContainer.fadeOut(150);
			multiImageContainer.data('visible', false);
			
			/* Allow sub tabs content container to scroll again */
			$('div.sub-tabs-content-container').css('overflow-y', 'auto');

			/* Remove the events */
			$(document).unbind('mousedown', multiImageContainerClose);
			Headway.iframe.contents().unbind('mousedown', multiImageContainerClose);

			$(window).unbind('resize', multiImageContainerClose);
			
		}
		
	});
	
	$('div.input-multi-image div.multi-image-container li.add-image span.button').bind('click', function() {
		
		var container = $(this).parents('div.multi-image-container');
		var self = this;
		
		openImageUploader(function(url, filename) {
			
			var addImageLi = $(self).parent();
			
			var newImage = $('<li class="image"><span class="src" url="' + url + '">' + filename + '</span><span class="delete-image">Delete</span></li>');
			
			newImage.insertBefore(addImageLi);
			
			$('span.delete-image', newImage).unbind('click').bind('click', {container: container}, deleteMultiImage);
			
			container.find('ul').sortable('refresh');
			
			var images = [];
			
			container.find('li.image span.src').each(function(){
				images.push($(this).attr('url'));
			});
			
			updatePanelInputHidden({input: container.find('input'), value: images, action: 'add'});	

		});
		
	});
	
	deleteMultiImage = function(event) {
		
		if ( !confirm('Are you sure you wish to remove this image?') ) {
			return false;
		}
		
		var container = event.data.container;
			
		$(this).parent().remove();
		
		var images = [];
		
		container.find('li.image span.src').each(function(){
			images.push($(this).attr('url'));
		});
		
		updatePanelInputHidden({input: container.find('input'), value: images, action: 'delete'});
		
		allowSaving();
						
	}
	
	multiImageContainerClose = function(event) {
						
		/* Do not trigger this if they're clicking the same button that they used to open the multi-image uploader */
		if ( $(event.target).parents('div.input-multi-image div.input-right').length === 1 || $(event.target).parents('div#box-input-image').length === 1 )
			return;
		
		var multiImageContainer = event.data.multiImageContainer;
		
		/* Hide the container */
		multiImageContainer.fadeOut(150);
		multiImageContainer.data('visible', false);
		
		/* Allow sub tabs content container to scroll again */
		$('div.sub-tabs-content-container').css('overflow-y', 'auto');
		
		/* Remove the events */
		$(document).unbind('mousedown', multiImageContainerClose);
		Headway.iframe.contents().unbind('mousedown', multiImageContainerClose);

		$(window).unbind('resize', multiImageContainerClose);
		
	}
		

	/* Color Inputs */
	$('div.input-colorpicker div.colorpicker-box', context).bind('click', function() {
		
		var offset = $(this).offset();
		
		var colorpickerWidth = 356;
		var colorpickerHeight = 196;
				
		var colorpickerLeft = offset.left;
		var colorpickerTop = offset.top - colorpickerHeight + $(this).outerHeight();
										
		//If the colorpicker is bleeding to the right of the window, flip it to the left
		if ( (offset.left + colorpickerWidth) > $(window).width() )
			//6 pixels at end is just for a precise adjustment.  Color picker width and color picker box outer width don't get it to the precise position.
			colorpickerLeft = offset.left - colorpickerWidth + $(this).outerWidth() + 6;
			
		/* Keep the sub tabs content container from scrolling */
		$('div.sub-tabs-content-container').css('overflow-y', 'hidden');	
			
		//If the colorpicker exists, just show it
		if ( $(this).data('colorpickerId') ) {
			
			var colorpicker = $('div#' + $(this).data('colorpickerId'));
														
			$(this).colorPickerShow();
						
			//Put the CSS after showing so it actually applies
			colorpicker.css({
				top: colorpickerTop + 'px',
				left: colorpickerLeft + 'px'
			});
			
			return true;
			
		}
		
		//Colorpicker doesn't exist, we have to create and bind stuff
		$(this).colorPicker({
			position: {
				top: colorpickerTop,
				left: colorpickerLeft,
				position: 'fixed'
			},
			eventName: false, /* Make it so it doesn't bind the colorpicker-box click event */
			onChange: function(hsb, hex, rgb, el) {	

				//this refers to colorpicker object
				
				if ( hex == 'transparent' ) {
					var color = 'transparent';
				} else {
					var color = '#' + hex;
				}

				var input = $(el).siblings('input');
				var colorpickerColor = $(el).children('.colorpicker-color');

				/* Call developer-defined callback */
				var callback = eval(input.attr('callback'));
				callback($(el), input, {value: color});
				/* End Callback */

				//Update the color of the original element
				colorpickerColor.css('background-color', color);
				
				//If the color is transparent, add the transparent class to the colorpicker color.  Otherwise, remove the class.
				if ( color == 'transparent' ) {
					colorpickerColor.addClass('colorpicker-color-transparent');
				} else {
					colorpickerColor.removeClass('colorpicker-color-transparent');
				}

				//Update the input
				input.val(color);
				
				//Update the hidden flag
				updatePanelInputHidden({input: input, value: color});

				allowSaving();

			},
			onSubmit: function(hsb, hex, rgb, el) {

				//this refers to colorpicker object
				if ( hex == 'transparent' ) {
					var color = 'transparent';
				} else {
					var color = '#' + hex;
				}

				var input = $(el).siblings('input');
				var colorpickerColor = $(el).children('.colorpicker-color');

				/* Call developer-defined callback */
				var callback = eval(input.attr('callback'));
				callback($(el), input, {value: color});
				/* End Callback */

				//Update the color of the original element
				colorpickerColor.css('background-color', color);
				
				//If the color is transparent, add the transparent class to the colorpicker color.  Otherwise, remove the class.
				if ( color == 'transparent' ) {
					colorpickerColor.addClass('colorpicker-color-transparent');
				} else {
					colorpickerColor.removeClass('colorpicker-color-transparent');
				}

				//Update the input
				input.val(color);
				
				//Update the hidden flag
				updatePanelInputHidden({input: input, value: color});

				//Hide the colorpicker
				$(el).colorPickerHide();
				
				/* Allow sub tabs content container to scroll again */
				$('div.sub-tabs-content-container').css('overflow-y', 'auto');

				allowSaving();	

			},
			onBeforeShow: function() {

				//this refers to colorpicker box
				var input = $(this).siblings('input');

				$(this).colorPickerSetColor(input.val());

			},
			onHide: function() {
				
				/* Allow sub tabs content container to scroll again */
				$('div.sub-tabs-content-container').css('overflow-y', 'auto');
				
			}
		});

		return $(this).colorPickerShow();
						
	});


}
})(jQuery);