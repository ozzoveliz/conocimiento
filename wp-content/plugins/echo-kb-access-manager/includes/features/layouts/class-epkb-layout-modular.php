<?php

/**
 *  Outputs the Modular Layout for knowledge base main page.
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPKB_Layout_Modular extends EPKB_Layout {

	const MAX_ROWS = 5;

	/**
	 * Generate content of the KB main page
	 */
	protected function generate_kb_main_page() {    ?>
		<div id="epkb-modular-layout-container" role="main" aria-labelledby="Knowledge Base" class="epkb-css-full-reset <?php echo method_exists( 'EPKB_Utilities', 'get_active_theme_classes' ) ? EPKB_Utilities::get_active_theme_classes( 'mp' ) : ''; ?>">			<?php
			$this->display_modular_layout_container(); ?>
		</div>   <?php
	}

	/**
	 * Display KB Main Page content
	 */
	private function display_modular_layout_container() {

		// show message that articles are coming soon if the current KB does not have any Category
		if ( ! $this->has_kb_categories ) {
			$this->show_categories_missing_message();
			return;
		}

		// display rows of the Modular Layout
		for ( $row_number = 1; $row_number <= self::MAX_ROWS; $row_number ++ ) {

			if ( empty( $this->kb_config[ 'ml_row_' . $row_number . '_module' ] ) || $this->kb_config[ 'ml_row_' . $row_number . '_module' ] == 'none' ) {
				continue;
			} ?>

			<div id="epkb-ml__row-<?php echo esc_attr( $row_number ); ?>" class="epkb-ml__row">                <?php
				switch ( $this->kb_config[ 'ml_row_' . $row_number . '_module' ] ) {
					case 'search':
						self::search_module( $this->kb_config );
						break;
					case 'categories_articles':
						$this->categories_articles_module();
						break;
					case 'articles_list':
						$this->articles_list_module();
						break;
					case 'faqs':
						$this->faqs_module();
						break;
					default:
						break;
				} ?>
			</div>  <?php
		}
	}

	/**
	 * MODULE: Search
	 *
	 * @param $kb_config
	 */
	public static function search_module( $kb_config ) {

		// Advanced Search uses its own search box
		if ( EPKB_Utilities::is_advanced_search_enabled( $kb_config ) ) {
			do_action( 'eckb_advanced_search_box', $kb_config );
			return;
		}

		$layout = $kb_config['ml_search_layout'];
		$search_handler = new EPKB_ML_Search( $kb_config ); ?>

		<div id="epkb-ml__module-search" class="epkb-ml__module">   <?php

			switch ( $layout ) {
				case 'modern':
				default:
					$search_handler->display_modern_layout();
					break;

				case 'classic':
					$search_handler->display_classic_layout();
					break;
			} ?>

		</div>  <?php
	}

	/**
	 * MODULE: Categories and Articles
	 */
	private function categories_articles_module() {

		$layout = $this->kb_config['ml_categories_articles_layout'];

		$module_settings = array(
			'design'  => 'epkb-ml-' . $layout . '-layout--design-1',
			//'design' => 'epkb-ml-'.$layout.'-layout--design-' . $this->kb_config['ml_categories_articles_layout_' . $layout . '_design'],
			'height'  => $this->kb_config['ml_categories_articles_height_mode'],
			'columns' => $this->kb_config['ml_categories_columns'],
		);
		$categories_icons = $this->get_category_icons();
		$categories_articles_handler = new EPKB_ML_Categories_Articles( $this->kb_config, $this->category_seq_data, $this->articles_seq_data, $module_settings, $categories_icons );
		$categories_articles_sidebar_class = '';

		if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' ) {
			$categories_articles_sidebar_class = 'epkb-ml-cat-article-sidebar--active';
		} ?>

		<div id="epkb-ml__module-categories-articles" class="epkb-ml__module <?php echo $categories_articles_sidebar_class; ?>">  <?php

			// Display Left Sidebar
			if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' && $this->kb_config['ml_categories_articles_sidebar_location'] == 'left' ) {
				$this->display_categories_articles_sidebar();
			}

			// Display Categories & Articles Section
			switch ( $layout ) {
				case 'classic':
				default:
					$categories_articles_handler->display_classic_layout();
					break;
				case 'product':
					$categories_articles_handler->display_product_layout();
					break;
			}

			// Display Right Sidebar
			if ( $this->kb_config['ml_categories_articles_sidebar_toggle'] == 'on' && $this->kb_config['ml_categories_articles_sidebar_location'] == 'right' ) {
				$this->display_categories_articles_sidebar();
			} ?>

		</div>    <?php
	}

	/**
	 * Categories & Articles Sidebar
	 */
	private function display_categories_articles_sidebar() {

		$sidebar_location = 'epkb-ml-sidebar--' . $this->kb_config['ml_categories_articles_sidebar_location']         ?>

		<div id="epkb-ml-cat-article-sidebar" class="<?php echo 'epkb-ml-sidebar-' . esc_attr( $this->kb_config['ml_categories_articles_layout'] ) . '--design-1 ' . esc_attr( $sidebar_location ); ?>">			<?php

			// Sidebar Position 1
			switch ( $this->kb_config['ml_categories_articles_sidebar_position_1'] ) {

				case 'newest_articles':
					$this->display_sidebar_newest_articles();
					break;

				case 'recent_articles':
					$this->display_sidebar_recent_articles();
					break;

				default: break;
			}

			// Sidebar Position 2
			switch ( $this->kb_config['ml_categories_articles_sidebar_position_2'] ) {

				case 'newest_articles':
					$this->display_sidebar_newest_articles();
					break;

				case 'recent_articles':
					$this->display_sidebar_recent_articles();
					break;

				default: break;
			}   ?>

		</div>	<?php
	}

	/**
	 * Newest Articles list for Categories & Articles Sidebar
	 */
	private function display_sidebar_newest_articles() {

		$articles_list_handler = new EPKB_ML_New_Recent_Articles_List( $this->kb_config );
		$newest_articles = $articles_list_handler->execute_search( 'date' );    ?>

		<!-- Newest Articles -->
		<section id="epkb-ml-newest-articles" class="epkb-ml-article-section">
			<div class="epkb-ml-article-section__head"><?php esc_html_e( 'Newest articles', 'echo-knowledge-base' ); ?></div>
			<div class="epkb-ml-article-section__body">
				<ul class="epkb-ml-articles-list">  <?php
					foreach ( $newest_articles as $article ) { ?>
						<li><?php $articles_list_handler->single_article_link( $article ); ?></li><?php
					}   ?>
				</ul>
			</div>
		</section>  <?php
	}

	/**
	 * Recent Articles list for Categories & Articles Sidebar
	 */
	private function display_sidebar_recent_articles() {

		$articles_list_handler = new EPKB_ML_New_Recent_Articles_List( $this->kb_config );
		$recent_articles = $articles_list_handler->execute_search( 'modified' );    ?>

		<!-- Recent Articles -->
		<section id="epkb-ml-recent-articles" class="epkb-ml-article-section">
			<div class="epkb-ml-article-section__head"><?php esc_html_e( 'Recently updated', 'echo-knowledge-base' ); ?></div>
			<div class="epkb-ml-article-section__body">
				<ul class="epkb-ml-articles-list">  <?php
					foreach ( $recent_articles as $article ) { ?>
						<li><?php $articles_list_handler->single_article_link( $article ); ?></li><?php
					}   ?>
				</ul>
			</div>
		</section>			<?php
	}

	/**
	 * MODULE: New and Recent Articles List
	 */
	private function articles_list_module() { ?>
		<div id="epkb-ml__module-articles-list" class="epkb-ml__module">   <?php
			$articles_list_handler = new EPKB_ML_New_Recent_Articles_List( $this->kb_config );
			$articles_list_handler->display_articles_list(); ?>
		</div>  <?php
	}

	/**
	 * MODULE: FAQs
	 */
	private function faqs_module() { ?>
		<div id="epkb-ml__module-faqs" class="epkb-ml__module">   <?php
			$faqs_handler = new EPKB_ML_FAQs( $this->kb_config );
			$faqs_handler->display_faqs(); ?>
		</div>  <?php
	}

	/**
	 * Returns inline styles for Modular Layout
	 *
	 * @param $kb_config
	 * @param $is_article
	 *
	 * @return string
	 */
	public static function get_inline_styles( $kb_config, $is_article = false ) {

		$output = '
		/* CSS for Modular Layout
		-----------------------------------------------------------------------*/';

		for ( $row_number = 1; $row_number <= self::MAX_ROWS; $row_number ++ ) {

			if ( empty( $kb_config[ 'ml_row_' . $row_number . '_module' ] ) || $kb_config[ 'ml_row_' . $row_number . '_module' ] == 'none' ) {
				continue;
			}

			$output .= '
			#epkb-ml__row-' . $row_number . ' {
				max-width: ' . $kb_config['ml_row_' . $row_number . '_desktop_width'] . $kb_config['ml_row_' . $row_number . '_desktop_width_units'] . ';
			}';

			switch ( $kb_config[ 'ml_row_' . $row_number . '_module' ] ) {

				// CSS for Module: Search
				case 'search':
					$output .= EPKB_ML_Search::get_inline_styles( $kb_config, $is_article );
					break;

				// CSS for Module: Categories & Articles
				case 'categories_articles':
					$output .= EPKB_ML_Categories_Articles::get_inline_styles( $kb_config );
					break;

				// CSS for Module: New and Recent Articles List
				case 'articles_list':
					$output .= EPKB_ML_New_Recent_Articles_List::get_inline_styles( $kb_config );
					break;

				// CSS for Module: FAQs
				case 'faqs':
					$output .= EPKB_ML_FAQs::get_inline_styles( $kb_config );
					break;

				default:
					break;
			}
		}

		// render custom CSS at the end to give it higher priority
		$output .= '
		/* Custom CSS for Modular Layout
		-----------------------------------------------------------------------*/
		' . EPKB_Utilities::get_wp_option( 'epkb_ml_custom_css_' . $kb_config['id'], '' ) . ' 
		';

		return $output;
	}
}