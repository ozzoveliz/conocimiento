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
use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

/**
 * Class to handle admin actions of the plugin.
 */
class MOTCE_Admin_Observer {

	/**
	 * Holds the MOTCE_Admin_Observer class instance.
	 *
	 * @var MOTCE_Admin_Observer
	 */
	private static $obj;

	/**
	 * Object instance(MOTCE_Admin_Observer) getter method.
	 *
	 * @return MOTCE_Admin_Observer
	 */
	public static function get_observer() {
		if ( ! isset( self::$obj ) ) {
			self::$obj = new MOTCE_Admin_Observer();
		}
		return self::$obj;
	}

	/**
	 * Function to execute specific actions based on option value in request.
	 *
	 * @return void
	 */
	public function handle_admin_observer() {

		if ( ! isset( $_REQUEST['motce_option'] ) ) {
			return;
		}

		check_admin_referer( MOTCE_Plugin_Constants::ADMIN_OBSERVER_NC );

		switch ( sanitize_text_field( wp_unslash( $_REQUEST['motce_option'] ) ) ) {
			case 'motce_test_connection':
				$this->test_app_connection();
				break;
		}
	}

	/**
	 * Function to test the connection of configured app.
	 *
	 * @return void
	 */
	private function test_app_connection() {
		$config = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );

		$api_handler = MOTCE_Outlook::get_client( $config );
		$response    = $api_handler->test_connection();

		if ( $response['status'] ) {
			$this->show_success_message_for_test_connection();
		} elseif ( 'MailboxNotEnabledForRESTAPI' === $response['data']['error'] ) {
			$error_code = array(
				'Error'       => $response['data']['error'],
				'Description' => 'It seems the user does not have the required license (Exchange Online).',
			);
			$this->display_error_message( $error_code );
		} else {
			$error_code = array(
				'Error'       => $response['data']['error'],
				'Description' => empty( $response['data']['error'] ) ? '' : $response['data']['error_description'],
			);
			$this->display_error_message( $error_code );
		}
	}

	/**
	 * Function to display error message in the test connection window.
	 *
	 * @param array $error_code This contains error codes and error description.
	 * @return void
	 */
	private function display_error_message( $error_code ) {
		?>
			<div class="motce_test_connection__error">
				<div class="motce_test_connection__error-heading">
					Error
				</div>

				<table class="motce-tab-content-app-config-table" style="border-collapse:collapse;width:90%">
					<tr>
						<td align="center" class="motce_test_connection__error-tableHeading" colspan="2"><h2><span>Test Configuration Failed</span></h2></td>
					</tr>
					<?php
					$allowed_html = array(
						'a' => array(
							'href'   => array(),
							'target' => array(),
						),
					);
					foreach ( $error_code as $key => $value ) {
						echo '<tr><td class="left-div motce_test_connection__error-table-colkey"><span style="margin-right:10px;"><b>' . esc_html( sanitize_text_field( $key ) ) . ':</b></span></td>
                       <td class="right-div motce_test_connection__error-table-colvalue"><span>' . ( ( 'Description' === $key ) ? wp_kses( $value, $allowed_html ) : esc_html( sanitize_text_field( $value ) ) ) . '</span></td></tr>';
					}
					?>
				</table>
				<h3 style="margin:20px;">
					Contact us at <a style="color:#dc143c" href="mailto:office365support@xecurify.com">office365support@xecurify.com</a>
				</h3>
			</div>
		<?php
		$this->load_css();
		exit();
	}

	/**
	 * Function to display success message in the test connection window.
	 *
	 * @return void
	 */
	private function show_success_message_for_test_connection() {
		echo '<div class="motce_test_connection__success">
            <div class="motce_test_connection__success-heading">
                Success
            </div>
            <div class="motce_test_connection__success_test_connection-content">';
		echo '</div>';
		$this->load_css();
		die();
	}

	/**
	 * Function to load css in the test connection window.
	 *
	 * @return void
	 */
	private function load_css() {
		?>
		<style>
			.test-container{
				width: 100%;
				background: #f1f1f1;
				margin-top: -30px;
			}

			.motce_test_connection__success_test_connection-title{
				display:flex;justify-content:flex-start;align-items:center;margin:10px;width:90%;
			}
			.motce_test_connection__success_test_connection-content{
				width:90%;display:flex;justify-content:flex-start;align-items:flex-start;align-content: flex-start;flex-wrap:wrap;height:400px;overflow-y:scroll;
			}
			.motce_test_connection__success_test_connection-content::-webkit-scrollbar {
				display: none;
			}
			.motce_test_connection__success_test_connection-content-objects{
				padding:10px;background-color:#eee;font-size:15px;margin:10px;border-radius:2px;
				display: flex;justify-content: center;align-items: center;
			}

			.motce_test_connection__error{
				width:100%;display:flex;flex-direction:column;justify-content:center;align-items:center;font-size:15px;margin-top:10px;width:100%;
			}
			.motce_test_connection__error-heading{
				width:86%;padding: 15px;text-align: center;background-color:#f2dede;color:#a94442;border: 1px solid #E6B3B2;font-size: 18pt;margin-bottom:20px;
			}
			.motce_test_connection__error-tableHeading{
				padding: 30px 5px 30px 5px;border:1px solid #757575;
			}
			.motce_test_connection__error-table-colkey{
				padding: 30px 5px 30px 5px;border:1px solid #757575;
			}
			.motce_test_connection__error-table-colvalue{
				padding: 30px 5px 30px 5px;border:1px solid #757575;
			}
			.motce_test_connection__success{
				display:flex;justify-content:center;align-items:center;flex-direction:column;border:1px solid #eee;padding:10px;
			}
			.motce_test_connection__success-heading{
				width:90%;color: #3c763d;background-color: #dff0d8;padding: 2%;margin-bottom: 20px;text-align: center;border: 1px solid #AEDB9A;font-size: 18pt;
			}

			.motce-tab-content-app-config-table{
				max-width: 1000px;
				background: white;
				padding: 1em 2em;
				margin: 2em auto;
				border-collapse:collapse;
				border-spacing:0;
				display:table;
				font-size:14pt;
			}

			.motce-tab-content-app-config-table td.left-div{
				width: 40%;
				word-break: break-all;
				font-weight:bold;
				border:2px solid #949090;
				padding:2%;
			}
			.motce-tab-content-app-config-table td.right-div{
				width: 40%;
				word-break: break-all;
				padding:2%;
				border:2px solid #949090;
				word-wrap:break-word;
			}

		</style>
		<?php
	}
}
