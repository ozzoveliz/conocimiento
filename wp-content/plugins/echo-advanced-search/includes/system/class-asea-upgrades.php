<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ASEA_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'ASEA_Upgrades', 'update_plugin_version' ) );
		add_filter( 'eckb_plugin_upgrade_message', array( 'ASEA_Upgrades', 'display_upgrade_message' ) );
        add_action( 'eckb_remove_upgrade_message', array( 'ASEA_Upgrades', 'remove_upgrade_message' ) );
	}

    /**
     * If necessary run plugin database updates
     */
    public static function update_plugin_version() {

        $last_version = ASEA_Utilities::get_wp_option( 'asea_version', null );

        // fix empty version
		if ( empty($last_version) ) {
			ASEA_Utilities::save_wp_option( 'asea_version', Echo_Advanced_Search::$version, true );
			return;
		}

        // if plugin is up-to-date then return
        if ( version_compare( $last_version, Echo_Advanced_Search::$version, '>=' ) ) {
            return;
        }

		// since we need to upgrade this plugin, on the Overview Page show an upgrade message
	    ASEA_Utilities::save_wp_option( 'asea_show_upgrade_message', true, true );

        // upgrade the plugin
        self::invoke_upgrades( $last_version );

        // update the plugin version
        $result = ASEA_Utilities::save_wp_option( 'asea_version', Echo_Advanced_Search::$version, true );
        if ( is_wp_error( $result ) ) {
	        ASEA_Logging::add_log( 'Could not update plugin version', $result );
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
        $all_kb_ids = asea_get_instance()->kb_config_obj->get_kb_ids();
        foreach ( $all_kb_ids as $kb_id ) {

	        $add_on_config = asea_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

	        $update_config = self::run_upgrade( $add_on_config, $last_version );

	        // store the updated KB data
	        if ( $update_config ) {
            	asea_get_instance()->kb_config_obj->update_kb_configuration( $kb_id, $add_on_config );
	        }
        }
    }

	public static function run_upgrade( &$add_on_config, $last_version ) {

		$update_config = false;

		if ( version_compare( $last_version, '2.11.0', '<' ) ) {
			self::upgrade_to_v2110( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '2.14.0', '<' ) ) {
			self::upgrade_to_v2140( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '2.15.0', '<' ) ) {
			self::upgrade_to_v2150( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '2.21.0', '<' ) ) {
			self::upgrade_to_v2210( $add_on_config );
			$update_config = true;
		}

		if ( version_compare( $last_version, '2.30.1', '<' ) ) {
			self::upgrade_to_v2301( $add_on_config );
			$update_config = true;
		}

		return $update_config;
	}

	/**
	 * Update ASEA config
	 * @param $add_on_config
	 */
	private static function upgrade_to_v2301( &$add_on_config ) {
		$add_on_config['search_query_param'] = 'kb-search';
	}

	/**
	 * Update ASEA config
	 * @param $add_on_config
	 */
	private static function upgrade_to_v2210( &$add_on_config ) {
		if ( ! empty($kb_config['advanced_search_ap_title_font_size']) ) {
			$add_on_config['advanced_search_ap_title_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_ap_title_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_mp_title_font_size']) ) {
			$add_on_config['advanced_search_mp_title_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_mp_title_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_mp_description_below_title_font_size']) ) {
			$add_on_config['advanced_search_mp_description_below_title_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_mp_description_below_title_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_ap_description_below_title_font_size']) ) {
			$add_on_config['advanced_search_ap_description_below_title_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_ap_description_below_title_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_mp_input_box_font_size']) ) {
			$add_on_config['advanced_search_mp_input_box_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_mp_input_box_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_ap_input_box_font_size']) ) {
			$add_on_config['advanced_search_ap_input_box_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_ap_input_box_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_mp_description_below_input_font_size']) ) {
			$add_on_config['advanced_search_mp_description_below_input_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_mp_description_below_input_font_size'] ) );
		}

		if ( ! empty($kb_config['advanced_search_ap_description_below_input_font_size']) ) {
			$add_on_config['advanced_search_ap_description_below_input_typography'] = array_merge( ASEA_Typography::$typography_defaults, array( 'font-size' => $add_on_config['advanced_search_ap_description_below_input_font_size'] ) );
		}

	}

	/**
	 * Update ASEA config
	 * @param $add_on_config
	 */
	private static function upgrade_to_v2150( &$add_on_config ) {
		/** @var $wpdb Wpdb */
		global $wpdb;
		$sql = "ALTER TABLE `" . $wpdb->prefix . "epkb_kb_search_data` MODIFY source varchar(50) NOT NULL";
		$wpdb->query($sql);

		// upgrade config for gradient from TEXT to NUMBER
		$add_on_config['advanced_search_mp_background_gradient_degree'] = is_numeric( $add_on_config['advanced_search_mp_background_gradient_degree'] ) ? intval( $add_on_config['advanced_search_mp_background_gradient_degree'] ) : '0';
		$add_on_config['advanced_search_mp_background_gradient_degree'] = empty($add_on_config['advanced_search_mp_background_gradient_degree'] ) ? '0' : $add_on_config['advanced_search_mp_background_gradient_degree'];

		$add_on_config['advanced_search_ap_background_gradient_degree'] = is_numeric( $add_on_config['advanced_search_ap_background_gradient_degree'] ) ? intval( $add_on_config['advanced_search_ap_background_gradient_degree'] ) : '0';
		$add_on_config['advanced_search_ap_background_gradient_degree'] = empty($add_on_config['advanced_search_ap_background_gradient_degree'] ) ? '0' : $add_on_config['advanced_search_ap_background_gradient_degree'];
	}

	/**
	 * Update ASEA config
	 * @param $add_on_config
	 */
	private static function upgrade_to_v2140( &$add_on_config ) {

		$title = isset($add_on_config['advanced_search_mp_title']) ? trim($add_on_config['advanced_search_mp_title']) : 'x';
		$add_on_config['advanced_search_mp_title_toggle'] = empty($title) ? 'off' : 'on';
		$title = isset($add_on_config['advanced_search_ap_title']) ? trim($add_on_config['advanced_search_ap_title']) : 'x';
		$add_on_config['advanced_search_ap_title_toggle'] = empty($title) ? 'off' : 'on';

		$title_below = isset($add_on_config['advanced_search_mp_description_below_title']) ? trim($add_on_config['advanced_search_mp_description_below_title']) : 'x';
		$add_on_config['advanced_search_mp_description_below_title_toggle'] = empty($title_below) ? 'off' : 'on';
		$title_below = isset($add_on_config['advanced_search_ap_description_below_title']) ? trim($add_on_config['advanced_search_ap_description_below_title']) : 'x';
		$add_on_config['advanced_search_ap_description_below_title_toggle'] = empty($title_below) ? 'off' : 'on';

		$input_below = isset($add_on_config['advanced_search_mp_description_below_input']) ? trim($add_on_config['advanced_search_mp_description_below_input']) : 'x';
		$add_on_config['advanced_search_mp_description_below_input_toggle'] = empty($input_below) ? 'off' : 'on';
		$input_below = isset($add_on_config['advanced_search_ap_description_below_input']) ? trim($add_on_config['advanced_search_ap_description_below_input']) : 'x';
		$add_on_config['advanced_search_ap_description_below_input_toggle'] = empty($input_below) ? 'off' : 'on';

	}

    /**
     * Change of minimum value for KB config.
     * @param $add_on_config
     */
    private static function upgrade_to_v2110( &$add_on_config ) {
        if ( $add_on_config['advanced_search_mp_filter_dropdown_width'] < 200 ) {
            $add_on_config['advanced_search_mp_filter_dropdown_width'] = 200;
        }
        if ( $add_on_config['advanced_search_ap_filter_dropdown_width'] < 200 ) {
            $add_on_config['advanced_search_ap_filter_dropdown_width'] = 200;
        }
    }

    /**
     * Show upgrade message on Overview Page.
     *
     * @param $output
     * @return string
     */
	public static function display_upgrade_message( $output ) {

		if ( ASEA_Utilities::get_wp_option( 'asea_show_upgrade_message', false ) ) {

			$plugin_name = '<strong>' . __('Advanced Search', 'echo-advanced-search') . '</strong>';
			$output .= '<p>' . $plugin_name . ' ' . sprintf( esc_html( _x( 'add-on was updated to version %s.',
									' version number, link to what is new page', 'echo-knowledge-base' ) ),
									Echo_Advanced_Search::$version ) . '</p>';
		}

		return $output;
	}
    
    public static function remove_upgrade_message() {
        delete_option('asea_show_upgrade_message');
    }
}
