<?php

/**  Register JS and CSS files  */

/**
 * FRONT-END pages using our plugin features
 */
function emkb_load_public_resources() {

	// TODO limit to public KB pages only 

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style('emkb-public-styles', Echo_Multiple_Knowledge_Bases::$plugin_url . 'css/public-styles' . $suffix . '.css', array(), Echo_Multiple_Knowledge_Bases::$version );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_enqueue_script('emkb-public-scripts', Echo_Multiple_Knowledge_Bases::$plugin_url . 'js/public-scripts' . $suffix . '.js',
					array('jquery', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-effects-core', 'jquery-effects-bounce'), Echo_Multiple_Knowledge_Bases::$version );
}
// NOT YET APPLICABLE: add_action( 'wp_enqueue_scripts', 'emkb_load_public_resources' );

/**
 * ADMIN-PLUGIN MENU PAGES (Plugin settings, reports, lists etc.)
 */
function emkb_load_admin_plugin_pages_resources() {

	// if SCRIPT_DEBUG is off then use minified scripts
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_style('emkb-admin-plugin-pages-styles', Echo_Multiple_Knowledge_Bases::$plugin_url . 'css/admin-plugin-pages' . $suffix . '.css', array(), Echo_Multiple_Knowledge_Bases::$version );
	
	if ( is_rtl() ) {
		wp_enqueue_style('emkb-admin-plugin-pages-rtl', Echo_Multiple_Knowledge_Bases::$plugin_url . 'css/admin-plugin-pages-rtl' . $suffix . '.css', array(), Echo_Multiple_Knowledge_Bases::$version );
	}
	
	wp_enqueue_style( 'wp-color-picker' ); //Color picker

	wp_enqueue_script('emkb-admin-plugin-pages-scripts', Echo_Multiple_Knowledge_Bases::$plugin_url . 'js/admin-plugin-pages' . $suffix . '.js',
					array('jquery', 'jquery-ui-core','jquery-ui-dialog','jquery-effects-core','jquery-effects-bounce', 'jquery-ui-sortable'), Echo_Multiple_Knowledge_Bases::$version );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}
