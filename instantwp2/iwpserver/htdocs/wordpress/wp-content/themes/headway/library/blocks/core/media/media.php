<?php
headway_register_block('HeadwayMediaBlock', headway_url() . '/library/blocks/core/media');

class HeadwayMediaBlock extends HeadwayBlockAPI {
	
	
	public $id = 'media';
	
	public $name = 'Media';
	
	public $core_block = true;
	
	public $options_class = 'HeadwayMediaBlockOptions';
	
	public $fixed_height = true;

	public $description = 'The Media block allows you to embed YouTube, Vimeo, or any other popular oEmbed supported service.  Also, the Media block can act as a simple image rotator.';
	
	
	function init() {
		
		add_filter('oembed_result', array(__CLASS__, 'add_embed_wmode_transparent'));
		add_filter('oembed_result', array(__CLASS__, 'add_iframe_wmode_transparent'));
		
	}
	
	
	function enqueue_action($block_id) {
			
		switch ( parent::get_setting($block_id, 'mode', 'embed') ) {
			
			case 'image-rotator':
			
				$images = parent::get_setting($block_id, 'images', array());

				//If there are no images or only 1 image, do not load cycle.
				if ( count($images) <= 1 )
					return false;

				wp_enqueue_script('headway-media-block-jquery-cycle', headway_url() . '/library/blocks/core/media/js/jquery.cycle.lite.js', array('jquery'));
			
			break;
			
		}
		
	}
	
	
	function dynamic_js($block_id) {
		
		//Make sure the block is set to image rotator mode.
		if ( parent::get_setting($block_id, 'mode', 'embed') != 'image-rotator' )
			return false;
	
		$images = parent::get_setting($block_id, 'images', array());
			
		//If there are no images or only 1 image, do not load cycle.
		if ( count($images) <= 1 )
			return false;
		
			return '
	jQuery(document).ready(function(){ 
		jQuery(\'#block-' . $block_id . ' div.image-rotator\').cycle({
			speed: ' . parent::get_setting($block_id, 'image-rotator-animation-speed', 500) . ',
			timeout: ' . parent::get_setting($block_id, 'image-rotator-timeout', 6) * 1000 . '
		}); 
	});' . "\n";
		
	}

	
	function content($block) {
				
		if ( parent::get_setting($block, 'mode', 'embed') == 'embed' ) {
			
			self::embed($block);
			
		} else {
			
			self::image_rotator($block);
			
		}
		
	}
	
	
	function embed($block) {
								
		if ( $embed_url = parent::get_setting($block, 'embed-url', false) ) {
						
			$block_width = HeadwayBlocksData::get_block_width($block);
			$block_height = HeadwayBlocksData::get_block_height($block);	
						
			$embed_code = wp_oembed_get($embed_url, array(
				'width' => $block_width,
				'height' => $block_height,
			));
			
			//Make the width and height exactly what the block's dimensions are.
			$embed_code = preg_replace(array('/width="\d+"/i', '/height="\d+"/i'), array('width="' . $block_width . '"', 'height="' . $block_height . '"'), $embed_code);
			
			echo $embed_code;
			
		} else {
			
			echo '<div class="alert alert-yellow"><p>There is no content to display.  Please enter a valid embed URL in the visual editor.</p></div>';
			
		}
		
	}
	
	
	function image_rotator($block) {
		
		$images = parent::get_setting($block, 'images', array());
		
		$block_width = HeadwayBlocksData::get_block_width($block);
		$block_height = HeadwayBlocksData::get_block_height($block);
				
		if ( count($images) === 0 ) {
			echo '<div class="alert alert-yellow"><p>There are no images to display.</p></div>';
			
			return;
		}
		
		echo '<div class="image-rotator">';
			
			foreach ( $images as $image ) {
				
				if ( parent::get_setting($block, 'crop-resize-images', true) )
					echo '<img src="' . headway_resize_image($image, $block_width, $block_height) . '" style="width:' . $block_width . 'px;height:' . $block_height . 'px;" />';
				else
					echo '<img src="' . $image . '" />';
				
			}
		
		echo '</div>';
		
	}
	
	
	/**
	 * Added to fix the issue of Flash appearing over drop down menus.
	 **/
	function add_embed_wmode_transparent($html) {
				
		//If no <embed> exists, don't do anything.
		if ( strpos($html, '<embed ') === false )
			return $html;

		return str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);	
	
	}
	
	
	/**
	 * If the oEmbed HTML is using an iframe instead of <embed>, add a query var to the URL of the iframe to tell it to use wmode=transparent. 
	 **/
	function add_iframe_wmode_transparent($html) {
		
		//If no iframe exists, don't do anything.
		if ( strpos($html, '<iframe') === false )
			return $html;
			
		$url_search = preg_match_all('/src=[\'\"](.*?)[\'\"]/', $html, $url);		
		$url = $url[1][0];
		
		//Add the query var
		$url = add_query_arg(array('wmode' => 'transparent'), $url);
		
		//Place the URL back in
		return preg_replace('/src=[\'\"](.*?)[\'\"]/', 'src="' . $url . '"', $html);
		
	}

	
}


