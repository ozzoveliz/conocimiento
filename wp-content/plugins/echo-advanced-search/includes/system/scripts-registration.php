<?php

/**  Register JS and CSS files  */

/**
 * FRONT-END pages using our plugin features
 */
function asea_load_public_resources() {

	asea_register_public_resources();
	
	// if this is not KB Main Page then do not load public resources or is a Category Archive page
	$kb_id = ASEA_Utilities::get_eckb_kb_id( '' );
	if ( empty( $kb_id ) ) {
		return;
	}

	asea_enqueue_public_resources( $kb_id );
}
add_action( 'wp_enqueue_scripts', 'asea_load_public_resources' );

/**
 * Register for FRONT-END pages using our plugin features
 */
function asea_register_public_resources( ) {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'asea-public-styles', Echo_Advanced_Search::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	
	if ( is_rtl() ) {
		wp_register_style( 'asea-public-styles-rtl', Echo_Advanced_Search::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	}
	
	wp_register_script( 'asea-jquery-ui-autocomplete', Echo_Advanced_Search::$plugin_url . 'js/asea-jquery-ui-autocomplete.min.js', array( 'jquery-ui-menu', 'wp-a11y' ), Echo_Advanced_Search::$version );
	wp_register_script( 'asea-public-scripts', Echo_Advanced_Search::$plugin_url . 'js/public-scripts' . $suffix . '.js', array('asea-jquery-ui-autocomplete'), Echo_Advanced_Search::$version );
}

/**
 * Queue for FRONT-END pages using our plugin features
 * @param int $kb_id
 */
function asea_enqueue_public_resources( $kb_id = 0 ) {
	wp_enqueue_style( 'asea-public-styles' );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'asea-public-styles-rtl' );
	}
	
	wp_enqueue_script( 'asea-jquery-ui-autocomplete' );
	wp_enqueue_script( 'asea-public-scripts' );
	
	if ( empty($kb_id) ) {
		$auto_complete_wait = 1000;
	} else {
		$kb_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		$auto_complete_wait = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_auto_complete_wait' );
	}

	wp_localize_script( 'asea-public-scripts', 'asea_vars', array(
		'ajaxurl'               => admin_url( 'admin-ajax.php', 'relative' ),
		'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (16)',
		'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved (6).', 'echo-knowledge-base' ),
		'unknown_error'         => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (17)',
		'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
		'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'advanced_search_auto_complete_wait'   => $auto_complete_wait,
	));

	asea_enqueue_google_fonts();
	
}
add_action( 'epkb_enqueue_scripts', 'asea_enqueue_public_resources' );

/**
 * Only used by CREL for KB Elementor Widget
 */
function asea_kb_config_load_public_css() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'asea-public-styles', Echo_Advanced_Search::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'asea-public-styles-rtl', Echo_Advanced_Search::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	}
}


