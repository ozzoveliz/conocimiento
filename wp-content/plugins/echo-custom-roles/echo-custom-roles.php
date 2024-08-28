<?php
/**
 * Plugin Name: KB - Custom Roles
 * Plugin URI: https://www.echoknowledgebase.com/wordpress-add-ons/
 * Description: Map roles to KB Roles / KB Groups.
 * Version: 1.7.0
 * Author: Echo Plugins
 * Author URI: https://www.echoknowledgebase.com
 * Text Domain: echo-knowledge-base
 * Domain Path: languages
 * Copyright: (c) 2018 Echo Plugins
 *
 * KB - Custom Roles is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * KB - Custom Roles is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Custom Roles. If not, see <http://www.gnu.org/licenses/>.
 *
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// plugin name; used in KB core
if ( ! defined( 'AMCR_PLUGIN_NAME' ) ) {
	define( 'AMCR_PLUGIN_NAME', 'KB - Custom Roles' );
}

/**
 * Main class to load the plugin.
 *
 * Singleton
 */
final class Echo_Custom_roles {

	/* @var Echo_Custom_roles */
	private static $instance;

	public static $version = '1.7.0';
	public static $plugin_dir;
	public static $plugin_url;
	public static $plugin_file = __FILE__;
	public static $needs_min_core_version = '2.1.0';

	/* @var AMCR_KB_Config_DB */
	public $kb_config_obj;
	
	/* @var AMCR_License_Handler */
	public $license_handler;

	public $is_dormant = false;

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
	 * @param bool $dormant
	 * @return Echo_Custom_roles
	 */
	public static function instance( $dormant=false ) {

		if ( isset( self::$instance ) || self::$instance instanceof Echo_Custom_roles  ) {
			return self::$instance;
		}

		self::$instance = new Echo_Custom_roles();

		// if the plugin is not active, still show update messages and license to enter/save
		if ( $dormant === true ) {
			self::$instance->setup_dormant_system();
			return '';
		}

		self::$instance->setup_system();
		self::$instance->setup_plugin();

		add_action( 'plugins_loaded', array( self::$instance, 'load_text_domain' ) );

		return self::$instance;
	}

	/**
	 * If add-on is not active because it was de-activated or is not matching with core then run only
	 * license related functions
	 */
	private function setup_dormant_system() {
		$this->is_dormant = true;
		require_once self::$plugin_dir . 'includes/system/class-amcr-autoloader.php';
		self::$instance->kb_config_obj = new AMCR_KB_Config_DB();
		new AMCR_Add_Ons_Page();
		require_once self::$plugin_dir . 'includes/system/scripts-registration.php';
		add_action( 'admin_enqueue_scripts', 'amcr_load_admin_plugin_pages_resources' );
		self::instance()->license_handler = new AMCR_License_Handler( self::$plugin_file, self::$version );
	}

	/**
	 * Setup class auto-loading and other support functions. Setup custom core features.
	 */
	private function setup_system() {

		// autoload classes ONLY when needed by executed code rather than on every page request
		require_once self::$plugin_dir . 'includes/system/class-amcr-autoloader.php';

		// load non-classes
		require_once self::$plugin_dir . 'includes/system/scripts-registration.php';
		require_once self::$plugin_dir . 'includes/system/plugin-links.php';

		// register settings
		self::$instance->kb_config_obj = new AMCR_KB_Config_DB();

		/** AMCR: initialize core classes */
		new AMCR_Upgrades();
		new AMCR_Settings_Page();

		// invoke last
		self::instance()->license_handler = new AMCR_License_Handler( self::$plugin_file, self::$version );
	}

	/**
	 * Setup plugin before it runs. Include functions and instantiate classes based on user action
	 */
	private function setup_plugin() {

		$action = AMCR_Utilities::get('action');

		// process action request if any
		if ( ! empty($action) ) {
			$this->handle_action_request( $action );
		}

		// handle AJAX front & back-end requests (no admin, no admin bar)
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->handle_ajax_requests( $action );
			return;
		}

