(function($) {

visualEditorModeDesign = function() {


	$('#toggle-inspector').bind('click', toggleInspector);
	
	
	this.init = function() {
		
		designEditor = new designEditorTabEditor();
		defaultsTabInstance = new designEditorTabDefaults();
		
		designEditorBindPropertyInputs();
		
	}
	
	
	this.iframeCallback = function() {
		
		addBlockControls(true, false);
		addInspector();

		/* Reset editor for layout switch */
		designEditor.switchLayout();
		
	}

	
}


/* DESIGN EDITOR TABS */
	designEditorTabEditor = function() {
	
		this.context = 'div#editor-tab';
	
		this._init = function() {
		
			this.setupBoxes();
			this.setupElementSelector();
			this.bindDesignEditorInfo();
		
		}
	
		this.setupBoxes = function() {
								
			designEditorBindPropertyBoxToggle(this.context);
		
		}
	
		this.setupElementSelector = function() {
				
			/* Setup properties box */
			$('div.design-editor-options', this.context).masonry({
				itemSelector:'div.design-editor-box',
				columnWidth: 240
			});

			$('div.design-editor-options-container', this.context).scrollbarPaper();
			/* End properties */

			/* Bind the element clicks */
			$('ul.element-selector li span', this.context).live('click', function(event) {

				var link = $(this).parent();

				if ( link.hasClass('element-group') )
					return;

				designEditor.processElementClick(link);				

				link.siblings('.ui-state-active').removeClass('ui-state-active');
				link.addClass('ui-state-active');

			});
			/* End binding */

			/* Add scrollbars to groups, main elements, and sub elements */
			$('ul.element-selector', this.context).scrollbarPaper();
		
		}

		this.processElementClick = function(link, type, name, elementID) {
		
			/* Set up variables */
			if ( typeof link != 'undefined' && link ) {
				var elementType = link.hasClass('main-element') ? 'main' : 'sub';
				var elementName = link.text();
				var element = link.attr('id').replace(/^element\-/ig, '');
			} else {
				var elementType = type;
				var elementName = name;
				var element = elementID;
			}

			/* If it is a main element has children, display them.  Otherwise hide them */
			if ( link.hasClass('has-children') && elementType == 'main' ) {

				/* If we're selecting a new main element, display the new sub elements */
				if ( $('ul#design-editor-sub-elements', designEditor.context).data('main_element') !== element ) {

					$('ul#design-editor-sub-elements', designEditor.context).show();
					$('ul#design-editor-sub-elements li', designEditor.context).hide().removeClass('ui-state-active');
					$('ul#design-editor-sub-elements li.parent-element-' + element, designEditor.context).show();

					$('ul#design-editor-sub-elements', designEditor.context).data('main_element', element);
				
					/* Refresh scrollbar for sub elements */
					$('ul#design-editor-sub-elements', designEditor.context).scrollbarPaper();

				/* Else the sub elements are already visible and we're just going back to the main element, just remove the selected element from sub	*/						
				} else {

					$('ul#design-editor-sub-elements li.ui-state-active', this.context).removeClass('ui-state-active');		

				}

			/* There are no children, hide them. */
			} else if ( elementType == 'main' ) {

				/* Hide sub elements panel and scrollbar */
				$('ul#design-editor-sub-elements', this.context).hide().data('main_element', false);
				$('div#scrollbarpaper-design-editor-sub-elements', this.context).hide();

			}

			/* LOAD INPUTS, INSTANCES, AND STATES */
				designEditorShowCog(this.context);

				$.when(
					this.loadElementInputs(element),
					this.loadElementInstances(element),
					this.loadElementStates(element),
					this.getElementInheritLocation(element)
				).then(function() {
					designEditorShowContent(this.context, true);
				});	
			/* END LOAD INPUTS */

		}

			this.loadElementInputs = function(element) {

				return $.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: element,
					unsavedValues: designEditorGetUnsavedValues(element),
					security: Headway.security
				}).success(function(inputs) {
				
					var options = $('div.design-editor-options', designEditor.context);
					var previousElement = options.data('element') || false;
					var previousElementSpecialElementType = options.data('specialElementType') || false;

					$('div.design-editor-options', designEditor.context).html(inputs);

					/* Highlight the selected element */
					var selector = $(options.find('input').get(0)).attr('element_selector');

					$i('.inspector-element-selected').removeClass('inspector-element-selected');
					$i(selector).addClass('inspector-element-selected');

					/* Add the selector to the info bar */
					$('div.design-editor-info code', designEditor.context).text(selector);

					/* If there are 4 or less property groups, then open them */
					if ( $('div.design-editor-options .design-editor-box', designEditor.context).length <= 4 )
						$('div.design-editor-options .design-editor-box', designEditor.context).removeClass('design-editor-box-minimized');
												
					/* Set the flags */
					$('div.design-editor-options', designEditor.context).data({'element': element, 'specialElementType': false, 'specialElementMeta': false});

					/* Focus the iframe to allow immediate nudging control */
					Headway.iframe.focus();

				});

			}

			this.loadElementInstances = function(element) {

				/* Element selector node */
				var elementSelectorNode = $('li#element-' + element);

				var instances = elementSelectorNode.data('instances');

				if ( !instances || !Object.keys(instances).length ) {

					$('div.design-editor-info select.instances', designEditor.context).hide();

				} else {

					$('div.design-editor-info select.instances', designEditor.context).show();

					var instancesStr = '';

					$.each(instances, function(id, name){
						instancesStr += '<option value="' + id + '">' + name + '</option>';
					});

					var instanceOptions = '<option value="">&mdash; Instances &mdash;</option>' + instancesStr;
					$('div.design-editor-info select.instances', designEditor.context).html(instanceOptions);

				}

				return true;

			}

			this.loadElementStates = function(element) {

				/* Element selector node */
				var elementSelectorNode = $('li#element-' + element);

				var states = elementSelectorNode.data('states');

				if ( !states || !Object.keys(states).length ) {

					$('div.design-editor-info select.states', designEditor.context).hide();

				} else {

					$('div.design-editor-info select.states', designEditor.context).show();

					var statesStr = '';

					$.each(states, function(id, name){
						statesStr += '<option value="' + id + '">' + name + '</option>';
					});

					var statesOptions = '<option value="">&mdash; States &mdash;</option>' + statesStr;
					$('div.design-editor-info select.states', designEditor.context).html(statesOptions);

				}

				return true;

			}

			this.getElementInheritLocation = function(element) {

				/* Element selector node */
				var elementSelectorNode = $('li#element-' + element);

				/* Add element name to info box */					
				$('div.design-editor-info h4 span', designEditor.context).text(elementSelectorNode.text());
			
				/* Reset layout element button */
				$('span.customize-element-for-layout').text('Customize For Current Layout');
			
				/* Show and fill inherit location if it exists and hide it if not */
				var inheritLocation = elementSelectorNode.data('inherit-location');

				if ( typeof inheritLocation != 'undefined' && inheritLocation.length ) {

					$('div.design-editor-info h4 strong', designEditor.context)
						.text('(Inheriting From ' + $.trim(inheritLocation) + ')')
						.show();
				
				} else {
				
					$('div.design-editor-info h4 strong', designEditor.context).hide();
				
				}

			}
	
		this.bindDesignEditorInfo = function() {
				
			/* Customize for layout button */
			$('span.customize-element-for-layout', this.context).bind('click', this.customizeForCurrentLayout);
		
			/* Customize for regular element button */
			$('span.customize-for-regular-element', this.context).bind('click', this.customizeRegularElement);
		
			/* Instances select */
			$('select.instances', this.context).bind('change', this.selectInstance);
		
			/* States select */
			$('select.states', this.context).bind('change', this.selectState);
		
		}

			this.customizeForCurrentLayout = function(event) {

				var options = $('div.design-editor-options', designEditor.context);
				
				var currentElement = designEditor.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^element\-/ig, '');
				var currentElementName = currentElement.text();
								
				/* Hide everything and show the cog */
				designEditorShowCog(designEditor.context);
				
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', designEditor.context).html(currentElementName + '<em> on ' + Headway.currentLayoutName + ' Layout</em>');
				$('div.design-editor-info h4 strong', designEditor.context)
					.html('(Inheriting From ' + currentElementName + ')')
					.show();
				
				/* Hide current button, states, instances, and show the button to return to the regular element */
				$(this).hide();
				
				$('div.design-editor-info select.instances', designEditor.context).hide();
				$('div.design-editor-info select.states', designEditor.context).hide();
				
				$('div.design-editor-info span.customize-for-regular-element', designEditor.context).show();
				
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'layout',
					specialElementMeta: Headway.currentLayout,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'layout', Headway.currentLayout),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', designEditor.context).html(inputs);

					designEditorShowContent(designEditor.context);

				});
				
				/* Set the flags */
				$('div.design-editor-options', designEditor.context).data({'element': currentElementID, 'specialElementType': 'layout', 'specialElementMeta': Headway.currentLayout});

			}

			this.customizeRegularElement = function(event) {

				var currentElement = designEditor.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^element\-/ig, '');
				var currentElementName = currentElement.text();
								
				currentElement.find('span').trigger('click');
				
				/* Hide the current button and bring back the layout-specific element button */
				$('div.design-editor-info span.customize-for-regular-element', designEditor.context).hide();
				$('div.design-editor-info span.customize-element-for-layout', designEditor.context).show();

			}

			this.selectInstance = function(instanceID, instanceName, loadInfo) {

				var options = $('div.design-editor-options', designEditor.context);
				
				var currentElement = designEditor.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^element\-/ig, '');
				var currentElementName = currentElement.text();

				if ( typeof instanceID != 'string' )
					var instanceID = $(this).val();

				if ( typeof instanceName != 'string' )
					var instanceName = $(this).find(':selected').text();

				if ( !instanceID ) {
					return designEditor.customizeRegularElement();
				}
				
				/* Hide everything and show the cog */
				designEditorShowCog(designEditor.context);
				
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', designEditor.context).html(instanceName);
				$('div.design-editor-info h4 strong', designEditor.context)
					.html('(Inheriting From ' + $.trim(currentElementName) + ')')
					.show();

				/* Load instances select */
				designEditor.loadElementInstances(currentElementID);
				
				/* Hide states, layout-specific button, and show the button to return to the regular element */					
				$('div.design-editor-info select.states', designEditor.context).hide();
				$('div.design-editor-info span.customize-element-for-layout', designEditor.context).hide();
				
				$('div.design-editor-info span.customize-for-regular-element', designEditor.context).show();
				
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'instance',
					specialElementMeta: instanceID,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'instance', instanceID),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', designEditor.context).html(inputs);

					/* Highlight the selected instance */
					var selector = $(options.find('input').get(0)).attr('element_selector');

					$i('.inspector-element-selected').removeClass('inspector-element-selected');
					$i(selector).addClass('inspector-element-selected');

					/* Add the selector to the info bar */
					$('div.design-editor-info code', designEditor.context).text(selector);

					/* If there are 4 or less property groups, then open them */
					if ( $('div.design-editor-options .design-editor-box', designEditor.context).length <= 4 )
						$('div.design-editor-options .design-editor-box', designEditor.context).removeClass('design-editor-box-minimized');

					$('select.instances').val(instanceID);

					designEditorShowContent(designEditor.context);

				});
				
				/* Set the flags */
				$('div.design-editor-options', designEditor.context).data({'element': currentElementID, 'specialElementType': 'instance', 'specialElementMeta': instanceID});

			}

			this.selectState = function(event) {

				var options = $('div.design-editor-options', designEditor.context);
				
				var currentElement = designEditor.getCurrentElement();
				var currentElementID = currentElement.attr('id').replace(/^element\-/ig, '');
				var currentElementName = currentElement.text();
				
				var stateID = $(this).val();
				var stateName = $(this).find(':selected').text();
				
				if ( !stateID )
					return false;

				/* Hide everything and show the cog */
				designEditorShowCog(designEditor.context);
				
				/* Change which element is being edited and the inheritance */
				$('div.design-editor-info h4 span', designEditor.context).html(currentElementName + ' &ndash; ' + stateName);
				$('div.design-editor-info h4 strong', designEditor.context)
					.html('(Inheriting From ' + currentElementName + ')')
					.show();
				
				/* Hide instances, layout-specific button, and show the button to return to the regular element */					
				$('div.design-editor-info select.instances', designEditor.context).hide();
				$('div.design-editor-info span.customize-element-for-layout', designEditor.context).hide();
				
				$('div.design-editor-info span.customize-for-regular-element', designEditor.context).show();
								
				/* Get the properties */
				$.post(Headway.ajaxURL, {
					action: 'headway_visual_editor',
					method: 'get_element_inputs',
					element: currentElementID,
					specialElementType: 'state',
					specialElementMeta: stateID,
					unsavedValues: designEditorGetUnsavedValues(currentElementID, 'state', stateID),
					security: Headway.security,
				}).success(function(inputs) {

					$('div.design-editor-options', designEditor.context).html(inputs);

					/* Highlight the selected state as long as it's not a pseudo-selector */
					var selector = $(options.find('input').get(0)).attr('element_selector');

					if ( selector.indexOf(':') == -1 ) {
						$i('.inspector-element-selected').removeClass('inspector-element-selected');
						$i(selector).addClass('inspector-element-selected');
					}

					/* Add the selector to the info bar */
					$('div.design-editor-info code', designEditor.context).text(selector);

					/* If there are 4 or less property groups, then open them */
					if ( $('div.design-editor-options .design-editor-box', designEditor.context).length <= 4 )
						$('div.design-editor-options .design-editor-box', designEditor.context).removeClass('design-editor-box-minimized');

					designEditorShowContent(designEditor.context);

				});
				
				/* Set the flags */
				$('div.design-editor-options', designEditor.context).data({'element': currentElementID, 'specialElementType': 'state', 'specialElementMeta': stateID});

			}
	
		this.getCurrentElement = function() {
		
			/* Check against sub elements then main elements. */
			if ( $('ul#design-editor-sub-elements li.ui-state-active', this.context).length === 1 ) {
			
				return $('ul#design-editor-sub-elements li.ui-state-active', this.context);
			
			} else if ( $('ul#design-editor-main-elements li.ui-state-active', this.context).length === 1 ) {
			
				return $('ul#design-editor-main-elements li.ui-state-active', this.context);
			
			} else {
			
				return null;
			
			}
		
		}
	
		this.switchLayout = function() {
		
			/* If editing layout-specific element, switch back to normal element. */
			var currentElement = this.getCurrentElement();
						
			if ( !currentElement || currentElement.length === 0 )
				return false;
		
			currentElement.find('span').trigger('click');
		
		}
	
		this._init();
	
	}

	designEditorTabDefaults = function() {
	
		this.context = 'div#defaults-tab';
	
		this._init = function() {
		
			this.setupBoxes();
			this.setupElementSelector();
		
		}
	
		this.setupBoxes = function() {
								
			designEditorBindPropertyBoxToggle(this.context);
		
		}
	
		this.setupElementSelector = function() {
		
			var self = this;
		
			/* Setup properties box */
			$('div.design-editor-options', this.context).masonry({
				itemSelector:'div.design-editor-box',
				columnWidth: 240
			});

			$('div.design-editor-options-container', this.context).scrollbarPaper();
			/* End properties */

			/* Bind the element clicks */
			$('ul.element-selector li span', this.context).live('click', function(event) {

				var link = $(this).parent();

				self.processDefaultElementClick(link);				

				link.siblings('.ui-state-active').removeClass('ui-state-active');
				link.addClass('ui-state-active');

			});
			/* End binding */

			/* Add scrollbars to groups, main elements, and sub elements */
			$('ul.element-selector', this.context).scrollbarPaper();
		
		}
	
		this.processDefaultElementClick = function(link) {
		
			var self = this;

			/* Set up variables */
			var elementType = link.hasClass('main-element') ? 'main' : 'sub';
			var elementName = link.text();
			var element = link.attr('id').replace(/^element\-/ig, '');

			/* LOAD INPUTS, INSTANCES, AND STATES */
				designEditorShowCog(this.context);

				$.when(

					/* Inputs */
					$.post(Headway.ajaxURL, {
						action: 'headway_visual_editor',
						method: 'get_element_inputs',
						element: element,
						specialElementType: 'default',
						unsavedValues: designEditorGetUnsavedValues(element, 'default'),
						security: Headway.security
					}).success(function(inputs) {

						$('div.design-editor-options', self.context).html(inputs);

						/* If there are 4 or less property groups, then open them */
						if ( $('div.design-editor-options .design-editor-box', self.context).length <= 4 )
							$('div.design-editor-options .design-editor-box', self.context).removeClass('design-editor-box-minimized');

					})
				
				/* Everything is done, we can hide cog and show options now */
				).then(function() {

					/* Add element name to info box */					
					$('div.design-editor-info h4 span', self.context).text(elementName);

					/* Show everything and hide cog */
					designEditorShowContent(self.context);

				});			
			/* END LOAD INPUTS */

		}
	
		this._init();
	
	}
