<?php
/**
 * Mail Sending Tab.
 *
 * @package embed-outlook-teams-shortcode-events/Views
 */

namespace MOTCE\View;

use MOTCE\Wrappers\MOTCE_Plugin_Constants;
use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to load view for Outlook shortcode.
 */
class MOTCE_Mail_View {

	/**
	 * Holds the MOTCE_Mail_View class instance.
	 *
	 * @var MOTCE_Mail_View
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Mail_View) getter method.
	 *
	 * @return MOTCE_Mail_View
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
		<h1>Send Mails using Microsoft Graph</h1>
			<div style="display:flex;">
			<div style="width: 100%">
					<?php
					$this->load_mail_view();
					?>
			</div>
			<div>
			<?php MOTCE_Support_Form::get_view()->motce_display_support_form(); ?>
			</div>
	</div>
		</div>
		<?php
	}

	/**
	 * Function to load outlook shortcode tile.
	 *
	 * @return void
	 */
	private function load_mail_view() {
		$mail_app           = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::MAIL_CONFIG );
		$mail_form          = ! empty( $mail_app['mailFrom'] ) ? $mail_app['mailFrom'] : '';
		$mail_to            = ! empty( $mail_app['mailTo'] ) ? $mail_app['mailTo'] : '';
		$save_to_sent_items = ! empty( $mail_app['saveToSentItems'] ) ? $mail_app['saveToSentItems'] : '';
		?>
		<div class="motce-tab-content-tile" style="width:110%;border:none;box-shadow:none;">
		<div>
			<form class="motce_ajax_submit_form" action="" method="post">
			<input type="hidden" name="mo_controller_option" id="mail_view" value="motce_mail_view_option">
			<input type="hidden" name="motce_tab" value="mail_view">
			<?php wp_nonce_field( 'motce_mail_view_option' ); ?>
			<div class="motce-tab-content-tile">
				<div class="motce-tab-content-tile-content">
						<span style="font-size: 18px;font-weight: 200;">1. Send Emails Manually
						</span>
						<div id="send_email_attr_access_desc" class="motce_help_desc">
							<span>
							You can send a email using your licensed microsoft office365 exchange account's userprinciplename to any other user
							email.
							</span>
						</div>
						<table class="motce-tab-content-app-config-table">
						<colgroup>
							<col span="1" style="width: 20%;">
							<col span="2" style="width: 80%;">
						</colgroup>
						<tr>
							<td><span>From <span style="color:red;font-weight:bold;">*</span></span></td>
							<td>
								<input required placeholder="Enter UserPrincipalName/Object ID e.g. user@example.onmicrosoft.com" type="email" name="mailFrom" value="<?php echo esc_html( $mail_form ); ?>">
							</td>
						</tr>
						<tr>
							<td><span>To <span style="color:red;font-weight:bold;">*</span></span></td>
							<td><input required placeholder="Enter Any Test User Email e.g. user@example.com" type="email" name="mailTo" value="<?php echo esc_html( $mail_to ); ?>"></td>
						</tr>
						<tr>
							<td><span>Save Emails to Sent Items</span></td>
							<td>
								<label class="switch">
									<input type="checkbox" name="saveToSentItems" <?php echo 'on' === $save_to_sent_items ? 'checked' : ''; ?>>
									<span class="slider round"></span>
								</label>
							</td>
						</tr>
						</table>
						<fieldset style="border:2px solid #eee;padding:10px;margin-top:0px;background-color:#ECEFF1;border-radius:8px">
						<legend style="font-size: 13px;color:red;font-weight:600;margin-left:auto;">
						[Available in Premium Plugin]
						</legend>
						<table class="motce-tab-content-app-config-table" style="margin-top:0px;">
							<colgroup>
								<col span="1" style="width: 20%;">
								<col span="2" style="width: 80%;">
							</colgroup>
							<tr>
								<td ><span>CC</span></td>
								<td><div style="border:1px solid #eee;background-color:rgba(255,255,255,.5);height:30px;width:100%;border-radius:5px;display:flex;justify-content:flex-start;align-items:center;">
									<div style="background-color:#eee;border-radius:10px;width:120px;height:24px;font-size:9px;display:flex;justify-content:space-around;align-items:center;margin:5px">
										<span>admin1@example.com</span>
										<span style="border-radius:50%;background-color:#fff;display:flex;justify-content:center;align-items:center;width:10px;height:10px;font-size:8px;">X</span>
									</div>
									<div style="background-color:#eee;border-radius:10px;width:120px;height:24px;font-size:9px;display:flex;justify-content:space-around;align-items:center;margin:5px">
										<span>admin2@example.com</span>
										<span style="border-radius:50%;background-color:#fff;display:flex;justify-content:center;align-items:center;width:10px;height:10px;font-size:8px;">X</span>
									</div>
								</div></td>
							</tr>
							<tr>
								<td><span>BCC</span></td>
								<td><div style="border:1px solid #eee;background-color:rgba(255,255,255,.5);height:30px;width:100%;border-radius:5px;display:flex;justify-content:flex-start;align-items:center;">
									<div style="background-color:#eee;border-radius:10px;width:120px;height:24px;font-size:9px;display:flex;justify-content:space-around;align-items:center;margin:5px">
										<span>user@example.com</span>
										<span style="border-radius:50%;background-color:#fff;display:flex;justify-content:center;align-items:center;width:10px;height:10px;font-size:8px;">X</span>
									</div>
								</div></td>
							</tr>
							<tr>
								<td><span>Subject</td>
								<td><input disabled style="border:1px solid #eee;" placeholder="" type="text" name="tenant_id" value="<?php echo esc_html( 'Graph Mail Test' ); ?>"></td>
							</tr>
							<tr>
								<td><span>Text/HTML</span></td>
								<td >
								<select disabled placeholder="" type="text" name="motce_select_text_format" value="">
									<option class="Select-placeholder" value="" disabled>Select Format</option>
									<option value="text_format">Text</option>
									<option value="html_format">HTML</option>
								</select></td>
							</tr>
							<tr>
								<td><span>Content</td>
								<td><textarea disabled class="motce_table_textbox" style="width:100%" name="motce_contact_us_query" rows="7" style="resize: vertical;" >Hi, You are recieving this test email using Microsoft Graph API.</textarea></td>
							</tr>
							<tr>
								<td><span>Attachments</span></td>
								<td><input disabled type="file" name="motce_email_to" value="<?php echo esc_html( '' ); ?>"></td>
							</tr>
							<tr><td colspan="2"></td></tr>
						</table>
						</fieldset>
					<div style="display: flex;justify-content:flex-start;align-items:center;">
						<div style="display: flex;margin:10px;">
							<input style="height:30px;" type="submit" id="saveButton" class="motce-tab-content-button" value="Save">
						</div>
						<div style="margin:10px;">
							<input style="height:30px;" id="ad_send_email" type="button" class="motce-tab-content-button" value="Send Test Email">
						</div>
					</div>
				</div>
			</div>
		</form>
		<form id="motce_send_test_mail_form" method="post">
			<input type="hidden" value="motce_send_test_mail_option" name="mo_controller_option">
			<input type="hidden" name="motce_tab" value="mail_view">
			<?php wp_nonce_field( 'motce_send_test_mail_option' ); ?>
		</form>
		</div></div>
		<script>
			document.getElementById('ad_send_email').addEventListener('click',function (){
				document.getElementById('motce_send_test_mail_form').submit();
			});
		</script>
		<?php
	}
}
