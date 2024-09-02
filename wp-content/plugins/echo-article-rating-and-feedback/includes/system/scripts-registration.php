<?php

/**  Register JS and CSS files  */

/**
 * FRONT-END pages using our plugin features
 */
function eprf_load_public_resources() {
	global $eckb_is_kb_main_page;

	if ( ! empty( $eckb_is_kb_main_page ) || is_archive() ) {
		return;
	}

	eprf_register_public_resources();
	
	$post = empty( $GLOBALS['post'] ) ? '' : $GLOBALS['post'];
	$kb_id = EPRF_Utilities::get_eckb_kb_id( '' );
	if ( ! class_exists( EPRF_KB_Core::EPRF_KB_KNOWLEDGE_BASE ) || empty( $post ) || empty( $kb_id ) ) {
		return;
	}

	eprf_enqueue_public_resources();
}
add_action( 'wp_enqueue_scripts', 'eprf_load_public_resources' );

/**
 * Register for FRONT-END pages using our plugin features
 */
function eprf_register_public_resources() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'eprf-public-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	if ( is_rtl() ) {
		wp_register_style( 'eprf-public-styles-rtl', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	}
	
	wp_register_script( 'eprf-public-scripts', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/public-scripts' . $suffix . '.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );
	wp_localize_script( 'eprf-public-scripts', 'eprf_vars', array(
		'ajaxurl'           => admin_url( 'admin-ajax.php', 'relative' ),
		'msg_try_again'     => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'    => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (16)',
		'not_saved'         => esc_html__( 'Error occurred - configuration NOT saved (6).', 'echo-knowledge-base' ),
		'unknown_error'     => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (17)',
		'reload_try_again'  => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'save_config'       => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
		'input_required'    => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'nonce'             => wp_create_nonce( '_wpnonce_eprf_ajax_action' ),
	));
}

/**
 * Queue for FRONT-END pages using our plugin features
 * @noinspection PhpUnusedParameterInspection
 * @param int $kb_id
 */
function eprf_enqueue_public_resources( $kb_id=0 ) {
	wp_enqueue_style( 'eprf-public-styles' );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'eprf-public-styles-rtl' );
	}
	wp_enqueue_script( 'eprf-public-scripts' );
	eprf_enqueue_google_fonts();
	do_action( 'epkb_enqueue_font_scripts');
}
// Article Rating does not run on KB Main Page: add_action( 'epkb_enqueue_scripts', 'eprf_enqueue_public_resources' );

/**
 * Only used for KB Configuration page
 */
function eprf_kb_config_load_public_css() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'eprf-public-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'eprf-public-styles-rtl', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	}
}

