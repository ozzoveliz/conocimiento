<?php

/**
 * Handle managing Category access configuration
 */
class AMGR_Access_Page_Cntrl_Categories extends AMGR_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amgr_display_kb_category_access_ajax', array( $this, 'display_access_tab' ) );
		add_action( 'wp_ajax_nopriv_amgr_display_kb_category_access_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amgr_save_categories_access_ajax', array( $this, 'save_category_access' ) );
		add_action( 'wp_ajax_nopriv_amgr_save_categories_access_ajax', array( $this, 'user_not_logged_in' ) );
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
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have permission to change access', 'echo-knowledge-base' ) . ' (E95)' );
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log( "invalid KB ID", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (201)', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgr_kb_group_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amgr_kb_group_id'] );
		if ( is_wp_error( $kb_group_id ) ) {
			AMGR_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (109)', 'echo-knowledge-base' ));
		}

		// display access tab
		$group_users_view = new AMGR_Access_Page_View_Categories( $kb_id );
		ob_start();
		$result = $group_users_view->ajax_update_tab_content( $kb_group_id );
		$output = ob_get_clean();

		if ( ! $result ) {
			EPKB_Utilities::ajax_show_error_die(__( 'Internal error occurred (A143', 'echo-knowledge-base' ));
		}

		AMGR_Access_Utilities::ajax_show_content( $output );
	}

	/**
	 * For given group save categories access.
	 */
	public function save_category_access() {

		// verify that the request is authentic
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die( EPKB_Admin_UI_Access::EPKB_KB_MANAGER_CONFIG );

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die(__( 'You do not have permission to change access', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			//AMGR_Logging::add_log( "invalid KB ID", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgr_kb_group_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amgr_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			//AMGR_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (119)', 'echo-knowledge-base' ));
		}

		// retrieve submitted categories and they access
		$new_categories_access = $this->get_categories_access_data();
		if ( empty( $new_categories_access ) ) {
			//AMGR_Logging::add_log( "no categories" );
			EPKB_Utilities::ajax_show_error_die( __( 'Your KB Group has no categories.', 'echo-knowledge-base' ) );
		}

		$new_full_access_categories_ids = $new_categories_access['full-access'];
		$new_read_access_categories_ids = $new_categories_access['read-access'];
		$new_no_access_categories_ids = $new_categories_access['no-access'];

		// 1. get existing categories access

		// retrieve all KB categories
		$all_kb_categories = EPKB_Core_Utilities::get_kb_categories_unfiltered( $kb_id );
		if ( $all_kb_categories === null ) {
			//AMGR_Logging::add_log( "internal error (111)" );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (111)', 'echo-knowledge-base' ));
		}

		$categories_seq_data = AMGR_Access_Utilities::get_categories_from_sequence( $kb_id );
		if ( $categories_seq_data === null ) {
			//AMGR_Logging::add_log( "internal error (115)" );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (115)', 'echo-knowledge-base' ));
		}

		$all_kb_categories_ids = array();
		foreach( $all_kb_categories as $kb_category ) {
			if ( ! empty($categories_seq_data[$kb_category->term_id]) && $categories_seq_data[$kb_category->term_id] < 4 ) {
				$all_kb_categories_ids[] = $kb_category->term_id;
			}
		}

		// retrieve current KB and KB Group category access
		$full_access_categories_ids = AMGR_Access_Utilities::get_group_categories_ids( $kb_id, $kb_group_id );
		if ( $full_access_categories_ids === null ) {
			//AMGR_Logging::add_log( "internal error (444)", $kb_group_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (444)', 'echo-knowledge-base' ));
		}

		// get read-only public categories
		$read_only_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $kb_group_id );
		if ( $read_only_categories_ids === null ) {
			//AMGR_Logging::add_log( "internal error (445)", $kb_group_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (445)', 'echo-knowledge-base' ));
		}

		// 2. ensure we got access for all categories
		$new_all_categories_ids = array_merge($new_full_access_categories_ids, $new_read_access_categories_ids, $new_no_access_categories_ids);
		$common_categories = array_intersect($all_kb_categories_ids, $new_all_categories_ids);
		if ( count($common_categories) != count($all_kb_categories_ids) ) {
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (112)', 'echo-knowledge-base' ));
		}

		// 3. remove categories that should no longer have access
		foreach( $full_access_categories_ids as $full_category_id ) {
			if ( ! in_array($full_category_id, $new_full_access_categories_ids) || in_array($full_category_id, $new_no_access_categories_ids) ) {
				// remove old access
				$result = epkb_get_instance()->db_access_kb_categories->delete_group_category( $kb_id, $kb_group_id, $full_category_id );
				if ( empty($result) ) {
				//	AMGR_Logging::add_log( "internal error (446)" );
					EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (149)', 'echo-knowledge-base' ));
				}
			}
		}

		// 4. add categories that should have access
		foreach( $new_full_access_categories_ids as $new_full_category_id ) {
			// do not add existing categories with access
			if ( in_array($new_full_category_id, $full_access_categories_ids) ) {
				continue;
			}

			$result = epkb_get_instance()->db_access_kb_categories->add_category_to_group( $kb_id, $kb_group_id, $new_full_category_id );
			if ( ! $result ) {
				//AMGR_Logging::add_log( "internal error (151)" );
				EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (151)', 'echo-knowledge-base' ));
			}
		}

		// 5. remove READ-ONLY categories
		foreach( $read_only_categories_ids as $read_only_category_id ) {
			if ( ! in_array($read_only_category_id, $new_read_access_categories_ids) || in_array($read_only_category_id, $new_no_access_categories_ids) ) {
				// remove old access
				$result = epkb_get_instance()->db_access_read_only_categories->delete_group_category( $kb_id, $kb_group_id, $read_only_category_id );
				if ( empty($result) ) {
				//	AMGR_Logging::add_log( "internal error (152)" );
					EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (152)', 'echo-knowledge-base' ));
				}
			}
		}

		// 6. add READ-ONLY categories that should have access
		foreach( $new_read_access_categories_ids as $new_read_category_id ) {
			// do not add existing categories with access
			if ( in_array($new_read_category_id, $read_only_categories_ids) ) {
				continue;
			}

			$result = epkb_get_instance()->db_access_read_only_categories->add_read_only_group_to_category( $kb_id, $kb_group_id, $new_read_category_id );
			if ( ! $result ) {
			//	AMGR_Logging::add_log( "internal error (151)" );
				EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (151)', 'echo-knowledge-base' ));
			}
		}

		// we are done here
		EPKB_Utilities::ajax_show_info_die( esc_html__( 'Access updated', 'echo-knowledge-base' ) );
	}

	private function get_categories_access_data() {

		// retrieve all selected Categories
		$new_categories_access_post = EPKB_Utilities::post( 'amgr_category_data' );
		if ( empty( $new_categories_access_post ) ) {
			return false;
		}

		$new_full_access_categories_ids = array();
		$new_read_access_categories_ids = array();
		$new_no_access_categories_ids = array();
		foreach( $new_categories_access_post as $access ) {
			$parts = explode('=', $access);
			if ( count($parts) != 2 ) {
				return false;
			}

			$kb_category_id = $parts[0];
			if ( ! EPKB_Utilities::is_positive_int($kb_category_id) ) {
				return false;
			}

			$access_level = $parts[1];
			if ( ! in_array($access_level, array('amgr-full-access', 'amgr-read-access', 'amgr-no-access')) ) {
				return false;
			}

			if ( $access_level === 'amgr-full-access' ) {
				$new_full_access_categories_ids[] = $kb_category_id;
			} else if ( $access_level === 'amgr-read-access' ) {
				$new_read_access_categories_ids[] = $kb_category_id;
			} else {
				$new_no_access_categories_ids[] = $kb_category_id;
			}
		}

		return array( 'full-access' => $new_full_access_categories_ids, 'read-access' => $new_read_access_categories_ids, 'no-access' => $new_no_access_categories_ids);
	}
}