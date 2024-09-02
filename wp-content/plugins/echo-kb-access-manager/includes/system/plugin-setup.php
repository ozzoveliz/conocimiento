<?php

/**
 * Activate this plugin i.e. setup tables, data etc.
 * NOT invoked on plugin updates
 *
 * @param bool $network_wide - If the plugin is being network-activated
 */
function epkb_activate_plugin( $network_wide=false ) {
	if ( is_multisite() && $network_wide ) {
		wp_die('Access Manager cannot be activated on multisite as a network plugin.');
	} else {
		epkb_activate_plugin_do();
	}
}
register_activation_hook( Echo_Knowledge_Base::$plugin_file, 'epkb_activate_plugin' );

function epkb_activate_plugin_do() {

	require_once 'class-epkb-autoloader.php';

	// 1. ensure we have initial configuration
	$core_plugin_version = EPKB_Utilities::get_wp_option( 'epkb_version', null, false, true );
	if ( is_wp_error($core_plugin_version) ) {
		AMGR_Logging::add_log( "Error ocurred when retrieving EPKB version upon activation", $core_plugin_version );
	}
	if ( is_wp_error($core_plugin_version) || empty($core_plugin_version) ) {
		$kb_id = EPKB_KB_Config_DB::DEFAULT_KB_ID;
		$handler = new EPKB_KB_Config_DB();
		$handler->update_kb_configuration( $kb_id, EPKB_KB_Config_Specs::get_default_kb_config( $kb_id) );
	}

	// 2. prepare AMGR portion
	amgr_activate_plugin( $core_plugin_version );

	// 3. either create a new KB or update existing one

	if ( empty( $core_plugin_version ) ) {

		set_transient( '_epkb_plugin_installed', true, 3600 );
		EPKB_Core_Utilities::add_kb_flag( 'epkb_run_setup' );

		// prepare KB configuration
		$kb_config = epkb_get_instance()->kb_config_obj->get_kb_config_or_default( EPKB_KB_Config_DB::DEFAULT_KB_ID );
		epkb_get_instance()->kb_config_obj->update_kb_configuration( EPKB_KB_Config_DB::DEFAULT_KB_ID, $kb_config );

		// update KB versions
		EPKB_Utilities::save_wp_option( 'epkb_version', Echo_Knowledge_Base::$version );
		EPKB_Utilities::save_wp_option( 'epkb_version_first', Echo_Knowledge_Base::$version );  // TODO REMOVE end 2024
	}

	set_transient( '_amgr_plugin_activated', true, 3600 );

	// Clear permalinks
	update_option( 'epkb_flush_rewrite_rules', true );
	set_transient( '_epkb_faqs_flush_rewrite_rules', true, 3600 );

	$handler = new AMGR_Setup_KB_Groups();
	$handler->setup_amgr_data();

	flush_rewrite_rules( false );
}

/**
 * User deactivates this plugin so refresh the permalinks
 */
function epkb_deactivation() {

	// Clear the permalinks to remove our post type's rules
	flush_rewrite_rules( false );

}
register_deactivation_hook( Echo_Knowledge_Base::$plugin_file, 'epkb_deactivation' );

/**
 * Setup AMGR part of the plugin
 *
 * @param $core_plugin_version
 */
function amgr_activate_plugin( $core_plugin_version ) {
	/** @var $wpdb Wpdb */
	global $wpdb;

	require_once Echo_Knowledge_Base::$plugin_dir . 'includes_amgr/system/class-amgr-autoloader.php';

	/** AMGR: Ensure that we have AMGR tables in place */
	$handle = new AMGR_DB_KB_Groups();
	@$handle->create_table();
	$handle = new AMGR_DB_KB_Public_Groups();
	@$handle->create_table();
	$handle = new AMGR_DB_KB_Group_Users();
	@$handle->create_table();
	$handle = new AMGR_DB_Access_KB_Categories();
	@$handle->create_table();
	$handle = new AMGR_DB_Access_Read_Only_Articles();
	@$handle->create_table();
	$handle = new AMGR_DB_Access_Read_Only_Categories();
	@$handle->create_table();

	// true if the plugin is activated for the first time since installation
	$amgr_plugin_version = get_option( 'amag_version' );
	if ( empty($amgr_plugin_version) ) {

		set_transient( '_amgr_plugin_installed', true, 3600 );

		// retrieve all existing KB IDs
		$kb_db = new EPKB_KB_Config_DB();
		AMGR_Logging::disable_logging();
		$all_kb_ids = $kb_db->get_kb_ids();
		AMGR_Logging::enable_logging();

		// setup configuration
		foreach ( $all_kb_ids as $kb_id ) {

			// ensure AMGR config doesn't exist already
			AMGR_Logging::disable_logging();
			$amgr_db = new AMGR_KB_Access_Config_DB( false );
			$amgr_config = $amgr_db->get_kb_config( $kb_id );
			AMGR_Logging::enable_logging();
			if ( is_wp_error( $amgr_config ) ) {
				$amgr_defaults = AMGR_KB_Config_Specs::get_default_kb_config( $kb_id );
				AMGR_Logging::disable_logging();
				$amgr_db->update_kb_configuration( $kb_id, $amgr_defaults );
				AMGR_Logging::enable_logging();
			}

			// do not check articles if KB not setup
			if ( $kb_id == 1 && empty($core_plugin_version) ) {
				continue;
			}

			// ensure that all articles are initially private
			$current_articles_ids = AMGR_Access_Utilities::get_articles_ids_from_sequence( $kb_id );
			if ( $current_articles_ids === null ) {
				AMGR_Logging::add_log( "could not get article ids from sequence at setup" );
			} else {
				foreach( $current_articles_ids as $current_articles_id ) {
					$post = EPKB_Core_Utilities::get_kb_post_secure( $current_articles_id );
					if ( $post === null ) {
						AMGR_Logging::add_log( "Could not retrieve post: " . $current_articles_id );
						continue;
					}

					if ( $post->post_status != 'publish' ) {
						continue;
					}

					// update the post status
					if ( false === $wpdb->update( $wpdb->posts, array( 'post_status' => 'private' ), array( 'ID' => $current_articles_id ) ) ) {
						AMGR_Logging::add_log( "Could not update post status in the database: " . $wpdb->last_error );
						continue;
					}
				}
			}
		} // next kb_id

		EPKB_Utilities::save_wp_option( 'amag_version', Echo_Knowledge_Base::$amag_version );
	}
}
