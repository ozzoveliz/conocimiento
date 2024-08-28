<?php

/**
 * Shortcode - Popular articles
 *
 * @copyright   Copyright (c) 2023, Echo Plugins
 * @license	 http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Popular_Articles_Shortcode {

	public function __construct() {
		add_shortcode( 'widg-popular-articles', array( $this, 'output_shortcode' ) );
	}

	public function output_shortcode( $attributes ) {
		global $eckb_kb_id;

		widg_load_public_resources_enqueue();

		// allows to adjust the widget title
		$title = empty( $attributes['title'] ) ? '' : strip_tags( trim( $attributes['title'] ) );
		$title = empty( $title ) ? __( 'Popular Articles', 'echo-widgets' ) : $title;

		// get add-on configuration
		$kb_id = empty( $attributes['kb_id'] ) ? ( empty( $eckb_kb_id ) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
		$kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

		$add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

		$nof_articles = empty( $attributes['number_of_articles'] ) ? 5 : WIDG_Utilities::sanitize_int( $attributes['number_of_articles'], 5 );

		$result = $this->execute_search( $kb_id, $nof_articles );

		$css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
		$css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

		// DISPLAY POPULAR ARTICLES
		ob_start();

		echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-shortcode-article-container">';
			echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . ' widg-shortcode-article-contents">';

			echo '<h4>' . esc_html( $title ) . '</h4>';

				if ( empty( $result ) ) {
					echo esc_html__( 'Coming Soon', 'echo-widgets' );
				} else {
					echo '<ul>';
					foreach( $result as $article ) {

						$article_url = get_permalink( $article->ID );
						if ( empty( $article_url ) || is_wp_error( $article_url ) ) {
							continue;
						}

						// get article icon filter if applicable
						$article_title_icon = '<i class="widg-shortcode-article-icon ep_font_icon_document"></i>';
						if ( has_filter( 'eckb_single_article_filter' ) ) {
							$article_title_icon_filter = apply_filters( 'eckb_article_icon_filter', '', $article->ID );
							$article_title_icon = empty( $article_title_icon_filter ) ? $article_title_icon : '<i class="widg-shortcode-article-icon epkbfa ' . $article_title_icon_filter . '"></i>';
						}

						echo
							'<li>' .
								'<a href="' .  esc_url( $article_url ) . '">' .
									'<span class="widg-article-title">' .
										$article_title_icon .
										'<span>' . esc_html( $article->post_title ) . '</span>' .
									'</span>' .
								'</a>' .
							'</li>';
					}
					echo '</ul>';
				}

			echo '</div>'; //widg-shortcode-article-contents
		echo '</nav>'; //widg-shortcode-article-container

		return ob_get_clean();
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $nof_articles
	 * @return array
	 */
	private function execute_search( $kb_id, $nof_articles ) {

		$post_status_search = WIDG_Utilities::is_amag_on( true ) ? array( 'publish', 'private' ) : array( 'publish' );

		$result = array();
		$search_params = array(
			'post_type'           => WIDG_KB_Handler::get_post_type( $kb_id ),
			'post_status'         => $post_status_search,
			'ignore_sticky_posts' => true, // sticky posts will not show at the top
			'posts_per_page'      => WIDG_Utilities::is_amag_on( true ) ? - 1 : $nof_articles, // limit search results
			'no_found_rows'       => true, // query only posts_per_page rather than finding total nof posts for pagination etc.
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'meta_query'          => array(
				'relation' => 'OR',
				array(
					'key'     => 'epkb-article-views',
					'type'    => 'numeric',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'epkb-article-views',
					'type'    => 'numeric',
					'compare' => 'EXISTS',
				),
			),
		);

		$found_posts = new WP_Query( $search_params );
		if ( ! empty( $found_posts->posts ) ) {
			$result = $found_posts->posts;
			wp_reset_postdata();
		}

		// limit the number of articles per widget parameter
		if ( WIDG_Utilities::is_amag_on( true ) && count( $result ) > $nof_articles ) {
			$result = array_splice( $result, 0, $nof_articles );
		}

		return $result;
	}
}