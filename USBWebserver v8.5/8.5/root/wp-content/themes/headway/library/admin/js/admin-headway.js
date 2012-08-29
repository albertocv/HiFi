(function($) {
$(document).ready(function() {

	/* Big Tabs */
		setupBigTabs = function() {
		
			if ( $('h2.big-tabs-tabs').length === 0 )
				return;
			
			//Bind tab buttons	
			$('h2.big-tabs-tabs a.nav-tab').live('click', function(event){

				var tabID = $(this).attr('href').replace('#tab-', '');
			
				//Stop all other animations
				$('div.big-tabs-container div.big-tab, div.hr-submit, p.submit').stop(true, true);
			
				//Check to make sure tab exists
				if ( $('div.big-tabs-container div#tab-' + tabID + '-content').length === 0 )
					return false;

				//Set tab as active
				$(this).siblings('.nav-tab-active').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
			
				//Hide the submit button so it can be faded in later
				$('div.hr-submit, p.submit').hide();
				
				//Set the container height to something static because things are going to get absolute
				if ( $('div.big-tabs-container div.big-tab-visible').outerHeight() > $('div.big-tabs-container div#tab-' + tabID + '-content').outerHeight() ) {
					var containerHeight = $('div.big-tabs-container div.big-tab-visible').outerHeight();
				} else {
					var containerHeight = $('div.big-tabs-container div#tab-' + tabID + '-content').outerHeight();
				}
				
				$('div.big-tabs-container')
					.height(containerHeight);
				
				//Hide/show the tabs accordingly
				$('div.big-tabs-container div.big-tab-visible')
					.removeClass('big-tab-visible')
					.addClass('big-tab-fading')
					.fadeOut(200, function() {
						
						$(this).removeClass('big-tab-fading');
						
					});
										
				$('div.big-tabs-container div#tab-' + tabID + '-content')
					.addClass('big-tab-visible')
					.addClass('big-tab-fading')
					.fadeIn(200, function() {
						
						$(this).removeClass('big-tab-fading');
						$('div.big-tabs-container').css('height', 'auto');
						
						$('div.hr-submit, p.submit').fadeIn(400);
						
					});
					
			});

			//Setup display for tabs and tab containers
			if ( window.location.hash.indexOf('tab-') !== -1 && $('div.big-tabs-container div#tab-' + window.location.hash.replace('#tab-', '') + '-content').length === 1 ) {
			
				var tabID = window.location.hash.replace('#tab-', '');
				var tab = $('h2.big-tabs-tabs a[href="#tab-' + tabID + '"]');
			
				//Set tab as active
				tab.addClass('nav-tab-active');
			
				//Show tab's container
				$('div.big-tabs-container div#tab-' + tabID + '-content').fadeIn(200, function() {
					$(this).addClass('big-tab-visible');
				});
			
			} else {
			
				var firstTab = $('h2.big-tabs-tabs a.nav-tab:first');
				var tabID = firstTab.attr('href').replace('#tab-', '');
			
				//Set the tab as active
				firstTab.addClass('nav-tab-active');
			
				//Show first tab's container			
				$('div.big-tabs-container div#tab-' + tabID + '-content').fadeIn(200, function() {
					$(this).addClass('big-tab-visible');
				});
			
			}
			
			//Show the tabs
			$('h2.big-tabs-tabs').animate({opacity: 1}, 200);
			
			//Show the submit HR and submit button
			setTimeout(function(){
				$('div.hr-submit, p.submit').fadeIn(200);
			}, 300);
		
		}
	
		//Call the function now
		setupBigTabs();
	/* End Big Tabs */


	/* Tooltips */
		if ( typeof $().qtip === 'function' ) {
			
			$('label span.label-tooltip').qtip({
				style: {
					classes: 'ui-tooltip-headway'
				},
				position: {
					my: 'bottom left',
					at: 'top right'
				}
			});
			
		}
	/* End Tooltips */
	
	
	/* Textareas */
	if ( $('textarea.allow-tabbing').length > 0 )
		$('textarea.allow-tabbing').tabby();
	
	
	/* System Info */
	if ( $('textarea#system-info-textarea').length > 0 ) {
		
		$('textarea#system-info-textarea').qtip({
			style: {
				classes: 'ui-tooltip-headway'
			},
			position: {
				my: 'bottom center',
				at: 'top center'
			}
		});
	
		$('textarea#system-info-textarea').bind('mouseup', function() {
		
			$(this)
				.focus()
				.select();
			
		});
		
	}
	
	
	/* SEO Templates */
		if ( $('div#seo-templates').length === 1 ) {
			
			fetchSEOTemplateValues = function(currentPage) {

				seoInputs.each(function() {

					var value = $('input#seo-' + currentPage + '-' + $(this).attr('id')).val();

					/*
					Since checkboxes and traditional inputs are handled differently we have to either
					set the value of regular inputs or set the checkbox as checked.
					*/
					if ( $(this).attr('type') != 'checkbox' ) {

						$(this).val(value);

					} else {

						if ( value == 1 ) {
							$(this).attr('checked', true);
						} else {
							$(this).attr('checked', false);
						}

					}

				});

			}
			
			/* Set Up Initial Values */
			var currentPage = $('div#seo-templates-header select').val();
			var seoInputs = $('div#seo-templates-inputs input, div#seo-templates-inputs textarea');
			
			fetchSEOTemplateValues(currentPage);
			
			/* Bind the page select */
			$('div#seo-templates-header select').bind('change', function() {
				
				currentPage = $(this).val();
				
				fetchSEOTemplateValues(currentPage);
				
			});
			
			/* Bind the inputs */
			seoInputs.bind('click blur', function() {
			
				var hidden = $('input#seo-' + currentPage + '-' + $(this).attr('id'));
				
				/*
				Since checkboxes and traditional inputs are handled differently we have to either
				set the value of regular inputs or set the checkbox as checked.
				*/
				if ( $(this).attr('type') != 'checkbox' ) {
					
					hidden.val($(this).val());
					
				} else {
					
					if ( $(this).is(':checked') ) {
						hidden.val('1');
					} else {
						hidden.val('0');
					}
					
				}
				
			});
			
			/* Bind the advanced options toggle */
			$('h3#seo-templates-advanced-options-title span').bind('click', function(event) {
				
				if ( !$(this).hasClass('seo-advanced-visible') ) {
					
					$('div#seo-templates-advanced-options').fadeIn(250);
					$(this).html('Hide &uarr;').addClass('seo-advanced-visible');
					
					jQuery.scrollTo($('h3#seo-templates-advanced-options-title'), 500, {
						easing: 'swing',
						offset: {top:-10}
					});
										
				} else {
					
					$('div#seo-templates-advanced-options').fadeOut(200);
					$(this).html('Show &darr;').removeClass('seo-advanced-visible');
					
					jQuery.scrollTo($('div#seo-templates'), 300, {
						easing: 'swing',
						offset: {top:-40}
					});
					
				}
				
			});
			
		}
	/* End SEO Templates */
	
	
	/* Reset */	
		if ( $('form#reset-headway').length === 1 ) {
			
			$('input#reset-headway-submit').bind('click', function() {
			
				return confirm('Warning! ALL existing Headway data, including, but not limited to: Blocks, Design Settings, and Headway Search Engine Optimization settings will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop');
				
			});
			
		}
	/* End Reset */

	
	/* Auto Updater */
		if ( $('body').hasClass('update-core-php') ) {
															
			/* Hide the first update buttons so we don't have to display the backup reminder four times. */
			$('input#upgrade-themes').hide();
			$('input#upgrade-plugins').hide();
			
			/* Change the update buttons to a really big buttons */
			$('input#upgrade-themes-2').addClass('headway-big-button').css({width: 300, height: 35}).before(HeadwayUpdateInfo.backupReminder);
			$('input#upgrade-plugins-2').addClass('headway-big-button').css({width: 300, height: 35}).before(HeadwayUpdateInfo.backupReminder);

		}
	/* End Auto Updater */
	
	
});
})(jQuery);