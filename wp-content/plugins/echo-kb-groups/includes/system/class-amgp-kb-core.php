<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class AMGP_KB_Core {

	const DEFAULT_KB_ID = 1;
	const AMGP_KB_CONFIG_PREFIX =  'epkb_config_';
	const AMGP_KB_DEBUG = 'epkb_debug';
	const AMGP_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const AMGP_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update
	const AMGP_KB_CAPABILITIES_SUFFIX = '_cpt';
	const AMGP_KB_ARTICLES_SEQ_META = 'epkb_articles_sequence';
	const AMGP_KB_CATEGORIES_SEQ_META = 'epkb_categories_sequence';

	// plugin pages links
	const AMGP_KB_CONFIGURATION_URL = 'edit.php?post_type=epkb_post_type_1&page=epkb-kb-configuration';
	const AMGP_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const AMGP_KB_LICENSE_FIELD = 'epkb_license_fields';

	// FILTERS
	const AMGP_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';

	// ACTIONS
    const AMGP_KB_CONFIG_GET_ADD_ON_INPUT           = 'epkb_kb_config_get_add_on_input';
    const AMGP_KB_CONFIG_SAVE_INPUT                 = 'epkb_kb_config_save_input_v2';

    // other
	const AMGP_ARTICLE_READ = 'AMGR_ARTICLE_READ';
	const AMGP_ARTICLE_EDIT = 'AMGR_ARTICLE_EDIT';
	const AMGP_ARTICLE_CREATE = 'AMGR_ARTICLE_CREATE';
	const AMGP_ARTICLE_DELETE = 'AMGR_ARTICLE_DELETE';
	const ALLOWED = 'access_allowed';
	const NEXT = 'access_next';
	const DENIED = 'access_denied';

	// AMGR Tables
	const DB_ACCESS_KB_CATEGORIES = 'amgr_access_kb_categories';
	const DB_ACCESS_READ_ARTICLES = 'amgr_access_read_articles';
	const DB_ACCESS_READ_CATEGORIES =  'amgr_access_read_categories';
	const DB_KB_GROUP_USERS = 'amgr_kb_group_users';
	const DB_KB_GROUPS = 'amgr_kb_groups';
	const DB_KB_PUBLIC_GROUPS = 'amgr_kb_public_groups';
	const AM_GR = 'amgr';

	/**
	 * Return KB Categories.
	 * @param $categories_seq_data
	 * @return array|EPKB_Categories_Array
	 */
	public static function AMGP_Categories_Array( $categories_seq_data ) {
		return class_exists('EPKB_Categories_Array') ? new EPKB_Categories_Array( $categories_seq_data ) : array();
	}

	/**
	 * Setup authorized groups.
	 * @param bool $is_get_authorized_groups
	 * @return AMGR_Access_Article|array
	 */
	public static function AMGP_Access_Article( $is_get_authorized_groups=false ) {
		return class_exists('AMGR_Access_Article') ? new AMGR_Access_Article( $is_get_authorized_groups ) : array();
	}

	/**
	 * Setup authorized groups.
	 * @return AMGR_Access_Category|array
	 */
	public static function AMGP_Access_Category() {
		return class_exists('AMGR_Access_Article') ? new AMGR_Access_Category() : array();
	}

	public static function get_current_kb_id() {
		return self::get_result( 'EPKB_KB_Handler', 'get_current_kb_id', '' );
	}

	public static function get_am_gr_version() {
		return Echo_Knowledge_Base::$amag_version;
	}

	public static function delete_all_group_mappings( $kb_id, $kb_group_id ) {
		return self::get_param_result( 'AMGR_WP_Roles', 'delete_all_group_mappings', array($kb_id, $kb_group_id), false );
	}


	/**********************************************************************************************************
	 *
	 *                                       CORE CALLING FUNCTIONS
	 *
	 **********************************************************************************************************/

	/**
	 * Safely invoke function.
	 *
	 * @param $class_name
	 * @param $method
	 * @param $default
	 * @return mixed
	 */
	private static function get_result( $class_name, $method, $default ) {

		// instantiate certain classes
		$class = $class_name;
		if ( in_array($class_name, array('EPKB_KB_Config_Elements', 'EPKB_HTML_Elements', 'EPKB_KB_Config_DB', 'EPKB_Input_Filter')) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array($class, $method) ) ) {
			AMGP_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func( array( $class, $method ) );
	}

	/**
	 * Safely invoke function with parameters.
	 *
	 * @param $class_name
	 * @param $method
	 * @param $params
	 * @param $default
	 * @return mixed
	 */
	private static function get_param_result( $class_name, $method, $params, $default ) {

		// instantiate certain classes
		$class = $class_name;
		if ( in_array($class_name, array('EPKB_KB_Config_Elements', 'EPKB_HTML_Elements', 'EPKB_KB_Config_DB', 'EPKB_Input_Filter')) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array($class, $method ) ) ) {
			AMGP_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
