<?php

/**
 * Groups KB CORE code
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 *
 */
class ELAY_KB_Core {

	const DEFAULT_KB_ID = 1;
	const ELAY_KB_CONFIG_PREFIX =  'epkb_config_';
	const ELAY_KB_DEBUG = 'epkb_debug';
	const ELAY_KB_KNOWLEDGE_BASE = 'Echo_Knowledge_Base';
	const ELAY_KB_POST_TYPE_PREFIX = 'epkb_post_type_';  // changing this requires db update
	const ELAY_ARTICLES_SEQUENCE = 'epkb_articles_sequence';
	const ELAY_CATEGORIES_SEQUENCE = 'epkb_categories_sequence';

	// BASIC
	const ELAY_KB_ADD_ON_LICENSE_MSG = 'epkb_add_on_license_message';
	const ELAY_KB_LICENSE_FIELD = 'epkb_license_fields';
	const ELAY_KB_ADD_ONS_PAGE = 'epkb-add-ons';
	const ELAY_KB_CONFIGURATION_PAGE = 'epkb-kb-configuration';

	// ACTIONS and FILTERS
	const ELAY_KB_CONFIG_SAVE_INPUT_V3 = 'eckb_kb_config_save_input_v3';
	const ELAY_KB_ADD_ON_CONFIG_SPECS = 'epkb_add_on_config_specs';
	const ELAY_ALL_WIZARDS_GET_CURRENT_CONFIG = 'epkb_all_wizards_get_current_config';
	const ELAY_APPLY_WIZARD_CHANGES = 'epkb_apply_wizard_changes';
	const ELAY_UPDATE_KB_WIZARD_ORDER_VIEW = 'epkb_wizard_update_order_view';
	const ELAY_UPDATE_KB_EDITOR = 'eckb_apply_editor_changes';

	// ELAY FILTERS
	const ELAY_KB_GRID_LAYOUT_OUTPUT = 'epkb_grid_layout_output';
	const ELAY_KB_GRID_DISPLAY_CATEGORIES_AND_ARTICLES = 'grid_display_categories_and_articles';
	const ELAY_KB_SIDEBAR_DISPLAY_CATEGORIES_AND_ARTICLES = 'sidebar_display_categories_and_articles';
	const ELAY_SAVE_SIDEBAR_INTRO_TEXT = 'elay_save_sidebar_intro_text';
	const ELAY_CONFIG_SIDEBAR_INTRO_SETTINGS = 'epkb_config_page_sidebar_intro_settings';
	const ELAY_APPLY_SETUP_WIZARD_CHANGES = 'epkb_apply_setup_wizard_changes';

	// KB ADMIN UI ACCESS CONTEXTS
	const ELAY_KB_ACCESS_FRONTEND_EDITOR_WRITE = 'admin_eckb_access_frontend_editor_write';

	// ELAY MODULES for KB MODULAR MAIN PAGE
	const ELAY_KB_ML_RESOURCE_LINKS_MODULE = 'epkb_ml_resource_links_module';
	const ELAY_KB_ML_RESOURCE_LINKS_MODULE_STYLES = 'epkb_ml_resource_links_module_styles';
	const ELAY_KB_ML_GRID_LAYOUT_STYLES = 'epkb_ml_grid_layout_styles';
	const ELAY_KB_ML_SIDEBAR_LAYOUT_STYLES = 'epkb_ml_sidebar_layout_styles';

	/**
	 * Get value from KB Configuration
	 *
	 * @param string $kb_id
	 * @param $setting_name
	 * @param string $default
	 *
	 * @return string|array with value or $default value if these settings not found
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
		return [self::DEFAULT_KB_ID];
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

	/**
	 * @param $kb_id
	 * @param array $config
	 * @return array|WP_Error configuration that was updated
	 */
	public static function update_kb_configuration( $kb_id, array $config ) {
		return self::get_param_result( 'EPKB_KB_Config_DB', 'update_kb_configuration', array($kb_id, $config), new WP_Error("Internal Error (x3)") );
	}

	public static function get_or_update_new_category_icons($kb_config, $theme_name ) {
		return self::get_param_result( 'EPKB_Core_Utilities', 'get_or_update_new_category_icons', array( $kb_config, $theme_name ), '' );
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

	public static function get_search_form( $kb_config ) {
		self::get_param_result( 'EPKB_KB_Search', 'get_search_form', array( $kb_config ), '' );
	}

	public static function submit_button_v2( $button_label, $action, $main_class='', $html='', $unique_button=true, $return_html=false, $inputClass='' ) {
		return self::get_param_result( 'EPKB_HTML_Elements', 'submit_button_v2', array( $button_label, $action, $main_class, $html, $unique_button, $return_html, $inputClass ), '' );
	}

	public static function get_refreshed_kb_categories( $kb_id, $category_seq_data ) {
		return self::get_param_result( 'EPKB_KB_Handler', 'get_refreshed_kb_categories', array( $kb_id, $category_seq_data ), null );
	}

	public static function get_category_data_option( $kb_id ) {

		// Bypass for old KB. TODO: remove this after versions
		if ( ! method_exists( 'EPKB_KB_Config_Category', 'get_category_data_option' ) ) {
			return self::get_param_result( 'EPKB_KB_Config_Category', 'get_category_icons_option', array( $kb_id ), array() );
		}

		return self::get_param_result( 'EPKB_KB_Config_Category', 'get_category_data_option', array( $kb_id ), array() );
	}

	public static function is_user_access_to_context_allowed( $context ) {
		return self::get_param_result( 'EPKB_Admin_UI_Access', 'is_user_access_to_context_allowed', array( $context ), false );
	}

	public static function get_author_capability() {
		return self::get_result( 'EPKB_Admin_UI_Access', 'get_author_capability', 'publish_posts' );
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
		if ( in_array( $class_name, array( 'EPKB_KB_Config_DB', 'EPKB_Admin_UI_Access' ) ) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array( $class, $method ) ) ) {
			ELAY_Logging::add_log( "Cannot invoke class $class_name with method $method." );
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
		if ( in_array( $class_name, array( 'EPKB_KB_Config_DB', 'EPKB_Admin_UI_Access' ) ) ) {
			$class = new $class_name();
		}

		if ( ! is_callable( array( $class, $method ) ) ) {
			ELAY_Logging::add_log( "Cannot invoke class $class_name with method $method." );
			return $default;
		}

		return call_user_func_array( array( $class, $method ), $params );
	}
}
