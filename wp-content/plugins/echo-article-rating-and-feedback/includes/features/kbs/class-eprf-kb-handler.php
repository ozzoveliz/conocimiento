<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle operations on knowledge base such as adding, deleting and updating KB
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPRF_KB_Handler {

	// Prefix for custom post type name associated with given KB; this will never change
	const KB_POST_TYPE_PREFIX = EPRF_KB_Core::EPRF_KB_POST_TYPE_PREFIX;  // changing this requires db update
	const KB_CATEGORY_TAXONOMY_SUFFIX = '_category';  // changing this requires db update; do not translate
	const KB_TAG_TAXONOMY_SUFFIX = '_tag'; // changing this requires db update; do not translate


	/**
	 * Is this KB post type?
	 *
	 * @param $post_type
	 * @return bool
	 */
	public static function is_kb_post_type( $post_type ) {
		if ( empty($post_type) || ! is_string($post_type)) {
			return false;
		}
		// we are only interested in KB articles
		return strncmp($post_type, self::KB_POST_TYPE_PREFIX, strlen(self::KB_POST_TYPE_PREFIX)) == 0;
	}

	/**
	 * Is this KB taxonomy?
	 *
	 * @param $taxonomy
	 * @return bool
	 */
	public static function is_kb_taxonomy( $taxonomy ) {
		if ( empty($taxonomy) || ! is_string($taxonomy) ) {
			return false;
		}
		// we are only interested in KB articles
		return strncmp($taxonomy, self::KB_POST_TYPE_PREFIX, strlen(self::KB_POST_TYPE_PREFIX)) == 0;
	}

	/**
	 * Does request have KB taxonomy or post type ?
	 *
	 * @return bool
	 */
	public static function is_kb_request() {

		$kb_post_type = empty($_REQUEST['post_type']) ? '' : preg_replace('/[^A-Za-z0-9 \-_]/', '', $_REQUEST['post_type']);
		$is_kb_post_type = empty($kb_post_type) ? false : self::is_kb_post_type( $kb_post_type );
		if ( $is_kb_post_type ) {
			return true;
		}

		$kb_taxonomy = empty($_REQUEST['taxonomy']) ? '' : preg_replace('/[^A-Za-z0-9 \-_]/', '', $_REQUEST['taxonomy']);
		$is_kb_taxonomy = empty($kb_taxonomy) ? false : self::is_kb_taxonomy( $kb_taxonomy );

		return $is_kb_taxonomy;
	}

	/**
	 * Retrieve KB post type name e.g. ep kb_post_type_1
	 *
	 * @param $kb_id - assumed valid id
	 *
	 * @return string
	 */
	public static function get_post_type( $kb_id ) {
		$kb_id = EPRF_Utilities::sanitize_int($kb_id, EPRF_KB_Core::DEFAULT_KB_ID );
		return self::KB_POST_TYPE_PREFIX . $kb_id;
	}

	/**
	 * Return category name e.g. ep kb_post_type_1_category
	 *
	 * @param $kb_id - assumed valid id
	 *
	 * @return string
	 */
	public static function get_category_taxonomy_name( $kb_id ) {
		return self::get_post_type( $kb_id ) . self::KB_CATEGORY_TAXONOMY_SUFFIX;
	}

	/**
	 * Return tag name e.g. ep kb_post_type_1_tag
	 *
	 * @param $kb_id - assumed valid id
	 *
	 * @return string
	 */
	public static function get_tag_taxonomy_name( $kb_id ) {
		return self::get_post_type( $kb_id ) . self::KB_TAG_TAXONOMY_SUFFIX;
	}

	/**
	 * Retrieve KB ID from article type name
	 *
	 * @param String $post_type is post or post type
	 *
	 * @return int | WP_Error if no kb_id found
	 */
	public static function get_kb_id_from_post_type( $post_type ) {
		if ( empty($post_type) || in_array($post_type, array('page', 'attachment', 'post')) || ! is_string($post_type) ) {
			return new WP_Error('35', "kb_id not found");
		}

		$kb_id = str_replace( self::KB_POST_TYPE_PREFIX, '', $post_type );
		if ( ! EPRF_Utilities::is_positive_int( $kb_id ) ) {
			return new WP_Error('36', "kb_id not valid");
		}

		return $kb_id;
	}

	/**
	 * Find KB Main Page that is not in trash and get its URL.
	 *
	 * @param $kb_config
	 * @return string
	 */
	public static function get_first_kb_main_page_url( $kb_config ) {

		$first_page_url = '';
		$kb_main_pages = $kb_config['kb_main_pages'];
		foreach ( $kb_main_pages as $post_id => $post_title ) {
			if ( empty($post_id) ) {
				continue;
			}
			$first_page_url = get_permalink( $post_id );
			if ( ! empty($first_page_url) && ! is_wp_error( $first_page_url ) ) {
				break;
			}
		}

		return $first_page_url;
	}
}
