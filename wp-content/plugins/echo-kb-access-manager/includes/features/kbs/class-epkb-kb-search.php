<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Search Knowledge Base
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_KB_Search {

	public function __construct() {
		add_action( 'wp_ajax_epkb-search-kb', array( $this, 'search_kb' ) );
		add_action( 'wp_ajax_nopriv_epkb-search-kb', array( $this, 'search_kb' ) );  // users not logged-in should be able to search as well
	}

	/**
	 * Process AJAX search request
	 */
	public function search_kb() {

		// we don't need nonce and permission check here

		$kb_id = EPKB_Utilities::sanitize_get_id( $_GET['epkb_kb_id'] );
		if ( is_wp_error( $kb_id ) ) {
			wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => EPKB_Utilities::report_generic_error( 5 ) ) ) );
		}

		$kb_config = epkb_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

		// remove question marks
		$search_terms = EPKB_Utilities::get( 'search_words' );
		$search_terms = stripslashes( $search_terms );
		$search_terms = str_replace('?', '', $search_terms);
		$search_terms = str_replace( array( "\r", "\n" ), '', $search_terms );

		// require minimum size of search word(s)
		if ( empty($search_terms) ) {
			wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => esc_html( $kb_config['min_search_word_size_msg'] ) ) ) );
		}

		// search for given keyword(s)
		$result = $this->execute_search( $kb_id, $search_terms );
		if ( empty( $result ) ) {

			if ( $kb_config['modular_main_page_toggle'] == 'on' ) {
				$search_result = '
                    <div class="epkb-ml-search-results__no-results">
                        <span class="epkb-ml-search-results__no-results__icon epkbfa epkbfa-exclamation-circle"></span>
                        <span class="epkb-ml-search-results__no-results__text">' . $kb_config['no_results_found'] . '</span>
                    </div>';
            } else {
				$search_result = $kb_config['no_results_found'];
            }

			$not_found_count = EPKB_Utilities::get_kb_option( $kb_id, 'epkb_miss_search_counter', 0 );
			EPKB_Utilities::save_kb_option( $kb_id, 'epkb_miss_search_counter', $not_found_count + 1 );

			wp_die( wp_json_encode( array(	'status' => 'success','search_result' => $search_result	) ) );
		}

		// ensure that links have https if the current schema is https
		set_current_screen('front');

		// wrap search results into HTML
		$search_result = $kb_config['modular_main_page_toggle'] == 'on'
			? EPKB_ML_Search::display_search_results_html( $result )
			: self::display_search_results_html( $result, $search_terms, $kb_config );

		$serach_count = EPKB_Utilities::get_kb_option( $kb_id, 'epkb_hit_search_counter', 0 );
		EPKB_Utilities::save_kb_option( $kb_id, 'epkb_hit_search_counter', $serach_count + 1 );

		wp_die( wp_json_encode( array( 'status' => 'success', 'search_result' => $search_result ) ) );
	}

	/**
	 * Call WP query to get matching terms (any term OR match)
	 *
	 * @param $kb_id
	 * @param $search_terms
	 * @return array
	 */
	private function execute_search( $kb_id, $search_terms ) {

		// add-ons can adjust the search
		if ( has_filter( 'eckb_execute_search_filter' ) ) {
			$result = apply_filters('eckb_execute_search_filter', '', $kb_id, $search_terms );
			if ( is_array( $result ) ) {

				/** AMGR specific code - START */
				// check access for each post
				$filtered_posts = array();
				$handler = new AMGR_Access_Article();
				foreach( $result as $post ) {

					// verify user access to the post
					$article_access = $handler->check_post_access( $post, null );
					if ( $article_access === AMGR_Access_Article::ALLOWED ) {
						$filtered_posts[] = $post;
						continue;
					}
				}
				/** AMGR specific code - END */

				return $filtered_posts;
			}
		}

		$result = array();
		$search_params = array(
				's' => $search_terms,
				'post_type' => EPKB_KB_Handler::get_post_type( $kb_id ),
				'ignore_sticky_posts' => true,  // sticky posts will not show at the top
				'posts_per_page' => 200,         // AMGR: limit search results
				'no_found_rows' => true,        // query only posts_per_page rather than finding total nof posts for pagination etc.
				'cache_results' => false,       // don't need that for mostly unique searches
				'orderby' => 'relevance'
		);

		// OLD installation or Access Manager
		$search_params['post_status'] = array( 'publish' );
		if ( EPKB_Utilities::is_amag_on() ) {
			$search_params['post_status'] = array( 'publish', 'private' );
		} else if ( is_user_logged_in() ) {
			$search_params['post_status'] = array( 'publish', 'private' );
		}

		$found_posts_obj = new WP_Query( $search_params );
		if ( ! empty($found_posts_obj->posts) ) {
			$result = $found_posts_obj->posts;
			wp_reset_postdata();
		}

		// AMGR condition
		$max_results_size = 20;
		if ( count($result) > $max_results_size ) {
			$result = array_slice($result, 0, $max_results_size);
		}

		return $result;
	}

	/**
	 * Display a search form for non-modular KB Main Page: core layouts + called by Elegant Layouts
	 *
	 * @param $kb_config
	 */
	public static function get_search_form( $kb_config ) {

		// SEARCH BOX OFF
		if ( $kb_config['search_layout'] == 'epkb-search-form-0' ) {
			return;
		}

		self::get_search_form_output( $kb_config );
	}

	public static function get_search_form_output( $kb_config ) {

		// handle Advanced Search
		if ( EPKB_Utilities::is_advanced_search_enabled( $kb_config ) ) {
			do_action( 'eckb_advanced_search_box', $kb_config );
			return;
		}

		// handle Modular search
		if ( $kb_config['modular_main_page_toggle'] == 'on' ) {
			EPKB_Modular_Main_Page::search_module( $kb_config );
			return;
		}

		/** output old search form **/

		$is_kb_main_page = EPKB_Utilities::is_kb_main_page(); // || EPKB_Utilities::get( 'is_kb_main_page' ) == 1;

		$prefix = $is_kb_main_page ? '' : 'article_';

		$style1_escaped = self::get_inline_style( $kb_config,
			'background-color:: ' . $prefix . 'search_background_color,
			 padding-top:: ' . $prefix . 'search_box_padding_top,
			 padding-right:: ' . $prefix . 'search_box_padding_right,
			 padding-bottom:: ' . $prefix . 'search_box_padding_bottom,
			 padding-left:: ' . $prefix . 'search_box_padding_left,
			 margin-top:: ' . $prefix . 'search_box_margin_top,
			 margin-bottom:: ' . $prefix . 'search_box_margin_bottom,
			 ');

		$style2_escaped = self::get_inline_style( $kb_config,
			'background-color:: ' . $prefix . 'search_btn_background_color,
			 background:: ' . $prefix . 'search_btn_background_color, 
			 border-color:: ' . $prefix . 'search_btn_border_color'
			 );
		$style3_escaped = self::get_inline_style( $kb_config, 'color:: ' . $prefix . 'search_title_font_color, typography:: ' . $prefix . 'search_title_typography');
		$style4_escaped = self::get_inline_style( $kb_config, 'border-width:: ' . $prefix . 'search_input_border_width, border-color:: ' . $prefix . 'search_text_input_border_color,
											background-color:: ' . $prefix . 'search_text_input_background_color, background:: ' . $prefix . 'search_text_input_background_color, typography:: ' . $prefix . 'search_input_typography' );
		$class1_escaped = self::get_css_class( $kb_config, 'epkb-search, :: ' . $prefix . 'search_layout' );

		$search_title = $kb_config[ $prefix . 'search_title' ];

		$search_title_tag_escaped = EPKB_Utilities::sanitize_html_tag( $kb_config[$prefix . 'search_title_html_tag'] );

		$search_input_width = $kb_config[$prefix . 'search_box_input_width'];
		$form_style_escaped = self::get_inline_style( $kb_config, 'width:' . $search_input_width . '%' );

	   $main_page_indicator = $is_kb_main_page ? 'eckb_search_on_main_page' : '';    ?>

		<div class="epkb-doc-search-container <?php echo esc_attr( $main_page_indicator ); ?>" <?php echo $style1_escaped; ?> >     <?php

			if ( ! empty( $search_title ) ) {   ?>
				<<?php echo esc_attr( $search_title_tag_escaped ); ?> class="epkb-doc-search-container__title" <?php echo $style3_escaped; ?>> <?php echo esc_html( $search_title ); ?></<?php echo esc_attr( $search_title_tag_escaped ); ?>>   <?php
			}	?>

			<form id="epkb_search_form" <?php echo $form_style_escaped . ' ' . $class1_escaped; ?> method="get" action="/">

				<div class="epkb-search-box">
					<input type="text" <?php echo $style4_escaped; ?> id="epkb_search_terms" aria-label="<?php echo esc_attr( $kb_config[$prefix . 'search_box_hint'] ); ?>" name="s" value="" placeholder="<?php echo esc_attr( $kb_config[$prefix . 'search_box_hint'] ); ?>" aria-controls="epkb_search_results" >
					<input type="hidden" id="epkb_kb_id" value="<?php echo esc_attr( $kb_config['id'] ); ?>">
					<div class="epkb-search-box_button-wrap">
						<button type="submit" id="epkb-search-kb" <?php echo $style2_escaped; ?>><?php echo esc_html( $kb_config[$prefix . 'search_button_name'] ); ?> </button>
					</div>
					<div class="loading-spinner"></div>
				</div>
				<div id="epkb_search_results" aria-live="polite"></div>

			</form>
		</div>  <?php
	}

	/**
	 * Output inline CSS style based on configuration.
	 *
	 * @param $kb_config
	 * @param string $styles A list of Configuration Setting styles
	 *
	 * @return string
	 */
	public static function get_inline_style( $kb_config, $styles ) {
		return EPKB_Utilities::get_inline_style( $styles, $kb_config );
	}

	/**
	 * Output CSS classes based on configuration.
	 *
	 * @param $kb_config
	 * @param $classes
	 *
	 * @return string
	 */
	public static function get_css_class( $kb_config, $classes ) {
		return EPKB_Utilities::get_css_class( $classes, $kb_config );
	}

	/**
	 * Returns HTML for given search results
	 *
	 * @param $search_results
	 * @param $search_terms
	 * @param $kb_config
	 * @return string
	 */
	private static function display_search_results_html( $search_results, $search_terms, $kb_config ) {

		$prefix = EPKB_Utilities::is_kb_main_page() || EPKB_Utilities::get( 'is_kb_main_page' ) == 1 ? '' : 'article_';

		$output_html = '<div class="epkb-search-results-message">' . esc_html( $kb_config[$prefix . 'search_results_msg'] ) . ' ' . $search_terms . '</div>';
		$output_html .= '<ul>';

		$title_style_escaped = '';
		$icon_style_escaped  = '';
		if ( $kb_config['search_box_results_style'] == 'on' ) {
			$color_prefix = EPKB_Utilities::is_elegant_layouts_enabled() ? 'sidebar_' : '';  // are we taking color from Sidebar ?
			$title_style_escaped = EPKB_Utilities::get_inline_style( 'color:: ' . $color_prefix . 'article_font_color' , $kb_config);
			$icon_style_escaped = EPKB_Utilities::get_inline_style( 'color:: ' . $color_prefix . 'article_icon_color' , $kb_config);
		}

		// display one line for each search result
		foreach ( $search_results as $post ) {

			$article_url = get_permalink( $post->ID );
			if ( empty( $article_url ) || is_wp_error( $article_url ) ) {
				continue;
			}

			// linked articles have their own icon
			$article_title_icon = 'ep_font_icon_document';
			if ( has_filter( 'eckb_single_article_filter' ) ) {
				$article_title_icon = apply_filters( 'eckb_article_icon_filter', $article_title_icon, $post->ID );
				$article_title_icon = empty( $article_title_icon ) ? 'epkbfa-file-text-o' : $article_title_icon;
			}

			// linked articles have the open in new tab option
			$new_tab = '';
			if ( EPKB_Utilities::is_link_editor_enabled() ) {
				$link_editor_config = EPKB_Utilities::get_postmeta( $post->ID, 'kblk-link-editor-data', [], true );
				$new_tab = empty( $link_editor_config['open-new-tab'] ) ? '' : 'target="_blank"';
			}

			$output_html .=
				'<li>' .
					'<a href="' .  esc_url( $article_url ) . '" ' . $new_tab . ' class="epkb-ajax-search" data-kb-article-id="' . $post->ID . '">' .
						'<span class="epkb_search_results__article-title" ' . $title_style_escaped . '>' .
							'<span class="epkb_search_results__article-title__icon epkbfa ' . esc_attr( $article_title_icon ) . ' ' . $icon_style_escaped . '"></span>' .
							'<span class="epkb_search_results__article-title__text">' . esc_html( $post->post_title ) . '</span>' .
						'</span>' .
					'</a>' .
				'</li>';
		}
		$output_html .= '</ul>';

		return $output_html;
	}
}
