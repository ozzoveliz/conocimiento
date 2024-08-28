<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display add-on plugins and licenses
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_Add_Ons_Page {

	public function __construct() {
		add_filter( ASEA_KB_Core::ASEA_KB_LICENSE_FIELD, array( $this, 'display_license_status_update') );
		add_action( 'wp_ajax_asea_handle_license_request', array( $this, 'handle_license_request' ) );
		add_action( 'wp_ajax_nopriv_asea_handle_license_request', array( 'ASEA_Utilities', 'user_not_logged_in' ) );
	}

	/**
	 * Before we show license check its status.
	 *
	 * @param $output
	 * @return string
	 */
	public function display_license_status_update( $output ) {

		// only administrators can handle licenses
		if ( ! current_user_can('manage_options') ) {
			return $output;
		}

		$output .= '<li class="ekb-add-on">';
		$output .= '<div class="ekb-add-on-title">'
					.'<h2>'. ASEA_License_Handler::PLUGIN_NAME . '</h2>' .
			       '</div>'.
		           '<div class="ekb-license-check" id="asea_license_check">' . __( 'Updating license status. Please wait.', 'echo-knowledge-base' ) . '</div>' .
		           '<div id="asea-ajax-in-progress" style="display:none;">' . esc_html__( 'Updating license...', 'echo-knowledge-base' ) . '</div>';
		$output .= '</li>';

		return $output;
	}

	/**
	 * Display license field
	 */
	public function handle_license_request() {

		// ensure user has correct permission
		if ( ! current_user_can( 'manage_options' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'You do not have permission to view license info.', 'echo-knowledge-base' ) );
		}

		$asea_instance = asea_get_instance();
		if ( empty($asea_instance) || ! $asea_instance instanceof Echo_Advanced_Search ) {
			ASEA_Utilities::ajax_show_error_die( __( 'Failed to initialize (M99).', 'echo-knowledge-base' ) );
		}

		$command = ASEA_Utilities::post('command');
		$command = ! empty($command) && in_array( $command, array( 'get_license_info', 'save' ) ) ? $command : '';
		if ( empty($command) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'Unknown licensing command: ', 'echo-knowledge-base' ) . $command );
		}

		if ( empty( $asea_instance->license_handler ) ) {
			ASEA_Utilities::ajax_show_error_die(  __( 'Failed to initialize (L99)', 'echo-knowledge-base' ) );
		}

		$license_handler = $asea_instance->license_handler;

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
	 * @param ASEA_License_Handler $license_handler
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
			ASEA_Logging::add_log("Could not contact licensing server.", ( isset($add_on_data->license) ? $add_on_data->license : '' ) );
			$cached_info = $license_handler->get_cached_info();
			$latest_add_on_version = $cached_info['latest_add_on_version'];
		}

		$license_state = isset($license_data->license) ? $license_data->license : '';
		$expiry_date = isset($license_data->expires) ? $license_data->expires : '';
		$license_error = isset($license_data->error) ? $license_data->error : '';

		// CONNECTION ERROR - if we can't connect show the last status
		if ( ! empty($license_error) && $license_handler->is_connection_error( $license_error ) ) {
			ASEA_Logging::add_log("Could not contact licensing server.", ( isset($license_data->license) ? $license_data->license : '' ) );
			$cached_info = $license_handler->get_cached_info();
			$status_msg = $cached_info['status_msg'] . '<br>
            <div class="eckb-tooltip-container eckb-tip-error">
                <div class="eckb-tooltip-button"> ' . __( 'Server Error: Click here for more info', 'echo-knowledge-base' ) . '</div>
                <div class="eckb-tooltip-contents eckb-tooltip-top">
                    <h4 class="eckb-tooltip-heading">' . __( 'Server Errors:', 'echo-knowledge-base' ) . '</h4>
                    ' . $license_state . '
                    <hr>
                    <p>' . __( 'Please try again later. If the problem persists first check our license FAQ for a solution here', 'echo-knowledge-base' ) . ' <a href="https://www.echoknowledgebase.com/documentation/my-account-and-license-faqs#license-faqs" target="_blank" rel="noopener">' . __( 'documentation', 'echo-knowledge-base' ) . '</a>
                    ' . __( 'If submitting a support ticket, please copy the above text and include it in the message.', 'echo-knowledge-base' ) . '</p>
                </div>
            </div>';
			return $this->license_fields( $license_state, $status_msg, $cached_info['expiry_date'], $cached_info['latest_add_on_version'], $license_key );
		}

		// License is active and valid
		if ( $license_state == 'valid' ) {
			$status_msg = __( 'License is valid and active.', 'echo-knowledge-base' );

		// license is deactivated
		} else if ( $license_state == 'deactivated' ) {
			$status_msg = __( 'License ', 'echo-knowledge-base' ) . $license_key . __( ' has been removed and deactivated for this website.', 'echo-knowledge-base' );

		// License is empty OR invalid (inactive, expired, wrong product etc.)
		} else {
			$status_msg = $license_handler->retrieve_status_message( $license_data, $license_key );
		}

		// only show expiry date when license is or was valid
		if ( ! empty($license_error) && ! in_array($license_error, array('site_inactive', 'expired', 'no_activations_left', 'disabled', 'license_not_activable')) ) {
			$expiry_date = '';
		}

		// store last known status
		ASEA_Utilities::save_wp_option( ASEA_License_Handler::ASEA_LICENSE_STATE, array( $status_msg, $license_state, $latest_add_on_version, $expiry_date ), true );

		ASEA_Utilities::save_wp_option('asea_last_license_check', array(), true);
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
		// check if add-on is dormant and display message
		if ( asea_get_instance()->is_dormant ) {
			$warning_msg = __( 'The add-on will not run until both this add-on and KB core have compatible versions. See the message at the top of the page for details.', 'echo-knowledge-base' );
		} else if ( $status != 'valid' ) {
			$warning_msg = __( 'Your license has to be both <strong>active</strong> and <strong>valid</strong> in order to receive <strong>add-on fixes, new features and support</strong>.', 'echo-knowledge-base' );
		}		?>

		<p class="ekb-error"><?php echo empty($warning_msg) ? '' : '<span>' . __( 'Warning:', 'echo-knowledge-base' ) . ' </span>' . wp_kses_post( $warning_msg ); ?></p>

		<!-- LICENSE KEY -->
		<div class="ekb-license-key">
			<ul>
				<li><label><?php _e( 'License Key:', 'echo-knowledge-base' ); ?></label></li>
				<li><input type="text" name="<?php echo ASEA_License_Handler::LICENSE_KEY_OPTION_ID; ?>" id="<?php echo ASEA_License_Handler::LICENSE_KEY_OPTION_ID; ?>"
				           value="<?php echo esc_attr( $license_key ); ?>" placeholder="<?php _e( 'Enter your license key', 'echo-knowledge-base' ); ?>"
				           data-status="<?php echo esc_attr($status); ?>" data-current-license-key="<?php echo esc_attr($license_key); ?>" maxlength="50"/></li>
				<li><span class="asea_license_action"><input id="asea_save_btn" class="primary-btn" type="button" name="asea_save_btn" value="<?php echo  __( 'Save', 'echo-knowledge-base' ); ?>"/>
						<input type="hidden" id="_wpnonce_asea_license_key" name="_wpnonce_asea_license_key" value="<?php echo wp_create_nonce( "_wpnonce_asea_license_key" ); ?>"/></span></li>
			</ul>
		</div>

		<!-- LICENSE STATUS -->
		<div class="ekb-license-status">
			<ul>
				<li>
					<span class="ekb-status-icon <?php echo $status == 'valid' ? 'ep_font_icon_checkmark' : 'ep_font_icon_error_circle'; ?>"></span>
				</li>
				<li><strong><?php _e( 'Status:', 'echo-knowledge-base' ); ?></strong></li>
				<li><span id="asea_license_key_status"><?php echo wp_kses_post( $status_msg ); ?></span></li>
			</ul>
		</div>

		<!-- EXPIRES ON -->
		<div class="ekb-license-expiry">			<?php

			$expiry_icon = '';
			$expiry_msg = __( 'No valid license key detected', 'echo-knowledge-base' );
			if ( ! empty($expiry_date) ) {
				$expiry_msg = asea_get_instance()->license_handler->get_expiry_warning( $expiry_date );
				$expiry_icon = empty($expiry_msg) ? 'ep_font_icon_checkmark' : 'ep_font_icon_error_circle';
			}
			$expiry_msg = empty($expiry_msg) ? ASEA_Utilities::get_formatted_datetime_string( $expiry_date ) : $expiry_msg;			?>

			<ul>
				<li><span class="ekb-status-icon <?php echo $expiry_icon; ?>"></span></li>
				<li><strong><?php _e( 'Expires on:', 'echo-knowledge-base' ); ?></strong></li>
				<li><?php echo wp_kses_post( $expiry_msg ); ?></li>
			</ul>
		</div>

		<!-- ADD-ON VERSION -->
		<div class="ekb-license-version">			<?php

			$new_version_available = version_compare( Echo_Advanced_Search::$version, $latest_add_on_version, "<" );
			$icon = $new_version_available ? 'ep_font_icon_error_circle' : 'ep_font_icon_checkmark';			?>

			<ul>
				<li><span class="ekb-status-icon <?php echo $icon; ?>"></span></li>
				<li><strong><?php _e( 'Add-on Version:', 'echo-knowledge-base' ); ?> </strong></li>
				<li><?php _e( 'Installed:', 'echo-knowledge-base' ); ?> <?php echo Echo_Advanced_Search::$version; ?></li>
				<li><?php _e( 'Latest:', 'echo-knowledge-base' ); ?> <?php echo esc_html($latest_add_on_version); ?></li>
				<li>					<?php

					// show update link only if license is valid
					if ( $new_version_available && $status == 'valid' ) {
						$name = plugin_basename( Echo_Advanced_Search::$plugin_file );
						echo '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $name, 'upgrade-plugin_' . $name ) ) .'" target="_blank">' . __( 'Update Plugin', 'echo-knowledge-base' ) . '</a>';
					}					?>

				</li>
			</ul>

		</div>		<?php
		
		return ob_get_clean();
	}
}