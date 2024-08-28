<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Control access to the KB Main Page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Main_Page_Front {

	public function __construct() {}

	/**
	 * Check whether user can access current KB Main Page - BASIC + ADVANCED check
	 *
	 * @param int $kb_id
	 * @return bool
	 */
	public static function can_user_access_kb_main_page( $kb_id ) {

		// retrieve KB PUBLIC group
		$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
		if ( is_wp_error($public_group) || empty($public_group) ) {
			return false;
		}

		// allow if PUBLIC categories exist
		$public_categories = epkb_get_instance()->db_access_kb_categories->get_group_categories( $kb_id, $public_group->kb_group_id );
		if ( ! empty($public_categories) ) {
			return true;
		}

		// allow if we have PUBLIC read-only access to articles
		$read_only_public_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $public_group->kb_group_id );
		if ( ! empty($read_only_public_articles_ids) ) {
			return true;
		}

		// allow if we have PUBLIC read-only access to categories
		$read_only_public_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $public_group->kb_group_id );
		if ( ! empty($read_only_public_categories_ids) ) {
			return true;
		}

		// no PUBLIC categories so we need logged-in user
		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			return false;
		}

		// admin and KB Manager can do anything
		if ( AMGR_Access_Utilities::is_admin_or_kb_manager( $user) ) {
			return true;
		}

		// AMGR Core does not use groups
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {
			$user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user );
			return ! empty($user_role);
		}

		// user can access if he/she belongs to any Group of this KB
		$user_groups = epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
		if ( ! empty($user_groups) ) {
			return true;
		}

		return false;
	}
}
