<?php
/**
 * Handles Outlook APIs and Azure Authorization.
 *
 * @package embed-outlook-teams-calendar-events/API
 */

namespace MOTCE\API;

use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle all outlook API endpoints and Azure authorization.
 */
class MOTCE_Outlook {

	/**
	 * Holds the MOTCE_Outlook class instance.
	 *
	 * @var MOTCE_Outlook
	 */
	private static $obj;

	/**
	 * Array of all MOTCE_Outlook endpoints.
	 *
	 * @var array
	 */
	private $endpoints;

	/**
	 * Array of all azure application configurations like client ID & secret.
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Scope value that should be passed while requesting for token.
	 *
	 * @var string
	 */
	private $scope = 'https://graph.microsoft.com/.default';

	/**
	 * It holds access token value.
	 *
	 * @var string
	 */
	private $access_token;

	/**
	 * Holds the MOTCE_Authorization class instance.
	 *
	 * @var MOTCE_Authorization
	 */
	private $handler;

	/**
	 * Array of headers that should be passed in API requests.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Constructor of MOTCE_Outlook class to set app configurations and initialize authorization class.
	 *
	 * @param array $config This contains azure ad client credentials.
	 */
	private function __construct( $config ) {
		$this->config  = $config;
		$this->handler = MOTCE_Authorization::get_controller();
		$this->set_endpoints();
	}

	/**
	 * Object instance(MOTCE_Outlook) getter method.
	 *
	 * @param array $config This contains azure ad client credentials.
	 * @return MOTCE_Outlook
	 */
	public static function get_client( $config ) {
		if ( ! isset( self::$obj ) ) {
			self::$obj = new MOTCE_Outlook( $config );
			self::$obj->set_endpoints();
		}
		return self::$obj;
	}

