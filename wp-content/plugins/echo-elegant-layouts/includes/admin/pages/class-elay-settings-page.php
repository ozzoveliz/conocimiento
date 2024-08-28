<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ELAY_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
	}

	public function display_debug_data( $output ) {

		// only administrators can handle licenses
		if ( ! current_user_can( 'manage_options' ) ) {
			return 'No access';
		}

		// display error logs
		$logs = ELAY_Logging::get_logs();

		if ( ! empty($logs) ) {
			$output .= "\n\n\n\nERROR LOG:\n\n";
			foreach ( $logs as $log ) {
				$output .= empty( $log['date'] ) ? '' : $log['date'] . " ";
				$output .= empty( $log['message'] ) ? '' : $log['message'] . " ";
				$output .= empty( $log['trace'] ) ? '' : $log['trace'] . "\n";
			}
		}

		return $output;
	}

	/**
	 * Display license information. FUTURE possibly TODO
	 *
	 * @return string
	 * @throws Exception
	 */
	private function display_license_information() {

		$output = '';
		$license_handler = elay_get_instance()->license_handler;

		$license_data = $license_handler->retrieve_license_data();

		$license_key = $license_handler->get_license_key();
		$add_on_data = $license_handler->contact_license_server( 'get_version', $license_key );
		$current_version = Echo_Elegant_Layouts::$version;

		if ( ! empty($add_on_data) &&  is_object( $add_on_data ) && ! empty( $add_on_data->new_version ) ) {
			$latest_add_on_version = $add_on_data->new_version;
		} else {
			return 'Could not contact licensing server';
		}

		$license_state = isset($license_data->license) ? $license_data->license : '';
		$expiry_date = isset($license_data->expires) ? $license_data->expires : '';
		$license_error = isset($license_data->error) ? $license_data->error : '';

				// License is active and valid
		if ( $license_state == 'valid' ) {
			$status_msg = 'License is valid and active.';

			// license is deactivated
		} else if ( $license_state == 'deactivated' ) {
			$status_msg = 'License ' . $license_key . ' has been removed and deactivated for this website.';

			// License is empty OR invalid (inactive, expired, wrong product etc.)
		} else {
			$status_msg = $license_handler->retrieve_status_message( $license_data, $license_key );
		}

		// only show expiry date when license is or was valid
		if ( ! empty($license_error) && ! in_array($license_error, array('site_inactive', 'expired', 'no_activations_left', 'disabled', 'license_not_activable')) ) {
			$expiry_date = '';
		}

		$is_add_on_dormant = elay_get_instance()->is_dormant;

		$expiry_msg = '';
		if ( ! empty($expiry_date) ) {
			$expiry_msg = elay_get_instance()->license_handler->get_expiry_warning( $expiry_date );
		}

		$license_status_short = $license_handler->get_license_status_short('');

		$output .= "\n\n";
		$output .= 'License: ' . $license_key . "\n";
		$output .= 'Current version: ' . $current_version . "\n";
		$output .= 'Latest version: ' . $latest_add_on_version . "\n";

		$output .= 'Status message: ' . $status_msg . "\n";
		$output .= 'License state: ' . $license_state . "\n";
		$output .= 'License status short: ' . ELAY_Utilities::get_variable_string($license_status_short) . "\n";

		$output .= 'Expiry date: ' . $expiry_date . "\n";
		$output .= 'Expiry msg: ' . $expiry_msg . "\n";
		$output .= 'License state: ' . $license_state . "\n";
		$output .= 'License error: ' . ELAY_Utilities::get_variable_string($license_error) . "\n";

		$output .= 'Last updated: ' . ( empty($add_on_data->last_updated) ? '' : $add_on_data->last_updated ) . "\n";
		$output .= 'Payment ID: ' . ( empty($license_data->payment_id) ? '' : $license_data->payment_id ) . "\n";
		$output .= 'Customer name: ' . ( empty($license_data->customer_name) ? '' : $license_data->customer_name ) . "\n";
		$output .= 'License limit: ' . ( empty($license_data->license_limit) ? '' : $license_data->license_limit ) . "\n";
		$output .= 'Site Count: ' . ( empty($license_data->site_count) ? '' : $license_data->site_count ) . "\n";
		$output .= 'Activations Left: ' . ( empty($license_data->activations_left) ? '' : $license_data->activations_left ) . "\n";


		$output .= 'Add-on dormant: ' . ( $is_add_on_dormant ? 'Yes' : 'No' ) . "\n\n\n";

		return $output;
	}

}