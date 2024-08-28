<?php

/**
 * Process front-end Ajax operations.
 */
class EPRF_Rating_Cntrl {

	public function __construct() {
		add_action( 'wp_ajax_eprf-update-rating', array( $this, 'process_user_rating' ) );
		add_action( 'wp_ajax_nopriv_eprf-update-rating', array( $this, 'process_user_rating' ) );
		add_action( 'wp_ajax_eprf-add-comment', array( $this, 'process_add_comment' ) );
		add_action( 'wp_ajax_nopriv_eprf-add-comment', array( $this, 'process_add_comment' ) );
	}

	/**
	 * Record rating given by user
	 */
	public function process_user_rating() {

		$article_id = EPRF_Utilities::get( 'article_id' );
		if ( empty( $article_id ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote ', 'echo-article-rating-and-feedback' ) . ' (1)' ) );
		}

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_eprf_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_eprf_ajax_action'], '_wpnonce_eprf_ajax_action' ) ) {
			EPRF_Utilities::ajax_show_error_die( __( 'Refresh your page', 'echo-article-rating-and-feedback' ) );
		}

		$post = EPRF_Core_Utilities::get_kb_post_secure( $article_id );
		if ( empty( $post ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (2)' ) );
		}

		$kb_id = EPRF_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error( $kb_id ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (3)' ) );
		}

		$kb_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id, true );
		if ( is_wp_error( $kb_config ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (4)' ) );
		}

		// Check if rating changing allowed
		if ( $kb_config[ 'article_content_enable_rating_element' ] == 'off' ) {
			return;
		}

		$rating_value = (float)EPRF_Utilities::get( 'rating_value' );

		// validate rating value
		$rating_mode = $kb_config['rating_mode'];
		$valid_value = $rating_mode == 'eprf-rating-mode-five-stars' ? ( $rating_value > 0 && $rating_value <= 5 ) : ( $rating_value == 1 || $rating_value == 5 );
		if ( ! $valid_value ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (5)' ) );
		}

		// check user already rated this article
		$db_handler = new EPRF_Rating_DB();
		$did_vote = $db_handler->has_article_user_IP( $kb_id, $article_id );
		if ( is_wp_error( $did_vote ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (6)' ) );
		}

		// did user already vote for this article?
		if ( $did_vote ) {
			wp_die( json_encode( array( 'status' => 'success', 'message' => $this->article_feedback_confirmation_msg( $kb_config['rating_confirmation_negative'] ) ) ) );
		}

		$user = EPRF_Utilities::get_current_user();
		$user_id = $user ? $user->ID : 0;

		$saved = $db_handler->insert_rating_record( $kb_id, $article_id, $user_id, date('Y-m-d H:i:s'), $rating_value, $rating_mode, EPRF_Core_Utilities::get_ip_address() );
		if ( is_wp_error( $saved ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not record the vote', 'echo-article-rating-and-feedback' ) . ' (7)' ) );
		}

		// save average like meta field to have the field for query sorting
		$new_rating_data = $db_handler->get_article_ratings( $kb_id, $article_id );
		if ( is_wp_error( $new_rating_data ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (8)' ) );
		}

		$new_rating = EPRF_Core_Utilities::calculate_article_rating_statistics($new_rating_data);

		$result = EPRF_Utilities::save_postmeta( $article_id, 'eprf-article-rating-average', $new_rating['average'], true );
		//$result_like = EPRF_Utilities::save_postmeta( $article_id, 'eprf-article-rating-like',  $new_rating['statistic']['like'], true );
		//$result_dislike = EPRF_Utilities::save_postmeta( $article_id, 'eprf-article-rating-dislike',  $new_rating['statistic']['dislike'], true );
		if ( is_wp_error( $result ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (9)' ) );
		}

		$statistics = EPRF_Rating_View::show_statistics_table( $new_rating, $kb_config, true );
		wp_die( json_encode( array( 'status' => 'success', 'message' => $this->article_feedback_confirmation_msg( $kb_config['rating_confirmation_positive'] ), 'rating' => $new_rating, 'statistics' => $statistics ) ) );
	}

	/**
	 * Record comment given by user
	 */
	public function process_add_comment() {

		$article_id = EPRF_Utilities::get( 'article_id' );
		if ( empty( $article_id ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback') . ' (10)' ) );
		}

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_eprf_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_eprf_ajax_action'], '_wpnonce_eprf_ajax_action' ) ) {
			EPRF_Utilities::ajax_show_error_die( __( 'Refresh your page', 'echo-article-rating-and-feedback' ) );
		}

		$post = EPRF_Core_Utilities::get_kb_post_secure( $article_id );
		if ( empty( $post ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (11)' ) );
		}

		$kb_id = EPRF_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( empty( $kb_id ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (12)' ) );
		}

		$kb_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id, true );
		if ( is_wp_error( $kb_config ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (13)' ) );
		}

		// Spam checking
		// 1. Fake input field - do not proceed if is filled, return generic response
		if ( ! empty( $_REQUEST['catch_details'] ) ) {
			wp_die( json_encode( array( 'status' => 'success_1', 'message' => $this->article_feedback_confirmation_msg( $kb_config['rating_confirmation_positive'] ) ) ) );
		}

		$current_vote = (int)EPRF_Utilities::get( 'current_vote', -1 );

		// check if feedback is allowed
		if ( ! self::is_feedback_allowed( $current_vote, $kb_config ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'Could not vote', 'echo-article-rating-and-feedback' ) . ' (14)' ) );
		}

		// no needs to filter data, wp_new_comment will do it
		$commentdata = array(
			'comment_post_ID'      => $article_id,
			'comment_approved'     => 0,
			'comment_author'       => EPRF_Utilities::get( 'name' ),
			'comment_author_email' => EPRF_Utilities::get( 'email' ),
			'comment_content'      => EPRF_Utilities::get( 'comment' ),
			'comment_type'         => 'eprf-article',
			'comment_author_url'   => ''
		);

		if ( $user = EPRF_Utilities::get_current_user() ) {
			$commentdata['user_ID'] = $user->ID;
		}

		// to enable multiple feedbacks per user: add_filter('duplicate_comment_id', '__return_false');
		$result = wp_new_comment( $commentdata, true );
		if ( is_wp_error( $result ) ) {
			EPRF_Utilities::ajax_show_error_die( $this->article_feedback_confirmation_msg( __( 'You have already submitted feedback.', 'echo-article-rating-and-feedback' ) ) );
		} else {
			wp_die( json_encode( array( 'status' => 'success', 'message' => $this->article_feedback_confirmation_msg( $kb_config['rating_confirmation_positive'] ) ) ) );
		}
	}

	public function article_feedback_confirmation_msg( $msg ) {
		return '<div class="eprf-article-buttons__feedback-confirmation__msg">' . $msg . '</div>';
	}

	/**
	 * Check if the user can leave feedback based on the settings and vote if need
	 *
	 * @param $vote
	 * @param $kb_config
	 * @return bool
	 */
	private static function is_feedback_allowed( $vote, $kb_config ) {

		if ( $kb_config['rating_open_form_button_enable'] == 'on' ) {
			return true;
		}

		if ( $kb_config['rating_mode'] == 'eprf-rating-mode-five-stars' ) {
			if ( $kb_config['rating_feedback_trigger_stars'] == 'always' ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_stars'] == 'user-votes' && $vote >= 0 ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_stars'] == 'negative-five' && $vote >= 0 && $vote < 5 ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_stars'] == 'negative-four' && $vote >= 0 && $vote < 4 ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_stars'] == 'never' && $kb_config['rating_feedback_required_stars'] == 'negative-five' && $vote >= 0 && $vote < 5 ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_stars'] == 'never' && $kb_config['rating_feedback_required_stars'] == 'negative-four' && $vote >= 0 && $vote < 4 ) {
				return true;
			}
		}

		if ( $kb_config['rating_mode'] == 'eprf-rating-mode-like-dislike' ) {
			if ( $kb_config['rating_feedback_trigger_like'] == 'always' ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_like'] == 'dislike' && $vote >= 0 && $vote < 5 ) {
				return true;
			}

			if ( $kb_config['rating_feedback_trigger_like'] == 'never' && $kb_config['rating_feedback_required_like'] == 'negative-five' && $vote >= 0 && $vote < 5 ) {
				return true;
			}
		}

		return false;
	}
}