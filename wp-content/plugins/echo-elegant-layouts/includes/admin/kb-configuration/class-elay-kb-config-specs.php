<?php

/**
 * Lists all KB configuration settings and adds filter to get configuration from add-ons.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_KB_Config_Specs {
	
	private static $cached_specs = array();

	/**
	 * Defines how KB configuration fields will be displayed, initialized and validated/sanitized
	 *
	 * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => false )
	 *
	 * @return array with KB config specification
	 */
	public static function get_fields_specification() {

		// retrieve settings if already cached
		if ( ! empty( self::$cached_specs ) && is_array( self::$cached_specs ) ) {
			return self::$cached_specs;
		}

		// get all configuration
		$config_specification = array(
			'elay_article_icon' => array(
				'label'       => esc_html__( 'Article List Icon', 'echo-knowledge-base' ),
				'name'        => 'elay_article_icon',
				'type'        => ELAY_Input_Filter::SELECTION,
				'options'     => array(
					'ep_font_icon_document' => _x( 'Document', 'icon type', 'echo-knowledge-base' ),
					'file-o' => _x( 'File', 'icon type', 'echo-knowledge-base' ),
					'file' => _x( 'File Inverted', 'icon type', 'echo-knowledge-base' ),
					'file-text-o' => _x( 'Text File', 'icon type', 'echo-knowledge-base' ),
					'file-text' => _x( 'Text File Inverted', 'icon type', 'echo-knowledge-base' ),
					'sticky-note' => _x( 'Note', 'icon type', 'echo-knowledge-base' ),
					'sticky-note-o' => _x( 'Note Inverted', 'icon type', 'echo-knowledge-base' ),
					'arrow-right' => _x( 'Arrow', 'icon type', 'echo-knowledge-base' ),
					'share' => _x( 'Arrow Curved', 'icon type', 'echo-knowledge-base' ),
					'ep_font_icon_right_arrow' => _x( 'Arrow Triangle', 'icon type', 'echo-knowledge-base' ),
					'arrow-circle-right' => _x( 'Arrow Circle', 'icon type', 'echo-knowledge-base' ),
					'circle' => _x( 'Circle', 'icon type', 'echo-knowledge-base' ),
				),
				'default'     => 'ep_font_icon_document'
			),
			'elay_sidebar_article_icon' => array(
				'label'       => esc_html__( 'Article List Icon', 'echo-knowledge-base' ),
				'name'        => 'elay_sidebar_article_icon',
				'type'        => ELAY_Input_Filter::SELECTION,
				'options'     => array(
					'ep_font_icon_document' => _x( 'Document', 'icon type', 'echo-knowledge-base' ),
					'arrow-right' => _x( 'Arrow', 'icon type', 'echo-knowledge-base' ),
					'share' => _x( 'Arrow Curved', 'icon type', 'echo-knowledge-base' ),
					'ep_font_icon_right_arrow' => _x( 'Arrow Triangle', 'icon type', 'echo-knowledge-base' ),
					'arrow-circle-right' => _x( 'Arrow Circle', 'icon type', 'echo-knowledge-base' ),
					'circle' => _x( 'Circle', 'icon type', 'echo-knowledge-base' ),
					'file-o' => _x( 'File', 'icon type', 'echo-knowledge-base' ),
					'file' => _x( 'File Inverted', 'icon type', 'echo-knowledge-base' ),
					'file-text-o' => _x( 'Text File', 'icon type', 'echo-knowledge-base' ),
					'file-text' => _x( 'Text File Inverted', 'icon type', 'echo-knowledge-base' ),
					'sticky-note' => _x( 'Note', 'icon type', 'echo-knowledge-base' ),
					'sticky-note-o' => _x( 'Note Inverted', 'icon type', 'echo-knowledge-base' ),
				),
				'default'     => 'ep_font_icon_document'
			),
		);
		$config_specification = array_merge( $config_specification, ELAY_KB_Config_Layout_Grid::get_fields_specification() );
		$config_specification = array_merge( $config_specification, ELAY_KB_Config_Layout_Sidebar::get_fields_specification() );
		$config_specification = array_merge( $config_specification, ELAY_KB_Config_Layout_Modular::get_fields_specification() );

		// all CORE settings are listed here; 'name' used for HTML elements
		$config_specification = array_merge( $config_specification, array(

			/******************************************************************************
			 *
			 *  Internal settings
			 *
			 ******************************************************************************/
			'elay_first_plugin_version' => array(
				'label'       => 'elay_first_plugin_version',
				'name'        => 'elay_first_plugin_version',
				'type'        => ELAY_Input_Filter::TEXT,
				'internal'    => true,
				'default'     => Echo_Elegant_Layouts::$version
			),
			'elay_upgrade_plugin_version' => array(      // will replace epkb_version
                'label'       => 'elay_upgrade_plugin_version',
                'name'        => 'elay_upgrade_plugin_version',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::TEXT,
                'internal'    => true,
                'default'     => Echo_Elegant_Layouts::$version,
			)
		) );

		self::$cached_specs = $config_specification;

		return self::$cached_specs;
	}

	/**
	 * Get KB default configuration
	 *
	 * @return array contains default values for KB configuration
	 */
	public static function get_default_kb_config() {
		$config_specs = self::get_fields_specification();

		$configuration = array();
		foreach( $config_specs as $key => $spec ) {
			$default = isset( $spec['default'] ) ? $spec['default'] : '';
			$configuration += array( $key => $default );
		}

		return $configuration;
	}

	/**
	 * Get names of all configuration items for KB configuration
	 * @return array
	 */
	public static function get_specs_item_names() {
		return array_keys( self::get_fields_specification() );
	}

	/**
	 * Return default values from given specification.
	 * @param $config_specs
	 * @return array
	 */
	public static function get_specs_defaults( $config_specs ) {
		$default_configuration = array();
		foreach( $config_specs as $key => $spec ) {
			$default = isset( $spec['default'] ) ? $spec['default'] : '';
			$default_configuration += array( $key => $default );
		}
		return $default_configuration;
	}
}
