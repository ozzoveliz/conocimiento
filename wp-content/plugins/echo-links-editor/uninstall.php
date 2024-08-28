<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

/** Delete plugin options */
// do not delete 'kblk_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'kblk_version' );
delete_option( 'kblk_error_log' );
delete_option( 'kblk_license_key' );
delete_option( 'kblk_license_status' );
delete_option( 'kblk_license_state' );
