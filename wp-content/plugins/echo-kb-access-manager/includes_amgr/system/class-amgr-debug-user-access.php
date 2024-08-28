<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contains methods to debug user access
 *
 * @copyright   Copyright (C) 2023, Echo Plugins
 */
class AMGR_Debug_User_Access {

	const AMGR_USER_ACCESS_DEBUG = 'amgr_debug_user_access';

	public function __construct() {
		add_action( 'wp_ajax_amgr_search_user', array( $this, 'search_user' ) );
		add_action( 'wp_ajax_nopriv_amgr_search_user', array( 'EPKB_Utilities', 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgr_save_debug_user_data', array( $this, 'save_debug_user_data' ) );
		add_action( 'wp_ajax_nopriv_amgr_save_debug_user_data', array( 'EPKB_Utilities', 'user_not_logged_in' ) );
	}

	/**
	 * Display Debug User Access Box.
	 * @return false|string
	 */
	public static function display_debug_user_access_box() {

		// get selected for debug user. if not set, turn off debug access toggler
		$debug_user_access_id = get_transient( self::AMGR_USER_ACCESS_DEBUG );
		$user = empty( $debug_user_access_id ) ? false : get_user_by( 'id', $debug_user_access_id );
		$user_email = $user ? $user->user_email : '-';
		$user_name = $user ? $user->display_name : '-';
		$debug_user_access_id = $user ? $debug_user_access_id : '';

		ob_start();     ?>

		<div id="amgr_display_debug_user_access">
			<section><?php
				EPKB_HTML_Elements::checkbox_toggle( [
					'id'            => self::AMGR_USER_ACCESS_DEBUG,
					'name'          => self::AMGR_USER_ACCESS_DEBUG,
					'text'          => __( 'Debugging', 'echo-knowledge-base' ),
					'textLoc'       => 'left',
					'checked'       => (bool)$debug_user_access_id,
				] );

				EPKB_HTML_Elements::text( [
					'label'         => __( 'Search User', 'echo-knowledge-base' ),
					'name'          => 'amgr_debug_user_search',
					'size'          => '50',
					'max'           => '100',
					'min'           => '0',
					'default'       => '',
					'value'         => '',
					'tooltip_body'  => esc_html__( 'Enter user ID or email or name.', 'echo-knowledge-base' ),
					'tooltip_args'  => [ 'open-icon' => 'info' ],
				] ); ?>
				<div class="epkb-input-group epkb-admin__user-field">
					<label><?php esc_html_e( 'User ID', 'echo-knowledge-base' ); ?>:</label> <strong id="amgr_debug_user_id"><?php echo $debug_user_access_id; ?></strong>
				</div>
				<div class="epkb-input-group epkb-admin__user-field">
					<label><?php esc_html_e( 'User Name', 'echo-knowledge-base' ); ?>:</label> <strong id="amgr_debug_user_name"><?php echo $user_name; ?></strong>
				</div>
				<div class="epkb-input-group epkb-admin__user-field">
					<label><?php esc_html_e( 'User email', 'echo-knowledge-base' ); ?>:</label> <strong id="amgr_debug_user_email"><?php echo $user_email; ?></strong>
				</div>
			</section>
			<section>				<?php
				EPKB_HTML_Elements::submit_button_v2( __( 'Apply', 'echo-knowledge-base' ), 'amgr_enable_debug_user_access', '', '', true, '', 'epkb-primary-btn' );				?>
			</section>
		</div> <?php

		return ob_get_clean();
	}

	/**
	 * Triggered when admin search users
	 */
	public static function search_user() {

		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die();

		$search = EPKB_Utilities::post( 'search' );
		if ( empty( $search ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'Wrong search request', 'echo-knowledge-base' ) );
		}

		$user = get_user_by( 'login', $search );

		if ( ! $user ) {
			$user = get_user_by( 'email', $search );
		}

		if ( ! $user ) {
			$user = get_user_by( 'id', $search );
		}

		if ( ! $user ) {
			$user = get_user_by( 'slug', $search );
		}

		if ( empty( $user ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'User not found', 'echo-knowledge-base' ) );
		}

		// show success message
		wp_die( json_encode( array( 'message' => EPKB_HTML_Forms::notification_box_bottom( __( 'User found', 'echo-knowledge-base' ) ), 'id' => $user->ID, 'name' => $user->display_name, 'email' => $user->user_email ) ) );
	}

	/**
	 * Triggered when admin enable/disable debug
	 */
	public static function save_debug_user_data() {

		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die();

		$enable = EPKB_Utilities::post( 'enable' );
		if ( $enable != '1' ) {
			delete_transient( self::AMGR_USER_ACCESS_DEBUG );
			EPKB_Utilities::ajax_show_info_die( __( 'Disabled User Access Debugging', 'echo-knowledge-base' ) );
		}

		$id = EPKB_Utilities::post( 'id' );
		if ( empty( $id ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'Invalid user ID', 'echo-knowledge-base' ) );
		}

		$user = get_user_by( 'id', $id );
		if ( empty( $user ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'User not found', 'echo-knowledge-base' ) );
		}

		set_transient( self::AMGR_USER_ACCESS_DEBUG, $id, HOUR_IN_SECONDS );

		EPKB_Utilities::ajax_show_info_die( __( 'Enabled User Access Debugging for one hour', 'echo-knowledge-base' ) );
	}
}
