<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGP_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'AMGP_Upgrades', 'update_plugin_version' ) );
		add_filter( 'eckb_plugin_upgrade_message', array( 'AMGP_Upgrades', 'display_upgrade_message' ) );
        add_action( 'eckb_remove_upgrade_message', array( 'AMGP_Upgrades', 'remove_upgrade_message' ) );
	}

    /**
     * If necessary run plugin database updates
     */
    public static function update_plugin_version() {

        $last_version = AMGP_Utilities::get_wp_option( 'amgp_version', null );

		if ( empty($last_version) ) {
			AMGP_Utilities::save_wp_option( 'amgp_version', Echo_KB_Groups::$version, true );
			return;
		}

        // if plugin is up-to-date then return
        if ( version_compare( $last_version, Echo_KB_Groups::$version, '>=' ) ) {
            return;
        }

		// since we need to upgrade this plugin, on the Overview Page show an upgrade message
	    AMGP_Utilities::save_wp_option( 'amgp_show_upgrade_message', true, true );

        // upgrade the plugin
        // self::invoke_upgrades( $last_version );

        // update the plugin version
        $result = AMGP_Utilities::save_wp_option( 'amgp_version', Echo_KB_Groups::$version, true );
        if ( is_wp_error( $result ) ) {
	        AMGP_Logging::add_log( 'Could not update plugin version', $result );
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
        $all_kb_ids = amgp_get_instance()->kb_config_obj->get_kb_ids();
        foreach ( $all_kb_ids as $kb_id ) {

	        $add_on_config = amgp_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

	        // store the updated KB data
	        if ( $update_config ) {
            	amgp_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $add_on_config );
	        }
        }
    }

    /**
     * Show upgrade message on Overview Page.
     *
     * @param $output
     * @return string
     */
	public static function display_upgrade_message( $output ) {

		if ( AMGP_Utilities::get_wp_option( 'amgp_show_upgrade_message', false ) ) {

			$plugin_name = '<strong>' . __('KB Groups', 'echo-knowledge-base') . '</strong>';
			$output .= '<p>' . $plugin_name . ' ' . sprintf( esc_html( _x( 'add-on was updated to version %s.',
									' version number, link to what is new page', 'echo-knowledge-base' ) ),
									Echo_KB_Groups::$version ) . '</p>';
		}

		return $output;
	}
    
    public static function remove_upgrade_message() {
        delete_option('amgp_show_upgrade_message');
    }
}
