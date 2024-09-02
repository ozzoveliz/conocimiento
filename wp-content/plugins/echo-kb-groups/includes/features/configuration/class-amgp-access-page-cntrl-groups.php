<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle managing Groups configuration
 */
class AMGP_Access_Page_Cntrl_Groups extends AMGP_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amgp_add_kb_groups_tabs_ajax', array( $this, 'display_groups_tab' ) );
		add_action( 'wp_ajax_nopriv_amgp_add_kb_groups_tabs_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amgp_add_kb_group_ajax', array( $this, 'create_kb_group' ) );
		add_action( 'wp_ajax_nopriv_amgp_add_kb_group_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_delete_kb_group_ajax', array( $this, 'delete_kb_group' ) );
		add_action( 'wp_ajax_nopriv_amgp_delete_kb_group_ajax', array( $this, 'user_not_logged_in' ) );
		add_action( 'wp_ajax_amgp_rename_kb_group_ajax', array( $this, 'rename_kb_group' ) );
		add_action( 'wp_ajax_nopriv_amgp_rename_kb_group_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amgp_reset_logs_ajax', array( $this, 'reset_log' ) );
		add_action( 'wp_ajax_nopriv_amgp_reset_logs_ajax', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Display KB Groups content
	 */
	public function display_groups_tab() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (201)', 'echo-knowledge-base' ));
		}

		// display groups tab
		$groups_view = new AMGP_Access_Page_View_Groups();
		ob_start();
		$result = $groups_view->ajax_update_tab_content( $kb_id );
		$output = ob_get_clean();

		if ( ! $result ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Internal error occurred (A183', 'echo-knowledge-base' ));
		}

		AMGP_Utilities::ajax_show_content( $output );
	}

	/**
	 * Create a new KB Group.
	 */
	public function create_kb_group() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Refresh your page first.', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		// retrieve Group Name
		$kb_group_name = self::get_validated_group_name_or_show_error();

		// ensure the KB Group name is unique in the current KB
		self::ensure_new_group_name_is_unique_or_show_error( $kb_group_name, $kb_id );

		// sanitize and add KB Group in the database
		$result = amgp_get_instance()->db_kb_groups->insert_group( $kb_id, $kb_group_name );
		if ( $result === null ) {
			AMGP_Utilities::ajax_show_error_die( 'Error Adding Group', 'Error occurred' );
		}

		// we are done here
		$reload = false;
		AMGP_Utilities::ajax_show_info_die( $reload ? esc_html__( 'Reload Settings saved. PAGE WILL RELOAD NOW.', 'echo-knowledge-base' ) : esc_html__( 'Group Added', 'echo-knowledge-base' ) );
	}

	/**
	 * Remove a KB Group.
	 */
	public function delete_kb_group() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Group NOT deleted. First refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (107)', 'echo-knowledge-base' ));
		}

		// retrieve Group Name
		$kb_group_name = empty($_POST['amgp_kb_group_name']) ? '' : sanitize_text_field( $_POST['amgp_kb_group_name'] );
		if ( empty($kb_group_name) || is_wp_error( $kb_group_name ) || strlen($kb_group_name) > 50 ) {
			AMGP_Logging::add_log( "invalid KB Group Name", $kb_group_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (108)', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (109)', 'echo-knowledge-base' ));
		}

		// prevent PUBLIC group deletion
		$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public || $is_group_public === null ) {
			return;
		}

		// do not allow to delete the last group  TODO FUTURE do not count PUBLIC and AUTHORIZED USERS groups
		$all_groups = amgp_get_instance()->db_kb_groups->get_groups( $kb_id );
		if ( $all_groups === null ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (119)', 'echo-knowledge-base' ));
		}

		// prevent users from deleting last KB Group
		if ( count($all_groups) < 2 ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Last KB Group cannot be deleted. If you want to clear that group, create a new KB Group and then delete this group.', 'echo-knowledge-base' ));
		}

		// retrieve KB Group
		$kb_group = amgp_get_instance()->db_kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $kb_group) ) {
			AMGP_Logging::add_log("Cannot delete KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id);
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (709)', 'echo-knowledge-base' ));
		}
		// user is trying to delete group that does not exist any more
		if ( empty($kb_group) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (110)', 'echo-knowledge-base' ));
		}

		// ensure that KB Group ID and KB Group Name matches
		if ( $kb_group->name !== $kb_group_name ) {
			AMGP_Logging::add_log( "KB Group names not matching", $kb_group_name );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (111)', 'echo-knowledge-base' ));
		}

		// ensure that KB Group has proper KB ID
		if ( $kb_group->kb_id != $kb_id ) {
			AMGP_Logging::add_log( "KB IDs are not matching", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (112)', 'echo-knowledge-base' ));
		}

		// delete the KB Group
		$result = amgp_get_instance()->db_kb_groups->delete_group( $kb_id, $kb_group_id );
		if ( empty($result) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error Deleting Group', esc_html__( 'Could not delete KB Group . ' . $kb_group_name, 'echo-knowledge-base' ) );
		}

		// delete all KB Group mappings
		$result = AMGP_KB_Core::delete_all_group_mappings( $kb_id, $kb_group_id );
		if ( ! $result ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Failed to delete the WP Role mapping.', 'echo-knowledge-base' ));
		}

		// we are done here
		AMGP_Utilities::ajax_show_info_die( esc_html__( 'Group Deleted', 'echo-knowledge-base' ) );
	}

	/**
	 * Rename KB Group.
	 */
	public function rename_kb_group() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'Group NOT renamed. First refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Logging::add_log( "invalid KB ID", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (113)', 'echo-knowledge-base' ));
		}

		// retrieve the new Group Name
		$new_kb_group_name = self::get_validated_group_name_or_show_error();

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgp_kb_group_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGP_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (115)', 'echo-knowledge-base' ));
		}

		// ensure the KB Group name is unique in the current KB
		self::ensure_new_group_name_is_unique_or_show_error( $new_kb_group_name, $kb_id, $kb_group_id );

		$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public || $is_group_public === null ) {
			return;
		}

		$kb_group = amgp_get_instance()->db_kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $kb_group) ) {
			AMGP_Logging::add_log("Cannot rename KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id);
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (716)', 'echo-knowledge-base' ));
		}
		if ( empty($kb_group) ) {
			AMGP_Logging::add_log( "Could not find the KB Group", $kb_group_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (116)', 'echo-knowledge-base' ));
		}

		// ensure that KB Group has proper KB ID
		if ( $kb_group->kb_id != $kb_id ) {
			AMGP_Logging::add_log( "KB IDs are not matching", $kb_id );
			AMGP_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (117)', 'echo-knowledge-base' ));
		}

		// rename the KB Group
		$result = amgp_get_instance()->db_kb_groups->rename_group( $kb_id, $kb_group_id, $new_kb_group_name );
		if ( empty($result) ) {
			AMGP_Utilities::ajax_show_error_die( 'Error Renaming Group', esc_html__( 'Could not add KB Group . ' . $new_kb_group_name, 'echo-knowledge-base' ) );
		}

		// we are done here
		AMGP_Utilities::ajax_show_info_die( esc_html__( 'Group Renamed', 'echo-knowledge-base' ) );
	}

	/**
	 * Get validated group name from POST request
	 *
	 * @return string
	 */
	private static function get_validated_group_name_or_show_error() {

		// retrieve Group Name
		$kb_group_name = empty( $_POST['amgp_kb_group_name'] ) ? '' : sanitize_text_field( $_POST['amgp_kb_group_name'] );

		// do not allow empty group name
		if ( empty( $kb_group_name ) ) {
			AMGP_Utilities::ajax_show_error_die(__( 'KB Group name can not be empty.', 'echo-knowledge-base' ));

		// do not allow group name longer than 50 letters
		} elseif ( strlen( $kb_group_name ) > 50 ) {
			AMGP_Utilities::ajax_show_error_die(__( 'KB Group name can not be longer than 50 letters.', 'echo-knowledge-base' ));
		}

		return $kb_group_name;
	}

	/**
	 * Ensure the new KB Group name is unique in the given KB - do not allow to have two groups with the same name in a single KB.
	 *
	 * @param $new_kb_group_name
	 * @param $kb_id
	 * @param int $kb_group_id
	 */
	private static function ensure_new_group_name_is_unique_or_show_error( $new_kb_group_name, $kb_id, $kb_group_id='0' ) {

		// make sure we have predictable types for comparison
		$kb_id = strval( $kb_id );
		$kb_group_id = strval( $kb_group_id );

		// try to find existing KB Group with new group name in the current KB
		$kb_group_by_new_name = amgp_get_instance()->db_kb_groups->get_a_row_by_where_clause( array( 'name' => $new_kb_group_name, 'kb_id' => $kb_id ) );

		// if KB Group with new name already exists in the current KB
		if ( ! is_wp_error( $kb_group_by_new_name) && ! empty( $kb_group_by_new_name ) ) {

			// - if updating existing group, then show error only if it is another group with the same name
			// - if creating a new group, then show error
			if ( ( intval( $kb_group_id ) > 0 && $kb_group_by_new_name->kb_group_id !== $kb_group_id ) || intval( $kb_group_id ) === 0 ) {
				AMGP_Utilities::ajax_show_error_die(__('The Group name already exists in the current KB.', 'echo-knowledge-base'));
			}
		}
	}

	/**
	 * Reset log
	 */
	public function reset_log() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_epkb_ajax_action'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_epkb_ajax_action'] ) ), '_wpnonce_epkb_ajax_action' ) ) {
			AMGP_Utilities::ajax_show_error_die( esc_html__( 'First refresh your page', 'echo-knowledge-base' ) );
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGP_Utilities::ajax_show_error_die( esc_html__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ) );
		}

		AMGP_Logging::reset_logs();

		AMGP_Utilities::ajax_show_info_die( 'Logs cleared.' );
	}
}