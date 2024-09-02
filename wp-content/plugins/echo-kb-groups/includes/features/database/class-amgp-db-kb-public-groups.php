<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle PUBLIC Groups
 *
 * @property string primary_key
 *@property string table_name
 */
class AMGP_DB_KB_Public_Groups extends AMGP_DB  {

	const PUBLIC_GROUP_NAME = 'Public';

    /**
     * The name of the cache group.
     *
     * @access public
     * @since  2.8
     * @var string
     */
    public $cache_group = AMGP_KB_Core::DB_KB_PUBLIC_GROUPS;

    /**
     * Get things started
     *
     * @access  public
     * @since   2.1
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . AMGP_KB_Core::DB_KB_PUBLIC_GROUPS;
        $this->primary_key = 'kb_group_id';
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
     * @since   2.1
     */
    public function get_column_defaults() {
        return array(
            'name'           => '',
            'date_created'   => date( 'Y-m-d H:i:s' ),
            'date_updated'   => date( 'Y-m-d H:i:s' ),
        );
    }

	/**
	 * Retrieve KB PUBLIC Group
	 *
	 * @param $kb_id
	 *
	 * @return null|Object|WP_Error - row as Object with properties as column names e.g. $result->I
	 *                              - null if missing
	 *                              - WP_Error on failure
	 */
	public function get_public_group( $kb_id ) {

		if ( ! AMGP_Utilities::is_positive_int($kb_id) ) {
			AMGP_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$group = $this->get_a_row_by_where_clause( array('kb_id' => $kb_id) );
		if ( is_wp_error( $group) ) {
			AMGP_Logging::add_log("Cannot get KB Group record: ", $kb_id);
			return $group;
		}

		if ( empty($group) ) {
			AMGP_LOGGING::add_log( 'Could not find Public group.', $kb_id );
			return null;
		}

		return $group;
	}

	/**
	 * Insert PUBLIC group.
	 *
	 * @param $kb_id
	 * @return int|null - null on Error or record ID
	 */
    public function insert_public_group( $kb_id ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGP_Logging::add_log("Attempt unauthorized access to Access Manager page", $kb_id);
		    return null;
	    }

	    // ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    AMGP_Logging::add_log("KB ID is not valid: ", $kb_id);
		    return null;
	    }

		// first insert the new public group into the KB Groups table
	    $kb_group_id = amgp_get_instance()->db_kb_groups->insert_group( $kb_id, self::PUBLIC_GROUP_NAME );
	    if ( empty($kb_group_id) ) {
		    AMGP_Logging::add_log('Could not create PUBLIC group.', $kb_id);
		    null;
	    }

	    // insert the record
	    $kb_group_id = parent::insert_record( array('kb_id' => $kb_id, 'kb_group_id' => $kb_group_id, 'name' => self::PUBLIC_GROUP_NAME ) );
	    if ( empty($kb_group_id) ) {
		    $kb_group_id = null;
	    } else {
		    $this->set_last_changed();
	    }

	    return $kb_group_id;
    }

    public function is_public_group( $kb_id, $kb_group_id ) {

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

	    return ! empty($group);
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
	                kb_id        bigint(20) NOT NULL,
	                kb_group_id  bigint(20) NOT NULL AUTO_INCREMENT,
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
