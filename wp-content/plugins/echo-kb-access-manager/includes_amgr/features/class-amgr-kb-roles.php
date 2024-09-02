<?php   if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create WordPress roles and capabilities to be used for KB Article, KB Categories and KB Tags content restriction.
 */
class AMGR_KB_Roles {

	public function __construct() {}

	/**
	 * Check if user has KB Article capabilities - HARD CODED CAPABILITIES PER ROLE
	 *
	 * @param $kb_id
	 * @param $user_role
	 * @param $kb_capability_check
	 *
	 * @return bool
	 */
	public static function has_user_kb_capability( $kb_id, $user_role, $kb_capability_check ) {

		if ( empty( $user_role) || ! AMGR_KB_Role::is_valid_role( $user_role )) {
			return false;
		}

		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );

		$kb_article_capabilities = array(

			/** KB Subscriber */
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_SUBSCRIBER . "kb_article_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_SUBSCRIBER . "read_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_SUBSCRIBER . "kb_category_read_{$capability_type}s",

			/** KB Contributor */
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "kb_article_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "read_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "kb_category_read_{$capability_type}s",
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "kb_article_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "kb_article_delete_{$capability_type}s",
			// articles
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "edit_{$capability_type}s",
			//AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "edit_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "delete_{$capability_type}s",
			//AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "delete_published_{$capability_type}s",
			// categories/tags
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "assign_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_CONTRIBUTOR . "assign_{$capability_type}_tags",

			/** KB Author */
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_AUTHOR . "kb_article_create_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "kb_article_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "read_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "kb_article_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "kb_article_delete_{$capability_type}s",
			// articles
			AMGR_KB_Role::KB_ROLE_AUTHOR . "edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "edit_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "publish_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "delete_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_AUTHOR . "delete_published_{$capability_type}s",
			// KB categories capabilities
			AMGR_KB_Role::KB_ROLE_AUTHOR . "kb_category_read_{$capability_type}s",
			// categories
			AMGR_KB_Role::KB_ROLE_AUTHOR . "assign_{$capability_type}_categories",
			// terms
			AMGR_KB_Role::KB_ROLE_AUTHOR . "assign_{$capability_type}_tags",

			/** KB Editor */
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_article_create_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_article_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_article_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_article_delete_{$capability_type}s",
			// articles
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_others_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "publish_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_others_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "read_private_{$capability_type}s",

			// KB categories capabilities
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_category_create_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_category_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_category_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_EDITOR . "kb_category_delete_{$capability_type}s",
			// categories
			AMGR_KB_Role::KB_ROLE_EDITOR . "manage_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_EDITOR . "assign_{$capability_type}_categories",
			// terms
			AMGR_KB_Role::KB_ROLE_EDITOR . "manage_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_EDITOR . "edit_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_EDITOR . "delete_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_EDITOR . "assign_{$capability_type}_tags",

			/** KB Leader */
			// to determine

			/** KB Manager */
			// KB article capabilities
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_article_create_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_article_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_article_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_article_delete_{$capability_type}s",
			// articles
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_others_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "publish_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_published_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_others_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_private_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "read_private_{$capability_type}s",

			// KB categories capabilities
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_category_create_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_category_read_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_category_edit_{$capability_type}s",
			AMGR_KB_Role::KB_ROLE_MANAGER . "kb_category_delete_{$capability_type}s",
			// categories
			AMGR_KB_Role::KB_ROLE_MANAGER . "manage_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_{$capability_type}_categories",
			AMGR_KB_Role::KB_ROLE_MANAGER . "assign_{$capability_type}_categories",
			// terms
			AMGR_KB_Role::KB_ROLE_MANAGER . "manage_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_MANAGER . "edit_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_MANAGER . "delete_{$capability_type}_tags",
			AMGR_KB_Role::KB_ROLE_MANAGER . "assign_{$capability_type}_tags",
		);

		return in_array( $user_role . $kb_capability_check, $kb_article_capabilities);
	}

	/**
	 * Add a) WordPress post-like capabilities to our KB Article and b) KB capabilities
	 * See WP schema.php
	 *
	 * @param $kb_role
	 * @param $kb_capability_check
	 * @return bool
	 */
	public static function has_user_kb_admin_capability( $kb_role, $kb_capability_check ) {

		$kb_admin_capabilities = array(

			/** KB Subscriber */
			/** KB Contributor */
			/** KB Author */
			/** KB Editor */

			/** KB Leader */
			// to determine

			/** KB Manager */
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_frontend_editor_write",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_search_analytics_read",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_addons_news_read",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_faqs_write",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_manager_page",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_crud_users",
			AMGR_KB_Role::KB_ROLE_MANAGER . "admin_eckb_access_crud_groups"
		);

		return in_array($kb_role . $kb_capability_check, $kb_admin_capabilities);
	}
}

