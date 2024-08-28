<?php

/**
 * Lists settings, default values and display of ELAY modules for KB Core Modular main page.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_KB_Config_Layout_Modular {

    /**
     * Defines KB configuration for this theme.
     * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => 'false' )
     *
     * @return array with both basic and theme-specific configuration
     */
    public static function get_fields_specification() {

	    /**
	     * RESOURCE LINKS MODULE
	     */
        $config_specification = array(
	        'ml_resource_links_columns'                             => array(
		        'label'       => __( 'Number of Columns', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_columns',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        '2-col'     => __( '2 Columns',   'echo-elegant-layouts' ),
			        '3-col'     => __( '3 Columns',   'echo-elegant-layouts' ),
			        '4-col'     => __( '4 Columns',   'echo-elegant-layouts' ),
		        ),
		        'default'     => '3-col'
	        ),

	        // Content Above the Resources
	        'ml_resource_links_container_background_color'          => array(
		        'label'       => __( 'Row Background Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_background_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#FFFFFF'
	        ),
	        'ml_resource_links_container_title_html_tag'            => array(
		        'label'       => __( 'Title HTML Tag', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_title_html_tag',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'default'     => 'h2',
		        'style'       => 'small',
		        'options'     => array(
			        'div' => 'div',
			        'h1' => 'h1',
			        'h2' => 'h2',
			        'h3' => 'h3',
			        'h4' => 'h4',
			        'h5' => 'h5',
			        'h6' => 'h6',
			        'span' => 'span',
			        'p' => 'p',
		        ),
	        ),
	        'ml_resource_links_container_title_text'                => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_title_text',
		        'max'         => '150',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'Help Links'
	        ),
	        'ml_resource_links_container_text_alignment'            => array(
		        'label'       => __( 'Main Text Alignment', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_text_alignment',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'left'      => __( 'Left', 'echo-elegant-layouts' ),
			        'center'    => __( 'Center', 'echo-elegant-layouts' ),
			        'right'     => __( 'Right', 'echo-elegant-layouts' )
		        ),
		        'default'     => 'center'
	        ),
	        'ml_resource_links_container_text_color'                => array(
		        'label'       => __( 'Main Text Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_text_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#000000'
	        ),
	        'ml_resource_links_container_description_text'          => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_container_description_text',
		        'max'         => '500',
		        'mandatory'   => false,
		        'input_size'  => 'large',
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Need assistance or have questions? Explore our list of resources below.' )
	        ),

			// All Resources - Boxes
	        'ml_resource_links_border_color'                        => array(
		        'label'       => __( 'Resource Border Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_border_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#fdfdfd'
	        ),
	        'ml_resource_links_background_color'                    => array(
		        'label'       => __( 'Resource Background Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_background_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#fdfdfd'
	        ),
	        'ml_resource_links_background_hover_color'              => array(
		        'label'       => __( 'Resource Background Hover Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_background_hover_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#fdfdfd'
	        ),
	        'ml_resource_links_description_text_alignment'          => array(
		        'label'       => __( 'Resource Description Text Alignment', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_description_text_alignment',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'left'      => __( 'Left', 'echo-elegant-layouts' ),
			        'center'    => __( 'Center', 'echo-elegant-layouts' ),
			        'right'     => __( 'Right', 'echo-elegant-layouts' )
		        ),
		        'default'     => 'center'
	        ),
	        'ml_resource_links_description_text_color'              => array(
		        'label'       => __( 'Resource Description Text Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_description_text_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#000000'
	        ),
	        'ml_resource_links_border_width'                        => array(
		        'label'       => __( 'Match Categories & Articles width', 'echo-knowledge-base' ),
		        'name'        => 'ml_resource_links_border_width',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_shadow'                              => array(
		        'label'       => __( 'Match Categories & Articles Shadow', 'echo-knowledge-base' ),
		        'name'        => 'ml_resource_links_shadow',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),

			// All Resources - Icons
	        'ml_resource_links_icon_location'                       => array(
		        'label'       => __( 'Resource Icon Location', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_icon_location',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'top'       => __( 'Top', 'echo-elegant-layouts' ),
			        'left'      => __( 'Left', 'echo-elegant-layouts' ),
			        'right'     => __( 'Right', 'echo-elegant-layouts' )
		        ),
		        'default'     => 'top'
	        ),
	        'ml_resource_links_icon_color'                          => array(
		        'label'       => __( 'Resource Icon Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_icon_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#43596e'
	        ),
	        'ml_resource_links_icon_type'                           => array(
		        'label'       => __( 'Resource Icon Type', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_icon_type',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'none'      => '-----',
			        'font'      => __( 'Font Icon', 'echo-elegant-layouts' ),
			        'image'     => __( 'Image Icon', 'echo-elegant-layouts' ),
		        ),
		        'default'     => 'font'
	        ),
	        'ml_resource_links_icon_image_size'                     => array(
		        'label'       => __( 'Resource Icon Size ( px )', 'echo-knowledge-base' ),
		        'name'        => 'ml_resource_links_icon_image_size',
		        'max'         => '250',
		        'min'         => '0',
		        'type'        => ELAY_Input_Filter::NUMBER,
		        'style'       => 'small',
		        'default'     => '80'
	        ),

			// All Resources - Buttons / Links
	        'ml_resource_links_option'                              => array(
		        'label'       => __( 'Resource Style', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_option',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'button'    => __( 'Button', 'echo-elegant-layouts' ),
			        'link'      => __( 'Box', 'echo-elegant-layouts' ),
		        ),
		        'default'     => 'button'
	        ),
	        'ml_resource_links_button_text_color'                   => array(
		        'label'       => __( 'Resource Text Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_button_text_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#fdfdfd'
	        ),
	        'ml_resource_links_button_text_hover_color'             => array(   // TODO REMOVE
		        'label'       => __( 'Resource Text Hover Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_button_text_hover_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#fdfdfd'
	        ),
	        'ml_resource_links_button_background_color'             => array(
		        'label'       => __( 'Resource Background Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_button_background_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#43596e'
	        ),
	        'ml_resource_links_button_background_hover_color'       => array(    // TODO REMOVE
		        'label'       => __( 'Resource Background Hover Color', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_button_background_hover_color',
		        'max'         => '7',
		        'min'         => '7',
		        'type'        => ELAY_Input_Filter::COLOR_HEX,
		        'default'     => '#527987'
	        ),
	        'ml_resource_links_button_location'                     => array(
		        'label'       => __( 'Resource Location', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_button_location',
		        'type'        => ELAY_Input_Filter::SELECTION,
		        'options'     => array(
			        'left'      => __( 'Left', 'echo-elegant-layouts' ),
			        'center'    => __( 'Center', 'echo-elegant-layouts' ),
			        'right'     => __( 'Right', 'echo-elegant-layouts' )
		        ),
		        'default'     => 'center'
	        ),

	        // Resource #1
	        'ml_resource_links_1'                                   => array(
		        'label'       => __( 'Resource #1', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'ml_resource_links_1_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Contact Us' )
	        ),
	        'ml_resource_links_1_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Reach Out to Our Team for General Inquiries.' )
	        ),
	        'ml_resource_links_1_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'Contact Us'
	        ),
	        'ml_resource_links_1_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_1_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'epkbfa-phone-square'
	        ),
	        'ml_resource_links_1_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_1_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #2
	        'ml_resource_links_2'                                   => array(
		        'label'       => __( 'Resource #2', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'ml_resource_links_2_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'Community Forum'
	        ),
	        'ml_resource_links_2_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Participate in Community Discussions and Offer Your Support to Others.' )
	        ),
	        'ml_resource_links_2_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Join Our Community' )
	        ),
	        'ml_resource_links_2_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_2_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'epkbfa-comments-o'
	        ),
	        'ml_resource_links_2_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_2_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #3
	        'ml_resource_links_3'                                   => array(
		        'label'       => __( 'Resource #3', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'on'
	        ),
	        'ml_resource_links_3_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Open Support Ticket' )
	        ),
	        'ml_resource_links_3_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'If You Need Assistance with an Issue, Submit a Support Request.' )
	        ),
	        'ml_resource_links_3_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => __( 'Submit a Ticket' )
	        ),
	        'ml_resource_links_3_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_3_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => 'epkbfa-medkit'
	        ),
	        'ml_resource_links_3_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_3_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #4
	        'ml_resource_links_4'                                   => array(
		        'label'       => __( 'Resource #4', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_4_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_4_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_4_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_4_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_button_url',
		        'max'         => '150',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_4_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_4_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_4_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #5
	        'ml_resource_links_5'                                   => array(
		        'label'       => __( 'Resource #5', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_5_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_5_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_5_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_5_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_5_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_5_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_5_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #6
	        'ml_resource_links_6'                                   => array(
		        'label'       => __( 'Resource #6', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_6_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_6_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_6_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_6_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_6_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_6_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_6_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #7
	        'ml_resource_links_7'                                   => array(
		        'label'       => __( 'Resource #7', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_7_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_7_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_7_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_7_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_7_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_7_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_7_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),

	        // Resource #8
	        'ml_resource_links_8'                                   => array(
		        'label'       => __( 'Resource #8', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8',
		        'type'        => ELAY_Input_Filter::CHECKBOX,
		        'default'     => 'off'
	        ),
	        'ml_resource_links_8_title_text'                        => array(
		        'label'       => __( 'Title', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_title_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_8_description_text'                  => array(
		        'label'       => __( 'Description', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_description_text',
		        'max'         => '500',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_8_button_text'                       => array(
		        'label'       => __( 'Link Text', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_button_text',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_8_button_url'                        => array(
		        'label'       => __( 'Link URL', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_button_url',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::URL,
		        'default'     => ''
	        ),
	        'ml_resource_links_8_icon_font'                         => array(
		        'label'       => __( 'Font Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_icon_font',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
	        'ml_resource_links_8_icon_image'                        => array(
		        'label'       => __( 'Image Icon', 'echo-elegant-layouts' ),
		        'name'        => 'ml_resource_links_8_icon_image',
		        'max'         => '150',
		        'min'         => '0',
		        'mandatory'   => false,
		        'type'        => ELAY_Input_Filter::TEXT,
		        'default'     => ''
	        ),
        );

        return $config_specification;
    }

	/*
     * Return default values from given specification.
     * @param $config_specs
     * @return array
     */
    /*public static function get_specs_defaults( $config_specs ) {
        $default_configuration = array();
        foreach( $config_specs as $key => $spec ) {
            $default = isset($spec['default']) ? $spec['default'] : '';
            $default_configuration += array( $key => $default );
        }
        return $default_configuration;
    }*/
}
