<?php
/**
 * The template for displaying search results page.
 *
 */

global $post;

$kb_id = ASEA_Utilities::get_eckb_kb_id();
$kb_config = asea_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

/**
 * Display ARTICLE PAGE content
 */
if ( empty( $hide_header_footer ) ) {
	get_header();
}

// retrieve search terms (backward compability)
$search_terms = ASEA_Utilities::get( _x( 'search', 'search query parameter in URL', 'echo-advanced-search' ) );
$search_terms_new = ASEA_Utilities::get( ASEA_Core_Utilities::get_search_query_param( $kb_id ) );
$search_terms = empty($search_terms) ? $search_terms_new : $search_terms;

$page_num = (int) ASEA_Utilities::get( _x('pg', 'abbreviation for the word: page', 'echo-advanced-search') );
$page_num = empty($page_num) ? 1 : $page_num;
$user_category_ids = ASEA_Utilities::get( _x('category', 'echo-advanced-search') );
$user_category_ids = empty($user_category_ids) ? array() : explode('|', $user_category_ids);

// get search results
$search_db = new ASEA_Search_Query();
$search_terms = esc_sql( sanitize_text_field( $search_terms ) );
$found_posts = $search_db->kb_search_articles( $kb_id, $search_terms, $user_category_ids, ASEA_Search_Box_cntrl::get_search_results_page_size( $kb_id ), $page_num );
$search_total = $search_db->articles_total + $search_db->tags_total;

$error_message = '';
if ( $found_posts === false ) {
	$error_message .= __('Error occurred (5321).', 'echo-advanced-search' );
} else 	if ( empty($found_posts) ) {
	$error_message .= ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_no_results_found' );
}
asea_display_search_results( $kb_config, $found_posts, $search_terms, $page_num, $error_message, $search_total, $user_category_ids );

// will we use our template or prepare data for current theme template?
/* if ( ! empty( $epkb_config['templates_for_kb'] ) && $epkb_config['templates_for_kb'] == 'current_theme_templates' && asea_get_query_template() ) {
	asea_display_archive_search_page( $kb_config, $found_posts, $search_terms );
} else {
	asea_display_search_results( $kb_config, $found_posts, $search_terms, $page_num, $error_message, $search_total, $user_category_ids );
} */

if ( empty( $hide_header_footer ) ) {
	get_footer();
}

/**
 * Display search results using archive page template so that we do not need to deal with search widget.
 * @param $kb_config
 * @param $found_posts
 * @param $search_terms
 */
/* TODO if we want archive template of current theme to be used for search results. will it work?
function asea_display_archive_search_page( $kb_config, $found_posts, $search_terms ) {

	// set title & description of the page
	global $asea_archive_title;
	global $asea_archive_descr;
	$asea_archive_title = esc_html( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_results_msg' ) ) . ' ' . $search_terms;
	$asea_archive_descr = esc_html( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_more_results_found' )  . ' (' . count($found_posts) . ')' );

	add_filter( 'get_the_archive_title', function($title) {
		global $asea_archive_title;
		return $asea_archive_title;
	} );

	add_filter( 'get_the_archive_description', function($descr) {
		global $asea_archive_descr;
		return $asea_archive_descr;
	} );

	// set posts
	$ids = array();
	if ( $found_posts ) {
		foreach ($found_posts as $found_post) {
			$ids[] = $found_post->ID;
		}

		$args = array(
				'post_type' => 'any',
				'post__in' => $ids,
				'posts_per_page' => -1
		);

	} else {
		$args = array(
				'post__in' => array(0)
		);
	}

	query_posts($args); // change the main query

	require_once( asea_get_query_template() );
}

function asea_get_query_template() {
	$result = get_query_template( 'archive', array('archive.php','index.php') );
	return empty($result) ? '' : $result;
}
*/

