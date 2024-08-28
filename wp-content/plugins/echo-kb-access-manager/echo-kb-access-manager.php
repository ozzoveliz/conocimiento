<?php
/**
 * Plugin Name: Knowledge Base with Access Manager
 * Plugin URI: https://www.echoknowledgebase.com
 * Description: Create Echo Knowledge Base articles, docs and FAQs with Access Manager.
 * Version: 8.42.0
 * Author: Echo Plugins
 * Author URI: https://www.echoknowledgebase.com
 * Text Domain: echo-knowledge-base
 * Domain Path: /languages
 * License: GNU General Public License v2.0
 *
 * Knowledge Base with Access Manager is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Knowledge Base with Access Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Knowledge Base with Access Manager. If not, see <http://www.gnu.org/licenses/>.
 *
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/** AMGR: plugin name */
if ( ! defined( 'AMAG_PLUGIN_NAME' ) ) {
	define( 'AMAG_PLUGIN_NAME', 'Echo KB with Access Manager' );
}

if ( ! class_exists( 'Echo_Knowledge_Base' ) ) :

/**
 * Main class to load the plugin.
 *
 * Singleton
 */
final class Echo_Knowledge_Base {

	/* @var Echo_Knowledge_Base */
	private static $instance;

	public static $version = '11.42.0';
	public static $amag_version = '8.42.0';
	public static $plugin_dir;
	public static $plugin_url;
	public static $plugin_file = __FILE__;
	public static $needs_min_add_on_version = array( 'LAY' => '1.4.0', 'MKB' => '1.7.0', 'RTD' => '1.0.0', 'IDG' => '1.0.0', 'BLK' => '1.0.0',
													 'SEA' => '1.0.0', 'MGP' => '1.0.0', 'MCR' => '1.0.0', 'PRF' => '1.0.0', 'PIE' => '1.0.0', 'ART' => '1.0.0' );

	/* @var EPKB_KB_Config_DB */
	public $kb_config_obj;


	/* @var AMGR_KB_Access_Config_DB */
	public $kb_access_config_obj;

	/* @var AMGR_License_Handler */
	public $license_handler;

	/* @var AMGR_DB_KB_Groups */
	public $db_kb_groups;
	/* @var AMGR_DB_KB_Public_Groups */
	public $db_kb_public_groups;
	/* @var AMGR_DB_KB_Group_Users */
	public $db_kb_group_users;
	/* @var AMGR_DB_Access_KB_Categories */
	public $db_access_kb_categories;
	/* @var AMGR_DB_Access_Read_Only_Articles */
	public $db_access_read_only_articles;
	/* @var AMGR_DB_Access_Read_Only_Categories */
	public $db_access_read_only_categories;
	/* @var AMGR_Access_Manager */
	public $kb_access_manager;

	/**
	 * Initialise the plugin
	 */
	private function __construct() {
		self::$plugin_dir = plugin_dir_path(  __FILE__ );
		self::$plugin_url = plugin_dir_url( __FILE__ );
	}

	/**
	 * Retrieve or create a new instance of this main class (avoid global vars)
	 *
	 * @static
	 * @return Echo_Knowledge_Base
	 */
	public static function instance() {

		if ( ! empty( self::$instance ) && ( self::$instance instanceof Echo_Knowledge_Base ) ) {
			return self::$instance;
		}

		self::$instance = new Echo_Knowledge_Base();
		self::$instance->setup_system();
		self::$instance->setup_plugin();

		add_action( 'plugins_loaded', array( self::$instance, 'load_text_domain' ), 11 );

		return self::$instance;
	}

