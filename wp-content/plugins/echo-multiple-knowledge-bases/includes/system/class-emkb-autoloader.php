<?php  if ( ! defined( 'ABSPATH' ) ) exit;

spl_autoload_register(array( 'EMKB_Autoloader', 'autoload'));

/**
 * A class which contains the autoload function, that the spl_autoload_register
 * will use to autoload PHP classes.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EMKB_Autoloader {

	public static function autoload( $class ) {
		static $classes = null;

		if ( $classes === null ) {
			$classes = array(

				// CORE
				'emkb_utilities'                    =>  'includes/class-emkb-utilities.php',
				'emkb_core_utilities'  				=>  'includes/class-emkb-core-utilities.php',
				'emkb_input_filter'                 =>  'includes/class-emkb-input-filter.php',

				// SYSTEM
				'emkb_logging'                      =>  'includes/system/class-emkb-logging.php',
				'emkb_kb_core'                      =>  'includes/system/class-emkb-kb-core.php',
				'emkb_license_handler'              =>  'includes/system/class-emkb-license-handler.php',
				'emkb_upgrades'                     =>  'includes/system/class-emkb-upgrades.php',
				'emkb_typography'                   =>  'includes/system/class-emkb-typography.php',

				// ADMIN CORE
				'emkb_admin_notices'                =>  'includes/admin/class-emkb-admin-notices.php',

				// PAGES
				'emkb_add_ons_page'                 =>  'includes/admin/pages/class-emkb-add-ons-page.php',


				// FEATURE
				'emkb_kb_handler'                   =>  'includes/features/kbs/class-emkb-kb-handler.php',
				'emkb_multiple_kbs_handler'         =>  'includes/features/class-emkb-multiple-kbs-handler.php'
			);
		}

		$cn = strtolower( $class );
		if ( isset( $classes[ $cn ] ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( plugin_dir_path( Echo_Multiple_Knowledge_Bases::$plugin_file ) . $classes[ $cn ] );
		}
	}
}
