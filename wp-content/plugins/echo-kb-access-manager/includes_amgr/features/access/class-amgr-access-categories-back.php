<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle KB Categories Add and Edit page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Access_Categories_Back {

	public function __construct( $create_category=false ) {

		// delete KB Category
		add_action( 'pre_delete_term', array( $this, 'delete_kb_category' ), 9999, 2 );

		// edit KB Category
		add_action( 'edit_terms', array( $this, 'edit_kb_category' ), 9999, 2 );

		add_action( 'admin_init', array( $this, 'filter_term' ) );

		$current_kb_id = EPKB_KB_Handler::get_current_kb_id();
		$category_taxonomy = EPKB_KB_Handler::get_category_taxonomy_name( $current_kb_id );
		add_action( $category_taxonomy . '_pre_edit_form', array( $this, 'filter_term_edit' ), 1 );

		//add_filter( 'tag_row_actions', array( $this, 'filter_term2' ), 9999, 2 );   // handled by WP capabilities

		$all_kb_ids = epkb_get_instance()->kb_config_obj->get_kb_ids();
		foreach( $all_kb_ids as $kb_id ) {
			add_filter( 'pre_update_option_epkb_post_type_' . $kb_id . '_category_children', array(	$this, 'update_kb_child_category_option' ), 9999, 3 );
		}

		add_filter( 'get_the_terms', array( $this, 'filter_kb_categories' ), 9999, 3 );
	//	add_filter('list_terms_exclusions', 'yoursite_list_terms_exclusions', 10, 2);

		// add hooks for KB taxonomy
		if ( empty( $current_kb_id ) ) {
			return;
		}

		// AMGR Core does not use groups
		if ( AMGR_WP_ROLES::use_kb_groups() ) {
			add_action( 'after-epkb_post_type_' . $current_kb_id . '_category-table', array( $this, 'list_orphan_categories' ) );
		}

		if ( ! $create_category ) {
			return;
		}

		// Create KB Category - don't need to check
		//$kb_taxonomy_name = EPKB_KB_Handler::get_category_taxonomy_name( $current_kb_id);
		//add_action( 'create_' . $kb_taxonomy_name, array( $this, 'create_kb_category' ), 9999 );
	}

	/**
	 * Filter KB Categories.
	 *
	 * @param $terms
	 * @param $post_id
	 * @param $taxonomy
	 *
	 * @return mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter_kb_categories( $terms, $post_id, $taxonomy ) {

		// only filter KB Categories
		if ( ! EPKB_KB_Handler::is_kb_category_taxonomy( $taxonomy ) || empty( $terms ) || ! is_array($terms) ) {
			return $terms;
		}

		$handler = new AMGR_Admin_Categories_Page();
		return $handler->get_kb_terms( $terms, array($taxonomy), array(), array() );
	}

	/**
	 * Verify a single capability required for an action.
	 *
	 * @param $kb_id
	 * @param $required_capability
	 * @param array $args
	 * @param WP_User $user
	 * @return bool
	 */
	public static function does_have_capability( $kb_id, $required_capability, array $args, WP_User $user ) {

		$highest_user_role = AMGR_Access_Utilities::get_user_highest_role_from_all_groups( $kb_id, $user );
		if ( empty($highest_user_role) ) {
			return false;
		}

		// for manage categories/tags capability (e.g. menu item) we don't have a specific term ID
		if ( count($args) < 3 && empty($args[2]) ) {
			return AMGR_KB_Roles::has_user_kb_capability( $kb_id, $highest_user_role, $required_capability);
		}

		// this is capability for KB Category/Tag so we need term ID
		$term_id = empty($args[2]) ? 0 : $args[2];
		$term_id = empty($term_id) ? EPKB_Utilities::get( 'tag_ID' ) : $term_id;
		if ( empty($term_id) ) {
			return false;
		}

		// get term to check
		$term = get_term($term_id);
		if ( empty( $term ) || is_wp_error( $term ) ) {
			return false;
		}

		// verify user access to the category
		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, null, null, $required_capability );
		if ( $can_access === true ) {
			return true;
		}

		return false;
	}

	/**
	 * User created a new KB Category
	 *
	 * @param string $term_id
	 */
	public function create_kb_category( $term_id='' ) {

		$taxonomy = EPKB_Utilities::post( 'taxonomy' );
		$parent_id = EPKB_Utilities::post( 'parent_id' );

		if ( empty($term_id) ) {
			AMGR_Logging::add_log("Could not create KB Category. No term found.", $term_id );
			die();  // TODO forward
		}

		$term = get_term($term_id);
		if ( empty( $term ) || is_wp_error( $term ) ) {
			AMGR_Logging::add_log("Could not create KB Category. No term found.", $term_id );
			die();  // TODO forward
		}

		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, $taxonomy, $parent_id, AMGR_Access_Category::AMGR_CATEGORY_CREATE );
		if ( $can_access === true ) {
			return;
		}
		if ( $can_access === null  ) {
			AMGR_Logging::add_log("Error occurred. Could not create KB Category. taxonomy: " . $taxonomy, $term_id );
			die();  // TODO forward
		}

		AMGR_Logging::add_log("User has no access to the KB category. taxonomy: " . $taxonomy, $term_id );

		wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
	}

	/**
	 * User edits KB Category. Ensure any category parent is valid.
	 *
	 * @param string $term_id
	 * @param $taxonomy
	 *
	 * @noinspection PhpUnusedParameterInspection*/
	public function edit_kb_category( $term_id , $taxonomy ) {

		if ( ! EPKB_KB_Handler::is_kb_category_taxonomy( $taxonomy ) ) {
			return;
		}

		$taxonomy = EPKB_Utilities::post( 'taxonomy' );
		$parent_id = EPKB_Utilities::post( 'parent_id' );

		if ( empty( $term_id ) ) {
			AMGR_Logging::add_log( "Could not edit KB Category. No term found. (01)", $term_id );
			die();  // TODO forward
		}

		$term = get_term( $term_id );
		if ( empty( $term ) || is_wp_error( $term ) ) {
			AMGR_Logging::add_log( "Could not edit KB Category. No term found. (02)", $term_id );
			die();  // TODO forward
		}

		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, $taxonomy, $parent_id, AMGR_Access_Category::AMGR_CATEGORY_EDIT );
		if ( $can_access === true ) {
			return;
		}

		if ( $can_access === null  ) {
			AMGR_Logging::add_log("Error occurred. Could not edit KB Category. taxonomy: " . $taxonomy, $term_id );
			die();  // TODO forward
		}

		AMGR_Logging::add_log("User has no access to the KB category. taxonomy: " . $taxonomy, $term_id );

		wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
	}

	/**
	 * User is about to delete KB Category. Remove category from AMGR Access Category table before WP deletes the actual category.
	 * Log error and die if there is an issue (don't see how to show the error to the user)
	 *
	 * @param string $term_id
	 * @param $taxonomy
	 *
	 * @noinspection PhpUnusedParameterInspection*/
	public function delete_kb_category( $term_id , $taxonomy ) {

		$taxonomy = EPKB_Utilities::post( 'taxonomy' );
		$parent_id = EPKB_Utilities::post( 'parent_id' );

		// ignore non-KB categories
		if ( ! EPKB_KB_Handler::is_kb_category_taxonomy( $taxonomy ) ) {
			return;
		}

		if ( empty($term_id) ) {
			AMGR_Logging::add_log("Could not delete KB Category. No term found.", $term_id );
			die();  // TODO forward
		}

		$term = get_term($term_id);
		if ( empty( $term ) || is_wp_error( $term ) ) {
			AMGR_Logging::add_log("Could not delete KB Category. No term found.", $term_id );
			die();  // TODO forward
		}

		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, $taxonomy, $parent_id, AMGR_Access_Category::AMGR_CATEGORY_DELETE );
		if ( $can_access === null  ) {
			AMGR_Logging::add_log("Error occurred. Could not delete KB Category. taxonomy: " . $taxonomy, $term_id );
			die();  // TODO forward
		}

		if ( ! $can_access ) {
			AMGR_Logging::add_log("User has no access to the KB category. taxonomy: " . $taxonomy, $term_id );
			wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
		}

		// get KB ID
		$kb_id = EPKB_KB_Handler::get_kb_id_from_category_taxonomy_name( $taxonomy );
		if ( empty($kb_id) || is_wp_error($kb_id) ) {
			AMGR_Logging::add_log("Found empty or invalid kb id", $kb_id );
			wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
		}

		$result1 = epkb_get_instance()->db_access_kb_categories->delete_category( $kb_id, $term->term_id );
		$result2 = epkb_get_instance()->db_access_read_only_categories->delete_category( $kb_id, $term->term_id );

		if ( $result1 == false || $result2 == false ) {
			wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
		}
	}

	/**
	 * Ensure that _children WP option is updated with all valid KB categories children.
	 *
	 * @param $value
	 * @param $old_value
	 * @param $option
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection*/
	public function update_kb_child_category_option( $value, $old_value, $option ) {

		$kb_id = str_replace(EPKB_KB_Handler::KB_POST_TYPE_PREFIX, '', $option);
		$kb_id = str_replace('_category_children', '', $kb_id);
		if ( ! EPKB_Utilities::is_positive_int($kb_id) ) {
			return $value;
		}

		$kb_categories = EPKB_Core_Utilities::get_kb_categories_unfiltered( $kb_id, 'term_id' );
		if ( empty($kb_categories) ) {
			return $value;
		}

		$children = array();
		foreach ( $kb_categories as $term ) {
			if ( $term->parent > 0 )
				$children[$term->parent][] = $term->term_id;
		}

		return $children;
	}

	/**
	 * Can user Edit Term.
	 *
	 * @param $term
	 */
	public function filter_term_edit( $term ) {

		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, null, null, AMGR_Access_Category::AMGR_CATEGORY_EDIT );
		if ( $can_access === true ) {
			return;
		}

		wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
	}

	/**
	 * Verify that current user has access to the term
	 */
	public function filter_term() {

		$term_id = EPKB_Utilities::get( 'tag_id' );
		$taxonomy = EPKB_Utilities::get( 'taxonomy' );

		if ( empty( $term_id)  || empty( $taxonomy ) || ! EPKB_KB_Handler::is_kb_category_taxonomy( $taxonomy ) ) {
			return;
		}

		$term = get_term( $term_id );
		if ( empty( $term ) || is_wp_error( $term ) ) {
			AMGR_Logging::add_log("Could not create KB Category. No term found.", $term_id );
			die();  // TODO forward
		}

		$handler = new AMGR_Access_Category();
		$can_access = $handler->check_access( $term, null, null, AMGR_Access_Category::AMGR_CATEGORY_EDIT );
		if ( $can_access === true ) {
			return;
		}

		wp_die( AMGR_Access_Reject::display_denied_message(), 403 );
	}

	/**
	 * For KB Categories that do not have editable parents just show children
	 *
	 * @param $taxonomy
	 */
	public function list_orphan_categories( $taxonomy ) {

		// get all terms
		$terms = get_terms( array(
								'taxonomy'      => $taxonomy,
								'hide_empty'    => false ) );
		if ( is_wp_error( $terms ) || empty( $terms ) || ! is_array( $terms ) ) {
			return;
		}

		$first_orphan_term = true;
		$kb_id = EPKB_KB_Handler::get_kb_id_from_category_taxonomy_name( $taxonomy );
		if ( is_wp_error($kb_id) ) {
			return;
		}

		$term_index = array();
		$kb_category_ids = array();
		foreach($terms as $term) {
			$kb_category_ids[] = $term->term_id;
			$term_index[$term->term_id] = $term;
		}

		// find terms that are "orphans"
		$orphan_terms = array();
		foreach( $terms as $term ) {

			if ( $this->are_all_parents_accessible( $kb_category_ids, $term_index, $term ) ) {
				continue;
			}

			$orphan_terms[] = $term;
		}

		if ( empty($orphan_terms) ) {
			return;
		}   ?>

		<div class="amgr-orphan-list-categories">

			<div class="amgr-olc-title"><h2><?php esc_html_e( 'Access to Child Categories', 'echo-knowledge-base' ); ?></h2></div>
			<div class="amgr-olc-description"><p><?php esc_html_e( 'You have access to the following child categories, even though you do not have access to their parent categories. ' .
						'For example, in the category hierarchy Category A (parent) -> Category B (child), you would only have access to Category B.', 'echo-knowledge-base' ); ?><p></div>

			<div class="amgr-olc-heading">
				<div class="amgr-olc-col"><span><?php esc_html_e( 'Name', 'echo-knowledge-base' ); ?></span></div>
				<div class="amgr-olc-col"><span><?php esc_html_e( 'Slug', 'echo-knowledge-base' ); ?></span></div>
			</div>

			<div class="amgr-olc-list"> <?php
				foreach( $orphan_terms as $term ) {

					if ( $first_orphan_term ) {
						$first_orphan_term = false;
					}
					$edit_link = esc_url( get_edit_term_link( $term->term_id, $taxonomy, EPKB_KB_Handler::get_post_type( $kb_id ) ) );

					echo '
						<div class="amgr-category">
							<div class="amgr-category__name_action">
								<div class="amgr-category__name">' . esc_html( $term->name ) . '</div>
								<div class="amgr-category__action"><a href="' . esc_url( $edit_link ) . '" target="_blank">' . esc_html__( 'Edit', 'echo-knowledge-base' ) . '</a></div>
							</div>
							<div class="amgr-category__slug">' . esc_html( $term->slug ) . '</div>
						</div>';
				}   ?>

			</div>

		</div>		<?php
	}

	private function are_all_parents_accessible( $kb_category_ids, $term_index, $term ) {

		// if no parent then we are at the top
		if ( empty($term->parent) ) {
			return true;
		}

		// get parent
		/** @var WP_Term $parent */
		$parent = empty($term_index[$term->parent]) ? null : $term_index[$term->parent];
		if ( empty($parent) ) {
			return false;
		}

		// if no parent then we are at the top
		if ( empty($parent->parent) ) {
			return true;
		}

		// is parent accessible?
		if ( ! in_array($parent->term_id, $kb_category_ids) ) {
			return false;
		}

		// get parent 2
		/** @var WP_Term $parent2 */
		$parent2 = empty($term_index[$parent->parent]) ? null : $term_index[$parent->parent];
		if ( empty($parent2) ) {
			return false;
		}

		// if no parent then we are at the top
		if ( empty($parent2->parent) ) {
			return true;
		}

		// is parent accessible?
		if ( ! in_array($parent2->term_id, $kb_category_ids) ) {
			return false;
		}

		// get parent 3
		/** @var WP_Term $parent3 */
		$parent3 = empty($term_index[$parent2->parent]) ? null : $term_index[$parent2->parent];
		if ( empty($parent3) ) {
			return false;
		}

		// if no parent then we are at the top
		if ( empty($parent3->parent) ) {
			return true;
		}

		// is parent accessible?
		if ( ! in_array($parent3->term_id, $kb_category_ids) ) {
			return false;
		}

		return true;
	}
}

