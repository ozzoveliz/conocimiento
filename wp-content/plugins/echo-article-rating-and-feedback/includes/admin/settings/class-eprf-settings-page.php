<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPRF_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
	}

	public function display_debug_data( $output ) {

		// only administrators can handle licenses
		if ( ! current_user_can( 'manage_options' ) ) {
			return 'No access';
		}

		// display KB configuration
		/* $output .= "\n\n\nEPRF Configuration:\n\n";
		$all_kb_configs = eprf_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_config ) {
			$output .= 'EPRF Config ' . $kb_config['id'] . "\n\n";
			$specs = EPRF_KB_Config_Specs::get_fields_specification();
			foreach( $kb_config as $name => $value ) {
				if ( ! is_string($value) ) {
					$value = EPRF_Utilities::get_variable_string($value);
				}
				$label = empty($specs[$name]['label']) ? 'unknown' : $specs[$name]['label'];
				$output .= '- ' . $label . ' [' . $name . ']' . ' => ' . $value . "\n";
			}

			$output .= "\n\n";
		}

		// display error logs
		$output .= "\n\n\n\nERROR LOG:\n\n";
		$logs = EPRF_Logging::get_logs();
		foreach( $logs as $log ) {
			$output .= empty($log['date']) ? '' : $log['date'] . " ";
			$output .= empty($log['message']) ? '' : $log['message'] . " ";
			$output .= empty($log['trace']) ? '' : $log['trace'] . "\n";
		} */

		return $output;
	}

}