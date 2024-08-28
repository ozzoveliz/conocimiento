<?php

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class KBLK_KB_Core {

	const DEFAULT_KB_ID = 1;
	const KBLK_KB_CONFIG_PREFIX =  'epkb_config_';
	const KBLK_KB_DEBUG = 'epkb_debug';
	const KBLK_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const KBLK_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update
	const KBLK_ARTICLES_SEQUENCE = 'epkb_articles_sequence';

	// BASIC
	const KBLK_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';
	const KBLK_KB_LICENSE_FIELD = 'epkb_license_fields';
	const KBLK_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const KBLK_KB_CONFIGURATION_PAGE = 'epkb-kb-configuration';

	// ACTIONS and FILTERS
	const KBLK_KB_CONFIG_SAVE_INPUT_V3 = 'eckb_kb_config_save_input_v3';


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
	 * @return array|string with value or $default value if this settings not found
	 *
	 */
	public static function get_kb_configs( $skip_check=false ) {
		if ( function_exists('epkb_get_instance') && isset(epkb_get_instance()->kb_config_obj) ) {
			return epkb_get_instance()->kb_config_obj->get_kb_configs( $skip_check );
		}
		return new WP_Error('DB200', 'Failed to retrieve KB Configuration');
	}
	
	public static function get_epkbfa_all_icons() {
		return self::get_result( 'EPKB_Icons', 'get_epkbfa_all_icons', array() );
	}

	public static function format_font_awesome_icon_name( $value ) {
		return self::get_param_result( 'EPKB_Icons', 'format_font_awesome_icon_name', array( $value ), '' );
	}

	public static function apply_category_language_filter( $category_seq_data ) {
		return self::get_param_result( 'EPKB_WPML', 'apply_category_language_filter', array( $category_seq_data ), '' );
	}

	public static function apply_article_language_filter( $value ) {
		return self::get_param_result( 'EPKB_WPML', 'apply_article_language_filter', array( $value ), '' );
	}

	public static function get_category_icon( $box_category_id, $categories_icons ) {
		return self::get_param_result( 'EPKB_KB_Config_Category', 'get_category_icon', array( $box_category_id, $categories_icons ), array() );
	}

	/**
	 * @param $kb_id
	 * @param array $config
	 * @return array|WP_Error configuration that was updated
	 */
	public static function update_kb_configuration( $kb_id, array $config ) {
		return self::get_param_result( 'EPKB_KB_Config_DB', 'update_kb_configuration', array($kb_id, $config), new WP_Error("Internal Error (x3)") );
	}

	public static function get_refreshed_kb_categories( $kb_id, $category_seq_data ) {
		return self::get_param_result( 'EPKB_KB_Handler', 'get_refreshed_kb_categories', array( $kb_id, $category_seq_data ), null );
	}

	public static function get_category_data_option( $kb_id ) {
		return self::get_param_result( 'EPKB_KB_Config_Category', 'get_category_data_option', array( $kb_id ), array() );
	}

	public static function is_user_access_to_context_allowed( $context ) {
		return self::get_param_result( 'EPKB_Admin_UI_Access', 'is_user_access_to_context_allowed', array( $context ), false );
	}

	/**
	 * Remove KB Articles that the current user does not have access to.
	 * @param $value
	 * @return mixed
	 */
	public static function found_posts( $value ) {
		return self::get_param_result( 'AMGR_Access_Articles_Front', 'found_posts', array( $value ), false );
	}

	public static function get_font_data() {
		return class_exists('EPKB_Typography') ? EPKB_Typography::$font_data : [];
	}

	/**
	 * Get categories that the current user has access to.
	 * @param $terms
	 * @return mixed
	 */
	public static function filter_user_categories( $terms ) {
		return self::get_param_result( 'AMGR_Access_Utilities', 'filter_user_categories', array( $terms ), array() );
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
			KBLK_Logging::add_log("Cannot invoke class $class with method $method.");
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

		if ( ! is_callable( array( $class, $method ) ) ) {
			KBLK_Logging::add_log("Cannot invoke class $class with method $method.");
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
