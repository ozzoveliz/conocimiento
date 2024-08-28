<?php  

/**
 * Various utility functions only for EPRF plugin
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPRF_Core_Utilities {

	/**
	 * Given all ratings for given article, calculate related statistics.
	 *
	 * @param $data
	 * @return array
	 */
	public static function calculate_article_rating_statistics( $data ) {
		
		$statistic = array(
			'rating-5' => 0,
			'rating-4' => 0,
			'rating-3' => 0,
			'rating-2' => 0,
			'rating-1' => 0,
			'like'     => 0,
			'dislike'  => 0,
		);
		
		$rates_total = 0;
		$data = empty( $_REQUEST['epkb-editor-page-loaded'] ) ? $data : [];
		
		foreach ( $data as $item ) {
			$rates_total += $item->rating_value;

			$ix = floor($item->rating_value);
			
			if ($ix == 0) {
				$ix = 1;
			}
			
			if ( ! isset($statistic['rating-' . $ix]) ) {
				continue;
			}

			$statistic['rating-' . $ix]++;
			
			if ( $item->rating_value < 3 ) {
				$statistic['dislike']++;
			}
			
			if ( $item->rating_value > 3 ) {
				$statistic['like']++;
			}
			
			if ( $item->rating_value == 3 ) {
				// we do not count this right now: $statistic['dislike']++;
				$statistic['like']++;
			}
			
		}
		
		$average = empty($data) ? 0 : round($rates_total / count($data), 1);

		$result = array(
						'total' => $rates_total,
						'average' => $average,
						'count' => count($data),
						'statistic' => $statistic
					);
					
		return $result;
	}

	/**
	 * Calculate article popularity based on the following formulate:
	 *          rating (WR) = (v / (v+m)) x R + (m / (v+m)) x C
	 *
	 * @param $kb_id
	 * @param $article_id
	 *
	 * @return float|WP_Error
	 */
	public static function calculate_article_popularity( $kb_id, $article_id ) {

		// average rating of the article
		$R = EPRF_Utilities::get_postmeta($article_id, 'eprf-article-rating-average', 0, false, true );
		if ( is_wp_error($R) ) {
			return $R;
		}

		// number of votes for the article
		$db = new EPRF_Rating_DB();
		$v = $db->get_count_rows_range( $kb_id, 'post_id', $article_id, $article_id );   // TODO need dates?
		
		// get list of the article_ids of all articles
		$article_ids = get_posts( array(
			'post_type' => EPRF_KB_Handler::get_post_type( $kb_id ),
			'numberposts ' => -1,
			'fields' => 'article_ids',
			'no_found_rows' => true
		) );

		$options = self::get_postmeta_multiple( $article_ids, 'eprf-article-rating-average', array() );

		$meta = array();
		foreach ( $options as $option ) {
			$meta[] = (float)$option->meta_value;
		}
		
		// average of all article averages
		$m = 1;     // TODO: parameter to tune
		$C = array_sum( $meta ) / count( $article_ids );
		$average = ($v / ( $v + $m ) ) * $R + ( $m / ( $v + $m ) ) * $C;

		return $average;
	}

	/**
	 * Retrieve user IP address if possible and hash it to get unique number.
	 *
	 * @return string
	 */
	public static function get_ip_address() {

		$ip_params = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
		foreach ( $ip_params as $ip_param ) {
			if ( ! empty( $_SERVER[$ip_param] ) ) {
				foreach ( explode( ',', $_SERVER[$ip_param] ) as $ip ) {
					$ip = trim( $ip );

					// validate IP address
					if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
						return md5( esc_attr( $ip ) );
					}
				}
			}
		}

		return '';
	}

	/**
	 * Get given Post Metadata
	 *
	 * @param $post_ids
	 * @param $meta_key
	 * @param $default
	 * @param bool $return_error
	 *
	 * @return array|string or default or WP_Error if $return_error is true
	 */
	public static function get_postmeta_multiple( $post_ids, $meta_key, $default, $return_error=false ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( ! is_array($post_ids) ) {
			return $return_error ? new WP_Error( 'Invalid Post IDs', EPRF_Utilities::get_variable_string( $post_ids ) ) : $default;
		}

		// retrieve data for all posts with specific option
		$post_ids = implode( ',', $post_ids );
		$options = $wpdb->get_results( $wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE post_id in (%s) and meta_key = '%s'", $post_ids, $meta_key ) );
		if ($options !== null ) {
			$options = maybe_unserialize( $options );
		}

		if ( $return_error && $options === null && ! empty($wpdb->last_error) ) {
			EPRF_Logging::add_log( "DB failure: " . $wpdb->last_error, 'Meta Key: ' . $meta_key );
			return new WP_Error('DB failure', $wpdb->last_error);
		}

		// if KB option is missing then return defaults
		if ( $options === null || ! is_array($options) ) {
			return $default;
		}

		return $options;
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
		$post_id = EPRF_Utilities::sanitize_int( $post_id );
		if ( empty($post_id) ) {
			return null;
		}

		// retrieve the post and ensure it is one
		$post = get_post( $post_id );
		if ( empty($post) || ! $post instanceof WP_Post ) {
			return null;
		}

		// verify it is a KB article
		if ( ! EPRF_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * Retrieve KB ID.
	 *
	 * @param WP_Post $post
	 * @return int|NULL on ERROR
	 */
	public static function get_kb_id( $post=null ) {
		global $eckb_kb_id;

		$kb_id = '';
		$post = $post === null ? get_post() : $post;
		if ( ! empty( $post ) && $post instanceof WP_Post ) {
			$kb_id = EPRF_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		}

		$kb_id = empty($kb_id) || is_wp_error($kb_id) ? ( empty($eckb_kb_id) ? '' : $eckb_kb_id ) : $kb_id;
		if ( empty($kb_id) ) {
			EPRF_Logging::add_log("KB ID not found", $kb_id);
			return null;
		}

		return $kb_id;
	}

	/**
	 * Is WPML enabled? Only for KB CORE. ADD-ONs to call this function in core
	 *
	 * @param $kb_id
	 *
	 * @return bool
	 */
	public static function is_wpml_enabled_addon( $kb_id ) {

		if ( EPRF_Utilities::is_positive_int( $kb_id ) ) {
			$kb_config = EPRF_KB_Core::get_kb_config( $kb_id );
			if ( is_wp_error( $kb_config ) ) {
				return false;
			}
		} else {
			return false;
		}

		return EPRF_Utilities::is_wpml_enabled( $kb_config );
	}
}
