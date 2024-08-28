<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display links editor
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class KBLK_Links_Editor {

	private function __construct() {}

	public static function init_actions() {

		// is there block or Gutenber editor?
		$is_block_editor = has_action( 'enqueue_block_assets' );
		$is_gutenberg = function_exists( 'gutenberg_can_edit_post_type' );

		// for old version of WordPress return
		if ( ! $is_block_editor && ! $is_gutenberg  ) {
			return;
		}

		if ( $is_block_editor ) {
			add_filter( 'use_block_editor_for_post', array( __CLASS__, 'use_block_editor' ), 100, 2 );
		}
		if ( $is_gutenberg ) {
			add_filter( 'gutenberg_can_edit_post', array( __CLASS__, 'use_block_editor' ), 100, 2 );
		}

		add_filter( 'redirect_post_location', array( __CLASS__, 'redirect_location' ) );
		add_action( 'admin_head-edit.php', array( __CLASS__, 'add_edit_php_inline_style' ) );

		// Post state (edit.php)
		add_filter( 'display_post_states', array( __CLASS__, 'add_post_state' ), 10, 2 );

		add_filter( 'post_row_actions', array( __CLASS__, 'add_edit_links' ), 9999, 2 );

		if ( $is_gutenberg ) {
			// These are handled by this plugin.
			remove_action( 'admin_init', 'gutenberg_add_edit_link_filters' );
			remove_action( 'admin_print_scripts-edit.php', 'gutenberg_replace_default_add_new_button' );
			remove_filter( 'redirect_post_location', 'gutenberg_redirect_to_classic_editor_when_saving_posts' );
			remove_filter( 'display_post_states', 'gutenberg_add_gutenberg_post_state' );
			remove_action( 'edit_form_top', 'gutenberg_remember_classic_editor_when_saving_posts' );
		}
	}

	/**
	 * Choose which editor to use for a post.
	 *
	 * @param boolean $use_block_editor True for Block Editor, false for Classic Editor.
	 * @param WP_Post $post             The post being edited.
	 * @return boolean True for Block Editor, false for Classic Editor.
	 */
	public static function use_block_editor( $use_block_editor, $post ) {
		// if this is linked article or will be linked article then don't use block editor
		if ( KBLK_Utilities::is_link_editor( $post ) || KBLK_Utilities::get( 'linked-editor', '' ) == 'yes' ) {
			return false;
		}

		// otherwise use whatever is selected by user if the user is still using classic editor or both
		return $use_block_editor;
	}

	/**
	 * Keep the `classic-editor` query arg through redirects when saving posts.
	 *
	 * @param $location
	 *
	 * @return string
	 */
	public static function redirect_location( $location ) {
		if (
			isset( $_REQUEST['classic-editor'] ) ||
			( isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], '&classic-editor' ) !== false )
		) {
			$location = add_query_arg( 'classic-editor', '', $location );
		}

		return $location;
	}

	/**
	 * Show the editor that will be used in a "post state" in the Posts list table.
	 *
	 * @param $post_states
	 * @param $post
	 *
	 * @return array
	 */
	public static function add_post_state( $post_states, $post ) {

		// only handle KB articles
		if ( ! KBLK_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return $post_states;
		}

		if ( ! KBLK_Utilities::is_link_editor( $post) ) {
			return $post_states;
		}

		$post_states[] = _x( 'LINK Editor', 'Editor Name', 'echo-links-editor' );

		return $post_states;
	}

	public static function add_edit_links( $actions, $post ) {

		// only handle KB articles
		if ( ! KBLK_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return $actions;
		}

		if ( ! KBLK_Utilities::is_link_editor( $post) ) {
			return $actions;
		}

		foreach( $actions as $ix => $link ) {
			if ( strpos($link, 'Block Editor') !== false ) {
				unset($actions[$ix]);
			} else if ( strpos($link, 'Classic Editor') !== false ) {
				unset($actions[$ix]);
			}
		}

		return $actions;
	}

	public static function add_edit_php_inline_style() {		?>
		<style>
			.classic-editor-forced-state {
				font-style: italic;
				font-weight: 400;
				color: #72777c;
				font-size: small;
			}
		</style>		<?php
	}
}
