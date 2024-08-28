<?php

/**
 * Activate the plugin
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */

/**
 * Activate this plugin i.e. setup tables, data etc.
 * NOT invoked on plugin updates
 *
 * @param bool $network_wide - If the plugin is being network-activated
 */
function widg_activate_plugin( $network_wide=false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			widg_get_instance()->kb_config_obj->reset_cache();
			widg_activate_plugin_do();
			restore_current_blog();
		}
	} else {
		widg_activate_plugin_do();
	}
}

function widg_activate_plugin_do() {

	require_once 'class-widg-autoloader.php';

	// true if the plugin was activated for the first time since installation
	$plugin_version = get_option( 'widg_version' );
	if ( empty($plugin_version) ) {

		set_transient( '_widg_plugin_installed', true, 3600 );

		// setup configuration
		require_once 'class-widg-autoloader.php';
		if ( class_exists('WIDG_KB_Config_DB') ) {
			$kb_db = new WIDG_KB_Config_DB();

			// retrieve all existing KB IDs
			$all_kb_ids = $kb_db->get_kb_ids();
			foreach ( $all_kb_ids as $kb_id ) {

				// update configuration if not found
				$add_on_config = $kb_db->get_kb_config( $kb_id, true );
				if ( is_wp_error( $add_on_config ) ) {
					$add_on_defaults = WIDG_KB_Config_Specs::get_default_kb_config();
					$kb_db->update_kb_configuration( $kb_id, $add_on_defaults, false );
				}
			}
		}

		WIDG_Utilities::save_wp_option( 'widg_version', Echo_Widgets::$version, true );
	}

	set_transient( '_widg_plugin_activated', true, 3600 );

}
register_activation_hook( Echo_Widgets::$plugin_file, 'widg_activate_plugin' );

/**
 * User deactivates this plugin so refresh the permalinks
 */
function widg_deactivation() {
}
register_deactivation_hook( Echo_Widgets::$plugin_file, 'widg_deactivation' );
