<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Store Frontend Editor configuration data
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_KB_Editor_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	public static function register_editor_hooks() {
		add_filter( 'eckb_editor_fields_specs', array('ASEA_KB_Editor_Config', 'get_editor_fields_specs' ), 10, 2 );
		add_filter( 'eckb_all_editors_get_current_config', array('ASEA_KB_Editor_Config', 'get_current_config' ), 10, 2 );
		add_filter( 'eckb_editor_get_default_config', array('ASEA_KB_Editor_Config', 'get_configuration_defaults' ) );
		add_filter( 'eckb_editor_fields_config', array('ASEA_KB_Editor_Config', 'get_editor_fields_config' ), 10, 3 );
		add_filter( 'eckb_theme_wizard_get_themes_v2', array('ASEA_KB_Editor_Config', 'get_theme_config'), 30 );
		
		add_filter( 'epkb_front_end_editor_search_config', array('ASEA_KB_Editor_Config', 'get_front_end_editor_config' ), 10 );
	}

	/**
	 * Return to Editor add-onc specs
	 * @param $eckb_field_specification
	 * @param $kb_id
	 * @return array
	 */
	public static function get_editor_fields_specs( $eckb_field_specification, $kb_id ) {
		return array_merge( $eckb_field_specification, ASEA_KB_Config_Specs::get_fields_specification() );
	}

	/**
	 * Returnt to Wizard the current KB configuration
	 *
	 * @param $kb_config
	 * @param $kb_id
	 * @return array
	 */
	public static function get_current_config( $kb_config, $kb_id ) {
		$addon_config = asea_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $addon_config );
	}

	/**
	 * Return add-on configuration defaults.
	 *
	 * @param $template_defaults
	 * @return array
	 */
	public static function get_configuration_defaults( $template_defaults ) {
		$kb_asea_defaults = ASEA_KB_Config_Specs::get_default_kb_config();
		return array_merge( $template_defaults, $kb_asea_defaults );
	}

	/**
	 * Returnt to Editor add-onc configuration
	 * @param $editor_config
	 * @param $kb_config
	 * @param $page_type
	 * @return array
	 */
	public static function get_editor_fields_config( $editor_config, $kb_config, $page_type ) {

		$asea_config = [];
		
		if ( $page_type == 'settings' ) {
			return $editor_config;
		}
		
		if ( $page_type == 'main-page' || $kb_config['kb_main_page_layout'] == 'Sidebar' ) {
			$asea_config += ASEA_KB_Editor_Main_Page_Config::get_config();
		} else if ( $page_type == 'article-page' ) {
			$asea_config += ASEA_KB_Editor_Article_Page_Config::get_config();
		}

		// the width is now controlled by the rows in Modular layout
		if ( $page_type == 'main-page' && !empty( $kb_config['modular_main_page_toggle'] ) && $kb_config['modular_main_page_toggle'] == 'on'
			 && !empty( $asea_config['search_box_zone'] && !empty( $asea_config['search_box_zone']['settings']['width'] ) ) ) {
			unset( $asea_config['search_box_zone']['settings']['width'] );
		}

		return array_merge( $asea_config, $editor_config );  // keep ASEA first
	}

	/**
	 * Return Editor theme presets
	 *
	 * @param $themes_in
	 * @return array
	 */
	public static function get_theme_config( $themes_in ) {
		$themes = self::get_main_page_themes();

		if ( is_rtl() ) {
			foreach ( $themes as $config_mp_name => $config_values ) {
				if ( isset( self::$main_page_themes_rtl[$config_mp_name] ) ) {
					foreach( self::$main_page_themes_rtl[$config_mp_name] as $index => $value ) {
						$themes[$config_mp_name][$index] = self::$main_page_themes_rtl[$config_mp_name][$index];
					}
				}
			}
		}

		// duplicate Main Page presets to Article Page presets
		foreach ( $themes as $config_mp_name => $config_values ) {
			$config_ap_name = str_replace('_mp_', '_ap_', $config_mp_name);
			$themes[$config_ap_name] = $config_values;
		}

		return array_merge( $themes_in, $themes );
	}

	private static function get_main_page_themes() {

		return [
			// Box
			'advanced_search_mp_box_font_width' => [5=>80, 9=>'80'],
			'advanced_search_mp_box_margin_bottom' => [5=>40, 9=>'20'],
			'advanced_search_mp_box_margin_top' => [5=>0, 9=>0],
			'advanced_search_mp_box_padding_top' => [5=>'50', 9=>'50', 14=>'10', 15=>'10', 21=>'40'],
			'advanced_search_mp_box_padding_bottom' => [5=>'50', 9=>'50', 14=>'20', 15=>'10', 21=>'10'],
			'advanced_search_mp_box_padding_left' => [5=>0, 9=>0],
			'advanced_search_mp_box_padding_right' => [5=>0, 9=>0],
			'advanced_search_mp_box_results_style' => [5=>'off', 9=>'off'],

			// Title
			'advanced_search_mp_title' => [5=>'How Can We Help?', 9=>'How Can We Help?'],
			'advanced_search_mp_title_by_filter' => [5=>'Filter by categories', 9=>'Filter by categories'],
			'advanced_search_mp_title_font_color' => [1=>'#FFFFFF', 2=>'#000000', 3=>'#ffffff', 4=>'#ffffff', 5=>'#ffffff', 6=>'#FFFFFF', 7=>'#ffffff', 8=>'#528ffe', 9=>'#FFFFFF', 10=>'', 11=>'', 12=>'#FFFFFF',
														13=>'#000000', 14=>'#FFFFFF', 15=>'#000000', 16=>'#000000', 17=>'#6fb24c', 19=>'#000000', 20=>'#000000', 50=>'#FFFFFF', 51=>'#FFFFFF', 52=>'#000000', 53=>'#FFFFFF', 54=>'#000000', 55=>'#000000',
														56=>'#000000', 57=>'#FFFFFF', 58=>'#000000', 59=> '#FFFFFF', 60=> '#FFFFFF'],
			'advanced_search_mp_title_font_shadow_color' => [5=>'#010000', 6=>'#ffffff', 9=>'#010000', 15=>'#ffffff', 52=>'#000000', 56=>'#3e3e3e', 60=>'#000000'],
			'advanced_search_mp_title_padding_bottom' => [5=>11, 9=>'30'],
			'advanced_search_mp_title_text_shadow_toggle' => [5=>'off', 6=>'off', 9=>'off', 15=>'off', 50=>'on', 56=>'on', 60=>'on'],
			'advanced_search_mp_title_text_shadow_x_offset' => [5=>2, 9=>'2', 50=>'2', 56=>'2', 60=>'2'],
			'advanced_search_mp_title_text_shadow_y_offset' => [5=>2, 9=>'2', 50=>'2',56=>'2', 60=>'2'],
			'advanced_search_mp_title_text_shadow_blur' => [50=>'7', 56=>'3', 60=>'7'],
			'advanced_search_mp_title_toggle' => [],

			//  Background
			'advanced_search_mp_background_color' => [1=>'#f7941d', 2=>'#dd9933', 3=>'#b1d5e1', 4=>'#B1D5E1', 5=>'#43596e', 6=>'#921612', 7=>'#FFFFFF', 8=>'#f4f8ff', 9=>'#dd9933', 10=>'#8c1515', 11=>'#43596e', 12=>'#6e6767',
														13=>'#f2f2f2', 14=>'#1e73be', 15=>'#fcfcfc', 16=>'#fbfbfb', 17=>'#fbfbfb', 18=>'#8224e3', 19=>'#f3e6c8', 20=>'f3e6c8', 21 => '#0d2c41', 50=>'#ffffff', 51=>'#904e95',
														52=>'#ffffff', 53=>'#ffffff', 54=>'#ffffff', 55=>'#904e95', 56=>'#f9f9f9', 57=>'#904e95', 58=>'#ffffff'],
			'advanced_search_mp_background_gradient_degree' => [5=>0, 9=>'45'],
			'advanced_search_mp_background_gradient_from_color' => [5=>'#7f5706', 9=>'#f7941d', 50=> '#E4C4A6', 51=> '#525296', 52=> '#f7941d', 53=> '#56B6C5', 54=> '#f1f1f1', 55=> '#f1f1f1', 56=> '#a5a9d7',57=> '#3c134b', 59=> '#834BF6', 60=> '#FEA500'],
			'advanced_search_mp_background_gradient_opacity' => [5=>0.8, 9=>'.8', 50=>'.8',51=>'.9',52=>'.6', 53=>'.9',54=>'.9', 55=>'.9', 56=>'.9', 57=>'.9', 59=>'.9', 60=>'.9'],
			'advanced_search_mp_background_gradient_to_color' => [5=>'#000000', 9=>'#141414', 50=>'#F3E6CA', 51=> '#393996', 52=> '#93240b', 53=> '#31b0c4',54=> '#666666', 55=> '#1d82d5', 56=> '#E2E4FB',57=> '#25082f', 59=> '#702ef4', 60=> '#FBE000'],
			'advanced_search_mp_background_gradient_toggle' => [9=>'on', 50=>'on', 51=>'on', 52=>'on', 53=>'on', 54=>'on', 55=>'on', 56=>'on',57=>'on',59=>'on', 60=>'on'],
			'advanced_search_mp_background_image_position_x' => [9=>'left'],
			'advanced_search_mp_background_image_position_y' => [9=>'center'],
			'advanced_search_mp_background_image_url' => [  7=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															9=>'https://www.creative-addons.com/wp-content/uploads/2021/01/christopher-gower-m_HRfLhgABo-unsplash.jpg',
															50=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															51=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															52=>'https://www.echoknowledgebase.com/wp-content/uploads/2021/07/welcome_01.png',
															53=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															54=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															55=>'https://www.echoknowledgebase.com/wp-content/uploads/2021/07/triangles.jpg',
															56=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg',
															57=>'https://www.echoknowledgebase.com/wp-content/uploads/2021/07/triangles.jpg',
															59=>'https://www.creative-addons.com/wp-content/uploads/2021/01/christopher-gower-m_HRfLhgABo-unsplash.jpg',
															60=>'https://www.echoknowledgebase.com/wp-content/uploads/2021/07/triangles.jpg',
			],
			'advanced_search_mp_background_pattern_image_opacity' => [5=>0.5, 9=>'0.5'],
			'advanced_search_mp_background_pattern_image_position_x' => [5=>'left', 9=>'left'],
			'advanced_search_mp_background_pattern_image_position_y' => [5=>'top', 9=>'bottom'],

			// Button
			'advanced_search_mp_btn_background_color' => [1=>'#40474f', 2=>'#1168bf', 3=>'#686868', 4=>'#686868', 5=>'#686868', 6=>'#f4c60c', 7=>'#666666', 8=>'#bf25ff', 9=>'#686868', 10=>'#878787', 11=>'#686868', 12=>'#686868',
															13=>'#000000', 14=>'#40474f', 15=>'#f4c60c', 16=>'#40474f', 17=>'#6fb24c', 50=>'#1168bf', 51=>'#1168bf', 52=>'#1168bf', 53=>'#1168bf', 54=>'#1168bf', 55=>'#1168bf',
															56=>'#1168bf', 57=>'#1168bf', 58=>'#1168bf'],
			'advanced_search_mp_btn_border_color' => [1=>'#F1F1F1', 2=>'#F1F1F1', 3=>'#F1F1F1', 4=>'#F1F1F1', 5=>'#F1F1F1', 6=>'#f4c60c', 7=>'#666666', 8=>'#bf25ff', 9=>'#F1F1F1', 10=>'#000000', 11=>'#F1F1F1', 12=>'#F1F1F1',
														13=>'#000000', 14=>'#F1F1F1', 15=>'#0bcad9', 16=>'#F1F1F1', 17=>'#6fb24c', 50=>'#F1F1F1', 51=>'#F1F1F1', 52=>'#F1F1F1', 53=>'#F1F1F1', 54=>'#F1F1F1', 55=>'#F1F1F1',
														56=>'#F1F1F1', 57=>'#F1F1F1', 58=>'#F1F1F1'],
			'advanced_search_mp_button_name' => [5=>__( 'Search', 'echo-advanced-search' ), 9=>__( 'Search', 'echo-advanced-search' )],

			// Description
			'advanced_search_mp_description_below_input' => [9=>__( 'Contact Us |  View our Products |   About Us', 'echo-advanced-search' )],
			'advanced_search_mp_description_below_input_padding_bottom' => [5=>20, 9=>'20'],
			'advanced_search_mp_description_below_input_padding_top' => [5=>20, 9=>'6'],
			'advanced_search_mp_description_below_input_toggle' => [5=>'on', 9=>'on', 14=>'off'],
			'advanced_search_mp_description_below_title' => [5=>__( 'Tech tutorials, Reviews, How To\'s', 'echo-advanced-search' )],
			'advanced_search_mp_description_below_title_font_shadow_color' => [5=>'#010000', 9=>'#010000', 50=>'#000000', 56=>'#3e3e3e', 57=>'#FFFFFF', 60=>'#000000'],
			'advanced_search_mp_description_below_title_padding_bottom' => [5=>39, 9=>'20'],
			'advanced_search_mp_description_below_title_padding_top' => [5=>0, 9=>'20'],
			'advanced_search_mp_description_below_title_text_shadow_blur' => [5=>0, 9=>0, 50=>5, 56=>3, 60=>5],
			'advanced_search_mp_description_below_title_text_shadow_toggle' => [5=>'off', 9=>'off', 50=>'on', 56=>'on', 60=>'on'],
			'advanced_search_mp_description_below_title_text_shadow_x_offset' => [5=>2, 9=>'2', 50=>'2', 56=>'2', 60=>'2'],
			'advanced_search_mp_description_below_title_text_shadow_y_offset' => [5=>2, 9=>'2', 50=>'2', 56=>'2', 60=>'2'],
			'advanced_search_mp_description_below_title_toggle' => [5=>'on', 9=>'off', 14=>'off'],

			// Filter
			'advanced_search_mp_filter_box_background_color' => [5=>'#ffffff', 9=>'#ffffff'],
			'advanced_search_mp_filter_box_font_color' => [5=>'#000000', 7=>'#ffffff', 9=>'#000000'],
			'advanced_search_mp_filter_category_level' => [5=>'top', 9=>'top'],
			'advanced_search_mp_filter_dropdown_width' => [5=>260, 9=>'260'],
			'advanced_search_mp_filter_toggle' => [5=>'off', 9=>'off', 14=>'off'],

			// Input Box
			'advanced_search_mp_box_input_width' => [5=>40, 9=>'40', 21=>'30', 50=>'30', 51=>'30', 53=>'30', 54=>'30', 55=>'30', 56=>'30', 57=>'30', 59=>'30', 60=>'30'],
			'advanced_search_mp_input_border_width' => [5=>3, 9=>'3', 56=>'3', 21=>'6'],
			'advanced_search_mp_input_box_loading_icon_placement' => [5=>'right', 9=>'right'],
			'advanced_search_mp_input_box_padding_bottom' => [5=>10, 9=>'10'],
			'advanced_search_mp_input_box_padding_left' => [5=>43, 9=>'43'],
			'advanced_search_mp_input_box_padding_right' => [5=>43, 9=>'43'],
			'advanced_search_mp_input_box_padding_top' => [5=>10, 9=>'10'],
			'advanced_search_mp_input_box_radius' => [5=>0, 9=>0],
			'advanced_search_mp_input_box_search_icon_placement' => [5=>'left', 9=>'left'],
			'advanced_search_mp_input_box_shadow_blur' => [5=>0, 9=>'19', 50=>'5', 51=>'8', 53=>'8', 54=>'0', 56=>'8', 59=>'0', 60=>'0'],
			'advanced_search_mp_input_box_shadow_rgba' => [5=>'21, 21, 21, 0.3', 9=>'21, 21, 21, 0.5', 50=>'21, 21, 21, 0.5', 51=>'21, 21, 21, 0.5', 53=>'21, 21, 21, 0.5', 54=>'21, 21, 21, 0.2', 55=>'21, 21, 21, 0.2', 56=>'21, 21, 21, 0.5', 57=>'255, 255, 255, 0.16', 59=>'21, 21, 21, 0.2', 60=>'21, 21, 21, 0.2'],
			'advanced_search_mp_input_box_shadow_spread' => [5=>20, 9=>'10', 50=>'5', 51=>'5', 53=>'5', 54=>'10', 55=>'10', 56=>'3', 57=>'10', 59=>'10', 60=>'10'],
			'advanced_search_mp_input_box_shadow_x_offset' => [5=>0, 9=>0, 50=>0, 51=>0, 53=>0, 54=>0, 55=>0, 56=>0, 57=>0, 59=>0, 60=>0],
			'advanced_search_mp_input_box_shadow_y_offset' => [5=>0, 9=>0, 50=>0, 51=>0, 53=>0, 54=>0, 55=>0, 56=>0, 57=>0, 59=>0, 60=>0],
			'advanced_search_mp_text_input_background_color' => [1=>'#FFFFFF', 5=>'#FFFFFF', 9=>'#FFFFFF', 14=>'#FFFFFF', 15=>'#FFFFFF', 16=>'#FFFFFF', 17=>'#FFFFFF', 55=>'#ffffff', 56=>'#ffffff', 57=>'#ffffff'],
			'advanced_search_mp_text_input_border_color' => [1=>'#CCCCCC', 2=>'#CCCCCC', 3=>'#CCCCCC', 4=>'#CCCCCC', 5=>'#000000', 6=>'#0bcad9', 7=>'#d1d1d1', 8=>'#bf25ff', 9=>'#dd9933', 10=>'#000000', 11=>'#00c6c6', 12=>'#1e73be',
				13=>'#000000', 14=>'#CCCCCC', 15=>'#0bcad9', 16=>'#CCCCCC', 17=>'#6fb24c', 21 => '#d34d04', 50=>'#CCCCCC', 51=>'#CCCCCC', 52=>'#f7941d', 53=>'#FFFFFF', 54=>'#CCCCCC',
				55=>'#CCCCCC', 56=>'#a5a9d7', 57=>'#f7941d', 58=>'#CCCCCC'],

			// Results
			'advanced_search_mp_search_result_category_color' => [5=>'#000000', 9=>'#000000'],
			'advanced_search_mp_search_result_category_label' => [5=>'Top Category:', 9=>'Top Category:'],
			'advanced_search_mp_search_results_article_font_size' => [5=>16, 9=>'16'],

			// Other
			'advanced_search_mp_link_font_color' => [1=>'#FFFFFF', 2=>'#000000', 3=>'#ffffff', 4=>'#FFFFFF', 5=>'#FFFFFF', 6=>'#FFFFFF', 7=>'#ffffff', 8=>'#528ffe', 9=>'#FFFFFF', 10=>'#FFFFFF', 11=>'#FFFFFF', 12=>'#FFFFFF',
														13=>'#000000', 14=>'#000000', 15=>'#000000', 16=>'#000000', 17=>'#6fb24c', 19=>'#000000', 20=>'#000000', 21=>'#FFFFFF', 50=>'#FFFFFF', 51=>'#FFFFFF', 52=>'#000000', 53=>'#FFFFFF',
														54=>'#666666', 55=>'#FFFFFF', 56=>'#000000', 57=>'#FFFFFF', 58=>'#000000', 60=>'#FFFFFF'],


			// Typography is reset for each preset
			'advanced_search_mp_title_typography' => [5=>['font-size' => '36', 'font-weight' => 'normal'], 6=>['font-size' => '36'], 9=>['font-size' => '36', 'font-weight' => 'normal']],
			'advanced_search_mp_description_below_title_typography' => [5=>['font-size' => '16'], 9=>['font-size' => '16']],
			'advanced_search_mp_input_box_typography' => [5=>['font-size' => '18'], 9=>['font-size' => '18']],
			'advanced_search_mp_description_below_input_typography' => [5=>['font-size' => '16'], 9=>['font-size' => '16']],
		];
	}
	
	private static $main_page_themes_rtl = [
		'advanced_search_mp_title_text_shadow_x_offset' => [9=>'-2', 50=>'-2', 54=>'-2', 55=>'-2', 56=>'-2', 57=>'-2',60=>'-2'],
		'advanced_search_mp_description_below_title_text_shadow_x_offset' => [5=>'-2', 50=>'-2', 54=>'-2', 55=>'-2', 56=>'-2', 57=>'-2', 60=>'-2'],
		'advanced_search_mp_input_box_loading_icon_placement' => [5=>'left', 9=>'left'],
		'advanced_search_mp_input_box_search_icon_placement' => [5=>'right', 9=>'right']
	];
	
	// Add search page config to FE 
	public static function get_front_end_editor_config( $kb_config ) {
		return ASEA_KB_Editor_Search_Page_Config::get_config( $kb_config );
	}
}