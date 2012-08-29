<?php
/**
 * Regular functions to be used throughout Headway.  This file has absolutely no organizational pattern.
 **/


/**
 * Returns the Headway License Key from the options or constant if define.
 **/
function headway_get_license_key() {

	if ( defined('HEADWAY_LICENSE_KEY') )
		return apply_filters('headway_license_key', trim(HEADWAY_LICENSE_KEY));

	return apply_filters('headway_license_key', trim(HeadwayOption::get('license-key')));

}


function headway_validate_license_key() {

	global $wp_version;

	if ( !headway_get_license_key() )
		return array('error' => 'Please provide a license key.');

	$request_parameters = array(
		'timeout' => 5,
		'body' => array(
			'wp_version' => $wp_version,
			'php_version' => phpversion(),
			'headway_version' => HEADWAY_VERSION,
			'home_url' => home_url(),
			'license_key' => headway_get_license_key()
		)
	);

	$request = wp_remote_post(HEADWAY_LICENSE_VALIDATION_URL, $request_parameters);
	$license_validation = wp_remote_retrieve_body($request);

	if ( is_serialized($license_validation) )
		return maybe_unserialize($license_validation);

	return false;

}


/**
 * Simple alias for get_template_directory_uri()
 * 
 * @uses get_template_directory_uri()
 **/
function headway_url() {

	return apply_filters('headway_url', get_template_directory_uri());

}


/**
 * @todo Document
 **/
 function headway_cache_url() {

 	$uploads = wp_upload_dir();

 	return apply_filters('headway_cache_url', headway_format_url_ssl($uploads['baseurl'] . '/headway/cache'));		

 }


/**
 * Starts the GZIP output buffer.
 *
 * @return bool
 **/
function headway_gzip() {

	//If zlib is not loaded, we can't gzip.
	if ( !extension_loaded('zlib') )
		return false;

	//If zlib.output_compression is on, do not gzip
	if ( ini_get('zlib.output_compression') == 1 )
		return false;

	//Allow headway_gzip filter to cancel gzip compression.
	if ( !$gzip = apply_filters('headway_gzip', HeadwayOption::get('enable-gzip', false, true)) )
		return false;

	//If gzip has already happened, then just return.
	if ( in_array('ob_gzhandler', ob_list_handlers()) )
		return;

	ob_start('ob_gzhandler');
	
	return true;

}


/**
 * A simple function to retrieve a key/value pair from the $_GET array or any other user-specified array.  This will automatically return false if the key is not set.
 * 
 * @param string Key to retrieve
 * @param array Optional array to retrieve from.  Default is $_GET
 * 
 * @return mixed
 **/
function headway_get($name, $array = false, $default = null) {
	
	if ( $array === false )
		$array = $_GET;
	
	if ( is_array($array) && isset($array[$name]) )
		return $array[$name];
	elseif ( is_object($array) && isset($array->$name) )
		return $array->$name;
		
	return $default;	
		
}


/**
 * Extension of headway_get().  Use this to fetch a key/value pair from the $_POST array.
 * 
 * @uses headway_get()
 * 
 * @param string Key to retrieve
 * 
 * @return mixed
 **/
function headway_post($name, $default = null) {
	
	return headway_get($name, $_POST, $default);
	
}


/**
 * @todo Document
 **/
function headway_format_url_ssl($url) {
	
	if ( !is_ssl() )
		return $url;
		
	return str_replace('http://', 'https://', $url);
	
}


/**
 * Retrieves the current URL.
 *
 * @return string
 **/
function headway_get_current_url() {

	$prefix = headway_get('HTTPS', $_SERVER) != 'on' ? 'http://' : 'https://';
	$http_host = !isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_X_FORWARDED_HOST'];

	return $prefix . $http_host . $_SERVER['REQUEST_URI'];

}


/**
 * @todo Document
 **/
function headway_change_to_unix_path($path) {
	
	return str_replace('\\', '/', $path);
	
}


/**
 * @todo Document
 **/
