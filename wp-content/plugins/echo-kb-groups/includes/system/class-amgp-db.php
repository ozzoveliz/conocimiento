<?php  if ( ! defined( 'ABSPATH' ) ) exit;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Base class for database access.
 * Based on EDD_DB class.
*/
abstract class AMGP_DB {

	/**
	 * The name of KB database table
	 */
	protected $table_name;

	/**
	 * The name of the primary key column
	 */
	protected $primary_key;

	public function __construct() {}

	/**
	 * Get table columns
	 */
	public function get_column_format() {
		return array();
	}

	/**
	 * Default column values
	 */
	public function get_column_defaults() {
		return array();
	}

    /**
     * Retrieve a row by the primary key
     *
     * @param $primary_key_value
     *
     * @return Object|WP_Error|null - row as an Object with properties as column names e.g. $result->ID
     *                              - null if 0 records found
     *                              - WP_Error on failure
     */
	public function get_by_primary_key( $primary_key_value ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty($this->primary_key) ) {
			AMGP_Logging::add_log("Primary key is empty", $this->primary_key);
			return null;
		}

		if ( ! AMGP_Utilities::is_positive_int($primary_key_value) ) {
			AMGP_Logging::add_log("Primary key is not valid", $primary_key_value);
			return null;
        }

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $primary_key_value ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Primary Key: ' . $primary_key_value );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Retrieve a row by a specific value in a column
	 *
	 * @param $column
	 * @param $column_value
	 *
	 * @return Object|WP_Error|null - row as an Object with properties as column names e.g. $result->ID
	 *                              - null if 0 records found
	 *                              - WP_Error on failure
	 */
	public function get_a_row_by_column_value( $column, $column_value ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		$column = esc_sql( $column );
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $column_value ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Column - value: ' . $column . ' - ' . $column_value );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Retrieve multiple rows by a specific value in a column
	 *
	 * @param $column
	 * @param $column_value
	 *
	 * @return array|WP_Error|null - column value
	 *                             - null if 0 records found
	 *                             - WP_Error on error
	 */
	public function get_rows_by_column_value( $column, $column_value ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$column = esc_sql( $column );
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s ;", $column_value ) );
		if ( ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: ", $wpdb->last_error );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Retrieve a row by a WHERE clause
	 *
	 * @param $where_data
	 *
	 * @return Object|WP_Error|null - row as an Object with properties as column names e.g. $result->ID
	 *                              - null if 0 records found
	 *                              - WP_Error on failure
	 */
	public function get_a_row_by_where_clause( array $where_data ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$values = array();
		$where = ' ';
		$first = true;
		foreach( $where_data as $column => $value ) {
			$format = $this->get_column_format();
			$where .= ($first ? '' : ' AND ') . esc_sql($column) . ' = ' . $format[$column];
			$values[] = $value;
			$first = false;
		}

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE {$where} LIMIT 1;", $values ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: ", $wpdb->last_error );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Retrieve multiple rows using a where clause
	 *
	 * @param $where_data
	 *
	 * @return array|WP_Error|null - column value
	 *                             - null if 0 records found
	 *                             - WP_Error on error
	 */
	public function get_rows_by_where_clause( array $where_data ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$values = array();
		$where = ' ';
		$first = true;
		foreach( $where_data as $column => $value ) {
			$format = $this->get_column_format();
			$where .= ($first ? '' : ' AND ') . esc_sql($column) . ' = ' . $format[$column];
			$values[] = $value;
			$first = false;
		}

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE {$where} ;", $values ) );
		if ( ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log("DB failure: ", $wpdb->last_error);
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Get all records from the table.
	 *
	 * @return array|WP_Error|null - column value
	 *                             - null if 0 records found
	 *                             - WP_Error on error
	 */
	public function get_all_rows() {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$result = $wpdb->get_results( "SELECT * FROM $this->table_name ;" );
		if ( ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log("DB failure: ", $wpdb->last_error);
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

    /**
     * Retrieve a specific column's value by the primary key
     *
     * @param $column
     * @param $primary_key
     *
     * @return string|WP_Error|null - column value
     *                              - null if 0 records found
     *                              - WP_Error on failure
     */
	public function get_column_value_by_primary_key( $column, $primary_key ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty($this->primary_key) ) {
			return null;
		}

		if ( ! AMGP_Utilities::is_positive_int($primary_key) ) {
			AMGP_Logging::add_log("Primary key is not valid", $primary_key);
			return null;
		}

		$column = esc_sql( $column );
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $primary_key ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Column: ' . $column. ', Primary Key: ' . $primary_key );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

    /**
     * Retrieve a specific column's value by the the specified column / value
     *
     * @param $select_column - returned column value
     * @param $column_name
     * @param $column_value
     *
     * @return string|WP_Error|null - column value
     *                              - null if 0 records found
     *                              - WP_Error on failure
     */
	public function get_column_value_by( $select_column, $column_name, $column_value ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		$select_column = esc_sql( $select_column );
		$column_name   = esc_sql( $column_name );
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT $select_column FROM $this->table_name WHERE $column_name = %s LIMIT 1;", $column_value ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Select Column: ' . $select_column. ', Column - value: ' . $column_name . ' - ' . $column_value );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @param $select_column - returned column value
	 * @param $column_name
	 * @param $column_value
	 *
	 * @return array|WP_Error|null - column value
	 *                             - null if 0 records found
	 *                             - WP_Error on error
	 */
	public function get_column_values_by( $select_column, $column_name, $column_value ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$select_column = esc_sql( $select_column );
		$column_name   = esc_sql( $column_name );
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT $select_column FROM $this->table_name WHERE $column_name = %s ;", $column_value ) );
		if ( ! empty($wpdb->last_error) ) {
			AMGP_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Select Column: ' . $select_column. ', Column - value: ' . $column_name . ' - ' . $column_value );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		return $result;
	}

    /**
     * Insert a new row
     *
     * @param $data - Data to insert (in column => value pairs).
     *                Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     *                Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
     * @return int|false - inserted record ID
     */
    // KEEP PROTECTED to let implementation to handle security check
	protected function insert_record( $data ) { /** KEEP PROTECTED */
        /** @var $wpdb Wpdb */
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array
		$column_formats = $this->get_column_format();

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$user = AMGP_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			AMGP_Logging::add_log("Could not find user when inserting record into " . $this->table_name, $data);
			return false;
		}

		$data['created_by'] = $user->ID;

		if ( false === $wpdb->insert( $this->table_name, $data, $column_formats ) ) {
			AMGP_Logging::add_log("Could not insert record into " . $this->table_name . " table. Data to be inserted: ", $data);
			return false;
        }

		return $wpdb->insert_id;
	}

    /**
     * Update a row
     *
     * @param $id - primary key or column value used in WHERE
     * @param array $data - Data to update (in column => value pairs).
     *                      Both $data columns and $data values should be "raw" (neither should be SQL escaped).
     *                      Sending a null value will cause the column to be set to NULL - the corresponding
     *                      format is ignored in this case.
     * @param string $column_name - used in the WHERE clause. default is the primary key
     *
     * @return bool - return false on error
     */
	// KEEP PROTECTED
	protected function update_record( $id, $data = array(), $column_name = '' ) {  /** KEEP PROTECTED */
        /** @var $wpdb Wpdb */
		global $wpdb;

		// Row ID must be a positive integer
		if ( ! AMGP_Utilities::is_positive_int( $id ) ) {
			AMGP_Logging::add_log("Row ID is not valid", $id);
			return false;
		}

		if ( empty( $column_name ) ) {
			$column_name = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_column_format();

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $column_name => $id ), $column_formats ) ) {
			AMGP_Logging::add_log("Could not update record in " . $this->table_name . " table. Row id: " . $id . ". Data to be inserted: ", $data);
			return false;
		}

		return true;
	}

	/**
	 * If record exist then UPDATE it otherwise INSERT it.
	 *
	 * @param $primary_key_value
	 * @param $data
	 *
	 * @return bool
	 */
	// KEEP PROTECTED
	protected function upsert_record( $primary_key_value, $data ) {

		if ( empty($this->primary_key) ) {
			return null;
		}

		$record = $this->get_by_primary_key( $primary_key_value );
		if ( is_wp_error( $record) ) {
			AMGP_Logging::add_log("Cannot get by primary key: ", $primary_key_value);
			return false;
		}

		// if no record found (or error occurred), INSERT the record
		if ( empty($record) ) {
			$result = $this->insert_record( array($this->primary_key => $primary_key_value) + $data );
			return $result > 0;
		} else {
			return $this->update_record( $primary_key_value, $data );
		}
	}

    /**
     * Delete a row identified by the primary key
     *
     * @param int $primary_key
     *
     * @return bool
     */
	// KEEP PROTECTED
	protected function delete_record( $primary_key = 0 ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty($this->primary_key) ) {
			return null;
		}

		// Row ID must be positive integer
        if ( ! AMGP_Utilities::is_positive_int( $primary_key ) ) {
	        AMGP_Logging::add_log("Row ID is not valid", $primary_key);
	        return false;
        }

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $primary_key ) ) ) {
			AMGP_Logging::add_log("Could not delete record in " . $this->table_name . " table. Row id: " . $primary_key);
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @param $column_name
	 * @param $column_value
	 * @return bool - return false on error
	 */
	// KEEP PROTECTED
	protected function delete_record_by_column_value( $column_name, $column_value ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$column_name = esc_sql( $column_name );
		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $column_name = %d", $column_value ) ) ) {
			AMGP_Logging::add_log("Could not delete records in " . $this->table_name . " table. Column $column_name and value $column_value");
			return false;
		}

		return true;
	}

	/**
	 * Delete multiple rows using a where clause
	 *
	 * @param $where_data
	 * @return bool - return false on error
	 */
	// KEEP PROTECTED
	protected function delete_rows_by_where_clause( array $where_data ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$values = array();
		$where = ' ';
		$first = true;
		foreach( $where_data as $column => $value ) {
			$format = $this->get_column_format();
			$where .= ($first ? '' : ' AND ') . esc_sql($column) . ' = ' . $format[$column];
			$values[] = $value;
			$first = false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE {$where} ;", $values ) ) ) {
			AMGP_Logging::add_log("Could not delete records in " . $this->table_name . " table. Where is {$where}");
			return false;
		}

		return true;
	}

	/**
	 * Check if the given table exists
     *
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists( $table ) {
        /** @var $wpdb Wpdb */
		global $wpdb;

		$table = sanitize_text_field( $table );
		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @return bool Returns if the customers table was installed and upgrade routine run
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

	public function getTableName() {
		return $this->table_name;
	}
}
