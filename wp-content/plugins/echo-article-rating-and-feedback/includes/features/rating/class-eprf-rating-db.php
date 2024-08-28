<?php  // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CRUD for article rating data
 *
 * @property string primary_key
 * @property string table_name
 */
class EPRF_Rating_DB extends EPRF_DB  {

    /**
     * Get things started
     *
     * @access  public
     */
    public function __construct() {
        /** @var $wpdb Wpdb */
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'ep'.'kb_article_ratings';
        $this->primary_key = 'rating_id';
    }

    /**
     * Get columns and formats
     *
     * @access  public
     */
    public function get_column_format() {
        return array(
			'rating_id'        => '%d',
			'kb_id'            => '%d',
			'post_id'          => '%d',
			'user_id'          => '%d',
			'rating_date'      => '%s',
			'rating_value'     => '%f',
			'rating_type'      => '%s',
			'user_ip'          => '%s'
        );
    }

    /**
     * Get default column values
     *
     * @access  public
     */
    public function get_column_defaults() {
        return array(
            'user_input'      => '',
            'rating_value'    => '',
            'rating_date'     => date('Y-m-d H:i:s')
        );
    }
	
	/**
	 * Retrieve rating data for the article.
	 *
	 * @param $kb_id
	 * @param $article_id
	 * @return array | WP_Error
	 */
    public function get_article_ratings( $kb_id, $article_id ) {

	    if ( ! EPRF_Utilities::is_positive_int($kb_id) ) {
		    EPRF_Logging::add_log("KB ID is not valid", $kb_id);
		    return new WP_Error('invalid-kb-id', 'KB ID is not valid');
	    }
		
		$review_data = $this->get_rows_by_column_value( 'post_id', $article_id );		
		if ( is_wp_error( $review_data) ) {
			EPRF_Logging::add_log("Cannot get results for article: " . $article_id , $kb_id);
			return $review_data;
		}
		
		return empty($review_data) ? array() : $review_data;
    }

