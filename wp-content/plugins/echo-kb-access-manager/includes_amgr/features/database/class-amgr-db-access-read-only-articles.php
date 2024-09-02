<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * READ-ONLY access to KB Articles.
 *
 * @property string primary_key
 * @property string table_name
 */
class AMGR_DB_Access_Read_Only_Articles extends AMGR_DB  {

    /**
     * The name of the cache group.
     *
     * @access public
     * @since  2.8
     * @var string
     */
    public $cache_group = 'amgr_access_read_articles';

    /**
     * Get things started
     *
     * @access  public
     * @since   2.1
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'amgr_access_read_articles';
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
            'kb_article_id'  => '%d',
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
     * Get READ-ONLY KB Article records
     *
     * @param $kb_id
     * @param $kb_article_id
     * @return array|null - array of Objects
     *                    - EMPTY array() if no rows
     *                    - NULL on failure
     */
    public function get_read_only_article_groups( $kb_id, $kb_article_id ) {

	    if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
		    AMGR_Logging::add_log("KB ID is not valid", $kb_id);
		    return null;
	    }

        if ( ! EPKB_Utilities::is_positive_int( $kb_article_id ) ) {
            AMGR_Logging::add_log("Post ID is not valid", $kb_article_id);
            return null;
        }

	    $kb_articles = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_article_id' => $kb_article_id) );
	    if ( is_wp_error($kb_articles) ) {
		    AMGR_Logging::add_log( "Cannot retrieve article " . $kb_article_id . " for KB " . $kb_id );
		    return null;
	    }