function headway_fix_data_type($data) {
	
	if ( is_numeric($data) ) {
		
		return (int)$data;
		
	} elseif ( $data === 'true' ) {
		
	 	return true;
		
	} elseif ( $data === 'false' ) {
		
	 	return false;
		
	} elseif ( $data === '' ) {
		
		return null;
		
	} else {

		$data = maybe_unserialize($data);
		
		if ( !is_array($data) ) {
			return stripslashes($data);
			
		//If it's an array, run this function across all of the nodes in the array.
		} else {
			
			return array_map('maybe_unserialize', $data);
			
		}
		
	}
	
}


/**
 * Generates the URL for the image resizer.
 * 
 * @param string $url URL to original image.
 * @param int $w Width to resize to.
 * @param int $h Height to resize to.
 * @param int $zc Determines whether or not to zoom/crop the image.
 * @uses wp_upload_dir()
 * @uses image_resize_dimensions()
 * @uses image_resize()
 *
 * @return string|array The URL to the image.
 *
 * Title		: Aqua Resizer
 * Description	: Resizes WordPress images on the fly
 * Version	: 1.1
 * Author	: Syamil MJ
 * Author URI	: http://aquagraphite.com
 * License	: WTFPL - http://sam.zoy.org/wtfpl/
 * Documentation	: https://github.com/sy4mil/Aqua-Resizer/
 **/
function headway_thumbnail() {
	_deprecated_function(__FUNCTION__, '3.1.3', 'headway_resize_image()');
	$args = func_get_args();
	return call_user_func_array('headway_resize_image', $args);
}

function headway_resize_image($url, $width, $height = null, $crop = true, $array = null) {

	if ( apply_filters('headway_disable_resize_image', false) === true )
		return apply_filters('headway_resize_image_url', $url);
		
	//validate inputs
	if ( !$url || !$width ) 
		return false;
	
	//define upload path & dir
	$upload_info = wp_upload_dir();
	$upload_dir = $upload_info['basedir'];
	$upload_url = $upload_info['baseurl'];

	//SSL friendliness
	if ( is_ssl() ) {
		
		$url = preg_replace('/^http:\/\//', 'https://', $url);
		$upload_url = preg_replace('/^http:\/\//', 'https://', $upload_url);
		
	}
	
	//check if $img_url is local
	if ( strpos($url, home_url()) === false )
		return false;
	
	//define path of image
	$rel_path = str_replace($upload_url, '', $url);
	$img_path = $upload_dir . $rel_path;
	
	//check if img path exists, and is an image indeed
	if( !file_exists($img_path) OR !getimagesize($img_path) ) 
		return false;
	
	//get image info
	$info = pathinfo($img_path);
	$ext = $info['extension'];
	list($orig_w,$orig_h) = getimagesize($img_path);
	
	//get image size after cropping
	$dims = image_resize_dimensions($orig_w, $orig_h, $width, $height, $crop);
	$dst_w = $dims[4];
	$dst_h = $dims[5];
	
	//use this to check if cropped image already exists, so we can return that instead
	$suffix = "{$dst_w}x{$dst_h}";
	$dst_rel_path = str_replace( '.'.$ext, '', $rel_path);
	$destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";
	
	//if orig size is smaller
	if ( $width >= $orig_w ) {
		
		if ( !$dst_h ) {

			//can't resize, so return original url
			$img_url = $url;
			$dst_w = $orig_w;
			$dst_h = $orig_h;
			
		} else { 

			//check if cropped image already exists, so we can return that instead
			$destfilename = "{$upload_dir}{$dst_rel_path}-{$suffix}.{$ext}";
			
			if ( file_exists($destfilename) ) {
				$img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
			} else {
				//else resize and return the new resized image url
				$resized_img_path = image_resize( $img_path, $width, $height, $crop );
				$resized_rel_path = str_replace( $upload_dir, '', $resized_img_path);
				$img_url = $upload_url . $resized_rel_path;
			}
			
		}
	
	//else check if cache exists	
	} elseif ( file_exists($destfilename) ) {
		$img_url = "{$upload_url}{$dst_rel_path}-{$suffix}.{$ext}";
	
	//else, we resize the image and return the new resized image url
	} else {
		$resized_img_path = image_resize( $img_path, $width, $height, $crop );
		$resized_rel_path = str_replace( $upload_dir, '', $resized_img_path);
		$img_url = $upload_url . $resized_rel_path;
	}
	
	//return the output
	if ( $array ) {

		//array return
		return apply_filters('headway_resize_image_url', array(
			'url' => $img_url,
			'width' => $dst_w,
			'height' => $dst_h
		));
		
	} else {

		//str return
		return apply_filters('headway_resize_image_url', $img_url);

	}
		
}

	
/**
 * Detects if the browser is Internet Explorer.  Will also check if a specific version of MSIE.
 * 
 * @param int $version
 *
 * @return bool
 **/
