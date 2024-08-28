<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display view of KB Articles access controls.
 */
class AMGR_Access_Page_View_Articles {

	private $html;
	private $kb_id;
	private $kb_groups;

	public function __construct( $kb_id ) {
		$this->html = new EPKB_HTML_Elements();
		$this->kb_id = $kb_id;
	}

	/**
	 * Called by AJAX after user makes changes.
	 *
	 * @param $kb_group_id
	 * @return bool
	 */
	public function ajax_update_tab_content( $kb_group_id ) {

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E88)' );
		}

		$kb_groups = epkb_get_instance()->db_kb_groups->get_groups( $this->kb_id );
		if ( $kb_groups === null ) {
			AMGR_Logging::add_log( "Could not get KB Groups", $this->kb_id );
			AMGR_Access_Utilities::output_inline_error_notice( 'Internal Error occurred (a243)' );
			return false;
		}   ?>

		<section class="amag-page-header">
			<h2>Read-only Access to Articles</h2>
			<ul>    <?php
				if ( AMGR_WP_Roles::use_kb_groups() ) { ?>
					<li>Articles for which the group has full access are marked as <span class="amgr-full-access-icon">F</span>.</li>
					<li>Articles with public access are marked as <span class="amgr-public-access-icon">P</span>.</li>  <?php
				}					?>
				<li>Assigning individual articles to groups will not provide that group with Category access. Groups with access to individual articles will, however, see the Category structure that the article is nested in.</li>
			</ul>
		</section>      <?php

		if ( empty($kb_groups) ) {  ?>
            <div class="callout callout_error">
                <h4>No Groups have been created.</h4>
                <p>Create one or more groups before configuring read-only access to articles.</p>
            </div>     <?php
			return true;
		}

		// prepare data
		$this->kb_groups = $kb_groups;  ?>

		<div class="amgr-access-articles-content-container">    <?php
			 $this->display_articles_access( $kb_group_id );    ?>
		</div>  <?php

		return true;
	}

	/**
	 * Show Full KB level access
	 *
	 * @param string $kb_group_id
	 * @return bool
	 */
	private function display_articles_access( $kb_group_id ) {     ?>

        <label>Group:</label>
        <select id="amgr-access-tabs-kb-group-list-articles">
            <option value="0">Choose a Group</option>   <?php
			foreach( $this->kb_groups as $kb_group ) {
				$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group->kb_group_id );
				if ( $is_group_public === null ) {
					AMGR_Logging::add_log( "Could not verify Public Group", $this->kb_id );
					return false;
				}

				if ( $is_group_public || AMGR_WP_Roles::use_kb_groups() ) {
					echo '<option ' . ( $kb_group->kb_group_id == $kb_group_id ? 'selected' : '' ) . ' value="' . esc_attr( $kb_group->kb_group_id ) . '">' . esc_html( $kb_group->name ) . '</option>';
				}
			}                ?>
        </select>   <?php

		if ( empty($kb_group_id) || ! AMGR_Access_Utilities::is_kb_group_id_in_array( $kb_group_id, $this->kb_groups ) ) {     ?>
            <div id="amgr-articles-checkboxes-content"></div>     <?php
			return true;
		}   ?>

        <div id="amgr-read-only-article-access" class="amgr-content-articles">     <?php
            $isSuccess = $this->display_hierarchy_of_articles( $kb_group_id ); ?>
        </div>

        <input type="hidden" id="amgr_kb_group_id_article_access" name="amgr_kb_group_id_article_access" value="<?php echo esc_attr($kb_group_id); ?>" />   <?php

		$this->html->submit_button_v2( __( 'Save', 'echo-knowledge-base' ), 'amgr_save_articles_access_ajax', 'epkb-btn-wrap--plain', '', true, '', 'amag-primary-btn' );

		return $isSuccess;
	}

	/**
	 * Display list of all articles for selected Group.
	 *
	 * @param $kb_group_id
	 * @return bool
	 */
	private function display_hierarchy_of_articles( $kb_group_id ) {

		// get all read-only articles for given group
		$read_only_articles_ids = epkb_get_instance()->db_access_read_only_articles->get_group_read_only_articles_ids( $this->kb_id, $kb_group_id );
		if ( $read_only_articles_ids === null ) {
			return false;
		}

		// get group full-access articles
		$group_categories_ids = AMGR_Access_Utilities::get_group_categories_ids( $this->kb_id, $kb_group_id );
		if ( $group_categories_ids === null ) {
			return false;
		}

		// get all existing articles - ignore if no articles exist yet
		$stored_articles_seq = EPKB_Utilities::get_kb_option( $this->kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, [], true );

		$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			AMGR_Logging::add_log( "Could not verify Public Group", $this->kb_id );
			AMGR_Access_Utilities::output_inline_error_notice( 'Internal Error occurred (133)' );
			return false;
		}

		$article_ids = array();
		$articles_shown_ids = array();
		echo '<ul>';
		foreach( $stored_articles_seq as $category_id => $articles_array ) {

			// if group has access to the category where the article is located then say so
			$group_has_access = in_array($category_id, $group_categories_ids);

			$ix = 0;
			foreach( $articles_array as $article_id => $article_title ) {
				if ( $ix ++ < 2 ) {
					continue;
				}

				$article_ids[] = $article_id;
				$link = get_permalink( $article_id );
				$link = empty($link) || is_wp_error( $link ) ? '' : $link;

				// if group has full access they don't need read-only access
				if ( $group_has_access ) {
					echo '<li>
							<span class="amgr-full-access-icon">F</span> 
							<span>' . esc_html( $article_title ) . '</span>
							<span><a href="'. esc_url( $link ) . '" target="_blank" >link</a></span>
						  </li>';
					continue;
				}

				// if article has public access then group does not need read-only access
				$result = AMGR_Access_Utilities::is_article_public( $this->kb_id, $article_id, true );
				$public_category_indicator = $result === null ? 'error' : ( $result ? '<span class="amgr-public-access-icon">P</span> ' : '' );

				if ( ! empty($public_category_indicator) && ! $is_group_public ) {
					echo '<li>
							<span class="amgr-public-access-icon">P</span> 
							<span>' . esc_html( $article_title ) . '</span>
							<span><a href="' . esc_url( $link ) . '" target="_blank" >link</a></span>
						  </li>';
					continue;
				}

				// each article will be only displayed once
				if ( in_array($article_id, $articles_shown_ids) ) {
					continue;
				} else {
					$articles_shown_ids[] = $article_id;
				}

				// show the article
				$isChecked = in_array($article_id, $read_only_articles_ids);
				$article_title = strlen($article_title) > 50 ? EPKB_Utilities::substr( $article_title, 0, 50 ) . '...' : $article_title;     ?>

                 <li>
                    <span>
                        <input type="checkbox" class="amgr-article-read-only-access"
                               id="<?php echo 'amgr-read-only-article-' . $article_id; ?>"
                               name="<?php echo 'ammgr-read-only-article-' . $article_id; ?>"
                               value="<?php echo $article_id; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>

                        <label for="<?php echo 'amgr-read-only-article-' . $article_id; ?>"><?php echo esc_html( $article_title ); ?></label>
                    </span>
					<span><a href="<?php echo esc_url( $link ); ?>" target="_blank" >link</a></span>
				</li>			       <?php
			}
		}
		echo '</ul>';

		return true;
	}
}