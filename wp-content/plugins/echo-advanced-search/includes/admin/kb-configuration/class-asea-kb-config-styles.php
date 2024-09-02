<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contains sets of styles for advanced search
 *
 */
class ASEA_KB_Config_Styles {

	public static function get_advanced_search_box_style_names() {
		return array(
			'1-search' => 'Design 1',
			'2-search' => 'Design 2',
			'3-search' => 'Design 3',

			'4-search' => 'Design 4',
			'5-search' => 'Design 5',
			'6-search' => 'Design 6',

			'7-search' => 'Design 7: City',
			'8-search' => 'Design 8: Warehouse',
			'9-search' => 'Design 9: Formal',
		);
	}

	/**
	 * Return specific advanced search set
	 *
	 * @param $ignore
	 * @param $pix
	 * @param $set_name
	 *
	 * @return array
	 */
	public static function get_advanced_search_box_style_set( $ignore, $pix, $set_name ) {

		switch( $set_name ) {
			case '1-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_1( $pix ) );
				break;
		 	case '2-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_2( $pix ) );
				break;
			case '3-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_3( $pix ) );
				break;
			case '4-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_4( $pix ) );
				break;
			case '5-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_5( $pix ) );
				break;
			case '6-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_6( $pix ) );
				break;
			case '7-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_7( $pix ) );
				break;
			case '8-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_8( $pix ) );
				break;
			case '9-search':
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_9( $pix ) );
				break;
			default:
				return array_merge( self::get_default_box_styles( $pix ), self::advanced_search_style_1( $pix ) );
				break;
		}
	}

	/**
	 * Default values for all styles
	 *
	 * @param $pix
	 * @return array
	 */
	public static function get_default_box_styles( $pix ) {
		return array(
			//Setup Settings ------------------------------------------/
			'advanced_search_' . $pix . '_visibility' =>  'off',

			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  50,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  50,
			'advanced_search_' . $pix . '_box_padding_left' =>  0,
			'advanced_search_' . $pix . '_box_padding_right' =>  0,
			'advanced_search_' . $pix . '_box_margin_top' =>  0,
			'advanced_search_' . $pix . '_box_margin_bottom' =>  40,
			'advanced_search_' . $pix . '_box_font_width' =>  80,
			
			//Title Settings
			'advanced_search_' . $pix . '_title_padding_bottom' =>  30,
			'advanced_search_' . $pix . '_title_text_shadow_x_offset' =>  2,
			'advanced_search_' . $pix . '_title_text_shadow_y_offset' =>  2,
			'advanced_search_' . $pix . '_title_text_shadow_blur' =>  2,
			'advanced_search_' . $pix . '_title_text_shadow_toggle' =>  'off',
			'advanced_search_' . $pix . '_title_tag' =>  'div',
			
			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_padding_top' =>  20,
			'advanced_search_' . $pix . '_description_below_title_padding_bottom' =>  20,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_x_offset' =>  1,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_y_offset' =>  2,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_blur' =>  3,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle' =>  'off',
			
			//Input Box Settings
			'advanced_search_' . $pix . '_box_input_width' =>  50,
			'advanced_search_' . $pix . '_input_border_width' =>  1,
			'advanced_search_' . $pix . '_input_box_radius' =>  0,
			'advanced_search_' . $pix . '_input_box_padding_top' =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom' =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left' =>  20,
			'advanced_search_' . $pix . '_input_box_padding_right' =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_x_offset' =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_y_offset' =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_blur' =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_spread' =>  5,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' =>  '',
			'advanced_search_' . $pix . '_input_box_search_icon_placement' =>  'none',
			'advanced_search_' . $pix . '_input_box_loading_icon_placement' =>  'right',

			//Description 2 Settings
			'advanced_search_' . $pix . '_description_below_input_padding_top' =>  20,
			'advanced_search_' . $pix . '_description_below_input_padding_bottom' =>  20,
			
			//Filter
			'advanced_search_' . $pix . '_filter_toggle' =>  'on',
			'advanced_search_' . $pix . '_auto_complete_wait' =>  '1000',
			'advanced_search_' . $pix . '_results_list_size' =>  '10',
			'advanced_search_' . $pix . '_results_page_size' =>  '10',
			'advanced_search_' . $pix . '_show_top_category' =>  'off',
			'advanced_search_' . $pix . '_box_results_style' =>  'off',
			
			//Results Settings
			'advanced_search_' . $pix . '_filter_dropdown_width' =>  '260',
			'advanced_search_' . $pix . '_filter_category_level' =>  'top',
			
			//Background Image
			'advanced_search_' . $pix . '_background_image_url' =>  ' ',
			'advanced_search_' . $pix . '_background_image_position_x' =>  'left',
			'advanced_search_' . $pix . '_background_image_position_y' =>  'top',
			
			//Background Pattern Image
			'advanced_search_' . $pix . '_background_pattern_image_url' =>  ' ',
			'advanced_search_' . $pix . '_background_pattern_image_position_x' =>  'left',
			'advanced_search_' . $pix . '_background_pattern_image_position_y' =>  'top',
			'advanced_search_' . $pix . '_background_pattern_image_opacity' =>  '0.5',
			
			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_degree' =>  '45',
			'advanced_search_' . $pix . '_background_gradient_opacity' =>  1,
			'advanced_search_' . $pix . '_background_gradient_toggle' =>  'off',
		);
	}
	/**
	 * DEFAULT for COLOR SETTINGS
	 *
	 * @param $pix
	 * @return array
	 */
	private static function advanced_search_style_1( $pix ) {
		
		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  50,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  50,

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#ffffff',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#eeee22',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',
			'advanced_search_' . $pix . '_background_color'                             =>  '#f7941d',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_border_width'                    =>  0,
			'advanced_search_' . $pix . '_input_box_radius'                      =>  50,
			'advanced_search_' . $pix . '_input_box_shadow_spread'               =>  5,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  30,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  30,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',
			'advanced_search_' . $pix . '_input_box_shadow_x_offset' 			 =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_y_offset' 			 =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_blur' 				 =>  0,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '21, 21, 21, 0.15',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'How can we help?',
			'advanced_search_' . $pix . '_title_toggle'                          =>  'on',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'off',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'off',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search'
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_2( $pix ) {

		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  50,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  50,

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#ffffff',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#eeee22',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_box_radius'                      =>  4,
			'advanced_search_' . $pix . '_box_input_width'                       =>  40,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  20,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_x_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_y_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_blur' 				 =>  4,
			'advanced_search_' . $pix . '_input_box_shadow_spread' 				 =>  2,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '21, 21, 21, 0.15',
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',

			//Background Image
			'advanced_search_' . $pix . '_background_image_url'                  =>  plugins_url() . "/echo-advanced-search/img/presets/office-preset-bg.jpg",
			'advanced_search_' . $pix . '_background_image_position_y'           =>  'center',

			//Background Pattern Image
			'advanced_search_' . $pix . '_background_pattern_image_opacity'      =>  0.4,

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_opacity'           =>  0.8,
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'on',
			'advanced_search_' . $pix . '_background_gradient_from_color'        =>  '#00c1b6',
			'advanced_search_' . $pix . '_background_gradient_to_color'          =>  '#136eb5',


			//Text
			'advanced_search_' . $pix . '_title_toggle'                          =>  'on',
			'advanced_search_' . $pix . '_title'                                 =>  'Welcome to our Knowledge Base',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'off',
			'advanced_search_' . $pix . '_description_below_input'               =>  '<i>Tip: Start typing in the input box for immediate search results.</i>',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'on',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',

			//Filter
			'advanced_search_' . $pix . '_filter_toggle' =>  'off',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_3( $pix ) {

		$styles = array(

			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  30,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  30,

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#ffffff',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#eeee22',
			'advanced_search_' . $pix . '_title_padding_bottom' 				 =>  0,

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_box_radius'                      =>  50,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  20,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '0, 0, 0, 0',
			
			//Description 2 Settings
			'advanced_search_' . $pix . '_description_below_input_padding_top'   =>  0,


			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'on',
			'advanced_search_' . $pix . '_background_gradient_from_color'        =>  '#00c1b6',
			'advanced_search_' . $pix . '_background_gradient_to_color'          =>  '#136eb5',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'How can we <strong>help?</strong>',
			'advanced_search_' . $pix . '_title_toggle'                          =>  'on',
			'advanced_search_' . $pix . '_description_below_title'               =>  'Welcome to our Support Portal. <strong>Search for answers using the search box below</strong>,</br> or create a support ticket if you cannot find your answer.',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'on',
			'advanced_search_' . $pix . '_description_below_input'               =>  'Popular searches: '.
																						'<strong><a href="https://www.echoknowledgebase.com/documentation/" target="_blank">KB Main Page</a></strong>, '.
																						'<strong><a href="https://www.echoknowledgebase.com/documentation/category/knowledge-base-plugin-core/kb-faqs/" target="_blank">FAQs</a></strong>, '.
																						'<strong><a href="https://www.echoknowledgebase.com/documentation/kb-widgets-overview/" target="_blank">Widgets</a></strong>',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'on',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search'
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_4( $pix ) {

		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  50,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  50,

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#ffffff',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#1e73be',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',
			'advanced_search_' . $pix . '_background_color'                             =>  '#a7d689',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_box_radius'                      =>  4,
			'advanced_search_' . $pix . '_box_input_width'                       =>  40,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  20,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_x_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_y_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_blur' 				 =>  4,
			'advanced_search_' . $pix . '_input_box_shadow_spread' 				 =>  2,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '21, 21, 21, 0.15',
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'off',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'Welcome to our Knowledge Base',
			'advanced_search_' . $pix . '_title_toggle'        				     =>  'on',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'off',
			'advanced_search_' . $pix . '_description_below_input'               =>  '<a href="https://www.echoknowledgebase.com/documentation/" target="_blank">Documentation</a> | '.
				'<a href="https://www.echoknowledgebase.com/blog/" target="_blank">Blog</a> | ' .
				'<a href="https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/" target="_blank">Demos</a> | ' .
				'<a href="https://www.echoknowledgebase.com/contact-us/" target="_blank">Support</a>',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'on',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',

			//Filter
			'advanced_search_' . $pix . '_filter_toggle' =>  'off',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_5( $pix ) {

		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top'                       =>  30,
			'advanced_search_' . $pix . '_box_padding_bottom'                    =>  30,
			'advanced_search_' . $pix . '_background_color'                      =>  '#ffffff',

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#000000',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#000000',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_padding_bottom'       =>  20,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_border_width'                    =>  7,
			'advanced_search_' . $pix . '_input_box_radius'                      =>  30,
			'advanced_search_' . $pix . '_input_box_shadow_spread'               =>  20,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  35,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  10,
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '0, 0, 0, 0',
			
			//Description 2 Settings
			'advanced_search_' . $pix . '_description_below_input_padding_top'   =>  30,

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'off',


			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'How can we help?',
			'advanced_search_' . $pix . '_title_toggle'                          =>  'on',
			'advanced_search_' . $pix . '_description_below_title'               =>  'Welcome to our Support Portal. <strong>Search for answers using the search box below</strong>,<br/> or create a support ticket if you cannot find your answer.',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'on',
			'advanced_search_' . $pix . '_description_below_input'               =>  'If you cannot find answer then <strong><a href="https://www.echoknowledgebase.com/contact-us/" target="_blank">contact us</a></strong>.',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'on',

			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search'
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_6( $pix ) {

		$styles = array(

			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top' =>  50,
			'advanced_search_' . $pix . '_box_padding_bottom' =>  50,

			//Title Settings
			'advanced_search_' . $pix . '_title_font_color'                      =>  '#ffffff',
			'advanced_search_' . $pix . '_link_font_color'                       =>  '#1e73be',
			'advanced_search_' . $pix . '_title_text_shadow_x_offset'            =>  0,
			'advanced_search_' . $pix . '_title_text_shadow_y_offset'            =>  0,
			'advanced_search_' . $pix . '_title_text_shadow_blur' 				 =>  30,


			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'off',
			'advanced_search_' . $pix . '_background_color'                             =>  '#a7d689',

			//Input Box Settings and Results Settings
			'advanced_search_' . $pix . '_input_box_radius'                      =>  4,
			'advanced_search_' . $pix . '_box_input_width'                       =>  40,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  20,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_x_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_y_offset' 			 =>  1,
			'advanced_search_' . $pix . '_input_box_shadow_blur' 				 =>  4,
			'advanced_search_' . $pix . '_input_box_shadow_spread' 				 =>  15,
			'advanced_search_' . $pix . '_input_box_shadow_rgba' 			     =>  '21, 21, 21, 0.5',
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',

			//Background Image
			'advanced_search_' . $pix . '_background_image_url'                  =>  plugins_url() . "/echo-advanced-search/img/presets/office-preset-bg.jpg",
			'advanced_search_' . $pix . '_background_image_position_y'           =>  'center',

			//Background Pattern Image
			'advanced_search_' . $pix . '_background_pattern_image_opacity'      =>  0.4,

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_degree'            =>  95,
			'advanced_search_' . $pix . '_background_gradient_opacity'           =>  0.2,
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'on',
			'advanced_search_' . $pix . '_background_gradient_from_color'        =>  '#000000',
			'advanced_search_' . $pix . '_background_gradient_to_color'          =>  '#000000',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'Welcome to our Knowledge Base',
			'advanced_search_' . $pix . '_title_toggle'                          =>  'on',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'off',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'off',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Search the documentation...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',

			//Filter
			'advanced_search_' . $pix . '_filter_toggle' =>  'off',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
				'advanced_search_' . $pix . '_input_box_loading_icon_placement'       =>  'left',
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_7( $pix ) {

		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top'                       =>  20,
			'advanced_search_' . $pix . '_box_padding_bottom'                    =>  20,

			//Title Settings			
			'advanced_search_' . $pix . '_title_padding_bottom'                  =>  5,
			'advanced_search_' . $pix . '_title_text_shadow_toggle'              =>  'on',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_padding_bottom'       =>  40,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'on',

			//Input Box Settings
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  15,
			'advanced_search_' . $pix . '_input_box_padding_left'                =>  30,
			'advanced_search_' . $pix . '_input_box_padding_right'               =>  30,
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'left',
			
			//Description 2 Settings
			'advanced_search_' . $pix . '_description_below_input_padding_top'   =>  40,

			//Background Image
			'advanced_search_' . $pix . '_background_image_url'                  =>  plugins_url() . "/echo-advanced-search/img/presets/city-bg-preset.jpg",
			'advanced_search_' . $pix . '_background_image_position_y'           =>  'center',

			//Background Pattern Image
			'advanced_search_' . $pix . '_background_pattern_image_opacity'      =>  0.4,

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_opacity'           =>  0.8,
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'on',
			'advanced_search_' . $pix . '_background_gradient_from_color'        =>  '#00c1b6',
			'advanced_search_' . $pix . '_background_gradient_to_color'          =>  '#136eb5',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'How can we <strong>help?</strong>',
			'advanced_search_' . $pix . '_description_below_title'               =>  'We are here for you. <strong>Find</strong> the <strong>answers</strong> you are looking for below or<br><i> if you need more help submit a support ticket <a href="https://www.echoknowledgebase.com/contact-us/" target="_blank"> here.</a></i>',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'on',
			'advanced_search_' . $pix . '_description_below_input'               =>  'Useful links: <a href="https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/" target="_blank">Demos</a>, ' .
		                                                                                           '<a href="https://www.echoknowledgebase.com/wordpress-add-ons/" target="_blank">Add-ons</a>, ' .
			                                                                                       '<a href="https://www.echoknowledgebase.com/documentation/" target="_blank">Documentation</a><br>' .
			                                                                                       '<i>10,000 articles in our knowledge base</i>',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Have a question? Type your search term here...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',
			'advanced_search_' . $pix . '_filter_indicator_text'                 =>  '',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				
			) );
		}
		
		return $styles;
	}

	private static function advanced_search_style_8( $pix ) {

		$styles = array(
			//Container Settings
			'advanced_search_' . $pix . '_box_padding_top'                       =>  40,
			'advanced_search_' . $pix . '_box_padding_bottom'                    =>  40,
			'advanced_search_' . $pix . '_background_color'                              =>  '#000000',
			
			//Title Settings
			'advanced_search_' . $pix . '_title_padding_bottom'                  =>  5,
			'advanced_search_' . $pix . '_title_text_shadow_toggle'              =>  'off',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_padding_bottom'=>  20,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'on',

			//Input Box Settings
			'advanced_search_' . $pix . '_input_box_radius'                      =>  50,
			'advanced_search_' . $pix . '_input_box_shadow_blur'                 =>  20,
			'advanced_search_' . $pix . '_input_box_shadow_spread'               =>  14,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  10,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  10,
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',


			//Text
			'advanced_search_' . $pix . '_title'                                 =>  'How can we <strong>help?</strong>',
			'advanced_search_' . $pix . '_description_below_title'               =>  'You can search our knowledge base to find answers to the most common questions.',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'on',
			'advanced_search_' . $pix . '_description_below_input'               =>  '',
			'advanced_search_' . $pix . '_description_below_input_toggle'        =>  'off',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Type your search term here...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',
			'advanced_search_' . $pix . '_filter_indicator_text'                 =>  'Filter',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				
			) );
		}
		
		return $styles;
	}
	private static function advanced_search_style_9( $pix ) {

		$styles = array(
			//Title Settings
			'advanced_search_' . $pix . '_title_padding_bottom'                  =>  55,
			'advanced_search_' . $pix . '_title_text_shadow_toggle'              =>  'on',

			//Description 1 Settings
			'advanced_search_' . $pix . '_description_below_title_padding_bottom'=>  40,
			'advanced_search_' . $pix . '_description_below_title_text_shadow_toggle'   =>  'on',

			//Input Box Settings
			'advanced_search_' . $pix . '_input_box_shadow_spread'               =>  20,
			'advanced_search_' . $pix . '_input_box_padding_top'                 =>  15,
			'advanced_search_' . $pix . '_input_box_padding_bottom'              =>  15,
			'advanced_search_' . $pix . '_input_box_search_icon_placement'       =>  'right',
			'advanced_search_' . $pix . '_input_box_loading_icon_placement'      =>  'left',

			//Description 2 Settings			
			'advanced_search_' . $pix . '_description_below_input_padding_top'   =>  40,

			//Background Image
			'advanced_search_' . $pix . '_background_image_url'                  =>  plugins_url() . "/echo-advanced-search/img/presets/office-preset-bg.jpg",
			'advanced_search_' . $pix . '_background_image_position_y'           =>  'center',

			//Background Gradient
			'advanced_search_' . $pix . '_background_gradient_degree'            =>  '180',
			'advanced_search_' . $pix . '_background_gradient_opacity'           =>  0.5,
			'advanced_search_' . $pix . '_background_gradient_toggle'            =>  'on',

			//Text
			'advanced_search_' . $pix . '_title'                                 =>  '<strong>How can we help?</strong>',
			'advanced_search_' . $pix . '_description_below_title'               =>  '',
			'advanced_search_' . $pix . '_description_below_title_toggle'        =>  'off',
			'advanced_search_' . $pix . '_description_below_input'               =>  '<a href="https://www.echoknowledgebase.com/documentation/" target="_blank">Documentation</a> | '.
			                                                                                           '<a href="https://www.echoknowledgebase.com/blog/" target="_blank">Blog</a> | ' .
			                                                                                           '<a href="https://www.echoknowledgebase.com/demo-1-knowledge-base-basic-layout/" target="_blank">Demos</a> | ' .
			                                                                                           '<a href="https://www.echoknowledgebase.com/contact-us/" target="_blank">Support</a>',
			'advanced_search_' . $pix . '_box_hint'                              =>  'Type your search term here...',
			'advanced_search_' . $pix . '_button_name'                           =>  'Search',
			'advanced_search_' . $pix . '_filter_indicator_text'                 =>  '',
		);
		
		if ( is_rtl() ) {
			$styles = array_merge( $styles, array(
				
			) );
		}
		
		return $styles;
	}

}