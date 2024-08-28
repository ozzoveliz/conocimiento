<?php

/**
 * Lists settings, default values and display of SIDEBAR layout.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_KB_Config_Layout_Sidebar {

    /**
     * Defines KB configuration for this theme.
     * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => 'false' )
     *
     * @return array with both basic and theme-specific configuration
     */
    public static function get_fields_specification() {

        $config_specification = array(

            /******************************************************************************
             *
             *  Main or Article Page - Layout and Style
             *
             ******************************************************************************/

            /***  Main or Article Page -> General ***/

            'sidebar_side_bar_width' => array(
                'label'       => __( 'Sidebar Width ( % )', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_side_bar_width',
                'max'         => '35',
                'min'         => '15',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '25'
            ),
            'sidebar_side_bar_height_mode' => array(
                'label'       => __( 'Height Mode', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_side_bar_height_mode',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'side_bar_no_height' => __( 'Variable', 'echo-elegant-layouts' ),
                    'side_bar_fixed_height' => __( 'Fixed (Scrollbar)', 'echo-elegant-layouts' ) ),
                'default'     => 'side_bar_no_height'
            ),
            'sidebar_side_bar_height' => array(
                'label'       => __( 'Height ( px )', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_side_bar_height',
                'max'         => '1000',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '350'
            ),
            'sidebar_scroll_bar' => array(
                'label'       => __( 'Scroll Bar style', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_scroll_bar',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'slim_scrollbar'    => _x( 'Slim','echo-elegant-layouts' ),
                    'default_scrollbar' => _x( 'Default', 'echo-elegant-layouts' ) ),
                'default'     => 'slim_scrollbar'
            ),

			'sidebar_section_category_typography' => array(
				'label'       => __( 'Category Typography', 'echo-knowledge-base' ),
				'name'        => 'sidebar_section_category_typography',
				'type'        => ELAY_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '18',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
	        'sidebar_section_category_typography_desc' => array(
		        'label'       => __( 'Category Description Typography', 'echo-knowledge-base' ),
		        'name'        => 'sidebar_section_category_typography_desc',
		        'type'        => ELAY_Input_Filter::TYPOGRAPHY,
		        'default'     => array(
			        'font-family' => '',
			        'font-size' => '16',
			        'font-size-units' => 'px',
			        'font-weight' => '',
		        )
	        ),
	        'sidebar_section_body_typography' => array(
		        'label'       => __( 'Typography', 'echo-knowledge-base' ),
		        'name'        => 'sidebar_section_body_typography',
		        'type'        => ELAY_Input_Filter::TYPOGRAPHY,
		        'default'     => array(
			        'font-family' => '',
			        'font-size' => '16',
			        'font-size-units' => 'px',
			        'font-weight' => '',
		        )
	        ),
            'sidebar_top_categories_collapsed' => array(
                        'label'       => __( 'Top Categories Collapsed', 'echo-elegant-layouts' ),
                        'name'        => 'sidebar_top_categories_collapsed',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'off'
                ),
            'sidebar_nof_articles_displayed' => array(
                'label'       => __( 'Number of Articles Listed', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_nof_articles_displayed',
                'max'         => '200',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => 15,
            ),
            'sidebar_show_articles_before_categories' => array(
                'label'       => __( 'Show Articles', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_show_articles_before_categories',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'on' => __( 'Before Categories', 'echo-elegant-layouts' ),
                    'off' => __( 'After Categories', 'echo-elegant-layouts' ),
                ),
                'default'     => 'off'
            ),
            'sidebar_expand_articles_icon' => array(
                'label'       => __( 'Icon to Expand/Collapse Articles', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_expand_articles_icon',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array( 'ep_font_icon_plus_box' => _x( 'Plus Box', 'icon type', 'echo-elegant-layouts' ),
                                        'ep_font_icon_plus' => _x( 'Plus Sign', 'icon type', 'echo-elegant-layouts' ),
                                        'ep_font_icon_right_arrow' => _x( 'Arrow Triangle', 'icon type', 'echo-elegant-layouts' ),
                                        'ep_font_icon_arrow_carrot_right' => _x( 'Arrow Caret', 'icon type', 'echo-elegant-layouts' ),
                                        'ep_font_icon_arrow_carrot_right_circle' => _x( 'Arrow Caret 2', 'icon type', 'echo-elegant-layouts' ),
                                        'ep_font_icon_folder_add' => _x( 'Folder', 'icon type', 'echo-elegant-layouts' ) ),
                'default'     => 'ep_font_icon_arrow_carrot_right'
            ),

            /***  Main or Article Page -> Search Box ***/

	        // TODO REMOVE all 'sidebar_search_' fields
            'sidebar_search_layout' => array(
                'label'       => __( 'Layout', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_layout',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'elay-search-form-1' => __( 'Rounded search button is on the right', 'echo-elegant-layouts' ),
                    'elay-search-form-4' => __( 'Squared search Button is on the right', 'echo-elegant-layouts' ),
                    'elay-search-form-2' => __( 'Search button is below', 'echo-elegant-layouts' ),
                    'elay-search-form-3' => __( 'No search button', 'echo-elegant-layouts' ),
                    'elay-search-form-0' => __( 'No search box', 'echo-elegant-layouts' )
                ),
                'default'     => 'elay-search-form-1'
            ),
			'sidebar_search_box_collapse_mode' => array(
		        'label'       => __( 'Collapse mode: Always Open', 'echo-elegant-layouts' ),
		        'name'        => 'sidebar_search_box_collapse_mode',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
            'sidebar_search_input_border_width' => array(
                'label'       => __( 'Border (px)', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_input_border_width',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 1
            ),
            'sidebar_search_box_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 0
            ),
            'sidebar_search_box_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 0
            ),
            'sidebar_search_box_margin_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_margin_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 0
            ),
            'sidebar_search_box_margin_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_margin_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 40
            ),
            'sidebar_search_box_input_width' => array(
                'label'       => __( 'Width (%)', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_input_width',
                'max'         => '100',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 50
            ),


	        /***   Main or Article Page -> Articles Listed in Sub-Category ***/

            'sidebar_section_head_alignment' => array(
                'label'       => __( 'Category Text Alignment', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_alignment',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'left' => __( 'Left', 'echo-elegant-layouts' ),
                    'center' => __( 'Centered', 'echo-elegant-layouts' ),
                    'right' => __( 'Right', 'echo-elegant-layouts' )
                ),
                'default'     => 'left'
            ),
            'sidebar_section_head_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_padding_top',
                'max'         => '20',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
            'sidebar_section_head_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_padding_bottom',
                'max'         => '20',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
            'sidebar_section_head_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_padding_left',
                'max'         => '20',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
            'sidebar_section_head_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_padding_right',
                'max'         => '20',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
            'sidebar_section_desc_text_on' => array(
                'label'       => __( 'Category Description', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_desc_text_on',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'off'
            ),
            'sidebar_section_border_radius' => array(
                'label'       => __( 'Radius', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_border_radius',
                'max'         => '30',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 5
            ),
            'sidebar_section_border_width' => array(
                'label'       => __( 'Width', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_border_width',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 1
            ),
            'sidebar_section_box_shadow' => array(
                'label'       => __( 'Navigation Shadow', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_box_shadow',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'no_shadow' => __( 'No Shadow', 'echo-elegant-layouts' ),
                    'section_light_shadow' => __( 'Light Shadow', 'echo-elegant-layouts' ),
                    'section_medium_shadow' => __( 'Medium Shadow', 'echo-elegant-layouts' ),
                    'section_bottom_shadow' => __( 'Bottom Shadow', 'echo-elegant-layouts' )
                ),
                'default'     => 'section_medium_shadow'
            ),
            'sidebar_section_divider' => array(
                'label'       => __( 'On/Off', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_divider',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'on'
            ),
            'sidebar_section_divider_thickness' => array(
                'label'       => __( 'Thickness ( px )', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_divider_thickness',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => 1
            ),
            'sidebar_section_box_height_mode' => array(
                'label'       => __( 'Height Mode', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_box_height_mode',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'section_no_height' => __( 'Variable', 'echo-elegant-layouts' ),
                    'section_min_height' => __( 'Minimum', 'echo-elegant-layouts' ),
                    'section_fixed_height' => __( 'Maximum', 'echo-elegant-layouts' )  ),
                'default'     => 'section_no_height'
            ),
            'sidebar_section_body_height' => array(
                'label'       => __( 'Height ( px )', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_body_height',
                'max'         => '1000',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 350
            ),
            'sidebar_section_body_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_body_padding_top',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
            'sidebar_section_body_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_body_padding_bottom',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 10
            ),
            'sidebar_section_body_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_body_padding_left',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 0
            ),
            'sidebar_section_body_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_body_padding_right',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 5
            ),
            'sidebar_article_underline' => array(
                'label'       => __( 'Article Underline Hover', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_underline',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'off'
            ),
	        'sidebar_article_active_bold' => array(
		        'label'       => __( 'Article Active Bold', 'echo-elegant-layouts' ),
		        'name'        => 'sidebar_article_active_bold',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
            'sidebar_article_list_margin' => array(
                'label'       => __( 'Indentation', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_list_margin',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 10
            ),
            'sidebar_article_list_spacing' => array(		// TODO REMOVE
                'label'       => __( 'Between ( px )', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_list_spacing',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => 8
            ),
			

            /******************************************************************************
             *
             *  Main or Article Page - All Colors Settings
             *
             ******************************************************************************/

            /***  Main or Article Page -> Colors -> General  ***/

            'sidebar_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#fdfdfd'
            ),


            /***  Main or Article Page -> Colors -> Search Box  ***/

	        // TODO REMOVE
            'sidebar_search_title_font_color' => array(
                'label'       => __( 'Title', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_title_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#686868'
            ),
            'sidebar_search_background_color' => array(
                'label'       => __( 'Search Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#F1F1F1'
            ),
            'sidebar_search_text_input_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_text_input_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'sidebar_search_text_input_border_color' => array(
                'label'       => __( 'Border', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_text_input_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'sidebar_search_btn_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_btn_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#686868'
            ),
            'sidebar_search_btn_border_color' => array(
                'label'       => __( 'Border', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_btn_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#F1F1F1'
            ),


            /***  Main or Article Page -> Colors -> Articles Listed in Category Box ***/

            'sidebar_article_font_color' => array(
                'label'       => __( 'Article Color', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#b3b3b3'
            ),
            'sidebar_article_icon_color' => array(
                'label'       => __( 'Icon Color', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_icon_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#525252'
            ),
            'sidebar_article_active_font_color' => array(
                'label'       => __( 'Active Article Color', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_active_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#000000'
            ),
            'sidebar_article_active_background_color' => array(
                'label'       => __( 'Active Article Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_article_active_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#e8e8e8'
            ),
            'sidebar_section_head_font_color' => array(
                'label'       => __( 'Category Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#525252'
            ),
            'sidebar_section_head_background_color' => array(
                'label'       => __( 'Category Text Background', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#f1f1f1'
            ),
            'sidebar_section_head_description_font_color' => array(
                'label'       => __( 'Category Description', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_head_description_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#b3b3b3'
            ),
            'sidebar_section_border_color' => array(
                'label'       => __( 'Border', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#F7F7F7'
            ),
            'sidebar_section_divider_color' => array(
                'label'       => __( 'Color', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_divider_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#CDCDCD'
            ),
            'sidebar_section_category_font_color' => array(
                'label'       => __( 'Subcategory Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_category_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#868686'
            ),
			'sidebar_section_subcategory_typography' => array(
				'label'       => __( 'Subcategory Typography', 'echo-knowledge-base' ),
				'name'        => 'sidebar_section_subcategory_typography',
				'type'        => ELAY_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '16',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
            'sidebar_section_category_icon_color' => array(
                'label'       => __( 'Subcategory Expand Icon', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_section_category_icon_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#868686'
            ),


            /***  Main Page -> Text ***/

            'sidebar_main_page_intro_text' => array(
                'label'       => __( 'Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_main_page_intro_text',
                'max'         => '50000',
                'min'         => '1',
                'mandatory'   => false,
                'type'        => ELAY_Input_Filter::WP_EDITOR,
                'default'     => sprintf(
	                __( '%sWelcome to our Knowledge Base.%s%sTo edit this welcome text go to the Settings admin page, click on Labels and edit Sidebar Layout Introduction Page text.%s', 'echo-knowledge-base' ),
	                '<h2>', '</h2>', '<h3 style="color: red;"><strong>', '</strong></h3>'
                ),
            ),

            /******************************************************************************
             *
             *  Main or Article Page -> Front-End Text
             *
             ******************************************************************************/

	        // TODO REMOVE all below
            'sidebar_search_title' => array(
                'label'       => __( 'Search Title', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_title',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'How Can We Help?', 'echo-elegant-layouts' )
            ),
            'sidebar_search_box_hint' => array(
                'label'       => __( 'Search Hint', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_box_hint',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Search the documentation...', 'echo-elegant-layouts' )
            ),
            'sidebar_search_results_msg' => array(
                'label'       => __( 'Search Results Message', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_search_results_msg',
                'max'         => '150',
                'mandatory' => false,
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Search Results for', 'echo-elegant-layouts' )
            ),
            'sidebar_no_results_found' => array(
                'label'       => __( 'No Matches Found Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_no_results_found',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'No matches found', 'echo-elegant-layouts' )
            ),
            'sidebar_min_search_word_size_msg' => array(
                'label'       => __( 'Minimum Search Word Size Message', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_min_search_word_size_msg',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Enter a word with at least one character.', 'echo-elegant-layouts' )
            ),
            'sidebar_category_empty_msg' => array(
                'label'       => __( 'Empty Category Notice', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_category_empty_msg',
                'max'         => '150',
                'mandatory' => false,
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Articles coming soon', 'echo-elegant-layouts' )
            ),
            'sidebar_collapse_articles_msg' => array(
                'label'       => __( 'Collapse Articles Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_collapse_articles_msg',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Collapse Articles', 'echo-elegant-layouts' )
            ),
            'sidebar_show_all_articles_msg' => array(
                'label'       => __( 'Show All Articles Text', 'echo-elegant-layouts' ),
                'name'        => 'sidebar_show_all_articles_msg',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Show all articles', 'echo-elegant-layouts' )
            )
        );

        return $config_specification;
    }

	/*
     * Return default values from given specification.
     * @param $config_specs
     * @return array
     */
    public static function get_specs_defaults( $config_specs ) {
        $default_configuration = array();
        foreach( $config_specs as $key => $spec ) {
            $default = isset($spec['default']) ? $spec['default'] : '';
            $default_configuration += array( $key => $default );
        }
        return $default_configuration;
    }
}
