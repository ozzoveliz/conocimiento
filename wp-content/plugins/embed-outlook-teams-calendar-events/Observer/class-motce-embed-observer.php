<?php
/**
 * Admin Observer to carry out specific admin tasks.
 *
 * @package embed-outlook-teams-calendar-events/Observer
 */

namespace MOTCE\Observer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MOTCE\API\MOTCE_Outlook;
use MOTCE\Wrappers\MOTCE_Outlook_Wrapper;
use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

/**
 * Class to handle ajax actions of the plugin.
 */
class MOTCE_Embed_Observer {

	/**
	 * Holds the MOTCE_Embed_Observer class instance.
	 *
	 * @var MOTCE_Embed_Observer
	 */
	private static $obj;

	/**
	 * Holds the MOTCE_MOTCE_Embed_Observer class instance.
	 *
	 * @return MOTCE_Embed_Observer
	 */
	public static function get_observer() {
		if ( ! isset( self::$obj ) ) {
			self::$obj = new MOTCE_Embed_Observer();
		}
		return self::$obj;
	}

	/**
	 * Holds the UPN for the current used id.
	 *
	 * @return mixed
	 */
	public static function get_upn() {
		$user_id       = get_current_user_id();
		$aad_object_id = get_user_meta( $user_id, 'aadObjectId', true );

		if ( ! empty( $aad_object_id ) ) {
			return $aad_object_id;
		}
	}

	/**
	 * Function to handle outlook API calls.
	 *
	 * @return void
	 */
	public function handle_calendar_embed_api_handler() {
		if ( ! check_ajax_referer( MOTCE_Plugin_Constants::EMBED_CALENDAR_AJAX_NC, 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'err' => 'Permission denied.',
				)
			);
			exit;
		}

		if ( ! isset( $_POST['task'] ) || ! isset( $_POST['payload']['upn'] ) ) {
			return;
		}

		$payload = sanitize_text_field( wp_unslash( $_POST['payload']['upn'] ) );

		switch ( sanitize_text_field( wp_unslash( $_POST['task'] ) ) ) {
			case 'motce_get_all_events':
				$this->get_all_events( $payload );
				break;
			case 'motce_get_all_outlook_categories':
				$this->get_all_outlook_categories( $payload );
				break;
		}
	}

	/**
	 * Function to embed outlook calendar.
	 *
	 * @param string $attrs This contains attributes sent when shortcode is embedded.
	 * @param string $content This contains content to be displayed.
	 * @return string
	 */
	public function embed_shortcode_outlook_calendar( $attrs = '', $content = '' ) {
		$upn = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::UPN );

		$aad_object_id = self::get_upn();
		if ( ! empty( $aad_object_id ) ) {
			$upn = $aad_object_id;
		}

		$attrs = shortcode_atts( array(), $attrs, 'MO_OUTLOOK_CALENDAR' );

		$content = $content . '
			<div class="motce_calendar_container">
				<div id="motce_calendar">
					<div id="calendar_loader" style="border:1px solid #eee;color:#000;height:100%;display:flex;justify-content:center;align-items:center;font-size:14px;text-align:center;flex-direction:column;">
						<img style="width:50px;height:50px" src="' . esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'outlook.svg' ) ) . '" />
						<img style="width:50px;height:50px;margin-left:10px;" src="' . esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'loader.gif' ) ) . '"/>
					</div>
				</div>
			</div>
		';
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'moment' );

			wp_enqueue_script( 'motce_calendar_view_sc_js', plugins_url( '../includes/js/calendarview.js', __FILE__ ), array( 'jquery', 'moment' ), MOTCE_PLUGIN_VERSION, false );

			wp_localize_script(
				'motce_calendar_view_sc_js',
				'embedConfig',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( MOTCE_Plugin_Constants::EMBED_CALENDAR_AJAX_NC ),
					'upnID'    => $upn,
				)
			);

			wp_enqueue_style( 'motce_calendar_view_sc_css', plugins_url( '../includes/css/calendarview.css', __FILE__ ), array(), MOTCE_PLUGIN_VERSION );

		return $content;
	}

	/**
	 * Function to handle ajax action for getting all calendar events.
	 *
	 * @param array $payload This contains json payload recieved from ajax call.
	 * @return void
	 */
	private function get_all_events( $payload ) {
		$config = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );
		$upn    = $payload;

		$api_handler = MOTCE_Outlook::get_client( $config );
		$response    = $api_handler->get_user_calendar_events( $upn );

		if ( $response['status'] ) {
			$all_events = MOTCE_Outlook_Wrapper::process_calendar_event_items( $response['data'] );
			wp_send_json_success( $all_events );
		} else {
			$error_code = array(
				'Error'       => $response['data']['error'],
				'Description' => empty( $response['data']['error'] ) ? '' : $response['data']['error_description'],
			);

			wp_send_json_error( $error_code );
		}
	}

	/**
	 * Function to handle ajax action for getting all outlook categories specific to mailbox.
	 *
	 * @param array $payload This contains json payload recieved from ajax call.
	 * @return void
	 */
	private function get_all_outlook_categories( $payload ) {
		$config = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );
		$upn    = $payload;

		$api_handler = MOTCE_Outlook::get_client( $config );
		$response    = $api_handler->get_user_outlook_categories( $upn );

		if ( $response['status'] ) {
			$all_categories = MOTCE_Outlook_Wrapper::process_outlook_categories( $response['data'] );
			wp_send_json_success( $all_categories );
		} else {
			$error_code = array(
				'Error'       => $response['data']['error'],
				'Description' => empty( $response['data']['error'] ) ? '' : $response['data']['error_description'],
			);

			wp_send_json_error( $error_code );
		}
	}
}
