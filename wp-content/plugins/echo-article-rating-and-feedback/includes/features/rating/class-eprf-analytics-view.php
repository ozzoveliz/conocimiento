<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display rating analytics
 *
 */
class EPRF_Analytics_View {

    public function __construct() {

	    // Add custom views to Analytics admin page
	    add_filter( 'eckb_admin_analytics_page_views', array( $this, 'rating_analytics_view' ), 10, 2 );

	    add_action( 'eckb_add_container_classes', array( $this, 'add_top_container_class' ) );

	    // TODO add_action( 'wp_ajax_eprf_handle_rating_analytics', array( $this, 'handle_rating_analytics' ) );
	    // TODO add_action( 'wp_ajax_nopriv_eprf_handle_rating_analytics', array( EPRF_Utilities, 'user_not_logged_in' ) );
    }

	/**
	 * Add configuration array for Rating Data view to Analytics admin page
	 *
	 * @param $views_config
	 * @param $kb_config
	 *
	 * @return array
	 */
	public function rating_analytics_view( $views_config, $kb_config ) {

		return array_merge(
			$views_config,
			array(

				// View: Search Data
				array(

					// Shared
					'list_key' => 'rating-data',

					// Top Panel Item
					'label_text' => __( 'Rating Data', 'echo-article-rating-and-feedback' ),
					'icon_class' => 'epkbfa epkbfa-thumbs-up',

					// Boxes List
					'boxes_list' => array(

						// Box: Search Data
						array(
							'class' => 'eprf-admin__boxes-list__box__search-data__rating-data',
							'html' => $this->get_rating_analytics_box_html( $kb_config['id'] ),
						),
					),
				),
			)
		);
	}

	/**
	 * Called by KB Core to display Advanced Rating analytics content.
	 *
	 * @param $kb_id
	 *
	 * @return false|string
	 */
	public function get_rating_analytics_box_html( $kb_id ) {

		ob_start();

		echo '<input type="hidden" id="_wpnonce_eprf_rating_analytics" name="_wpnonce_eprf_rating_analytics" value="' .
			wp_create_nonce( "_wpnonce_eprf_ajax_action" ) . '"/>';

		// MOST RATED ARTICLES

		$db_handler = new EPRF_Rating_DB();
		$least_rated_articles_list = $db_handler->get_most_frequently_rated_articles( $kb_id, '2000-01-01 00:00:00', '2100-01-01 00:00:00', 100 );
		if ( is_wp_error( $least_rated_articles_list ) ) {
			echo EPRF_Utilities::report_generic_error( 12, $least_rated_articles_list );
			return ob_get_clean();
		}

		$most_rated_articles_data = array();
		foreach( $least_rated_articles_list as $most_rated_article ) {

			$post = EPRF_Core_Utilities::get_kb_post_secure( $most_rated_article->post_id );
			if ( empty($post) || $post->post_status != 'publish' ) {
				continue;
			}

			$post_title = empty($post->post_title) ? '<unknown>' : $post->post_title;
			$link = get_permalink( $post->ID );
			$link = empty($link) || is_wp_error( $link ) ? '' : $link;

			$most_rated_articles_data[] = array( '<a href="' . esc_url( $link ) . '" target="_blank">' . $post_title . '</a>', $most_rated_article->times );
		}


		// LEAST RATED ARTICLES

		$db_handler = new EPRF_Rating_DB();
		$least_rated_articles_list = $db_handler->get_least_frequently_rated_articles( $kb_id, '2000-01-01 00:00:00', '2100-01-01 00:00:00', 100 );
		if ( is_wp_error( $least_rated_articles_list ) ) {
			echo EPRF_Utilities::report_generic_error( 13, $least_rated_articles_list );
			return ob_get_clean();
		}

		$leat_rated_articles_data = array();
		foreach( $least_rated_articles_list as $least_rated_article ) {

			$post = EPRF_Core_Utilities::get_kb_post_secure( $least_rated_article->post_id );
			$post_title = empty($post->post_title) ? '<not found>' : $post->post_title;

			if ( empty($post->post_title) ) {
				continue;
			}

			if ( $post ) {
				$link = get_permalink( $post->ID );
				$link = empty($link) || is_wp_error( $link ) ? '' : $link;
			} else {
				$link = '';
			}

			$leat_rated_articles_data[] = array( '<a href="' . esc_url( $link ) . '" target="_blank">' . $post_title . '</a>', $least_rated_article->times );
		}


		// TOTAL RATING COUNT
		$number_of_rated_articles = $db_handler->get_number_of_votes( $kb_id, '2000-01-01 00:00:00', '2100-01-01 00:00:00' );
		if ( is_wp_error( $number_of_rated_articles ) ) {
			echo EPRF_Utilities::report_generic_error( 14, $least_rated_articles_list );
			return ob_get_clean();
		}
		$stats_data['total_ratinges'] = array( 'Number of Votes', $number_of_rated_articles );

		// TOTAL NO RESULTS RATING COUNT
		// TODO FUTURE all articles count - all articles with rating count
		// $number_of_rated_articles = $db_handler->get_number_of_articles_without_rating( $kb_id, '2000-01-01 00:00:00', '2100-01-01 00:00:00' );
		//	$stats_data['total_no_results_ratinges'] = array( 'Total Articles without Rating', $number_of_rated_articles );

		// TODO FUTURE
		// ARTICLES BY RATING
		$most_helpful = $db_handler->get_most_helpfull( $kb_id, 100);
		$least_helpful = $db_handler->get_least_helpfull( $kb_id, 100);
		$least_rated = $db_handler->get_least_rated( $kb_id, 100);		?>

		<div class="eckb-config-content" id="eprf-rating-data-content">

			<?php // $this->display_rating_date_range(); ?>

			<?php $this->pie_chart_rating_data_box( 'Most Frequently Rated Articles', $most_rated_articles_data, 'eprf-popular-ratinges-data', 'No articles were rated.' ); ?>
			<?php $this->pie_chart_rating_data_box( 'Least Frequently Rated Articles', $leat_rated_articles_data, 'eprf-no-result-popular-ratinges-data', 'No articles were rated.' ); ?>

			<?php $this->statistics_data_box( 'Overall Statistics', $stats_data, 'statistics-ratinges-data' ); ?>

			<?php //$this->show_rating_table( $most_helpful, __('Most Helpful Articles', 'echo-article-rating-and-feedback' ), 'r1' ); ?>
			<?php //$this->show_rating_table( $least_helpful, __('Least Helpful Articles', 'echo-article-rating-and-feedback' ), 'r2' ); ?>
			<?php //$this->show_rating_table( $least_rated, __('Least Rated Articles', 'echo-article-rating-and-feedback' ), 'r3' ); ?>

		</div>    <?php

		return ob_get_clean();
	}

