<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Parent class for all Controllers to manage access
 */
class AMGR_Access_Page_Controller {

	public function __construct() {
	}

	public function user_not_logged_in() {
		EPKB_Utilities::ajax_show_error_die( '<p>' . esc_html__( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', esc_html__( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}