<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB All Articles, Article Add and Articles Edit pages
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Admin_Articles_Page {

    const KB_CATEGORY_TAG = 'amag-kb-category-id-';

	function __construct() {

		// with KB Groups and KB Roles the categories box will be different
		if ( ! AMGR_WP_ROLES::use_kb_groups() ) {
			add_action( 'add_meta_boxes', array( $this, 'show_kb_article_categories_meta_box' ) );
		}

		add_filter( 'add_meta_boxes', array( $this, 'remove_default_categories_meta_box' ), 999999 );

		// add hooks for KB taxonomy
		$kb_id = EPKB_KB_Handler::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

		// add custom columns to All Articles
		$post_type = EPKB_KB_Handler::get_post_type( $kb_id );
		add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_column_heading' ), 10 );
		add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'add_column_value' ), 10, 2 );
		add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'add_sortable_columns' ) );
	}

	/**
	 * Display KB Categories for this article
	 */
	public function show_kb_article_categories_meta_box() {
		global $post;

		if ( empty($post) || ! $post instanceof WP_Post ) {
			return;
		}

		// ignore non-KB posts
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			return;
		}

		add_meta_box( 'amgr-group-article-access', __( 'Assign Article Categories', 'echo-knowledge-base' ), array( $this, 'amgr_display_kb_categories_list'),
						EPKB_KB_Handler::get_post_type( $kb_id ), 'side', 'default' );
	}

	/**
	 * Display List of KB Categories for this article
	 * @param $post
	 */
	public function amgr_display_kb_categories_list( $post ) {

		// get KB ID
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			echo '<div>error occurred (E89)</div>';
			return;
		}

		$is_current_article = AMGR_Access_Utilities::is_current_article( $kb_id, $post->ID );
		if ( $is_current_article === null ) {
			echo '<div>Problem retrieving article. Please refresh your page and try again. (E94)</div>';
			return;
        }

		// which groups can user access
        if ( $is_current_article ) {

	        $handler = new AMGR_Access_Article( true );
	        if ( $handler->check_post_access( $post, AMGR_Access_Article::AMGR_ARTICLE_EDIT ) !== AMGR_Access_Article::ALLOWED ) {
		        echo '<div>Problem retrieving KB Groups. Please refresh your page and try again. (E95)</div>';
		        return;
	        }
        }

        // we need user
        $user = AMGR_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			echo '<div>You are not logged in.</div>';
			return;
        }       ?>

		<div id="amgr-article-page-inner-container" class="amgr-admin-categories">			<?php
			$this->show_admin_categories( $kb_id, $post->ID );			           ?>
		</div>		<?php

		wp_nonce_field( 'amgr_group_access_nonce', 'amgr_group_access_nonce' );
	}

	/**
	 * Show all categories and their assignment to groups.
	 *
	 * @param $kb_id
	 * @param $post_id
	 *
	 * @return bool|null
	 */
	private function show_admin_categories( $kb_id, $post_id ) {			 ?>

		<!--- Categories Container -->
		<div class="amgr-article-page-categories-container">
			<h3>Categories</h3>
			<ul>                <?php

				// get complete categories hierarchy
				$category_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, null, true );
				if ( $category_seq_data === null ) {
					echo '<div>' . esc_html__( 'No Categories found. First create categories.', 'echo-knowlegde-base' ) . '</div>';
					return null;
				}
				$stored_ids_obj = new EPKB_Categories_Array( $category_seq_data ); // normalizes the array as well
				$all_categories_ids_levels = $stored_ids_obj->get_all_keys_keep_order();

				// get article categories
				$article_category_ids = AMGR_Access_Utilities::get_article_category_ids_unfiltered( $kb_id, $post_id );
				if ( $article_category_ids === null ) {
					echo '<div>' . esc_html__( 'Problem retrieving article. Please refresh your page and try again. (E24)', 'echo-knowlegde-base' ) . '</div>';
					return null;
				}

				// display all relevant categories
				$article_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, array(), true );
				foreach( $all_categories_ids_levels as $category_ids_level ) {

					$kb_category_id = key($category_ids_level);

					// get group ids that have access to this category
					$category_groups_icons = '';
					if ( AMGR_Access_Utilities::is_category_public( $kb_id, $kb_category_id ) ) {
						$category_groups_icons = $this->amgr_group_icon_placeholder( 'PUB', 'amgr-group-icon-placeholder-read-only', 0 );
					}

					$isChecked = in_array( $kb_category_id, $article_category_ids );
					$kb_category_id_tag = self::KB_CATEGORY_TAG . esc_attr( $kb_category_id );
					$escaped_tag = esc_attr($kb_category_id_tag);
					$category_name = empty($article_seq_data[$kb_category_id][0]) ? '' : $article_seq_data[$kb_category_id][0];
					$esc_kb_category_name = esc_html( $category_name );
					$category_level = $category_ids_level[$kb_category_id];					?>

					<li>    <?php
						echo str_repeat("&nbsp;&nbsp;&nbsp;", $category_level); ?>
						<input type="checkbox" id="<?php echo $escaped_tag; ?>" name="<?php echo $escaped_tag; ?>"
						       value="<?php echo $escaped_tag; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
						<label for="<?php echo $escaped_tag; ?>"><?php _e( $esc_kb_category_name, 'group_access' ); ?></label>
						<div class="amgr-group-icon-placeholder-container"><?php echo $category_groups_icons; ?></div>
					</li>                    <?php

				}       ?>

			</ul>
		</div>  <?php

		return true;
	}

	private function amgr_group_icon_placeholder( $name, $access_class, $id ) {
        return '<span data-amgr-group-icon-id="' . esc_attr($id) . '" class="amgr-group-icon-placeholder ' . esc_attr($access_class) . '">' . esc_html( substr($name, 0, 2) ) . '</span>';
	}

	public function add_column_heading( $columns ) {
		return array_merge( $columns, array(
			'amgr_access_level'	=> __( 'Access', 'echo-knowledge-base' )
		// TODO	'amgr_groups'   	=> __( 'KB Groups', 'echo-knowledge-base' )
		) );
	}

	public function add_column_value( $column_name, $post_id ) {
		If ( $column_name == 'amgr_access_level' ) {

			$kb_id = EPKB_KB_Handler::get_current_kb_id();
			if ( empty($kb_id) ) {
				return;
			}

			$result = AMGR_Access_Utilities::is_article_public( $kb_id, $post_id, true);

			echo $result === null ? 'error' : ( $result ? 'Public' : 'Private' );
		}
	}

	public function add_sortable_columns($columns) {
		$columns['amgr_access_level'] = 'amgr_access_level';
		return $columns;
	}

	/**
	 * Remove default meta box with Categories list
	 */
	public function remove_default_categories_meta_box() {
		global $current_screen;
		$kb_taxonomy_name = EPKB_KB_Handler::get_category_taxonomy_name2();
		remove_meta_box( $kb_taxonomy_name . 'div', $current_screen, 'side' );
	}
}