	/**
	 * Setup class autoloading and other support functions. Setup custom core features.
	 */
	private function setup_system() {

		// autoload classes ONLY when needed by executed code rather than on every page request
		require_once self::$plugin_dir . 'includes/system/class-epkb-autoloader.php';
		require_once self::$plugin_dir . 'includes_amgr/system/class-amgr-autoloader.php';

		// register settings
		self::$instance->kb_config_obj = new EPKB_KB_Config_DB();
		self::$instance->kb_access_config_obj = new AMGR_KB_Access_Config_DB();

		// load non-classes
		require_once self::$plugin_dir . 'includes/system/plugin-setup.php';
		require_once self::$plugin_dir . 'includes/system/scripts-registration-public.php';
		require_once self::$plugin_dir . 'includes/system/scripts-registration-admin.php';
		require_once self::$plugin_dir . 'includes_amgr/system/scripts-registration.php';
		require_once self::$plugin_dir . 'includes/system/plugin-links.php';

		add_action( 'init', array( self::$instance, 'epkb_stop_heartbeat' ), 1 );

		/** AMGR: initialize core classes */
		self::$instance->db_kb_groups                 = new AMGR_DB_KB_Groups();
		self::$instance->db_kb_public_groups          = new AMGR_DB_KB_Public_Groups();
		self::$instance->db_kb_group_users            = new AMGR_DB_KB_Group_Users();
		self::$instance->db_access_kb_categories      = new AMGR_DB_Access_KB_Categories();
		self::$instance->db_access_read_only_articles = new AMGR_DB_Access_Read_Only_Articles();
		self::$instance->db_access_read_only_categories = new AMGR_DB_Access_Read_Only_Categories();

		self::$instance->kb_access_manager = new AMGR_Access_Manager();

		new AMGR_Access_Articles_Front();
		new AMGR_Access_Articles_Back();
		new AMGR_Access_Categories_Back();
		new AMGR_Admin_Categories_Page();

		new EPKB_Upgrades();
        new AMGR_Upgrades();

		// setup custom core features
		new EPKB_Articles_CPT_Setup();
		new EPKB_Articles_Admin();
		new EPKB_FAQs_CPT_Setup();

		// subscribe to category actions create/edit/delete including for REST requests in Gutenberg
		new EPKB_Categories_Admin();

		// invoke last
	 	self::instance()->license_handler = new AMGR_License_Handler( self::$plugin_file, self::$amag_version );
	}

	/**
	 * Setup plugin before it runs. Include functions and instantiate classes based on user action
	 */
	private function setup_plugin() {

		$action = EPKB_Utilities::get( 'action' );

		// process action request if any
		if ( ! empty( $action ) ) {
			$this->handle_action_request( $action );
		}

		// handle AJAX front & back-end requests (no admin, no admin bar)
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->handle_ajax_requests( $action );
			return;
		}

		// ADMIN or CLI
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {	// || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
            if ( $this->is_kb_plugin_active_for_network( 'echo-kb-access-manager/echo-kb-access-manager.php' ) ) {
	            EPKB_Logging::add_log( 'Access Manager cannot be installed network-wide.' );
	            return;
            } else {
                $this->setup_backend_classes();
            }
			return;
		}

