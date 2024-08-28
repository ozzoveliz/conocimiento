<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'ELAY_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'elay_utilities'                    =>  'includes/class-elay-utilities.php',
				'elay_core_utilities'  				=>  'includes/class-elay-core-utilities.php',
				'elay_input_filter'                 =>  'includes/class-elay-input-filter.php',
				'elay_html_forms'                   =>  'includes/class-elay-html-forms.php',

				// SYSTEM
				'elay_logging'                      =>  'includes/system/class-elay-logging.php',
				'elay_kb_core'                      =>  'includes/system/class-elay-kb-core.php',
				'elay_license_handler'              =>  'includes/system/class-elay-license-handler.php',
				'elay_upgrades'                     =>  'includes/system/class-elay-upgrades.php',
				'elay_typography'                   =>  'includes/system/class-elay-typography.php',

				// ADMIN CORE
				'elay_admin_notices'                =>  'includes/admin/class-elay-admin-notices.php',

				// PAGES
				'elay_add_ons_page'                 =>  'includes/admin/pages/class-elay-add-ons-page.php',
				'elay_settings_page'                =>  'includes/admin/pages/class-elay-settings-page.php',
				'elay_configuration_page'           =>  'includes/admin/pages/class-elay-configuration-page.php',

				// KB Configuration
				'elay_kb_config_controller'         =>  'includes/admin/kb-configuration/class-elay-kb-config-controller.php',
				'elay_kb_config_specs'              =>  'includes/admin/kb-configuration/class-elay-kb-config-specs.php',
				'elay_kb_config_db'                 =>  'includes/admin/kb-configuration/class-elay-kb-config-db.php',
				'elay_kb_config_layouts'            =>  'includes/admin/kb-configuration/class-elay-kb-config-layouts.php',
				'elay_kb_config_layout_sidebar'     =>  'includes/admin/kb-configuration/class-elay-kb-config-layout-sidebar.php',
				'elay_kb_config_layout_grid'        =>  'includes/admin/kb-configuration/class-elay-kb-config-layout-grid.php',
				'elay_kb_config_layout_modular'     =>  'includes/admin/kb-configuration/class-elay-kb-config-layout-modular.php',

				// EDITOR
				'elay_kb_editor_config'             =>  'includes/admin/editor/class-elay-kb-editor-config.php',
				'elay_kb_editor_article_page_config'=>  'includes/admin/editor/class-elay-kb-editor-article-page-config.php',
				'elay_kb_editor_main_page_config'   =>  'includes/admin/editor/class-elay-kb-editor-main-page-config.php',
				'elay_kb_editor_sidebar_config'     =>  'includes/admin/editor/class-elay-kb-editor-sidebar-config.php',

				// FEATURES - KB
				'elay_kb_handler'           =>  'includes/features/kbs/class-elay-kb-handler.php',

				// FEATURES - LAYOUT
				'elay_layout'               =>  'includes/features/layouts/class-elay-layout.php',
				'elay_layout_sidebar_v2'    =>  'includes/features/layouts/class-elay-layout-sidebar-v2.php',
				'elay_layout_grid'          =>  'includes/features/layouts/class-elay-layout-grid.php',

				// FEATURES - MODULAR MAIN PAGE
				'elay_modular_main_page'    =>  'includes/features/layouts/modules/class-elay-modular-main-page.php',
				'elay_ml_resource_links'    =>  'includes/features/layouts/modules/class-elay-ml-resource-links.php',
			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Elegant_Layouts::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
