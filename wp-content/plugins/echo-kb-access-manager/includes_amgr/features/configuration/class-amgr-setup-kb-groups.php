<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Setup PUBLIC and initial private KB Groups
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Setup_KB_Groups {

	/**
	 * Ensure each KB has one PUBLIC group.
	 * Do not create one if this was done already.
	 */
	public function setup_amgr_data() {
		/** @var $wpdb Wpdb */
		global $wpdb;

		// ensure KB groups are setup
		$all_kb_ids = epkb_get_instance()->kb_config_obj->get_kb_ids();
		foreach( $all_kb_ids as $kb_id ) {

			// we need Public group
			AMGR_Logging::disable_logging();
			$public_group = epkb_get_instance()->db_kb_public_groups->get_public_group( $kb_id );
			AMGR_Logging::enable_logging();
			if ( is_wp_error($public_group) ) {
				AMGR_Logging::add_log('Cannot retrieve PUBLIC group to setup groups', $kb_id, 'Last DB ERROR: (' . $wpdb->last_error . ')');
				continue;
			}

			// no public group - create one
			if ( empty($public_group) ) {
				$kb_public_group_id = epkb_get_instance()->db_kb_public_groups->insert_public_group( $kb_id );
				if ( $kb_public_group_id === null ) {
					AMGR_Logging::add_log('Cannot insert PUBLIC group.', $kb_id);
					continue;
				}
			}
		}
	}
}
