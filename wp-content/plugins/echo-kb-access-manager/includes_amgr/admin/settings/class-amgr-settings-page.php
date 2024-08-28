<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
	}

	public function display_debug_data() {

		// ensure user has correct permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			return AMGR_Access_Reject::display_denied_message();
		}

		// display KB configuration
		$output = "\n\n";
		$output .= "AMGR Configurations:\n";
		$output .= "==================\n\n";
		$all_access_kb_configs = epkb_get_instance()->kb_access_config_obj->get_kb_configs();
		foreach ( $all_access_kb_configs as $kb_access_config ) {
			$output .= 'AMGR Config ' . $kb_access_config['id'] . "\n";
			$specs = AMGR_KB_Config_Specs::get_fields_specification( $kb_access_config['id'] );
			foreach( $kb_access_config as $name => $value ) {
				if ( ! is_string($value) ) {
					$value = EPKB_Utilities::get_variable_string($value);
				}
				$label = empty($specs[$name]['label']) ? 'unknown' : $specs[$name]['label'];
				$output .= "\t- " . $label . ' [' . $name . ']' . ' => ' . $value . "\n";
			}

			$output .= "\n\n";
		}

		// display error logs
		$output .= "AMGR ERROR LOG:\n";
		$output .= "==========\n\n";
		$logs = AMGR_Logging::get_logs();
		foreach( $logs as $log ) {
			$output .= empty($log['plugin']) ? '' : $log['plugin'] . " - ";
			$output .= empty($log['kb']) ? '' : $log['kb'] . " - ";
			$output .= empty($log['date']) ? '' : $log['date'] . " - ";
			$output .= empty($log['message']) ? '' : $log['message'] . "\n";
			$output .= empty($log['trace']) ? '' : $log['trace'] . "\n\n";
		}

	}
}