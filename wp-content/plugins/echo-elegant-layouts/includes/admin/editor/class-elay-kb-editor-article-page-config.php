<?php

/**
 * Configuration for the front end editor
 */
class ELAY_KB_Editor_Article_Page_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';
	const EDITOR_TAB_GLOBAL = 'global';
	const EDITOR_TAB_DISABLED = 'hidden';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	/**
	 * Retrieve Editor configuration
	 * @param $kb_config
	 * @return array
	 */
	public static function get_config( $kb_config ) {

		$editor_config = [];

		$editor_config += ELAY_KB_Editor_Sidebar_Config::get_config( 'article_page', $kb_config );

		return $editor_config;
	}
}