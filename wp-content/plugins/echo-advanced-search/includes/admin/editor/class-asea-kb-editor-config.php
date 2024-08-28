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
	 * @param $themes_compacted_in
	 * @return array
	 */
	public static function get_theme_config( $themes_compacted_in ) {
		$themes_compacted = self::$themes_compacted;

		if ( is_rtl() ) {
			foreach ( $themes_compacted as $config_mp_name => $config_values ) {
				if ( isset( self::$themes_compacted_rtl[$config_mp_name] ) ) {
					foreach( self::$themes_compacted_rtl[$config_mp_name] as $index => $value ) {
						$themes_compacted[$config_mp_name][$index] = self::$themes_compacted_rtl[$config_mp_name][$index];
					}
				}
			}
		}

		// duplicate Main Page presets to Article Page presets
		foreach ( $themes_compacted as $config_mp_name => $config_values ) {
			$config_ap_name = str_replace('_mp_', '_ap_', $config_mp_name);
			$themes_compacted[$config_ap_name] = $config_values;
		}

		return array_merge( $themes_compacted_in, $themes_compacted );
	}

	private static $themes_compacted = [
		//'theme_name' => [1=>'standard', 2=>'elegant', 3=>'modern', 4=>'image', 5=>'informative', 6=>'formal', 7=>'bright', 8=>'disctinct', 9=>'basic', 10=>'organized', 11=>'organized_2', 12=>'products_based', 13=>'clean', 14=>'standard_2', 15=>'icon_focused', 16=>'business', 17=>'minimalistic', 50=>'grid_basic', 51=>'grid_demo_5', 52=>'grid_demo_6', 53=>'grid_demo_7', 54=>'sidebar_basic', 55=>'sidebar_colapsed', 56=>'sidebar_formal', 57=>'sidebar_compact', 58=>'sidebar_plain'],

		// Box
		'advanced_search_mp_box_font_width' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>80, 6=>'', 7=>'', 8=>'', 9=>'80', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_input_width' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>40, 6=>'', 7=>'', 8=>'', 9=>'40', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_margin_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>40, 6=>'', 7=>'', 8=>'', 9=>'20', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_margin_top' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_padding_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>50, 6=>'', 7=>'', 8=>'', 9=>'50', 10=>'', 11=>'', 12=>'30', 13=>'', 14=>'20', 15=>'10', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_padding_left' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_padding_right' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_padding_top' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>50, 6=>'', 7=>'', 8=>'', 9=>'50', 10=>'', 11=>'', 12=>'30', 13=>'', 14=>'10', 15=>'10', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_box_results_style' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'off', 6=>'', 7=>'', 8=>'', 9=>'off', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Title
		'advanced_search_mp_title' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'How Can We Help?', 6=>'', 7=>'', 8=>'', 9=>'How Can We Help?', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_by_filter' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'Filter by categories', 6=>'', 7=>'', 8=>'', 9=>'Filter by categories', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_font_color' => [1=>'#FFFFFF', 2=>'#000000', 3=>'#ffffff', 4=>'#ffffff', 5=>'#ffffff', 6=>'#000000', 7=>'#ffffff', 8=>'#528ffe', 9=>'#FFFFFF', 10=>'', 11=>'', 12=>'#FFFFFF', 13=>'#000000', 14=>'#FFFFFF', 15=>'#000000', 16=>'#000000', 17=>'#6fb24c', 50=>'#000000', 51=>'#000000', 52=>'#000000', 53=>'#000000', 54=>'#000000', 55=>'#ffffff', 56=>'#136eb5', 57=>'#f7941d', 58=>'#000000',],
		'advanced_search_mp_title_font_shadow_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#010000', 6=>'#ffffff', 7=>'', 8=>'', 9=>'#010000', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'#ffffff', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_padding_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>11, 6=>'', 7=>'', 8=>'', 9=>'30', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_tag' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_text_shadow_blur' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_text_shadow_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'off', 6=>'off', 7=>'', 8=>'', 9=>'off', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'off', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_text_shadow_x_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>2, 6=>'', 7=>'', 8=>'', 9=>'2', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_text_shadow_y_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>2, 6=>'', 7=>'', 8=>'', 9=>'2', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_title_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'on', 6=>'off', 7=>'', 8=>'', 9=>'on', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		//  Background
		'advanced_search_mp_background_color' => [1=>'#f7941d', 2=>'#c9418e', 3=>'#b1d5e1', 4=>'#B1D5E1', 5=>'#904e95', 6=>'#edf2f6', 7=>'#FFFFFF', 8=>'#f4f8ff', 9=>'#dd9933', 10=>'#8c1515', 11=>'#43596e', 12=>'#6e6767', 13=>'#f2f2f2', 14=>'#1e73be', 15=>'#fcfcfc', 16=>'#fbfbfb', 17=>'#fbfbfb', 50=>'#ffffff', 51=>'#904e95', 52=>'#ffffff', 53=>'#ffffff', 54=>'#ffffff', 55=>'#904e95', 56=>'#f9f9f9', 57=>'#fbfbfb', 58=>'#ffffff',],
		'advanced_search_mp_background_gradient_degree' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>'45', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_gradient_from_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#7f5706', 6=>'', 7=>'', 8=>'', 9=>'#f7941d', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_gradient_opacity' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0.8, 6=>'', 7=>'', 8=>'', 9=>'.8', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_gradient_to_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#000000', 6=>'', 7=>'', 8=>'', 9=>'#141414', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_gradient_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'on', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_image_position_x' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'left', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_image_position_y' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'center', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_image_url' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'https://www.creative-addons.com/wp-content/uploads/2021/01/imgix-klWUhr-wPJ8-unsplash.jpg', 8=>'', 9=>'https://www.creative-addons.com/wp-content/uploads/2021/01/christopher-gower-m_HRfLhgABo-unsplash.jpg', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_pattern_image_opacity' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0.5, 6=>'', 7=>'', 8=>'', 9=>'0.5', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_pattern_image_position_x' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'left', 6=>'', 7=>'', 8=>'', 9=>'left', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_pattern_image_position_y' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'top', 6=>'', 7=>'', 8=>'', 9=>'bottom', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_background_pattern_image_url' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Button
		'advanced_search_mp_btn_background_color' => [1=>'#40474f', 2=>'#1168bf', 3=>'#686868', 4=>'#686868', 5=>'#686868', 6=>'#f4c60c', 7=>'#666666', 8=>'#bf25ff', 9=>'#686868', 10=>'#878787', 11=>'#686868', 12=>'#686868', 13=>'#000000', 14=>'#40474f', 15=>'#f4c60c', 16=>'#40474f', 17=>'#6fb24c', 50=>'#1168bf', 51=>'#1168bf', 52=>'#1168bf', 53=>'#1168bf', 54=>'#1168bf', 55=>'#1168bf', 56=>'#1168bf', 57=>'#1168bf', 58=>'#1168bf',],
		'advanced_search_mp_btn_border_color' => [1=>'#F1F1F1', 2=>'#F1F1F1', 3=>'#F1F1F1', 4=>'#F1F1F1', 5=>'#F1F1F1', 6=>'#f4c60c', 7=>'#666666', 8=>'#bf25ff', 9=>'#F1F1F1', 10=>'#000000', 11=>'#F1F1F1', 12=>'#F1F1F1', 13=>'#000000', 14=>'#F1F1F1', 15=>'#0bcad9', 16=>'#F1F1F1', 17=>'#6fb24c', 50=>'#F1F1F1', 51=>'#F1F1F1', 52=>'#F1F1F1', 53=>'#F1F1F1', 54=>'#F1F1F1', 55=>'#F1F1F1', 56=>'#F1F1F1', 57=>'#F1F1F1', 58=>'#F1F1F1',],
		'advanced_search_mp_button_name' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'Search', 6=>'', 7=>'', 8=>'', 9=>'Search', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Description
		'advanced_search_mp_description_below_input' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'Contact Us |  View our Products |   About Us', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_input_padding_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>20, 6=>'', 7=>'', 8=>'', 9=>'20', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_input_padding_top' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>20, 6=>'', 7=>'', 8=>'', 9=>'6', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_input_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'on', 6=>'', 7=>'', 8=>'', 9=>'on', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'off', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'Tech tutorials, Reviews, How To\'s', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_font_shadow_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#010000', 6=>'', 7=>'', 8=>'', 9=>'#010000', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_padding_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>39, 6=>'', 7=>'', 8=>'', 9=>'20', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_padding_top' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>'20', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_text_shadow_blur' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_text_shadow_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'off', 6=>'', 7=>'', 8=>'', 9=>'off', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_text_shadow_x_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>2, 6=>'', 7=>'', 8=>'', 9=>'2', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_text_shadow_y_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>2, 6=>'', 7=>'', 8=>'', 9=>'2', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'on', 6=>'', 7=>'', 8=>'', 9=>'off', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'off', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Filter
		'advanced_search_mp_filter_box_background_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#ffffff', 6=>'', 7=>'', 8=>'', 9=>'#ffffff', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_filter_box_font_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#000000', 6=>'', 7=>'#ffffff', 8=>'', 9=>'#000000', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_filter_category_level' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'top', 6=>'', 7=>'', 8=>'', 9=>'top', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_filter_dropdown_width' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>260, 6=>'', 7=>'', 8=>'', 9=>'260', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_filter_indicator_text' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'', 7=>'', 8=>'', 9=>'', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_filter_toggle' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'off', 6=>'', 7=>'', 8=>'', 9=>'off', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'off', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Input Box
		'advanced_search_mp_input_border_width' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>3, 6=>'', 7=>'', 8=>'', 9=>'3', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_loading_icon_placement' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'right', 6=>'', 7=>'', 8=>'', 9=>'right', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_padding_bottom' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>10, 6=>'', 7=>'', 8=>'', 9=>'10', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_padding_left' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>43, 6=>'', 7=>'', 8=>'', 9=>'43', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_padding_right' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>43, 6=>'', 7=>'', 8=>'', 9=>'43', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_padding_top' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>10, 6=>'', 7=>'', 8=>'', 9=>'10', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_radius' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_search_icon_placement' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'left', 6=>'', 7=>'', 8=>'', 9=>'left', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_shadow_blur' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>'19', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_shadow_rgba' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'21, 21, 21, 0.3', 6=>'', 7=>'', 8=>'', 9=>'21, 21, 21, 0.5', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_shadow_spread' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>20, 6=>'', 7=>'', 8=>'', 9=>'10', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_shadow_x_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_shadow_y_offset' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>0, 6=>'', 7=>'', 8=>'', 9=>0, 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Results
		'advanced_search_mp_search_result_category_color' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'#000000', 6=>'', 7=>'', 8=>'', 9=>'#000000', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_search_result_category_label' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>'Top Category:', 6=>'', 7=>'', 8=>'', 9=>'Top Category:', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_search_results_article_font_size' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>16, 6=>'', 7=>'', 8=>'', 9=>'16', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Other
		'advanced_search_mp_link_font_color' => [1=>'#FFFFFF', 2=>'#000000', 3=>'#ffffff', 4=>'#FFFFFF', 5=>'#FFFFFF', 6=>'#000000', 7=>'#ffffff', 8=>'#528ffe', 9=>'#FFFFFF', 10=>'#FFFFFF', 11=>'#FFFFFF', 12=>'#FFFFFF', 13=>'#000000', 14=>'#000000', 15=>'#000000', 16=>'#000000', 17=>'#6fb24c', 50=>'#000000', 51=>'#000000', 52=>'#000000', 53=>'#000000', 54=>'#000000', 55=>'#FFFFFF', 56=>'#136eb5', 57=>'#f7941d', 58=>'#000000',],
		'advanced_search_mp_text_input_background_color' => [1=>'#FFFFFF', 2=>'', 3=>'', 4=>'', 5=>'#FFFFFF', 6=>'', 7=>'', 8=>'', 9=>'#FFFFFF', 10=>'', 11=>'', 12=>'', 13=>'', 14=>'#FFFFFF', 15=>'#FFFFFF', 16=>'#FFFFFF', 17=>'#FFFFFF', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#ffffff', 56=>'#ffffff', 57=>'#ffffff', 58=>'',],
		'advanced_search_mp_text_input_border_color' => [1=>'#CCCCCC', 2=>'#CCCCCC', 3=>'#CCCCCC', 4=>'#CCCCCC', 5=>'#000000', 6=>'#0bcad9', 7=>'#d1d1d1', 8=>'#bf25ff', 9=>'#dd9933', 10=>'#000000', 11=>'#00c6c6', 12=>'#1e73be', 13=>'#000000', 14=>'#CCCCCC', 15=>'#0bcad9', 16=>'#CCCCCC', 17=>'#6fb24c', 50=>'#CCCCCC', 51=>'#CCCCCC', 52=>'#f7941d', 53=>'#CCCCCC', 54=>'#CCCCCC', 55=>'#eeee22', 56=>'#00c1b6', 57=>'#f7941d', 58=>'#CCCCCC',],

		// Typography is reset for each preset
		'advanced_search_mp_title_typography' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>['font-size' => '36', 'font-weight' => 'normal'], 6=>['font-size' => '22'], 7=>'', 8=>'', 9=>['font-size' => '36', 'font-weight' => 'normal'], 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_title_typography' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>['font-size' => '16'], 6=>'', 7=>'', 8=>'', 9=>['font-size' => '16'], 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_input_box_typography' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>['font-size' => '18'], 6=>'', 7=>'', 8=>'', 9=>['font-size' => '18'], 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'advanced_search_mp_description_below_input_typography' => [1=>'', 2=>'', 3=>'', 4=>'', 5=>['font-size' => '16'], 6=>'', 7=>'', 8=>'', 9=>['font-size' => '16'], 10=>'', 11=>'', 12=>'', 13=>'', 14=>'', 15=>'', 16=>'', 17=>'', 50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
	];
	
	private static $themes_compacted_rtl = [
		'advanced_search_mp_title_text_shadow_x_offset' => [9=>'-2'],
		'advanced_search_mp_description_below_title_text_shadow_x_offset' => [5=>'-2'],
		'advanced_search_mp_input_box_loading_icon_placement' => [5=>'left', 9=>'left'],
		'advanced_search_mp_input_box_search_icon_placement' => [5=>'right', 9=>'right']
	];
	
	// Add search page config to FE 
	public static function get_front_end_editor_config( $kb_config ) {
		return ASEA_KB_Editor_Search_Page_Config::get_config( $kb_config );
	}
}