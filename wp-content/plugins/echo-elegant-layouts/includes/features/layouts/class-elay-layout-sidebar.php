<?php

/**
 *  Outputs the Sidebar Layout for knowledge base main page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_Layout_Sidebar extends ELAY_Layout {

    const SIDEBAR_LAYOUT = 'Sidebar';

	private $article_content = '';
	private $displayed_article_ids = array();

	public function __construct() {
		// V1 and V2
		add_action( 'wp_ajax_elay_get_article', array( $this, 'get_article' ) );
		add_action( 'wp_ajax_nopriv_elay_get_article', array( $this, 'get_article' ) );

		// V1 and V2 add Class to Advanced Search Container
		add_action( 'eckb_doc_search_container_classes', array( $this, 'advanced_search_container_style' ) );

		// outputting MAIN PAGE
        add_filter( ELAY_KB_Core::ELAY_KB_SIDEBAR_LAYOUT_OUTPUT, array($this, 'display_kb_main_page_sbl' ), 10, 4 );

		// V1: outputting ARTICLE PAGE
		add_filter( ELAY_KB_Core::ELAY_KB_ARTICLE_PAGE_LAYOUT_OUTPUT, array($this, 'display_kb_article_page'), 10, 5 );
	}

	/**
	 * Triggered when:
	 *   a) user clicks on article link
	 *   b) user searches
	 *
	 * Returns the desired article.
	 */
	public function get_article() {
		/* @var $wp_embed WP_Embed */
		global $wp_embed;

		// retrieve and sanitize data
		$article_id = isset($_REQUEST['article_id']) ? ELAY_Utilities::sanitize_int( $_REQUEST['article_id'] ) : '';
		if ( empty($article_id) ) {
			return;
		}
		// retrieve article securely
		$article = ELAY_Core_Utilities::get_kb_post_secure( $article_id );
		// TODO AM GR: if ( empty($article) ) {
        if ( true) {
			return;
		}

		// replace URLs with embeded videos; need global $post initialized
		$GLOBALS['post'] = $article;
		$article_content = $wp_embed->autoembed( $article->post_content );
		$article_content = do_shortcode( $article_content );

		// we are done here
		$article_url = get_permalink($article);
		wp_die( json_encode( array('success' => true, 'html' => $article_content, 'url' => ( is_wp_error( $article_url ) ? '' : $article_url ), 'article_id' => $article_id) ) );
	}

	public function display_kb_main_page_sbl( $kb_config, $is_builder_on, $article_seq, $categories_seq ) {
		$this->article_content = null;
		$this->display_kb_main_page( $kb_config, $is_builder_on, $article_seq, $categories_seq );
		$this->generate_kb_main_page();
	}

	public function display_kb_article_page( $content, $kb_config, $is_builder_on, $article_seq, $categories_seq ) {

		if ( $this->sidebar_loaded ) {
			return;
		}

		$this->article_content = $content;
		$this->display_kb_main_page( $kb_config, $is_builder_on, $article_seq, $categories_seq );
		$this->generate_kb_main_page();
	}

	/**
	 * Generate content of the KB main page
	*/
	private function generate_kb_main_page() {	?>

		<div id="elay-sidebar-layout-page-container" class="elay-css-full-reset elay-sidebar-template <?php echo ELAY_Utilities::get_active_theme_classes( 'mp' ); ?>">   		<?php

			//  KB Search form
			$this->get_search_form();  			?>

			<!--  Knowledge Base Layout -->
			<div id="elay-content-container">

				<!--  Main Page Content -->
				<div class="elay-section-container">
					<?php $this->display_main_page_content_inner(); ?>
				</div>

			</div>

		</div>  <?php
	}

	/**
	 * Filter categories and articles based on authorization
	 */
	private function control_access() {
		if ( class_exists('AMGR_Access_Utilities', false) ) {
			$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $this->kb_id, $this->category_seq_data, $this->articles_seq_data );
			if ( $kb_groups_set === null || ( empty($kb_groups_set['categories_seq_data']) && empty($kb_groups_set['articles_seq_data']) ) ) {
				echo AMGR_Access_Reject::reject_user_access( $this->kb_id );
				return;
			}

			$this->category_seq_data = $kb_groups_set['categories_seq_data'];
			$this->articles_seq_data = $kb_groups_set['articles_seq_data'];
		}
	}

	/**
	 * Display KB main page content
	 */
	private function display_main_page_content_inner() {

		// Show message that KB is under construction if there is no any article with category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		// protect KB Main page
		$this->control_access();

		//Calculate Article width based on Sidebar width
		$sidebar_width = $this->kb_config['sidebar_side_bar_width'];
		$padding_width = 4; // From CSS assigned values
		$article_width = ( 100 - $sidebar_width ) - $padding_width . '%';

		// for Main Page we just show introductory content
		if ( $this->article_content === null ) {
			$this->article_content = '<div id="eckb-article-content">' . wp_kses_post($this->kb_config['sidebar_main_page_intro_text']) . '</div>';
		}

		//CSS Article Reset / Defaults
		$article_class = '';
		if( $this->kb_config['templates_for_kb'] === 'kb_templates' ) {
			if ( $this->kb_config[ 'templates_for_kb_article_reset'] === 'on' ) {
				$article_class .= 'eckb-article-resets ';
			}
			if ( $this->kb_config[ 'templates_for_kb_article_defaults'] === 'on' ) {
				$article_class .= 'eckb-article-defaults ';
			}
        }
        
		/** DISPLAY ARTICLE */   ?>
		<section class="elay-single-article <?php echo $article_class; ?>" style="width: <?php echo $article_width; ?>">

            <div class="loading-spinner"></div>			<?php

			echo $this->article_content;        ?>

		</section>    <?php

		/** DISPLAY SIDEBAR */
		// for each CATEGORY display: a) its articles and b) top-level SUB-CATEGORIES with its articles

		$side_bar_style = '';
		if ( $this->kb_config['sidebar_side_bar_height_mode'] == 'side_bar_fixed_height' ) {
			$side_bar_style .= 'overflow: auto, max-height:: sidebar_side_bar_height, ';
		}
		$side_bar_style .= 'border-radius::  sidebar_section_border_radius,
                            border-width::   sidebar_section_border_width,
                            border-style:    solid,
                            border-color::   sidebar_section_border_color,
                            background-color::   sidebar_background_color,
                            width:' . $sidebar_width . '% ' ;

		$side_bar_class = $this->get_css_class('elay-sidebar, ::sidebar_section_box_shadow, ::sidebar_scroll_bar ' );		    ?>

		<section <?php echo $side_bar_class . $this->get_inline_style( $side_bar_style );?>>
			<ul class="eckb-categories-list">  			<?php

				/** DISPLAY TOP CATEGORIES and ARTICLES */
				$section_count = 0;
				$this->displayed_article_ids = array();
				$total_count = count( $this->category_seq_data );

				foreach ( $this->category_seq_data as $category_id => $subcategories ) {   ?>

					<li class="sidebar-sections"> 				<?php
						$this->display_section_heading( $category_id, $section_count , $total_count);
						$this->display_section_body( $subcategories , $category_id ); 			?>
					</li>  				<?php
					$section_count++;

				}  	?>

			</ul>
		</section>   		<?php
	}

	private function display_section_heading( $category_id , $count, $total_count ) {

		$section_header_values = 'text-align::sidebar_section_head_alignment,
								 border-width::   sidebar_section_divider_thickness,
								 padding-top::    sidebar_section_head_padding_top,
								 padding-bottom:: sidebar_section_head_padding_bottom,
								 padding-left::   sidebar_section_head_padding_left,
								 padding-right::  sidebar_section_head_padding_right,
								 border-bottom-color:: sidebar_section_divider_color,
								 background-color::  sidebar_section_head_background_color,';

		//If it's the first Heading we need to match the border radius to the top section.
		if ( $count == 0 ) {
			$section_header_values .= '
			border-top-left-radius::  sidebar_section_border_radius, 
			border-top-right-radius:: sidebar_section_border_radius';
		}
		//If it's the Last Heading we need to match the border radius to the bottom section.
		if ( ( $count + 1 ) === $total_count ) {
			$section_header_values .= '
			border-bottom-left-radius::  sidebar_section_border_radius, 
			border-bottom-right-radius:: sidebar_section_border_radius';
		}

		$section_divider = $this->kb_config['sidebar_section_divider'] == 'on' ? ', sidebar_section_divider' : '' ;
		$section_header_class_values = 'elay_section_heading,' . $section_divider . ( $this->kb_config['sidebar_top_categories_collapsed'] == 'on' ? ', elay-top-class-collapse-on' : '' );
		$section_header_class = $this->get_css_class( $section_header_class_values );
		$section_header_style = $this->get_inline_style( $section_header_values	);
		$section_header_title_style = $this->get_inline_style( 'color::  sidebar_section_head_font_color, text-align::sidebar_section_head_alignment'	);
		
		$section_header_description_style = $this->get_inline_style( 'color::  sidebar_section_head_description_font_color'	);

		$category_name = isset($this->articles_seq_data[$category_id][0]) ? $this->articles_seq_data[$category_id][0] : 'Uncategorized';
		$category_desc = isset($this->articles_seq_data[$category_id][1]) && $this->kb_config['sidebar_section_desc_text_on'] == 'on' ? $this->articles_seq_data[$category_id][1] : '';
		$box_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $category_id . ' data-kb-type=category ' : '';

		$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );
		$style1 = $this->get_inline_style( 'color:: sidebar_section_head_font_color' );         ?>

		<div <?php echo $section_header_class . $section_header_style; ?>>
			<div class="elay-category-level-1" <?php echo $box_category_data . $section_header_title_style; ?>>   <?php
				if ( $this->kb_config['sidebar_top_categories_collapsed'] == 'on' ) {		?>
					<i <?php echo $class1 . ' ' . $style1; ?> ></i>         <?php
				}        ?>
				<a <?php echo $section_header_title_style; ?>><?php echo $category_name; ?></a>
			</div>  	<?php
			if ( $category_desc ) {         ?>
				<p <?php echo $section_header_description_style; ?> >
					<?php echo $category_desc; ?>
				</p>  			<?php
			}           ?>
		</div>  <?php
	}

	private function display_section_body( $subcategories, $category_id ) {

		$section_body_styles = '';
		if ( $this->kb_config['sidebar_section_box_height_mode'] == 'section_min_height' ) {
			$section_body_styles .= 'min-height:: sidebar_section_body_height, ';
		} else if ( $this->kb_config['sidebar_section_box_height_mode'] == 'section_fixed_height' ) {
			$section_body_styles .= 'overflow: auto, height:: sidebar_section_body_height, ';
		}

		$section_body_styles .= 'padding-top::   sidebar_section_body_padding_top,
								 padding-bottom::sidebar_section_body_padding_bottom,
								 padding-left::  sidebar_section_body_padding_left,
								 padding-right:: sidebar_section_body_padding_right,';

		$sub_category_styles =  'padding-left::   sidebar_article_list_margin';		?>

		<div class="elay-section-body" <?php echo $this->get_inline_style( $section_body_styles ); ?>>  <?php

			$sub_category_list = is_array($subcategories) ? $subcategories : array();
			
			/** DISPLAY TOP-CATEGORY ARTICLES LIST */
			if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
				$this->display_articles_list( 1, $category_id, ! empty($sub_category_list) );
			}
			
			if ( $sub_category_list ) {     ?>
				<ul class="elay-sub-category eckb-sub-category-ordering" <?php echo $this->get_inline_style( $sub_category_styles ); ?>>   					<?php

					/** DISPLAY SUB-CATEGORIES */
					foreach ( $sub_category_list as $sub_category_id => $sub_sub_categories ) {
						$sub_category_name = isset($this->articles_seq_data[$sub_category_id][0]) ?
													 $this->articles_seq_data[$sub_category_id][0] : 'Category.';

						$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );
						$style1 = $this->get_inline_style( 'color:: sidebar_section_category_icon_color' );
						$style2 = $this->get_inline_style( 'color:: sidebar_section_category_font_color' );

						$box_sub_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $sub_category_id  . ' data-kb-type=sub-category ' : '';  	?>

						<li>
							<div class="elay-category-level-2-3" <?php echo $this->get_inline_style( 'padding-bottom:: sidebar_article_list_spacing,padding-top::sidebar_article_list_spacing' ); ?><?php echo $box_sub_category_data; ?>>
								<i <?php echo $class1 . ' ' . $style1; ?> ></i>
								<a <?php echo $style2; ?> ><?php echo $sub_category_name; ?></a>
							</div>    <?php

							/** DISPLAY SUB-CATEGORY ARTICLES LIST */
							if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
								$this->display_articles_list( 2, $sub_category_id, ! empty($sub_sub_categories) );
							}

							$this->display_sub_sub_categories( $sub_sub_categories );
							
							/** DISPLAY SUB-CATEGORY ARTICLES LIST */
							if (  $this->kb_config['sidebar_show_articles_before_categories'] == 'off' ) {
								$this->display_articles_list( 2, $sub_category_id, ! empty( $sub_sub_categories ) );
							}               ?>
						</li>   	<?php

					}  //foreach  ?>

				</ul>			<?php
			}
			
			/** DISPLAY TOP-CATEGORY ARTICLES LIST */
			if (  $this->kb_config['sidebar_show_articles_before_categories'] == 'off' ) {
				$this->display_articles_list( 1, $category_id, ! empty($sub_category_list) );
			}   ?>
		</div>  	<?php
	}

	private function display_sub_sub_categories( $sub_sub_categories, $level = 'sub-' ) {

		$level .= 'sub-';

		$sub_category_styles =  'padding-left::   sidebar_article_list_margin';

		$sub_category_list = is_array($sub_sub_categories) ? $sub_sub_categories : array();
		if ( $sub_category_list ) {     ?>
			<ul class="elay-sub-sub-category eckb-sub-sub-category-ordering" <?php echo $this->get_inline_style( $sub_category_styles ); ?>>   					<?php

				/** DISPLAY SUB-SUB-CATEGORIES */
				foreach ( $sub_category_list as $sub_sub_category_id => $sub_sub_category_list ) {
					$sub_category_name = isset($this->articles_seq_data[$sub_sub_category_id][0]) ?
												$this->articles_seq_data[$sub_sub_category_id][0] : 'Category.';

					$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );
					$style1 = $this->get_inline_style( 'color:: sidebar_section_category_icon_color' );
					$style2 = $this->get_inline_style( 'color:: sidebar_section_category_font_color' );

					$box_sub_category_data = $this->is_builder_on ? 'data-kb-category-id=' . $sub_sub_category_id  . ' data-kb-type='.$level.'category ' : '';  	?>

					<li>
						<div class="elay-category-level-2-3" <?php echo $this->get_inline_style( 'padding-bottom:: sidebar_article_list_spacing, padding-top::sidebar_article_list_spacing' ); ?> <?php echo $box_sub_category_data; ?>>
							<i <?php echo $class1 . ' ' . $style1; ?> ></i>
							<a <?php echo $style2; ?> ><?php echo $sub_category_name; ?></a>
						</div>    <?php
						
						/** DISPLAY SUB-SUB-CATEGORY ARTICLES LIST */
						if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
							$this->display_articles_list( 3, $sub_sub_category_id, ! empty($sub_sub_category_list), $level );
						}
						
						/** RECURSION DISPLAY SUB-SUB-...-CATEGORIES */
						if ( ! empty($sub_sub_category_list) && strlen($level) < 20 ) {
							$this->display_sub_sub_categories( $sub_sub_category_list, $level );
						}
						
						/** DISPLAY SUB-SUB-CATEGORY ARTICLES LIST */
						if (  $this->kb_config['sidebar_show_articles_before_categories'] == 'off' ) {
							$this->display_articles_list( 3, $sub_sub_category_id, ! empty($sub_sub_category_list), $level );
						}   ?>
					</li>   	<?php

				}  //foreach  			?>

			</ul>			<?php
		}
	}

	/**
	 * Display list of articles that belong to given subcategory
	 *
	 * @param $level
	 * @param $category_id
	 * @param bool $sub_category_exists - if true then we don't want to show "Articles coming soon" if there are no articles because
	 *                                   we have at least categories listed. But sub-category should always have that message if no article present
	 * @param string $sub_sub_string
	 */
	private function display_articles_list( $level, $category_id, $sub_category_exists=false, $sub_sub_string = '' ) {

		// retrieve articles belonging to given (sub) category if any
		$articles_list = array();
		if ( isset($this->articles_seq_data[$category_id]) ) {
			$articles_list = $this->articles_seq_data[$category_id];
			unset($articles_list[0]);
			unset($articles_list[1]);
		}

		// return if we have no articles and will not show 'Articles coming soon' message
		$articles_coming_soon_msg = $this->kb_config['sidebar_category_empty_msg'];
		if ( empty($articles_list) && ( $sub_category_exists || empty($articles_coming_soon_msg) ) ) {
			return;
		}

		$sub_category_styles = '';
		if ( $level == 1 ) {
			$data_kb_type = 'article';
			$sub_category_styles .= 'padding-left:: sidebar_article_list_margin,';
		} else if ( $level == 2 ) {
			$sub_category_styles .=     'padding-left:: sidebar_article_list_margin';
			$data_kb_type = 'sub-article';
		} else {
			$sub_category_styles .= 'padding-left::sidebar_article_list_margin';
			$data_kb_type = empty($sub_sub_string) ? 'sub-sub-article' : $sub_sub_string . 'article';
		}

		$class = 'class="' . ( $level == 1 ? 'elay-main-category ' : '' ) . 'elay-articles eckb-articles-ordering"'; 	?>

		<ul <?php echo $class . ' ' . $this->get_inline_style( $sub_category_styles ); ?>> <?php

			if ( empty($articles_list) ) {
				echo '<li '.$this->get_inline_style( 'padding-bottom:: sidebar_article_list_spacing,padding-top::sidebar_article_list_spacing' ) . 'class="elay-articles-coming-soon">' .
                            esc_html__( $articles_coming_soon_msg, 'echo-elegant-layouts' ) . '</li>';
			}

			$article_num = 0;
			$article_data = '';

			$nof_articles_displayed = isset($_GET['wizard-on']) ? 9999 : $this->kb_config['sidebar_nof_articles_displayed'];

			// show list of articles in this category
			foreach ( $articles_list as $article_id => $article_title ) {
				$article_num++;
				$this->displayed_article_ids[$article_id] = isset($this->displayed_article_ids[$article_id]) ? $this->displayed_article_ids[$article_id] + 1 : 1;
				$seq_no = $this->displayed_article_ids[$article_id];
				$hide_class = $article_num > $nof_articles_displayed ? 'elay-hide-elem' : '';
				$style2 = 'id="sidebar_link_' . $article_id . ( $seq_no > 1 ? '_' . $seq_no : '' ) . '"';
				if ( $this->is_builder_on ) {
					$article_data = $this->is_builder_on ? 'data-kb-article-id=' . $article_id . ' data-kb-type=' . $data_kb_type : '';
				}

				/** DISPLAY ARTICLE LINK */      ?>
				<li class="<?php echo $hide_class; ?>" <?php echo $article_data . ' ' . $style2 . ' ' . $this->get_inline_style( 'padding-bottom:: sidebar_article_list_spacing,padding-top::sidebar_article_list_spacing' ); ?> >   <?php
					$article_link_data = 'class="elay-sidebar-article" ' . 'data-kb-article-id=' . $article_id;
                    $this->single_article_link( $article_title, $article_id, $article_link_data, 'sidebar_', $seq_no ); ?>
				</li> <?php
			}

			// if article list is longer than initial article list size then show expand/collapse message
			if ( count($articles_list) > $nof_articles_displayed ) {	?>
				<li class="elay-show-all-articles">
					<span class="elay-show-text">
						<span><?php echo esc_html__( $this->kb_config['sidebar_show_all_articles_msg'], 'echo-elegant-layouts' ) . '</span> ( ' . ( count( $articles_list ) - $nof_articles_displayed ); ?> )
					</span>
					<span class="elay-hide-text elay-hide-elem"><?php esc_html_e( $this->kb_config['sidebar_collapse_articles_msg'], 'echo-elegant-layouts' ); ?></span>
				</li>					<?php
			}  ?>

		</ul> <?php
	}

	public function advanced_search_container_style() {
		echo 'elay-advanced-search-container';
	}
}
