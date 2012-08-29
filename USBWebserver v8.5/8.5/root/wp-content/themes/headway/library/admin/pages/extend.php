<?php
HeadwayAdminExtend::display();


class HeadwayAdminExtend {


    public static function display() {

        /* Output */
        $options = array(
            'body' => array(
                'installed_plugins' => self::get_installed_plugins(),
                'installed_themes' => self::get_installed_themes(),
                'install_nonces' => self::get_install_nonces(),
                'license_key' => headway_get_license_key(),
                'headway_options_url' => admin_url('admin.php?page=headway-options'),
                'admin_url' => untrailingslashit(admin_url())
            )
        );

        $addons_request = wp_remote_post(HEADWAY_EXTEND_DATA_URL, $options);
        $addons = wp_remote_retrieve_body($addons_request);

        if ( !$addons || $addons_request['response']['code'] != 200 ) {

            echo '<h2>Headway Extend</h2>';

            echo '<h3>Whoops!</h3>';
            echo '<p>There was a problem fetching the addons from Headway Extend.  Please try again later.</p>';

        } else {

            echo $addons;

        }

    }


    public static function get_installed_plugins() {

        $raw_plugins = get_plugins();
        $installed_plugins = array();

        foreach ( $raw_plugins as $key => $plugin ) {

            $is_active = is_plugin_active($key);

            $installed_plugins[$key] = array(
                'plugin' => $key, 
                'name' => $plugin['Name'], 
                'is_active' => $is_active,
                'activation_url' => $is_active ? '' : wp_nonce_url('plugins.php?action=activate&plugin=' . $key, 'activate-plugin_' . $key)
            );

        }

        return $installed_plugins;

    }


    public static function get_installed_themes() {

        $raw_themes = function_exists('wp_get_themes') ? wp_get_themes() : get_themes();
        $installed_themes = array();

        foreach ( $raw_themes as $key => $theme ) {

            $is_active = get_option('stylesheet') == $theme['Stylesheet'];

            /* WP 3.4 and newer requires a different nonce */
            $nonce = function_exists('wp_get_themes') ? 'switch-theme_' . $theme['Stylesheet'] : 'switch-theme_' . $theme['Template'];

            $installed_themes[$theme['Stylesheet']] = array(
                'theme' => $theme['Stylesheet'], 
                'name' => $theme['Name'], 
                'is_active' => $is_active,
                'activation_url' => $is_active ? '' : wp_nonce_url('themes.php?action=activate&template=' . urlencode($theme['Template']) . '&stylesheet=' . urlencode($theme['Stylesheet']), $nonce)
            );

        }

        return $installed_themes;

    }


    public static function get_install_nonces() {

        $addons_array_request = wp_remote_get(add_query_arg(array('action' => 'addons-array'), HEADWAY_EXTEND_DATA_URL));
        $addons_array = wp_remote_retrieve_body($addons_array_request);

        if ( !is_serialized($addons_array) || $addons_array_request['response']['code'] !== 200 )
            return false;

        $addons_array = maybe_unserialize($addons_array);
        $nonces = array();

        foreach ( $addons_array['themes'] as $addon )
            $nonces[$addon['path']] = wp_create_nonce('install-theme_' . $addon['path']);

        foreach ( $addons_array['blocks'] as $addon )
            $nonces[$addon['path']] = wp_create_nonce('install-plugin_' . $addon['path']);

        return $nonces;

    }


}