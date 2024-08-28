<?php

/**
 * Shortcode - List of Categories (top level, all, certain IDs)
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Categories_List_Shortcode {

	public function __construct() {
		add_shortcode( 'widg-categories-list', array( $this, 'output_shortcode' ) );
	}

	public function output_shortcode( $attributes ) {
		global $eckb_kb_id;

		widg_load_public_resources_enqueue();

		// allows to adjust the widget title
		$title = empty( $attributes['title'] ) ? '' : strip_tags( trim( $attributes['title'] ) );
		$title = empty( $title ) ? __( 'Categories', 'echo-widgets' ) : $title;

		// get add-on configuration
		$kb_id = empty( $attributes['kb_id'] ) ? ( empty( $eckb_kb_id ) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
		$kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

		$add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

		$filter = empty( $attributes['filter'] ) || $attributes['filter'] == 'top' ? 'top' : 'all';

		$order_by = 'name';
		if ( !empty( $attributes['order_by'] ) && $attributes['order_by'] == 'id' ) {
			$order_by = 'id';
		}

		$terms = $this->execute_search( $kb_id, $filter, $this->retrieve_category_ids( $attributes ), $order_by );

		$css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
		$css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY CATEGORIES (top level, all, certain IDs)
        ob_start();

	    echo '<nav role="navigation" aria-label="' . esc_attr( $title ) . '" class="widg-shortcode-categories-container">';
			echo '<div class="' . esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ) . '  widg-shortcode-categories-contents">';

	        echo '<h4>' . esc_html( $title ) . '</h4>';

	        if ( empty($terms) ) {
	            echo esc_html__( 'Coming Soon', 'echo-widgets' );
	        } else {
		        
	            echo '<ul>';
		        
	            foreach( $terms as $category ) {
	                $category_url = get_term_link( $category );
	                if ( empty($category_url) || is_wp_error( $category_url )) {
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
		echo '</nav>'; //widg-shortcode-categories-container

        return ob_get_clean();
    }

	private function retrieve_category_ids( $attributes ) {

		$in_category_ids = empty( $attributes['category_ids'] ) ? '' : WIDG_Utilities::sanitize_comma_separated_ints( $attributes['category_ids'] );

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
	 * @param $order_by
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
				if ( empty( $category_data[ $term->term_id ] ) || ! empty( $category_data[ $term->term_id ]['is_draft'] ) ) {
					unset( $terms[ $key ] );
				}
			}
		}

		if ( $filter == 'all' ) {
			$terms = $this->add_levels_to_categories( $terms, $kb_id );
		}

		return $terms;
	}

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
