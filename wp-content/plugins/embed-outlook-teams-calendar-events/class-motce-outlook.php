<?php
/**
 * Embed Outlook Calendars Class.
 *
 * @package embed-outlook-teams-calendar-events
 */

namespace MOTCE;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MOTCE\Controller\MOTCE_Admin_Controller;
use MOTCE\Observer\MOTCE_Admin_Observer;
use MOTCE\Observer\MOTCE_Embed_Observer;
use MOTCE\Observer\MOTCE_Forms_Observer;
use MOTCE\View\MOTCE_Admin_View;
use MOTCE\View\MOTCE_Feedback_Form;

/**
 * Class to initialize the plugin by loading required hooks.
 */
class MOTCE_Outlook {

	/**
	 * Holds the MOTCE_Outlook class instance.
	 *
	 * @var MOTCE_Outlook
	 */
	private static $instance;

	/**
	 * Loads class instance.
	 *
	 * @return MOTCE_Outlook
	 */
	public static function load_instance() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
			self::$instance->load_hooks();
		}
		return self::$instance;
	}

	/**
	 * Loads all required hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_settings_scripts' ) );
		add_action( 'admin_init', array( MOTCE_Admin_Controller::get_controller(), 'handle_admin_controller' ) );
		add_action( 'admin_footer', array( MOTCE_Feedback_Form::get_view(), 'motce_display_feedback_form' ) );
		add_action( 'admin_init', array( MOTCE_Forms_Observer::get_observer(), 'handle_forms_observer' ) );
		add_action( 'init', array( MOTCE_Admin_Observer::get_observer(), 'handle_admin_observer' ) );
		add_action( 'wp_ajax_motce_calendar_embed', array( MOTCE_Embed_Observer::get_observer(), 'handle_calendar_embed_api_handler' ) );
		add_shortcode( 'MO_OUTLOOK_CALENDAR', array( MOTCE_Embed_Observer::get_observer(), 'embed_shortcode_outlook_calendar' ) );
		register_uninstall_hook( __FILE__, 'uninstall' );
	}

	/**
	 * Register Plugin admin menu.
	 *
	 * @return void
	 */
	public function load_admin_menu() {
		$page = add_menu_page(
			'miniOrange Outlook Integration ' . __( '+ Sync' ),
			'miniOrange WP Outlook Sync',
			'manage_options',
			'motce',
			array( MOTCE_Admin_View::get_view(), 'display_plugin_menu' ),
			plugin_dir_url( __FILE__ ) . 'images/miniorange.png'
		);
	}

	/**
	 * Loads all required css files.
	 *
	 * @param string $page holds current page value.
	 * @return void
	 */
	public function load_settings_style( $page ) {
		if ( 'toplevel_page_motce' !== $page ) {
			return;
		}
		wp_enqueue_style( 'motce_supportform_css', plugins_url( 'includes/css/supportform.css', __FILE__ ), array(), MOTCE_PLUGIN_VERSION );
		wp_enqueue_style( 'motce_css', plugins_url( 'includes/css/motce_settings.css', __FILE__ ), array(), MOTCE_PLUGIN_VERSION );
		wp_enqueue_style( 'motce_calendar_view_css', plugins_url( 'includes/css/calendarview.css', __FILE__ ), array(), MOTCE_PLUGIN_VERSION );
	}

	/**
	 * Load all required script files.
	 *
	 * @param string $page holds current page value.
	 * @return void
	 */
	public function load_settings_scripts( $page ) {
		if ( 'toplevel_page_motce' !== $page ) {
			return;
		}
		$phone_js_url      = plugins_url( 'includes/js/phone.js', __FILE__ );
		$timepicker_js_url = plugins_url( 'includes/js/timepicker.min.js', __FILE__ );
		$select2_js_url    = plugins_url( 'includes/js/select2.min.js', __FILE__ );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'mo_epbr_phone_js', $phone_js_url, array(), MOTCE_PLUGIN_VERSION, 'all' );
		wp_enqueue_script( 'mo_epbr_timepicker_js', $timepicker_js_url, array(), MOTCE_PLUGIN_VERSION, 'all' );
		wp_enqueue_script( 'mo_epbr_select2_js', $select2_js_url, array(), MOTCE_PLUGIN_VERSION, 'all' );

		wp_enqueue_script( 'motce_supportform_js', plugins_url( 'includes/js/supportform.js', __FILE__ ), array(), MOTCE_PLUGIN_VERSION, true );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'moment' );
	}
}
