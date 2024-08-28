<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;


/**
 * Uninstall this plugin
 *
 */


flush_rewrite_rules(false);

/** Delete plugin options */
// do not delete 'amcr_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'amcr_version' );
delete_option( 'amcr_error_log' );
delete_option( 'amcr_license_key' );
delete_option( 'amcr_license_status' );
delete_option( 'amcr_license_state' );
