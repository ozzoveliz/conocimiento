<?php  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// uncomment this line for testing
//set_site_transient( 'update_plugins', null );

/**
 * Manage plugin updates and licensing using license API.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 * Adapted from code in EDD (Copyright (c) 2015, Pippin Williamson) and WP.
 *
 **/
class AMGP_License_Handler {

	private $file;
	private $license_key;
	private $version;
	private $author = 'Echo Plugins';
	private $license_server_url = self::LICENSE_SERVER_URL;		// site with registered EDD plugin to handle license

	const PLUGIN_NAME           = 'KB Groups';
	const LICENSE_KEY_OPTION_ID = 'amgp_license_key';
	const AMGP_LICENSE_STATE    = 'amgp_license_state';
	const LICENSE_SERVER_URL    = 'https://www.echoknowledgebase.com/';

	const MISSING_DATA = 'missing_data';
	const CANNOT_CONNECT = 'cannot_connect';

	function __construct( $_file, $_version ) {
		$this->file        = $_file;
		$this->version     = $_version;
		$this->license_key = AMGP_Utilities::get_wp_option( self::LICENSE_KEY_OPTION_ID, '' );
		$this->setup_hooks();
		$this->auto_updater();
	}

	public function get_license_key() {
		return $this->license_key;
	}

	/**
	 * Setup hooks for various license functions
	 */
	private function setup_hooks() {
		add_filter( AMGP_KB_Core::AMGP_KB_ADD_ON_LICENSE_MSG, array( $this, 'get_license_status_short') );
	}

	/****************************************************************************
	 *
	 *                           Add-on Update
	 *
	 ****************************************************************************/

	/**
	 *  Update the plugin if the license is valid and there is a new version available
	 */
	private function auto_updater() {

		// setup the updater
		if ( ! class_exists( 'AMGP_EDD_SL_Plugin_Updater' ) ) {
			require_once 'class-amgp-edd-sl-plugin-updater.php';
		}

		// data to send in our API request
		/* $api_params = array(
			'edd_action'	=> 'get_version',
			'license' 	    => '',
			'item_name'	    => 'Article Features'
		);

		// Call the API
		$get_url = esc_url_raw( add_query_arg( $api_params, self::LICENSE_SERVER_URL ) );
		$get_args = array( 'timeout' => 60, 'body' => $api_params, 'sslverify' => false );
		$response = wp_remote_get( $get_url, $get_args ); */

		new AMGP_EDD_SL_Plugin_Updater(
			$this->license_server_url,
			$this->file,
			array(
				'version' 	=> $this->version,
				'license' 	=> $this->license_key,
				'item_name'	=> self::PLUGIN_NAME,
				'author' 	=> $this->author
			)
		);
	}


	/****************************************************************************
	 *
	 *                           Licensing
	 *
	 ****************************************************************************/

	/**
	 * Invoked through filter by Overview tab and top page.
	 *
	 * @param string $output
	 * @return string
	 * @throws Exception
	 */
	public function get_license_status_short( $output ) {

		$output = is_array($output) ? $output : array();

		$stored_license_data = $this->get_license_state();
		if ( empty($stored_license_data) ) {
			$output[self::PLUGIN_NAME] = __( 'Could not retrieve license state', 'echo-knowledge-base' );
			return $output;
		}

		$output_status = array();

		// 1. ADD-ON VERSION
		if ( ! empty($stored_license_data['new_version']) ) {
			$output_status[] = sprintf( __( 'Please update %s to its latest version %s' , 'echo-knowledge-base' ), '<strong>' . self::PLUGIN_NAME . '</strong>', $stored_license_data['new_version'] );
		}

		// 2. EXPIRATION
		if ( ! empty($stored_license_data['expiry_msg']) ) {
			$output_status[] = $stored_license_data['expiry_msg'];
		}

		// 3. LICENSE STATE
		if ( ! empty($stored_license_data['error']) ) {
			$output_status[]= $stored_license_data['error'];
		}

		// 4. WARNING MESSAGE
		if ( ! empty($stored_license_data['warning']) ) {
			$output_status[] = $stored_license_data['warning'];
		}

		if ( empty($output_status) ) {
			return $output;
		} else if ( count($output_status) == 1 ) {
			$output[self::PLUGIN_NAME] = reset($output_status);
			return $output;
		}

		$list = '<ul>';
		foreach( $output_status as $message ) {
			$list .= "<li>$message</li>";
		}
		$output[self::PLUGIN_NAME] = $list . '</ul>';

		return $output;
	}

