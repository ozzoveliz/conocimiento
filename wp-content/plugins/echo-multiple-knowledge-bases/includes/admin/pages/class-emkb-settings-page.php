<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EMKB_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
	}

	public function display_debug_data( $output ) {

		// only administrators can handle licenses
		if ( ! current_user_can('manage_options') ) {
			return 'No access';
		}

		// display error logs
		/* $output = "\n\nERROR LOG:\n\n";
		$logs = EMKB_Logging::get_logs();
		foreach( $logs as $log ) {
			$output .= empty($log[0]) ? '' : $log[0] . " ";
			$output .= empty($log[1]) ? '' : $log[1] . " ";
			$output .= empty($log[2]) ? '' : $log[2] . "\n";
		} */

		return $output;
	}
}