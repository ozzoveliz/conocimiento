<?php
/**
 * App Config.
 *
 * @package embed-outlook-teams-calendar-events/Views
 */

namespace MOTCE\View;

/**
 * Class to load support form.
 */
class MOTCE_Support_Form {

	/**
	 * Holds the MOTCE_App_Config class instance.
	 *
	 * @var MOTCE_Support_Form
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_App_Config) getter method.
	 *
	 * @return MOTCE_Support_Form
	 */
	public static function get_view() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to display support form.
	 *
	 * @return void
	 */
	public function motce_display_support_form() {
		?>
				<div>
				<form method="post" action="">
					<input type="hidden" name="motce_forms_option" value="motce_contact_us_query_option" />
					<?php wp_nonce_field( 'motce_contact_us_query_option', 'motce_nonce' ); ?>
					<div class="support_container" id="contact-us">
						<div class="support_header">
							<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/support-header2.jpg' ); ?>"/>
						</div>
						<div style="display:flex;justify-content:flex-start;align-items:center;width:90%;margin-top:8px;font-size:14px;font-weight:500;">Email:</div>
						<input style="width:91%;border:none;margin-top:4px;background-color:#fff" type="email" required name="motce_contact_us_email" value="<?php echo esc_attr( ( get_option( 'motce_admin_email' ) === '' ) ? get_option( 'motce_admin_email' ) : get_option( 'admin_email' ) ); ?>" placeholder="Email">
						<div style="display:flex;justify-content:flex-start;align-items:center;width:90%;margin-top:8px;font-size:14px;font-weight:500;">Contact No.:</div>
						<input id="contact_us_phone" class="support__telphone" type="tel" style="border:none;margin:5px 22px;background-color:#fff;" pattern="[\+]?[0-9]{1,4}[\s]?([0-9]{4,12})*" name="motce_contact_us_phone" value="<?php echo esc_attr( get_option( 'motce_admin_phone' ) ); ?>" placeholder="Enter your phone">
						<div style="display:flex;justify-content:flex-start;align-items:center;width:90%;margin-top:5px;font-size:14px;font-weight:500;">How can we help you?</div>
						<textarea id="textarea-contact-us" style="padding:10px 10px;width:91%;border:none;margin-top:5px;background-color:#fff" onkeypress="motce_valid_query(this)" onkeyup="motce_valid_query(this)" onblur="motce_valid_query(this)" required name="motce_contact_us_query" rows="3" style="resize: vertical;" placeholder="You will get reply via email"></textarea>
						<div style="text-align:center;">
							<input type="submit" name="submit" style=" width:120px;margin:8px;" class="button button-primary button-large"/>
						</div>
					</div>
				</form>
			</div>

			<script>
			function motce_valid_query(f) {
			!(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(
				/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
			}
			</script>
		<?php
	}
}