		// ADMIN or CLI
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->setup_backend_classes();
			return;
		}

		// FRONT-END (no ajax, possibly admin bar)
	}

	/**
	 * Handle plugin actions here such as saving settings
	 * @param $action
	 */
	private function handle_action_request( $action ) {

		if ( empty($action) || ! AMCR_KB_Handler::is_kb_request() ) {
			return;
		}
	}

	/**
	 * Handle AJAX requests coming from front-end and back-end
	 * @param $action
	 */
	private function handle_ajax_requests( $action ) {

        if ( empty($action) ) {
            return;
        }

		// handle WP Custom Roless
		if ( in_array($action, array( 'amcr_display_wp_roles_tabs_ajax', 'amcr_add_wp_role_ajax', 'amcr_delete_wp_role_ajax', 'amcr_reset_logs_ajax' ) ) ) {
			new AMCR_Access_Page_Cntrl_WP_Roles();
			return;
		}

		if ( $action == 'amcr_handle_license_request' ) {
			new AMCR_Add_Ons_Page();
			return;
		}
	}

	/**
	 * Setup up classes when on ADMIN pages
	 */
	private function setup_backend_classes() {
		global $pagenow, $post;

		$request_page = AMCR_Utilities::post('page');

		// only process KB pages
		if ( $pagenow == 'post.php' ) {
			$post_id = AMCR_Utilities::get('post');
			$post_id = empty($post_id) ? AMCR_Utilities::post('post_ID', 0, false) : $post_id;
			$post = AMCR_Utilities::get_kb_post_secure( $post_id, false );
			$is_kb_request = ! empty( $post );
		} else {
			$is_kb_request = AMCR_KB_Handler::is_kb_request();
		}

		if ( empty($is_kb_request) ) {
			return;
		}

		// include our admin scripts on our admin pages (submenus of KB menu)
		add_action( 'admin_enqueue_scripts', 'amcr_load_admin_plugin_pages_resources' );
		new AMCR_Admin_Notices();

		// admin other classes
		$classes = array(
			// class name, backend/admin-bar, pages, admin_action, KB post type required
			array( 'AMCR_Add_Ons_Page', array('backend'), array('edit.php', AMCR_KB_Core::AMCR_KB_ADD_ONS_PAGE), array(''), true ),
			array( 'AMCR_Admin_Articles_Page', array('backend'), array('post-new.php'), array(''), true ),   // initial Add article screen
			array( 'AMCR_Admin_Articles_Page', array('backend'), array('post.php'), array('edit'), false ),  // after Add submit or Article Edit (edit)
			array( 'AMCR_Admin_Articles_Page', array('backend'), array('edit.php'), array('edit'), false ),  // after Add submit or Article Edit (edit)
			array( 'AMCR_Access_Page_View_WP_Roles', array('backend'), array('amag-access-mgr', ''), array(''), true ),
        );

		$current_page = empty($request_page) ? $pagenow : $request_page;
		$action = AMCR_Utilities::get('action');
		foreach( $classes as $class_info ) {

			if ( $class_info[4] && ! $is_kb_request ) {
				continue;
			}

			// INDIVIDUAL PAGES: if feature available on a specific page then ensure the page is being loaded
			if ( ( ! in_array($current_page, $class_info[2]) ) &&
			     ( empty($class_info[3]) || empty($action) || ! in_array($action, $class_info[3]) ) ) {
				continue;
			}

			$new_class = $class_info[0];
			if ( class_exists($new_class) ) {
				new $new_class();
			}
		}
	}

	/**
	/**
	 * Loads the plugin language files
	 *
	 * Note: the first-loaded translation file overrides any following files if they both have the same translation
	 */
	public function load_text_domain() {
		global $wp_version;

		// Set filter for plugin's languages directory
		$plugin_lang_dir = ( defined('AMAG_PLUGIN_NAME') ? 'echo-kb-access-manager' : 'echo-knowledge-base' ) . '/languages/';
		$plugin_lang_dir = apply_filters( 'amcr_wp_languages_directory', $plugin_lang_dir );

		// Traditional WordPress plugin locale filter
		$user_locale = $wp_version >= 4.7 && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $user_locale, 'echo-knowledge-base' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'echo-knowledge-base', $locale );

		// Setup paths to current locale file
		$mofile_local  = $plugin_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

		// does WP provide language pack?  (only 100% translated packs will be downloaded ??)
		if ( file_exists( $mofile_global ) ) {
			// in global /wp-content/languages/<plugin-name>/ folder
			load_textdomain( 'echo-knowledge-base', $mofile_global );
		}

		// complement with our own language packs
		if ( file_exists( WP_PLUGIN_DIR . '/' . $mofile_local ) ) {
			// in /wp-content/plugins/<plugin-name>/languages/ folder
			load_plugin_textdomain( 'echo-knowledge-base', false, $plugin_lang_dir );
		}
	}

	// Don't allow this singleton to be cloned.
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, 'Invalid (#1)', '4.0' );
	}

	// Don't allow un-serializing of the class except when testing
	public function __wakeup() {
		if ( strpos($GLOBALS['argv'][0], 'phpunit') === false ) {
			_doing_it_wrong( __FUNCTION__, 'Invalid (#1)', '4.0' );
		}
	}
}

