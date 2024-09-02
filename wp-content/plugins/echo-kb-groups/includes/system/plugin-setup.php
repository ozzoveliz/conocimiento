<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Activate this plugin i.e. setup tables, data etc.
 * NOT invoked on plugin updates
 *
 * @param bool $network_wide - If the plugin is being network-activated
 */
function amgp_activate_plugin( $network_wide=false ) {
	if ( is_multisite() && $network_wide ) {
		wp_die('Access Manager cannot be activated on multisite as a network plugin.');
	} else {
		amgp_activate_plugin_do();
	}
}
register_activation_hook( Echo_KB_Groups::$plugin_file, 'amgp_activate_plugin' );

function amgp_activate_plugin_do() {

	require_once 'class-amgp-autoloader.php';

	AMGP_Logging::disable_logging();

	// true if the plugin is activated for the first time since installation
	$plugin_version = get_option( 'amgp_version' );
	if ( empty( $plugin_version ) ) {

		update_option( 'amgp_version', Echo_KB_Groups::$version );
		set_transient( '_amgp_plugin_installed', true, 3600 );

		if ( class_exists('AMGP_KB_Config_DB') ) {
			$kb_db = new AMGP_KB_Config_DB();

			// retrieve all existing KB IDs
			$all_kb_ids = $kb_db->get_kb_ids();
			// setup configuration
			foreach ( $all_kb_ids as $kb_id ) {

				// update configuration if not found
				$add_on_config = $kb_db->get_kb_config( $kb_id, false );
				if ( is_wp_error( $add_on_config ) ) {
					$add_on_defaults = AMGP_KB_Config_Specs::get_default_kb_config();
					$kb_db->update_kb_configuration( $kb_id, $add_on_defaults, false );
				}
			}
		}
	}

	AMGP_Logging::enable_logging();

	set_transient( '_amgp_plugin_activated', true, 3600 );
}

/**
 * User deactivates this plugin so refresh the permalinks
 */
function amgp_deactivation() {
}
register_deactivation_hook( Echo_KB_Groups::$plugin_file, 'amgp_deactivation' );
