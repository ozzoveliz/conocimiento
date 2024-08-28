<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Control access to a category
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Category {

	const AMGR_CATEGORY_READ = 'AMGR_CATEGORY_READ';
	const AMGR_CATEGORY_EDIT = 'AMGR_CATEGORY_EDIT';
	const AMGR_CATEGORY_CREATE = 'AMGR_CATEGORY_CREATE';
	const AMGR_CATEGORY_DELETE = 'AMGR_CATEGORY_DELETE';

	/**
	 * Confirm current user can access given category
	 *
	 * @param $term
	 * @param null $taxonomy
	 * @param null $parent_id
	 * @param string $required_capability
	 * @return bool|null - null on error, false - cannot, true - can
	 */
	public function check_access( $term, $taxonomy=null, $parent_id=null, $required_capability='' ) {

		if ( ! $term instanceof WP_Term || empty( $term->term_id ) ) {
			return true;
		}

		$required_capability = empty( $required_capability ) ? self::AMGR_CATEGORY_READ : $required_capability;

		// if processed in the past then just continue
		$category_access = epkb_get_instance()->kb_access_manager->get_category_access( $term->term_id, $required_capability );
		if ( empty( $category_access ) ) {
			$category_access = $this->check_access_now( $term, $taxonomy, $parent_id, $required_capability );
		}

		// store article access
		epkb_get_instance()->kb_access_manager->set_category_access( $term->term_id, $required_capability, $category_access );

		return $category_access;
	}

	/**
	 * Confirm current user can access given category
	 *
	 * @param $term
	 * @param null $taxonomy
	 * @param null $parent_id
	 * @param string $required_capability
	 *
	 * @return bool|null - null on error, false - cannot, true - can
	 * @noinspection PhpUnusedParameterInspection*/
	private function check_access_now( $term, $taxonomy=null, $parent_id=null, $required_capability='' ) {

		$taxonomy = empty($taxonomy) ? ( empty($term->taxonomy) ? '' : $term->taxonomy ) : $taxonomy;
		// $parent_id = empty($parent_id) ? ( empty($term->parent) ? null : $term->parent ) : $parent_id;

		// ignore non-KB categories
		if ( ! EPKB_KB_Handler::is_kb_category_taxonomy( $taxonomy ) ) {
			return true;
		}

		// get KB ID
		$kb_id = EPKB_KB_Handler::get_kb_id_from_category_taxonomy_name( $taxonomy );
		if ( empty($kb_id) || is_wp_error($kb_id) ) {
			AMGR_Logging::add_log("Found empty or invalid kb id", $kb_id );
			return null;
		}

		// default KB capability is to read KB Article
		$required_capability = $this->get_capability( $kb_id, $required_capability );

		// handle PUBLIC category
		if ( $this->is_read_capability( $kb_id, $required_capability ) && AMGR_Access_Utilities::is_category_public( $kb_id, $term->term_id ) ) {
			return true;
		}

		// user needs to be logged in
		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			return false;
		}

		$is_admin_manager = AMGR_Access_Utilities::is_admin_or_kb_manager( $user );

		// if needed get active KB Group
		$current_group_id = null;
		$user_role = $is_admin_manager ? AMGR_KB_Role::KB_ROLE_MANAGER : ( AMGR_WP_ROLES::use_kb_groups() ? '' : AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user ) );
		if ( AMGR_WP_ROLES::use_kb_groups() && ! $is_admin_manager ) {

			// user is creating Category
			if ( $this->is_create_capability( $kb_id, $required_capability ) ) {
				$user_category_groups = epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
				if ( $user_category_groups === null ) {
					return null;
				}

			// user is reading or editing Category
			} else {

				// groups with full access to the category
				$user_full_access_category_groups = epkb_get_instance()->db_access_kb_categories->get_user_category_groups( $kb_id, $term->term_id, $user );
				if ( is_wp_error( $user_full_access_category_groups ) ) {
					return null;
				}
				$user_full_access_category_groups = empty($user_full_access_category_groups) ? array() : $user_full_access_category_groups;

				// groups with read-only access to the category; include only if this is READ-ONLY capability
				$user_read_only_category_groups = array();
				if ( $this->is_read_capability( $kb_id, $required_capability ) ) {
					$user_read_only_category_groups = epkb_get_instance()->db_access_read_only_categories->get_read_only_user_category_groups( $kb_id, $term->term_id, $user );
					if ( is_wp_error( $user_read_only_category_groups ) ) {
						return null;
					}
					$user_read_only_category_groups = empty($user_read_only_category_groups) ? array() : $user_read_only_category_groups;
				}

				$user_category_groups = array_merge($user_full_access_category_groups, $user_read_only_category_groups);
			}

			// user category group does not exist - only admin/KB Manager can handle unassigned terms
			$highest_role_group = $this->get_user_highest_role_group( $user_category_groups );
			if ( empty($highest_role_group) ) {
				return false;
			}

			$current_group_id = $highest_role_group->kb_group_id;
			$user_role = $highest_role_group->kb_role_name;
		}

		// does the user have correct KB Role for given action?
		if ( ! AMGR_KB_Roles::has_user_kb_capability( $kb_id, $user_role, $required_capability ) ) {
			return false;
		}

		// complete the request
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		switch( $required_capability ) {

			/** ONLY for category create operation **/
			case "kb_category_create_{$capability_type}s":
				return $is_admin_manager || ! AMGR_WP_ROLES::use_kb_groups() ? true : $this->add_category_to_group( $kb_id, $current_group_id, $term->term_id );
				break;

			/** ONLY for category delete operation **/
			case "kb_category_delete_{$capability_type}s":
				return $this->delete_kb_category( $kb_id, $term->term_id );
				break;

			case "kb_category_read_{$capability_type}s":
			case "kb_category_edit_{$capability_type}s":
			case "edit_{$capability_type}_categories":
			case "delete_{$capability_type}_categories":
			case "manage_{$capability_type}_categories":
			case "assign_{$capability_type}_categories":
				return true;
				break;
		}

		AMGR_Logging::add_log('unknown category operation', $required_capability);
		return false;
	}

	private function get_capability( $kb_id, $required_capability ) {

		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );

		if ( $required_capability == self::AMGR_CATEGORY_CREATE ) {
			return "kb_category_create_{$capability_type}s";
		} else if ( $required_capability == self::AMGR_CATEGORY_EDIT ) {
			return "kb_category_edit_{$capability_type}s";
		} else if ( empty($required_capability) || $required_capability == self::AMGR_CATEGORY_READ ) {
			return "kb_category_read_{$capability_type}s";
		} else if ( $required_capability == self::AMGR_CATEGORY_DELETE ) {
			return "kb_category_delete_{$capability_type}s";
		}

		return $required_capability;
	}

	private function is_create_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return $required_capability === "kb_category_create_{$capability_type}s";
	}

	private function is_read_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return $required_capability === "kb_category_read_{$capability_type}s";
	}

	private function is_delete_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return $required_capability === "kb_category_delete_{$capability_type}s";
	}

	/**
	 * Find group that user has the highest role in
	 * @param $user_groups
	 *
	 * @return Object - role or empty or null on error
	 */
	private function get_user_highest_role_group( $user_groups ) {

		$highest_group = null;
		$user_groups = empty($user_groups) ? array() : $user_groups;
		foreach( $user_groups as $user_group ) {

			if ( $user_group->kb_role_name === AMGR_KB_Role::KB_ROLE_EDITOR ) {
				return $user_group;
			}

			if ( $user_group->kb_role_name === AMGR_KB_Role::KB_ROLE_AUTHOR ) {
				$highest_group = $user_group;
			} else if ( empty($highest_group) ) {
				$highest_group = $user_group;
			}
		}

		return $highest_group;
	}

	/**
	 * 	Add the new category to the KB Group
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_category_id
	 * @return true|null - null on error
	 */
	private function add_category_to_group( $kb_id, $kb_group_id, $kb_category_id ) {
		return epkb_get_instance()->db_access_kb_categories->add_category_to_group( $kb_id, $kb_group_id, $kb_category_id );
	}

	/**
	 * Delete the KB Category.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @return bool|null
	 */
	private function delete_kb_category( $kb_id, $kb_category_id ) {
		// delete the group category from Category access tables; confirm user can delete the category
		$result1 = epkb_get_instance()->db_access_kb_categories->delete_category( $kb_id, $kb_category_id );
		$result2 = epkb_get_instance()->db_access_read_only_categories->delete_category( $kb_id, $kb_category_id );
		return ( $result1 === false || $result2 === false ) ? null : true;
	}
}
