<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Store Frontend Editor configuration data
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ELAY_KB_Editor_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	public static function register_editor_hooks() {
		add_filter( 'eckb_editor_fields_specs', array('ELAY_KB_Editor_Config', 'get_editor_fields_specs' ), 10, 2 );
		add_filter( 'eckb_all_editors_get_current_config', array('ELAY_KB_Editor_Config', 'get_current_config' ), 10, 2 );
		add_filter( 'eckb_editor_get_default_config', array('ELAY_KB_Editor_Config', 'get_configuration_defaults' ) );
		add_filter( 'eckb_editor_fields_config', array('ELAY_KB_Editor_Config', 'get_editor_fields_config' ), 10, 3 );
		add_filter( 'eckb_theme_wizard_get_themes_v2', array('ELAY_KB_Editor_Config', 'get_theme_config' ), 30 );
	}

	/**
	 * Returnt to Editor add-onc specs
	 * @param $eckb_field_specification
	 * @param $kb_id
	 * @return array
	 */
	public static function get_editor_fields_specs( $eckb_field_specification, $kb_id ) {
		return array_merge( $eckb_field_specification, ELAY_KB_Config_Specs::get_fields_specification() );
	}

	/**
	 * Returnt to Wizard the current KB configuration
	 *
	 * @param $kb_config
	 * @param $kb_id
	 * @return array
	 */
	public static function get_current_config( $kb_config, $kb_id ) {
		$addon_config = elay_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $addon_config );
	}

	/**
	 * Return add-on configuration defaults.
	 *
	 * @param $template_defaults
	 * @return array
	 */
	public static function get_configuration_defaults( $template_defaults ) {
		$kb_elay_defaults = ELAY_KB_Config_Specs::get_default_kb_config();
		return array_merge($template_defaults, $kb_elay_defaults);
	}

	/**
	 * Returnt to Editor add-onc configuration
	 * @param $editor_config
	 * @param $kb_config
	 * @param $page_type
	 * @return array
	 */
	public static function get_editor_fields_config( $editor_config, $kb_config, $page_type ) {
		
		$elay_config = [];

		if ( $page_type == 'settings' ) {
			return $editor_config;
		}

		if ( $page_type == 'main-page' ) {
			$elay_config += ELAY_KB_Editor_Main_Page_Config::get_config( $kb_config );
		} else if ( $page_type == 'article-page' ) {
			$elay_config += ELAY_KB_Editor_Article_Page_Config::get_config( $kb_config );
		}

		return array_merge( $editor_config, $elay_config );
	}

	/**
	 * Return ELAY Wizard themes and add additional parameters
	 *
	 * @param $themes_compacted_in
	 * @return array
	 */
	public static function get_theme_config( $themes_compacted_in ) {
		$themes_compacted = self::$themes_compacted;

		if ( is_rtl() ) {
			foreach ( $themes_compacted as $config_name => $config_values ) {
				if ( isset( self::$themes_compacted_rtl[$config_name] ) ) {
					foreach( self::$themes_compacted_rtl[$config_name] as $index => $value ) {
						$themes_compacted[$config_name][$index] = self::$themes_compacted_rtl[$config_name][$index];
					}
				}
			}
		}

		return array_merge( $themes_compacted_in, $themes_compacted );
	}

	private static $themes_compacted = [

		// Setup
		'theme_name' => [50=>'grid_basic', 51=>'grid_demo_5', 52=>'grid_demo_6', 53=>'grid_demo_7', 54=>'sidebar_basic', 55=>'sidebar_colapsed', 56=>'sidebar_formal', 57=>'sidebar_compact', 58=>'sidebar_plain'],
		'kb_name' => [50=>'Basic', 51=>'Informative', 52=>'Simple', 53=>'Left Icon Style', 54=>'Basic', 55=>'Collapsed', 56=>'Formal', 57=>'Compact', 58=>'Plain'],
		'kb_main_page_layout' => [50=>'Grid', 51=>'Grid', 52=>'Grid', 53=>'Grid', 54=>'Sidebar', 55=>'Sidebar', 56=>'Sidebar', 57=>'Sidebar', 58=>'Sidebar'],

		// KB Core Search
		'search_background_color' => [50=>'#c7c7c7', 51=>'#904e95', 52=>'#c7c7c7', 53=>'#c7c7c7', 54=>'#c7c7c7', 55=>'#904e95', 56=>'#efefef', 57=>'#c7c7c7', 58=>'#c7c7c7',],
		'search_box_input_width' => [50=>'50', 51=>'20', 52=>'30', 53=>'30', 54=>'', 55=>'30', 56=>'', 57=>'', 58=>''],
		'search_box_margin_bottom' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'search_box_padding_bottom' => [50=>'', 51=>'40', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'search_box_padding_top' => [50=>'', 51=>'40', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'search_btn_background_color' => [50=>'#1168bf', 51=>'#904e95', 52=>'#f7941d', 53=>'#56B6C6', 54=>'#1168bf', 55=>'#904e95', 56=>'#00c1b6', 57=>'#f7941d', 58=>'#1168bf',],
		'search_btn_border_color' => [50=>'#F1F1F1', 51=>'#F1F1F1', 52=>'#F1F1F1', 53=>'#F1F1F1', 54=>'#F1F1F1', 55=>'#F1F1F1', 56=>'#00c1b6', 57=>'#f7941d', 58=>'#F1F1F1',],
		'search_input_border_width' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'search_layout' => [50=>'', 51=>'epkb-search-form-3', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'search_text_input_background_color' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'search_text_input_border_color' => [50=>'#CCCCCC', 51=>'#CCCCCC', 52=>'#CCCCCC', 53=>'#CCCCCC', 54=>'#CCCCCC', 55=>'#CCCCCC', 56=>'#00c1b6', 57=>'#f7941d', 58=>'#CCCCCC',],
		'search_title_font_color' => [50=>'#000000', 51=>'#000000', 52=>'#000000', 53=>'#000000', 54=>'#000000', 55=>'#FFFFFF', 56=>'#1e73be', 57=>'#f7941d', 58=>'#000000',],

		// Categories
		'grid_category_icon_location'               => [50=>'', 51=>'', 52=>'', 53=>'left', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'grid_section_body_alignment'               => [50=>'', 51=>'', 52=>'', 53=>'left', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'grid_section_body_padding_bottom'          => [50=>'', 51=>'', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'grid_section_body_padding_left'            => [50=>'', 51=>'', 52=>'', 53=>'76', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'grid_section_body_padding_right'           => [50=>'', 51=>'', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'grid_section_body_padding_top'             => [50=>'15', 51=>'', 52=>'10', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'section_category_font_color'               => [50=>'', 51=>'#b3b3b3', 52=>'#000000', 53=>'#000000', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'section_border_color'                      => [50=>'', 51=>'#F7F7F7', 52=>'#f7941d', 53=>'#CECED2', 54=>'', 55=>'', 56=>'', 57=>'', 58=>''],
		'section_border_radius'                     => [50=>'', 51=>'0', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'section_border_width'                      => [50=>'', 51=>'0', 52=>'2', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_box_hover'                    => [50=>'', 51=>'hover-4', 52=>'hover-4', 53=>'hover-4', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_box_shadow'                   => [50=>'', 51=>'section_medium_shadow', 52=>'', 53=>'section_light_shadow', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_cat_name_padding_bottom'      => [50=>'', 51=>'0', 52=>'0', 53=>'5', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_cat_name_padding_left'        => [50=>'', 51=>'', 52=>'', 53=>'5', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_cat_name_padding_right'       => [50=>'', 51=>'', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_cat_name_padding_top'         => [50=>'', 51=>'17', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_desc_padding_bottom'          => [50=>'', 51=>'15', 52=>'10', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_desc_padding_left'            => [50=>'', 51=>'', 52=>'', 53=>'55', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_desc_padding_right'           => [50=>'', 51=>'', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_desc_padding_top'             => [50=>'', 51=>'5', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_desc_text_on'                 => [50=>'', 51=>'on', 52=>'', 53=>'on', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_divider'                      => [50=>'', 51=>'', 52=>'off', 53=>'off', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'section_divider_color'                     => [50=>'', 51=>'#FFFFFF', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_head_alignment'               => [50=>'', 51=>'', 52=>'', 53=>'left', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'section_head_description_font_color'       => [50=>'', 51=>'', 52=>'#000000', 53=>'#7E8082', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'section_head_font_color'                   => [50=>'', 51=>'#904e95', 52=>'#000000', 53=>'#000000', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'section_head_category_icon_color'          => [50=>'#1e73be', 51=>'#904e95', 52=>'#f7941d', 53=>'#56B6C6', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_head_padding_bottom'          => [50=>'', 51=>'10', 52=>'', 53=>'5', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_head_padding_left'            => [50=>'', 51=>'10', 52=>'', 53=>'20', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_head_padding_right'           => [50=>'', 51=>'10', 52=>'', 53=>'20', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_head_padding_top'             => [50=>'', 51=>'10', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_icon_padding_bottom'          => [50=>'', 51=>'0', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'0', 57=>'', 58=>'',],
		'grid_section_icon_padding_left'            => [50=>'', 51=>'10', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_icon_padding_right'           => [50=>'', 51=>'', 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_icon_padding_top'             => [50=>'', 51=>10, 52=>'', 53=>'0', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_icon_size'                    => [50=>'', 51=>'', 52=>'', 53=>'50', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		// Sidebar
		'article_nav_sidebar_type_left'             => [50=>'eckb-nav-sidebar-v1', 51=>'eckb-nav-sidebar-v1', 52=>'eckb-nav-sidebar-v1', 53=>'eckb-nav-sidebar-v1', 54=>'eckb-nav-sidebar-v1', 55=>'eckb-nav-sidebar-v1', 56=>'eckb-nav-sidebar-v1', 57=>'eckb-nav-sidebar-v1', 58=>'eckb-nav-sidebar-v1',],
		'sidebar_article_active_background_color'   => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#f5eaff', 56=>'#f8f8f8', 57=>'#fbfbfb', 58=>'#f8f8f8',],
		'sidebar_article_active_bold'               => [50=>'', 51=>'', 52=>'off', 53=>'off', 54=>'', 55=>'', 56=>'', 57=>'off', 58=>'',],
		'sidebar_article_active_font_color'         => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#000000', 56=>'', 57=>'#f7941d', 58=>'#000000',],
		'sidebar_article_font_color'                => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'#1c8bed', 55=>'#2ea3f2', 56=>'#00c1b6', 57=>'#459fed', 58=>'#2ea3f2',],
		'sidebar_article_icon_color'                => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#904e95', 56=>'#136eb5', 57=>'#f7941d', 58=>'#136eb5',],
		'sidebar_article_list_margin'               => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'20', 57=>'', 58=>'',],
		'sidebar_background_color'                  => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'#FFFFFF', 55=>'#FFFFFF', 56=>'#FFFFFF', 57=>'#fbfbfb', 58=>'#FFFFFF',],
		'sidebar_expand_articles_icon'              => [50=>'', 51=>'', 52=>'ep_font_icon_plus', 53=>'ep_font_icon_plus', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'sidebar_main_page_intro_text'              => [50=>'', 51=>'', 52=>'Welcome to our Knowledge Base.', 53=>'Welcome to our Knowledge Base.', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'sidebar_section_body_padding_bottom'       => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'0', 57=>'0', 58=>'',],
		'sidebar_section_body_padding_top'          => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'0', 57=>'0', 58=>'',],
		'sidebar_section_border_color'              => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#FFFFFF', 56=>'#dbdbdb', 57=>'#dbdbdb', 58=>'#f7f7f7',],
		'sidebar_section_border_radius'             => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'0', 56=>'0', 57=>'0', 58=>'3',],
		'sidebar_section_border_width'              => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'0', 57=>'0', 58=>'0',],
		'sidebar_section_box_shadow'                => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'section_light_shadow', 56=>'no_shadow', 57=>'no_shadow', 58=>'no_shadow',],
		'sidebar_section_category_font_color'       => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#904e95', 56=>'', 57=>'#686868', 58=>'#868686',],
		'sidebar_section_category_icon_color'       => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#904e95', 56=>'', 57=>'#212121', 58=>'#868686',],
		'sidebar_section_divider'                   => [50=>'', 51=>'', 52=>'off', 53=>'off', 54=>'', 55=>'', 56=>'off', 57=>'off', 58=>'',],
		'sidebar_section_divider_color'             => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#904e95', 56=>'#00c1b6', 57=>'#dadada', 58=>'#f0f0f0',],
		'sidebar_section_head_alignment'            => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'left', 56=>'left', 57=>'left', 58=>'',],
		'sidebar_section_head_background_color'     => [50=>'', 51=>'', 52=>'#f4f4f4', 53=>'', 54=>'', 55=>'#FFFFFF', 56=>'#FFFFFF', 57=>'#FFFFFF', 58=>'#FFFFFF',],
		'sidebar_section_head_description_font_color' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'#bfdac1', 56=>'#bfdac1', 57=>'', 58=>'#355666',],
		'sidebar_section_head_font_color'           => [50=>'#1168bf', 51=>'#904e95', 52=>'#f7941d', 53=>'#56B6C6', 54=>'', 55=>'#904e95', 56=>'#136eb5', 57=>'#212121', 58=>'#136eb5',],
		'sidebar_section_head_padding_left'         => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'10', 56=>'0', 57=>'0', 58=>'',],
		'sidebar_section_head_padding_top'          => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'10', 56=>'0', 57=>'0', 58=>'',],
		'sidebar_section_head_padding_right'        => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'10', 56=>'0', 57=>'0', 58=>'',],
		'sidebar_section_head_padding_bottom'       => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'10', 56=>'0', 57=>'0', 58=>'',],

		'sidebar_show_articles_before_categories' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'off', 56=>'', 57=>'off', 58=>'',],
		'sidebar_side_bar_width' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'20', 58=>'',],
		'sidebar_top_categories_collapsed' => [50=>'', 51=>'', 52=>'off', 53=>'off', 54=>'', 55=>'on', 56=>'', 57=>'', 58=>'',],

		// Articles
		'back_navigation_text_color' => [50=>'#1e73be', 51=>'#1e73be', 52=>'#1e73be', 53=>'#56B6C6', 54=>'#1e73be', 55=>'#904e95', 56=>'#1e73be', 57=>'#1e73be', 58=>'#00c1b6',],
		'breadcrumb_text_color' => [50=>'#1e73be', 51=>'#1e73be', 52=>'#1e73be', 53=>'#56B6C6', 54=>'#1e73be', 55=>'#904e95', 56=>'#1e73be', 57=>'#1e73be', 58=>'#136eb5',],

		// Other
		'search_title' => [50=>'Have a Question?', 51=>'Support Center', 52=>'', 53=>'Support Center', 54=>'Self Help Documentation', 55=>'', 56=>'Support Center', 57=>'', 58=>'What are you looking for?',],

		// Typography is reset for each preset
		'grid_section_typography' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_description_typography' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],
		'grid_section_article_typography' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>'', 58=>'',],

		'sidebar_section_category_typography' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>['font-size' => '18'], 58=>'',],
		'sidebar_section_category_typography_desc' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>['font-size' => '12'], 58=>'',],
		'sidebar_section_body_typography' => [50=>'', 51=>'', 52=>'', 53=>'', 54=>'', 55=>'', 56=>'', 57=>['font-size' => '14'], 58=>'',],
	];
	
	private static $themes_compacted_rtl = [

		// Categories
		'grid_section_body_alignment' => [53=>'right'],
		'grid_section_body_padding_left' => [53=>'0'],
		'grid_section_body_padding_right' => [53=>'80'],
		'grid_section_cat_name_padding_left' => [53=>'0'],
		'grid_section_cat_name_padding_right' => [53=>'80'],
		
		'grid_section_desc_padding_left' => [53=>'0'],
		'grid_section_desc_padding_right' => [53=>'80'],
		
		'grid_section_head_alignment' => [53=>'right'],
		'grid_section_icon_padding_left' => [51=>'', 53=>'0'],
		'grid_section_icon_padding_right' => [51=>'10', 53=>'20'],
		
		// Sidebar
		'sidebar_section_head_alignment' => [54=>'right', 55=>'right', 56=>'right', 57=>'right', 58=>'right',],
	];
}