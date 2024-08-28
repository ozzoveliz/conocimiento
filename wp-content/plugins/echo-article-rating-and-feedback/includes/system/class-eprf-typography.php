<?php

/**
 * KB Typography. See json_to_php_array() on how to upgrade.
 *
 * @copyright   Copyright (C) 2020, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class EPRF_Typography {

	public static $typography_defaults = [
		'font-family' => '',
		'font-size' => '',
		'font-size-units' => 'px',
		'font-weight' => '',
	];

	private static $font_families = [];

	/**
	 * Get array with google fonts names (only names)
	 * @return array
	 */

	public static function get_google_fonts_family_list() {

		if ( ! empty(self::$font_families) ) {
			return self::$font_families;
		}

		$font_families = [];
		foreach ( EPRF_KB_Core::get_font_data() as $font_family => $font_link ) {
			$font_families[] = $font_family;
		}
		self::$font_families = $font_families;

		return self::$font_families;
	}

	/**
	 * get google font link
	 * @param $font_name
	 * @return String
	 */
	public static function get_google_font_link( $font_name ) {
		$font_data = EPRF_KB_Core::get_font_data();
		return empty($font_data[$font_name]) ? '' : $font_data[$font_name];
	}

	/***
	 * To update Typography:
	 * 1. copy webfonts#webfontList https://developers.google.com/fonts/docs/developer_api  to $json_fonts_data as a string
	 * 2. clear debug log 
	 * 3. use json_to_php_array() in any place once 
	 * 4. get array from debug log 
	 * 5. replace array( and ), with [ and ]
	 * 6. replace it in the $font_data in this file 
	 * 7. Remove $json_fonts_data content 
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	public static function future_to_do_update_from_latest_google_typography() {

		$json_fonts_data = ''; // ' { google font data here } ';
		
		$json_fonts_data = json_decode( $json_fonts_data, true );
		
		if ( ! empty( $json_fonts_data['items'] ) ) {
			$json_fonts_data = $json_fonts_data['items'];
		}
		
		$php_fonts_data = [];
		$php_fonts_10_links = [];
		$php_fonts_10_link = 'https://fonts.googleapis.com/css2?';
		$i = 0;
		
		foreach( $json_fonts_data as $font ) {
			
			$i++;
			
			if ( ! ($i % 10) ) { 
				$php_fonts_10_link = substr( $php_fonts_10_link, 0, -1 );
				$php_fonts_10_links[] = $php_fonts_10_link;
				$php_fonts_10_link = 'https://fonts.googleapis.com/css2?';
			}
			
			$font_options = '';
			$php_fonts_10_link .= 'family=' . str_replace( ' ', '+', $font['family'] ) . ':ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&';
			
			if ( ! empty( $font['variants'] ) ) {
				foreach ( $font['variants'] as $variant ) {
					if ( $variant == 'regular' ) {
						$variant = '400';
					}
					if ( $variant == 'italic' ) {
						$variant = '400italic';
					}
					// add , if it is not first option or : if first
					$font_options .= $font_options ? ',' . $variant : ':' . $variant;
				}
			}

			$php_fonts_data[$font['family']] = 'https://fonts.googleapis.com/css?family=' . str_replace( ' ', '+', $font['family'] ) . $font_options;
			
		}
		
		error_log(var_export($php_fonts_data, true));
		error_log(var_export($php_fonts_10_links, true));
	}
}