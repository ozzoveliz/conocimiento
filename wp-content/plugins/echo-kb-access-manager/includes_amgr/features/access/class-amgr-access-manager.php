<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manages various aspects of access to KB
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Manager {

	const AMGR_KB_CAPABILITIES_SUFFIX = '_cpt';

	// caches
	private $articles_access = array();
	private $articles_active_group_id = array();
	private $categories_access = array();
	private $categories_active_group_id = array();

	// setup hooks
	public function __construct() {

		// protect KB admin operations
		add_filter( 'user_has_cap', array( $this, 'check_user_has_kb_capabilities' ), 99999, 4 );

		add_action( 'delete_user', array( $this, 'delete_user_from_amgr') );
	}

	/**
	 * For KB Capability check verify the current user has KB Role that allows him/her to do something.
	 **
	 *
	 * @param bool[] $user_caps Current user's capabilities.
	 * @param string[] $required_caps [0]    Required primitive capability for the requested capability.
	 * @param array $args {
	 * @param WP_User $user
	 *
	 * @return bool[] Current user's capabilities.
	 */
	public function check_user_has_kb_capabilities( array $user_caps, array $required_caps, array $args, WP_User $user ) {

		// process one required capability at a time
		foreach ( $required_caps as $required_cap ) {

			if ( empty( $required_cap ) || $required_cap == 'do_not_allow' ) {
				continue;
			}

			// if user has needed capabilities then skip
			if ( in_array($required_cap, array_keys($user_caps)) ) {
				continue;
			}

			// check access to KB Admin pages and Access Manager operations
			if ( strpos( $required_cap, 'admin_eckb_' ) !== false ) {
				$has_capability = $this->can_do_kb_admin_operations( $user_caps, $required_cap, $user );
				if ( $has_capability ) {
					$user_caps[ $required_cap ] = true;
				} else if ( $has_capability === false && isset($user_caps[ $required_cap ])) {
					unset($user_caps[ $required_cap ]);
				}
				continue;
			}

			// check KB Article or KB Category/Term access
			if ( strpos( $required_cap, '_amgr_' ) !== false && strpos( $required_cap, AMGR_Access_Manager::AMGR_KB_CAPABILITIES_SUFFIX ) !== false ) {
				$has_capability = $this->can_do_kb_backend_operations( $required_cap, $args, $user );
				if ( $has_capability ) {
					$user_caps[ $required_cap ] = true;
				} else if ( $has_capability === false && isset($user_caps[ $required_cap ])) {
					unset($user_caps[ $required_cap ]);
				}
				continue;
			}
		}

		return $user_caps;
	}

	/**
	 * Check Article and Terms capabilities.
	 *
	 * @param $required_capability
	 * @param $args
	 * @param $user
	 * @return bool
	 */
	private function can_do_kb_backend_operations( $required_capability, $args, $user ) {

		// get KB ID
		$kb_id = AMGR_Access_Utilities::get_kb_id( $required_capability );
		if ( empty($kb_id) ) {
			return false;
		}

		// capability KB ID has to match post KB ID
		if ( strpos($required_capability, AMGR_Access_Utilities::get_capability_type( $kb_id ) ) === false ) {
			return false;
		}

		// admin and manager can do anything to KB article
		if ( AMGR_Access_Utilities::is_admin_or_kb_manager( $user ) ) {
			return true;
		}

		// determine whether this is capability for article or category/term
		if ( strpos($required_capability, '_categories') === false && strpos($required_capability, '_tags') === false  ) {
			return AMGR_Access_Articles_Back::does_have_capability( $kb_id, $required_capability, $args, $user );
		} else {
			return AMGR_Access_Categories_Back::does_have_capability( $kb_id, $required_capability, $args, $user );
		}
	}

	/**
	 * Handle access to KB Admin pages and Access Manager operations
	 *
	 * @param array $user_caps
	 * @param $required_capability
	 * @param WP_User $user
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection*/
	private function can_do_kb_admin_operations( array $user_caps, $required_capability, WP_User $user ) {

		// only admin or KB Manager can handle Access Manager configuration
		if ( ! AMGR_Access_Utilities::is_admin_or_kb_manager( $user ) ) {
			return false;
		}

		// is this backend capability?
		return AMGR_KB_Roles::has_user_kb_admin_capability( AMGR_KB_Role::KB_ROLE_MANAGER, $required_capability );
	}


	/****************************************************************************************************************
	 *
	 *             Other
	 *
	 ***************************************************************************************************************/

	/**
	 * Check that all AMGR table records make sense.
	 *
	 * @param bool $fix_issues
	 */
	public static function verify_system_integrity( $fix_issues=false ) {

		// TODO FUTURE if echo-knowledge-base is still active show warning
		// TODO FUTURE do we still need this: roles and capabilities in user records...
		// TODO FUTURE user one role per group
		// TODO FUTURE ensure KB access default is RESTRICTED

		$errors = array();
		$kb_ids = epkb_get_instance()->kb_config_obj->get_kb_ids();


		// 3. check that KB Groups all belongs to KB
		$kb_groups = epkb_get_instance()->db_kb_groups->get_all_rows();
		// error handling...
		foreach( $kb_groups as $kb_group ) {
			if ( ! in_array($kb_group->kb_id, $kb_ids) )  {
				$errors[] = 'Found KB Group with unknown KB ID: . KB ID found: ' . $kb_group->kb_id;
			}
		}

		// 4. check that users belong to existing KB Group
		$kb_users = epkb_get_instance()->db_kb_group_users->get_all_rows();
		// error handling...

		foreach( $kb_users as $kb_user ) {
			if ( ! AMGR_Access_Utilities::is_kb_group_id_in_array( $kb_user->kb_group_id, $kb_groups ) )  {
				$errors[] = 'Found user with unknown KB Group. KB Group ID found: ' . $kb_user->kb_group_id;
			}
		}

		// 5. check that users have valid KB Role
		foreach( $kb_users as $kb_user ) {
			if ( ! AMGR_KB_Role::is_valid_role( $kb_user->kb_role_name ) ) {
				$errors[] = 'Found user with invalid KB Role. KB Role found: ' . $kb_user->kb_role_name;
			}
		}

		// 6. check that users are valid WP users
		foreach( $kb_users as $kb_user ) {
			if ( ! get_userdata( $kb_user->wp_user_id ) ) {
				$errors[] = 'Found user that is not existing WP user. User ID found: ' . $kb_user->wp_user_id;
			}
		}

		// get all KB Categories
		$kb_categories = array();
		$categories_error = false;
		foreach( $kb_ids as $kb_id ) {
			 $kb_categories_per_kb = EPKB_Core_Utilities::get_kb_categories_unfiltered( $kb_id );
			 if ( $kb_categories_per_kb === null  ) {
			 	AMGR_Logging::add_log( 'Failed to retrieve KB Categories', $kb_id );
				 $categories_error = true;
			 	continue;
			 }

			 $kb_category_ids = array();
			 foreach($kb_categories_per_kb as $kb_category_per_kb) {
				 $kb_category_ids[] = $kb_category_per_kb->term_id;
			 }

			$kb_categories[$kb_id] = $kb_category_ids;
		}

		// 8. check KB Categories Access
		$kb_categories_access = epkb_get_instance()->db_access_kb_categories->get_all_rows();

		foreach($kb_categories_access as $kb_category_access) {
			if ( ! in_array( $kb_category_access->kb_id, $kb_ids) ) {
				$errors[] = 'Found KB Category Access record with invalid KB ID. KB ID found: ' . $kb_category_access->kb_id;
			}

			if ( ! AMGR_Access_Utilities::is_kb_group_id_in_array( $kb_category_access->kb_group_id, $kb_groups ) )  {
				$errors[] = 'Found KB Category Access record with invalid KB Group ID. KB Group ID found: ' . $kb_category_access->kb_group_id;
			}

			if ( ! $categories_error && ! empty($kb_categories[$kb_category_access->kb_id]) && ! in_array($kb_category_access->kb_category_id, $kb_categories[$kb_category_access->kb_id]) ) {
				$errors[] = 'Found KB Category Access record with invalid KB Category ID. KB Category ID found: ' . $kb_category_access->kb_category_id;
			}
		}

		// 9. check KB Article Access
		$kb_articles_access = epkb_get_instance()->db_access_read_only_articles->get_all_rows();

		foreach($kb_articles_access as $kb_article_access) {
			if ( ! in_array( $kb_article_access->kb_id, $kb_ids) ) {
				$errors[] = 'Found KB Article Access record with invalid KB ID. KB ID found: ' . $kb_article_access->kb_id;
			}

			if ( ! AMGR_Access_Utilities::is_kb_group_id_in_array( $kb_article_access->kb_group_id, $kb_groups ) )  {
				$errors[] = 'Found KB Article Access record with invalid KB Group ID. KB Group ID found: ' . $kb_article_access->kb_group_id;
			}

			$result = EPKB_Core_Utilities::get_kb_post_secure( $kb_article_access->kb_article_id );
			if ( empty($result) ) {
				$errors[] = 'Found KB Article Access record with invalid post ID. Post ID found: ' . $kb_article_access->kb_article_id;
			}
		}

		if ( ! empty($errors) ) {
			AMGR_Logging::add_log( 'Found integrity issues.' ); // TODO
		}
	}

	/**
	 * When WP user is deleted, remove it from AMGR table.
	 *
	 * @param $user_id
	 */
	public function delete_user_from_amgr( $user_id ) {
		epkb_get_instance()->db_kb_group_users->remove_user_from_all_groups( $user_id );
	}


	/****************************************************************************************************************
	 *
	 *             Article/Category Access
	 *
	 ***************************************************************************************************************/

	/**
	 * Set Article access cache.
	 *
	 * @param $kb_article_id
	 * @param $capability
	 * @param $article_access
	 */
	public function set_article_access( $kb_article_id, $capability, $article_access ) {
		if ( empty($kb_article_id) || empty($capability) || empty($article_access) ) {
			return;
		}
		$user = AMGR_Access_Utilities::get_current_user();
		$user_id = empty($user) ? 0 : $user->ID;
		$key = $kb_article_id . '_' . $user_id . '_' . $capability;
		$this->articles_access[$key] = $article_access;
	}

	/**
	 * Retrieve cached Article access
	 *
	 * @param $kb_article_id
	 * @param $capability
	 * @return string|null
	 */
	public function get_article_access( $kb_article_id, $capability ) {
		if ( empty($kb_article_id) || empty($capability) ) {
			return null;
		}

		$user = AMGR_Access_Utilities::get_current_user();
		$user_id = empty($user) ? 0 : $user->ID;
		$key = $kb_article_id . '_x_' . $user_id . '_x_' . $capability;

		return empty($this->articles_access[$key]) ? null : $this->articles_access[$key];
	}

	/**
	 * Set Category access cache.
	 *
	 * @param $kb_category_id
	 * @param $capability
	 * @param $category_access
	 */
	public function set_category_access( $kb_category_id, $capability, $category_access ) {
		if ( empty($kb_category_id) || empty($capability) || empty($category_access) ) {
			return;
		}
		$user = AMGR_Access_Utilities::get_current_user();
		$user_id = empty($user) ? 0 : $user->ID;
		$key = $kb_category_id . '_x_' . $user_id . '_x_' . $capability;
		$this->categories_access[$key] = $category_access;
	}

	/**
	 * Retrieve cached Category access
	 *
	 * @param $kb_category_id
	 * @param $capability
	 * @return string|null
	 */
	public function get_category_access( $kb_category_id, $capability ) {
		if ( empty($kb_category_id) || empty($capability) ) {
			return null;
		}
		$user = AMGR_Access_Utilities::get_current_user();
		$user_id = empty($user) ? 0 : $user->ID;
		$key = $kb_category_id . '_x_' . $user_id . '_x_' . $capability;

		return empty($this->categories_access[$key]) ? null : $this->categories_access[$key];
	}

	public function get_article_active_group_id( $kb_id ) {
		return empty($this->articles_active_group_id[$kb_id]) ? null : $this->articles_active_group_id[$kb_id];
	}

	public function set_article_active_group_id( $kb_id, $articles_active_group_id ) {
		$this->articles_active_group_id[$kb_id] = $articles_active_group_id;
	}

	public function get_categories_active_group_id( $kb_id ) {
		return empty($this->categories_active_group_id[$kb_id]) ? null : $this->categories_active_group_id[$kb_id];
	}

	public function set_categories_active_group_id( $kb_id, $categories_active_group_id ) {
		$this->categories_active_group_id[$kb_id] = $categories_active_group_id;
	}

	/****************************************************************************************************************
	 *
	 *             Ensure AMGR tables exist
	 *
	 ***************************************************************************************************************/

	/** AMGR: Ensure that we have AMGR tables in place */
	public static function check_amgr_tables() {

		if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'admin_eckb_access_manager_page' ) ) {
			return;
		}

		$amgr_table_check = EPKB_Utilities::get_wp_option( 'amgr_table_check', false );
		if ( ! empty( $amgr_table_check ) ) {
			return;
		}

		self::create_table_if_missing( epkb_get_instance()->db_kb_groups );
		self::create_table_if_missing( epkb_get_instance()->db_kb_public_groups );
		self::create_table_if_missing( epkb_get_instance()->db_kb_group_users );
		self::create_table_if_missing( epkb_get_instance()->db_access_kb_categories );
		self::create_table_if_missing( epkb_get_instance()->db_access_read_only_articles );
		self::create_table_if_missing( epkb_get_instance()->db_access_read_only_categories );

		// setup Public group
		$handler = new AMGR_Setup_KB_Groups();
		$handler->setup_amgr_data();

		EPKB_Utilities::save_wp_option( 'amgr_table_check', true );
	}

	/**
	 * @param AMGR_DB $handle
	 */
	private static function create_table_if_missing( $handle ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$table_exists = $handle->table_exists( $handle->getTableName() );
		if ( $table_exists ) {
			return;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		@$handle->create_table();

		if ( $handle->getTableName() == $wpdb->prefix . 'amgr_kb_public_groups' ) {
			// setup KB Public Group for the new knowledge base
			$handler = new AMGR_Setup_KB_Groups();
			$handler->setup_amgr_data();
		}
	}
}
