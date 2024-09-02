<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'AMGP_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGP_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'amgp_utilities'                    =>  'includes/class-amgp-utilities.php',
				'amgp_html_elements'                =>  'includes/class-amgp-html-elements.php',
				'amgp_input_filter'                 =>  'includes/class-amgp-input-filter.php',

				// SYSTEM
				'amgp_logging'                      =>  'includes/system/class-amgp-logging.php',
				'amgp_kb_core'                      =>  'includes/system/class-amgp-kb-core.php',
				'amgp_license_handler'              =>  'includes/system/class-amgp-license-handler.php',
				'amgp_upgrades'                     =>  'includes/system/class-amgp-upgrades.php',
				'amgp_db'                           =>  'includes/system/class-amgp-db.php',

				// ADMIN CORE
				'amgp_admin_notices'                =>  'includes/admin/class-amgp-admin-notices.php',
				'amgp_settings_page'                =>  'includes/admin/settings/class-amgp-settings-page.php',

				// ADMIN PLUGIN MENU PAGES

				// KB Configuration
				'amgp_kb_config_controller'         =>  'includes/admin/kb-configuration/class-amgp-kb-config-controller.php',
				'amgp_kb_config_specs'              =>  'includes/admin/kb-configuration/class-amgp-kb-config-specs.php',
				'amgp_kb_config_db'                 =>  'includes/admin/kb-configuration/class-amgp-kb-config-db.php',

				'amgp_add_ons_page'                 =>  'includes/admin/add-ons/class-amgp-add-ons-page.php',

				// FEATURES 
				'amgp_access_utilities'             =>  'includes/features/class-amgp-access-utilities.php',
				'amgp_kb_roles'                     =>  'includes/features/class-amgp-kb-roles.php',
				'amgp_kb_role'                      =>  'includes/features/class-amgp-kb-roles.php',
				'amgp_kb_handler'                   =>  'includes/features/kbs/class-amgp-kb-handler.php',
				'amgp_wp_roles'                     =>  'includes/features/class-amgp-wp-roles.php',

				// access
				'amgp_admin_articles_page'          =>  'includes/features/access/class-amgp-admin-articles-page.php',
				'amgp_groups'                       =>  'includes/features/access/class-amgp-groups.php',

				// controllers
				'amgp_access_page_controller'       =>  'includes/features/configuration/class-amgp-access-page-controller.php',
				'amgp_access_page_cntrl_groups'     =>  'includes/features/configuration/class-amgp-access-page-cntrl-groups.php',
				'amgp_access_page_cntrl_users'      =>  'includes/features/configuration/class-amgp-access-page-cntrl-users.php',

				// views
				'amgp_access_page_view_groups'      =>  'includes/features/configuration/class-amgp-access-page-view-groups.php',
				'amgp_access_page_view_users'       =>  'includes/features/configuration/class-amgp-access-page-view-users.php',

				// database
				'amgp_db_kb_groups'             	=>  'includes/features/database/class-amgp-db-kb-groups.php',
				'amgp_db_kb_public_groups'         	=>  'includes/features/database/class-amgp-db-kb-public-groups.php',
				'amgp_db_kb_group_users'        	=>  'includes/features/database/class-amgp-db-kb-group-users.php',
				'amgp_db_access_read_only_articles' =>  'includes/features/database/class-amgp-db-access-read-only-articles.php',
				'amgp_db_access_read_only_categories' =>  'includes/features/database/class-amgp-db-access-read-only-categories.php',
				'amgp_db_access_kb_categories'  	=>  'includes/features/database/class-amgp-db-access-kb-categories.php',

			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_KB_Groups::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