	/**
	 * Displays a Pie Chart Box with a list on the left and a pie chart on the right.
	 * The Chart is created using Chart.js and called in from our admin-plugins.js file then targets the container ID.
	 *
	 * @param  string $title Top Title of the container box.
	 * @param  array $data Multi-dimensional array containing a list of Words and their counts.
	 * @param  string $id The id of the container and chart id. JS is used to target it to create the chart.
	 * @param string $empty_message
	 */
	private function pie_chart_rating_data_box( $title, $data, $id, $empty_message='' ) {   ?>

		<section class="eprf-pie-chart-container" id="<?php echo $id; ?>">
			<!-- Header ------------------->
			<div class="eprf-pie-chart-header">
				<h4><?php echo $title; ?></h4>
			</div>

			<!-- Body ------------------->
			<div class="eprf-pie-chart-body">
				<div class="eprf-pie-chart-left-col">
					<ul class="eprf-pie-data-list">			<?php
						$item_count = 0;
						if ( empty( $data ) ) {
							echo $empty_message;
						} else {
							foreach ( $data as $word ) {    ?>
								<li class="<?php echo ++$item_count <= 10 ? 'eprf-first-10' : 'eprf-after-10'; ?>">
									<span class="eprf-circle epkbfa epkbfa-circle"></span>
									<span class="eprf-pie-chart-word"><?php echo stripslashes( $word[0] ); ?></span>
									<span class="eprf-pie-chart-count"><?php echo esc_html( $word[1] ); ?></span>
								</li>                <?php
							}
						}		?>
					</ul> <?php

					// More button
					if ( $item_count > 10 ) {   ?>
						<a class="eprf-pie-chart__more-button epkb-primary-btn">
							<span class="eprf-pie-chart__more-button__more-text"><?php esc_html_e( 'More', 'echo-article-rating-and-feedback' ); ?></span>
							<span class="eprf-pie-chart__more-button__less-text epkb-hidden"><?php esc_html_e( 'Less', 'echo-article-rating-and-feedback' ); ?></span>
						</a>    <?php
					}   ?>
				</div>
				<div class="eprf-pie-chart-right-col">
					<div id="eprf-pie-chart" style="height: 225px">
						<canvas id="<?php echo $id; ?>-chart"></canvas>
					</div>
				</div>
			</div>
		</section>	<?php
	}

