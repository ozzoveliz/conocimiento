<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB Groups actions.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGP_Groups {

	function __construct() {
		add_filter( 'aman_is_kb_manager', array( $this, 'is_kb_manager' ), 10, 2 );
		// we don't want to add default category. user has to select one and API insert has to have one too
		//add_action( 'wp_insert_post', array( $this, 'insert_post_action' ), 100, 2 );
	}

	/**
	 * is user KB Manager
	 *
	 * @param $ignore
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function is_kb_manager( /** @noinspection PhpUnusedParameterInspection */ $ignore, $user_id ) {
		$kb_managers = self::get_kb_managers();
		return ! empty($kb_managers) && in_array($user_id, $kb_managers);
	}

	/**
	 * Retrieve known KB Managers
	 * @return array|null
	 */
	public static function get_kb_managers() {
		return amgp_get_instance()->kb_config_obj->get_value( AMGP_KB_Core::DEFAULT_KB_ID, 'kb_managers', null );
	}

	/**
	 * Set KB Managers
	 *
	 * @param $kb_managers
	 * @return array|WP_Error
	 */
	public static function set_kb_managers( $kb_managers ) {
		return amgp_get_instance()->kb_config_obj->set_value( AMGP_KB_Core::DEFAULT_KB_ID, 'kb_managers', $kb_managers );
	}

	/**
	 * For a new article we need to have at least one category assigned to it. We cannot block user from publishing without assigned
     * category, so instead we assign the first category we find if user didn't assign any.
	 *
	 * @param $post_ID
	 * @param $post
	 * @return void|null
	 */
	public static function insert_post_action( $post_ID, $post ) {

		// verify it is a KB article
		if ( ! AMGP_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return;
		}

		$kb_id = AMGP_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error( $kb_id ) ) {
			return;
		}

		// do not add default category if the user is adding a new article; we will force user to add one; only add default if inserted through API
		if ( empty( $post->post_status ) || $post->post_status = 'auto-draft' ||  $post->post_status = 'draft' || $post->post_status = 'trash' ) {
			return;
		}

		$category_name = AMGP_KB_Handler::get_category_taxonomy_name( $kb_id );

		// check categories user assigned already
		$terms = get_the_terms( $post, $category_name );
		if ( $terms ) {
			return;
		}

		// get complete categories hierarchy
		$articles_seq_data = AMGP_Utilities::get_kb_option( $kb_id, AMGP_KB_Core::AMGP_KB_ARTICLES_SEQ_META, null, true );
		if ( $articles_seq_data === null ) {
			return;
		}

		$handler = AMGP_KB_Core::AMGP_Access_Category();
		$category_id = false;
		foreach ( $articles_seq_data as $cat_id => $value ) {
			$term = get_term( $cat_id, $category_name );
			if ( ! $handler->check_access( $term ) ) {
				continue;
			}

			$category_id = $cat_id;
			break;
		}

		if ( ! $category_id ) {
			return;
		}

		wp_set_object_terms( $post_ID, $category_id, $category_name );
	}
}

