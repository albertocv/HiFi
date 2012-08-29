// $Id$
/**
* @author Henri MEDOT
* @version last revision 2009-12-01
*/
 (function($) {
    $.fn.extend({
        scrollbarPaper: function() {
            this.each(function(i) {
							
            	if ( (navigator.userAgent.indexOf('Mac OS X 10_7') !== -1 || navigator.userAgent.indexOf('Mac OS X 10_8') !== -1) && navigator.userAgent.indexOf('WebKit') !== -1  )
					return false;
							
                var $this = $(this);
                var paper = $this.data('paper');

                if (paper == null) {

                    $this.before('\
						<div class="scrollbarpaper-container">\
							<div class="scrollbarpaper-track">\
								<div class="scrollbarpaper-drag">\
									<div class="scrollbarpaper-drag-top"></div>\
									<div class="scrollbarpaper-drag-bottom"></div>\
								</div>\
							</div>\
						</div>\
					');

                    paper = $this.prev();

					if ( $(this).attr('id') )
						paper.attr('id', 'scrollbarpaper-' + $(this).attr('id'));
						
					if ( $(this).attr('class') && $(this).attr('class').split(' ')[0] )
						paper.addClass('scrollbarpaper-container-' + $(this).attr('class').split(' ')[0]);

                    $this.data('paper', paper);
                    $this.data('track', $('.scrollbarpaper-track', paper));
                    $this.data('drag', $('.scrollbarpaper-drag', paper));
                    $this.data('dragTop', $('.scrollbarpaper-drag-top', paper));
                    $this.data('dragBottom', $('.scrollbarpaper-drag-bottom', paper));

                }

                var track = $this.data('track');
                var drag = $this.data('drag');
                var dragTop = $this.data('dragTop');
                var dragBottom = $this.data('dragBottom');

                var contentHeight = $this[0].scrollHeight;
								
                $this.data('height', $this.outerHeight());
                $this.data('contentHeight', contentHeight);
                $this.data('offset', $this.offset());

                $this.unbind();
                var ratio = $this.outerHeight() / contentHeight;

                paper.height($this.outerHeight());

                if (ratio < 1) {
					
					paper.show();
                    drag.show();
                    $this.addClass('scrollbarpaper-visible');
                    paper.height($this.outerHeight());
                    var offset = $this.offset();

                    var dragHeight = Math.max(Math.round($this.outerHeight() * ratio), dragTop.height() + dragBottom.height());
                    drag.height(dragHeight - 10);

                    var updateDragTop = function() {
                        drag.css('top', Math.min(Math.round($this.scrollTop() * ratio), $this.outerHeight() - dragHeight) + 'px');
                    };
                    updateDragTop();

                    $this.scroll(function(event) {
                        updateDragTop();
                    });

                    var unbindMousemove = function() {
                        $('html').unbind('mousemove.scrollbarpaper');
                    };
                    drag.mousedown(function(event) {
                        unbindMousemove();
                        var offsetTop = event.pageY - drag.offset().top;
                        $('html').bind('mousemove.scrollbarpaper',
                        function(event) {
                            $this.scrollTop((event.pageY - $this.offset().top - offsetTop) / ratio);
                            return false;
                        }).mouseup(unbindMousemove);
                        return false;
                    });

                } else {
	
                    $this.unbind();
					paper.hide();
                    drag.hide();
                    $this.removeClass('scrollbarpaper-visible');

                }

                var setScrollbarPaperTimeout = function() {
                    window.setTimeout(function() {
                        var offset = $this.offset();
                        var dataOffset = $this.data('offset');

                        if (
							($this.outerHeight() != $this.data('height')) 
							|| ($this[0].scrollHeight != $this.data('contentHeight'))
							|| (offset.top != dataOffset.top)
							|| (offset.left != dataOffset.left)
						) {
                            $this.scrollbarPaper();
                        }
                        else {
                            setScrollbarPaperTimeout();
                        }
                    },
                    200);
                };

                setScrollbarPaperTimeout();

            }); //End the each

			return this;
        }
    });

})(jQuery);