class HeadwayMediaBlockOptions extends HeadwayBlockOptionsAPI {
	
	public $tabs = array(
		'mode' => 'Mode',
		'embed-options' => 'Embed Options',
		'image-rotator-options' => 'Image Rotator Options'
	);
	
	public $tab_notices = array(
		'mode' => 'With the Media block, you can either create an image rotator or embed any video (or Flickr slideshow) by providing only the URL.  In the option below, choose whether you would like this block to be an image rotator or embed container.  Then, go to the content tab to the left and add the content depending on which mode you have this block set to.'
	);

	public $inputs = array(
		'mode' => array(
			'mode' => array(
				'type' => 'select',
				'name' => 'mode',
				'label' => 'Mode',
				'options' => array(
					'embed' => 'Embed',
					'image-rotator' => 'Image Rotator'
				)
			)
		),

		'embed-options' => array(
			'embed-url' => array(
				'type' => 'text',
				'name' => 'embed-url',
				'label' => 'Embed URL',
				'default' => null,
				'tooltip' => 'Enter the URL <strong>(NO HTML)</strong> to the media you wish to embed.  We support most major video and photo sites including (but not limited to) YouTube, Vimeo, Flickr, blip.tv, Hulu, and more.  <strong>Please note:</strong> the Mode option in the Mode tab must be set to <em>Embed</em> to use this option.'
			)
		),
		
		'image-rotator-options' => array(
			'images' => array(
				'type' => 'multi-image',
				'name' => 'images',
				'label' => 'Images',
				'tooltip' => 'Upload the images that you would like to add to the image rotator here.  You can even drag and drop the images to change the order.  <strong>Please note:</strong> the Mode option in the Mode tab must be set to <em>Image Rotator</em> to use this option.'
			),
			
			'image-rotator-animation-speed' => array(
				'type' => 'slider',
				'name' => 'image-rotator-animation-speed',
				'label' => 'Animation Speed',
				'default' => 500,
				'slider-min' => 50,
				'slider-max' => 5000,
				'slider-interval' => 10,
				'tooltip' => 'Adjust this to change how long the animation lasts when fading between images.',
				'unit' => 'ms'
			),
			
			'image-rotator-timeout' => array(
				'type' => 'slider',
				'name' => 'image-rotator-timeout',
				'label' => 'Pause',
				'default' => 6,
				'slider-min' => 1,
				'slider-max' => 20,
				'slider-interval' => 1,
				'tooltip' => 'This is the amount of time each image will stay visible.',
				'unit' => 's'
			),
			
			'crop-resize-images' => array(
				'type' => 'checkbox',
				'name' => 'crop-resize-images',
				'label' => 'Crop and Resize Images',
				'default' => true,
				'tooltip' => 'The Media block has the ability to automatically resize and crop images to fit in the image rotator if the images are not the correct size.  If you do not want the Media block to do this, uncheck this option and the Media block will insert your original uploaded images into the rotator.'
			)
		)
	);
	
}