	/**
	 * Retrieve MOST RATED articles
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $limit
	 *
	 * @return WP_Error|array - array of Objects or empty
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_most_frequently_rated_articles( $kb_id, $date_from, $date_to, $limit ) {

		if ( ! EPRF_Utilities::is_positive_int($kb_id) ) {
			EPRF_Logging::add_log("KB ID is not valid", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$result = $this->get_rows_by_date_range( $kb_id, 'rating_date', $date_from, $date_to, 'times DESC', 'post_id', $limit );
		if ( is_wp_error( $result) ) {
			EPRF_Logging::add_log("Cannot get most rated articles records: ", $kb_id);
			return $result;
		}

		return empty($result) ? array() : $result;
	}

	/**
	 * Retrieve LEAST RATATED articles
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 * @param $limit
	 *
	 * @return WP_Error|array - array of Objects or empty
	 *                    - EMPTY array() if no rows
	 *                    - NULL on failure
	 */
	public function get_least_frequently_rated_articles( $kb_id, $date_from, $date_to, $limit ) {

		if ( ! EPRF_Utilities::is_positive_int($kb_id) ) {
			EPRF_Logging::add_log("KB ID is not valid", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$result = $this->get_rows_by_date_range( $kb_id, 'rating_date', $date_from, $date_to, 'times ASC', 'post_id', $limit );
		if ( is_wp_error( $result) ) {
			EPRF_Logging::add_log("Cannot get least rated articles records: ", $kb_id);
			return $result;
		}

		return empty($result) ? array() : $result;
	}

	/**
	 * Get number of all articles ratings.
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 *
	 * @return int|WP_Error
	 */
	public function get_number_of_votes( $kb_id, $date_from, $date_to ) {
		
		if ( ! EPRF_Utilities::is_positive_int($kb_id) ) {
			EPRF_Logging::add_log("KB ID is not valid", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$result = $this->get_count_rows_range( $kb_id, 'rating_date', $date_from, $date_to );
		if ( is_wp_error( $result) ) {
			EPRF_Logging::add_log("Cannot get rating count: ", $kb_id);
			return $result;
		}

		return $result;
	}

	// TODO FUTURE
	/**
	 * Retrieve "no results" rating COUNT
	 *
	 * @param $kb_id
	 * @param $date_from
	 * @param $date_to
	 *
	 * @return int
	 */
	public function get_number_of_articles_without_rating( $kb_id, $date_from, $date_to ) {
		
		if ( ! EPRF_Utilities::is_positive_int($kb_id) ) {
			EPRF_Logging::add_log("KB ID is not valid", $kb_id);
			return 0;
		}

		$result = $this->get_count_rows_range( $kb_id, 'rating_date', $date_from, $date_to ); //, ' AND count = 0 ' );
		if ( is_wp_error( $result) ) {
			EPRF_Logging::add_log("Cannot get rating count: ", $kb_id);
			return 0;
		}

		return $result;
	}

	/**
	 * Insert a new rating record
	 *
	 * @param $kb_id
	 * @param $post_id
	 * @param $user_id
	 * @param $rating_date
	 * @param $rating_value
	 * @param $rating_type
	 * @param $user_ip
	 *
	 * @return int|WP_Error
	 */
    public function insert_rating_record( $kb_id, $post_id, $user_id, $rating_date, $rating_value, $rating_type, $user_ip ) {

	    // ensure KB exists
	    if ( ! $this->is_kb_id_valid( $kb_id ) ) {
		    EPRF_Logging::add_log("KB ID is not valid: ", $kb_id);
		    return new WP_Error('invalid-kb-id', 'KB ID is not valid');
	    }

        // insert the record
	    $record = array('kb_id' => $kb_id, 'post_id' => $post_id, 'user_id' => $user_id, 'rating_date' => $rating_date, 'rating_value' => $rating_value,
			            'rating_type' => $rating_type, 'user_ip' => $user_ip);
		
        $rating_id = parent::insert_record( $record );
        if ( empty($rating_id) ) {
	        return new WP_Error('db-insert-error', 'Could not insert rating record');
        }

        return $rating_id;
    }

	/**
	 * @param $kb_id
	 *
	 * @return bool
	 */
	private function is_kb_id_valid( $kb_id ) {
		$kb_ids = eprf_get_instance()->kb_config_obj->get_kb_ids();
		return empty($kb_ids) ? false : in_array($kb_id, $kb_ids);
	}

	/**
	 * Check user IP in DB to prevent duplicate reviews
	 *
	 * @param $kb_id
	 * @param $article_id
	 * @param $run_update
	 * @return bool|WP_Error
	 */
	public function has_article_user_IP( $kb_id, $article_id, $run_update=false ) {

		if ( ! EPRF_Utilities::is_positive_int( $kb_id ) ) {
			EPRF_Logging::add_log("KB ID is not valid", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		/**  TODO remove in 2024  */
		if ( $run_update ) {
			// check if we have not converted IP in DB and replace with md5 hash
			global $wpdb;

			$old_ip_results = $wpdb->get_results( "SELECT * FROM $this->table_name WHERE user_ip REGEXP '[:.]' LIMIT 20;" );
			if ( ! empty( $old_ip_results ) ) {
				foreach ( $old_ip_results as $old_ip_result ) {
					$wpdb->update( $this->table_name, array('user_ip' => md5( $old_ip_result->user_ip) ), array( 'rating_id' => $old_ip_result->rating_id ) );
				}
			}
		}
		/**  END TODO remove in 2024  */

		// if user LOGGED IN then find user by his/her user ID
		$user = EPRF_Utilities::get_current_user();
		if ( ! empty( $user ) ) {
			$result = $this->get_a_row_by_where_clause( array('user_id' => $user->ID, 'post_id' => $article_id) );
			if ( is_wp_error( $result) ) {
				EPRF_Logging::add_log("Cannot get article: ", $article_id);
				return $result;
			}
			
			return ! empty($result);
		}

		// if user NOT logged in then check by IP Hash
		$user_ip = EPRF_Core_Utilities::get_ip_address();
		$result = $this->get_a_row_by_where_clause( array('user_ip' => $user_ip, 'post_id' => $article_id) );
		if ( is_wp_error( $result) ) {
			EPRF_Logging::add_log("Cannot get article: ", $article_id);
			return $result;
		}

		return ! empty( $result );
	}

	/**
	 * Delete all ratings for the product
	 *
	 * @param $kb_id
	 * @param $post_id
	 * @param bool $delete_meta
	 * @return bool
	 */
	public function delete_article_rating( $kb_id, $post_id, $delete_meta=true ) {
		
		// try to clean in db
		$is_deleted =  $this->delete_rows_by_where_clause( array( 'kb_id' => $kb_id, 'post_id' => $post_id ) );

		// clean average in the post
		if ( $is_deleted && $delete_meta ) {
			$result = EPRF_Utilities::save_postmeta( $post_id, 'eprf-article-rating-average', 0, true );
			$result_like = EPRF_Utilities::save_postmeta( $post_id, 'eprf-article-rating-like', 0, true );
			$result_dislike = EPRF_Utilities::save_postmeta( $post_id, 'eprf-article-rating-dislike', 0, true );
			if ( is_wp_error($result) || is_wp_error($result_like) || is_wp_error($result_dislike) ) {
				return false;
			}
		}

		return $is_deleted;
	}

	// FUTURE TO DO
	/**
	 * Get most helpfull articles
	 *
	 * @param $kb_id
	 * @param $limit
	 *
	 * @return array|WP_error
	 */
	public function get_most_helpfull($kb_id, $limit) {
		// ensure KB exists
		if ( ! $this->is_kb_id_valid( $kb_id ) ) {
			EPRF_Logging::add_log("KB ID is not valid: ", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );

		$args = array(
				'numberposts' => $limit,
				'meta_key' => 'eprf-article-rating-average',
				'post_type'   => $kb_post_type,
				'orderby' => 'meta_value'
		);

		$posts = get_posts( $args );

		$most_helpful = array();

		if ( $posts ) {
			foreach ($posts as $post) {
				$most_helpful[] = array(
						'title' => $post->post_title,
						'average' => get_post_meta($post->ID, 'eprf-article-rating-average', true),
						'count' => $this->get_count_rows_range($kb_id, 'rating_date', '2000-01-01 00:00:00', '2100-01-01 00:00:00', " AND post_id = '".$post->ID."'")
				);
			}
		}

		return $most_helpful;
	}

	// TODO FUTURE
	/**
	 * Get least helpfull articles
	 *
	 * @param $kb_id
	 * @param $limit
	 *
	 * @return array|WP_Error
	 */
	public function get_least_helpfull($kb_id, $limit) {
		// ensure KB exists
		if ( ! $this->is_kb_id_valid( $kb_id ) ) {
			EPRF_Logging::add_log("KB ID is not valid: ", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );

		$args = array(
				'numberposts' => $limit,
				'post_type'   => $kb_post_type,
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
				'meta_query' => array(
						'relation' => 'OR',
						'meta_value_num' => array(
								'key' => 'eprf-article-rating-average',
								'compare' => 'EXISTS'
						),

						array(
								'key' => 'eprf-article-rating-average',
								'compare' => 'NOT EXISTS'
						)
				)
		);

		$posts = get_posts( $args );

		$least_helpful = array();

		if ( $posts ) {
			foreach ($posts as $post) {
				$least_helpful[] = array(
						'title' => $post->post_title,
						'average' => get_post_meta($post->ID, 'eprf-article-rating-average', true),
						'count' => $this->get_count_rows_range($kb_id, 'rating_date', '2000-01-01 00:00:00', '2100-01-01 00:00:00', " AND post_id = '".$post->ID."'")
				);
			}
		}

		return $least_helpful;
	}

	// TODO FUTURE
	/**
	 * Get least rated
	 *
	 * @param $kb_id
	 * @param $limit
	 *
	 * @return array|WP_Error
	 */
	public function get_least_rated($kb_id, $limit) {
		// ensure KB exists
		if ( ! $this->is_kb_id_valid( $kb_id ) ) {
			EPRF_Logging::add_log("KB ID is not valid: ", $kb_id);
			return new WP_Error('invalid-kb-id', 'KB ID is not valid');
		}

		$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );

		$args = array(
				'numberposts' => $limit,
				'post_type'   => $kb_post_type,
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
				'meta_query' => array(
						'relation' => 'OR',
						'meta_value_num' => array(
								'key' => 'eprf-article-rating-average',
								'compare' => 'EXISTS'
						),
				)
		);

		$posts = get_posts( $args );

		$least_helpful = array();

		if ($posts) {
			foreach ($posts as $post) {
				$least_helpful[] = array(
						'title' => $post->post_title,
						'average' => get_post_meta($post->ID, 'eprf-article-rating-average', true),
						'count' => $this->get_count_rows_range($kb_id, 'rating_date', '2000-01-01 00:00:00', '2100-01-01 00:00:00', " AND post_id = '".$post->ID."'")
				);
			}
		}

		return $least_helpful;
	}

	/**
     * Create the table
     *
     * @access  public
     * @since   2.1
     */
    public function create_table() {
	    global $wpdb;

	    $collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $sql = "CREATE TABLE " . $this->table_name . " (
	                rating_id       BIGINT(20) NOT NULL AUTO_INCREMENT,
	                kb_id           BIGINT(20) NOT NULL,
	                post_id         BIGINT(20) NOT NULL,
   	                user_id         BIGINT(20) NOT NULL,
	                rating_date     datetime NOT NULL,
	                rating_value    FLOAT(20) NOT NULL,
	                rating_type     VARCHAR(50) NOT NULL,
	                user_ip         VARCHAR(50) NOT NULL,
	                PRIMARY KEY  (rating_id),
	                KEY ix_eprf_kb_id (kb_id),
	                KEY ix_eprf_date (rating_date),
	                KEY ix_eprf_rating_value (rating_value)	                
		) " . $collate . ";";

        dbDelta( $sql );
    }
}