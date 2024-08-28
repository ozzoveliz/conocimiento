<?php

/**
 * Widget - Articles for given categories
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Category_Articles_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'widg_category_articles', 'Echo KB - ' . __( 'Category Articles', 'echo-widgets' ),
			array( 
				'description' => __( 'Displays a list of articles for specific categories.', 'echo-widgets' )
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
        $title = empty($title) ? __( 'Category Articles', 'echo-widgets' ) : $title;

        // get add-on configuration
        $kb_id = empty( $instance['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $instance['kb_id'];
		
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );
        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $nof_articles = empty( $instance['number_of_articles'] ) ? 5 : WIDG_Utilities::sanitize_int($instance['number_of_articles'], 5);
		$order_by = empty($instance['order_by']) || $instance['order_by'] == 'date created' ? 'date' : ( $instance['order_by'] == 'date modified' ? 'modified' : 'title' );
		
		$include_children = ! empty($instance['include_children']);
		
		$category_ids = empty( $instance['category_ids'] ) ? '' : WIDG_Utilities::sanitize_comma_separated_ints( $instance['category_ids'] );
		$articles = $this->get_articles( $kb_id, $category_ids, $nof_articles, $order_by, $include_children );

        $css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY CATEGORY ARTICLES
        echo '<nav role="navigation"' . ( $title == 'empty' ? '' : ' aria-label="' . esc_attr( $title ) . '"' ) . ' class="widg-widget-article-container">';
            echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . ' widg-widget-article-contents">';

		        echo $title == 'empty' ? '' : $args['before_title'] . esc_html( $title ) . $args['after_title'];

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
							$article_title_icon = '<i class="widg-widget-article-icon ep_font_icon_document"></i>';
							if ( has_filter( 'eckb_single_article_filter' ) ) {
								$article_title_icon_filter = apply_filters( 'eckb_article_icon_filter', '', $article->ID );
								$article_title_icon = empty( $article_title_icon_filter ) ? $article_title_icon : '<i class="widg-widget-article-icon epkbfa ' . $article_title_icon_filter . '"></i>';
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

        // theme-specific HTML that surrounds this widget
        echo $args['after_widget'];
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
            'title'         => __( 'Category Articles', 'echo-widgets' ),
            'kb_id'         => WIDG_KB_Config_DB::DEFAULT_KB_ID,
            'number_of_articles'  => 5,
            'order_by'       => 'date created',
	        'category_ids'   => '',
			'include_children' => ''
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

        <!-- Number of articles -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'number_of_articles' ) ); ?>"><?php _e( 'Number of Articles to Display:', 'echo-widgets' ) ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_of_articles' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'number_of_articles' ) ); ?>" type="text" value="<?php echo $instance['number_of_articles']; ?>" />
        </p>

        <!-- order By -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>"><?php _e( 'Order By:', 'echo-widgets' ); ?></label>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'order_by' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>">
                <?php foreach ( array( 'date modified', 'date created', 'title' ) as $order ) { ?>
                    <option <?php selected( $instance['order_by'], $order ); ?> value="<?php echo esc_attr( $order ); ?>"><?php 
						
						if ( $order == 'date modified' ) {
							_e('Date modified',  'echo-widgets' );
						} else if ( $order == 'date created' ) {
							_e('Date created',  'echo-widgets' );
						} else {
							echo ucfirst( $order ); 
						}
						
					?></option>
                <?php } ?>
            </select>
        </p>

	    <!-- Category IDs -->
	    <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'category_ids' ) ); ?>"><?php _e( 'Category ID(s):', 'echo-widgets' ) ?></label>
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category_ids' ) ); ?>" type="text" value="<?php echo $instance['category_ids']; ?>" />
	    </p>            
		
		<!-- Include Children categories -->
	    <p>
		    <label for="<?php echo esc_attr( $this->get_field_id( 'include_children' ) ); ?>"><?php _e( 'Include Articles in Sub Categories:', 'echo-widgets' ) ?></label>
		    <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'include_children' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'include_children' ) ); ?>" type="checkbox" <?php echo checked($instance['include_children'], 'on'); ?> />
	    </p>	
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
        $instance['number_of_articles'] = isset( $new_instance['number_of_articles'] ) ? strip_tags( trim($new_instance['number_of_articles']) ) : 5;
        $instance['order_by']      = isset( $new_instance['order_by'] ) ? strip_tags( trim($new_instance['order_by']) ) : 'date';
	    $instance['category_ids']  = isset( $new_instance['category_ids'] ) ? strip_tags( trim($new_instance['category_ids']) ) : '';
		$instance['include_children']  = isset( $new_instance['include_children'] ) ? strip_tags( trim($new_instance['include_children']) ) : '';

	    return $instance;
    }
}
