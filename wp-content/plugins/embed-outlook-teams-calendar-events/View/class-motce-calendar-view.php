<?php
/**
 * Calendar Preiew.
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
 * Class to load view for Outlook calendar.
 */
class MOTCE_Calendar_View {

	/**
	 * Holds the MOTCE_Calendar_View class instance.
	 *
	 * @var MOTCE_Calendar_View
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Calendar_View) getter method.
	 *
	 * @return MOTCE_Calendar_View
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
			<div style="width: 100%">
					<?php
					$this->load_calendar_view();
					?>
			</div>
		</div>
		<?php
	}

	/**
	 * Function to load outlook calendar tile.
	 *
	 * @return void
	 */
	private function load_calendar_view() {

		$upn = MOTCE_WP_Wrapper::get_option( MOTCE_Plugin_Constants::UPN );

		?>
			<div class="motce_calendar_container">
				<div id="motce_group_calendar_adv">
					<div class="user_email">
						<div class="email_label"> Calendar displayed for the user : </div>
						<div class="email_label_input"> <input disabled type="email" name="user_mail" value="<?php echo esc_html( $upn ); ?>"> </div>
					</div>
					<div class="dropdown-row">
					<div class="dropdown-col">
						<div class="dropdown-container">
							<div class="dropdown-toggle click-dropdown">
							Personal Calendar
							</div>
							<div class="dropdown-menu premium-content">
							<ul>
								<li><a href="#" disabled>Group Calendar <svg class="crown-premium" id="fi_2665606" enable-background="new 0 0 511.883 511.883" height="512" viewBox="0 0 511.883 511.883" width="512" xmlns="http://www.w3.org/2000/svg"><g><path d="m384.906 193.554 7.559 15.117c10.759 21.546.39 48.153-27.466 55.898-.352.117-38.936 10.474-50.654-24.624l-11.294-33.926 32.139-32.153-79.307-118.945-79.307 118.945 32.139 32.153-11.294 33.926c-12.925 38.725-66.118 29.608-78.662 2.3-4.951-10.752-4.746-22.983.542-33.574l7.544-15.103-126.845-45.278 60.883 233.672 30 30h165 165l30-30 61-233.657z" fill="#fed843"></path><path d="m450.883 381.962 61-233.657-126.977 45.249 7.559 15.117c10.759 21.546.39 48.153-27.466 55.898-.352.117-38.936 10.474-50.654-24.624l-11.294-33.926 32.139-32.153-79.307-118.945v357.041h165z" fill="#fabe2c"></path><path d="m255.883 381.962h-195v75h195 195v-75z" fill="#fabe2c"></path><path d="m255.883 381.962h195v75h-195z" fill="#ff9100"></path><path d="m202.85 328.929h106.066v106.066h-106.066z" fill="#fabe2c" transform="matrix(.707 -.707 .707 .707 -195.142 292.811)"></path><path d="m255.883 306.962v150l75-75z" fill="#ff9100"></path></g></svg></a></li>
								<span class="tooltip-premium" disabled>It is premium content</span>
							</ul>
							</div>
						</div>
					</div>
					</div>
				</div>
				<div id="motce_calendar">
					<div id="calendar_loader" style="border:1px solid #eee;color:#000;height:100%;display:flex;justify-content:center;align-items:center;font-size:14px;text-align:center;flex-direction:column;">
						<img style="width:50px;height:50px" src="<?php echo esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'outlook.svg' ) ); ?>">
						<img style="width:50px;height:50px;margin-left:10px;" src="<?php echo esc_url_raw( MOTCE_WP_Wrapper::get_image_src( 'loader.gif' ) ); ?>">
					</div>
				</div>
			</div>
		<?php

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'motce_calendar_view_js', plugins_url( '../includes/js/calendarview.js', __FILE__ ), array( 'jquery' ), MOTCE_PLUGIN_VERSION, false );
		wp_localize_script(
			'motce_calendar_view_js',
			'embedConfig',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( MOTCE_Plugin_Constants::EMBED_CALENDAR_AJAX_NC ),
				'upnID'    => $upn,
			)
		);
	}
}
