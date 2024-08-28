<?php  // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Prevent feedback to show on article pages.
 */
class EPRF_Rating_Comments  {

    public function __construct() {
        add_filter( 'parse_comment_query', array( $this, 'comment_where_filter' ));
	//	add_filter( 'get_comments_number', array( $this, 'comment_number_filter' ), 10, 2);
		add_filter( 'pre_wp_update_comment_count_now', array( $this, 'comment_number_filter2' ), 20, 3);
		add_filter( 'admin_comment_types_dropdown', array( $this, 'comment_types_dropdown_filter' ) );
		add_action( 'comment_post', array( $this, 'comment_post_action'), 99, 3 );
    }
	
	// filter for all functions like get_comments 
	public function comment_where_filter( $where ) {
		if ( !is_admin() ) {
			$where->query_vars['type__not_in'] = 'eprf-article';
		}
		
		return $where;
	}
	
	// filter comment_number function on the article page
	public function comment_number_filter( $number, $post_id ) {
		global $wpdb;

		$post = get_post( $post_id );

		// verify it is a KB article
		if ( EPRF_KB_Handler::is_kb_post_type( $post->post_type ) ) {

			$feedback_comments_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_type = 'eprf-article' ", $post_id ) );

			return abs($number - $feedback_comments_count);
		} else {
			return $number;
		}
	}
	
	public function comment_number_filter2( $new, $old, $post_id ) {

		$post_type = get_post_type( $post_id );
		if ( empty( $post_type ) ) {
			return $new;
		}

		if ( ! EPRF_KB_Handler::is_kb_post_type( $post_type ) ) {
			return $new;
		}

		global $wpdb;
		$new = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND comment_type != 'eprf-article'", $post_id ) );

		return $new;
	}

	/**
	 * Filter to add option in comments type in admin
	 * @param $types
	 * @return mixed
	 */

	function comment_types_dropdown_filter( $types ) {
		$types['eprf-article'] = __('Rating Feedback', 'echo-article-rating-and-feedback');
		return $types;
	}

	/**
	 * Filter comments so response of the eprf comment will be eprf comment too
	 */
	function comment_post_action( $comment_ID, $comment_approved, $commentdata ) {
		// not response
		if ( $commentdata['comment_parent'] == 0 || $commentdata['comment_type'] == 'eprf-article' ) {
			return;
		}

		$parent_comment = get_comment( $commentdata['comment_parent'] );
		if ( empty( $parent_comment ) ) {
			return;
		}

		// parent not eprf
		if ( $parent_comment->comment_type != 'eprf-article' ) {
			return;
		}

		// change current comment type
		wp_update_comment( [
			'comment_ID' => $comment_ID,
			'comment_type' => 'eprf-article'
		] );
	}
}