function headway_is_ie($version_check = false) {
	
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	preg_match('/MSIE\s([\d.]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
	
	if ( count($matches) === 0 || !is_array($matches) )
		return false;

	/* The user agent has a version with a decimal in it so it needs to be changed to an integer so it's 9 rather than 9.0 */
	$version = (int)$matches[1];

	if ( $version_check !== false )
		return $version_check == $version;
		
	return $version;
	
}


/**
 * Parses PHP using eval.
 *
 * @param string $content PHP to be parsed.
 * 
 * @return mixed PHP that has been parsed.
 **/
function headway_parse_php($content) {
	
	/* If Headway PHP parsing is disabled, then return the content now. */
	if ( defined('HEADWAY_DISABLE_PHP_PARSING') && HEADWAY_DISABLE_PHP_PARSING === true )
		return $content;
	
	/* If it's a WordPress Network setup and the current site being viewed isn't the main site, 
	   then don't parse unless HEADWAY_ALLOW_NETWORK_PHP_PARSING is true. */
	if ( !is_main_site() && (!defined('HEADWAY_ALLOW_NETWORK_PHP_PARSING') || HEADWAY_ALLOW_NETWORK_PHP_PARSING === false) )
		return $content;
	
	ob_start();
	eval("?>$content<?php ");
	$parsed = ob_get_contents();
	ob_end_clean();
	return $parsed;
	
}


function wp_enqueue_multiple_scripts($scripts, $in_footer = true) {
	
	if ( !is_array($scripts) || count($scripts) === 0 )
		return false;
	
	foreach ($scripts as $script => $src) {

		if ( is_string($script) && is_string($src) ) {
			wp_enqueue_script($script, $src, false, false, $in_footer);
		} else {
			wp_enqueue_script($src, false, false, false, $in_footer);
		}

	}
	
}


function wp_enqueue_multiple_styles($styles) {
	
	if ( !is_array($styles) || count($styles) === 0 )
		return false;
	
	foreach ($styles as $style => $src) {

		if ( is_string($style) && is_string($src) ) {
			wp_enqueue_style($style, $src);
		} else {
			wp_enqueue_style($src);
		}

	}
	
}


function headway_in_numeric_range($check, $begin, $end, $allow_equals = true) {

	if ( $allow_equals && ($begin <= $check && $check <= $end) )
		return true;

	if ( !$allow_equals && ($begin < $check && $check < $end) )
		return true;

	return false;
	
}


function headway_remove_from_array(array &$array, $value) {
	
	$array = array_diff($array, array($value));
	
	return $array;
	
}


function headway_array_insert(array &$array, array $insert, $position) {
	
	settype($position, 'int');
 
	//if pos is start, just merge them
	if ( $position === 0 ) {
		
	    $array = array_merge($insert, $array);
	
	} else {
		
	    //if pos is end just merge them
	    if( $position >= (count($array)-1) ) {
		
	        $array = array_merge($array, $insert);
	
	    } else {
		
	        //split into head and tail, then merge head+inserted bit+tail
	        $head = array_slice($array, 0, $position);
	        $tail = array_slice($array, $position);
	        $array = array_merge($head, $insert, $tail);
	
	    }
	
	}
	
	return $array;
	
}


function headway_array_key_neighbors($array, $findKey, $valueOnly = true) {
	
	if ( ! array_key_exists($findKey, $array))
		return FALSE;

	$select = $previous = $next = NULL;

	foreach($array as $key => $value) {
		
		$thisValue = $valueOnly ? $value : array($key => $value);
		
		if ($key === $findKey) {
			$select = $thisValue;
			continue;
		}
		
		if ($select !== NULL) {
			$next = $thisValue;
			break;
		}
		
		$previous = $thisValue;

	}

	return array(
		'prev' => $previous,
		'current' => $select,
		'next' => $next
	);

}


/**
 * http://www.php.net/manual/en/function.array-merge-recursive.php#104145
 **/
function headway_array_merge_recursive_simple() {

    if ( func_num_args() < 2 ) {
	
        trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);

        return;

    }

    $arrays = func_get_args();
    $merged = array();

    while ( $arrays ) {
	
        $array = array_shift($arrays);

        if ( !is_array($array) ) {
	
            trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
            return;

        }

        if (!$array)
            continue;

        foreach ($array as $key => $value) {
	
            if (is_string($key)) {
	
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
                else
                    $merged[$key] = $value;

           } else {
	
                $merged[] = $value;

			}

		}

    }

    return $merged;

}


