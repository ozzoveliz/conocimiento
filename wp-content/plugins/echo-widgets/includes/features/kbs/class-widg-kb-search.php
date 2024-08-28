<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Search Knowledge Base
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class WIDG_KB_Search {
	
	public function __construct() {
		add_action( 'wp_ajax_widg-search-kb', array($this, 'search_kb') );
 		add_action( 'wp_ajax_nopriv_widg-search-kb', array($this, 'search_kb') );
	}

	/**
	 * Process AJAX search request
	 */
	public function search_kb() {

		// we don't need nonce and permission check here

		$kb_id = WIDG_Utilities::sanitize_get_id( $_GET['widg_kb_id'] );
		if ( is_wp_error( $kb_id ) ) {
			wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => esc_html__( 'Error occurred. Please try again later.', 'echo-widgets' ) ) ) );
		}

		$min_search_word_size_msg = WIDG_Utilities::get_kb_option( $kb_id, 'min_search_word_size_msg', __( 'Enter a word with at least one character.', 'echo-widgets' ), false );
		$no_results_found_msg = WIDG_Utilities::get_kb_option( $kb_id, 'no_results_found', __( 'No matches found', 'echo-widgets' ), false );
		$search_results_msg = WIDG_Utilities::get_kb_option( $kb_id, 'search_results_msg', __( 'Search Results for', 'echo-widgets' ), false );

		// get number of needed articles
		$search_results_limit = isset( $_GET['search_results_limit'] ) ? intval( $_GET['search_results_limit'] ) : 0;
		if ( ! $search_results_limit ) {
			$search_results_limit = widg_get_instance()->kb_config_obj->get_value( $kb_id, 'widg_search_results_limit', 8 );
		}

		// require minimum size of search word(s)
		$search_terms = isset($_GET['search_words']) ? sanitize_text_field( trim($_GET['search_words']) ) : '';
		$search_terms = str_replace('?', '', $search_terms);

		// require minimum size of search word(s)
		if ( empty($search_terms) ) {
			wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => esc_html( $min_search_word_size_msg ) ) ) );
		}

		// search for given keyword(s)
		$result = $this->execute_search( $search_terms, $kb_id, $search_results_limit );

		if ( empty($result) ) {
			$search_result  = '<div class="widg-search-results-content">';
			$search_result .= '<span class="widg-no-search-results">' . $no_results_found_msg . '</span>';
			$search_result .= '</div>';

		} else {
			$search_result = '<div class="widg-search-results-content">';
			$search_result .= '<div class="widg-search-results-message">' . esc_html( $search_results_msg ) . ': <strong>' . $search_terms . '</strong></div>';
			$search_result .= '<ul>';
			foreach( $result as $row ) {
				$article_url = get_permalink( $row->ID );
				if ( empty($article_url) || is_wp_error( $article_url )) {
					continue;
				}

				$search_result .=
					'<li>' .
						'<a href="' .  esc_url( $article_url ) . '" class="widg-ajax-search" data-kb-article-id="' . $row->ID . '">' .
							'<span class="widg-article-title">' .
								'<i class="ep_font_icon_document"></i>' .
								'<span>'.esc_html($row->post_title).'</span>' .
							'</span>' .
						'</a>' .
					'</li>';
			}
			$search_result .= '</ul>';
			$search_result .= '</div>';
		}

		// Advanced Search Log
		do_action( "asea_add_search_log", $search_terms, '', count($result), 'widgets', $kb_id );

		// we are done here
		wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => $search_result ) ) );
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $search_terms
	 * @param $kb_id
	 * @param int $nof_articles
	 * @return array
	 */
	private function execute_search( $search_terms, $kb_id, $nof_articles = 8 ) {

		$post_status_search = class_exists('AM'.'GR_Access_Utilities', false) ? array('publish', 'private') : array('publish');

		$result = array();
		$search_params = array(
				's' => $search_terms,
				'post_type' => WIDG_KB_Handler::get_post_type( $kb_id ),
				'post_status' => $post_status_search,
				'ignore_sticky_posts' => true,  // sticky posts will not show at the top
				'posts_per_page' => WIDG_Utilities::is_amag_on( true ) ? -1 : $nof_articles,         // limit search results
				'no_found_rows' => true,        // query only posts_per_page rather than finding total nof posts for pagination etc.
				'cache_results' => false,       // don't need that for mostly unique searches
				'orderby' => 'relevance'
		);

		$found_posts = new WP_Query( $search_params );
		if ( ! empty($found_posts->posts) ) {
			$result = $found_posts->posts;
			wp_reset_postdata();
		}

		// limit the number of articles per widget parameter
		if ( WIDG_Utilities::is_amag_on( true ) && count($result) > $nof_articles ) {
			$result = array_splice($result, 0, $nof_articles);
		}

		return $result;
	}

}
