<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

/** Delete plugin options */
// do not delete 'widg_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'widg_version' );
delete_option( 'widg_error_log' );
delete_option( 'widg_license_key' );
delete_option( 'widg_license_status' );
delete_option( 'widg_license_state' );
