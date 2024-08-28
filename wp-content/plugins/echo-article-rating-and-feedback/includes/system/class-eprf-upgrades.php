<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class EPRF_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'EPRF_Upgrades', 'update_plugin_version' ) );
		add_filter( 'eckb_plugin_upgrade_message', array( 'EPRF_Upgrades', 'display_upgrade_message' ) );
        add_action( 'eckb_remove_upgrade_message', array( 'EPRF_Upgrades', 'remove_upgrade_message' ) );
	}

    /**
     * If necessary run plugin database updates
     */
    public static function update_plugin_version() {

        $last_version = EPRF_Utilities::get_wp_option( 'eprf_version', null );

        // fix empty version
		if ( empty($last_version) ) {
			EPRF_Utilities::save_wp_option( 'eprf_version', Echo_Article_Rating_And_Feedback::$version, true );
			return;
		}


		// if plugin is up-to-date then return
        if ( version_compare( $last_version, Echo_Article_Rating_And_Feedback::$version, '>=' ) ) {
            return;
        }

		// since we need to upgrade this plugin, on the Overview Page show an upgrade message
	    EPRF_Utilities::save_wp_option( 'eprf_show_upgrade_message', true, true );

        // upgrade the plugin
        self::invoke_upgrades( $last_version );

        // update the plugin version
        $result = EPRF_Utilities::save_wp_option( 'eprf_version', Echo_Article_Rating_And_Feedback::$version, true );
        if ( is_wp_error( $result ) ) {
	        EPRF_Logging::add_log( 'Could not update plugin version', $result );
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
        $all_kb_ids = eprf_get_instance()->kb_config_obj->get_kb_ids();
        foreach ( $all_kb_ids as $kb_id ) {

	        $add_on_config = eprf_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

			$update_config = self::run_upgrade( $add_on_config, $last_version );

	        // store the updated KB data
	        if ( $update_config ) {
            	eprf_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $add_on_config );
	        }
        }
    }

	public static function run_upgrade( &$add_on_config, $last_version ) {

    	$update_config = false;

		if ( version_compare( $last_version, '1.4.0', '<' ) ) {
			self::upgrade_to_v140( $add_on_config );
			$update_config = true;
		}
		if ( version_compare( $last_version, '1.6.0', '<' ) ) {
			self::upgrade_to_v160( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '1.7.0', '<' ) ) {
			self::upgrade_to_v170( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '1.11.1', '<' ) ) {
			self::upgrade_to_v1111( $add_on_config );
			$update_config = true;
		}

		return $update_config;
	}

	private static function upgrade_to_v170( &$kb_config ) {
		if ( ! empty($kb_config['rating_text_font_size']) ) {
			$kb_config['rating_text_typography'] = array_merge( EPRF_Typography::$typography_defaults, array( 'font-size' => $kb_config['rating_text_font_size'] ) );
		}
	}

	private static function upgrade_to_v160( &$kb_config ) {
		$kb_config['rating_element_row'] = $kb_config['rating_element_location'] == 'rating-below-article' ? 'article-bottom' : '5';
		$kb_config['article_content_enable_rating_stats'] = $kb_config['rating_stats_header_toggle'];
	}

	private static function upgrade_to_v140( &$kb_config ) {

		// meta data
		if ( isset($kb_config['rating_stats_meta']) ) {
			$kb_config['rating_stats_header_toggle'] = $kb_config['rating_stats_meta'] == 'top' ? 'on' : 'off';
			$kb_config['rating_stats_footer_toggle'] = $kb_config['rating_stats_meta'] == 'bottom' ? 'on' : 'off';
		}
	}

	private static function upgrade_to_v1111( &$kb_config ) {
		if ( isset( $kb_config['rating_switch_off'] ) && $kb_config['rating_switch_off'] == 'on' ) {
			$kb_config['article_content_enable_rating_element'] = 'off';
		}
	}

    /**
     * Show upgrade message on Overview Page.
     *
     * @param $output
     * @return string
     */
	public static function display_upgrade_message( $output ) {

		if ( EPRF_Utilities::get_wp_option( 'eprf_show_upgrade_message', false ) ) {

			$plugin_name = '<strong>' . __('Article Rating and Feedback', 'echo-knowledge-base') . '</strong>';
			$output .= '<p>' . $plugin_name . ' ' . sprintf( esc_html( _x( 'add-on was updated to version %s.',
									' version number, link to what is new page', 'echo-knowledge-base' ) ),
									Echo_Article_Rating_And_Feedback::$version ) . '</p>';
		}

		return $output;
	}
    
    public static function remove_upgrade_message() {
        delete_option('eprf_show_upgrade_message');
    }
}
