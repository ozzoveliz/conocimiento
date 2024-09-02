<?php
/**
 * Methods that add code that Access Manager needs within core KB to run
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPKB_Access_Manager {

	public static function add_action_display_category_notices( $taxonomy ) {
		add_action( "{$taxonomy}_add_form_fields", array( 'EPKB_Access_Manager', 'display_category_notices' ), 98 );
	}

	// KB Groups can have access to a specific KB only; this complicates access and ordering
	public static function hide_menu_access_control() {
		return AMGR_WP_Roles::use_kb_groups();
	}

	public static function show_debug_user_access() {
		return array(
			'title' => esc_html__( 'Debug User Access', 'echo-knowledge-base' ),
			'description' => esc_html__( 'Enable debug when instructed by the support team.', 'echo-knowledge-base' ),
			'html' => AMGR_Debug_User_Access::display_debug_user_access_box(),
		);
	}

	public static function display_debug_data() {
		return self::display_amgr_debug_data();
	}

	public static function get_logs() {
		return AMGR_Logging::get_logs();
	}

	public static function reset_logs() {
		AMGR_Logging::reset_logs();
	}

	public static function get_kb_id( $current_id ) {
		return empty( $current_id ) ? self::get_first_kb_id() : $current_id;
	}

	public static function get_capability_type( $kb_id ) {
		return AMGR_Access_Utilities::get_capability_type( $kb_id );
	}

	public static function get_categories_capabilities( $capability_type ) {
		return [ 'capabilities'      => [
			'manage_terms' => 'manage_' . $capability_type . '_categories',
			'edit_terms'   => 'edit_' . $capability_type . '_categories',
			'delete_terms' => 'delete_' . $capability_type . '_categories',
			'assign_terms' => 'assign_' . $capability_type . '_categories',
		] ];
	}

	public static function get_tags_capabilities( $capability_type ) {
		return [ 'capabilities'          => [
			'manage_terms' => 'manage_' . $capability_type . '_tags',
			'edit_terms'   => 'edit_' . $capability_type . '_tags',
			'delete_terms' => 'delete_' . $capability_type . '_tags',
			'assign_terms' => 'assign_' . $capability_type . '_tags',
		] ];
	}

	public static function get_cpt_capabilities( $capability_type ) {
		return ['capabilities' => self::get_cpt_capabilities_inner( $capability_type )];
	}

	public static function add_admin_body_class() {
		add_filter( 'admin_body_class', array( 'EPKB_Access_Manager', 'add_admin_body_class_inner') );
	}

	public static function get_count( $articles ) {
		// check access for each post
		$count = 0;
		$handler = new AMGR_Access_Article();
		foreach ( $articles as $article ) {

			// verify user access to the post
			$article_access = $handler->check_post_access( $article, null );
			if ( $article_access === AMGR_Access_Article::ALLOWED ) {
				$count++;
			}
		}

		return $count;
	}

	public static function filter_seq_data( $kb_config, &$category_seq_data, &$articles_seq_data ) {
		// protect Category Archive Page
		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_config['id'], $category_seq_data, $articles_seq_data );
		if ( $kb_groups_set === null || ( empty( $kb_groups_set['categories_seq_data'] ) && empty( $kb_groups_set['articles_seq_data'] ) ) ) {
			$category_seq_data = [];
			$articles_seq_data = [];
		} else {
			$category_seq_data = $kb_groups_set['categories_seq_data'];
			$articles_seq_data = $kb_groups_set['articles_seq_data'];
		}
	}

	public static function setup_data() {
		// setup KB Public Group for the new knowledge base
		$handler = new AMGR_Setup_KB_Groups();
		$handler->setup_amgr_data();
	}

	public static function get_kb_id2() {
		return AMGR_WP_Roles::use_kb_groups() ? self::get_first_kb_id() : EPKB_KB_Config_DB::DEFAULT_KB_ID;
	}

	public static function filter_result( $result ) {
		// check access for each post
		$filtered_posts = array();
		$handler = new AMGR_Access_Article();
		foreach( $result as $post ) {

			// verify user access to the post
			$article_access = $handler->check_post_access( $post, null );
			if ( $article_access === AMGR_Access_Article::ALLOWED ) {
				$filtered_posts[] = $post;
				continue;
			}
		}

		return $filtered_posts;
	}

	public static function search_limit() {
		return 200;
	}

	public static function limit_result( $result ) {
		$max_results_size = 20;
		if ( count( $result ) > $max_results_size ) {
			$result = array_slice( $result, 0, $max_results_size );
		}

		return $result;
	}

	public static function get_seq_data( $kb_id ) {
		$articles_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Articles_Admin::KB_ARTICLES_SEQ_META, array(), true );
		$category_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, array(), true );

		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( empty( $kb_groups_set ) ) {
			return [];
		}

		$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
		$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];

		return [ 'articles_seq_data' => $articles_seq_data, 'category_seq_data' => $category_seq_data ];
	}

	// protect KB Main Page - only KB Manager and administrator can see even empty KB Main Page
	public static function get_seq_data2( $kb_id, &$category_seq_data, &$articles_seq_data) {
		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( $kb_groups_set === null || ( ! AMGR_Access_Utilities::is_admin_or_kb_manager() && empty( $kb_groups_set['categories_seq_data'] ) && empty( $kb_groups_set['articles_seq_data'] ) ) ) {
			echo AMGR_Access_Reject::reject_user_access( $kb_id, '04' );
			return false;
		}

		$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
		$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];

		return true;
	}

	public static function get_seq_data3( $kb_id, &$category_seq_data, &$articles_seq_data ) {
		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( $kb_groups_set === null || ( empty( $kb_groups_set['categories_seq_data'] ) && empty( $kb_groups_set['articles_seq_data'] ) ) ) {
			$category_seq_data = [];
			$articles_seq_data = [];
		} else {
			$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
			$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];
		}
	}

	public static function get_seq_data4( $kb_id, &$category_seq_data, &$articles_seq_data ) {
		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( $kb_groups_set === null || ( ! AMGR_Access_Utilities::is_admin_or_kb_manager() && empty( $kb_groups_set['categories_seq_data'] ) && empty( $kb_groups_set['articles_seq_data'] ) ) ) {
			return false;
		}

		$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
		$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];

		return true;
	}

	public static function get_seq_data5( $kb_id, $articles_seq_data ) {
		$category_seq_data = EPKB_Utilities::get_kb_option( $kb_id, EPKB_Categories_Admin::KB_CATEGORIES_SEQ_META, array(), true );

		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( $kb_groups_set === null || empty( $kb_groups_set['articles_seq_data'] ) ) {
			return null;
		}

		return $kb_groups_set['articles_seq_data'];
	}

	public static function get_seq_data6( $kb_id, &$category_seq_data, &$articles_seq_data, $article_id ) {
		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( empty( $kb_groups_set)  ) {
			return false;
		}

		$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
		$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];

		// if none then return
		$article_ids = AMGR_Access_Utilities::get_articles_ids_from_sequence( $kb_id, $articles_seq_data );
		if ( empty( $article_ids ) || ! in_array( $article_id, $article_ids) ) {
			return false;
		}

		return true;
	}

	public static function get_seq_data7( $kb_id, &$category_seq_data, &$articles_seq_data, $category_empty_msg ) {

		$kb_groups_set = AMGR_Access_Utilities::get_main_page_group_sets( $kb_id, $category_seq_data, $articles_seq_data );
		if ( empty( $kb_groups_set ) ) {
			echo esc_html( $category_empty_msg ) . '</p></main>';
			return [];
		}

		$articles_seq_data = empty( $kb_groups_set['articles_seq_data'] ) ? array() : $kb_groups_set['articles_seq_data'];
		$category_seq_data = empty( $kb_groups_set['categories_seq_data'] ) ? array() : $kb_groups_set['categories_seq_data'];

		global $wp_query;
		$result['initial_posts_per_page'] = empty( $wp_query->query_vars['posts_per_page'] ) ? (int)get_option( 'posts_per_page' ) : $wp_query->query_vars['posts_per_page'];
		$result['$initial_paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		return $result;
	}

	public static function check_access( $kb_id ) {
		if ( ! AMGR_Access_Main_Page_Front::can_user_access_kb_main_page( $kb_id ) ) {
			return [ 'result' => AMGR_Access_Reject::reject_user_access( $kb_id, '05' ) ];
		}

		return [];
	}

	public static function report_on_error() {
		return true;
	}

	public static function plugin_name() {
		return 'echo-kb-access-manager/echo-kb-access-manager.php';
	}

	public static function show_error( $field_spec, $input_value, $result ) {
	}

	public static function limit_query() {
		return true;
	}

	public static function delete_access_data() {
		self::delete_amgr_data();
	}

	public static function multi_site() {
		wp_die('Access Manager cannot be activated on multisite as a network plugin.');
	}

	public static function menu_items( $post_type_name='' ) {
		if ( current_user_can( 'admin_eckb_access_manager_page' ) ) {
			add_submenu_page( 'edit.php?post_type=' . $post_type_name, esc_html__( 'Access Manager - Echo Knowledge Base', 'echo-knowledge-base' ), esc_html__( 'Access Manager', 'echo-knowledge-base' ),
				'admin_eckb_access_manager_page', 'amag-access-mgr', array( new AMGR_Access_Page(), 'display_access_manager_page' ) );
		} else if ( current_user_can( 'admin_eckb_access_crud_users' ) ) {
			add_submenu_page( 'edit.php?post_type=' . $post_type_name, esc_html__( 'Access Manager - Echo Knowledge Base', 'echo-knowledge-base' ), esc_html__( 'Access Manager', 'echo-knowledge-base' ),
				'admin_eckb_access_crud_users', 'amag-access-mgr', array( new AMGR_Access_Page(), 'display_access_manager_page' ) );
		}
	}

	/**
	 * Do not log anything if not in the back-end or not logged in as an admin
	 *
	 * @return bool
	 */
	public static function can_log_message( $report_on_error ) {

		// AMGR specific - not needed
		// we cannot log too early
		/*if ( ! function_exists('wp_get_current_user') ) {
			return false;
		}*/

		return $report_on_error;
	}

	public static function plugin_setup() {
		require_once 'class-epkb-autoloader.php';

		// ensure we have initial configuration
		$plugin_version = EPKB_Utilities::get_wp_option( 'epkb_version', null, false, true );
		if ( is_wp_error( $plugin_version ) ) {
			AMGR_Logging::add_log( 'Error occurred when retrieving EPKB version upon activation', $plugin_version );
		}
		if ( is_wp_error( $plugin_version ) || empty( $plugin_version ) ) {
			$kb_id = EPKB_KB_Config_DB::DEFAULT_KB_ID;
			$handler = new EPKB_KB_Config_DB();
			$handler->update_kb_configuration( $kb_id, EPKB_KB_Config_Specs::get_default_kb_config( $kb_id ) );
		}

		//. prepare AMGR portion
		self::amgr_activate_plugin( $plugin_version );

		return $plugin_version;
	}

	public static function finish_plugin_setup() {
		$handler = new AMGR_Setup_KB_Groups();
		$handler->setup_amgr_data();
	}

	private static function display_amgr_debug_data() {

		// ensure user has correct permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			return EPKB_HTML_Forms::notification_box_middle( array(
				'type' => 'error',
				'desc' => esc_html__( 'No access', 'echo-knowledge-base' ),
			), true );
		}

		ob_start();

		$kb_groups = new AMGR_DB_KB_Groups();
		$result = $kb_groups->get_all_rows(); ?>

		<h4><?php esc_html_e( 'Groups Info:', 'echo-knowledge-base' ); ?></h4><?php

		if ( is_wp_error( $result ) ) {
			echo '<p>' . esc_html( $result->get_error_message() ) . '</p>';
		} else { ?>

			<table class="epkb_debug_table">
			<tr>
				<td>name</td> <td>kb_group_id</td> <td>kb_id</td>
			</tr><?php

			foreach ( $result as $row ) { ?>
				<tr>
				<td><?php echo esc_html( $row->name ); ?></td> <td><?php echo esc_html( $row->kb_group_id ); ?></td> <td><?php echo esc_html( $row->kb_id ); ?></td>
				</tr><?php
			} ?>
			</table><?php
		}

		$kb_groups = new AMGR_DB_KB_Public_Groups();
		$result = $kb_groups->get_all_rows(); ?>

		<h4><?php esc_html_e( 'Groups Public Info:', 'echo-knowledge-base' ); ?></h4><?php

		if ( is_wp_error( $result ) ) {
			echo '<p>' . esc_html( $result->get_error_message() ) . '</p>';
		} else { ?>

			<table class="epkb_debug_table">
			<tr>
				<td>name</td> <td>kb_group_id</td> <td>kb_id</td>
			</tr><?php

			foreach ( $result as $row ) { ?>
				<tr>
				<td><?php echo esc_html( $row->name ); ?></td> <td><?php echo esc_html( $row->kb_group_id ); ?></td> <td><?php echo esc_html( $row->kb_id ); ?></td>
				</tr><?php
			} ?>
			</table><?php

		}

		return ob_get_clean();
	}

	/**
	 * AMGR: Get CPT specific capabilities; primitive capabilities have plural form
	 *
	 * @param $capability_type
	 * @return array
	 */
	private static function get_cpt_capabilities_inner( $capability_type ) {
		return array(
			'edit_post' => "edit_{$capability_type}",
			'read_post' => "read_{$capability_type}",
			'delete_post' => "delete_{$capability_type}",
			'edit_posts' => "edit_{$capability_type}s",
			'edit_others_posts' => "edit_others_{$capability_type}s",
			'publish_posts' => "publish_{$capability_type}s",
			'read_private_posts' => "read_private_{$capability_type}s",
			'delete_posts' => "delete_{$capability_type}s",
			'delete_private_posts' => "delete_private_{$capability_type}s",
			'delete_published_posts' => "delete_published_{$capability_type}s",
			'delete_others_posts' => "delete_others_{$capability_type}s",
			'edit_private_posts' => "edit_private_{$capability_type}s",
			'edit_published_posts' => "edit_published_{$capability_type}s"
		);
	}

	/**
	 * Adds one or more classes to the body tag in the admin.
	 *
	 * @param  String $classes Current body classes.
	 * @return String          Altered body classes.
	 */
	public static function add_admin_body_class_inner( $classes ) {

		// add specific CSS class to 'add/edit' Article admin screen
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen ) && $current_screen instanceof WP_Screen && EPKB_KB_Handler::is_kb_post_type( $current_screen->post_type ) && ( $current_screen->action == 'add' || EPKB_Utilities::get( 'action' ) == 'edit' ) ) {
			$classes .= ' amag-private-articles';
		}

		return $classes . ' amag-private-admin ';
	}

	/**
	 * Get relevant KB id for AMGR
	 *
	 * @return int|mixed
	 */
	private static function get_first_kb_id() {

		$user = AMGR_Access_Utilities::get_current_user();
		if ( empty( $user ) ) {
			return EPKB_KB_Config_DB::DEFAULT_KB_ID;
		}

		// retrieve user's KB ids
		$user_kb_ids = epkb_get_instance()->db_kb_group_users->get_user_kbs();

		// return default KB id on failure
		if ( empty( $user_kb_ids ) ) {
			return EPKB_KB_Config_DB::DEFAULT_KB_ID;
		}

		// make sure we have correct order in the KB ids array
		if ( is_array( $user_kb_ids ) ) {
			sort( $user_kb_ids );
		}

		return empty( $user_kb_ids[0] ) ? EPKB_KB_Config_DB::DEFAULT_KB_ID : $user_kb_ids[0];
	}

	public static function to_string( $error, $details=true ) {
		$kb_id_text = defined( 'EM'.'KB_PLUGIN_NAME' ) && ! empty( $error['kb'] ) ? 'KB ID: ' . $error['kb'] . ', ' : '';
		$error_msg = empty( $error['message'] ) ? '' : $error['message'];
		$error_trace = empty( $error['trace'] ) ? '' : $error['trace'];
		return esc_html( $kb_id_text ) . '<br>' . wp_kses_post( $error_msg ) . '<br>' . ( $details ? wp_kses_post( $error_trace ) : '' );
	}

	public static function display_category_notices() {
		EPKB_KB_Config_Category::category_icon_message( 'epkb-icons-are-enabled','You will not see this new category until KB Manager or admin will grant access to this category for your group.', 'https://www.echoknowledgebase.com/documentation/kb-editor-workflow/', 'Learn More');
	}

	/**
	 * Setup AMGR part of the plugin
	 *
	 * @param $core_plugin_version
	 */
	private static function amgr_activate_plugin( $core_plugin_version ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		require_once Echo_Knowledge_Base::$plugin_dir . 'includes_amgr/system/class-amgr-autoloader.php';

		/** AMGR: Ensure that we have AMGR tables in place */
		$handle = new AMGR_DB_KB_Groups();
		@$handle->create_table();
		$handle = new AMGR_DB_KB_Public_Groups();
		@$handle->create_table();
		$handle = new AMGR_DB_KB_Group_Users();
		@$handle->create_table();
		$handle = new AMGR_DB_Access_KB_Categories();
		@$handle->create_table();
		$handle = new AMGR_DB_Access_Read_Only_Articles();
		@$handle->create_table();
		$handle = new AMGR_DB_Access_Read_Only_Categories();
		@$handle->create_table();

		// true if the plugin is activated for the first time since installation
		$amgr_plugin_version = get_option( 'amag_version' );
		if ( empty( $amgr_plugin_version ) ) {

			set_transient( '_amgr_plugin_installed', true, 3600 );

			// retrieve all existing KB IDs
			$kb_db = new EPKB_KB_Config_DB();
			AMGR_Logging::disable_logging();
			$all_kb_ids = $kb_db->get_kb_ids();
			AMGR_Logging::enable_logging();

			// setup configuration
			foreach ( $all_kb_ids as $kb_id ) {

				// ensure AMGR config doesn't exist already
				AMGR_Logging::disable_logging();
				$amgr_db = new AMGR_KB_Access_Config_DB( false );
				$amgr_config = $amgr_db->get_kb_config( $kb_id );
				AMGR_Logging::enable_logging();
				if ( is_wp_error( $amgr_config ) ) {
					$amgr_defaults = AMGR_KB_Config_Specs::get_default_kb_config( $kb_id );
					AMGR_Logging::disable_logging();
					$amgr_db->update_kb_configuration( $kb_id, $amgr_defaults );
					AMGR_Logging::enable_logging();
				}

				// do not check articles if KB not setup
				if ( $kb_id == 1 && empty( $core_plugin_version ) ) {
					continue;
				}

				// ensure that all articles are initially private
				$current_articles_ids = AMGR_Access_Utilities::get_articles_ids_from_sequence( $kb_id );
				if ( $current_articles_ids === null ) {
					AMGR_Logging::add_log( 'could not get article ids from sequence at setup' );
				} else {
					foreach ( $current_articles_ids as $current_articles_id ) {
						$post = EPKB_Core_Utilities::get_kb_post_secure( $current_articles_id );
						if ( $post === null ) {
							AMGR_Logging::add_log( 'Could not retrieve post: ' . $current_articles_id );
							continue;
						}

						if ( $post->post_status != 'publish' ) {
							continue;
						}

						// update the post status
						if ( false === $wpdb->update( $wpdb->posts, array('post_status' => 'private'), array('ID' => $current_articles_id) ) ) {
							AMGR_Logging::add_log( 'Could not update post status in the database: ' . $wpdb->last_error );
							continue;
						}
					}
				}
			} // next kb_id

			EPKB_Utilities::save_wp_option( 'amag_version', Echo_Knowledge_Base::$amag_version );
		}
	}

	private static function delete_amgr_data() {
		/** @global wpdb $wpdb */
		global $wpdb;

		delete_option( 'amag_version' );
		delete_option( 'amgp_version' );
		delete_option( 'amcr_version' );
		delete_option( 'amgr_error_log' );
		delete_option( 'amgr_show_upgrade_message' );
		delete_option( 'amgr_table_check' );
		delete_option( 'amgr_last_license_check' );
		delete_option( 'amgr_license_state' );
		delete_option( 'amgr_access_config_1' );
		delete_option( 'amgr_license_state' );


		// Remove all database tables
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_kb_groups' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_kb_public_groups' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_kb_group_users' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_access_kb_categories' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_access_read_articles' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'amgr_access_read_categories' );
	}

	const EPKB_KB_MANAGER_CAPABILITY = 'admin_eckb_access_manager_page';
	const EPKB_KB_MANAGER_CONFIG = 'admin_eckb_access_config_write';

	// AMGR: if KB Groups add-on is enabled then limit certain contexts for non Admins and non KB Managers
	const AMGR_RESTRICTED_ADMIN_UI_CONTEXTS = array(
		'admin_eckb_access_frontend_editor_write',
		'admin_eckb_access_order_articles_write',

		// additionally, KB Managers can access contexts the following Admin contexts
		'admin_eckb_access_config_write',
	);

	public static function is_context_check( $context ) {
		// KB manager can access any KB context
		if ( current_user_can( self::EPKB_KB_MANAGER_CAPABILITY ) ) {
			return true;
		}

		// if KB Groups add-on is enabled then limit certain contexts for non Admins and non KB Managers
		if ( AMGR_WP_Roles::use_kb_groups() && in_array( $context, self::AMGR_RESTRICTED_ADMIN_UI_CONTEXTS ) ) {
			return false;
		}

		return null;
	}

	public static function get_manager_capability( $contexts ) {
		// CAPABILITY LEVEL 3: check if any context is in allowed admin UI contexts for 'KB Manager'
		foreach ( $contexts as $context ) {

			if ( in_array( $context, EPKB_Admin_UI_Access::ADMIN_UI_CONTEXTS ) ) {
				return self::EPKB_KB_MANAGER_CAPABILITY;
			}
		}

		return '';
	}

	public static function get_group_role( $role ) {
		// Enabled KB Groups add-on
		if ( AMGR_WP_Roles::use_kb_groups() ) {
			$kb_id = empty( $kb_id ) ? EPKB_KB_Handler::get_relevant_kb_id() : $kb_id;
			$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_id );
			return str_replace( 'xxx', $capability_type, $role );
		}

		return '';
	}

	public static function groups_capability() {
		// enabled KB Groups add-on
		if ( AMGR_WP_Roles::use_kb_groups() ) {
			return self::EPKB_KB_MANAGER_CAPABILITY;
		}

		return '';
	}

	public static function is_context_continue( $context ) {
		// KB Groups does not allow KB Author and KB Editor to use Editor or Ordering of restricted Articles / Categories
		if ( AMGR_WP_Roles::use_kb_groups() && in_array( $context, ['admin_eckb_access_frontend_editor_write', 'admin_eckb_access_order_articles_write'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get options list for Access Control settings
	 *
	 * @param false $include_author
	 *
	 * @return array
	 */
	public static function get_access_control_options( $kb_config, $include_author=false ) {

		$access_control_ptions = [];

		// Enabled KB Groups add-on
		if ( AMGR_WP_Roles::use_kb_groups() ) {
			$capability_type = AMGR_Access_Utilities::get_capability_type( $kb_config['id'] );

			// Optional Author capability
			if ( $include_author ) {
				$author_capability = str_replace( 'xxx', $capability_type, EPKB_Admin_UI_Access::EPKB_KB_AUTHOR_CAPABILITY );
				$access_control_ptions[$author_capability] = self::get_admins_distinct_box() . self::get_kb_managers_distinct_box() . self::get_editors_distinct_box() . self::get_authors_distinct_box();
			}

			// Editor capability
			$editor_capability = str_replace( 'xxx', $capability_type, EPKB_Admin_UI_Access::EPKB_KB_EDITOR_CAPABILITY );
			$access_control_ptions[$editor_capability] = self::get_admins_distinct_box() . self::get_kb_managers_distinct_box() . self::get_editors_distinct_box();

			// Admin capability
			$access_control_ptions[self::EPKB_KB_MANAGER_CAPABILITY] = self::get_admins_distinct_box() . self::get_kb_managers_distinct_box();

			// Disabled KB Groups add-on
		} else {

			if ( $include_author ) {
				$access_control_ptions[EPKB_Admin_UI_Access::EPKB_WP_AUTHOR_CAPABILITY] = self::get_admins_distinct_box() . self::get_editors_distinct_box() . self::get_authors_distinct_box() . self::get_users_with_capability_distinct_box( EPKB_Admin_UI_Access::EPKB_WP_AUTHOR_CAPABILITY );
			}

			$access_control_ptions[EPKB_Admin_UI_Access::EPKB_WP_EDITOR_CAPABILITY] = self::get_admins_distinct_box() . self::get_editors_distinct_box() . self::get_users_with_capability_distinct_box( EPKB_Admin_UI_Access::EPKB_WP_EDITOR_CAPABILITY );
			$access_control_ptions[EPKB_Admin_UI_Access::EPKB_ADMIN_CAPABILITY]     = self::get_admins_distinct_box();
		}

		return $access_control_ptions;
	}

	private static function get_admins_distinct_box() {
		return sprintf( esc_html__( '%sAdmins%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--high">', '</span>' );
	}

	private static function get_users_with_capability_distinct_box( $capability ) {
		return sprintf( esc_html__( '%susers with "%s" capability%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--lowest">', $capability, '</span>' );
	}

	// if KB Groups add-on is enabled then no need to look for contexts available for Admins and KB Managers only
	public static function is_ui_access_loop( $context ) {
		if ( AMGR_WP_Roles::use_kb_groups() && in_array( $context, self::AMGR_RESTRICTED_ADMIN_UI_CONTEXTS ) ) {
			return true;
		}

		return false;
	}

	private static function get_editors_distinct_box() {
		return AMGR_WP_Roles::use_kb_groups()
			? sprintf( esc_html__( '%sKB Editors%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--middle">', '</span>' )
			: sprintf( esc_html__( '%sEditors%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--middle">', '</span>' );
	}

	private static function get_authors_distinct_box() {
		return AMGR_WP_Roles::use_kb_groups()
			? sprintf( esc_html__( '%sKB Authors%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--low">', '</span>' )
			: sprintf( esc_html__( '%sAuthors%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--low">', '</span>' );
	}

	private static function get_kb_managers_distinct_box() {
		return sprintf( __( '%sKB Managers%s', 'echo-knowledge-base' ), '<span class="epkb-admin__distinct-box epkb-admin__distinct-box--high">', '</span>' );
	}
}
