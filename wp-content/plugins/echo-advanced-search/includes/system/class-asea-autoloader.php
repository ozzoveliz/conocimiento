<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'ASEA_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ASEA_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'asea_utilities'                    =>  'includes/class-asea-utilities.php',
				'asea_core_utilities'  				=>  'includes/class-asea-core-utilities.php',
				'asea_input_filter'                 =>  'includes/class-asea-input-filter.php',
				'asea_html_forms'                   =>  'includes/class-asea-html-forms.php',
				'asea_minifier'                     =>  'includes/class-asea-minifier.php',

				// SYSTEM
				'asea_logging'                      =>  'includes/system/class-asea-logging.php',
				'asea_kb_core'                      =>  'includes/system/class-asea-kb-core.php',
				'asea_templates'                    =>  'includes/system/class-asea-templates.php',
				'asea_license_handler'              =>  'includes/system/class-asea-license-handler.php',
				'asea_upgrades'                     =>  'includes/system/class-asea-upgrades.php',
				'asea_db'                           =>  'includes/system/class-asea-db.php',
				'asea_typography'                   =>  'includes/system/class-asea-typography.php',

				// PAGES
				'asea_add_ons_page'                 =>  'includes/admin/pages/class-asea-add-ons-page.php',
				'asea_settings_page'                =>  'includes/admin/pages/class-asea-settings-page.php',
				'asea_config_page'                  =>  'includes/admin/pages/class-asea-config-page.php',

				// ADMIN CORE
				'asea_admin_notices'                =>  'includes/admin/class-asea-admin-notices.php',

				// KB Configuration
				'asea_kb_config_controller'         =>  'includes/admin/kb-configuration/class-asea-kb-config-controller.php',
				'asea_kb_config_specs'              =>  'includes/admin/kb-configuration/class-asea-kb-config-specs.php',
				'asea_kb_config_db'                 =>  'includes/admin/kb-configuration/class-asea-kb-config-db.php',
				'asea_kb_config_styles'             =>  'includes/admin/kb-configuration/class-asea-kb-config-styles.php',

				// EDITOR
				'asea_kb_editor_config'             =>  'includes/admin/editor/class-asea-kb-editor-config.php',
				'asea_kb_editor_article_page_config'=>  'includes/admin/editor/class-asea-kb-editor-article-page-config.php',
				'asea_kb_editor_main_page_config'   =>  'includes/admin/editor/class-asea-kb-editor-main-page-config.php',
				'asea_kb_editor_search_page_config' =>  'includes/admin/editor/class-asea-kb-editor-search-page-config.php',

				// FEATURES - KB
				'asea_kb_handler'                   =>  'includes/features/kbs/class-asea-kb-handler.php',

				// FEATURES - SEARCH
				'asea_search_box_view'              =>  'includes/features/search/class-asea-search-box-view.php',
				'asea_search_box_cntrl'             =>  'includes/features/search/class-asea-search-box-cntrl.php',
				'asea_search_query'                 =>  'includes/features/search/class-asea-search-query.php',
				'asea_search_query_extras'          =>  'includes/features/search/class-asea-search-query-extras.php',
				'asea_search_logging'               =>  'includes/features/search/class-asea-search-logging.php',
				'asea_search_db'                    =>  'includes/features/search/class-asea-search-db.php',
				'asea_search_shortcode'             =>  'includes/features/search/class-asea-search-shortcode.php',

				// FEATURES - ANALYTICS
				'asea_analytics_view'               =>  'includes/features/analytics/class-asea-analytics-view.php'
			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Advanced_Search::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