/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function asea_load_admin_plugin_pages_resources() {

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style( 'asea-admin-plugin-pages-styles', Echo_Advanced_Search::$plugin_url . 'css/admin-plugin-pages' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style( 'asea-admin-plugin-pages-rtl', Echo_Advanced_Search::$plugin_url . 'css/admin-plugin-pages-rtl' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	}
	
	wp_enqueue_script( 'asea-admin-plugin-pages-scripts', Echo_Advanced_Search::$plugin_url . 'js/admin-plugin-pages' . $suffix . '.js',
					array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core'), Echo_Advanced_Search::$version );
	wp_localize_script( 'asea-admin-plugin-pages-scripts', 'asea_vars', array(
					'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
					'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (11)',
					'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved (12).', 'echo-knowledge-base' ),
					'unknown_error'         => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (13)',
					'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
					'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
					'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' )
				));

	// data tables
	wp_enqueue_style( 'asea-admin-plugin-datatables-styles', Echo_Advanced_Search::$plugin_url . 'css/jquery.datatables-custom.min.css', array(), Echo_Advanced_Search::$version );
	wp_enqueue_script( 'asea-admin-jquery-datatables', Echo_Advanced_Search::$plugin_url . 'js/jquery.datatables.min.js', array( 'jquery' ), Echo_Advanced_Search::$version );

	if ( ASEA_Utilities::get('page', '', false) == 'ep'.'kb-plugin-analytics' ) {
		wp_enqueue_script( 'asea-admin-jquery-chart', Echo_Advanced_Search::$plugin_url . 'js/Chart.min.js', array( 'jquery' ), Echo_Advanced_Search::$version );
		wp_enqueue_script( 'asea-analytic-page-scripts', Echo_Advanced_Search::$plugin_url . 'js/admin-analytics' . $suffix . '.js',
				array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core'), Echo_Advanced_Search::$version );
	}

	// datarange widget
	wp_enqueue_style( 'asea-admin-plugin-datarange-styles', Echo_Advanced_Search::$plugin_url . 'css/daterangepicker.css', array(), Echo_Advanced_Search::$version );
	wp_enqueue_script( 'asea-admin-jquery-datarange', Echo_Advanced_Search::$plugin_url . 'js/moment.min.js', array( 'jquery' ), Echo_Advanced_Search::$version );
	wp_enqueue_script( 'asea-admin-jquery-datarange2', Echo_Advanced_Search::$plugin_url . 'js/daterangepicker.min.js', array( 'jquery' ), Echo_Advanced_Search::$version );

	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

/**
 * ASEA config data for the Front-end Editor
 * @param $epkb_editor_addon_data
 * @param $kb_config
 * @return mixed
 */
function asea_editor_addon_data( $epkb_editor_addon_data, $kb_config ) {
	
	$epkb_editor_addon_data['asea_presets'] = array();
	$epkb_editor_addon_data['asea_presets']['current'] = array();
	
	$preset_names = ASEA_KB_Config_Styles::get_advanced_search_box_style_names();
	
	foreach ( $preset_names as $preset_key => $preset_name ) { 
		$epkb_editor_addon_data['asea_presets'][$preset_key] = ASEA_KB_Config_Styles::get_advanced_search_box_style_set( array(), 'mp', $preset_key ) +
		                                                       ASEA_KB_Config_Styles::get_advanced_search_box_style_set( array(), 'ap', $preset_key );
		
		foreach ( $epkb_editor_addon_data['asea_presets'][$preset_key] as $setting_name => $setting_value ) {
			
			if ( isset($kb_config[$setting_name]) ) {
				$epkb_editor_addon_data['asea_presets']['current'][$setting_name] = $kb_config[$setting_name];
			}
		}
	}

	return $epkb_editor_addon_data;
}

add_filter( 'epkb_editor_addon_data', 'asea_editor_addon_data', 10, 2 );

function asea_editor_get_presets_html() { ?>
	<div class="epkb-editor-settings-panel-container"  id="epkb-editor-settings-asea-presets">
		<div class="epkb-editor-settings-accordeon-item__title"><?php _e( 'Choose a Preset and save it', 'echo-advanced-search' ); ?></div>
		<div class="epkb-editor-settings-control-container epkb-editor-settings-control-type-json-button">
			<label class="epkb-editor-settings-control-json-button" data-name="asea_presets">
				<input type="radio" name="asea_presets" value="current"  id="asea-preset-current">
				<div class="epkb-editor-settings-control-json-button--label"><?php _e( 'Current', 'echo-advanced-search' ); ?></div>
			</label><?php 
			
			$preset_names = ASEA_KB_Config_Styles::get_advanced_search_box_style_names();
			
			foreach ( $preset_names as $preset_key => $preset_name ) { ?>
				<label class="epkb-editor-settings-control-json-button" data-name="asea_presets">
					<input type="radio" name="asea_presets" id="asea-preset-<?php echo $preset_key; ?>" value="<?php echo $preset_key; ?>">
					<div class="epkb-editor-settings-control-json-button--label"><?php echo $preset_name; ?></div>
				</label><?php
			} ?>
			
		</div>
	</div><?php 
}
add_action( 'epkb_editor_settings_html', 'asea_editor_get_presets_html' );

/**
 * Enqueue fonts that are configured in KB config
 */
function asea_enqueue_google_fonts() {

	$kb_id = ASEA_Utilities::get_eckb_kb_id();
	$kb_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );

	foreach ( $kb_config as $name => $value ) {
		if ( is_array( $value ) && ! empty( $value['font-family'] ) ) {
			$font_link = ASEA_Typography::get_google_font_link( $value['font-family'] );
			if ( ! empty($font_link) ) {
				wp_enqueue_style( 'epkb-font-' . sanitize_title( $value['font-family']), $font_link );
			}
		}
	}
}

/**
 * Enqueue styles for editor backend mode
 */
function asea_load_editor_styles_inline() {

	$kb_id = ASEA_Utilities::get_eckb_kb_id();
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_style( 'asea-public-styles', Echo_Advanced_Search::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
	wp_print_styles( array( 'asea-public-styles' ) );

	if ( is_rtl() ) {
		wp_register_style( 'asea-public-styles-rtl', Echo_Advanced_Search::$plugin_url . 'css/public-styles-rtl' . $suffix . '.css', array(), Echo_Advanced_Search::$version );
		wp_print_styles( array( 'asea-public-styles-rtl' ) );
	}

	$kb_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );
	$auto_complete_wait = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_auto_complete_wait' );

	$asea_vars = array(
		'ajaxurl'               => admin_url( 'admin-ajax.php', 'relative' ),
		'msg_try_again'         => esc_html__( 'Please try again later.', 'echo-knowledge-base' ),
		'error_occurred'        => esc_html__( 'Error occurred', 'echo-knowledge-base' ) . ' (16)',
		'not_saved'             => esc_html__( 'Error occurred - configuration NOT saved (6).', 'echo-knowledge-base' ),
		'unknown_error'         => esc_html__( 'Unknown error', 'echo-knowledge-base' ) . ' (17)',
		'reload_try_again'      => esc_html__( 'Please reload the page and try again.', 'echo-knowledge-base' ),
		'save_config'           => esc_html__( 'Saving configuration', 'echo-knowledge-base' ),
		'input_required'        => esc_html__( 'Input is required', 'echo-knowledge-base' ),
		'advanced_search_auto_complete_wait'   => $auto_complete_wait,
	);

	wp_register_script( 'asea-jquery-ui-autocomplete', Echo_Advanced_Search::$plugin_url . 'js/asea-jquery-ui-autocomplete.min.js', array( 'jquery-ui-menu', 'wp-a11y' ), Echo_Advanced_Search::$version );

	wp_register_script( 'asea-public-scripts', Echo_Advanced_Search::$plugin_url . 'js/public-scripts' . $suffix . '.js', array('asea-jquery-ui-autocomplete'), Echo_Advanced_Search::$version );

	wp_add_inline_script( 'asea-public-scripts', '
		var asea_vars = ' . wp_json_encode( $asea_vars, ENT_QUOTES ) . ';' );
	
	wp_print_scripts( array( 'asea-jquery-ui-autocomplete', 'asea-public-scripts' ) );

	$kb_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );

	foreach ( $kb_config as $name => $value ) {
		if ( is_array( $value ) && ! empty( $value['font-family'] ) ) {
			$font_link = ASEA_Typography::get_google_font_link( $value['font-family'] );
			if ( ! empty($font_link) ) {
				wp_register_style( 'epkb-font-' . sanitize_title( $value['font-family']), $font_link );
				wp_print_styles( array( 'epkb-font-' . sanitize_title( $value['font-family'] ) ) );
			}
		}
	}
}
add_action( 'epkb_load_editor_backend_mode_styles_inline', 'asea_load_editor_styles_inline' );