<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display search analytics
 *
 */
class ASEA_Analytics_View {

	private $filters = [];
	private $filters_options = [];

	const TIMEFRAME_INTERVALS = array(
		'last_30_days'  => [ 'today - 30 days', 'now' ],
		'this_week'     => [ 'monday this week', 'now' ],
		'last_week'     => [ 'monday this week - 1 week', 'monday this week' ],
		'this_month'    => [ 'first day of this month', 'now' ],
		'last_month'    => [ 'first day of this month - 1 month', 'first day of this month' ],
		'last_3_months' => [ 'first day of this month - 2 month', 'now' ],
		'this_year'     => [ 'first day of january this year', 'now' ]
	);

    public function __construct() {

	    // Add custom views to Analytics admin page
	    add_filter( 'eckb_admin_analytics_page_views', array( $this, 'search_analytics_view' ), 10, 2 );

	    add_action( 'eckb_add_container_classes', array( $this, 'add_top_container_class' ) );

	    // TODO add_action( 'wp_ajax_asea_handle_search_analytics', array( $this, 'handle_search_analytics' ) );
	    // TODO add_action( 'wp_ajax_nopriv_asea_handle_search_analytics', array( 'ASEA_Utilities', 'user_not_logged_in' ) );
    }

	/**
	 * Set post filters data
	 */
	private function set_filters_post_data() {
		$timeframe = ASEA_Utilities::post( 'asea_admin_analytics_filters_timeframe', 'last_3_months' );
		$this->filters['timeframe'] = $timeframe;
		$this->filters['timeframe_from'] = date( 'Y-m-d 00:00:00', strtotime( self::TIMEFRAME_INTERVALS[$timeframe][0] ) );
		$this->filters['timeframe_to'] = date( 'Y-m-d 23:59:59', strtotime( self::TIMEFRAME_INTERVALS[$timeframe][1] ) );
	}

	/**
	 * Set post filters list options
	 */
	private function set_filters_options() {
		$this->filters_options['timeframe'] = array(
			'last_30_days'  => __( 'Last 30 days', 'echo-advanced-search' ),
			'this_week'     => __( 'This Week', 'echo-advanced-search' ),
			'last_week'     => __( 'Last Week', 'echo-advanced-search' ),
			'this_month'    => __( 'This Month', 'echo-advanced-search' ),
			'last_month'    => __( 'Last Month', 'echo-advanced-search' ),
			'last_3_months' => __( 'Last 3 Months', 'echo-advanced-search' ),
			'this_year'     => __( 'This Year', 'echo-advanced-search' )
		);
	}

