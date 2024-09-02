<?php  if ( ! defined( 'ABSPATH' ) ) exit;

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
function amcr_activate_plugin( $network_wide=false ) {
	if ( is_multisite() && $network_wide ) {
		wp_die('Access Manager cannot be activated on multisite as a network plugin.');
	} else {
		amcr_activate_plugin_do();
	}
}
register_activation_hook( Echo_Custom_roles::$plugin_file, 'amcr_activate_plugin' );

function amcr_activate_plugin_do() {

	require_once 'class-amcr-autoloader.php';

	AMCR_Logging::disable_logging();

	// true if the plugin was activated for the first time since installation
	$plugin_version = get_option( 'amcr_version' );
	if ( empty($plugin_version) ) {

		update_option( 'amcr_version', Echo_Custom_roles::$version );
		set_transient( '_amcr_plugin_installed', true, 3600 );

		if ( class_exists('AMCR_KB_Config_DB') ) {
			$kb_db = new AMCR_KB_Config_DB();

			// retrieve all existing KB IDs
			$all_kb_ids = $kb_db->get_kb_ids();
			foreach ( $all_kb_ids as $kb_id ) {

				// ensure add-on config doesn't exist already
				$add_on_config = $kb_db->get_kb_config( $kb_id, false );
				if ( is_wp_error( $add_on_config ) ) {
					$add_on_defaults = AMCR_KB_Config_Specs::get_default_kb_config();
					$kb_db->update_kb_configuration( $kb_id, $add_on_defaults, false );
				}
			}
		}
	}

	AMCR_Logging::enable_logging();

	set_transient( '_amcr_plugin_activated', true, 3600 );
}

/**
 * User deactivates this plugin so refresh the permalinks
 */
function amcr_deactivation() {
}
register_deactivation_hook( Echo_Custom_roles::$plugin_file, 'amcr_deactivation' );