/* END DESIGN EDITOR TABS */


/* CONTENT TOGGLING */
	designEditorShowCog = function(context) {
					
		$('p.design-editor-options-instructions', context).hide();
		$('div.design-editor-options', context).hide();
		$('div.design-editor-info', context).hide();
		
		createCog($('div.design-editor-options-container', context), true, true);
		
	}

	designEditorShowContent = function(context, refreshInfoButtons) {
		
		refreshInfoButtons = typeof refreshInfoButtons == 'undefined' ? false : true;
	
		/* Show info/options and hide cog/instructions */
		$('div.design-editor-info', context).show();
		$('div.design-editor-options', context).show();
	
		$('p.design-editor-options-instructions', context).hide();
		$('div.design-editor-options-container', context).find('.cog-container').remove();

		/* Run Masonry after everything is visible */
		$('div.design-editor-options', context).masonry('reload');
		
		/* Reset the Customize Regular Element/For Current Layout buttons */
		if ( refreshInfoButtons ) {
			
			$('div.design-editor-info span.customize-element-for-layout', context).show();
			$('div.design-editor-info span.customize-for-regular-element', context).hide();
		
		}
	
		/* Refresh Tooltips */
		setupTooltips();
	
	}

	designEditorShowInstructions = function(context) {
	
		$('div.design-editor-options-container div.cog-container', context).remove();
		$('div.design-editor-options', context).hide();
		$('div.design-editor-info', context).hide();

		$('p.design-editor-options-instructions', context).show();
	
	}