	/**
	 * Displays a Pie Chart Box with a list on the left and a pie chart on the right.
	 * The Chart is created using Chart.js and called in from our admin-plugins.js file then targets the container ID.
	 *
	 * @param  string $title Top Title of the container box.
	 * @param  array $data Multidimensional array containing a list of Words and their counts.
	 * @param  string $id The id of the container and chart id. JS is used to target it to create the chart.
	 * @param string $empty_message
	 */
	private function pie_chart_search_data_box( $title, $data, $id, $empty_message='' ) {   ?>

		<section class="asea-pie-chart-container" id="<?php echo $id; ?>">
			<!-- Header ------------------->
			<div class="asea-pie-chart-header">
				<h4><?php echo $title; ?></h4>
			</div>

			<!-- Body ------------------->
			<div class="asea-pie-chart-body">
				<div class="asea-pie-chart-left-col">
					<ul class="asea-pie-data-list">			<?php
						$item_count = 0;
                        if ( empty( $data ) ) {
							echo $empty_message;
						} else {
							foreach ( $data as $word ) {    ?>
								<li class="<?php echo ++$item_count <= 10 ? 'asea-first-10' : 'asea-after-10'; ?>">
									<span class="asea-circle epkbfa epkbfa-circle"></span>
									<span class="asea-pie-chart-word"><?php echo stripslashes( $word[0] ); ?></span>
									<span class="asea-pie-chart-count"><?php echo esc_html( $word[1] ); ?></span>
								</li>                <?php
							}
						}?>
					</ul> <?php

					// More button
                    if ( $item_count > 10 ) {   ?>
                        <a class="asea-pie-chart__more-button epkb-primary-btn">
                            <span class="asea-pie-chart__more-button__more-text"><?php esc_html_e( 'More', 'echo-advanced-search' ); ?></span>
                            <span class="asea-pie-chart__more-button__less-text epkb-hidden"><?php esc_html_e( 'Less', 'echo-advanced-search' ); ?></span>
                        </a>    <?php
                    }   ?>
				</div>
				<div class="asea-pie-chart-right-col">
					<div id="asea-pie-chart" style="height: 225px">
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
		<section class="asea-statistics-container" id="<?php echo $id; ?>">
			<!-- Header ------------------->
			<div class="asea-statistics-header">
				<h4><?php echo $title; ?></h4>
				<i class="asea-statistics-cog epkbfa epkbfa-cog" aria-hidden="true"></i>
			</div>
			<!-- Body ------------------->
			<div class="asea-statistics-body">

				<ul class="asea-statistics-list">	<?php
					foreach( $stats_data as $type => $data ) {     ?>
						<li>
							<span class="asea-statistics-word"><?php echo $data[0]; ?></span>
							<span class="asea-statistics-count"><?php echo $data[1]; ?></span>
						</li>					<?php
					}   ?>
				</ul>
			</div>
		</section>	<?php
	}

	private function bold_used_search_keywords( $user_input, $search_keywords ) {

		if ( empty($user_input) || empty($search_keywords) ) {
			return 'error';
		}

		$words = explode(' ', $user_input);
		$search_keywords = explode(' ', $search_keywords);
		$output = '';
		$first_word = true;
		foreach( $words as $word ) {
			$output .= ($first_word ? '' : ' ') . ( in_array($word, $search_keywords) ? '<strong>' . esc_html($word) . '</strong>' : esc_html($word) );
			$first_word = false;
		}

		return $output;
	}

	/**
	 * Show a button which will delete all search analytics
	 * @param $kb_id
	 */
	private function display_search_reset_button( $kb_id ) { ?>
		<section class="asea-reset-container">

			<div id="asea-reset-analytics-button" class="asea-reset-body">
				<input type="hidden" id="_wpnonce_asea_search_analytics" name="_wpnonce_asea_search_analytics" value="<?php echo wp_create_nonce( "_wpnonce_asea_search_analytics" ); ?>"/>
				<input type="hidden" id="asea_reset_analytics_kb_id" name="asea_reset_analytics_kb_id" value="<?php echo $kb_id; ?>"/>
				<a href="" class="asea-reset-analytics asea-error-btn">
					<?php _e( 'Delete Search Analytics', 'echo-advanced-search' ); ?>
					<span class="epkbfa epkbfa-undo" aria-hidden="true"></span>
				</a>
			</div>  <?php

			// Confirm Deletion of Search Data Dialog Box.
			ASEA_HTML_Forms::dialog_confirm_action( array(
				'id'              => 'asea-reset-search-data',
				'title'           => __( 'Delete Search Data', 'echo-advanced-search' ),
				'body'            => __( 'Are you sure you want to delete all search data? Backup your database if in doubt.', 'echo-advanced-search' ),
				'accept_label'    => __( 'Delete Data', 'echo-advanced-search' ),
				'accept_type'     => 'error',
				'show_cancel_btn' => 'yes',
			) );			?>

		</section> <?php
	}


	/********************************************************************************
	 *
	 *                            SEARCH TABLE TODO
	 *
	 ********************************************************************************/

