<?php

/**
 * Widget - List of Tags
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Tags_List_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'widg_tags_list', 'Echo KB - ' . __( 'Tags', 'echo-widgets' ),
			array( 
				'description' => __( 'Displays a list of KB tags.', 'echo-widgets' )
            )
		);
	}

    /** 
     * Output the widget content.
     * @see WP_Widget::widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
	    global $eckb_kb_id;

		widg_load_public_resources_enqueue();
		
        // theme-specific HTML that surrounds this widget
        echo $args['before_widget'];

        // allows to adjust the widget title
		$instance_title = empty( $instance['title'] ) ? '' : $instance['title'];
		$instance_widget_id = empty( $instance['widget_id'] ) ? '' : $instance['widget_id'];
		$title = apply_filters( 'widget_title', $instance_title, $instance_widget_id );
        $title = empty($title) ? __( 'Tags', 'echo-widgets' ) : $title;

        // get add-on configuration
        $kb_id = empty( $instance['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $instance['kb_id'];
		
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );
        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $result = $this->execute_search( $kb_id );

        $css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY TAGS
        echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-widget-tags-container">';
            echo '<div class="' . $css_reset . ' ' . $css_default . '  widg-widget-tags-contents">';

                echo $args['before_title'] . esc_html(  $title ) . $args['after_title'];

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
            echo '</div>'; //widg-widget-categories-contents
        echo '</nav>';// widg-widget-tags-container

        // theme-specific HTML that surrounds this widget
        echo $args['after_widget'];
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

    /**
     * Shows widget form to collect its parameters.
     * @see WP_Widget::form
     *
     * @param array $instance
     * @return string|void
     */
    public function form( $instance ) {

        // Set up some default widget settings.
        $defaults = array(
            'title'         => __( 'KB Tags', 'echo-widgets' ),
            'kb_id'         => WIDG_KB_Config_DB::DEFAULT_KB_ID,
        );

        $instance = wp_parse_args( (array) $instance, $defaults );      ?>
        
        <!-- Title -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'echo-widgets' ) ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>

        <!-- KB ID -->
        <?php if ( defined( 'EM'.'KB_PLUGIN_NAME' ) ) {    ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"><?php _e( 'KB ID:', 'echo-widgets' ) ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'kb_id' ) ); ?>" type="text" value="<?php echo $instance['kb_id']; ?>" />
            </p>            <?php
        }   ?>

        <?php
    }

    /**
     * Process widget form input when user saves.
     * @see WP_Widget::update
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['title']        = strip_tags( trim($new_instance['title']) );
        $instance['kb_id']        = isset( $new_instance['kb_id'] ) ? strip_tags($new_instance['kb_id']) : WIDG_KB_Config_DB::DEFAULT_KB_ID;

        return $instance;
    }
}
