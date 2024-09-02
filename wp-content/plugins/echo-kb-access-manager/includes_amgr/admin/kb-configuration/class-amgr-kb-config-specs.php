<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lists all KB configuration settings and adds filter to get configuration from add-ons.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_KB_Config_Specs {
	
	private static $cached_specs = array();

	/**
	 * Defines how KB configuration fields will be displayed, initialized and validated/sanitized
	 *
	 * ALL FIELDS ARE MANDATORY by default ( otherwise use 'mandatory' => false )
	 *
	 * @param int $kb_id is the ID of knowledge base to get default config for
	 * @return array with KB config specification
	 */
	public static function get_fields_specification( $kb_id ) {

		// if kb_id is invalid use default KB
		if ( ! EPKB_Utilities::is_positive_int( $kb_id ) ) {
			AMGR_Logging::add_log( 'setting kb_id to 0 because kb_id is not positive int', $kb_id );
			$kb_id = AMGR_KB_Access_Config_DB::DEFAULT_KB_ID;
		}

		// retrieve settings if already cached
		if ( ! empty(self::$cached_specs[$kb_id]) && is_array(self::$cached_specs[$kb_id]) ) {
			return self::$cached_specs[$kb_id];
		}

		// get all configuration
		$config_specification = array(
				'id' => array(
					'label'       => 'kb_id',
					'type'        => EPKB_Input_Filter::ID,
					'internal'    => true,
					'default'     => $kb_id
				),
				'no_access_title' => array(
						'label'       => esc_html__( 'No Access - Title', 'echo-knowledge-base' ),
						'name'        => 'no_access_title',
						'info'        => esc_html__( 'If user cannot access content and 403 return code is configured to be returned then this title will be displayed.' ),
						'size'        => '30',
						'max'         => '100',
						'min'         => '1',
						'mandatory'   => false,
						'type'        => EPKB_Input_Filter::TEXT,
						'default'     => esc_html__( 'Access Denied', 'echo-knowledge-base' )
				),
				'no_access_text' => array(
						'label'       => esc_html__( 'No Access - Message (User Not Logged In)', 'echo-knowledge-base' ),
						'name'        => 'no_access_text',
						'info'        => esc_html__( 'If user cannot access content and 403 return code is configured to be returned then this message will be displayed.' ),
						'size'        => '30',
						'max'         => '500',
						'min'         => '1',
						'mandatory'   => false,
						'type'        => EPKB_Input_Filter::TEXT,
						'default'     => esc_html__( 'You do not have permission to access this KB content.', 'echo-knowledge-base' )
				),
				'no_access_text_logged' => array(
					'label'       => esc_html__( 'No Access - Message (User Logged In)', 'echo-knowledge-base' ),
					'name'        => 'no_access_text_logged',
					'info'        => esc_html__( 'If user cannot access content and 403 return code is configured to be returned then this message will be displayed.' ),
					'size'        => '30',
					'max'         => '500',
					'min'         => '1',
					'mandatory'   => false,
					'type'        => EPKB_Input_Filter::TEXT,
					'default'     => esc_html__( 'You do not have permission to access this KB content.', 'echo-knowledge-base' )
				),
				'no_access_action_user_without_login' => array(
						'label'       => esc_html__( 'No Access Action - User Not Logged In', 'echo-knowledge-base' ),
						'name'        => 'no_access_action_user_without_login',
						'info'        => esc_html__( 'What should happen if visitor (no login) is not permitted to access content.' ),
						'type'        => EPKB_Input_Filter::SELECTION,
						'options'     => array(
								'redirect_to_login_page'    => esc_html__( 'Redirect to login page', 'echo-knowledge-base' ),
								'return_403_http_code'      => esc_html__( 'Return 403 (Not Authorized)', 'echo-knowledge-base' ),
								'display_404_page'          => esc_html__( 'Display 404 page (Not Found)', 'echo-knowledge-base' ),
								'show_access_denied_msg'    => esc_html__( 'Show Access Denied Message', 'echo-knowledge-base' ),
								'redirect_to_custom_page'   => esc_html__( 'Redirect to custom page', 'echo-knowledge-base' )
						),
						'default'     => 'redirect_to_login_page'
				),
				'no_access_action_user_with_login' => array(
						'label'       => esc_html__( 'No Access Action - User is Logged In', 'echo-knowledge-base' ),
						'name'        => 'no_access_action_user_with_login',
						'info'        => esc_html__( 'What should happen if user (who is logged in) is not permitted to access content.' ),
						'type'        => EPKB_Input_Filter::SELECTION,
						'options'     => array(
								'return_403_http_code'      => esc_html__( 'Return 403 (Not Authorized)', 'echo-knowledge-base' ),
								'display_404_page'          => esc_html__( 'Display 404 page (Not Found)', 'echo-knowledge-base' ),
								'show_access_denied_msg'    => esc_html__( 'Show Access Denied Message', 'echo-knowledge-base' ),
								'redirect_to_custom_page'   => esc_html__( 'Redirect to custom page', 'echo-knowledge-base' )
						),
						'default'     => 'return_403_http_code'
				),
				'no_access_redirect_to_custom_page' => array(
					'label'       => esc_html__( 'Custom Page for Redirect', 'echo-knowledge-base' ),
					'name'        => 'no_access_redirect_to_custom_page',
					'size'        => '50',
					'max'         => '200',
					'min'         => '1',
					'mandatory'   => false,
					'type'        => EPKB_Input_Filter::URL,
					'default'     => ''
				),
				'show_private_article_prefix' => array(
					'label'       => esc_html__( "Show 'Private' article prefix", 'echo-knowledge-base' ),
					'name'        => 'show_private_article_prefix',
					'info'        => esc_html__( "WordPress adds 'Private' prefix to private articles. Select whether to show the prefix." ),
					'type'        => EPKB_Input_Filter::CHECKBOX,
					'default'     => 'on'
				),
		);

		self::$cached_specs = $config_specification;

		return self::$cached_specs;
	}

	/**
	 * Get KB default configuration
	 *
	 * @param int $kb_id is the ID of knowledge base to get default config for
	 * @return array contains default values for KB configuration
	 */
	public static function get_default_kb_config( $kb_id ) {
		$config_specs = self::get_fields_specification( $kb_id );

		$default_configuration = array();
		foreach( $config_specs as $key => $spec ) {
			$default = isset($spec['default']) ? $spec['default'] : '';
			$default_configuration += array( $key => $default );
		}

		return $default_configuration;
	}

	/**
	 * Get names of all configuration items for KB configuration
	 * @return array
	 */
	public static function get_specs_item_names() {
		return array_keys( self::get_fields_specification( EPKB_KB_Config_DB::DEFAULT_KB_ID ) );
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
