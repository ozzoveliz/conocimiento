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
function eprf_add_plugin_action_links ( $links ) {
	$my_links = array(
		__( 'Documentation', 'echo-knowledge-base' )      => '<a href="https://www.echoknowledgebase.com/documentation/?top-category=add-ons" target="_blank">' . esc_html__( 'Docs', 'echo-knowledge-base' ) . '</a>',
		__( 'Support', 'echo-knowledge-base' )            => '<a href="https://www.echoknowledgebase.com/contact-us/?inquiry-type=technical">' . esc_html__( 'Support', 'echo-knowledge-base' ) . '</a>'
	);

	return array_merge( $my_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename(Echo_Article_Rating_And_Feedback::$plugin_file), 'eprf_add_plugin_action_links' , 10, 2 );

/**
 * Add info about plugin on the Plugins page displayed on the right.
 *
 * @param $links
 * @param $file
 * @return array
 */
function eprf_add_plugin_row_meta($links, $file) {
	if ( $file != 'echo-article-rating-and-feedback/echo-article-rating-and-feedback.php' ) {
		return $links;
	}

	//TODO $links[] = '<a href="https://www.echoknowledgebase.com/kb-updates-for-access-manager-search/" target="_blank">' . esc_html__( "What's New", 'echo-knowledge-base' ) . '</a>';

	return $links;
}
add_filter( 'plugin_row_meta', 'eprf_add_plugin_row_meta', 10, 2 );
