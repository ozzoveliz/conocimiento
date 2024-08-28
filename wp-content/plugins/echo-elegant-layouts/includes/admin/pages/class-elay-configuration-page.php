<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * KB Configuration page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ELAY_Configuration_Page {

	public function __construct() {
		add_action( ELAY_KB_Core::ELAY_CONFIG_SIDEBAR_INTRO_SETTINGS, array( 'ELAY_Configuration_Page', 'add_config_page_intro_text' ), 10, 2 );
		add_action( 'wp_ajax_' . ELAY_KB_Core::ELAY_SAVE_SIDEBAR_INTRO_TEXT, array( 'ELAY_Configuration_Page', 'save_sidebar_intro_text' ) );
		add_action( 'wp_ajax_nopriv_' . ELAY_KB_Core::ELAY_SAVE_SIDEBAR_INTRO_TEXT, array( 'ELAY_Configuration_Page', 'save_sidebar_intro_text' ) );
	}

	/**
	 * Add wp editor to Configuration page / Settings / Other settings
	 *
	 * @param $unsused
	 * @param $kb_config
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection*/
	public static function add_config_page_intro_text( $unsused, $kb_config ) {

		if ( $kb_config['kb_main_page_layout'] != ELAY_Layout::SIDEBAR_LAYOUT ) {
			return $unsused;
		}

		$boxes_list = array(
			'title' => __( 'Sidebar Intro Text', 'echo-elegant-layouts' ),
			'html' => self::show_sidebar_intro_editor( $kb_config ),
			'minimum_required_capability_context' => 'admin_eckb_access_frontend_editor_write'
		);
		
		return $boxes_list;
	}
	
	private static function show_sidebar_intro_editor( $kb_config ) {
		ob_start();
		
		$elay_config = elay_get_instance()->kb_config_obj->get_kb_config( $kb_config['id'] ); ?>
		
		<form id="elay-config-page__intro-settings">
			<input type="hidden" id="elay-kb-id" value="<?php echo $kb_config['id']; ?>">
			<?php wp_editor( $elay_config['sidebar_main_page_intro_text'], 'wpeditor', array('textarea_name' => 'sidebar_main_page_intro_text') );
			ELAY_KB_Core::submit_button_v2( __( 'Save', 'echo-elegant-layouts' ), 'save_sidebar_intro_text', '', '', true, false, 'epkb-aibb-btn epkb-aibb-btn--blue' ); ?>
		</form><?php
		
		return ob_get_clean();
	}
	
	/**
	 * Save intro text on kb config page 
	 */
	public static function save_sidebar_intro_text() {

		ELAY_Utilities::ajax_verify_nonce_and_admin_permission_or_error_die( ELAY_KB_Core::ELAY_KB_ACCESS_FRONTEND_EDITOR_WRITE );

		// retrieve intro text
		$intro_text = ELAY_Utilities::post( 'sidebar_main_page_intro_text', '', 'wp_editor' );

		// retrieve KB id
		$kb_id = ELAY_Utilities::post( 'elay_kb_id', ELAY_KB_Config_DB::DEFAULT_KB_ID );
		if ( ! ELAY_Utilities::is_positive_int( $kb_id ) ) {
			ELAY_Logging::add_log( 'Error occurred', $kb_id );
			ELAY_Utilities::ajax_show_error_die(__( 'Error occurred', 'echo-knowledge-base' ) . ' (30)');
		}

		$result = elay_get_instance()->kb_config_obj->set_value( $kb_id, 'sidebar_main_page_intro_text', $intro_text );
		if ( is_wp_error( $result ) ) {
			ELAY_Logging::add_log( 'Error occurred', $result );
			ELAY_Utilities::ajax_show_error_die(__( 'Error occurred', 'echo-knowledge-base' ) . ' (31)');
		}

		wp_die( wp_json_encode( array( 'status' => 'success', 'message' => __( 'Configuration Saved', 'echo-knowledge-base') ) ) );
	}
}
