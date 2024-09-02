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
	 * @param $themes_in
	 * @return array
	 */
	public static function get_theme_config( $themes_in ) {
		$themes = self::get_main_page_themes();

		if ( is_rtl() ) {
			foreach ( $themes as $config_name => $config_values ) {
				if ( isset( self::$themes_rtl[$config_name] ) ) {
					foreach( self::$themes_rtl[$config_name] as $index => $value ) {
						$themes[$config_name][$index] = self::$themes_rtl[$config_name][$index];
					}
				}
			}
		}

		return array_merge( $themes_in, $themes );
	}

	private static function get_main_page_themes() {

		return [
			// Setup
			'theme_name' => [50 => 'grid_basic', 51 => 'grid_demo_5', 52 => 'grid_demo_6', 53 => 'grid_demo_7', 54 => 'sidebar_basic', 55 => 'sidebar_colapsed', 56 => 'sidebar_formal', 57 => 'sidebar_compact', 58 => 'sidebar_plain', 59 => 'grid_demo_8', 60=>'grid_demo_9'],
			'kb_name' => [50 => __( 'Basic', 'echo-knowledge-base' ), 51 => __( 'Informative', 'echo-knowledge-base' ), 52 => __( 'Simple', 'echo-knowledge-base' ), 53 => __( 'Left Icon Style', 'echo-knowledge-base' ),
							54 => __( 'Basic', 'echo-knowledge-base' ), 55 => __( 'Collapsed', 'echo-knowledge-base' ), 56 => __( 'Formal', 'echo-knowledge-base' ), 57 => __( 'Compact', 'echo-knowledge-base' ),
							58 => __( 'Plain', 'echo-knowledge-base' ), 59 => __( 'Simple 2', 'echo-knowledge-base' ),60 => __( 'Icon Squares', 'echo-knowledge-base' )],
			'kb_main_page_layout' => [50 => 'Grid', 51 => 'Grid', 52 => 'Grid', 53 => 'Grid', 54 => 'Sidebar', 55 => 'Sidebar', 56 => 'Sidebar', 57 => 'Sidebar', 58 => 'Sidebar', 59 => 'Grid',60 => 'Grid'],

			// KB Core Search
			'search_background_color' => [50 => '#F3E6CA', 51 => '#525296', 52 => '#c7c7c7', 53 => '#c7c7c7', 54 => '#f1f1f1', 55 => '#f5f8ff', 56 => '#f5f8ff', 57 => '#3c134b', 58 => '#c7c7c7', 59 => '#F7F5FF', 60 => '#F7AC08'],
			'search_box_input_width' => [50 => '40', 51 => '40', 52 => '30', 53 => '30', 55 => '30', 57 => '30', 58 => ''],
			'search_box_margin_bottom' => [],
			'search_box_padding_bottom' => [51 => '40'],
			'search_box_padding_top' => [51 => '40'],
			'search_btn_background_color' => [50 => '#204B5B', 51 => '#525296', 52 => '#f7941d', 53 => '#56B6C6', 54 => '#666666', 55 => '#1d82d5', 56 => '#a5a9d7', 57 => '#25082f', 58 => '#1168bf'],
			'search_btn_border_color' => [50 => '#ADE7D5', 51 => '#F1F1F1', 52 => '#F1F1F1', 53 => '#F1F1F1', 54 => '#666666', 55 => '#1d82d5', 56 => '#E2E4FB', 57 => '#FFFFFF', 58 => '#F1F1F1'],
			'search_input_border_width' => [],
			'search_layout' => [51 => 'epkb-search-form-3'],
			'search_text_input_background_color' => [58 => ''],
			'search_text_input_border_color' => [50 => '#CCCCCC', 51 => '#CCCCCC', 52 => '#CCCCCC', 53 => '#CCCCCC', 54 => '#FFFFFF', 55 => '#FFFFFF', 56 => '#E2E4FB', 57 => '#FFFFFF', 58 => '#CCCCCC'],
			'search_title_font_color' => [50 => '#000000', 51 => '#FFFFFF', 52 => '#000000', 53 => '#000000', 54 => '#000000', 55 => '#000000', 56 => '#000000', 57 => '#FFFFFF', 58 => '#000000', 59 =>'#372580'],

			// Categories
			'grid_category_icon_location' => [53 => 'left', 58 => '',59 => 'left',60 => 'left'],
			'grid_section_body_alignment' => [53 => 'left', 58 => ''],
			'grid_section_body_padding_bottom' => [50 => '20',52 => '20',53 => '0', 58 => ''],
			'grid_section_body_padding_left' => [53 => '76', 58 => ''],
			'grid_section_body_padding_right' => [53 => '0', 58 => ''],
			'grid_section_body_padding_top' => [50 => '15', 52 => '10', 53 => '0', 58 => ''],
			'section_category_font_color' => [51 => '#525296', 52 => '#000000', 53 => '#000000', 58 => ''],
			'section_border_color' => [ 50 => '#EEEEEE', 51 => '#ffffff', 52 => '#f7941d', 53 => '#CECED2', 58 => '', 59 => '#c5b7ff', 60 => '#F7AC08'],
			'section_border_radius' => [51 => '10', 53 => '0', 59 => '9', 60 => '4'],
			'section_border_width' => [50 => '2', 51 => '0', 52 => '2', 59 => '1', 60 => '1'],
			'grid_section_box_hover' => [51 => 'hover-4', 52 => 'hover-4', 53 => 'hover-4'],
			'grid_section_box_shadow' => [51 => 'section_light_shadow', 53 => 'section_light_shadow'],
			'grid_section_cat_name_padding_bottom' => [51 => '0', 52 => '0', 53 => '5', 59 => '1', 60 => '1'],
			'grid_section_cat_name_padding_left' => [51 => '10', 53 => '5', 59 => '0', 60 => '1'],
			'grid_section_cat_name_padding_right' => [53 => '0', 59 => '0', 60 => '1'],
			'grid_section_cat_name_padding_top' => [51 => '17', 53 => '0', 59 => '0', 60 => '0'],
			'grid_section_desc_padding_bottom' => [51 => '1', 52 => '10', 53 => '0'],
			'grid_section_desc_padding_left' => [51 => '10',53 => '55'],
			'grid_section_desc_padding_right' => [53 => '0'],
			'grid_section_desc_padding_top' => [51 => '0', 53 => '0'],
			'grid_section_desc_text_on' => [51 => 'on', 53 => 'on', 59 => 'off', 60 => 'off'],
			'grid_section_divider' => [52 => 'off', 53 => 'off', 59 => 'off', 60 => 'off'],
			'section_divider_color' => [51 => '#FFFFFF'],
			'grid_section_head_alignment' => [51 => 'left',53 => 'left',60 => 'left'],
			'section_head_description_font_color' => [52 => '#000000', 53 => '#7E8082'],
			'section_head_font_color' => [51 => '#525296', 52 => '#000000', 53 => '#000000'],
			'section_head_category_icon_color' => [50 => '#1e73be', 51 => '#525296', 52 => '#f7941d', 53 => '#56B6C5', 59=> '#834BF6'],
			'grid_section_head_padding_bottom' => [51 => '10', 53 => '5', 59 => '40', 60 => '1'],
			'grid_section_head_padding_left' => [51 => '10', 53 => '20'],
			'grid_section_head_padding_right' => [51 => '10', 53 => '20'],
			'grid_section_head_padding_top' => [51 => '10', 59 => '40', 60 => '1'],
			'grid_section_icon_padding_bottom' => [51 => '0', 53 => '0', 59 => '1', 60 => '1'],
			'grid_section_icon_padding_left' => [51 => '10', 53 => '0', 59 => '0', 60 => '0'],
			'grid_section_icon_padding_right' => [53 => '0', 59 => '15', 60 => '15'],
			'grid_section_icon_padding_top' => [51 => 10, 53 => '0', 59 => '1', 60 => '1'],
			'grid_section_icon_size' => [50 => '100', 51 =>'25', 53 => '50', 60 => '100'],
			'grid_section_article_count' => [51 => 'off', 59 => 'off', 60 => 'off'],

			// Sidebar
			'article_nav_sidebar_type_left' => [50 => 'eckb-nav-sidebar-v1', 51 => 'eckb-nav-sidebar-v1', 52 => 'eckb-nav-sidebar-v1', 53 => 'eckb-nav-sidebar-v1', 54 => 'eckb-nav-sidebar-v1', 55 => 'eckb-nav-sidebar-v1',
												56 => 'eckb-nav-sidebar-v1', 57 => 'eckb-nav-sidebar-v1', 58 => 'eckb-nav-sidebar-v1'],
			'sidebar_article_active_background_color' => [54 => '#666666',55 => '#e2f0fb', 56 => '#E2E4FB', 57 => '#FFFFFF', 58 => '#f8f8f8'],
			'sidebar_article_active_bold' => [52 => 'off', 53 => 'off', 54 => 'off',56 => 'off', 57 => 'off'],
			'sidebar_article_active_font_color' => [54=> '#FFFFFF', 56 => '#000000',55 => '#000000', 57 => '#f7941d', 58 => '#000000'],
			'sidebar_article_font_color' => [54=>'#000000',55=>'#3B3B3B',56=>'#3B3B3B', 57=>'#3B3B3B', 58=>'#3B3B3B'],
			'sidebar_article_icon_color' => [54 => '#666666', 55 => '#1d82d5', 56 => '#136eb5', 57 => '#3c134b', 58 => '#136eb5'],
			'sidebar_article_list_margin' => [54 => '15',55 => '30',56 => '30'],
			'sidebar_background_color' => [54 => '#FFFFFF', 55 => '#f8f8f8', 56 => '#f5f8ff', 57 => '#fbfbfb', 58 => '#FFFFFF'],
			'sidebar_expand_articles_icon' => [52 => 'ep_font_icon_plus', 53 => 'ep_font_icon_plus'],
			'sidebar_main_page_intro_text' => [52 => 'Welcome to our Knowledge Base.', 53 => 'Welcome to our Knowledge Base.'],
			'sidebar_section_body_padding_bottom' => [54 => '20', 55 => '20',56 => '20', 57 => '0'],
			'sidebar_section_body_padding_top' => [54 => '10', 55 => '10',56 => '10', 57 => '0'],
			'sidebar_section_body_padding_right'=> [54 => '10', 55 => '0', 56 => '0'],
			'sidebar_section_border_color' => [54 => '#E9ECF4', 55 => '#E9ECF4', 56 => '#E9ECF4', 57 => '#dbdbdb', 58 => '#f7f7f7'],
			'sidebar_section_border_radius' => [54 => '8', 55 => '0', 56 => '8', 57 => '0', 58 => '3',  59 => '17',  60 => '1'],
			'sidebar_section_border_width' => [54 => '1',55 => '1',56 => '1', 57 => '0', 58 => '0'],
			'sidebar_section_box_shadow' => [54 => 'section_light_shadow', 55 => 'no_shadow', 56 => 'section_light_shadow', 57 => 'no_shadow', 58 => 'no_shadow'],
			'sidebar_section_category_font_color' => [54 => '#000000', 55 => '#000000', 56 => '#000000', 57 => '#686868', 58 => '#000000'],
			'sidebar_section_category_icon_color' => [55 => '#1d82d5', 57 => '#212121', 58 => '#868686'],
			'sidebar_section_divider' => [52 => 'off', 53 => 'off', 56 => 'off', 57 => 'on'],
			'sidebar_section_divider_color' => [55 => '#1d82d5', 56 => '#00c1b6', 57 => '#25082f', 58 => '#f0f0f0'],
			'sidebar_section_head_alignment' => [55 => 'left', 56 => 'left', 57 => 'center'],
			'sidebar_section_head_background_color' => [52 => '#f4f4f4', 55 => '#FFFFFF', 56 => '#FFFFFF', 57 => '#FFFFFF', 58 => '#FFFFFF'],
			'sidebar_section_head_description_font_color' => [55 => '#bfdac1', 56 => '#bfdac1', 58 => '#355666'],
			'sidebar_section_head_font_color' => [50 => '#1168bf', 51 => '#000000', 52 => '#f7941d', 53 => '#56B6C6', 54 => '#000000', 55 => '#000000', 56 => '#000000', 57 => '#212121', 58 => '#136eb5'],
			'sidebar_section_head_padding_left' => [54 => '20', 55 => '20', 56 => '20', 57 => '0'],
			'sidebar_section_head_padding_top' => [54 => '20', 55 => '20', 56 => '20', 57 => '20'],
			'sidebar_section_head_padding_right' => [54 => '20', 55 => '20', 56 => '20', 57 => '0'],
			'sidebar_section_head_padding_bottom' => [54 => '20', 55 => '20', 56 => '20', 57 => '20'],

			'sidebar_show_articles_before_categories' => [54 => 'on', 55 => 'on', 56 => 'on',57 => 'off'],
			'sidebar_side_bar_width' => [57 => '20'],
			'sidebar_top_categories_collapsed' => [52 => 'off', 53 => 'off', 54 => 'off', 55 => 'on', 56 => 'on'],
			'sidebar_article_icon_toggle' => [56 => 'off'],

			// Articles
			'back_navigation_text_color' => [50 => '#1e73be', 51 => '#1e73be', 52 => '#1e73be', 53 => '#56B6C6', 54 => '#1e73be', 55 => '#1d82d5', 56 => '#1e73be', 57 => '#1e73be', 58 => '#00c1b6'],
			'breadcrumb_text_color' => [50 => '#1e73be', 51 => '#1e73be', 52 => '#1e73be', 53 => '#56B6C6', 54 => '#1e73be', 55 => '#1d82d5', 56 => '#1e73be', 57 => '#1e73be', 58 => '#136eb5'],

			// Other
			'search_title' => [50 => 'Have a Question?', 51 => 'Support Center', 52 => '', 53 => 'Support Center', 54 => 'Self Help Documentation', 55 => '', 56 => 'Support Center', 57 => '', 58 => 'What are you looking for?'],

			// Typography is reset for each preset
			'grid_section_typography' => [],
			'grid_section_description_typography' => [51 => ['font-size' => '14'], 53 => ['font-size' => '14']],
			'grid_section_article_typography' => [],

			'sidebar_section_category_typography' => [
				57 => ['font-size' => '18']
			],
			'sidebar_section_category_typography_desc' => [57 => ['font-size' => '12']],
			'sidebar_section_subcategory_typography' => [
				54 => ['font-size' => '15'],
				55 => ['font-size' => '15'],
				56 => ['font-size' => '15'],
				57 => ['font-size' => '15'],
				58 => ['font-size' => '15']
			],
			'sidebar_section_body_typography' => [
				54 => ['font-size' => '14'],
				55 => ['font-size' => '14'],
				56 => ['font-size' => '14'],
				57 => ['font-size' => '14'],
				58 => ['font-size' => '14']
			],
		];
	}
	
	private static $themes_rtl = [

		// Categories
		'grid_section_body_alignment' => [53=>'right'],
		'grid_section_body_padding_left' => [53=>'0'],
		'grid_section_body_padding_right' => [53=>'80'],
		'grid_section_cat_name_padding_left' => [53=>'0'],
		'grid_section_cat_name_padding_right' => [53=>'80'],
		
		'grid_section_desc_padding_left' => [53=>'0'],
		'grid_section_desc_padding_right' => [53=>'80'],
		
		'grid_section_head_alignment' => [53=>'right'],
		'grid_section_icon_padding_left' => [53=>'0'],
		'grid_section_icon_padding_right' => [51=>'10', 53=>'20'],
		
		// Sidebar
		'sidebar_section_head_alignment' => [54=>'right', 55=>'right', 56=>'right', 57=>'right', 58=>'right'],
	];
}