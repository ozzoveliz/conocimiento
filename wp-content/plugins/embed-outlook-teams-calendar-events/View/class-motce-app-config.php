<?php
/**
 * App Config.
 *
 * @package embed-outlook-teams-calendar-events/Views
 */

namespace MOTCE\View;

use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to load view for Manage Application tab.
 */
class MOTCE_App_Config {

	/**
	 * Holds the MOTCE_App_Config class instance.
	 *
	 * @var MOTCE_App_Config
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_App_Config) getter method.
	 *
	 * @return MOTCE_App_Config
	 */
	public static function get_view() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to display all tiles of tab.
	 *
	 * @return void
	 */
	public function display_tab_details() {

		?>
		<div class="motce-tab-content">
			<div style="display: flex;">
			<div class="motce-tab-name">
				<div style="display: flex;"><h1>Configure Microsoft Outlook Application</h1>
				<button class="motce-tab-content-button motce-tab-button-content">
				<a href="<?php echo esc_url( MOTCE_SETUP_GUIDE_URL ); ?>" target="_blank" style="text-decoration:none;color:white;">Setup Guide</a>
				</button>
			</div>
			<div style="width: 70%">
				<div>
					<?php
					$this->display_client_config();
					?>
				</div>
			</div></div>
			<div>
			<?php MOTCE_Support_Form::get_view()->motce_display_support_form(); ?>
			</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Function to load App Configurations tile.
	 *
	 * @return void
	 */
	private function display_client_config() {
		$app       = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::APP_CONFIG );
		$client_id = ! empty( $app['client_id'] ) ? $app['client_id'] : '';
		if ( isset( $app['client_secret'] ) && ! empty( $app['client_secret'] ) ) {
			$client_secret = MOTCE_WP_Wrapper::decrypt_data( $app['client_secret'], hash( 'sha256', $client_id ) );
		} else {
			$client_secret = '';
		}
		$tenant_id = ! empty( $app['tenant_id'] ) ? $app['tenant_id'] : '';
		$upn       = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::UPN );

		?>
		<form class="motce_ajax_submit_form" action="" method="post">
			<input type="hidden" name="mo_controller_option" id="app_config" value="motce_client_config_option">
			<input type="hidden" name="motce_tab" value="app_config_view">
			<?php wp_nonce_field( 'motce_client_config_option' ); ?>
			<div class="motce-tab-content-tile" style="width: 141% !important;">
				<div class="motce-tab-content-tile-content">
					<span style="font-size: 18px;font-weight: 200;display:flex;">1. Basic App Configuration </span>
					<table class="motce-tab-content-app-config-table">
						<tr>
							<td class="left-div"><span>Application ID <span style="color:red;font-weight:bold;">*</span></span></td>
							<td class="right-div"><input placeholder="Enter Your Application (Client) ID" style="width:75%;" type="text" name="client_id" value="<?php echo esc_html( sanitize_text_field( $client_id ) ); ?>"></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<b>Note:</b> You can find the <b>Application ID</b> in your Active Directory application's Overview tab. 
							</td>
						</tr>		
						<tr>
							<td class="left-div"><span>Client Secrets <span style="color:red;font-weight:bold;">*</span></span></td>
							<td class="right-div"><input autoComplete="new-password" placeholder="Enter Your Client Secret" style="width:75%;" type="password" name="client_secret" value="<?php echo esc_html( sanitize_text_field( $client_secret ) ); ?>"></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<b>Note:</b> You can find the <b>Client Secret</b> value in your Active Directory application's Certificates & Secrets tab. 
							</td>
						</tr> 
						<tr>
							<td class="left-div"><span>Tenant ID <span style="color:red;font-weight:bold;">*</span></span></td>
							<td class="right-div"><input placeholder="Enter Your Directory (Tenant) ID" style="width:75%;" type="text" name="tenant_id" value="<?php echo esc_html( sanitize_text_field( $tenant_id ) ); ?>"></td>
						</tr>
						<tr>
							<td></td>
							<td>
								<b>Note:</b> You can find the <b>Tenant ID</b> in your Active Directory application's Overview tab. 
							</td>
						</tr>
						<tr>
							<td></br></td>
						</tr>
						<tr>
							<td><span> Test UPN/ID <span style="color:red;font-weight:bold;">*</span></span></td>
							<td><input placeholder="Enter UserPrincipalName/Object ID of User To Test" style="width:75%;" type="text" name="upn_id" value="<?php echo esc_html( sanitize_text_field( $upn ) ); ?>"></td>
						</tr>
						<tr>
							<td></td>
							<td>
							<b>Note:</b> You can find the <b>User Principle Name / Object ID</b> in the user profile in Users tab in your active directory. Click on <b>Save</b> button to see calendar view of this user.
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<div style="display: flex;justify-content:flex-start;align-items:center;">
									<div style="display: flex;margin:1px;">
										<input style="height:30px;" type="submit" id="saveButton" class='motce-tab-content-button' value="Save">
									</div>
									<div style="margin:10px;">
										<input style="height:30px;" id="view_attributes" type="button" class='motce-tab-content-button' value="Test Connection" onclick="show_test_connection()">
									</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
		<script>
			function show_test_connection(){
				document.getElementById("app_config").value = "motce_app_test_config_option";
				var myWindow = window.open("<?php echo esc_url_raw( $this->get_test_url() ); ?>", "TEST Connection", "scrollbars=1 width=800, height=600");
			}
		</script>
		<?php
	}

	/**
	 * Function to get test connection window url.
	 *
	 * @return string
	 */
	private function get_test_url() {
		return admin_url( '?motce_option=motce_test_connection&_wpnonce=' . wp_create_nonce( MOTCE_Plugin_Constants::ADMIN_OBSERVER_NC ) );
	}
}
