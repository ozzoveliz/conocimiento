<?php
/**
 * Admin Controller.
 *
 * @package embed-outlook-teams-calendar-events/Controller
 */

namespace MOTCE\Controller;

use MOTCE\Wrappers\MOTCE_Plugin_Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class to handle form submissions actions.
 */
class MOTCE_Admin_Controller {

	/**
	 * Holds the MOTCE_Admin_Controller class instance.
	 *
	 * @var MOTCE_Admin_Controller
	 */
	private static $instance;

	/**
	 * Object instance(MOTCE_Admin_Controller) getter method.
	 *
	 * @return MOTCE_Admin_Controller
	 */
	public static function get_controller() {
		if ( ! isset( self::$instance ) ) {
			$class          = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/**
	 * Function to handle form submissions of all tabs.
	 *
	 * @return void
	 */
	public function handle_admin_controller() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['mo_controller_option'] ) || ! check_admin_referer( sanitize_text_field( wp_unslash( $_POST['mo_controller_option'] ) ) ) ) {
			return;
		}

		$handler = null;
		$config  = array();
		$option  = isset( $_POST['motce_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['motce_tab'] ) ) : '';

		$combined_keys = array_merge( MOTCE_Plugin_Constants::APP_CONFIG_KEYS, MOTCE_Plugin_Constants::MAIL_CONFIG_KEYS );

		foreach ( $combined_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				if ( 'mailFrom' === $key || 'mailTo' === $key ) {
					$config[ $key ] = sanitize_email( wp_unslash( $_POST[ $key ] ) );
				} else {
					$config[ $key ] = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
				}
			}
		}

		switch ( $option ) {
			case 'app_config_view':
				$handler      = MOTCE_App_Config::get_controller();
				$motce_option = sanitize_text_field( wp_unslash( $_POST['mo_controller_option'] ) );
				$config       = self::filter_config_keys( $config, MOTCE_Plugin_Constants::APP_CONFIG_KEYS );
				break;

			case 'mail_view':
				$handler      = MOTCE_Mail_Controller::get_controller();
				$motce_option = sanitize_text_field( wp_unslash( $_POST['mo_controller_option'] ) );
				$config       = self::filter_config_keys( $config, MOTCE_Plugin_Constants::MAIL_CONFIG_KEYS );
				break;
		}
		if ( $handler ) {
			$handler->save_settings( $motce_option, $config );
		}
	}

	/**
	 * Function to filter config keys based on a constant key array.
	 *
	 * @param array $config The configuration array to filter.
	 * @param array $constant_keys The constant key array to filter against.
	 * @return array The filtered configuration array.
	 */
	public function filter_config_keys( $config, $constant_keys ) {
		return array_filter(
			$config,
			function ( $key ) use ( $constant_keys ) {
				return in_array( $key, $constant_keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);
	}
}
