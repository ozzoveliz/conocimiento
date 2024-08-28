<?php

/**
 * Shortcode - articles belonging to given categories
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Category_Articles_Shortcode {

	public function __construct() {
		add_shortcode( 'widg-category-articles', array( $this, 'output_shortcode' ) );
	}

	/**
	 * Output the shortcode content.
	 *
	 * @param array $attributes
	 * @return string
	 */
	public function output_shortcode( $attributes ) {
		global $eckb_kb_id;

		widg_load_public_resources_enqueue();

        // allows to adjust the widget title
        $title = empty( $attributes['title'] ) ? '' : strip_tags( trim( $attributes['title'] ) );
        $title = empty( $title ) ? __( 'Category Articles', 'echo-widgets' ) : $title;

        // get add-on configuration
		$kb_id = empty( $attributes['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $nof_articles = empty( $attributes['number_of_articles'] ) ? 5 : WIDG_Utilities::sanitize_int( $attributes['number_of_articles'], 5 );
		$order_by = empty( $attributes['order_by'] ) || $attributes['order_by'] == 'date created' ? 'date' : ( $attributes['order_by'] == 'date modified' ? 'modified' : 'title' );
		
		$include_children = ! empty( $attributes['include_children'] ) && $attributes['include_children'] == 'on';
		
		$category_ids = empty( $attributes['category_ids'] ) ? '' : WIDG_Utilities::sanitize_comma_separated_ints( $attributes['category_ids'] );
		$articles = $this->get_articles( $kb_id, $category_ids, $nof_articles, $order_by, $include_children );

        $css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY CATEGORY ARTICLES
        ob_start();

		echo '<nav role="navigation"' . ( $title == 'empty' ? '' : ' aria-label="' . esc_attr( $title ) . '"' ) . ' class="widg-shortcode-article-container">';
			echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . ' widg-shortcode-article-contents">';

		        echo $title == 'empty' ? '' : '<h4>' . esc_html( $title ) . '</h4>';

		        if ( empty($articles) ) {
		            echo esc_html__( 'Coming Soon', 'echo-widgets' );

		        } else {

		            echo '<ul>';
				        foreach( $articles as $article ) {

			                $article_url = get_permalink( $article->ID );
			                if ( empty($article_url) || is_wp_error( $article_url )) {
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

			echo '</div>';
		echo '</nav>';

        return ob_get_clean();
    }

	/**
	 * Retrieve all articles for all listed categories.
	 *
	 * @param $kb_id
	 * @param $in_category_ids
	 * @param $nof_articles
	 * @param $order_by
	 * @param $include_children
	 * @return array
	 */
    private function get_articles( $kb_id, $in_category_ids, $nof_articles, $order_by, $include_children ) {

		$articles = array();
	    if ( empty($in_category_ids) ) {
			return $articles;
	    }

	    // get articles for each category
	    $category_ids = array();
	    foreach( explode(',', $in_category_ids) as $category_id ) {

		    if ( WIDG_Utilities::is_positive_int( $category_id) ) {
			    $category_ids[] = $category_id;
		    }
	    }

	    if ( empty($category_ids) ) {
	    	return $articles;
	    }

	    return $this->execute_search( $kb_id, $nof_articles, $category_ids, $order_by, $include_children );
    }

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $nof_articles
	 * @param $sub_or_category_ids
	 * @param $order_by
	 * @param $include_children
	 * @return array
	 */
    private function execute_search( $kb_id, $nof_articles, $sub_or_category_ids, $order_by, $include_children ) {
	    if ( ! WIDG_Utilities::is_positive_int( $kb_id ) ) {
		    WIDG_Logging::add_log( 'Invalid kb id', $kb_id );
		    return array();
	    }

	    $order = $order_by == 'title' ? 'ASC' : 'DESC';
	    $post_status_search = class_exists('AM'.'GR_Access_Utilities', false) ? array('publish', 'private') : array('publish');

	    $query_args = array(
		    'post_type' => WIDG_KB_Handler::get_post_type( $kb_id ),
		    'post_status' => $post_status_search,
		    'posts_per_page' => $nof_articles,
		    'orderby' => $order_by,
		    'order'=> $order,
		    'tax_query' => array(
			    array(
				    'taxonomy' => WIDG_KB_Handler::get_category_taxonomy_name( $kb_id ),
				    'terms' => $sub_or_category_ids,
				    'include_children' => $include_children // Remove if you need posts with child terms
			    )
		    )
	    );

	    return get_posts( $query_args );  /** @secure 02.17 */
    }
}
