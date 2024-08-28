<?php

/**
 * Handle saving specific plugin configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ASEA_KB_Config_Controller {

	public function __construct() {
		add_filter( ASEA_KB_Core::ASEA_KB_ADD_ON_CONFIG_SPECS, array( $this, 'get_add_on_config_specs' ), 10, 1 );
		add_filter( ASEA_KB_Core::ASEA_ALL_WIZARDS_GET_CURRENT_CONFIG, array( $this, 'get_current_config' ), 10, 2 );
		add_filter( ASEA_KB_Core::ASEA_KB_CONFIG_SAVE_INPUT_V3, array( $this, 'save_new_kb_config_changes' ), 10, 4 );
		add_filter( ASEA_KB_Core::ASEA_SEARCH_QUERY_PARAM, array( $this, 'filter_search_query_param' ), 10, 2 );

		add_action( 'wp_ajax_eckb_update_query_parameter', array( $this, 'update_search_query_param' ) );
		add_action( 'wp_ajax_nopriv_eckb_update_query_parameter', array( 'ASEA_Utilities', 'user_not_logged_in' ) );
	}

	/**
	 * Retrieve user input and fill up missing values with original values.
	 * @param $all_add_on_specs
	 * @return array|WP_Error
	 */
	public function get_add_on_config_specs( $all_add_on_specs ) {

		// retrieve new KB configuration
		$add_on_specs = ASEA_KB_Config_Specs::get_fields_specification();

		return array_merge($all_add_on_specs, $add_on_specs);
	}

	/**
	 * Returnt to Wizard the current KB configuration
	 *
	 * @param $kb_config
	 * @param $kb_id
	 * @return array
	 */
	public static function get_current_config( $kb_config, $kb_id ) {
		$asea_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $asea_config );
	}

	/**
	 * Save new KB configuration for this add-on based on user input.
	 *
	 * @param $status
	 * @param $kb_id
	 * @param $new_config
	 * @return String|WP_Error
	 */
	public function save_new_kb_config_changes( $status, $kb_id, $new_config ) {

		$result = asea_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $new_config );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// do not overwrite error from other add-ons
		return empty( $status ) ? '' : $status;
	}

	/**
	 * Filter search page parameter
	 * @param $search_query_param
	 * @param $kb_id
	 * @return string
	 */
	public static function filter_search_query_param( $search_query_param, $kb_id ) {
		return ASEA_Core_Utilities::get_search_query_param( $kb_id );
	}

	/**
	 * Triggered when user clicks to update search query parameter.
	 */
	function update_search_query_param() {

		// wp_die if nonce invalid or user does not have admin permission
		ASEA_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die( '_wpnonce_epkb_ajax_action' );

		// get KB ID
		$kb_id = (int)ASEA_Utilities::post( 'epkb_kb_id', 0 );
		if ( ! ASEA_Utilities::is_positive_int( $kb_id ) ) {
			ASEA_Utilities::ajax_show_error_die( ASEA_Utilities::report_generic_error( 410 ) );
		}

		$search_query_param = ASEA_Utilities::post( 'search_query_param' );

		// allow only letters, numbers, dash, underscore
		$search_query_param = preg_replace( '/[^a-zA-Z0-9-_]/', '', $search_query_param );

		$result = asea_get_instance()->kb_config_obj->set_value( $kb_id, 'search_query_param', $search_query_param );
		if ( is_wp_error( $result ) ) {
			ASEA_Utilities::ajax_show_error_die( ASEA_Utilities::report_generic_error( 412, $result ) );
		}

		ASEA_Utilities::ajax_show_info_die( esc_html__( 'Configuration saved', 'echo-advanced-search' ) );
	}
}