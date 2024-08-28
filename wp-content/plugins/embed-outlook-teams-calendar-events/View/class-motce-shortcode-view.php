<?php
/**
 * Shortcode Preiew.
 *
 * @package embed-outlook-teams-shortcode-events/Views
 */

namespace MOTCE\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to load view for Outlook shortcode.
 */
class MOTCE_Shortcode_View {

	/**
	 * Holds the MOTCE_Shortcode_View class instance.
	 *
	 * @var MOTCE_Shortcode_View
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Shortcode_View) getter method.
	 *
	 * @return MOTCE_Shortcode_View
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
			<div style="display:flex;">
			<div style="width: 100%">
					<?php
					$this->load_shortcode_view();
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
	private function load_shortcode_view() {
		?>
	<div class="motce-tab-content-tile" style="width:135%;">
	<div class="motce-tab-content-tile-content">
		<span style="font-size: 18px;font-weight: 700;">1. Embed using WordPress Shortcode</span>
		<div id="basic_attr_access_desc" class="motce_help_desc" style="font-weight:500;">
			<span>Copy this shortcode and follow the below steps to embed outlook calendar.
				</span>
		</div>
		<div>
			<ol style="margin-left:20px;">
				<li>Copy the <b>Shortcode</b> given below.</li>
			</ol>
		</div>
		<div style="background-color:#eee;display:flex;align-items:center;padding:12px;margin-top:1rem;width: 29%;">
			<span style="width:99%;" id="mo_copy_shortcode">[MO_OUTLOOK_CALENDAR]</span>
			<input type='text' value='[MO_OUTLOOK_CALENDAR]' id='shortcodeField' style='display: none;' />
			<form id="mo_copy_to_clipboard" method="post" name="mo_copy_to_clipboard">
				<input type="hidden" name="mo_controller_option" id="app_config" value="mo_copy_to_clipboard">
				<input type="hidden" name="motce_tab" value="app_config">
				<div style="margin-left:3px;" class="tooltip">
					<button type="button" onclick="mycopyFunction();"> Copy </button>
					<span class="tooltiptext" id="tooltip-text">Copy Shortcode</span>
				</div>                 
			</form>
		</div>
		<div>
			<ol start="2" style="margin-left:20px;">
				<li>Go to the <a href="<?php echo esc_url( admin_url() . 'edit.php?post_type=page' ); ?>"><b>Pages</b></a> or <a href="<?php echo esc_url( admin_url() . 'edit.php?post_type=post' ); ?>"><b>Posts</b></a> tab in your WordPress dashboard.</li>
				<li>Click on add new / select any existing post/page on which you want to embed Outlook Calendar.</li>
				<li>Click the "+" icon and search for <b>Shortcode</b></li>
				<li>Paste the copied shortcode into the shortcode block.</li>
				<li>Preview changes and then click <b>Publish</b> or <b>Update</b>.</li>
			</ol>
		</div>
		</div>
		</div>

		<script>
		function mycopyFunction() {
			var copyText = document.getElementById("shortcodeField");
			copyText.select();
			copyText.setSelectionRange(0, 99999);
			navigator.clipboard.writeText(copyText.value);
			var tooltipText = document.getElementById("tooltip-text");
			tooltipText.innerHTML = "Copied!";
			setTimeout(function() {
				tooltipText.innerHTML = "Copy Shortcode";
			}, 2000);
		} 
		</script>        
		<?php
	}
}
