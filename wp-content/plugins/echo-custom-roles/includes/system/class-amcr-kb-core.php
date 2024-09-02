<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class AMCR_KB_Core {

	const DEFAULT_KB_ID = 1;
	const AMCR_KB_CONFIG_PREFIX =  'epkb_config_';
	const AMCR_KB_DEBUG = 'epkb_debug';
	const AMCR_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const AMCR_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update

	// plugin pages links
	const AMCR_KB_CONFIGURATION_URL = 'edit.php?post_type=epkb_post_type_1&page=epkb-kb-configuration';
	const AMCR_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const AMCR_KB_LICENSE_FIELD = 'epkb_license_fields';

	// FILTERS
	const AMCR_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';

	// ACTIONS
    const AMCR_KB_CONFIG_GET_ADD_ON_INPUT           = 'epkb_kb_config_get_add_on_input';
    const AMCR_KB_CONFIG_SAVE_INPUT                 = 'epkb_kb_config_save_input_v2';


	public static function get_kb_groups( $kb_id ) {
		return self::get_param_result( 'AMGR_WP_Roles', 'get_kb_groups', array( $kb_id ), array() );
	}

	public static function update_wp_role_mapping( $kb_id, $wp_role_name, $kb_role_name, $kb_group_id ) {
		return self::get_param_result( 'AMGR_WP_Roles', 'update_wp_role_mapping', array($kb_id, $wp_role_name, $kb_role_name, $kb_group_id), false );
	}

	public static function delete_wp_role_mapping( $kb_id, $wp_role_name, $kb_group_id ) {
		return self::get_param_result( 'AMGR_WP_Roles', 'delete_wp_role_mapping', array($kb_id, $wp_role_name, $kb_group_id), false );
	}

	public static function get_wp_roles_mappings_for_kb( $kb_id ) {
		return self::get_param_result( 'AMGR_WP_Roles', 'get_wp_roles_mappings_for_kb', array($kb_id), new WP_Error('Error', 'Failed to retrieve Mappings.') );
	}

	public static function use_kb_groups() {
		return self::get_result( 'AMGR_WP_Roles', 'use_kb_groups', null );
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
			AMCR_Logging::add_log("Cannot invoke class $class with method $method.");
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
			AMCR_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
