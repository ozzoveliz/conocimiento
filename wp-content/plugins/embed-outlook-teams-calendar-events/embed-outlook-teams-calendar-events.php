<?php
/**
 * Plugin Name: Embed Outlook Teams Calendar Events
 * Plugin URI: https://plugins.miniorange.com/
 * Description: This plugin will allow you to sync personal and group calendars, events, contacts from Outlook to WordPress.
 * Version: 1.0.3
 * Author: miniOrange
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package embed-outlook-teams-calendar-events
 */

namespace MOTCE;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-motce-namespace-autoloader.php';

$autoloader = new MOTCE_Namespace_Autoloader(
	array(
		'directory'        => __DIR__,
		'namespace_prefix' => 'MOTCE',
		'classes_dir'      => array( '.', 'API', 'Controller', 'Observer', 'View', 'Wrapper' ),
	)
);

$autoloader->init();

define( 'MOTCE_PLUGIN_FILE', __FILE__ );
define( 'MOTCE_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'MOTCE_PLUGIN_VERSION', '1.0.3' );
define( 'MOTCE_SETUP_GUIDE_URL', 'https://plugins.miniorange.com/setup-guide-for-wordpress-outlook-calendar-events-integration' );
MOTCE_Outlook::load_instance();
