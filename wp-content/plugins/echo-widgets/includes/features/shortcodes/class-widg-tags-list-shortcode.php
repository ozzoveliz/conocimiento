<?php

/**
 * Shortcode - List of Tags
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Tags_List_Shortcode {

	public function __construct() {
		add_shortcode( 'widg-tags-list', array( $this, 'output_shortcode' ) );
	}

	public function output_shortcode( $attributes ) {
		global $eckb_kb_id;

		widg_load_public_resources_enqueue();
		
        // allows to adjust the widget title
        $title = empty( $attributes['title'])  ? '' : strip_tags( trim( $attributes['title'] ) );
        $title = empty( $title ) ? __( 'Tags', 'echo-widgets' ) : $title;

        // get add-on configuration
        $kb_id = empty( $attributes['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $result = $this->execute_search( $kb_id );

        $css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY TAGS
        ob_start();

		echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-shortcode-tags-container">';
			echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . '  widg-shortcode-tags-contents">';

	        echo '<h4>' . esc_html( $title ) . '</h4>';

	        if ( empty($result) ) {
	            echo esc_html__( 'Coming Soon', 'echo-widgets' );
	        } else {
	            echo '<ul>';
	            foreach( $result as $tag ) {
	                $tag_url = get_term_link( $tag );
	                if ( empty($tag_url) || is_wp_error( $tag_url )) {
	                    continue;
	                }

	                echo
	                    '<li>' .
	                        '<a href="' .  esc_url( $tag_url ) . '">' .
	                            '<span class="widg-tag-title">' .
	                                '<span>' . esc_html( $tag->name ) . '</span>' .
                                '</span>' .
	                        '</a>' .
	                    '</li>';
	            }
	            echo '</ul>';
	        }

			echo '</div>'; //widg-shortcode-categories-contents
		echo '</nav>';// widg-shortcode-tags-container

        return ob_get_clean();
    }

    /**
     * Call WP query to get matching terms (any term OR match)
     *
     * @param $kb_id
     * @return array
     */
    private function execute_search( $kb_id ) {

        if ( ! WIDG_Utilities::is_positive_int( $kb_id ) ) {
            WIDG_Logging::add_log( 'Invalid kb id', $kb_id );
            return array();
        }

        $args = array(
            'parent'        => 0,
            'orderby'       => 'name',
            'hide_empty'    => false  // if 'hide_empty' then do not return tags with no articles
        );

        $terms = get_terms( WIDG_KB_Handler::get_tag_taxonomy_name( $kb_id ), $args );
        if ( is_wp_error( $terms ) ) {
            WIDG_Logging::add_log( 'cannot get terms for kb_id', $kb_id, $terms );
            return array();
        } else if ( empty($terms) || ! is_array($terms) ) {
            return array();
        }

        return array_values($terms);   // rearrange array keys
    }
}
