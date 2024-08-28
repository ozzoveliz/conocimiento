<?php

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class EMKB_KB_Core {

	const DEFAULT_KB_ID = 1;
	const EMKB_KB_CONFIG_PREFIX =  'epkb_config_';
	const EMKB_KB_DEBUG = 'epkb_debug';
	const EMKB_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const EMKB_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update


	// BASIC
	const EMKB_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';
	const EMKB_KB_LICENSE_FIELD = 'epkb_license_fields';
	const EMKB_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const EMKB_KB_CONFIGURATION_PAGE = 'epkb-kb-configuration';

	// EMKB FILTERS
    const EMKB_LOAD_ADMIN_PLUGIN_PAGES_RESOURCES = 'epkb_load_admin_plugin_pages_resources';
	const EMKB_KB_FLUSH_REWRITE_RULES = 'epkb_flush_rewrite_rules';

	// KB states
	const PUBLISHED = 'published';
	const ARCHIVED = 'archived';

	// name of KB shortcode
	const KB_MAIN_PAGE_SHORTCODE_NAME = 'epkb-knowledge-base'; // changing this requires db update
	
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
		return array(self::DEFAULT_KB_ID);
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
	 * @return array|string with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_configs( $skip_check=false ) {
		if ( function_exists('epkb_get_instance') && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_configs( $skip_check );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}

	/**
	 * @param $kb_id
	 * @param array $config
	 * @return array|WP_Error configuration that was updated
	 */
	public static function update_kb_configuration( $kb_id, array $config ) {
		return self::get_param_result( 'EPKB_KB_Config_DB', 'update_kb_configuration', array($kb_id, $config), new WP_Error("Internal Error (x3)") );
	}

	public static function is_user_access_to_context_allowed( $context ) {
		return EMKB_KB_Core::get_param_result( 'EPKB_Admin_UI_Access', 'is_user_access_to_context_allowed', $context, false );
	}

	public static function add_new_knowledge_base( $new_kb_id, $new_kb_main_page_title, $new_kb_main_page_slug ) {
		return self::get_param_result( 'EPKB_KB_Handler', 'add_new_knowledge_base', array($new_kb_id, $new_kb_main_page_title, $new_kb_main_page_slug), new WP_Error("Internal Error (xy)") );
	}

	/**
	 * Reset KB Configuration cache
	 */
	public static function reset_cache() {
		if ( function_exists('epkb_get_instance' ) && isset( epkb_get_instance()->kb_config_obj) ) {
			epkb_get_instance()->kb_config_obj->reset_cache();
		}
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
			EMKB_Logging::add_log("Cannot invoke class $class with method $method.");
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
		if ( in_array($class_name, array('EPKB_KB_Config_DB')) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array($class, $method ) ) ) {
			EMKB_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
