<?php
/**
 * Handles Token Authorization.
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
 * Class to handle token authorization and API endpoints' requests.
 */
class MOTCE_Authorization {
	/**
	 * Holds the MOTCE_Authorization class instance.
	 *
	 * @var MOTCE_Authorization
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Authorization) getter method.
	 *
	 * @return MOTCE_Authorization
	 */
	public static function get_controller() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to get access token using client credentials grant type.
	 *
	 * @param array  $endpoints This holds array of all the endpoints of Outlook REST APIs.
	 * @param array  $config This holds array of azure application client credentials.
	 * @param string $scope This is vaue of scope to be passed in token endpoint.
	 * @return array
	 */
	public function get_access_token_using_client_credentials( $endpoints, $config, $scope ) {

		$client_secret = MOTCE_WP_Wrapper::decrypt_data( $config['client_secret'], hash( 'sha256', $config['client_id'] ) );

		$args = array(
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_secret' => $client_secret,
				'client_id'     => $config['client_id'],
				'scope'         => $scope,
			),
			'headers' => array(
				'Content-type' => 'application/x-www-form-urlencoded',
			),
		);

		$response = wp_remote_post( esc_url_raw( $endpoints['token'] ), $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'status' => false,
				'data'   => MOTCE_Plugin_Constants::INTERNET_CONNECTION_ERROR_MESSAGE,
			);
		} elseif ( isset( $response['body'] ) ) {
			$body = json_decode( $response['body'], true );
			if ( isset( $body['access_token'] ) ) {
				return array(
					'status' => true,
					'data'   => $body['access_token'],
				);
			} elseif ( isset( $body['error'] ) ) {
				return array(
					'status' => false,
					'data'   => $body,
				);
			}
		}

		return array(
			'status' => false,
			'data'   => MOTCE_Plugin_Constants::INVALID_PLUGIN_CONFIGURATIONS_MESSAGE,
		);
	}

	/**
	 * Function to execute API calls using GET method.
	 *
	 * @param string $url This contains api endpoint where GET method should be carried out.
	 * @param array  $headers This contains array of headers that to be passed in API call.
	 * @return array
	 */
	public function get_request( $url, $headers ) {
		$args     = array(
			'headers' => $headers,
		);
		$response = wp_remote_get( esc_url_raw( $url ), $args );
		if ( is_wp_error( $response ) ) {
			return array(
				'status' => false,
				'data'   => MOTCE_Plugin_Constants::INTERNET_CONNECTION_ERROR_MESSAGE,
			);
		} elseif ( is_array( $response ) && isset( $response['body'] ) ) {
				$body = json_decode( $response['body'], true );

			if ( empty( $body ) ) {
				return array(
					'status' => false,
					'data'   => MOTCE_Plugin_Constants::UNAUTHORIZED_OPERATION_MESSAGE,
				);
			} elseif ( isset( $body['error'] ) ) {
				return array(
					'status' => false,
					'data'   => array(
						'error'             => $body['error']['code'],
						'error_description' => $body['error']['message'],
					),
				);
			}

			return array(
				'status' => true,
				'data'   => $body,
			);
		}
	}

	/**
	 * Function to execute API calls using POST method.
	 *
	 * @param string $url This contains API endpoint where POST method should be carried out.
	 * @param array  $args This contains array of args that to be passed in API call.
	 * @return array
	 */
	public static function post_request( $url, $args ) {
		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			return array(
				'status' => false,
				'data'   => MOTCE_Plugin_Constants::INTERNET_CONNECTION_ERROR_MESSAGE,
			);
		} elseif ( is_array( $response ) && isset( $response['body'] ) ) {
			$body          = json_decode( $response['body'], true );
			$response_code = $response['response']['code'];

			if ( in_array( $response_code, range( 200, 202 ), true ) ) {
				return array(
					'status' => true,
					'data'   => empty( $body ) ? array() : $body,
				);
			} elseif ( in_array( $response_code, range( 400, 404 ), true ) ) {
				if ( empty( $body ) ) {
					return array(
						'status' => false,
						'data'   => MOTCE_Plugin_Constants::UNAUTHORIZED_OPERATION_MESSAGE,
					);
				} elseif ( isset( $body['error'] ) ) {
					return array(
						'status' => false,
						'data'   => array(
							'error'             => $body['error']['code'],
							'error_description' => $body['error']['message'],
						),
					);
				}
			}
			return array(
				'status' => false,
				'data'   => MOTCE_Plugin_Constants::UNAUTHORIZED_OPERATION_MESSAGE,
			);
		}
	}
}
