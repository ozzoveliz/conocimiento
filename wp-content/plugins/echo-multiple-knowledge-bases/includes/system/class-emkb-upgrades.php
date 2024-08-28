<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EMKB_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'EMKB_Upgrades', 'update_plugin_version' ) );
		add_filter( 'eckb_plugin_upgrade_message', array( 'EMKB_Upgrades', 'display_upgrade_message' ) );
        add_action( 'eckb_remove_upgrade_message', array( 'EMKB_Upgrades', 'remove_upgrade_message' ) );
	}

    /**
     * If necessary run plugin database updates
     */
    public static function update_plugin_version() {

        $last_version = EMKB_Utilities::get_wp_option( 'emkb_version', null );

        // fix empty version
		if ( empty($last_version) ) {
			EMKB_Utilities::save_wp_option( 'emkb_version', Echo_Multiple_Knowledge_Bases::$version, true );
			return;
		}

        // if plugin is up-to-date then return
        if ( version_compare( $last_version, Echo_Multiple_Knowledge_Bases::$version, '>=' ) ) {
            return;
        }

		// since we need to upgrade this plugin, on the Overview Page show an upgrade message
	    EMKB_Utilities::save_wp_option( 'emkb_show_upgrade_message', true, true );

        // upgrade the plugin
       //  self::invoke_upgrades( $last_version );

        // update the plugin version
        $result = EMKB_Utilities::save_wp_option( 'emkb_version', Echo_Multiple_Knowledge_Bases::$version, true );
        if ( is_wp_error( $result ) ) {
	        EMKB_Logging::add_log( 'Could not update plugin version', $result );
            return;
        }
    }

    /**
     * Show upgrade message on Overview Page.
     *
     * @param $output
     * @return string
     */
	public static function display_upgrade_message( $output ) {

		if ( EMKB_Utilities::get_wp_option( 'emkb_show_upgrade_message', false ) ) {

			$plugin_name = '<strong>' . __('Multiple KBs', 'echo-knowledge-base') . '</strong>';
			$output .= '<p>' . $plugin_name . ' ' . sprintf( esc_html( _x( 'add-on was updated to version %s.',
									' version number, link to what is new page', 'echo-knowledge-base' ) ),
									Echo_Multiple_Knowledge_Bases::$version ) . '</p>';
		}

		return $output;
	}
    
    public static function remove_upgrade_message() {
        delete_option('emkb_show_upgrade_message');
    }
}
