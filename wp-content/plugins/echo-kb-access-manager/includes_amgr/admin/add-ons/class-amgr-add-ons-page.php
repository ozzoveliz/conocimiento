<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display add-on plugins and licenses
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Add_Ons_Page {

	const AMGR_KB_ADD_ONS_PAGE = 'epkb-add-ons';

	public function __construct() {
		add_filter( 'epkb_license_fields', array( 'AMGR_Add_Ons_Page', 'display_license_status_update' ), 1 );  // display AMGR license at the top of the list
		add_action( 'wp_ajax_amgr_handle_license_request', array( $this, 'handle_license_request' ) );
		add_action( 'wp_ajax_nopriv_amgr_handle_license_request', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Before we show license check its status.
	 *
	 * @param $output
	 * @return string
	 */
	public static function display_license_status_update( $output ) {

		// only administrators can handle licenses
		if ( ! current_user_can('manage_options') ) {
			return $output;
		}

		$output .= '<li class="ekb-add-on">';
		$output .= '<div class="ekb-add-on-title">'
					.'<h2>'. AMGR_License_Handler::PLUGIN_NAME . '</h2>' .
			       '</div>'.
		           '<div class="ekb-license-check" id="amgr_license_check">Updating license status. Please wait.</div>' .
		           '<div id="amgr-ajax-in-progress" style="display:none;">' . esc_html__( 'Updating license...', 'echo-knowledge-base' ) . '</div>';
		$output .= '</li>';

		return $output;
	}

	/**
	 * Display license field
	 */
	public function handle_license_request() {

		// ensure user has correct permission
		if ( ! current_user_can( 'manage_options' ) ) {
			EPKB_Utilities::ajax_show_error_die( 'You do not have permission to view license info.' );
		}

		$amgr_instance = epkb_get_instance();
		if ( empty($amgr_instance) || ! $amgr_instance instanceof Echo_Knowledge_Base ) {
			EPKB_Utilities::ajax_show_error_die( 'Failed to initialize (M99).' );
		}

		$command = EPKB_Utilities::post( 'command' );
		$command = ! empty($command) && in_array( $command, array( 'get_license_info', 'save' ) ) ? $command : '';
		if ( empty($command) ) {
			EPKB_Utilities::ajax_show_error_die( 'Unknown licensing command: ' . $command );
		}

		if ( empty( $amgr_instance->license_handler ) ) {
			EPKB_Utilities::ajax_show_error_die( 'Failed to initialize (L99)' );
		}

		$license_handler = $amgr_instance->license_handler;

		// handle empty license field - no need to contact license server
		if ( $command == 'get_license_info' ) {
			$license_data = $license_handler->retrieve_license_data();

		// handle SAVE action
		} else {
			$license_data = $license_handler->handle_license_command();
		}

		wp_die( wp_json_encode( array( 'output' => $this->get_license_state( $license_handler, $license_data ), $type='success') ) );
	}

	/**
	 * Retrieve full license state and output fields
	 *
	 * @param AMGR_License_Handler $license_handler
	 * @param $license_data
	 * @return string license fields
	 */
	private function get_license_state( $license_handler, $license_data ) {

		$license_key = $license_handler->get_license_key();

		// ADD-ON VERSION - find the latest version of this add-on
		$add_on_data = $license_handler->contact_license_server( 'get_version', $license_key );
		if ( ! empty($add_on_data) &&  is_object( $add_on_data ) && ! empty( $add_on_data->new_version ) ) {
			$latest_add_on_version = $add_on_data->new_version;
		} else {
			//AMGR_Logging::add_log("Could not contact licensing server.", ( isset($add_on_data->license) ? $add_on_data->license : '' ) );
			$cached_info = $license_handler->get_cached_info();
			$latest_add_on_version = $cached_info['latest_add_on_version'];
		}

		$license_state = isset($license_data->license) ? $license_data->license : '';
		$expiry_date = isset($license_data->expires) ? $license_data->expires : '';
		$license_error = isset($license_data->error) ? $license_data->error : '';

		// CONNECTION ERROR - if we can't connect show the last status
		if ( ! empty($license_error) && $license_handler->is_connection_error( $license_error ) ) {
			//AMGR_Logging::add_log("Could not contact licensing server.", ( isset($license_data->license) ? $license_data->license : '' ) );
			$cached_info = $license_handler->get_cached_info();
			$status_msg = $cached_info['status_msg'] . '<br>
            <div class="eckb-tooltip-container eckb-tip-error">
                <div class="eckb-tooltip-button"> Server Error: Click here for more info</div>
                <div class="eckb-tooltip-contents eckb-tooltip-top">
                    <h4 class="eckb-tooltip-heading">Server Errors:</h4>
                    ' . $license_state . '
                    <hr>
                    <p> Please try again later. If the problem persists first check our license FAQ for a solution here <a href="https://www.echoknowledgebase.com/documentation/my-account-and-license-faqs#license-faqs" target="_blank" rel="noopener">documentation</a>
                    If submitting a support ticket, please copy the above text and include it in the message.</p>
                </div>
            </div>';
			return $this->license_fields( $license_state, $status_msg, $cached_info['expiry_date'], $cached_info['latest_add_on_version'], $license_key );
		}

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

		// store last known status
		EPKB_Utilities::save_wp_option( AMGR_License_Handler::AMGR_LICENSE_STATE, array( $status_msg, $license_state, $latest_add_on_version, $expiry_date ) );

		EPKB_Utilities::save_wp_option('amgr_last_license_check', array() );
		return $this->license_fields( $license_state, $status_msg, $expiry_date, $latest_add_on_version, $license_key );
	}

	/**
	 * Renders licence key fields
	 *
	 * @param $status
	 * @param $status_msg
	 * @param $expiry_date
	 * @param $latest_add_on_version
	 * @param $license_key
	 *
	 * @return string Text field
	 */
	private function license_fields( $status, $status_msg, $expiry_date, $latest_add_on_version, $license_key ) {

		ob_start();

		// WARNING MESSAGE
		if ( $status != 'valid' ) {
			$warning_msg = 'Your license has to be both <strong>active</strong> and <strong>valid</strong> in order to receive <strong>add-on fixes, new features and support</strong>.';
		}		?>

		<p class="ekb-error"><?php echo empty($warning_msg) ? '' : '<span>Warning: </span>' . wp_kses_post( $warning_msg ); ?></p>

		<!-- LICENSE KEY -->
		<div class="ekb-license-key">
			<ul>
				<li><label>License Key:</label></li>
				<li><input type="text" name="<?php echo AMGR_License_Handler::LICENSE_KEY_OPTION_ID; ?>" id="<?php echo AMGR_License_Handler::LICENSE_KEY_OPTION_ID; ?>"
				           value="<?php echo esc_attr( $license_key ); ?>" placeholder="Enter your license key"
				           data-status="<?php echo esc_attr($status); ?>" data-current-license-key="<?php echo esc_attr($license_key); ?>" maxlength="50"/></li>
				<li><span class="amgr_license_action"><input id="amgr_save_btn" class="primary-btn" type="button" name="amgr_save_btn" value="<?php echo  esc_html__( 'Save', 'echo-knowledge-base' ); ?>"/>
						<input type="hidden" id="_wpnonce_amgr_license_key" name="_wpnonce_amgr_license_key" value="<?php echo wp_create_nonce( "_wpnonce_amgr_license_key" ); ?>"/></span></li>
			</ul>
		</div>

		<!-- LICENSE STATUS -->
		<div class="ekb-license-status">
			<ul>
				<li>
					<span class="ekb-status-icon <?php echo ( $status == 'valid' ? 'ep_font_icon_checkmark' : 'ep_font_icon_error_circle' ); ?>"></span>
				</li>
				<li><strong>Status:</strong></li>
				<li><span id="amgr_license_key_status"><?php echo wp_kses_post( $status_msg ); ?></span></li>
			</ul>
		</div>

		<!-- EXPIRES ON -->
		<div class="ekb-license-expiry">			<?php

			$expiry_icon = '';
			$expiry_msg = 'No valid license key detected';
			if ( ! empty($expiry_date) ) {
				$expiry_msg = epkb_get_instance()->license_handler->get_expiry_warning( $expiry_date );
				$expiry_icon = empty($expiry_msg) ? 'ep_font_icon_checkmark' : 'ep_font_icon_error_circle';
			}
			$expiry_msg = empty($expiry_msg) ? EPKB_Utilities::get_formatted_datetime_string( $expiry_date ) : $expiry_msg;			?>

			<ul>
				<li><span class="ekb-status-icon <?php echo esc_attr( $expiry_icon ); ?>"></span></li>
				<li><strong>Expires on:</strong></li>
				<li><?php echo wp_kses_post( $expiry_msg ); ?></li>
			</ul>
		</div>

		<!-- ADD-ON VERSION -->
		<div class="ekb-license-version">			<?php

			$new_version_available = version_compare( Echo_Knowledge_Base::$amag_version, $latest_add_on_version, "<" );
			$icon = $new_version_available ? 'ep_font_icon_error_circle' : 'ep_font_icon_checkmark';			?>

			<ul>
				<li><span class="ekb-status-icon <?php echo esc_attr( $icon ); ?>"></span></li>
				<li><strong>Add-on Version: </strong></li>
				<li>Installed: <?php echo Echo_Knowledge_Base::$amag_version; ?></li>
				<li>Latest:<?php echo esc_html($latest_add_on_version); ?></li>
				<li>					<?php

					// show update link only if license is valid
					if ( $new_version_available && $status == 'valid' ) {
						$name = plugin_basename( Echo_Knowledge_Base::$plugin_file );
						echo '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $name, 'upgrade-plugin_' . $name ) ) .'" target="_blank">Update Plugin</a>';
					}					?>

				</li>
			</ul>

		</div>		<?php
		
		return ob_get_clean();
	}

	public function user_not_logged_in() {
		EPKB_Utilities::ajax_show_error_die( '<p>' . esc_html__( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', esc_html__( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}