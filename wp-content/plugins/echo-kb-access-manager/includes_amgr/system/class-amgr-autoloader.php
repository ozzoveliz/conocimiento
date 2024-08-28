<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'AMGR_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'amgr_html_elements'                =>  'includes_amgr/class-amgr-html-elements.php',

				// SYSTEM
				'amgr_logging'                      =>  'includes_amgr/system/class-amgr-logging.php',
				'amgr_license_handler'              =>  'includes_amgr/system/class-amgr-license-handler.php',
				'amgr_upgrades'                     =>  'includes_amgr/system/class-amgr-upgrades.php',
				'amgr_db'                           =>  'includes_amgr/system/class-amgr-db.php',
				'amgr_debug_user_access'            =>  'includes_amgr/system/class-amgr-debug-user-access.php',

				// ADMIN CORE
				'amgr_admin_notices'                =>  'includes_amgr/admin/class-amgr-admin-notices.php',
				'amgr_settings_page'                =>  'includes_amgr/admin/settings/class-amgr-settings-page.php',

				// ADMIN PLUGIN MENU PAGES
				// KB Configuration
				'amgr_kb_config_controller'         =>  'includes_amgr/admin/kb-configuration/class-amgr-kb-config-controller.php',
				'amgr_kb_config_specs'              =>  'includes_amgr/admin/kb-configuration/class-amgr-kb-config-specs.php',
				'amgr_kb_access_config_db'          =>  'includes_amgr/admin/kb-configuration/class-amgr-kb-access-config-db.php',
				'amgr_kb_config_elements'           =>  'includes_amgr/admin/kb-configuration/class-amgr-kb-config-elements.php',

				'amgr_add_ons_page'                 =>  'includes_amgr/admin/add-ons/class-amgr-add-ons-page.php',

				// FEATURES
				'amgr_access_utilities'             =>  'includes_amgr/features/class-amgr-access-utilities.php',
				'amgr_kb_roles'                     =>  'includes_amgr/features/class-amgr-kb-roles.php',
				'amgr_kb_role'                      =>  'includes_amgr/features/class-amgr-kb-roles.php',
				'amgr_wp_roles'                     =>  'includes_amgr/features/class-amgr-wp-roles.php',

				// access
				'amgr_access_reject'                =>  'includes_amgr/features/access/class-amgr-access-reject.php',
				'amgr_access_manager'               =>  'includes_amgr/features/access/class-amgr-access-manager.php',
				'amgr_access_article'               =>  'includes_amgr/features/access/class-amgr-access-article.php',
				'amgr_access_articles_back'         =>  'includes_amgr/features/access/class-amgr-access-articles-back.php',
				'amgr_access_articles_front'        =>  'includes_amgr/features/access/class-amgr-access-articles-front.php',
				'amgr_access_category'              =>  'includes_amgr/features/access/class-amgr-access-category.php',
				'amgr_access_categories_back'       =>  'includes_amgr/features/access/class-amgr-access-categories-back.php',
				'amgr_admin_categories_page'        =>  'includes_amgr/features/access/class-amgr-admin-categories-page.php',
				'amgr_admin_articles_page'          =>  'includes_amgr/features/access/class-amgr-admin-articles-page.php',
				'amgr_access_main_page_front'       =>  'includes_amgr/features/access/class-amgr-access-main-page-front.php',

				// Configuration Page
				'amgr_access_page'                  =>  'includes_amgr/features/configuration/class-amgr-access-page.php',
				'amgr_setup_kb_groups'              =>  'includes_amgr/features/configuration/class-amgr-setup-kb-groups.php',

				// controllers
				'amgr_access_page_controller'       =>  'includes_amgr/features/configuration/class-amgr-access-page-controller.php',
				'amgr_access_page_cntrl_kbs'        =>  'includes_amgr/features/configuration/class-amgr-access-page-cntrl-kbs.php',
				'amgr_access_page_cntrl_categories' =>  'includes_amgr/features/configuration/class-amgr-access-page-cntrl-categories.php',
				'amgr_access_page_cntrl_articles'   =>  'includes_amgr/features/configuration/class-amgr-access-page-cntrl-articles.php',

				// views
				'amgr_access_page_view_kb'          =>  'includes_amgr/features/configuration/class-amgr-access-page-view-kb.php',
				'amgr_access_page_view_categories'  =>  'includes_amgr/features/configuration/class-amgr-access-page-view-categories.php',
				'amgr_access_page_view_articles'    =>  'includes_amgr/features/configuration/class-amgr-access-page-view-articles.php',

				// database
				'amgr_db_kb_groups'             	=>  'includes_amgr/features/database/class-amgr-db-kb-groups.php',
				'amgr_db_kb_public_groups'         	=>  'includes_amgr/features/database/class-amgr-db-kb-public-groups.php',
				'amgr_db_kb_group_users'        	=>  'includes_amgr/features/database/class-amgr-db-kb-group-users.php',
				'amgr_db_access_read_only_articles' =>  'includes_amgr/features/database/class-amgr-db-access-read-only-articles.php',
				'amgr_db_access_read_only_categories' =>  'includes_amgr/features/database/class-amgr-db-access-read-only-categories.php',
				'amgr_db_access_kb_categories'  	=>  'includes_amgr/features/database/class-amgr-db-access-kb-categories.php',

			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Knowledge_Base::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
