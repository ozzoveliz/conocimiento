<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Store Frontend Editor configuration data
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_KB_Editor_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	public static function register_editor_hooks() {
		add_filter( 'eckb_editor_fields_specs', array('WIDG_KB_Editor_Config', 'get_editor_fields_specs' ), 10, 2 );
		add_filter( 'eckb_all_editors_get_current_config', array('WIDG_KB_Editor_Config', 'get_current_config' ), 10, 2 );
		add_filter( 'eckb_editor_fields_config', array('WIDG_KB_Editor_Config', 'get_editor_fields_config' ), 10, 3 );
	}

	/**
	 * Returnt to Editor add-onc specs
	 * @param $eckb_field_specification
	 * @param $kb_id
	 * @return array
	 */
	public static function get_editor_fields_specs( $eckb_field_specification, $kb_id ) {
		return array_merge( $eckb_field_specification, WIDG_KB_Config_Specs::get_fields_specification() );
	}

	/**
	 * Returnt to Wizard the current KB configuration
	 *
	 * @param $kb_config
	 * @param $kb_id
	 * @return array
	 */
	public static function get_current_config( $kb_config, $kb_id ) {
		$addon_config = widg_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		return array_merge( $kb_config, $addon_config );
	}

	/**
	 * Returnt to Editor add-onc configuration
	 * @param $editor_config
	 * @param $kb_config
	 * @param $page_type
	 * @return array
	 */
	public static function get_editor_fields_config( $editor_config, $kb_config, $page_type ) {
		
		$widg_config = [];

		if ( $page_type == 'article-page' ) {
			$widg_config += WIDG_KB_Editor_Article_Page_Config::get_config( $kb_config );
		}

		return array_merge( $editor_config, $widg_config );
	}

}