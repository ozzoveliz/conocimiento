<?php

/**
 *  Outputs the Categories and Articles List module for Modular Layout.
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_ML_Categories_Articles {

	private $displayed_article_ids = array();

	private $kb_config;
	private $category_seq_data;
	private $articles_seq_data;
	private $module_settings;
	private $categories_icons;

	function __construct( $kb_config, $category_seq_data, $articles_seq_data, $module_settings, $categories_icons ) {
		$this->kb_config = $kb_config;
		$this->category_seq_data = $category_seq_data;
		$this->articles_seq_data = $articles_seq_data;
		$this->module_settings = $module_settings;
		$this->categories_icons = $categories_icons;
	}

	/**
	 * Display Classic Layout
	 */
	public function display_classic_layout() { ?>

		<div id="epkb-ml-classic-layout" class="<?php echo esc_attr( $this->module_settings['design'] ) . ' epkb-ml-classic-layout--height-' . esc_attr( $this->module_settings['height'] ) . ' epkb-ml-classic-layout--' . esc_attr( $this->module_settings['columns'] ); ?>">    <?php

			$cat_index = 1;
			$count_loops = 1;
			switch ( $this->module_settings['columns'] ) {
				case '2-col':
				default:
					$categories_per_row = 2;
					break;
				case '3-col':
					$categories_per_row = 3;
					break;
				case '4-col':
					$categories_per_row = 4;
					break;
			}

			foreach ( $this->category_seq_data as $category_id => $level_2_categories ) {

				$category_name = isset( $this->articles_seq_data[ $category_id ][0] ) ? $this->articles_seq_data[ $category_id ][0] : '';
				if ( empty( $category_name ) ) {
					continue;
				}

				$level_2_categories = is_array( $level_2_categories ) ? $level_2_categories : array();

				// retrieve level 1 articles
				$articles_level_1_list = array();
				if ( isset( $this->articles_seq_data[ $category_id ] ) ) {
					$articles_level_1_list = $this->articles_seq_data[ $category_id ];
					unset( $articles_level_1_list[0] );
					unset( $articles_level_1_list[1] );
				}

				if ( $cat_index == 1 ) { ?>
					<div class="epkb-ml__module-categories-articles__row">  <?php
				}

				self::display_classic_category( $category_id, $category_name, $articles_level_1_list, $level_2_categories, $this->categories_icons );

				if ( $cat_index == $categories_per_row || $count_loops == count( $this->category_seq_data ) ) { ?>
					</div>  <?php
					$cat_index = 0;
				}
				$cat_index ++;
				$count_loops ++;

			} ?>
		</div>  <?php
	}

	/**
	 * Display Product Layout
	 */
	public function display_product_layout() { ?>

		<div id="epkb-ml-product-layout" class="<?php echo esc_attr( $this->module_settings['design'] ) . ' epkb-ml-product-layout--height-' . esc_attr( $this->module_settings['height'] ); ?>">

			<!-- Top Level Categories -->
			<div class="epkb-ml-product-layout-categories-container">   <?php

				switch ( $this->module_settings['columns'] ) {
					case '2-col':
					default:
						$colClass = "epkb-ml-1-lvl-categories-button--2-col";
						break;
					case '3-col':
						$colClass = "epkb-ml-1-lvl-categories-button--3-col";
						break;
					case '4-col':
						$colClass = "epkb-ml-1-lvl-categories-button--4-col";
						break;
				}				?>

				<!-- 1st Level Categories Button -->
				<div class="epkb-ml-1-lvl-categories-button-container <?php echo esc_html( $colClass ); ?>">                    <?php
					foreach ( $this->category_seq_data as $category_id => $level_2_categories ) {

						$category_name = isset( $this->articles_seq_data[ $category_id ][0] ) ? $this->articles_seq_data[ $category_id ][0] : '';
						if ( empty( $category_name ) ) {
							continue;
						}

						$this->display_product_category_button_lvl_1( $category_id, $this->categories_icons, $category_name );
					} ?>
				</div>

				<!-- 1st Level Categories content -->
				<div class="epkb-ml-1-lvl-categories-content-container">

					<button class="epkb-back-button">
						<span class="epkb-back-button__icon epkbfa epkbfa-arrow-left"></span>
						<span class="epkb-back-button__text"><?php esc_html_e( 'Back', 'echo-knowledge-base' ); ?></span>
					</button>   <?php

					foreach ( $this->category_seq_data as $category_id => $level_2_categories ) {
						$this->display_product_category_content_lvl_1( $category_id, $this->categories_icons, $level_2_categories );
					}   ?>

				</div>

			</div>

		</div>  <?php
	}

	/**
	 * Display button of top category for Product Layout
	 *
	 * @param $category_id
	 * @param $categories_icons
	 * @param $category_name
	 */
	private function display_product_category_button_lvl_1( $category_id, $categories_icons, $category_name ) {

		$category_icon = EPKB_KB_Config_Category::get_category_icon( $category_id, $categories_icons );
		$category_title_tag = empty( $this->kb_config['ml_categories_articles_title_html_tag'] ) ? 'div' : $this->kb_config['ml_categories_articles_title_html_tag'];   ?>

		<section id="epkb-1-lvl-id-<?php echo $category_id; ?>" class="epkb-ml-1-lvl__cat-button">  <?php
			if ( $category_icon['type'] == 'image' ) { ?>
				<img class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--image"
				     src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $category_icon['image_alt'] ); ?>">  <?php
			} else { ?>
				<div class="epkb-ml-1-lvl__cat-icon epkb-ml-1-lvl__cat-icon--font epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>"></div>	<?php
			} ?>
			<<?php echo esc_html( $category_title_tag ); ?> class="epkb-ml-1-lvl__cat-title"><?php echo esc_html( $category_name ); ?></<?php echo esc_html( $category_title_tag ); ?>>
		</section>                    <?php
	}

	/**
	 * Display content of top category for Product Layout
	 *
	 * @param $category_id
	 * @param $categories_icons
	 * @param $level_2_categories
	 */
	private function display_product_category_content_lvl_1( $category_id, $categories_icons, $level_2_categories ) {

		$category_desc = isset( $this->articles_seq_data[ $category_id ][1] ) ? $this->articles_seq_data[ $category_id ][1] : '';

		// retrieve level 1 articles
		$articles_level_1_list = array();
		if ( isset( $this->articles_seq_data[ $category_id ] ) ) {
			$articles_level_1_list = $this->articles_seq_data[ $category_id ];
			unset( $articles_level_1_list[0] );
			unset( $articles_level_1_list[1] );
		}

		// If no Level 1 articles exist, add Class to make Category Desc full width.
		$noLevel1ArticlesClass = '';
		$noDescClass = '';
		$noArticlesClass = '';
		$articleColumns = 2;

		if ( empty( $articles_level_1_list ) ) {
			$noLevel1ArticlesClass = 'epkb-ml-1-lvl-desc-articles--no-articles';
		}

		if ( empty( $category_desc ) ) {
			$noDescClass = 'epkb-ml-1-lvl-desc-articles--no-desc';
			$articleColumns = 3;
		}

		// If no articles then add class for articles coming soon message.
		if ( empty( $articles_level_1_list ) && empty( $level_2_categories ) ) {
			$noArticlesClass = 'epkb-ml-1-lvl__cat-content--no-articles';
		}   ?>

		<div class="epkb-ml-1-lvl__cat-content <?php echo esc_html( $noArticlesClass ); ?>" data-cat-content="epkb-1-lvl-id-<?php echo esc_attr( $category_id ); ?>">   <?php

			if ( ! empty( $category_desc ) || ! empty( $articles_level_1_list ) ) { ?>

				<!-- Top Categories Description and articles -->
				<div class="epkb-ml-1-lvl-desc-articles <?php echo esc_html( $noLevel1ArticlesClass . ' ' . $noDescClass ); ?>">
					<div class="epkb-ml-1-lvl__desc"><?php echo esc_html( $category_desc ); ?></div>
					<div class="epkb-ml-1-lvl__articles">

						<div class="epkb-ml-articles-list">    <?php

							$total_article_index = 0;

							// calculate number of articles per column
							$articles_per_column[0] = ceil( count( $articles_level_1_list ) / $articleColumns );
							$articles_per_column[1] = $articleColumns > 2
								? ceil( ( count( $articles_level_1_list ) - $articles_per_column[0] ) / ( $articleColumns - 1 ) )
								: count( $articles_level_1_list ) - $articles_per_column[0];

							$column_count = 0;
							$column_articles_count = 0;
							$colNum = 1;

							// create a nested array of articles for each column
							$columns = array_fill( 0, $articleColumns, [] );
							foreach ( $articles_level_1_list as $article_id => $article_title ) {
								if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
									continue;
								}

								$this->displayed_article_ids[ $article_id ] = isset( $this->displayed_article_ids[ $article_id ] ) ? $this->displayed_article_ids[ $article_id ] + 1 : 1;
								$seq_no = $this->displayed_article_ids[ $article_id ];

								if ( isset( $articles_per_column[ $column_count ] ) && $column_articles_count >= $articles_per_column[ $column_count ] ) {
									$column_count ++;
									$column_articles_count = 0;
								}

								$columns[ $column_count ][] = [
									'title'  => $article_title,
									'id'     => $article_id,
									'seq_no' => $seq_no,
								];
								$column_articles_count ++;
							}

							// display the articles in the columns  ?>
							<div class="epkb-ml-articles-list epkb-total-columns-<?php echo $articleColumns; ?>">   <?php
								foreach ( $columns as $column_articles ) {
									$article_index = 0; ?>
									<ul class="epkb-list-column epkb-list-column-<?php echo $colNum; ?>">   <?php
										foreach ( $column_articles as $article ) {
											if ( ceil( $article_index * $articleColumns ) < $this->kb_config['ml_categories_articles_nof_articles_displayed'] ) { ?>
												<li><?php $this->single_article_link( $article['title'], $article['id'], '', $article['seq_no'] ); ?></li>  <?php
											} else { ?>
												<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article['title'], $article['id'], '', $article['seq_no'] ); ?></li>  <?php
											}
											$article_index ++;
											$total_article_index ++;
										} ?>
									</ul>   <?php
									$colNum ++;
								} ?>
							</div> <?php

							$additional_articles_number = $total_article_index - $this->kb_config['ml_categories_articles_nof_articles_displayed'];
							if ( $additional_articles_number > 0 ) { ?>
								<span class="epkb-ml-articles-show-more"><a href="#"><?php echo sprintf( esc_html__( 'Show more (%s more)', 'echo-knowledge-base' ), $additional_articles_number ); ?></a></span>    <?php
							} ?>
						</div>
					</div>
				</div>            <?php
			}

			// The Top Category and it's children are completely empty display articles coming soon message.
			if ( empty( $articles_level_1_list ) && empty( $level_2_categories ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html__( $articles_coming_soon_msg, 'echo-knowledge-base' ); ?></div>            <?php
			} ?>

		</div>

		<!-- 2nd Level Categories Button -->        <?php
		if ( ! empty( $level_2_categories ) ) { ?>
			<div class="epkb-ml-2-lvl-categories-button-container" data-cat-content="epkb-1-lvl-id-<?php echo esc_attr( $category_id ); ?>">  <?php
				foreach ( $level_2_categories as $level_2_category_id => $level_3_categories ) {

					$level_2_category_name = isset( $this->articles_seq_data[ $level_2_category_id ][0] ) ? $this->articles_seq_data[ $level_2_category_id ][0] : '';
					if ( empty( $level_2_category_name ) ) {
						continue;
					}

					$this->display_product_category_button_lvl_2( $level_2_category_id, $categories_icons, $level_2_category_name );
				} ?>
			</div>        <?php
		} ?>

		<!-- 2nd Level Categories content -->   <?php
		foreach ( $level_2_categories as $level_2_category_id => $level_3_categories ) {
			$this->display_product_category_content_lvl_2( $level_2_category_id, $categories_icons, $level_3_categories );
		}
	}

	/**
	 * Display button of second level category for Product Layout
	 *
	 * @param $level_2_category_id
	 * @param $categories_icons
	 * @param $level_2_category_name
	 */
	private function display_product_category_button_lvl_2( $level_2_category_id, $categories_icons, $level_2_category_name ) {

		$level_2_category_icon = EPKB_KB_Config_Category::get_category_icon( $level_2_category_id, $categories_icons ); ?>

		<section id="epkb-ml-2-lvl-<?php echo $level_2_category_id; ?>" class="epkb-ml-2-lvl__cat-button">  <?php
			if ( $level_2_category_icon['type'] == 'image' ) { ?>
				<img class="epkb-ml-2-lvl__cat-icon epkb-ml-2-lvl__cat-icon--image"
				     src="<?php echo esc_url( $level_2_category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $level_2_category_icon['image_alt'] ); ?>">  <?php
			} else { ?>
				<div class="epkb-ml-2-lvl__cat-icon epkb-ml-2-lvl__cat-icon--font epkbfa <?php echo esc_attr( $level_2_category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $level_2_category_icon['name'] ); ?>"></div>    <?php
			} ?>
			<div class="epkb-ml-2-lvl__cat-title"><?php echo esc_html( $level_2_category_name ); ?></div>
		</section>  <?php
	}

	/**
	 * Display content of second level category for Product Layout
	 *
	 * @param $level_2_category_id
	 * @param $categories_icons
	 * @param $level_3_categories
	 */
	private function display_product_category_content_lvl_2( $level_2_category_id, $categories_icons, $level_3_categories ) {

		$level_2_category_desc = isset( $this->articles_seq_data[ $level_2_category_id ][1] ) ? $this->articles_seq_data[ $level_2_category_id ][1] : '';

		// retrieve level 2 articles
		$articles_level_2_list = array();
		if ( isset( $this->articles_seq_data[ $level_2_category_id ] ) ) {
			$articles_level_2_list = $this->articles_seq_data[ $level_2_category_id ];
			unset( $articles_level_2_list[0] );
			unset( $articles_level_2_list[1] );
		}

		$articleColumns = 2; ?>

		<div class="epkb-ml-2-lvl__cat-content" data-cat-content="epkb-ml-2-lvl-<?php echo esc_attr( $level_2_category_id ); ?>">            <?php

			if ( ! empty( $level_2_category_desc ) ) { ?>
				<div class="epkb-ml-2-lvl__desc"><?php echo esc_html( $level_2_category_desc ); ?></div>            <?php
			}

			if ( empty( $articles_level_2_list ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html__( $articles_coming_soon_msg, 'echo-knowledge-base' ); ?></div> <?php
			} ?>

			<div class="epkb-ml-2-lvl__articles">
				<ul class="epkb-ml-articles-list">    <?php
					$total_article_index = 0;
					$articles_per_column = ceil( count( $articles_level_2_list ) / $articleColumns ); // calculate number of articles per column
					$column_count = 0;
					$column_articles_count = 0;
					$colNum = 1;

					// create a nested array of articles for each column
					$columns = array_fill( 0, $articleColumns, [] );
					foreach ( $articles_level_2_list as $article_id => $article_title ) {

						if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
							continue;
						}

						$this->displayed_article_ids[ $article_id ] = isset( $this->displayed_article_ids[ $article_id ] ) ? $this->displayed_article_ids[ $article_id ] + 1 : 1;
						$seq_no = $this->displayed_article_ids[ $article_id ];

						if ( $column_articles_count >= $articles_per_column ) {
							$column_count ++;
							$column_articles_count = 0;
						}

						$columns[ $column_count ][] = [
							'title'  => $article_title,
							'id'     => $article_id,
							'seq_no' => $seq_no,
						];
						$column_articles_count ++;
					}

					// display the articles in the columns  ?>
					<div class="epkb-ml-articles-list epkb-total-columns-<?php echo $articleColumns; ?>">   <?php
						foreach ( $columns as $column_articles ) {
							$article_index = 0; ?>
							<ul class="epkb-list-column epkb-list-column-<?php echo $colNum; ?>">   <?php
								foreach ( $column_articles as $article ) {
									if ( ceil( $article_index * $articleColumns ) < $this->kb_config['ml_categories_articles_nof_articles_displayed'] ) { ?>
										<li><?php $this->single_article_link( $article['title'], $article['id'], '', $article['seq_no'] ); ?></li>  <?php
									} else { ?>
										<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article['title'], $article['id'], '', $article['seq_no'] ); ?></li>  <?php
									}
									$article_index ++;
									$total_article_index ++;
								} ?>
							</ul>   <?php
							$colNum ++;
						} ?>
					</div> <?php

					$additional_articles_number = $total_article_index - $this->kb_config['ml_categories_articles_nof_articles_displayed'];
					if ( $additional_articles_number > 0 ) { ?>
						<span class="epkb-ml-articles-show-more"><a	href="#"><?php echo sprintf( esc_html__( 'Show more (%s more)', 'echo-knowledge-base' ), $additional_articles_number ); ?></a></span>    <?php
					} ?>
			</div>


			<!-- 3rd Level Categories content -->
			<div class="epkb-ml-3-lvl-cat-container">   <?php

				foreach ( $level_3_categories as $level_3_category_id => $level_4_categories ) {

					$level_3_category_name = isset( $this->articles_seq_data[ $level_3_category_id ][0] ) ? $this->articles_seq_data[ $level_3_category_id ][0] : '';
					if ( empty( $level_3_category_name ) ) {
						continue;
					}

					$this->display_product_category_content_lvl_3( $level_3_category_id, $categories_icons, $level_3_category_name );
				} ?>

			</div>
		</div>  <?php
	}

	/**
	 * Display content of third level category for Product Layout
	 *
	 * @param $level_3_category_id
	 * @param $categories_icons
	 * @param $level_3_category_name
	 */
	private function display_product_category_content_lvl_3( $level_3_category_id, $categories_icons, $level_3_category_name ) {

		// retrieve level 3 articles
		$articles_level_3_list = array();
		if ( isset( $this->articles_seq_data[ $level_3_category_id ] ) ) {
			$articles_level_3_list = $this->articles_seq_data[ $level_3_category_id ];
			unset( $articles_level_3_list[0] );
			unset( $articles_level_3_list[1] );
		}

		$sub_sub_category_icon = EPKB_KB_Config_Category::get_category_icon( $level_3_category_id, $categories_icons ); ?>

		<div class="epkb-ml-3-lvl__category">
			<section class="epkb-ml-3-lvl__title-container">                <?php
				if ( $sub_sub_category_icon['type'] == 'image' ) { ?>
					<img class="epkb-ml-3-lvl__cat-icon epkb-ml-3-lvl__cat-icon--image"
					     src="<?php echo esc_url( $sub_sub_category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $sub_sub_category_icon['image_alt'] ); ?>">    <?php
				} else { ?>
					<div class="epkb-ml-3-lvl__cat-icon epkb-ml-3-lvl__cat-icon--font epkbfa <?php echo esc_attr( $sub_sub_category_icon['name'] ); ?>"	data-kb-category-icon="<?php echo esc_attr( $sub_sub_category_icon['name'] ); ?>"></div>    <?php
				} ?>
				<div class="epkb-ml-3-lvl__text"><?php echo esc_html( $level_3_category_name ); ?></div>
			</section> <?php

			if ( empty( $articles_level_3_list ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<div class="epkb-ml-articles-coming-soon"><?php echo esc_html__( $articles_coming_soon_msg, 'echo-knowledge-base' ); ?></div> <?php
			} ?>

			<!-- Visible Articles -->
			<ul class="epkb-ml-articles-list">    <?php
				$article_index = 0;
				foreach ( $articles_level_3_list as $article_id => $article_title ) {

					if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
						continue;
					}

					$this->displayed_article_ids[ $article_id ] = isset( $this->displayed_article_ids[ $article_id ] ) ? $this->displayed_article_ids[ $article_id ] + 1 : 1;
					$seq_no = $this->displayed_article_ids[ $article_id ];

					if ( $article_index < $this->kb_config['ml_categories_articles_nof_articles_displayed'] ) { ?>
						<li><?php $this->single_article_link( $article_title, $article_id, '', $seq_no ); ?></li>    <?php
					} else { ?>
						<li class="epkb-ml-article-hide"><?php $this->single_article_link( $article_title, $article_id, '', $seq_no ); ?></li>   <?php
					}

					$article_index ++;
				} ?>
			</ul> <?php

			$additional_articles_number = $article_index - $this->kb_config['ml_categories_articles_nof_articles_displayed'];
			if ( $additional_articles_number > 0 ) { ?>
				<span class="epkb-ml-articles-show-more"><a href="#"><?php echo sprintf( esc_html__( 'Show more (%s more)', 'echo-knowledge-base' ), $additional_articles_number ); ?></a></span>    <?php
			} ?>
		</div> <?php
	}

	/**
	 * Display HTML of single Category for Classic layout
	 *
	 * @param $category_id
	 * @param $category_name
	 * @param $level_1_articles
	 * @param $level_2_categories
	 * @param $categories_icons
	 */
	private function display_classic_category( $category_id, $category_name, $level_1_articles, $level_2_categories, $categories_icons ) {

		$category_icon = EPKB_KB_Config_Category::get_category_icon( $category_id, $categories_icons );
		$category_title_tag = empty( $this->kb_config['ml_categories_articles_title_html_tag'] ) ? 'div' : $this->kb_config['ml_categories_articles_title_html_tag'];
		$category_desc = isset( $this->articles_seq_data[ $category_id ][1] ) ? $this->articles_seq_data[ $category_id ][1] : '';
		$category_articles_number = 0;
		$use_show_more = $this->kb_config['ml_categories_articles_collapse_categories'] == 'all_collapsed'; ?>

		<!-- Category Section -->
		<section class="epkb-category-section">

			<!-- Section Head -->
			<div class="epkb-category-section__head">

				<!-- Icon -->
				<div class="epkb-category-section__head_icon">  <?php

					if ( $category_icon['type'] == 'image' ) { ?>
						<img class="epkb-cat-icon epkb-cat-icon--image "
						     src="<?php echo esc_url( $category_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $category_icon['image_alt'] ); ?>">								<?php
					} else { ?>
						<span class="epkb-cat-icon epkb-cat-icon--font epkbfa <?php echo esc_attr( $category_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $category_icon['name'] ); ?>"></span>    <?php
					} ?>
				</div>

				<!-- Category Name -->
				<div class="epkb-category-section__head_title">
					<<?php echo esc_html( $category_title_tag ); ?> class="epkb-category-section__head_title__text"><?php echo esc_html( $category_name ); ?></<?php echo esc_html( $category_title_tag ); ?>>
			</div>
			<!-- Category Description -->
			<div class="epkb-category-section__head_desc">
				<?php echo esc_html( $category_desc ); ?>
			</div>

			<!-- Section Body -->
			<div class="epkb-category-section__body<?php echo $use_show_more ? ' ' . 'epkb-category-section__body--collapsed' : ''; ?>">
				<div class="epkb-main-articles">
					<ul class="epkb-ml-articles-list">    <?php
						foreach ( $level_1_articles as $article_id => $article_title ) {

							if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
								continue;
							}

							$this->displayed_article_ids[ $article_id ] = isset( $this->displayed_article_ids[ $article_id ] ) ? $this->displayed_article_ids[ $article_id ] + 1 : 1;
							$seq_no = $this->displayed_article_ids[ $article_id ]; ?>

							<li><?php $this->single_article_link( $article_title, $article_id, '', $seq_no ); ?></li>    <?php

							$category_articles_number ++;
						} ?>
					</ul>
				</div> <?php

				if ( ! empty( $level_2_categories ) ) { ?>
					<!-- Level 2 Categories -->
					<div class="epkb-ml-2-lvl-categories<?php echo $use_show_more ? ' ' . 'epkb-ml-2-lvl-categories--collapsed' : ''; ?>">   <?php

						foreach ( $level_2_categories as $level_2_cat_id => $level_3_categories ) {

							// level 2 category container start
							echo '<div class="epkb-ml-2-lvl-category-container' . ( $use_show_more ? '' : ' ' . 'epkb-ml-2-lvl-category--active' ) . '">';

							$this->display_classic_sub_category( 2, $level_2_cat_id, $categories_icons, $category_articles_number );

							if ( ! empty( $level_3_categories ) ) { ?>

								<!-- Level 3 Categories -->
								<div class="epkb-ml-3-lvl-categories<?php echo $use_show_more ? ' ' . 'epkb-ml-3-lvl-categories--collapsed' : ''; ?>">  <?php

									foreach ( $level_3_categories as $level_3_cat_id => $level_4_categories ) {

										// level 3 category container start
										echo '<div class="epkb-ml-3-lvl-category-container' . ( $use_show_more ? '' : ' ' . 'epkb-ml-3-lvl-category--active' ) . '">';

										$this->display_classic_sub_category( 3, $level_3_cat_id, $categories_icons, $category_articles_number );

										if ( ! empty( $level_4_categories ) ) { ?>

											<!-- Level 4 Categories -->
											<div class="epkb-ml-4-lvl-categories<?php echo $use_show_more ? ' ' . 'epkb-ml-4-lvl-categories--collapsed' : ''; ?>">  <?php

												foreach ( $level_4_categories as $level_4_cat_id => $level_5_categories ) {

													// level 4 category container start
													echo '<div class="epkb-ml-4-lvl-category-container' . ( $use_show_more ? '' : ' ' . 'epkb-ml-4-lvl-category--active' ) . '">';

													$this->display_classic_sub_category( 4, $level_4_cat_id, $categories_icons, $category_articles_number );

													if ( ! empty( $level_5_categories ) ) { ?>

														<!-- Level 5 Categories -->
														<div class="epkb-ml-5-lvl-categories<?php echo $use_show_more ? ' ' . 'epkb-ml-5-lvl-categories--collapsed' : ''; ?>">  <?php

															foreach ( $level_5_categories as $level_5_cat_id => $level_6_categories ) {

																// level 5 category container start
																echo '<div class="epkb-ml-5-lvl-category-container' . ( $use_show_more ? '' : ' ' . 'epkb-ml-5-lvl-category--active' ) . '">';

																$this->display_classic_sub_category( 5, $level_5_cat_id, $categories_icons, $category_articles_number );

																// level 5 category container end
																echo '</div>';
															} ?>

														</div>  <?php
													}

													// level 4 category container end
													echo '</div>';
												} ?>

											</div>  <?php
										}

										// level 3 category container end
										echo '</div>';
									} ?>

								</div>  <?php
							}

							// level 2 category container end
							echo '</div>';
						} ?>

					</div>  <?php
				} ?>

			</div>  <?php

			if ( $category_articles_number == 0 && empty( $level_2_categories ) ) {
				$articles_coming_soon_msg = $this->kb_config['category_empty_msg']; ?>
				<!-- Section Footer -->
				<div class="epkb-category-section__footer">
					<div class="epkb-ml-articles-coming-soon">
						<?php echo esc_html__( $articles_coming_soon_msg, 'echo-knowledge-base' ); ?>
					</div>
				</div>          <?php
			} else if ( $use_show_more ) {  ?>
				<!-- Section Footer -->
				<div class="epkb-category-section__footer">
					<div class="epkb-ml-article-count"><?php echo $category_articles_number . ' ' . sprintf( esc_html__( 'ARTICLES', 'echo-knowledge-base' ) ); ?></div>
					<div class="epkb-ml-articles-show-more">
						<span class="epkbfa epkbfa-plus epkb-ml-articles-show-more__show-more__icon"></span>
					</div>
				</div>          <?php
			}   ?>
		</section>  <?php
	}

	/**
	 * Display Sub Category and its Articles for Classic Layout - used for 2nd, 3rd, 4th, 5th levels
	 *
	 * @param $cat_level
	 * @param $sub_cat_id
	 * @param $categories_icons
	 * @param $category_articles_number
	 */
	private function display_classic_sub_category( $cat_level, $sub_cat_id, $categories_icons, &$category_articles_number ) {

		$sub_category_name = isset( $this->articles_seq_data[ $sub_cat_id ][0] ) ? $this->articles_seq_data[ $sub_cat_id ][0] : '';
		if ( empty( $sub_category_name ) ) {
			return;
		}

		$sub_cat_icon = EPKB_KB_Config_Category::get_category_icon( $sub_cat_id, $categories_icons );
		$use_show_more = $this->kb_config['ml_categories_articles_collapse_categories'] == 'all_collapsed'; ?>

		<div class="epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-category__name">
            <span class="epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-category__icon">   <?php
	            if ( $sub_cat_icon['type'] == 'image' ) { ?>
		            <img class="epkb-cat-icon epkb-cat-icon--image" src="<?php echo esc_url( $sub_cat_icon['image_thumbnail_url'] ); ?>" alt="<?php echo esc_attr( $sub_cat_icon['image_alt'] ); ?>"> <?php
	            } else { ?>
		            <span class="epkb-cat-icon epkbfa <?php echo esc_html( $sub_cat_icon['name'] ); ?>" data-kb-category-icon="<?php echo esc_attr( $sub_cat_icon['name'] ); ?>"></span>    <?php
	            } ?>
            </span>
			<span class="epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-category__text"><?php echo esc_html( $sub_category_name ); ?></span> <?php
			if ( $use_show_more ) { ?>
				<div class="epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-category__show-more"><span class="epkbfa epkbfa-plus epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-category__show-more__icon"></span></div>   <?php
			}   ?>
		</div>    <?php

		// Retrieve sub articles
		$sub_articles = array();
		if ( isset( $this->articles_seq_data[ $sub_cat_id ] ) ) {
			$sub_articles = $this->articles_seq_data[ $sub_cat_id ];
			unset( $sub_articles[0] );
			unset( $sub_articles[1] );
		} ?>

		<ul class="epkb-ml-<?php echo esc_attr( $cat_level ); ?>-lvl-article-list<?php echo $use_show_more ? ' ' . 'epkb-ml-' . esc_attr( $cat_level ) . '-lvl-article-list--collapsed' : ''; ?>"> <?php

			foreach ( $sub_articles as $article_id => $article_title ) {

				if ( ! EPKB_Utilities::is_article_allowed_for_current_user( $article_id ) ) {
					continue;
				}

				$this->displayed_article_ids[ $article_id ] = isset( $this->displayed_article_ids[ $article_id ] ) ? $this->displayed_article_ids[ $article_id ] + 1 : 1;
				$seq_no = $this->displayed_article_ids[ $article_id ]; ?>

				<li><?php $this->single_article_link( $article_title, $article_id, '', $seq_no ); ?></li>    <?php

				$category_articles_number ++;
			} ?>
		</ul>   <?php
	}

	/**
	 * Display a link to a KB article.
	 *
	 * @param $title
	 * @param $article_id
	 * @param string $prefix
	 * @param string $seq_no
	 */
	private function single_article_link( $title, $article_id, $prefix = '', $seq_no = '' ) {

		if ( empty( $article_id ) ) {
			return;
		}

		// TODO Move to Style setting tag, <style id="epkb-module-layout-settings">
		$article_color = EPKB_Utilities::get_inline_style( 'color:: ' . $prefix . 'article_font_color', $this->kb_config );
		$icon_color = EPKB_Utilities::get_inline_style( 'color:: ' . $prefix . 'article_icon_color', $this->kb_config );

		// handle any add-on content TODO: apply
		if ( has_filter( 'eckb_single_article_filter' ) ) {
			$result = apply_filters('eckb_single_article_filter', $article_id, array( $this->kb_config['id'], $title, $article_color, $icon_color ) );
			if ( ! empty( $result ) && $result === true ) {
				return;
			}
		}

		$link = get_permalink( $article_id );
		if ( ! has_filter( 'article_with_seq_no_in_url_enable' ) ) {
			$link = empty( $seq_no ) || $seq_no < 2 ? $link : add_query_arg( 'seq_no', $seq_no, $link );
			$link = empty( $link ) || is_wp_error( $link ) ? '' : $link;
		} ?>

		<a href="<?php echo esc_url( $link ); ?>" class="epkb-ml-article-container" data-kb-article-id="<?php echo esc_attr( $article_id ); ?>">
			<span class="epkb-article-inner">
				<span class="epkb-article__icon ep_font_icon_document" aria-hidden="true"></span>
				<span class="epkb-article__text"><?php echo esc_html( $title ); ?></span>
			</span>
		</a> <?php
	}

	/**
	 * Returns inline styles for Categories & Articles Module
	 *
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $kb_config ) {

		$output = '
		/* CSS for Categories & Articles Module
		-----------------------------------------------------------------------*/';

		// Classic Layout ----------------------------------------------------------------------------/
		if ( $kb_config['ml_categories_articles_layout'] == 'classic' ) {
			if ( $kb_config['ml_categories_articles_height_mode'] == 'fixed' ) {
				$output .= '
				#epkb-ml__module-categories-articles .epkb-ml-classic-layout--height-fixed .epkb-category-section {
					min-height: ' . $kb_config['ml_categories_articles_fixed_height'] . 'px;
				}';
			}

			if ( $kb_config['ml_categories_articles_icon_background_color_toggle'] == 'off' ) {
				$output .= '
				#epkb-ml-classic-layout .epkb-category-section__head .epkb-cat-icon {
					background-color: transparent !important;
				}';
			}

			$output .= '
			#epkb-ml-classic-layout .epkb-category-section__head .epkb-cat-icon--font {
			        font-size: ' . $kb_config['ml_categories_articles_icon_size'] . 'px;
			}
			#epkb-ml-classic-layout .epkb-category-section__head .epkb-cat-icon--image {
			    background-color: ' . $kb_config['ml_categories_articles_icon_background_color'] . ';
			    width: ' . ( $kb_config['ml_categories_articles_icon_size'] + 20 ) . 'px;
			    height: ' . ( $kb_config['ml_categories_articles_icon_size'] + 20 ) . 'px;
			}
			#epkb-ml-classic-layout .epkb-category-section__head_title__text {
			    color: ' . $kb_config['ml_categories_articles_top_category_title_color'] . ';
			}';

			if ( $kb_config['ml_categories_articles_collapse_categories'] == 'all_collapsed' ) {
				$output .= '
				#epkb-ml-classic-layout .epkb-ml-articles-show-more {
				    border-color: ' . $kb_config['ml_categories_articles_icon_color'] . ';
				}
				#epkb-ml-classic-layout .epkb-ml-articles-show-more:hover {
				    color: #fff;
				    background-color: ' . $kb_config['ml_categories_articles_icon_color'] . ';
				}';
			}

			$output .= '
			#epkb-ml-classic-layout .epkb-category-section .epkb-category-section__head_icon .epkb-cat-icon--font {
			    color: ' . $kb_config['ml_categories_articles_icon_color'] . ';
			    background-color: ' . $kb_config['ml_categories_articles_icon_background_color'] . '; 
			}
			#epkb-ml-classic-layout .epkb-article-inner {
			    color: ' . $kb_config['ml_categories_articles_article_color'] . ';
			}
			#epkb-ml-classic-layout .epkb-category-section__head_desc {
			    color: ' . $kb_config['ml_categories_articles_cat_desc_color'] . ';
			}
			#epkb-ml-classic-layout .epkb-ml-2-lvl-category__icon .epkb-cat-icon,
			#epkb-ml-classic-layout .epkb-ml-2-lvl-category__text {
			    color: ' . $kb_config['ml_categories_articles_sub_category_color'] . ';
			}
			#epkb-ml-classic-layout .epkb-ml-3-lvl-category__icon .epkb-cat-icon,
			#epkb-ml-classic-layout .epkb-ml-3-lvl-category__text {
			    color: ' . $kb_config['ml_categories_articles_sub_category_color'] . ';
			}
			#epkb-ml-classic-layout .epkb-ml-4-lvl-category__icon .epkb-cat-icon,
			#epkb-ml-classic-layout .epkb-ml-4-lvl-category__text {
			    color: ' . $kb_config['ml_categories_articles_sub_category_color'] . ';
			}
			#epkb-ml-classic-layout .epkb-ml-5-lvl-category__icon .epkb-cat-icon,
			#epkb-ml-classic-layout .epkb-ml-5-lvl-category__text {
			    color: ' . $kb_config['ml_categories_articles_sub_category_color'] . ';
			} ';
		}

		// Product Layout ----------------------------------------------------------------------------/
		if ( $kb_config['ml_categories_articles_layout'] == 'product' ) {

			// Categories and Articles Module
			if ( $kb_config['ml_categories_articles_icon_background_color_toggle'] == 'off' ) {
				$output .= '
				#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon {
					background-color: transparent !important;
				}';
			}
			$output .= '

			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon--font {
			    font-size: ' . $kb_config['ml_categories_articles_icon_size'] . 'px;
			}  
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-title {
			    color: ' . $kb_config['ml_categories_articles_top_category_title_color'] . ';
			}    
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-icon {
			    color: ' . $kb_config['ml_categories_articles_icon_color'] . ';
			    background-color: ' . $kb_config['ml_categories_articles_icon_background_color'] . ';
			    width: ' . ( $kb_config['ml_categories_articles_icon_size'] + 40 ) . 'px;
			    height: ' . ( $kb_config['ml_categories_articles_icon_size'] + 40 ) . 'px;
			}     
			#epkb-ml-product-layout .epkb-article-inner {
			    color: ' . $kb_config['ml_categories_articles_article_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-content-container,
			#epkb-ml-product-layout .epkb-ml-2-lvl__cat-content {
			    background-color: ' . $kb_config['ml_categories_articles_article_bg_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-articles-show-more a {
			    color: ' . $kb_config['ml_categories_articles_article_show_more_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-1-lvl__cat-content .epkb-ml-1-lvl-desc-articles .epkb-ml-1-lvl__desc,
			#epkb-ml-product-layout .epkb-ml-2-lvl__cat-content .epkb-ml-2-lvl__desc,
			#epkb-ml-product-layout .epkb-ml-2-lvl__cat-content .epkb-ml-articles-coming-soon {
			    color: ' . $kb_config['ml_categories_articles_cat_desc_color'] . ';
			}
			#epkb-ml-product-layout .epkb-back-button {
			    background-color: ' . $kb_config['ml_categories_articles_back_button_bg_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-button,
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-button:hover {
			    border-color: ' . $kb_config['ml_categories_articles_border_color'] . ' !important;
			}
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-button--active,
			#epkb-ml-product-layout .epkb-ml-1-lvl-categories-button-container .epkb-ml-1-lvl__cat-button--active:hover {
			    box-shadow: 0 0 0 4px ' . $kb_config['ml_categories_articles_border_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-button,
			#epkb-ml-product-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-button:hover {
			    border-color: ' . $kb_config['ml_categories_articles_border_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-button--active {
			    box-shadow: 0px 1px 0 0px ' . $kb_config['ml_categories_articles_border_color'] . ';
			}
			#epkb-ml-product-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-button .epkb-ml-2-lvl__cat-icon,
			#epkb-ml-product-layout .epkb-ml-2-lvl-categories-button-container .epkb-ml-2-lvl__cat-button .epkb-ml-2-lvl__cat-title {
			    color: ' . $kb_config['ml_categories_articles_sub_category_color'] . ';
			}           
			';
		}

		// Sidebar ---------------------------------------------------------/
		if ( $kb_config['ml_categories_articles_sidebar_toggle'] == 'on' ) {

			// Find which Row the Categories Module is saved too.
			$module = '';
			foreach ( $kb_config as $key => $value ) {
				if ( $value === 'categories_articles' ) {
					$module = $key;
				}
			}

			$row_width = '';
			$row_units = '';

			// Get the Row Values based on which row the Category articles module has been assigned to.
			switch ( $module ) {
				case 'ml_row_1_module':
					$row_width = $kb_config['ml_row_1_desktop_width'];
					$row_units = $kb_config['ml_row_1_desktop_width_units'];
					break;
				case 'ml_row_2_module':
					$row_width = $kb_config['ml_row_2_desktop_width'];
					$row_units = $kb_config['ml_row_2_desktop_width_units'];
					break;
				case 'ml_row_3_module':
					$row_width = $kb_config['ml_row_3_desktop_width'];
					$row_units = $kb_config['ml_row_3_desktop_width_units'];
					break;
				case 'ml_row_4_module':
					$row_width = $kb_config['ml_row_4_desktop_width'];
					$row_units = $kb_config['ml_row_4_desktop_width_units'];
					break;
				case 'ml_row_5_module':
					$row_width = $kb_config['ml_row_5_desktop_width'];
					$row_units = $kb_config['ml_row_5_desktop_width_units'];
					break;
				default:
					break;
			}

			// Sidebar Specific
			$output .= '
			#epkb-ml-classic-layout,
			#epkb-ml-product-layout {
			   width: ' . ( $row_width - $kb_config['ml_categories_articles_sidebar_desktop_width'] ) . '' . $row_units . ';
			}
			#epkb-ml-cat-article-sidebar {
                width: ' . $kb_config['ml_categories_articles_sidebar_desktop_width'] . '' . $row_units . ';
			}';
			// Sidebar Elements
			$output .= '
			    #epkb-ml-cat-article-sidebar .epkb-ml-article-section .epkb-ml-article-section__head { 
			        color: ' . $kb_config['ml_categories_articles_top_category_title_color'] . ';
			    }
			    #epkb-ml-cat-article-sidebar .epkb-ml-article-section .epkb-article-inner { 
			        color: ' . $kb_config['ml_categories_articles_article_color'] . ';
			    }
			';

			if ( $kb_config['ml_categories_articles_layout'] == 'classic' ) {
				$output .= '';
			}
			if ( $kb_config['ml_categories_articles_layout'] == 'product' ) {
				$output .= '
				#epkb-ml-cat-article-sidebar .epkb-ml-article-section { 
				    border-color: ' . $kb_config['ml_categories_articles_border_color'] . ' !important;
				}
				';
			}

		}

		return $output;
	}
}