<?php

/**
 * Handle saving specific plugin configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMCR_KB_Config_Controller {

	public function __construct() {
		add_filter( AMCR_KB_Core::AMCR_KB_CONFIG_GET_ADD_ON_INPUT, array( $this, 'get_changed_input' ), 10, 3 );
		add_filter( AMCR_KB_Core::AMCR_KB_CONFIG_SAVE_INPUT, array( $this, 'save_kb_config_changes_in_db' ), 10, 4 );
	}

	/**
	 * Retrieve user input and fill up missing values with original values.
	 *
	 * @param $kb_id
	 * @param $all_add_on_configs
	 * @param $form_fields
	 * @return array|WP_Error
	 */
	public function get_changed_input( $all_add_on_configs, $kb_id, $form_fields ) {

		// retrieve new KB configuration
		$add_on_config = $this->get_new_kb_config( $form_fields, $kb_id );
		if ( is_wp_error( $add_on_config ) ) {
			return $add_on_config;
		}

		return array_merge($all_add_on_configs, $add_on_config);
	}

    /**
     * Save KB configuration for this add-on based on user input.
     *
     * @param $status
     * @param $kb_id
     * @param $form_fields
     * @param $main_page_layout
     * @return String|WP_Error
     */
	public function save_kb_config_changes_in_db( $status, $kb_id, $form_fields, $main_page_layout ) {

		// retrieve new KB configuration
		$add_on_config = $this->get_new_kb_config( $form_fields, $kb_id );
		if ( is_wp_error( $add_on_config ) ) {
			return $add_on_config;
		}

		// save LINK configuration
		$result = amcr_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $add_on_config );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

        // do not overwrite error from other add-ons
        return empty($status) ? '' : $status;
	}

	private function get_new_kb_config( $form_fields_sanitized, $kb_id ) {

		// retrieve current KB configuration
		$orig_config = amcr_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		if ( is_wp_error( $orig_config ) ) {
			return $orig_config;
		}

		$field_specs = AMCR_KB_Config_Specs::get_fields_specification();
		$input_filter = new AMCR_Input_Filter();

		// retrieve new values
		$new_kb_config = $input_filter->retrieve_and_sanitize_form_fields( $form_fields_sanitized, $field_specs, $orig_config );

		return $new_kb_config;
	}
}