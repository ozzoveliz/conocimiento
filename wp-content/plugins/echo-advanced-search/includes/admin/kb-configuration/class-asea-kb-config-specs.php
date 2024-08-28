<?php

/**
 * Lists all KB configuration settings and adds filter to get configuration from add-ons.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ASEA_KB_Config_Specs {

	private static $cached_specs = array();

	/**
	 * Defines how KB configuration fields will be displayed, initialized and validated/sanitized
	 *
	 * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => false )
	 *
	 * @return array with KB config specification
	 */
	public static function get_fields_specification() {

		// retrieve settings if already cached
		if ( ! empty(self::$cached_specs) && is_array(self::$cached_specs) ) {
			return self::$cached_specs;
		}

		//Search Box
		$default_color = array (
				'advanced_search_title_font_color'                              =>  '#FFFFFF',
				'advanced_search_link_font_color'                               =>  '#eeee22',
				'advanced_search_background_color'                              =>  '#f7941d',
				'advanced_search_text_input_background_color'                   =>  '#FFFFFF',
				'advanced_search_text_input_border_color'                       =>  '#CCCCCC',
				'advanced_search_btn_background_color'                          =>  '#40474f',
				'advanced_search_btn_border_color'                              =>  '#F1F1F1',
				'advanced_search_background_gradient_from_color'                =>  '#00c1b6',
				'advanced_search_background_gradient_to_color'                  =>  '#136eb5',
				'advanced_search_title_font_shadow_color'                       =>  '#010000',
				'advanced_search_description_below_title_font_shadow_color'     =>  '#010000',
				'advanced_search_filter_box_font_color'                         =>  '#000000',
				'advanced_search_filter_box_background_color'                   =>  '#ffffff',
				'advanced_search_search_result_category_color'                  =>  '#000000'
		);

		$default_style = array(

				//Container Settings
				'advanced_search_box_padding_top'                       =>  50,
				'advanced_search_box_padding_bottom'                    =>  50,
				'advanced_search_box_padding_left'                      =>  0,
				'advanced_search_box_padding_right'                     =>  0,
				'advanced_search_box_margin_top'                        =>  0,
				'advanced_search_box_margin_bottom'                     =>  40,
				'advanced_search_box_font_width'                        =>  80,

				//Title Settings
				'advanced_search_title_padding_bottom'                  =>  30,
				'advanced_search_title_text_shadow_x_offset'            =>  2,
				'advanced_search_title_text_shadow_y_offset'            =>  2,
				'advanced_search_title_text_shadow_blur'                =>  0,
				'advanced_search_title_text_shadow_toggle'              =>  'off',
				'advanced_search_title_tag'                             =>	'div',

				//Description 1 Settings
				'advanced_search_description_below_title_padding_top'   =>  20,
				'advanced_search_description_below_title_padding_bottom' =>  20,
				'advanced_search_description_below_title_text_shadow_x_offset'  =>  2,
				'advanced_search_description_below_title_text_shadow_y_offset'  =>  2,
				'advanced_search_description_below_title_text_shadow_blur'      =>  0,
				'advanced_search_description_below_title_text_shadow_toggle'    =>  'off',

				//Input Box Settings and Results Settings
				'advanced_search_input_border_width'                    =>  1,
				'advanced_search_box_input_width'                       =>  40,
				'advanced_search_input_box_radius'                      =>  0,
				'advanced_search_input_box_shadow_rgba'                 =>  '',
				'advanced_search_input_box_shadow_x_offset'             =>  0,
				'advanced_search_input_box_shadow_y_offset'             =>  0,
				'advanced_search_input_box_shadow_blur'                 =>  0,
				'advanced_search_input_box_shadow_spread'               =>  5,
				'advanced_search_input_box_padding_top'                 =>  10,
				'advanced_search_input_box_padding_bottom'              =>  10,
			    'advanced_search_input_box_padding_left'                =>  43,
			    'advanced_search_input_box_padding_right'               =>  43,
				'advanced_search_box_results_style'                     =>  'off',
				'advanced_search_input_box_search_icon_placement'       =>  'none',
				'advanced_search_input_box_loading_icon_placement'      =>  'right',
			    'advanced_search_filter_toggle'                         =>  'off',
				'advanced_search_filter_dropdown_width'                 =>  '260',
				'advanced_search_filter_category_level'					=>	'top',

				//Description 2 Settings
				'advanced_search_description_below_input_padding_top'           =>  20,
				'advanced_search_description_below_input_padding_bottom'        =>  20,

				//Search Results Settings
				'advanced_search_search_results_article_font_size'      =>  16,

				//Background Image
				'advanced_search_background_image_position_x'           =>  'left',
				'advanced_search_background_image_position_y'           =>  'top',

				//Background Pattern Image
				'advanced_search_background_pattern_image_position_x'   =>  'left',
				'advanced_search_background_pattern_image_position_y'   =>  'top',

				//Background Gradient
				'advanced_search_background_gradient_degree'            =>  '45',
				'advanced_search_background_gradient_opacity'           =>  1,
				'advanced_search_background_gradient_toggle'            =>  'off',
		);
		
		$default_style_rtl = array(
			'advanced_search_title_text_shadow_x_offset'                    =>  -2,
			'advanced_search_description_below_title_text_shadow_x_offset'  =>  -2,
			'advanced_search_input_box_loading_icon_placement'              =>  'left',
		);
		
		if ( is_rtl() ) {
			$default_style = array_merge( $default_style, $default_style_rtl );
		}

		// get all configuration
		$global_search_specification = self::get_global_search_specification();
		$main_page_config_specification = self::get_main_page_fields_specification( $default_color, $default_style );
		$article_page_config_specification = self::get_article_page_fields_specification( $default_color, $default_style );

		self::$cached_specs = array_merge( $main_page_config_specification, $article_page_config_specification, $global_search_specification );

		return self::$cached_specs;
	}

	private static function get_global_search_specification() {
		$config_specification = array(
			'asea_debug' => array(
				'label'       => __( 'Enable Search Debug', 'echo-knowledge-base' ),
				'name'        => 'asea_debug',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			'search_query_param' => array(
				'label'       => __( 'KB Search Query Parameter', 'echo-knowledge-base' ),
				'name'        => 'search_query_param',
				'max'         => '30',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => 'kbsearch'
			),
		);
		return $config_specification;
	}

	private static function get_main_page_fields_specification( $default_color, $default_style ) {
		$config_specification = array(

			'advanced_search_mp_title_font_color'                           => array(
				'label'       => __( 'Title and Description Color', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_title_font_color']
			),
			'advanced_search_mp_title_font_shadow_color'                    => array(
				'label'       => __( 'Title Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_font_shadow_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_title_font_shadow_color']
			),
			'advanced_search_mp_title_tag'                                  => array(
				'label'       => __( 'Title Tag', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_tag',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'div' => __( 'div' ),
					'h1' => __( 'h1' ),
					'h2' => __( 'h2' ),
					'h3' => __( 'h3' ),
					'h4' => __( 'h4' ),
					'h5' => __( 'h5' ),
					'h6' => __( 'h6' ),
					'span' => __( 'span' ),
					'p' => __( 'p' ),
				),
				'default'     => $default_style['advanced_search_title_tag']
			),
			'advanced_search_mp_filter_category_level'                      => array(
				'label'       => __( 'Category Level', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_category_level',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'top' => __( 'Top Level' ),
					'sub' => __( 'Top + Sub Level' ),
				),
				'default'     => $default_style['advanced_search_filter_category_level']
			),
			'advanced_search_mp_description_below_title_font_shadow_color'  => array(
				'label'       => __( 'Description Below Title Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_font_shadow_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_description_below_title_font_shadow_color']
			),
			'advanced_search_mp_link_font_color'                            => array(
				'label'       => __( 'Links', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_link_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_link_font_color']
			),
			'advanced_search_mp_background_color'                           => array(
				'label'       => __( 'Search Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_color']
			),
			'advanced_search_mp_text_input_background_color'                => array(
				'label'       => __( 'Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_text_input_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_text_input_background_color']
			),
			'advanced_search_mp_text_input_border_color'                    => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_text_input_border_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_text_input_border_color']
			),
			'advanced_search_mp_btn_background_color'                       => array(
				'label'       => __( 'Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_btn_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_btn_background_color']
			),
			'advanced_search_mp_btn_border_color'                           => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_btn_border_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_btn_border_color']
			),
			'advanced_search_mp_background_gradient_from_color'             => array(
				'label'       => __( 'FROM:', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_gradient_from_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_gradient_from_color']
			),
			'advanced_search_mp_background_gradient_to_color'               => array(
				'label'       => __( 'TO:', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_gradient_to_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_gradient_to_color']
			),
			'advanced_search_mp_filter_box_font_color'                      => array(
				'label'       => __( 'Filter Box Text', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_box_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_filter_box_font_color']
			),
			'advanced_search_mp_filter_box_background_color'                => array(
				'label'       => __( 'Filter Box Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_box_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_filter_box_background_color']
			),
			'advanced_search_mp_search_result_category_color'               => array(
				'label'       => __( 'Category text Color in search result', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_search_result_category_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_search_result_category_color']
			),
			'advanced_search_mp_box_visibility' => array(       // TODO REMOVE
				'label'       => __( 'Visibility', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_visibility',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'asea-visibility-search-form-1' => __( 'Visible' ),
					'asea-visibility-search-form-2' => __( 'Hidden' ),
					'asea-visibility-search-form-3' => __( 'Hidden With Toggle to Show It' )
				),
				'default'     => 'asea-visibility-search-form-1'
			),
			'advanced_search_mp_auto_complete_wait' => array(
				'label'       => __( 'Auto-complete Waiting Time [ms]', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_auto_complete_wait',
				'max'         => '5000',
				'min'         => '500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '1000'
			),
			'advanced_search_mp_results_list_size' => array(
				'label'       => __( 'Size of Search Results List', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_results_list_size',
				'max'         => '100',
				'min'         => '5',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_mp_results_page_size'                          => array(
				'label'       => __( 'Size of Search Results Page', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_results_page_size',
				'max'         => '100',
				'min'         => '5',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_mp_show_top_category'                          => array(
				'label'       => __( 'Show Category for Each Result', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_show_top_category',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			'advanced_search_mp_title_toggle'                               => array(
				'label'       => __( 'Search Title Toggle', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_mp_title_typography' => array(
				'label'       => __( 'Search Title Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_mp_title_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '36',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_mp_title'                                      => array(
				'label'       => __( 'Search Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'How Can We Help?', 'echo-advanced-search' ),
				'allowed_tags' => array( 
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_mp_title_by_filter'                            => array(
				'label'       => __( 'Filter by Categories Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_by_filter',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Filter by categories', 'echo-advanced-search' )
			),
			'advanced_search_mp_title_clear_results'                        => array(
				'label'       => __( 'Clear Results Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_clear_results',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Clear Results', 'echo-advanced-search' )
			),
			'advanced_search_mp_description_below_title_toggle'             => array(
				'label'       => __( 'Show Description Below Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_mp_description_below_title'                    => array(
				'label'       => __( 'Description Under Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => 'Search for answers or browse our knowledge base.',
				'allowed_tags' => array( 
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_mp_description_below_input_toggle'             => array(
				'label'       => __( 'Show Description Below Input', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_input_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_mp_description_below_input'                    => array(
				'label'       => __( 'Description Under Search Input', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_input',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => '<a href="https://www.echoknowledgebase.com/documentation/" target="_blank">Documentation</a> | '.
				                 '<a href="https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/" target="_blank">Demos</a> | ' .
				                 '<a href="https://www.echoknowledgebase.com/contact-us/" target="_blank">Support</a>',
				'allowed_tags' => array( 
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_mp_box_hint'                                   => array(
				'label'       => __( 'Search Hint', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_hint',
				'max'         => '60',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search the documentation...', 'echo-advanced-search' )
			),
			'advanced_search_mp_button_name'                                => array(
				'label'       => __( 'Search Button Name', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_button_name',
				'max'         => '25',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search', 'echo-advanced-search' )
			),
			'advanced_search_mp_results_msg'                                => array(
				'label'       => __( 'Search Results Message', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_results_msg',
				'max'         => '60',
				'mandatory' => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search Results for', 'echo-advanced-search' )
			),
			'advanced_search_mp_no_results_found'                           => array(
				'label'       => __( 'No Matches Found Text', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_no_results_found',
				'max'         => '200',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'No matches found', 'echo-advanced-search' ),
				'allowed_tags' => array(
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_mp_more_results_found'                         => array(
				'label'       => __( 'Found Additional Results', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_more_results_found',
				'max'         => '80',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'All Search Results', 'echo-advanced-search' )
			),
			'advanced_search_mp_filter_indicator_text'                      => array(
				'label'       => __( 'Text for Category Filter indicator', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_indicator_text',
				'max'         => '50',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( '', 'echo-advanced-search' )
			),
			'advanced_search_mp_search_result_category_label'               => array(
				'label'       => __( 'Label for category in search results', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_search_result_category_label',
				'max'         => '40',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Top Category:', 'echo-advanced-search' )
			),
			'advanced_search_mp_box_padding_top'                            => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_top']
			),
			'advanced_search_mp_box_padding_bottom'             => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_bottom']
			),
			'advanced_search_mp_box_padding_left'               => array(
				'label'       => __( 'Left', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_padding_left',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_left']
			),
			'advanced_search_mp_box_padding_right'              => array(
				'label'       => __( 'Right', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_padding_right',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_right']
			),
			'advanced_search_mp_box_margin_top'                 => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_margin_top',
				'max'         => '200',
				'min'         => '-200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_margin_top']
			),
			'advanced_search_mp_box_margin_bottom'              => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_margin_bottom',
				'max'         => '200',
				'min'         => '-200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_margin_bottom']
			),
			'advanced_search_mp_box_font_width'                 => array(
				'label'       => __( 'Width of Text Above/Below Input ( % )', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_font_width',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_font_width']
			),
			'advanced_search_mp_title_padding_bottom'           => array(
				'label'       => __( 'Bottom Space', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_padding_bottom',
				'max'         => '300',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '14'
			),
			'advanced_search_mp_title_text_shadow_x_offset'     => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_text_shadow_x_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_x_offset']
			),
			'advanced_search_mp_title_text_shadow_y_offset'     => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_text_shadow_y_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_y_offset']
			),
			'advanced_search_mp_title_text_shadow_blur'         => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_text_shadow_blur',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_blur']
			),
			'advanced_search_mp_title_text_shadow_toggle'       => array(
				'label'       => __( 'Turn on Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_title_text_shadow_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_title_text_shadow_toggle']
			),
			'advanced_search_mp_description_below_title_typography' => array(
				'label'       => __( 'Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_mp_description_below_title_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '16',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_mp_description_below_title_padding_top'      => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '2'
			),
			'advanced_search_mp_description_below_title_padding_bottom'   => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '12'
			),
			'advanced_search_mp_description_below_title_text_shadow_x_offset'     => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_text_shadow_x_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_x_offset']
			),
			'advanced_search_mp_description_below_title_text_shadow_y_offset'     => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_text_shadow_y_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_y_offset']
			),
			'advanced_search_mp_description_below_title_text_shadow_blur'         => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_text_shadow_blur',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_blur']
			),
			'advanced_search_mp_description_below_title_text_shadow_toggle'       => array(
				'label'       => __( 'Turn on Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_title_text_shadow_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_toggle']
			),
			'advanced_search_mp_input_border_width'                         => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_border_width',
				'max'         => '10',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_border_width']
			),
			'advanced_search_mp_box_input_width'                            => array(
				'label'       => __( 'Width (%)', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_input_width',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_input_width']
			),
			'advanced_search_mp_input_box_radius'                           => array(
				'label'       => __( 'Radius', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_radius',
				'max'         => '400',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_radius']
			),
			'advanced_search_mp_input_box_typography' => array(
				'label'       => __( 'Input Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_mp_input_box_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '18',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_mp_input_box_shadow_rgba'                      => array(
				'label'       => __( 'Box Shadow Color : Example: 21, 21, 21, 0.15', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_shadow_rgba',
				'max'         => '100',
				'min'         => '0',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => $default_style['advanced_search_input_box_shadow_rgba']
			),
			'advanced_search_mp_input_box_shadow_x_offset'                  => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_shadow_x_offset',
				'max'         => '500',
				'min'         => '-500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_x_offset']
			),
			'advanced_search_mp_input_box_shadow_y_offset'                  => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_shadow_y_offset',
				'max'         => '500',
				'min'         => '-500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_y_offset']
			),
			'advanced_search_mp_input_box_shadow_blur'                      => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_shadow_blur',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_blur']
			),
			'advanced_search_mp_input_box_shadow_spread'                    => array(
				'label'       => __( 'Spread', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_shadow_spread',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_spread']
			),
			'advanced_search_mp_input_box_padding_top'                      => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_top']
			),
			'advanced_search_mp_input_box_padding_bottom'                   => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_bottom']
			),
			'advanced_search_mp_input_box_padding_left'                     => array(
				'label'       => __( 'Left', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_padding_left',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_left']
			),
			'advanced_search_mp_input_box_padding_right'                    => array(
				'label'       => __( 'Right', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_padding_right',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_right']
			),
			'advanced_search_mp_box_results_style'                          => array(
				'label'       => __( 'Search Results: Match Article Colors', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_box_results_style',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_box_results_style']
			),
			'advanced_search_mp_input_box_search_icon_placement'            => array(
				'label'       => __( 'Search Icon Placement', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_search_icon_placement',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'left'      => __( 'Left' ),
					'right'     => __( 'Right' ),
					'none'      => __( 'None' )
				),
				'default'     => $default_style['advanced_search_input_box_search_icon_placement']
			),
			'advanced_search_mp_input_box_loading_icon_placement'           => array(
				'label'       => __( 'Loading Icon Placement', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_input_box_loading_icon_placement',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'left'      => __( 'Left' ),
					'right'     => __( 'Right' )
				),
				'default'     => $default_style['advanced_search_input_box_loading_icon_placement']
			),
			'advanced_search_mp_filter_toggle'                              => array(
				'label'       => __( 'Category Filter', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_filter_toggle']
			),
			'advanced_search_mp_filter_dropdown_width'                      => array(
				'label'       => __( 'Dropdown Max Width (px)', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_filter_dropdown_width',
				'max'         => '1200',
				'min'         => '200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_filter_dropdown_width']
			),
			'advanced_search_mp_description_below_input_typography' => array(
				'label'       => __( 'Below Input Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_mp_description_below_input_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '16',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_mp_description_below_input_padding_top'        => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_input_padding_top',
				'max'         => 500,
				'min'         => 0,
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_mp_description_below_input_padding_bottom'     => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_description_below_input_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_mp_search_results_article_font_size'           => array(
				'label'       => __( 'Article Font Size ( px )', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_search_results_article_font_size',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_search_results_article_font_size']
			),
			'advanced_search_mp_background_image_url'                       => array(
				'label'       => __( 'Background Image URL', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_image_url',
				'max'         => '300',
				'min'         => '0',
				'mandatory'    => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => ''
			),
			'advanced_search_mp_background_image_position_x'                => array(
				'label'       => __( 'Background Image Position X', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_image_position_x',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center' => __( 'Center', 'echo-advanced-search' ),
					'left'   => __( 'Left', 'echo-advanced-search' ),
					'right'  => __( 'Right', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_image_position_x']
			),
			'advanced_search_mp_background_image_position_y'                => array(
				'label'       => __( 'Background Image Position Y', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_image_position_y',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center'    => __( 'Center', 'echo-advanced-search' ),
					'top'       => __( 'Top', 'echo-advanced-search' ),
					'bottom'    => __( 'Bottom', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_image_position_y']
			),
			'advanced_search_mp_background_pattern_image_url'               => array(
				'label'       => __( 'Background Pattern Image URL', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_pattern_image_url',
				'max'         => '300',
				'min'         => '0',
				'mandatory'    => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => ''
			),
			'advanced_search_mp_background_pattern_image_position_x'        => array(
				'label'       => __( 'Image Position X', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_pattern_image_position_x',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center' => __( 'Center', 'echo-advanced-search' ),
					'left'   => __( 'Left', 'echo-advanced-search' ),
					'right'  => __( 'Right', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_pattern_image_position_x']
			),
			'advanced_search_mp_background_pattern_image_position_y'        => array(
				'label'       => __( 'Image Position Y', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_pattern_image_position_y',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center'    => __( 'Center', 'echo-advanced-search' ),
					'top'       => __( 'Top', 'echo-advanced-search' ),
					'bottom'    => __( 'Bottom', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_pattern_image_position_y']
			),
			'advanced_search_mp_background_pattern_image_opacity'           => array(
				'label'       => __( 'Opacity of the Image', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_pattern_image_opacity',
				'max'         => '5',
				'min'         => '0.1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => '0.5'
			),
			'advanced_search_mp_background_gradient_degree'                 => array(
				'label'       => __( 'Degree of the Gradient', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_gradient_degree',
				'max'         => '1000',
				'min'         => '-1000',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_background_gradient_degree']
			),
			'advanced_search_mp_background_gradient_opacity'                => array(
				'label'       => __( 'Opacity of the Gradient', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_gradient_opacity',
				'max'         => '5',
				'min'         => '0.1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => $default_style['advanced_search_background_gradient_opacity']
			),
			'advanced_search_mp_background_gradient_toggle'                 => array(
				'label'       => __( 'Turn on Gradient Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_mp_background_gradient_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_background_gradient_toggle']
			),
			'advanced_search_results_meta_created_on_toggle'                 => array(
				'label'       => __( 'Created On', 'echo-advanced-search' ),
				'name'        => 'advanced_search_results_meta_created_on_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			'advanced_search_results_meta_author_toggle'                 => array(
				'label'       => __( 'Author', 'echo-advanced-search' ),
				'name'        => 'advanced_search_results_meta_author_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			'advanced_search_results_meta_categories_toggle'                 => array(
				'label'       => __( 'Categories', 'echo-advanced-search' ),
				'name'        => 'advanced_search_results_meta_categories_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			
			
		);
		return $config_specification;
	}

	private static function get_article_page_fields_specification( $default_color, $default_style ) {
		$config_specification = array(

			'advanced_search_ap_title_font_color'               => array(
				'label'       => __( 'Title and Description Color', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_title_font_color']
			),
			'advanced_search_ap_title_font_shadow_color'        => array(
				'label'       => __( 'Title Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_font_shadow_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_title_font_shadow_color']
			),
			'advanced_search_ap_title_tag'              => array(
				'label'       => __( 'Title Tag', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_tag',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'div' => __( 'div' ),
					'h1' => __( 'h1' ),
					'h2' => __( 'h2' ),
					'h3' => __( 'h3' ),
					'h4' => __( 'h4' ),
					'h5' => __( 'h5' ),
					'h6' => __( 'h6' ),
					'span' => __( 'span' ),
					'p' => __( 'p' ),
				),
				'default'     => $default_style['advanced_search_title_tag']
			),
			'advanced_search_ap_filter_category_level'              => array(
				'label'       => __( 'Category Level', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_category_level',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'top' => __( 'Top Level' ),
					'sub' => __( 'Top + Sub Level' ),
				),
				'default'     => $default_style['advanced_search_filter_category_level']
			),
			'advanced_search_ap_description_below_title_font_shadow_color'        => array(
				'label'       => __( 'Description Below Title Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_font_shadow_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_description_below_title_font_shadow_color']
			),
			'advanced_search_ap_link_font_color'               => array(
				'label'       => __( 'Links', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_link_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_link_font_color']
			),
			'advanced_search_ap_background_color'               => array(
				'label'       => __( 'Search Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_color']
			),
			'advanced_search_ap_text_input_background_color'    => array(
				'label'       => __( 'Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_text_input_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_text_input_background_color']
			),
			'advanced_search_ap_text_input_border_color'        => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_text_input_border_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_text_input_border_color']
			),
			'advanced_search_ap_btn_background_color'           => array(
				'label'       => __( 'Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_btn_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_btn_background_color']
			),
			'advanced_search_ap_btn_border_color'               => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_btn_border_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_btn_border_color']
			),
			'advanced_search_ap_background_gradient_from_color' => array(
				'label'       => __( 'FROM:', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_gradient_from_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_gradient_from_color']
			),
			'advanced_search_ap_background_gradient_to_color'   => array(
				'label'       => __( 'TO:', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_gradient_to_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_background_gradient_to_color']
			),
			'advanced_search_ap_filter_box_font_color'          => array(
				'label'       => __( 'Filter Box Text', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_box_font_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_filter_box_font_color']
			),
			'advanced_search_ap_filter_box_background_color'    => array(
				'label'       => __( 'Filter Box Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_box_background_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_filter_box_background_color']
			),
			'advanced_search_ap_search_result_category_color'    => array(
				'label'       => __( 'Category text Color in search result', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_search_result_category_color',
				'max'         => '7',
				'min'         => '7',
				'type'        => ASEA_Input_Filter::COLOR_HEX,
				'default'     => $default_color['advanced_search_search_result_category_color']
			),
			'advanced_search_ap_box_visibility' => array(       // TODO REMOVE
				'label'       => __( 'Visibility', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_visibility',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'asea-visibility-search-form-1' => __( 'Visible' ),
					'asea-visibility-search-form-2' => __( 'Hidden' ),
					'asea-visibility-search-form-3' => __( 'Hidden With Toggle to Show It' )
				),
				'default'     => 'asea-visibility-search-form-1'
			),
			'advanced_search_ap_auto_complete_wait' => array(
				'label'       => __( 'Auto-complete Waiting Time [ms]', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_auto_complete_wait',
				'max'         => '5000',
				'min'         => '500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '1000'
			),
			'advanced_search_ap_results_list_size' => array(
				'label'       => __( 'Size of Search Results List', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_results_list_size',
				'max'         => '100',
				'min'         => '5',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_ap_results_page_size' => array(
				'label'       => __( 'Size of Search Results Page', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_results_page_size',
				'max'         => '100',
				'min'         => '5',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_ap_show_top_category'              => array(
				'label'       => __( 'Show Category for Each Result', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_show_top_category',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'off'
			),
			'advanced_search_ap_title_toggle'              => array(
				'label'       => __( 'Search Title Toggle', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_ap_title_typography' => array(
				'label'       => __( 'Search Title Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_ap_title_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '36',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_ap_title'                          => array(
				'label'       => __( 'Search Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'How Can We Help?', 'echo-advanced-search' ),
				'allowed_tags' => array(
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_ap_title_by_filter'                          => array(
				'label'       => __( 'Filter by categories Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_by_filter',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Filter by categories', 'echo-advanced-search' )
			),
			'advanced_search_ap_title_clear_results'                          => array(
				'label'       => __( 'Clear Results Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_clear_results',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Clear Results', 'echo-advanced-search' )
			),
			'advanced_search_ap_description_below_title_toggle'              => array(
				'label'       => __( 'Show Description Below Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_ap_description_below_title'        => array(
				'label'       => __( 'Description Under Title', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => 'Search for answers or browse our knowledge base.',
				'allowed_tags' => array(
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_ap_description_below_input_toggle'              => array(
				'label'       => __( 'Show Description Below Input', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_input_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => 'on'
			),
			'advanced_search_ap_description_below_input'        => array(
				'label'       => __( 'Description Under Search Input', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_input',
				'max'         => '1000',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => '<a href="https://www.echoknowledgebase.com/documentation/" target="_blank">Documentation</a> | '.
				                 '<a href="https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/" target="_blank">Demos</a> | ' .
				                 '<a href="https://www.echoknowledgebase.com/contact-us/" target="_blank">Support</a>',
				'allowed_tags' => array(
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_ap_box_hint'                       => array(
				'label'       => __( 'Search Hint', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_hint',
				'max'         => '60',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search the documentation...', 'echo-advanced-search' )
			),
			'advanced_search_ap_button_name'                    => array(
				'label'       => __( 'Search Button Name', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_button_name',
				'max'         => '25',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search', 'echo-advanced-search' )
			),
			'advanced_search_ap_results_msg'                    => array(
				'label'       => __( 'Search Results Message', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_results_msg',
				'max'         => '60',
				'mandatory' => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Search Results for', 'echo-advanced-search' )
			),
			'advanced_search_ap_no_results_found'               => array(
				'label'       => __( 'No Matches Found Text', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_no_results_found',
				'max'         => '200',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'No matches found', 'echo-advanced-search' ),
				'allowed_tags' => array(
					'a' => array(
						'href'  => true,
						'title' => true,
						'target' => true,
					),
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'i' => array(),
					'span' => array(
						'class' => true
					),
				) // https://developer.wordpress.org/reference/functions/wp_kses/
			),
			'advanced_search_ap_more_results_found'             => array(
				'label'       => __( 'Found Additional Results', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_more_results_found',
				'max'         => '80',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'All Search Results', 'echo-advanced-search' )
			),
			'advanced_search_ap_filter_indicator_text'          => array(
				'label'       => __( 'Text for Category Filter indicator', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_indicator_text',
				'max'         => '50',
				'min'         => '1',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( '', 'echo-advanced-search' )
			),
			'advanced_search_ap_search_result_category_label'               => array(
				'label'       => __( 'Label for category in search results', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_search_result_category_label',
				'max'         => '40',
				'min'         => '1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => __( 'Top Category:', 'echo-advanced-search' )
			),
			'advanced_search_ap_box_padding_top'                => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_top']
			),
			'advanced_search_ap_box_padding_bottom'             => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_bottom']
			),
			'advanced_search_ap_box_padding_left'               => array(
				'label'       => __( 'Left', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_padding_left',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_left']
			),
			'advanced_search_ap_box_padding_right'              => array(
				'label'       => __( 'Right', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_padding_right',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_padding_right']
			),
			'advanced_search_ap_box_margin_top'                 => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_margin_top',
				'max'         => '200',
				'min'         => '-200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_margin_top']
			),
			'advanced_search_ap_box_margin_bottom'              => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_margin_bottom',
				'max'         => '200',
				'min'         => '-200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_margin_bottom']
			),
			'advanced_search_ap_box_font_width'                 => array(
				'label'       => __( 'Width of Text Above/Below Input ( % )', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_font_width',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_font_width']
			),
			'advanced_search_ap_title_padding_bottom'           => array(
				'label'       => __( 'Bottom Space', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_padding_bottom',
				'max'         => '300',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '14'
			),
			'advanced_search_ap_title_text_shadow_x_offset'     => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_text_shadow_x_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_x_offset']
			),
			'advanced_search_ap_title_text_shadow_y_offset'     => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_text_shadow_y_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_y_offset']
			),
			'advanced_search_ap_title_text_shadow_blur'         => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_text_shadow_blur',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_title_text_shadow_blur']
			),
			'advanced_search_ap_title_text_shadow_toggle'       => array(
				'label'       => __( 'Turn on Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_title_text_shadow_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_title_text_shadow_toggle']
			),
			'advanced_search_ap_description_below_title_typography' => array(
				'label'       => __( 'Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_ap_description_below_title_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '16',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_ap_description_below_title_padding_top'      => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '2'
			),
			'advanced_search_ap_description_below_title_padding_bottom'   => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '12'
			),
			'advanced_search_ap_description_below_title_text_shadow_x_offset'     => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_text_shadow_x_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_x_offset']
			),
			'advanced_search_ap_description_below_title_text_shadow_y_offset'     => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_text_shadow_y_offset',
				'max'         => '100',
				'min'         => '-100',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_y_offset']
			),
			'advanced_search_ap_description_below_title_text_shadow_blur'         => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_text_shadow_blur',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_blur']
			),
			'advanced_search_ap_description_below_title_text_shadow_toggle'       => array(
				'label'       => __( 'Turn on Text Shadow', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_title_text_shadow_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_description_below_title_text_shadow_toggle']
			),
			'advanced_search_ap_input_border_width'             => array(
				'label'       => __( 'Border', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_border_width',
				'max'         => '10',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_border_width']
			),
			'advanced_search_ap_box_input_width'                => array(
				'label'       => __( 'Width (%)', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_input_width',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_box_input_width']
			),
			'advanced_search_ap_input_box_radius'               => array(
				'label'       => __( 'Radius', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_radius',
				'max'         => '400',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_radius']
			),
			'advanced_search_ap_input_box_typography' => array(
				'label'       => __( 'Input Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_ap_input_box_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '18',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_ap_input_box_shadow_rgba'          => array(
				'label'       => __( 'Box Shadow Color : Example: 21, 21, 21, 0.15', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_shadow_rgba',
				'max'         => '100',
				'min'         => '0',
				'mandatory'   => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => $default_style['advanced_search_input_box_shadow_rgba']
			),
			'advanced_search_ap_input_box_shadow_x_offset'      => array(
				'label'       => __( 'X Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_shadow_x_offset',
				'max'         => '500',
				'min'         => '-500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_x_offset']
			),
			'advanced_search_ap_input_box_shadow_y_offset'      => array(
				'label'       => __( 'Y Offset', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_shadow_y_offset',
				'max'         => '500',
				'min'         => '-500',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_y_offset']
			),
			'advanced_search_ap_input_box_shadow_blur'          => array(
				'label'       => __( 'Blur', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_shadow_blur',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_blur']
			),
			'advanced_search_ap_input_box_shadow_spread'        => array(
				'label'       => __( 'Spread', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_shadow_spread',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_shadow_spread']
			),
			'advanced_search_ap_input_box_padding_top'          => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_padding_top',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_top']
			),
			'advanced_search_ap_input_box_padding_bottom'       => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_bottom']
			),
			'advanced_search_ap_input_box_padding_left'         => array(
				'label'       => __( 'Left', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_padding_left',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_left']
			),
			'advanced_search_ap_input_box_padding_right'        => array(
				'label'       => __( 'Right', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_padding_right',
				'max'         => '200',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_input_box_padding_right']
			),
			'advanced_search_ap_box_results_style'              => array(
				'label'       => __( 'Search Results: Match Article Colors', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_box_results_style',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_box_results_style']
			),
			'advanced_search_ap_input_box_search_icon_placement'    => array(
				'label'       => __( 'Search Icon Placement', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_search_icon_placement',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'left'      => __( 'Left' ),
					'right'     => __( 'Right' ),
					'none'      => __( 'None' )
				),
				'default'     => $default_style['advanced_search_input_box_search_icon_placement']
			),
			'advanced_search_ap_input_box_loading_icon_placement'   => array(
				'label'       => __( 'Loading Icon Placement', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_input_box_loading_icon_placement',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'left'      => __( 'Left' ),
					'right'     => __( 'Right' )
				),
				'default'     => $default_style['advanced_search_input_box_loading_icon_placement']
			),
			'advanced_search_ap_filter_toggle'                  => array(
				'label'       => __( 'Category Filter', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_filter_toggle']
			),
			'advanced_search_ap_filter_dropdown_width'        => array(
				'label'       => __( 'Dropdown Max Width (px)', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_filter_dropdown_width',
				'max'         => '1200',
				'min'         => '200',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_filter_dropdown_width']
			),
			'advanced_search_ap_description_below_input_typography' => array(
				'label'       => __( 'Below Input Typography', 'echo-knowledge-base' ),
				'name'        => 'advanced_search_ap_description_below_input_typography',
				'type'        => ASEA_Input_Filter::TYPOGRAPHY,
				'default'     => array(
					'font-family' => '',
					'font-size' => '16',
					'font-size-units' => 'px',
					'font-weight' => '',
				)
			),
			'advanced_search_ap_description_below_input_padding_top'      => array(
				'label'       => __( 'Top', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_input_padding_top',
				'max'         => 500,
				'min'         => 0,
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_ap_description_below_input_padding_bottom'   => array(
				'label'       => __( 'Bottom', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_description_below_input_padding_bottom',
				'max'         => '500',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => '10'
			),
			'advanced_search_ap_search_results_article_font_size'        => array(
				'label'       => __( 'Article Font Size ( px )', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_search_results_article_font_size',
				'max'         => '100',
				'min'         => '0',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_search_results_article_font_size']
			),
			'advanced_search_ap_background_image_url'           => array(
				'label'       => __( 'Background Image URL', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_image_url',
				'max'         => '300',
				'min'         => '0',
				'mandatory'    => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => ''
			),
			'advanced_search_ap_background_image_position_x'    => array(
				'label'       => __( 'Background Image Position X', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_image_position_x',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center' => __( 'Center', 'echo-advanced-search' ),
					'left'   => __( 'Left', 'echo-advanced-search' ),
					'right'  => __( 'Right', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_image_position_x']
			),
			'advanced_search_ap_background_image_position_y'    => array(
				'label'       => __( 'Background Image Position Y', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_image_position_y',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center'    => __( 'Center', 'echo-advanced-search' ),
					'top'       => __( 'Top', 'echo-advanced-search' ),
					'bottom'    => __( 'Bottom', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_image_position_y']
			),
			'advanced_search_ap_background_pattern_image_url'           => array(
				'label'       => __( 'Background Pattern Image URL', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_pattern_image_url',
				'max'         => '300',
				'min'         => '0',
				'mandatory'    => false,
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => ''
			),
			'advanced_search_ap_background_pattern_image_position_x'    => array(
				'label'       => __( 'Image Position X', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_pattern_image_position_x',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center' => __( 'Center', 'echo-advanced-search' ),
					'left'   => __( 'Left', 'echo-advanced-search' ),
					'right'  => __( 'Right', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_pattern_image_position_x']
			),
			'advanced_search_ap_background_pattern_image_position_y'    => array(
				'label'       => __( 'Image Position Y', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_pattern_image_position_y',
				'type'        => ASEA_Input_Filter::SELECTION,
				'options'     => array(
					'center'    => __( 'Center', 'echo-advanced-search' ),
					'top'       => __( 'Top', 'echo-advanced-search' ),
					'bottom'    => __( 'Bottom', 'echo-advanced-search' )
				),
				'default'     => $default_style['advanced_search_background_pattern_image_position_y']
			),
			'advanced_search_ap_background_pattern_image_opacity'       => array(
				'label'       => __( 'Opacity of the Image', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_pattern_image_opacity',
				'max'         => '5',
				'min'         => '0.1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => '0.5'
			),
			'advanced_search_ap_background_gradient_degree'     => array(
				'label'       => __( 'Degree of the Gradient', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_gradient_degree',
				'max'         => '1000',
				'min'         => '-1000',
				'type'        => ASEA_Input_Filter::NUMBER,
				'default'     => $default_style['advanced_search_background_gradient_degree']
			),
			'advanced_search_ap_background_gradient_opacity'    => array(
				'label'       => __( 'Opacity of the Gradient', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_gradient_opacity',
				'max'         => '5',
				'min'         => '0.1',
				'type'        => ASEA_Input_Filter::TEXT,
				'default'     => $default_style['advanced_search_background_gradient_opacity']
			),
			'advanced_search_ap_background_gradient_toggle'     => array(
				'label'       => __( 'Turn on Gradient Background', 'echo-advanced-search' ),
				'name'        => 'advanced_search_ap_background_gradient_toggle',
				'type'        => ASEA_Input_Filter::CHECKBOX,
				'default'     => $default_style['advanced_search_background_gradient_toggle']
			)
		);

		return $config_specification;
	}

	/**
	 * Get KB default configuration
	 *
	 * @return array contains default values for KB configuration
	 */
	public static function get_default_kb_config() {
		$config_specs = self::get_fields_specification();

		$configuration = array();
		foreach( $config_specs as $key => $spec ) {
			$default = isset($spec['default']) ? $spec['default'] : '';
			$configuration += array( $key => $default );
		}

		return $configuration;
	}

	/**
	 * Get names of all configuration items for KB configuration
	 * @return array
	 */
	public static function get_specs_item_names() {
		return array_keys( self::get_fields_specification() );
	}

	/**
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
