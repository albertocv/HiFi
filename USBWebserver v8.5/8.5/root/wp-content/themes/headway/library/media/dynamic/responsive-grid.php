<?php
class HeadwayResponsiveGridDynamicMedia {
	
	
	static function content() {
		
		$content = self::ipad_landscape();
		$content .= self::ipad_portrait();
		$content .= self::smartphones();
		
		return apply_filters('headway_responsive_grid_css', $content);
		
	}
	
	
	static function ipad_landscape() {

		$grid_width = HeadwayGrid::get_grid_width();
		$wrapper_width = $grid_width + 30; /* 30 is default padding compensation */
		
		$screen_max_width = ( $wrapper_width < 1024 ) ? $wrapper_width : 1024;
		
		return '
			/* --- iPad Landscape (or the wrapper width... whatever is lower) --- */
			@media screen and (max-width: ' . $screen_max_width . 'px) {

				div#whitewrap .block img {
					max-width: 100%;
					height: auto;
				}

				div#whitewrap .block-fixed-height:not(.block-type-navigation) {
					height: auto;
					min-height: 40px;
				}
				
				div#whitewrap .block-type-footer p.footer-responsive-grid-link-container {
					display: block;
				}

				/* Responsive Block Hiding */
				.responsive-block-hiding-device-tablets-landscape {
					display: none !important;
				}

			}
		';
		
	}
	
	
	static function ipad_portrait() {
		
		return '
			/* --- iPad Portrait --- */
			@media screen and (max-width: 880px) {

				/* Responsive Block Hiding */
				.responsive-block-hiding-device-tablets-portrait {
					display: none !important;
				}

			}
		';
		
	}
	
	
	static function smartphones() {
		
		$wrapper_top_margin = HeadwayOption::get('wrapper-top-margin', 'general', 30) . 'px';
		$wrapper_bottom_margin = HeadwayOption::get('wrapper-bottom-margin', 'general', 30) . 'px';
				
		$wrapper_margin = HeadwayOption::get('disable-wrapper-margin-for-smartphones', false, true) ? '0' : $wrapper_top_margin . ' auto ' . $wrapper_bottom_margin;		
				
		return '
			/* --- Smartphones and small Tablet PCs --- */
			@media screen and (max-width: 620px) {
				
				div.wrapper {
					margin: ' . $wrapper_margin . ';
				}

				/* Set all blocks/columns to be 100% width */
				div#whitewrap .block, div#whitewrap .row, div#whitewrap .column {
					width: 100%;
					margin-left: 0;
					margin-right: 0;
				}

				/* Take the minimum height off of fluid blocks. */
				div#whitewrap .block-fluid-height {
					min-height: 40px;
				}

				/* Responsive Block Hiding */
				.responsive-block-hiding-device-smartphones {
					display: none !important;
				}

				/* Navigation Block */
					div#whitewrap .block-type-navigation {
						height: auto;
						min-height: 40px;
					}
				/* End Navigation Block */

				/* Content Block */
					div#whitewrap .block-type-content a.post-thumbnail {
						width: 100%;
						margin: 20px 0;
						text-align: center;
					}

						div#whitewrap .block-type-content a.post-thumbnail img {
							max-width: 90%;
						}
						
					div#whitewrap .block-type-content .loop-navigation {
						text-align: center;
					}
					
						div#whitewrap .block-type-content .loop-navigation .nav-previous, div#whitewrap .block-type-content .loop-navigation .nav-next {
							float: none;
							margin: 0 10px;
						}
						
						div#whitewrap .block-type-content .loop-navigation .nav-next {
							margin-top: 20px;
						}
				/* End Content Block */

				/* Footer Block */
				.block-type-footer div.footer > * {
					clear: both;
					float: none;
					display: block;
					margin: 15px 0;
					text-align: center;
				}
				/* End Footer Block */

			}
		';
		
	}
	
	
	static function fitvids() {
		
		return 'jQuery(document).ready(function() { jQuery(document).fitVids(); });';
		
	}
	
	
}