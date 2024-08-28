<?php

/**
 * Manage KB configuration FOR THIS ADD_ON in the database.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGP_KB_Config_DB {

	// Prefix for WP option name that stores KB configuration
	const KB_CONFIG_PREFIX =  'amgp_config_';
	const DEFAULT_KB_ID = 1;

	private $cached_settings = array();
	private $is_cached_all_kbs = false;

	public function __construct() {
		add_action( 'eckb_new_knowledge_base_added', array( $this, 'add_new_add_on_config' ) );
	}

	/**
	 * Retrieve CONFIGURATION for all KNOWLEDGE BASES
	 * If none found then return default KB configuration.
	 *
	 * @param bool $skip_check - true if caller checks that values are valid and needs quick invocation
	 *
	 * @return array settings for all registered knowledge bases OR default config if none found
	 */
	function get_kb_configs( $skip_check=false ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// retrieve settings if already cached
		if ( ! empty($this->cached_settings) && $this->is_cached_all_kbs ) {
			if ( $skip_check ) {
				return $this->cached_settings;
			}
			$kb_options_checked = array();
			$data_valid = true;
			foreach( $this->cached_settings as $config ) {
				if ( empty($config['id']) ) {
					$data_valid = false;
					break;
				}
				// use defaults for missing or empty fields
				$kb_id = $config['id'];
				$kb_options_checked[$kb_id] = wp_parse_args( $config, AMGP_KB_Config_Specs::get_default_kb_config() );
			}
			if ( $data_valid && ! empty($kb_options_checked) && ! empty($kb_options_checked[self::DEFAULT_KB_ID]) ) {
				return $kb_options_checked;
			}
		}

		// retrieve all KB options for existing knowledge bases from WP Options table
		$kb_options = $wpdb->get_results("SELECT option_value FROM $wpdb->options WHERE option_name LIKE '" . self::KB_CONFIG_PREFIX . "%'", ARRAY_A );
		if ( empty($kb_options) || ! is_array($kb_options) ) {
			AMGP_Logging::add_log("Did not retrieve any kb config. Using defaults", $kb_options);
			$kb_options = array();
		}

		// unserialize options and use defaults if necessary
		$kb_options_checked = array();
		foreach ( $kb_options as $ix => $row ) {

			if ( ! isset($ix) || empty($row) || empty($row['option_value']) ) {
				continue;
			}

			$config = maybe_unserialize( $row['option_value'] );
			if ( $config === false ) {
				AMGP_Logging::add_log("Could not unserialize configuration");
				continue;
			}

			if ( empty($config) || ! is_array($config) ) {
				AMGP_Logging::add_log("Did not find configuration");
				continue;
			}

			if ( count($config) < 1 ) {
				AMGP_Logging::add_log("Found KB configuration is incomplete", count($config));
			}

			if ( empty($config['id']) ) {
				AMGP_Logging::add_log("Found invalid configuration", $config);
				continue;
			}

			$kb_id = ( $config['id'] === self::DEFAULT_KB_ID ) ? $config['id'] : AMGP_Utilities::sanitize_get_id( $config['id'] );
			if ( is_wp_error($kb_id) ) {
				continue;
			}

			// use defaults for missing or empty fields
			$kb_options_checked[$kb_id] = wp_parse_args( $config, AMGP_KB_Config_Specs::get_default_kb_config() );
			$kb_options_checked[$kb_id]['id'] = $kb_id;

			// cached the settings for future use
			$this->cached_settings[$kb_id] = $kb_options_checked[$kb_id];
		}

		$this->is_cached_all_kbs = ! empty($kb_options_checked);

		// if no valid KB configuration found use default
		if ( empty($kb_options_checked) || ! isset($kb_options_checked[self::DEFAULT_KB_ID]) ) {
			AMGP_Logging::add_log("Need at least default configuration.");
			$kb_options_checked[self::DEFAULT_KB_ID] = AMGP_KB_Config_Specs::get_default_kb_config();
		}

		return $kb_options_checked;
	}

	/**
	 * Get IDs for all existing knowledge bases. If missing, return default KB ID
	 *
	 * @return array containing all existing KB IDs
	 */
	public function get_kb_ids() {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// retrieve all KB option names for existing knowledge bases from WP Options table
		$kb_option_names = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '" . AMGP_KB_Core::AMGP_KB_CONFIG_PREFIX . "%'", ARRAY_A );
		if ( empty($kb_option_names) || ! is_array($kb_option_names) ) {
			AMGP_Logging::add_log("Did not retrieve any kb config. Using defaults", $kb_option_names);
			$kb_option_names = array();
		}

		$kb_ids = array();
		foreach ( $kb_option_names as $kb_option_name ) {

			if ( empty($kb_option_name) ) {
				continue;
			}

			$kb_id = str_replace( AMGP_KB_Core::AMGP_KB_CONFIG_PREFIX, '', $kb_option_name['option_name'] );
			$kb_id = AMGP_Utilities::sanitize_int( $kb_id, self::DEFAULT_KB_ID );
			$kb_ids[$kb_id] = $kb_id;
		}

		// at least include default KB ID
		if ( empty($kb_ids) || ! isset($kb_ids[self::DEFAULT_KB_ID]) ) {
			$kb_ids[self::DEFAULT_KB_ID] = self::DEFAULT_KB_ID;
		}

		return $kb_ids;
	}

	/**
	 * GET KB configuration from the WP Options table. If not found then return ERROR.
	 * Logs all errors so the caller doesn't need to.
	 *
	 * @param String $kb_id to get configuration for
	 * @param bool $default_on_error - do not return default configuration on error
	 *
	 * @return array|WP_Error return current KB configuration
	 */
	public function get_kb_config( $kb_id, $default_on_error=true ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// always return error if kb_id invalid. we don't want to override stored KB config if there is
		// internal error that causes this
		$kb_id = ( $kb_id === self::DEFAULT_KB_ID ) ? $kb_id : AMGP_Utilities::sanitize_get_id( $kb_id );
		if ( is_wp_error($kb_id) ) {
			return $kb_id;
		}
		/** @var int $kb_id */

		// retrieve settings if already cached
		if ( ! empty($this->cached_settings[$kb_id]) ) {
			$config = wp_parse_args( $this->cached_settings[$kb_id], AMGP_KB_Config_Specs::get_default_kb_config() );
			$config['id'] = $kb_id;
			return $config;
		}

		// retrieve specific KB configuration
		$config = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name = '" . self::KB_CONFIG_PREFIX . $kb_id . "'" );
		if ( ! empty($config) ) {
			$config = maybe_unserialize( $config );
		}

		// if KB configuration is missing
		if ( empty($config) || ! is_array($config) ) {
			AMGP_Logging::add_log("Did not find AMGP configuration (DB23).", $kb_id);
			return ( $default_on_error ? AMGP_KB_Config_Specs::get_default_kb_config() : new WP_Error('get_kb_config', 'Did not find KB Config') );
		}

		if ( count($config) < 1 ) {
			AMGP_Logging::add_log("Found AMGP configuration is incomplete", count($config));
		}

		// use defaults for missing or empty fields
		$config = wp_parse_args( $config, AMGP_KB_Config_Specs::get_default_kb_config() );
		$config['id'] = $kb_id;

		// cached the settings for future use
		$this->cached_settings[$kb_id] = $config;

		return $config;
	}

	/**
	 * GET KB configuration from the WP Options table. If not found then return default.
	 *
	 * @param String $kb_id to get configuration for
	 * @return array return current KB configuration
	 */
	public function get_kb_config_or_default( $kb_id ) {

		$kb_config = $this->get_kb_config( $kb_id );
		if ( is_wp_error( $kb_config ) ) {
			return AMGP_KB_Config_Specs::get_default_kb_config();
		}

		return $kb_config;
	}

	/**
	 * Return specific value from the KB configuration. Values are automatically trimmed.
	 *
	 * @param string $kb_id
	 * @param $setting_name
	 * @param string $default
	 * @return array|string with value or $default value if this settings not found
	 */
	public function get_value( $kb_id, $setting_name, $default = '' ) {

		if ( empty($setting_name) ) {
			return $default;
		}

		$kb_config = empty($kb_id) ? AMGP_KB_Config_Specs::get_default_kb_config() : $this->get_kb_config( $kb_id );
		if ( is_wp_error( $kb_config ) ) {
			AMGP_Logging::add_log( "Could not retrieve KB configuration (15). Settings name: ", $setting_name, $kb_config );
			return $default;
		}

		if ( isset($kb_config[$setting_name]) ) {
			return $kb_config[$setting_name];
		}

		$default_settings = AMGP_KB_Config_Specs::get_default_kb_config();

		return  isset($default_settings[$setting_name]) ? $default_settings[$setting_name] : $default;
	}

	/**
	 * Set specific value in KB Configuration
	 *
	 * @param $kb_id
	 * @param $key
	 * @param $value
	 * @return array|WP_Error
	 */
	public function set_value( $kb_id, $key, $value ) {

		$kb_config = $this->get_kb_config( $kb_id );
		if ( is_wp_error($kb_config) ) {
			return $kb_config;
		}

		$kb_config[$key] = $value;

		return $this->update_kb_configuration( $kb_id, $kb_config );
    }

	/**
	 * Update KB Configuration. Use default if config missing.
	 *
	 * @param int $kb_id is identification of the KB to update
	 * @param array $config contains KB configuration or empty if adding default configuration
	 * @param bool $upsert - if false then fail if record already exists
	 *
	 * @return array|WP_Error configuration that was updated
	 */
	public function update_kb_configuration( $kb_id, array $config, $upsert=true ) {

		$kb_id = ( $kb_id === self::DEFAULT_KB_ID ) ? $kb_id : AMGP_Utilities::sanitize_get_id( $kb_id );
		if ( is_wp_error($kb_id) ) {
			return $kb_id;
		}
		/** @var int $kb_id */

		$orig_kb_config = $this->get_kb_config( $kb_id );
		if ( is_wp_error($orig_kb_config) ) {
			return $orig_kb_config;
		}

		if ( $orig_kb_config['kb_managers'] != $config['kb_managers'] && ! AMGP_Access_Utilities::is_admin() ) {
			return new WP_Error('Access not allowed');
		}

		$fields_specification = AMGP_KB_Config_Specs::get_fields_specification();
		$input_filter = new AMGP_Input_Filter();
		$sanitized_config = $input_filter->validate_and_sanitize_specs( $config, $fields_specification );
		if ( is_wp_error($sanitized_config) ) {
			AMGP_Logging::add_log( 'Could not update AMGP configuration', $kb_id, $sanitized_config );
			return $sanitized_config;
		}

		$sanitized_config = wp_parse_args( $sanitized_config, AMGP_KB_Config_Specs::get_default_kb_config() );

		return $this->save_kb_config( $sanitized_config, $kb_id, $upsert );
	}

	/**
	 * Insert or update KB configuration
	 *
	 * @param array $config
	 * @param $kb_id - assuming it is a valid ID (sanitized)
	 * @param bool $upsert - if false then fail if record already exists
	 *
	 * @return array|WP_Error if configuration is missing or cannot be serialized
	 */
	private function save_kb_config( array $config, $kb_id, $upsert=true ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( empty($config) || ! is_array($config) ) {
			return new WP_Error( 'save_kb_config', 'Configuration is empty' );
		}
		$config['id'] = $kb_id;  // ensure it is the same id

		// KB configuration always starts with amgp_config_[ID]
		$option_name = self::KB_CONFIG_PREFIX . $kb_id;

		// add or update the option
		$serialized_config = maybe_serialize($config);
		if ( empty($serialized_config) ) {
			return new WP_Error( 'save_kb_config', 'Failed to serialize kb config for kb_id ' . $kb_id );
		}

		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)" .
		                                        ( $upsert ? "ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)" : '' ),
												$option_name, $serialized_config, 'no' ) );
		if ( $result === false ) {
			AMGP_Logging::add_log( 'Failed to update kb config for kb_id', $kb_id );
			return new WP_Error( 'save_kb_config', 'Failed to update kb config for kb_id ' . $kb_id );
		}

		// cached the settings for future use
		$this->cached_settings[$kb_id] = $config;

		return $config;
	}

	/**
	 * Multisite installation has to reset caching between installs.
	 */
	public function reset_cache() {
		$this->cached_settings = array();
		$this->is_cached_all_kbs = false;
	}

	/**
	 * When a new KB is added through Multiple KBs add-on, create for it this add-on configuration
	 *
	 * @param $kb_id
	 */
	public function add_new_add_on_config( $kb_id ) {
		AMGP_Logging::disable_logging();
		$this->update_kb_configuration( $kb_id, AMGP_KB_Config_Specs::get_default_kb_config() );
		AMGP_Logging::enable_logging();
	}
}