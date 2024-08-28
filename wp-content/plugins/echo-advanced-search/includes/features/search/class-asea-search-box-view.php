<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display the Advanced Search Box
 *
 */
class ASEA_Search_Box_View {

    public function __construct() {
        add_action( 'eckb_advanced_search_box', array( $this, 'display_advanced_search_structure' ) );
	    add_action( 'asea_search_background_image', array( $this, 'display_background_image' ) );
	    add_action( 'asea_search_gradient_background', array( $this, 'display_gradient' ) );
	    add_action( 'asea_search_pattern_image', array( $this, 'display_pattern_image' ) );
	    add_action( 'asea_sub_section_1_1_content', array( $this, 'display_search_title' ) );
	    add_action( 'asea_sub_section_1_1_content', array( $this, 'display_paragraph_description_below_title' ) );
        add_action( 'asea_sub_section_1_2_content', array( $this, 'display_search_input_and_results_box' ) );
	    add_action( 'asea_sub_section_1_3_content', array( $this, 'display_paragraph_description_below_input' ) );
        add_action( 'asea_section_1_styles', array( $this, 'asea_section_1_styles' ) );
        add_action( 'asea_search_container_style_tags', array( $this, 'asea_search_container_style_tags' ) );
		add_action( 'eckb_after_theme_preview', array( $this, 'asea_search_container_style_tags' ), 10, 2 );
    }

	/**
	 * Display the main layout structure of the Advanced Search.
	 * Hooks are positioned within the Advanced Search layout so that HTML elements can be displayed in different order.
	 * Layout has 3 Main sections and each section will have 5 subsections to hook into.
	 * There is one hook after the whole search boxes for future use.
	 *
	 * @param array $kb_config Current active KB settings.
	 *
	 */
	public function display_advanced_search_structure( $kb_config ) {

		// add ASEA configuration
		$asea_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_config['id'] );
		if ( is_wp_error( $asea_config ) ) {
			return;
		}

		$kb_config = array_merge( $asea_config, $kb_config );

