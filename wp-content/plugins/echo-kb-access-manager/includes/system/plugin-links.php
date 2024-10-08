<?php

/**
 * Setup links and information on Plugins WordPress page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */


/**
 * Adds various links for plugin on the Plugins page displayed on the left
 *
 * @param   array $links contains current links for this plugin
 * @return  array returns an array of links
 */
function epkb_add_plugin_action_links ( $links ) {
	$my_links = array(
			__( 'Configuration', 'echo-knowledge-base' )    => '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::KB_POST_TYPE_PREFIX . '1&page=epkb-kb-configuration' ) ) . '">' . esc_html__( 'Configuration', 'echo-knowledge-base' ) . '</a>',
			__( 'Support', 'echo-knowledge-base' )          => '<a href="https://www.echoknowledgebase.com/contact-us/?inquiry-type=technical" target="_blank">' . esc_html__( 'Support', 'echo-knowledge-base' ) . '</a>'
	);

	return array_merge( $my_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( Echo_Knowledge_Base::$plugin_file ), 'epkb_add_plugin_action_links', 10, 2 );

/**
 * Add info about plugin on the Plugins page displayed on the right.
 *
 * @param $links
 * @param $file
 * @return array
 */
function epkb_add_plugin_row_meta( $links, $file ) {
	if ( $file != 'echo-kb-access-manager/echo-kb-access-manager.php' ) {
		return $links;
	}

	$links[] = '<a href="https://www.echoknowledgebase.com/documentation/kb-visual-editor/" target="_blank">' . esc_html__( 'Get Started', 'echo-knowledge-base' ) . '</a>';
	$links[] = '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . EPKB_KB_Handler::KB_POST_TYPE_PREFIX . '1&page=epkb-new-features' ) ) . '">' . esc_html__( "What's New", 'echo-knowledge-base' ) . '</a>';

	return $links;
}
// add_filter( 'plugin_row_meta', 'epkb_add_plugin_row_meta', 10, 2 );