/* END CONTENT TOGGLING */


/* DESIGN EDITOR OPTIONS/INPUTS */
	designEditorGetUnsavedValues = function(element, specialElementType, specialElementMeta) {
		
		if ( typeof specialElementType == 'undefined' )
			var specialElementType = false;
		
		if ( typeof specialElementMeta == 'undefined' )
			var specialElementMeta = false;
		
		var inputs = $('input[element="' + element + '"]', 'div#visual-editor-hidden-inputs');
		var properties = {};
		
		/* Filter by special elements if those are set */
		if ( specialElementType )
			inputs = inputs.filter('[specialElementType="' + specialElementType + '"]');
		else
			inputs = inputs.filter('[specialElementType="false"]');
			
		if ( specialElementMeta )
			inputs = inputs.filter('[specialElementMeta="' + specialElementMeta + '"]');
		else
			inputs = inputs.filter('[specialElementMeta="false"]');
			
		/* Construct the object to be outputted */
		inputs.each(function() {
		
			properties[$(this).attr('property')] = $(this).val();
			
		});
								
		return Object.keys(properties).length > 0 ? properties : null;
		
	}

	designEditorBindPropertyBoxToggle = function(context) {
		
		$('div.design-editor-options', context).delegate('span.design-editor-box-toggle, span.design-editor-box-title', 'click', function(){

			var box = $(this).parents('div.design-editor-box');

			box.toggleClass('design-editor-box-minimized');

			$('div.design-editor-options', context).masonry('reload');

		});

	}

	designEditorBindPropertyInputs = function() {
		
		/* Customize Buttons */
		$('div#panel').delegate('div.customize-property', 'click', function() {
			
			var property = $(this).parents('li');
			var hidden = property.find('input.property-hidden-input');
			
			$(this).parents('li').removeClass('uncustomized-property', 150);
			$(this).fadeOut(150);
			
			setTimeout(function() {

				/* When clicking on Customize on a property that uses a select, sometimes the first option in the select is what you want.  
				This will fill the hidden input with it */
				if ( !hidden.val() && hidden.siblings('select') ) {
					hidden.val(hidden.siblings('select').val());
				}

				designEditorUpdateInputHidden(hidden, hidden.val());

				allowSaving();
				
			}, 160);
			
		});
		
		/* Uncustomize Button */
		$('div#panel').delegate('span.uncustomize-property', 'click', function() {
			
			if ( !confirm('Are you sure you wish to uncustomize this property?  The value will be reset.') )
				return false;
			
			var property = $(this).parents('li');
			var hidden = property.find('input.property-hidden-input');
			
			property.find('div.customize-property')
				.fadeIn(150);
				
			property.addClass('uncustomized-property', 150);
			
			designEditorUpdateInputHidden(hidden, null);
			
			/* Remove the CSS declaration */
			var selector = hidden.attr('element_selector') || false;
			var property = hidden.attr('property').toLowerCase();
							
			if ( selector && property )
				stylesheet.delete_rule_property(selector, property);
							
			allowSaving();
			
		});
		
		/* Select */
		$('div#panel').delegate('div.property-select select', 'change', designEditorInputSelect);
		
		/* Font Select */
		$('div#panel').delegate('div.property-font-family-select select', 'change', designEditorInputFontSelect);
		
		/* Integer */
		$('div#panel').delegate('div.property-integer input', 'focus', designEditorInputIntegerFocus);
		
		$('div#panel').delegate('div.property-integer input', 'keyup blur change', designEditorInputIntegerChange);
				
		/* Image Uploaders */
		$('div#panel').delegate('div.property-image span.button', 'click', designEditorInputImageUpload);

		$('div#panel').delegate('div.property-image span.delete-image', 'click', designEditorInputImageUploadDelete);

		/* Color Inputs */
		$('div#panel').delegate('div.property-color div.colorpicker-box', 'click', designEditorInputColor);
		
	}