function headway_format_color_hex($color, $pound_sign = true) {

	/* Start by removing any pound sign that exists */
	$color = str_replace('#', '', $color);

	/* If the value is transparent, then just return right away, no need to add pound sign */
	if ( $color == 'transparent' )
		return 'transparent';

	/* If $pound_sign is false, return just the hex, no pound sign */
	if ( !$pound_sign )
		return $color;

	return '#' . $color;	

}


/**
 * http://www.php.net/manual/en/function.get-browser.php#101125
 **/
function headway_get_browser() {

	$u_agent = $_SERVER['HTTP_USER_AGENT']; 
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version = '';

	/* First get the platform */
	if ( preg_match('/linux/i', $u_agent) )
		$platform = 'linux';
		
	elseif ( preg_match('/macintosh|mac os x/i', $u_agent) )
		$platform = 'mac';
		
	elseif ( preg_match('/windows|win32/i', $u_agent) )
		$platform = 'windows';

	/* Next get the name of the useragent yes seperately and for good reason */
	if ( preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent) ) { 
		
		$bname = 'Internet Explorer'; 
		$ub = 'MSIE'; 
	
	} elseif ( preg_match('/Firefox/i', $u_agent) ) { 
		
		$bname = 'Mozilla Firefox'; 
		$ub = 'Firefox'; 
		
	} elseif ( preg_match('/Chrome/i', $u_agent) ) { 
		
		$bname = 'Google Chrome'; 
		$ub = 'Chrome'; 
		
	} elseif ( preg_match('/Safari/i', $u_agent) ) { 
		
		$bname = 'Apple Safari'; 
		$ub = 'Safari'; 
		
	} elseif ( preg_match('/Opera/i', $u_agent) ) { 
		
		$bname = 'Opera'; 
		$ub = 'Opera'; 
		
	} elseif ( preg_match('/Netscape/i', $u_agent) ) {
		 
		$bname = 'Netscape'; 
		$ub = 'Netscape'; 
		
	} 

	/* Get the correct version number */
	$known = array('Version', $ub, 'other');
	$pattern = '#(?P<browser>' . join('|', $known) . ')[/ ]+(?P<version>[0-9.|a-zA-Z.]*)#';
	
	if  ( !preg_match_all($pattern, $u_agent, $matches) ) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	
	if ($i != 1) {
		
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if ( strripos($u_agent, 'Version') < strripos($u_agent, $ub) )
		   $version = $matches['version'][0];
		else
		   $version = $matches['version'][1];
		
	} else {
		$version = $matches['version'][0];
	}

	//Check if we have a number
	if ( $version == null || $version == '' )
		$version = '?';

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'    => $pattern
	);
	
}