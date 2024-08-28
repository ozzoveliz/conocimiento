<?php
/**
 * Includes all helper functions.
 *
 * @package embed-outlook-teams-calendar-events/Wrappers
 */

namespace MOTCE\Wrappers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle all utility/helper functions.
 */
class MOTCE_WP_Wrapper {

	/**
	 * Holds the MOTCE_WP_Wrapper class instance.
	 *
	 * @var MOTCE_WP_Wrapper
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_WP_Wrapper) getter method.
	 *
	 * @return MOTCE_WP_Wrapper
	 */
	public static function get_wrapper() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to set options in option table.
	 *
	 * @param string               $key key to set an option.
	 * @param string|array|boolean $value value to be set for given key.
	 * @return void
	 */
	public static function set_option( $key, $value ) {
		update_option( 'motce_' . $key, $value );
	}

	/**
	 * Function to get option value from option table.
	 *
	 * @param string $key option table key.
	 * @return string|array|boolean
	 */
	public static function get_option( $key ) {
		return get_option( 'motce_' . $key );
	}

	/**
	 * Function to delete option from option table with specific key.
	 *
	 * @param string $key option table key.
	 * @return void
	 */
	public static function delete_option( $key ) {
		delete_option( 'motce_' . $key );
	}

	/**
	 * Function to display error notice in the plugin.
	 *
	 * @param string $message - error notice message.
	 * @return void
	 */
	public static function show_error_notice( $message ) {
		$allowed_html      = array(
			'a'      => array(
				'href' => array(),
			),
			'b'      => array(),
			'strong' => array(),
			'i'      => array(),
			'em'     => array(),
		);
		$sanitized_message = wp_kses( $message, $allowed_html );
		self::set_option( MOTCE_Plugin_Constants::NOTICE_MESSAGE, $sanitized_message );
		$hook_name = 'admin_notices';
		remove_action( $hook_name, array( self::get_wrapper(), 'load_success_notice' ) );
		add_action( $hook_name, array( self::get_wrapper(), 'load_error_notice' ) );
	}

	/**
	 * Function to display success notice in the plugin.
	 *
	 * @param string $message - success notice message.
	 * @return void
	 */
	public static function show_success_notice( $message ) {
		self::set_option( MOTCE_Plugin_Constants::NOTICE_MESSAGE, $message );
		$hook_name = 'admin_notices';
		remove_action( $hook_name, array( self::get_wrapper(), 'load_error_notice' ) );
		add_action( $hook_name, array( self::get_wrapper(), 'load_success_notice' ) );
	}

	/**
	 * Function attached to admin notices hook to show success notice.
	 *
	 * @return void
	 */
	public function load_success_notice() {
		$class   = 'updated';
		$message = self::get_option( MOTCE_Plugin_Constants::NOTICE_MESSAGE );
		echo "<div style='margin:5px 0' class='" . esc_attr( sanitize_text_field( $class ) ) . "'> <p>" . esc_attr( sanitize_text_field( $message ) ) . '</p></div>';
	}

	/**
	 * Function attached to admin notices hook to show error notice.
	 *
	 * @return void
	 */
	public function load_error_notice() {
		$class   = 'error';
		$message = self::get_option( MOTCE_Plugin_Constants::NOTICE_MESSAGE );
		echo "<div style='margin:5px 0' class='" . esc_attr( $class ) . "'> <p>" . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Function to encrypt data using key.
	 *
	 * @param string $data the key=value pairs separated with &.
	 * @param string $key key to decrypt data.
	 * @return string
	 */
	public static function encrypt_data( $data, $key ) {
		$key       = openssl_digest( $key, 'sha256' );
		$method    = 'aes-128-ecb';
		$str_crypt = openssl_encrypt( $data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING );
		return base64_encode( $str_crypt ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- base64 encoded encrypted data before storing in database.
	}


	/**
	 * Function to decrypt data using key.
	 *
	 * @param string $data crypt response from Sagepay.
	 * @param string $key key to decrypt data.
	 * @return string|false
	 */
	public static function decrypt_data( $data, $key ) {
		$str_in  = base64_decode( $data ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- base64 decoded data while fetching from database.
		$key     = openssl_digest( $key, 'sha256' );
		$method  = 'AES-128-ECB';
		$iv_size = openssl_cipher_iv_length( $method );
		$iv      = substr( $str_in, 0, $iv_size );
		$data    = substr( $str_in, $iv_size );
		$clear   = openssl_decrypt( $data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING, $iv );

		return $clear;
	}

	/**
	 * Function to get path of particular image source.
	 *
	 * @param string $image_name file name of image.
	 * @return string
	 */
	public static function get_image_src( $image_name ) {
		return plugin_dir_url( MOTCE_PLUGIN_FILE ) . 'images/' . $image_name;
	}

	/**
	 * Method to make a remote call using wp_remote_post or wp_remote_get.
	 *
	 * @param string $url The URL to make the request to.
	 * @param string $field_string The Fields to be sent within the request.
	 * @param string $headers The headers to be sent within the request.
	 * @param array  $args The arguments for the request.
	 * @param string $method The HTTP method to use (default is POST).
	 * @return array|WP_Error The response on success, WP_Error object on failure.
	 */
	public static function motce_remote_request( $url, $field_string, $headers, $args = array(), $method = 'POST' ) {
		$default_headers = array(
			'Content-Type'  => 'application/json',
			'charset'       => 'UTF-8',
			'Authorization' => 'Basic',
		);

		$header = ! empty( $headers ) ? $headers : $default_headers;

		$default_args = array(
			'method'      => $method,
			'body'        => $field_string,
			'timeout'     => 10,
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => $header,
		);

		$args = ! empty( $args ) ? $args : $default_args;

		if ( 'POST' === $method ) {
			$response = wp_remote_post( $url, $args );
		} elseif ( 'GET' === $method ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$response = wp_remote_request( $url, $args );
		}

		if ( ! is_wp_error( $response ) ) {
			return $response;
		} else {
			self::show_error_notice( 'Unable to connect to the Internet. Please try again.' );
			return null;
		}

		return $response;
	}
}
