<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register( array( 'WIDG_Autoloader', 'autoload') );

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class WIDG_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'widg_utilities'                    =>  'includes/class-widg-utilities.php',
				'widg_core_utilities'  				=>  'includes/class-widg-core-utilities.php',
				'widg_input_filter'                 =>  'includes/class-widg-input-filter.php',

				// SYSTEM
				'widg_logging'                      =>  'includes/system/class-widg-logging.php',
				'widg_kb_core'                      =>  'includes/system/class-widg-kb-core.php',
				'widg_license_handler'              =>  'includes/system/class-widg-license-handler.php',
				'widg_upgrades'                     =>  'includes/system/class-widg-upgrades.php',
				'widg_typography'                   =>  'includes/system/class-widg-typography.php',

				// ADMIN CORE
				'widg_admin_notices'                =>  'includes/admin/class-widg-admin-notices.php',

				// PAGES
				'widg_add_ons_page'                 =>  'includes/admin/pages/class-widg-add-ons-page.php',
				'widg_settings_page'                =>  'includes/admin/pages/class-widg-settings-page.php',

				// KB Configuration
				'widg_kb_config_controller'         =>  'includes/admin/kb-configuration/class-widg-kb-config-controller.php',
				'widg_kb_config_specs'              =>  'includes/admin/kb-configuration/class-widg-kb-config-specs.php',
				'widg_kb_config_db'                 =>  'includes/admin/kb-configuration/class-widg-kb-config-db.php',

				// EDITOR
				'widg_kb_editor_config'             =>  'includes/admin/editor/class-widg-kb-editor-config.php',
				'widg_kb_editor_article_page_config'=>  'includes/admin/editor/class-widg-kb-editor-article-page-config.php',


				// FEATURES - KB
                'widg_kb_handler'                   =>  'includes/features/kbs/class-widg-kb-handler.php',
                'widg_kb_search'                    =>  'includes/features/kbs/class-widg-kb-search.php',

                // FEATURES - WIDGETS
                'widg_widgets'                      =>  'includes/features/widgets/class-widg-widgets.php',
                'widg_recent_articles_widget'       =>  'includes/features/widgets/class-widg-recent-articles-widget.php',
                'widg_category_articles_widget'     =>  'includes/features/widgets/class-widg-category-articles-widget.php',
                'widg_tag_articles_widget'          =>  'includes/features/widgets/class-widg-tag-articles-widget.php',
                'widg_categories_list_widget'       =>  'includes/features/widgets/class-widg-categories-list-widget.php',
                'widg_tags_list_widget'             =>  'includes/features/widgets/class-widg-tags-list-widget.php',
				'widg_search_articles_widget'       =>  'includes/features/widgets/class-widg-search-articles-widget.php',
				'widg_popular_articles_widget'      =>  'includes/features/widgets/class-widg-popular-articles-widget.php',

                // FEATURES - SHORTCODES
                'widg_shortcodes'                   =>  'includes/features/shortcodes/class-widg-shortcodes.php',
                'widg_recent_articles_shortcode'    =>  'includes/features/shortcodes/class-widg-recent-articles-shortcode.php',
                'widg_category_articles_shortcode'  =>  'includes/features/shortcodes/class-widg-category-articles-shortcode.php',
                'widg_tag_articles_shortcode'       =>  'includes/features/shortcodes/class-widg-tag-articles-shortcode.php',
                'widg_categories_list_shortcode'    =>  'includes/features/shortcodes/class-widg-categories-list-shortcode.php',
                'widg_tags_list_shortcode'          =>  'includes/features/shortcodes/class-widg-tags-list-shortcode.php',
				'widg_search_articles_shortcode'    =>  'includes/features/shortcodes/class-widg-search-articles-shortcode.php',
				'widg_popular_articles_shortcode'   =>  'includes/features/shortcodes/class-widg-popular-articles-shortcode.php',

            );
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Widgets::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
