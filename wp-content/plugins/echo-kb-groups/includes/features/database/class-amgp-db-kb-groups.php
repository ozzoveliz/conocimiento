<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle both PRIVATE AND PUBLIC KB Groups in database.
 * Based on EDD_DB_Customers
 *
 * @property string primary_key
 * @property string table_name
 */
class AMGP_DB_KB_Groups extends AMGP_DB  {

    /**
     * The name of the cache group.
     *
     * @access public
     * @var string
     */
    public $cache_group = AMGP_KB_Core::DB_KB_GROUPS;

    /**
     * Get things started
     *
     * @access  public
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . AMGP_KB_Core::DB_KB_GROUPS;
        $this->primary_key = 'kb_group_id';
    }

    /**
     * Get columns and formats
     *
     * @access  public
     */
    public function get_column_format() {
        return array(
            'kb_group_id'    => '%d',
            'kb_id'          => '%d',
            'name'           => '%s',
            'created_by'     => '%d',
            'date_created'   => '%s',
            'date_updated'   => '%s',
        );
    }

    /**
     * Get default column values
     *
     * @access  public
     */
    public function get_column_defaults() {
        return array(
            'name'           => '',
            'date_created'   => date( 'Y-m-d H:i:s' ),
            'date_updated'   => date( 'Y-m-d H:i:s' ),
        );
    }

	/**
	 * Retrieve KB Group
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 *
	 * @return null|Object - row as Object with properties as column names e.g. $result->I
	 *                       null IF:  a) failure or b) no record found
	 */
	public function get_group( $kb_id, $kb_group_id ) {

		if ( ! AMGP_Utilities::is_positive_int($kb_id) ) {
			AMGP_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		// validate KB Group ID
		if ( ! AMGP_Utilities::is_positive_int($kb_group_id) ) {
			AMGP_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return null;
		}

		$group = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id) );
		if ( is_wp_error( $group) ) {
			AMGP_Logging::add_log("Cannot get KB Group record: ", $kb_id);
			return null;
		}

