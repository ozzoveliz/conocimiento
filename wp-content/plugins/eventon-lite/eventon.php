<?php
/**
 * Plugin Name: EventON Lite
 * Plugin URI: http://www.myeventon.com/lite
 * Description: A beautifully crafted minimal calendar experience - Lite Version
 * Version: 2.2.20
 * Author: Ashan Jay
 * Author URI: http://www.ashanjay.com
 * Requires at least: 6.0
 * Tested up to: 6.6.1
 * 
 * Text Domain: eventon
 * Domain Path: /lang/languages/
 * 
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package EventON
 * @category Core
 * @author AJDE
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! defined( 'EVO_PLUGIN_FILE' ) ) {
	define( 'EVO_PLUGIN_FILE', __FILE__ );
}

// Include main EventON Class
if ( ! class_exists( 'EventON', false ) ) {
	include_once dirname( EVO_PLUGIN_FILE ) . '/includes/class-eventon.php';
}


// Returns the main instance of EVO
function EVO(){	
	return EventON::instance();
}

// Global for backwards compatibility
$GLOBALS['eventon'] = EVO();	

// From the sweet spot of the universe!
?>