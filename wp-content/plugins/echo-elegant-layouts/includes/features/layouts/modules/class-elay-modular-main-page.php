<?php

/**
 *  Outputs the ELAY modules for KB Core Modular Main Page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_Modular_Main_Page {

	public function __construct() {

		// Module: Resource Links
		add_action( ELAY_KB_Core::ELAY_KB_ML_RESOURCE_LINKS_MODULE, array( 'ELAY_Modular_Main_Page', 'display_resource_links_module' ), 10, 1 );
        add_filter( ELAY_KB_Core::ELAY_KB_ML_RESOURCE_LINKS_MODULE_STYLES, array( 'ELAY_ML_Resource_Links', 'get_inline_styles' ), 10, 2 );
		add_filter( ELAY_KB_Core::ELAY_KB_ML_GRID_LAYOUT_STYLES, array( 'ELAY_Layout_Grid', 'get_inline_styles' ), 10, 2 );
		add_filter( ELAY_KB_Core::ELAY_KB_ML_SIDEBAR_LAYOUT_STYLES, array( 'ELAY_Layout_Sidebar_v2', 'get_inline_styles' ), 10, 2 );
	}

	/**
	 * Display Resource Links Module
	 *
	 * @param $kb_config
	 */
	public static function display_resource_links_module( $kb_config ) {

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );     ?>

		<div id="elay-ml__module-resource-links" class="elay-ml__module elay-ml__module-resource-layout--<?php echo esc_attr( $kb_config['ml_resource_links_columns'] ); ?>">
			<div id="elay-ml-resource-links-<?php echo strtolower( $kb_config['kb_main_page_layout'] ); ?>-layout" class="elay-ml-resource-links-container">
				<?php ELAY_ML_Resource_Links::display_resource_links( $kb_config );   ?>
			</div>
		</div>    <?php
	}
}