		// catch saving and creating of Post in Gutenberg
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) && ( strpos( $_SERVER['HTTP_REFERER'], '/wp-admin/post.php' ) !== false || strpos( $_SERVER['HTTP_REFERER'], '/wp-admin/post-new.php' ) !== false ) ) {
			require_once self::$plugin_dir . 'includes/admin/admin-functions.php';
		}

		// FRONT-END (no ajax, possibly admin bar)
		new EPKB_Layouts_Setup();      // KB Main page shortcode, list of themes
		new EPKB_Articles_Setup();
		new EPKB_Templates();
		new EPKB_Shortcodes();
	}

	/**
	 * Handle plugin actions here such as saving settings
	 * @param $action
	 */
	private function handle_action_request( $action ) {

		if ( $action == 'eckb_apply_editor_changes' ) {
			new EPKB_Editor_Controller();
			return;
		}
		
		if ( $action == 'epkb_load_editor' ) {
			new EPKB_Editor_View();
			return;
		}
		
		if ( ! EPKB_KB_Handler::is_kb_request() ) {
			return;
		}

		if ( $action == 'add-tag' ) {  // AMGR: adding category term
			new AMGR_Access_Categories_Back( true );
			return;
		}

		if ( $action == 'epkb_download_debug_info' ) {
			new EPKB_Debug_Controller();
			return;
		}
	}

	/**
	 * Handle AJAX requests coming from front-end and back-end
	 * @param $action
	 */
	private function handle_ajax_requests( $action ) {

        if ( empty( $action ) ) {
            return;
        }

		if ( $action == 'epkb-search-kb' ) {  // user searching KB
			new EPKB_KB_Search();
			return;
		} else if ( in_array( $action, array( 'epkb_toggle_debug', 'epkb_enable_advanced_search_debug', 'epkb_show_logs', 'epkb_reset_logs' ) ) ) {
			new EPKB_Debug_Controller();
			return;
		} else if ( in_array( $action, array( 'epkb_get_wizard_template', 'epkb_apply_wizard_changes', 'epkb_wizard_update_order_view', 'epkb_apply_setup_wizard_changes', 'epkb_report_admin_error' ) ) ) {
			new EPKB_KB_Wizard_Cntrl();
			return;
		} else if ( in_array( $action, array( EPKB_Need_Help_Features::FEATURES_TAB_VISITED_ACTION ) ) ) {
			new EPKB_Need_Help_Features();
			return;
		} else if ( in_array( $action, array( 'epkb_wpml_enable', 'epkb_preload_fonts','epkb_disable_openai', 'epkb_load_resource_links_icons', 'epkb_load_general_typography', 'epkb_save_access_control', 'epkb_apply_settings_changes' ) ) ) {
			new EPKB_KB_Config_Controller();
			return;
		} else if ( in_array( $action, array( 'epkb_reset_sequence', 'epkb_show_sequence' ) ) ) {
			new EPKB_Reset();
			return;
		} else if ( in_array( $action, array( 'epkb_create_kb_demo_data' ) ) ) {
			new EPKB_Controller();
			return;
		} else if ( in_array( $action, array( 'epkb_save_faq', 'epkb_get_faq', 'epkb_delete_faq', 'epkb_save_faq_group', 'epkb_delete_faq_group' ) ) ) {
			new EPKB_FAQs_Ctrl();
			return;
		} else if ( in_array( $action, array( 'amgr_search_user', 'amgr_save_debug_user_data' ) ) ) {
			new AMGR_Debug_User_Access();
			return;
		}
		
		if ( $action == 'add-tag' ) {
			new EPKB_KB_Config_Category();
			new AMGR_Access_Categories_Back( true );			
			return;
		}

		if ( $action == 'epkb_dismiss_ongoing_notice' ) {
			new EPKB_Admin_Notices( true );
			return;
		}

		if ( $action == 'epkb_editor_error' || $action == 'eckb_editor_get_themes_list' ) {
			new EPKB_Editor_Controller();
			return;
		}

		if ( in_array( $action, array( 'epkb_load_articles_list', 'epkb_convert_kb_content' ) )  ) {
			new EPKB_Convert_Ctrl();
			return;
		}

		if ( $action == 'epkb_update_the_content_flag' ) {
			new EPKB_Articles_Setup();
			return;
		}

		if ( $action == 'epkb_delete_all_kb_data' ) {
			new EPKB_Delete_KB();
			return;
		}

		if ( in_array( $action, [ 'epkb_ai_request', 'epkb_ai_feedback' ] ) ) {
			new EPKB_AI_Help_Sidebar_Ctrl();
			return;
		}

		if ( $action == 'epkb_count_article_view' ) {
			new EPKB_Article_Count_Cntrl();
			return;
		}

		// AMGR: handle management of KB
		if ( in_array($action, array( 'amgr_display_kb_kbs_access_ajax', 'amgr_reset_logs_ajax', 'epkb_save_amgr_settings' ) ) ) {
			new AMGR_Access_Page_Cntrl_KBs();
			return;
		}

		// AMGR: handle management of Articles ACCESS
		if ( in_array($_REQUEST['action'], array( 'amgr_display_kb_articles_access_ajax', 'amgr_save_articles_access_ajax' ) ) ) {
			new AMGR_Access_Page_Cntrl_Articles();
			return;
		}

		// AMGR: handle management of Categories ACCESS
		if ( in_array($_REQUEST['action'], array( 'amgr_display_kb_category_access_ajax', 'amgr_save_categories_access_ajax' ) ) ) {
			new AMGR_Access_Page_Cntrl_Categories();
			return;
		}

		// AMGR: handle management of Articles ACCESS
		if ( $_REQUEST['action'] == 'amgr_save_kb_config_changes' ) {
			new AMGR_KB_Config_Controller();
			return;
		}

		if ($action == 'amgr_handle_license_request' ) {
			new AMGR_Add_Ons_Page();
			return;
		}
	}

	/**
	 * Setup up classes when on ADMIN pages
	 */
	public function setup_backend_classes() {
		global $pagenow;

		$is_kb_request = EPKB_KB_Handler::is_kb_request();
		$request_page = empty($_REQUEST['page']) ? '' : EPKB_Utilities::request_key( 'page' );
		$admin_pages = [ 'post.php', 'edit.php', 'post-new.php', 'edit-tags.php', 'term.php' ];

		// show KB notice and AI Help Sidebar on our pages or when potential KB Main Page is being edited
		if ( $is_kb_request && in_array( $pagenow, $admin_pages ) ) {
			new EPKB_Admin_Notices();
			new AMGR_Admin_Notices();
			new EPKB_AI_Help_Sidebar();
		}

		// article new page
		if ( $is_kb_request && $pagenow == 'post-new.php' ) {
			add_action( 'admin_enqueue_scripts', 'epkb_load_admin_article_page_styles' );
		}

		// article edit page - include scripts to show categories box
		if ( $pagenow == 'post.php' && ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
			$kb_post_type = get_post_type( $_REQUEST['post'] );
			if ( EPKB_KB_Handler::is_kb_post_type( $kb_post_type ) ) {
				new EPKB_AI_Help_Sidebar();
				add_action( 'admin_enqueue_scripts', 'epkb_load_admin_article_page_styles' );
				add_action( 'admin_enqueue_scripts', 'amgr_load_admin_plugin_pages_resources' );
			}
		}

		// include our admin scripts on our admin pages (submenus of KB menu) but not on Edit/Add page due to blocks etc.
		if ( $is_kb_request && $pagenow != 'post-new.php' && $pagenow != 'post.php' ) {

			// KB Configuration Page
			if ( $request_page == 'epkb-kb-configuration' ) {

				// Setup Wizard
				if ( isset( $_GET['setup-wizard-on'] ) ) {
					add_action( 'admin_enqueue_scripts', 'epkb_load_admin_kb_setup_wizard_script' );

				// Usual KB Configuration page
				} else {
					add_action( 'admin_enqueue_scripts', 'epkb_load_admin_kb_wizards_script' );
					add_action( 'admin_enqueue_scripts', 'epkb_load_admin_plugin_pages_resources' );
					add_action( 'admin_enqueue_scripts', 'amgr_load_admin_plugin_pages_resources' );
				}

			// KB Admin Pages (not config)
			} else {
				add_action( 'admin_enqueue_scripts', 'epkb_load_admin_plugin_pages_resources' );
				add_action( 'admin_enqueue_scripts', 'amgr_load_admin_plugin_pages_resources' );
			}
		}

		// AMGR: if KB Groups is "active" but not activated then don't let user to browse or create an article until activated
		if ( empty( $request_page ) && in_array( $pagenow, array('edit.php', 'post-new.php') ) && AMGR_WP_Roles::use_kb_groups() ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( ! is_plugin_active( 'echo-kb-groups/echo-kb-groups.php' ) ) {
				echo '<h4>' . esc_html__('Activate plugin', 'echo-knowledge-base' ) . '</h4>';
				echo '<p>' . esc_html__('KB Groups plugin is not active. Active it before proceeding.', 'echo-knowledge-base' ) . '</p>';
				echo '<a href="' . self_admin_url( 'index.php' ) . '">' . esc_html__('Back to WP Admin', 'echo-knowledge-base' ) . '</a>';
				die();
			}
		}

		// AMGR: include our admin scripts on our admin pages (submenus of KB menu)
		if ( $is_kb_request && in_array( $pagenow, $admin_pages ) ) {
			add_action( 'admin_enqueue_scripts', 'amgr_load_admin_plugin_pages_resources' );
		}

		// on Category page show category icon selection feature
		if ( $is_kb_request && ( $pagenow == 'term.php' || $pagenow == 'edit-tags.php' ) ) {
			new EPKB_KB_Config_Category();
		}

		// admin core classes
		require_once self::$plugin_dir . 'includes/admin/admin-menu.php';
		require_once self::$plugin_dir . 'includes/admin/admin-functions.php';

		// AMGR: admin other classes
		$classes = array(
			// class name=0, loading=1 (backend/admin-bar), pages=2, admin_action=3, KB post type required=4
			array( 'AMGR_Add_Ons_Page', array( 'backend' ), array( 'edit.php', AMGR_Add_Ons_Page::AMGR_KB_ADD_ONS_PAGE ), array( '' ), true ),
			array( 'AMGR_Admin_Articles_Page', array( 'backend' ), array( 'post-new.php' ), array( '' ), true ),   // initial Add article screen
			array( 'AMGR_Admin_Articles_Page', array( 'backend' ), array( 'post.php' ), array( 'edit' ), false ),  // after Add submit or Article Edit (edit)
			array( 'AMGR_Admin_Articles_Page', array( 'backend' ), array( 'edit.php' ), array( 'edit' ), false ),  // after Add submit or Article Edit (edit)
		);

		$current_page = empty($request_page) ? $pagenow : $request_page;
		$action = EPKB_Utilities::get('action');
		foreach( $classes as $class_info ) {

			if ( $class_info[4] && ! $is_kb_request ) {
				continue;
			}

			// INDIVIDUAL PAGES: if feature available on a specific page then ensure the page is being loaded
			if ( ( ! in_array( $current_page, $class_info[2] ) ) &&
			     ( empty( $class_info[3] ) || empty( $action ) || ! in_array( $action, $class_info[3] ) ) ) {
				continue;
			}

			$new_class = $class_info[0];
			if ( class_exists( $new_class ) ) {
				new $new_class();
			}
		}

		if ( ! empty( $pagenow ) && in_array( $pagenow, [ 'plugins.php', 'plugins-network.php' ] ) ) {
			new EPKB_Deactivate_Feedback();
		}

		// setup article views counter hooks
		new EPKB_Article_Count_Handler();
	}

	/**
	/**
	 * Loads the plugin language files from ./languages directory.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'echo-knowledge-base', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	// Don't allow this singleton to be cloned.
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, 'Invalid (#1)', '4.0' );
	}

	// Don't allow un-serializing of the class except when testing
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, 'Invalid (#1)', '4.0' );
	}

	/**
	 * When developing and debugging we don't need heartbeat
	 */
	public function epkb_stop_heartbeat() {

		AMGR_Access_Manager::check_amgr_tables();

		if ( defined( 'RUNTIME_ENVIRONMENT' ) && RUNTIME_ENVIRONMENT == 'ECHODEV' ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

    private function is_kb_plugin_active_for_network( $plugin ) {
        if ( ! is_multisite() ) {
            return false;
        }

        $plugins = get_site_option( 'active_sitewide_plugins' );
        if ( isset( $plugins[ $plugin ] ) ) {
            return true;
        }

        return false;
    }
}

/**
 * Returns the single instance of this class
 * @return Echo_Knowledge_Base - this class instance
 */
function epkb_get_instance() {
	return Echo_Knowledge_Base::instance();
}
epkb_get_instance();

else :

	// we don't want KB Core running so deactivate it
	$plugins = get_option( 'active_plugins' );
	if ( is_array($plugins) ) {
		foreach ( $plugins as $ix => $plugin ) {
			if ( $plugin === 'echo-knowledge-base/echo-knowledge-base.php' ) {
				$plugins[$ix] = false;
				update_option( 'active_plugins', array_filter( $plugins ) );
				break;
			}
		}
	}

endif; // end class_exists() check