/* END DESIGN EDITOR INPUTS */


/* INPUT FUNCTIONALITY */
	/* Select */
	designEditorInputSelect = function(event) {
		
		var hidden = $(this).siblings('input.property-hidden-input');
						
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */
		
		designEditorUpdateInputHidden(hidden, $(this).val());

		allowSaving();
		
	}

	/* Font Select */
	designEditorInputFontSelect = function(event) {
		
		var hidden = $(this).siblings('input.property-hidden-input');
						
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */
		
		designEditorUpdateInputHidden(hidden, $(this).val());
		
		/* Change the font of the select to the selected option */
		$(this).css('fontFamily', $(this).val());

		allowSaving();
		
	}

	/* Integer */
	designEditorInputIntegerFocus = function(event) {

		if ( typeof originalValues !== 'undefined' ) {
			delete originalValues;
		}
		
		originalValues = new Object;
		
		var hidden = $(this).siblings('input.property-hidden-input');
		var id = hidden.attr('selector') + '-' + hidden.attr('property');
		
		originalValues[id] = $(this).val();
		
	}
	
	designEditorInputIntegerChange = function(event) {

		var hidden = $(this).siblings('input.property-hidden-input');
		var value = $(this).val();

		if ( event.type == 'keyup' && value == '-' )
			return;
		
		/* Validate the value and make sure it's a number */
		if ( isNaN(value) ) {
			
			/* Take the nasties out to make sure it's a number */
			value = value.replace(/[^0-9]*/ig, '');
			
			/* If the value is an empty string, then revert back to the original value */
			if ( value === '' ) {
				
				var id = hidden.attr('selector') + '-' + hidden.attr('property');
				var value = originalValues[id];
										
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
		
		
		/* Call callback  */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden);
		/* End Callback */

		designEditorUpdateInputHidden(hidden, $(this).val());

		allowSaving();
		
	}

	/* Image Uploaders */
	designEditorInputImageUpload = function(event) {
		
		var self = this;
		
		openImageUploader(function(url, filename) {
			
			var hidden = $(self).siblings('input');

			hidden.val(url);

			$(self).siblings('.image-input-controls-container').find('span.src').text(filename);
			$(self).siblings('.image-input-controls-container').show();

			designEditorUpdateInputHidden(hidden, url);

			/* Call developer-defined callback */
			var callback = eval(hidden.attr('callback'));
			callback($(self), hidden, {method: 'add', value: url});
			/* End Callback */
			
		});
		
	}
	
	designEditorInputImageUploadDelete = function(event) {
		
		if ( !confirm('Are you sure you wish to remove this image?') ) {
			return false;
		}

		$(this).parent('.image-input-controls-container').hide();
		$(this).hide();
		
		var hidden = $(this).parent().siblings('input');

		hidden.val('');

		designEditorUpdateInputHidden(hidden, '');	

		/* Call developer-defined callback */
		var callback = eval(hidden.attr('callback'));
		callback($(this), hidden, {method: 'delete'});
		/* End Callback */

		allowSaving();
		
	}
	
	/* Color Inputs */
	designEditorInputColor = function(event) {
		
		var offset = $(this).offset();
		
		var colorpickerWidth = 356;
		var colorpickerHeight = 196;
		
		var colorpickerLeft = offset.left;
		var colorpickerTop = offset.top - colorpickerHeight + $(this).outerHeight();
										
		//If the colorpicker is bleeding to the right of the window, flip it to the left
		if ( (offset.left + colorpickerWidth) > $(window).width() ) {
			
			//6 pixels at end is just for a precise adjustment.  Color picker width and color picker box outer width don't get it to the precise position.
			var colorpickerLeft = offset.left - colorpickerWidth + $(this).outerWidth() + 6;
			
		}
		
		/* Keep the design editor options container from scrolling */
		$('div.design-editor-options-container').css('overflow-y', 'hidden');

		//If the colorpicker exists, just show it
		if ( $(this).data('colorpickerId') ) {
			
			var colorpicker = $('div#' + $(this).data('colorpickerId'));
														
			$(this).colorPickerShow();
			
			//Put the CSS after showing so it actually applies
			colorpicker.css({
				top: colorpickerTop + 'px',
				left: colorpickerLeft + 'px '
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
				designEditorUpdateInputHidden(input, color.replace('#', ''));

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
				designEditorUpdateInputHidden(input, color.replace('#', ''));

				//Hide the colorpicker
				$(el).colorPickerHide();
				
				/* Allow design editor options container to scroll again */
				$('div.design-editor-options-container').css('overflow-y', 'auto');

				allowSaving();	

			},
			onBeforeShow: function() {	

				//this refers to colorpicker box
				var input = $(this).siblings('input');

				$(this).colorPickerSetColor(input.val());

			},
			onHide: function() {
				
				/* Allow design editor options container to scroll again */
				$('div.design-editor-options-container').css('overflow-y', 'auto');
				
			}
		});

		return $(this).colorPickerShow();
		
	}
/* END INPUT FUNCTIONALITY */


/* DESIGN EDITOR SAVING */
	designEditorUpdateInputHidden = function(input, value) {

		var input = $(input);
		
		/* If it's an uncustomized property and the user somehow tabs to the input, DO NOT send the stuff to the DB. */
		if ( input.parents('li.uncustomized-property').length == 1 )
			return false;
		
		/* Get all vars */
		var element = input.attr('element').toLowerCase();
		var property = input.attr('property').toLowerCase();
		var selector = input.attr('element_selector') || false;
		var specialElementType = input.attr('special_element_type').toLowerCase() || false;
		var specialElementMeta = input.attr('special_element_meta').toLowerCase() || false;

		/* Build name and ID */
		var hiddenInputID = 'input-' + element + '-' + property;
		var hiddenInputName = 'design-editor[' + element + ']';
		
		/* Add layout, instance, or state to the name/ID.  Otherwise just say that it's a default element type */
		if ( specialElementType != false && specialElementMeta != false ) {
			hiddenInputID = hiddenInputID + '-' + specialElementType + '_' + specialElementMeta;
			hiddenInputName = hiddenInputName + '[' + specialElementType + '][' + specialElementMeta + ']';
		} else {
			hiddenInputName = hiddenInputName + '[regular][]';				
		}
		
		/* Add the property to the end of the property input name */
		hiddenInputName = hiddenInputName + '[' + property + ']';
		
		/* Finish by adding '-hidden' to the ID */
		hiddenInputID = hiddenInputID + '-hidden';
		
		/* Create input if it doesn't exist—otherwise, update it. */
		if ( $('input#' + hiddenInputID, 'div#visual-editor-hidden-inputs').length === 0 ) {

			var hiddenInput = $('<input type="hidden" />');
			
			hiddenInput.attr({
				id: hiddenInputID,
				name: hiddenInputName,
				element: element,
				property: property,
				specialElementType: specialElementType,
				specialElementMeta: specialElementMeta
			});		

			/* Finish setting up input */
			hiddenInput
				.val(value)
				.appendTo('div#visual-editor-hidden-inputs');

		} else {

			$('input#' + hiddenInputID, 'div#visual-editor-hidden-inputs').val(value);

		}

	}
/* END DESIGN EDITOR SAVING */


/* COMPLEX JS CALLBACKS */
	propertyInputCallbackFontFamily = function(selector, value) {
		
		$.post(Headway.ajaxURL, {
			action: 'headway_visual_editor',
			method: 'get_font_stack',
			security: Headway.security,
			font: value
		}, function(response) {
			
			if ( typeof response != 'undefined' && response != false ) {
				var fontStack = response;
			} else {
				var fontStack = value;
			}
			
			stylesheet.update_rule(selector, {"font-family": fontStack});
		
		});
		
	}

	propertyInputCallbackBackgroundImage = function(selector, params, value) {
		
		if ( params.method === 'add' ) {
			
			stylesheet.update_rule(selector, {"background-image": 'url(' + value + ')'});
			
		} else if ( params.method === 'delete' ) {
			
			stylesheet.update_rule(selector, {"background-image": null});
			
		}
		
	}

	propertyInputCallbackFontStyling = function(selector, value) {
		
		if ( value === 'normal' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'normal',
				'font-weight': 'normal'
			});
			
		} else if ( value === 'bold' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'normal',
				'font-weight': 'bold'
			});
			
		} else if ( value === 'italic' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'italic',
				'font-weight': 'normal'
			});
			
		} else if ( value === 'bold-italic' ) {
			
			stylesheet.update_rule(selector, {
				'font-style': 'italic',
				'font-weight': 'bold'
			});
			
		}
		
	}

	propertyInputCallbackCapitalization = function(selector, value) {
		
		if ( value === 'none' ) {
			
			stylesheet.update_rule(selector, {
				'text-transform': 'none',
				'font-variant': 'normal'
			});
			
		} else if ( value === 'small-caps' ) {
			
			stylesheet.update_rule(selector, {
				'text-transform': 'none',
				'font-variant': 'small-caps'
			});
			
		} else {
			
			stylesheet.update_rule(selector, {
				'text-transform': value,
				'font-variant': 'normal'
			});
			
		}
		
	}

	propertyInputCallbackShadow = function(selector, property_id, value) {
		
		var shadowType = ( property_id.indexOf('box-shadow') === 0 ) ? 'box-shadow' : 'text-shadow';
											
		var currentShadow = $i(selector).css(shadowType) || false;
								
		//If the current shadow isn't set, then create an empty template to work off of.
		if ( currentShadow == false || currentShadow == 'none' )
			currentShadow = 'rgba(0, 0, 0, 0) 0 0 0';
		
		//Remove all spaces inside rgba, rgb, and hsb colors and also remove all px
		var shadowFragments = currentShadow.replace(/, /g, ',').replace(/px/g, '').split(' ');
		
		var shadowColor = shadowFragments[0];
		var shadowHOffset = shadowFragments[1];
		var shadowVOffset = shadowFragments[2];
		var shadowBlur = shadowFragments[3];
		var shadowInset = ( typeof shadowFragments[4] != 'undefined' && shadowFragments[4] == 'inset' ) ? 'inset' : '';
		
		switch ( property_id ) {
			
			case shadowType + '-horizontal-offset':
				shadowHOffset = value;
			break;
			
			case shadowType + '-vertical-offset':
				shadowVOffset = value;
			break;
			
			case shadowType + '-blur':
				shadowBlur = value;
			break;
			
			case shadowType + '-inset':
				shadowInset = value;
			break;
			
			case shadowType + '-color':
				shadowColor = value;
			break;
			
		}
		
		var shadow = shadowColor + ' ' + shadowHOffset + 'px ' + shadowVOffset + 'px ' + shadowBlur + 'px' + shadowInset;
					
		var properties = {};
		
		//Use this syntax so the shadow type can feed from variable.
		properties[shadowType] = shadow;
					
		stylesheet.update_rule(selector, properties);
		
	}
