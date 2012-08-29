jQuery(document).ready(function(){ 
		jQuery("#block-2").find("ul.menu").superfish({
			delay: 200,
			animation: {opacity:"show"},
			speed: 'fast',
			onBeforeShow: function() {
				var parent = jQuery(this).parent();
				
				var subMenuParentLink = jQuery(this).siblings('a');
				var subMenuParents = jQuery(this).parents('.sub-menu');

				if ( subMenuParents.length > 0 || jQuery(this).parents('.nav-vertical').length > 0 ) {
					jQuery(this).css('marginLeft',  parent.outerWidth());
					jQuery(this).css('marginTop',  -subMenuParentLink.outerHeight());
				}
			}
		});		
});



