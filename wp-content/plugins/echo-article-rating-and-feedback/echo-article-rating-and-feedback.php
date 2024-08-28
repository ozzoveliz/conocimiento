<?php
/**
 * Plugin Name: KB - Article Rating and Feedback
 * Plugin URI: https://www.echoknowledgebase.com/wordpress-add-ons/
 * Description: allow reader of your articles to vote on article quality and provide feedback.
 * Version: 2.0.4
 * Author: Echo Plugins
 * Author URI: https://www.echoknowledgebase.com
 * Text Domain: echo-article-rating-and-feedback
 * Domain Path: /languages
 * Copyright: (c) 2018 Echo Plugins
 *
 * KB - Article Rating and Feedback is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * KB - Article Rating and Feedback is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with KB - Article Rating and Feedback. If not, see <http://www.gnu.org/licenses/>.
 *
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// plugin name; used in KB core
if ( ! defined( 'EPRF_PLUGIN_NAME' ) ) {
	define( 'EPRF_PLUGIN_NAME', 'KB - Article Rating and Feedback' );
}

/**
 * Main class to load the plugin.
 *
 * Singleton
 */
final class Echo_Article_Rating_And_Feedback {

	/* @var Echo_Article_Rating_And_Feedback */
	private static $instance;

	public static $version = '2.0.4';
	public static $plugin_dir;
	public static $plugin_url;
	public static $plugin_file = __FILE__;
	public static $needs_min_core_version = '7.2.0';

	/* @var EPRF_KB_Config_DB */
	public $kb_config_obj;
	
	/* @var EPRF_License_Handler */
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
	 * @return Echo_Article_Rating_And_Feedback
	 */
	public static function instance( $dormant=false ) {

		if ( isset( self::$instance ) || self::$instance instanceof Echo_Article_Rating_And_Feedback  ) {
			return self::$instance;
		}

		self::$instance = new Echo_Article_Rating_And_Feedback();

		// if the plugin is not active, still show update messages and license to enter/save
		if ( $dormant === true ) {
			self::$instance->setup_dormant_system();
			return '';
		}

		self::$instance->setup_system();
		self::$instance->setup_plugin();

		add_action( 'plugins_loaded', array( self::$instance, 'load_text_domain' ), 11 );

		return self::$instance;
	}

	/**
	 * If add-on is not active because it was de-activated or is not matching with core then run only
	 * license related functions
	 */
	private function setup_dormant_system() {
		$this->is_dormant = true;
		require_once self::$plugin_dir . 'includes/system/class-eprf-autoloader.php';
		self::$instance->kb_config_obj = new EPRF_KB_Config_DB();
		new EPRF_Add_Ons_Page();
		require_once self::$plugin_dir . 'includes/system/scripts-registration.php';
		add_action( 'admin_enqueue_scripts', 'eprf_load_admin_plugin_pages_resources' );
		self::instance()->license_handler = new EPRF_License_Handler( self::$plugin_file, self::$version );
	}

	/**
	 * Setup class autoloading and other support functions. Setup custom core features.
	 */
	private function setup_system() {

		// autoload classes ONLY when needed by executed code rather than on every page request
		require_once self::$plugin_dir . 'includes/system/class-eprf-autoloader.php';

		// register settings
		self::$instance->kb_config_obj = new EPRF_KB_Config_DB();

		// load non-classes
		require_once self::$plugin_dir . 'includes/system/scripts-registration.php';
		require_once self::$plugin_dir . 'includes/system/plugin-links.php';

		new EPRF_Upgrades();
		new EPRF_KB_Config_Controller();

		new EPRF_Rating_Comments();

		// invoke last
		self::instance()->license_handler = new EPRF_License_Handler( self::$plugin_file, self::$version );
	}

