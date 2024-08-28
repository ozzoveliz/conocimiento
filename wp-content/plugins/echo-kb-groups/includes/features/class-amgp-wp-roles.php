<?php   if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manage user access through WP Roles.
 * User can have multiple WP Roles.
 */
class AMGP_WP_Roles {

	const WP_ROLE_MAPPINGS = 'amag_wp_roles_map';
	const WP_ROLE_GROUP_MAPPINGS = 'amag_wp_role_group_map';

	// cache settings
	static $cache_use_kb_groups = null;
	static $cache_use_wp_role_mapping = null;
	static $cache = array();

	/**
	 * Use KB Groups and KB Roles if:
	 * 1) KB Groups add-on is active
	 * 2) KB Groups add-on is inactive but KB Group users exist
	 *
	 * @return bool
	 */
	public static function use_kb_groups() {

		if ( self::$cache_use_kb_groups !== null ) {
			return self::$cache_use_kb_groups;
		}

		$am_gp_version = AMGP_Utilities::get_wp_option( 'am'.'gp_version', '' );
		if ( ! empty($am_gp_version) ) {
			self::$cache_use_kb_groups = true;
			return self::$cache_use_kb_groups;
		}

		$kb_users = amgp_get_instance()->db_kb_group_users->get_all_rows();
		if ( empty($kb_users) ) {
			self::$cache_use_kb_groups = false;
			return self::$cache_use_kb_groups;
		}
		if ( is_wp_error($kb_users) || count($kb_users) > 0 ) {
			self::$cache_use_kb_groups = true;
			return self::$cache_use_kb_groups;
		}

		self::$cache_use_kb_groups = false;

		return self::$cache_use_kb_groups;
	}

	/**
	 * Use Custom Roles if:
	 * 1) Custom Roles add-on is active
	 * 2) Custom Roles add-on is inactive but mapping exists
	 * @return bool
	 */
	public static function use_wp_role_mapping() {

		if ( self::$cache_use_wp_role_mapping !== null ) {
			return self::$cache_use_wp_role_mapping;
		}

		$am_cr_version = AMGP_Utilities::get_wp_option( 'am'.'cr_version', '' );
		if ( ! empty($am_cr_version) ) {
			self::$cache_use_wp_role_mapping = true;
			return self::$cache_use_wp_role_mapping;
		}

		$wp_roles_mapping = AMGP_Utilities::get_wp_option( self::WP_ROLE_MAPPINGS, array(), true, true );
		if ( is_wp_error($wp_roles_mapping) ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
			return true;
		}

		self::$cache_use_wp_role_mapping = count($wp_roles_mapping) > 0;

		return self::$cache_use_wp_role_mapping;
	}

	/**
	 * Return WP Role mapppings for given KB
	 * @param $kb_id
	 * @return array|false
	 */
	public static function get_wp_roles_mappings_for_kb( $kb_id ) {
		$wp_roles_mapping = self::get_wp_roles_mappings( $kb_id );
		return ( empty($wp_roles_mapping) || empty($wp_roles_mapping[$kb_id]) ? array() : $wp_roles_mapping[$kb_id] );
	}

	/**
	 * Return all WP Role mapppings
	 *
	 * @param $kb_id
	 * @return array|false
	 */
	public static function get_wp_roles_mappings( $kb_id ) {

		$use_kb_groups = self::use_kb_groups();
		$use_wp_role_maps = self::use_wp_role_mapping();
		$wp_roles_mapping = array();

		// if Custom Roles is on then get it
		if ( $use_wp_role_maps ) {
			$option_name = $use_kb_groups ? self::WP_ROLE_GROUP_MAPPINGS : self::WP_ROLE_MAPPINGS;
			$wp_roles_mapping = AMGP_Utilities::get_wp_option( $option_name, array(), true, true );
			if ( is_wp_error($wp_roles_mapping) ) {
				AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
				return false;
			}

		// if don't have Custom Roles and KB Groups is not on then use WP built-in roles
		} else if ( ! $use_kb_groups ) {
			$wp_roles_mapping[$kb_id]['subscriber'][0] = AMGP_KB_Role::KB_ROLE_SUBSCRIBER;
			$wp_roles_mapping[$kb_id]['contributor'][0] = AMGP_KB_Role::KB_ROLE_CONTRIBUTOR;
			$wp_roles_mapping[$kb_id]['author'][0] = AMGP_KB_Role::KB_ROLE_AUTHOR;
			$wp_roles_mapping[$kb_id]['editor'][0] = AMGP_KB_Role::KB_ROLE_EDITOR;
		}
		// otherwise no roles mapping

		return $wp_roles_mapping;
	}

