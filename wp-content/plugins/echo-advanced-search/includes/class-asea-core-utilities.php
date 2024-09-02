<?php

/**
 * Various utility functions for SEARCH
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_Core_Utilities {

	/**
	 * Return value for given search configuration and whether we are on Main Page or Article Page
	 * @param $kb_config
	 * @param $config_name
	 *
	 * @return string|array
	 */
	public static function get_search_kb_config( $kb_config, $config_name ) {

		$config_name = str_replace('*', self::get_search_index( $kb_config ), $config_name);

		if ( isset( $kb_config[$config_name] ) ) {
			return $kb_config[$config_name];
		}

		$default_specs = ASEA_KB_Config_Specs::get_default_kb_config();

		return isset( $default_specs[$config_name] ) ? $default_specs[$config_name] : '';
	}

	public static function get_search_index( $kb_config=array() ) {
		global $asea_use_main_page_settings;

		$ix = ASEA_Utilities::is_kb_main_page() || ASEA_Utilities::get( 'is_kb_main_page' ) == 1 || ! empty( $asea_use_main_page_settings ) ? 'mp' : 'ap';
		$ix = empty( $kb_config['kb_main_page_layout'] ) || $kb_config['kb_main_page_layout'] != 'Sidebar' ? $ix : 'mp';

		return $ix;
	}

	/**
	 * if the Editor is active then use its configuration changes to update the current configuration
	 * @param $config
	 * @return mixed
	 */
	public static function update_from_editor_config( $config ) {

		// do not make any changes to the configuration unless Editor is active
		if ( empty( $_REQUEST['epkb-editor-page-loaded'] ) || empty( $_REQUEST['epkb-editor-settings'] ) ) {
			return $config;
		}

		$new_config = json_decode(stripcslashes($_REQUEST['epkb-editor-settings'] ), true);
		if ( empty( $new_config ) || ! is_array($new_config) ) {
			return $config;
		}

		// use Editor configuration to update current configuration
		foreach ( $new_config as $zone_name => $zone ) {
			foreach ( $zone['settings'] as $field_name => $field ) {
				if ( ! isset( $config[$field_name] ) ) {
					continue;
				}

				$config[$field_name] = $field['value'];
			}
		}

		return $config;
	}

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
		$post_id = ASEA_Utilities::sanitize_int( $post_id );
		if ( empty($post_id) ) {
			return null;
		}

		// retrieve the post and ensure it is one
		$post = get_post( $post_id );
		if ( empty( $post ) || ! $post instanceof WP_Post ) {
			return null;
		}

		// verify it is a KB article
		if ( ! ASEA_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return null;
		}

		return $post;
	}

	/**************************************************************************************************************************
	 *
	 *                     CATEGORIES
	 *
	 *************************************************************************************************************************/

	/**
	 *
	 * USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS.
	 *
	 * Get all existing KB categories.
	 *
	 * @param $kb_id
	 * @param string $order_by
	 * @return array|null - return array of KB categories (empty if not found) or null on error
	 */
	public static function get_kb_categories_unfiltered( $kb_id, $order_by='name' ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$order = $order_by == 'name' ? 'ASC' : 'DESC';
		$order_by = $order_by == 'date' ? 'term_id' : $order_by;   // terms don't have date so use id
		$kb_category_taxonomy_name = ASEA_KB_Handler::get_category_taxonomy_name( $kb_id );
		$result = $wpdb->get_results( $wpdb->prepare("SELECT t.*, tt.* " .
												   " FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id " .
												   " WHERE tt.taxonomy = %s ORDER BY " . esc_sql( 't.' . $order_by ) . ' ' . esc_sql( $order ), $kb_category_taxonomy_name ) );
		return isset($result) && is_array($result) ? $result : null;
	}

	/**
	 *
	 * USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS. Check Draft field
	 *
	 * Get all existing KB categories.
	 *
	 * @param $kb_id
	 * @param string $order_by
	 * @return array|null - return array of KB categories (empty if not found) or null on error
	 */
	public static function get_kb_categories_visible( $kb_id, $order_by='name' ) {

		$all_categories = self::get_kb_categories_unfiltered( $kb_id, $order_by );
		if ( empty( $all_categories ) ) {
			return $all_categories;
		}

		$categories_data = ASEA_KB_Core::get_category_data_option( $kb_id );
		foreach( $all_categories as $key => $category ) {
			$term_id = $category->term_id;

			if ( empty( $term_id ) ) {
				continue;
			}

			if ( empty( $categories_data[$term_id] ) ) {
				continue;
			}

			// remove draft categories
			if ( ! empty( $categories_data[$term_id]['is_draft'] ) ) {
				unset( $all_categories[$key] );
			}
		}

		return $all_categories;
	}

	/**
	 * USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS.
	 *
	 * Get KB Article categories.
	 *
	 * @param $kb_id
	 * @param $article_id
	 * @return array|null - categories belonging to the given KB Article or null on error
	 */
	public static function get_article_categories_unfiltered( $kb_id, $article_id ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty( $article_id ) ) {
			return null;
		}

		// get article categories
		$post_taxonomy_objs = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->term_taxonomy
				   WHERE taxonomy = '%s' and term_taxonomy_id in
				   (SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id = %d) ",
			ASEA_KB_Handler::get_category_taxonomy_name( $kb_id ), $article_id ) );
		if ( ! empty( $wpdb->last_error ) ) {
			return null;
		}

		$categories = $post_taxonomy_objs === null || ! is_array($post_taxonomy_objs) ? array() : $post_taxonomy_objs;

		// convert to term objects
		$categories_obj = [];
		foreach ( $categories as $key => $category ) {
			if ( empty( $category->term_id ) || empty( $category->taxonomy ) ) {
				continue;
			}
			$term = get_term( $category->term_id, $category->taxonomy );
			if ( ! empty( $term ) && ! is_wp_error( $term ) && property_exists( $term, 'term_id' ) ) {
				$categories_obj[$key] = $term;
			}
		}

		return $categories_obj;
	}

	/**
	 * USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS.
	 *
	 * Get KB Article categories.
	 *
	 * @param $kb_id
	 * @param $article_id
	 * @return array|null - categories belonging to the given KB Article or null on error
	 */
	public static function get_article_categories_visible( $kb_id, $article_id ) {

		$categories = self::get_article_categories_unfiltered( $kb_id, $article_id );
		if ( empty( $categories ) ) {
			return $categories;
		}

		$categories_data = ASEA_KB_Core::get_category_data_option( $kb_id );
		foreach( $categories as $key => $category ) {

			$term_id = $category->term_id;
			if ( empty( $term_id ) ) {
				continue;
			}

			if ( empty( $categories_data[$term_id] ) ) {
				continue;
			}

			// remove draft categories
			if ( ! empty( $categories_data[$term_id]['is_draft'] ) ) {
				unset( $categories[$key] );
			}
		}

		return $categories;
	}

	/**
	 * USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS.
	 *
	 * Retrieve KB Category.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @return WP_Term|false
	 */
	public static function get_kb_category_unfiltered( $kb_id, $kb_category_id ) {
		$term = get_term_by( 'id', $kb_category_id, ASEA_KB_Handler::get_category_taxonomy_name( $kb_id ) );
		if ( empty($term) || ! $term instanceof WP_Term ) {
			ASEA_Logging::add_log( "Category is not KB Category: " . $kb_category_id . " (35)", $kb_id );
			return false;
		}

		return $term;
	}

	/**
	 * Is WPML enabled? Only for KB CORE. ADD-ONs to call this function in core
	 *
	 * @param $kb_id
	 *
	 * @return bool
	 */
	public static function is_wpml_enabled_addon( $kb_id ) {

		if ( ASEA_Utilities::is_positive_int( $kb_id ) ) {
			$kb_config = ASEA_KB_Core::get_kb_config( $kb_id );
			if ( is_wp_error( $kb_config ) ) {
				return false;
			}
		} else {
			return false;
		}

		return ASEA_Utilities::is_wpml_enabled( $kb_config );
	}

	/**
	 * Get search query param
	 * @return string
	 */
	public static function get_search_query_param( $kb_id ) {

		$kb_search_text = _x( 'kb-search', 'search query parameter in URL', 'echo-advanced-search' );
		$kbsearch_text = _x( 'kbsearch', 'search query parameter in URL', 'echo-advanced-search' );

		// get current KB configuration
		$kb_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		if ( empty( $kb_config ) || ! is_array( $kb_config ) || is_wp_error( $kb_config ) || empty( $kb_config['search_query_param'] ) ) {
			return $kb_search_text;
		}

		// a) LEGACY case
		if ( $kb_config['search_query_param'] == 'kb-search' ) {
			return $kb_search_text;
		}

		// b) NEW case is 'kbsearch'
		if (  $kb_config['search_query_param'] == 'kbsearch' ) {
			return  $kb_search_text == 'kb-search' || $kbsearch_text != 'kbsearch' ? $kbsearch_text :
				preg_replace( "/[^A-zÀ-úА-я\s]/", '', $kb_search_text );
		}

		// c) USER-DEFINED case
		return $kb_config['search_query_param'];
	}
}
