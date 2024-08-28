<?php

/**
 * Utility functions specific to this plugin
 */
class EMKB_Core_Utilities {

	/**
	 * Retrieve a KB article with security checks
	 *
	 * @param $post_id
	 * @return null|WP_Post - return null if this is NOT KB post
	 */
	public static function get_kb_post_secure( $post_id ) {

		if ( empty($post_id) ) {
			return null;
		}

		// ensure post_id is valid
		$post_id = EMKB_Utilities::sanitize_int( $post_id );
		if ( empty($post_id) ) {
			return null;
		}

		// retrieve the post and ensure it is one
		$post = get_post( $post_id );
		if ( empty( $post ) || ! $post instanceof WP_Post ) {
			return null;
		}

		// verify it is a KB article
		if ( ! EMKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * Is WPML enabled? Only for KB CORE. ADD-ONs to call this function in core
	 *
	 * @param $kb_id
	 *
	 * @return bool
	 */
	public static function is_wpml_enabled_addon( $kb_id ) {

		if ( EMKB_Utilities::is_positive_int( $kb_id ) ) {
			$kb_config = EMKB_KB_Core::get_kb_config( $kb_id );
			if ( is_wp_error( $kb_config ) ) {
				return false;
			}
		} else {
			return false;
		}

		return EMKB_Utilities::is_wpml_enabled( $kb_config );
	}

	/**
	 * Show admin notice at the top of page and redirect
	 *
	 * @param $msg_code
	 * @param string $redirect
	 */
	public static function show_top_level_admin_msg_and_redirect( $msg_code, $redirect='admin.php' ) {
		$url = admin_url( $redirect );

		$query = array();
		$query['emkb_admin_notice'] = urlencode( $msg_code );

		$redirect = add_query_arg( $query, $url );
		wp_safe_redirect( $redirect );  // sanitizes the $redirect
		die();
	}
}
