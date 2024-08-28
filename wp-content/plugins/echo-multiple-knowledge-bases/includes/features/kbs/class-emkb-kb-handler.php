<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle operations on knowledge base such as adding, deleting and updating KB
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EMKB_KB_Handler {

	// Prefix for custom post type name associated with given KB; this will never change
	const KB_POST_TYPE_PREFIX = EMKB_KB_Core::EMKB_KB_POST_TYPE_PREFIX;  // changing this requires db update
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
		$kb_id = EMKB_Utilities::sanitize_int($kb_id, EMKB_KB_Core::DEFAULT_KB_ID );
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

		if ( empty($post_type) || ! is_string($post_type) || strpos( $post_type, self::KB_POST_TYPE_PREFIX ) === false ) {
			return new WP_Error('35', "kb_id not found");
		}

		$kb_id = str_replace( self::KB_POST_TYPE_PREFIX, '', $post_type );
		if ( ! EMKB_Utilities::is_positive_int( $kb_id ) ) {
			return new WP_Error('36', "kb_id not valid");
		}

		return $kb_id;
	}

	/**
	 * Find KB Main Page that is not in trash and get its URL (page that matches kb_articles_common_path in KB config or first main page URL).
	 *
	 * @param $kb_config
	 * @return string
	 */
	public static function get_first_kb_main_page_url( $kb_config ) {

		$first_page_url = '';
		$kb_main_pages = $kb_config['kb_main_pages'];

		foreach ( $kb_main_pages as $post_id => $post_title ) {

			if ( empty( $post_id ) ) {
				continue;
			}

			$post = get_post( $post_id );
			if ( ! empty( $post ) && ! is_array( $post ) ) {

				$main_page_url = get_permalink( $post_id );
				if ( ! empty( $main_page_url ) && ! is_wp_error( $main_page_url ) ) {

					$main_page_path = urldecode( sanitize_title_with_dashes( $post->post_name, '', 'save' ) );
					if ( $main_page_path == $kb_config['kb_articles_common_path'] ) {
						return $main_page_url;
					}
					$first_page_url = empty( $first_page_url ) ?  $main_page_url : $first_page_url;
				}
			}
		}

		return $first_page_url;
	}


	/**
	 * Determine if the current page is KB main page i.e. it contains KB shortcode and return its KB ID if any
	 * @param null $the_post - either pass post to the method or use current post
	 * @return int|null return KB ID if current page is KB main page otherwise null
	 */
	public static function get_kb_id_from_kb_main_shortcode( $the_post=null ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// ensure WP knows about the shortcode
		add_shortcode( EMKB_KB_Core::KB_MAIN_PAGE_SHORTCODE_NAME, array( 'EP'.'KB_Layouts_Setup', 'output_kb_page_shortcode' ) );

		$global_post = empty( $GLOBALS['post'] ) ? '' : $GLOBALS['post'];
		$apost = empty( $the_post ) ? $global_post : $the_post;
		if ( empty( $apost ) || ! $apost instanceof WP_Post ) {
			return null;
		}

		// determine whether this page contains this plugin shortcode
		$content = '';
		if ( has_shortcode( $apost->post_content, EMKB_KB_Core::KB_MAIN_PAGE_SHORTCODE_NAME ) ) {
			$content = $apost->post_content;
		} else if ( isset($apost->ID) ) {
			$content = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta " .
			                           "WHERE post_id = {$apost->ID} and meta_value LIKE '%%" . EMKB_KB_Core::KB_MAIN_PAGE_SHORTCODE_NAME . "%%'" );
		}

		return self::get_kb_id_from_shortcode( $content );
	}

	/**
	 * Retrieve KB ID from post content - shortcode
	 *
	 * @param String $content should have the shortcode with KB ID
	 *
	 * @return int|null returns KB ID if found
	 */
	private static function get_kb_id_from_shortcode( $content ) {

		if ( empty($content) || ! is_string($content) ) {
			return null;
		}

		$start = strpos($content, EMKB_KB_Core::KB_MAIN_PAGE_SHORTCODE_NAME);
		if ( empty($start) || $start < 0 ) {
			return null;
		}

		$end = strpos($content, ']', $start);
		if ( empty($start) || $start < 1 ) {
			return null;
		}

		$shortcode = substr($content, $start, $end);
		if ( empty($shortcode) || strlen($shortcode) < strlen(EMKB_KB_Core::KB_MAIN_PAGE_SHORTCODE_NAME)) {
			return null;
		}

		preg_match_all('!\d+!', $shortcode, $number);
		$number = empty($number[0][0]) ? 0 : $number[0][0];
		if ( ! EMKB_Utilities::is_positive_int( $number ) ) {
			return null;
		}

		return (int)$number;
	}
}
