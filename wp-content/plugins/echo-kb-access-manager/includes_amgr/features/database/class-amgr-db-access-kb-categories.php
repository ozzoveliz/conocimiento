<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Access to KB Categories.
 *
 * @property string primary_key
 * @property string table_name
 */
class AMGR_DB_Access_KB_Categories extends AMGR_DB  {

    /**
     * The name of the cache group.
     *
     * @access public
     * @since  2.8
     * @var string
     */
    public $cache_group = 'amgr_access_kb_categories';

    /**
     * Get things started
     *
     * @access  public
     * @since   2.1
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'amgr_access_kb_categories';
        $this->primary_key = null; // future to have composite primary key
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
            'kb_group_id'    => '%d',
            'kb_category_id' => '%d',
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
	 * Retrieve categories that the user has access to through his/her groups.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @param $user
	 *
	 * @return array|null|WP_Error - column value
	 *                             - null if 0 records found
	 *                             - WP_Error on error
	 */
	public function get_user_category_groups( $kb_id, $kb_category_id, $user ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return new WP_Error('11', 'KB ID is not valid');
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
			AMGR_Logging::add_log("Post ID is not valid", $kb_category_id);
			return new WP_Error('11', 'Post ID is not valid');
		}

		$user_category_groups = $wpdb->get_results(	$wpdb->prepare(
							"SELECT * 
							 FROM $this->table_name c INNER JOIN " . $wpdb->prefix . 'amgr_kb_group_users' . " u 
							 ON c.kb_group_id = u.kb_group_id AND c.kb_id = u.kb_id 
							 WHERE c.kb_id = %d AND c.kb_category_id = %d AND u.wp_user_id = %d LIMIT 100;", $kb_id, $kb_category_id, $user->ID
						) );
		if ( ! empty($wpdb->last_error) ) {
			AMGR_Logging::add_log("DB failure: ", $wpdb->last_error);
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		// we we have both KB Groups and Role Mappings then KB Groups wins unless user has no role in KB Group
		if ( AMGR_WP_Roles::use_kb_groups() && AMGR_WP_Roles::use_wp_role_mapping() ) {

			$user_mapped_groups = AMGR_WP_Roles::get_groups_for_given_user( $kb_id, $user );
			if ( $user_mapped_groups === null ) {
				AMGR_Logging::add_log( "Failed to retrieve mapping" );
				return new WP_Error( 'Failed to retrieve mapping' );
			}

			$category_groups = $this->get_category_groups( $kb_id, $kb_category_id );
			if ( $category_groups === null ) {
				AMGR_Logging::add_log( "Failed to retrieve categories for group" );
				return new WP_Error( 'Failed to retrieve categories for group' );
			}

			$category_groups_ids = AMGR_Access_Utilities::get_group_ids( $category_groups );

			// add mapped groups that are not part of user membership
			foreach( $user_mapped_groups as $user_mapped_group ) {
				if ( in_array($user_mapped_group->kb_group_id, $category_groups_ids) && ! in_array( $user_mapped_group->kb_group_id, $user_category_groups )) {
					$user_category_groups[] = $user_mapped_group;
				}
			}
		}

		return $user_category_groups;
	}

	/**
	 * Get the KB Group that owns the KB Category. Ensure KB Group belongs to the KB.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @return array|null - array of group IDs or empty if none found or null on failure
	 */
	public function get_category_groups( $kb_id, $kb_category_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
			AMGR_Logging::add_log("Category ID is not valid", $kb_category_id);
			return null;
		}

		// get groups for this KB and Category
		$kb_category_group_records = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_category_id' => $kb_category_id) );
		if ( is_wp_error( $kb_category_group_records) ) {
			AMGR_Logging::add_log( "Cannot retrieve category " . $kb_category_id . " for KB " . $kb_id );
			return null;
		}
		if ( empty($kb_category_group_records) ) {
			return array();
		}

		// ensure KB Group belongs to the KB
		$kb_category_groups = array();
		foreach( $kb_category_group_records as $kb_category_group_record ) {

			$kb_group = epkb_get_instance()->db_kb_groups->get_group( $kb_id, $kb_category_group_record->kb_group_id);
			if ( $kb_group === null ) {
				AMGR_Logging::add_log( "Found KB Group does not belong to the KB " . $kb_category_id . " for KB " . $kb_id, $kb_category_group_record );
				return null;
			}

			$kb_category_groups[] = $kb_group;
		}

