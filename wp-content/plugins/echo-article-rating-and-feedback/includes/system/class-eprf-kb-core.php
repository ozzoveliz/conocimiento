<?php

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class EPRF_KB_Core {

	const DEFAULT_KB_ID = 1;
	const EPRF_KB_CONFIG_PREFIX =  'epkb_config_';
	const EPRF_KB_DEBUG = 'epkb_debug';
	const EPRF_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const EPRF_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update

	// BASIC
	const EPRF_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';
	const EPRF_KB_LICENSE_FIELD = 'epkb_license_fields';
	const EPRF_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const EPRF_KB_CONFIGURATION_PAGE = 'epkb-kb-configuration'; // TODO check if we need it, looks like it is from wizard preview. Check all uses of this constant and related script file
	const EPRF_KB_ANALYTICS_PAGE = 'epkb-plugin-analytics';

	// ACTIONS and FILTERS
	const EPRF_KB_CONFIG_SAVE_INPUT_V3 = 'eckb_kb_config_save_input_v3';
	const EPRF_KB_ADD_ON_CONFIG_SPECS = 'epkb_add_on_config_specs';
	const EPRF_ALL_WIZARDS_GET_CURRENT_CONFIG = 'epkb_all_wizards_get_current_config';
	const EPRF_APPLY_WIZARD_CHANGES = 'epkb_apply_wizard_changes';
	const EPRF_UPDATE_KB_WIZARD_ORDER_VIEW = 'epkb_wizard_update_order_view';
	const EPRF_UPDATE_KB_EDITOR = 'eckb_apply_editor_changes';

	/**
	 * Get value from KB Configuration
	 *
	 * @param string $kb_id
	 * @param $setting_name
	 * @param string $default
	 *
	 * @return string|array with value or $default value if this settings not found
	 */
	public static function get_value( $kb_id, $setting_name, $default='' ) {
		if ( function_exists ('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_value( $kb_id, $setting_name, $default );
		}
		return $default;
	}

	/**
	 * Get KB Configuration
	 *
	 * @param string $kb_id
	 * @return array|WP_Error with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_config( $kb_id ) {
		if ( function_exists( 'epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * Get KB Configuration
	 *
	 * @return array|WP_Error with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_ids() {
		if ( function_exists('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_ids();
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * Get KB Configuration or default
	 *
	 * @param string $kb_id
	 * @return array|WP_Error with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_config_or_default( $kb_id ) {
		if ( function_exists('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * Get all KB Configuration
	 *
	 * @param boolean $skip_check
	 * @return array|WP_Error with value or $default value if this settings not found
	 */
	public static function get_kb_configs( $skip_check=false ) {
		if ( function_exists('epkb_get_instance') && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_configs( $skip_check );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	public static function get_current_kb_id() {
		return self::get_result( 'EPKB_KB_Handler', 'get_current_kb_id', EPRF_KB_Config_DB::DEFAULT_KB_ID );
	}

	public static function is_user_access_to_context_allowed( $context ) {
		return self::get_param_result( 'EPKB_Admin_UI_Access', 'is_user_access_to_context_allowed', array( $context ), false );
	}

	public static function get_font_data() {
		return class_exists( 'EPKB_Typography' ) ? EPKB_Typography::$font_data : [];
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

		$class = $class_name;
		if ( in_array( $class_name, array( 'EPKB_KB_Config_DB' ) ) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array( $class, $method ) ) ) {
			EPRF_Logging::add_log( "Cannot invoke class $class_name with method $method." );
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

		$class = $class_name;
		if ( in_array( $class_name, array( 'AMGR_Access_Articles_Front', 'EPKB_Admin_UI_Access' ) ) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array( $class, $method ) ) ) {
			EPRF_Logging::add_log( "Cannot invoke class $class_name with method $method." );
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
