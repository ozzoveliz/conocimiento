<?php
/**
 * Admin Observer to carry out specific admin tasks.
 *
 * @package embed-outlook-teams-calendar-events/Observer
 */

namespace MOTCE\Observer;

use MOTCE\Wrappers\MOTCE_WP_Wrapper;
use MOTCE\API\MOTCE_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle admin actions of the plugin.
 */
class MOTCE_Forms_Observer {

	/**
	 * Holds the MOTCE_Forms_Observer class instance.
	 *
	 * @var MOTCE_Forms_Observer
	 */
	private static $obj;

	/**
	 * Object instance(MOTCE_Forms_Observer) getter method.
	 *
	 * @return MOTCE_Forms_Observer
	 */
	public static function get_observer() {
		if ( ! isset( self::$obj ) ) {
			self::$obj = new MOTCE_Forms_Observer();
		}
		return self::$obj;
	}

	/**
	 * Function to execute specific actions based on option value in request.
	 *
	 * @return void
	 */
	public function handle_forms_observer() {
		if ( ! isset( $_REQUEST['motce_forms_option'] ) || ! isset( $_POST['motce_nonce'] ) || ! check_admin_referer( sanitize_text_field( wp_unslash( $_REQUEST['motce_forms_option'] ) ), 'motce_nonce' ) ) {
			return;
		}

		$values_array = array();
		foreach ( $_POST as $key => $value ) {
			$values_array[ $key ] = sanitize_text_field( wp_unslash( $value ) );
		}

		switch ( sanitize_text_field( wp_unslash( $_REQUEST['motce_forms_option'] ) ) ) {

			case 'motce_contact_us_query_option':
				$this->motce_send_support_query_successfull( $values_array );
				break;

			case 'motce_feedback':
				$this->motce_send_feedback_form( $values_array );
				break;

			case 'motce_skip_feedback':
				$this->motce_send_feedback_form( $values_array );
				break;
		}
	}

	/**
	 * Function to send support query and check whether it was successfully sent.
	 *
	 * @param array|mixed $query Has all the values to be passed in query.
	 * @return void
	 */
	private function motce_send_support_query_successfull( $query ) {
		$submitted = $this->motce_send_support_query( $query );
		if ( ! is_null( $submitted ) ) {
			if ( false === $submitted ) {
				MOTCE_WP_Wrapper::show_error_notice( esc_html__( 'Your query could not be submitted. Please try again.' ) );
			} else {
				MOTCE_WP_Wrapper::show_success_notice( esc_html__( 'Thanks for getting in touch! We shall get back to you shortly.' ) );
			}
		}
	}

	/**
	 * Function to handle feedback form.
	 *
	 * @param array|null $submited Has the result of mail send.
	 * @return void
	 */
	public function handle_feedback_form( $submited ) {
		if ( json_last_error() === JSON_ERROR_NONE ) {
			if ( isset( $submited['status'] ) && 'ERROR' === $submited['status'] ) {
				MOTCE_WP_Wrapper::show_error_notice( esc_html( $submited['message'] ) );
			} elseif ( false === $submited ) {
				MOTCE_WP_Wrapper::show_error_notice( esc_html__( 'Error while submitting the query.' ) );
			}
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		deactivate_plugins( MOTCE_PLUGIN_FILE );
		MOTCE_WP_Wrapper::show_success_notice( esc_html__( 'Thank you for the feedback.' ) );

		wp_safe_redirect( admin_url() . '/plugins.php' );
		exit();
	}

	/**
	 * Function to send the feedback form.
	 *
	 * @param string $query Contains the required option value.
	 * @return void
	 */
	private function motce_send_feedback_form( $query ) {

		if ( isset( $query['motce_forms_option'] ) && 'motce_feedback' === $query['motce_forms_option'] ) {
			$submited = $this->motce_send_email_alert( $query, false );
			$this->handle_feedback_form( $submited );
		}
		if ( isset( $query['motce_forms_option'] ) && 'motce_skip_feedback' === $query['motce_forms_option'] ) {
			$submited = $this->motce_send_email_alert( $query, true );
			$this->handle_feedback_form( $submited );
		}
	}

	/**
	 * Sends an email alert regarding plugin deactivation or feedback submission.
	 *
	 * @param array   $query Has all the $_Post values to be passed to the function.
	 * @param boolean $is_skipped Indicates if deactivation was skipped.
	 * @return array|null Response array from email submission.
	 */
	private function motce_send_email_alert( $query, $is_skipped = false ) {

		$user = wp_get_current_user();

		$message = 'Plugin Deactivated';

		$deactivate_reason_message = isset( $query['query_feedback'] ) ? $query['query_feedback'] : false;

		if ( $is_skipped ) {
			$deactivate_reason_message = 'Skipped';
			return;
		}

		$reply_required = '';
		if ( isset( $query['get_reply'] ) ) {
			$reply_required = $query['get_reply'];
		}
		if ( empty( $reply_required ) ) {
			$reply_required = "Don't reply";
			$message       .= '<b style="color:red";> &nbsp; [Reply :' . $reply_required . ']</b>';
		} else {
			$reply_required = 'Yes';
			$message       .= '[Reply :' . $reply_required . ']';
		}

		if ( is_multisite() ) {
			$multisite_enabled = 'True';
		} else {
			$multisite_enabled = 'False';
		}

		$message .= ', [Multisite Enabled: ' . $multisite_enabled . ']';

		$message .= ', Feedback : ' . $deactivate_reason_message . '';

		$email      = '';
		$rate_value = '';

		if ( isset( $query['rate'] ) ) {
			$rate_value = $query['rate'];
		}

		$message .= ', [Rating :' . $rate_value . ']';

		if ( isset( $query['query_mail'] ) ) {
			$email = $query['query_mail'];
		}

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$email = get_option( 'motce_admin_email' );
			if ( empty( $email ) ) {
				$email = $user->user_email;
			}
		}
		$phone            = get_option( 'motce_admin_phone' );
		$feedback_reasons = new MOTCE_Customer();
		$response         = $feedback_reasons->motce_send_email_alert( $email, $phone, $message );
		return $response;
	}

	/**
	 * Function to send the support query parameters.
	 *
	 * @param array|mixed $query Has all the values to be passed in query.
	 * @return array
	 */
	private function motce_send_support_query( $query ) {

		$email = isset( $query['motce_contact_us_email'] ) ? $query['motce_contact_us_email'] : '';
		$phone = isset( $query['motce_contact_us_phone'] ) ? $query['motce_contact_us_phone'] : '';
		$query = isset( $query['motce_contact_us_query'] ) ? $query['motce_contact_us_query'] : '';

		$query = '[Embed Outlook Teams Calendar Events Plugin] ' . $query;

		$response = MOTCE_Customer::motce_submit_contact_us( $email, $phone, $query );

		return $response;
	}
}
