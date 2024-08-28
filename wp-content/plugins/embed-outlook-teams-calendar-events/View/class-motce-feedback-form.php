<?php
/**
 * Handles all view file class instances.
 *
 * @package embed-outlook-teams-calendar-events/Views
 */

namespace MOTCE\View;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle feedback form view.
 */
class MOTCE_Feedback_Form {

	/**
	 * Holds the instance of MOTCE_Feedback_Form class.
	 *
	 * @var MOTCE_Feedback_Form
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Feedback_Form) getter method.
	 *
	 * @return MOTCE_Feedback_Form
	 */
	public static function get_view() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to display feedback form.
	 *
	 * @return void
	 */
	public function motce_display_feedback_form() {
		if ( 'plugins.php' !== basename( isset( $_SERVER['PHP_SELF'] ) ? wp_sanitize_redirect( wp_unslash( $_SERVER['PHP_SELF'] ) ) : '' ) ) {
			return;
		}
		?> 
		<div id="feedback_modal" class="mo_modal" style="width:90%; margin-left:12%; margin-top:5%; text-align:center;">
		 
			<div class="mo_modal-content" style="width:50%;">
				<h3 style="margin: 2%; text-align:center;"><b><?php esc_textarea( 'Your feedback', 'Embed Outlook Teams Calendar Events' ); ?></b><span class="mo_close" style="cursor: pointer">&times;</span>
				</h3>
				<hr style="width:75%;">
				
				<form name="f" method="post" action="" id="mo_feedback">
					<?php wp_nonce_field( 'motce_feedback', 'motce_nonce' ); ?>
					<input type="hidden" name="motce_forms_option" value="motce_feedback"/>
					<div>
						<p style="margin:2%">
						<h4 style="margin: 2%; text-align:center;"><?php esc_textarea( 'Please help us to improve our plugin by giving your opinion.', 'Embed Outlook Teams Calendar Events' ); ?><br></h4>
						
						<div id="smi_rate" style="text-align:center">
						<input type="radio" name="rate" id="angry" value="1"/>
							<label for="angry"><img class="sm" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/angry.png' ); ?>" />
							</label>
							
						<input type="radio" name="rate" id="sad" value="2"/>
							<label for="sad"><img class="sm" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/sad.png' ); ?>" />
							</label>
						
						
						<input type="radio" name="rate" id="neutral" value="3"/>
							<label for="neutral"><img class="sm" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/normal.png' ); ?>" />
							</label>
							
						<input type="radio" name="rate" id="smile" value="4"/>
							<label for="smile">
							<img class="sm" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/smile.png' ); ?>" />
							</label>
							
						<input type="radio" name="rate" id="happy" value="5" checked/>
							<label for="happy"><img class="sm" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/happy.png' ); ?>" />
							</label>
							
						<div id="outer" style="visibility:visible"><span id="result"><?php esc_html_e( 'Thank you for appreciating our work', 'Embed Outlook Teams Calendar Events' ); ?></span></div>
						</div><br>
						<hr style="width:75%;">
						<?php
						$email = get_option( 'motce_admin_email' );
						if ( empty( $email ) ) {
							$user  = wp_get_current_user();
							$email = $user->user_email;
						}
						?>
						<div style="text-align:center;">
							
							<div style="display:inline-block; width:60%;">
							<input type="email" id="query_mail" name="query_mail" style="text-align:center; border:0px solid black; border-style:solid; background:#f0f3f7; width:20vw;border-radius: 6px;"
								placeholder="<?php esc_html_e( 'Please enter your email address', 'Embed Outlook Teams Calendar Events' ); ?>" required value="<?php echo esc_attr( $email ); ?>" readonly="readonly"/>
							
							<input type="radio" name="edit" id="edit" onclick="editName()" value=""/>
							<label for="edit"><img class="editable" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../images/61456.png' ); ?>" />
							</label>
							
							</div>
							<br><br>
							<textarea id="query_feedback" name="query_feedback" rows="4" style="width: 60%"
								placeholder="<?php esc_html_e( 'Tell us what happened!', 'Embed Outlook Teams Calendar Events' ); ?>"></textarea>
							<br><br>
							<input type="checkbox" name="get_reply" value="reply" checked><?php esc_html_e( 'miniOrange representative will reach out to you at the email-address entered above.', 'Embed Outlook Teams Calendar Events' ); ?></input>
						</div>
						<br>
					
						<div class="mo-modal-footer" style="text-align: center;margin-bottom: 2%">
							<input type="submit" name="miniorange_feedback_submit"
								class="button button-primary button-large" value="<?php esc_html_e( 'Send', 'Embed Outlook Teams Calendar Events' ); ?>"/>
							<span width="30%">&nbsp;&nbsp;</span>
							<input type="button" name="miniorange_skip_feedback"
								class="button button-primary button-large" value="<?php esc_html_e( 'Skip', 'Embed Outlook Teams Calendar Events' ); ?>" onclick="document.getElementById('mo_feedback_form_close').submit();"/>
						</div>
					</div>
					
				</form>
				<form name="f" method="post" action="" id="mo_feedback_form_close">
					<?php wp_nonce_field( 'motce_skip_feedback', 'motce_nonce' ); ?>
					<input type="hidden" name="motce_forms_option" value="motce_skip_feedback"/>
				</form>

			</div>

		</div>

		<style>			
			/*Included for the feedback form*/
			.mo_modal {
				display: none;
				overflow: hidden;
				position: fixed;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				z-index: 1050;
				-webkit-overflow-scrolling: touch;
				outline: 0;

			}

			.mo_modal-content {
				position: relative;
				background-color: #ffffff;
				border: 1px solid #999999;
				border: 1px solid rgba(0, 0, 0, 0.2);
				border-radius: 6px;
				-webkit-box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
				box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
				-webkit-background-clip: padding-box;
				background-clip: padding-box;
				outline: 0;
				margin-left: 20%;
				margin-right: 24%;
				margin-top:6%;
			}

			.mo_close {
				color: #aaaaaa;
				float: right;
				font-size: 28px;
				font-weight: bold;
			}
			.overlay{
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				width: 100%;
				height: 100%;
				background: #000;
				opacity: .5;
				z-index: 0;

			}
			.fade {
				opacity: 0;
				-webkit-transition: opacity 0.15s linear;
				-o-transition: opacity 0.15s linear;
				transition: opacity 0.15s linear;
			}
			.fade.in {
				opacity: 1;
			}
			.modal-header {
				padding: 15px;
				border-bottom: 1px solid #e5e5e5;
			}
			.modal-header .close {
				margin-top: -2px;
			}
			.modal-title {
				margin: 0;
				line-height: 1.42857143;
				font-size: large;
			}
			.modal-body {
				position: relative;
				padding: 15px;
			}
			.modal-dialog {
				position: relative;
				width: auto;
				margin: 10px;
			}
			.modal.fade .modal-dialog {
				-webkit-transform: translate(0, -25%);
				-ms-transform: translate(0, -25%);
				-o-transform: translate(0, -25%);
				transform: translate(0, -25%);
				-webkit-transition: -webkit-transform 0.3s ease-out;
				-o-transition: -o-transform 0.3s ease-out;
				transition: transform 0.3s ease-out;
			}
			.modal.in .modal-dialog {
				-webkit-transform: translate(0, 0);
				-ms-transform: translate(0, 0);
				-o-transform: translate(0, 0);
				transform: translate(0, 0);
			}
			.modal-footer {
				padding: 15px;
				text-align: center;
				border-top: 1px solid #e5e5e5;
				position: relative;
				margin: 220px;
				margin-top: 35%;
			}
			.modal-footer .btn + .btn {
				margin-left: 5px;
				margin-bottom: 0;
			}
			.modal-footer .btn-group .btn + .btn {
				margin-left: -1px;
			}
			.modal-footer .btn-block + .btn-block {
				margin-left: 0;
			}
			.close {
				float: right;
				font-size: 21px;
				font-weight: bold;
				line-height: 1;
				color: #000000;
				text-shadow: 0 1px 0 #ffffff;
				opacity: 0.2;
				filter: alpha(opacity=20);
			}
			.close:hover,
			.close:focus {
				color: #000000;
				text-decoration: none;
				cursor: pointer;
				opacity: 0.5;
				filter: alpha(opacity=50);
			}
			button.close {
				padding: 0;
				cursor: pointer;
				background: transparent;
				border: 0;
				-webkit-appearance: none;
			}

			.mo-span-circle{
				display: inline-block;
				padding: 15px;
				line-height: 100%;

				-moz-border-radius: 50%;
				border-radius: 50%;

				background-color: black;
				color: white;
				text-align: center;
				font-size: 2em;

			}
			.nav-tab-active{
				margin-bottom: -1px;
				background: white;
				border-bottom: white;
				border-bottom: 3px solid white;
			}
			.nav-tab-active:hover{
				color: black;
				background: white;
				border-bottom: white;
				border-bottom: 3px solid white;
			}

			/* The Modal (background) */
			.modal {
				display: none; /* Hidden by default */
				position: fixed; /* Stay in place */
				z-index: 3; /* Sit on top */
				padding-top: 100px; /* Location of the box */
				left: 0;
				top: 0;
				width: 100%; /* Full width */
				height: 100%; /* Full height */
				/*overflow: auto;  Enable scroll if needed */
				background-color: rgb(0,0,0); /* Fallback color */
				background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
				transition: all 1s;
			}

			/* Modal Content */
			.modal-content {
				background-color: #FFFFFF;
				margin: 0;
				padding: 20px;
				border: 1px solid #888;
				width: 70%;
				border-radius: 20px;
				box-shadow: 5px 5px 5px rgba(0, 0, 0, 0.25);

				position: fixed;
				top: 50%;
				left: 50%;
				margin-right: -50%;
				transform: translate(-50%, -50%);
				height: 700px;
			}

			.modal-button {
				width: 15%;
				height: 50px;
				font-size: 20px !important;
			}
			.add-new-hover:hover{
				color: white !important;
			}

			.editable{
				text-align:center;
				width:1em;
				height:1em;
			}
			.sm {
				text-align:center;
				width: 2vw;
				height: 2vw;
				padding: 1vw;
			}

			.sm:hover {
				opacity:0.6;
				cursor: pointer;
			}

			.sm:active {
				opacity:0.4;
				cursor: pointer;
			}

			input[type=radio]:checked + label > .sm {
				border: 2px solid #21ecdc;
			}

			.mo-epbr-col-md-8
			{
				position: relative;
				width: 100%;
				min-height: 1px;

			}
		</style>
		<script>
			jQuery('a[aria-label="Deactivate Embed Outlook Teams Calendar Events"]').click(function () {
				var mo_modal = document.getElementById('feedback_modal');

				var span = document.getElementsByClassName("mo_close")[0];

				mo_modal.style.display = "block";
				document.querySelector("#query_feedback").focus();
				span.onclick = function () {
					mo_modal.style.display = "none";
					jQuery('#mo_feedback_form_close').submit();
				};

				window.onclick = function (event) {
					if (event.target === mo_modal) {
						mo_modal.style.display = "none";
					}
				};
				return false;

			});

			const MOTCE_INPUTS = document.querySelectorAll('#smi_rate input');
			MOTCE_INPUTS.forEach(el => el.addEventListener('click', (e) => updateValue(e)));


			function editName(){

				document.querySelector('#query_mail').removeAttribute('readonly');
				document.querySelector('#query_mail').focus();
				return false;

			}
			function updateValue(e) {
				document.querySelector('#outer').style.visibility="visible";
				var result = '<?php esc_textarea( 'Thank you for appreciating our work', 'Embed Outlook Teams Calendar Events' ); ?>';
				switch(e.target.value){
					case '1':	result = '<?php esc_html_e( 'Not happy with our plugin? Let us know what went wrong', 'Embed Outlook Teams Calendar Events' ); ?>';
						break;
					case '2':	result = '<?php esc_html_e( 'Found any issues? Let us know and we\'ll fix it ASAP', 'Embed Outlook Teams Calendar Events' ); ?>';
						break;
					case '3':	result = '<?php esc_html_e( 'Let us know if you need any help', 'Embed Outlook Teams Calendar Events' ); ?>';
						break;
					case '4':	result = '<?php esc_html_e( 'We\'re glad that you are happy with our plugin', 'Embed Outlook Teams Calendar Events' ); ?>';
						break;
					case '5':	result = '<?php esc_html_e( 'Thank you for appreciating our work' ); ?>';
						break;
				}
				document.querySelector('#result').innerHTML = result;

			}
		</script>
		<?php
	}
}