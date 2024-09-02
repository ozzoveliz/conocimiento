<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle managing Users configuration
 */
class AMGP_Access_Page_Cntrl_Users extends AMGP_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amgp_add_kb_user_roles_tabs_ajax', array( $this, 'display_user_roles_tab' ) );
		add_action( 'wp_ajax_nopriv_amgp_add_kb_user_roles_tabs_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_add_kb_group_user_ajax', array( $this, 'add_kb_group_user' ) );
		add_action( 'wp_ajax_nopriv_amgp_add_kb_group_user_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_remove_kb_group_user_ajax', array( $this, 'remove_kb_group_user' ) );
		add_action( 'wp_ajax_nopriv_amgp_remove_kb_group_user_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_filter_users_ajax', array( $this, 'filter_user_page' ) );
		add_action( 'wp_ajax_nopriv_amgp_filter_users_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_get_user_page_ajax', array( $this, 'get_user_page' ) );
		add_action( 'wp_ajax_nopriv_amgp_get_user_page_ajax', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Display KB Users content
	 */
	public function display_user_roles_tab() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have permission to change access', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (208)', 'echo-knowledge-base' ));
		}

		// retrieve KB Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (207)', 'echo-knowledge-base' ));
		}

		$active_role = empty($_POST['amgp_active_role']) ? '' : AMGP_Utilities::sanitize_english_text( $_POST['amgp_active_role'] );

		// display users tab
		$group_users_view = new AMGP_Access_Page_View_Users();
		ob_start();
		$result = $group_users_view->ajax_update_tab_content( $kb_id, $kb_group_id, $active_role );
		$output = ob_get_clean();

		if ( ! $result ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Internal error occurred (A143)', 'echo-knowledge-base' ));
		}

		AMGP_Utilities::ajax_show_content( $output );
	}

	/**
	 * Add user to KB Group
	 */
	public function add_kb_group_user() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'User NOT added. Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (208)', 'echo-knowledge-base' ));
		}

		// retrieve KB Role Name
		$kb_role_name = empty($_POST['amgp_kb_role_name']) ? '' : sanitize_text_field( $_POST['amgp_kb_role_name'] );
		if ( empty($kb_role_name) || is_wp_error( $kb_role_name ) || strlen($kb_role_name) > 50 ) {
			AMGP_Logging::add_log( "invalid KB Role Name", $kb_role_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (210)', 'echo-knowledge-base' ));
		}

		// retrieve WP User to add
		$wp_user_id = empty($_POST['amgp_wp_user_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_wp_user_id'] );
		if ( empty($wp_user_id) || is_wp_error( $wp_user_id ) ) {
			AMGP_Logging::add_log( "invalid WP User ID", $wp_user_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (231)', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (209)', 'echo-knowledge-base' ));
		}

		// add KB Managers
		if ( $kb_role_name === AMGP_KB_Role::KB_ROLE_MANAGER ) {
			$this->update_kb_managers( $wp_user_id, 'add' );
			return;
		}

		// PUBLIC group has no subscribers
		$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			return;
		}

		if ( $is_group_public && $kb_role_name == AMGP_KB_Role::KB_ROLE_SUBSCRIBER ) {
			return;
		}

		// retrieve the KB Group
		$kb_group = amgp_get_instance()->db_kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $kb_group) ) {
			AMGP_Logging::add_log("Cannot add user to the KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id);
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (730)', 'echo-knowledge-base' ));
		}
		if ( empty($kb_group) ) {
			AMGP_Logging::add_log( "Could not find group: ", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (230)', 'echo-knowledge-base' ));
		}

		// only allow user to have one KB Role per KB Group
		$current_user_role = amgp_get_instance()->db_kb_group_users->get_user_role_config( $kb_id, $kb_group_id, false );
		if ( $current_user_role === null ) {
			AMGP_Logging::add_log( "Failed to query KB User Group table.", $kb_id. ' - ' . $kb_group_id . '-' . $wp_user_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (331)', 'echo-knowledge-base' ));
		}
		if ( ! empty($current_user_role) ) {
			AMGP_Logging::add_log( "USER ADD: User " . $wp_user_id . " is already in KB Group " . $kb_group->name . ' as ' . $current_user_role, $wp_user_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (332)', 'echo-knowledge-base' ));
		}

		// add WP User to the KB Group
		$result = amgp_get_instance()->db_kb_group_users->add_group_user( $kb_id, $wp_user_id, $kb_group_id, $kb_role_name );
		if ( empty( $result ) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error Adding User to the Group', esc_html__( 'Could not add User. Role: ' . $kb_role_name, 'echo-knowledge-base' ) );
		}

		// we are done here
		AMGP_Utilities::ajax_show_info_die( esc_html__( 'Added User to Group', 'echo-knowledge-base' ) );
	}

	/**
	 * Remove user from KB Group
	 */
	public function remove_kb_group_user() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'User NOT added. Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (213)', 'echo-knowledge-base' ));
		}

		// retrieve KB Role Name
		$kb_role_name = empty($_POST['amgp_kb_role_name']) ? '' : sanitize_text_field( $_POST['amgp_kb_role_name'] );
		if ( empty($kb_role_name) || is_wp_error( $kb_role_name ) || strlen($kb_role_name) > 50 ) {
			AMGP_Logging::add_log( "invalid KB Role Name", $kb_role_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (215)', 'echo-knowledge-base' ));
		}

		// retrieve WP User to remove
		$wp_user_id = empty($_POST['amgp_wp_user_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_wp_user_id'] );
		if ( empty($wp_user_id) || is_wp_error( $wp_user_id ) ) {
			AMGP_Logging::add_log( "invalid WP User ID", $wp_user_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page maybe outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		// remove KB Managers
		if ( $kb_role_name === AMGP_KB_Role::KB_ROLE_MANAGER ) {
			$this->update_kb_managers( $wp_user_id, 'remove' );
			return;
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (243)', 'echo-knowledge-base' ));
		}

		$kb_group = amgp_get_instance()->db_kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $kb_group) ) {
			AMGP_Logging::add_log("Cannot remove user from KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id);
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (733)', 'echo-knowledge-base' ));
		}
		if ( empty($kb_group) ) {
			AMGP_Logging::add_log( "Could not retrieve user IDs for given role", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (233)', 'echo-knowledge-base' ));
			return;
		}

		// remove the WP User from the KB Group
		$result = amgp_get_instance()->db_kb_group_users->remove_group_user( $kb_group_id, $wp_user_id );
		if ( empty($result) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error Removing User from Group', esc_html__( 'Could not remove User . ' . $kb_role_name, 'echo-knowledge-base' ) );
		}

		// we are done here
		AMGP_Utilities::ajax_show_info_die( esc_html__( 'Removed User from Group', 'echo-knowledge-base' ) );
	}

	/**
	 * Filter users page
	 */
	public function filter_user_page() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amgp_get_user_page_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amgp_get_user_page_ajax'] ) ), '_wpnonce_amgp_get_user_page_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (219)', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (283)', 'echo-knowledge-base' ));
		}

		// retrieve KB Role Name
		$kb_role_name = empty($_POST['amgp_kb_role_name']) ? '' : sanitize_text_field( $_POST['amgp_kb_role_name'] );
		if ( empty($kb_role_name) || is_wp_error( $kb_role_name ) || strlen($kb_role_name) > 50 ) {
			AMGP_Logging::add_log( "invalid KB Role Name", $kb_role_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (215)', 'echo-knowledge-base' ));
		}

		// retrieve CURRENT page number
		$user_search_filter = AMGP_Utilities::post('amag_user_filter');

		ob_start();
		$result = AMGP_Access_Page_View_Users::get_user_page( $kb_id, $kb_group_id, $kb_role_name, 1, $user_search_filter );
		if ( empty($result) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error filtering users' );
		}
		$output = ob_get_clean();
		wp_die( wp_json_encode(  array( 'message' => $output, 'role' => $kb_role_name ) ) );
	}

	/**
	 * Get the next page
	 */
	public function get_user_page() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amgp_get_user_page_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amgp_get_user_page_ajax'] ) ), '_wpnonce_amgp_get_user_page_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (219)', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (283)', 'echo-knowledge-base' ));
		}

		// retrieve KB Role Name
		$kb_role_name = empty($_POST['amgp_kb_role_name']) ? '' : sanitize_text_field( $_POST['amgp_kb_role_name'] );
		if ( empty($kb_role_name) || is_wp_error( $kb_role_name ) || strlen($kb_role_name) > 50 ) {
			AMGP_Logging::add_log( "invalid KB Role Name", $kb_role_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (215)', 'echo-knowledge-base' ));
		}

		// retrieve CURRENT page number
		$current_page_number = AMGP_Utilities::post( 'amag_current_page_number', 0 );
		$current_page_number = AMGP_Utilities::sanitize_get_id( $current_page_number );
		if ( empty($current_page_number) || is_wp_error( $current_page_number ) ) {
			AMGP_Logging::add_log( "invalid current user page number", $current_page_number );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (219)', 'echo-knowledge-base' ));
		}

		// retrieve NEXT page number
		$next_page_number = AMGP_Utilities::post( 'amag_next_page_number', 0 );
		if ( empty($next_page_number) || is_wp_error( $next_page_number ) ) {
			AMGP_Logging::add_log( "invalid current user page number", $current_page_number );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (213)', 'echo-knowledge-base' ));
		}

		$next_page_number = $next_page_number == esc_html__('Previous') ? $current_page_number - 1 : ( $next_page_number == esc_html__('Next') ? $current_page_number + 1 : $next_page_number);
		$next_page_number = (int) $next_page_number;
		$next_page_number = $next_page_number < 0 ? 1 : $next_page_number;
		if ( empty($next_page_number) || ! is_int( $next_page_number ) ) {
			AMGP_Logging::add_log( "invalid new user page number", $next_page_number );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (219)', 'echo-knowledge-base' ));
		}

		ob_start();
		$result = AMGP_Access_Page_View_Users::get_user_page( $kb_id, $kb_group_id, $kb_role_name, $next_page_number );
		if ( empty($result) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error getting users' );
		}
		$output = ob_get_clean();
		wp_die( wp_json_encode(  array( 'message' => $output, 'role' => $kb_role_name ) ) );
	}

	/**
	 * Remov KB Manager from the KB
	 *
	 * @param $wp_user_id
	 * @param $action
	 */
	private function update_kb_managers( $wp_user_id, $action ) {

		// only admin users can do this
		if ( ! current_user_can( 'manage_options' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// add new KB Manager to the group
		$kb_managers = AMGP_Groups::get_kb_managers();
		if ( $kb_managers === null ) {
			AMGP_Logging::add_log( "USER REMOVE: Failed to get config for removing user " . $wp_user_id . ' as KB Manager.' );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (781)', 'echo-knowledge-base' ));
		}

		// perform the operation
		if ( $action == 'add' ) {
			$kb_managers[$wp_user_id] = $wp_user_id;
		} else if ( isset($kb_managers[$wp_user_id]) ) {
			unset($kb_managers[$wp_user_id]);
		}

		$result = AMGP_Groups::set_kb_managers( $kb_managers );
		if ( is_wp_error($result) ) {
			AMGP_Logging::add_log( "ADD/REMOVE KB Manager: Failed to add/remove user: " . $wp_user_id . ' as KB Manager.' );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (782)', 'echo-knowledge-base' ));
		}

		// we are done here
		AMGP_Utilities::ajax_show_info_die( esc_html__( 'Removed User', 'echo-knowledge-base' ) );
	}
}