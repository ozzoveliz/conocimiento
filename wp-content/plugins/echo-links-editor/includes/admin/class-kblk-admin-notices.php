<?php

/**
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class KBLK_Admin_Notices {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		//add_action( 'kblk_dismiss_notices', array( $this, 'dismiss_admin_notices' ) );
	}

	/**
	 * Show noticers for admin at the top of the page
	 */
	public function show_admin_notices() {

		if ( empty($_GET['kblk_admin_notice']) ) {
			return;
		}

		$param = '';
		if ( ! empty($_GET['kblk_notice_param']) ) {
			$param = ' ' . sanitize_text_field( $_GET['kblk_notice_param'] );
		}

		$class = 'error kblk-notice';
		switch ( $_GET['kblk_admin_notice'] ) {

				case 'kb_refresh_page' :
					$message = __( 'Refresh your page', 'echo-knowledge-base' );
					$class = 'primary kblk-notice';
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

		echo  KBLK_Utilities::get_bottom_notice_message_box( $message, '', $class );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @return void
	 */
	function dismiss_admin_notices() {

		if ( empty( $_GET['kblk_dismiss_notice_nonce'] ) || ! wp_verify_nonce( $_GET['kblk_dismiss_notice_nonce'], 'kblk_dismiss_notice') ) {
			wp_die( __( 'Security check failed', 'echo-knowledge-base' ), __( 'Error', 'echo-knowledge-base' ), array( 'response' => 403 ) );
		}

		if ( ! empty( $_GET['kblk_admin_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_kblk_' . KBLK_Utilities::sanitize_english_text( $_GET['kblk_admin_notice'], 'KBLK admin notice' ) . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'kblk_action', 'kblk_admin_notice' ) ) );
			exit;
		}
	}
}