	/**
	 * Displays overall statistics in numbers.
	 *
	 * @param  string   $title  Top Title of the container box.
	 * @param  array    $stats_data   Multidimensional array containing a list of Words and their counts.
	 * @param  string   $id     The id of the container.
	 */
	private function statistics_data_box( $title, $stats_data, $id ) {      ?>
		<section class="eprf-statistics-container" id="<?php echo $id; ?>">
			<!-- Header ------------------->
			<div class="eprf-statistics-header">
				<h4><?php echo $title; ?></h4>
				<i class="eprf-statistics-cog epkbfa epkbfa-cog" aria-hidden="true"></i>
			</div>
			<!-- Body ------------------->
			<div class="eprf-statistics-body">

				<ul class="eprf-statistics-list">	<?php
					foreach( $stats_data as $type => $data ) {     ?>
						<li>
							<span class="eprf-statistics-word"><?php echo $data[0]; ?></span>
							<span class="eprf-statistics-count"><?php echo $data[1]; ?></span>
						</li>					<?php
					}   ?>
				</ul>
			</div>
		</section>	<?php
	}

	// TOOD FUTURE
	/* private function display_rating_date_range() {  ?>
		<div id="reportrange">
            <i class="epkbfa epkbfa-calendar"></i>
			<div class="report-date report-from">
				<input type="date" id="reportDateFrom">
			</div>
			<div class="report-date-divider">-</div>
			<div class="report-date report-to">
				<input type="date" id="reportDateTo"	>
			</div>
			<button class="button button-primary" id="eprfUpdateReports"><?php _e('Search', 'echo-article-rating-and-feedback' ); ?></button>
			
		</div>  <?php
	} */

	// TODO FUTURE
	// TODO Rename all CSS Classes.
	/**
	 * Show rating data table
	 *
	 * @param $data
	 * @param $title
	 * @param $name
	 */
	/* public static function show_rating_table( $data, $title, $name ) {

		if ( $data ) { ?>
			<div class="rating-table-wrap">
				<h4><?php echo $title; ?></h4>
				<div class="report-radio">
					<label>
						<input type="radio" value="10" name=<?php echo $name; ?> checked="checked">
						<span><?php _e('Top-10', 'echo-article-rating-and-feedback' ); ?></span>
					</label>
					<label>
						<input type="radio" value="100" name=<?php echo $name; ?>>
						<span><?php _e('Top-100', 'echo-article-rating-and-feedback' ); ?></span>
					</label>
				</div>
				<table class="rating-table">
					<tr>
						<th><?php _e('Title', 'echo-article-rating-and-feedback' ); ?></th>
						<th><?php _e('Average', 'echo-article-rating-and-feedback' ); ?></th>
						<th><?php _e('Votes', 'echo-article-rating-and-feedback' ); ?></th>
					</tr> <?php 
					$i = 0;
					foreach ($data as $item) { ?>
						<tr <?php echo ($i++ > 9) ? 'style="display:none;"' : ''; ?>>
							<td class="rating-table-name"><?php echo $item['title']; ?></td>
							<td class="rating-table-average"><?php echo $item['average'] ? $item['average'] : '-'; ?></td>
							<td class="rating-table-count"><?php echo $item['count']; ?></td>
						</tr> <?php 
					} ?>
				</table> 
			</div> <?php
		}
	} */

	// TODO FUTURE
	/**
	 * AJAX: Return rating report based on entered dates.
	 */
	/* public function handle_rating_analytics() {

		// verify that request is authentic
		if ( empty( $_REQUEST['_wpnonce_eprf_ajax_action'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_eprf_ajax_action'], '_wpnonce_eprf_ajax_action' ) ) {
			EPRF_Utilities::ajax_show_error_die( __( 'First refresh your page', 'echo-article-rating-and-feedback' ) );
		}

		// ensure user has correct permissions
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			EPRF_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-article-rating-and-feedback' ) );
		}

		$kb_id = EPRF_Utilities::post( 'kb_id', EPRF_KB_Config_DB::DEFAULT_KB_ID );
		$start_date = EPRF_Utilities::post('start_date', date_i18n('Y-m-d'));
		$end_date = EPRF_Utilities::post( 'end_date', date_i18n('Y-m-d') );
		$filtered_rating_data = $this->display_rating_results_table( $kb_id, $start_date, $end_date );

		wp_die( wp_json_encode( array( 'output' => $filtered_rating_data, $type='success') ) );
	} */

