<?php
/**
 * Mail Config.
 *
 * @package embed-outlook-teams-calendar-events/API
 */

namespace MOTCE\Controller;

use MOTCE\API\MOTCE_Outlook;
use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to handle form actions specific to Mail Sending tab.
 */
class MOTCE_Mail_Controller {

	/**
	 * Holds the MOTCE_Mail_Controller class instance.
	 *
	 * @var MOTCE_Mail_Controller
	 */
	private static $instance;

	/**
	 * Variable to store $_POST array values.
	 *
	 * @var array
	 */
	private $post;

	/**
	 * Object instance(MOTCE_Mail_Controller) getter method.
	 *
	 * @return MOTCE_Mail_Controller
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
			case 'motce_mail_view_option':
				$this->save_mail_config( $config );
				break;

			case 'motce_send_test_mail_option':
				$this->send_test_mail();
				break;
		}
	}

	/**
	 * Function to send test mail.
	 *
	 * @return void
	 */
	private function send_test_mail() {

		$data = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::MAIL_CONFIG );
		if ( empty( $data ) ) {
			return;
		}
		$outlook_client = MOTCE_Outlook::get_client( $data );
		$response       = $outlook_client->motce_send_mail_to_azure_user();

		if ( is_bool( $response['status'] ) && true === $response['status'] ) {
			MOTCE_WP_Wrapper::show_success_notice( esc_html( MOTCE_Plugin_Constants::MAIL_SENT_SUCCESS_MESSAGE ) );
			return;
		}

		$allowed_html = array(
			'a'      => array(
				'href' => array(),
			),
			'b'      => array(),
			'strong' => array(),
			'i'      => array(),
			'em'     => array(),
		);

		$error_description = wp_kses( $response['data']['error_description'], $allowed_html );
		MOTCE_WP_Wrapper::show_error_notice( $error_description );
	}

	/**
	 * Function to save azure client configurations.
	 *
	 * @param array $config This contains all required $_POST keys and their value array.
	 * @return void
	 */
	private function save_mail_config( $config ) {

		if ( empty( $config ) ) {
			MOTCE_WP_Wrapper::show_error_notice( esc_html( MOTCE_Plugin_Constants::INCORRECT_APP_CONFIGURATION_INPUT_MESSAGE ) );
			return;
		}
		MOTCE_WP_Wrapper::set_option( MOTCE_Plugin_Constants::MAIL_CONFIG, $config );

		MOTCE_WP_Wrapper::show_success_notice( esc_html( MOTCE_Plugin_Constants::APP_CONFIG_SUCCESS_MESSAGE ) );
	}
}
