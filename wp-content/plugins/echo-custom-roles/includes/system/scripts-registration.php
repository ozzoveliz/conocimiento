<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**  Register JS and CSS files  */

/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function amcr_load_admin_plugin_pages_resources(  ) {

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'amcr-admin-plugin-pages-styles', Echo_Custom_roles::$plugin_url . 'css/admin-plugin-pages' . $suffix . '.css', array(), Echo_Custom_roles::$version );
	wp_enqueue_script( 'amcr-admin-plugin-pages-scripts', Echo_Custom_roles::$plugin_url . 'js/admin-plugin-pages' . $suffix . '.js',
					array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core','jquery-effects-bounce', 'jquery-ui-sortable'), Echo_Custom_roles::$version );
	wp_localize_script( 'amcr-admin-plugin-pages-scripts', 'amcr_vars', array(
					'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
					'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
					'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
					'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
					'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
					'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
				));

	if ( AMCR_Utilities::get('page') == 'amag-access-mgr' ) {
		wp_enqueue_script( 'amcr-admin-amcr-config-script', Echo_Custom_roles::$plugin_url . 'js/admin-amcr-config-script' . $suffix . '.js',
			array('jquery',	'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Custom_roles::$version );
		wp_localize_script( 'amcr-admin-amcr-config-script', 'amcr_vars', array(
			'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
			'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
			'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
			'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
			'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
			'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
			'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
			'reduce_name_size'      => esc_html__( 'Warning: Please reduce your name size. Tab will only show first 25 characters', 'echo-knowledge-base' ),
		));
	}

	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}