/**
 * Display search results
 *
 * @param $kb_config
 * @param $found_posts
 * @param $search_terms
 * @param $page_num
 * @param $error_message
 * @param $search_total
 * @param $user_category_ids
 */
function asea_display_search_results( $kb_config, $found_posts, $search_terms, $page_num, $error_message, $search_total, $user_category_ids ) {

	// Add theme name to Div for specific targeting
	$active_theme = ASEA_Utilities::get_wp_option( 'stylesheet', 'unknown' );	?>

	<section id="asea-search-results-container" class="<?php echo 'eckb_sp_' . $active_theme; ?>">
		<div class="asea-search-results-inner asea-search-results-reset asea-search-results-defaults asea-search-results-style-1">
			<header class="asea-search-results-header">
				<div class="asea-search-results-title">
					<h3><?php echo esc_html( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_results_msg' ) ) . ' ' . $search_terms; ?></h3>
				</div>
				<div class="asea-search-results-description">
					<p><?php echo esc_html( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_more_results_found' ) . ' (' . $search_total . ')' ); ?></p>
				</div>
			</header>			<?php

			// if no results found then display it
			if ( ! empty($error_message) ) {    ?>
				<div class="asea-no-search-results">
					<span class="asea-no-results-icon epkbfa epkbfa-exclamation-triangle"></span><?php echo $error_message; ?>
				</div>  <?php
			} else {
				asea_display_search_list( $kb_config, $found_posts, $search_terms, $page_num, $search_total, $user_category_ids );
			}			?>

		</div>
	</section>      <?php
}

/**
 * Display list of search results.
 *
 * @param $kb_config
 * @param $found_posts
 * @param $search_terms
 * @param $page_num
 * @param $search_total
 * @param $user_category_ids
 */
function asea_display_search_list( $kb_config, $found_posts, $search_terms, $page_num, $search_total, $user_category_ids ) {     ?>

	<div class="asea-search-results-main">				<?php

	$title_style = '';
	$icon_style  = '';
	if ( ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_*_box_results_style' ) == 'on' ) {

		// get KB Core configuration article setting
		$core_kb_config = ASEA_KB_Core::get_kb_config( $kb_config['id'] );
		if ( ! is_wp_error($core_kb_config) ) {
			$title_style = ASEA_Utilities::get_inline_style( 'color:: article_font_color' , $core_kb_config);
			$icon_style = ASEA_Utilities::get_inline_style( 'color:: article_icon_color' , $core_kb_config);
		}

	}

	// display one line for each search found_posts				?>
	<ul class="asea-search-result-list">					<?php

		foreach( $found_posts as $found_post ) {

			if ( empty($found_post) ) {
				continue;
			}

			// WordPress plugins need this
			//setup_postdata( $found_post ); - inside wp_reset_postdata();
			wp_reset_postdata();
			setup_postdata( $found_post );

			$article_url = get_permalink( $found_post->ID );
			if ( empty($article_url) || is_wp_error( $article_url )) {
				continue;
			}

			// linked articles have their own icon
			$article_title_icon = 'ep_font_icon_document';
			if ( has_filter( 'eckb_single_article_filter' ) ) {
				$article_title_icon = apply_filters( 'eckb_article_icon_filter', $article_title_icon, $found_post->ID );
				$article_title_icon = empty( $article_title_icon ) ? 'epkbfa-file-text-o' : $article_title_icon;
			}

			// linked articles have open in new tab option
			$new_tab = '';
			if ( ASEA_Utilities::is_link_editor_enabled() ) {
				$new_tab = ASEA_Utilities::is_link_editor( $found_post ) ? 'target="_blank"' : '';
			}

			$article_id                 = 'post-'.$found_post->ID;
			$article_title              = esc_html( $found_post->post_title );
			$article_title_style        = $title_style;
			$article_url                = esc_url( $article_url );
			$article_icon               = esc_attr( $article_title_icon );
			$article_icon_style         = $icon_style;
			$article_author             = get_the_author_meta( 'display_name', $found_post->post_author );
			$article_excerpt            = $found_post->post_excerpt;

			$show_created_on_date = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_results_meta_created_on_toggle' ) == 'on';
			$show_author = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_results_meta_author_toggle' ) == 'on';
			$show_categories = ASEA_Core_Utilities::get_search_kb_config( $kb_config, 'advanced_search_results_meta_categories_toggle' ) == 'on';	?>

			<li>
				<article class="asea-article-container" id="<?php echo $article_id; ?>" data-kb-article-id="<?php echo $found_post->ID; ?>">
					<div class="asea-article-header">
						<div class="asea-article-title" >
							<h4><a href="<?php echo $article_url; ?>" <?php echo $new_tab; ?> <?php echo $article_title_style; ?>><?php echo $article_title; ?></a></h4>
							<span class="asea-article-title-icon epkbfa <?php echo $article_icon; ?>" <?php echo $article_icon_style; ?>></span>
						</div><?php
						if ( $show_created_on_date || $show_author || $show_categories ) { ?>
							<div class="asea-article-metadata">
								<ul <?php echo $article_title_style; ?>><?php
									if ( $show_created_on_date && ! empty( $found_post->post_date ) ) {    ?>
										<li class="asea-article-posted-on"> <?php
											printf(
												'<time class="entry-date" datetime="%1$s">%2$s</time>',
												esc_attr( get_the_date( DATE_W3C ) ),
												esc_html( get_the_date() )
											);									 ?>
										</li><?php
									}

									if ( $show_author && ! empty( $article_author ) ) { ?>
										<li class="asea-article-author"><?php echo $article_author; ?></li><?php
									}

									if ( $show_categories ) { ?>
										<li class="asea-article-categories"><?php echo get_the_term_list( $found_post->ID, $found_post->post_type . '_category', '', ',', '' ); ?>
										</li><?php
									} ?>
								</ul>
							</div><?php
						} ?>
					</div>
					<div class="asea-article-body">
						<div class="asea-article-excerpt" <?php echo $article_title_style; ?>><?php echo $article_excerpt; ?></div>
						<div class="asea-article-read-more-text"><a href="<?php echo $article_url; ?>" <?php echo $new_tab; ?> ><?php _e('Read More', 'echo-advanced-search'); ?></a></div>
					</div>
					<div class="asea-article-footer"></div>
				</article>
			</li>					<?php
		}   	?>

	</ul>
	</div>

	<footer class="asea-search-results-footer">				<?php

		$query_params = [ ASEA_Core_Utilities::get_search_query_param( $kb_config['id'] ) => urlencode( $search_terms ) ];

		if ( count( $user_category_ids ) > 0 ) {
			$query_params[ __( 'category', 'echo-advanced-search' ) ] = urlencode( implode( '|', $user_category_ids ) );
		}

		// Previous Page button
		if ( $page_num > 1 ) {

			$prev_query_params = $query_params + [ _x( 'pg', 'abbreviation for the word: page', 'echo-advanced-search' ) => $page_num - 1 ];

			echo '<a class="asea-paginate-button asea-previous-button" href="' . esc_url( add_query_arg( $prev_query_params ) ) . '">' . __( 'Previous Page', 'echo-advanced-search' ) . '</a>';
		}

		// Next Page button
		if ( $search_total > ASEA_Search_Box_cntrl::get_search_results_page_size( $kb_config['id'] ) * $page_num ) {

			$next_query_params = $query_params + [ _x( 'pg', 'abbreviation for the word: page', 'echo-advanced-search' ) => $page_num + 1 ];

			echo '<a class="asea-paginate-button asea-next-button" href="' . esc_url( add_query_arg( $next_query_params ) ) . '">' . __( 'Next Page', 'echo-advanced-search' ) . '</a>';
		} ?>
	</footer>       <?php
}