		return $group;
	}

    /**
     * Retrieve Groups for given KB.
     * @param $kb_id
     * @return null|array - array of Objects or empty
     *                    - EMPTY array() if no rows
     *                    - NULL on failure
     */
    public function get_groups( $kb_id ) {

        if ( ! AMGP_Utilities::is_positive_int($kb_id) ) {
            AMGP_Logging::add_log("KB ID is not valid", $kb_id);
            return null;
        }

	    // 3. ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    AMGP_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

        $result = $this->get_rows_by_column_value( 'kb_id', $kb_id );
        if ( is_wp_error($result) ) {
        	return null;
        }

        return $result === null ? array() : $result;
    }

	/**
	 * Retrieve PRIVATE Groups for given KB.
	 * @param $kb_id
	 * @return null|array - array of Objects or empty
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_private_groups( $kb_id ) {

		if ( ! AMGP_Utilities::is_positive_int($kb_id) ) {
			AMGP_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		// 3. ensure KB exists
		if ( ! $this->is_kb_id_valid( $kb_id ) ) {
			AMGP_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$kb_groups = $this->get_rows_by_column_value( 'kb_id', $kb_id );
		if ( is_wp_error($kb_groups) ) {
			return null;
		}

		$kb_private_groups = array();
		foreach( $kb_groups as $kb_group ) {
			$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group->kb_group_id );
			if ( ! $is_group_public ) {
				$kb_private_groups[] = $kb_group;
			}
		}

		return $kb_private_groups === null ? array() : $kb_private_groups;
	}

    /**
     * Insert a new KB Group
     *
     * @param $kb_id
     * @param $kb_group_name
     *
     * @return int|null - null on Error or record ID
     */
    public function insert_group( $kb_id, $kb_group_name ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGP_Logging::add_log("Attempt unauthorized access to Access Manager page", $kb_id);
		    return null;
	    }

	    if ( ! $this->validate_group_name( $kb_group_name) ) {
		    AMGP_Logging::add_log("KB Group name is not valid: ", $kb_group_name);
		    return null;
	    }

	    // ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    AMGP_Logging::add_log("KB ID is not valid: ", $kb_id);
		    return null;
	    }

        // 1. validate the KB Group
        $kb_group = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id, 'name' => $kb_group_name) );
        if ( is_wp_error( $kb_group) ) {
            AMGP_Logging::add_log("Cannot get KB Group by name.", $kb_group);
            return null;
        }

        if ( ! empty($kb_group) && $kb_group_name == $kb_group->name ) {
	        AMGP_Logging::add_log( "'" . $kb_group_name . "' group already exists.");
	        return null;
        }

        // 2. insert the record
        $kb_group_id = parent::insert_record( array('kb_id' => $kb_id, 'name' => $kb_group_name) );
        if ( empty($kb_group_id) ) {
	        $kb_group_id = null;
        } else {
	        $this->set_last_changed();
        }

        return $kb_group_id;
    }

	/**
	 * Update Group name
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_group_name
	 *
	 * @return bool
	 */
    public function rename_group( $kb_id, $kb_group_id, $kb_group_name ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGP_Logging::add_log("Attempt unauthorized access to Access Manager page", $kb_group_id);
		    return false;
	    }

	    // user cannot rename PUBLIC group
	    $is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
	    if ( $is_group_public || $is_group_public === null ) {
		    AMGP_Logging::add_log("Cannot rename PUBLIC group: " . $kb_group_id, $kb_id);
	        return false;
	    }

	    if ( ! $this->validate_group_name( $kb_group_name) ) {
		    AMGP_Logging::add_log("KB Group name is not valid", $kb_group_name);
		    return false;
	    }

        $result = parent::update_record( $kb_group_id, array( 'name' => $kb_group_name) );
        if ( $result ) {
            $this->set_last_changed();
        }

        return $result;
    }

	/**
	 * Delete a KB Group
	 *
	 * @param $kb_id
	 * @param int $kb_group_id
	 *
	 * @return bool
	 */
    public function delete_group( $kb_id, $kb_group_id ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGP_Logging::add_log("Attempt unauthorized access to Access Manager page.", $kb_group_id);
		    return false;
	    }

	    $is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
	    if ( $is_group_public || $is_group_public === null ) {
		    AMGP_Logging::add_log("Cannot delete PUBLIC group: " . $kb_group_id, $kb_id);
		    return false;
	    }

	    // ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    AMGP_Logging::add_log("KB ID is not valid: ", $kb_id);
		    return false;
	    }

		// validate KB Group ID
	    if ( ! AMGP_Utilities::is_positive_int($kb_group_id) ) {
		    AMGP_Logging::add_log("KB Group ID is not valid", $kb_group_id);
		    return false;
	    }

	    // prevent deletion of the last KB Group
	    $all_groups = $this->get_groups( $kb_id );
	    if ( $all_groups === null ) {
		    AMGP_Logging::add_log("Cannot get current groups. KB ID " . $kb_id, $kb_group_id);
		    return false;
	    }
	    if ( count($all_groups) < 2 ) {
		    return false;
	    }

        // 1. validate the KB Group
        $kb_group = $this->get_by_primary_key( $kb_group_id );
        if ( is_wp_error( $kb_group) ) {
            AMGP_Logging::add_log("Cannot get KB Group by primary key: ", $kb_group_id);
            return false;
        }
        if ( empty($kb_group) || $kb_group->kb_group_id != $kb_group_id ) {
	        AMGP_Logging::add_log("Delete Group: group is not in the table ", $kb_group_id);
	        return false;
        }

        // 2. delete all users for this KB Group
        $kb_group_users = new AMGP_DB_KB_Group_Users();
        if ( ! $kb_group_users->remove_users_from_group( $kb_group_id ) ) {
	        AMGP_Logging::add_log("Could not delete users in this KB Group ", $kb_group_id);
	        return false;
        }

	    // 3. delete group KB Categories from category tables
	    $result = amgp_get_instance()->db_access_kb_categories->delete_group_categories( $kb_id, $kb_group_id );
        if ( $result === false ) {
	        AMGP_Logging::add_log("Could not delete categories for group to be deleted ", $kb_group_id);
	        return false;
        }
	    $result = amgp_get_instance()->db_access_read_only_categories->delete_group_categories( $kb_id, $kb_group_id );
	    if ( $result === false ) {
		    AMGP_Logging::add_log("Could not delete read-only categories for group to be deleted ", $kb_group_id);
		    return false;
	    }
	    $result = amgp_get_instance()->db_access_read_only_articles->delete_group_articles( $kb_id, $kb_group_id );
	    if ( $result === false ) {
		    AMGP_Logging::add_log("Could not delete read-only aticles for group to be deleted ", $kb_group_id);
		    return false;
	    }

        // 5. delete the KB Group
        $result = parent::delete_record( $kb_group_id );
        if ( $result ) {
            $this->set_last_changed();
        } else {
        	return false;
        }

	    // verify that article records were deleted
	    $deletion = $this->get_by_primary_key( $kb_group_id );
        if ( is_wp_error( $deletion) ) {
            AMGP_Logging::add_log("Cannot get KB Group by ID: ", $kb_group_id);
            return false;
        }
	    if ( $deletion !== null ) {
		    AMGP_Logging::add_log("Remove Group: record still in the table", $kb_group_id);
		    return false;
	    }

	    return true;
    }

    /**
     * Check if given KB Group belongs to given KB ID
     *
     * @param $kb_id
     * @param $kb_group_id
     * @return bool
     */
    public function is_group_from_kb( $kb_id, $kb_group_id ) {

	    // ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    AMGP_Logging::add_log("KB ID is not valid: ", $kb_id);
		    return false;
	    }

	    // validate KB Group ID
	    if ( ! AMGP_Utilities::is_positive_int($kb_group_id) ) {
		    AMGP_Logging::add_log("KB Group ID is not valid", $kb_group_id);
		    return false;
	    }

        $kb_group = $this->get_by_primary_key( $kb_group_id );
        if ( empty($kb_group) || is_wp_error( $kb_group) ) {
            AMGP_Logging::add_log("Cannot get KB Group by ID: ", $kb_group_id);
            return false;
        }

        return $kb_group->kb_id == $kb_id;
    }

	/**
	 * @param $kb_id
	 *
	 * @return bool
	 */
	private function is_kb_id_valid( $kb_id ) {
		$kb_ids = amgp_get_instance()->kb_config_obj->get_kb_ids();
		return empty($kb_ids) ? false : in_array($kb_id, $kb_ids);
	}

	/**
	 * Validate Group name.
	 *
	 * @param $kb_group_name
	 * @return bool
	 */
    private function validate_group_name( $kb_group_name ) {
    	return ! empty($kb_group_name) && is_string($kb_group_name) && strlen($kb_group_name) <= 50;
    }

    /**
     * Sets the last_changed cache key for cache group.
     *
     * @access public
     * @since  2.8
     */
    public function set_last_changed() {
        wp_cache_set( 'last_changed', microtime(), $this->cache_group );
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
	                kb_group_id  bigint(20) NOT NULL AUTO_INCREMENT,
	                kb_id        bigint(20) NOT NULL,
	                name         varchar(50) NOT NULL,
	                created_by   bigint(20) NOT NULL,
	                date_created datetime NOT NULL,
	                date_updated datetime NOT NULL,
	                PRIMARY KEY  (kb_group_id),
	                KEY kb_id (kb_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );
    }

}
