<?php

/**  Register JS and CSS files  */

/**
 * FRONT-END pages using our plugin features
 */
function widg_load_public_resources() {

	global $eckb_kb_id;

// TODO

	widg_load_public_resources_now();

	$post = empty( $GLOBALS['post'] ) ? '' : $GLOBALS['post'];
	if ( ! class_exists( WIDG_KB_Core::WIDG_KB_KNOWLEDGE_BASE ) || empty( $post ) || ! $post instanceof WP_Post ||
		 empty( $post->post_type ) || empty( $eckb_kb_id ) ) {
		return;
	}
}
add_action( 'wp_enqueue_scripts', 'widg_load_public_resources' );


function widg_load_public_resources_now() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'widg-public-styles', Echo_Widgets::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Widgets::$version );
	
	if ( is_rtl() ) {
		wp_register_style( 'widg-public-styles-rtl', Echo_Widgets::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Widgets::$version );
	}
	
	wp_register_script( 'widg-public-scripts', Echo_Widgets::$plugin_url . 'js/public-scripts' . $suffix . '.js',
	array('jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Widgets::$version );
	wp_localize_script( 'widg-public-scripts', 'widg_vars', array(
		'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
		'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
		'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
		'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'ajaxurl'               => esc_html__( admin_url( 'admin-ajax.php', 'relative' ) )
	));
}

/**
 * Enqueue resources on Pages with KB widget
 */
function widg_load_public_resources_enqueue() {

	wp_enqueue_style( 'widg-public-styles' );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'widg-public-styles-rtl' );
	}
	
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_enqueue_script( 'widg-public-scripts');
	do_action( 'epkb_enqueue_font_scripts' );
}

/**
 * Only used for KB Configuration page
 */
function widg_kb_config_load_public_css() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_style( 'widg-public-styles', Echo_Widgets::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Widgets::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'widg-public-styles-rtl', Echo_Widgets::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Widgets::$version );
	}
}

/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function widg_load_admin_plugin_pages_resources(  ) {

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'widg-admin-plugin-pages-styles', Echo_Widgets::$plugin_url . 'css/admin-plugin-pages' . $suffix . '.css', array(), Echo_Widgets::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'widg-admin-plugin-pages-styles-rtl', Echo_Widgets::$plugin_url . 'css/admin-plugin-pages-rtl' . $suffix . '.css', array(), Echo_Widgets::$version );
	}
	
	wp_enqueue_script( 'widg-admin-plugin-pages-scripts', Echo_Widgets::$plugin_url . 'js/admin-plugin-pages' . $suffix . '.js',
					array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core','jquery-effects-bounce', 'jquery-ui-sortable'), Echo_Widgets::$version );
	wp_localize_script( 'widg-admin-plugin-pages-scripts', 'widg_vars', array(
					'ajaxurl'               => admin_url( 'admin-ajax.php', 'relative' ),
					'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
					'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
					'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
					'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
					'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
					'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
				));

	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

/**
 * Enqueue styles for editor safe mode
 */
function widg_load_editor_styles_inline() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'widg-public-styles', Echo_Widgets::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Widgets::$version );
	wp_print_styles( array( 'widg-public-styles' ) );

	if ( is_rtl() ) {
		wp_register_style( 'widg-public-styles-rtl', Echo_Widgets::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Widgets::$version );
		wp_print_styles( array( 'widg-public-styles-rtl' ) );
	}

	wp_register_script( 'widg-public-scripts', Echo_Widgets::$plugin_url . 'js/public-scripts' . $suffix . '.js',
		array('jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Widgets::$version );
	$widg_vars = array(
		'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ),
		'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
		'unknown_error'         => esc_html__( 'unknown error', 'echo-knowledge-base' ),
		'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'ajaxurl'               => esc_html__( admin_url( 'admin-ajax.php', 'relative' ) )
	);

	wp_add_inline_script( 'widg-public-scripts', '
		var widg_vars = ' . wp_json_encode( $widg_vars, ENT_QUOTES ) . ';' );

	wp_print_scripts( array( 'widg-public-scripts' ) );
}
add_action( 'epkb_load_editor_backend_mode_styles_inline', 'widg_load_editor_styles_inline' );