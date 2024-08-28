<?php

/**  Register JS and CSS files  */

/**
 * FRONT-END pages using our plugin features
 */
function amgr_load_public_resources() {

	global $eckb_kb_id;

    // if this is not KB Main Page then do not load public resources
    if ( empty($eckb_kb_id) ) {
        return;
    }

	amgr_load_public_resources_now();
}
add_action( 'wp_enqueue_scripts', 'amgr_load_public_resources' );

function amgr_load_public_resources_now() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'amgr-public-styles', Echo_Knowledge_Base::$plugin_url . 'css_amgr/public-styles' . $suffix . '.css', array(), Echo_Knowledge_Base::$amag_version );
	wp_enqueue_script( 'amgr-public-scripts', Echo_Knowledge_Base::$plugin_url . 'js_amgr/public-scripts' . $suffix . '.js', array( 'jquery' ), Echo_Knowledge_Base::$amag_version );
	wp_enqueue_script( 'amgr-cookie-scripts', Echo_Knowledge_Base::$plugin_url . 'js_amgr/js.cookie' . $suffix . '.js', array( 'amgr-public-scripts' ) , Echo_Knowledge_Base::$amag_version );
	wp_localize_script( 'amgr-public-scripts', 'amgr_vars', array(
		'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
		'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
		'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
		'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'nonce'                 => wp_create_nonce( "_wpnonce_epkb_ajax_action" )
	));
}

/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function amgr_load_admin_plugin_pages_resources(  ) {
	global $current_screen;

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'amgr-admin-plugin-pages-styles', Echo_Knowledge_Base::$plugin_url . 'css_amgr/admin-plugin-pages' . $suffix . '.css', array(), Echo_Knowledge_Base::$amag_version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'amgr-admin-plugin-pages-styles-rtl', Echo_Knowledge_Base::$plugin_url . 'css_amgr/admin-plugin-pages-rtl' . $suffix . '.css', array(), Echo_Knowledge_Base::$amag_version );
	}

	$admin_script_dependencies = array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce', 'jquery-ui-sortable' );
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		$admin_script_dependencies += array( 'wp-blocks', 'wp-edit-post' );
	}
	wp_enqueue_script( 'amgr-admin-plugin-pages-scripts', Echo_Knowledge_Base::$plugin_url . 'js_amgr/admin-plugin-pages' . $suffix . '.js', $admin_script_dependencies, Echo_Knowledge_Base::$amag_version );
	wp_localize_script( 'amgr-admin-plugin-pages-scripts', 'amgr_vars', array(
					'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
					'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
					'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
					'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
					'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
					'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
					'nonce'                 => wp_create_nonce( "_wpnonce_epkb_ajax_action" ),
					'kb_taxonomy_name'      => EPKB_KB_Handler::get_category_taxonomy_name2(),
					'search_user'           => esc_html__( 'Searching User...', 'echo-knowledge-base' ),
					'user_not_found'        => esc_html__( 'User not found', 'echo-knowledge-base' ),
				));

	if ( EPKB_Utilities::get( 'page' ) == 'amag-access-mgr' ) {
		wp_enqueue_script( 'amgr-admin-amgr-config-script', Echo_Knowledge_Base::$plugin_url . 'js_amgr/admin-amgr-config-script' . $suffix . '.js',
					array('jquery',	'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Knowledge_Base::$amag_version );
		wp_localize_script( 'amgr-admin-amgr-config-script', 'amgr_vars', array(
			'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
			'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
			'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
			'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
			'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
			'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
			'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
			'reduce_name_size'      => esc_html__( 'Warning: Please reduce your name size. Tab will only show first 25 characters', 'echo-knowledge-base' ),
			'nonce'                 => wp_create_nonce( "_wpnonce_epkb_ajax_action" )
		));
	}

	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}
