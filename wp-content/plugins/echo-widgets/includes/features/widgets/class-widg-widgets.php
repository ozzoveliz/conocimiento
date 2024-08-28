<?php

/**
 * Setup of widgets
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Widgets {

	public function __construct() {

	    // do not run on KB Configuration page
        if ( isset($_REQUEST['page']) && $_REQUEST['page'] == WIDG_KB_Core::WIDG_KB_CONFIGURATION_PAGE && ! isset($_REQUEST['wizard-search']) && ! isset($_REQUEST['wizard-features']) ) {
            return;
        }

		add_action( 'widgets_init', array($this, 'register_widgets') );
    }

	/**
	 * Register KB widgets
	 */
	public function register_widgets() {
		register_widget( 'WIDG_Recent_Articles_Widget' );
		register_widget( 'WIDG_Category_Articles_Widget' );
		register_widget( 'WIDG_Tag_Articles_Widget' );
		register_widget( 'WIDG_Categories_List_Widget' );
		register_widget( 'WIDG_Tags_List_Widget' );
		register_widget( 'WIDG_Search_Articles_Widget' );
		register_widget( 'WIDG_Popular_Articles_Widget' );
	}

    /**
     * Register KB areas for widgets to be added to
     */
	public function register_kb_sidebar() {

	    // add KB sidebar area
        register_sidebar( array(
            'name'          => __('Echo KB Articles Sidebar', 'echo-knowledge-base'),
            'id'            => 'eckb_articles_sidebar',
            'before_widget' => '',
            'after_widget'  => '',
            'before_title'  => '<h4>',
            'after_title'   => '</h4>'
        ) );
    }
}
