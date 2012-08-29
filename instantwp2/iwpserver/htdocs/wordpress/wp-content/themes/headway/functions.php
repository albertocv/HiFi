<?php
/**
 * Starts Headway
 *
 * @package Headway
 * @author Clay Griffiths
 */

/* Prevent direct access to this file */
if ( !defined('WP_CONTENT_DIR') )
	die('Please do not access this file directly.');

/* Make sure PHP 5.2 or newer is installed and WordPress 3.2 or newer is installed. */
require_once get_template_directory() . '/library/common/compatibility-checks.php';

/* Load Headway! */
require_once get_template_directory() . '/library/common/functions.php';
require_once get_template_directory() . '/library/common/application.php';

Headway::init();