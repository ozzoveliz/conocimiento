<?php
/**
 * App Config.
 *
 * @package embed-outlook-teams-calendar-events/API
 */

namespace MOTCE\Controller;

use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to handle form actions specific to Manage application tab.
 */
class MOTCE_App_Config {

	/**
	 * Holds the MOTCE_App_Config class instance.
	 *
	 * @var MOTCE_App_Config
	 */
	private static $instance;

	/**
	 * Variable to store $_POST array values.
	 *
	 * @var array
	 */
	private $post;

	/**
	 * Object instance(MOTCE_App_Config) getter method.
	 *
	 * @return MOTCE_App_Config
	 */
	public static function get_controller() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to execute form actions based on the option value recieved in post request.
	 *
	 * @param array $option This contains option value for admin controller to carry out respective actions.
	 * @param array $config This contains all required $_POST keys and their value array.
	 * @return void
	 */
	public function save_settings( $option, $config ) {

		if ( ! isset( $option ) ) {
			return;
		}

		switch ( $option ) {
			case 'motce_client_config_option':
				$this->save_client_config( $config );
				break;
		}
	}

	/**
	 * Function to santize POST data and to check empty or null values.
	 *
	 * @param array $config This contains all required $_POST keys and their value array.
	 * @return boolean
	 */
	private function check_for_empty_or_null( $config ) {
		foreach ( MOTCE_Plugin_Constants::APP_CONFIG_KEYS as $key ) {
			if ( ! isset( $config[ $key ] ) || empty( $config[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Function to save azure client configurations.
	 *
	 * @param array $config This contains all required $_POST keys and their value array.
	 * @return void
	 */
	private function save_client_config( $config ) {
		if ( ! $this->check_for_empty_or_null( $config ) ) {
			MOTCE_WP_Wrapper::show_error_notice( esc_html( MOTCE_Plugin_Constants::INCORRECT_APP_CONFIGURATION_INPUT_MESSAGE ) );
			return;
		}
		$config['client_secret'] = MOTCE_WP_Wrapper::encrypt_data( $config['client_secret'], hash( 'sha256', $config['client_id'] ) );
		MOTCE_WP_Wrapper::set_option( MOTCE_Plugin_Constants::APP_CONFIG, $config );

		MOTCE_WP_Wrapper::set_option( MOTCE_Plugin_Constants::UPN, $config['upn_id'] );

		MOTCE_WP_Wrapper::show_success_notice( esc_html( MOTCE_Plugin_Constants::APP_CONFIG_SUCCESS_MESSAGE ) );
	}
}
