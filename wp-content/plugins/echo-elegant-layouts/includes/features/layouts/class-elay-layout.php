<?php

/**
 *
 * BASE THEME class that every theme should extend
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
abstract class ELAY_Layout {

	const BASIC_LAYOUT = 'Basic';
	const TABS_LAYOUT = 'Tabs';
	const CATEGORIES_LAYOUT = 'Categories';
	const CLASSIC_LAYOUT = 'Classic';
	const DRILL_DOWN_LAYOUT = 'Drill-Down';
	const SIDEBAR_LAYOUT = 'Sidebar';
	const GRID_LAYOUT = 'Grid';

	protected $kb_config;
	protected $kb_id;
	protected $category_seq_data;
	protected $articles_seq_data;
	protected $is_ordering_wizard_on = false;
	protected $has_kb_categories = true;
	protected $active_theme = 'unknown';
	protected $sidebar_loaded = false;

	/**
	 * Show the KB Main page with list of categories and articles
	 *
	 * @param $kb_config
	 * @param bool $is_ordering_wizard_on
	 * @param array $article_seq
	 * @param array $categories_seq
	 */
	public function display_kb_main_page( $kb_config, $is_ordering_wizard_on=false, $article_seq=array(), $categories_seq=array() ) {

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		$this->kb_config = $kb_config;
		$this->kb_id = $kb_config['id'];
		$this->is_ordering_wizard_on = $is_ordering_wizard_on;

		// set category and article sequence
		if ( $is_ordering_wizard_on && ! empty( $article_seq ) && ! empty( $categories_seq ) ) {
			$this->articles_seq_data = $article_seq;
			$this->category_seq_data = $categories_seq;
		} else {
			$this->category_seq_data = ELAY_Utilities::get_kb_option( $this->kb_id, ELAY_KB_Core::ELAY_CATEGORIES_SEQUENCE, array(), true );
			$this->articles_seq_data = ELAY_Utilities::get_kb_option( $this->kb_id, ELAY_KB_Core::ELAY_ARTICLES_SEQUENCE, array(), true );
		}

		// for WPML filter categories and articles given active language
		if ( ELAY_Utilities::is_wpml_enabled( $kb_config ) ) {
			$this->category_seq_data = ELAY_KB_Core::apply_category_language_filter( $this->category_seq_data );
			$this->articles_seq_data = ELAY_KB_Core::apply_article_language_filter( $this->articles_seq_data );
		}

		// check we have categories defined
		$this->has_kb_categories = $this->kb_has_categories();

		// articles with no categories - temporary add one
		if ( isset( $this->articles_seq_data[0] ) ) {
			$this->category_seq_data[0] = array();
		}
	}

	/**
	 * Display a link to a KB article.
	 *
	 * @param $title
	 * @param $article_id
	 * @param string $seq_no
	 */
	protected function single_article_link( $title , $article_id, $seq_no='' ) {

		if ( empty( $article_id ) ) {
			return;
		}

		$outer_span = $this->get_css_class( 'elay-article-title' .
										( $this->kb_config['sidebar_article_underline'] == 'on' ? ', article_underline_effect' : '' ) .
										( $this->kb_config['sidebar_article_active_bold'] == 'on' ? ', article_active_bold' : '' )
		);
		$article_color_escaped = $this->get_inline_style( 'color:: ' . 'sidebar_article_font_color' );
		$icon_color_escaped = $this->get_inline_style( 'color:: ' . 'sidebar_article_icon_color' );

		$icon_class = 'elay-article-title__icon ep_font_icon_document';

		// handle any add-on content
		$title_attr_escaped = '';
		$new_tab = '';
		$link = '';
		if ( has_filter( 'eckb_single_article_filter' ) ) {

			$result = apply_filters('eckb_single_article_filter', $article_id, array( $this->kb_id, $title, $outer_span, $article_color_escaped, $icon_color_escaped ) );

			// keep for old compatibility for links to output separately
			if ( ! empty( $result ) && $result === true ) {
				return;
			}

			if ( is_array( $result) && isset( $result['url_value'] ) ) {
				$link = $result['url_value'];
				$title_attr_escaped = 'title="' . esc_attr( $result['title_attr_value'] ) . '"';
				$new_tab = $result['new_tab'];
				$icon_class = 'elay-article-title__icon epkbfa epkbfa-' . $result['icon'];
			}
		}

		// if not linked article
		if ( empty( $link ) ) {
			$link = get_permalink( $article_id );
			if ( ! has_filter('article_with_seq_no_in_url_enable') ) {
				$link = empty( $seq_no ) || $seq_no < 2 ? $link : add_query_arg('seq_no', $seq_no, $link);
				$link = empty( $link ) || is_wp_error( $link ) ? '' : $link;
			}

			$icon_class = 'elay-article-title__icon ' . ( str_contains( $this->kb_config['elay_sidebar_article_icon'], 'ep_font_' ) ? '' : 'epkbfa epkbfa-' ) . $this->kb_config['elay_sidebar_article_icon'];
		}

		$icon_toggle_class = '';
		if ( isset( $this->kb_config['sidebar_article_icon_toggle'] ) && $this->kb_config['sidebar_article_icon_toggle'] == 'off' ) {
			$icon_class = '';
			$icon_toggle_class = 'elay-article--no-icon';
		}   ?>

		<a href="<?php echo esc_url( $link ); ?>" <?php echo $title_attr_escaped; ?> class="elay-sidebar-article <?php echo  esc_attr( $icon_toggle_class ); ?>" data-kb-article-id='<?php echo $article_id; ?>' <?php echo ( empty( $new_tab ) ? '' : 'target="_blank"' ); ?>>
			<span <?php echo $outer_span; ?> >
				<span class="<?php echo esc_attr( $icon_class ); ?>" <?php echo $icon_color_escaped; ?> aria-hidden="true"></span>
				<span class="elay-article-title__text"><?php echo esc_html( $title ); ?></span>
			</span>
		</a> <?php
	}

	/**
	 * Display a search form for Grid Layout only for non-modular page
	 */
	protected function get_search_form() {
		ELAY_KB_Core::get_search_form( $this->kb_config );
	}

	/**
	 * Output inline CSS style based on configuration.
	 *
	 * @param string $styles  A list of Configuration Setting styles
	 * @return string
	 */
	public function get_inline_style( $styles ) {
		return ELAY_Utilities::get_inline_style( $styles, $this->kb_config );
	}

	/**
	 * Output CSS classes based on configuration.
	 *
	 * @param $classes
	 * @return string
	 */
	public function get_css_class( $classes ) {
		return ELAY_Utilities::get_css_class( $classes, $this->kb_config );
	}

	/**
	 * Retrieve category icons.
	 * @return array|string|null
	 */
	protected function get_category_icons() {

		if ( ELAY_Utilities::get( 'epkb-editor-page-loaded' ) == '1' && isset($this->kb_config['theme_presets']) && $this->kb_config['theme_presets'] !== 'current' ) {
			$category_icons = ELAY_KB_Core::get_or_update_new_category_icons( $this->kb_config, $this->kb_config['theme_presets'] );
			if ( ! empty( $category_icons ) ) {
				return $category_icons;
			}
		}

		return ELAY_KB_Core::get_category_data_option( $this->kb_config['id'] );
	}

	/**
	 * Detect whether the current KB has any category
	 *
	 * @return bool
	 */
	protected function kb_has_categories() {

		// if non-empty categories sequence in DB then nothing to do
		if ( ! empty( $this->category_seq_data ) && is_array( $this->category_seq_data ) ) {
			return true;
		}

		// if no categories in the sequence then query DB directly; return if error
		$category_seq_data = ELAY_KB_Core::get_refreshed_kb_categories( $this->kb_id, $this->category_seq_data );
		if ( $category_seq_data === null || ! is_array( $category_seq_data ) ) {
			return true;
		}

		// re-populate the class
		$this->category_seq_data = $category_seq_data;
		$this->articles_seq_data = ELAY_Utilities::get_kb_option( $this->kb_id, ELAY_KB_Core::ELAY_ARTICLES_SEQUENCE, array(), true );

		// for WPML filter categories and articles given active language
		if ( ELAY_Utilities::is_wpml_enabled( $this->kb_config ) ) {
			$this->category_seq_data = ELAY_KB_Core::apply_category_language_filter( $this->category_seq_data );
			$this->articles_seq_data = ELAY_KB_Core::apply_article_language_filter( $this->articles_seq_data );
		}

		return ! empty( $this->category_seq_data );
	}

	/**
	 * Show message that KB does not have any categories
	 */
	protected function show_categories_missing_message() {

		$kb_post_type = ELAY_KB_Handler::get_post_type( $this->kb_id );
		$kb_category_taxonomy_name = ELAY_KB_Handler::get_category_taxonomy_name( $this->kb_id );
		$manage_articles_url = admin_url( 'edit-tags.php?taxonomy=' . $kb_category_taxonomy_name . '&post_type=' . $kb_post_type );
		$import_url = ELAY_Utilities::is_export_import_enabled() ?
								admin_url( '/edit.php?post_type=' . ELAY_KB_Handler::get_post_type( $this->kb_id ) . '&page=ep'.'kb-kb-configuration#tools__import' )
								: 'https://www.echoknowledgebase.com/wordpress-plugin/kb-articles-import-export/';     ?>

		<section class="elay-kb-no-content">   <?php

			// for users with at least Author access
			if ( current_user_can( ELAY_KB_Core::get_author_capability() ) ) {    ?>
				<h2 class="elay-kb-no-content-title"><?php esc_html_e( 'You do not have any KB categories. What would you like to do?', 'echo-knowledge-base' ); ?></h2>  <?php

				// for users with at least Editor access
				if ( ELAY_KB_Core::is_user_access_to_context_allowed( 'admin_eckb_access_frontend_editor_write' ) ) {   ?>
					<div class="elay-kb-no-content-body">
						<p><a id="elay-kb-create-demo-data" class="elay-kb-no-content-btn" href="#" data-id="<?php echo esc_attr( $this->kb_id ); ?>"><?php esc_html_e( 'Generate Demo Categories and Articles', 'echo-knowledge-base' ); ?></a></p>
						<p><a class="elay-kb-no-content-btn" href="<?php echo esc_url( $manage_articles_url ); ?>" target="_blank"><?php esc_html_e( 'Create Categories', 'echo-knowledge-base' ); ?></a></p>
						<p><a class="elay-kb-no-content-btn" href="<?php echo esc_url( $import_url ); ?>" target="_blank"><?php esc_html_e( 'Import Articles and Categories', 'echo-knowledge-base' ); ?></a></p>
					</div><?php

					ELAY_HTML_Forms::dialog_confirm_action( array(
						'id'                => 'epkb-created-kb-content',
						'title'             => __( 'Notice', 'echo-knowledge-base' ),
						'body'              => __( 'Demo categories and articles have been created. The page will reload.', 'echo-knowledge-base' ),
						'accept_label'      => __( 'Ok', 'echo-knowledge-base' ),
						'accept_type'       => 'primary',
						'show_cancel_btn'   => 'no',
						'show_close_btn'    => 'no',
					) );

				}   ?>

				<div class="elay-kb-no-content-footer">
					<p><?php esc_html_e( 'Ensure all articles are assigned to categories.', 'echo-knowledge-base' ); ?></p>
					<p>
						<span><?php esc_html_e( 'If you need help, please contact us', 'echo-knowledge-base' ); ?></span>
						<a href="https://www.echoknowledgebase.com/technical-support/" target="_blank"> <?php esc_html_e( 'here', 'echo-knowledge-base' ); ?></a>
					</p>
				</div>  <?php

			// for other users
			} else {    ?>
				<h2 class="elay-kb-no-content-title"><?php echo esc_html( $this->kb_config['category_empty_msg'] ); ?></h2>     <?php
			}   ?>

		</section>      <?php
	}
}