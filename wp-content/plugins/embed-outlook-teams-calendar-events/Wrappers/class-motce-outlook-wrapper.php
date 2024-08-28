<?php
/**
 * Outlook wrapper to process all API responses.
 *
 * @package embed-outlook-teams-calendar-events/Views
 */

namespace MOTCE\Wrappers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle all utility functions to process API response.
 */
class MOTCE_Outlook_Wrapper {

	/**
	 * Holds the MOTCE_Outlook_Wrapper class instance.
	 *
	 * @var MOTCE_Outlook_Wrapper
	 */
	private static $motce;

	/**
	 * Object instance(MOTCE_Outlook_Wrapper) getter method.
	 *
	 * @return MOTCE_Outlook_Wrapper
	 */
	public static function get_wrapper() {
		if ( ! isset( self::$motce ) ) {
			self::$motce = new MOTCE_Outlook_Wrapper();
		}
		return self::$motce;
	}

	/**
	 * Helper function to process calendar events data.
	 *
	 * @param array $result calendar event items array.
	 * @return array
	 */
	public static function process_calendar_event_items( $result ) {
		if ( ! isset( $result['value'] ) ) {
			return array();
		}

		$res = $result['value'];

		$output = array();

		foreach ( $res as $key => $event ) {
			$temp_output = array();

			$temp_output['startDate'] = $event['start']['dateTime'];

			if ( ! empty( $event['categories'] ) ) {
				$temp_output['calendar'] = $event['categories'][0];
			} else {
				$temp_output['calendar'] = 'Event';
			}

			$temp_output['eventName'] = $event['subject'];
			$temp_output['webLink']   = $event['webLink'];

			array_push( $output, $temp_output );

		}

		return $output;
	}

	/**
	 * Helper function to process outlook categories.
	 *
	 * @param array $result outlook calendar categories array.
	 * @return array
	 */
	public static function process_outlook_categories( $result ) {
		if ( ! isset( $result['value'] ) ) {
			return array();
		}

		$res = $result['value'];

		$output = array();

		foreach ( $res as $key => $category ) {
			$output[ $category['displayName'] ] = $category;
		}

		return $output;
	}

}
