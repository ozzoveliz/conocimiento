<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manages various aspects of access management to KB
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Articles_Back {

	function __construct() {

		add_filter( 'the_title', array( $this, 'filter_article_title'), 9999, 2 );

		add_filter( 'post_row_actions', array( $this, 'check_post' ), 9999, 2 ); // handled by WP capabilities

		add_filter('found_posts', array( $this, 'found_post'), 999, 2);     // backend

		if ( AMGR_Access_Utilities::is_all_articles_page( false ) ) {
			add_filter('post_limits_request', array( $this, 'remove_all_articles_query_limit' ), 1, 10);
		}

		$post_type = EPKB_Utilities::get( 'post_type' );
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post_type );
		if ( empty($kb_id) || is_wp_error($kb_id) ) {
			return;
		}

		// we do not allow user to update categories for inline-save because it is too complicated
		$kb_post_type = EPKB_KB_Handler::get_post_type( $kb_id );
		$action = EPKB_Utilities::post( 'action' );
		if ( in_array($action, array('editpost', 'edit')) && ! has_action( 'save_post_' . $kb_post_type, array( $this, 'save_kb_article' ) ) ) {
			add_action( 'save_post_' . $kb_post_type, array( $this, 'save_kb_article' ), 1, 3 );
		}

		add_action( 'delete_post', array( $this, 'delete_kb_article' ) );
	}

	/**
	 * Protect Article title
	 *
	 * @param string $title Post Title
	 * @param int $id Post ID
	 * @return string $title New title
	 */
	public function filter_article_title( $title, $id=0 ) {
		global $post;

		// interested in KB Articles only
		if ( empty($id) || ! $post instanceof WP_Post || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return $title;
		}

		// can current user access the article
		$handler = new AMGR_Access_Article();
		if ( $handler->check_post_access( $post ) === AMGR_Access_Article::ALLOWED ) {
			return $title;
		}

		return '<protected>';
	}

	/**
	 * Check posts listed on admin page.
	 *
	 * @param array $actions An array of row action links. Defaults are
	 *                         'Edit', 'Quick Edit', 'Restore', 'Trash',
	 *                         'Delete Permanently', 'Preview', and 'View'.
	 * @param WP_Post $post The post object.	 *
	 *
	 * @return mixed
	 */
	public function check_post( $actions, $post ) {

		// interested in KB Articles only
		if ( empty($actions) || ! $post instanceof WP_Post || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return $actions;
		}

		$handler = new AMGR_Access_Article();
		$filtered_actions = array();

		if ( isset($actions['preview']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_READ ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['preview'] = $actions['preview'];
		}
		if ( isset($actions['view']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_READ ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['view'] = $actions['view'];
		}

		if ( isset($actions['restore']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_CREATE ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['restore'] = $actions['restore'];
		}

		if ( isset($actions['edit']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_EDIT ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['edit'] = $actions['edit'];
		}
		if ( isset($actions['quick edit']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_EDIT ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['quick edit'] = $actions['quick edit'];
		}
		if ( isset($actions['inline hide-if-no-js']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_EDIT ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['inline hide-if-no-js'] = $actions['inline hide-if-no-js'];
		}
		if ( isset($actions['trash']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_DELETE ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['trash'] = $actions['trash'];
		}
		if ( isset($actions['delete permanently']) && $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_DELETE ) === AMGR_Access_Article::ALLOWED ) {
			$filtered_actions['delete permanently'] = $actions['delete permanently'];
		}

		return $filtered_actions;
	}

	/**
	 * For All Articles page, query all articles and then filter them based on access and then limit them based on paging.
	 * @param $limit
	 * @return string
	 */
	public function remove_all_articles_query_limit( $limit ) {
		global $amgr_all_articles_limit;
		$amgr_all_articles_limit = $limit;
		return  AMGR_Access_Utilities::is_admin_or_kb_manager() ? $limit : '';
	}

	/**
	 * Remove KB Articles that the current user does not have access to.
	 *
	 * @param $counter
	 * @param $query
	 * @return int
	 */
	public function found_post($counter, $query ) {
		global $amgr_access_action_code;

		if ( empty($query->posts) ) {
			return $counter;
		}

		// check access for each post
		$filtered_posts = array();
		$filtered_posts_wp = array();
		$filtered_draft_post_ids = array();
		$handler = new AMGR_Access_Article();
		$pending_review_post_ids = array();
		foreach( $query->posts as $original_post ) {

			if ( EPKB_Utilities::is_positive_int( $original_post ) || !empty( $original_post->ID ) ) {
				$post = get_post( $original_post );
			} else {
				$post = $original_post;
			}

			// verify user access to the post
			$article_access = $handler->check_post_access( $post, null );
			if ( $article_access === AMGR_Access_Article::ALLOWED ) {
				$filtered_posts[] = $post->ID;
				$filtered_posts_wp[] = $original_post;  // WordPress sometime uses IDs and sometimes posts so keep it
				if ( ! empty($post->post_status) && $post->post_status == 'draft' ) {
					$filtered_draft_post_ids[] = $post->ID;
				}
				if ( ! empty($post->post_status) && $post->post_status == 'pending' ) {
					$pending_review_post_ids[] = $post->ID;
				}
				continue;
			}
		}

		$difference = count($query->posts) - count($filtered_posts);
		$counter = $counter - $difference < 0 ? 0 : $counter - $difference;
		$query->posts = $filtered_posts_wp;
		$query->post_count = count( $query->posts );

		// AMGR Core does not use groups
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {
			return $counter;
		}

		// we need to process articles on All Article page differently except for admin and KB managers
		if ( AMGR_Access_Utilities::is_all_articles_page() && AMGR_Access_Utilities::is_admin_or_kb_manager() == false ) {
			$counter = $this->update_posts_for_all_articles_page( $query, $filtered_posts, $filtered_draft_post_ids, $pending_review_post_ids );
			if ( $counter === null ) {
				$query->post_count = 0;
				$query->posts = array();
				$amgr_access_action_code = 555;
				return 0;
			}
		}

		return $counter;
	}

	/**
	 * Get articles per active group.
	 *
	 * @param WP_Query $query
	 * @param $filtered_posts
	 * @param $filtered_draft_post_ids
	 * @param $pending_review_post_ids
	 *
	 * @return int|null on error
	 */
	private function update_posts_for_all_articles_page( &$query, $filtered_posts, $filtered_draft_post_ids, $pending_review_post_ids ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// ensure we have all information we need
		$kb_group_id = EPKB_Utilities::get( 'amag_chosen_kb_group', null );
		$post_type = EPKB_Utilities::get('post_type' );
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post_type );
		if ( is_wp_error($kb_id) ) {
			return null;
		}

		// first time group is not chosen then choose Public group if user has access otherwise
		// their other group
		if ( empty($kb_group_id) ) {

			// we need active group ID
			$active_group_id = AMGR_Access_Utilities::get_valid_active_group( $kb_id, AMGR_KB_Role::KB_ROLE_CONTRIBUTOR );
			if ( empty($active_group_id) ) {
				return null;
			}
			$kb_group_id = $active_group_id;
		}

		// get all existing articles
		$stored_articles_seq = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, null, true );
		if ( $stored_articles_seq === null ) {
			AMGR_Logging::add_log( "Could not get Articles Sequence", $kb_id );
			return null;
		}

		// get group category IDs
		$group_categories_ids = AMGR_Access_Utilities::get_group_categories_ids( $kb_id, $kb_group_id );
		if ( $group_categories_ids === null ) {
			return null;
		}

		// paging
		$posts_per_page = empty($query->query['posts_per_page']) ? 10 : $query->query['posts_per_page'];
		$paged = empty($query->query['paged']) ? 0 : $query->query['paged'];
		$paged = $paged > 0 ? ( $paged - 1 ) : 0;

		// get all related articles
		if ( ! empty($query->query['s']) ) {

			$like = '%' . $wpdb->esc_like( $query->query['s'] ) . '%';
			$sql = $this->create_search_query( $kb_id, ($paged * $posts_per_page), $posts_per_page );
			$article_ids_obj = $wpdb->get_results( $wpdb->prepare( $sql, $like, $like, $like, $like ) );
			if ( ! empty($wpdb->last_error) ) {
				AMGR_Logging::add_log( "DB failure: ", $wpdb->last_error );
				return null;
			}

			$search_article_ids = array();
			foreach($article_ids_obj as $article_id_obj) {
				$search_article_ids[] = $article_id_obj->ID;
			}

			// get all articles the group can see
			$article_ids = array();
			foreach( $stored_articles_seq as $category_id => $articles_array ) {

				// only get Group categories
				if ( ! in_array($category_id, $group_categories_ids) ) {
					continue;
				}

				$ix = 0;
				foreach( $articles_array as $article_id => $article_title ) {
					if ( $ix ++ < 2 ) {
						continue;
					}

					// the article has to be passed in from WordPress if it was filtered by All | Mine | Published | Draft etc.
					if ( ! in_array($article_id, $filtered_posts) ) {
						continue;
					}

					if ( ! in_array($article_id, $search_article_ids) ) {
						continue;
					}

					if ( ! in_array($article_id, $article_ids) ) {
						$article_ids[] = $article_id;
					}
				}
			}

		} else {

			// get all articles the group can see
			$article_ids = array();
			foreach( $stored_articles_seq as $category_id => $articles_array ) {

				// only get articles that have category in the Group categories
				if ( ! in_array($category_id, $group_categories_ids) ) {
					continue;
				}

				$ix = 0;
				foreach( $articles_array as $article_id => $article_title ) {
					if ( $ix ++ < 2 ) {
						continue;
					}

					// the article has to be passed in from WordPress if it was filtered by All | Mine | Published | Draft etc.
					if ( ! in_array($article_id, $filtered_posts) ) {
						continue;
					}

					if ( ! in_array($article_id, $article_ids) ) {
						$article_ids[] = $article_id;
					}
				}
			}

			// pending articles that user has access to will not be in article sequence so add them back here
			foreach($pending_review_post_ids as $pending_review_post_id) {
				if ( ! in_array($pending_review_post_id, $article_ids) ) {
					$article_ids[] = $pending_review_post_id;
				}
			}
		}

		// Show Drafts as visible posts as well even thou they are not visible on the front-end
		foreach( $filtered_draft_post_ids as $filtered_draft_post_id ) {
			$article_category_ids = AMGR_Access_Utilities::get_article_category_ids_unfiltered( $kb_id, $filtered_draft_post_id );
			// check if article and group categories match
			$common_categories = array_intersect($article_category_ids, $group_categories_ids);

			if ( empty($article_category_ids) || empty($common_categories) ) {
				continue;
			}

			$article_ids[] = $filtered_draft_post_id;
		}

		$visible_posts = array_slice($article_ids, ($paged * $posts_per_page), $posts_per_page);

		// update query
		$full_visible_posts = array();
		foreach( $visible_posts as $post_id ) {
			$post = get_post( $post_id );
			if ( empty($post) || ! is_object($post) || ! $post instanceof WP_Post ) {
				continue;
			}
			$full_visible_posts[] = $post;
		}
		$query->posts = $full_visible_posts;

		return count($article_ids);
	}

	/**
	 * After a post is saved, updated or deleted, update articles categories based on User Group memberships.
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function save_kb_article( $post_id, $post, $update ) {

		// TODO how to fail this save operation with a message to the user?

		if ( empty($post) || ! $post instanceof WP_Post || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			AMGR_Logging::add_log('Invalid post type.', $post_id);
			return;
		}

		// first get KB ID
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log('Invalid KB ID for post ID ' . $post_id, $kb_id);
			wp_die('Could not process post type', 'Error occurred', 403);
		}

		// get current user
		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			AMGR_Logging::add_log('Invalid user for post ID ' . $post_id, $kb_id);
			wp_die('Invalid user for post', 'Error occurred', 403);
		}

		// get selected categories; only if user has valid group we can update the article itself
		$new_category_ids = $this->get_selected_kb_categories( $kb_id, $post );
		if ( $new_category_ids === null ) {
			wp_die( 'Could not get or set selected categories', 'Error occurred (F43)', 403 );
		}

		// update Post terms relationships
		$result = wp_set_object_terms( $post_id, $new_category_ids, EPKB_KB_Handler::get_category_taxonomy_name( $kb_id ) );
		if ( is_wp_error( $result ) ) {
			AMGR_Logging::add_log( 'Could not insert default category for new KB. post id: ' . $post_id . ', taxonomy: ' . EPKB_KB_Handler::get_category_taxonomy_name( $kb_id ), $result );
			wp_die( 'Could not save categories', 'Error occurred (F44)', 403 );
		}

		$this->ensure_article_status_set( $kb_id, $post );
	}

	/**
	 * Prepare query to search
	 * @param $kb_id
	 * @param $from_ix
	 * @param $count
	 * @return string
	 */
	private function create_search_query( $kb_id, $from_ix, $count ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$from_ix = EPKB_Utilities::sanitize_int( $from_ix, 0);
		$count = EPKB_Utilities::sanitize_int( $count, 10);

		return "SELECT SQL_CALC_FOUND_ROWS ID
			  FROM $wpdb->posts
			  WHERE 1=1  AND
              (((post_title LIKE %s OR
                (post_excerpt LIKE %s OR
                (post_content LIKE %s)))
				 AND post_type = 'epkb_post_type_" . $kb_id . "' AND
				 (post_status = 'publish' OR post_status = 'future' OR post_status = 'draft' OR post_status = 'pending' OR post_status = 'private')))
				 ORDER BY post_title LIKE %s DESC, post_date DESC LIMIT " . $from_ix . ", " . $count;
	}

	/**
	 * Ensure that article post_status is set to Private or Public as necessary.
	 *
	 * @param $kb_id
	 * @param $post
	 */
	private function ensure_article_status_set( $kb_id, $post ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// make article private if it needs to be
		$new_status = '';
		$is_article_public = AMGR_Access_Utilities::is_article_public( $kb_id, $post->ID );
		if ( $is_article_public && $post->post_status == 'private' ) {
			$new_status = 'publish';
		} else if ( ! $is_article_public && ! in_array($post->post_status, array('private', 'draft', 'revision', 'pending')) ) {
			$new_status = 'private';
		}

		// make post status public or private if necessary
		if ( ! empty($new_status) ) {
			if ( false === $wpdb->update( $wpdb->posts, array( 'post_status' => $new_status ), array( 'ID' => $post->ID ) ) ) {
				AMGR_Logging::add_log( "Could not update post in the database: " . $wpdb->last_error );
				wp_die('Could not update post status', 'Error occurred', 403);
			}
		}
	}

	/**
	 * Get user selected KB Categories that user has access to
	 * @param $kb_id
	 * @param $post
	 * @return array|null
	 */
	private function get_selected_kb_categories( $kb_id, $post ) {

		// first check if user can modify the article
		$handler = new AMGR_Access_Article( true );
		if ( $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_EDIT ) !== AMGR_Access_Article::ALLOWED ) {
			return null;
		}

		$selected_category_ids = $this->retrieve_selected_categories( $kb_id );

		// AMGR Core does not use groups
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {
			return $selected_category_ids;
		}

		$authorized_groups = $handler->get_authorized_groups();
		$authorized_groups_ids = AMGR_Access_Utilities::get_group_ids( $authorized_groups );

		$new_category_ids = array();
		foreach( $selected_category_ids as $selected_category_id ) {

			// get group ids that have access to this category
			$category_group_ids = epkb_get_instance()->db_access_kb_categories->get_category_group_ids( $kb_id, $selected_category_id );
			if ( $category_group_ids === null ) {
				AMGR_Logging::add_log('Could not retrieve category groups ' . $post->ID, $kb_id);
				return null;
			}

			// skip if this category has no groups unless this is an administrator
			if ( empty($category_group_ids) && ! AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
				continue;
			}

			// if user has no authorized group that category belongs to then skip
			$common_groups_ids = array_intersect( $category_group_ids, $authorized_groups_ids);
			if ( empty($common_groups_ids)  && ! AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
				continue;
			}

			// add selected categories
			$new_category_ids[] = (int)$selected_category_id;
		}

		return $new_category_ids;
	}

	/**
	 * Retrieve categories that user selected on saving post.
	 *
	 * @param $kb_id
	 * @return array
	 */
	private function retrieve_selected_categories( $kb_id ) {

		$tax_input = EPKB_Utilities::post( 'tax_input', array() );
		$category_ids = array();
		$categories = array();
		$action = EPKB_Utilities::post('action');

		// distinguish between save and inline save
		if ( in_array($action, array('editpost') ) ) {

			foreach($_POST as $key => $value) {
				if ( strpos($key, AMGR_Admin_Articles_Page::KB_CATEGORY_TAG) === 0 ) {
					$category_ids[] = EPKB_Utilities::post( $value );
				}
			}

			$_REQUEST['post_category'] = array();

		} else {
			$taxonomy_name = EPKB_KB_Handler::get_category_taxonomy_name( $kb_id );
			$taxonomies = empty($tax_input[$taxonomy_name]) ? array() : $tax_input[$taxonomy_name];
			foreach( $taxonomies as $taxonomy_id ) {
				if ( empty($taxonomy_id) ) {
					continue;
				}
				$category_ids[] = $taxonomy_id;
			}
		}

		// retrieve the actual KB Category
		foreach( $category_ids as $category_id ) {
			if ( empty($category_id) ) {
				continue;
			}
			$category_id = sanitize_text_field($category_id);
			$category_id = str_replace(AMGR_Admin_Articles_Page::KB_CATEGORY_TAG, '', $category_id);

			$categories[] = (int)$category_id;
		}

		return $categories;
	}

	/**
	 * Remove KB Article from AMGR tables.
	 *
	 * @param $post_id
	 */
	public function delete_kb_article( $post_id ) {

		$post = EPKB_Core_Utilities::get_kb_post_secure( $post_id );
		if ( empty($post) || ! $post instanceof WP_Post || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return;
		}

		// first get KB ID
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log('Invalid KB ID for post ID ' . $post_id, $kb_id);
			return;
		}

		// get current user
		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			AMGR_Logging::add_log('Invalid user for post ID ' . $post_id, $kb_id);
			return;
		}

		// first check if user can modify the article
		$handler = new AMGR_Access_Article();
		if ( $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_DELETE ) !== AMGR_Access_Article::ALLOWED ) {
			AMGR_Logging::add_log('User cannot delete this article ', $post_id);
			return;
		}

		epkb_get_instance()->db_access_read_only_articles->delete_article( $kb_id, $post_id );
	}

	/**
	 * Verify a single capability required for an action.
	 *
	 * @param $kb_id
	 * @param $required_capability
	 * @param array $args
	 * @param WP_User $user
	 * @return bool
	 */
	public static function does_have_capability( $kb_id, $required_capability, array $args, WP_User $user ) {

		$post = null;
		if ( count($args) > 2 ) {
			$post = empty( $args[2] ) ? ( empty( $GLOBALS['post'] ) ? null : $GLOBALS['post'] ) : get_post( $args[2] );
			$post_request = EPKB_Utilities::post( 'post' );
			$post = empty( $post ) ? ( empty($post_request) ? 0 : get_post( $post_request ) ) : $post;
		}

		// if capability is not for a specific post then check user role
		if ( empty($post) || ! $post instanceof WP_Post ) {
			$user_role = AMGR_Access_Utilities::get_user_highest_role_from_all_groups( $kb_id, $user );
			if ( $user_role === null ) {
				return false;
			}

			// does the user have correct KB Role for given action?
			return AMGR_KB_Roles::has_user_kb_capability( $kb_id, $user_role, $required_capability );
		}

		// verify it is a KB article
		if ( ! EPKB_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return false;
		}

		// can current user access the article
		$handler = new AMGR_Access_Article();
		if ( $handler->check_post_access( $post, $required_capability ) === AMGR_Access_Article::ALLOWED ) {
			return true;
		}

		return false;
	}
}
