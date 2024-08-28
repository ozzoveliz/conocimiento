<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


/**
 * Uninstall this plugin
 *
 */


flush_rewrite_rules(false);

/** Delete plugin options */
// do not delete 'eprf_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'eprf_version' );
// TODO if user explicitly specifies: delete_option( 'eprf_config_' );

delete_option( 'eprf_error_log' );
delete_option( 'eprf_license_key' );
delete_option( 'eprf_license_status' );
delete_option( 'eprf_license_state' );
