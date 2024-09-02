<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'AMCR_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMCR_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'amcr_utilities'                    =>  'includes/class-amcr-utilities.php',
				'amcr_html_elements'                =>  'includes/class-amcr-html-elements.php',
				'amcr_input_filter'                 =>  'includes/class-amcr-input-filter.php',

				// SYSTEM
				'amcr_logging'                      =>  'includes/system/class-amcr-logging.php',
				'amcr_kb_core'                      =>  'includes/system/class-amcr-kb-core.php',
				'amcr_license_handler'              =>  'includes/system/class-amcr-license-handler.php',
				'amcr_upgrades'                     =>  'includes/system/class-amcr-upgrades.php',

				// ADMIN CORE
				'amcr_admin_notices'                =>  'includes/admin/class-amcr-admin-notices.php',
				'amcr_settings_page'                =>  'includes/admin/settings/class-amcr-settings-page.php',

				// ADMIN PLUGIN MENU PAGES

				// KB Configuration
				'amcr_kb_config_controller'         =>  'includes/admin/kb-configuration/class-amcr-kb-config-controller.php',
				'amcr_kb_config_specs'              =>  'includes/admin/kb-configuration/class-amcr-kb-config-specs.php',
				'amcr_kb_config_db'                 =>  'includes/admin/kb-configuration/class-amcr-kb-config-db.php',
				'amcr_add_ons_page'                 =>  'includes/admin/add-ons/class-amcr-add-ons-page.php',

				// FEATURES 
				'amcr_kb_handler'                   =>  'includes/features/kbs/class-amcr-kb-handler.php',

				// controllers
				'amcr_access_page_controller'       =>  'includes/features/configuration/class-amcr-access-page-controller.php',
				'amcr_access_page_cntrl_wp_roles'   =>  'includes/features/configuration/class-amcr-access-page-cntrl-wp-roles.php',

				// views
				'amcr_access_page_view_wp_roles'    =>  'includes/features/configuration/class-amcr-access-page-view-wp-roles.php',

			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Custom_roles::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
