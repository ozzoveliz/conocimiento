<?php

/**
 * Parent class for all Controllers to manage access
 */
class AMGR_Access_Page_Controller {

	public function __construct() {
	}

	public function user_not_logged_in() {
		EPKB_Utilities::ajax_show_error_die( '<p>' . __( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', __( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}