	/**
	 * It is used to set the endpoints of outlook APIs.
	 *
	 * @return void
	 */
	private function set_endpoints() {
		$app_config                            = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );
		$this->endpoints['authorize']          = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
		$this->endpoints['token']              = 'https://login.microsoftonline.com/' . $app_config['tenant_id'] . '/oauth2/v2.0/token';
		$this->endpoints['users']              = 'https://graph.microsoft.com/v1.0/users/';
		$this->endpoints['calendars']          = 'https://graph.microsoft.com/v1.0/users/%s/calendars';
		$this->endpoints['events']             = 'https://graph.microsoft.com/v1.0/users/%s/calendar/events';
		$this->endpoints['outlook_categories'] = 'https://graph.microsoft.com/v1.0/users/%s/outlook/masterCategories';
	}

	/**
	 * Function to get new acess token.
	 *
	 * @return string|false
	 */
	public function get_new_access_token() {

		$response = $this->handler->get_access_token_using_client_credentials( $this->endpoints, $this->config, $this->scope );

		$this->access_token = $response;

		if ( $response['status'] ) {
			$this->args = array(
				'Authorization' => 'Bearer ' . $this->access_token['data'],
			);

			return $this->access_token['data'];
		}

		return false;
	}

	/**
	 * Function to test connection of configured app.
	 *
	 * @return array
	 */
	public function test_connection() {
		$response = self::get_user_outlook_categories( $this->config['upn_id'] );
		return $response;
	}

	/**
	 * Function to fetch user specific calendar events.
	 *
	 * @param string $upn This contains upn value for user.
	 * @return array
	 */
	public function get_user_calendar_events( $upn ) {
		$access_token = $this->get_new_access_token();
		if ( ! $access_token ) {
			return $this->access_token;
		}

		$this->args = array(
			'Authorization' => 'Bearer ' . $access_token,
			'Prefer'        => 'outlook.timezone = "India Standard Time"',
		);

		$response = $this->handler->get_request( sprintf( $this->endpoints['events'], $upn ), $this->args );

		return $response;
	}

	/**
	 * Function to retrieve user specific categories of outlook mailbox.
	 *
	 * @param string $upn This contains upn value for user.
	 * @return array
	 */
	public function get_user_outlook_categories( $upn ) {

		$access_token = $this->get_new_access_token();
		if ( ! $access_token ) {
			return $this->access_token;
		}

		$this->args = array(
			'Authorization' => 'Bearer ' . $access_token,
		);

		$has_calendar_read_permission      = self::check_application_permission( base64_decode( $access_token ), 'Calendars.Read' ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$has_calendar_readwrite_permission = self::check_application_permission( base64_decode( $access_token ), 'Calendars.ReadWrite' ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( ! $has_calendar_read_permission && ! $has_calendar_readwrite_permission ) {
			return array(
				'status' => false,
				'data'   => array(
					'error'             => 'Insufficient Permissions',
					'error_description' => 'Calendars.Read permission is not present in the Azure AD Application > API Permissions section. Resolve this issue by following the step from <a href="https://plugins.miniorange.com/setup-guide-for-wordpress-outlook-calendar-events-integration#calendar-settings" target="_blank" >here</a>.',
				),
			);
		}

		$response = $this->handler->get_request( sprintf( $this->endpoints['outlook_categories'], $upn ), $this->args );
		return $response;
	}

	/**
	 * Function to send mail to azure user.
	 *
	 * @return boolean
	 */
	public function motce_send_mail_to_azure_user() {

		$mail_app       = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::MAIL_CONFIG );
		$config         = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );
		$access_token   = $this->handler->get_access_token_using_client_credentials( $this->endpoints, $config, $this->scope );
		$graph_mail_url = $this->endpoints['users'] . rawurlencode( $mail_app['mailFrom'] ) . '/sendMail';

		$has_mail_send_permission = self::check_application_permission( base64_decode( $access_token['data'] ), 'Mail.Send' ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( ! $has_mail_send_permission ) {
			return array(
				'status' => false,
				'data'   => array(
					'error'             => 'Insufficient Permissions',
					'error_description' => 'Mail.Send Permission is not present in the Azure AD Application > API Permissions section. Resolve this issue by following the step from <a href="https://plugins.miniorange.com/setup-guide-for-wordpress-outlook-calendar-events-integration#mailbox-settings" target="_blank" >here</a>.',
				),
			);
		}

		$user_object = '{}';
		$user_object = json_decode( $user_object, true );

		$user_object['message'] = array(
			'subject'      => 'Graph Mail Test',
			'body'         => array(
				'contentType' => 'Text',
				'content'     => 'Hi, You are recieving this test email using Microsoft Graph API.',
			),
			'toRecipients' => array(
				array(
					'emailAddress' => array(
						'address' => $mail_app['mailTo'],
					),
				),
			),
		);

		$user_object['saveToSentItems'] = ! empty( $mail_app['saveToSentItems'] ) && 'on' === $mail_app['saveToSentItems'] ? true : false;

		$headers = array(
			'Authorization' => 'Bearer ' . $access_token['data'],
			'Content-Type'  => 'application/json',
		);

		$body = wp_json_encode( $user_object );

		$args = array(
			'body'    => $body,
			'headers' => $headers,
		);

		$response = MOTCE_Authorization::post_request( $graph_mail_url, $args );
		return $response;
	}

	/**
	 * Function to extract json parts.
	 *
	 * @param string $input JSON Array.
	 * @return array
	 */
	public function extract_json_parts( $input ) {
		preg_match_all( '/{.*?}/', $input, $matches );
		return $matches[0];
	}

	/**
	 * Function to check Mail.Send Permission.
	 *
	 * @param string $json_string json string to check.
	 * @param string $permission_to_check permission string to check.
	 * @return bool
	 */
	public function check_application_permission( $json_string, $permission_to_check ) {
		$json_parts = self::extract_json_parts( $json_string );

		if ( count( $json_parts ) < 2 ) {
			return false;
		}

		$payload = json_decode( $json_parts[1], true );

		if ( isset( $payload['roles'] ) && is_array( $payload['roles'] ) ) {
			return in_array( $permission_to_check, $payload['roles'], true );
		}

		return false;
	}
}