		// TODO - OLD REMOVE
		if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_visibility' ) === 'asea-visibility-search-form-2' ) {
			return;
		}

		$is_main_page = ASEA_Core_Utilities::get_search_index( $kb_config ) == 'mp';
		$main_page_indicator = $is_main_page ? '' : 'eckb_search_on_main_page';         ?>

		<!-------- ASEA Main Container ----------->
		<div id="asea-doc-search-container" class="<?php echo $main_page_indicator . ' '; ?>">

			<?php do_action('asea_search_container_style_tags', $kb_config ); ?>

			<!-------- ASEA Search Container # 1 ----------------->
			<section id="asea-section-1" <?php do_action('asea_section_1_styles', $kb_config ); ?>>

				<!-------- ASEA Background Image Container ------>
				<div id="asea-search-background-image-1" <?php do_action('asea_search_background_image', $kb_config ); ?>></div>

				<!-------- ASEA Gradient Container -------------->
				<div id="asea-search-gradient-1" <?php do_action('asea_search_gradient_background', $kb_config ); ?>></div>

				<!-------- ASEA Pattern Container --------------->
				<div id="asea-search-pattern-1"  <?php do_action('asea_search_pattern_image', $kb_config ); ?>></div>

				<!-------- ASEA Sub Section Container 1-1 --------------->
				<section id="asea-sub-section-1-1"><?php do_action('asea_sub_section_1_1_content', $kb_config ); ?></section>

				<!-------- ASEA Sub Section Container 1-2 --------------->
				<section id="asea-sub-section-1-2"><?php do_action('asea_sub_section_1_2_content', $kb_config ); ?></section>

				<!-------- ASEA Sub Section Container 1-3 --------------->
				<section id="asea-sub-section-1-3"><?php do_action('asea_sub_section_1_3_content', $kb_config ); ?></section>

				<!-------- ASEA Sub Section Container 1-4 --------------->
				<section id="asea-sub-section-1-4"><?php do_action('asea_sub_section_1_4_content', $kb_config ); ?></section>

				<!-------- ASEA Sub Section Container 1-5 --------------->
				<section id="asea-sub-section-1-5"><?php do_action('asea_sub_section_1_5_content', $kb_config ); ?></section>

			</section>

		</div>		<?php

		do_action('eckb_doc_search_container_after', $kb_config );
	}

	/**
 * Display Background Image
 * @param $kb_config
 */
	public function display_background_image( $kb_config ) {

		$background_image_url = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_image_url' );
		$background_image_url = trim($background_image_url);
		if ( ! empty($background_image_url) ) {
			echo 'style="';
			echo 'background-image: url(' . $background_image_url . ');';
			echo 'background-position-x:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_image_position_x' ) . ';';
			echo 'background-position-y:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_image_position_y' ) . ';';
			echo '"';
		}
	}

	/**
	 * Display Gradient
	 * @param $kb_config
	 */
	public function display_gradient( $kb_config ) {

		if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_gradient_toggle' ) == 'on' ) {
			echo 'style="';
			echo 'background: linear-gradient(' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_gradient_degree' ) . 'deg,' .
			     ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_gradient_from_color' ) . ' 0%,' .
			     ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_gradient_to_color' ) . ' 100% );';
			echo 'opacity:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_gradient_opacity' ) . ';';
			echo '"';
		}
	}

	/**
	 * Display Background Pattern Image
	 * @param $kb_config
	 */
	public function display_pattern_image( $kb_config ) {

		$background_pattern_image_url = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_pattern_image_url' );
		$background_pattern_image_url = trim($background_pattern_image_url);
		if ( ! empty($background_pattern_image_url) ) {
			echo 'style="';
			echo 'background-image: url(' . $background_pattern_image_url . ');';
			echo 'background-position-x:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_pattern_image_position_x' ) . ';';
			echo 'background-position-y:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_pattern_image_position_y' ) . ';';
			echo 'opacity:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_pattern_image_opacity' ) . ';';
			echo '"';
		}
	}

	/**
	 * Display search title
	 * @param $kb_config
	 */
	public function display_search_title( $kb_config ) {

		//Inline Styles ----------------------------------/
		$title_style = 'color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_font_color') . ';';
		$title_style .= 'padding-bottom:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_padding_bottom') . 'px!important;';
		$title_style .= ASEA_Utilities::get_typography_config( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_typography') );


		if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_text_shadow_toggle') == 'on' ) {
			$title_style .= 'text-shadow:'
			                . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_text_shadow_x_offset') . 'px '
			                . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_text_shadow_y_offset') . 'px '
			                . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_text_shadow_blur') . 'px '
			                . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_font_shadow_color') . '; '
			;
		}

		$search_title_toggle = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_toggle' );
		$search_title = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title' );
		$search_title = trim($search_title);

		$background_color = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_background_color');
		$title_font_color = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_font_color');
		if ( $background_color == $title_font_color ) {
			$search_title = '';                             ?>
			<div style='padding-top: 40px;'></div>     <?php
		}

		// user can specify tag for search title
		$title_tag =  ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_tag');
		if ( $search_title_toggle == 'on' ) {
			$inline_styles = '#asea-search-title {
				' . $title_style . '
			}'; ?>
			<style><?php echo ASEA_Minifier::minify_css( $inline_styles ); ?></style>
			<<?php echo $title_tag; ?> id="asea-search-title"> <?php echo wp_kses_post( $search_title ); ?></<?php echo $title_tag; ?>>	<?php
		}
	}

	/**
	 * Display Paragraph Description 1
	 * @param $kb_config
	 */
	public function display_paragraph_description_below_title( $kb_config ) {

		//Get Input Width Value so that the description matches in length.
		$search_input_width = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_font_width' );

		//Inline Styles ----------------------------------/
		$desc_style = 'color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_font_color') . ';';
		$desc_style .= 'padding-top:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_padding_top') . 'px!important;';
		$desc_style .= 'padding-bottom:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_padding_bottom') . 'px!important;';
		$desc_style .= 'width:' .    $search_input_width . '%;';
		$desc_style .= ASEA_Utilities::get_typography_config( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_typography') );

		if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_text_shadow_toggle') == 'on' ) {
			$desc_style .= 'text-shadow:'
			               . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_text_shadow_x_offset') . 'px '
			               . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_text_shadow_y_offset') . 'px '
			               . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_text_shadow_blur') . 'px '
			               . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_font_shadow_color') . '; '
			;
		}

		$below_title_text_toggle = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title_toggle' );
		$below_title_text = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_title' );
		$below_title_text = trim($below_title_text);
		if ( $below_title_text_toggle == 'on' ) {
			$inline_styles = '#asea-search-description-1 {
				' . $desc_style . '
			}'; ?>
			<style><?php echo ASEA_Minifier::minify_css( $inline_styles ); ?></style>
			<p id="asea-search-description-1"><?php echo wp_kses_post( $below_title_text ); ?></p>  <?php
		}
	}

	/**
	 * Display Paragraph Description 2
	 * @param $kb_config
	 */
	public function display_paragraph_description_below_input( $kb_config ) {

		//Get Input Width Value so that the description matches in length.
		$search_input_width = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_font_width' );

		$style3 = $this->get_inline_style( $kb_config, '
							color:: advanced_search_*_title_font_color,
							typography:: advanced_search_*_description_below_input_typography,	
							padding-top:: advanced_search_*_description_below_input_padding_top,	
							padding-bottom:: advanced_search_*_description_below_input_padding_bottom,	
							width:' . $search_input_width . '%
		');

		$below_input_text_toggle = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_input_toggle' );
		$below_input_text = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_description_below_input' );
		$below_input_text = trim($below_input_text);
		if ( $below_input_text_toggle == 'on' ) {     ?>
			<p id="asea-search-description-2" <?php echo $style3; ?>> <?php echo wp_kses_post( $below_input_text ); ?></p>	<?php
		}
	}

	/**
	 * Display Search Input and Results
	 * @param $kb_config
	 */
	public function display_search_input_and_results_box( $kb_config ) {

		// Search Filter state: on/off
		$search_filter = empty($kb_config['search_multiple_kbs']) ? ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_toggle' ) : 'off';

		// Search Icon position: none/left/right
		$search_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_search_icon_placement' );

		// Loading Icon position: left/right
		$loading_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_loading_icon_placement' );

		//Search Box container style  ---------------------------------------------------------------------------------/
		$search_box_style = 'border-width:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_border_width' ) . 'px!important;';
		$search_box_style .= 'border-radius:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_radius' ) . 'px;';
		$search_box_style .= ASEA_Utilities::get_typography_config( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_typography' ) );
		$search_box_style .= 'border-color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_text_input_border_color' ) . '!important;';
		$search_box_style .= 'background-color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_text_input_background_color' ) . '!important;';
		$search_box_style .= 'background:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_text_input_background_color' ) . ';';
		$search_box_style .= 'padding-left:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_left' ) . 'px!important;';
		$search_box_style .= 'padding-right:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_right' ) . 'px!important;';
		$search_box_style .= 'border-style: solid!important;';

		if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_rgba') ) {
			$search_box_style .= 'box-shadow:'
			                     . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_x_offset' ) . 'px '
			                     . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_y_offset' ) . 'px '
			                     . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_blur' ) . 'px '
			                     . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_spread' ) . 'px ' .
			                     'rgba(' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_shadow_rgba' ) . ')
			;';
		}

		// Input Box Styles --------------------------------------------------------------------------------------------/
		$input_style = 'padding-top:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_top' ) . 'px!important;';
		$input_style .= 'padding-bottom:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_bottom' ) . 'px!important;';

		// Set additional padding left for input box if:
		// - is RTL and Search Filter is 'on'
		// - or Search Icon is on the left side
		// - or Loading Icon is on the left side
		if ( ( is_rtl() && $search_filter == 'on' ) || $search_icon_placement == 'left' || $loading_icon_placement == 'left' ) {
			$input_style .= 'padding-left:15px!important;';
		}

		// Set additional padding right for input box if:
		// - is LTR and Search Filter is 'on'
		// - or Search Icon is on the right side
		// - or Loading Icon is on the right side
		if ( ( ! is_rtl() && $search_filter == 'on' ) || $search_icon_placement == 'right' || $loading_icon_placement == 'right' ) {
			$input_style .= 'padding-right:15px!important;';
		}
		
		// Search Results CSS / Styles ---------------------------------------------------------------------------------/
		$inline_styles = '#asea-doc-search-container #asea_search_form #asea_search_results ul li a .eckb-article-title,
				#asea-doc-search-container #asea_search_form #asea_search_results ul li a .eckb-article-title .eckb-article-title-icon,
				#asea-doc-search-container #asea_search_form #asea_search_results #asea-all-search-results a
		{ font-size:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_search_results_article_font_size' ) . 'px; }
		  #asea-doc-search-container #asea-section-1 #asea_search_form #asea_search_results #asea-all-search-results a
		{ color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_link_font_color' ) . '; }';
		echo '<style>' . ASEA_Minifier::minify_css( $inline_styles ) . '</style>';

		$class1 = $this->get_css_class( $kb_config, 'asea-search, : advanced_search_*_layout' );

		$search_input_width = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_input_width' );
		$form_style = $this->get_inline_style($kb_config, 'width:' . $search_input_width . '%' );		

		$search_dropdown_width = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_dropdown_width' );
		$search_dropdown_style = $this->get_inline_style($kb_config, 'max-width:' . $search_dropdown_width . 'px' );

		$category_level = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_category_level' );
		$filter_by_title = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_by_filter' );
		$filter_by_title = trim($filter_by_title);

		$clear_results_title = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_title_clear_results' );
		$clear_results_title = trim($clear_results_title);

		$kb_ids = empty( $kb_config['search_multiple_kbs'] ) ? $kb_config['id'] : $kb_config['search_multiple_kbs'];  ?>

		<div id="asea-doc-search-box-container">

			<form id="asea_search_form" <?php echo $form_style . ' ' . $class1; ?> method="get" action="" role="search" aria-label="Search">

				<!----- Search Box ------>  <?php
				$inline_styles = '#asea-doc-search-container #asea-doc-search-box-container #asea_search_form .asea-search-box {
						' . $search_box_style . '
					}
					#asea_advanced_search_terms {
						' . $input_style . '
					}'; ?>
				<style><?php echo ASEA_Minifier::minify_css( $inline_styles ); ?></style>

				<div class="asea-search-box" ><?php

					// Left icons wrap: let's change position because the RTL changes direction for flex elements
					if ( is_rtl() ) {
						$this->asea_search_icons_wrap( $kb_config, 'right' );
					} else {
						$this->asea_search_icons_wrap( $kb_config, 'left' );
					}   ?>

					<input type="search"  id="asea_advanced_search_terms" name="kb-search" value=""
					       aria-autocomplete="list" autocapitalize="off" autocomplete="off" spellcheck="false"
					       aria-label="<?php echo esc_attr( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_hint') ); ?>"
						   aria-controls="asea_search_results"
					       data-language="<?php echo ASEA_Search_Query_Extras::get_current_language( $kb_config['id'] ); ?>"
					       placeholder="<?php echo esc_attr( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_hint') ); ?>" />
					<input type="hidden" id="asea_kb_id" value="<?php echo $kb_ids; ?>"/>					<?php

					if ( ! empty($kb_config['seq_id']) ) { ?>
						<input type="hidden" id="asea_seq_id" value="<?php echo esc_attr( $kb_config['seq_id'] ); ?>"/>					<?php
					}

					// Left icons wrap: let's change position because the RTL changes direction for flex elements
					if ( is_rtl() ) {
						$this->asea_search_icons_wrap( $kb_config, 'left' );
					} else {
						$this->asea_search_icons_wrap( $kb_config, 'right' );
					}

					// If the search filter is on and is LTR
                    if ( $search_filter == 'on' ) {
                        $this->asea_search_filter_icon( $kb_config );
                    }    ?>
				</div>  <?php

				if ( $search_filter == 'on' ) {
					$this->display_category_filter( $kb_config['id'], $category_level, $search_dropdown_style, $filter_by_title, $clear_results_title );
				}   ?>

				<!----- Search Box Results ------>
				<div id="asea_search_results" aria-live="polite"></div>

			</form>

		</div>		<?php
	}

	private function display_category_filter( $kb_id, $category_level, $search_dropdown_style, $filter_by_title, $clear_results_title ) {    ?>

		<!----- Search Box filter ------>
		<div class="asea-search-filter-container <?php echo esc_attr( $category_level ); ?>" <?php echo $search_dropdown_style; ?>>

			<fieldset>
				<legend><?php echo esc_html( $filter_by_title ); ?></legend>
				<span id="asea-search-filter-clear-results"><?php echo esc_html( $clear_results_title ); ?></span>						<?php

				// retrieve top filtered categories
				$kb_taxonomy = ASEA_KB_Handler::get_category_taxonomy_name( $kb_id );
				$args = array(
					'taxonomy'      => $kb_taxonomy,
					'orderby'       => 'name',
					'order'         => 'ASC',
					'hide_empty'    => false,
					'hierarchical'  => 1,
					'parent' 		=> 0
				);
				$found_categories = get_categories( $args );
				$top_filtered_categories = [];
				foreach( $found_categories as $found_category ) {
					$top_filtered_categories[] = $found_category->term_id;
				}

				$unfiltered_categories = ASEA_Core_Utilities::get_kb_categories_visible( $kb_id );
				$unfiltered_categories = empty($unfiltered_categories) ? [] : $unfiltered_categories;

				// Categories Filter  ?>
				<div class="asea-filter-category-options-container">
					<ul>						 	<?php

						foreach( $unfiltered_categories as $top_category ) {

							if ( ! empty($top_category->parent) ) {
								continue;
							}

							$args = array(
								'taxonomy'      => $kb_taxonomy,
								'orderby'       => 'name',
								'order'         => 'ASC',
								'hide_empty'    => false,
								'hierarchical'  => 1,
								'parent'        => $top_category->term_id
							);
							$child_categories = get_categories( $args );					?>

							<li>    <?php

								if ( in_array( $top_category->term_id, $top_filtered_categories ) || ! empty( $child_categories ) ) { ?>

									<div class="asea-filter-option">
										<label>
											<input class="asea-filter-option-input" name="cat-<?php echo esc_attr( $top_category->term_id ); ?>" type="checkbox" value="<?php echo esc_attr( $top_category->term_id ); ?>">
											<span class="asea-filter-option-label"><?php echo esc_html( $top_category->name ); ?></span>
										</label>
									</div>                                <?php
								}

								// Display child categories only if set in configuration
								if ( $child_categories && $category_level == 'sub' ) {      ?>
									<ul class="children" style="margin-left:20px;list-style:none;display:none;"> <?php
										foreach( $child_categories as $child_category ) { ?>
											<li>
												<div class="asea-filter-option">
													<label>
														<input class="asea-filter-option-input" name="cat-<?php echo esc_attr( $child_category->term_id ); ?>" type="checkbox" value="<?php echo esc_attr( $child_category->term_id ); ?>">
														<span class="asea-filter-option-label"><?php echo esc_html( $child_category->name ); ?></span>
													</label>
												</div>
											</li>   <?php
										}   ?>
									</ul>								<?php
								} ?>

							</li>								<?php

						}   ?>

					</ul>
				</div>
			</fieldset>
		</div>  <?php
	}

	/**
	 * Adds a loading spinner when user enters text into the text input to indicate something is loading.
	 * @param $kb_config
	 */
	public function asea_loading_spinner( $kb_config ) {

		//Loading Icon CSS / Styles -----------------------------------------------------------------------------------/

        // Get the font size of the input box which will tell us how big the icon size is.
        $typography = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_typography');
		$font_size = ! empty( $typography['font-size'] ) ? $typography['font-size'] : '18';
		$font_units = ! empty( $typography['font-size-units'] ) ? $typography['font-size-units'] : 'px';

		// Space between icons
		$space_between_icons = 15;

		// Search Filter state: on/off
        $search_filter = empty($kb_config['search_multiple_kbs']) ? ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_toggle' ) : 'off';

		// Search Icon position: none/left/right
		$search_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_search_icon_placement' );

        // Loading Icon styles
		$loading_icon_style = 'style="';
		$loading_icon_style .= 'width:' . $font_size . $font_units . ';';
		$loading_icon_style .= 'height:' . $font_size . $font_units . ';';
		$loading_icon_style .= '"';

		// Loading Icon wrap styles
        $loading_icon_wrap_style = 'style="';
        $loading_icon_wrap_style .= 'width:' . $font_size . $font_units . ';';
        $loading_icon_wrap_style .= 'height:' . $font_size . $font_units . ';';

		// If: Search Icon is on the same side
		// Or: Search Filter is active and on the same side
		// Then: need additional space between icons
		$loading_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_loading_icon_placement' );
		if ( ( $search_icon_placement == $loading_icon_placement )
            || ( $search_filter == 'on' && ( ( is_rtl() && $loading_icon_placement == 'left' ) || ( ! is_rtl() && $loading_icon_placement == 'right' ) ) ) ) {
                $loading_icon_wrap_style .= 'margin-' . $loading_icon_placement . ':' . $space_between_icons .'px;';
        }

        $loading_icon_wrap_style .= '"';

		// IMPORTANT: need to avoid any empty space between HTML tags here, otherwise extra space causes alignment issue for inline-block elements
		echo '<div class="asea-search-box__loading-icon__wrap" ' . $loading_icon_wrap_style . '>';
            echo '<div class="loading-spinner" ' . $loading_icon_style . '></div>';
        echo '</div>';
	}

	/**
	 * Adds a Search Icon before or after the input text.
	 * @param $kb_config
	 */
	public function asea_search_icon( $kb_config ) {

		//Search Icon CSS / Styles ------------------------------------------------------------------------------------/

        // Get the font size of the input box which will tell us how big the icon size is.
        $typography = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_typography' );
        $font_size = empty( $typography['font-size'] ) ? '18' : $typography['font-size'];
        $font_units = empty( $typography['font-size-units'] ) ? 'px' : $typography['font-size-units'];

		// Space between icons
		$space_between_icons = 15;

		// Search Filter state: on/off
        $search_filter = empty($kb_config['search_multiple_kbs']) ? ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_toggle' ) : 'off';

		// Search Icon position: none/left/right
		$search_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_search_icon_placement' );

        // Search Icon styles
        $search_icon_style  = 'style="';
        $search_icon_style .= 'font-size:' . $font_size . $font_units . ';';

        // If Search Filter is active, then need to consider its icon space when it is on the same side that Loading Icon
        if ( $search_filter == 'on' ) {
            if ( ! is_rtl() && $search_icon_placement == 'right' ) {
                $search_icon_style .= 'margin-right:' . $space_between_icons . 'px;';
            } elseif ( is_rtl() && $search_icon_placement == 'left' ) {
                $search_icon_style .= 'margin-left:' . $space_between_icons . 'px;';
            }
        }

		$search_icon_style .= '"';

		// IMPORTANT: need to avoid any empty space before and after HTML tag here, otherwise extra space causes alignment issue for inline-block elements
        echo '<div class="asea-search-icon epkbfa epkbfa-search" ' . $search_icon_style . '></div>';
	}

	/**
	 * Adds a box with search icons for both left and right side
	 *
	 * @param $kb_config
	 * @param string $icons_wrap_position
	 */
	public function asea_search_icons_wrap( $kb_config, $icons_wrap_position = '' ) {

		if ( empty( $icons_wrap_position ) || ( $icons_wrap_position !== 'left' && $icons_wrap_position !== 'right' ) ) {
			return;
		}

		// Icons Wrap styles
		$icons_wrap_style = 'style="';
		$icons_wrap_style .= 'padding-top:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_top' ) . 'px;';
		$icons_wrap_style .= 'padding-bottom:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_bottom' ) . 'px;';
		$icons_wrap_style .= '"';

		// Search Icon position: none/left/right
		$search_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_search_icon_placement' );

		// Loading Icon position: left/right
		$loading_icon_placement = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_loading_icon_placement' );

		// Icons wrap if the Search Filter is active
		// IMPORTANT: need to avoid any empty space between HTML tags here, otherwise extra space causes alignment issue for inline-block elements
		echo '<div class="asea-search-box__icons-wrap" ' . $icons_wrap_style . '>';

			// LEFT ICONS WRAP
			if ( $icons_wrap_position == 'left' ) {

				// Search Icon
				if ( $search_icon_placement == $icons_wrap_position ) {
					$this->asea_search_icon( $kb_config );
				}

				// Loading Icon
				if ( $loading_icon_placement == $icons_wrap_position ) {
					$this->asea_loading_spinner( $kb_config );
				}

			// RIGHT ICONS WRAP
			} else {

				// Loading Icon
				if ( $loading_icon_placement == $icons_wrap_position ) {
					$this->asea_loading_spinner( $kb_config );
				}

				// Search Icon
				if ( $search_icon_placement == $icons_wrap_position ) {
					$this->asea_search_icon( $kb_config );
				}

			}

		echo '</div>';
	}

	/**
	 * Adds a box with text and drop down icon into the text input box
	 *
	 * @param $kb_config
	 */
	public function asea_search_filter_icon( $kb_config ) {

		// Filter-icon Icon CSS / Styles
		$filter_icon_style  = 'style="';
		$filter_icon_style .= ASEA_Utilities::get_typography_config( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_typography' ) );

		$filter_icon_style .= 'padding-top:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_top' ) . 'px;';
		$filter_icon_style .= 'padding-bottom:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_input_box_padding_bottom' ) . 'px;';
		$filter_icon_style .= 'color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_box_font_color' ) . ';';
		$filter_icon_style .= 'background-color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_box_background_color' ) . ';';
		$filter_icon_style .= '"';

		$text_indicator_filter = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_indicator_text' );

		// IMPORTANT: need to avoid any empty space between HTML tags here, otherwise extra space causes alignment issue for inline-block elements
		echo '<div class="asea-search-filter-icon-container" ' . $filter_icon_style . '>';

			if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_filter_indicator_text' ) ) {
				echo '<span class="asea-search-filter-text">' . esc_html( $text_indicator_filter ) . '</span>';
			}

			echo '<span class="asea-search-filter-icon epkbfa epkbfa-chevron-down"></span>';
		echo '</div>';
	}

	/**
	 * Adds Style tags inside main container to handle config settings that require direct targets of HTML tags that cannot be done with inline or css classes.
	 *
	 * @param $kb_config
	 * @param string $prefix
	 */
	public function asea_search_container_style_tags( $kb_config, $prefix = '' ) {
		$inline_styles = $prefix . ' #asea-doc-search-container #asea-section-1 a { color:' . ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_link_font_color') . ' }';   ?>
		<style><?php echo ASEA_Minifier::minify_css( $inline_styles ); ?></style>   <?php
	}

	/**
	 * Display Search Container #1 Style
	 * @param $kb_config
	 */
	public function asea_section_1_styles( $kb_config ) {
		echo $this->get_inline_style( $kb_config,
					'background-color:: advanced_search_*_background_color,
					 padding-top:: advanced_search_*_box_padding_top,
					 padding-right:: advanced_search_*_box_padding_right,
					 padding-bottom:: advanced_search_*_box_padding_bottom,
					 padding-left:: advanced_search_*_box_padding_left,
					 margin-top::   advanced_search_*_box_margin_top,
					 margin-bottom:: advanced_search_*_box_margin_bottom,
			 ');
	}

	/**
	 * Output inline CSS style based on configuration.
	 *
	 * @param $kb_config
	 * @param string $styles A list of Configuration Setting styles
	 *
	 * @return string
	 */
	private function get_inline_style( $kb_config, $styles) {
		$styles = str_replace('*', ASEA_Core_Utilities::get_search_index( $kb_config ), $styles);
		return ASEA_Utilities::get_inline_style( $styles, $kb_config );
	}

	/**
	 * Output CSS classes based on configuration.
	 *
	 * @param $kb_config
	 * @param $classes
	 *
	 * @return string
	 */
	public function get_css_class( $kb_config, $classes ) {
		$classes = str_replace('*', ASEA_Core_Utilities::get_search_index( $kb_config ), $classes);
		return ASEA_Utilities::get_css_class( $classes, $kb_config );
	}
}
