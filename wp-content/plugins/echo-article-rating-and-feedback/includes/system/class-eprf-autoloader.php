<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'EPRF_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPRF_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'eprf_utilities'                    =>  'includes/class-eprf-utilities.php',
				'eprf_core_utilities'  				=>  'includes/class-eprf-core-utilities.php',
				'eprf_input_filter'                 =>  'includes/class-eprf-input-filter.php',
				'eprf_html_forms'                   =>  'includes/class-eprf-html-forms.php',

				// SYSTEM
				'eprf_logging'                      =>  'includes/system/class-eprf-logging.php',
				'eprf_kb_core'                      =>  'includes/system/class-eprf-kb-core.php',
				'eprf_license_handler'              =>  'includes/system/class-eprf-license-handler.php',
				'eprf_upgrades'                     =>  'includes/system/class-eprf-upgrades.php',
				'eprf_db'                           =>  'includes/system/class-eprf-db.php',
				'eprf_typography'                   =>  'includes/system/class-eprf-typography.php',


				// ADMIN CORE
				'eprf_admin_notices'                =>  'includes/admin/class-eprf-admin-notices.php',
				'eprf_settings_page'                =>  'includes/admin/settings/class-eprf-settings-page.php',

				// KB Configuration
				'eprf_kb_config_controller'         =>  'includes/admin/kb-configuration/class-eprf-kb-config-controller.php',
				'eprf_kb_config_specs'              =>  'includes/admin/kb-configuration/class-eprf-kb-config-specs.php',
				'eprf_kb_config_db'                 =>  'includes/admin/kb-configuration/class-eprf-kb-config-db.php',
				'eprf_add_ons_page'                 =>  'includes/admin/add-ons/class-eprf-add-ons-page.php',

				// EDITOR
				'eprf_kb_editor_config'             =>  'includes/admin/editor/class-eprf-kb-editor-config.php',
				'eprf_kb_editor_article_page_config'=>  'includes/admin/editor/class-eprf-kb-editor-article-page-config.php',


				// FEATURES - RATING
				'eprf_kb_handler'                   =>  'includes/features/kbs/class-eprf-kb-handler.php',
				'eprf_rating_cntrl'                 =>  'includes/features/rating/class-eprf-rating-cntrl.php',
				'eprf_rating_view'                  =>  'includes/features/rating/class-eprf-rating-view.php',
				'eprf_rating_comments'              =>  'includes/features/rating/class-eprf-rating-comments.php',
				'eprf_rating_admin_view'            =>  'includes/features/rating/class-eprf-rating-admin-view.php',
				'eprf_rating_db'                    =>  'includes/features/rating/class-eprf-rating-db.php',
				'eprf_analytics_view'               =>  'includes/features/rating/class-eprf-analytics-view.php',
				'eprf_rating_admin_cntrl'           =>  'includes/features/rating/class-eprf-rating-admin-ctrl.php'
			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Article_Rating_And_Feedback::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
