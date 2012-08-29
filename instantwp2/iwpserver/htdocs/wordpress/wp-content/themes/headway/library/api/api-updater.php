<?php
class HeadwayUpdaterAPI {
	
	/** The slug of the product.  This will be provided to you by the Headway team. **/
	private $slug;

	/** The path to the product.  If a theme, use get_option('stylesheet'), plugins use plugin_basename(__FILE__) **/
	private $path;

	/** Name of product. **/
	private $name;

	/** Either 'theme' or 'block' **/
	private $type;

	/** Current version of theme or plugin. **/
	private $current_version;

	/** Determines whether or not the transient will be modified to allow upgrades. We recommend that child themes are notify only. **/
	private $notify_only;

	/** The URL to the product info **/
	private $product_url;

	/** Transient ID.  This will automatically be set. **/
	private $transient_id;


	public function __construct(array $args) {

		/* Set arguments */
			$this->slug 			= headway_get('slug', $args);
			$this->path 			= headway_get('path', $args);
			$this->name 			= headway_get('name', $args);
			$this->type 			= headway_get('type', $args);
			$this->current_version 	= headway_get('current_version', $args);
			$this->notify_only 		= headway_get('notify_only', $args, null);
			$this->product_url		= headway_get('product_url', $args, 'http://headwaythemes.com');

			if ( $this->notify_only === null && $this->type == 'theme' )
				$this->notify_only = true;

			if ( $this->notify_only === null && $this->type == 'block' )
				$this->notify_only = false;

		/* Make sure all properties are set */
			if ( !$this->slug )
				wp_die('$slug must be set for the HeadwayUpdaterAPI class.');

			if ( !$this->path )
				wp_die('$path must be set for the HeadwayUpdaterAPI class.');

			if ( !$this->name )
				wp_die('$name must be set for the HeadwayUpdaterAPI class.');

			if ( !$this->type )
				wp_die('$type must be set for the HeadwayUpdaterAPI class.');

			if ( !$this->current_version )
				wp_die('$current_version must be set for the HeadwayUpdaterAPI class.');
				
		/* Setup Hooks */
			if ( !is_admin() && !is_super_admin() )
				return;

			$this->transient_id = 'headway-update-check-' . $this->type . '-' . $this->slug;
			$themes_or_plugins = $this->type == 'theme' ? 'themes' : 'plugins';

			add_action('admin_notices', array($this, 'update_notice')); 

			add_action('load-' . $themes_or_plugins . '.php', array($this, 'clear_transient'));
			add_action('load-update-core.php', array($this, 'clear_transient'));

			if ( !$this->notify_only ) {

				add_filter('site_transient_update_' . $themes_or_plugins, array($this, 'intercept_transient'));
				add_filter('transient_update_' . $themes_or_plugins, array($this, 'intercept_transient'));
				
			} else {

				add_action('load-' . $themes_or_plugins . '.php', array($this, 'retrieve_update_info'));
				add_action('load-update-core.php', array($this, 'retrieve_update_info'));

			}

			if ( $this->type != 'theme' )
				add_action('install_plugins_pre_plugin-information', array($this, 'display_plugin_changelog'));

	}
	
	

	/**
	 * Retrieves the latest information from the Headway update server.
	 * 
	 * If an update is available, the information will be similar to this example:
	 * 
	 * 'slug' => 'SLUG OF THEME OR PLUGIN, WILL MATCH THE SLUG IN THIS CLASS',
	 * 'type' => 'TYPE OF PRODUCT, WILL MATCH WHAT'S IN THIS CLASS',
	 * 'new_version' => 'LATEST_VERSION_NUMBER',
	 * 'download_url' => 'http://headwaythemes.com/xxxx',
	 * 'changelog_url' => 'http://headwaythemes.com/xxxx',
	 * 'is_valid_key' => true
	 **/
	public function retrieve_update_info($force_request = false) {

		$update_info = get_transient($this->transient_id);

		/* Query Headway mothership if the previous check has expired. */
		if ( !$update_info ) {

			global $wp_version;

			$update_info_request_parameters = array(
				'timeout' => 5,
				'body' => array(
					'slug' => $this->slug,
					'current_version' => $this->current_version,
					'wp_version' => $wp_version,
					'php_version' => phpversion(),
					'headway_version' => HEADWAY_VERSION,
					'home_url' => home_url(),
					'license_key' => headway_get_license_key()
				)
			);

			$update_info_request = wp_remote_post(HEADWAY_UPDATER_URL, $update_info_request_parameters);
			$update_info = wp_remote_retrieve_body($update_info_request);

			/* If $update_info is false for any reason (bad request, timeout, etc), then just store the result for 30 minutes or until another update check is performed. */
			if ( !$update_info || !is_serialized($update_info) )
				return $this->set_temporary_transient();

			/* We're safe to unserialize the update info. */
			$update_info = maybe_unserialize($update_info);

			/* Check that the update info's ID and type matches what's in the class */
			if ( $update_info['slug'] != $this->slug )
				return $this->set_temporary_transient();

			/* Store update info in the transient for 24 hours. */
			set_transient($this->transient_id, $update_info, 60 * 60 * 24);

		}

		/* If already update to date, then just return false. */
		if ( headway_get('new_version', $update_info) && version_compare($this->current_version, headway_get('new_version', $update_info), '>=') )
			return false;
			
		return $update_info;
		
	}	


