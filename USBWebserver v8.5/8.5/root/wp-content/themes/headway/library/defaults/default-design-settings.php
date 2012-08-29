<?php
global $headway_default_element_data;

$headway_default_element_data = array(
	/* Defaults */
	'default-text' => array(
		'properties' => array(
			'font-size' => '14',
			'font-family' => 'palatino',
			'line-height' => '100',
			'color' => '555555'
		)
	),

	'default-hyperlink' => array(
		'properties' => array(
			'color' => '555555'
		)
	),

	'default-block' => array(
		'properties' => array(
			'overflow' => 'hidden'
		)
	),
	
	/* Structure */
	'body' => array(
		'properties' => array(
			'background-color' => 'dddddd'
		)
	),
	
	'wrapper' => array(
		'properties' => array(
			'background-color' => 'ffffff',
			'padding-top' => '15',
			'padding-right' => '15',
			'padding-bottom' => '15',
			'padding-left' => '15',
			'box-shadow-color' => 'c7c7c7',
			'box-shadow-blur' => '8',
			'box-shadow-horizontal-offset' => '1',
			'box-shadow-vertical-offset' => '1'
		)
	),
	
	/* Header Block */
	'block-header-site-title' => array(
		'properties' => array(
			'font-family' => 'palatino',
			'color' => '222222',
			'font-size' => '34',
			'line-height' => '100',
			'text-decoration' => 'none'
		)
	),
	
	'block-header-site-tagline' => array(
		'properties' => array(
			'font-family' => 'palatino',
			'color' => '999999',
			'font-size' => '15',
			'line-height' => '120',
			'font-styling' => 'italic'
		)
	),
	
	/* Navigation Block */
	'block-navigation' => array(
		'properties' => array(
			'border-top-width' => '1',
			'border-bottom-width' => '1',
			'border-left-width' => '0',
			'border-right-width' => '0',
			'border-color' => 'eeeeee',
			'border-style' => 'solid',
			'overflow' => 'visible'
		)
	),
	
	'block-navigation-menu-item' => array(
		'properties' => array(
			'text-decoration' => 'none',
			'color' => '888888',
			'capitalization' => 'uppercase',
			'font-family' => 'palatino',
			'padding-right' => '15',
			'padding-left' => '15'
		),
		'special-element-state' => array(
			'selected' => array(
				'color' => '222222'
			),
			'hover' => array(
				'color' => '555555'
			)
		)
	),
	
	/* Widget Block */
	'block-widget-area-widget' => array(
		'properties' => array(
			'line-height' => '150',
			'padding-top' => '5',
			'padding-right' => '10',
			'padding-bottom' => '5',
			'padding-left' => '10'
		)
	),

	'block-widget-area-widget-title' => array(
		'properties' => array(
			'font-size' => '13',
			'border-style' => 'solid',
			'border-top-width' => '1',
			'border-bottom-width' => '1',
			'border-left-width' => '0',
			'border-right-width' => '0',
			'border-color' => 'eeeeee',
			'letter-spacing' => '1',
			'capitalization' => 'uppercase',
			'line-height' => '250',
			'color' => '111111',
			'font-family' => 'palatino'
		)
	),
	
	'block-widget-area-widget-links' => array(
		'properties' => array(
			'color' => '333333'
		)
	),
	
	/* Content Block */
	'block-content-entry-container' => array(
		'properties' => array(
			'border-style' => 'solid',
			'border-top-width' => '0',
			'border-bottom-width' => '1',
			'border-left-width' => '0',
			'border-right-width' => '0',
			'border-color' => 'efefef',
			'padding-bottom' => '30'
		)
	),	
	
	'block-content-title' => array(
		'properties' => array(
			'font-family' => 'palatino',
			'font-size' => '24',
			'color' => '333333',
			'line-height' => '130'
		)
	),
	
	'block-content-archive-title' => array(
		'properties' => array(
			'font-family' => 'palatino',
			'font-size' => '24',
			'color' => '555555',
			'line-height' => '110',
			'border-bottom-width' => '1',
			'border-color' => 'eeeeee',
			'border-style' => 'solid',
			'padding-bottom' => '15'
		)
	),
	
	'block-content-entry-meta' => array(
		'properties' => array(
			'font-style' => 'italic',
			'font-family' => 'palatino',
			'line-height' => '120',
			'color' => '818181'
		)
	),
	
	'block-content-entry-content' => array(
		'properties' => array(
			'color' => '555555',
			'font-family' => 'palatino',
			'font-size' => '14',
			'line-height' => '180'
		)
	),
	
	'block-content-heading' => array(
		'properties' => array(
			'font-size' => '20',
			'line-height' => '180'
		)
	),
	
	'block-content-sub-heading' => array(
		'properties' => array(
			'font-size' => '16',
			'line-height' => '180'
		)
	),
	
	'block-content-more-link' => array(
		'properties' => array(
			'background-color' => 'eeeeee',
			'text-decoration' => 'none',
			'border-top-left-radius' => '4',
			'border-top-right-radius' => '4',
			'border-bottom-right-radius' => '4',
			'border-bottom-left-radius' => '4',
			'padding-top' => '2',
			'padding-right' => '6',
			'padding-bottom' => '2',
			'padding-left' => '6'
		),
		'special-element-state' => array(
			'hover' => array(
				'background-color' => 'e7e7e7'
			)
		)
	),
	
	'block-content-loop-navigation-link' => array(
		'properties' => array(
			'background-color' => 'e1e1e1',
			'text-decoration' => 'none',
			'border-top-left-radius' => '4',
			'border-top-right-radius' => '4',
			'border-bottom-right-radius' => '4',
			'border-bottom-left-radius' => '4',
			'padding-top' => '4',
			'padding-right' => '8',
			'padding-bottom' => '4',
			'padding-left' => '8'
		),
		'special-element-state' => array(
			'hover' => array(
				'background-color' => 'd5d5d5'
			)
		)
	),
	
	'block-content-post-thumbnail' => array(
		'properties' => array(
			'border-top-width' => '1',
			'border-right-width' => '1',
			'border-bottom-width' => '1',
			'border-left-width' => '1',
			'border-color' => 'eeeeee',
			'border-style' => 'solid',
			'padding-top' => '3',
			'padding-right' => '3',
			'padding-bottom' => '3',
			'padding-left' => '3'
		)
	),
	
	'block-content-comments-area-headings' => array(
		'properties' => array(
			'color' => '333333',
			'font-size' => '18',
			'line-height' => '130'
		)
	),

	'block-content-comment-container' => array(
		'properties' => array(
			'padding-left' => '64'
		)
	),
	
	'block-content-comment-author' => array(
		'properties' => array(
			'font-size' => '18',
			'line-height' => '120'
		)
	),

	'block-content-comment-meta' => array(
		'properties' => array(
			'color' => '888888',
			'font-size' => '14'
		)
	),
	
	'block-content-comment-body' => array(
		'properties' => array(
			'font-size' => '14',
			'line-height' => '170'
		)
	),
	
	'block-content-comment-reply-link' => array(
		'properties' => array(
			'font-size' => '12',
			'background-color' => 'eeeeee',
			'text-decoration' => 'none',
			'border-top-left-radius' => '4',
			'border-top-right-radius' => '4',
			'border-bottom-right-radius' => '4',
			'border-bottom-left-radius' => '4',
			'padding-top' => '3',
			'padding-right' => '6',
			'padding-bottom' => '3',
			'padding-left' => '6'
		),
		'special-element-state' => array(
			'hover' => array(
				'background-color' => 'e7e7e7'
			)
		)
	),

	'block-content-comment-form-input-label' => array(
		'properties' => array(
			'font-size' => '14',
			'line-height' => '220',
			'color' => '888888'
		)
	),

	
	/* Footer */
	'block-footer' => array(
		'properties' => array(
			'border-top-width' => '1',
			'border-right-width' => '0',
			'border-bottom-width' => '0',
			'border-left-width' => '0',
			'border-color' => 'eeeeee',
			'border-style' => 'solid'
		)
	),
	
	'block-footer-copyright' => array(
		'properties' => array(
			'color' => '666666'
		)
	),
	
	'block-footer-headway-attribution' => array(
		'properties' => array(
			'color' => '666666'
		)
	),
	
	'block-footer-administration-panel' => array(
		'properties' => array(
			'color' => '666666'
		)
	),
	
	'block-footer-go-to-top' => array(
		'properties' => array(
			'color' => '666666'
		)
	),
	
	'block-footer-responsive-grid-link' => array(
		'properties' => array(
			'color' => '666666'
		)
	),
	
	'block-navigation-sub-nav-menu' => array(
		'properties' => array(
			'background-color' => 'eeeeee'
		)
	)
);