/* END COMPLEX JS CALLBACKS */


/* INSPECTOR */
	addInspector = function() {

		/* Get the elements and set up bindings */
		$.post(Headway.ajaxURL, {
			action: 'headway_visual_editor',
			method: 'get_inspector_elements',
			security: Headway.security,
			layout: Headway.currentLayout,
		}, function(elements) {

			$.each(elements.elements, function(index, value) {

				if ( value['group'] == 'blocks' && !parent )
					return;

				$i(value['selector']).data({
					inspectorElementOptions: value
				});

				$i(value['selector']).addClass('inspector-element');

			});

			$.each(elements.instances, function(index, value) {

				$i(value['selector']).data({
					inspectorElementOptions: value
				});

				$i(value['selector']).addClass('inspector-element inspector-element-instance');

			});

		}, 'json');

		/* Build element hover tooltip */
		$i('body').qtip({
			id: 'inspector-tooltip',
			style: {
				classes: 'ui-tooltip-headway'
			},
			position: {
				target: [-9999, -9999],
				my: 'center',
				at: 'center',
				container: $i('body'),
				effect: false,
				adjust: {
					x: 35,
					y: 35
				}
			},
			content: {
				text: 'Hover over an element.'
			},
			show: {
				event: false,
				ready: true
			},
			hide: false,
			events: {
				render: function(event, api) {
					
					delete inspectorElement;
					delete inspectorTooltip;
					delete inspectorElementOptions;

					inspectorTooltip = api;

					if ( !$('#toggle-inspector').hasClass('inspector-disabled') ) {
						enableInspector();
					} else {
						disableInspector();
					}

				}
			}
		});

		/* Handle mouse hovering to move and modify the tooltip and highlight the elements */
		inspectorMouseMove = function(event) {

			inspectorElement = $(event.target);

			if ( !inspectorElement.hasClass('inspector-element') )
				inspectorElement = inspectorElement.parents('.inspector-element').first();

			inspectorTooltip.show();

			var inspectorElementOptions = inspectorElement.data('inspectorElementOptions');

			$i('.inspector-element-hover').removeClass('inspector-element-hover');
			$i(inspectorElementOptions['selector']).addClass('inspector-element-hover');

			var tooltipText = inspectorElementOptions['groupName'] + ' &rsaquo; ';

			if ( inspectorElementOptions['parentName'] )
				tooltipText += inspectorElementOptions['parentName'] + ' &rsaquo; ';

			tooltipText += '<strong>' + inspectorElementOptions['name'] + '</strong>';

			inspectorTooltip.set('position.target', [event.pageX, event.pageY]);
			inspectorTooltip.set('content.text', tooltipText);

		}

		/* Allow the elements to be clicked */
		inspectorMouseUp = function(event) {

			if ( typeof inspectorElement == 'undefined' || !inspectorElement )
				return;

			var inspectorElementOptions = inspectorElement.data('inspectorElementOptions');

			/* Open panel and switch to editor panel */
			$('div#panel').tabs('select', 'editor-tab');
			showPanel();

			/* Remove the highlight on the previously selected elements */
			$('.design-editor-element-selector-container .ui-state-active').removeClass('ui-state-active');

			if ( inspectorElementOptions['parent'] ) {

				$('ul#design-editor-main-elements li#element-' + inspectorElementOptions['parent']).addClass('ui-state-active');

				$('ul#design-editor-sub-elements').show();
				$('ul#design-editor-sub-elements li').hide().removeClass('ui-state-active');
				$('ul#design-editor-sub-elements li.parent-element-' + inspectorElementOptions['parent']).show();
				$('ul#design-editor-sub-elements').data('main_element', inspectorElementOptions['parent']);
				$('ul#design-editor-sub-elements').scrollbarPaper();

				/* Open sub element inputs */
				$('ul#design-editor-sub-elements li#element-' + inspectorElementOptions['id']).find('span').trigger('click');

			} else if ( typeof inspectorElementOptions['instance'] == 'undefined' ) {

				$('ul#design-editor-main-elements li#element-' + inspectorElementOptions['id']).find('span').trigger('click');

			} else {

				$('ul#design-editor-main-elements li#element-' + inspectorElementOptions['id']).addClass('ui-state-active');
				
				designEditor.selectInstance(inspectorElementOptions['instance'], inspectorElementOptions['name'], true);

			}

			showPanel();

		}

	}

	toggleInspector = function() {

		if ( $('#toggle-inspector').hasClass('inspector-disabled') )
			return enableInspector();

		disableInspector();

	}

	disableInspector = function() {

		delete disableBlockDimensions;
		delete inspectorElement;

		$i('.inspector-element-hover').removeClass('inspector-element-hover');
		$i('body').removeClass('disable-block-hover').addClass('inspector-disabled'); 
		$i('.block').qtip('enable');

		inspectorTooltip.hide();
		hideTaskNotification();

		$i('body').unbind('mousemove', inspectorMouseMove);
		$i('body').unbind('mouseup', inspectorMouseUp);

		Headway.iframe.contents().unbind('keydown', inspectorNudging);
		Headway.iframe.unbind('keydown', inspectorNudging);

		$('#toggle-inspector').text('Enable Inspector').addClass('inspector-disabled').removeClass('mode-button-depressed');

	}

	enableInspector = function() {

		disableBlockDimensions = true;

		$i('body').addClass('disable-block-hover').removeClass('inspector-disabled'); 
		$i('.block').qtip('disable');

		inspectorTooltip.show();

		$i('body').bind('mousemove', inspectorMouseMove);
		$i('body').bind('mouseup', inspectorMouseUp);

		/* For some reason the iframe doesn't always focus correctly so both of these bindings are needed */
		Headway.iframe.contents().bind('keydown', inspectorNudging);
		Headway.iframe.bind('keydown', inspectorNudging);

		/* Focus iframe on mouseover */
		Headway.iframe.bind('mouseover', function() {
			Headway.iframe.focus();
		});

		showTaskNotification('<strong>Click</strong> Highlighted Elements to Style Them.<br /><br />Once an element is selected, you may nudge it using your arrow keys.', false, false, .8);

		$('#toggle-inspector').text('Disable Inspector').removeClass('inspector-disabled').addClass('mode-button-depressed');

	}

	inspectorNudging = function(event) {

		var key = event.keyCode;

		if ( key < 37 || key > 40 || !$i('.inspector-element-selected').length || $i('.inspector-element-selected').is('body') )
			return;

		var interval = event.shiftKey ? 5 : 1;

		/* Get the selector that way the stylesheet object can be used */
		var methodInput = $('.design-editor-box-nudging .design-editor-property-position select', '#editor-tab');
		var methodInputHidden = methodInput.siblings('input[type="hidden"]');
		var selector = methodInputHidden.attr('element_selector');

		/* Set the 3 nudging properties to customized */
		$('.design-editor-box-nudging .uncustomized-property .customize-property span', '#editor-tab').trigger('click');

		/* Set the nudging method to whatever the position property is of the element as long as it's not static */
		if ( $i('.inspector-element-selected').css('position') != 'static' ) {

			var positionMethod = $i('.inspector-element-selected').css('position');

			$i('.inspector-element-selected').css({
				position: positionMethod	
			});

			methodInput.val(positionMethod).trigger('change');

		} else {

			var positionMethod = 'relative';

			$i('.inspector-element-selected').css({
				position: positionMethod	
			});

			methodInput.val(positionMethod).trigger('change');

		}

		switch ( key ) {

			/* Left */
			case 37:

				var previousLeft = parseInt($i('.inspector-element-selected').css('left'));

				if ( isNaN(previousLeft) )
					var previousLeft = 0;

				stylesheet.update_rule(selector, {"left": (previousLeft - interval) + 'px'});

				var currentLeft = $i('.inspector-element-selected').css('left').replace('px', '');
				$('.design-editor-box-nudging .design-editor-property-left input[type="text"]', '#editor-tab').val(currentLeft).trigger('change');

			break;

			/* Up */
			case 38:

				var previousTop = parseInt($i('.inspector-element-selected').css('top'));

				if ( isNaN(previousTop) )
					previousTop = 0;

				stylesheet.update_rule(selector, {"top": (previousTop - interval) + 'px'});

				var currentTop = $i('.inspector-element-selected').css('top').replace('px', '');
				$('.design-editor-box-nudging .design-editor-property-top input[type="text"]', '#editor-tab').val(currentTop).trigger('change');

			break;

			/* Right */
			case 39:

				var previousLeft = parseInt($i('.inspector-element-selected').css('left'));

				if ( isNaN(previousLeft) )
					var previousLeft = 0;

				stylesheet.update_rule(selector, {"left": (previousLeft + interval) + 'px'});

				var currentLeft = $i('.inspector-element-selected').css('left').replace('px', '');
				$('.design-editor-box-nudging .design-editor-property-left input[type="text"]', '#editor-tab').val(currentLeft).trigger('change');

			break;

			/* Down */
			case 40:

				var previousTop = parseInt($i('.inspector-element-selected').css('top'));

				if ( isNaN(previousTop) )
					previousTop = 0;

				stylesheet.update_rule(selector, {"top": (previousTop + interval) + 'px'});

				var currentTop = $i('.inspector-element-selected').css('top').replace('px', '');
				$('.design-editor-box-nudging .design-editor-property-top input[type="text"]', '#editor-tab').val(currentTop).trigger('change');

			break;

		}

		/* Prevent scrolling */
		event.preventDefault();
		return false;

	}
/* END INSPECTOR */


})(jQuery);