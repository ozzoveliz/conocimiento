<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if plugin upgrade to a new version requires any actions like database upgrade
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_Upgrades {

	public function __construct() {
        // will run after plugin is updated but not always like front-end rendering
		add_action( 'admin_init', array( 'ELAY_Upgrades', 'update_plugin_version' ) );
	}

	/**
	 * If necessary run plugin database updates
	 */
	public static function update_plugin_version() {

		// ensure the plugin version and configuration is set
		$last_version = ELAY_Utilities::get_wp_option( 'elay_version', null );
		if ( empty( $last_version ) ) {
			ELAY_Utilities::save_wp_option( 'elay_version', Echo_Elegant_Layouts::$version ) ;
			return;
		}

		$kb_config = elay_get_instance()->kb_config_obj->get_kb_config( ELAY_KB_Config_DB::DEFAULT_KB_ID, true );
		if ( is_wp_error( $kb_config ) ) {
			// TODO report error in admin page
			return;
		}
		
		// initialize plugin upgraded version if empty or not initialized
		$last_upgrade_version = $kb_config['elay_upgrade_plugin_version'];
		if ( empty( $last_upgrade_version ) ) {
			$last_upgrade_version = $last_version;
			elay_get_instance()->kb_config_obj->set_value( ELAY_KB_Config_DB::DEFAULT_KB_ID, 'elay_upgrade_plugin_version', $last_upgrade_version );
		}

		// if plugin is up-to-date then return
		if ( version_compare( $last_upgrade_version, Echo_Elegant_Layouts::$version, '>=' ) ) {
			return;
		}

		// upgrade the plugin
		self::invoke_upgrades( $last_upgrade_version );

		ELAY_Utilities::save_wp_option( 'elay_version', Echo_Elegant_Layouts::$version );
	}

	/**
	 * Invoke each database update as necessary.
	 *
	 * @param $last_version
	 */
	private static function invoke_upgrades( $last_version ) {

		// update all KBs
		$all_kb_configs = elay_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_config ) {

			self::run_upgrade( $kb_config, $last_version );

			$kb_config['elay_upgrade_plugin_version'] = Echo_Elegant_Layouts::$version;

			// store the updated KB data
			elay_get_instance()->kb_config_obj->update_kb_configuration( $kb_config['id'], $kb_config );
		}
	}

	public static function run_upgrade( &$add_on_config, $last_version ) {

		if ( version_compare( $last_version, '2.8.0', '<' ) ) {
			self::upgrade_to_v2_8_0( $add_on_config );
		}

		if ( version_compare( $last_version, '2.23.1', '<' ) ) {
			self::upgrade_to_v2_23_1( $add_on_config );
		}
	}

	private static function upgrade_to_v2_23_1( &$kb_config ) {
		$kb_config['grid_section_body_padding_bottom'] = 20;        // add back padding that was removed from hard-coded CSS
	}

	private static function upgrade_to_v2_8_0( &$add_on_config ) {

		if ( ! empty($add_on_config['grid_section_font_size']) ) {

			switch ( $add_on_config['grid_section_font_size'] ) {
				case 'section_xsmall_font':
					$grid_section_typography = '15';
					$grid_section_description_typography = '12';
					$grid_section_article_typography = '10';
					break;
				case 'section_small_font':
					$grid_section_typography = '18';
					$grid_section_description_typography = '14';
					$grid_section_article_typography = '12';
					break;
				case 'section_medium_font':
					$grid_section_typography = '21';
					$grid_section_description_typography = '17';
					$grid_section_article_typography = '14';
					break;
				case 'section_large_font':
					$grid_section_typography = '24';
					$grid_section_description_typography = '19';
					$grid_section_article_typography = '16';
					break;
				default:
					$grid_section_typography = '21';
					$grid_section_description_typography = '16';
					$grid_section_article_typography = '12';
					break;
			}

			$add_on_config['grid_section_typography'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $grid_section_typography ) );
			$add_on_config['grid_section_description_typography'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $grid_section_description_typography ) );
			$add_on_config['grid_section_article_typography'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $grid_section_article_typography ) );
		}

		if ( ! empty($add_on_config['sidebar_section_font_size']) ) {

			switch ( $add_on_config['sidebar_section_font_size'] ) {
				case 'section_xsmall_font':
					$sidebar_section_category_typography        = '13';
					$sidebar_section_category_typography_desc   = '10';
					$sidebar_section_body_typography            = '10';
					break;
				case 'section_small_font':
					$sidebar_section_category_typography = '16';
					$sidebar_section_category_typography_desc = '12';
					$sidebar_section_body_typography = '12';
					break;
				case 'section_medium_font':
					$sidebar_section_category_typography = '18';
					$sidebar_section_category_typography_desc = '14';
					$sidebar_section_body_typography = '14';
					break;
				case 'section_large_font':
					$sidebar_section_category_typography = '21';
					$sidebar_section_category_typography_desc = '16';
					$sidebar_section_body_typography = '16';
					break;
				default:
					$sidebar_section_category_typography = '18';
					$sidebar_section_category_typography_desc = '14';
					$sidebar_section_body_typography = '14';
					break;

			}

			$add_on_config['sidebar_section_category_typography'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $sidebar_section_category_typography ) );
			$add_on_config['sidebar_section_category_typography_desc'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $sidebar_section_category_typography_desc ) );
			$add_on_config['sidebar_section_body_typography'] = array_merge( ELAY_Typography::$typography_defaults, array( 'font-size' => $sidebar_section_body_typography ) );
		}
	}
}