	/**
	 * AJAX: Return search report based on entered dates.
	 */
	public function handle_search_analytics() {

		// verify that request is authentic
		if ( empty( $_REQUEST['_wpnonce_asea_search_analytics'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_asea_search_analytics'], '_wpnonce_asea_search_analytics' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'First refresh your page', 'echo-advanced-search' ) );
		}

		// ensure user has correct permissions
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-advanced-search' ) );
		}

		$kb_id = ASEA_Utilities::post( 'kb_id', ASEA_KB_Config_DB::DEFAULT_KB_ID );
		$start_date = ASEA_Utilities::post('start_date', date_i18n('Y-m-d'));
		$end_date = ASEA_Utilities::post( 'end_date', date_i18n('Y-m-d') );
		$filtered_search_data = $this->display_search_results_table( $kb_id, $start_date, $end_date );

		wp_die( wp_json_encode( array( 'output' => $filtered_search_data, $type='success') ) );
	}

	/**
	 * Adds asea-analytics-container string to top of Analytics page.
	 *
	 * Description: So that we can keep the prefix separate in CSS. This allows Dave to use ASEA only
	 * for the top container without affecting core.
	 *
	 */
	public function add_top_container_class() {
		echo 'asea-analytics-container';
	}

	private function display_search_results_table( $kb_id, $from_date, $to_date ) {

		$search_data = self::get_data_range( $kb_id, $from_date, $to_date );

		$output = '
			<table id="asea_datatable" class="display" style="width:100%;">
		        <thead>
		            <tr>
		                <th>Date</th>
		                <th>Search Text</th>
		                <th>Number of Articles Found</th>
		                <th>Articles Found</th>
		            </tr>
		        </thead>
				<tbody>';

		$kb_config = ASEA_KB_Core::get_kb_config( $kb_id );
		if ( is_wp_error($kb_config) ) {
			$output .= 'error occurred (432)';
			return $output;
		}

		$main_page_url = ASEA_KB_Handler::get_first_kb_main_page_url( $kb_config );
		foreach( $search_data as $search_attempt ) {
			$search_date = empty($search_attempt['date'] ) ? 'N/A' : $search_attempt['date'];
			$user_input = empty($search_attempt['user_input']) ? '<unknown>' : stripslashes($search_attempt['user_input']);
			$filtered_user_input = ASEA_Search_Box_cntrl::filter_user_input( $user_input);
			$results_count = empty($search_attempt['count']) ? 'N/A' : $search_attempt['count'];

			$output .= '
				<tr>
	                <td>' . $search_date . '</td>
	                <td>' . $user_input . '</td>
	                <td>' . $results_count . '</td>
	                <td><a href="' .  esc_url( $main_page_url . '?' . ASEA_Core_Utilities::get_search_query_param( $kb_id ) . '=' . urlencode($filtered_user_input) ) . '" target="_blank">Search Results</a></td>
	            </tr>';
		}

		$output .= '</tbody>
		    </table>';

		return $output;
	}

	/**
	 * Get data from given range.
	 *
	 * @param $kb_id
	 * @param $start_date
	 * @param $end_date
	 *
	 * @return array
	 */
	private static function get_data_range( $kb_id, $start_date, $end_date) {

		$search_data = array(); // TODO ASEA_Search_Box_DB::get_logs( $kb_id );
		$filtered_search_data = array();
		foreach( $search_data as $search_attempt ) {
			$attempt_date = ASEA_Utilities::get_formatted_datetime_string($search_attempt['date'], 'Y-m-d');

			if ( $attempt_date >= $start_date && $attempt_date <= $end_date ) {
				$filtered_search_data[] = $search_attempt;
			}
		}

		return $filtered_search_data;
	}


	/********************************************************************************
	 *
	 *                           ADMIN PAGE VIEW
	 *
	 ********************************************************************************/

	/**
	 * Get all data by source
	 * @param $kb_id
	 * @param string $where_condition
	 * @return null|array
	 */
	private function get_all_data_by_source( $kb_id, $where_condition='' ) {

		$date_from = empty( $this->filters['timeframe_from'] ) ? '2000-01-01 00:00:00' : $this->filters['timeframe_from'];
		$date_to = empty( $this->filters['timeframe_to'] ) ? '2100-01-01 23:59:59' : $this->filters['timeframe_to'];

		$db_handler = new ASEA_Search_DB();
		$most_popular_list = $db_handler->get_most_popular_searches( $kb_id, $date_from, $date_to, 100 , $where_condition );
		if ( $most_popular_list === null ) {
			echo ASEA_Utilities::report_generic_error( 12 );
			return null;
		}

		$most_popular_searches = array();
		foreach( $most_popular_list as $most_popular_search ) {
			$search_formatted = $this->bold_used_search_keywords( $most_popular_search->user_input, $most_popular_search->search_keywords );
			$most_popular_searches[] = array( $search_formatted, $most_popular_search->times );
		}

		// NO RESULTS SEARCHES
		$no_results_list= $db_handler->get_no_results_searches( $kb_id, $date_from, $date_to, 100, $where_condition );
		if ( $no_results_list === null ) {
			echo ASEA_Utilities::report_generic_error( 13 );
			return null;
		}

		$no_results_searches = array();
		foreach( $no_results_list as $no_results_search ) {
			$search_formatted = $this->bold_used_search_keywords( $no_results_search->user_input, $no_results_search->search_keywords );
			$no_results_searches[] = array( $search_formatted, $no_results_search->times );
		}

		// TOTAL SEARCH COUNT
		$total_search_count = $db_handler->get_search_count( $kb_id, $date_from, $date_to, $where_condition );
		$stats_data['total_searches'] = array( 'Total Searches', $total_search_count );

		// TOTAL NO RESULTS SEARCH COUNT
		$total_search_count = $db_handler->get_no_result_search_count( $kb_id, $date_from, $date_to, $where_condition );
		$stats_data['total_no_results_searches'] = array( 'Total No Results Searches', $total_search_count );

		$result['most_popular_searches'] = $most_popular_searches;
		$result['no_results_searches'] = $no_results_searches;
		$result['stats_data'] = $stats_data;

		return $result;
	}

	/**
	 * Get configuration array for secondary views in Analytics admin page
	 *
	 * @param $kb_id
	 *
	 * @return array[]
	 */
	private function get_secondary_tabs_config( $kb_id ) {

		$secondary_views_config = array(

			// Secondary View: All Data
			array(
				// Shared
				'list_key' => 'all-data',
				'active' => true,

				// Secondary Panel Item
				'label_text' => __( 'All Data', 'echo-advanced-search' ),


				'list_top_actions_html' => $this->get_top_actions_timeframe_filter_html(),

				// Secondary Boxes List
				'boxes_list' => array(

					// Box: Search Data
					array(
						'class' => 'asea-admin__boxes-list__box__search-data__all-data',
						'html' => $this->get_all_data_box_html( $kb_id ),
					),
				),
			)
		);

		$db_handler = new ASEA_Search_DB();
		$source_data = $db_handler->get_distinct_column_value_by_name( $kb_id, 'source' );
		if ( is_wp_error( $source_data ) ) {
			$source_data = [];
		}

		$source_data_list = array();
		foreach( $source_data as $source_data_item ) {
			$source_data_list[] = $source_data_item->source;
		}
		asort($source_data_list);

		foreach( $source_data_list as $source_data_list_item ) {

			$where_condition = " AND source='" . $source_data_list_item . "'" ;
			$source_data = $this->get_all_data_by_source( $kb_id, $where_condition );
			if ( empty( $source_data ) ) {
				continue;
			}

			ob_start();     ?>

			<div class="asea-search-data-content">     <?php
				$this->pie_chart_search_data_box( 'Popular Searches', $source_data['most_popular_searches'], 'asea-ps-data-'.$source_data_list_item, 'No searches were recorded.' );
				$this->pie_chart_search_data_box( 'No Results Searches', $source_data['no_results_searches'], 'asea-nrps-data-'.$source_data_list_item, 'No empty searches found.' );       ?>
			</div>      <?php

			$view_content = ob_get_clean();

			$secondary_views_config[] = array(

				// Shared
				'list_key' => $source_data_list_item,

				// Secondary Panel Item
				'label_text' => strpos( $source_data_list_item, "search-shortcode-" ) !== false
					? __( 'Search Shortcode #','echo-advanced-search' ) . substr( $source_data_list_item, 17 )
					: ( strpos( $source_data_list_item, 'widgets' ) !== false
						? __( 'Widgets','echo-advanced-search' )
						: __( 'KB Search','echo-advanced-search' )
					),

				'list_top_actions_html' => $this->get_top_actions_timeframe_filter_html(),

				// Secondary Boxes List
				'boxes_list' => array(

					// Box: Search Data
					array(
						'class' => 'asea-admin__boxes-list__box__search-data__kb-search',
						'html' => $view_content,
					),
				),
			);
		}

		return $secondary_views_config;
	}

	/**
	 * Add configuration array for Search Data view to Analytics admin page
	 *
	 * @param $views_config
	 * @param $kb_config
	 *
	 * @return array
	 */
	public function search_analytics_view( $views_config, $kb_config ) {

		// init filters post data
		$this->set_filters_post_data();

		// init filters options
		$this->set_filters_options();

		$db_handler = new ASEA_Search_DB();
		$source_data = $db_handler->get_distinct_column_value_by_name( $kb_config['id'], 'source' );
		if ( is_wp_error( $source_data ) ) {
			$source_data = [];
		}

		$source_data_list = array();
		foreach( $source_data as $source_data_item ) {
			$source_data_list[] = $source_data_item->source;
		}
		asort($source_data_list);

		return array_merge(
			$views_config,
			array(

				// View: Search Data
				array(

					// Shared
					'list_key' => 'search-data',

					// Top Panel Item
					'label_text' => __( 'Search Data', 'echo-advanced-search' ),
					'icon_class' => 'epkbfa epkbfa-search',

					// Secondary Panel Items
					'secondary_tabs' => $this->get_secondary_tabs_config( $kb_config['id'] ),
				),
			)
		);
	}

	/**
	 * Get HTML for All Data box in Analytics admin page
	 *
	 * @param $kb_id
	 *
	 * @return false|string
	 */
	public function get_all_data_box_html( $kb_id ) {

		ob_start();

		$all_data = $this->get_all_data_by_source( $kb_id );
		if ( empty( $all_data ) ) {
			echo ASEA_Utilities::report_generic_error( 22 );
			return ob_get_clean();
		}       ?>

		<div class="asea-search-data-content">
			<?php $this->pie_chart_search_data_box( 'Popular Searches', $all_data['most_popular_searches'], 'asea-popular-searches-data', 'No searches were recorded.' ); ?>
			<?php $this->pie_chart_search_data_box( 'No Results Searches', $all_data['no_results_searches'], 'asea-no-result-popular-searches-data', 'No empty searches found.' ); ?>
			<?php $this->statistics_data_box( 'Overall Statistics', $all_data['stats_data'], 'statistics-searches-data' ); ?>
			<?php $this->display_search_reset_button( $kb_id ); ?>
		</div>		<?php

		return ob_get_clean();
	}

	/**
	 * Show timeframe filter
	 *
	 * @return false|string
	 */
	private function get_top_actions_timeframe_filter_html() {

		ob_start(); ?>
        <div class="asea-admin__list-actions-row--left asea-ap-filters-wrap">
	        <form class="asea-ap-filters" action="" method="POST">  <?php
                // Timeframe Filter
		        ASEA_KB_Core::get_dropdown_element_html(
                    array(
                        'name'          => 'asea_admin_analytics_filters_timeframe',
                        'input_class'   => 'asea_admin_analytics_filters',
                        'value'         => $this->filters['timeframe'],
                        'label'         => __( 'Timeframe', 'echo-advanced-search' ),
                        'options'       => $this->filters_options['timeframe'],
                    )
                );  ?>
            </form>
        </div>  <?php

		return ob_get_clean();
	}
}