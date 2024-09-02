<?php

/**
 *  Outputs the Sidebar Layout for knowledge base - both Main Page (Sidebar Layout) and Article Page (Sidebar layout).
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_Layout_Sidebar_v2 extends ELAY_Layout {

	private $displayed_article_ids = array();

	public function __construct() {
		add_action( 'eckb-article-v2-elay_sidebar', array( $this, 'generate_sidebar_V2' ) );
		add_filter( 'eckb_main_page_sidebar_intro_text', array( $this, 'get_main_page_sidebar_intro_text' ), 10, 2 );
		add_filter( ELAY_KB_Core::ELAY_KB_SIDEBAR_DISPLAY_CATEGORIES_AND_ARTICLES, array( $this, 'display_categories_and_articles'), 10, 4 );
	}

	/**
	 * Display Categories and Articles module content for KB Main Page
	 *
	 * @param $kb_config
	 * @param $category_seq_data
	 * @param $articles_seq_data
	 * @param $sidebar_layout_content
	 */
	public function display_categories_and_articles( $kb_config, $category_seq_data, $articles_seq_data, $sidebar_layout_content ) {

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		$this->kb_config = $kb_config;
		$this->category_seq_data = $category_seq_data;
		$this->articles_seq_data = $articles_seq_data;      ?>

		<div id="epkb-ml-sidebar-layout" role="main" aria-labelledby="Knowledge Base" class="epkb-layout-container epkb-css-full-reset">

			<!--  Main Page Content -->
			<div class="epkb-section-container">	<?php
				echo $sidebar_layout_content; ?>
			</div>

		</div>   <?php
	}

	/**
	 * Display Sidebar on Article Page or Sidebar Layout on Main Page
	 *
	 * @param $args
	 */
	public function generate_sidebar_V2( $args ) {

		$this->sidebar_loaded = true;

		// setup demo data if needed
		$is_ordering_wizard_on = false;
		$article_seq = array();
		$categories_seq = array();
		if ( ! empty( $GLOBALS['epkb-articles-seq-data'] ) && ! empty( $GLOBALS['epkb-categories-seq-data'] ) ) {
			$is_ordering_wizard_on = true;
			$article_seq = $GLOBALS['epkb-articles-seq-data'];
			$categories_seq = $GLOBALS['epkb-categories-seq-data'];
		}

		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'epkb_wizard_update_order_view' ) {
			$is_ordering_wizard_on = true;
		}

		$this->display_kb_main_page( $args['config'], $is_ordering_wizard_on, $article_seq, $categories_seq ); // only sets variables
		$this->display_sidebar_V2();
	}

	/**
	 * Invoked by KB core when Sidebar Layout is displayed on the Main Page
	 */
	/** @noinspection PhpUnusedParameterInspection */
	public function get_main_page_sidebar_intro_text( $content, $kb_id ) {
		$intro_text = elay_get_instance()->kb_config_obj->get_value( $kb_id, 'sidebar_main_page_intro_text', '' );

		// in sidebar this function is called from the filter where kb_id is passed in
		$this->kb_id = empty( $this->kb_id ) ? $kb_id : $this->kb_id;

		// Show message that KB is under construction if there is no any article with category
		if ( ! $this->kb_has_categories() ) {
			ob_start();
			$this->show_categories_missing_message();
			$intro_text .= ob_get_clean();
		}

		return $intro_text;
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
	 * DISPLAY SIDEBAR
	 * For each CATEGORY display: a) its articles and b) top-level SUB-CATEGORIES with its articles
	 */
	private function display_sidebar_V2() {

		// Show message that KB is under construction if there is no any article with category
		if ( ! $this->has_kb_categories ) {
			return;
		}

		// protect Sidebar
		$this->control_access();

		// Reformat Class Names
		$boxShadow      = '';
		if ( ! empty( $this->kb_config['sidebar_section_box_shadow'] ) ) {
			switch ( $this->kb_config['sidebar_section_box_shadow'] ) {
				case 'section_light_shadow':
					$boxShadow = 'elay-sidebar--light-shadow';
					break;
				case 'section_medium_shadow':
					$boxShadow = 'elay-sidebar--medium-shadow';
					break;
				case 'section_bottom_shadow':
					$boxShadow = 'elay-sidebar--bottom-shadow';
					break;
			}
		}

		$slimScrollbar  = '';
		if ( ! empty( $this->kb_config['sidebar_scroll_bar'] ) ) {
			switch ( $this->kb_config['sidebar_scroll_bar'] ) {
				case 'slim_scrollbar':
					$slimScrollbar = 'elay-sidebar--slim-scrollbar';
					break;
				case 'default_scrollbar':
					break;
			}
		}

		$current_category_id = 0;
		$is_archive = is_archive();
		if ( $is_archive ) {
			$this->kb_config['sidebar_top_categories_collapsed'] = 'on';
			$current_term = ELAY_Utilities::get_current_category();
			$current_category_id = empty( $current_term) ? 0 : $current_term->term_id;
		}

		$sidebar_top_categories_collapsed_Class = '';
		$sidebar_top_categories_collapsed = $this->kb_config['sidebar_top_categories_collapsed'];
		if ( $sidebar_top_categories_collapsed == 'on' ) {
			$sidebar_top_categories_collapsed_Class = 'elay-sidebar--TopCat-on';
		}

		$prefix = $is_archive ? 'cp' : ( ELAY_Utilities::is_kb_main_page() ? 'mp' : 'ap' );    ?>

		<section id="elay-sidebar-container-v2" class="elay-sidebar--reset <?php echo
				$boxShadow . ' ' . $slimScrollbar . ' ' . $sidebar_top_categories_collapsed_Class . ' ' . ELAY_Utilities::get_active_theme_classes( $prefix ) . '" ' . 'aria-label="Side menu"'; ?>>

			<ul class="elay-sidebar__cat-container">  			<?php

				/** DISPLAY TOP CATEGORIES and ARTICLES */
				$this->displayed_article_ids = array();

				foreach ( $this->category_seq_data as $category_id => $subcategories ) {   ?>
					<li id="elay-top-cat-id-<?php echo $category_id; ?>" class="elay-sidebar__cat__top-cat"> 				<?php
						$this->display_section_heading_V2( $category_id, $current_category_id );
						$this->display_section_body_V2( $subcategories, $category_id, $current_category_id ); 			?>
					</li>  				<?php
				}  	?>

			</ul>

		</section>   		<?php
	}

	private function display_section_heading_V2( $category_id, $current_category_id ) {

		$section_divider = $this->kb_config['sidebar_section_divider'] == 'on' ? ' sidebar_section_divider' : '' ;

		$category_name = isset($this->articles_seq_data[$category_id][0]) ? $this->articles_seq_data[$category_id][0] : 'Uncategorized';
		$category_desc = isset($this->articles_seq_data[$category_id][1]) && $this->kb_config['sidebar_section_desc_text_on'] == 'on' ? $this->articles_seq_data[$category_id][1] : '';
		$box_category_data = $this->is_ordering_wizard_on ? 'data-kb-category-id=' . $category_id . ' data-kb-type=category ' : '';

		$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );

		$sidebar_top_categories_collapsed = $this->kb_config['sidebar_top_categories_collapsed'];

		$topClassCollapse = $this->kb_config['sidebar_top_categories_collapsed'] == 'on' ? ' elay-top-class-collapse-on' : '';

		$categoryIcon = '';


		$top_category_style = $this->get_inline_style(
			'typography:: sidebar_section_category_typography'
		);
		$top_category_desc_style = $this->get_inline_style(
			'typography:: sidebar_section_category_typography_desc'
		);

		if ( $sidebar_top_categories_collapsed == 'on' ) {
			$categoryIcon = '<span '.$class1.'></span>';
		}			?>

		<div class="elay-sidebar__cat__top-cat__heading-container <?php echo $topClassCollapse . ' ' . $section_divider . ( $current_category_id == $category_id ? ' ' . 'elay-sidebar__cat__current-cat' : '' ); ?>">
			<div class="elay-sidebar__heading__inner" <?php echo $box_category_data; ?>>

				<!-- CATEGORY ICON -->
				<div class="elay-sidebar__heading__inner__name">
					<?php echo $categoryIcon; ?>
					<h2 class="elay-sidebar__heading__inner__cat-name" <?php echo $top_category_style; ?>><?php echo esc_html( $category_name ); ?></h2>
				</div>


				<!-- CATEGORY DESC -->				<?php
				if ( $category_desc ) { ?>
					<div class="elay-sidebar__heading__inner__desc">
						<p <?php echo $top_category_desc_style; ?>><?php echo $category_desc; ?></p>
					</div>
					<?php
				}			?>
			</div>
		</div>		<?php
	}

	private function display_section_body_V2( $subcategories, $category_id, $current_category_id ) {
		$top_category_body_style = $this->get_inline_style(
			'typography:: sidebar_section_body_typography'
		);		?>

		<div class="elay-sidebar__cat__top-cat__body-container" <?php echo $top_category_body_style; ?>>  <?php

			$sub_category_list = is_array($subcategories) ? $subcategories : array();

			/** DISPLAY TOP-CATEGORY ARTICLES LIST */
			if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
				$this->display_articles_list( 1, $category_id, ! empty($sub_category_list) );
			}

			if ( $sub_category_list ) {     ?>
				<ul class="elay-sidebar__body__sub-cat eckb-sub-category-ordering"><?php

					/** DISPLAY SUB-CATEGORIES */
					foreach ( $sub_category_list as $sub_category_id => $sub_sub_categories ) {
						$sub_category_name = isset($this->articles_seq_data[$sub_category_id][0]) ?
							$this->articles_seq_data[$sub_category_id][0] : _x( 'Category', 'taxonomy singular name' );

						$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );
						$style1 = $this->get_inline_style( 'color:: sidebar_section_category_icon_color' );

						$box_sub_category_data = $this->is_ordering_wizard_on ? 'data-kb-category-id=' . $sub_category_id  . ' data-kb-type=sub-category ' : '';  	?>

						<li>
							<div class="elay-category-level-2-3<?php echo $current_category_id == $sub_category_id ? ' ' . 'elay-sidebar__cat__current-cat' : ''; ?>" <?php echo $box_sub_category_data; ?>>
								<span <?php echo $class1 . ' ' . $style1; ?> ></span>
								<a class="elay-category-level-2-3__cat-name" >
									<h3><?php echo $sub_category_name; ?></h3>
								</a>
							</div>    <?php

							/** DISPLAY SUB-CATEGORY ARTICLES LIST */
							if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
								$this->display_articles_list( 2, $sub_category_id, ! empty($sub_sub_categories) );
							}

							$this->display_sub_sub_categories( $sub_sub_categories, 'sub-', 4, $current_category_id );

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

	private function display_sub_sub_categories( $sub_sub_categories, $level = 'sub-', $levelNum = 4, $current_category_id = 0 ) {

		$level .= 'sub-';

		$sub_category_styles = is_rtl() ? 'padding-right:: sidebar_article_list_margin,' : 'padding-left:: sidebar_article_list_margin,';

		$sub_category_list = is_array($sub_sub_categories) ? $sub_sub_categories : array();
		if ( $sub_category_list ) {     ?>
			<ul class="elay-sub-sub-category eckb-sub-sub-category-ordering" <?php echo $this->get_inline_style( $sub_category_styles ); ?>>   					<?php

				/** DISPLAY SUB-SUB-CATEGORIES */
				foreach ( $sub_category_list as $sub_sub_category_id => $sub_sub_category_list ) {
					$sub_category_name = isset($this->articles_seq_data[$sub_sub_category_id][0]) ?
						$this->articles_seq_data[$sub_sub_category_id][0] : 'Category.';

					$class1 = $this->get_css_class( '::sidebar_expand_articles_icon, elay_sidebar_expand_category_icon' );
					$style1 = $this->get_inline_style( 'color:: sidebar_section_category_icon_color' );

					$box_sub_category_data = $this->is_ordering_wizard_on ? 'data-kb-category-id=' . $sub_sub_category_id  . ' data-kb-type='.$level.'category ' : '';  	?>

					<li>
						<div class="elay-category-level-2-3<?php echo $current_category_id == $sub_sub_category_id ? ' ' . 'elay-sidebar__cat__current-cat' : ''; ?>" <?php echo $box_sub_category_data; ?>>
							<span <?php echo $class1 . ' ' . $style1; ?> ></span>
							<a class="elay-category-level-2-3__cat-name">
								<h<?php echo $levelNum; ?>><?php echo $sub_category_name; ?></h<?php echo $levelNum; ?> >
							</a>
						</div>    <?php

						/** DISPLAY SUB-SUB-CATEGORY ARTICLES LIST */
						if (  $this->kb_config['sidebar_show_articles_before_categories'] != 'off' ) {
							$this->display_articles_list( 3, $sub_sub_category_id, ! empty($sub_sub_category_list), $level );
						}

						/** RECURSION DISPLAY SUB-SUB-...-CATEGORIES */
						if ( ! empty($sub_sub_category_list) && strlen($level) < 20 ) {
							$levelNum++;
							$this->display_sub_sub_categories( $sub_sub_category_list, $level, $levelNum, $current_category_id );
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
			$sub_category_styles .= is_rtl() ? 'padding-right:: sidebar_article_list_margin,' : 'padding-left:: sidebar_article_list_margin,';
		} else if ( $level == 2 ) {
			$sub_category_styles .= is_rtl() ? 'padding-right:: sidebar_article_list_margin,' : 'padding-left:: sidebar_article_list_margin,';
			$data_kb_type = 'sub-article';
		} else {
			$sub_category_styles .=  is_rtl() ? 'padding-right:: sidebar_article_list_margin,' : 'padding-left:: sidebar_article_list_margin';
			$data_kb_type = empty($sub_sub_string) ? 'sub-sub-article' : $sub_sub_string . 'article';
		}

		$class = 'class="' . ( $level == 1 ? 'elay-sidebar__body__main-cat ' : '' ) . 'elay-articles eckb-articles-ordering"'; 	?>

		<ul <?php echo $class . ' ' . $this->get_inline_style( $sub_category_styles ); ?>> <?php

			$article_num = 0;

			$nof_articles_displayed = isset( $_GET['ordering-wizard-on'] ) ? 9999 : $this->kb_config['sidebar_nof_articles_displayed'];

			// show list of articles in this category
			foreach ( $articles_list as $article_id => $article_title ) {

				if ( ! ELAY_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
					continue;
				}

				$article_num++;
				$this->displayed_article_ids[$article_id] = isset($this->displayed_article_ids[$article_id]) ? $this->displayed_article_ids[$article_id] + 1 : 1;
				$seq_no = $this->displayed_article_ids[$article_id];
				$hide_class = $article_num > $nof_articles_displayed ? 'elay-hide-elem' : '';
				$style2 = 'id="sidebar_link_' . $article_id . ( $seq_no > 1 ? '_' . $seq_no : '' ) . '"';

				$article_data = $this->is_ordering_wizard_on ? 'data-kb-article-id=' . $article_id . ' data-kb-type=' . $data_kb_type : '';

				/** DISPLAY ARTICLE LINK */      ?>
				<li class="<?php echo $hide_class; ?>" <?php echo $article_data . ' ' . $style2 . ' ' . $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?> >   <?php
					$this->single_article_link( $article_title, $article_id, $seq_no ); ?>
				</li> <?php
			}

			// if article list is longer than initial article list size then show expand/collapse message
			if ( $article_num > $nof_articles_displayed ) {	?>
				<li class="elay-show-all-articles" <?php echo $this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ); ?> aria-expanded="false">
					<span class="elay-show-text">
						<span><?php echo esc_html( $this->kb_config['sidebar_show_all_articles_msg'] ) . '</span> ( ' . ( $article_num - $nof_articles_displayed ); ?> )
					</span>
					<span class="elay-hide-text elay-hide-elem"><?php echo esc_html( $this->kb_config['sidebar_collapse_articles_msg'] ); ?></span>
				</li>					<?php
			}

			if ( $article_num == 0 ) {
				echo '<li '.$this->get_inline_style( 'padding-bottom:: article_list_spacing,padding-top::article_list_spacing' ) . 'class="elay-articles-coming-soon">' .
					esc_html__( $articles_coming_soon_msg, 'echo-elegant-layouts' ) . '</li>';
			} ?>

		</ul> <?php
	}

	/**
	 * Returns inline styles
	 *
	 * @param $output
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $output, $kb_config ) {

		// Container
		$container_background_Color     = $kb_config['article-content-background-color-v2'];
		$container_border_Color         = $kb_config['sidebar_section_border_color'];
		$container_border_Width         = $kb_config['sidebar_section_border_width'];
		$container_border_Radius        = $kb_config['sidebar_section_border_radius'];
		$sidebar_side_bar_height        = $kb_config['sidebar_side_bar_height'];
		$sidebar_background_color       = $kb_config['sidebar_background_color'];

		// Category Heading
		$catHeading_alignment           = $kb_config['sidebar_section_head_alignment'];
		$catHeading_dividerThickness    = $kb_config['sidebar_section_divider_thickness'];
		$catHeading_paddingTop          = $kb_config['sidebar_section_head_padding_top'];
		$catHeading_paddingBottom       = $kb_config['sidebar_section_head_padding_bottom'];
		$catHeading_paddingLeft         = $kb_config['sidebar_section_head_padding_left'];
		$catHeading_paddingRight        = $kb_config['sidebar_section_head_padding_right'];
		$catHeading_dividerColor        = $kb_config['sidebar_section_divider_color'];
		$catHeading_BackgroundColor     = $kb_config['sidebar_section_head_background_color'];

		// Category Heading - Inner
		$catHeadingInner_fontColor      = $kb_config['sidebar_section_head_font_color'];
		$catHeadingInner_TextAlignment  = $kb_config['sidebar_section_head_alignment'];
		$catHeadingInner_DescColor      = $kb_config['sidebar_section_head_description_font_color'];

		// Category Body
		$catBodyContainer_paddingTop    = $kb_config['sidebar_section_body_padding_top'];
		$catBodyContainer_paddingBottom = $kb_config['sidebar_section_body_padding_bottom'];
		$catBodyContainer_paddingLeft   = $kb_config['sidebar_section_body_padding_left'];
		$catBodyContainer_paddingRight  = $kb_config['sidebar_section_body_padding_right'];
		$subCategory_padding            = $kb_config['article_list_spacing'] + 2;

		$catBodyContainer_BodyHeight    = $kb_config['sidebar_section_body_height'];

		// Article
		$article_Font_color             = $kb_config['sidebar_article_font_color'];
		$article_Font_Active_color      = $kb_config['sidebar_article_active_font_color'];
		$article_Font_BackgroundColor   = $kb_config['sidebar_article_active_background_color'];

		$sub_cat_font_family            = $kb_config['general_typography']['font-family'];
		$sub_cat_font_weight            = $kb_config['sidebar_section_subcategory_typography']['font-weight'];
		$sub_cat_font_size              = $kb_config['sidebar_section_subcategory_typography']['font-size'];
		$sub_cat_color                  = $kb_config['sidebar_section_category_font_color'];

		// Category Main Category

		// Category Sub Category
		$catBodySubCatArticleMargin     = $kb_config['sidebar_article_list_margin'];

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		$overflow = 'initial';
		$max_height = 'initial';
		if ( $kb_config['sidebar_side_bar_height_mode'] == 'side_bar_fixed_height' ) {
			$overflow = 'auto';
			$max_height = $sidebar_side_bar_height . 'px;';
		}

		// Container -----------------------------------------/
		$output .= '
			#elay-sidebar-container-v2,
            #epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner {
                border-color:       ' . $container_border_Color . ';
                border-width:       ' . $container_border_Width . 'px;
                border-radius:      ' . $container_border_Radius . 'px;
                overflow:           ' . $overflow . ';
                max-height:         ' . $max_height . ';
            }
            #elay-sidebar-container-v2 {
                background-color:   ' . $sidebar_background_color . ';
            }
            #epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner {
                background-color:   ' . $container_background_Color . ';
            }';

		// Headings  -----------------------------------------/
		$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__heading-container,
            #epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__heading-container {
                text-align:                 ' . $catHeading_alignment .';
                border-width:               ' . $catHeading_dividerThickness .'px;
                padding-top:                ' . $catHeading_paddingTop .'px;
                padding-bottom:             ' . $catHeading_paddingBottom .'px;
                padding-left:               ' . $catHeading_paddingLeft .'px;
                padding-right:              ' . $catHeading_paddingRight .'px;
                border-bottom-color:        ' . $catHeading_dividerColor .';
                background-color:           ' . $catHeading_BackgroundColor .';
            } ';
		
		if ( $catHeading_alignment == 'right' ) {
			$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner__name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner__name,
			.elay-sidebar--TopCat-on .elay-sidebar__cat__top-cat__heading-container,
			.elay-sidebar--TopCat-on .elay-sidebar__cat__top-cat__heading-container {
				flex-direction: row-reverse;
			}
			
			#elay-sidebar-container-v2 .elay-sidebar__cat-container .elay-sidebar__cat__top-cat .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat-container .elay-sidebar__cat__top-cat .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name {
				flex-direction: row-reverse;
			}
			
			.elay-category-level-2-3 {
				align-items: center;
				display: flex;
				flex-direction: row-reverse;
			}
			
			#elay-sidebar-container-v2 .elay-articles .elay-article-title,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-articles .elay-article-title {
				display: flex;
				flex-direction: row-reverse;
			}
			
			#elay-sidebar-container-v2 .elay-articles .elay-article-title__icon,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-articles .elay-article-title__icon {
				position: static;
			}
			
			#elay-sidebar-container-v2 .elay-sidebar__cat-container .elay_sidebar_expand_category_icon,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat-container .elay_sidebar_expand_category_icon {
				transform: scaleX(-1);
				margin-left: 5px;
				margin-right: -5px;
			}
			
			#elay-sidebar-container-v2 .elay-articles .elay-article-title__text,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-articles .elay-article-title__text {
				margin-right: 5px;
				margin-left: 0;
				text-align: right;
			}

			#elay-sidebar-container-v2 .elay-sidebar__cat-container li .active,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat-container li .active {
				margin-right: -5px !important;
				margin-left: 0 !important;
			}

			.elay_sidebar_expand_category_icon {
				padding-left: 5px !important;
				padding-right: 0px !important;
			}
			
			#elay-sidebar-container-v2 .elay-sidebar__cat-container .elay-sidebar__cat__top-cat .elay-sidebar__cat__top-cat__body-container .elay-sidebar__body__main-cat,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat-container .elay-sidebar__cat__top-cat .elay-sidebar__cat__top-cat__body-container .elay-sidebar__body__main-cat {
				padding-' . ( is_rtl() ? 'right' : 'left'  ) . ': 0 !important; 
				padding-' . ( is_rtl() ? 'left' : 'right' ) . ': 0 !important;
			} ';

		} else if ( $catHeading_alignment == 'center' ) {

			$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner__name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__heading-container .elay-sidebar__heading__inner__name {
				justify-content: center;
			}
			
			.elay-sidebar--TopCat-on .elay-sidebar__cat__top-cat__heading-container {
				justify-content: center;
			} ';
		}

		// First Category Heading
		$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat:first-child .elay-sidebar__cat__top-cat__heading-container,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat:first-child .elay-sidebar__cat__top-cat__heading-container {
				border-top-left-radius:     ' . $container_border_Radius . 'px;
				border-top-right-radius:    ' . $container_border_Radius . 'px;
			} ';

		// Last Category Heading
		$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat:last-child .elay-sidebar__cat__top-cat__heading-container,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat:last-child .elay-sidebar__cat__top-cat__heading-container {
				border-bottom-left-radius:     ' . $container_border_Radius . 'px;
				border-bottom-right-radius:    ' . $container_border_Radius . 'px;
			}

			#elay-sidebar-container-v2 .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name,
			#elay-sidebar-container-v2 .elay-sidebar__heading__inner .elay-sidebar__heading__inner__cat-name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__heading__inner .elay-sidebar__heading__inner__cat-name,
			#elay-sidebar-container-v2 .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name>a,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__heading__inner .elay-sidebar__heading__inner__name>a {
				color:                  ' . $catHeadingInner_fontColor .';
				text-align:             ' . $catHeadingInner_TextAlignment .';
			}

			#elay-sidebar-container-v2 .elay-sidebar__heading__inner .elay-sidebar__heading__inner__desc p,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__heading__inner .elay-sidebar__heading__inner__desc p {
				color:                  ' . $catHeadingInner_DescColor .';
				text-align:             ' . $catHeadingInner_TextAlignment .';
			} ';

		// Category Body
		$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__body-container,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__body-container {
				padding-top:            ' . $catBodyContainer_paddingTop . 'px;
				padding-bottom:         ' . $catBodyContainer_paddingBottom . 'px;
				padding-left:           ' . $catBodyContainer_paddingLeft . 'px;
				padding-right:          ' . $catBodyContainer_paddingRight . 'px;
			}
			#elay-sidebar-container-v2 .elay-sidebar__body__sub-cat .elay-category-level-2-3 {
			    padding-top: ' . $subCategory_padding . 'px;
			    padding-bottom: ' . $subCategory_padding . 'px;
			}  ';
		
		if ( $kb_config['sidebar_section_box_height_mode'] == 'section_min_height' ) {
			$output .= '
				#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__body-container,
				#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__body-container {
					min-height:             ' . $catBodyContainer_BodyHeight . ';
				} ';
		} else if ( $kb_config['sidebar_section_box_height_mode'] == 'section_fixed_height' ) {
			$output .= '
				#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__body-container,
				#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__body-container {
					overflow: auto;
					height:             ' . $catBodyContainer_BodyHeight . ';
				} ';
		}
		// Category Sub Category
		$output .= '
			#elay-sidebar-container-v2 .elay-sidebar__cat__top-cat__body-container .elay-sidebar__body__sub-cat,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-sidebar__cat__top-cat__body-container .elay-sidebar__body__sub-cat {
				padding-left:           ' . $catBodySubCatArticleMargin . 'px;
			} ';

		// Article
		$output .= '
			.elay-sidebar__cat__top-cat__body-container .elay-articles .elay-article-title {
				color:                      ' . $article_Font_color . ';
			}
			.elay-sidebar__cat__top-cat__body-container .elay-articles .active {
				color:                      ' . $article_Font_Active_color . ';
				background-color:           ' . $article_Font_BackgroundColor . ';
			}
			.elay-sidebar__cat__top-cat__body-container .elay-articles .active .elay-article-title {
				color:                      ' . $article_Font_Active_color . ';
			} ';

		$font_family = '';
		$font_weight = '';
		$font_color  = '';
		$font_size   = '';
		if ( $sub_cat_font_family ) {
			$font_family = 'font-family: ' . $sub_cat_font_family . ' !important;';
		}
		if ( $sub_cat_font_weight ) {
			$font_weight = 'font-weight: ' . $sub_cat_font_weight . ' !important;';
		}
		if ( $sub_cat_color ) {
			$font_color = 'color: ' . $sub_cat_color . ' !important;';
		}
		if ( $sub_cat_font_size ) {
			$font_size = 'font-size: ' . $sub_cat_font_size . 'px !important;';
		}

		$output .= '
			#elay-sidebar-container-v2 .elay-category-level-2-3__cat-name,
			#epkb-ml-sidebar-layout #epkb-ml-sidebar-layout-inner .elay-category-level-2-3__cat-name {
			' . $font_family . '
			' . $font_weight . '
			' . $font_color . '
			' . $font_size . '
		} ';

		return $output;
	}
}
