<?php

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

/** Delete plugin options */
// do not delete 'elay_version' so we know whether this is a new install
// TODO if user explicitly specifies: delete_option( 'elay_version' );
// TODO if user explicitly specifies: delete_option( 'elay_categories_icons' );
// TODO if user explicitly specifies: delete_option( 'elay_config_' );

delete_option( 'elay_error_log' );
delete_option( 'elay_license_key' );
delete_option( 'elay_license_status' );
delete_option( 'elay_license_state' );