// for add-on we need to register installation scripts now
require_once Echo_Custom_roles::$plugin_dir . 'includes/system/plugin-setup.php';

/**
 * Returns the single instance of this class
 *
 * @return String|Echo_Custom_roles - this class instance
 */
function amcr_get_instance() {

	$plugins_check = amcr_check_plugins_versions();

	// if KB Core plugin is not active then do not run
	if ( $plugins_check == 'core_missing' ) {
		add_action( 'admin_notices', 'amcr_display_version_error' );
		return '';
	}

	// we need core Knowledge Base plugin in order to run this plugin
	if ( $plugins_check != 'core_too_old' && $plugins_check != 'core_too_new' ) {
		return Echo_Custom_roles::instance();
	}

	// Show admin screen top notification
	add_action( 'admin_notices', 'amcr_display_version_error' );

	// load instance but in 'dormant' state
	return Echo_Custom_roles::instance( true );
}
// ensure this happens after main KB plugin is loaded
add_action( 'plugins_loaded', 'amcr_get_instance' );

/**
 * Show admin screen top notification
 */
function amcr_display_version_error() {

	$kb_post_prefix = 'ep' . 'kb_post_type';
	$kb_plugin_name = 'Access Manager';

	// only show messages on KB pages (will not show CORE missing messages)
	if ( ! isset($_REQUEST['post_type']) || strncmp($_REQUEST['post_type'] , $kb_post_prefix, strlen($kb_post_prefix)) !== 0 ) {
		return;
	}

	$plugins_check = amcr_check_plugins_versions();

	// if KB Core plugin is not installed or active then let user know
	if ( $plugins_check == 'core_missing' ) {
		echo '<div class="error amcr-notice"><p>Custom Roles requires <a href="https://wordpress.org/plugins/echo-knowledge-base/"
		                title="' . $kb_plugin_name . '" target="_blank">' . $kb_plugin_name . '</a>. Please install it to continue.</p></div>';
		return;
	}

	// compare core and add-on versions
	if ( $plugins_check == 'core_too_old' ) {
		echo '<div class="error amcr-notice">Custom Roles plugin requires at least ' . Echo_Custom_roles::$needs_min_core_version .
		     ' version of the ' . $kb_plugin_name . ' plugin.	To continue please update ' . $kb_plugin_name . '.</p></div>';

    } else if ( $plugins_check == 'core_too_new' ) {
	    $add_on_url = admin_url('edit.php?post_type=' . $kb_post_prefix . '_1&page=ep'.'kb-add-ons&ep'.'kb-tab=licenses');
		echo '<div class="error amcr-notice">' . $kb_plugin_name . ' plugin requires the most recent version of the Custom Roles plugin. The old version of Custom Roles stays
			<strong>disabled</strong> until updated. <a href="' . $add_on_url .'" target="_blank">Check its status here.</a></p></div>';
	} else {
	    // should not happen
	    echo '<div class="error amcr-notice">Custom Roles: unknown add-on version conflict.</div>';
    }
}

/**
 * Check version of this add-on against core plugin and report differences.
 *
 * @return string
 */
function amcr_check_plugins_versions() {

	// if KB Core plugin is not active then do not run
	if ( ! class_exists( 'Echo_' . 'Knowledge_Base', false ) || empty(Echo_Knowledge_Base::$amag_version) ) {
		return 'core_missing';
	}

	$min_add_on_version = isset(Echo_Knowledge_Base::$needs_min_add_on_version) && isset(Echo_Knowledge_Base::$needs_min_add_on_version['MCR'])
										? Echo_Knowledge_Base::$needs_min_add_on_version['MCR'] : '1.0.0';

	if ( version_compare(Echo_Knowledge_Base::$amag_version, Echo_Custom_roles::$needs_min_core_version, '<' ) ) {
		return 'core_too_old';
	} else if ( version_compare(Echo_Custom_roles::$version, $min_add_on_version, '<' ) ) {
		return 'core_too_new';
	}

	return '';
}
