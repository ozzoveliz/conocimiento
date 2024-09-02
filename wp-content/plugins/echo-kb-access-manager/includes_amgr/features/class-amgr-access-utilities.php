<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KB Access utility functions
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Utilities {

	public static function get_capability_type( $kb_id ) {
		return 'amgr_' . $kb_id . AMGR_Access_Manager::AMGR_KB_CAPABILITIES_SUFFIX;
	}

	/**
     * Get current user.
     *
	 * @return null|WP_User
	 */
	public static function get_current_user() {

		$user = null;
		if ( function_exists('wp_get_current_user') ) {
			$user = wp_get_current_user();
		}

		// is user not logged in? user ID is 0 if not logged
		if ( empty($user) || ! $user instanceof WP_User || empty($user->ID) ) {
		    $user = null;
		}

		return $user;
    }

	/**
	 * Determine if current user is WP administrator WITHOUT calling current_user_can()
	 *
	 * @param null $user
	 * @return bool
	 */
	public static function is_admin( $user=null ) {

		// get current user
        $user = empty($user) ? self::get_current_user() : $user;
		if ( empty($user) || empty($user->roles) ) {
			return false;
		}

		return in_array('administrator', $user->roles) || array_key_exists('manage_options', $user->allcaps);
	}

	/**
	 * Determine if current user is admin or KB Manager
	 * @param null $user
	 * @return bool
	 */
	public static function is_admin_or_kb_manager( $user=null ) {

		// get current user
		$user = empty($user) ? self::get_current_user() : $user;
		if ( empty($user) || empty($user->ID) ) {
			return false;
		}

		// is admin?
		if ( self::is_admin( $user ) ) {
			return true;
		}

		// KB Manager can do anything with KB
		$is_kb_manager = apply_filters( 'aman_is_kb_manager', false, $user->ID );
		if ( $is_kb_manager !== true && $is_kb_manager !== false ) {
			AMGR_Logging::add_log("Error retrieving KB Managers.");
			return false;
		}
		
		return $is_kb_manager;
	}

	public static function is_logged_off() {
	    $user = self::get_current_user();
	    return empty($user);
    }

	/**
	 * Determine if user is on All Articles page.
	 *
	 * @param bool $check_user
	 *
	 * @return bool - true if this is All Articles page
	 */
	public static function is_all_articles_page( $check_user=true ) {

		// logged off user does not need active Group
		if ( $check_user && self::is_logged_off() ) {
			return false;
		}

		// only on All Articles for KB
		$post_type = EPKB_Utilities::get( 'post_type' );
		if ( ! EPKB_KB_Handler::is_kb_post_type( $post_type ) ) {
			return false;
		}

		return empty($_SERVER['REQUEST_URI']) ? false : strstr($_SERVER['REQUEST_URI'], '/edit.php') !== false;
	}

	/**************************************************************************************************************************
	 *
	 *                     KB GROUPS
	 *
	 **************************************************************************************************************************/

	/**
	 * Find the highest role user has in all of his groups.
     *
	 * @param $kb_id
	 * @param $user
	 * @return String|null - role or empty or null on error
	 */
	public static function get_user_highest_role_from_all_groups( $kb_id, $user=null ) {

		// get current user
		$user = empty($user) ? self::get_current_user() : $user;
		if ( empty($user) ) {
			return null;
		}

		// AMGR Core does not use groups
		if ( ! AMGR_WP_Roles::use_kb_groups() ) {
			return AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user );
		}

		// if admin or KB Manager then allow anything
		if ( self::is_admin_or_kb_manager( $user ) ) {
			return AMGR_KB_Role::KB_ROLE_MANAGER;
		}

		$user_groups = epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
		if ( $user_groups === null ) {
			return null;
		}

		$highest_role = '';
		foreach( $user_groups as $user_group ) {
			$highest_role = AMGR_KB_Role::get_higher_role( $highest_role, $user_group->kb_role_name );
		}

		return $highest_role;
	}

	/**
	 * Determine if current user has role equal or higher
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $required_role
	 *
	 * @return bool|null - null on error
	 */
	public static function is_user_role_in_group_same_or_higher( $kb_id, $kb_group_id, $required_role ) {

		// get current user
		$user = self::get_current_user();
		if ( empty($user) ) {
			return null;
		}

		if ( self::is_admin_or_kb_manager( $user ) ) {
			return true;
		}

		// AMGR Core does not use groups
		if ( AMGR_WP_ROLES::use_kb_groups() ) {
			$user_role = epkb_get_instance()->db_kb_group_users->get_user_role( $kb_id, $kb_group_id );
			if ( $user_role === null ) {
				return null;
			}

		} else {
			$user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user );
		}

		return AMGR_KB_Role::is_user_role_same_or_higher( $user_role, $required_role );
	}

	/**
	 * Retrieve user chosen Article Group (All Articles Page) but ensure user belongs to that group.
	 *
	 * @param $kb_id
	 * @param $minimum_required_kb_role
	 *
	 * @return int|null - null on error/access denied and 0 if no active group
	 */
	public static function get_valid_active_group( $kb_id, $minimum_required_kb_role ) {

		// get current user
		$user = self::get_current_user();
		if ( empty($user) ) {
			return null;
		}

		$active_group_id = EPKB_Utilities::post( 'amag_chosen_kb_group' );

		// if no active group then use one the user belongs to
        if ( empty($active_group_id) ) {

		    $user_groups = self::get_user_groups_with_min_role( $kb_id, $minimum_required_kb_role );
            if ( empty($user_groups) || empty($user_groups[0]) || empty($user_groups[0]->kb_group_id) ) {
                return null;
            }

            $active_group_id = $user_groups[0]->kb_group_id;
        }

		if ( ! EPKB_Utilities::is_positive_int( $active_group_id ) ) {
			return null;
		}

		return $active_group_id;
	}

	/**
	 * Get all user KB Groups. Include PUBLIC group only if member or admin
	 *
	 * @param $kb_id
	 * @param $min_user_role
	 * @return array|null
	 */
	public static function get_user_groups_with_min_role( $kb_id, $min_user_role ) {

	    // get all groups including PUBLIC group
		$all_kb_groups = epkb_get_instance()->db_kb_groups->get_groups( $kb_id );
		if ( $all_kb_groups === null ) {
			return null;
		}
		if ( empty($all_kb_groups) ) {
			return array();
		}

		// get current user
		$user = self::get_current_user();

		// if user not logged in then do not return any group
		if ( empty($user) ) {
            return array();
        }

		// if admin or KB Manager then retrieve all groups for this KB
		if ( self::is_admin_or_kb_manager( $user ) ) {
			return $all_kb_groups;
		}

		// get user Groups
		$user_groups = epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
		if ( $user_groups === null ) {
			AMGR_LOGGING::add_log('Could not retrieve user groups. User: ' . $user->ID, $kb_id);
			return null;
		}

		$all_kb_groups_ids = array();
		foreach( $all_kb_groups as $all_kb_group ) {
			$all_kb_groups_ids[$all_kb_group->kb_group_id] = $all_kb_group;
		}

		$user_groups_min_role = array();
		foreach( $user_groups as $user_group ) {
			if ( AMGR_KB_Role::is_user_role_same_or_higher( $user_group->kb_role_name, $min_user_role ) ) {
				$kb_group_id = $user_group->kb_group_id;
				if ( ! empty($all_kb_groups_ids[$kb_group_id] ) ) {
					$user_groups_min_role[] = $all_kb_groups_ids[$kb_group_id];
				}
			}
		}

		return $user_groups_min_role;
	}


	/**************************************************************************************************************************
	 *
	 *                     KB CATEGORIES / ARTICLES
	 *
	 **************************************************************************************************************************/

	/**
	 * Is this an existing article?
	 *
	 * @param $kb_id
	 * @param $post_id
	 * @return bool
	 */
	public static function is_current_article( $kb_id, $post_id ) {

		// get existing articles
		$article_ids = self::get_articles_ids_from_sequence( $kb_id );
		if ( $article_ids === null ) {
			return false;    // we assume it is current article
		}

		// check if we know about this article
		return in_array($post_id, $article_ids);
	}

	/**
	 * Retrieve current article ids.
	 *
	 * @param $kb_id
	 * @param null $articles_sequence
	 *
	 * @return array|null - null on error
	 */
	public static function get_articles_ids_from_sequence( $kb_id, $articles_sequence=null ) {

		// get existing articles
		$stored_articles_seq = empty($articles_sequence) ? EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, null, true ) : $articles_sequence;
		if ( is_wp_error($stored_articles_seq) || $stored_articles_seq === null ) {
			return null;    // we assume it is current article
		}

		$article_ids = array();
		foreach( $stored_articles_seq as $category_id => $articles_array ) {
			$ix = 0;
			foreach( $articles_array as $article_id => $article_title ) {
				if ( $ix ++ < 2 ) {
					continue;
				}
				$article_ids[$article_id] = $article_id;
			}
		}

		return $article_ids;
    }

	/**
	 * Update custom order with changed articles/categories
	 *
	 * @param $kb_id
	 * @return null|array
	 */
	public static function get_categories_from_sequence( $kb_id ) {

		// retrieve previous sequence since we will be adding any new categories to the end
		$categories_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, null, true );
		if ( $categories_seq_data === null ) {
			return null;
		}
		$custom_ids_obj = new EPKB_Categories_Array( $categories_seq_data ); // normalizes the array as well

		$categories_data = array();
		foreach( $custom_ids_obj->ids_array as $category_id => $sub_array ) {

			$categories_data[$category_id] = 1;

			if ( ! empty($sub_array) && is_array($sub_array) ) {
				foreach( $sub_array as $sub_category_id => $sub_sub_array ) {

					$categories_data[$sub_category_id] = 2;

					if ( ! empty($sub_sub_array) && is_array($sub_sub_array) ) {
						foreach( $sub_sub_array as $sub_sub_category_id => $sub_sub_sub_array ) {

							$categories_data[$sub_sub_category_id] = 3;

							if ( ! empty($sub_sub_sub_array) && is_array($sub_sub_sub_array) ) {
								foreach( $sub_sub_sub_array as $sub_sub_sub_category_id => $other ) {
									$categories_data[$sub_sub_sub_category_id] = 4;
								}
							}
						}
					}
				}
			}
		}

		return $categories_data;
	}

	/**
	 * Get category ids for given Group
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @return array|null
	 */
	public static function get_group_categories_ids( $kb_id, $kb_group_id ) {

		$group_categories = epkb_get_instance()->db_access_kb_categories->get_group_categories( $kb_id, $kb_group_id );
		if ( $group_categories === null ) {
			return null;
		}

		// get just user category ids
		$group_category_ids = array();
		foreach( $group_categories as $group_category ) {
			$group_category_ids[] = $group_category->kb_category_id;
		}

		return $group_category_ids;
	}

	/**
	 * Generate Main Page categories/articles data for each KB Group
	 *
	 * @param $kb_id
	 * @param $category_seq_data
	 * @param $articles_seq_data
	 * @return array|null
	 */
	public static function get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data ) {

		// KB Manager and administrator can see it all
		if ( AMGR_Access_Utilities::is_admin_or_kb_manager() ) {
			$kb_groups_set['categories_seq_data'] = $category_seq_data;
			$kb_groups_set['articles_seq_data'] = $articles_seq_data;
			return $kb_groups_set;
		}

		// AMGR core does not have KB Groups
		if ( ! AMGR_WP_Roles::use_kb_groups() ) {
			$user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id );
			If ( ! empty($user_role) ) {
				$kb_groups_set['categories_seq_data'] = $category_seq_data;
				$kb_groups_set['articles_seq_data'] = $articles_seq_data;
				return $kb_groups_set;
			}

			// user does not have KB Role so Public group determines access
		}

		// get all user groups
		$user_groups = array();
		if ( AMGR_WP_Roles::use_kb_groups() ) {
			$user_groups = self::get_user_groups_with_min_role( $kb_id, AMGR_KB_Role::KB_ROLE_SUBSCRIBER );
			if ( $user_groups === null ) {
				return null;
			}
		}

		// add Public group
		$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
		if ( is_wp_error($public_group) || empty($public_group) ) {
			return null;
		}

		$user_groups[] = $public_group;

		// all user group categories
		$read_only_categories_ids = array();
		$read_only_articles_ids = array();
		$all_user_group_category_ids = array();
		foreach( $user_groups as $user_group ) {

			// get read-only categories and articles
			$ro_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $user_group->kb_group_id );
			if ( ! empty($ro_categories_ids) ) {
				$read_only_categories_ids = array_merge($read_only_categories_ids, $ro_categories_ids);
			}

			$ro_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $user_group->kb_group_id );
			if ( ! empty($ro_articles_ids) ) {
				$read_only_articles_ids = array_merge($read_only_articles_ids, $ro_articles_ids);
			}

		    // get group categories IDs
			$group_category_ids = self::get_group_categories_ids( $kb_id, $user_group->kb_group_id );
			if ( $group_category_ids === null ) {
				return null;
			}

			$group_category_ids = self::id_to_int( $group_category_ids );
            $all_user_group_category_ids = array_merge($all_user_group_category_ids, $group_category_ids);
        }

		// read-only categories need to be added
		$all_user_group_category_ids = array_merge($all_user_group_category_ids, $read_only_categories_ids);

        // remove ARTICLE entries for categories user has no access to
		$kb_groups_set['articles_seq_data'] = array();
		$read_only_article_categories_ids = array();
		foreach ( $articles_seq_data as $category_id => $articles_array ) {

			if ( ! in_array($category_id, $all_user_group_category_ids) ) {
				$ix = 0;
				foreach ( $articles_array as $article_id => $article_title ) {
					if ( $ix ++ < 2 ) {
						continue;
					}
					if ( ! in_array( $article_id, $read_only_articles_ids ) ) {
						unset( $articles_array[ $article_id ] );
					}
				}
			}

			// did we find read-only article?
			if ( count($articles_array) > 2 ) {
				$read_only_article_categories_ids[] = (int)$category_id;
			} else if ( ! in_array($category_id, $all_user_group_category_ids) ) {
				continue;
			}

			$kb_groups_set['articles_seq_data'][ $category_id ] = $articles_array;
        }

        // read-only article categories need to be added
		$all_user_group_category_ids = array_merge($all_user_group_category_ids, $read_only_article_categories_ids);

		// remove CATEGORY entries for categories user has no access to
		$kb_groups_set['categories_seq_data'] = $category_seq_data;
		foreach( $kb_groups_set['categories_seq_data'] as $category_id => $sub_array ) {

			if ( ! empty($sub_array) && is_array($sub_array) ) {
				foreach( $sub_array as $sub_category_id => $sub_sub_array ) {

					if ( ! empty($sub_sub_array) && is_array($sub_sub_array) ) {
						foreach( $sub_sub_array as $sub_sub_category_id => $sub_sub_sub_array ) {

							if ( ! empty($sub_sub_sub_array) && is_array($sub_sub_sub_array) ) {
								foreach( $sub_sub_sub_array as $sub_sub_sub_category_id => $other ) {

									// read-only article category - add its tree
									if ( in_array($sub_sub_sub_category_id, $all_user_group_category_ids) ) {
										$all_user_group_category_ids[] = $sub_sub_category_id;
										$all_user_group_category_ids[] = $sub_category_id;
										$all_user_group_category_ids[] = $category_id;
									} else {
									    unset($kb_groups_set['categories_seq_data'][$category_id][$sub_category_id][$sub_sub_category_id][$sub_sub_sub_category_id]);
									}
								}
							}

							// read-only article category - add its tree
							if ( in_array($sub_sub_category_id, $all_user_group_category_ids) ) {
								$all_user_group_category_ids[] = $sub_category_id;
								$all_user_group_category_ids[] = $category_id;
							} else {
								unset($kb_groups_set['categories_seq_data'][$category_id][$sub_category_id][$sub_sub_category_id]);
							}
						}
					}

					// read-only article category - add its tree
					if ( in_array($sub_category_id, $all_user_group_category_ids) ) {
						$all_user_group_category_ids[] = $category_id;
					} else {
						unset($kb_groups_set['categories_seq_data'][$category_id][$sub_category_id]);
					}
				}
			}

			// read-only article category
			if ( ! in_array($category_id, $all_user_group_category_ids) ) {
				unset($kb_groups_set['categories_seq_data'][$category_id]);
			}
		}

		// every category that will be visible needs to have name
		$article_seq_keys = array_keys($kb_groups_set['articles_seq_data']);
		foreach( $all_user_group_category_ids as $all_user_group_category_id ) {
			if ( ! in_array($all_user_group_category_id, $article_seq_keys) ) {
				$category_name = empty($articles_seq_data[$all_user_group_category_id][0]) ? '' : $articles_seq_data[$all_user_group_category_id][0];
				$category_descr = empty($articles_seq_data[$all_user_group_category_id][1]) ? '' : $articles_seq_data[$all_user_group_category_id][1];
				$kb_groups_set['articles_seq_data'][$all_user_group_category_id] = array(0 => $category_name, 1 => $category_descr);
			}
		}

		return $kb_groups_set;
	}

	/**
	 * For given category, update its articles to given new status.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @param bool $is_set_public
	 * @return bool - false on failure
	 */
	public static function set_new_status_for_category_articles( $kb_id, $kb_category_id, $is_set_public=false ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		$new_status = $is_set_public ? 'publish' : 'private';
		$post_status_where = $is_set_public ? " post_status = 'private' "
			: " post_status != 'revision' AND post_status != 'draft' AND post_status != 'pending' AND post_status != 'private' ";

		$query = "UPDATE $wpdb->posts
				  SET post_status = '$new_status'
				  WHERE post_type = 'epkb_post_type_%d' AND $post_status_where AND ID in				    
				  (SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d)";

		// update the post status
		if ( false === $wpdb->query( $wpdb->prepare( $query, $kb_id, $kb_category_id ) ) ) {
			AMGR_Logging::add_log( "Could not update post status in the database: " . $wpdb->last_error );
			return false;
		}

		return true;
	}

	/**
	 * If KB Category is Public then get the category articles.
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 *
	 * @return array|null
	 */
	public static function get_deleted_category_articles( $kb_id, $kb_category_id ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// 1. if KB Category is not Public then nothing to do
		$is_category_public = AMGR_Access_Utilities::is_category_public( $kb_id, $kb_category_id, true );
		if ( $is_category_public === null ) {
			return null;
		}
		if ( $is_category_public === false ) {
			return array();
		}

		// 2. get all articles for given KB Category. This assumes we did not yet deleted the category from WP tables
		$sql = "SELECT * 
				  FROM $wpdb->posts
				  WHERE post_type = 'epkb_post_type_%d' AND post_status = 'publish' AND ID in				    
				  (SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d)";

		$article_ids_obj = $wpdb->get_results( $wpdb->prepare( $sql, $kb_id, $kb_category_id ) );
		if ( ! empty($wpdb->last_error) ) {
			AMGR_Logging::add_log( "DB failure: ", $wpdb->last_error );
			return null;
		}

		// 3. for each article find it is is still public
		$article_ids = array();
		foreach( $article_ids_obj as $article_id_ojb ) {
			$article_ids[] = $article_id_ojb->ID;
		}

		return $article_ids;
	}

	/**
	 * For each article, if it is n
	 *
	 * @param $kb_id
	 * @param $article_ids
	 *
	 * @return bool|null
	 */
	public static function update_status_of_deleted_category_articles( $kb_id, $article_ids ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// for each article find it is is still public
		$return_value = true;
		foreach( $article_ids as $article_id ) {
			$is_article_public = AMGR_Access_Utilities::is_article_public( $kb_id, $article_id, true );
			if ( $is_article_public === true  ) {
				continue;
			}

			// if there is error or article is not public any more, change it to private
			if ( false === $wpdb->update( $wpdb->posts, array( 'post_status' => 'private' ), array( 'ID' => $article_id ) ) ) {
				AMGR_Logging::add_log( "Could not update post in the database: " . $wpdb->last_error );
				$return_value = null;
			}
		}

		return $return_value;
	}

	/**
	 * Filter categories based on user access.
	 *
	 * @param $terms
	 * @param string $category_access
	 * @return array
	 */
	public static function filter_user_categories( $terms, $category_access=AMGR_Access_Category::AMGR_CATEGORY_READ ) {
		// check each term access
		$filtered_terms = array();
		$handler = new AMGR_Access_Category();
		foreach( $terms as $term ) {

			if ( empty($term) ) {
				continue;
			}

			$can_access = $handler->check_access( $term, null, null, $category_access );
			if ( $can_access === true ) {
				$filtered_terms[] = $term;
			}
		}

		return $filtered_terms;
	}

	/**
	 *
	 *  USED TO HANDLE ALL CATEGORIES REGARDLESS OF USER PERMISSIONS.
	 *
	 * Get KB Article categories.
	 *
	 * @param $kb_id
	 * @param $article_id
	 * @return array|null - categories belonging to the given KB Article or null on error
	 */
	public static function get_article_category_ids_unfiltered( $kb_id, $article_id ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty($article_id) ) {
			return null;
		}

		// get article categories
		$post_taxonomy_objs = $wpdb->get_results( $wpdb->prepare(
			"SELECT term_id FROM $wpdb->term_taxonomy
										 WHERE taxonomy = %s and term_taxonomy_id in 
										(SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id = %d) ",
			EPKB_KB_Handler::get_category_taxonomy_name( $kb_id ), $article_id ) );
		if ( $post_taxonomy_objs === null || ! is_array( $post_taxonomy_objs ) ) {
			return null;
		}

		$article_category_ids = array();
		foreach( $post_taxonomy_objs as $post_taxonomy_obj ) {
			if ( isset($post_taxonomy_obj->term_id) ) {
				$article_category_ids[] = $post_taxonomy_obj->term_id;
			}
		}
		return $article_category_ids;
	}


	/**************************************************************************************************************************
	 *
	 *                     OTHER
	 *
	 **************************************************************************************************************************/

	/**
	 * @param $kb_group_id
	 * @param $kb_groups
	 *
	 * @return bool
	 */
	public static function is_kb_group_id_in_array( $kb_group_id, $kb_groups ) {
		if ( empty($kb_groups) ) {
			return false;
		}
		foreach( $kb_groups as $kb_group ) {
			if ( $kb_group->kb_group_id == $kb_group_id ) {
				return true;
			}
		}
		return false;
	}

	public static function get_group_ids( $kb_groups ) {
		if ( empty($kb_groups) ) {
			return array();
		}
	    $kb_groups_ids = array();
		foreach ( $kb_groups as $kb_group ) {
            $kb_groups_ids[] = $kb_group->kb_group_id;
		}
		return $kb_groups_ids;
	}

	/**
	 * Check that given KB Article has PUBLIC group category.
	 *
	 * @param $kb_id
	 * @param $wp_article_id
	 * @param bool $return_error
	 * @return bool or null on error if $return_error is true
	 */
	public static function is_article_public( $kb_id, $wp_article_id, $return_error=false ) {

		// get article categories
		$article_category_ids = self::get_article_category_ids_unfiltered( $kb_id, $wp_article_id );
		if ( $article_category_ids === null ) {
			return ($return_error ? null : false);
		}

		if ( empty($article_category_ids) ) {
			return false;
		}

		// retrieve KB PUBLIC group
		$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
		if ( is_wp_error($public_group) || empty($public_group) ) {
			return ($return_error ? null : false);
		}

	    // does the article have any PUBLIC group categories
		$public_group_category_ids = self::get_group_categories_ids( $kb_id, $public_group->kb_group_id );
		if ( $public_group_category_ids === null ) {
			return ($return_error ? null : false);
		}

		$common_categories = array_intersect($article_category_ids, $public_group_category_ids);
		if ( ! empty($common_categories) ) {
			return true;
		}

		// check read-only public categories
		$ro_public_group_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $public_group->kb_group_id );
		if ( $ro_public_group_categories_ids === null ) {
			return ($return_error ? null : false);
		}

		// have the article and public read-only categories same category?
		$common_ro_categories = array_intersect($article_category_ids, $ro_public_group_categories_ids);
		if ( ! empty($common_ro_categories) ) {
			return true;
		}

		// check read-only public articles
		$ro_public_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $public_group->kb_group_id );
		if ( $ro_public_articles_ids === null ) {
			return ($return_error ? null : false);
		}

		return in_array($wp_article_id, $ro_public_articles_ids);
    }

	/**
	 * Check that given KB Category is PUBLIC
	 *
	 * @param $kb_id
	 * @param $kb_category_id
	 * @param bool $return_error
	 * @return bool or null on error if $return_error is true
	 */
	public static function is_category_public( $kb_id, $kb_category_id, $return_error=false ) {

		// groups belonging to the category
		$kb_categories_group_ids = epkb_get_instance()->db_access_kb_categories->get_category_group_ids( $kb_id, $kb_category_id );
		if ( $kb_categories_group_ids === null ) {
		    return ( $return_error ? null : false);
        }

		// retrieve KB PUBLIC group
		$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
		if ( is_wp_error($public_group) || empty($public_group) ) {
			return ( $return_error ? null : false);
		}
		// check that the Public group has that category
        if ( in_array($public_group->kb_group_id, $kb_categories_group_ids) ) {
			return true;
        }

		// check read-only Public categories
		$ro_public_group_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $public_group->kb_group_id );
		if ( $ro_public_group_categories_ids === null ) {
			return ( $return_error ? null : false);
		}
		if ( in_array($kb_category_id, $ro_public_group_categories_ids) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $required_capability
	 * @return int
	 */
	public static function get_kb_id( $required_capability ) {

		// retrieve KB ID from the capability
		$tokens = explode('_', $required_capability);
		$next_kb_id = false;
		$kb_id = 0;
		foreach($tokens as $token) {
			if ( $next_kb_id ) {
				$kb_id = $token;
				break;
			}
			if ( $token == 'amgr' ) {
				$next_kb_id = true;
			}
		}

		return $kb_id;
	}

	const AMGR_PUBLIC_ACCESS_LEVEL = 'AMGR_PUBLIC_ACCESS_LEVEL';
	const AMGR_RESTRICTED_ACCESS_LEVEL = 'AMGR_RESTRICTED_ACCESS_LEVEL';
	const AMGR_MIXED_ACCESS_LEVEL = 'AMGR_MIXED_ACCESS_LEVEL';

	/**
     * Determine KB access level based on AMGR data
     *
	 * @param $kb_id
	 * @return null|string - null on error
	 */
	public static function determine_kb_access_level( $kb_id ) {

		$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
		if ( is_wp_error($public_group) || empty($public_group) ) {
			return null;
		}

		// retrieve PUBLIC content
		$public_categories = epkb_get_instance()->db_access_kb_categories->get_group_categories( $kb_id, $public_group->kb_group_id );
		$read_only_public_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $public_group->kb_group_id );
        $read_only_public_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $kb_id, $public_group->kb_group_id );
        if ( $public_categories === null || $read_only_public_categories_ids === null || $read_only_public_articles_ids === null ) {
            return null;
        }

		// retrieve all content
		$kb_all_categories = EPKB_Core_Utilities::get_kb_categories_unfiltered( $kb_id );
		$kb_all_articles_ids = AMGR_Access_Utilities::get_articles_ids_from_sequence( $kb_id );
		if ( $kb_all_categories === null && $kb_all_articles_ids === null ) {
			return null;
		}

		$is_all_private = empty($public_categories) && empty($read_only_public_categories_ids) && empty($read_only_public_articles_ids);
		$is_all_public = ! $is_all_private && ( count($read_only_public_categories_ids) == count($kb_all_categories) ) && ! empty($kb_all_categories);

		return $is_all_private ? self::AMGR_RESTRICTED_ACCESS_LEVEL : ( $is_all_public ? self::AMGR_PUBLIC_ACCESS_LEVEL : self::AMGR_MIXED_ACCESS_LEVEL );
    }

    public static function id_to_int( $string_array ) {
		if ( empty($string_array) ) {
			return $string_array;
		}
		foreach( $string_array as $ix => $item ) {
			$string_array[$ix] = (int)$item;
		}
		return $string_array;
    }

	/**
	 * Show on the page error message on page load.
	 *
	 * @param string $error_msg
	 */
	public static function output_inline_error_notice( $error_msg ) {
		echo '<div class="eckb-inline-error-notice">
                <div class="eckb-inline-error-notice-contents">
                ' . wp_kses_post( $error_msg ) . '
				</div>
			  </div>';
	}

	/**
	 * Display content (not message).
	 *
	 * @param $message
	 */
	public static function ajax_show_content( $message ) {
		wp_die( wp_json_encode(  array( 'message' => wp_kses( $message, EPKB_Utilities::get_admin_ui_extended_html_tags() ) ) ) );
	}
}

