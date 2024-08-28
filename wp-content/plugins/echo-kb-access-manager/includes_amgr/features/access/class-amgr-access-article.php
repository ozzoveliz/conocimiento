<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Control access to an article
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Article {

	// type of access
	const AMGR_ARTICLE_READ = 'AMGR_ARTICLE_READ';
	const AMGR_ARTICLE_EDIT = 'AMGR_ARTICLE_EDIT';
	const AMGR_ARTICLE_CREATE = 'AMGR_ARTICLE_CREATE';
	const AMGR_ARTICLE_DELETE = 'AMGR_ARTICLE_DELETE';

	const ALLOWED = 'access_allowed';
	const DENIED = 'access_denied';

	private $action = null;
	private $action_code = 0;
	private $authorized_groups = array();
	private $is_get_authorized_groups = false;

	private static $code_to_description = array(
		// ALLOWED
			400 => 'This KB has PUBLIC access.',
			401 => 'This is not a KB Article.',
			402 => 'Unassigned article for admin/manager.',
			403 => 'User is WP Administrator',
			404 => 'The user is KB Manager.',
			405 => 'User is KB Article author.',
			406 => 'This entire KB is RESTRICTED and the user belongs to KB Group with full access.',
			407 => 'The user belongs to KB Group that has access to KB Category that the KB Article belongs to.',
			408 => 'User has access to the article',
			409 => 'Admin or manager has always access',
			410 => 'Not KB Article',
			411 => 'User has access to the article',
			412 => 'User has access to this read-only article',
			413 => 'User has access to the article',
			414 => 'Admin or manager has always access',
			415 => 'Article being created as an empty draft',

		// DENIED
			300 => 'Error: Could not find KB ID.',
			301 => 'Error: Could not retrieve KB Group categories.',
			302 => 'User is not logged in.',
			303 => 'User has no access to the KB Article.',
			304 => 'Error: Could not retrieve active KB Group.',
			305 => 'This entire KB is RESTRICTED and the user does not belong to any KB Group with full access.',
			306 => 'Error: cannot retrieve article categories for group.',
			307 => 'Error: cannot retrieve articles.',
			308 => 'User does not belong to active group',
			309 => 'Error: cannot retrieve KB Groups for the user.',
			310 => 'Unassigned group chosen but user not admin or KB Manager.',
			311 => 'Error: cannot get user role',
			312 => 'User has not access to the KB Article 2',
			313 => 'Error: cannot get group categories IDs'
	);

	/**
	 * Setup authorized groups.
	 *
	 * @param bool $is_get_authorized_groups
	 */
	public function __construct( $is_get_authorized_groups=false ) {
		$this->is_get_authorized_groups = $is_get_authorized_groups;
	}

	/**
	 * @param $post
	 * @param null $required_capability
	 *
	 * @return String
	 */
	public function check_post_access( $post, $required_capability=null ) {
		global $amgr_access_action_code;

		if ( empty($post) || empty($post->ID) ) {
			$this->action_code = 410;
			return self::ALLOWED;
		}

		$required_capability = empty($required_capability) ? self::AMGR_ARTICLE_READ : $required_capability;

		// if processed in the past then just continue
		$article_access = epkb_get_instance()->kb_access_manager->get_article_access( $post->ID, $required_capability );
		if ( empty($article_access) ) {
			$article_access = $this->check_access_now( $post, $required_capability );
			if ( $article_access == self::DENIED ) {
				$amgr_access_action_code = $this->action_code;
			}
			$amgr_access_action_code = 99;
		}

		// TODO as DEBUG: AMGR_Logging::add_log('Access to single post with result action: ' . $article_access . ', action code: ' . $this->action_code, $post->ID);

		// store article access
		epkb_get_instance()->kb_access_manager->set_article_access( $post->ID, $required_capability, $article_access );

		return $article_access;
	}

	/**
	 * Can user access this article?
	 *
	 * @param WP_Post $post
	 * @param $required_capability - not WordPress capability
	 *
	 * @return String
	 */
	private function check_access_now( $post=null, $required_capability=null ) {

		// handle only KB Articles
		if ( ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			$this->action_code = 401;
			return self::ALLOWED;
		}

		// let admin/manager to see All Articles
		if ( AMGR_Access_Utilities::is_all_articles_page() && AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
			$this->action_code = 414;
			return self::ALLOWED;
		}

		// get post KB
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( empty( $kb_id ) || is_wp_error($kb_id) ) {
			$this->action_code = 300;
			return self::DENIED;
		}

		// default KB capability is to read KB Article
		$required_capability = $this->get_capability( $kb_id, $required_capability );
		$is_read_capability = $this->is_article_read_capability( $kb_id, $required_capability );

		$need_active_group = $this->does_need_active_group();
		$is_all_articles_page = AMGR_Access_Utilities::is_all_articles_page();

		// ignore new articles being entered (they have no categories yet)
		if ( $is_read_capability && $post->post_status == 'auto-draft' && $post->post_title == __( 'Auto Draft' ) && empty($post->post_content) && empty($post->post_excerpt) ) {
			$this->action_code = 415;
			return self::ALLOWED;
		}

		// get article categories
		$article_category_ids = AMGR_Access_Utilities::get_article_category_ids_unfiltered( $kb_id, $post->ID );
		// if the article has no categories then do not allow; only admin can access it
		if ( empty($article_category_ids) && ! $this->is_edit_article_capability( $kb_id, $required_capability ) && ! AMGR_Access_Utilities::is_admin_or_kb_manager() ) {

			// allow if user is creating the article
			$user = AMGR_Access_Utilities::get_current_user();
			if ( ! empty($user) && $this->is_user_creating_article( $kb_id, $post, $user, $required_capability ) ) {
				$this->action_code = 415;
				return self::ALLOWED;
			}

			$this->action_code = 306;
			return self::DENIED;
		}

		// if this article has PUBLIC group then allow
		if ( ! $need_active_group && $is_read_capability && AMGR_Access_Utilities::is_article_public( $kb_id, $post->ID ) ) {
			$this->action_code = 400;
			return self::ALLOWED;
		}

		// for RESTRICTED article we need to know who the user is
		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			$this->action_code = 302;
			return self::DENIED;
		}

		$is_admin_manager = AMGR_Access_Utilities::is_admin_or_kb_manager( $user );

		// AMGR Core does not use groups
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {

			if ( $this->is_user_capable( $kb_id, $user, $required_capability ) ) {
				$this->action_code = 408;
				return self::ALLOWED;
			}

			$this->action_code = 303;
			return self::DENIED;
		}

		// 1. get user groups
		if ( empty($need_active_group) ) {

			// get current user KB Groups
			$user_groups = $is_admin_manager ? epkb_get_instance()->db_kb_groups->get_groups( $kb_id ) : epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
			if ( $user_groups === null ) {
				$this->action_code = 309;
				return self::DENIED;
			}

		} else {

			// we need active group ID
			$active_group_id = $this->get_active_group( $kb_id );
			if ( empty($active_group_id) ) {
				$this->action_code = 304;
				return self::DENIED;
			}

			// user needs to have access to active group
			$active_group = epkb_get_instance()->db_kb_groups->get_group( $kb_id, $active_group_id );
			if ( empty($active_group)  ) {
				$this->action_code = 308;
				return self::DENIED;
			}

			$user_groups[] = $active_group;
		}

		// 2. filter user groups that user role matches the operation on the article
		$authorized_groups = array();
		foreach( $user_groups as $user_group ) {

			$user_role = $is_admin_manager ? AMGR_KB_Role::KB_ROLE_MANAGER : epkb_get_instance()->db_kb_group_users->get_user_role( $kb_id, $user_group->kb_group_id );
			if ( empty($user_role) ) {
				$this->action_code = 311;
				return self::DENIED;
			}

			// verify user has required role in the group
			if ( $is_admin_manager || AMGR_KB_Roles::has_user_kb_capability( $kb_id, $user_role, $required_capability ) ) {
				$authorized_groups[] = $user_group;
			} else if ( $user_role == AMGR_KB_Role::KB_ROLE_AUTHOR && $post->post_author == $user->ID ) {
				$authorized_groups[] = $user_group;
			}
		}

		// if none found no access
		if ( empty($authorized_groups) ) {
			$this->action_code = 312;
			return self::DENIED;
		}

		// Article Add/Edit screen needs to show all groups for given user
		if ( ! $is_all_articles_page && $this->is_edit_article_capability( $kb_id, $required_capability ) ) {
			if ( $this->is_get_authorized_groups ) {
				$this->authorized_groups = $authorized_groups;
			}

			$this->action_code = 407;
			return self::ALLOWED;
		}

		// article delete screen does not need groups
		if ( $this->is_delete_article_capability( $kb_id, $required_capability ) ) {
			$this->action_code = 408;
			return self::ALLOWED;
		}

		// admin or manager has always access
		if ( $is_admin_manager && empty($active_group_id) ) {
			if ( $this->is_get_authorized_groups ) {
				$this->authorized_groups = $authorized_groups;
			}
			$this->action_code = 409;
			return self::ALLOWED;
		}

		// 3. find which groups have access to article categories
		foreach( $authorized_groups as $authorized_group ) {

			// get categories associated with article and group
			$authorized_group_category_ids = AMGR_Access_Utilities::get_group_categories_ids( $kb_id, $authorized_group->kb_group_id );
			if ( $authorized_group_category_ids === null ) {
				$this->action_code = 313;
				return self::DENIED;
			}

			// READ-ONLY CATEGORIES: if article reading then consider read-only categories but not on All Articles page
			if ( ! $is_all_articles_page && $is_read_capability ) {
				$read_only_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $authorized_group->kb_group_id );
				if ( ! empty($read_only_categories_ids) ) {
					$authorized_group_category_ids = array_merge($authorized_group_category_ids, $read_only_categories_ids);
				}
			}

			// check if article and group categories match
			$common_categories = array_intersect($article_category_ids, $authorized_group_category_ids);
			if ( ! empty($common_categories) ) {
				if ( $this->is_get_authorized_groups ) {
					$this->authorized_groups[] = $authorized_group;
					continue;
				} else {
					$this->action_code = 411;
					return self::ALLOWED;
				}
			}

			// READ-ONLY ARTICLES: also check read-only articles that the group has access to
			if ( ! $is_all_articles_page && $is_read_capability ) {
				$read_only_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $authorized_group->kb_group_id );
				if ( ! empty($read_only_articles_ids) && in_array($post->ID, $read_only_articles_ids) ) {
					$this->action_code = 412;
					return self::ALLOWED;
				}
			}
		}

		// return with authorized groups
		if ( $this->is_get_authorized_groups && ! empty($this->authorized_groups) ) {
			$this->action_code = 413;
			return self::ALLOWED;
		}

		$this->action_code = 303;
		return self::DENIED;
	}

	/**
	 * @param $kb_id
	 *
	 * @return int|null
	 */
	private function get_active_group( $kb_id ) {

		$active_group_id = epkb_get_instance()->kb_access_manager->get_article_active_group_id( $kb_id );
		if ( empty($active_group_id) ) {
			$active_group_id = AMGR_Access_Utilities::get_valid_active_group( $kb_id, AMGR_KB_Role::KB_ROLE_CONTRIBUTOR );
			if ( empty( $active_group_id ) ) {
				return null;
			}

			epkb_get_instance()->kb_access_manager->set_article_active_group_id( $kb_id, $active_group_id );
		}

		return $active_group_id;
	}

	private function get_capability( $kb_id, $required_capability ) {

		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );

		if ( $required_capability == self::AMGR_ARTICLE_CREATE ) {
			return "kb_article_create_{$capability_type}s";
		} else if ( $required_capability == self::AMGR_ARTICLE_EDIT ) {
			return "kb_article_edit_{$capability_type}s";
		} else if ( empty($required_capability) || $required_capability == self::AMGR_ARTICLE_READ ) {
			return "kb_article_read_{$capability_type}s";
		} else if ( $required_capability == self::AMGR_ARTICLE_DELETE ) {
			return "kb_article_delete_{$capability_type}s";
		}

		return $required_capability;
	}

	private function is_article_read_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return $required_capability === "kb_article_read_{$capability_type}s" || $required_capability === "read_private_{$capability_type}s";
	}

	private function is_edit_article_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return in_array($required_capability,
						array("kb_article_edit_{$capability_type}s", "edit_published_{$capability_type}s", 'edit_amgr_' . $kb_id . '_cpts', "edit_private_{$capability_type}s") );
	}

	private function is_delete_article_capability( $kb_id, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return in_array($required_capability,
						array("kb_article_delete_{$capability_type}s", "delete_private_{$capability_type}s", 'edit_amgr_' . $kb_id . '_cpts') );
	}

	private function does_need_active_group() {

		// logged off user does not need active Group
		if ( AMGR_Access_Utilities::is_logged_off() ) {
			return false;
		}

		return empty($_SERVER['REQUEST_URI']) ? false : strstr($_SERVER['REQUEST_URI'], '/edit.php') !== false;
	}

	/**
	 * Using WP Roles, determine whether user has the KB Capability.
	 *
	 * @param $kb_id
	 * @param $user
	 * @param $required_capability
	 *
	 * @return bool
	 */
	private function is_user_capable( $kb_id, $user, $required_capability ) {

		$is_admin_manager = AMGR_Access_Utilities::is_admin_or_kb_manager( $user );
		if ( $is_admin_manager === true ) {
			return true;
		}

		$user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user );

		return AMGR_KB_Roles::has_user_kb_capability( $kb_id, $user_role, $required_capability );
	}

	private function is_user_creating_article( $kb_id, $post, $user, $required_capability ) {
		$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
		return ( $post->post_status == 'auto-draft' || $post->post_status == 'draft' || $post->post_status == 'publish' || $post->post_status == 'pending' ) &&
			$user->ID == $post->post_author && $required_capability == "kb_article_read_{$capability_type}s";
	}

	// for testing
	public function get_action_code() {
		return $this->action_code;
	}

	/**
	 * @return array
	 */
	public static function getCodeToDescription() {
		return self::$code_to_description;
	}

	/**
	 * @return null
	 */
	public function getAction() {
		return $this->action;
	}

	public function get_access_code_description() {
		return isset(self::$code_to_description[$this->action_code]) ? self::$code_to_description[$this->action_code] : '';
	}

	public function get_authorized_groups() {
		return $this->authorized_groups;
	}
}