/**
 * Contains hard-coded KB Roles
 */
abstract class AMGR_KB_Role {
	const KB_ROLE_SUBSCRIBER = 'kb_role_subscriber';
	const KB_ROLE_CONTRIBUTOR = 'kb_role_contributor';
	const KB_ROLE_AUTHOR = 'kb_role_author';
	const KB_ROLE_EDITOR = 'kb_role_editor';
	const KB_ROLE_MANAGER = 'kb_role_manager';

	/**
	 * Returns all existing KB roles.
	 *
	 * @return array
	 */
	public static function get_roles() {
		return array( self::KB_ROLE_SUBSCRIBER, self::KB_ROLE_CONTRIBUTOR, self::KB_ROLE_AUTHOR, self::KB_ROLE_EDITOR, self::KB_ROLE_MANAGER );
	}

	/**
	 * True if given role is valid KB role.
	 *
	 * @param $kb_role_name
	 * @return bool
	 */
	public static function is_valid_role( $kb_role_name ) {
		return ! empty($kb_role_name) && is_string($kb_role_name) && in_array($kb_role_name, self::get_roles());
	}

	public static function get_kb_role_name( $kb_role_name ) {
		$kb_role_name = str_replace( '_', ' ', $kb_role_name );
		$kb_role_name = strtolower($kb_role_name);
		$kb_role_name = str_replace( 'kb ', 'KB ', $kb_role_name );
		return ucwords($kb_role_name);
	}

	public static function is_user_role_same_or_higher( $user_role, $required_role ) {

		// if admin or KB Manager then allow anything
		if ( AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
			return true;
		}

		if ( ! self::is_valid_role( $user_role ) ) {
			AMGR_Logging::add_log('found invalid user Role name: ', $user_role );
			return false;
		} else if ( ! self::is_valid_role($required_role) ) {
			AMGR_Logging::add_log('found invalid KB Role name: ', $required_role );
			return false;
		}

		switch( $required_role ) {
			case self::KB_ROLE_MANAGER:
				return $user_role == self::KB_ROLE_MANAGER;
			case self::KB_ROLE_EDITOR:
				return in_array( $user_role, array(self::KB_ROLE_EDITOR, self::KB_ROLE_MANAGER));
			case self::KB_ROLE_AUTHOR:
				return in_array( $user_role, array(self::KB_ROLE_AUTHOR, self::KB_ROLE_EDITOR, self::KB_ROLE_MANAGER));
			case self::KB_ROLE_CONTRIBUTOR:
				return in_array( $user_role, array(self::KB_ROLE_CONTRIBUTOR, self::KB_ROLE_AUTHOR, self::KB_ROLE_EDITOR, self::KB_ROLE_MANAGER));
			case self::KB_ROLE_SUBSCRIBER:
				return true;
			default:
				return false;
		}
	}

	/**
	 * Get the higher role from two roles.
	 *
	 * @param $kb_role_1
	 * @param $kb_role_2
	 * @return String|null on error
	 */
	public static function get_higher_role( $kb_role_1, $kb_role_2 ) {

		$kb_role_1 = self::is_valid_role( $kb_role_1 ) ? $kb_role_1 : null;
		$kb_role_2 = self::is_valid_role( $kb_role_2 ) ? $kb_role_2 : null;

		if ( empty($kb_role_1) && empty($kb_role_2) ) {
			return null;
		}

		if ( empty($kb_role_1) ) {
			return $kb_role_2;
		}

		if ( empty($kb_role_2) ) {
			return $kb_role_1;
		}

		if ( $kb_role_1 == self::KB_ROLE_MANAGER ) {
			return $kb_role_1;
		}
		if ( $kb_role_2 == self::KB_ROLE_MANAGER ) {
			return $kb_role_2;
		}

		if ( $kb_role_1 == self::KB_ROLE_EDITOR ) {
			return $kb_role_1;
		}
		if ( $kb_role_2 == self::KB_ROLE_EDITOR ) {
			return $kb_role_2;
		}
		if ( $kb_role_1 == self::KB_ROLE_AUTHOR ) {
			return $kb_role_1;
		}
		if ( $kb_role_2 == self::KB_ROLE_AUTHOR ) {
			return $kb_role_2;
		}
		if ( $kb_role_1 == self::KB_ROLE_CONTRIBUTOR ) {
			return $kb_role_1;
		}
		if ( $kb_role_2 == self::KB_ROLE_CONTRIBUTOR ) {
			return $kb_role_2;
		}

		return $kb_role_1;
	}
}
