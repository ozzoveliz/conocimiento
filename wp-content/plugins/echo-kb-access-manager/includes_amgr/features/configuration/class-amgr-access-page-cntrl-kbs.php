<?php

/**
 * Handle saving specific KB configuration.
 */
class AMGR_Access_Page_Cntrl_KBs extends AMGR_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amgr_display_kb_kbs_access_ajax', array( $this, 'display_access_tab' ) );
		add_action( 'wp_ajax_nopriv_amgr_display_kb_kbs_access_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amgr_reset_logs_ajax', array( $this, 'reset_log' ) );
		add_action( 'wp_ajax_nopriv_amgr_reset_logs_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_epkb_save_amgr_settings', array( $this, 'save_amgr_config_changes_in_db' ) );
		add_action( 'wp_ajax_nopriv_epkb_save_amgr_settings', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Display KB Access tab content
	 */
	public function display_access_tab() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_amar_access_content_action_ajax'], '_wpnonce_amar_access_content_action_ajax' ) ) {
			EPKB_Utilities::ajax_show_error_die(__( 'Refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die(__( 'You do not have permission to change access', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log( "invalid KB ID", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (201)', 'echo-knowledge-base' ));
		}

		// display access tab
		ob_start();
		$kbs_view = new AMGR_Access_Page_View_KB( $kb_id );
		$kbs_view->show_KB_section( $kb_id );
		$output = ob_get_clean();

		AMGR_Access_Utilities::ajax_show_content( $output );
	}

	public function reset_log() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_epkb_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_epkb_ajax_action'], '_wpnonce_epkb_ajax_action' ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'First refresh your page', 'echo-knowledge-base' ) );
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have ability to change access privileges.', 'echo-knowledge-base' )  . ' (E96)' );
		}

		AMGR_Logging::reset_logs();
		EPKB_Logging::reset_logs();

		// let other add-ons to clear the logs
		do_action('eckb_reset_error_Logs');

		EPKB_Utilities::ajax_show_info_die( 'Logs cleared.' );
	}

	/**
	* Triggered when user submits changes to KB configuration
	*/
	public function save_amgr_config_changes_in_db() {

		// verify that request is authentic
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die();

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have ability to change access privileges.', 'echo-knowledge-base' )  . ' (E97)' );
		}

		// retrieve KB ID we are saving
		$kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log( "invalid KB ID", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (201)', 'echo-knowledge-base' ));
		}

		// retrieve current AMGR KB configuration
		$orig_config = epkb_get_instance()->kb_access_config_obj->get_kb_config( $kb_id );
		if ( is_wp_error( $orig_config ) ) {
			EPKB_Utilities::ajax_show_error_die(__( 'Error occurred. ' . $orig_config->get_error_message() . '. Please refresh your browser and try again.', 'echo-knowledge-base' ));
		}

		// retrieve user input
		$field_specs = AMGR_KB_Config_Specs::get_fields_specification( $kb_id );
		$form_fields = empty($_POST['form']) ? array() : EPKB_Utilities::retrieve_and_sanitize_form( $_POST['form'], $field_specs );
		if ( empty($form_fields) ) {
			AMGR_Logging::add_log("form fields missing");
			EPKB_Utilities::ajax_show_error_die(__( 'Form fields missing. Please refresh your browser', 'echo-knowledge-base' ));
		}

		$input_handler = new EPKB_Input_Filter();
		$new_kb_config = $input_handler->retrieve_and_sanitize_form_fields( $form_fields, $field_specs, $orig_config );

		// ensure kb id is preserved
		$new_kb_config['id'] = $kb_id;

		// ensure that we have a valid configuration for custom redirect URL
		if ( ( $new_kb_config['no_access_action_user_without_login'] == 'redirect_to_custom_page' || $new_kb_config['no_access_action_user_with_login'] == 'redirect_to_custom_page' ) && empty( $new_kb_config['no_access_redirect_to_custom_page'] ) ) {
			EPKB_Utilities::ajax_show_error_die( __( 'Invalid custom redirect URL', 'echo-knowledge-base' ) );
		}

		// sanitize and save AMGR configuration in the database. see AMGR_Settings_DB class
		$result = epkb_get_instance()->kb_access_config_obj->update_kb_configuration( $kb_id, $new_kb_config );
		if ( is_wp_error($result) ) {
			AMGR_Logging::add_log( "Cannot update private article prefix", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'An error occured (431)', 'echo-knowledge-base' ));
		}

		// we are done here..
		EPKB_Utilities::ajax_show_info_die( esc_html__( 'Configuration updated.', 'echo-knowledge-base' ) );
	}
}
