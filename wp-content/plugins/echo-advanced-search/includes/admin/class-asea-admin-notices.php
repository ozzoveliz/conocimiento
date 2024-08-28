<?php

/**
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_Admin_Notices {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		//add_action( 'asea_dismiss_notices', array( $this, 'dismiss_admin_notices' ) );
	}

	/**
	 * Show noticers for admin at the top of the page
	 */
	public function show_admin_notices() {

		if ( empty($_GET['asea_admin_notice']) ) {
			return;
		}

		$param = '';
		if ( ! empty($_GET['asea_notice_param']) ) {
			$param = ' ' . sanitize_text_field( $_GET['asea_notice_param'] );
		}

		$class = 'error';
		switch ( $_GET['asea_admin_notice'] ) {

				case 'kb_refresh_page' :
					$message = __( 'Refresh your page', 'echo-knowledge-base' );
					$class = 'primary';
					break;
				case 'kb_refresh_page_error' :
					$message = __( 'Error occurred. Please refresh your browser and try again.', 'echo-knowledge-base' );
					break;
				case 'kb_security_failed' :
					$message = __( 'You do not have permission.', 'echo-knowledge-base' );
					break;
				default:
					$message = 'unknown error (133)';
					break;
			}

		echo  ASEA_HTML_Forms::notification_box_bottom( $message, '', $class );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @return void
	 */
	function dismiss_admin_notices() {

		if ( empty( $_GET['asea_dismiss_notice_nonce'] ) || ! wp_verify_nonce( $_GET['asea_dismiss_notice_nonce'], 'asea_dismiss_notice') ) {
			wp_die( __( 'Security check failed', 'echo-knowledge-base' ), __( 'Error', 'echo-knowledge-base' ), array( 'response' => 403 ) );
		}

		if ( ! empty( $_GET['asea_admin_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_asea_' . ASEA_Utilities::sanitize_english_text( $_GET['asea_admin_notice'], 'ASEA admin notice' ) . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'asea_action', 'asea_admin_notice' ) ) );
			exit;
		}
	}
}
