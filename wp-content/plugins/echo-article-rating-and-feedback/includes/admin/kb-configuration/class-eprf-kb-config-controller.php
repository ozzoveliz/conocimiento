<?php

/**
 * Handle saving specific plugin configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPRF_KB_Config_Controller {

	public function __construct() {
		add_filter( EPRF_KB_Core::EPRF_KB_ADD_ON_CONFIG_SPECS, array( $this, 'get_add_on_config_specs' ), 10, 1 );
		add_filter( EPRF_KB_Core::EPRF_ALL_WIZARDS_GET_CURRENT_CONFIG, array( $this, 'get_current_config' ), 10, 2 );
		add_filter( EPRF_KB_CORE::EPRF_KB_CONFIG_SAVE_INPUT_V3, array( $this, 'save_new_kb_config_changes' ), 10, 4 );
	}

	/**
	 * Retrieve user input and fill up missing values with original values.
	 * @param $all_add_on_specs
	 * @return array|WP_Error
	 */
	public function get_add_on_config_specs( $all_add_on_specs ) {

		// retrieve new KB configuration
		$add_on_specs = EPRF_KB_Config_Specs::get_fields_specification();

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
		$eprf_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $eprf_config );
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

		$result = eprf_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $new_config );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// do not overwrite error from other add-ons
		return empty($status) ? '' : $status;
	}
}