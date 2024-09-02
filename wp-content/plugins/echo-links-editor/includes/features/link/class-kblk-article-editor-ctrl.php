<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * When "Link" Article is saved, save link configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class KBLK_Article_Editor_Cntrl {

	public function __construct() {

		$kb_post_type = KBLK_Utilities::post( 'post_type' );
		if ( empty( $kb_post_type ) ) {
			return;
		}

		add_action( 'wp_insert_post_data', array( $this, 'update_article_mime_type' ), 10, 2 );
		add_action( 'save_post_' . $kb_post_type, array( $this, 'update_article_link_info' ), 10, 2 );
	}

	/**
	 * Indicate post is linked article
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr      An array of sanitized, but otherwise unmodified post data.
	 * @return array
	 */
	public function update_article_mime_type( $data, $postarr ) {

		// ignore non-KB posts
		if ( empty($postarr['post_type']) || ! KBLK_KB_Handler::is_kb_post_type( $postarr['post_type'] ) ) {
			return $data;
		}

		// is Custom Links ON ?
		$link_editor_on = KBLK_Utilities::post( 'kblk_link_editor_mode' );
		if ( $link_editor_on === 'yes' ) {
			$data['post_mime_type'] = 'kb_link';
		} else if ( $link_editor_on === 'no' ) {
			$data['post_mime_type'] = '';
		}

		return $data;
	}

	/**
	 * Update linked article information.
	 * TODO: if error show it to the user
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return bool
	 */
	public function update_article_link_info( $post_id, $post ) {

		if ( empty( $post ) || ! $post instanceof WP_Post || ! KBLK_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return true;
		}

		// ignore autosave/revision which is not article submission; same with ajax and bulk edit
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return true;
		}

		// get current configuration
		$link_editor_config = KBLK_Utilities::get_postmeta( $post_id, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error( $link_editor_config ) ) {
			return false;
		}

		// update current configuration only if the editor is on
		$link_editor_on = KBLK_Utilities::post( 'kblk_link_editor_mode' );
		if ( $link_editor_on === 'yes' ) {
			$link_editor_config['link-editor-on'] = true;
		} else if ( $link_editor_on === 'no' ) {
			$link_editor_config['link-editor-on'] = false;
		}

		// is Custom Links ON ?
		if ( $link_editor_config['link-editor-on'] === true ) {
			$link_editor_config = $this->retrieve_link_editor_config( $link_editor_config, $post_id );
			if ( $link_editor_config === false ) {
				return false;
			}
		}

		// save new configuration
		$link_editor_config = KBLK_Utilities::save_postmeta( $post_id, 'kblk-link-editor-data', $link_editor_config, true );
		if ( is_wp_error( $link_editor_config ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Update linked article meta data.
	 *
	 * @param $link_editor_config
	 * @param $post_id
	 * @return bool
	 */
	private function retrieve_link_editor_config( $link_editor_config, $post_id ) {

		// save search text
		$search_terms = KBLK_Utilities::post( 'kblk_search_terms' );
		$search_terms = str_replace( '?', '', $search_terms );
		$search_terms = str_replace( array( "\r", "\n" ), '', $search_terms );
		if ( ! empty( $search_terms ) && strlen( $search_terms ) > 2 ) {
			$result = KBLK_Utilities::save_postmeta( $post_id, 'kblk_search_terms', $search_terms, true );
			if ( is_wp_error( $result ) ) {
				return false;
			}
		}

		// LINK URL
		$link_url = KBLK_Utilities::post('kblk_link_url', '', 'url');
		$link_url = empty($link_url) ? '' : esc_url_raw($link_url);
		$link_editor_config['url'] = $link_url;

		// LINK TITLE ATTRIBUTE
		$link_title_attribute = KBLK_Utilities::post('kblk_link_title_attribute');
		$link_editor_config['title-attribute'] = empty($link_title_attribute) ? '' : $link_title_attribute;

		// OPEN NEW TAB
		$open_new_tab = KBLK_Utilities::post('kblk_open_new_tab');
		$open_new_tab = ! empty($open_new_tab) && $open_new_tab === 'on';
		$link_editor_config['open-new-tab'] = $open_new_tab;

		// ICON
		$icon = KBLK_Utilities::post('kblk_icon');
		$icon = str_replace('epkbfa-', '', $icon);
		$common_kb_icons = KBLK_Article_Link_Icons::get_common_icons();
		$other_icons = KBLK_Article_Link_Icons::get_other_icons();
		if ( ! in_array($icon, $common_kb_icons) && ! in_array($icon, $other_icons) ) {
			$icon = 'link';
		}
		$link_editor_config['icon'] = $icon;

		return $link_editor_config;
	}
}
