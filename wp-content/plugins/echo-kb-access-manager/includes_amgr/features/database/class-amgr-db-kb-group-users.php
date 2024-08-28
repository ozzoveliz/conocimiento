<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB Users in database.
 * Based on EDD_DB_Customers
 *
 * @property string table_name
 * @property string primary_key
 */
class AMGR_DB_KB_Group_Users extends AMGR_DB  {

    /**
     * The name of the cache user.
     *
     * @access public
     * @since  2.8
     * @var string
     */
    public $cache_user = 'amgr_kb_group_users';

    /**
     * Get things started
     *
     * @access  public
     * @since   2.1
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'amgr_kb_group_users';
        $this->primary_key = null;  // future to have composite primary key
    }

    /**
     * Get columns and formats
     *
     * @access  public
     * @since   2.1
     */
    public function get_column_format() {
        return array(
            'kb_id'          => '%d',
            'wp_user_id'     => '%d',
            'kb_group_id'    => '%d',
            'kb_role_name'   => '%s',
            'created_by'     => '%d',
            'date_created'   => '%s',
            'date_updated'   => '%s',
        );
    }

    /**
     * Get default column values
     *
     * @access  public
     * @since   2.1
     */
    public function get_column_defaults() {
        return array(
            'date_created'   => date( 'Y-m-d H:i:s' ),
            'date_updated'   => date( 'Y-m-d H:i:s' ),
        );
    }

	/**
	 * Add a new KB User
	 *
	 * @param $kb_id
	 * @param $wp_user_id
	 * @param $kb_group_id
	 * @param $kb_role_name
	 *
	 * @return boolean
	 */
	public function add_group_user( $kb_id, $wp_user_id, $kb_group_id, $kb_role_name ) {

		$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			return false;
		}

