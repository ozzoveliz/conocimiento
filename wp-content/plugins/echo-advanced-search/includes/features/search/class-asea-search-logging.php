<?php

/**
 * Stores all searches for further analysis.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_Search_Logging {

	const LOGGING_OPTION_NAME = 'asea_search_records';
	const MAX_NOF_LOGS_STORED = 10000;

	public function __construct() {
		add_action( 'asea_add_search_log', array($this, 'add_search_log'), 10, 5 );
	}

	/**
	 * Add a new log entry to the log stored in the WP options table
	 *
	 * @param $user_input - sanitized - unfiltered (as entered by user)
	 * @param $search_keywords - filtered
	 * @param $count
	 * @param string $source
	 * @param $kb_id
	 */
	public static function add_search_log( $user_input, $search_keywords, $count, $source='kb-search', $kb_id='' ) {

		if ( empty($kb_id) ) {
			$kb_id = ASEA_Utilities::get( 'asea_kb_id', ASEA_KB_Config_DB::DEFAULT_KB_ID );
		}

		if ( empty($search_keywords) ) {
			$search_keywords = ASEA_Search_Query_Extras::get_search_keywords( $kb_id, $user_input );
		}
		$search_keywords = implode(' ', $search_keywords);


		// limit the size
		$user_input = ASEA_Utilities::substr( $user_input, 0, 100);
		$source = ASEA_Utilities::substr( $source, 0, 50);

		// create the search log entry
		$user_id = ASEA_Utilities::get_current_user();
		$user_id = empty($user_id->ID) ? '' : $user_id->ID;
		$user_uid = ''; // use setcookie()
		$search_log[] = array( 'kb' => $kb_id, 'source' => $source, 'user_id' => $user_id, 'user_uid' => $user_uid, 'date' => date("Y-m-d H:i:s"), 'user_input' => $user_input, 'search_keywords' => $search_keywords, 'count' => $count );

		// TODO table limit

		// save the search log
		$db_handler = new ASEA_Search_DB();
		$db_handler->insert_search_record( $kb_id, $source, $user_id, $user_uid, $user_input, $search_keywords, $count );
	}
}