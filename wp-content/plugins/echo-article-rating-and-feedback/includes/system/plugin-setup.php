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
function eprf_activate_plugin( $network_wide=false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			eprf_get_instance()->kb_config_obj->reset_cache();
			eprf_activate_plugin_do();
			restore_current_blog();
		}
	} else {
		eprf_activate_plugin_do();
	}
}

function eprf_activate_plugin_do() {

	require_once 'class-eprf-autoloader.php';

	// true if the plugin was activated for the first time since installation
	$plugin_version = get_option( 'eprf_version' );
	if ( empty($plugin_version) ) {

		set_transient( '_eprf_plugin_installed', true, 3600 );

		// setup configuration
		require_once 'class-eprf-autoloader.php';
		if ( class_exists('EPRF_KB_Config_DB') ) {
			$kb_db = new EPRF_KB_Config_DB();

			// retrieve all existing KB IDs
			$all_kb_ids = $kb_db->get_kb_ids();
			foreach ( $all_kb_ids as $kb_id ) {

				// update configuration if not found
				$add_on_config = $kb_db->get_kb_config( $kb_id, true );
				if ( is_wp_error( $add_on_config ) ) {
					$add_on_defaults = EPRF_KB_Config_Specs::get_default_kb_config();
					$kb_db->update_kb_configuration( $kb_id, $add_on_defaults, false );
				}
			}
		}

        EPRF_Utilities::save_wp_option( 'eprf_version', Echo_Article_Rating_And_Feedback::$version, true );
	}

	set_transient( '_eprf_plugin_activated', true, 3600 );

	$handle = new EPRF_Rating_DB();
	@$handle->create_table();
}
register_activation_hook( Echo_Article_Rating_And_Feedback::$plugin_file, 'eprf_activate_plugin' );

/**
 * User deactivates this plugin so refresh the permalinks
 */
function eprf_deactivation() {
}
register_deactivation_hook( Echo_Article_Rating_And_Feedback::$plugin_file, 'eprf_deactivation' );
