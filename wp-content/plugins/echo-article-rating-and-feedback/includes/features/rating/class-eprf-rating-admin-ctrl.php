<?php  // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process back-end hooks.
 */
class EPRF_Rating_Admin_Cntrl {

    public function __construct() {
		add_action( 'delete_post', array( $this, 'before_delete_article' ) );
	    add_action( 'wp_ajax_eprf_handle_reset_article_feedback', array( $this, 'handle_reset_article_feedback' ) );
    }
	
	// delete article statistics 
	public function before_delete_article( $article_id ) {

		$article = EPRF_Core_Utilities::get_kb_post_secure( $article_id );
		if ( empty( $article ) ) {
			return;
		}

		$kb_id = EPRF_Core_Utilities::get_kb_id( $article );
		if ( empty( $kb_id ) ) {
			return;
		}

		$db_handler = new EPRF_Rating_DB();
		$db_handler->delete_article_rating( $kb_id, $article_id, false );
	}

	/**
	 * Remove all article's ratings. Will not touch comments.
	 */
	public function handle_reset_article_feedback() {

		// verify that the request is authentic
		if ( empty( $_REQUEST['_wpnonce_eprf_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_eprf_ajax_action'], '_wpnonce_eprf_ajax_action' ) ) {
			EPRF_Utilities::ajax_show_error_die( __( 'Refresh your page', 'echo-article-rating-and-feedback' ) );
		}

		// ensure user has correct permission
		if ( ! current_user_can( 'manage_options' ) ) {
			EPRF_Utilities::ajax_show_error_die( 'You do not have permission to reset article ratings.' );
		}

		$article_id = EPRF_Utilities::get( 'article_id' );
		$kb_id = EPRF_Core_Utilities::get_kb_id( get_post( $article_id ) );
		if ( empty( $article_id ) || empty( $kb_id ) ) {
			EPRF_Utilities::ajax_show_error_die( EPRF_Utilities::report_generic_error( 412, __( 'Could not reset (0)', 'echo-article-rating-and-feedback' ) ) );
		}

		$db_handler = new EPRF_Rating_DB();
		$isDeleted = $db_handler->delete_article_rating( $kb_id, $article_id );
		if ( ! $isDeleted ) {
			EPRF_Utilities::ajax_show_error_die( EPRF_Utilities::report_generic_error( 412, __( 'Could not reset (1)', 'echo-article-rating-and-feedback' ) ) );
		}

		wp_die( json_encode( array( 'status' => 'success', 'alert' => __( 'Article ratings were reset', 'echo-article-rating-and-feedback' ), 'message' => '') ) );
	}
}