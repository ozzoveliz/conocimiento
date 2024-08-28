<?php

/**
 * Widget - Recent Articles
 *
 * @copyright   Copyright (c) 2023, Echo Plugins
 * @license	 http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Recent_Articles_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'widg_recent_articles', 'Echo KB - ' . __( 'Recent Articles', 'echo-widgets' ),
			array(
				'description' => __( 'Displays a list of recently created or modified articles.', 'echo-widgets' )
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
        $title = empty($title) ? __( 'Recent Articles', 'echo-widgets' ) : $title;

		// get add-on configuration
		$kb_id = empty( $instance['kb_id'] ) ? ( empty( $eckb_kb_id ) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $instance['kb_id'];

		$kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );
		$add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

		$nof_articles = empty( $instance['number_of_articles'] ) ? 5 : WIDG_Utilities::sanitize_int( $instance['number_of_articles'], 5 );
		$orderby = empty( $instance['order_by'] ) || $instance['order_by'] == 'date created' ? 'date' : ( $instance['order_by'] == 'date modified' ? 'modified' : 'title' );

		$result = $this->execute_search( $kb_id, $nof_articles, $orderby );

		$css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
		$css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

		// DISPLAY RECENT ARTICLES
		echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-widget-article-container">';
			echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . ' widg-widget-article-contents">';

				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];

				if ( empty($result) ) {
					echo esc_html__( 'Coming Soon', 'echo-widgets' );
				} else {
					echo '<ul>';

					foreach( $result as $article ) {

						$article_url = get_permalink( $article->ID );
						if ( empty($article_url) || is_wp_error( $article_url ) ) {
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
			echo '</div>'; //widg-widget-article-contents
		echo '</nav>'; //widg-widget-article-container

		// theme-specific HTML that surrounds this widget
		echo $args['after_widget'];
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $nof_articles
	 * @return array
	 */
	private function execute_search( $kb_id, $nof_articles, $orderby ) {

		$post_status_search = WIDG_Utilities::is_amag_on( true ) ? array( 'publish', 'private' ) : array( 'publish' );

		$result = array();
		$order = $orderby == 'title' ? 'ASC' : 'DESC';
		$search_params = array(
			'post_type'           => WIDG_KB_Handler::get_post_type( $kb_id ),
			'post_status'         => $post_status_search,
			'ignore_sticky_posts' => true,      // sticky posts will not show at the top
			'posts_per_page'      => WIDG_Utilities::is_amag_on( true ) ? - 1 : $nof_articles,  // limit search results
			'no_found_rows'       => true,            // query only posts_per_page rather than finding total nof posts for pagination etc.
			'orderby' => $orderby,
			'order'   => $order
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

	/**
	 * Shows widget form to collect its parameters.
	 * @see WP_Widget::form
	 *
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {

		// Set up some default widget settings.
		$defaults = array(
			'title'         => __( 'Recent Articles', 'echo-widgets' ),
			'kb_id'              => WIDG_KB_Config_DB::DEFAULT_KB_ID,
			'number_of_articles' => 5,
			'order_by'       => 'date created',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );	  ?>
		
		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- KB ID -->		<?php
		if ( defined( 'EM'.'KB_PLUGIN_NAME' ) ) {	?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"><?php _e( 'KB ID:', 'echo-widgets' ) ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'kb_id' ) ); ?>" type="text" value="<?php echo $instance['kb_id']; ?>" />
			</p>			<?php
		}   ?>

		<!-- Number of articles -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number_of_articles' ) ); ?>"><?php _e( 'Number of Articles to Display:', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_of_articles' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'number_of_articles' ) ); ?>" type="text" value="<?php echo $instance['number_of_articles']; ?>" />
		</p>

		<!-- Order By -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>"><?php _e( 'Order By:', 'echo-widgets' ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'order_by' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>">    <?php
				foreach ( array( 'date modified', 'date created', 'title' ) as $order ) { ?>
					<option <?php selected( $instance['order_by'], $order ); ?> value="<?php echo esc_attr( $order ); ?>"><?php 
						
						if ( $order == 'date modified' ) {
							_e('Date modified',  'echo-widgets' );
						} else if ( $order == 'date created' ) {
							_e('Date created',  'echo-widgets' );
						} else {
							echo ucfirst( $order ); 
						}	?>
					</option>                <?php
				} ?>
			</select>
		</p> <?php
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

		$instance['title'] = strip_tags( trim( $new_instance['title'] ) );
		$instance['kb_id'] = isset( $new_instance['kb_id'] ) ? strip_tags( $new_instance['kb_id'] ) : WIDG_KB_Config_DB::DEFAULT_KB_ID;
		$instance['number_of_articles'] = isset( $new_instance['number_of_articles'] ) ? strip_tags( trim( $new_instance['number_of_articles'] ) ) : 5;
		$instance['order_by']      = isset( $new_instance['order_by'] ) ? strip_tags( trim($new_instance['order_by']) ) : 'date';

		return $instance;
	}
}