		return $kb_category_groups;
	}

	/**
	 * Get the KB Group that owns the KB Category. Ensure KB Group belongs to the KB.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @return array|null - array of group IDs or empty if none found or null on failure
	 */
    public function get_category_group_ids( $kb_id, $kb_category_id ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

        if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
            AMGR_Logging::add_log("Category ID is not valid", $kb_category_id);
            return null;
        }

        // get groups for this KB and Category
	    $kb_category_groups = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_category_id' => $kb_category_id) );
	    if ( is_wp_error( $kb_category_groups) ) {
		    AMGR_Logging::add_log( "Cannot retrieve category " . $kb_category_id . " for KB " . $kb_id );
		    return null;
	    }
	    if ( empty($kb_category_groups) ) {
		    return array();
	    }

	    $kb_groups = epkb_get_instance()->db_kb_groups->get_groups( $kb_id );
	    if ( $kb_groups == null ) {
	    	return null;
	    }
	    $kb_groups_ids = AMGR_Access_Utilities::get_group_ids( $kb_groups );

	    // ensure KB Group belongs to the KB
	    $kb_category_groups_ids = array();
	    foreach( $kb_category_groups as $kb_category_group ) {

		    if ( ! in_array($kb_category_group->kb_group_id, $kb_groups_ids) ) {
			    AMGR_Logging::add_log( "Found KB Group does not belong to the KB " . $kb_category_id . " for KB " . $kb_id, $kb_category_group );
			    return null;
		    }

		    $kb_category_groups_ids[] = $kb_category_group->kb_group_id;
	    }

        return $kb_category_groups_ids;
    }

    /**
     * Get KB Categories that belong to a KB Group. Ensure KB Group belongs to the KB.
     *
     * @param $kb_id
     * @param $kb_group_id
     * @return null|array - array of Objects or empty array
     *                    - EMPTY array() if no rows
     *                    - NULL on failure
     */
    public function get_group_categories( $kb_id, $kb_group_id ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

        if ( ! EPKB_Utilities::is_positive_int( $kb_group_id ) ) {
            AMGR_Logging::add_log("Group ID is not valid", $kb_group_id);
            return null;
        }

        // confirm that KB Group belongs to the KB
	    $log_error = EPKB_Utilities::get( 'amag_chosen_kb_group' ) == '';
        if ( ! epkb_get_instance()->db_kb_groups->is_group_from_kb( $kb_id, $kb_group_id, $log_error ) ) {
        	// do not log error if user gets wrong URL when choosing group on All Articles page
	        if ( $log_error ) {
		        AMGR_Logging::add_log( "Group does not belong to KB " . $kb_id, $kb_group_id );
	        }
            return null;
        }

	    $group_categories = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id) );
	    if ( is_wp_error($group_categories) ) {
		    AMGR_Logging::add_log( "Cannot retrieve categories for group " . $kb_group_id . " for KB " . $kb_id );
		    return null;
	    }

        return $group_categories === null ? array() : $group_categories;
    }

	/**
	 * Get all KB categories that all groups have access to.
	 *
	 * @param $kb_id
	 * @return array|null - array of Objects or empty array
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	 public function get_kb_categories( $kb_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$kb_categories = $this->get_rows_by_column_value( 'kb_id', $kb_id );
		if ( is_wp_error($kb_categories) ) {
			AMGR_Logging::add_log("Failed to get categories.", 'KB ID: ' . $kb_id);
			return null;
		}

		// no categories found
		if ( $kb_categories === null ) {
			return array();
		}

		return $kb_categories;
	}

	/**
	 * Get specific KB Category.
	 *
	 * @param $kb_category_id
	 * @return null|false|object - category
	 *                    - false if no category found
	 *                    - NULL on failure
	 */
	public function get_kb_category( $kb_category_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
			AMGR_Logging::add_log("KB Category ID is not valid", $kb_category_id);
			return null;
		}

		$kb_category = $this->get_rows_by_where_clause( array('kb_category_id' => $kb_category_id) );
		if ( is_wp_error($kb_category) ) {
			AMGR_Logging::add_log("Failed to invoke select for KB Category: ", $kb_category_id);
			return null;
		}
		if ( empty($kb_category) ) {
			return false;
		}
		if ( count($kb_category) > 1 ) {
			AMGR_Logging::add_log( "Found more than one  KB Group for given category " . $kb_category_id, $kb_category );
			return null;
		}

		$kb_category = reset($kb_category);

		return $kb_category;
	}

	/**
	 * Add KB Group to KB Category. Ensure KB Group belongs to the KB.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @param $kb_group_id
	 *
	 * @return boolean
	 */
    public function add_category_to_group( $kb_id, $kb_group_id, $kb_category_id ) {

		$has_access = AMGR_Access_Utilities::is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, AMGR_KB_Role::KB_ROLE_EDITOR );
		if ( $has_access === null || ! $has_access ) {
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_category_id) ) {
			AMGR_Logging::add_log("Category ID is not valid", $kb_category_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return false;
		}

		// confirm that KB Group belongs to the KB
		if ( ! epkb_get_instance()->db_kb_groups->is_group_from_kb( $kb_id, $kb_group_id ) ) {
			AMGR_Logging::add_log("Group does not belong to KB " . $kb_id, $kb_group_id);
			return false;
		}

		// confirm the category is KB Category
		$term = get_term_by('id', $kb_category_id, EPKB_KB_Handler::get_category_taxonomy_name( $kb_id ) );
		if ( empty($term) || ! $term instanceof WP_Term ) {
			AMGR_Logging::add_log("Category is not KB Category: " . $kb_category_id . " (33)", $kb_id);
			return false;
		}

		// 1. validate the KB Group
		$kb_groups = new AMGR_DB_KB_Groups();
		$result = $kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $result) ) {
			AMGR_Logging::add_log("Cannot add KB Group to KB Category. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id . ', category ID: ' . $kb_category_id);
			return false;
		}
		if ( $result === null ) {
			AMGR_Logging::add_log("Add Group: KB Group ID was not found", $kb_group_id);
			return false;
		}

		// 2. if KB Group is Public then make its articles with 'Publish' post status
		$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			AMGR_Logging::add_log("Add Group: failed to check if group is public", $kb_group_id);
			return false;
		}
		if ( $is_group_public ) {
			$result = AMGR_Access_Utilities::set_new_status_for_category_articles( $kb_id, $kb_category_id, true );
			if ( $result == false ) {
				AMGR_Logging::add_log("Add Group: failed to update category articles to publish", $kb_group_id);
				return false;
			}
		}

		// 3. insert the record
		$result = parent::insert_record( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'kb_category_id' => $kb_category_id ) );
		if ( $result !== false ) {
			$this->set_last_changed();
		}

		return $result !== false;
	}

	/**
	 * Delete a record about KB Group having access to KB Category
	 *
	 * @param $kb_id
	 * @param int $kb_category_id
	 * @return bool - return false on error
	 */
	public function delete_category( $kb_id, $kb_category_id ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return false;
	    }

	    if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
		    AMGR_Logging::add_log("Category ID is not valid", $kb_category_id);
		    return false;
	    }

	    // 0. get deleted category articles
		$deleted_category_article_ids = AMGR_Access_Utilities::get_deleted_category_articles( $kb_id, $kb_category_id );
	    if ( $deleted_category_article_ids === false ) {
	    	return false;
	    }

		// 1. delete the KB Category
		$result = parent::delete_rows_by_where_clause( array( 'kb_id' => $kb_id, 'kb_category_id' => $kb_category_id ) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// 2. verify that category records were deleted
		$deleted = $this->get_rows_by_where_clause( array( 'kb_id' => $kb_id, 'kb_category_id' => $kb_category_id ) );
		if ( is_wp_error($deleted) ) {
		    AMGR_Logging::add_log( "DB error: cannot delete category " . $kb_category_id );
			return false;
		}

		if ( $deleted !== null && count($deleted) > 0 ) {
			AMGR_Logging::add_log("Delete category from read-only categories table: category still in the table");
			return false;
		}

		// 3. update category articles status to private if deleting their only Public category
		$result = AMGR_Access_Utilities::update_status_of_deleted_category_articles( $kb_id, $deleted_category_article_ids );
		if ( empty($result) ) {
			AMGR_Logging::add_log("Could not update category article post status: " . $kb_id, $kb_category_id);
			return false;
		}

		return true;
	}

	/**
	 * Delete a record about KB Group having access to any KB Categories
	 *
	 * @param $kb_id
	 * @param int $kb_group_id
	 *
	 * @return bool - return false on error
	 */
	public function delete_group_categories( $kb_id, $kb_group_id ) {

		$has_access = AMGR_Access_Utilities::is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, AMGR_KB_Role::KB_ROLE_EDITOR );
		if ( $has_access === null || ! $has_access ) {
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_group_id ) ) {
			AMGR_Logging::add_log("Group ID is not valid", $kb_group_id);
			return false;
		}

		// 1. delete the KB Group from all categories; IGNORE KB ID
		$result = parent::delete_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id ) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// 2. verify that category records were deleted
		$deleted = $this->get_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id ) );
		if ( is_wp_error($deleted) ) {
			AMGR_Logging::add_log( "DB error: cannot delete all categories for KB Group " . $kb_group_id );
			return false;
		} else if ( ! empty($deleted) ) {
			AMGR_Logging::add_log("Cannot delete all categories for KB Group " . $kb_group_id);
			return false;
		}

		return true;
	}

	/**
	 * Delete KB Group access to a particular KB Category
	 *
	 * @param $kb_id
	 * @param int $kb_group_id
	 * @param $kb_category_id
	 *
	 * @return bool - return false on error
	 */
	public function delete_group_category( $kb_id, $kb_group_id, $kb_category_id ) {

		$has_access = AMGR_Access_Utilities::is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, AMGR_KB_Role::KB_ROLE_EDITOR );
		if ( $has_access === null || ! $has_access ) {
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_group_id ) ) {
			AMGR_Logging::add_log("Group ID is not valid", $kb_group_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_category_id ) ) {
			AMGR_Logging::add_log("Category ID is not valid", $kb_category_id);
			return false;
		}

		// 0. get deleted category articles
		$deleted_category_article_ids = AMGR_Access_Utilities::get_deleted_category_articles( $kb_id, $kb_category_id );
		if ( $deleted_category_article_ids === false ) {
			return false;
		}

		// 1. delete the KB Group from all categories; IGNORE KB ID
		$result = parent::delete_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id, 'kb_category_id' => $kb_category_id ) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// 2. verify that category records were deleted
		$deleted = $this->get_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id, 'kb_category_id' => $kb_category_id ) );
		if ( is_wp_error($deleted) ) {
			AMGR_Logging::add_log( "DB error: cannot delete all categories for KB Group " . $kb_group_id );
			return false;
		} else if ( ! empty($deleted) ) {
			AMGR_Logging::add_log("Cannot delete all categories for KB Group " . $kb_group_id);
			return false;
		}

		// 3. update category articles status to private if deleting their only Public category
		$result = AMGR_Access_Utilities::update_status_of_deleted_category_articles( $kb_id, $deleted_category_article_ids );
		if ( empty($result) ) {
			AMGR_Logging::add_log("Could not update category article post status: " . $kb_id, $kb_category_id);
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
        wp_cache_set( 'last_changed', microtime(), $this->cache_group );
    }

    /**
     * Retrieves the value of the last_changed cache key for customers.
     *
     * @access public
     * @since  2.8
     */
    public function get_last_changed() {
        if ( function_exists( 'wp_cache_get_last_changed' ) ) {
            return wp_cache_get_last_changed( $this->cache_group );
        }

        $last_changed = wp_cache_get( 'last_changed', $this->cache_group );
        if ( ! $last_changed ) {
            $last_changed = microtime();
            wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
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
	                kb_id               bigint(20) NOT NULL,
	                kb_group_id         bigint(20) NOT NULL,
	                kb_category_id      bigint(20) NOT NULL,
		            created_by          bigint(20) NOT NULL,
	                date_created        datetime NOT NULL,
	                date_updated        datetime NOT NULL,
	                PRIMARY KEY (kb_group_id, kb_category_id),
	                KEY ix_kb_id (kb_id),
	                KEY ix_kb_category_id (kb_category_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );
    }
}
