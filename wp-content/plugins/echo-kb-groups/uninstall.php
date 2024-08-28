<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


/**
 * Uninstall this plugin
 *
 */


flush_rewrite_rules(false);

/** Delete plugin options */
// do not delete 'amgp_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'amgp_version' );
delete_option( 'amgp_error_log' );
delete_option( 'amgp_license_key' );
delete_option( 'amgp_license_status' );
delete_option( 'amgp_license_state' );
