<?php

/**
 * Setup shortcodes for widget-like areas within posts and pages
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Shortcodes {

	public function __construct() {
		new WIDG_Recent_Articles_Shortcode();
		new WIDG_Category_Articles_Shortcode();
		new WIDG_Tag_Articles_Shortcode();
		new WIDG_Categories_List_Shortcode();
		new WIDG_Tags_List_Shortcode();
		new WIDG_Search_Articles_Shortcode();
		new WIDG_Popular_Articles_Shortcode();
	}
}
