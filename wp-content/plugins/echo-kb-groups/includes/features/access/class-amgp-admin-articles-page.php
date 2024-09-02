<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB All Articles, Article Add and Articles Edit pages
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGP_Admin_Articles_Page {

    const KB_CATEGORY_TAG = 'amag-kb-category-id-';

	function __construct() {

		// user switches group
		add_action( 'wp_ajax_amgp_switch_kb_group_on_article_page', array( $this, 'switch_kb_group' ) );
		add_action( 'wp_ajax_nopriv_amgp_switch_kb_group_on_article_page', array( $this, 'user_not_logged_in' ) );

		add_action( 'add_meta_boxes', array( $this, 'show_kb_article_categories_meta_box') );
		add_action( 'admin_footer-edit.php', array( $this, 'show_kb_article_choice_group' ) );
	}

	/**
	 * User is switching KB Group
	 */
	public function switch_kb_group() {

		// run a quick security check
		if ( ! isset( $_REQUEST['_wpnonce_amgp_article_switch_kb'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce_amgp_article_switch_kb'] ) ), '_wpnonce_amgp_article_switch_kb' ) ) {
			AMGP_Utilities::ajax_show_error_die('Security check failed.');
		}

		// retrieve KB ID
		$kb_id = empty($_POST['amgp_kb_id']) ? '' : AMGP_Utilities::sanitize_get_id( $_POST['amgp_kb_id'] );
		if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
			AMGP_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (90)', 'echo-knowledge-base' ) );
		}

		// ensure user has correct permission
		if ( ! current_user_can( 'edit_' . AMGP_Access_Utilities::get_capability_type( $kb_id ) . 's') ) {
			AMGP_Utilities::ajax_show_error_die( 'You do not have permission to view this page.' );  // TODO this does not show on the admin page
		}

		$active_group_id = AMGP_Access_Utilities::get_valid_active_group( $kb_id, AMGP_KB_Role::KB_ROLE_CONTRIBUTOR );
		if ( empty($active_group_id) ) {
			AMGP_Utilities::ajax_show_error_die( esc_html__( 'This page is outdated. Please refresh your browser (91)', 'echo-knowledge-base' ) );
		}

		wp_die( wp_json_encode(  array( 'amag_chosen_kb_group' => $active_group_id ) ) );
	}

	/**
	 * Display KB Group choices on All Articles Page
	 */
	public function show_kb_article_choice_group() {

		if ( AMGP_Access_Utilities::is_admin_or_kb_manager() ) {
			return;
		}

		// add hooks for KB taxonomy
		$kb_id = AMGP_KB_Core::get_current_kb_id();
		if ( empty($kb_id) ) {
			return;
		}

        // display KB Groups form
	    echo'<form>';
			$this->display_group_selection_html( $kb_id, AMGP_KB_Role::KB_ROLE_CONTRIBUTOR, 'article_switch_kb' );
        echo '</form>';
	}

	/**
	 * Display list of KB Groups on All Articles page
	 *
	 * @param $kb_id
	 * @param $lowest_role
	 * @param $suffix
	 */
	public function display_group_selection_html( $kb_id, $lowest_role, $suffix ) {

		echo '>';

		// get user KB Groups to choose from
		$user_groups = AMGP_Access_Utilities::get_user_groups_with_min_role( $kb_id, $lowest_role );
		if ( $user_groups === null ) {
			esc_html_e( 'Problem retrieving KB Groups. Please refresh your page and try again.', 'echo-knowledge-base' ) . ' (E955)';
			return;
		}

		// if user has no KB Groups then do not display anything
		if ( empty($user_groups) ) {
			echo 'You are not part of any KB Group. Join KB Group before proceeding.';
			return;
		}

		// get selected group
		$active_group_id = AMGP_Access_Utilities::get_valid_active_group( $kb_id, $lowest_role );
		if ( empty($active_group_id) ) {
			echo ' No KB Group selected';
			return;
		}

		// create list of groups
		$options = array();
		$chosen_group_id = '';
		foreach( $user_groups as $user_group ) {
			$options[$user_group->kb_group_id] = $user_group->name;
			if ( $active_group_id == $user_group->kb_group_id ) {
				$chosen_group_id = $user_group->kb_group_id;
			}
		}

		// display KB Groups radio buttons
		$html = new AMGP_HTML_Elements();
		$args = array('name' => 'amag_chosen_kb_group_' . $suffix, 'label' => 'KB Groups', 'options' => $options, 'current' => $chosen_group_id );    ?>

		<section class="amgp-admin-list-page-chosen-group-container"> <?php
            $html->radio_buttons_vertical( $args ); ?>
		</section>
		<input type="hidden" id="amag_chosen_kb_group" name="amag_chosen_kb_group" value="<?php esc_attr_e( $active_group_id ); ?>"/>
		<input type="hidden" id="amgp_kb_id" name="amgp_kb_id" value="<?php esc_attr_e( $kb_id ); ?>"/>
		<input type="hidden" id="_wpnonce_amgp_<?php esc_attr_e( $suffix ); ?>" name="_wpnonce_amgp_<?php esc_attr_e( $suffix ); ?>" value="<?php echo wp_create_nonce( "_wpnonce_amgp_" . $suffix ); ?>"/>    <?php
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
		$kb_id = AMGP_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			return;
		}

		add_meta_box( 'amgp-group-article-access', esc_html__( 'Assign Group Categories', 'echo-knowledge-base' ), array( $this, 'amgp_display_kb_categories_list'),
						AMGP_KB_Handler::get_post_type( $kb_id ), 'side', 'default' );
	}

	/**
	 * Display List of KB Categories for this article
	 * @param $post
	 */
	public function amgp_display_kb_categories_list( $post ) {

		// get KB ID
		$kb_id = AMGP_KB_Handler::get_kb_id_from_post_type( $post->post_type );
		if ( is_wp_error($kb_id) ) {
			echo '<div>error occurred (E89)</div>';
			return;
		}

		$is_current_article = AMGP_Access_Utilities::is_current_article( $kb_id, $post->ID );
		if ( $is_current_article === null ) {
			echo '<div>Problem retrieving article. Please refresh your page and try again. (E94)</div>';
			return;
        }

		// which groups can user access
        if ( $is_current_article ) {

	        $handler = AMGP_KB_Core::AMGP_Access_Article( true );
	        if ( $handler->check_post_access( $post, AMGP_KB_Core::AMGP_ARTICLE_EDIT ) !== AMGP_KB_Core::ALLOWED ) {
		        echo '<div>' . esc_html__( 'Problem retrieving KB Groups. Please refresh your page and try again.', 'echo-knowledge-base' ) . ' (E95)' . '</div>';
		        return;
	        }

	        $user_valid_groups_records = $handler->get_authorized_groups();
	        $user_valid_groups = array();
	        foreach( $user_valid_groups_records as $user_valid_groups_record ) {
	        	$user_auth_group = amgp_get_instance()->db_kb_groups->get_group( $kb_id, $user_valid_groups_record->kb_group_id );
	        	if ( empty($user_auth_group) ) {
			        echo '<div>' . esc_html__( 'Problem retrieving KB Groups. Please refresh your page and try again.', 'echo-knowledge-base' ) . ' (E90)' . '</div>';
			        return;
		        }
		        $user_valid_groups[] = $user_auth_group;
	        }

        } else {
	        // get user KB Groups to display
	        $user_valid_groups = AMGP_Access_Utilities::get_user_groups_with_min_role( $kb_id, AMGP_KB_Role::KB_ROLE_CONTRIBUTOR );
	        if ( $user_valid_groups === null ) {
		        echo '<div>' . esc_html__( 'Problem retrieving KB Groups. Please refresh your page and try again.', 'echo-knowledge-base' ) . ' (E96)' . '</div>';
		        return;
	        }
        }

		// if user has no KB Groups nothing to display
		if ( empty($user_valid_groups) ) {
			echo '<div>You are not part of any KB Group.</div>';
			return;
		}

        // we need user
        $user = AMGP_Access_Utilities::get_current_user();
		if ( empty($user) ) {
			echo '<div>You are not logged in.</div>';
			return;
        }

		if ( AMGP_Access_Utilities::is_admin_or_kb_manager( $user ) ) {
			$css_user_type = 'amgp-admin-categories';
		} else {
			$css_user_type = 'amgp-group-categories';
		}        ?>

		<div id="amgp-article-page-inner-container" class="<?php esc_attr_e( $css_user_type ); ?>">			<?php

			if ( AMGP_Access_Utilities::is_admin_or_kb_manager( $user ) ) {
				$this->show_admin_categories( $kb_id, $post->ID );
			} else {
				$this->show_author_editor_categories( $kb_id, $post->ID, $user_valid_groups );
			}           ?>

		</div>		<?php

		wp_nonce_field( 'amgp_group_access_nonce', 'amgp_group_access_nonce' );
	}

	/**
	 * Show categories that Contributor, Author or Editor has access to.
	 *
	 * @param $kb_id
	 * @param $post_id
	 * @param $user_valid_groups
	 *
	 * @return bool|null
	 */
	private function show_author_editor_categories( $kb_id, $post_id, $user_valid_groups ) {		?>

		<!--- Groups Container -->
		<div class="amgp-article-page-group-container">
			<h3>Filter Categories by Group</h3>
			<ul>                    <?php

				// get article categories
				$article_category_ids = AMGP_Utilities::get_article_category_ids_unfiltered( $kb_id, $post_id );
				if ( $article_category_ids === null ) {
					echo '<div>Problem retrieving article. Please refresh your page and try again. (E24)</div>';
					return null;
				}

				$ix = 1;
				$group_to_color_map = array();
				$user_groups_ids = array();
				$found_full_access_group = false;
				$user_group_categories_ids = array();
				foreach( $user_valid_groups as $user_group ) {

					// exclude groups that have no full-access categories
					$group_full_access_categories = amgp_get_instance()->db_access_kb_categories->get_group_categories( $kb_id, $user_group->kb_group_id );
					if ( $group_full_access_categories === null ) {
						echo '<div>Problem retrieving article. Please refresh your page and try again. (E44)</div>';
						return null;
					}
					if ( empty($group_full_access_categories) ) {
						continue;
					}

					$found_full_access_group = true;
					$user_groups_ids[] = $user_group->kb_group_id;
					$group_to_color_map[$user_group->kb_group_id] = array('color_number' => $ix, 'name' => $user_group->name );
					$escaped_group_id = esc_attr($user_group->kb_group_id);

					$isChecked = false;
					$group_categories_ids = AMGP_Access_Utilities::get_group_categories_ids( $kb_id, $escaped_group_id );
					if ( ! empty($group_categories_ids) ) {
						$isChecked = array_intersect($article_category_ids, $group_categories_ids);
						$user_group_categories_ids = array_merge($user_group_categories_ids, $group_categories_ids);
					}       ?>

					<li>
						<input type="checkbox" id="<?php esc_attr_e( $escaped_group_id ); ?>" name="<?php esc_attr_e( $escaped_group_id ); ?>"
						       value="<?php esc_attr_e( $escaped_group_id ); ?>" <?php echo ( $isChecked ? 'checked' : '' ); ?>>
						<label for="<?php esc_attr_e( $escaped_group_id ); ?>"><?php echo $user_group->name; ?></label>
						<div class="amgp-group-icon-placeholder-container">                                <?php
							echo $this->amgp_group_icon_placeholder_editor_version( $user_group->name, $ix++, $user_group->kb_group_id );    ?>
						</div>
					</li>                        <?php
				}

				if ( ! $found_full_access_group ) {
					echo 'None of your Groups has full-access to Categories.';
				}       ?>

			</ul>
		</div>

		<!--- Categories Container -->
		<div class="amgp-article-page-categories-container">
			<h3>Categories</h3>
			<ul>                <?php

				// get complete categories hierarchy
				$category_seq_data = AMGP_Utilities::get_kb_option( $kb_id, AMGP_KB_Core::AMGP_KB_CATEGORIES_SEQ_META, null, true );
				if ( $category_seq_data === null ) {
					echo '<div>error occurred (E97)</div>';
					return null;
				}
				$stored_ids_obj = AMGP_KB_Core::AMGP_Categories_Array( $category_seq_data ); // normalizes the array as well
				$all_categories_ids_levels = $stored_ids_obj->get_all_keys_keep_order();

				// display all relevant categories
				$found_group_with_categories = false;
				$article_seq_data = AMGP_Utilities::get_kb_option( $kb_id, AMGP_KB_Core::AMGP_KB_ARTICLES_SEQ_META, array(), true );
				$last_level_1_no_access_category = null;
				$last_level_2_no_access_category = null;
				$last_level_3_no_access_category = null;
				$groups_ids_with_selected_categories = array();
				foreach( $all_categories_ids_levels as $category_ids_level ) {

					$kb_category_id = key($category_ids_level);
					$category_level = $category_ids_level[$kb_category_id];
					$category_name = empty($article_seq_data[$kb_category_id][0]) ? '' : $article_seq_data[$kb_category_id][0];
					$kb_category_name = esc_html( $category_name );
					$kb_category_id_tag = self::KB_CATEGORY_TAG . esc_attr( $kb_category_id );

					if ( $category_level == 1 ) {
						$last_level_1_no_access_category = $category_name;
						$last_level_2_no_access_category = null;
						$last_level_3_no_access_category = null;
					} else if ( $category_level == 2 ) {
						$last_level_2_no_access_category = $category_name;
						$last_level_3_no_access_category = null;
					} else if ( $category_level == 3 ) {
						$last_level_3_no_access_category = $category_name;
					}

					// get group ids that have access to this category
					$category_group_ids = amgp_get_instance()->db_access_kb_categories->get_category_group_ids( $kb_id, $kb_category_id );
					if ( $category_group_ids === null ) {
						echo '<div>Problem retrieving article. Please refresh your page and try again. (E20)</div>';
						return null;
					}

					// skip if this category has no groups
					if ( empty($category_group_ids) ) {
						continue;
					}

					// no groups that user has access to do not display them
					$common_groups_ids = array_intersect( $category_group_ids, $user_groups_ids);
					if ( empty($common_groups_ids) ) {
						continue;
					}

					$last_level_1_no_access_category = $category_level == 1 ? null : $last_level_1_no_access_category;
					$last_level_2_no_access_category = $category_level == 2 ? null : $last_level_2_no_access_category;
					$last_level_3_no_access_category = $category_level == 3 ? null : $last_level_3_no_access_category;

					if ( $category_level >= 2 && ! empty($last_level_1_no_access_category) ) {  ?>
						<li class="amgp-group-parent-placeholder">
							<?php echo str_repeat("&nbsp;&nbsp;&nbsp;", 1); ?>
							<span class="amgp-parent-icon-placeholder fa fa-caret-square-o-down"></span>
							<span class="amgp-parent-name"><?php esc_html_e( $last_level_1_no_access_category, 'group_access' ); ?></span>
						</li>    <?php
						$last_level_1_no_access_category = null;
					}

					if ( $category_level == 3 && ! empty($last_level_2_no_access_category) ) {  ?>
						<li class="amgp-group-parent-placeholder ">
							<?php echo str_repeat("&nbsp;&nbsp;&nbsp;", 2); ?>
							<span class="amgp-parent-icon-placeholder fa fa-caret-square-o-down"></span>
							<span class="amgp-parent-name"><?php esc_html_e( $last_level_2_no_access_category, 'group_access' ); ?></span>
						</li>    <?php
						$last_level_2_no_access_category = null;
					}

					// collect group icons and ids
					$category_groups_icons_escaped = '';
					$group_ids = '';
					foreach( $common_groups_ids as $common_groups_id ) {
						if ( empty($group_to_color_map[$common_groups_id]) ) {
							continue;
						}

						$user_group = $group_to_color_map[$common_groups_id];
						$category_groups_icons_escaped .= $this->amgp_group_icon_placeholder_editor_version( $user_group['name'], $user_group['color_number'], $common_groups_id );
						$group_ids .= ( empty($group_ids) ? '' : ',' ) . $common_groups_id;
					}

					$isChecked = in_array( $kb_category_id, $article_category_ids );
					if ( $isChecked ) {
						foreach( $common_groups_ids as $common_groups_id ) {
							$groups_ids_with_selected_categories[$common_groups_id] = $common_groups_id;
						}
					}

					$found_group_with_categories = true;
					$escaped_tag = esc_attr($kb_category_id_tag); ?>

					<li data-amgp-group-id="<?php esc_attr_e( $group_ids ); ?>">    <?php
						echo str_repeat("&nbsp;&nbsp;&nbsp;", $category_level); ?>
						<input type="checkbox" id="<?php esc_attr_e( $escaped_tag ); ?>" name="<?php esc_attr_e( $escaped_tag ); ?>"
						       value="<?php esc_attr_e( $escaped_tag ); ?>" <?php echo ( $isChecked ? 'checked' : '' ); ?>>
						<label for="<?php esc_attr_e( $escaped_tag ); ?>"><?php esc_html_e( $kb_category_name, 'group_access' ); ?></label>
						<div class="amgp-group-icon-placeholder-container"><?php echo $category_groups_icons_escaped; ?></div>
					</li>                    <?php
				}

				// if there are categories that the article belongs to but no group owns then show it
				$all_kb_full_access_categories = amgp_get_instance()->db_access_kb_categories->get_kb_categories( $kb_id );
				if ( $all_kb_full_access_categories === null ) {
					echo '<div>Problem retrieving article. Please refresh your page and try again. (E434)</div>';
					return null;
				}

				$all_kb_full_access_categories_ids = array();
				foreach( $all_kb_full_access_categories as $all_kb_category ) {
					$all_kb_full_access_categories_ids[] = (int)$all_kb_category->kb_category_id;
				}

				$read_only_category_ids_obj = amgp_get_instance()->db_access_read_only_categories->get_all_read_only_kb_category_ids( $kb_id );
				if ( $read_only_category_ids_obj === null ) {
					echo '<div>Problem retrieving read-only categories. Please refresh your page and try again. (D45)</div>';
					return null;
				}

				$read_only_category_ids = array();
				foreach ( $read_only_category_ids_obj as $kb_category_id_obj ) {
					$read_only_category_ids[] = $kb_category_id_obj->kb_category_id;
				}

				$categories_with_no_groups_ids = array_diff($article_category_ids, $all_kb_full_access_categories_ids);
				$categories_with_no_groups_ids = array_diff($categories_with_no_groups_ids, $read_only_category_ids);
				if ( ! empty($categories_with_no_groups_ids) ) {
					foreach( $categories_with_no_groups_ids as $categories_with_no_groups_id ) {
						$category_name = empty($article_seq_data[$categories_with_no_groups_id][0]) ? "<unknown>" : $article_seq_data[$categories_with_no_groups_id][0];
						$kb_category_name = esc_html( $category_name );     ?>
						<li style="display:block">
							<input type="checkbox" name="<?php esc_attr_e( $kb_category_name ); ?>" checked  onclick="return false;" readonly/>
							<label for="<?php esc_attr_e( $kb_category_name ); ?>"><?php esc_html_e( $kb_category_name, 'group_access' ); ?></label>
						</li>   <?php
					}
				}

				if ( ! $found_group_with_categories ) {
					echo '<div>Your KB Group has no categories.</div>';
				}       ?>

			</ul>
		</div>  <?php

		return $found_group_with_categories;
	}

	/**
	 * Show all categories and their assignment to groups.
	 *
	 * @param $kb_id
	 * @param $post_id
	 *
	 * @return bool|null
	 */
	private function show_admin_categories( $kb_id, $post_id ) {

		// get all groups
		$user_valid_groups = amgp_get_instance()->db_kb_groups->get_groups( $kb_id );
		if ( $user_valid_groups === null ) {
			echo '<div>Problem retrieving article. Please refresh your page and try again. (E14)</div>';
			return null;
		}

		$i18_category_access_link_escaped = '<a style="padding-left: 10px; font-weight: normal;" href="' . admin_url('edit.php?post_type=' . AMGP_KB_Handler::KB_POST_TYPE_PREFIX . '1&page=amag-access-mgr') . '" target="_blank">' .
		                                esc_html__( 'Edit Categories Access', 'echo-knowledge-base' ) . '</a>';		 ?>

		<!--- Groups Container -->
		<div class="amgp-article-page-group-container">
			<h3><?php echo esc_html__( 'Groups', 'echo-knowledge-base' ) . $i18_category_access_link_escaped;  ?></h3>
			<ul>    <?php

				$ix = 1;
				$group_to_color_map = array();
				$found_full_access_group = false;
				$user_group_categories_ids = array();
				foreach( $user_valid_groups as $user_group ) {

					$found_full_access_group = true;
					$group_to_color_map[$user_group->kb_group_id] = array('color_number' => $ix, 'name' => $user_group->name );
					$escaped_group_id = esc_attr($user_group->kb_group_id);

					$group_categories_ids = AMGP_Access_Utilities::get_group_categories_ids( $kb_id, $escaped_group_id );
					if ( ! empty($group_categories_ids) ) {
						$user_group_categories_ids = array_merge($user_group_categories_ids, $group_categories_ids);
					}       ?>

					<li>
						<label for="<?php esc_attr_e( $escaped_group_id ); ?>"><?php esc_html_e( $user_group->name, 'group_access' ); ?></label>
						<div class="amgp-group-icon-placeholder-container">                                <?php
							echo $this->amgp_group_icon_placeholder( $user_group->name, ' amgp-group-color', $user_group->kb_group_id );    ?>
						</div>
					</li>                        <?php
				}

				if ( ! $found_full_access_group ) {
					echo 'None of your Groups has full-access to Categories.';
				}       ?>

			</ul>
		</div>

		<!--- Categories Container -->
		<div class="amgp-article-page-categories-container">
			<h3>Categories</h3>
			<ul>                <?php

				// get complete categories hierarchy
				$category_seq_data = AMGP_Utilities::get_kb_option( $kb_id, AMGP_KB_Core::AMGP_KB_CATEGORIES_SEQ_META, null, true );
				if ( $category_seq_data === null ) {
					echo '<div>error occurred (E97)</div>';
					return null;
				}
				$stored_ids_obj = AMGP_KB_Core::AMGP_Categories_Array( $category_seq_data ); // normalizes the array as well
				$all_categories_ids_levels = $stored_ids_obj->get_all_keys_keep_order();

				// get article categories
				$article_category_ids = AMGP_Utilities::get_article_category_ids_unfiltered( $kb_id, $post_id );
				if ( $article_category_ids === null ) {
					echo '<div>Problem retrieving article. Please refresh your page and try again. (E24)</div>';
					return null;
				}

				// display all relevant categories
				$article_seq_data = AMGP_Utilities::get_kb_option( $kb_id, AMGP_KB_Core::AMGP_KB_ARTICLES_SEQ_META, array(), true );
				$groups_ids_with_selected_categories = array();
				foreach( $all_categories_ids_levels as $category_ids_level ) {

					$kb_category_id = key($category_ids_level);

					// get group ids that have access to this category
					$category_group_ids = amgp_get_instance()->db_access_kb_categories->get_category_group_ids( $kb_id, $kb_category_id );
					if ( $category_group_ids === null ) {
						echo '<div>Problem retrieving article. Please refresh your page and try again. (E20)</div>';
						return null;
					}

					$read_only_groups = amgp_get_instance()->db_access_read_only_categories->get_read_only_category_records( $kb_id, $kb_category_id );
					if ( $read_only_groups === null ) {
						echo '<div>Problem retrieving groups. Please refresh your page and try again. (E25)</div>';
						return null;
					}

					$read_only_group_ids = AMGP_Access_Utilities::get_group_ids( $read_only_groups );
					$category_group_ids = array_merge($category_group_ids, $read_only_group_ids);

					// collect group icons and ids
					$category_groups_icons_escaped = '';
					$group_ids = '';
					foreach( $category_group_ids as $common_groups_id ) {

						if ( empty($group_to_color_map[$common_groups_id]) ) {
							continue;
						}

						// what level of access the group has to this category
						$group_categories_ids = AMGP_Access_Utilities::get_group_categories_ids( $kb_id, $common_groups_id );
						if ( $group_categories_ids === null ) {
							echo '<div>Problem retrieving categories. Please refresh your page and try again. (E23)</div>';
							return null;
						}
						$group_read_only_categories_ids = amgp_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $kb_id, $common_groups_id );
						if ( $group_read_only_categories_ids === null ) {
							echo '<div>Problem retrieving categories. Please refresh your page and try again. (E27)</div>';
							return null;
						}

						$access_class = '';
						if ( in_array($kb_category_id, $group_read_only_categories_ids) ) {
							$access_class = 'amgp-group-icon-placeholder-read-only';
						} else if ( in_array($kb_category_id, $group_categories_ids) ) {
							$access_class = 'amgp-group-icon-placeholder-full-access';
						}

						$user_group = $group_to_color_map[$common_groups_id];
						$category_groups_icons_escaped .= $this->amgp_group_icon_placeholder( $user_group['name'], $access_class, $common_groups_id );
						$group_ids .= ( empty($group_ids) ? '' : ',' ) . $common_groups_id;
					}

					$isChecked = in_array( $kb_category_id, $article_category_ids );
					foreach( $category_group_ids as $common_groups_id ) {
						$groups_ids_with_selected_categories[$common_groups_id] = $common_groups_id;
					}

					$kb_category_id_tag = self::KB_CATEGORY_TAG . esc_attr( $kb_category_id );
					$escaped_tag = esc_attr($kb_category_id_tag);
					$category_name = empty($article_seq_data[$kb_category_id][0]) ? '' : $article_seq_data[$kb_category_id][0];
					$esc_kb_category_name = esc_html( $category_name );
					$category_level = $category_ids_level[$kb_category_id];					?>

					<li data-amgp-group-id="<?php esc_attr_e( $group_ids ); ?>">    <?php
						echo str_repeat("&nbsp;&nbsp;&nbsp;", $category_level); ?>
						<input type="checkbox" id="<?php echo $escaped_tag; ?>" name="<?php echo $escaped_tag; ?>"
						       value="<?php echo $escaped_tag; ?>" <?php echo ( $isChecked ? 'checked' : '' ); ?>>
						<label for="<?php echo $escaped_tag; ?>"><?php esc_html_e( $esc_kb_category_name, 'group_access' ); ?></label>
						<div class="amgp-group-icon-placeholder-container"><?php echo $category_groups_icons_escaped; ?></div>
					</li>                    <?php

				}       ?>

			</ul>
		</div>  <?php

		return true;
	}

	private function amgp_group_icon_placeholder( $name, $access_class, $id ) {
        return '<span data-amgp-group-icon-id="' . esc_attr($id) . '" class="amgp-group-icon-placeholder ' . esc_attr($access_class) . '">' . esc_html( substr($name, 0, 2) ) . '</span>';
	}

	private function amgp_group_icon_placeholder_editor_version( $name, $color_number, $id ) {
		return '<span data-amgp-group-icon-id="' . esc_attr($id) . '" class="amgp-group-icon-placeholder amgp-group-color-' . esc_attr($color_number) . '">' . esc_html( substr($name, 0, 2) ) . '</span>';
	}

	public function user_not_logged_in() {
		AMGP_Utilities::ajax_show_error_die( '<p>' . esc_html__( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', esc_html__( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}