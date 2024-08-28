<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


/**
 * Uninstall this plugin
 *
 */


flush_rewrite_rules(false);

/** Delete plugin options */
// do not delete 'asea_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'asea_version' ); and delete_option('asea_analytics_start_date')
delete_option( 'asea_error_log' );
delete_option( 'asea_license_key' );
delete_option( 'asea_license_status' );
delete_option( 'asea_license_state' );
