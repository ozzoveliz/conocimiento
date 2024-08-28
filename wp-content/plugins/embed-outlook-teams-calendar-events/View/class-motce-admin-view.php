<?php
/**
 * Handles all view file class instances.
 *
 * @package embed-outlook-teams-calendar-events/Views
 */

namespace MOTCE\View;

use MOTCE\Wrappers\MOTCE_WP_Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to load all tab views.
 */
class MOTCE_Admin_View {

	/**
	 * Holds the MOTCE_Admin_View class instance.
	 *
	 * @var MOTCE_Admin_View
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Admin_View) getter method.
	 *
	 * @return MOTCE_Admin_View
	 */
	public static function get_view() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to load main view of the plugin.
	 *
	 * @return void
	 */
	public function display_plugin_menu() {

		if ( isset( $_GET['tab'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter in the URL for checking option name doesn't require nonce verification.
			$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter in the URL for checking option name doesn't require nonce verification.
		} else {
			$active_tab = 'app_config';
		}
		$this->load_tabs( $active_tab );
	}

	/**
	 * Function to load view of active tab.
	 *
	 * @param string $active_tab This includes value of active tab.
	 * @return void
	 */
	private function load_tabs( $active_tab ) {
		echo '<div id="motce_container" class="mo-container">';
			$this->display_header_menu();
			$this->display_tabs( $active_tab );
			$this->display_tab_content( $active_tab );
		echo '</div>';
	}

	/**
	 * Function to display plugin header.
	 *
	 * @return void
	 */
	private function display_header_menu() {
		?>
		<div style="display: flex;">
			<img id="motce-title-logo" src="
			<?php
			echo esc_url_raw(
				MOTCE_WP_Wrapper::get_image_src( 'miniorange.png' )
			);
			?>
											">
			<h1><label for="sync_integrator">Embed Outlook Teams Calendar Events</label></h1>
		</div>
		<?php
	}

	/**
	 * Function to load navigation panel for all tabs.
	 *
	 * @param string $active_tab This includes value of active tab.
	 * @return void
	 */
	private function display_tabs( $active_tab ) {
		?>
		<div class="motce-tab ms-tab-background motce-tab-border">
			<ul class="motce-tab-ul">
				<li id="app_config" class="motce-tab-li">
					<a href="
					<?php
					echo esc_url_raw(
						add_query_arg(
							array(
								'tab' => 'app_config',
							)
						)
					);
					?>
								">
						<div id="application_div_id" class="motce-tab-li-div 
						<?php
						if ( 'app_config' === $active_tab ) {
							echo 'motce-tab-li-div-active';
						}
						?>
						" aria-label="Application" title="Application Configuration" role="button" tabindex="0">
							<div id="add_icon" class="motce-tab-li-icon" >
								<img style="width:20px;height:20px" src="<?php echo esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'outlook.svg' ) ); ?>">
							</div>
							<div id="add_app_label" class="motce-tab-li-label">
								Manage Application
							</div>
						</div>
					</a>
				</li>
				<li id="calendar_view" class="motce-tab-li">
					<a href="
					<?php
					echo esc_url_raw(
						add_query_arg(
							array(
								'tab' => 'calendar_view',
							)
						)
					);
					?>
							">
						<div id="application_div_id" class="motce-tab-li-div 
						<?php
						if ( 'calendar_view' === $active_tab ) {
							echo 'motce-tab-li-div-active';
						}
						?>
						" aria-label="Application" title="Document Library" role="button" tabindex="0">
							<div id="add_icon" class="motce-tab-li-icon" >
								<img style="width:20px;height:20px" src="<?php echo esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'calendar.png' ) ); ?>">
							</div>
							<div id="add_app_label" class="motce-tab-li-label">
							Calendar Preview
							</div>
						</div>
					</a>
				</li>
				<li id="shortcode_view" class="motce-tab-li">
					<a href="
					<?php
					echo esc_url_raw(
						add_query_arg(
							array(
								'tab' => 'shortcode_view',
							)
						)
					);
					?>
							">
						<div id="application_div_id" class="motce-tab-li-div 
						<?php
						if ( 'shortcode_view' === $active_tab ) {
							echo 'motce-tab-li-div-active';
						}
						?>
						" aria-label="Application" title="Document Library" role="button" tabindex="0">
							<div id="add_icon" class="motce-tab-li-icon" >
								<span class="dashicons dashicons-admin-page" style="color:#0078D4;"></span>
							</div>
							<div id="add_app_label" class="motce-tab-li-label">
							Shortcode
							</div>
						</div>
					</a>
				</li>
				<li id="mail_view" class="motce-tab-li">
					<a href="
					<?php
					echo esc_url_raw(
						add_query_arg(
							array(
								'tab' => 'mail_view',
							)
						)
					);
					?>
							">
						<div id="application_div_id" class="motce-tab-li-div 
						<?php
						if ( 'mail_view' === $active_tab ) {
							echo 'motce-tab-li-div-active';
						}
						?>
						" aria-label="Application" title="Document Library" role="button" tabindex="0">
							<div id="add_icon" class="motce-tab-li-icon" >
								<span class="dashicons dashicons-email" style="color:#0078D4;"></span>
							</div>
							<div id="add_app_label" class="motce-tab-li-label">
							Send Mail
							</div>
						</div>
					</a>
				</li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Function to display active tab content.
	 *
	 * @param string $active_tab This includes value of active tab.
	 * @return void
	 */
	private function display_tab_content( $active_tab ) {
		$handler = self::get_view();
		switch ( $active_tab ) {
			case 'app_config':
				$handler = MOTCE_App_Config::get_view();
				break;
			case 'calendar_view':
				$handler = MOTCE_Calendar_View::get_view();
				break;
			case 'shortcode_view':
				$handler = MOTCE_Shortcode_View::get_view();
				break;
			case 'mail_view':
				$handler = MOTCE_Mail_View::get_view();
				break;
		}
		$handler->display_tab_details();
	}

	/**
	 * Function to show warning if class view is not loaded properly.
	 *
	 * @return void
	 */
	public function display_tab_details() {
		esc_html_e( "Class missing. Please check if you've installed the plugin correctly." );
	}
}
