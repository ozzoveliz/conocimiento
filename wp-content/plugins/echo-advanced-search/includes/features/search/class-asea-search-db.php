<?php  // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CRUD for search data
 *
 * @property string primary_key
 * @property string table_name
 */
class ASEA_Search_DB extends ASEA_DB  {

	/**
	 * Get things started
	 *
	 * @access  public
	 */
	public function __construct() {
		/** @var $wpdb Wpdb */
		global $wpdb;

        $this->table_name  = $wpdb->prefix . 'ep'.'kb_kb_search_data';
		$this->primary_key = 'search_id';
	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 */
	public function get_column_format() {
		return array(
			'search_id'         => '%d',
			'kb_id'             => '%d',
			'source'            => '%s',
			'user_id'           => '%d',
			'user_uid'          => '%s',
			'search_date'       => '%s',
			'user_input'        => '%s',
			'search_keywords'   => '%s',
			'count'             => '%d'
		);
	}

	/**
	 * Get default column values
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'source'          => '',
			'user_uid'        => '',
			'user_input'      => '',
			'search_keywords' => '',
			'search_date'     => date( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Retrieve MOST POPULAR SEARCHES
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $limit
	 * @param $where_condition
	 *
	 * @return null|array - array of Objects or empty
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_most_popular_searches( $kb_id, $date_from, $date_to, $limit, $where_condition='' ) {

		if ( ! ASEA_Utilities::is_positive_int($kb_id) ) {
			ASEA_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$result = $this->get_rows_by_date_range( $kb_id, 'search_date', $date_from, $date_to, 'times DESC', 'search_keywords', $limit, ' AND count > 0 ' . $where_condition );
		if ( is_wp_error( $result) ) {
			ASEA_Logging::add_log("Cannot get most popular Search records: ", $kb_id);
			return null;
		}

		return $result;
	}

	/**
	 * Retrieve MOST POPULAR SEARCHES
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $limit
	 * @param $where_condition
	 *
	 * @return null|array - array of Objects or empty
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_no_results_searches( $kb_id, $date_from, $date_to, $limit, $where_condition='' ) {

		if ( ! ASEA_Utilities::is_positive_int($kb_id) ) {
			ASEA_Logging::add_log("KB ID is not valid", $kb_id);
			return null;
		}

		$result = $this->get_rows_by_date_range( $kb_id, 'search_date', $date_from, $date_to, 'times DESC', 'search_keywords', $limit, ' AND count = 0 ' . $where_condition );
		if ( is_wp_error( $result) ) {
			ASEA_Logging::add_log("Cannot get no results Search records: ", $kb_id);
			return null;
		}

		return $result;
	}

	/**
	 * Retrieve SEARCH COUNT
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $where_condition
	 *
	 * @return int
	 */
	public function get_search_count( $kb_id, $date_from, $date_to, $where_condition='' ) {

		if ( ! ASEA_Utilities::is_positive_int($kb_id) ) {
			ASEA_Logging::add_log("KB ID is not valid", $kb_id);
			return 0;
		}

		$result = $this->get_count_rows_range( $kb_id, 'search_date', $date_from, $date_to, $where_condition );
		if ( is_wp_error( $result) ) {
			ASEA_Logging::add_log("Cannot get Search count: ", $kb_id);
			return 0;
		}

		return $result;
	}

	/**
	 * Retrieve "no results" SEARCH COUNT
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $where_condition
	 *
	 * @return int
	 */
	public function get_no_result_search_count( $kb_id, $date_from, $date_to, $where_condition='' ) {

		if ( ! ASEA_Utilities::is_positive_int($kb_id) ) {
			ASEA_Logging::add_log("KB ID is not valid", $kb_id);
			return 0;
		}

		$result = $this->get_count_rows_range( $kb_id, 'search_date', $date_from, $date_to, ' AND count = 0 ' . $where_condition );
		if ( is_wp_error( $result) ) {
			ASEA_Logging::add_log("Cannot get Search count: ", $kb_id);
			return 0;
		}

		return $result;
	}

	/**
	 * Insert a new Search record
	 *
	 * @param $kb_id
	 * @param $source
	 * @param $user_id
	 * @param $user_uid
	 * @param $user_input
	 * @param $search_keywords
	 * @param $count
	 *
	 * @return int|WP_Error - WP_Error or record ID
	 */
	public function insert_search_record( $kb_id, $source, $user_id, $user_uid, $user_input, $search_keywords, $count ) {

		// ensure KB exists
		if ( ! $this->is_kb_id_valid( $kb_id ) ) {
			ASEA_Logging::add_log("KB ID is not valid: ", $kb_id);
			return null;
		}

		// insert the record
		$record = array('kb_id' => $kb_id, 'user_id' => $user_id, 'source' => $source, 'user_uid' => $user_uid, 'user_input' => $user_input, 'search_keywords' => $search_keywords, 'count' => $count);
		$search_id = parent::insert_record( $record );
		if ( empty($search_id) ) {
			return new WP_Error( 'db-insert-error', 'Could not insert Help Dialog submission record' );
		}

		return $search_id;
	}

	/**
	 * @param $kb_id
	 *
	 * @return bool
	 */
	private function is_kb_id_valid( $kb_id ) {
		$kb_ids = asea_get_instance()->kb_config_obj->get_kb_ids();
		return empty($kb_ids) ? false : in_array($kb_id, $kb_ids);
	}

	/**
	 * Delete all asea analytics data for $kb_id
	 * @param $kb_id
	 * @return bool
	 */
	public function delete_analytics_data( $kb_id ) {
		return $this->delete_rows_by_where_clause( array( 'kb_id' => $kb_id ) );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 */
	public function create_table() {
		global $wpdb;

		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
	                search_id       INT(20) NOT NULL AUTO_INCREMENT,
	                kb_id           SMALLINT(20) NOT NULL,
	                source          varchar(50) NOT NULL,
	                user_id         INT(20) NOT NULL,
	                user_uid        varchar(50) NOT NULL,
	                search_date     datetime NOT NULL,
	                user_input      varchar(150) NOT NULL,
	                search_keywords varchar(150) NOT NULL,
	                count           SMALLINT(20) NOT NULL,
	                PRIMARY KEY  (search_id),
	                KEY ix_asea_kb_id (kb_id),
	                KEY ix_asea_date (search_date),
	                KEY ix_asea_search_keywords (search_keywords)
		) " . $collate . ";";

		dbDelta( $sql );
	}
}
