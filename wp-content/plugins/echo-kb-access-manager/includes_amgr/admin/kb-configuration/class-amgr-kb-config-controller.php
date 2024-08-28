<?php

/**
 * Handle saving specific plugin configuration.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_KB_Config_Controller {

	public function __construct() {
        add_action( 'wp_ajax_amgr_save_kb_config_changes', array( $this, 'save_kb_config_changes_in_db' ) );
        add_action( 'wp_ajax_nopriv_amgr_save_kb_config_changes', array( $this, 'user_not_logged_in' ) );
	}

    /**
     * Triggered when user submits changes to KB configuration
     */
    public function save_kb_config_changes_in_db() {

        // verify that the request is authentic
        if ( empty( $_POST['_wpnonce_amgr_save_kb_config'] ) || ! wp_verify_nonce( $_POST['_wpnonce_amgr_save_kb_config'], '_wpnonce_amgr_save_kb_config' ) ) {
            EPKB_Utilities::ajax_show_error_die(__( 'Settings not saved. First refresh your page', 'echo-knowledge-base' ));
        }

        // ensure user has correct permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            EPKB_Utilities::ajax_show_error_die(__( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ));
        }

        // retrieve KB ID we are saving
        $kb_id = empty($_POST['amag_kb_id']) ? '' : EPKB_Utilities::sanitize_get_id( $_POST['amag_kb_id'] );
        if ( empty($kb_id) || is_wp_error( $kb_id ) ) {
            AMGR_Logging::add_log( "invalid kb id", $kb_id );
            EPKB_Utilities::ajax_show_error_die(__( 'This page is outdated. Please refresh your browser', 'echo-knowledge-base' ));
        }

        // core handles only default KB
        if ( $kb_id != AMGR_KB_Access_Config_DB::DEFAULT_KB_ID && ! defined( 'E' . 'MKB_PLUGIN_NAME' ) ) {
            AMGR_Logging::add_log("received invalid kb_id when saving config. (x5)", $kb_id );
            EPKB_Utilities::ajax_show_error_die(__( 'Ensure that Multiple KB add-on is active and refresh this page.', 'echo-knowledge-base' ));
            return;
        }

        // retrieve current AMGR KB configuration
        $orig_config = epkb_get_instance()->kb_access_config_obj->get_kb_config( $kb_id );
        if ( is_wp_error( $orig_config ) ) {
            EPKB_Utilities::ajax_show_error_die(__( 'Error occurred. ' . $orig_config->get_error_message() . '. Please refresh your browser and try again.', 'echo-knowledge-base' ));
        }

        // retrieve user input
        $field_specs = AMGR_KB_Config_Specs::get_fields_specification( $kb_id );
		$form_fields = empty($_POST['form']) ? array() : EPKB_Utilities::retrieve_and_sanitize_form( $_POST['form'], $field_specs );
        if ( empty($form_fields) ) {
            AMGR_Logging::add_log("form fields missing");
            EPKB_Utilities::ajax_show_error_die(__( 'Form fields missing. Please refresh your browser', 'echo-knowledge-base' ));
        } else if ( count($form_fields) < count($orig_config) ) {
            AMGR_Logging::add_log("Found KB configuration is incomplete", count($form_fields));
            EPKB_Utilities::ajax_show_error_die(__( 'Some form fields are missing. Please refresh your browser and try again or contact support', 'echo-knowledge-base' ));
        }

        $input_handler = new EPKB_Input_Filter();
		$new_kb_config = $input_handler->retrieve_and_sanitize_form_fields( $form_fields, $field_specs, $orig_config );

        // ensure kb id is preserved
        $new_kb_config['id'] = $kb_id;

        // sanitize and save AMGR configuration in the database. see AMGR_Settings_DB class
        $result = epkb_get_instance()->kb_access_config_obj->update_kb_configuration( $kb_id, $new_kb_config );
        if ( is_wp_error( $result ) ) {
            /* @var $result WP_Error */
            $message = $result->get_error_data();
            if ( empty($message) ) {
                EPKB_Utilities::ajax_show_error_die( $result->get_error_message(), __( 'Could not save the new configuration', 'echo-knowledge-base' ) );
            } else {
                EPKB_Utilities::ajax_show_error_die( $this->generate_error_summary( $message ), __( 'Configuration NOT saved due to following problems:', 'echo-knowledge-base' ) );
            }
        }

        // some settings require page reload
        $reload = $this->is_page_reload( $orig_config, $new_kb_config, $field_specs);

        // we are done here
        EPKB_Utilities::ajax_show_info_die( $reload ? __( 'Reload Settings saved. PAGE WILL RELOAD NOW.', 'echo-knowledge-base' ) : __( 'Settings saved', 'echo-knowledge-base' ) );
    }

    private function is_page_reload( $orig_settings, $new_settings, $spec ) {

        $diff = EPKB_Utilities::diff_two_dimentional_arrays( $new_settings, $orig_settings );
        foreach( $diff as $key => $value ) {
            if ( ! empty($spec[$key]['reload']) ) {
                return true;
            }
        }

        return false;
    }

    private function generate_error_summary( $errors ) {

        $output = '';

        if ( empty( $errors ) || ! is_array( $errors )) {
            return $output . __( 'unknown error', 'echo-knowledge-base' ) . ' (344)';
        }

        $output .= '<ol>';
        foreach( $errors as $error ) {
            $output .= '<li>' . wp_kses( $error, array('strong' => array('style' => array()),'div' => array('style' => array()),'p' => array()) ) . '</li>';
        }
        $output .= '</ol>';

        return $output;
    }

    public function user_not_logged_in() {
        EPKB_Utilities::ajax_show_error_die( '<p>' . __( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', __( 'Cannot save your changes', 'echo-knowledge-base' ) );
    }
}