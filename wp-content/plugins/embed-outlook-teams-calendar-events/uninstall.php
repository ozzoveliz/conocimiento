<?php
/**
 * This deletes the configuration on plugin on uninstall.
 *
 * @package embed-outlook-teams-calendar-events
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'application_config' );
delete_option( 'mail_config' );