		if ( $is_group_public && $kb_role_name == AMGR_KB_Role::KB_ROLE_SUBSCRIBER) {
			return false;
		}

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGR_Logging::add_log("Attempt unauthorized access to Access Manager page for WP user id: ", $wp_user_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int($wp_user_id) ) {
			AMGR_Logging::add_log("WP User ID is not valid", $wp_user_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return false;
		}

		if ( ! AMGR_KB_Role::is_valid_role( $kb_role_name ) ) {
			AMGR_Logging::add_log("KB Role Name is not valid", $kb_role_name);
			return false;
		}

		// check that the KB Group exists
		$result = epkb_get_instance()->db_kb_groups->is_group_from_kb( $kb_id, $kb_group_id );
		if ( ! $result ) {
			return false;
		}

		// only Administrators can add KB Managers
		if ( ! current_user_can('manage_options') && $kb_role_name == AMGR_KB_Role::KB_ROLE_MANAGER ) {
			AMGR_Logging::add_log("KB Role Name is not valid. Administrator is required to add KB Managers.", $kb_role_name);
			return false;
		}

		// verify that the user exists
		$wp_user = get_userdata( $wp_user_id );
		if ( ! $wp_user ) {
			AMGR_Logging::add_log("Found user that is not existing WP user.", $wp_user_id);
			return false;
		}

		// 1. insure that record does not exist already
		$kb_group_user = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id, 'wp_user_id' => $wp_user_id, 'kb_group_id' => $kb_group_id, 'kb_role_name' => $kb_role_name) );
		if ( is_wp_error( $kb_group_user) ) {
			AMGR_Logging::add_log("Cannot add user to the KB Group. User ID: ", $wp_user_id . ', KB Group ID: ' . $kb_group_id . ', KB Role: ' . $kb_role_name);
			return false;
		}
		if ( ! empty($kb_group_user) ) {
			AMGR_Logging::add_log("Found existing record already.", $wp_user_id . ', ' . $kb_group_id . ', ' . $kb_role_name);
			return false;
		}

		// 2. ensure the user is not KB Manager
		$is_kb_manager = apply_filters( 'aman_is_kb_manager', false, $wp_user_id );
		if ( $is_kb_manager !== true && $is_kb_manager !== false ) {
			AMGR_Logging::add_log("Error retrieving KB Managers.", $kb_id);
			return false;
		}
		if ( $is_kb_manager === true ) {
			AMGR_Logging::add_log("The user is already KB Manager.", $wp_user_id);
			return false;
		}

		// 2. insert the record
		$result = parent::insert_record( array('kb_id' => $kb_id, 'wp_user_id' => $wp_user_id, 'kb_group_id' => $kb_group_id, 'kb_role_name' => $kb_role_name) );
		if ( $result !== false ) {
			$this->set_last_changed();
		}

		return $result !== false;
	}

	/**
	 * Get KB Role given KB, KB Group and WP User
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $wp_user_id
	 * @return null|String - null on error; empty if user not in the group
	 */
	public function get_user_role_config( $kb_id, $kb_group_id, $wp_user_id ) {

		if ( ! AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
			return null;
		}

		// find the KB Group User record based on KB, KB Group and WP User ID
		$kb_group_user = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'wp_user_id' => $wp_user_id) );
		if ( is_wp_error( $kb_group_user) ) {
			AMGR_Logging::add_log("Cannot retrieve user role for KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id . ', wp_user_id: ' . $wp_user_id);
			return null;
		}
		if ( $kb_group_user === null ) {
			return '';
		}

		if ( ! AMGR_KB_Role::is_valid_role( $kb_group_user->kb_role_name ) ) {
			return null;
		}

		return $kb_group_user->kb_role_name;
	}

	/**
	 * Get KB Role given KB, KB Group and WP User.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @return null|String - null on error; empty if user not in the group
	 */
	public function get_user_role( $kb_id, $kb_group_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return null;
		}

		// this should be only called if KB Group add-on is on
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {
			AMGR_Logging::add_log( 'Internal error (15)' );
			return null;
		}

		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			return '';
		}

		// 1. KB Group membership takes precedence
		$kb_group_user = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'wp_user_id' => $user->ID) );
		if ( is_wp_error( $kb_group_user) ) {
			AMGR_Logging::add_log("Cannot retrieve user role for KB Group. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id . ', wp_user_id: ' . $user->ID);
			return null;
		}
		if ( $kb_group_user !== null ) {
			return AMGR_KB_Role::is_valid_role( $kb_group_user->kb_role_name ) ? $kb_group_user->kb_role_name : null;
		}

		// 2. otherwise determine KB Role based on mapping of WP Role to KB, KB Group and WP User ID
		$wp_kb_user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user, true );
		if ( $wp_kb_user_role === null ) {
			return null;
		}

		return $wp_kb_user_role;
	}

	/**
	 * Get all users belonging to given group. Do NOT include WP Mapping membership.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @return array|null - user(s)
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
    public function get_group_users_config( $kb_id, $kb_group_id ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

	    if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
		    AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
		    return null;
	    }

	    $group_users = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id) );
	    if ( is_wp_error($group_users) ) {
		    AMGR_Logging::add_log( "Cannot retrieve users for a group " . $kb_group_id . " for KB " . $kb_id);
		    return null;
	    }

	    return $group_users === null ? array() : $group_users;
    }

	/**
	 * Get all users belong to a given group with specific role
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_role_name
	 *
	 * @return array|null - user(s)
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
    public function get_group_role_users_config( $kb_id, $kb_group_id, $kb_role_name ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

	    if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
		    AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
		    return null;
	    }

		if ( ! AMGR_KB_Role::is_valid_role( $kb_role_name ) ) {
			AMGR_Logging::add_log("KB Role Name is not valid", $kb_role_name);
			return null;
		}

	    $group_users = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'kb_role_name' => $kb_role_name) );
	    if ( is_wp_error($group_users) ) {
		    AMGR_Logging::add_log( "Cannot retrieve users for a group " . $kb_group_id . " for KB " . $kb_id . " and role " . $kb_role_name);
		    return null;
	    }

	    return $group_users === null ? array() : $group_users;
    }

	/**
	 * Get all KB Groups that the user belongs to within given KB
	 *
	 * @param $kb_id
	 * @return array|null - group(s)
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_groups_for_given_user( $kb_id ) {

		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			return array();
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		// 1. KB Group membership
		$user_groups = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'wp_user_id' => $user->ID) );
		if ( is_wp_error($user_groups) ) {
			AMGR_Logging::add_log( "Cannot retrieve records for user " . $user->ID . " for KB " . $kb_id);
			return null;
		}
		$user_groups = $user_groups === null ? array() : $user_groups;
		$user_groups_ids = AMGR_Access_Utilities::get_group_ids( $user_groups );

		// 2. also get KB Groups based on mapping
		$user_mapped_groups = AMGR_WP_Roles::get_groups_for_given_user( $kb_id, $user );
		if ( $user_mapped_groups === null ) {
			return null;
		}

		// add mapped groups that are not part of user membership
		foreach( $user_mapped_groups as $user_mapped_group ) {
			if ( ! in_array($user_mapped_group->kb_group_id, $user_groups_ids) ) {
				$user_groups[] = $user_mapped_group;
			}
		}

		return $user_groups;
    }

	/**
	 * Get all KBs that the user belongs to
	 *
	 * @return array|null - group(s)
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_user_kbs() {

		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			return array();
		}

		// 1. first get KBs through KB Group membership
		$user_groups = $this->get_rows_by_where_clause( array('wp_user_id' => $user->ID) );
		if ( is_wp_error($user_groups) ) {
			AMGR_Logging::add_log( "Cannot retrieve kb records for user " . $user->ID );
			return null;
		}
		$user_groups = $user_groups === null ? array() : $user_groups;

		$user_kbs = array();
		foreach( $user_groups as $user_group ) {
			$user_kbs[] = $user_group->kb_id;
		}

		// 2. get KBs through WP Role mapping
		$wp_map_user_kbs = AMGR_WP_Roles::get_user_kbs( $user );

		return array_merge($user_kbs, $wp_map_user_kbs);
	}

	/**
	 * Delete a KB Group User
	 *
	 * @param $kb_group_id
	 * @param $wp_user_id
	 * @return false|int
	 */
	public function remove_group_user( $kb_group_id, $wp_user_id ) {

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGR_Logging::add_log("Attempt unauthorized access to Access Manager page for user WP user id and group id: " . $wp_user_id, $kb_group_id);
			return false;
		}

		// validate input
		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("WP User ID is not valid", $kb_group_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return null;
		}

		// 1. validate the record exists
		$kb_group_user = $this->get_a_row_by_where_clause( array('kb_group_id' => $kb_group_id, 'wp_user_id' => $wp_user_id) );
		if ( is_wp_error( $kb_group_user) ) {
			AMGR_Logging::add_log("Cannot get KB Group user: ", $kb_group_user);
			return false;
		}
		if ( $kb_group_user === null ) {
			//AMGR_Logging::add_log("Remove Group User: record is not in the table", $wp_user_id);
			return false;
		}

		/* $wp_user = get_userdata( $kb_group_user->wp_user_id );
		if ( ! $wp_user ) {
			AMGR_Logging::add_log("Found user that is not existing WP user.", $kb_group_user->wp_user_id);
			return false;
		} */

		// 2. delete the KB Group User; IGNORE KB ID
		$result = parent::delete_rows_by_where_clause( array('kb_group_id' => $kb_group_id, 'wp_user_id' => $wp_user_id) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// verify that article records were deleted
		$kb_group_user = $this->get_a_row_by_where_clause( array('kb_group_id' => $kb_group_id, 'wp_user_id' => $wp_user_id) );
		if ( is_wp_error( $kb_group_user) ) {
			AMGR_Logging::add_log("Cannot get KB Group user: ", $kb_group_user);
			return false;
		}
		if ( $kb_group_user !== null ) {
			AMGR_Logging::add_log("Remove Group User: record still in the table", $kb_group_user);
			return false;
		}

		return true;
	}

	/**
	 * Delete user from all KB Groups by user ID
	 *
	 * @param $wp_user_id
	 * @return bool
	 */
	public function remove_user_from_all_groups( $wp_user_id ) {

		/** allow for automatic deletion of users by other plugins:
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGR_Logging::add_log("Attempt unauthorized access to Access Manager page. WP user id: ", $wp_user_id);
			return false;
		} */

		if ( ! EPKB_Utilities::is_positive_int($wp_user_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $wp_user_id);
			return false;
		}

		// remove records; IGNORE KB ID
		$result = $this->delete_record_by_column_value( 'wp_user_id', $wp_user_id );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// verify that article records were deleted
		$deletion = $this->get_rows_by_column_value( 'wp_user_id', $wp_user_id );
		if ( is_wp_error($deletion) ) {
			return false;
		}

		if ( $deletion !== null && count($deletion) > 0 ) {
			AMGR_Logging::add_log("Remove User from all Groups: record still in the table", $wp_user_id);
			return false;
		}

		return true;
	}

	/**
	 * Delete all users for given Group
	 *
	 * @param $kb_group_id
	 *
	 * @return bool
	 */
    public function remove_users_from_group( $kb_group_id ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGR_Logging::add_log("Attempt unauthorized access to Access Manager page. Group id: ", $kb_group_id);
		    return false;
	    }

	    if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
		    AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
		    return false;
	    }

	    // remove records; IGNORE KB ID
	    $result = $this->delete_record_by_column_value( 'kb_group_id', $kb_group_id );
	    if ( $result ) {
		    $this->set_last_changed();
	    } else {
		    return false;
	    }

	    // verify that article records were deleted
	    $deletion = $this->get_rows_by_column_value( 'kb_group_id', $kb_group_id );
	    if ( is_wp_error($deletion) ) {
		    return false;
	    }

	    if ( $deletion !== null && count($deletion) > 0 ) {
		    AMGR_Logging::add_log("Remove Users from Group: record still in the table", $kb_group_id);
		    return false;
	    }

	    return true;
    }

    /**
     * Sets the last_changed cache key for customers.
     *
     * @access public
     * @since  2.8
     */
    public function set_last_changed() {
        wp_cache_set( 'last_changed', microtime(), $this->cache_user );
    }

    /**
     * Retrieves the value of the last_changed cache key for customers.
     *
     * @access public
     * @since  2.8
     */
    // TODO
    public function get_last_changed() {
        if ( function_exists( 'wp_cache_get_last_changed' ) ) {
            return wp_cache_get_last_changed( $this->cache_user );
        }

        $last_changed = wp_cache_get( 'last_changed', $this->cache_user );
        if ( ! $last_changed ) {
            $last_changed = microtime();
            wp_cache_set( 'last_changed', $last_changed, $this->cache_user );
        }

        return $last_changed;
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   2.1
     */
    public function create_table() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE " . $this->table_name . " (
	                kb_group_id     bigint(20) NOT NULL,
	                wp_user_id      bigint(20) NOT NULL,
	                kb_role_name    varchar(50) NOT NULL,
	                kb_id           bigint(20) NOT NULL,
	                created_by   bigint(20) NOT NULL,	                
	                date_created datetime NOT NULL,
	                date_updated datetime NOT NULL,
	                PRIMARY KEY (kb_group_id, wp_user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );
    }

}