        return $kb_articles === null ? array() : $kb_articles;
    }

	/**
	 * Get all READ-ONLY articles IDs from single KB
	 *
	 * @param $kb_id
	 * @return array|null - array of Objects
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_all_read_only_kb_articles_ids( $kb_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$kb_articles_ids = $this->get_column_values_by( 'kb_article_id', 'kb_id', $kb_id );
		if ( is_wp_error($kb_articles_ids) ) {
			AMGR_Logging::add_log( "Cannot retrieve articles for given KB", $kb_id, $kb_articles_ids );
			return null;
		}
		if ( $kb_articles_ids === null ) {
			return array();
		}

		return $kb_articles_ids;
	}

	/**
	 * Get READ-ONLY KB Articles for given group.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 *
	 * @return array|null - array of Objects
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_group_read_only_articles_ids( $kb_id, $kb_group_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return null;
		}

		$group_articles = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id) );
		if ( is_wp_error($group_articles) ) {
			AMGR_Logging::add_log( "Cannot retrieve articles for group " . $kb_group_id . " for KB " . $kb_id );
			return null;
		}

		if ( empty($group_articles) ) {
			return array();
		}

		$ro_articles_ids = array();
		foreach( $group_articles as $group_article ) {
			$ro_articles_ids[] = (int)$group_article->kb_article_id;
		}

		return $ro_articles_ids;
	}

	/**
	 * Get READ-ONLY KB Articles for given group.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_article_id
	 *
	 * @return boolean|null - array of Objects
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function is_article_read_only_for_group( $kb_id, $kb_group_id, $kb_article_id ) {

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_group_id) ) {
			AMGR_Logging::add_log("KB Group ID is not valid", $kb_group_id);
			return null;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_article_id) ) {
			AMGR_Logging::add_log("Article ID is not valid", $kb_article_id);
			return false;
		}

		$group_articles = $this->get_rows_by_where_clause( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'kb_article_id' => $kb_article_id) );
		if ( is_wp_error($group_articles) ) {
			AMGR_Logging::add_log( "Cannot retrieve articles for group " . $kb_group_id . " for KB " . $kb_id );
			return null;
		}

		return ! empty($group_articles);
	}

	/**
	 * Add KB Group to READ-ONLY article. Ensure KB Group belongs to the KB.
	 *
	 * @param $kb_id
	 * @param $kb_article_id
	 * @param $kb_group_id
	 *
	 * @return boolean
	 */
	public function add_read_only_group_to_article( $kb_id, $kb_group_id, $kb_article_id ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$has_access = AMGR_Access_Utilities::is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, AMGR_KB_Role::KB_ROLE_CONTRIBUTOR );
		if ( $has_access === null || ! $has_access ) {
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log("KB ID is not valid", $kb_id);
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int($kb_article_id) ) {
			AMGR_Logging::add_log("Article ID is not valid", $kb_article_id);
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

		// 1. validate the KB Group
		$kb_groups = new AMGR_DB_KB_Groups();
		$result = $kb_groups->get_by_primary_key( $kb_group_id );
		if ( is_wp_error( $result) ) {
			AMGR_Logging::add_log("Cannot add KB Group to KB Article. KB ID: ", $kb_id . ', KB Group ID: ' . $kb_group_id . ', article ID: ' . $kb_article_id);
			return false;
		}
		if ( $result === null ) {
			AMGR_Logging::add_log("KB Group ID was not found", $kb_group_id);
			return false;
		}

		// 2. insert the record
		$result = parent::insert_record( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'kb_article_id' => $kb_article_id, ) );
		if ( $result !== false ) {
			$this->set_last_changed();
		}

		// 3. if KB Group is Public then make this article with 'Publish' post status
		$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			AMGR_Logging::add_log("Add Group: failed to check if group is public", $kb_group_id);
			return false;
		}
		if ( $is_group_public ) {
			if ( false === $wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $kb_article_id ) ) ) {
				AMGR_Logging::add_log( "Could not update post status in the database: " . $wpdb->last_error );
				return false;
			}
		}

		return $result !== false;
	}

	/**
	 * Delete all READ-ONLY article records when article is being deleted
	 *
	 * @param $kb_id
	 * @param int $kb_article_id
	 * @return bool - return false on error
	 */
	public function delete_article( $kb_id, $kb_article_id ) {

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGR_Logging::add_log("Attempt unauthorized access to Access Manager page", $kb_id);
			return false;
		}

		// delete the KB article
		$result = parent::delete_rows_by_where_clause( array( 'kb_id' => $kb_id, 'kb_article_id' => $kb_article_id ) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// verify that article records were deleted
		$deleted = $this->get_rows_by_where_clause( array( 'kb_id' => $kb_id, 'kb_article_id' => $kb_article_id ) );
		if ( is_wp_error($deleted) ) {
			return false;
		}

		if ( $deleted !== null && count($deleted) > 0 ) {
			AMGR_Logging::add_log("Delete article from read-only articles table: article still in the table");
			return false;
		}

		return true;
	}

	/**
	 * Delete a READ-ONLY record about KB Group having access to any KB articles
	 *
	 * @param $kb_id
	 * @param int $kb_group_id
	 *
	 * @return bool - return false on error
	 */
	public function delete_group_articles( $kb_id, $kb_group_id ) {

		$has_access = AMGR_Access_Utilities::is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, AMGR_KB_Role::KB_ROLE_EDITOR );
		if ( $has_access === null || ! $has_access ) {
			return false;
		}

		if ( ! EPKB_Utilities::is_positive_int( $kb_group_id ) ) {
			AMGR_Logging::add_log("Group ID is not valid", $kb_group_id);
			return false;
		}

		$group_article_ids = $this->get_group_read_only_articles_ids( $kb_id, $kb_group_id );
		if ( $group_article_ids === null ) {
			AMGR_Logging::add_log("Add Group: failed to retrieve group articles", $kb_group_id);
			return false;
		}

		// 1. delete the KB Group from all articles; IGNORE KB ID
		$result = parent::delete_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id ) );
		if ( $result ) {
			$this->set_last_changed();
		} else {
			return false;
		}

		// 2. verify that article records were deleted
		$deleted = $this->get_rows_by_where_clause( array( 'kb_group_id' => $kb_group_id ) );
		if ( is_wp_error($deleted) ) {
			AMGR_Logging::add_log( "DB error: cannot delete all articles for KB Group " . $kb_group_id );
			return false;
		} else if ( ! empty($deleted) ) {
			AMGR_Logging::add_log("Cannot delete all articles for KB Group " . $kb_group_id);
			return false;
		}

		// 3. if KB Group is Public then make this article with 'Publish' post status
		$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			AMGR_Logging::add_log("Add Group: failed to check if group is public", $kb_group_id);
			return false;
		}
		if ( $is_group_public ) {

			$result = AMGR_Access_Utilities::update_status_of_deleted_category_articles( $kb_id, $group_article_ids );
			if ( empty($result) ) {
				return false;
			}
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
                kb_id           bigint(20) NOT NULL,
                kb_group_id     bigint(20) NOT NULL,
                kb_article_id   bigint(20) NOT NULL,
	            created_by      bigint(20) NOT NULL,
                date_created    datetime NOT NULL,
                date_updated    datetime NOT NULL,
                PRIMARY KEY (kb_group_id, kb_article_id),
                KEY kb_id (kb_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta( $sql );
    }

}
