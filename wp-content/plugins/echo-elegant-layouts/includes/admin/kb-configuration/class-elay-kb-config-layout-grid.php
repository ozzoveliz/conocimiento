<?php

/**
 * Lists settings, default values and display of GRID layout.
 *
 * @copyright   Copyright (C) 2018 Echo Plugins
 */
class ELAY_KB_Config_Layout_Grid {

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
             *  KB Main Layout - Layout and Style
             *
             ******************************************************************************/

            /***  General ***/
            'grid_section_typography' => array(
				'label'       => __( 'Name Typography', 'echo-knowledge-base' ),
				'name'        => 'grid_section_typography',
				'type'        => ELAY_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '21',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'grid_section_description_typography' => array(
				'label'       => __( 'Description Typography', 'echo-knowledge-base' ),
				'name'        => 'grid_section_description_typography',
				'type'        => ELAY_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '19',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'grid_section_article_typography' => array(
				'label'       => __( 'Article Counter Typography', 'echo-knowledge-base' ),
				'name'        => 'grid_section_article_typography',
				'type'        => ELAY_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '14',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
            'grid_nof_columns' => array(
                'label'       => __( 'Number of Columns', 'echo-elegant-layouts' ),
                'name'        => 'grid_nof_columns',
                'type'        => ELAY_Input_Filter::SELECTION,
	            'style'       => 'small',
                'options'     => array( 'one-col' => '1', 'two-col' => '2', 'three-col' => '3', 'four-col' => '4' ),
                'default'     => 'three-col'
            ),
            'grid_category_icon_location' => array(
                'label'       => __( 'Icons Location/Turn Off', 'echo-elegant-layouts' ),
                'name'        => 'grid_category_icon_location',
                'type'        => ELAY_Input_Filter::SELECTION,
	            'style'       => 'small',
                'options'     => array(
                    'no_icons' => __( 'No Icons', 'echo-elegant-layouts' ),
                    'top' => __( 'Top', 'echo-elegant-layouts' ),
	                'left'   => is_rtl() ? _x( 'Right', 'echo-knowledge-base' ) : _x( 'Left', 'echo-knowledge-base' ),
	                'right'  => is_rtl() ? _x( 'Left', 'echo-knowledge-base' ) : _x( 'Right', 'echo-knowledge-base' ),
                    'bottom' => __( 'Bottom', 'echo-elegant-layouts' ),
                ),
                'default'     => 'top'
            ),
            'grid_category_icon_thickness' => array(
                'label'       => __( 'Category Icon Thickness', 'echo-elegant-layouts' ),
                'name'        => 'grid_category_icon_thickness',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'normal'    => __( 'Normal',   'echo-elegant-layouts' ),
                    'bold'    => __( 'Bold',   'echo-elegant-layouts' ),
                ),
                'default'     => 'normal'
            ),
            'grid_section_icon_size' => array(
                'label'       => __( 'Icon Size ( px )', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_icon_size',
                'max'         => '300',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '50'
            ),
            'grid_section_article_count' => array(
                'label'       => __( 'Show Articles Count', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_article_count',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'on'
            ),


            /***  Search Box ***/

	        // TODO REMOVE all 'grid_search' below
            'grid_search_layout' => array(
                'label'       => __( 'Layout', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_layout',
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
            'grid_search_input_border_width' => array(
                'label'       => __( 'Border (px)', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_input_border_width',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '1'
            ),
            'grid_search_box_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '50'
            ),
            'grid_search_box_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '50'
            ),
            'grid_search_box_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_search_box_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_search_box_input_width' => array(
                'label'       => __( 'Width (%)', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_input_width',
                'max'         => '100',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '50'
            ),

            /***   Section    ***/

            'grid_section_head_alignment' => array(
                'label'       => __( 'Header Text Alignment', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_alignment',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
	                'left'   => is_rtl() ? _x( 'Right', 'echo-knowledge-base' ) : _x( 'Left', 'echo-knowledge-base' ),
                    'center' => __( 'Centered', 'echo-elegant-layouts' ),
	                'right'  => is_rtl() ? _x( 'Left', 'echo-knowledge-base' ) : _x( 'Right', 'echo-knowledge-base' ),
                ),
                'default'     => 'center'
            ),
            'grid_section_head_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_head_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_head_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_head_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),

            'grid_section_body_alignment' => array(
                'label'       => __( 'Body Text Alignment', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_alignment',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
	                'left'   => is_rtl() ? _x( 'Right', 'echo-knowledge-base' ) : _x( 'Left', 'echo-knowledge-base' ),
	                'center' => __( 'Centered', 'echo-elegant-layouts' ),
	                'right'  => is_rtl() ? _x( 'Left', 'echo-knowledge-base' ) : _x( 'Right', 'echo-knowledge-base' ),
                ),
                'default'     => 'center'
            ),
            'grid_section_cat_name_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_cat_name_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_cat_name_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_cat_name_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_cat_name_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_cat_name_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_cat_name_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_cat_name_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_desc_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_desc_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_desc_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_desc_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_desc_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_desc_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_desc_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_desc_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_desc_text_on' => array(
                'label'       => __( 'Category Description', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_desc_text_on',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'off'
            ),


            'grid_section_border_radius' => array(  // TODO REMOVE
                'label'       => __( 'Border Radius', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_border_radius',
                'max'         => '30',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '4'
            ),
            'grid_section_border_width' => array(   // TODO REMOVE
                'label'       => __( 'Border Width', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_border_width',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '1'
            ),
            'grid_section_box_shadow' => array(
                'label'       => __( 'Category Box Shadow', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_box_shadow',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'no_shadow' => __( 'No Shadow', 'echo-elegant-layouts' ),
                    'section_light_shadow' => __( 'Light Shadow', 'echo-elegant-layouts' ),
                    'section_medium_shadow' => __( 'Medium Shadow', 'echo-elegant-layouts' ),
                    'section_bottom_shadow' => __( 'Bottom Shadow', 'echo-elegant-layouts' )
                ),
                'default'     => 'no_shadow'
            ),
            'grid_section_box_hover' => array(
                'label'       => __( 'Hover Effect', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_box_hover',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'no_effect' => __( 'No Effect', 'echo-elegant-layouts'),
                    'hover-1' => __( 'Hover 1: Gives Opacity 70% ', 'echo-elegant-layouts' ),
                    'hover-2' => __( 'Hover 2: Gives Opacity 80% ', 'echo-elegant-layouts' ),
                    'hover-3' => __( 'Hover 3: Gives Opacity 90% ', 'echo-elegant-layouts' ),
                    'hover-4' => __( 'Hover 4: Gives Lightest Grey Background ', 'echo-elegant-layouts' ),
                    'hover-5' => __( 'Hover 5: Gives Lighter Grey Background ', 'echo-elegant-layouts' ),
                ),
                'default'     => 'no_effect'
            ),
            'grid_section_divider' => array(
                'label'       => __( 'Divider', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_divider',
                'type'        => ELAY_Input_Filter::CHECKBOX,
                'default'     => 'on'
            ),
            'grid_section_divider_thickness' => array(
                'label'       => __( 'Divider Thickness (px)', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_divider_thickness',
                'max'         => '10',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '1'
            ),
            'grid_section_box_height_mode' => array(
                'label'       => __( 'Height Mode', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_box_height_mode',
                'type'        => ELAY_Input_Filter::SELECTION,
                'options'     => array(
                    'section_no_height' => __( 'Variable', 'echo-elegant-layouts' ),
                    'section_min_height' => __( 'Minimum', 'echo-elegant-layouts' ),
                    'section_fixed_height' => __( 'Maximum', 'echo-elegant-layouts' )  ),
                'default'     => 'section_no_height'
            ),
            'grid_section_body_height' => array(
                'label'       => __( 'Height', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_height',
                'max'         => '1000',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '350'
            ),
            'grid_section_body_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_body_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_body_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_body_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_article_list_spacing' => array(			// TODO REMOVE
                'label'       => __( 'Spacing ( px )', 'echo-elegant-layouts' ),
                'name'        => 'grid_article_list_spacing',
                'max'         => '50',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
	            'style'       => 'small',
                'default'     => '0'
            ),
            'grid_section_icon_padding_top' => array(
                'label'       => __( 'Top', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_icon_padding_top',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_icon_padding_bottom' => array(
                'label'       => __( 'Bottom', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_icon_padding_bottom',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '20'
            ),
            'grid_section_icon_padding_left' => array(
                'label'       => __( 'Left', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_icon_padding_left',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),
            'grid_section_icon_padding_right' => array(
                'label'       => __( 'Right', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_icon_padding_right',
                'max'         => '200',
                'min'         => '0',
                'type'        => ELAY_Input_Filter::NUMBER,
                'default'     => '0'
            ),

            /******************************************************************************
             *
             *  KB Main Colors - All Colors Settings
             *
             ******************************************************************************/

            /***  Colors -> General  ***/

            'grid_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),


            /***  Colors -> Search Box  ***/

	        // TODO REMOVE
            'grid_search_title_font_color' => array(
                'label'       => __( 'Title', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_title_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#ffffff'
            ),
            'grid_search_background_color' => array(
                'label'       => __( 'Search Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#827a74'
            ),
            'grid_search_text_input_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_text_input_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'grid_search_text_input_border_color' => array(
                'label'       => __( 'Border', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_text_input_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'grid_search_btn_background_color' => array(
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_btn_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#686868'
            ),
            'grid_search_btn_border_color' => array(
                'label'       => __( 'Border', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_btn_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#F1F1F1'
            ),


            /***  Colors -> Articles Listed in Category Box ***/

            'grid_section_head_font_color' => array(    // TODO REMOVE
                'label'       => __( 'Category Name', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#555555'
            ),
            'grid_section_head_background_color' => array(  // TODO REMOVE
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'grid_section_head_description_font_color' => array(    // TODO REMOVE
                'label'       => __( 'Category Description Color', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_description_font_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#B3B3B3'
            ),
            'grid_section_body_background_color' => array(  // TODO REMOVE
                'label'       => __( 'Background', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_background_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#FFFFFF'
            ),
            'grid_section_border_color' => array(   // TODO REMOVE
                'label'       => __( 'Border Color', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_border_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#E1E0E0'
            ),
            'grid_section_divider_color' => array(  // TODO REMOVE
                'label'       => __( 'Divider Color', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_divider_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#E1E0E0'
            ),
            'grid_section_head_icon_color' => array(    // TODO REMOVE
                'label'       => __( 'Icon Color', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_head_icon_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#f7941d'
            ),
            'grid_section_body_text_color' => array(    // TODO REMOVE
                'label'       => __( 'Article Title', 'echo-elegant-layouts' ),
                'name'        => 'grid_section_body_text_color',
                'max'         => '7',
                'min'         => '7',
                'type'        => ELAY_Input_Filter::COLOR_HEX,
                'default'     => '#666666'
            ),

            /******************************************************************************
             *
             *  Front-End Text
             *
             ******************************************************************************/

            /***   Search  ***/

	        // TODO REMOVE all below
            'grid_search_title' => array(
                'label'       => __( 'Search Title', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_title',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'How Can We Help?', 'echo-elegant-layouts' )
            ),
            'grid_search_box_hint' => array(
                'label'       => __( 'Search Hint', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_box_hint',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Search the documentation...', 'echo-elegant-layouts' )
            ),
            'grid_search_results_msg' => array(
                'label'       => __( 'Search Results Message', 'echo-elegant-layouts' ),
                'name'        => 'grid_search_results_msg',
                'max'         => '100',
                'mandatory' => false,
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Search Results for', 'echo-elegant-layouts' )
            ),
            'grid_no_results_found' => array(
                'label'       => __( 'No Matches Found Text', 'echo-elegant-layouts' ),
                'name'        => 'grid_no_results_found',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'No matches found', 'echo-elegant-layouts' )
            ),
            'grid_min_search_word_size_msg' => array(
                'label'       => __( 'Minimum Search Word Size Message', 'echo-elegant-layouts' ),
                'name'        => 'grid_min_search_word_size_msg',
                'max'         => '150',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'Enter a word with at least one character.', 'echo-elegant-layouts' )
            ),


            /***   Categories and Articles ***/

			'grid_category_empty_msg' => array(   // TODO REMOVE
				'label'       => __( 'Empty Category Notice', 'echo-elegant-layouts' ),
				'name'        => 'grid_category_empty_msg',
				'max'         => '150',
				'mandatory' => false,
				'type'        => ELAY_Input_Filter::TEXT,
				'default'     => __( 'Articles coming soon', 'echo-elegant-layouts' )
			),
            'grid_category_link_text' => array(
                'label'       => __( 'Additional Text', 'echo-elegant-layouts' ),
                'name'        => 'grid_category_link_text',
                'max'         => '200',
                'min'         => '0',
	            'mandatory' => false,
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( '', 'echo-elegant-layouts' )
            ),
            'grid_article_count_text' => array(
                'label'       => __( 'Articles Count Text', 'echo-elegant-layouts' ),
                'name'        => 'grid_article_count_text',
                'max'         => '200',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'article', 'echo-elegant-layouts' )
            ),
            'grid_article_count_plural_text' => array(
                'label'       => __( 'Articles Count Plural Text', 'echo-elegant-layouts' ),
                'name'        => 'grid_article_count_plural_text',
                'max'         => '200',
                'min'         => '1',
                'type'        => ELAY_Input_Filter::TEXT,
                'default'     => __( 'articles', 'echo-elegant-layouts' )
            )

        );

        return $config_specification;
    }

    public static function get_specs_defaults( $config_specs ) {
        $default_configuration = array();
        foreach( $config_specs as $key => $spec ) {
            $default = isset($spec['default']) ? $spec['default'] : '';
            $default_configuration += array( $key => $default );
        }
        return $default_configuration;
    }
}
