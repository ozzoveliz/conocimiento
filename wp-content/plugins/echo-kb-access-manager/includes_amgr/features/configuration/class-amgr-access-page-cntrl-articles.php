<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle managing Articles access configuration
 */
class AMGR_Access_Page_Cntrl_Articles extends AMGR_Access_Page_Controller {

	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_amgr_display_kb_articles_access_ajax', array( $this, 'display_access_tab' ) );
		add_action( 'wp_ajax_nopriv_amgr_display_kb_articles_access_ajax', array( $this, 'user_not_logged_in' ) );

		add_action( 'wp_ajax_amgr_save_articles_access_ajax', array( $this, 'save_article_access' ) );
		add_action( 'wp_ajax_nopriv_amgr_save_articles_access_ajax', array( $this, 'user_not_logged_in' ) );
	}

	/**
	 * Display KB Access tab content
	 */
	public function display_access_tab() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amar_access_content_action_ajax'] ) ), '_wpnonce_amar_access_content_action_ajax' ) ) {
			EPKB_Utilities::ajax_show_error_die(__( 'Refresh your page', 'echo-knowledge-base' ));
		}

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die( esc_html__( 'You do not have permission to change access', 'echo-knowledge-base' ) . ' (E87)' );
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
		$group_users_view = new AMGR_Access_Page_View_Articles( $kb_id );
		ob_start();
		$result = $group_users_view->ajax_update_tab_content( $kb_group_id );
		$output = ob_get_clean();

		if ( ! $result ) {
			EPKB_Utilities::ajax_show_error_die(__( 'Internal error occurred (A143', 'echo-knowledge-base' ));
		}

		AMGR_Access_Utilities::ajax_show_content( $output );
	}

	/**
	 * For given group save articles access.
	 */
	public function save_article_access() {

		// verify that the request is authentic
		EPKB_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die();

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die(__( 'You do not have permission to change access', 'echo-knowledge-base' ));
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log( "invalid KB ID", $kb_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
		}

		// retrieve Group ID
		$kb_group_id = empty($_POST['amgr_kb_group_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amgr_kb_group_id'] );
		if ( empty($kb_group_id) || is_wp_error( $kb_group_id ) ) {
			AMGR_Logging::add_log( "invalid KB Group ID", $kb_group_id );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (119)', 'echo-knowledge-base' ));
		}

		// retrieve submitted articles and they access
		$new_article_access = $this->get_article_access_data();
		if ( $new_article_access === false ) {
			AMGR_Logging::add_log( "internal error (11)" );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (11)', 'echo-knowledge-base' ));
		}

		// get all group full-access articles that are not part of post data
		$group_categories_ids = AMGR_Access_Utilities::get_group_categories_ids( $kb_id, $kb_group_id );
		if ( $group_categories_ids === null ) {
			return false;
		}

		// get all read-only articles for given group
		/* $read_only_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $kb_group_id );
		if ( $read_only_articles_ids === null ) {
			AMGR_Logging::add_log( "could not get read-only article ids" );
			EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (77)', 'echo-knowledge-base' ));
			return false;
		} */

		// first delete all read-only articles access
		$result = epkb_get_instance()->db_access_read_only_articles->delete_group_articles( $kb_id, $kb_group_id );
		if ( empty($result) ) {
			AMGR_Logging::add_log( "Could not delete read-only article access", $kb_id );
			AMGR_Access_Utilities::output_inline_error_notice( 'Internal Error occurred (44)' );
			return false;
		}

		// now add read-only article access
		foreach( $new_article_access as $kb_article_id => $article_access ) {
			if ( $article_access === true ) {
				$result = epkb_get_instance()->db_access_read_only_articles->add_read_only_group_to_article( $kb_id, $kb_group_id, $kb_article_id );
				if ( empty($result) ) {
					AMGR_Logging::add_log( "Could not add read-only article access", $kb_id );
					AMGR_Access_Utilities::output_inline_error_notice( 'Internal Error occurred (33)' );
					return false;
				}
			}
		}

		// we are done here
		EPKB_Utilities::ajax_show_info_die( esc_html__( 'Access updated', 'echo-knowledge-base' ) );
	}

	private function get_article_access_data() {

		$new_article_access = array();

		// retrieve all selected Categories
		$new_articles_access_post = EPKB_Utilities::post( 'amgr_article_data', [] );
		if ( empty($new_articles_access_post) ) {
			return $new_article_access;
		}

		foreach( $new_articles_access_post as $article_access ) {
			$parts = explode('=', $article_access);
			if ( count($parts) != 2 ) {
				AMGR_Logging::add_log( "invalid article access" );
				EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (19)', 'echo-knowledge-base' ));
				return false;
			}

			$kb_article_id = $parts[0];
			if ( ! EPKB_Utilities::is_positive_int($kb_article_id) ) {
				AMGR_Logging::add_log( "invalid article access" );
				EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (20)', 'echo-knowledge-base' ));
				return false;
			}

			$access_level = $parts[1];
			if ( ! in_array($access_level, array('true', 'false')) ) {
				AMGR_Logging::add_log( "invalid article access" );
				EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser (19)', 'echo-knowledge-base' ));
				return false;
			}

			$new_article_access[$kb_article_id] = $access_level === "true";
		}

		return $new_article_access;
	}
}