	/**
	 * Add WP Role mapping.
	 *
	 * @param $kb_id
	 * @param $wp_role
	 * @param $kb_role
	 * @param int $kb_group_id
	 * @param bool $delete
	 *
	 * @return bool
	 */
	public static function update_wp_role_mapping( $kb_id, $wp_role, $kb_role, $kb_group_id=0, $delete=false ) {

		// core mapping is hard-coded
		$use_wp_role_maps = self::use_wp_role_mapping();
		if ( ! $use_wp_role_maps ) {
			return true;
		}

		// get current mapping
		$wp_roles_mapping = self::get_wp_roles_mappings( $kb_id );
		if ( $wp_roles_mapping === false ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
			return false;
		}

		// update the mapping
		if ( $delete ) {
			unset($wp_roles_mapping[$kb_id][$wp_role][$kb_group_id]);
			if ( self::is_array_element_empty($wp_roles_mapping[$kb_id][$wp_role]) ) {
				unset($wp_roles_mapping[$kb_id][$wp_role]);
			}
			if ( self::is_array_element_empty($wp_roles_mapping[$kb_id]) ) {
				unset($wp_roles_mapping[$kb_id]);
			}
		} else {
			$wp_roles_mapping[$kb_id][$wp_role][$kb_group_id] = $kb_role;
		}

		$use_kb_groups = self::use_kb_groups();

		// we have both add-ons so use KB Groups mappings
		// if Roles Mappping is on use its configuration
		$option_name = $use_kb_groups ? self::WP_ROLE_GROUP_MAPPINGS : self::WP_ROLE_MAPPINGS;
		$result = AMGP_Utilities::save_wp_option( $option_name, $wp_roles_mapping, true );
		if ( is_wp_error($result) ) {
			AMGP_Logging::add_log( 'Error Adding WP Role mapping', $result );
			return false;
		}

		return true;
	}

	/**
	 * Remove WP Role mapping.
	 *
	 * @param $kb_id
	 * @param $wp_role
	 * @param int $kb_group_id
	 *
	 * @return bool
	 */
	public static function delete_wp_role_mapping( $kb_id, $wp_role, $kb_group_id=0 ) {
		return self::update_wp_role_mapping( $kb_id, $wp_role, '', $kb_group_id, true );
	}

	/**
	 * When deleting KB Group, delete related role mappings.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @return bool
	 */
	public static function delete_all_group_mappings( $kb_id, $kb_group_id ) {

		// get current mapping
		$wp_roles_mapping = self::get_wp_roles_mappings( $kb_id );
		if ( $wp_roles_mapping === false ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping (2)', $wp_roles_mapping );
			return false;
		}

		// done if KB has no role mappings
		if ( empty($wp_roles_mapping[$kb_id]) ) {
			return true;
		}

		foreach( $wp_roles_mapping[$kb_id] as $wp_role => $map_data ) {

			// delete role mapping for matching KB Group ID; ignore errors
			if ( isset($map_data[$kb_group_id]) ) {
				self::delete_wp_role_mapping( $kb_id, $wp_role, $kb_group_id );
			}
		}

		return true;
	}

	/**
	 * ONLY if KB Groups not considered "active": Based on user WP Role, return the highest comparable KB Role.
	 *
	 * @param $kb_id
	 * @param $user
	 * @param bool $kb_groups_on
	 *
	 * @return string - empty if user has no KB Role
	 */
	public static function get_user_highest_kb_role_based_on_wp_role( $kb_id, $user=null, $kb_groups_on=false ) {

		if ( ! $kb_groups_on && self::use_kb_groups() ) {
			AMGP_Logging::add_log( 'Internal error (14)' );
			return '';
		}

		$user = empty($user) ? AMGP_Access_Utilities::get_current_user() : $user;
		if ( empty($user) ) {
			return '';
		}

		$wp_roles_mapping = self::get_wp_roles_mappings_for_kb( $kb_id );
		if ( $wp_roles_mapping === false ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
			return '';
		}

		// find the highest role
		$highest_role = '';
		foreach( $wp_roles_mapping as $wp_role => $data ) {

			if ( in_array($wp_role, $user->roles) ) {
				foreach ( $data as $kb_group_id => $kb_role_name ) {
					$kb_role_name = AMGP_KB_Role::get_higher_role( $highest_role, $kb_role_name );
					if ( empty( $kb_role_name ) ) {
						continue;
					}
					$highest_role = $kb_role_name;
				}
			}
		}

		return $highest_role;
	}

