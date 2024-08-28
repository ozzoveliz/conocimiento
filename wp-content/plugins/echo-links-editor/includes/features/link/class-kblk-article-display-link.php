<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display the actual article link on KB Main Page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class KBLK_Article_Display_Link {

	const MAX_KEY_WORDS = 5;

	public function __construct() {
		// WP hooks
		add_filter( 'post_type_link', array( $this, 'replace_linked_article_permalink' ), 10, 2 );
		// add_filter( 'pre_get_posts', array( $this, 'remove_linked_articles_from_search' ) );
		add_filter( 'found_posts', array( $this, 'found_posts'), 999, 2 );

		// KB hooks
		add_filter( 'eckb_single_article_filter', array($this, 'display_article_link'), 10, 2 );
		add_filter( 'eckb_article_icon_filter', array( $this, 'display_category_page_article_icon' ), 10, 2 );
		add_filter( 'eckb_link_newtab_filter', array( $this, 'article_link_newtab' ), 10, 1 );
	}

	/**
	 * Returns linked article URL. URL escaped in calling function.
	 *
	 * @param $permalink
	 * @param $post
	 * @return mixed
	 */
	public function replace_linked_article_permalink( $permalink, $post ) {

		// only handle our articles
		if ( ! isset( $post->ID, $post->post_type ) || ! KBLK_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return $permalink;
		}

		// check if KB Article is linked
		if ( ! KBLK_Utilities::is_link_editor( $post ) ) {
			return $permalink;
		}

		$link_editor_config = KBLK_Utilities::get_postmeta( $post->ID, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error($link_editor_config) ) {
			return $permalink;
		}

		// do not show link if not enabled
		return empty($link_editor_config['url']) ? $permalink : $link_editor_config['url'];
	}

	/**
	 * Remove linked articles from WordPress search and elsewhere
	 *
	 * @param $counter
	 * @param WP_Query $query
	 * @return int
	 */
	public function found_posts( $counter, $query ) {

		// for now, we just use this for search
		if ( ! $query->is_search() ) {
			return $counter;
		}

		if ( empty($query->posts) ) {
			return $counter;
		}

		// check access for each post
		$filtered_posts_wp = array();
		foreach( $query->posts as $original_post ) {

			// IGNORE empty elements
			if ( empty($original_post) ) {
				$filtered_posts_wp[] = $original_post;
				continue;
			}

			if ( KBLK_Utilities::is_positive_int( $original_post ) || !empty( $original_post->ID )  ) {
				$post = get_post( $original_post );
			} else {
				$post = $original_post;
			}

			// IGNORE non KB Articles
			if ( empty($post->post_type) || ! KBLK_KB_Handler::is_kb_post_type( $post->post_type ) ) {
				$filtered_posts_wp[] = $original_post;
				continue;
			}

			// include posts that are not linked or include linked articles if on All Articles page
			if ( ! KBLK_Utilities::is_link_editor( $post ) || $this->is_all_articles_page() ) {
				$filtered_posts_wp[] = $original_post;
				continue;
			}
		}

		$difference = count($query->posts) - count($filtered_posts_wp);
		$counter = $counter - $difference < 0 ? 0 : $counter - $difference;
		$query->posts = $filtered_posts_wp;
		$query->post_count = count( $query->posts );

		return $counter;
	}

	/**
	 * On KB Main Page display the article link to PDF, documents and other links.
	 *
	 * @param $article_id
	 * @param $link_kb_config
	 *
	 * @return array|bool
	 */
	public function display_article_link( $article_id, $link_kb_config ) {

		// check if KB Article is linked
		$link_editor_config = KBLK_Utilities::get_postmeta( $article_id, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error($link_editor_config) ) {
			return false;
		}

		// do not show link if not enabled
		if ( empty($link_editor_config['link-editor-on']) || $link_editor_config['link-editor-on'] !== true ) {
			return false;
		}

		// get article link configuration
		$url_value = empty($link_editor_config['url']) ? '' : $link_editor_config['url'];

		$title_attr_value = empty($link_editor_config['title-attribute']) ? '' : $link_editor_config['title-attribute'];
		$new_tab = empty($link_editor_config['open-new-tab']) ? '' : 'target="_blank"';
		$icon = empty($link_editor_config['icon']) ? 'link' : $link_editor_config['icon'];

		// get article KB configuration
		$kb_id  = empty($link_kb_config[0]) ? KBLK_KB_Core::DEFAULT_KB_ID : $link_kb_config[0];
		$title  = empty($link_kb_config[1]) ? '' : $link_kb_config[1];
		$class1 = empty($link_kb_config[2]) ? '' : $link_kb_config[2];
		$style1 = empty($link_kb_config[3]) ? '' : $link_kb_config[3];
		$style2 = empty($link_kb_config[4]) ? '' : $link_kb_config[4];

		$kb_config = KBLK_KB_Core::get_kb_config_or_default( $kb_id );

		// for Modular mode return link configuration; Elegant Layout does not handle linked articles
		$is_kb_new_modular_version =  isset( Echo_Knowledge_Base::$version ) && version_compare( Echo_Knowledge_Base::$version, '11.30.0', '>=' );
		$is_elay_layout = $kb_config['kb_main_page_layout'] == 'Grid' || $kb_config['kb_main_page_layout'] == 'Sidebar';
		if ( ! $is_elay_layout && $is_kb_new_modular_version && isset( $kb_config['modular_main_page_toggle'] ) && $kb_config['modular_main_page_toggle'] == 'on' ) {
			return [ 'url_value' => $url_value, 'title_attr_value' => $title_attr_value, 'new_tab' => $new_tab, 'icon' => $icon ];
		}

		// display the link
		if ( $kb_config['kb_main_page_layout'] == 'Modular' ) {     // TODO remove this check when Modular mode is deprecated
			$output = '
			<a href="' . esc_url( $url_value ) . '" class="epkb-ml-article-container" data-kb-article-id="' . esc_attr( $article_id ) . '">
				<span class="epkb-article-inner">
					<span class="eckb-article-title__icon epkbfa epkbfa-' . $icon . '" aria-hidden="true"></span>
					<span class="epkb-article__text">' . esc_html( $title ) . '</span>
				</span>
			</a>';
		} else {
			$output = '
			<a href="' . esc_url( $url_value ) . '" title="' . esc_attr( $title_attr_value ) . ' " ' . $new_tab . '>
				<span ' . $class1 . ' ' . $style1 . '>
					<span class="eckb-article-title__icon epkbfa epkbfa-' . $icon . ' " ' . $style2 . '></span>
					<span class="eckb-article-title__text">' . esc_html( $title ) . '</span>
				</span>
	        </a>';
		}

		echo $output;

		return true;
	}

	/**
	 * Returns linked article icon name.
	 *
	 * @param $icon_name
	 * @param $post_id
	 * @return String
	 */
	public function display_category_page_article_icon( $icon_name, $post_id ) {

		// check if KB Article is linked
		$link_editor_config = KBLK_Utilities::get_postmeta( $post_id, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error($link_editor_config) ) {
			return $icon_name;
		}

		// do not show link if not enabled
		if ( empty($link_editor_config['link-editor-on']) || $link_editor_config['link-editor-on'] !== true || empty($link_editor_config['icon']) ) {
			return $icon_name;
		}

		return 'epkbfa-' . $link_editor_config['icon'];
	}/** @noinspection PhpUnusedParameterInspection */

	/**
	 * Determine if user is on All Articles page.
	 *
	 * @return bool - true if this is All Articles page
	 */
	private function is_all_articles_page() {
		return empty($_SERVER['REQUEST_URI']) ? false : strstr($_SERVER['REQUEST_URI'], '/edit.php') !== false;
	}

	/**
	 * Returns linked article new tab option.
	 *
	 * @param $article_id
	 * @return string
	 */
	public function article_link_newtab( $article_id ) {

		// check if KB Article is linked
		$link_editor_config = KBLK_Utilities::get_postmeta( $article_id, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error($link_editor_config) ) {
			return '';
		}
		// do not show link if not enabled
		if ( empty($link_editor_config['link-editor-on']) || $link_editor_config['link-editor-on'] !== true ) {
			return '';
		}

		return empty($link_editor_config['open-new-tab']) ? '' : ' target="_blank" ';
	}
}
