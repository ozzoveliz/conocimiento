<?php
/**
 * Handles Customer APIs.
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
 * This library is miniOrange Authentication Service.
 *
 * Contains Request Calls to Customer service.
 */
class MOTCE_Customer {

	/**
	 * Stores the email of the customer.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * Stores the phone number of customer.
	 *
	 * @var string
	 */
	public $phone;

	/*
	 * * Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
	 * * We need the default value for creating the first time,
	 * * As we don't have the Default keys available before registering the user to our server.
	 * * This default values are only required for sending an One Time Passcode at the user provided email address.
	 */
	/**
	 * Holds the default customer key.
	 *
	 * @var string
	 */
	private $default_customer_key = '16555';

	/**
	 * Holds the default api key.
	 *
	 * @var string
	 */
	private $default_api_key = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';

	/**
	 * Function to send the email alert as demo request.
	 *
	 * @param string  $email This contains email of the customer.
	 * @param string  $phone This contains phone number of the customer.
	 * @param string  $message This contains the message written by the customer.
	 * @param boolean $demo_request This is the boolean value of whether this is a demo request or not.
	 * @return string
	 */
	public function motce_send_email_alert( $email, $phone, $message, $demo_request = false ) {
		$customer_key = $this->default_customer_key;
		$api_key      = $this->default_api_key;

		$current_time_in_millis = $this->motce_get_timestamp();
		$current_time_in_millis = number_format( $current_time_in_millis, 0, '', '' );
		$string_to_hash         = $customer_key . $current_time_in_millis . $api_key;
		$hash_value             = hash( 'sha512', $string_to_hash );
		$subject                = 'Feedback: Embed Outlook Teams Calendar Events';
		if ( $demo_request ) {
			$subject = 'DEMO REQUEST: Embed Outlook Teams Calendar Events';
		}
		global $user;
		$user = wp_get_current_user();

		$query = '[Embed Outlook Teams Calendar Events]: ' . $message;

		$content = '<div >Hello, <br><br>First Name :' . $user->user_firstname . '<br><br>Last  Name :' . $user->user_lastname . '   <br><br>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>Phone Number :' . $phone . '<br><br>Email :<a href="mailto:' . $email . '" target="_blank">' . $email . '</a><br><br>Query :' . $query . '</div>';

		$url        = MOTCE_Plugin_Constants::HOSTNAME . '/moas/api/notify/send';
		$from_email = 'no-reply@xecurify.com';

		$fields        = array(
			'customerKey' => $customer_key,
			'sendEmail'   => true,
			'email'       => array(
				'customerKey' => $customer_key,
				'fromEmail'   => $from_email,
				'bccEmail'    => $from_email,
				'fromName'    => 'Xecurify',
				'toEmail'     => 'info@xecurify.com',
				'toName'      => 'office365support@xecurify.com',
				'bccEmail'    => 'office365support@xecurify.com',
				'subject'     => $subject,
				'content'     => $content,
			),
		);
		$field_string  = wp_json_encode( $fields );
		$headers       = array(
			'Content-Type'  => 'application/json',
			'Customer-Key'  => $customer_key,
			'Timestamp'     => $current_time_in_millis,
			'Authorization' => $hash_value,
		);
		$response_body = MOTCE_WP_Wrapper::motce_remote_request( $url, $field_string, $headers, '', 'POST' );
		return $response_body;
	}

	/**
	 * Function to submit contact us.
	 *
	 * @param string $email This contains email of the customer.
	 * @param string $phone This contains phone number of the customer.
	 * @param string $query This contains the query to be sent in the email.
	 * @return array
	 */
	public static function motce_submit_contact_us( $email, $phone, $query ) {
		$url          = MOTCE_Plugin_Constants::HOSTNAME . '/moas/rest/customer/contact-us';
		$current_user = wp_get_current_user();

		$fields = array(
			'firstName' => $current_user->user_firstname,
			'lastName'  => $current_user->user_lastname,
			'company'   => isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '',
			'email'     => $email,
			'ccEmail'   => 'office365support@xecurify.com',
			'phone'     => $phone,
			'query'     => $query,
		);

		$field_string = wp_json_encode( $fields );

		$response = MOTCE_WP_Wrapper::motce_remote_request( $url, $field_string, '', '', 'POST' );
		return $response['body'];
	}

	/**
	 * Method to get the timestamp.
	 *
	 * @return mixed|string The timestamp if successful, an error message otherwise.
	 */
	public function motce_get_timestamp() {
		$url      = MOTCE_Plugin_Constants::HOSTNAME . '/moas/rest/mobile/get-timestamp';
		$response = MOTCE_Authorization::post_request( $url, '' );
		return $response['data'];
	}

	/**
	 * Method to check and verify the customer.
	 *
	 * @return array
	 */
	public function motce_check_customer() {
		$url = MOTCE_Plugin_Constants::HOSTNAME . '/moas/rest/customer/check-if-exists';

		$email = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::ADMIN_EMAIL );

		$fields       = array(
			'email' => $email,
		);
		$field_string = wp_json_encode( $fields );

		$response = MOTCE_WP_Wrapper::motce_remote_request( $url, $field_string, '', '', 'POST' );

		return $response;
	}

	/**
	 * Method to get the customer.
	 *
	 * @return array
	 */
	public function motce_get_customer_key() {
		$url = MOTCE_Plugin_Constants::HOSTNAME . '/moas/rest/customer/key';

		$email    = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::ADMIN_EMAIL );
		$password = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::ADMIN_PASSWORD );

		$fields       = array(
			'email'    => $email,
			'password' => $password,
		);
		$field_string = wp_json_encode( $fields );

		$response = MOTCE_WP_Wrapper::motce_remote_request( $url, $field_string, '', '', 'POST' );
		return $response;
	}

	/**
	 * Method to create the customer.
	 *
	 * @return array
	 */
	public function motce_create_customer() {
		$url = MOTCE_Plugin_Constants::HOSTNAME . '/moas/rest/customer/add';

		$this->email = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::ADMIN_EMAIL );
		$password    = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::ADMIN_PASSWORD );

		$fields       = array(
			'areaOfInterest' => 'WP Embed Power BI Content',
			'email'          => $this->email,
			'password'       => $password,
		);
		$field_string = wp_json_encode( $fields );

		$response = MOTCE_WP_Wrapper::motce_remote_request( $url, $field_string, '', '', 'POST' );
		return $response;
	}
}
