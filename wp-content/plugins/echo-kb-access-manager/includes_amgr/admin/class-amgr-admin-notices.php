<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Admin_Notices {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}

	/**
	 * Show noticers for admin at the top of the page
	 */
	public function show_admin_notices() {

		if ( ! EPKB_KB_Handler::is_kb_request() ) {
			return;
		}

		// get Access Manager errors
		$errors = AMGR_Logging::get_logs();
		$errors = array_slice($errors, 0, 1);
		$error_message = '';
		foreach( $errors as $error ) {
			$error_message .= AMGR_Logging::to_string($error) . '<br>';
		}

		// get KB errors
		$errors = EPKB_Logging::get_logs();
		$errors = array_slice($errors, 0, 2);
		foreach( $errors as $error ) {
			$error_message .= EPKB_Logging::to_string($error) . '<br>';
		}

		if ( empty( $error_message ) ) {
			return;
		}

		// get the problem detailed message
		$message = esc_html__( 'Please copy the error and contact customer support.' . '<br>' . 'You can then clear the error' . '<br>' . $error_message, 'echo-knowledge-base' );  ?>

		<div class="eckb-bottom-notice-message-large eckb-bottom-notice-message--error-large">

            <div class="eckb-bottom-notice-message__header">
                <div class="eckb-bottom-notice-message__header__title"><?php echo esc_html__( 'Access Manager found an issue', 'echo-knowledge-base' ); ?></div>
                    <div class="eckb-bottom-notice-message__header__clear-log">
                        <a href="#" class="amgr_notice_reset_logs_ajax" data-nonce="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); ?>"><?php echo esc_html__( 'Clear Log', 'echo-knowledge-base' ); ?></a>
                    </div>
                <div class="eckb-bottom-notice-message__header__close epkb-close-notice epkbfa epkbfa-window-close"></div>
            </div>

            <div class='contents'>
				<p>                            <?php
					echo wp_kses_post( $message ); ?>
                </p>
			</div>

		</div>			<?php
	}
}