	public function get_cached_info() {
		$default_array = array( __( 'Unknown status.', 'echo-knowledge-base' ), '', Echo_KB_Groups::$version, '' );
		$amgp_license_state = AMGP_Utilities::get_wp_option( self::AMGP_LICENSE_STATE, $default_array, true );
		$status_msg = isset($amgp_license_state[0]) ? $amgp_license_state[0] : __( 'Unknown status.', 'echo-knowledge-base' );
		$license_state = isset($amgp_license_state[1]) ? $amgp_license_state[1] : '';
		$latest_add_on_version = isset($amgp_license_state[2]) ? $amgp_license_state[2] : Echo_KB_Groups::$version;
		$expiry_date = isset($amgp_license_state[3]) ? $amgp_license_state[3] : '';
		return array( 'status_msg' => $status_msg, 'license_state' => $license_state, 'latest_add_on_version' => $latest_add_on_version, 'expiry_date' => $expiry_date );
	}

	/**
	 * KEEP PUBLIC
	 * Handle license commands: SAVE licence
	 */
	public function handle_license_command() {

		// run a quick security check
		if ( ! isset( $_REQUEST['_wpnonce_amgp_license_key'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_amgp_license_key'], '_wpnonce_amgp_license_key' ) ) {
			AMGP_Utilities::ajax_show_error_die( __( 'Security check failed.', 'echo-knowledge-base' ) );
		}

		// need license key to activate/deactivate the license
		$entered_license_key = empty($_REQUEST['amgp_license_key']) ? '' : sanitize_text_field( $_REQUEST['amgp_license_key'] );
		$current_license_key = $this->get_license_key();

		// DEACTIVATE if license field is empty; ACTIVATE otherwise
		$command = empty($entered_license_key) ? 'deactivate' : 'activate';

		// activate or deactivate the license by contacting the license server
		$license_data = $this->contact_license_server( $command . '_license', $entered_license_key );

		// save the license if it is different (new, changed or empty)
		if ( $entered_license_key != $current_license_key ) {
			$this->license_key = $entered_license_key;
			$this->save_license( $entered_license_key );
		}

		return $license_data;
	}

	/**
	 * Save license to the WP database.
	 *
	 * @param $license_key
	 */
	private function save_license( $license_key ) {
		// save license in the database
		$this->license_key = $license_key;
		$result = AMGP_Utilities::save_wp_option( self::LICENSE_KEY_OPTION_ID, $license_key, true );
		if ( is_wp_error( $result ) ) {
			/* @var $result WP_Error */
			$message = $result->get_error_data();
			if ( empty($message) ) {
				AMGP_Utilities::ajax_show_error_die( $result->get_error_message(),  __( 'Could not save settings.', 'echo-knowledge-base' ) );
			}
			AMGP_Utilities::ajax_show_error_die( $this->generate_error_summary( $result->get_error_data() ), __( 'Settings NOT saved due to following problems:', 'echo-knowledge-base' ) );
		}
	}

	/**
	 * Retrieve license status from the server.
	 *
	 * license status:
	 *          'valid'   - license is valid
	 *          'inactive' - license is not currently active
	 *          'disabled' - license is disabled
	 *          'expired' - license expired
	 *          'error'   - error occurred
	 *          'item_name_mismatch' - license name and key are mismatched i.e. this license is for a different product
	 *          'invalid_item_id' and 'site_inactive'
	 *
	 * @return StdClass
	 */
	public function retrieve_license_data() {
		$license_data = $this->contact_license_server( 'check_license', $this->license_key );
		return $license_data;
	}

	/**
	 * Retrieve license information from license server and sanitize the fields
	 *
	 * NOTE: Inactive - means that license is not active on any site ! not just this specific one.
	 *       site_inactive - this particular license does not have the site active
	 *
	 * EDD StdClass: 'check_license':
	 *                    success - true / false (if 'invalid')
	 *                    license - valid / invalid / site_inactive / inactive / expired etc.
	 *                    error   - N/A  ==>  set to "license" if not empty and not valid
	 * 
	 *               'activate_license':
	 *                    success - true / false (if error)
	 *                    license - valid / invalid (if error)
	 *                    error   - expired / revoked / missing etc.
	 *
	 *               'deactivate_license':
	 *                    success - true / false (if 'failed')
	 *                    license - deactivated / failed
	 *                    error   - N/A   ===>  set to "failed_to_deactivate" if success = 'false'
     *
	 *                'get_version':
	 *                    msg - if error occurs it contains the error
	 *                    new_version - new version of the plugin
	 *
	 *               missing or invalid response from the licensing server:
	 *                    success - false
	 *                    license - detail message
	 *                    error   - cannot_connect, missing_data
	 *
	 *  From EDD:
	 *   Once a license has been activated once, it will remain active unless it expires or is manually set to be disabled - even if no sites are currently using it.
	 *        Otherwise, it will remain active to let you know that it has been activated at least once.
     *   Note that when you are deactivating a site, it is the site that is being deactivated, not the license. The license itself is still active - while the site is not.
	 *
	 *
	 * @param $action: activate_license, deactivate_license
	 * @param $license_key
	 * @return StdClass -> adjust EDD results below such that error has value even for 'check_license' with 'license' not 'valid'
	 *
	 */
	public function contact_license_server( $action, $license_key ) {

		if ( empty($license_key) && $action != 'get_version' ) {
			return $this->set_error_object( 'empty_license', __( 'Please enter your license.', 'echo-knowledge-base' ) );
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'	=> $action,
			'license' 	    => ( $action == 'get_version' ? '' : $license_key ),
			'item_name'	    => self::PLUGIN_NAME, // the name of our product in EDD without url_encode() !
		);

		// Call the API
		$get_url = esc_url_raw( add_query_arg( $api_params, self::LICENSE_SERVER_URL ) );
		$get_args = array( 'timeout' => 60, 'body' => $api_params, 'sslverify' => false );
		$response = wp_remote_post( $get_url, $get_args );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			$error_msg = '<br/>REQUEST: url => ' . $get_url . ', args => ' . AMGP_Utilities::get_variable_string( $get_args );
			$error_msg .= '<br/>RESPONSE: ' . AMGP_Utilities::get_variable_string( $response );
			return $this->set_error_object( self::CANNOT_CONNECT, 'Cannot connect to the licensing server to retrieve the latest information (' .
                                                     $response->get_error_message() . ', ' . $response->get_error_code() . '). info: ' . $error_msg );
		}

		// decode the license data
		$api_data = json_decode( wp_remote_retrieve_body( $response ) );

		// if get_version then exit early
		if ( $action == 'get_version' ) {
			if ( empty($api_data) || ! is_object( $api_data ) || empty($api_data->new_version) ) {
				$error_msg = '<br/>REQUEST: url => ' . $get_url . ', args => ' . AMGP_Utilities::get_variable_string( $get_args );
				$error_msg .= '<br/>RESPONSE: ' . AMGP_Utilities::get_variable_string( $response );
				return $this->set_error_object( 'invalid_data', 'Did not receive valid data from get_version. info: ' . $error_msg );
			}

			$api_data->new_version = sanitize_text_field( $api_data->new_version );
			return $api_data;
		}

		// if license data is missing it means we didn't get reply back from the licensing server
		if ( empty($api_data) || ! isset($api_data->success) || ! isset($api_data->license) ) {
			$error_msg = '<br/>REQUEST: url => ' . $get_url . ', args => ' . AMGP_Utilities::get_variable_string( $get_args );
			$error_msg .= '<br/>RESPONSE: ' . AMGP_Utilities::get_variable_string( $response );
			return $this->set_error_object( self::MISSING_DATA, 'server_error_data' . $error_msg );
		}

		switch( $action ) {
			case 'check_license':
				$api_data->error = empty($api_data->license) || $api_data->license == 'valid' ? '' : trim($api_data->license);
				break;
			case 'deactivate_license':
				$api_data->error = $api_data->license == 'failed' ? 'failed_to_deactivate' : '';
				break;
			case 'activate_license':
			default:
				break;
		}

		// format and sanitize
		$api_data->error = empty($api_data->error) ? '' : sanitize_text_field( $api_data->error );
		$api_data->success = empty($api_data->success) ? '' : sanitize_text_field( $api_data->success );
		$api_data->license = empty($api_data->license) ? 'unknown' : $api_data->license;
		$api_data->license = sanitize_text_field( $api_data->license );
		$api_data->expires = isset($api_data->expires) ? $api_data->expires : '';
		$api_data->expires = sanitize_text_field( $api_data->expires );
		if ( isset($api_data->max_sites) ) {
			$api_data->max_sites = sanitize_text_field( $api_data->max_sites );
		}

		return $api_data;
	}

	public function is_connection_error( $error ) {
		return $error == self::CANNOT_CONNECT || $error == self::MISSING_DATA;
	}
	
	/**
	 * Create message for user when activating/deactivating key.
	 *
	 * @param $license_data
	 * @param $license_key
	 *
	 * @return string
	 */
	public function retrieve_status_message( $license_data, $license_key ) {

		$visit_account_page = '<a href="https://www.echoknowledgebase.com/your-account" target="_blank" title="' . __( 'visit your account page', 'echo-knowledge-base' ) . '">' . __( 'visit your account page', 'echo-knowledge-base' ) . '</a>';

		$error_code = empty($license_data->error) ? '[unknown error]' : trim($license_data->error);
		switch( $error_code ) {

			case 'empty_license':
				$message = sprintf( __( 'License for %s is missing. Please enter and activate your license.', 'echo-knowledge-base' ), '<strong>' . self::PLUGIN_NAME . '</strong>' );
				break;

			case 'expired' : // 'check_license' and 'activate' action
				$expired_date = empty($license_data->expires) ? '' : strtotime( $license_data->expires, current_time( 'timestamp' ) );
				$expired_date = empty($expired_date) ? '[unknown]' : date_i18n( get_option( 'date_format' ),  $expired_date);
				$message = sprintf( __( 'Your license key expired on %s. Please <a href="%s" target="_blank" title="Renew your license key">renew your license key</a>.', 'echo-knowledge-base' ),
									$expired_date, 'https://www.echoknowledgebase.com/checkout/?edd_license_key=' . $license_key);
				break;

			case 'missing' :
				$message = sprintf( __( 'Invalid license key. Please %s and verify it.', 'echo-knowledge-base' ), $visit_account_page );
				break;

			case 'site_inactive' :  // 'check_license' action
				$message = sprintf( __( 'This license is not active for this URL. Either activate (save) the license or %s to manage your license key URLs.', 'echo-knowledge-base' ), $visit_account_page );
				break;

			case 'invalid' :        // 'check_license' action
				$message = sprintf( __( 'The entered license is not valid. Please enter a valid license or %s to manage your license key(s).', 'echo-knowledge-base' ), $visit_account_page );
				break;

			case 'item_name_mismatch':  // 'check_license' and 'activate' action
			case 'invalid_item_id':	    // 'check_license' and 'activate' action
				$message = __( 'This license is not for ', 'echo-knowledge-base' ) . self::PLUGIN_NAME;
				break;

			case 'no_activations_left':  // 'activate' action
				$max_sites = isset( $license_data->max_sites) ?  $license_data->max_sites : 0;
				$message = sprintf( __( 'This license key is registered on other %s website%s which is the license limit. In order to register the license on this website you can either: ' .
										'a) increase the number of websites the license can handle OR b) de-register your old website URL and register this ' .
										'website URL. Please %s to make the URL change.', 'echo-knowledge-base' ), $max_sites, ($max_sites == 1 ? '' : 's'), $visit_account_page );
				break;

			case 'revoked':      // 'activate' action
			case 'disabled':	// 'check_license' action
				$message = sprintf(
								__( 'Your license key has been disabled. Please <a href="%s" target="_blank">contact support</a> for more information.', 'echo-knowledge-base' ),
									'https://www.echoknowledgebase.com/contact-us/');
				break;

			case 'license_not_activable':  // 'activate' action
				$message = __( 'The key you entered belongs to a bundle, please use this add-on specific license key.', 'echo-knowledge-base' );
				break;

			case 'failed_to_deactivate':
				$message = __( 'Error occurred (a30)', 'echo-knowledge-base' ) . ' - ' . $error_code;
				break;

			case 'key_mismatch':    // 'activate' action see EDD_Software_Licensing
				$message = __( 'Error occurred (a31)', 'echo-knowledge-base' ) . ' - ' . $error_code;
				break;

			default:
				$message = __( 'Error occurred (a32)', 'echo-knowledge-base' ) . ' - ' . $error_code;
				break;
		}

		return $message;
	}

	public function set_error_object( $error, $license_msg ) {
		$error_object = new StdClass;
		$error_object->success = false;
		$error_object->error = $error;
		$error_object->expires = '';
		$error_object->license = $license_msg;
		return $error_object;
	}

	/**
	 * Return expiry message if date is expired or is about to expire.
	 *
	 * @param $expiry_date
	 * @return string
	 */
	public function get_expiry_warning( $expiry_date ) {

		if ( empty($expiry_date) || $expiry_date == "lifetime" ) {
			return '';
		}

		$difference_in_days = AMGP_Utilities::get_days_since( '', $expiry_date );
		if ( $difference_in_days > 30 ) {
			return '';
		}

		$msg = __( 'Please', 'echo-knowledge-base' ) .' <a href="https://www.echoknowledgebase.com/checkout/?edd_license_key=' . esc_attr($this->license_key) .
		        '" target="_blank" title="' . __( 'renew your license key', 'echo-knowledge-base' ) . '">' . __( 'renew your license key', 'echo-knowledge-base' ) . '</a>.';

		if ( $difference_in_days > 0 ) {
			return sprintf( __( 'Your license will expire in %s days.', 'echo-knowledge-base' ), $difference_in_days ) . ' ' . $msg;
		} else {
			return __( 'Your license ', 'echo-knowledge-base' ) . ( $difference_in_days < 0 ? __( 'expired ', 'echo-knowledge-base' ) . abs($difference_in_days) .
					__( ' days ago. ', 'echo-knowledge-base' ) : __( ' will expire today. ', 'echo-knowledge-base' ) . $msg ) ;
		}
	}

	private function generate_error_summary( $errors ) {

		if ( empty( $errors ) || ! is_array( $errors )) {
			return __( 'unknown error (334)', 'echo-knowledge-base' );
		}

		$output = '<ol>';
		foreach( $errors as $error ) {
			$output .= '<li>' . wp_kses( $error, array('strong') ) . '</li>';
		}
		$output .= '</ol>';

		return $output;
	}

	/**
	 * Every week get license status. Every 4 weeks report ongoing connectivity issues with the license server.
	 *
	 * @param bool $force_refresh
	 *
	 * @return bool
	 */
	public function get_license_state( $force_refresh=false ) {

		// get our latest stored values
		$stored_license_data = AMGP_Utilities::get_wp_option('amgp_last_license_check', array(), true, true);
		if ( is_wp_error($stored_license_data) ) {
			AMGP_Logging::add_log("Could not retrieve license data option.");
			return null;
		}

		// check only once a week unless forced
		if ( ! empty($stored_license_data) && ! $force_refresh && ! empty($stored_license_data['last_check_dt'])
		     && date_diff($stored_license_data['last_check_dt'], new DateTime())->days < 15 ) {
			return $stored_license_data;
		}

		// refresh data
		$month_ago = new DateTime();
		$month_ago->modify('-30 days');
		$stored_license_data['name'] = self::PLUGIN_NAME;
		$stored_license_data['last_check_dt'] = new DateTime();
		$stored_license_data['last_connection_error'] = empty($stored_license_data['last_connection_error']) ? null : $stored_license_data['last_connection_error'];
		$stored_license_data['new_version'] = empty($stored_license_data['new_version']) ? null : $stored_license_data['new_version'];
		$stored_license_data['expiry_msg'] = empty($stored_license_data['expiry_msg']) ? '' : $stored_license_data['expiry_msg'];
		$stored_license_data['error'] = empty($stored_license_data['error']) ? '' : $stored_license_data['error'];
		$stored_license_data['warning'] = empty($stored_license_data['warning']) ? '' : $stored_license_data['warning'];

		// retrieve license data
		$license_data = $this->retrieve_license_data();

		$license_state = isset($license_data->license) ? $license_data->license : '';
		$expiry_date = isset($license_data->expires) ? $license_data->expires : '';
		$license_error = isset($license_data->error) ? $license_data->error : '';
		$licenses_tab_url = admin_url( 'edit.php?post_type=' . AMGP_KB_Handler::get_post_type( AMGP_KB_Core::DEFAULT_KB_ID ) . '&page=ep' . 'kb-add-ons&ep' . 'kb-tab=licenses' );

		// CONNECTION ERROR - if we can't connect show the last status
		if ( ! empty($license_error) && $this->is_connection_error( $license_error ) ) {

			// wait 4 weeks before reporting connectivity issue
			if ( ! empty($stored_license_data['last_connection_error']) && AMGP_Utilities::get_days_since( $stored_license_data['last_connection_error'] ) > 30 ) {
				$stored_license_data['error'] = sprintf( __( 'Could not contact licensing server.<a href="%s" target="_blank">Check your license status here.</a>.', 'echo-knowledge-base' ), $licenses_tab_url );
				return $stored_license_data;
			}

			return $stored_license_data;
		}

		$stored_license_data['last_connection_error'] = null;

		$license_key = $this->get_license_key();

		// 1. ADD-ON VERSION - find the latest version of this add-on
		$add_on_data = $this->contact_license_server( 'get_version', $license_key );
		if ( ! empty($add_on_data) &&  is_object( $add_on_data ) && ! empty( $add_on_data->new_version ) ) {
			$latest_add_on_version = $add_on_data->new_version;
		} else {
			//AMGP_Logging::add_log("Could not contact licensing server.", ( isset($add_on_data->license) ? $add_on_data->license : '' ) );
			$cached_info = $this->get_cached_info();
			$latest_add_on_version = $cached_info['latest_add_on_version'];
		}
		$stored_license_data['new_version'] = version_compare( Echo_KB_Groups::$version, $latest_add_on_version, "<" ) == 0 ? null : $latest_add_on_version;

		// 2. EXPIRATION
		$expiry_msg = '';
		// only show expiry date when license is or was valid
		if ( ! empty($license_error) && ! in_array($license_error, array('site_inactive', 'expired', 'no_activations_left', 'disabled', 'license_not_activable')) ) {
			$expiry_date = '';
		} else {
			$expiry_msg = amgp_get_instance()->license_handler->get_expiry_warning( $expiry_date );
		}
		$stored_license_data['expiry_msg'] = $expiry_msg;

		// 3. LICENSE STATE
		// License is active and valid
		if ( $license_state == 'valid' ) {
			$status_msg = __( 'License is valid and active.', 'echo-knowledge-base' );
			$stored_license_data['error'] = '';
		// license is deactivated
		} else if ( $license_state == 'deactivated' ) {
			$status_msg = sprintf( __( 'License %s has been removed and deactivated for this website.', 'echo-knowledge-base' ), $license_key );
			$stored_license_data['error'] = $status_msg;
		// License is empty OR invalid (inactive, expired, wrong product etc.)
		} else {
			$status_msg = $this->retrieve_status_message( $license_data, $license_key );
			$stored_license_data['error'] = $status_msg;
		}

		// 4. WARNING MESSAGE
		// check if add-on is dormant and display message
		if ( amgp_get_instance()->is_dormant ) {
			$stored_license_data['warning'] = __( 'The add-on will not run until both this add-on and KB core have compatible versions. See the message at the top of the page for details.', 'echo-knowledge-base' );
		} else if ( $license_state != 'valid' ) {
			$stored_license_data['warning'] = __( 'Your license has to be both <strong>active</strong> and <strong>valid</strong> in order to receive <strong>add-on fixes, new features and support</strong>.<br/>', 'echo-knowledge-base' );
		}

		AMGP_Utilities::save_wp_option('amgp_last_license_check', $stored_license_data, true);

		// store last known status
		AMGP_Utilities::save_wp_option( AMGP_License_Handler::AMGP_LICENSE_STATE, array( $status_msg, $license_state, $latest_add_on_version, $expiry_date ), true );

		return $stored_license_data;
	}
}