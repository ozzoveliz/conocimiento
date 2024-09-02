<?php

/**
 * Configuration for the front end editor
 */

class ASEA_KB_Editor_Article_Page_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';
	const EDITOR_TAB_GLOBAL = 'global';
	const EDITOR_TAB_DISABLED = 'hidden';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';
	const EDITOR_GROUP_MULTIPLE = 'multiple';

	/**
	 * Serach Box zone
	 * @return array
	 */
	private static function search_box_zone() {

		$settings = [

			// Style Tab
			'advanced_search_presets' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'type' => 'preset',
				'default' => 'current',
				'label' => __( 'Pre-made Designs', 'echo-advanced-search' ),
				'options' => [ 'current' => __( 'Current', 'echo-advanced-search' ) ] +
				             ASEA_KB_Config_Styles::get_advanced_search_box_style_names(),
				'name' => 'advanced_search_presets',
				'style' => 'small'
			],
			
			'advanced_search_ap_background_color' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-section-1',
				'style_name' => 'background-color'
			],

			// Features Tab
			'advanced_search_ap_visibility' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],

			'advanced_search_ap_box_font_width' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '#asea-search-description-1, #asea-search-description-2',
				'style_name' => 'width',
				'postfix' => '%'
			],

			'advanced_search_ap_background_image_header'        => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Background Image', 'echo-advanced-search')
			],
			
			'advanced_search_ap_background_image_url'           => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			
			'advanced_search_ap_background_image_position_x'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'     =>      '#asea-search-background-image-1',
				'style_name' => 'background-position-x',
			],
			
			'advanced_search_ap_background_image_position_y'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'     =>      '#asea-search-background-image-1',
				'style_name' => 'background-position-y',
			],

			'advanced_search_ap_background_pattern_header'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Background Pattern Image', 'echo-advanced-search'),
			],
			
			'advanced_search_ap_background_pattern_image_url'           => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload'     =>      1
			],
			
			'advanced_search_ap_background_pattern_image_position_x'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'     =>      '#asea-search-pattern-1',
				'style_name' => 'background-position-x',
			],
			
			'advanced_search_ap_background_pattern_image_position_y'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'     =>      '#asea-search-pattern-1',
				'style_name' => 'background-position-y',
			],
			
			'advanced_search_ap_background_pattern_image_opacity'       => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'     =>      '#asea-search-pattern-1',
				'style_name' => 'opacity'
			],

			'advanced_search_ap_background_gradient_header'     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Gradient Background Color', 'echo-advanced-search'),
			],
			
			'advanced_search_ap_background_gradient_toggle'     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			
			'advanced_search_ap_background_gradient' => [
				'editor_tab'        => self::EDITOR_TAB_FEATURES,
				'group_type'        => self::EDITOR_GROUP_MULTIPLE,
				'style_template'    => 'linear-gradient( advanced_search_ap_background_gradient_degreedeg, advanced_search_ap_background_gradient_from_color 0%, advanced_search_ap_background_gradient_to_color 100% )',
				'target_selector'   => '#asea-search-gradient-1',
				'style_name'        => 'background',
				'toggler'           => 'advanced_search_ap_background_gradient_toggle',
				'subfields' => [
					'advanced_search_ap_background_gradient_from_color' => [],
					'advanced_search_ap_background_gradient_to_color'   => [],
					'advanced_search_ap_background_gradient_degree'     => [],
				]
			],
			
			'advanced_search_ap_background_gradient_opacity'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'style_name' => 'opacity',
				'target_selector' => '#asea-search-gradient-1',
				'toggler'           => 'advanced_search_ap_background_gradient_toggle'
			],

			// Advanced Tab
			'advanced_search_ap_box_padding'    => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Padding', 'echo-advanced-search'),
				'units' => 'px',
				'subfields' => [
					'advanced_search_ap_box_padding_left' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'padding-left',
						'postfix' => 'px'
					],
					'advanced_search_ap_box_padding_top' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'padding-top',
						'postfix' => 'px'
					],
					'advanced_search_ap_box_padding_right' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'padding-right',
						'postfix' => 'px'
					],
					'advanced_search_ap_box_padding_bottom' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'padding-bottom',
						'postfix' => 'px'
					],
				]
			],
			
			'advanced_search_ap_box_margin' => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Margin', 'echo-advanced-search'),
				'units' => 'px',
				'subfields' => [
					'advanced_search_ap_box_margin_top' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'margin-top',
						'postfix' => 'px'
					],
					'advanced_search_ap_box_margin_bottom' => [
						'target_selector' => '#asea-section-1',
						'style_name' => 'margin-bottom',
						'postfix' => 'px'
					],
				]
			],

		];

		return [
			'search_box_zone' => [
				'title'     =>  __( 'Advanced Search Box', 'echo-advanced-search'),
				'classes'   => '#asea-doc-search-container',
				'settings'  => $settings,
				'disabled_settings' => [
					'advanced_search_ap_visibility' => 'on'
				],
				'parent_zone_tab_title' => __( 'Advanced Search Box', 'echo-advanced-search' ),
			]];
	}

	/**
	 * Search Title zone
	 * @return array
	 */
	private static function search_title_zone() {

		$settings = [

			// Content Tab
			'advanced_search_ap_title' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#asea-search-title',
				'html' => '1',
				'description' => __( 'a, br, em, strong, i and span (with class property) allowed', 'echo-advanced-search' )
			],

			// Style Tab
			'advanced_search_ap_title_typography' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-title',
			],
			'advanced_search_ap_title_font_color'           => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-description-1,#asea-search-description-2, #asea-search-title,  #asea-search-description-1,  #asea-search-description-2',
				'style_name' => 'color'
			],
			'advanced_search_ap_link_font_color'            => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => ' #asea-doc-search-container #asea-section-1 a,  #asea-doc-search-container #asea-section-1 a',
				'style_name' => 'color'
			],
			'advanced_search_ap_title_tag'          => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'reload' => 1,
				'separator_above' => 'yes'
			],

			// Features Tab
			'advanced_search_ap_title_toggle' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'advanced_search_ap_title_text_shadow_toggle'   => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			'advanced_search_ap_title_text_shadow' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'group_type' => self::EDITOR_GROUP_MULTIPLE,
				'toggler'           => 'advanced_search_ap_title_text_shadow_toggle',
				'style_template' => 'advanced_search_ap_title_text_shadow_x_offset  advanced_search_ap_title_text_shadow_y_offset advanced_search_ap_title_text_shadow_blur advanced_search_ap_title_font_shadow_color',
				'target_selector' => '#asea-search-title',
				'style_name' => 'text-shadow',
				'subfields' => [
					'advanced_search_ap_title_font_shadow_color'    => [

					],
					'advanced_search_ap_title_text_shadow_x_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_title_text_shadow_y_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_title_text_shadow_blur'     => [
						'postfix' => 'px'
					],
				]
			],

			// Advanced Tab
			'advanced_search_ap_title_padding_bottom' => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'target_selector' => '#asea-search-title',
				'style_name' => 'padding-bottom',
				'postfix' => 'px'
			],

		];

		return [
			'search_title_zone' => [
				'title'     =>  __( 'Search Title', 'echo-advanced-search'),
				'classes'   => '#asea-search-title',
				'settings'  => $settings,
				'disabled_settings' => [
					'advanced_search_ap_title_toggle' => 'off'
				]
			]];
	}

	/**
	 * Search Below Title zone
	 * @return array
	 */
	private static function search_below_title_zone() {

		$settings = [

			// Content Tab
			'advanced_search_ap_description_below_title_toggle' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'reload' => 1,
			],

			'advanced_search_ap_description_below_title'        => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#asea-search-description-1',
				'html' => '1',
				'description' => __( 'a, br, em, strong, i and span (with class property) allowed', 'echo-advanced-search' )
			],
			

			// Style Tab
			'advanced_search_ap_description_below_title_typography' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-description-1',
			],

			'advanced_search_ap_title_font_color'           => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-description-1,#asea-search-description-2, #asea-search-title,  #asea-search-description-1,  #asea-search-description-2',
				'style_name' => 'color',
				'toggler' => [
					'advanced_search_ap_title_toggle' => 'off',
				]
			],

			'advanced_search_ap_link_font_color'            => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => ' #asea-doc-search-container #asea-section-1 a,  #asea-doc-search-container #asea-section-1 a',
				'style_name' => 'color',
			],

			// Features Tab
			'advanced_search_ap_description_below_title_text_shadow_toggle'     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			
			'advanced_search_ap_description_text_shadow' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'group_type' => self::EDITOR_GROUP_MULTIPLE,
				'toggler'           => 'advanced_search_ap_description_below_title_text_shadow_toggle',
				'style_template' => 'advanced_search_ap_description_below_title_text_shadow_x_offset  advanced_search_ap_description_below_title_text_shadow_y_offset ' .
				                    'advanced_search_ap_description_below_title_text_shadow_blur advanced_search_ap_description_below_title_font_shadow_color',
				'target_selector' => '#asea-search-description-1',
				'style_name' => 'text-shadow',
				'subfields' => [
					'advanced_search_ap_description_below_title_font_shadow_color'    => [

					],
					'advanced_search_ap_description_below_title_text_shadow_x_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_description_below_title_text_shadow_y_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_description_below_title_text_shadow_blur'     => [
						'postfix' => 'px'
					],
				]
			],

			// Advanced Tab
			'advanced_search_ap_description_below_title_padding'                => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Padding', 'echo-advanced-search'),
				'units' => 'px',
				'subfields' => [
					'advanced_search_ap_description_below_title_padding_top' => [
						'target_selector' => '#asea-search-description-1',
						'style_name' => 'padding-top',
						'postfix' => 'px'
					],
					'advanced_search_ap_description_below_title_padding_bottom' => [
						'target_selector' => '#asea-search-description-1',
						'style_name' => 'padding-bottom',
						'postfix' => 'px'
					],
				]
			],

		];

		return [
			'search_below_title_zone' => [
				'title'     =>  __( 'Search Description', 'echo-advanced-search'),
				'classes'   => '#asea-search-description-1',
				'settings'  => $settings,
				'disabled_settings' => [
					'advanced_search_ap_description_below_title_toggle' => 'off'
				]
			]];
	}

	/**
	 * Search Input box zone
	 * @return array
	 */
	private static function search_input_zone() {

		$settings = [

			// Content Tab
			'advanced_search_ap_box_hint'                       => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#asea_advanced_search_terms',
				'target_attr' => 'placeholder'
			],
			'advanced_search_ap_filter_header'          => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'Category Filter', 'echo-advanced-search'),
			],

			'advanced_search_ap_filter_indicator_text'  => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.asea-search-filter-text',
				'text' => '1',
			],

			'advanced_search_ap_title_by_filter'        => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.asea-search-filter-container>fieldset>legend',
				'text' => '1',
			],

			'advanced_search_ap_title_clear_results'    => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#asea-search-filter-clear-results',
				'text' => '1',
			],

			'advanced_search_ap_results_header'         => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'Search Results', 'echo-advanced-search'),
			],
			
			'advanced_search_ap_no_results_found'       => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'html' => '1',
				'description' => __( 'a, br, em, strong, i and span (with class property) allowed', 'echo-advanced-search' ),
			],
			
			'advanced_search_ap_more_results_found'     => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
			],

			// Style Tab
			'advanced_search_ap_box_input_width'                    => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea_search_form',
				'style_name' => 'width',
				'postfix' => '%'
			],
			
			'advanced_search_ap_input_box_typography' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-search-box',
			],
			
			'advanced_search_ap_text_input_background_color'        => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-search-box',
				'style_name' => 'background-color'
			],

			'advanced_search_ap_input_border_width'                 => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-search-box',
				'style_name' => 'border-width',
				'postfix' => 'px',
				'separator_above' => 'yes'
			],
			
			'advanced_search_ap_input_box_radius'                   => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-search-box',
				'style_name' => 'border-radius',
				'postfix' => 'px'
			],
			
			'advanced_search_ap_text_input_border_color'            => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-search-box',
				'style_name' => 'border-color'
			],

			'advanced_search_ap_search_results_article_font_size'   => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'style_name' => 'font-size',
				'postfix' => 'px',
				'separator_above' => 'yes'
			],
			'advanced_search_ap_input_box_shadow_header'        => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'type' => 'header',
				'content' => __( 'Box Shadow', 'echo-advanced-search'),
			],
			'advanced_search_ap_input_box_shadow' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'group_type' => self::EDITOR_GROUP_MULTIPLE,
				'style_template' => 'advanced_search_ap_input_box_shadow_x_offset  advanced_search_ap_input_box_shadow_y_offset advanced_search_ap_input_box_shadow_blur advanced_search_ap_input_box_shadow_spread rgba(advanced_search_ap_input_box_shadow_rgba)',
				'target_selector' => '.asea-search-box',
				'style_name' => 'box-shadow',
				'subfields' => [
					'advanced_search_ap_input_box_shadow_rgba'    => [

					],
					'advanced_search_ap_input_box_shadow_x_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_input_box_shadow_y_offset' => [
						'postfix' => 'px'
					],
					'advanced_search_ap_input_box_shadow_blur'     => [
						'postfix' => 'px'
					],
					'advanced_search_ap_input_box_shadow_spread'     => [
						'postfix' => 'px'
					],
				]
			],

			// Features Tab
			'advanced_search_ap_auto_complete_wait'             => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
			],
			
			'advanced_search_ap_results_page_size'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
			],

			'advanced_search_ap_input_box_search_icon_placement'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes',
			],
			
			'advanced_search_ap_input_box_loading_icon_placement'   => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],

			'advanced_search_ap_filter_header_2'                => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Category Filter', 'echo-advanced-search'),
			],

			'advanced_search_ap_filter_toggle'                  => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],

			'advanced_search_ap_filter_box_font_color'          => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '.asea-search-filter-icon-container',
				'style_name' => 'color',
				'toggler' => 'advanced_search_ap_filter_toggle',
			],

			'advanced_search_ap_filter_box_background_color'    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '.asea-search-filter-icon-container',
				'style_name' => 'background-color',
				'toggler' => 'advanced_search_ap_filter_toggle',
			],

			'advanced_search_ap_filter_category_level'          => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler' => 'advanced_search_ap_filter_toggle',
			],

			'advanced_search_ap_filter_dropdown_width'          => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'style'       => 'small',
				'target_selector' => '.asea-search-filter-container',
				'style_name' => 'max-width',
				'postfix' => 'px',
				'toggler' => 'advanced_search_ap_filter_toggle',
			],

			'advanced_search_ap_box_results_header'             => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Search Results', 'echo-advanced-search'),
			],
			
			'advanced_search_ap_box_results_style'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
			],
			
			'advanced_search_ap_results_list_size'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
			],
			
			'advanced_search_ap_show_top_category'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
			],
			
			'advanced_search_ap_search_result_category_color'   => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler'           => 'advanced_search_ap_show_top_category'
			],

			// Advanced Tab
			'advanced_search_ap_input_box_padding' => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Input Box Padding', 'echo-advanced-search'),
				'units' => 'px',
				'subfields' => [
					'advanced_search_ap_input_box_padding_left' => [
						'target_selector' => '.asea-search-box',
						'style_name' => 'padding-left',
						'postfix' => 'px',
					],
					'advanced_search_ap_input_box_padding_top' => [
						'target_selector' => '#asea_advanced_search_terms, .asea-search-filter-icon-container, .asea-search-box__icons-wrap',
						'style_name' => 'padding-top',
						'postfix' => 'px',
					],
					'advanced_search_ap_input_box_padding_right' => [
						'target_selector' => '.asea-search-box',
						'style_name' => 'padding-right',
						'postfix' => 'px',
					],
					'advanced_search_ap_input_box_padding_bottom' => [
						'target_selector' => '#asea_advanced_search_terms, .asea-search-filter-icon-container, .asea-search-box__icons-wrap',
						'style_name' => 'padding-bottom',
						'postfix' => 'px',
					],
				]
			],

		];

		return [
			'search_input_zone' => [
				'title'     =>  __( 'Search Input Box', 'echo-advanced-search'),
				'classes'   => '#asea-doc-search-box-container',
				'settings'  => $settings
			]];
	}

	/**
	 * Serach Box Below Input zone
	 * @return array
	 */
	private static function search_box_below_input_zone() {

		$settings = [

			'advanced_search_ap_description_below_input_toggle' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'reload' => 1,
			],

			// Text
			'advanced_search_ap_description_below_input' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#asea-search-description-2',
				'html' => '1',
				'description' => __( 'a, br, em, strong, i and span (with class property) allowed', 'echo-advanced-search' )
			],

			// Description Below Search Input - Style
			'advanced_search_ap_description_below_input_typography' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-description-2',
			],

			'advanced_search_ap_title_font_color'           => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#asea-search-description-1,#asea-search-description-2, #asea-search-title,  #asea-search-description-1,  #asea-search-description-2',
				'style_name' => 'color',
				'toggler' => [
					'advanced_search_ap_title_toggle' => 'off',
				]
			],

			'advanced_search_ap_link_font_color'            => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => ' #asea-doc-search-container #asea-section-1 a,  #asea-doc-search-container #asea-section-1 a',
				'style_name' => 'color',
			],

			'advanced_search_ap_description_below_input_padding' => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Padding', 'echo-advanced-search'),
				'units' => 'px',
				'subfields' => [
					'advanced_search_ap_description_below_input_padding_top' => [
						'target_selector' => '#asea-search-description-2',
						'style_name' => 'padding-top',
						'postfix' => 'px'
					],
					'advanced_search_ap_description_below_input_padding_bottom' => [
						'target_selector' => '#asea-search-description-2',
						'style_name' => 'padding-bottom',
						'postfix' => 'px'
					],
				]
			],
		];

		return [
			'search_box_below_input_zone' => [
				'title'     =>  __( 'Search Text Below Input', 'echo-advanced-search' ),
				'classes'   => '#asea-sub-section-1-3',
				'settings'  => $settings,
				'disabled_settings' => [
					'advanced_search_ap_description_below_input_toggle' => 'off'
				]
			]];
	}

	/**
	 * Retrieve Editor configuration
	 * @return array
	 */
	public static function get_config() {

		$editor_config = [];

		$editor_config += self::search_box_zone();
		$editor_config += self::search_title_zone();
		$editor_config += self::search_below_title_zone();
		$editor_config += self::search_input_zone();
		$editor_config += self::search_box_below_input_zone();

		return $editor_config;
	}
}