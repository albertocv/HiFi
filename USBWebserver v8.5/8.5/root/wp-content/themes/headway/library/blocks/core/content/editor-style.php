<?php
function headway_content_block_editor_style() {
	
	$body_bg = HeadwayElementsData::get_property('block-content-entry-container', 'background-color', 'ffffff');	
	$body_color = HeadwayElementsData::get_property('block-content-entry-content', 'color', '333333');
	$body_font_family = HeadwayElementsData::get_property('block-content-entry-content', 'font-family', 'helvetica, sans-serif');
	$body_font_size = HeadwayElementsData::get_property('block-content-entry-content', 'font-size', '13');
	$body_line_height = HeadwayElementsData::get_property('block-content-entry-content', 'line-height', '180');

	if ( !($body_hyperlink_color = HeadwayElementsData::get_property('block-content-entry-content-hyperlinks', 'color', null)) )
		$body_hyperlink_color = $body_color;

	return '
		* {
			font-size: ' . $body_font_size . 'px;
			font-family: ' . $body_font_family . ';
			font-style: inherit;
			font-weight: inherit;
			line-height: ' . $body_line_height . '%;
			color: inherit;
		}
		body {
			background: #' . $body_bg . ';
			color: #' . $body_color . ';
			font-size: ' . $body_font_size . 'px;
			font-family: ' . $body_font_family . ';
			line-height: ' . $body_line_height . '%;
		}

		/* Headings */
		h1,h2,h3,h4,h5,h6 {
			clear: both;
		}
		h1,
		h2 {
			color: #000;
			font-size: 15px;
			font-weight: bold;
			margin: 0 0 20px;
		}
		h3, h4, h5, h6 {
			font-size: 10px;
			letter-spacing: 0.1em;
			line-height: 2.6em;
			text-transform: uppercase;
			margin: 0 0 15px;
		}
		hr {
			background-color: #ccc;
			border: 0;
			height: 1px;
			margin: 0 0 15px;
		}

		/* Text elements */
		p {
			margin: 0 0 15px;
		}
		
		/* Lists */
		ul, ol {
			padding: 0 0 0 40px;
			margin: 15px 0;
		}
		
		ul ul, ol ol { margin: 0; } /* Lists inside lists should not have the margin on them. */	

	    ul li { list-style: disc; }
	    ul ul li { list-style: circle; }
	    ul ul ul li { list-style: square; }
	    
	    ol li { list-style: decimal; }
	    ol ol li { list-style: lower-alpha; }
	    ol ol ol li { list-style: lower-roman; }
		
		strong {
			font-weight: bold;
		}
		cite, em, i {
			font-style: italic;
		}
		cite {
			border: none;
		}
		pre {
			background: #f4f4f4;
			font: 13px "Courier 10 Pitch", Courier, monospace;
			line-height: 1.5;
			margin-bottom: 1.625em;
			padding: 0.75em 1.625em;
		}
		code {
			font: 13px Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace;
		}
		abbr, acronym {
			border-bottom: 1px dotted #666;
			cursor: help;
		}

		/* Links */
		a,
		a em,
		a strong {
			color: #' . $body_hyperlink_color . ';
			text-decoration: underline;
			cursor: pointer;
		}
		a:focus,
		a:active,
		a:hover {
			text-decoration: none;
		}

		/* Alignment */
		.alignleft {
			display: inline;
			float: left;
			margin-right: 1.625em;
		}
		.alignright {
			display: inline;
			float: right;
			margin-left: 1.625em;
		}
		.aligncenter {
			clear: both;
			display: block;
			margin-left: auto;
			margin-right: auto;
		}';
	
}