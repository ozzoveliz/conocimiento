<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Search Knowledge Base
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_KB_Search {

	public function __construct() {
		add_action( 'wp_ajax_elay-search-kb', array($this, 'search_kb') );
		add_action( 'wp_ajax_nopriv_elay-search-kb', array($this, 'search_kb') );
	}

	/**
	 * Process AJAX search request
	 */
	public function search_kb() {

		// we don't need nonce and permission check here

		$kb_id = ELAY_Utilities::sanitize_get_id( $_GET['elay_kb_id'] );
		if ( is_wp_error( $kb_id ) ) {
			wp_die( json_encode( array( 'status' => 'success', 'search_result' => esc_html__( 'Error occurred. Please try again later.', 'echo-elegant-layouts' ) ) ) );
		}

		$kb_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );
		$search_box_results_style = ELAY_KB_Core::get_value( $kb_id, 'search_box_results_style', 'off' );

		$prefix = ELAY_Utilities::get('is_sidebar_layout') == "true" ? 'sidebar_' : 'grid_';

		// remove question marks
		$search_terms = ELAY_Utilities::get( 'search_words' );
		$search_terms = stripslashes( $search_terms );
		$search_terms = str_replace('?', '', $search_terms);
		$search_terms = str_replace( array( "\r", "\n" ), '', $search_terms );

		// require minimum size of search word(s)
		if ( empty($search_terms) ) {
			wp_die( json_encode( array( 'status' => 'success', 'search_result' => esc_html( $kb_config[$prefix . 'min_search_word_size_msg'] ) ) ) );
		}

		// search for given keyword(s)
		$result = $this->execute_search( $kb_id, $search_terms );

		if ( empty($result) ) {
			$search_result = $kb_config[$prefix . 'no_results_found'];

		} else {
			// ensure that links have https if the current schema is https
			set_current_screen('front');

			$search_result = '<div>' . esc_html( $kb_config[$prefix . 'search_results_msg'] ) . ' ' . $search_terms . '</div>';
			$search_result .= '<ul>';

			$title_style = '';
			$icon_style  = '';
			if ( $kb_config['sidebar_search_box_results_style'] == 'on' || $search_box_results_style == 'on' ) {
				$title_style = ELAY_Utilities::get_inline_style( 'color:: sidebar_article_font_color' , $kb_config);
				$icon_style = ELAY_Utilities::get_inline_style( 'color:: sidebar_article_icon_color' , $kb_config);
			}

			// display one line for each search result
			foreach( $result as $post ) {

				$article_url = get_permalink( $post->ID );
				if ( empty($article_url) || is_wp_error( $article_url )) {
					continue;
				}

				// linked articles have their own icon
				$article_title_icon = 'ep_font_icon_document';
				if ( has_filter( 'eckb_single_article_filter' ) ) {
					$article_title_icon = apply_filters( 'eckb_article_icon_filter', $article_title_icon, $post->ID );
					$article_title_icon = empty( $article_title_icon ) ? 'epkbfa-file-text-o' : $article_title_icon;
				}

				$search_result .=
					'<li>' .
						'<a href="' .  esc_url( $article_url ) . '" class="elay-ajax-search" data-kb-article-id="' . $post->ID . '">' .
							'<span class="elay-article-title" ' . $title_style . '>' .
	                            '<i class="eckb-article-title-icon epkbfa ' . esc_attr($article_title_icon) . ' ' . $icon_style . '"></i>' .
								'<span>' . esc_html($post->post_title) . '</span>' .
							'</span>' .
						'</a>' .
					'</li>';
			}
			$search_result .= '</ul>';
		}

		// we are done here
		wp_die( json_encode( array( 'status' => 'success', 'search_result' => $search_result ) ) );
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $search_terms
	 * @return array
	 */
	private function execute_search( $kb_id, $search_terms ) {

		// add-ons can adjust the search
		if ( has_filter( 'eckb_execute_search_filter' ) ) {
			$result = apply_filters('eckb_execute_search_filter', '', $kb_id, $search_terms );
			if ( is_array($result) ) {
				return $result;
			}
		}
		
		// TODO replace with filter above
		$post_status_search = class_exists('AM'.'GR_Access_Utilities', false) ? array('publish', 'private') : array('publish');

		$result = array();
		$search_params = array(
				's' => $search_terms,
				'post_type' => ELAY_KB_Handler::get_post_type( $kb_id ),
				'post_status' => $post_status_search,
				'ignore_sticky_posts' => true,  // sticky posts will not show at the top
				'posts_per_page' => 20,         // limit search results
				'no_found_rows' => true,        // query only posts_per_page rather than finding total nof posts for pagination etc.
				'cache_results' => false,       // don't need that for mostly unique searches
				'orderby' => 'relevance'
		);

		$found_posts_obj = new WP_Query( $search_params );
		if ( ! empty($found_posts_obj->posts) ) {
			$result = $found_posts_obj->posts;
			wp_reset_postdata();
		}

		return $result;
	}
}