	/**
	 * Retrieve all user KB IDs
	 * @param $user
	 * @return array
	 */
	public static function get_user_kbs( $user ) {

		$user_kbs = array();
		$kb_ids = amgp_get_instance()->kb_config_obj->get_kb_ids();

		if ( AMGP_Access_Utilities::is_admin_or_kb_manager( $user ) ) {
			return $kb_ids;
		}

		foreach( $kb_ids as $kb_id ) {
			$user_role = self::get_user_highest_kb_role_based_on_wp_role( $kb_id, $user, true );
			if ( ! empty($user_role) ) {
				$user_kbs[] = $kb_id;
			}
		}

		return $user_kbs;
	}

	/**
	 * Get KB Groups based on Role Mappings
	 *
	 * @param $kb_id
	 * @param $user
	 * @return array|null
	 */
	public static function get_groups_for_given_user( $kb_id, $user ) {

		// if we don't use group then don't return any
		if ( ! self::use_kb_groups() ) {
			return array();
		}

		$wp_roles_mapping = self::get_wp_roles_mappings_for_kb( $kb_id );
		if ( $wp_roles_mapping === false ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
			return null;
		}

		$user_groups = array();
		foreach( $wp_roles_mapping as $wp_role => $data ) {
			foreach( $data as $kb_group_id => $kb_role_name ) {

				// ignore if mapping has no group; should not happen
				if ( empty($kb_group_id) ) {
					AMGP_Logging::add_log( 'Internal error (16)', $wp_roles_mapping );
					continue;
				}

				// is user role matching the map record role?
				if ( ! in_array( $wp_role, $user->roles) ) {
					continue;
				}

				$kb_group = amgp_get_instance()->db_kb_groups->get_group( $kb_id, $kb_group_id );
				if ( $kb_group === null ) {
					continue;
				}

				$kb_group->kb_role_name = $kb_role_name;
				$user_groups[] = $kb_group;
			}
		}

		return $user_groups;
	}

	private static function is_array_element_empty( $array_element ) {
		return isset($array_element) && count($array_element) === 0;
	}

	/**
	 * @param $kb_id
	 * @param $in_kb_group_id
	 * @param $in_kb_role_name
	 *
	 * @return array|false - false on error
	 */
	public static function get_wp_role_user_ids_for_group( $kb_id, $in_kb_group_id, $in_kb_role_name ) {

		// 1. mapping for given KB ID
		$wp_roles_mapping = self::get_wp_roles_mappings_for_kb( $kb_id );
		if ( $wp_roles_mapping === false ) {
			AMGP_Logging::add_log( 'Error retrieving WP Role mapping', $wp_roles_mapping );
			return false;
		}

		// 2. mappping for given KB Group and KB Role
		$matching_wp_roles = array();
		foreach( $wp_roles_mapping as $wp_role => $data ) {
			foreach( $data as $kb_group_id => $kb_role_name ) {
				if ( $in_kb_group_id != $kb_group_id || $kb_role_name != $in_kb_role_name ) {
					continue;
				}
				$matching_wp_roles[] = $wp_role;
			}
		}

		// 3. mapping based on WP Role
		$user_ids = array();
		$wp_users = self::get_wp_users();
		foreach( $wp_users as $wp_user ) {
			foreach( $matching_wp_roles as $matching_wp_role ) {
				if ( in_array($matching_wp_role, $wp_user->roles) ) {
					$user_ids[] = $wp_user->ID;
				}
			}
		}

		return $user_ids;
	}

	/**
	 * Get WP Users except administrators.
	 * @return array
	 */
	private static function get_wp_users() {
		$args = array(
			'role__not_in' => array('Administrator'),
			'orderby'      => 'login',
			'order'        => 'ASC',
			'count_total'  => false,
			'number'       => '500',
		);

		return get_users( $args );
	}
}
