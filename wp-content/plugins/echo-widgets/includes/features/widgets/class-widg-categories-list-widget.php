<?php

/**
 * Widget - List of Categories (top level, all, certain IDs)
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Categories_List_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'widg_categories_list', 'Echo KB - ' . __( 'Categories', 'echo-widgets' ),
			array(
				'description' => __( 'Displays a list of top KB categories.', 'echo-widgets' )
			)
		);

		// include widget resources if in use
		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_enqueue_scripts', 'widg_load_public_resources_now' );
		}
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
		$instance_widget_id = empty( $instance['widget_id'] ) ? '' : WIDG_Utilities::sanitize_int( $instance['widget_id'] );

		$title = apply_filters( 'widget_title', $instance_title, $instance_widget_id );
		$title = empty( $title ) ? __( 'Categories', 'echo-widgets' ) : $title;

		// get add-on configuration
		$kb_id = empty( $instance['kb_id'] ) ? ( empty( $eckb_kb_id ) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $instance['kb_id'];
		$kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

		$add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

		$filter = empty( $instance['filter'] ) || $instance['filter'] == 'top' ? 'top' : 'all';

		$order_by = 'name';
		if ( ! empty( $instance['order_by'] ) && $instance['order_by'] == 'id' ) {
			$order_by = 'id';
		}

		$terms = $this->execute_search( $kb_id, $filter, $this->retrieve_category_ids( $instance ), $order_by );

		$css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
		$css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

	    // DISPLAY CATEGORIES (top level, all, certain IDs)
	    echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-widget-categories-container">';
	        echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . '  widg-widget-categories-contents">';

	        echo $args['before_title'] . esc_html( $title ) . $args['after_title'];

	        if ( empty( $terms ) ) {
	            echo esc_html__( 'Coming Soon', 'echo-widgets' );
	        } else {
		        
	            echo '<ul>';
			
	            foreach( $terms as $category ) {
					
	                $category_url = get_term_link( $category );
	                if ( empty( $category_url ) || is_wp_error( $category_url )) {
	                    continue;
	                }

	                 echo
	                     '<li class="widg-widget-category__level_' . $category->level . '">' .
	                        '<a href="' .  esc_url( $category_url ) . '">' .
	                            '<span class="widg-category-title">' .
	                                '<span>' . esc_html($category->name) . '</span>' .
	                            '</span>' .
	                        '</a>' .
	                    '</li>';
	            }
	            echo '</ul>';
	        }

			echo '</div>'; //widg-widget-categories-contents
        echo '</nav>'; //widg-widget-categories-container

        // theme-specific HTML that surrounds this widget
        echo $args['after_widget'];
    }

	private function retrieve_category_ids( $instance ) {

		$in_category_ids = empty( $instance['category_ids'] ) ? '' : WIDG_Utilities::sanitize_comma_separated_ints( $instance['category_ids'] );

		// get articles for each category
		$category_ids = array();
		foreach ( explode( ',', $in_category_ids ) as $category_id ) {

			if ( WIDG_Utilities::is_positive_int( $category_id ) ) {
				$category_ids[] = $category_id;
			}
		}

		return $category_ids;
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $filter
	 * @param string $category_ids
	 * @param string $order_by
	 * @return array
	 */
	private function execute_search( $kb_id, $filter, $category_ids = '', $order_by = 'name' ) {

		if ( ! WIDG_Utilities::is_positive_int( $kb_id ) ) {
			WIDG_Logging::add_log( 'Invalid kb id', $kb_id );
			return array();
		}

		if ( empty( $category_ids ) ) {
			if ( $filter == 'all' ) {
				$args = array(
					'orderby'    => $order_by,
					'hide_empty' => false  // if 'hide_empty' then do not return categories with no articles
				);
			} else {
				$args = array(
					'parent'     => 0,
					'orderby'    => $order_by,
					'hide_empty' => false  // if 'hide_empty' then do not return categories with no articles
				);
			}
		} else {
			$args = array(
				'orderby' => $order_by,
				'include' => $category_ids
			);
		}

		$terms = get_terms( WIDG_KB_Handler::get_category_taxonomy_name( $kb_id ), $args );
		if ( is_wp_error( $terms ) ) {
			WIDG_Logging::add_log( 'cannot get terms for kb_id', $kb_id, $terms );
			return array();
		} else if ( empty( $terms ) || ! is_array( $terms ) ) {
			return array();
		}

		$terms = array_values( $terms ); // rearrange array keys

		$category_data = WIDG_KB_Core::get_category_data_option( $kb_id );
		if ( ! empty( $category_data ) ) {
			// remove draft categories
			foreach ( $terms as $key => $term ) {
				if ( ! empty( $category_data[ $term->term_id ] ) && ! empty( $category_data[ $term->term_id ]['is_draft'] ) ) {
					unset( $terms[ $key ] );
				}
			}
		}

		if ( $filter == 'all' ) {
			$terms = $this->add_levels_to_categories( $terms, $kb_id );
		}

		return $terms;
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
			'title'        => __( 'Top KB Categories', 'echo-widgets' ),
			'kb_id'        => WIDG_KB_Config_DB::DEFAULT_KB_ID,
			'category_ids' => '',
			'filter'       => 'top',
			'order_by'     => 'name'
		);

		$instance = wp_parse_args( (array)$instance, $defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				   value="<?php echo $instance['title']; ?>"/>
		</p>

		<!-- KB ID -->
		<?php if ( defined( 'EM' . 'KB_PLUGIN_NAME' ) ) { ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"><?php _e( 'KB ID:', 'echo-widgets' ) ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"
					   name="<?php echo esc_attr( $this->get_field_name( 'kb_id' ) ); ?>" type="text"
					   value="<?php echo $instance['kb_id']; ?>"/>
			</p>            <?php
		} ?>

		<!-- Filter -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"><?php _e( 'Filter:', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>" type="text"
				   value="<?php echo $instance['filter']; ?>"/>
		</p>

		<!-- order By -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>"><?php _e( 'Order By:', 'echo-widgets' ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'order_by' ) ); ?>"
					id="<?php echo esc_attr( $this->get_field_id( 'order_by' ) ); ?>">
				<?php foreach ( array( 'name', 'id' ) as $order ) { ?>
					<option <?php selected( $instance['order_by'], $order ); ?>
							value="<?php echo esc_attr( $order ); ?>"><?php

						if ( $order == 'name' ) {
							_e( 'Name', 'echo-widgets' );
						} else {
							_e( 'ID', 'echo-widgets' );
						} ?>
					</option>
				<?php } ?>
			</select>
		</p>

		<!-- Category IDs -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category_ids' ) ); ?>"><?php _e( 'Category ID(s):', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category_ids' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'category_ids' ) ); ?>" type="text"
				   value="<?php echo $instance['category_ids']; ?>"/>
		</p>            <?php
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
		$instance['filter'] = isset( $new_instance['filter'] ) ? strip_tags( trim( $new_instance['filter'] ) ) : '';
		$instance['category_ids'] = isset( $new_instance['category_ids'] ) ? strip_tags( trim( $new_instance['category_ids'] ) ) : '';
		$instance['order_by'] = isset( $new_instance['order_by'] ) ? strip_tags( trim( $new_instance['order_by'] ) ) : 'name';

		return $instance;
	}

	/**
	 * Add category level so that we can indent it when outputting.
	 * @param $terms
	 * @param $kb_id
	 * @return array
	 */
	private function add_levels_to_categories( $terms, $kb_id ) {
		$new_terms = array();

		// add ancestors to all categories 
		foreach ( $terms as $category ) {
			$category->ancestors = get_ancestors( $category->term_id, WIDG_KB_Handler::get_category_taxonomy_name( $kb_id ) );
		}
		// build new array with all accentors
		foreach ( $terms as $category ) {
			$new_terms[ $category->term_id ] = array(
				'name'      => $category->name,
				'ancestors' => get_ancestors( $category->term_id, WIDG_KB_Handler::get_category_taxonomy_name( $kb_id ) )
			);
		}

		foreach ( $new_terms as $term_id => $category ) {

			$have_parent = false;

			foreach ( $category['ancestors'] as $ancestor_id ) {
				if ( isset( $new_terms[ $ancestor_id ] ) ) {
					//var_dump($ancestor_id);
					$new_terms[ $ancestor_id ]['children'][ $term_id ] = &$new_terms[ $term_id ];
					$have_parent = true;
					break;
				}
			}

			// if we here - there no ancestors in current list for the current article - null it
			if ( ! $have_parent ) {
				$category['ancestors'] = array();
			}

		}

		// now we have a tree and we should show it like a list.
		$list_with_depth = array();

		foreach ( $new_terms as $term_id => $category ) {
			// level 0

			if ( empty( $category['ancestors'] ) ) {
				foreach ( $terms as $tid => $term ) {
					if ( $term->term_id == $term_id ) {
						$term->level = 0;
						$list_with_depth[] = $term;
						unset( $terms[ $tid ] );
					}
				}

				if ( ! empty( $category['children'] ) ) {
					// level 1
					foreach ( $category['children'] as $id_1 => $category_1 ) {
						foreach ( $terms as $tid => $term ) {
							if ( $term->term_id == $id_1 ) {
								$term->level = 1;
								$list_with_depth[] = $term;
								unset( $terms[ $tid ] );
							}
						}

						// level 2
						if ( ! empty( $category_1['children'] ) ) {
							foreach ( $category_1['children'] as $id_2 => $category_2 ) {
								//var_dump($category_2);
								foreach ( $terms as $tid => $term ) {
									if ( $term->term_id == $id_2 ) {
										$term->level = 2;
										$list_with_depth[] = $term;
										unset( $terms[ $tid ] );
									}
								}

								// level 3
								if ( ! empty( $category_2['children'] ) ) {
									foreach ( $category_2['children'] as $id_3 => $category_3 ) {
										foreach ( $terms as $tid => $term ) {
											if ( $term->term_id == $id_3 ) {
												$term->level = 3;
												$list_with_depth[] = $term;
												unset( $terms[ $tid ] );
											}
										}

										// level 4
										if ( ! empty( $category_3['children'] ) ) {
											foreach ( $category_3['children'] as $id_4 => $category_4 ) {
												foreach ( $terms as $tid => $term ) {
													if ( $term->term_id == $id_4 ) {
														$term->level = 4;
														$list_with_depth[] = $term;
														unset( $terms[ $tid ] );
													}
												}

												// level 5
												if ( ! empty( $category_4['children'] ) ) {
													foreach ( $category_4['children'] as $id_5 => $category_5 ) {
														foreach ( $terms as $tid => $term ) {
															if ( $term->term_id == $id_5 ) {
																$term->level = 5;
																$list_with_depth[] = $term;
																unset ( $terms[ $tid ] );
															}
														}

														// level 6 - not now

													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $list_with_depth;
	}
}
