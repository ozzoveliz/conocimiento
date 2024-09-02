<?php

/**
 *  Outputs the Grid Layout for knowledge base main page.
 *
 *
 * @copyright   Copyright (C) 2018 Plugins
 */
class ELAY_Layout_Grid extends ELAY_Layout {

	public function __construct() {
		add_filter( ELAY_KB_Core::ELAY_KB_GRID_LAYOUT_OUTPUT, array( $this, 'get_layout_output'), 10, 4 );
		add_filter( ELAY_KB_Core::ELAY_KB_GRID_DISPLAY_CATEGORIES_AND_ARTICLES, array( $this, 'display_categories_and_articles'), 10, 3 );
	}

	/**
	 * Display Categories and Articles Mddule content for KB Main Page
	 *
	 * @param $kb_config
	 * @param $category_seq_data
	 * @param $articles_seq_data
	 */
	public function display_categories_and_articles( $kb_config, $category_seq_data, $articles_seq_data ) {

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		$this->kb_config = $kb_config;
		$this->category_seq_data = $category_seq_data;
		$this->articles_seq_data = $articles_seq_data;      ?>

		<div id="epkb-ml-grid-layout" role="main" aria-labelledby="epkb-ml-grid-layout" class="epkb-layout-container epkb-css-full-reset">

			<!--  Main Page Content -->
			<div class="epkb-section-container">	<?php
				$this->display_main_page_content(); ?>
			</div>

		</div>   <?php
	}

	/**
	 * Output the Layout
	 *
	 * @param $kb_config
	 * @param $is_ordering_wizard_on
	 * @param $article_seq
	 * @param $categories_seq
	 */
	public function get_layout_output( $kb_config, $is_ordering_wizard_on, $article_seq, $categories_seq ) {
		$this->display_kb_main_page( $kb_config, $is_ordering_wizard_on, $article_seq, $categories_seq );
		$this->generate_non_modular_kb_main_page();
	}

	/**
	 * Generate content of the KB main page
	*/
	private function generate_non_modular_kb_main_page() {

		if ( class_exists( 'AMGR_Access_Utilities', false ) ) {
			$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $this->kb_id, $this->category_seq_data, $this->articles_seq_data );
			if ( $kb_groups_set === null || ( ! AMGR_Access_Utilities::is_admin_or_kb_manager() && empty($kb_groups_set['categories_seq_data']) && empty($kb_groups_set['articles_seq_data']) ) ) {
				echo AMGR_Access_Reject::reject_user_access( $this->kb_id );
				return;
			}

			$this->category_seq_data = $kb_groups_set['categories_seq_data'];
			$this->articles_seq_data = $kb_groups_set['articles_seq_data'];
		}
		
		$class2 = $this->get_css_class( '::width' ); ?>

		<div id="elay-grid-layout-page-container" role="main" aria-labelledby="Knowledge Base" class="elay-css-full-reset elay-grid-template <?php echo ELAY_Utilities::get_active_theme_classes( 'mp' ); ?>">
			<div <?php echo $class2; ?>>  <?php

				//  KB Search form
				$this->get_search_form();

				//  KB Layout
				$style1 = $this->get_inline_style( 'background-color:: grid_background_color' );				?>
				<div id="elay-content-container" <?php echo $style1; ?> >

					<!--  Main Page Content -->
					<div class="elay-section-container">
						<?php $this->display_main_page_content(); ?>
					</div>

				</div>

