<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB Categories Add and Edit page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Admin_Categories_Page {

	var $kb_config = array();

	public function __construct() {

		// filter available categories for given KB Group
		add_filter( 'get_terms', array( $this, 'get_kb_terms' ), 9999, 4 );
	}

	/**
	 * Filter User categories based on user access.
	 *
	 * @param $terms
	 * @param $taxonomies
	 * @param $query_args
	 * @param $term_query
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection*/
	public function get_kb_terms( $terms, $taxonomies, $query_args, $term_query ) {

		if ( empty($taxonomies) || ! is_array($taxonomies) ) {
			return $terms;
		}

		$has_kb_taxonomy = false;
		foreach ( $taxonomies as $_taxonomy ) {
			if ( EPKB_KB_Handler::is_kb_category_taxonomy( $_taxonomy ) ) {
				$has_kb_taxonomy = true;
				break;
			}
		}

		if ( ! $has_kb_taxonomy ) {
			return $terms;
		}

		// if we have non-KB taxonomies then do not filter related terms
		$non_kb_terms = array();
		$kb_terms = array();
		if ( count($taxonomies) > 1) {
			foreach( $terms as $term ) {
				if ( ! empty( $term->taxonomy ) && EPKB_KB_Handler::is_kb_category_taxonomy( $term->taxonomy ) ) {
					$kb_terms[] = $term;
				} else {
					$non_kb_terms[] = $term;
				}
			}
			$terms = $kb_terms;
		}

		// prevent recursion
		remove_filter( 'get_terms', array( $this, 'get_kb_terms' ) );
		$terms = $this->get_user_categories( $terms );
		add_filter( 'get_terms', array( $this, 'get_kb_terms' ), 10, 4 );

		if ( count($taxonomies) > 1) {
			$terms = array_merge($terms, $non_kb_terms);
		}

		return $terms;
	}

	/**
	 * Retrieve user categories.
	 *
	 * @param $terms
	 * @return array
	 */
	private function get_user_categories( $terms ) {

		$is_categories_admin_page = ! empty($_SERVER['REQUEST_URI']) && (
										strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit-tags.php') !== false ||
										strstr($_SERVER['REQUEST_URI'], 'wp-admin/edit.php') !== false ||
										strstr($_SERVER['REQUEST_URI'], 'wp-admin/term.php') !== false );
		$is_categories_admin_page = AMGR_Access_Utilities::is_all_articles_page() ? false : $is_categories_admin_page;
		$category_access = $is_categories_admin_page ? AMGR_Access_Category::AMGR_CATEGORY_EDIT : AMGR_Access_Category::AMGR_CATEGORY_READ;

		// check each term access
		return AMGR_Access_Utilities::filter_user_categories( $terms, $category_access );
	}
}

