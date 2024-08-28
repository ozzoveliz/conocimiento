<?php

/**
 * Handle saving specific plugin configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_KB_Config_Controller {

	const MAX_RESOURCES = 8;
	const RESOURCE_LINKS_ICON = 'elay_resource_links_icon_images';

	public function __construct() {
		add_filter( ELAY_KB_Core::ELAY_KB_ADD_ON_CONFIG_SPECS, array( $this, 'get_add_on_config_specs' ), 10, 1 );
		add_filter( ELAY_KB_Core::ELAY_ALL_WIZARDS_GET_CURRENT_CONFIG, array( $this, 'get_current_config' ), 10, 2 );
		add_filter( ELAY_KB_Core::ELAY_KB_CONFIG_SAVE_INPUT_V3, array( $this, 'save_new_kb_config_changes' ), 10, 4 );
	}

	/**
	 * Retrieve user input and fill up missing values with original values.
	 * @param $all_add_on_specs
	 * @return array|WP_Error
	 */
	public function get_add_on_config_specs( $all_add_on_specs ) {

		// retrieve new KB configuration
		$add_on_specs = ELAY_KB_Config_Specs::get_fields_specification();

		return array_merge( $all_add_on_specs, $add_on_specs );
	}

	/**
	 * Returnt to Wizard the current KB configuration
	 *
	 * @param $kb_config
	 * @param $kb_id
	 * @return array
	 */
	public static function get_current_config( $kb_config, $kb_id ) {
		$elay_config = elay_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $elay_config );
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

		$result = elay_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $new_config );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// save resource links icon image data
		$image_data_result = $this->update_resource_links_icon_image_data( $kb_id, $new_config );
		if ( is_wp_error( $image_data_result ) ) {
			return $image_data_result;
		}

		// do not overwrite error from other add-ons
		return empty( $status ) ? '' : $status;
	}

	/**
	 * Update resource links icon image data
	 *
	 * @param $kb_id
	 * @param $config
	 * @return array|WP_Error
	 */
	private function update_resource_links_icon_image_data( $kb_id, $config ) {

		if ( ! isset( $config['ml_resource_links_icon_type'] ) ) {
			return;
		}

		$icon_type = $config['ml_resource_links_icon_type'];
		$icon_color = $config['ml_resource_links_icon_color'];
		$icon_image_size = $config['ml_resource_links_icon_image_size'];

		$resource_links_icon_data = array();
		for ( $resource_number = 1; $resource_number <= self::MAX_RESOURCES; $resource_number++ ) {

			$icon_image_url = '';
			$icon_image_alt = '';
			$icon_image_title = '';

			$icon_name = $config['ml_resource_links_' . $resource_number . '_icon_font'];
			$icon_image_id = $config['ml_resource_links_' . $resource_number . '_icon_image'];

			if ( ! empty( $icon_image_id ) ) {
				$icon_image_url = wp_get_attachment_image_url( $icon_image_id, $icon_image_size );
				$icon_image_alt = get_post_meta( $icon_image_id, '_wp_attachment_image_alt', TRUE );
				$icon_image_alt = empty( $icon_image_alt ) ? '' : $icon_image_alt;
				$icon_image_title = get_the_title( $icon_image_id );
			}

			$resource_links_icon_data[$resource_number] = array(
				'type'                => $icon_type,
				'name'                => $icon_name,
				'image_id'            => $icon_image_id,
				'image_size'          => $icon_image_size,
				'image_alt'           => $icon_image_alt,
				'image_title'         => $icon_image_title,
				'image_thumbnail_url' => $icon_image_url,
				'color'               => $icon_color,
			);
		}

		return ELAY_Utilities::save_kb_option( $kb_id, self::RESOURCE_LINKS_ICON, $resource_links_icon_data );
	}
}