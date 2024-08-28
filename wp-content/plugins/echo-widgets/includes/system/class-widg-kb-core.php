<?php

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class WIDG_KB_Core {

	const DEFAULT_KB_ID = 1;
	const WIDG_KB_CONFIG_PREFIX =  'epkb_config_';
	const WIDG_KB_DEBUG = 'epkb_debug';
	const WIDG_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const WIDG_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update

	// BASIC
	const WIDG_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';
	const WIDG_KB_LICENSE_FIELD = 'epkb_license_fields';
	const WIDG_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const WIDG_KB_CONFIGURATION_PAGE = 'epkb-kb-configuration';

	// FILTERS and ACTIONS
	const WIDG_KB_CONFIG_SAVE_INPUT_V3 = 'eckb_kb_config_save_input_v3';
	const WIDG_KB_ADD_ON_CONFIG_SPECS = 'epkb_add_on_config_specs';
	const WIDG_ALL_WIZARDS_GET_CURRENT_CONFIG = 'epkb_all_wizards_get_current_config';

	const WIDG_APPLY_WIZARD_CHANGES = 'epkb_apply_wizard_changes';


	/**
	 * Get value from KB Configuration
	 *
	 * @param string $kb_id
	 * @param $setting_name
	 * @param string $default
	 *
	 * @return array|string with value or $default value if this settings not found
	 */
	public static function get_value( $kb_id, $setting_name, $default = '' ) {
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
		if ( function_exists ('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * Get KB Configuration or default
	 *
	 * @param string $kb_id
	 * @return array|WP_Error
	 *
	 */
	public static function get_kb_config_or_default( $kb_id ) {
		if ( function_exists ('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * Get all KB Configuration
	 *
	 * @param boolean $skip_check
	 * @return array|string with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_configs( $skip_check=false ) {
		if ( function_exists ('epkb_get_instance' ) && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_configs( $skip_check );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	public static function is_user_access_to_context_allowed( $context ) {
		return WIDG_KB_Core::get_param_result( 'EPKB_Admin_UI_Access', 'is_user_access_to_context_allowed', $context, false );
	}
	public static function format_font_awesome_icon_name( $value ) {
		return self::get_param_result( 'EPKB_Icons', 'format_font_awesome_icon_name', array( $value ), '' );
	}

	public static function get_font_data() {
		return class_exists('EPKB_Typography') ? EPKB_Typography::$font_data : [];
	}

	public static function get_category_data_option( $kb_id ) {
		return self::get_param_result( 'EPKB_KB_Config_Category', 'get_category_data_option', array( $kb_id ), array() );
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
		if ( in_array($class_name, array('EPKB_KB_Config_DB')) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array($class, $method) ) ) {
			WIDG_Logging::add_log("Cannot invoke class $class with method $method.");
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
		if ( in_array($class_name, array('EPKB_KB_Config_DB', 'AMGR_Access_Articles_Front')) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array($class, $method ) ) ) {
			WIDG_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
