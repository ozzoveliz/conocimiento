<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMCR_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
		add_filter( 'amag_get_error_logs', array( $this, 'get_error_logs' ) );
		add_action( 'eckb_reset_error_Logs', array( $this, 'reset_log' ) );
	}

	public function display_debug_data( $output_param ) {

		// only administrators can handle licenses
		if ( ! current_user_can('manage_options') ) {
			return 'No access';
		}

		// display KB configuration
		$output = "\n\n\n";
		$output .= "CUSTOM ROLES Configuration:\n========================\n\n";
		$role_mapping = AMCR_Utilities::get_wp_option( 'amag_wp_roles_map', '' );
		$group_role_mapping = AMCR_Utilities::get_wp_option( 'amag_wp_role_group_map', '' );

		$output .= "Role Mapping:\n";
		$output .= AMCR_Utilities::get_variable_string($role_mapping);
		$output .= "\n\nRole Group Mapping:\n";
		$output .= AMCR_Utilities::get_variable_string($group_role_mapping);

		/* $all_kb_configs = amcr_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_config ) {
			$specs = AMCR_KB_Config_Specs::get_fields_specification();
			foreach( $kb_config as $name => $value ) {
				if ( ! is_string($value) ) {
					$value = AMCR_Utilities::get_variable_string($value);
				}
				$label = empty($specs[$name]['label']) ? 'unknown' : $specs[$name]['label'];
				$output .= '- ' . $label . ' [' . $name . ']' . ' => ' . $value . "\n";
			}

			$output .= "\n\n";
		} */

		// display error logs
		$output .= "\n\nCUSTOM ROLES ERROR LOG:\n==========\n\n";
		$logs = AMCR_Logging::get_logs();
		foreach( $logs as $log ) {
			$output .= empty($log['date']) ? '' : $log['date'] . " ";
			$output .= empty($log['kb']) ? '' : $log['kb'] . " ";
			$output .= empty($log['message']) ? '' : $log['message'] . " ";
			$output .= empty($log['trace']) ? '' : $log['trace'] . " ";
		}

		return $output_param . $output;
	}

	public function get_error_logs( $logs ) {
		return array_merge($logs, AMCR_Logging::get_logs());
	}

	public function reset_log() {

		// 1. Is User Admin ?
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::ajax_show_error_die( esc_html__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ) );
		}

		AMCR_Logging::reset_logs();
	}

	public function user_not_logged_in() {
		AMCR_Utilities::ajax_show_error_die( '<p>' . esc_html__( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', esc_html__( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}