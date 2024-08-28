<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle Access to Front content - Articles.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Articles_Front {

	// setup hooks
	public function __construct() {

		add_filter('the_posts', array( 'AMGR_Access_Articles_Front', 'found_posts'), 9999);   // frontend
		add_filter('private_title_format', array($this, 'private_title_format'), 9999, 2);   // frontend

		add_action( 'after_setup_theme', array( $this, 'enable_content_hooks' ), 0 );
		// add_action( 'loop_start', array( $this, 'handle_user_access' ) );
		// TODO FUTURE add_action( 'rss_head', array( $this, 'rcCheckFeed' ) );

		add_action('wp', array($this, 'filter_front_end_access'), 9999);
		add_filter('get_pages', array($this, 'filter_get_pages'), 9999, 2);
		// add_filter('get_post_status', array($this, 'is_public_post'), 9999, 2);

		//add_filter('the_posts', array($this, 'filter_get_pages2'), 9999);   // should be optional as 'found_posts' does the job
	}

	// called by old Advanced Search; remove 2025
	public function foundPosts( $posts ) {
		return self::found_posts( $posts );
	}

	/**
	 * Remove KB Articles that the current user does not have access to.
	 *
	 * @param array $posts
	 * @return array
	 */
	public static function found_posts( $posts ) {

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return $posts;
		}

		// check access for each post
		$filtered_posts = array();
		$handler = new AMGR_Access_Article();
		foreach( $posts as $post ) {

			if ( EPKB_Utilities::is_positive_int( $post ) ) {
				$post = get_post( $post );
			}

			// verify user access to the post
			$article_access = $handler->check_post_access( $post, null );
			if ( $article_access === AMGR_Access_Article::ALLOWED ) {
				$filtered_posts[] = $post;
				continue;
			}
		}

		return $filtered_posts;
	}

	/**
	 * Remove 'Private' prefix if configured.
	 *
	 * @param $private_title_format
	 * @param $post
	 * @return mixed
	 */
	public function private_title_format( $private_title_format, $post ) {

		if ( empty($post) || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type) ) {
			return $private_title_format;
		}

		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			return $private_title_format;
		}

		$prefix_on = epkb_get_instance()->kb_access_config_obj->get_value( $kb_id, 'show_private_article_prefix', 'on' );

		return $prefix_on === "off" ? '%s' : $private_title_format;
	}

	/**
	 * If KB article is public then return 'publish' post status so it can be accssed by public.
	 *
	 * @param $post_status
	 * @param $post
	 *
	 * @return string
	 */
	public function is_public_post( $post_status, $post ) {

		if ( ! EPKB_KB_Handler::is_kb_post_type( $post->post_type) ) {
			return $post_status;
		}

		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			return $post_status;
		}

		$is_public = AMGR_Access_Utilities::is_article_public( $kb_id, $post->ID );

		return $is_public && $post_status == 'private' ? 'publish' : $post_status;
	}

	/**
	 * Setup hooks for front-end content to check user access for.
	 */
	public function enable_content_hooks() {
		// Filter the content and excerpts.
		add_filter( 'the_content', array( $this, 'handle_user_access' ), 95 );
		add_filter( 'get_the_excerpt', array( $this, 'handle_user_access' ), 95 );
		add_filter( 'the_excerpt', array( $this, 'handle_user_access' ), 95 );
		add_filter( 'the_content_feed', array( $this, 'handle_user_access' ), 95 );
		add_filter( 'get_comment_text', array( $this, 'handle_user_access' ), 95 );
		add_filter( 'comments_template', array( $this, 'handle_user_access' ), 95 );
	}

	/**
	 * Check that current USER can access the KB Article
	 *
	 * @param $content
	 * @return bool|string
	 */
	public function handle_user_access( $content ) {

		// return content if access allowed
		$handler = new AMGR_Access_Article();
		$post = get_post();
		if ( $handler->check_post_access( $post ) === AMGR_Access_Article::ALLOWED ) {
			return $content;
		}

		return AMGR_Access_Reject::reject_user_access( $this->get_kb_id_from_post_type( $post->post_type ), '02' );
    }

	/**
	 * Protect access to articles and categories
	 */
	public function filter_front_end_access() {
		/** @var WP_Query $wp_query */
		global $wp_query, $post, $post_type;

		// handle page not found error
		if ( $wp_query->is_404 && EPKB_KB_Handler::is_kb_post_type( $post_type ) ) {

			$response = AMGR_Access_Reject::reject_user_access( $this->get_kb_id_from_post_type( $post_type ), '01' );
			if ( $response ) {
				echo $response;
				die();
			}

			return;
		}

		// handle access to a post
		if ( $wp_query->is_single ) {

			$post = empty( $wp_query->queried_object ) ? ( empty( $wp_query->post ) ? $post : $wp_query->post ) : $wp_query->queried_object;
			if ( empty($post) || ! $post instanceof  WP_Post || ! EPKB_KB_Handler::is_kb_post_type( $post->post_type) ) {
				return;
			}

			// return content if access is allowed
			$handler = new AMGR_Access_Article();
			if ( $handler->check_post_access( $post ) === AMGR_Access_Article::ALLOWED ) {
				return;
			}

			echo AMGR_Access_Reject::reject_user_access( $this->get_kb_id_from_post_type( $post->post_type ), '03' );

			die();
		}

		// handle access to category archive page
		if ( $wp_query->is_archive() && $wp_query->is_tax() ) {

			$term = $wp_query->get_queried_object();

			if ( isset( $term->taxonomy ) && EPKB_KB_Handler::is_kb_category_taxonomy( $term->taxonomy ) ) {

				$kb_id = EPKB_KB_Handler::get_kb_id_from_category_taxonomy_name( $term->taxonomy );
				if ( ! is_wp_error($kb_id) ) {

					$handler = new EPKB_Articles_DB();
					$category_articles = $handler->get_articles_by_sub_or_category( $kb_id, $term->term_id, 'date', 200, true );

					$is_category_public = AMGR_Access_Utilities::is_category_public( $kb_id, $term->term_id );
					if ( $is_category_public ) {
						$wp_query->posts = $category_articles;
						$wp_query->post_count = count($wp_query->posts);
					}

					// we do not need to handle category access since each article access should be handled. However, we need to ensure user has
					// access at least to one article so that we can reveal the category

					// get all user groups
					$user_groups = array();
					if ( AMGR_WP_Roles::use_kb_groups() ) {
						$user_groups = AMGR_Access_Utilities::get_user_groups_with_min_role( $kb_id, AMGR_KB_Role::KB_ROLE_SUBSCRIBER );
						if ( $user_groups === null ) {
							die();
						}
					}

					// add Public group
					$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
					if ( is_wp_error($public_group) || empty($public_group) ) {
						die();
					}

					$user_groups[] = $public_group;

					// allow access to the category if user has access to read-only articles
					$read_only_articles_ids = array();
					foreach( $user_groups as $user_group ) {

						$ro_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $user_group->kb_group_id );
						if ( ! empty( $ro_articles_ids ) ) {
							$read_only_articles_ids = array_merge( $read_only_articles_ids, $ro_articles_ids );
						}
					}

					$category_articles_ids = array();
					foreach( $category_articles as $category_article ) {
						$category_articles_ids[] = $category_article->ID;
					}

					// has user access to read-only articles within the category?
					$common_articles = array_intersect($category_articles_ids, $read_only_articles_ids);
					if ( ! empty($common_articles) ) {
						return;
					}
				}
			}

			$handler = new AMGR_Access_Category();
			$can_access = $handler->check_access( $term, null, null, AMGR_Access_Category::AMGR_CATEGORY_READ );
			if ( $can_access === true ) {
				return;
			}

			$response = AMGR_Access_Reject::reject_user_access( $this->get_kb_id_from_taxonomy( $term ), '06' );
			if ( $response ) {
				echo $response;
				die();
			}

			return;
		}

		// handle KB Main Page
		$post = empty( $wp_query->queried_object ) ? ( empty( $wp_query->post ) ? $post : $wp_query->post ) : $wp_query->queried_object;
		if ( ! empty( $post->post_type ) && 'page' == $post->post_type ) {

			// is this KB Main Page?
			$kb_id = 0;
			foreach ( epkb_get_instance()->kb_config_obj->get_kb_configs() as $kb_config ) {
				$kb_main_pages = epkb_get_instance()->kb_config_obj->get_value( $kb_config['id'], 'kb_main_pages' );
				if ( ! is_array( $kb_main_pages ) ) {
					continue;
				}

				// don't update if the current page is not present in KB main pages list
				if ( ! in_array( $post->ID, array_keys( $kb_main_pages ) ) ) {
					continue;
				}

				$kb_id = $kb_config['id'];
				break;
			}

			if ( empty( $kb_id ) ) {
				return;
			}

			if ( ! AMGR_Access_Main_Page_Front::can_user_access_kb_main_page( $kb_id ) ) {
				$response = AMGR_Access_Reject::reject_user_access( $kb_id, '07' );
				if ( $response ) {
					echo $response;
					die();
				}
				return;
			}

			$articles_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, array(), true );
			$category_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, array(), true );
			$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );

			if ( $kb_groups_set === null || ( ! AMGR_Access_Utilities::is_admin_or_kb_manager() && empty( $kb_groups_set['categories_seq_data'] ) && empty( $kb_groups_set['articles_seq_data'] ) ) ) {
				$response = AMGR_Access_Reject::reject_user_access( $kb_id, '08' );
				if ( $response ) {
					echo $response;
					die();
				}
			}

			return;
		}
	}

	private function get_kb_id_from_post_type( $post_type ) {
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post_type );
		return is_wp_error( $kb_id ) ? AMGR_KB_Access_Config_DB::DEFAULT_KB_ID : $kb_id;
	}

	private function get_kb_id_from_taxonomy( $term ) {
		$kb_id = empty($term->taxonomy) ? AMGR_KB_Access_Config_DB::DEFAULT_KB_ID : EPKB_KB_Handler::get_kb_id_from_category_taxonomy_name( $term->taxonomy );
		return is_wp_error( $kb_id ) ? AMGR_KB_Access_Config_DB::DEFAULT_KB_ID : $kb_id;
	}

	/**
	 * Filter articles.
	 * @param $posts
	 * @param $args
	 * @return array
	 */
	public function filter_get_pages($posts, $args) {

		// only handle KB Articles
		$post_type = empty($args['post_type']) ? null : $args['post_type'];
		if ( ! empty($post_type) && $post_type== 'page' || ! EPKB_KB_Handler::is_kb_post_type( $post_type ) ) {
			return $posts;
		}

		if ( ! is_array($posts) ) {
			return $posts;
		}

		// check access to each post
		$handler = new AMGR_Access_Article();
		$filtered_posts = array();
		foreach( $posts as $post ) {
			// return content if access allowed
			if ( $handler->check_post_access( $post ) === AMGR_Access_Article::ALLOWED ) {
				$filtered_posts[] = $post;
			}
		}

		return $filtered_posts;
	}
}