	/**
	 * Adds eprf-analytics-container string to top of Analytics page.
	 *
	 * Description: So that we can keep the prefix separate in CSS. This allows Dave to use EPRF only
	 * for the top container without affecting core.
	 *
	 */
	public function add_top_container_class(){
		echo ' eprf-analytics-container';
	}

	// TODO FUTURE
	/**
	 * Display analytics for ratinges.
	 *
	 * @param $kb_id
	 * @return string
	 */
	/* private function display_rating_analytics( $kb_id ) {

		//echo $this->display_rating_results_table( $kb_id, '9999-01-01', '9999-01-01' );

		//echo '<input type="hidden" id="_wpnonce_eprf_rating_analytics" name="_wpnonce_eprf_rating_analytics" value="' .
		  //   wp_create_nonce( "_wpnonce_eprf_rating_analytics" ) . '"/>';

		$analytics_start_date = EPRF_Utilities::get_wp_option( 'eprf_analytics_start_date', 'unknown' );
		echo 'Rating analytics recorded since ' . $analytics_start_date . "<br/>";
		echo 'Current storage limit: up to ' . EPRF_Rating_Logging::MAX_NOF_LOGS_STORED . ' rating records stored.' . "<br/>";
		echo 'Current rating drop-down limit: up to ' . EPRF_Rating_Box_cntrl::get_rating_results_list_size( $kb_id ) . ' rating records shown.' . "<br/>";
	} */

	// TODO FUTURE
	/* private function display_rating_results_table( $kb_id, $from_date, $to_date ) {

		$rating_data = self::get_data_range( $kb_id, $from_date, $to_date );

		$output = '
			<table id="eprf_datatable" class="display" style="width:100%;">
		        <thead>
		            <tr>
		                <th>Date</th>
		                <th>Rating Text</th>
		                <th>Number of Articles Found</th>
		                <th>Articles Found</th>
		            </tr>
		        </thead>
				<tbody>';

		$kb_config = EPRF_KB_Core::get_kb_config( $kb_id );
		if ( is_wp_error($kb_config) ) {
			$output .= 'error occurred (432)';
			return $output;
		}

		$main_page_url = EPRF_KB_Handler::get_first_kb_main_page_url( $kb_config );
		foreach( $rating_data as $rating_attempt ) {
			$rating_date = empty($rating_attempt['date'] ) ? 'N/A' : $rating_attempt['date'];
			$user_input = empty($rating_attempt['user_input']) ? '<unknown>' : stripslashes($rating_attempt['user_input']);
			$filtered_user_input = EPRF_Rating_Box_DB::filter_user_input( $user_input);
			$results_count = empty($rating_attempt['count']) ? 'N/A' : $rating_attempt['count'];

			$output .= '
				<tr>
	                <td>' . $rating_date . '</td>
	                <td>' . $user_input . '</td>
	                <td>' . $results_count . '</td>
	                <td><a href="' .  esc_url( $main_page_url . '?' . _x('rating', 'keyword used when rating article', 'echo-article-rating-and-feedback') . '=' . urlencode($filtered_user_input) ) . '" target="_blank">Rating Results</a></td>
	            </tr>';
		}

		$output .= '</tbody>
		    </table>';

		return $output;
	} */

	// TODO FUTURE
	/**
	 * Get data from given range.
	 *
	 * @param $kb_id
	 * @param $start_date
	 * @param $end_date
	 *
	 * @return array
	 */
	/* public static function get_data_range( $kb_id, $start_date, $end_date) {

		$rating_data = EPRF_Rating_Logging::get_logs( $kb_id );
		$filtered_rating_data = array();
		foreach( $rating_data as $rating_attempt ) {
			$attempt_date = EPRF_Utilities::get_formatted_datetime_string($rating_attempt['date'], 'Y-m-d');

			if ( $attempt_date >= $start_date && $attempt_date <= $end_date ) {
				$filtered_rating_data[] = $rating_attempt;
			}
		}

		return $filtered_rating_data;
	} */
}