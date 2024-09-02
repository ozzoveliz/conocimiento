<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle managing WP Role mapping
 */
class AMCR_Access_Page_Cntrl_WP_Roles extends AMCR_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amcr_display_wp_roles_tabs_ajax', array( $this, 'display_wp_roles_tab' ) );
		add_action( 'wp_ajax_nopriv_amcr_display_wp_roles_tabs_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amcr_add_wp_role_ajax', array( $this, 'create_wp_role_mapping' ) );
		add_action( 'wp_ajax_nopriv_amcr_add_wp_role_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amcr_delete_wp_role_ajax', array( $this, 'delete_wp_role_mapping' ) );
		add_action( 'wp_ajax_nopriv_amcr_delete_wp_role_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amcr_reset_logs_ajax', array( $this, 'reset_log' ) );
		add_action( 'wp_ajax_nopriv_amcr_reset_logs_ajax', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Display WP Role mapping
	 */
	public function display_wp_roles_tab() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMCR_Utilities::ajax_show_error_die(__( 'Refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMCR_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMCR_Logging::add_log( "invalid KB ID", $kb_id );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (201)', 'echo-knowledge-base' ));
		}

		// display WP Roles tab
		$wp_roles_view = new AMCR_Access_Page_View_WP_Roles();
		ob_start();
		$result = $wp_roles_view->ajax_update_tab_content( $kb_id );
		$output = ob_get_clean();

		if ( ! $result ) {
			AMCR_Utilities::ajax_show_error_die(__( 'Internal error occurred (A183', 'echo-knowledge-base' ));
		}

		AMCR_Utilities::ajax_show_content( $output );
	}

	/**
	 * Create a new WP Role mapping.
	 */
	public function create_wp_role_mapping() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMCR_Utilities::ajax_show_error_die(__( 'Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMCR_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMCR_Logging::add_log( "invalid KB ID", $kb_id );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		// retrieve WP Role Name
		$wp_role_name = AMCR_Utilities::post( 'amcr_wp_role_name' );
		if ( empty($wp_role_name) || is_wp_error( $wp_role_name ) || strlen($wp_role_name) > 50 ) {
			AMCR_Logging::add_log( "invalid WP Role Name", $wp_role_name );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (10)', 'echo-knowledge-base' ));
		}

		// if we have KB Groups and Roles add-on, handle KB Groups
		$kb_group_id = '';
		if ( AMCR_KB_Core::use_kb_groups() ) {

			// retrieve KB Group ID
			$kb_groups = AMCR_KB_Core::get_kb_groups( $kb_id );
			if ( $kb_groups === null ) {
				AMCR_Logging::add_log( "Could not retrieve KB Groups", $kb_id );
				AMCR_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (14)', 'echo-knowledge-base' ) );
			}
			$kb_groups_ids = array();
			foreach ( $kb_groups as $kb_group ) {
				$kb_groups_ids[] = $kb_group->kb_group_id;
			}

			$kb_group_id = AMCR_Utilities::post( 'amcr_kb_group_id' );
			if ( empty( $kb_group_id ) || is_wp_error( $kb_group_id ) || ! in_array( $kb_group_id, $kb_groups_ids ) ) {
				AMCR_Logging::add_log( "invalid KB Group ID", $kb_group_id );
				AMCR_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (13)', 'echo-knowledge-base' ) );
			}

		}

		// retrieve KB Role Name
		$kb_role_name = AMCR_Utilities::post( 'amcr_kb_role_name' );
		if ( empty($kb_role_name) || is_wp_error( $kb_role_name ) || strlen($kb_role_name) > 50 ) {
			AMCR_Logging::add_log( "invalid KB Role Name", $wp_role_name );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (11)', 'echo-knowledge-base' ));
		}

		$result = AMCR_KB_Core::update_wp_role_mapping( $kb_id, $wp_role_name, $kb_role_name, $kb_group_id );
		if ( ! $result ) {
			AMCR_Logging::add_log( "Could not update WP Role mapping", $wp_role_name );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (12)', 'echo-knowledge-base' ));
		}

		// we are done here
		$reload = false;
		AMCR_Utilities::ajax_show_info_die( $reload ? esc_html__( 'Reload Settings saved. PAGE WILL RELOAD NOW.', 'echo-knowledge-base' ) : esc_html__( 'WP Role Added', 'echo-knowledge-base' ) );
	}

	/**
	 * Remove a WP Role mapping.
	 */
	public function delete_wp_role_mapping() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMCR_Utilities::ajax_show_error_die(__( 'WP Role NOT deleted. First refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMCR_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMCR_Logging::add_log( "invalid KB ID", $kb_id );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (107)', 'echo-knowledge-base' ));
		}

		// if we have KB Groups and Roles add-on, handle KB Groups
		$kb_group_id = '';
		if ( AMCR_KB_Core::use_kb_groups() ) {

			// retrieve KB Group ID
			$kb_groups = AMCR_KB_Core::get_kb_groups( $kb_id );
			if ( $kb_groups === null ) {
				AMCR_Logging::add_log( "Could not retrieve KB Groups", $kb_id );
				AMCR_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (14)', 'echo-knowledge-base' ) );
			}
			$kb_groups_ids = array();
			foreach ( $kb_groups as $kb_group ) {
				$kb_groups_ids[] = $kb_group->kb_group_id;
			}

			$kb_group_id = AMCR_Utilities::post( 'amcr_kb_group_id' );
			if ( empty( $kb_group_id ) || is_wp_error( $kb_group_id ) || ! in_array( $kb_group_id, $kb_groups_ids ) ) {
				//AMCR_Logging::add_log( "invalid KB Group ID", $kb_group_id );
				//AMCR_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (13)', 'echo-knowledge-base' ) );
			}
		}

		// retrieve WP Role Name
		$wp_role_name = AMCR_Utilities::post( 'amcr_wp_role_name' );
		if ( empty($wp_role_name) || is_wp_error( $wp_role_name ) || strlen($wp_role_name) > 50 ) {
			AMCR_Logging::add_log( "invalid WP Role Name", $wp_role_name );
			AMCR_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		$result = AMCR_KB_Core::delete_wp_role_mapping( $kb_id, $wp_role_name, $kb_group_id );
		if ( ! $result ) {
			AMCR_Utilities::ajax_show_error_die(__( 'Failed to delete the WP Role mapping.', 'echo-knowledge-base' ));
		}

		// we are done here
		AMCR_Utilities::ajax_show_info_die( esc_html__( 'WP Role mapping Deleted', 'echo-knowledge-base' ) );
	}

	/**
	 * Reset Logs
	 */
	public function reset_log() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_epkb_ajax_action'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_epkb_ajax_action'] ) ), '_wpnonce_epkb_ajax_action' ) ) {
			AMCR_Utilities::ajax_show_error_die( esc_html__( 'First refresh your page', 'echo-knowledge-base' ) );
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::ajax_show_error_die( esc_html__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ) );
		}

		AMCR_Logging::reset_logs();

		AMCR_Utilities::ajax_show_info_die( 'Logs cleared.' );
	}

}