/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function eprf_load_admin_plugin_pages_resources() {

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'eprf-admin-plugin-pages-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/admin-plugin-pages' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'eprf-admin-plugin-pages-rtl', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/admin-plugin-pages-rtl' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	}
	
	wp_enqueue_style( 'wp-color-picker' ); //Color picker

	wp_enqueue_script( 'eprf-admin-plugin-pages-scripts', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/admin-plugin-pages' . $suffix . '.js',
					array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core','jquery-effects-bounce', 'jquery-ui-sortable'), Echo_Article_Rating_And_Feedback::$version );
	wp_localize_script( 'eprf-admin-plugin-pages-scripts', 'eprf_vars', array(
		'msg_try_again'     => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'    => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (11)',
		'not_saved'         => esc_html__( 'Error occurred - configuration NOT saved (12).', 'echo-knowledge-base' ),
		'unknown_error'     => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (13)',
		'reload_try_again'  => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'save_config'       => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
		'input_required'    => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'nonce'             => wp_create_nonce( '_wpnonce_eprf_ajax_action' ),
		'delete_confirm'    => esc_html__( 'Do you want to delete all rating on this article? This cannot be undone.', 'echo-article-rating-and-feedback' ),
	) );

	if ( EPRF_Utilities::get('page') == EPRF_KB_Core::EPRF_KB_CONFIGURATION_PAGE ) {
		wp_enqueue_script( 'eprf-admin-kb-config-script', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/admin-eprf-config-script' . $suffix . '.js',
			array('jquery',	'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Article_Rating_And_Feedback::$version );
		wp_localize_script( 'eprf-admin-kb-config-script', 'eprf_vars', array(
			'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
			'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (14)',
			'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved.', 'echo-knowledge-base' ),
			'unknown_error'         => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (15)',
			'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
			'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
			'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' )
		));
	}

	$post_type = EPRF_Utilities::get('post') ? get_post_type(EPRF_Utilities::get('post')) : '';
	if ( EPRF_Utilities::is_block_editor_active() && EPRF_Utilities::get('action') == 'edit' && EPRF_KB_Handler::is_kb_post_type( $post_type )  ) {
		wp_enqueue_script( 'eprf-admin-kb-gutenberg-script', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/admin-gutenberg-scripts' . $suffix . '.js', array(
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components'
        )
		);
	}
		
	// data tables
	wp_enqueue_style( 'eprf-admin-plugin-datatables-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/jquery.datatables-custom.min.css', array(), Echo_Article_Rating_And_Feedback::$version );
	wp_enqueue_script( 'eprf-admin-jquery-datatables', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/jquery.datatables.min.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );
	if ( eprf_Utilities::get('page', '', false) == 'ep'.'kb-plugin-analytics' ) {
		wp_enqueue_script( 'eprf-admin-jquery-chart', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/chart.min.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );
		wp_enqueue_script( 'eprf-admin-analytics-scripts', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/admin-analytics' . $suffix . '.js',
				array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core'), Echo_Article_Rating_And_Feedback::$version );
	}

	// datarange
	wp_enqueue_style( 'eprf-admin-plugin-datarange-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/daterangepicker.css', array(), Echo_Article_Rating_And_Feedback::$version );
	wp_enqueue_script( 'eprf-admin-jquery-datarange', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/moment.min.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );
	wp_enqueue_script( 'eprf-admin-jquery-datarange2', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/daterangepicker.min.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

/**
 * Enguque fonts that are configured in KB config
 */
function eprf_enqueue_google_fonts() {

	$kb_id = EPRF_Utilities::get_eckb_kb_id();
	$kb_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );

	foreach ( $kb_config as $name => $value ) {
		if ( is_array( $value ) && ! empty( $value['font-family'] ) ) {
			$font_link = EPRF_Typography::get_google_font_link( $value['font-family'] );
			if ( ! empty($font_link) ) {
				wp_enqueue_style( 'epkb-font-' . sanitize_title( $value['font-family']), $font_link );
			}
		}
	}
}

/**
 * Enqueue styles for editor safe mode
 */
function eprf_load_editor_styles_inline() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'eprf-public-styles', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
	wp_print_styles( array( 'eprf-public-styles' ) );

	if ( is_rtl() ) {
		wp_register_style( 'eprf-public-styles-rtl', Echo_Article_Rating_And_Feedback::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Article_Rating_And_Feedback::$version );
		wp_print_styles( array( 'eprf-public-styles-rtl' ) );
	}


	$eprf_vars = array(
		'ajaxurl'           => admin_url( 'admin-ajax.php', 'relative' ),
		'msg_try_again'     => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'    => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (16)',
		'not_saved'         => esc_html__( 'Error occurred - configuration NOT saved (6).', 'echo-knowledge-base' ),
		'unknown_error'     => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (17)',
		'reload_try_again'  => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'save_config'       => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
		'input_required'    => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'nonce'             => wp_create_nonce( '_wpnonce_eprf_ajax_action' )
	);

	wp_register_script( 'eprf-public-scripts', Echo_Article_Rating_And_Feedback::$plugin_url . 'js/public-scripts' . $suffix . '.js', array( 'jquery' ), Echo_Article_Rating_And_Feedback::$version );

	wp_add_inline_script( 'eprf-public-scripts', '
		var eprf_vars = ' . wp_json_encode( $eprf_vars, ENT_QUOTES ) . ';' );

	wp_print_scripts( array( 'eprf-public-scripts' ) );

	$kb_id = EPRF_Utilities::get_eckb_kb_id();
	$kb_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );

	foreach ( $kb_config as $name => $value ) {
		if ( is_array( $value ) && ! empty( $value['font-family'] ) ) {
			$font_link = EPRF_Typography::get_google_font_link( $value['font-family'] );
			if ( ! empty($font_link) ) {
				wp_register_style( 'epkb-font-' . sanitize_title( $value['font-family']), $font_link );
				wp_print_styles( array( 'epkb-font-' . sanitize_title( $value['font-family'] ) ) );
			}
		}
	}
}
add_action( 'epkb_load_editor_backend_mode_styles_inline', 'eprf_load_editor_styles_inline' );