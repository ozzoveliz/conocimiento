<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'AMGR_Upgrades', 'update_plugin_version' ) );
		add_filter( 'eckb_plugin_upgrade_message', array( 'AMGR_Upgrades', 'display_upgrade_message' ) );
        add_action( 'eckb_remove_upgrade_message', array( 'AMGR_Upgrades', 'remove_upgrade_message' ) );
	}

    /**
     * If necessary run plugin database updates
     */
    public static function update_plugin_version() {

        $last_version = EPKB_Utilities::get_wp_option( 'amag_version', null );
		if ( empty($last_version) ) {
			EPKB_Utilities::save_wp_option( 'amag_version', Echo_Knowledge_Base::$amag_version );
			return;
		}

		// if plugin is up-to-date then return
        if ( version_compare( $last_version, Echo_Knowledge_Base::$amag_version, '>=' ) ) {
            return;
        }

		// since we need to upgrade this plugin, on the kb Page show an upgrade message
	    EPKB_Utilities::save_wp_option( 'amgr_show_upgrade_message', true );

        // upgrade the plugin
        self::invoke_upgrades( $last_version );

        // update the plugin version
        $result = EPKB_Utilities::save_wp_option( 'amag_version', Echo_Knowledge_Base::$amag_version );
        if ( is_wp_error( $result ) ) {
	        AMGR_Logging::add_log( 'Could not update plugin version', $result );
            return;
        }
    }

    /**
     * Invoke each database update as necessary.
     *
     * @param $last_version
     */
    private static function invoke_upgrades( $last_version ) {

        // update all KBs
	    $update_config = false;
        $all_kb_ids = epkb_get_instance()->kb_config_obj->get_kb_ids();
        foreach ( $all_kb_ids as $kb_id ) {

			$amgr_config = epkb_get_instance()->kb_access_config_obj->get_kb_config_or_default( $kb_id );

			$update_config = self::run_upgrade( $amgr_config, $last_version );

	        // store the updated KB data
	        if ( $update_config ) {
            	epkb_get_instance()->kb_access_config_obj->update_kb_configuration( $kb_id, $amgr_config );
	        }
        }
    }

	public static function run_upgrade( &$amgr_config, $last_version ) {

		$update_config = false;

		if ( version_compare( $last_version, '6.11.0', '<' ) ) {
			self::upgrade_to_v6_11_0( $amgr_config );
			$update_config = true;
		}

		return $update_config;
	}

	private static function upgrade_to_v6_11_0( &$amgr_config ) {
		if ( $amgr_config['no_access_text'] == esc_html__( 'You do not have permission to access this content. You will be forwarded to a login screen.', 'echo-knowledge-base' ) ) {
			$amgr_config['no_access_text_logged'] = esc_html__( 'You do not have permission to access this content.', 'echo-knowledge-base' );
		} else {
			$amgr_config['no_access_text_logged'] = $amgr_config['no_access_text'];
		}
	}

    /**
     * Show upgrade message on kb Page.
     *
     * @param $output
     * @return string
     */
	public static function display_upgrade_message( $output ) {

		if ( EPKB_Utilities::get_wp_option( 'amgr_show_upgrade_message', false ) ) {

			$plugin_name = '<strong>' . esc_html__('Access Manager', 'echo-knowledge-base') . '</strong>';
			$output .= '<p>' . $plugin_name . ' ' . sprintf( esc_html( _x( 'add-on was updated to version %s.',
									' ', 'echo-knowledge-base' ) ),
									Echo_Knowledge_Base::$amag_version ,'') . '</p>';
		}

		return $output;
	}
    
    public static function remove_upgrade_message() {
        delete_option('amgr_show_upgrade_message');
    }
}
