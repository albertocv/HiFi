<?php
headway_register_block('HeadwayTextBlock', headway_url() . '/library/blocks/core/text');

class HeadwayTextBlock extends HeadwayBlockAPI {
	
	
	public $id = 'text';
	
	public $name = 'Text';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayTextBlockOptions';

	public $description = 'Use the built-in rich text editor to insert titles, text, and more!';
	
	
	function content($block) {
		
		$content = parent::get_setting($block, 'content');	
			
		echo '<div class="entry-content">';
			if ( $content != null )
				echo do_shortcode(stripslashes($content));
			else
				echo '<p>There is no content to display.</p>';
		echo '</div><!-- .entry-content -->';
		
	}


	function setup_elements() {
		
		$this->register_block_element(array(
			'id' => 'text',
			'name' => 'Text',
			'selector' => '.entry-content',
			'properties' => array('fonts', 'padding', 'text-shadow'),
			'inherit-location' => 'default-text'
		));

		$this->register_block_element(array(
			'id' => 'hyperlinks',
			'name' => 'Hyperlinks',
			'selector' => '.entry-content a',
			'properties' => array('fonts', 'text-shadow'),
			'inherit-location' => 'default-text',
			'states' => array(
				'Hover' => '.entry-content a:hover', 
				'Clicked' => '.entry-content a:active'
			)
		));
		
		$this->register_block_element(array(
			'id' => 'heading',
			'name' => 'Heading <small>&lt;H3&gt;, &lt;H2&gt;, &lt;H1&gt;</small>',
			'selector' => '.entry-content h3, div.entry-content h2, div.entry-content h1',
			'properties' => array('fonts', 'text-shadow', 'background', 'borders', 'padding', 'rounded-corners', 'box-shadow'),
			'inherit-location' => 'default-heading'
		));
		
		$this->register_block_element(array(
			'id' => 'sub-heading',
			'name' => 'Sub Heading <small>&lt;H4&gt;</small>',
			'selector' => '.entry-content h4',
			'properties' => array('fonts', 'text-shadow', 'background', 'borders', 'padding', 'rounded-corners', 'box-shadow'),
			'inherit-location' => 'default-sub-heading'
		));
		
	}
	
	
}


class HeadwayTextBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'content' => 'Content'
	);

	public $inputs = array(
		'content' => array(
			'content' => array(
				'type' => 'wysiwyg',
				'name' => 'content',
				'label' => 'Content',
				'default' => null
			)
		)
	);
	
}