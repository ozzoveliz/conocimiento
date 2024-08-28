<?php

/**
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EMKB_Admin_Notices {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
		//add_action( 'emkb_dismiss_notices', array( $this, 'dismiss_admin_notices' ) );
	}

	/**
	 * Show noticers for admin at the top of the page
	 */
	public function show_admin_notices() {

		if ( empty($_GET['emkb_admin_notice']) ) {
			return;
		}

		$param = '';
		if ( ! empty($_GET['emkb_notice_param']) ) {
			$param = sanitize_text_field( $_GET['emkb_notice_param'] );
		}
		
		$param2 = '';
		if ( ! empty($_GET['emkb_notice_param_2']) ) {
			$param2 = sanitize_text_field( $_GET['emkb_notice_param_2'] );
		}

		$class = 'error emkb-notice';
		switch ( $_GET['emkb_admin_notice'] ) {

				case 'kb_refresh_page' :
					$message = __( 'Refresh your page', 'echo-knowledge-base' );
					$class = 'primary emkb-notice';
					break;
				case 'kb_refresh_page_error' :
					$message = __( 'Error occurred. Please refresh your browser and try again.', 'echo-knowledge-base' );
					break;
				case 'kb_security_failed' :
					$message = __( 'You do not have permission.', 'echo-knowledge-base' );
					break;
				case 'kb_add_error':
					$message = __( 'Could not create a new knowledge base. Please try again later.', 'echo-multiple-knowledge-bases' );
					break;
				case 'kb_add_success':
					$message = __( 'A new pre-configured Knowledge Base with KB Main page has been created. Use Wizard to complete the KB setup.', 'echo-multiple-knowledge-bases' );
					$class = 'success emkb-notice';
					break;
				case 'kb_archive_error':
					$message = __( 'Could not archive the knowledge base. Please try again later.', 'echo-multiple-knowledge-bases' );
					break;
				case 'kb_archive_success':
					$message = sprintf( __( "Knowledge Base '%s' was archived (hidden from users and KB screens).", 'echo-multiple-knowledge-bases' ), $param );
					$class = 'success emkb-notice';
					break;
				case 'kb_activate_error':
					$message = __( 'Could not activate the knowledge base. Please try again later.', 'echo-multiple-knowledge-bases' );
					break;
				case 'kb_activate_success':
					$message = sprintf( __( "Knowledge Base '%s' is now published.", 'echo-multiple-knowledge-bases' ), $param );
					$class = 'success emkb-notice';
					break;
				case 'kb_delete_error':
					$message = __( 'Could not delete the knowledge base. Please try again later.', 'echo-multiple-knowledge-bases' );
					break;
				case 'kb_delete_content_error':
					$message = sprintf( __( "Cannot delete '%s' knowledge base because it has articles and categories. To delete '%s' KB, first bulk delete all articles, categories and tags.", 'echo-multiple-knowledge-bases' ), $param, $param );
					break;
				case 'kb_delete_warning':
					$message = sprintf( __( "Could not delete '%s' knowledge base. You need to remove KB shortcode from the following pages: '%s'.", 'echo-multiple-knowledge-bases' ), $param, $param2 );
					break;
				case 'kb_delete_success':
					$message = sprintf( __( "Knowledge Base '%s' was deleted.", 'echo-multiple-knowledge-bases' ), $param );
					$class = 'success emkb-notice';
					break;
				default:
					$message = 'unknown error (133)';
					break;
			}

		echo  EMKB_Utilities::get_bottom_notice_message_box( $message, '', $class );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @return void
	 */
	function dismiss_admin_notices() {

		if ( empty( $_GET['emkb_dismiss_notice_nonce'] ) || ! wp_verify_nonce( $_GET['emkb_dismiss_notice_nonce'], 'emkb_dismiss_notice') ) {
			wp_die( __( 'Security check failed', 'echo-knowledge-base' ), __( 'Error', 'echo-knowledge-base' ), array( 'response' => 403 ) );
		}

		if ( ! empty( $_GET['emkb_admin_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_emkb_' . EMKB_Utilities::sanitize_english_text( $_GET['emkb_admin_notice'], 'EMKB admin notice' ) . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'emkb_action', 'emkb_admin_notice' ) ) );
			exit;
		}
	}
}