			</div>
		</div>   <?php
	}

	/**
	 * Display KB main page content
	 */
	private function display_main_page_content() {

		// Show message that KB is under construction if there is no any article with category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		$categories_icons = $this->get_category_icons();

		// store links that already were used to add seq_no in the url
		$links_array = array(); ?>

		<div class="<?php echo empty( $this->kb_config['grid_nof_columns'] ) ? '' : 'elay-' . $this->kb_config['grid_nof_columns']; ?> eckb-categories-list" > <?php

		/** DISPLAY CATEGORIES */
		foreach ( $this->category_seq_data as $box_category_id => $box_categories ) {

			// if category link is to Category Archive page is on or not
			if ( $this->kb_config['section_hyperlink_text_on'] != 'on' ) {
				// determine link for each category - use the first top category article or KB root if one not found
				$link_url = self::get_box_category_link( $box_category_id );
			} else {
				$link_url = get_term_link( $box_category_id, ELAY_KB_Handler::get_category_taxonomy_name( $this->kb_config['id']) );
				$link_url  = is_wp_error( $link_url ) ? '' : $link_url;
			}

			// Grab the Styling for the links.
			$link_class = $this->get_css_class('::grid_section_box_hover');
			$link_style = $this->get_inline_style('color:: section_category_font_color');

			$category_name = isset($this->articles_seq_data[$box_category_id][0]) ? $this->articles_seq_data[$box_category_id][0] : '';
			if ( empty($category_name) ) {
				continue;
			}

			if ( $this->is_ordering_wizard_on ) {
				$this->display_section( $box_category_id, $categories_icons, $category_name );
			} else {

				// detect already added links
				$links_counts = array_count_values( $links_array );
				if ( ! empty($link_url) ) {
					$links_array[] = $link_url;
				}
				if ( isset( $links_counts[$link_url] ) && ! has_filter( 'article_with_seq_no_in_url_enable' ) ) {
					$link_url = add_query_arg( 'seq_no', $links_counts[$link_url] + 1, $link_url );
				}

				echo '<a href="' . esc_url( $link_url ) . '" ' . $link_class . ' ' . $link_style . '>';
				$this->display_section( $box_category_id, $categories_icons, $category_name );
				echo '</a>';
			}

		}       ?>

		</div>      <?php
	}

	private function display_section( $box_category_id, $categories_icons, $category_name ) {

		//SECTION MAIN CONTAINER

		$class0 = $this->get_css_class('::grid_section_box_shadow, elay-top-category-box '  );
		$style0 = $this->get_inline_style(
				'border-radius:: section_border_radius,
				 border-width:: section_border_width,
				 border-color:: section_border_color, 
				 background-color:: section_body_background_color, border-style: solid'
		);

		//SECTION HEAD
		$section_min_height = $this->kb_config['grid_section_icon_size'] + ( $this->kb_config['grid_section_icon_padding_top'] * 2);

		$section_alignment = 'elay-section-head-align--' . $this->kb_config['grid_section_head_alignment'];
		$section_icon_loc  = 'elay-section-head-icon-loc--' . $this->kb_config['grid_category_icon_location'];

		$class_section_head = $this->get_css_class( 'section-head, ' . $section_alignment . ' ' . $section_icon_loc. ' ' . ($this->kb_config['grid_section_divider'] == 'on' ? ', section_divider' : '' ) );
		$style_section_head = $this->get_inline_style(
				'border-bottom-width:: grid_section_divider_thickness,
						background-color:: section_head_background_color,
						border-top-left-radius:: section_border_radius,
						border-top-right-radius:: section_border_radius,
						border-bottom-color:: section_divider_color,
						padding-top:: grid_section_head_padding_top,
						padding-bottom:: grid_section_head_padding_bottom,
						padding-left:: grid_section_head_padding_left,
						padding-right:: grid_section_head_padding_right,
						min-height:' . $section_min_height . 'px'
		);
		$icon_styles = $this->get_inline_style('font-size:: grid_section_icon_size,
				padding-top::    grid_section_icon_padding_top,
				padding-bottom:: grid_section_icon_padding_bottom,
				padding-left::   grid_section_icon_padding_left,
				padding-right::  grid_section_icon_padding_right,
				color::section_head_category_icon_color,
				font-weight::grid_category_icon_thickness
		');
		$image_wrap_styles = $this->get_inline_style( '
					    padding-top::    grid_section_icon_padding_top,
					    padding-bottom:: grid_section_icon_padding_bottom,
					    padding-left::   grid_section_icon_padding_left,
					    padding-right::  grid_section_icon_padding_right,
		');	
		
		$image_styles = $this->get_inline_style( 'max-height:: grid_section_icon_size');	
		
		// Setup Category Icon 2
		$category_icon = ELAY_KB_Core::get_category_icon( $box_category_id, $categories_icons );


		$category_name_style = $this->get_inline_style(
			'color:: section_head_font_color,
				 text-align:: grid_section_head_alignment,
				  padding-top::     grid_section_cat_name_padding_top,
				  padding-right::   grid_section_cat_name_padding_right,
				  padding-bottom::  grid_section_cat_name_padding_bottom,
				  padding-left::    grid_section_cat_name_padding_left,
				  typography:: grid_section_typography'
		);
		$description_style = $this->get_inline_style(
			'color:: section_head_description_font_color,
				  text-align:: grid_section_head_alignment,
				  padding-top::grid_section_desc_padding_top,
				  padding-right::grid_section_desc_padding_right,
				  padding-bottom::grid_section_desc_padding_bottom,
				  padding-left::grid_section_desc_padding_left, 
				  typography:: grid_section_description_typography'
		);
		$category_desc = isset($this->articles_seq_data[$box_category_id][1]) && $this->kb_config['grid_section_desc_text_on'] == 'on' ? $this->articles_seq_data[$box_category_id][1] : '';

		// SECTION BODY
		$style5 = ' padding-top::    grid_section_body_padding_top,
					padding-bottom:: grid_section_body_padding_bottom,
					padding-left::   grid_section_body_padding_left,
					padding-right::  grid_section_body_padding_right, ';

		if ( $this->kb_config['grid_section_box_height_mode'] == 'section_min_height' ) {
			$style5 .= 'min-height:: grid_section_body_height';
		} else if ( $this->kb_config['grid_section_box_height_mode'] == 'section_fixed_height' ) {
			$style5 .= 'overflow: auto, height:: grid_section_body_height';
		}

		$box_category_data = $this->is_ordering_wizard_on ? 'data-kb-category-id=' . $box_category_id . ' data-kb-type=category ' : '';
		$body_text_align = 'elay-text-' . $this->kb_config['grid_section_body_alignment'];
		$elay_body_class = $this->get_css_class( 'elay-section-body, ' . $body_text_align );
		$body_text_style = $this->get_inline_style(	'padding-top::article_list_spacing, padding-bottom::article_list_spacing, typography:: grid_section_article_typography' );        ?>

		<!-- SECTION MAIN CONTAINER -->
		<section <?php echo $class0 . ' ' . $style0; ?> >

			<!-- SECTION HEAD -->
			<div <?php echo $class_section_head . ' ' . $style_section_head; ?> >

				<div class="elay-grid-category-title-icon-container">
					<?php
					// Category Icon Left / Top / Right
					if ( ! empty( $category_icon['type'] ) && ( $this->kb_config['grid_category_icon_location'] == 'left' || $this->kb_config['grid_category_icon_location'] == 'top'  || $this->kb_config['grid_category_icon_location'] == 'right') ) {

						// If Image Icon
						if ( $category_icon['type'] == 'image' ) { ?>
							<span class="elay-icon-elem elay-icon-elem--image " <?php echo $image_wrap_styles; ?>>
							<img src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo $category_icon['image_alt']; ?>" <?php echo $image_styles; ?>>
						</span>					<?php
							// If Font Icon
						} else { ?>
							<span class="elay-icon-elem epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $icon_styles; ?>></span>					<?php
						}

					} ?>
					<h2 class="elay-grid-category-name"	<?php echo $box_category_data . ' ' . $category_name_style; ?> >
						<?php echo esc_html( $category_name );    ?>
					</h2>

				</div>				<?php
				
				if ( $category_desc ) {     ?>
					<p class="elay-grid-category-desc" <?php echo $description_style; ?> >						<?php
						echo $category_desc; ?>
					</p>						<?php
				}	

				// Category Icon Bottom
				if ( ! empty($category_icon['type']) && $this->kb_config['grid_category_icon_location'] == 'bottom')  {

					// If Image Icon
					if ( $category_icon['type'] == 'image' ) { ?>
						<span class="elay-icon-elem elay-icon-elem--image " <?php echo $image_wrap_styles; ?>>
							<img src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" <?php echo $image_styles; ?>>
						</span>					<?php
					// If Font Icon
					} else { ?>
						<span class="elay-icon-elem epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" <?php echo $icon_styles; ?>></span>					<?php
					}
				}				?>

			</div>
			<!-- /SECTION HEAD -->

			<!-- SECTION BODY -->   <?php
				$this->output_box_message( $box_category_id, $elay_body_class, $style5, $body_text_style ); 			?>
			<!-- /SECTION BODY -->

		</section><!-- /SECTION MAIN CONTAINER -->      <?php
	}

	private function output_box_message( $box_category_id, $elay_body_class, $style5, $body_text_style ) {

		if ( $this->kb_config['grid_section_article_count'] == 'off' && empty( $this->kb_config['grid_category_link_text'] ) ) {
			return;
		}		?>

		<div <?php echo $elay_body_class; ?> <?php echo $this->get_inline_style( $style5 ); ?> > <?php

			$nof_articles = $this->get_articles_count( $box_category_id );

			// if category has no articles then show empty message instead of link
			if ( $nof_articles == 0 ) {
				echo '<p ' . $body_text_style . ' class="elay_grid_empty_msg">' . $this->kb_config['category_empty_msg'] . '</p>';

			} else {

				if ( $this->kb_config['grid_section_article_count'] == 'on' ) {
					$count_text = $nof_articles == 1 ? $this->kb_config['grid_article_count_text'] : $this->kb_config['grid_article_count_plural_text'];
					$plural_class = $nof_articles == 1 ? 'single' : 'plural';
					echo '<p ' . $body_text_style . '>' . $nof_articles . ' <span class="elay_grid_count_text--' . $plural_class . '">' . $count_text . '</span></p>';
				}

				echo '<p ' . $body_text_style . ' class="elay_grid_link_text">' . $this->kb_config['grid_category_link_text'] . '</p>';
			} 	?>

		</div>	<?php
	}

	/**
	 * Return first article of given category or empty if category or sub-categores have no article
	 *
	 * @param $box_category_id
	 * @return string
	 */
	private function get_box_category_link( $box_category_id ) {

		$post_id = '';
		if ( isset( $this->category_seq_data[$box_category_id] ) ) {
			$category_seq_data = array( $box_category_id => $this->category_seq_data[$box_category_id] );
			$post_id = $this->get_first_article( $category_seq_data, $this->articles_seq_data, 3 );
		}

		if ( empty( $post_id ) ) {
			return '';
		}

		$url = get_permalink( $post_id );

		return empty( $url ) || is_wp_error( $url ) ? '' : $url;
	}
	
	private function get_articles_count( $box_category_id ) {

		if ( empty( $this->articles_seq_data[ $box_category_id ] ) ) {
			return 0;
		}

        // Convert all categories to a one-dimensional array (maximum 5 levels)
		// level 1
		$category_list = array( $box_category_id );

		// level 2
		foreach ( $this->category_seq_data[ $box_category_id ] as $category_id_level_2 => $category_level_2 ) {
			$category_list[] = $category_id_level_2;
			// level 3
			foreach ( $category_level_2 as $category_id_level_3 => $category_level_3 ) {
				$category_list[] = $category_id_level_3;
				// level 4
				foreach ( $category_level_3 as $category_id_level_4 => $category_level_4 ) {
					$category_list[] = $category_id_level_4;
					// level 5
					foreach ( $category_level_4 as $category_id_level_5 => $category_level_5 ) {
						$category_list[] = $category_id_level_5;
						// level 6
						foreach ( $category_level_5 as $category_id_level_6 => $category_level_6 ) {
							$category_list[] = $category_id_level_6;
						}
					}
				}
			}
		}

        // Get unique articles list
		$article_ids = [];
		foreach ( $category_list as $category_id ) {
            foreach ( $this->articles_seq_data[$category_id] as $article_id => $article_title ) {
	            if ( ELAY_Utilities::is_article_allowed_for_current_user( $article_id ) && $article_id > 1 ) {
		            $article_ids[$article_id] = $article_title;
                }
            }
		}

		return count( $article_ids );
	}

	/**
	 * Display first article when user loads the KB Main Page the first time without article slug
	 *
	 * @param $category_seq_data
	 * @param $articles_seq_data
	 * @param int $level
	 * @return null|int - return post id or null
	 */
	private function get_first_article( $category_seq_data, $articles_seq_data, $level=2 ) {

		// find it on the first level
		foreach( $category_seq_data as $category_id => $sub_categories ) {
			if ( ! empty($articles_seq_data[$category_id]) ) {
				$keys = array_keys($articles_seq_data[$category_id]);
				if ( ! empty($keys[2]) && ELAY_Utilities::is_positive_int( $keys[2] ) ) {
					return $keys[2];
				}
			}

			if ( $level < 2 ) {
				continue;
			}

			// find it on the second level
			foreach( $sub_categories as $sub_category_id => $sub_sub_categories ) {
				if ( ! empty( $articles_seq_data[ $sub_category_id ] ) ) {
					$keys = array_keys( $articles_seq_data[ $sub_category_id ] );
					if ( ! empty( $keys[2] ) && ELAY_Utilities::is_positive_int( $keys[2] ) ) {
						return $keys[2];
					}
				}

				if ( $level < 3 ) {
					continue;
				}

				// find it on the third level
				foreach( $sub_sub_categories as $sub_sub_category_id => $sub_sub_sub_categories ) {
					if ( ! empty( $articles_seq_data[ $sub_sub_category_id ] ) ) {
						$keys = array_keys( $articles_seq_data[ $sub_sub_category_id ] );
						if ( ! empty( $keys[2] ) && ELAY_Utilities::is_positive_int( $keys[2] ) ) {
							return $keys[2];
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * Returns inline styles for Categories & Articles Module
	 *
	 * @param $output
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $output, $kb_config ) {

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		$background_color = empty( $kb_config['background_color'] ) ? '#ffffff' : $kb_config['background_color'];
		$output .= '
		#epkb-ml-grid-layout .epkb-section-container {
			background-color: ' . $background_color . ' !important;
		}';

		return $output;
	}
}