	public function update_notice() {
			
		if ( !is_super_admin() || HeadwayOption::get('disable-update-notices', false, false) || !$update_info = $this->retrieve_update_info() )
			return;

		$changelog_url = headway_get('changelog_url', $update_info, false);
		$new_version = headway_get('new_version', $update_info);
		$valid_key = headway_get('is_valid_key', $update_info, false);

		if ( $valid_key === true ) {

			if ( !$this->notify_only ) {

				echo sprintf(__('<div id="update-nag">%s %s is now available, you\'re running %s! <a href="%s">Click here to update</a> or <a href="%s" target="_blank">learn more</a> about the update.</div>', 'headway'), 
					$this->name, $new_version, $this->current_version, admin_url('update-core.php'), str_replace('{KEY}', headway_get_license_key(), $changelog_url));

			} else {

				echo sprintf(__('<div id="update-nag">%s %s is now available, you\'re running %s! Go to the <a href="%s" target="_blank">Headway Dashboard</a> to download the latest version or <a href="%s" target="_blank">learn more</a> about the update.</div>', 'headway'),
					$this->name, $new_version, $this->current_version, HEADWAY_DASHBOARD_URL, str_replace('{KEY}', headway_get_license_key(), $changelog_url));

			}

			return true;

		} else {

			switch ( $valid_key ) {

				case 'expired':
					echo sprintf(__('<div id="update-nag">%s %s is now available, you\'re running %s!  Your Headway license has expired.  Please visit the <a href="%s">Headway Dashboard</a> to renew your license so you can continue to receive updates.</div>', 'headway'), 
						$this->name, $new_version, $this->current_version, HEADWAY_DASHBOARD_URL);
				break;

				default:
					echo sprintf(__('<div id="update-nag">%s %s is now available, you\'re running %s!  You will not be able to update until you enter a valid license key on the <a href="%s">Headway Options</a> panel.</div>', 'headway'), 
						$this->name, $new_version, $this->current_version, admin_url('admin.php?page=headway-options'));
				break;

			}

			return false;

		}
		
	}


	public function intercept_transient($value) {

		$update_info = $this->retrieve_update_info();

		/* If the license key isn't valid, then don't allow the user to update. */
		if ( headway_get('is_valid_key', $update_info, false) === true ) {

			if ( $this->type == 'theme' ) {

				$value->response[$this->path] = array();

				$value->response[$this->path]['slug'] = $this->slug;
				$value->response[$this->path]['url'] = $this->product_url;
				$value->response[$this->path]['package'] = str_replace('{KEY}', headway_get_license_key(), headway_get('download_url', $update_info));
				$value->response[$this->path]['new_version'] = headway_get('new_version', $update_info);
				$value->response[$this->path]['changelog_url'] = headway_get('changelog_url', $update_info);

			} else {

				$value->response[$this->path] = new stdClass();

				$value->response[$this->path]->slug = $this->slug;
				$value->response[$this->path]->url = $this->product_url;
				$value->response[$this->path]->package = str_replace('{KEY}', headway_get_license_key(), headway_get('download_url', $update_info));
				$value->response[$this->path]->new_version = headway_get('new_version', $update_info);
				$value->response[$this->path]->changelog_url = headway_get('changelog_url', $update_info);

			}

		}

		return $value;

	}


	/**
	 * Used if something goes wrong while fetching the update info.
	 **/
	public function set_temporary_transient() {

		set_transient($this->transient_id, array('new_version' => $this->current_version), 60 * 30);
		return false;

	}


	public function clear_transient() {

		delete_transient($this->transient_id);
		remove_action('admin_notices', array($this, 'update_notice'));

	}


	public function display_plugin_changelog() {

		if ( headway_get('plugin') != $this->slug || !$update_info = $this->retrieve_update_info() )
		    return;

		$changelog_request = wp_remote_get(str_replace('{KEY}', headway_get_license_key(), $update_info['changelog_url']));
		$changelog = wp_remote_retrieve_body($changelog_request);

		echo $changelog;

		die();

	}

	
}