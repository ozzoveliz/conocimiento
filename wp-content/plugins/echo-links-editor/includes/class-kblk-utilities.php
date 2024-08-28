<?php

/**
 * Various utility functions
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class KBLK_Utilities {

	static $wp_options_cache = array();
	static $postmeta = array();

	const KBLK_ADMIN_CAPABILITY = 'manage_options';

	 /**
	 * Get Post status translation.
	 * @param $post_status
	 * @return mixed
	 */
	public static function get_post_status_text( $post_status ) {

		$post_statuses = array( 'draft' => __( 'Draft' ), 'pending' => __( 'Pending' ),
								'publish' => __( 'Published' ), 'future' => __( 'Scheduled' ),
								'private' => __( 'Private' ),
								'trash'   => __( 'Trash' ));

		if ( empty($post_status) || ! in_array($post_status, array_keys($post_statuses)) ) {
			return $post_status;
		}

		return $post_statuses[$post_status];
	}

	public static function get_eckb_kb_id( $default=1 ) {
		global $eckb_kb_id;
		return empty( $eckb_kb_id ) ? $default : $eckb_kb_id;
	}


	/**************************************************************************************************************************
	 *
	 *                     STRING OPERATIONS
	 *
	 **************************************************************************************************************************/

	/**
	 * PHP substr() function returns FALSE if the input string is empty. This method
	 * returns empty string if input is empty or if error occurs.
	 *
	 * @param $string
	 * @param $start
	 * @param null $length
	 *
	 * @return string
	 */
	public static function substr( $string, $start, $length=null ) {
		$result = substr( $string, $start, $length );
		return empty( $result ) ? '' : $result;
	}

	/**************************************************************************************************************************
	 *
	 *                     NUMBER OPERATIONS
	 *
	 **************************************************************************************************************************/

	/**
	 * Determine if value is positive integer ( > 0 )
	 * @param int $number is checked
	 * @return bool
	 */
	public static function is_positive_int( $number ) {

		// no invalid format
		if ( empty( $number ) || ! is_numeric( $number ) ) {
			return false;
		}

		// no non-digit characters
		$numbers_only = preg_replace('/\D/', "", $number );
		if ( empty( $numbers_only ) || $numbers_only != $number ) {
			return false;
		}

		// only positive
		return $numbers_only > 0;
	}

	/**
	 * Determine if value is positive integer
	 * @param int $number is check
	 * @return bool
	 */
	public static function is_positive_or_zero_int( $number ) {

		if ( ! isset($number) || ! is_numeric($number) ) {
			return false;
		}

		if ( ( (int) $number) != ( (float) $number )) {
			return false;
		}

		$number = (int) $number;

		return is_int($number);
	}


	/**************************************************************************************************************************
	 *
	 *                     DATE OPERATIONS
	 *
	 **************************************************************************************************************************/

	/**
	 * Retrieve specific format from given date-time string e.g. '10-16-2003 10:20:01' becomes '10-16-2003'
	 *
	 * @param $datetime_str
	 * @param string $format e.g. 'Y-m-d H:i:s'  or  'M j, Y'
	 *
	 * @return string formatted date or the original string
	 */
	public static function get_formatted_datetime_string( $datetime_str, $format='M j, Y' ) {

		if ( empty($datetime_str) || empty($format) ) {
			return $datetime_str;
		}

		$time = strtotime($datetime_str);
		if ( empty($time) ) {
			return $datetime_str;
		}

		$date_time = date_i18n($format, $time);
		if ( $date_time == $format ) {
			$date_time = $datetime_str;
		}

		return empty($date_time) ? $datetime_str : $date_time;
	}

	/**
	 * Get nof hours passed between two dates.
	 *
	 * @param string $date1
	 * @param string $date2 OR if empty then use current date
	 *
	 * @return int - number of hours between dates [0-x] or null if error
	 */
	public static function get_hours_since( $date1, $date2='' ) {

		try {
			$date1_dt = new DateTime( $date1 );
			$date2_dt = new DateTime( $date2 );
		} catch(Exception $ex) {
			return null;
		}

		if ( empty($date1_dt) || empty($date2_dt) ) {
			return null;
		}

		$hours = date_diff($date1_dt, $date2_dt)->h;

		return $hours === false ? null : $hours;
	}

	/**
	 * Get nof days passed between two dates.
	 *
	 * @param string $date1
	 * @param string $date2 OR if empty then use current date
	 *
	 * @return int - number of days between dates [0-x] or null if error
	 */
	public static function get_days_since( $date1, $date2='' ) {

		try {
			$date1_dt = new DateTime( $date1 );
			$date2_dt = new DateTime( $date2 );
		} catch(Exception $ex) {
			return null;
		}

		if ( empty($date1_dt) || empty($date2_dt) ) {
			return null;
		}

		$days = (int)date_diff($date1_dt, $date2_dt)->format("%r%a");

		return $days === false ? null : $days;
	}

	/**
	 * How long ago pass date occurred.
	 *
	 * @param string $date1
	 *
	 * @return string x year|month|week|day|hour|minute|second(s) or '[unknown]' on error
	 */
	public static function time_since_today( $date1 ) {
		return self::how_long_ago( $date1 );
	}

	/**
	 * How long ago since now.
	 *
	 * @param string $date1
	 * @param string $date2 or if empty use current time
	 *
	 * @return string x year|month|week|day|hour|minute|second(s) or '[unknown]' on error
	 */
	public static function how_long_ago( $date1, $date2='' ) {

		$time1 = strtotime($date1);
		$time2 = empty($date2) ? time() : strtotime($date2);
		if ( empty($time1) || empty($time2) ) {
			return '[???]';
		}

		$time = abs($time2 - $time1);
		$time = ( $time < 1 )? 1 : $time;
		$tokens = array (
			31536000 => __( 'year' ),
			2592000 => __( 'month' ),
			604800 => __( 'week' ),
			86400 => __( 'day' ),
			3600 => __( 'hour' ),
			60 => __( 'min' ),
			1 => __( 'sec' )
		);

		$output = '';
		foreach ($tokens as $unit => $text) {
			if ($time >= $unit) {
				$numberOfUnits = floor($time / $unit);
				$output =  $numberOfUnits . ' ' . $text . ( $numberOfUnits >1 ? 's' : '');
				break;
			}
		}

		return $output;
	}


	/**************************************************************************************************************************
	 *
	 *                     AJAX NOTICES
	 *
	 *************************************************************************************************************************/

	/**
	 * wp_die with an error message if nonce invalid or user does not have correct permission
	 *
	 * @param string $wpnonce_name
	 * @param string $context - leave empty if only admin can access this
	 */
	public static function ajax_verify_nonce_and_admin_permission_or_error_die( $wpnonce_name='_wpnonce_kblk_ajax_action', $context='' ) {

		// check wpnonce
		$wp_nonce = self::post( $wpnonce_name );
		if ( empty( $wp_nonce ) || ! wp_verify_nonce( $wp_nonce, $wpnonce_name ) ) {
			self::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' )  . ' (E01)');
		}

		// without context only admins can make changes
		if ( empty( $context ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				self::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) . ' (E02)' );
			}
			return;
		}

		// ensure user has correct permission
		if ( ! KBLK_KB_Core::is_user_access_to_context_allowed( $context ) ) {
			self::ajax_show_error_die(__( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) . ' (E02)');
		}
	}

	/**
	 * wp_die with an error message if nonce invalid or accessed directly
	 */
	public static function ajax_verify_nonce_and_prevent_direct_access_or_error_die() {

		// check wpnonce
		$wpnonce_value = self::post( '_wpnonce' );
		if ( empty( $wpnonce_value ) || ! wp_verify_nonce( $wpnonce_value, '_wpnonce_epkb_ajax_action' ) ) {
			self::ajax_show_error_die( __( 'Please refresh your page.', 'echo-knowledge-base' )  . ' (E03)');
		}

		// prevent direct access ?
		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			self::ajax_show_error_die( __( 'Error occurred. Please try again later.', 'echo-knowledge-base' ) . ' (E04)' );
		}
	}

	/**
	 * AJAX: Used on response back to JS. will call wp_die()
	 *
	 * @param string $message
	 * @param string $title
	 * @param string $type
	 */
	public static function ajax_show_info_die( $message='', $title='', $type='success' ) {
		if ( defined('DOING_AJAX') ) {
			wp_die( wp_json_encode( array( 'message' => self::get_bottom_notice_message_box( $message, $title, $type ) ) ) );
		}
	}

	/**
	 * AJAX: Used on response back to JS. will call wp_die()
	 *
	 * @param $message
	 * @param string $title
	 * @param string $error_code
	 */
	public static function ajax_show_error_die( $message, $title = '', $error_code = '' ) {
		if ( defined('DOING_AJAX') ) {
			wp_die( wp_json_encode( array( 'error' => true, 'message' => self::get_bottom_notice_message_box( $message, $title, 'error' ), 'error_code' => $error_code ) ) );
		}
	}

	/**
	 * Show info or error message to the user
	 *
	 * @param $message
	 * @param string $title
	 * @param string $type
	 * @param string $extra_html
	 * @return string
	 */
	public static function get_bottom_notice_message_box( $message, $title='', $type='success', $extra_html='' ) {
		/* array $EZSQL_ERROR */
		global $EZSQL_ERROR;

		if ( ! empty($EZSQL_ERROR) && is_array($EZSQL_ERROR) ) {
			foreach ( $EZSQL_ERROR as $error ){
				$amgr_tables = array("amgr_access_kb_categories", "amgr_access_read_articles", "amgr_access_read_categories", "amgr_kb_group_users", "amgr_kb_groups", "amgr_kb_public_groups");
				foreach ( $amgr_tables as $table_name ) {
					if ( !empty($error['error_str']) && strpos($error['error_str'], $table_name) !== false ) {
						//LOG Only Access Manager Error
						KBLK_Logging::add_log( 'Database error', $EZSQL_ERROR );
						$message .= __( '. Database Error.', 'echo-knowledge-base' );
					}
				}

			}
		}

		$message = empty( $message ) ? '' : $message;

		return
			"<div class='eckb-bottom-notice-message'>
				<div class='contents'>
					<span class='" . esc_attr( $type ) . "'>" .
						( empty( $title ) ? '' : '<h4>' . esc_html( $title ) . '</h4>' ) . "
						<p> " . wp_kses_post( $message ) . "</p>
					</span>
				</div>
				" . wp_kses( $extra_html, self::get_allowed_html_tags( ['all'] ) ) . "
				<div class='epkb-close-notice epkbfa epkbfa-window-close'></div>
			</div>";
	}

	public static function user_not_logged_in() {
		if ( defined('DOING_AJAX') ) {
			self::ajax_show_error_die( '<p>' . __( 'You are not logged in. Refresh your page and log in.', 'echo-knowledge-base' ) . '</p>', __( 'Cannot save your changes', 'echo-knowledge-base' ) );
		}
	}

	/**
	 * Common way to show support link
	 * @return string
	 */
	public static function contact_us_for_support() {

		// show only for admins and editors
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include( ABSPATH . "wp-includes/pluggable.php" );
		}

		$user = wp_get_current_user();
		if ( empty( $user ) || empty( $user->roles ) ) {
			return '';
		}

		if ( ! in_array( 'administrator', $user->roles ) ) {
			return '';
		}

		return ' ' . esc_html__( 'Please contact us for help', 'echo-knowledge-base' ) . ' ' .
		       '<a href="https://www.echoknowledgebase.com/technical-support/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'here', 'echo-knowledge-base' ) . '</a>.';
	}

	/**
	 * Common way to show feedback link
	 * @return string
	 */
	public static function contact_us_for_feedback() {
		return ' ' .  esc_html__( "We'd love to hear your feedback!", 'echo-knowledge-base' ) . ' ' .
		       '<a href="https://www.echoknowledgebase.com/feature-request/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'click here', 'echo-knowledge-base' ) . '</a>';
	}

	/**
	 * Get string for generic error, optional specific error number, and Contact us link
	 *
	 * For example: KBLK_Utilities::ajax_show_error_die( KBLK_Utilities::report_generic_error( 411 ) );
	 *
	 * @param int $error_number
	 * @param string $message
	 * @param bool $contact_us
	 * @return string
	 */
	public static function report_generic_error( $error_number=0, $message='', $contact_us=true ) {

		if ( empty( $message ) ) {
			$message = esc_html__( 'Error occurred', 'echo-knowledge-base' );
		} else if ( is_wp_error( $message ) ) {
			/** @var WP_Error $message */
			$message = $message->get_error_message();
		} else if ( ! is_string( $message ) ) {
			$message = esc_html__( 'Error occurred', 'echo-knowledge-base' );
		}

		return $message .
				( empty( $error_number ) ? '' : ' (' . $error_number . '). ' ) .
				( empty( $contact_us ) ? '' : self::contact_us_for_support() );
	}


	/**************************************************************************************************************************
	 *
	 *                     SECURITY
	 *
	 *************************************************************************************************************************/

	/**
	 * Return digits only.
	 *
	 * @param $number
	 * @param int $default
	 * @return int|$default
	 */
	public static function sanitize_int( $number, $default=0 ) {

		if ( $number === null || ! is_numeric($number) ) {
			return $default;
		}

		// intval returns 0 on error so handle 0 here first
		if ( $number == 0 ) {
			return 0;
		}

		$number = intval($number);

		return empty($number) ? $default : (int) $number;
	}

	/**
	 * Return text, space, "-" and "_" only.
	 *
	 * @param $text
	 * @param String $default
	 * @return String|$default
	 */
	public static function sanitize_english_text( $text, $default='' ) {

		if ( empty($text) || ! is_string($text) ) {
			return $default;
		}

		$text = preg_replace('/[^A-Za-z0-9 \-_]/', '', $text);

		return empty($text) ? $default : $text;
	}

	/**
	 * Retrieve ID or return error. Used for IDs.
	 *
	 * @param mixed $id is either $id number or array with 'id' index
	 *
	 * @return int|WP_Error
	 */
	public static function sanitize_get_id( $id ) {

		if ( empty( $id) || is_wp_error($id) ) {
			KBLK_Logging::add_log( 'Error occurred (01)' );
			return new WP_Error('E001', 'invalid ID' );
		}

		if ( is_array( $id) ) {
			if ( ! isset( $id['id']) ) {
				KBLK_Logging::add_log( 'Error occurred (02)' );
				return new WP_Error('E002', 'invalid ID' );
			}

			$id_value = $id['id'];
			if ( ! self::is_positive_int( $id_value ) ) {
				KBLK_Logging::add_log( 'Error occurred (03)', $id_value );
				return new WP_Error('E003', 'invalid ID' );
			}

			return (int) $id_value;
		}

		if ( ! self::is_positive_int( $id ) ) {
			KBLK_Logging::add_log( 'Error occurred (04)', $id );
			return new WP_Error('E004', 'invalid ID' );
		}

		return (int) $id;
	}

    /**
     * Sanitize array full of ints.
     *
     * @param $array_values
     * @param string $default
     * @return array|string
     */
	public static function sanitize_int_array( $array_values, $default='' ) {
	    if ( ! is_array($array_values) ) {
	        return $default;
        }

        $sanitized_array = array();
        foreach( $array_values as $value ) {
	        $sanitized_array[] = self::sanitize_int( $value );
        }

        return $sanitized_array;
    }

	/**
	 * Decode and sanitize form fields.
	 *
	 * @param $form
	 * @param $all_fields_specs
	 * @return array
	 */
	public static function retrieve_and_sanitize_form( $form, $all_fields_specs ) {
		if ( empty( $form ) ) {
			return array();
		}

		// first urldecode()
		if ( is_string( $form ) ) {
			parse_str( $form, $submitted_fields );
		} else {
			$submitted_fields = $form;
		}

		// now sanitize each field
		$sanitized_fields = array();
		foreach( $submitted_fields as $submitted_key => $submitted_value ) {

			$field_type = empty($all_fields_specs[$submitted_key]['type']) ? '' : $all_fields_specs[$submitted_key]['type'];

			if ( $field_type == KBLK_Input_Filter::WP_EDITOR ) {
				$sanitized_fields[$submitted_key] = wp_kses( $submitted_value, self::get_extended_html_tags() );

			} elseif ( $field_type == KBLK_Input_Filter::TYPOGRAPHY ) {
				$sanitized_fields[$submitted_key] = KBLK_Input_Filter::sanitize_typography( $submitted_value );

			} elseif ( $field_type == KBLK_Input_Filter::TEXT && ! empty($all_fields_specs[$submitted_key]['allowed_tags']) ) {
				// text input with allowed tags 
				$sanitized_fields[$submitted_key] = wp_kses( $submitted_value, $all_fields_specs[$submitted_key]['allowed_tags'] );

			} else {
				$sanitized_fields[$submitted_key] = sanitize_text_field( $submitted_value );
			}

		}

		return $sanitized_fields;
	}

	/**
	 * Return ints and comma only.
	 *
	 * @param $text
	 * @param String $default
	 * @return String|$default
	 */
	public static function sanitize_comma_separated_ints( $text, $default='' ) {

		if ( empty( $text ) || ! is_string( $text ) ) {
			return $default;
		}

		$text = preg_replace( '/[^0-9 \,_]/', '', $text );

		return empty( $text ) ? $default : $text;
	}

	/**
	 * Retrieve value from POST or GET
	 *
	 * @param $key
	 * @param string $default
	 * @param string $value_type How to treat and sanitize value. Values: text, url
	 * @param int $max_length
	 * @return array|string - empty if not found
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
	public static function post( $key, $default = '', $value_type = 'text', $max_length = 0 ) {

		if ( isset( $_POST[$key] ) ) {
			return self::post_sanitize( $key, $default, $value_type, $max_length );
		}

		if ( isset( $_GET[$key] ) ) {
			return self::get_sanitize( $key, $default, $value_type, $max_length );
		}

		return $default;
	}

	/**
	 * Retrieve value from POST or GET
	 *
	 * @param $key
	 * @param string $default
	 * @param string $value_type How to treat and sanitize value. Values: text, url
	 * @param int $max_length
	 * @return array|string - empty if not found
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified elsewhere.
	private static function post_sanitize( $key, $default = '', $value_type = 'text', $max_length = 0 ) {

		if ( $_POST[$key] === null || is_object( $_POST[$key] )  ) {
			return $default;
		}

		// config is sanitizing with its own specs separately
		if ( $value_type == 'db-config' ) {
			return $_POST[$key];
		}

		if ( is_array( $_POST[$key] ) ) {
			return self::sanitize_array( $_POST[$key] );
		}

		// jquery serialized form. sanitize values in array
		if ( $value_type == 'form' ) {
			$result = is_array( $default ) ? $default : [];
			wp_parse_str( stripslashes( $_POST[$key] ), $decoded_form );
			foreach ( $decoded_form as $field => $val ) {
				$result[$field] = wp_kses_post( $val );
			}
			return $result;
		}

		if ( $value_type == 'text-area' ) {
			$value = sanitize_textarea_field( stripslashes( $_POST[$key] ) );  // do not strip line breaks
		} else if ( $value_type == 'email' ) {
			$value = sanitize_email( $_POST[$key] );  // strips out all characters that are not allowable in an email
		} else if ( $value_type == 'url' ) {
			$value = sanitize_text_field( urldecode( $_POST[$key] ) );
		} else if ( $value_type == 'wp_editor' ) {
			$value = wp_kses( $_POST[$key], self::get_extended_html_tags() );
		} else {
			$value = sanitize_text_field( stripslashes( $_POST[$key] ) );
		}

		// optionally limit the value by length
		if ( ! empty( $max_length ) ) {
			$value = self::substr( $value, 0, $max_length );
		}

		return $value;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	public static function request_key( $key, $default = '' ) {

		if ( isset( $_REQUEST[$key] ) && is_string( $_REQUEST[$key] ) ) {
			return sanitize_key( $_REQUEST[$key] );
		}

		return $default;
	}

	/**
	 * Retrieve value from POST or GET
	 *
	 * @param $key
	 * @param string $default
	 * @param string $value_type How to treat and sanitize value. Values: text, url
	 * @param int $max_length
	 * @return array|string - empty if not found
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	public static function get( $key, $default = '', $value_type = 'text', $max_length = 0 ) {

		if ( isset( $_GET[$key] ) ) {
			return self::get_sanitize( $key, $default, $value_type, $max_length );
		}

		if ( isset( $_POST[$key] ) ) {
			return self::post_sanitize( $key, $default, $value_type, $max_length );
		}

		return $default;
	}

	/**
	 * Retrieve value from POST or GET
	 *
	 * @param $key
	 * @param string $default
	 * @param string $value_type How to treat and sanitize value. Values: text, url
	 * @param int $max_length
	 * @return array|string - empty if not found
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified elsewhere.
	private static function get_sanitize( $key, $default = '', $value_type = 'text', $max_length = 0 ) {

		if ( $_GET[$key] === null || is_object( $_GET[$key] )  ) {
			return $default;
		}

		// config is sanitizing with its own specs separately
		if ( $value_type == 'db-config' ) {
			return $_GET[$key];
		}

		if ( is_array( $_GET[$key] ) ) {
			return self::sanitize_array( $_GET[$key] );
		}

		// jquery serialized form. sanitize values in array
		if ( $value_type == 'form' ) {
			$result = is_array( $default ) ? $default : [];
			wp_parse_str( stripslashes( $_GET[$key] ), $decoded_form );
			foreach ( $decoded_form as $field => $val ) {
				$result[$field] = wp_kses_post( $val );
			}
			return $result;
		}

		if ( $value_type == 'text-area' ) {
			$value = sanitize_textarea_field( stripslashes( $_GET[$key] ) );  // do not strip line breaks
		} else if ( $value_type == 'email' ) {
			$value = sanitize_email( $_GET[$key] );  // strips out all characters that are not allowable in an email
		} else if ( $value_type == 'url' ) {
			$value = sanitize_text_field( urldecode( $_GET[$key] ) );
		} else if ( $value_type == 'wp_editor' ) {
			$value = wp_kses( $_GET[$key], self::get_extended_html_tags() );
		} else {
			$value = sanitize_text_field( stripslashes( $_GET[$key] ) );
		}

		// optionally limit value by length
		if ( ! empty( $max_length ) ) {
			$value = self::substr( $value, 0, $max_length );
		}

		return $value;
	}

	public static function sanitize_array( $value ) {
		$result = [];
		foreach ( $value as $key => $val ) {

			// can be 2-dimension array
			if ( is_array( $val ) ) {

				if ( empty( $result[ $key ] ) ) {
					$result[ $key ] = [];
				}

				foreach ( $val as $key_2 => $val_2 ) {
					$result[ $key ][ $key_2 ] = sanitize_text_field( stripslashes( $val_2 ) );
				}

			} else {
				$result[ $key ] = sanitize_text_field( stripslashes( $val ) );
			}
		}

		return $result;
	}


	/**************************************************************************************************************************
	 *
	 *                     GET/SAVE/UPDATE AN OPTION
	 *
	 *************************************************************************************************************************/

	/**
	 * Get KB-SPECIFIC option. Function adds KB ID suffix. Prefix represent core or ADD-ON prefix.
	 *
	 * @param $kb_id - assuming it is a valid ID
	 * @param $option_name - without kb suffix
	 * @param $default - use if KB option not found
	 * @param bool $is_array - ensure returned value is an array, otherwise return default
	 * @return string|array or default
	 */
	public static function get_kb_option( $kb_id, $option_name, $default, $is_array=false ) {
		$full_option_name = $option_name . '_' . $kb_id;
		return self::get_wp_option( $full_option_name, $default, $is_array );
	}

	/**
	 * Use to get:
	 *  a) PLUGIN-WIDE option not specific to any KB with e p k b prefix.
	 *  b) ADD-ON-SPECIFIC option with ADD-ON prefix.
	 *  b) KB-SPECIFIC configuration with e p k b prefix and KB ID suffix.
	 *
	 * @param $option_name
	 * @param $default
	 * @param bool|false $is_array
	 * @param bool $return_error
	 *
	 * @return array|string|WP_Error or default or error if $return_error is true
	 */
	public static function get_wp_option( $option_name, $default, $is_array=false, $return_error=false ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( isset(self::$wp_options_cache[$option_name]) ) {
			return self::$wp_options_cache[$option_name];
		}

		// retrieve specific WP option
		$option = $wpdb->get_var( $wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s", $option_name ) );
		if ( $option !== null ) {
			$option = maybe_unserialize( $option );
		}

		if ( $return_error && $option === null && ! empty($wpdb->last_error) ) {
			$wpdb_last_error = $wpdb->last_error;   // add_log changes last_error so store it first
			KBLK_Logging::add_log( "DB failure: " . $wpdb_last_error, 'Option Name: ' . $option_name );
			return new WP_Error(__( 'Error occurred', 'echo-knowledge-base' ), $wpdb_last_error);
		}

		// if WP option is missing then return defaults
		if ( $option === null || ( $is_array && ! is_array( $option ) ) ) {
			return $default;
		}

		self::$wp_options_cache[$option_name] = $option;

		return $option;
	}

	/**
	 * Save KB-SPECIFIC option. Function adds KB ID suffix. Prefix represent core or ADD-ON prefix.
	 *
	 * @param $kb_id - assuming it is a valid ID
	 * @param $option_name - without kb suffix
	 * @param $option_value
	 *
	 * @return mixed|WP_Error if option cannot be serialized or db insert failed
	 */
	public static function save_kb_option( $kb_id, $option_name, $option_value ) {
		$full_option_name = $option_name . '_' . $kb_id;
		return self::save_wp_option( $full_option_name, $option_value );
	}

	/**
	 * Save KB-SPECIFIC option. Function adds KB ID suffix. Prefix represent core or ADD-ON prefix.
	 * This version uses default WP saving for option
	 *
	 * @param $kb_id - assuming it is a valid ID
	 * @param $option_name - without kb suffix
	 * @param array $option_value
	 *
	 * @return bool True if the value was updated, false otherwise
	 */
	public static function save_kb_option2( $kb_id, $option_name, $option_value ) {
		$full_option_name = $option_name . '_' . $kb_id;
		return update_option( $full_option_name, $option_value );
	}

	/**
	 * Save WP option
	 * @param $option_name
	 * @param $option_value
	 * @param $sanitized
	 * @return mixed|WP_Error
	 */
	public static function save_wp_option( $option_name, $option_value, $sanitized=true ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// do not store null
		if ( $option_value === null ) {
			$option_value = '';
		}

		// add or update the option
		$serialized_value = $option_value;

		// check if array or object type of option can be properly serialized
		if ( is_array( $option_value ) || is_object( $option_value ) ) {
			$serialized_value = maybe_serialize($option_value);
			if ( empty( $serialized_value ) ) {
				return new WP_Error( '434', __( 'Error occurred', 'echo-knowledge-base' ) . ' ' . $option_name );
			}
		}

		$result = $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)
 												 ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
												$option_name, $serialized_value, 'no' ) );
		if ( $result === false ) {
			KBLK_Logging::add_log( 'Failed to update option', $option_name );
			return new WP_Error( '435', 'Failed to update option ' . $option_name );
		}

		self::$wp_options_cache[$option_name] = $option_value;

		return $option_value;
	}


    /**************************************************************************************************************************
     *
     *                     DATABASE
     *
     *************************************************************************************************************************/

	/**
	 * Get given Post Metadata
	 *
	 * @param $post_id
	 * @param $meta_key
	 * @param $default
	 * @param bool|false $is_array
	 * @param bool|WP_Error $return_error
	 *
	 * @return array|string or default or error if $return_error is true
	 */
	public static function get_postmeta( $post_id, $meta_key, $default, $is_array=false, $return_error=false ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( isset(self::$postmeta[$post_id][$meta_key]) ) {
			return self::$postmeta[$post_id][$meta_key];
		}

		if ( ! self::is_positive_int( $post_id) ) {
			return $return_error ? new WP_Error( __( 'Error occurred', 'echo-knowledge-base' ), self::get_variable_string( $post_id ) ) : $default;
		}

		// retrieve specific option
		$option = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key = %s", $post_id, $meta_key ) );
		if ($option !== null ) {
			$option = maybe_unserialize( $option );
		}

		if ( $return_error && $option === null && ! empty($wpdb->last_error) ) {
			$wpdb_last_error = $wpdb->last_error;   // add_log changes last_error so store it first
			KBLK_Logging::add_log( "DB failure: " . $wpdb_last_error, 'Meta Key: ' . $meta_key );
			return new WP_Error(__( 'Error occurred', 'echo-knowledge-base' ), $wpdb_last_error);
		}

		// if the option is missing then return defaults
		if ( $option === null || ( $is_array && ! is_array($option) ) ) {
			return $default;
		}

		self::$postmeta[$post_id][$meta_key] = $option;

		return $option;
	}

	/**
	 * Save or Insert Post Metadata
	 *
	 * @param $post_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param $sanitized
	 *
	 * @return mixed|WP_Error
	 */
	public static function save_postmeta( $post_id, $meta_key, $meta_value, $sanitized ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( ! self::is_positive_int( $post_id) ) {
			return new WP_Error( __( 'Error occurred', 'echo-knowledge-base' ), self::get_variable_string( $post_id ) );
		}

		if ( $sanitized !== true ) {
			return new WP_Error( '433', __( 'Error occurred', 'echo-knowledge-base' ) . ' ' . $meta_key );
		}

		// do not store null
		if ( $meta_value === null ) {
			$meta_value = '';
		}

		// add or update the option
		$serialized_value = $meta_value;
		if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
			$serialized_value = maybe_serialize($meta_value);
			if ( empty($serialized_value) ) {
				return new WP_Error( '434', __( 'Error occurred', 'echo-knowledge-base' ) . ' ' . $meta_key );
			}
		}

		// check if the meta field already exists before doing 'upsert'
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s AND post_id = %d", $meta_key, $post_id ) );
		if ( $result === null && ! empty($wpdb->last_error) ) {
			$wpdb_last_error = $wpdb->last_error;   // add_log changes last_error so store it first
			KBLK_Logging::add_log( "DB failure: " . $wpdb_last_error );
			return new WP_Error(__( 'Error occurred', 'echo-knowledge-base' ), $wpdb_last_error);
		}

		// INSERT or UPDATE the meta field
		if ( empty($result) ) {
			if ( false === $wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->postmeta (`meta_key`, `meta_value`, `post_id`) VALUES (%s, %s, %d)", $meta_key, $serialized_value, $post_id ) ) ) {
				KBLK_Logging::add_log("Failed to insert meta data. ", $meta_key);
				return new WP_Error( '33', __( 'Error occurred', 'echo-knowledge-base' ) );
			}
		} else {
			if ( false === $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_key = %s AND post_id = %d", $serialized_value, $meta_key, $post_id ) ) ) {
				KBLK_Logging::add_log("Failed to update meta data. ", $meta_key);
				return new WP_Error( '33', __( 'Error occurred', 'echo-knowledge-base' ) );
			}
		}

		if ( $result === false ) {
			KBLK_Logging::add_log( 'Failed to update meta key', $meta_key );
			return new WP_Error( '435', __( 'Error occurred', 'echo-knowledge-base' ) . ' ' . $meta_key );
		}

		self::$postmeta[$post_id][$meta_key] = $meta_value;

		return $meta_value;
	}

	/**
	 * Delete given Post Metadata
	 *
	 * @param $post_id
	 * @param $meta_key
	 *
	 * @return bool
	 */
	public static function delete_postmeta( $post_id, $meta_key ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( ! self::is_positive_int( $post_id) ) {
			return false;
		}

		// delete specific option
		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d and meta_key = %s", $post_id, $meta_key ) ) ) {
			KBLK_Logging::add_log("Could not delete post '" . self::get_variable_string($meta_key) . "'' metadata: ", $post_id);
			return false;
		}

		return true;
	}


	/**************************************************************************************************************************
	 *
	 *                     OTHER
	 *
	 *************************************************************************************************************************/

	/**
	 * Return string representation of given variable for logging purposes
	 *
	 * @param $var
	 *
	 * @return string
	 */
	public static function get_variable_string( $var ) {

		if ( ! is_array($var) ) {
			return self::get_variable_not_array( $var );
		}

		if ( empty($var) ) {
			return '['. __( 'empty', 'echo-knowledge-base' ) . ']';
		}

		$output = 'array';
		$ix = 0;
		foreach ($var as $key => $value) {

            if ( $ix++ > 10 ) {
                $output .= '[.....]';
                break;
            }

			$output .= "[" . $key . " => ";
			if ( ! is_array($value) ) {
				$output .= self::get_variable_not_array( $value ) . "]";
				continue;
			}

			$ix2 = 0;
			$output .= "[";
			$first = true;
			foreach($value as $key2 => $value2) {
                if ( $ix2++ > 10 ) {
                    $output .= '[.....]';
                    break;
                }

				if ( is_array($value2) ) {
                    $output .= print_r($value2, true);
                } else {
					$output .= ( $first ? '' : ', ' ) . $key2 . " => " . self::get_variable_not_array( $value2 );
					$first = false;
					continue;
				}
            }
			$output .= "]]";
		}

		return $output;
	}

	private static function get_variable_not_array( $var ) {

		if ( $var === null ) {
			return '<' . 'null' . '>';
		}

		if ( ! isset($var) ) {
            /** @noinspection HtmlUnknownAttribute */
            return '<' . 'not set' . '>';
		}

		if ( is_array($var) ) {
			return empty($var) ? '[]' : '[...]';
		}

		if ( is_object( $var ) ) {
			return '<' . get_class($var) . '>';
		}

		if ( is_bool( $var ) ) {
			return $var ? 'TRUE' : 'FALSE';
		}

		if ( is_string($var) || is_numeric($var) ) {
			return $var;
		}

		return '<' . 'unknown' . '>';
	}

	/**
	 * Array1 VALUES NOT IN array2
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array of values in array1 NOT in array2
	 */
	public static function diff_two_dimentional_arrays( array $array1, array $array2 ) {

		if ( empty($array1) ) {
			return array();
		}

		if ( empty($array2) ) {
			return $array1;
		}

		// flatten first array
		foreach( $array1 as $key => $value ) {
			if ( is_array($value) ) {
				$tmp_value = '';
				foreach( $value as $tmp ) {
					$tmp_value .= ( empty($tmp_value) ? '' : ',' ) . ( empty($tmp) ? '' : $tmp );
				}
				$array1[$key] = $tmp_value;
			}
		}

		// flatten second array
		foreach( $array2 as $key => $value ) {
			if ( is_array($value) ) {
				$tmp_value = '';
				foreach( $value as $tmp ) {
					$tmp_value .= ( empty($tmp_value) ? '' : ',' ) . ( empty($tmp) ? '' : $tmp );
				}
				$array2[$key] = $tmp_value;
			}
		}

		return array_diff_assoc($array1, $array2);
	}

	public static function mb_strtolower( $string ) {
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string ) : strtolower( $string );
	}

	/**
	 * Determine if current user is WP administrator WITHOUT calling current_user_can()
	 *
	 * @param null $user
	 * @return bool
	 */
	public static function is_user_admin( $user=null ) {

		// get current user
		$user = empty( $user ) ? self::get_current_user() : $user;
		if ( empty( $user ) || empty( $user->roles ) || empty( $user->allcaps ) ) {
			return false;
		}

		return in_array( 'administrator', $user->roles ) || array_key_exists( 'manage_options', $user->allcaps );
	}

	/**
	 * Get current user.
	 *
	 * @return null|WP_User
	 */
	public static function get_current_user() {

		$user = null;
		if ( function_exists( 'wp_get_current_user' ) ) {
			$user = wp_get_current_user();
		}

		// is user not logged in? user ID is 0 if not logged
		if ( empty( $user ) || ! $user instanceof WP_User || empty( $user->ID ) ) {
			$user = null;
		}

		return $user;
	}

	/**
	 * Check first installed version. Return true if $version less or equal than first installed version. Also return true if epkb_version_first was removed. Use to apply some functions only to new users
	 * for the NEW install return true
	 * @param $version
	 * @return bool
	 */
	public static function is_new_user( $version ) {
		$plugin_first_version = self::get_wp_option( 'epkb_version_first', $version );      // TODO replace with $kb_config['first_plugin_version']
		return ! version_compare( $plugin_first_version, $version, '<' );
	}


	/**
	 * Output inline CSS style based on configuration.
	 *
	 * @param string $styles A list of Configuration Setting styles
	 * @param $config
	 * @return string
	 */
	public static function get_inline_style( $styles, $config ) {

		if ( empty($styles) || ! is_string($styles) ) {
			return '';
		}

		$style_array = explode(',', $styles);
		if ( empty($style_array) ) {
			return '';
		}

		$output = 'style="';
		foreach( $style_array as $style ) {

			$key_value = array_map( 'trim', explode(':', $style) );
			if ( empty($key_value[0]) ) {
				continue;
			}

			if ( $key_value[0] != 'typography' ) {
				$output .= $key_value[0] . ': ';
			}

			// true if using config value
			if ( count($key_value) == 2 && isset($key_value[1]) ) {

				if ( $key_value[0] == 'justify-content' ) {

					if ( $key_value[1] == 'left' ) {
						$output .= 'flex-start';
					} else if ( $key_value[1] == 'right' ) {
						$output .= 'flex-end';
					} else {
						$output .= $key_value[1];
					}

				} else if( $key_value[0] == ' text-align' ) {

					if ( $key_value[1] == 'left' ) {
						$output .= 'start';
					} else if ( $key_value[1] == 'right' ) {
						$output .= 'end';
					} else {
						$output .= $key_value[1];
					}

				} else {
					$output .= $key_value[1];
				}

			} else if ( $key_value[0] == 'typography' && isset($config[$key_value[2]]) ) { // typography field

				$typography_values = array_merge( KBLK_Typography::$typography_defaults, $config[$key_value[2]] );
				if ( ! empty($typography_values['font-family']) ) {
					$output .= 'font-family:' . $typography_values['font-family'] . ';';
				}

				if ( ! empty($typography_values['font-size']) ) {
					$output .= 'font-size:' . $typography_values['font-size'] . $typography_values['font-size-units'] . ';';
				}

				if ( ! empty($typography_values['font-weight']) ) {
					$output .= 'font-weight:' . $typography_values['font-weight'] . ';';
				}

			} else if ( isset($key_value[2]) && isset($config[$key_value[2]]) ) {

				if ( $key_value[0] == 'justify-content' ) {

					if ( $config[ $key_value[2] ] == 'left' ) {
						$output .= 'flex-start';
					} else if ( $config[ $key_value[2] ] == 'right' ) {
						$output .= 'flex-end';
					} else {
						$output .= $config[ $key_value[2] ];
					}

				} else if ( $key_value[0] == 'text-align' ) {

					if ( $config[ $key_value[2] ] == 'left' ) {
						$output .= 'start';
					} else if ( $config[ $key_value[2] ] == 'right' ) {
						$output .= 'end';
					} else {
						$output .= $config[ $key_value[2] ];
					}

				} else {
					$output .= $config[ $key_value[2] ];
				}

				switch ( $key_value[0] ) {
					case 'border-radius':
					case 'border-width':
					case 'border-bottom-width':
					case 'border-top-left-radius':
					case 'border-top-right-radius':
					case 'border-bottom-left-radius':
					case 'border-bottom-right-radius':
					case 'min-height':
					case 'max-height':
					case 'height':
					case 'padding-left':
					case 'padding-right':
					case 'padding-top':
					case 'padding-bottom':
					case 'margin':
					case 'margin-top':
					case 'margin-right':
					case 'margin-bottom':
					case 'margin-left':
					case 'font-size':
						$output .= 'px';
						break;
				}
			}

			if ( $key_value[0] != 'typography' ) {
				$output .= '; ';
			}
		}

		return ' ' . trim( $output ) . '" ';
	}

	/**
	 * Output CSS classes based on configuration.
	 *
	 * @param $classes
	 * @param $config
	 * @return string
	 */
	public static function get_css_class( $classes, $config ) {

		if ( empty($classes) || ! is_string($classes) ) {
			return '';
		}

		$output = ' class="';
		foreach( array_map( 'trim', explode(',', $classes) ) as $class ) {
			$class_name = trim(str_replace(':', '', $class));
			$is_config = $class != $class_name;

			if ( $is_config && empty( $config[$class_name] ) ) {
				continue;
			}

			$output .= ( $is_config ? $config[$class_name] : $class ) . ' ';
		}
		return trim($output) . '"';
	}

	public static function get_typography_config( $typography ) {
		$typography_styles = '';

		if ( empty( $typography ) || ! is_array( $typography ) ) {
			$typography = KBLK_Typography::$typography_defaults;
		}

		if ( ! empty( $typography['font-family'] ) ) {
			$typography_styles .= 'font-family: ' . $typography['font-family'] . ' !important;';
		}

		if ( ! empty( $typography['font-size'] ) && empty( $typography['font-size-units'] ) ) {
			$typography_styles .= 'font-size: ' . $typography['font-size'] . 'px !important;';
		}

		if ( ! empty( $typography['font-size'] ) && ! empty( $typography['font-size-units'] ) ) {
			$typography_styles .= 'font-size: ' . $typography['font-size'] . $typography['font-size-units'] . ' !important;';
		}

		if ( ! empty( $typography['font-weight'] ) ) {
			$typography_styles .= 'font-weight: ' . $typography['font-weight'] . ' !important;';
		}

		return $typography_styles;
	}

	/**
	 * Check if Aaccess Manager is considered active.
	 *
	 * @param bool $is_active_check_only
	 * @return bool
	 */
	public static function is_amag_on( $is_active_check_only=true ) {
		/** @var $wpdb Wpdb */
		global $wpdb;

		if ( defined( 'AMAG_PLUGIN_NAME' ) ) {
			return true;
		}

		if ( $is_active_check_only ) {
			return false;
		}

		$table = $wpdb->prefix . 'am'.'gr_kb_groups';
		$result = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );

		return ( ! empty( $result ) && ( $table == $result ) );
	}
	
	/**
	 * Check if given articles belong to the currently selected langauge. Return ones that are.
	 * @param $articles
	 * @param bool $are_posts
	 * @return array
	 */
	public static function is_wpml_article_active( $articles, $are_posts=false ) {

		$article_ids = $articles;
		if ( $are_posts ) {
			$article_ids = array();
			foreach( $articles as $article ) {
				$article_ids[] = empty($article->ID) ? 0 : $article->ID;
			}
		}

		$current_lang = apply_filters( 'wpml_current_language', NULL );
		$current_article_ids = array();
		foreach( $article_ids as $article_id ) {
			$args = array( 'element_id' => $article_id, 'element_type' => 'post' );
			$article_lang = apply_filters( 'wpml_element_language_code', null, $args );
			if ( $article_lang == $current_lang ) {
				$current_article_ids[] = $article_id;
			}
		}

		return $current_article_ids;
	}

	/**
	 * Is WPML enabled?
	 *
	 * @param array $config
	 * @return bool
	 */
	public static function is_wpml_enabled( $config=array() ) {
		return ! empty( $config['wpml_is_enabled'] ) && $config['wpml_is_enabled'] === 'on' && ! defined( 'AMAG_PLUGIN_NAME' );
	}

	/**
	 * Is WPML plugin activated?
	 *
	 * @return bool
	 */
	public static function is_wpml_plugin_active() {
		return defined('ICL_SITEPRESS_VERSION');
	}

	public static function is_advanced_search_enabled( $kb_config=array() ) {

		if ( ! defined('AS'.'EA_PLUGIN_NAME') ) {
			return false;
		}

		return empty($kb_config) || (
		       $kb_config['kb_articles_common_path'] != 'demo-1-knowledge-base-basic-layout' &&
		       $kb_config['kb_articles_common_path'] != 'demo-2-knowledge-base-basic-layout' &&
		       $kb_config['kb_articles_common_path'] != 'demo-3-knowledge-base-tabs-layout' &&
		       $kb_config['kb_articles_common_path'] != 'demo-4-knowledge-base-tabs-layout' &&
		       $kb_config['kb_articles_common_path'] != 'demo-12-knowledge-base-image-layout' );
	}

	public static function is_article_rating_enabled() {
	   return defined( 'EP' . 'RF_PLUGIN_NAME' );
	}

	public static function is_elegant_layouts_enabled() {
		return defined('E'.'LAY_PLUGIN_NAME');
	}

	public static function is_multiple_kbs_enabled() {
		return defined('E'.'MKB_PLUGIN_NAME');
	}

	public static function is_article_features_enabled() {
		return defined('E'.'ART_PLUGIN_NAME');
	}

	public static function is_export_import_enabled() {
		return defined('E'.'PIE_PLUGIN_NAME');
	}

	public static function is_creative_addons_widgets_enabled() {
		return defined( 'CREATIVE_ADDONS_VERSION' ) && defined( 'ELEMENTOR_VERSION' );
	}

	public static function is_link_editor_enabled() {
		return defined('KB'.'LK_PLUGIN_NAME');
	}

	public static function is_kb_widgets_enabled() {
		return defined('WI'.'DG_PLUGIN_NAME');
	}

	public static function is_help_dialog_enabled() {
		return defined('EP'.'HD_PLUGIN_NAME');
	}

	public static function is_help_dialog_pro_enabled() {
		return defined('EP'.'HP_PLUGIN_NAME');
	}

	public static function is_knowledge_base_enabled() {
		return defined('EP'.'KB_PLUGIN_NAME');
	}

	public static function is_groups_enabled() {
		return defined('AM'.'GP_PLUGIN_NAME');
	}

	public static function is_custom_roles_enabled() {
		return defined('AM'.'CR_PLUGIN_NAME');
	}

	public static function is_link_editor( $post ) {
		return ! empty($post->post_mime_type) && ( $post->post_mime_type == 'kb_link' or $post->post_mime_type == 'kblink' );
	}

	/**
	 * Check if certain KB's plugin is enabled
	 *
	 * @param $plugin_id
	 * @return bool
	 */
	public static function is_plugin_enabled( $plugin_id ) {

		switch ( $plugin_id ) {
			case 'am'.'gr' :
			case 'core'    : return true;
			case 'pro'     : return self::is_help_dialog_pro_enabled();
			case 'em'.'kb' : return self::is_multiple_kbs_enabled();
			case 'ep'.'ie' : return self::is_export_import_enabled();
			case 'el'.'ay' : return self::is_elegant_layouts_enabled();
			case 'kb'.'lk' : return self::is_link_editor_enabled();
			case 'ep'.'rf' : return self::is_article_rating_enabled();
			case 'as'.'ea' : return self::is_advanced_search_enabled();
			case 'wi'.'dg' : return self::is_kb_widgets_enabled();
			case 'ep'.'hd' : return self::is_help_dialog_enabled();
			case 'cr'.'el' : return self::is_creative_addons_widgets_enabled();
			case 'am'.'gp' : return self::is_groups_enabled();
			case 'am'.'cr' : return self::is_custom_roles_enabled();
			default: return false;
		}
	}

	/**
	 * Check if Classic Editor plugin is active.
	 * By KAGG Design
	 * @return bool
	 */
	public static function is_block_editor_active() {
		// Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( self::is_classic_editor_plugin_active() ) {
			$editor_option       = get_option( 'classic-editor-replace' );
			$block_editor_active = array( 'no-replace', 'block' );
			return in_array( $editor_option, $block_editor_active, true );
		}

		return true;
	}

	/**
	 * Check if Classic Editor plugin is active.
	 * By KAGG Design
	 * @return bool
	 */
	public static function is_classic_editor_plugin_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'classic-editor/classic-editor.php' );
	}

	/**
	 * Send email using WordPress email facility.
	 *
	 * @param $message
	 * @param string $to_support_email - usually admin or support
	 * @param string $reply_to_email - usually customer email
	 * @param string $reply_to_name - usually customer name
	 * @param string $subject - which functionality is this email coming from
	 *
	 * @return string - return '' if email sent otherwise return error message
	 */
	public static function send_email( $message, $to_support_email='', $reply_to_email='', $reply_to_name='', $subject='' ) {

		// validate MESSAGE
		if ( empty( $message ) || strlen( $message ) > 5000 ) {
			KBLK_Logging::add_log( 'Invalid or too long email message', $message );
			return __( 'Error occurred', 'echo-knowledge-base' ) . ' (0)';
		}
		$message = sanitize_textarea_field( $message );

		// validate TO email
		if ( empty( $to_support_email ) ) { // send to admin if empty
			$to_support_email = get_option( 'admin_email' );
		}

		$to_support_email = sanitize_email( $to_support_email );
		if ( empty( $to_support_email ) || strlen( $to_support_email) > 100 ) {
			return __( 'Invalid email', 'echo-knowledge-base' ) . ' (1)';
		}

		if ( ! is_email( $to_support_email ) ) {
			return __( 'Invalid email', 'echo-knowledge-base' ) . ' (2)';
		}

		// validate REPLY TO email/name
		if ( empty( $reply_to_email ) ) {
			$reply_to_email = get_option( 'admin_email' );
		}

		$reply_to_email = sanitize_email( $reply_to_email );
		if ( empty( $reply_to_email ) || strlen( $reply_to_email ) > 100 ) {
			return __( 'Invalid email', 'echo-knowledge-base' ) . ' (3)';
		}
		
		if ( ! is_email( $reply_to_email ) ) {
			return __( 'Invalid email', 'echo-knowledge-base' ) . ' (4)';
		}
		
		if ( strlen( $reply_to_name ) > 100 ) {
			return __( 'Error occurred', 'echo-knowledge-base' ) . ' (5)';
		}
		
		$reply_to_name = sanitize_text_field( $reply_to_name );

		// validate SUBJECT
		$subject = sanitize_text_field( $subject );
		if ( empty( $subject ) ) {
			$subject = esc_html__( 'New message', 'echo-knowledge-base' ) . ' ' . esc_html_x( 'from', 'email sent from someone', 'echo-knowledge-base' ) . ' ' . esc_attr( get_bloginfo( 'name' ) );
		}

		if ( strlen( $subject ) > 200 ) {
			return __( 'Invalid subject', 'echo-knowledge-base' );
		}

		if ( strlen( $message ) > 10000 ) {
			return __( 'Email message is too long', 'echo-knowledge-base' );
		}

		// setup Email header
		$from_name = get_bloginfo( 'name' ); // Site title (set in Settings > General)
		$from_email = get_option( 'admin_email' );
		$headers = array(
			"From: {$from_name} <{$from_email}>\r\n",
			"Reply-To: {$reply_to_name} <{$reply_to_email}>\r\n",
			"Content-Type: text/html; charset=utf-8\r\n",
		);

		// setup Email message
		$message =
			'<html>
				<body>' . esc_html( $message ) .  '
				</body>
			</html>';

		// convert text to HTML - clickable links, turning line breaks into <p> and <br/> tags
		//$message = wpautop( make_clickable( $message ), false );
		$message = str_replace( '&#038;', '&amp;', $message );
		$message = str_replace( [ "\r\n", '\r\n', "\n", '\n', "\r", '\r' ], '<br />', $message );

		global /** @var WP_Error $kblk_email_error - email error from WordPress wp_mail() function */
		$kblk_email_error;

		$kblk_email_error = false;
		add_action( 'wp_mail_failed', [ 'KBLK_Utilities', 'check_email_errors' ] );

		// we to add filter to allow HTML in the email content to make sure the content type was not changed by third-party code
		add_filter( 'wp_mail_content_type', array( 'KBLK_Utilities', 'set_html_content_type' ), 999 );

		// send email
		$result = wp_mail( $to_support_email, $subject, $message, $headers );

		// remove filter that allows HTML in the email content
		remove_filter( 'wp_mail_content_type', array( 'KBLK_Utilities', 'set_html_content_type' ), 999 );
		remove_action( 'wp_mail_failed', [ 'KBLK_Utilities', 'check_email_errors' ] );

		// Log email errors if need
		$error_message = __( 'Failed to send the email.', 'echo-knowledge-base' );

		/** $kblk_email_error @ WP_Error */
		if ( is_wp_error( $kblk_email_error ) ) {
			KBLK_Logging::add_log( 'Email error: ' . $kblk_email_error->get_error_message() );
			$error_message = $kblk_email_error->get_error_message();
		}

		return $result ? '' : $error_message;
	}

	/**
	 * Called by WordPress to store Error when submitting email to the Mail Server
	 * @param WP_Error$error
	 */
	public static function check_email_errors( $error ) {
		global $kblk_email_error;
		$kblk_email_error = $error;
	}

	public static function set_html_content_type( $content_type ) {
		return 'text/html';
	}

	/**
	 * Get Category Link
	 * @param $category_id
	 * @param string $taxonomy
	 * @return string
	 */
	public static function get_term_url( $category_id, $taxonomy = '' ) {

		$category_url = empty( $taxonomy ) ? get_term_link( $category_id ) : get_term_link( $category_id, $taxonomy );
		if ( empty($category_url) || is_wp_error( $category_url )) {
			return '';
		}

		return $category_url;
	}

	/**
     * Return current page url without domain. Working only for the admin side
	 * @return string
	 */
    public static function get_current_admin_url() {

        $uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
        if ( empty( $uri ) ) {
            return '';
        }

        return remove_query_arg( array( '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice' ), $uri );
	}

	/**
	 * Return basic wordpress post html tags + some other
	 *
	 * @param $is_frontend
	 * @return array
	 */
	public static function get_extended_html_tags( $is_frontend=false ) {

		$extended_post_tags = [];
		if ( $is_frontend || self::is_user_allowed_unfiltered_html() ) {
			$extended_post_tags = [
				'img' => [
					'srcset' => true,
					'sizes' => true,
				],
				'source' => [
					'src' => true,
					'type' => true
				]
			];
		}

		return array_merge( wp_kses_allowed_html( 'post' ), $extended_post_tags );
    }

	public static function is_user_allowed_unfiltered_html() {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return false;
		}

		return current_user_can( 'unfiltered_html' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Return list of allowed HTML tags and their attributes for use in wp_kses()
	 *
	 * @param array $contexts
	 *
	 * @return array
	 */
	public static function get_allowed_html_tags( $contexts=[] ) {

		add_filter( 'safe_style_css', array( 'KBLK_Utilities', 'safe_inline_css_properties' ) );

		// Default context
		$tags_list = [
			'br'        => self::get_allowed_html_attributes(),
			'em'        => self::get_allowed_html_attributes(),
			'strong'    => self::get_allowed_html_attributes(),
			'div'       => self::get_allowed_html_attributes(),
			'i'         => self::get_allowed_html_attributes(),
			'span'      => self::get_allowed_html_attributes(),
			'h1'        => self::get_allowed_html_attributes(),
			'h2'        => self::get_allowed_html_attributes(),
			'h3'        => self::get_allowed_html_attributes(),
			'h4'        => self::get_allowed_html_attributes(),
			'h5'        => self::get_allowed_html_attributes(),
			'h6'        => self::get_allowed_html_attributes(),
			'p'         => self::get_allowed_html_attributes(),
			'ul'        => self::get_allowed_html_attributes(),
			'ol'        => self::get_allowed_html_attributes(),
			'li'        => self::get_allowed_html_attributes(),
			'table'     => self::get_allowed_html_attributes(),
			'tbody'     => self::get_allowed_html_attributes(),
			'thead'     => self::get_allowed_html_attributes(),
			'tr'        => self::get_allowed_html_attributes(),
			'td'        => self::get_allowed_html_attributes(),
			'th'        => self::get_allowed_html_attributes(),
			'header'    => self::get_allowed_html_attributes(),
			'section'   => self::get_allowed_html_attributes(),
			'label'     => self::get_allowed_html_attributes(),
			'legend'    => self::get_allowed_html_attributes(),
			'u'         => self::get_allowed_html_attributes(),
		];

		$allowed_html = self::is_user_allowed_unfiltered_html();
		// Link
		if ( in_array( 'link', $contexts ) || in_array( 'all', $contexts ) ) {
			$tags_list['a']         = self::get_allowed_html_attributes( 'a' );
		}

		// Image
		if ( in_array( 'image', $contexts ) || in_array( 'all', $contexts ) ) {
			$tags_list['img']       = self::get_allowed_html_attributes( 'img' );
		}

		// Audio
		if ( $allowed_html && ( in_array( 'audio', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['audio']     = self::get_allowed_html_attributes( 'audio' );
			$tags_list['source']    = self::get_allowed_html_attributes( 'source' );
		}

		// Video
		if ( $allowed_html && ( in_array( 'video', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['video']     = self::get_allowed_html_attributes( 'video' );
			$tags_list['source']    = self::get_allowed_html_attributes( 'source' );
		}

		// Form
		if ( $allowed_html && ( in_array( 'form', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['form']      = self::get_allowed_html_attributes( 'form' );
			$tags_list['input']     = self::get_allowed_html_attributes( 'input' );
			$tags_list['select']    = self::get_allowed_html_attributes( 'input' );
			$tags_list['textarea']  = self::get_allowed_html_attributes( 'input' );
			$tags_list['option']    = self::get_allowed_html_attributes( 'option' );
			$tags_list['button']    = self::get_allowed_html_attributes( 'input' );
			$tags_list['fieldset']  = self::get_allowed_html_attributes();
		}

		// Style
		if ( $allowed_html && ( in_array( 'style', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['style']     = self::get_allowed_html_attributes( 'script' );
		}

		// Script
		if ( $allowed_html && ( in_array( 'script', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['link']      = self::get_allowed_html_attributes( 'script' );
			$tags_list['script']    = self::get_allowed_html_attributes( 'script' );
		}

		// Iframe
		if ( $allowed_html && ( in_array( 'iframe', $contexts ) || in_array( 'all', $contexts ) ) ) {
			$tags_list['iframe']    = self::get_allowed_html_attributes( 'iframe' );
		}

		return $tags_list;
	}

	/**
	 * Return list of allowed HTML attributes for a given HTML tag
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	private static function get_allowed_html_attributes( $type='' ) {

		$attrs = [];

		// basic attributes
		$attrs['id'] = true;
		$attrs['class'] = true;
		$attrs['title'] = true;
		$attrs['style'] = true;
		$attrs['tabindex'] = true;
		$attrs['hidefocus'] = true;
		$attrs['role'] = true;
		$attrs['for'] = true;
		$attrs['width'] = true;
		$attrs['height'] = true;
		$attrs['colspan'] = true;
		$attrs['autocapitalize'] = true;
		$attrs['spellcheck'] = true;
		$attrs['download'] = true;
		$attrs['disabled'] = true;  // TODO: sync with add-ons

		// aria attributes
		$attrs['aria-hidden'] = true;
		$attrs['aria-label'] = true;
		$attrs['aria-autocomplete'] = true;

		// data attributes
		$attrs['data-target'] = true;
		$attrs['data-wp-editor-id'] = true;
		$attrs['data-id'] = true;
		$attrs['data-src'] = true;
		$attrs['data-post-id'] = true;
		$attrs['data-widget'] = true;
		$attrs['data-widget-type'] = true;
		$attrs['data-post-type'] = true;
		$attrs['data-include-type'] = true;
		$attrs['data-field'] = true;
		$attrs['data-on'] = true;
		$attrs['data-off'] = true;
		$attrs['data-mce-bogus'] = true;
		$attrs['area-hidden'] = true;
		$attrs['area-label'] = true;
		$attrs['colspan'] = true;
		$attrs['data-slug'] = true;
		$attrs['data-notice-id'] = true;
		$attrs['data-type'] = true;
		$attrs['data-name'] = true;
		$attrs['data-title'] = true;
		$attrs['data-zone'] = true;
		$attrs['data-depends'] = true;
		$attrs['data-wizard-type'] = true;
		$attrs['data-save_exit'] = true;
		$attrs['data-exit'] = true;
		$attrs['data-template_id'] = true;
		$attrs['data-template-id'] = true;
		$attrs['data-colors'] = true;
		$attrs['data-path'] = true;
		$attrs['data-plugin'] = true;
		$attrs['data-status'] = true;
		$attrs['data-key'] = true;
		$attrs['data-record-id'] = true;
		$attrs['data-active-kb-id'] = true;
		$attrs['data-link'] = true;
		$attrs['data-cfasync'] = true;
		$attrs['data-current-license-key'] = true;
		$attrs['data-article-id'] = true;
		$attrs['data-page-number'] = true;
		$attrs['data-start'] = true;
		$attrs['data-submit_text'] = true;
		$attrs['data-loading'] = true;
		$attrs['data-average'] = true;
		$attrs['data-language'] = true;
		$attrs['data-nonce'] = true;
		$attrs['data-setting'] = true;
		$attrs['data-value'] = true;
		$attrs['data-copy_text'] = true;
		$attrs['data-copied'] = true;
		$attrs['data-help'] = true;
		$attrs['data-preset'] = true;
		$attrs['data-line'] = true;
		$attrs['data-language_title'] = true;
		$attrs['data-index'] = true;
		$attrs['data-full-img-src'] = true;
		$attrs['data-full-video-src'] = true;
		$attrs['data-block-name'] = true;
		$attrs['data-group-name'] = true;
		$attrs['data-template-type'] = true;
		$attrs['data-target_selector'] = true;
		$attrs['data-style_name'] = true;
		$attrs['data-postfix'] = true;
		$attrs['data-wizard_input'] = true;
		$attrs['data-speed'] = true;

		// HD prefix
		$attrs['data-tab'] = true;
		$attrs['data-target-tab'] = true;
		$attrs['data-step'] = true;
		$attrs['data-target-step'] = true;
		$attrs['data-sub-step'] = true;
		$attrs['data-target-tab'] = true;
		$attrs['data-target-step'] = true;
		$attrs['data-target-sub-step'] = true;
		$attrs['data-option'] = true;
		$attrs['data-show-more'] = true;
		$attrs['data-show-less'] = true;

		// KB prefix
		$attrs['data-kb-admin-url'] = true;
		$attrs['data-kb-article-id'] = true;
		$attrs['data-kb-main-page-id'] = true;
		$attrs['data-kb-category-id'] = true;
		$attrs['data-kb-category-icon'] = true;
		$attrs['data-kb_article_seq_no'] = true;
		$attrs['data-kb_group_user_id'] = true;
		$attrs['data-kb-type'] = true;
		$attrs['data-kb_id'] = true;

		// Access Manager prefix
		$attrs['data-amgr-redirect-url'] = true;
		$attrs['data-amgr-category-id'] = true;
		$attrs['data-amgr-category-access-level'] = true;
		$attrs['data-amgr-group-icon-id'] = true;
		$attrs['data-amgr-parent-level-1'] = true;
		$attrs['data-amgr-parent-level-2'] = true;

		// KB Groups prefix
		$attrs['data-amgp-group-id'] = true;
		$attrs['data-amgp-group-icon-id'] = true;

		// Creative Add-ons
		$attrs['data-elementor-open-lightbox'] = true;
		$attrs['data-elementor-lightbox-title'] = true;

		switch( $type) {

			// inputs
			case 'input':
				$attrs['type'] = true;
				$attrs['name'] = true;
				$attrs['value'] = true;
				$attrs['checked'] = true;
				$attrs['autocomplete'] = true;
				$attrs['placeholder'] = true;
				$attrs['maxlength'] = true;
				$attrs['rows'] = true;
				break;

			// img
			case 'img':
				$attrs['src'] = true;
				$attrs['alt'] = true;
				break;

			// links
			case 'a':
				$attrs['href'] = true;
				$attrs['target'] = true;
				break;

			// option
			case 'option':
				$attrs['selected'] = true;
				$attrs['value'] = true;
				break;

			// forms
			case 'form':
				$attrs['method'] = true;
				$attrs['action'] = true;
				break;

			// iframe
			case 'iframe':
				$attrs['src'] = true;
				$attrs['frameborder'] = true;
				$attrs['allowtransparency'] = true;
				$attrs['allow'] = true;
				$attrs['allowfullscreen'] = true;
				break;

			// link, script, style
			case 'script':
				$attrs['href'] = true;
				$attrs['type'] = true;
				$attrs['rel'] = true;
				$attrs['media'] = true;
				break;

			// media
			case 'audio':
			case 'video':
				$attrs['controls'] = true;
				$attrs['autoplay'] = true;
				$attrs['loop'] = true;
				$attrs['muted'] = true;
				$attrs['poster'] = true;
				$attrs['preload'] = true;
				$attrs['src'] = true;
				break;

			// source for media
			case 'source':
				$attrs['src'] = true;
				$attrs['type'] = true;
				$attrs['media'] = true;
				$attrs['srcset'] = true;
				$attrs['sizes'] = true;
				break;

			default: break;
		}

		return $attrs;
	}

	/**
	 * Adjust list of safe CSS properties that allowed in wp_kses
	 *
	 * @param $styles
	 *
	 * @return mixed
	 */
	public static function safe_inline_css_properties( $styles ) {
		$styles[] = 'display';
		return $styles;
	}

	/**
	 * Get classes based on theme name for specific targeting
	 * @param $prefix
	 * @return string
	 */
	public static function get_active_theme_classes( $prefix = 'mp' ) {

		$current_theme = wp_get_theme();
		if ( ! empty( $current_theme ) && is_object( $current_theme ) && get_class( $current_theme ) != 'WP_Theme' ) {
			return '';
		}

		// get parent theme class if this is child theme
		$current_theme_parent = $current_theme->parent();
		if ( ! empty( $current_theme_parent ) && is_object( $current_theme_parent ) && get_class( $current_theme_parent ) == 'WP_Theme' ) {
			return 'eckb_' . $prefix . '_active_theme_' . $current_theme_parent->get_stylesheet();
		}

		return 'eckb_' . $prefix . '_active_theme_' . $current_theme->get_stylesheet();
	}

	/**
	 * Same code as wp_slash has in WP-5.5.0 (introduced in WP-3.6.0)
	 * The wp_slash_strings_only is now deprecated (introduced in WP-5.3.0),
	 * But the wp_slash does not handle non-string values until WP-5.5.0
	 * We need this function to support newest and oldest WP versions
	 *
	 * @param string|array $value
	 *
	 * @return bool
	 */
	public static function is_article_allowed_for_current_user( $article_id ) {

		// AMAG handles this separately
		if ( self::is_amag_on( true ) ) {
			return true;
		}

		$article = get_post( $article_id );

		// disallow article that failed to retrieve
		if ( empty( $article ) || empty( $article->post_status ) ) {
			return false;
		}

		// only check permissions for private articles
		if ( $article->post_status != 'private' ) {
			return true;
		}

		// for private articles user needs to be logged in
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return current_user_can( 'read_private_posts' ) || get_current_user_id() == $article->post_author;
	}
}