	/**
	 * Setup plugin before it runs. Include functions and instantiate classes based on user action
	 */
	private function setup_plugin() {

		$action = EPRF_Utilities::get('action');

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
		if ( ( empty( $_REQUEST['epkb-editor-backend-mode'] ) && is_admin() ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->setup_backend_classes();
			return;
		}

		// FRONT-END (no ajax, possibly admin bar)
		new EPRF_Rating_View();
		EPRF_KB_Editor_Config::register_editor_hooks();
	}

	/**
	 * Handle plugin actions here such as saving settings
	 * @param $action
	 */
	private function handle_action_request( $action ) {
		if ( in_array( $action, [ 'eckb_apply_editor_changes', 'epkb_apply_settings_changes' ] ) ) {
			EPRF_KB_Editor_Config::register_editor_hooks();
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
		
		if ( $action == 'eprf-update-rating' || $action == 'eprf-add-comment' ) {
			new EPRF_Rating_Cntrl();
			return;
		}

		if ( $action == 'eprf_handle_rating_analytics' ) {
			new EPRF_Analytics_View();
			return;
		}

		if ( $action == 'eprf_handle_license_request' ) {
			new EPRF_Add_Ons_Page();
			return;
		}
		
		if ( $action == 'eprf_handle_reset_article_feedback' ) {
			new EPRF_Rating_Admin_Cntrl();
			return;
		}

		if ( in_array( $action, array(EPRF_KB_Core::EPRF_APPLY_WIZARD_CHANGES, EPRF_KB_Core::EPRF_UPDATE_KB_WIZARD_ORDER_VIEW) ) ) {
			new EPRF_Rating_View();
			return;
		}
	}

	/**
	 * Setup up classes when on ADMIN pages
	 */
	private function setup_backend_classes() {
		global $pagenow, $post;

		new EPRF_Rating_Admin_Cntrl();

		$request_page = EPRF_Utilities::post('page');

		// only process KB pages
		if ( $pagenow == 'post.php' ) {
			$post_id = EPRF_Utilities::get('post');
			$post_id = empty($post_id) ? EPRF_Utilities::post('post_ID', false) : $post_id;
			$post = EPRF_Core_Utilities::get_kb_post_secure( $post_id );
			$is_kb_request = ! empty( $post );
		} else {
			$is_kb_request = EPRF_KB_Handler::is_kb_request();
		}

		if ( empty($is_kb_request) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', 'eprf_load_admin_plugin_pages_resources' );
		new EPRF_Admin_Notices();
		new EPRF_Rating_Admin_View();
		new EPRF_Rating_View();

		// KB Config page needs front-page resources
		if ( $request_page == EPRF_KB_Core::EPRF_KB_CONFIGURATION_PAGE ) {
			add_action( 'admin_enqueue_scripts', 'eprf_kb_config_load_public_css' );
		}

		// admin other classes
		require_once self::$plugin_dir . 'includes/admin/admin-functions.php';
		$classes = array(
			// class name=0, loading=1 (backend/admin-bar), pages=2, admin_action=3, KB post type required=4
			array( 'EPRF_Add_Ons_Page', array('backend'), array('edit.php', EPRF_KB_Core::EPRF_KB_ADD_ONS_PAGE), array(''), true ),
			array( 'EPRF_Analytics_View', array('backend'), array(EPRF_KB_Core::EPRF_KB_ANALYTICS_PAGE), array(''), true ),
		);

		$current_page = empty($request_page) ? $pagenow : $request_page;
		$action = EPRF_Utilities::get('action');
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
	 * Loads the plugin language files from ./languages directory.
	 */
	public function load_text_domain() {
		load_plugin_textdomain( 'echo-article-rating-and-feedback', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
require_once Echo_Article_Rating_And_Feedback::$plugin_dir . 'includes/system/plugin-setup.php';

/**
 * Returns the single instance of this class
 * @return Echo_Article_Rating_And_Feedback - this class instance
 */
function eprf_get_instance() {

	if ( defined('E'.'ART_PLUGIN_NAME') ) {
		return;
	}

	$plugins_check = eprf_check_plugins_versions();

	// if KB Core plugin is not active then do not run
	if ( $plugins_check == 'core_missing' ) {
		add_action( 'admin_notices', 'eprf_display_version_error' );
		return;
	}

	// we need core Knowledge Base plugin in order to run this plugin
	if ( $plugins_check != 'core_too_old' && $plugins_check != 'core_too_new' ) {
		return Echo_Article_Rating_And_Feedback::instance();
	}

	// Show admin screen top notification
	add_action( 'admin_notices', 'eprf_display_version_error' );

	// load instance but in 'dormant' state
	return Echo_Article_Rating_And_Feedback::instance( true );
}
// ensure this happens after the main KB plugin is loaded
add_action( 'plugins_loaded', 'eprf_get_instance' );

/**
 * Show admin screen top notification
 */
function eprf_display_version_error() {

	$kb_post_prefix = 'ep' . 'kb_post_type';
	$kb_plugin_name = defined( 'AMAG_PLUGIN_NAME' ) ? AMAG_PLUGIN_NAME : 'Knowledge Base for Documents and FAQs';
	$plugins_check = eprf_check_plugins_versions();

	// if KB Core plugin is not installed or active then let user know
	if ( $plugins_check == 'core_missing' ) {
		echo '<div class="error eprf-notice"><p>KB - Article Rating and Feedback requires <a href="https://wordpress.org/plugins/echo-knowledge-base/"
		                title="' . $kb_plugin_name . '" target="_blank">' . $kb_plugin_name . '</a>. Please install it to continue.</p></div>';
		return;
	}

    // only show messages on KB pages
    if ( ! isset($_REQUEST['post_type']) || strncmp($_REQUEST['post_type'] , $kb_post_prefix, strlen($kb_post_prefix)) !== 0 ) {
        return;
    }

	// compare core and add-on versions
	if ( $plugins_check == 'core_too_old' ) {
		echo '<div class="error eprf-notice">Article Rating and Feedback plugin requires at least ' . Echo_Article_Rating_And_Feedback::$needs_min_core_version .
		     ' version of the ' . $kb_plugin_name . ' plugin.	To continue please update ' . $kb_plugin_name . '.</p></div>';

    } else if ( $plugins_check == 'core_too_new' ) {
	    $add_on_url = admin_url('edit.php?post_type=' . $kb_post_prefix . '_1&page=ep'.'kb-add-ons&ep'.'kb-tab=licenses');
	    echo '<div class="error eprf-notice">' . $kb_plugin_name . ' plugin requires the most recent version of the Article Rating and Feedback plugin. The old version of Article Rating and Feedback stays
			<strong>disabled</strong> until updated. <a href="' . $add_on_url .'" target="_blank">Check its status here.</a></p></div>';
	} else {
	    // should not happen
	    echo '<div class="error eprf-notice">Article Rating and Feedback: unknown add-on version conflict.</div>';
    }
}

/**
 * Check version of this add-on against core plugin and report differences.
 *
 * @return string
 */
function eprf_check_plugins_versions() {

	// if KB Core plugin is not active then do not run
	if ( ! class_exists('Echo_'.'Knowledge_Base', false) ) {
		return 'core_missing';
	}

	$min_add_on_version = isset(Echo_Knowledge_Base::$needs_min_add_on_version) && isset(Echo_Knowledge_Base::$needs_min_add_on_version['PRF'])
										? Echo_Knowledge_Base::$needs_min_add_on_version['PRF'] : '1.0.0';

	if ( version_compare(Echo_Knowledge_Base::$version, Echo_Article_Rating_And_Feedback::$needs_min_core_version, '<' ) ) {
		return 'core_too_old';
	} else if ( version_compare( Echo_Article_Rating_And_Feedback::$version, $min_add_on_version, '<' ) ) {
		return 'core_too_new';
	}

	return '';
}

