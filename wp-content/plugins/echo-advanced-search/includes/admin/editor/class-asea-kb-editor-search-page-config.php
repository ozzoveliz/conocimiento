<?php

/**
 * Configuration for the front end editor
 */

class ASEA_KB_Editor_Search_Page_Config {

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
	private static function search_zone() {

		$settings = [

			// Content tab
			'advanced_search_mp_results_msg' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.asea-search-results-title h3',
				'text' => 1
			],
			'advanced_search_mp_more_results_found' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'reload' => 1
			],

			// Styles tab
			'article_icon_color' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-article-title-icon',
				'style_name' => 'color',
				'description' => __( 'This setting will also change article icon color on the Main Page.', 'echo-advanced-search' ),
			],
			'article_font_color' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.asea-article-title a, .asea-article-metadata ul, .asea-article-excerpt',
				'style_name' => 'color',
				'description' => __( 'This setting will also change article font color on the Main Page.', 'echo-advanced-search' ),
			],

			// Features tab
			'advanced_search_mp_results_page_size' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			'advanced_search_results_box_header' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Post Meta', 'echo-advanced-search' )
			],
			'advanced_search_results_meta_created_on_toggle' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			'advanced_search_results_meta_author_toggle' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			'advanced_search_results_meta_categories_toggle' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
		];

		return [
			'search_zone' => [
				'title'     =>  __( 'Search Results Page', 'echo-advanced-search'),
				'classes'   => '#asea-search-results-container',
				'settings'  => $settings,
			]];
	}

	/**
	 * Retrieve Editor configuration
	 * @return array
	 */
	public static function get_config( $kb_config ) {

		$editor_config = [];

		$editor_config += self::search_zone();

		return $editor_config;
	}
}