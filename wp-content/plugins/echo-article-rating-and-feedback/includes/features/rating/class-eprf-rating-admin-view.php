<?php
/**
 * Add ratings values to articles and articles list
 */
class EPRF_Rating_Admin_View {

	public function __construct() {
		add_action( 'admin_init', array($this, 'add_ID_to_columns_list') );
		add_action( 'post_submitbox_misc_actions', array($this, 'add_stats_to_edited_article') );
		add_action( 'admin_head', array($this, 'add_stats_to_edited_gt_article') );
		add_action( 'pre_get_posts', array($this, 'sort_by_rating') );
		add_action( 'restrict_manage_posts', array($this, 'add_rating_dropdown_filter'), 10, 2 );

		// both existing and new articles that were never rated will have no average rating
		/* $kb_id = EPRF_KB_Core::get_current_kb_id();
		/* if ( $kb_id ) {
			$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );
			add_action( "save_post_" . $kb_post_type , array( $this, 'add_default_rating' ) );
		} */
	}

	/**
	 * Add Rating to All articles pages
	 */
	public function add_ID_to_columns_list() {

		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

		$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );

		add_action( "manage_" . $kb_post_type . "_posts_columns", array( $this, 'add_column_heading' ), 99, 1);
		add_filter( "manage_" . $kb_post_type . "_posts_custom_column", array( $this, 'add_column_value' ), 99, 2 );
		add_filter( "manage_edit-" . $kb_post_type . "_sortable_columns", array( $this, 'add_sortable_columns'), 99 );
	}
	
	public function add_column_heading( $columns ) {
		
		$columns = empty($columns) ? array() : $columns;
		$additional_columns = array();
		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return $columns;
		}

		$add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		if ( is_wp_error($add_on_config) ) {
			return $columns;
		}

		switch ( $add_on_config['rating_mode'] ) {
			case 'eprf-rating-mode-five-stars':
				$additional_columns['eprf_rating'] = __('Rating', 'echo-article-rating-and-feedback' );
				break;
			case 'eprf-rating-mode-like-dislike':
				$additional_columns['eprf_average'] = __('Rating Average', 'echo-article-rating-and-feedback' );
				$additional_columns['eprf_rating'] = __('Rating', 'echo-article-rating-and-feedback' );
				//$additional_columns['eprf_like'] = __('Like', 'echo-article-rating-and-feedback' );
				//$additional_columns['eprf_dislike'] = __('Dislike', 'echo-article-rating-and-feedback' );
				break;
		}
		
		return array_merge( $columns, $additional_columns );
	}
	
	public function add_column_value( $column_name, $post_id ) {

		if ( ! in_array($column_name, array('eprf_rating','eprf_average')) ) {
			return;
		}

		// get add-on configuration
		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

		$add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		$db_handler = new EPRF_Rating_DB();

		if ( ! empty($column_name) && $column_name == 'eprf_rating' ) {
			$view = new EPRF_Rating_View();

			// based on whether user voted or not we display info
			$did_vote = $db_handler->has_article_user_IP( $kb_id, $post_id, true );
			if ( is_wp_error($did_vote) ) {
				return;
			}

			ob_start();
			$view->show_rating_element( $kb_id, $add_on_config, $post_id, $did_vote, false );
			$output = ob_get_clean();
			echo $output;
		}
		
		if ( ! empty($column_name) && $column_name == 'eprf_average' ) {

			$rating_data = $db_handler->get_article_ratings( $kb_id, $post_id );
			if ( is_wp_error($rating_data) ) {
				return;
			}

			if ( empty($rating_data) ) {
				_e( 'Not rated', 'echo-article-rating-and-feedback' );
			} else {
				$rating = EPRF_Core_Utilities::calculate_article_rating_statistics( $rating_data );
				echo round( $rating['statistic']['like']  / ($rating['statistic']['like'] + $rating['statistic']['dislike']) * 100  ) . '%'  ;
			}
		}
		
		/*if ( ! empty($column_name) && $column_name == 'eprf_like' ) {

			$rating_data = $db_handler->get_article_ratings( $kb_id, $post_id );

			if ( ! $rating_data ) {
				// empty cell because No rated in the neibor column
			} else {
				$rating = EPRF_Core_Utilities::calculate_article_rating_statistics( $rating_data );
				echo $rating['statistic']['like'];
			}
		}
		
		if ( ! empty($column_name) && $column_name == 'eprf_dislike' ) {

			$rating_data = $db_handler->get_article_ratings( $kb_id, $post_id );

			if ( ! $rating_data ) {
				// empty cell because No rated in the neibor column
			} else {
				$rating = EPRF_Core_Utilities::calculate_article_rating_statistics( $rating_data );
				echo $rating['statistic']['dislike'];
			}
		} */
	}
	
	public function add_sortable_columns($sortable_columns) {
		$sortable_columns['eprf_rating'] = 'eprf_rating';
		$sortable_columns['eprf_average'] = 'eprf_average';
		//$sortable_columns['eprf_like'] = 'eprf_like';
		//$sortable_columns['eprf_dislike'] = 'eprf_dislike';
		return $sortable_columns;
	}

	/**
	 * User sorts by rating column values.
	 * @param WP_Query $query
     */
	function sort_by_rating( $query ) {

		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

		$kb_post_type = EPRF_KB_Handler::get_post_type( $kb_id );
		if ( $query->query['post_type'] != $kb_post_type ) {
			return;
		}

		$rating_filter_value = EPRF_Utilities::get( 'eprf_rating_value', '' );

		// FILTERING for article rating
		if ( ! empty($rating_filter_value) ) {

			$query->set('meta_query', array(
						'relation' => 'AND',
						array(
							'key' => 'eprf-article-rating-average',
							'compare' => '>=',
							'value' => (float)$rating_filter_value,
							'type' => 'DECIMAL(3,2)'
						),
						array(
							'key' => 'eprf-article-rating-average',
							'compare' => '<=',
							'value' => (float)$rating_filter_value + 0.5,
							'type' => 'DECIMAL(3,2)'
						)
			) );
		}

		// ORDERING for article rating
		$orderby = $query->get( 'orderby' );
		switch ( $orderby ) {

			case 'eprf_rating':

				if ( empty($rating_filter_value) ) {
					$query->set('meta_query', array(
						'relation' => 'OR',

						'meta_value_num' => array(
							'key' => 'eprf-article-rating-average',
							'compare' => 'EXISTS'
						),

						'm2' => array(
							'key' => 'eprf-article-rating-average',
							'compare' => 'NOT EXISTS'
						)
					) );
				}

				$query->set( 'orderby', 'm2 meta_value_num' );
				
			break;
			
			case 'eprf_average':

				if ( empty($rating_filter_value) ) {
					$query->set('meta_query', array(
						'relation' => 'OR',

						'meta_value_num' => array(
							'key' => 'eprf-article-rating-average',
							'compare' => 'EXISTS'
						),

						'm2' => array(
							'key' => 'eprf-article-rating-average',
							'compare' => 'NOT EXISTS'
						)
					) );
				}

				$query->set( 'orderby', 'm2 meta_value_num' );
			break;
			
			/* case 'eprf_like':

				if ( empty($rating_filter_value) ) {
					$query->set('meta_query', array(
						'relation' => 'OR',

						'meta_value_num' => array(
							'key' => 'eprf-article-rating-like',
							'compare' => 'EXISTS'
						),

						'm2' => array(
							'key' => 'eprf-article-rating-like',
							'compare' => 'NOT EXISTS'
						)
					) );
				}

				$query->set( 'orderby', 'm2 meta_value_num' );
			break;
			
			case 'eprf_dislike':

				if ( empty($rating_filter_value) ) {
					$query->set('meta_query', array(
						'relation' => 'OR',

						'meta_value_num' => array(
							'key' => 'eprf-article-rating-dislike',
							'compare' => 'EXISTS'
						),

						'm2' => array(
							'key' => 'eprf-article-rating-dislike',
							'compare' => 'NOT EXISTS'
						)
					) );
				}

				$query->set( 'orderby', 'm2 meta_value_num' );
			break; */
		}
	}

	/**
	 * Add rating stats to the article edited in classic editor
	 * @param $post
	 */
	public function add_stats_to_edited_article( $post ) {

		if ( ! EPRF_KB_Handler::is_kb_post_type( $post->post_type ) ) {
			return;
		}

		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}   ?>

		<div class="eprf-ratings-classic-editor-container">
			<span class="dashicons dashicons-chart-bar" style="color: #82878c;"></span>
			<button class="gut_stars_reset not-gutenberg" id="resetArticleRating"><span class="epkbfa epkbfa-undo" aria-hidden="true"></span></button>				<?php
				_e('Rating', 'echo-article-rating-and-feedback' );

				// based on whether user voted or not we display info
				$db_handler = new EPRF_Rating_DB();
				$did_vote = $db_handler->has_article_user_IP( $kb_id, $post->ID, true );
				if ( ! is_wp_error($did_vote) ) {
					$add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
					$view = new EPRF_Rating_View();
					$view->show_rating_element( $kb_id, $add_on_config, $post->ID, $did_vote, false );
				}					?>

		</div> <?php
	}

	/**
	 * Add rating stats to the article edited in the Gutenberg editor
	 */
	public function add_stats_to_edited_gt_article() {
		global $post;

		// return if we are not editing KB article in Gutenberg
		if ( EPRF_Utilities::get('action') != 'edit' || empty($post->post_type) || ! EPRF_KB_Handler::is_kb_post_type( $post->post_type ) || ! EPRF_Utilities::is_block_editor_active() ) {
			return;
		}

		$kb_id = EPRF_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

		// output article rating element
		$add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		$view = new EPRF_Rating_View();

		// based on whether user voted or not we display info
		$db_handler = new EPRF_Rating_DB();
		$did_vote = $db_handler->has_article_user_IP( $kb_id, $post->ID, true );
		if ( is_wp_error($did_vote) ) {
			return;
		}

		ob_start();
		$view->show_rating_element( $kb_id, $add_on_config, $post->ID, $did_vote, false );
		$output = ob_get_clean(); ?>

		<div id="eprf_rating_html" style="display: none;"><?php echo $output; ?></div>  <?php
	}

	/**
	 * Add filter to the All Articles page for filtering based on rating
	 * @param $post_type
	 * @param $which
	 */
	public function add_rating_dropdown_filter( $post_type, $which ) {

	    if ( ! is_admin() || empty($post_type) || ! EPRF_KB_Handler::is_kb_post_type( $post_type ) ) {
            return;
        } 
		
		$kb_id = EPRF_KB_Core::get_current_kb_id();
		$add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		
		if ( $add_on_config['rating_mode'] == 'eprf-rating-mode-five-stars' ) { ?>

		<select name="eprf_rating_value">
			<option value="0"><?php _e('All Ratings', 'echo-article-rating-and-feedback' ); ?></option><?php
			$i = 0;
			while ($i < 6) { ?>
				<option value="<?php echo $i; ?>" <?php echo isset($_REQUEST['eprf_rating_value']) ? selected((int)$_REQUEST['eprf_rating_value'], $i) : ''; ?>><?php echo $i; ?> <?php _e( 'Stars Rating', 'echo-article-rating-and-feedback' ); ?></option> <?php
				$i++;
			}
		}
	}

	/**
	 * When a new article is created then add rating.
	 *
	 * @param $post_ID
	 */
	public function add_default_rating( $post_ID ) {
		if ( ! get_post_meta( $post_ID, 'eprf-article-rating-average', true ) ) {
			EPRF_Utilities::save_postmeta( $post_ID, 'eprf-article-rating-average', 0, true);
			//EPRF_Utilities::save_postmeta( $post_ID, 'eprf-article-rating-like', 0, true);
			//EPRF_Utilities::save_postmeta( $post_ID, 'eprf-article-rating-dislike', 0, true);
		}
	}
}