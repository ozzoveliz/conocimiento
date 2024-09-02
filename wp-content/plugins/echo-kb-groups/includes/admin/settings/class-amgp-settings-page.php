<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGP_Settings_Page {

	public function __construct() {
		add_filter( 'eckb_add_on_debug_data', array( $this, 'display_debug_data' ) );
		add_filter( 'amag_get_error_logs', array( $this, 'get_error_logs' ) );
		add_action( 'eckb_reset_error_Logs', array( $this, 'reset_log' ) );
	}

	public function display_debug_data( $output_param ) {

		// only administrators can handle licenses
		if ( ! current_user_can('manage_options') ) {
			return 'No access';
		}

		// display KB configuration
		$output = "\n\n\n";
		$output .= "KB GROUPS Configuration:\n============================\n\n";
		/*$all_kb_configs = amgp_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_config ) {
			$specs = AMGP_KB_Config_Specs::get_fields_specification();
			foreach( $kb_config as $name => $value ) {
				if ( ! is_string($value) ) {
					$value = AMGP_Utilities::get_variable_string($value);
				}
				$label = empty($specs[$name]['label']) ? 'unknown' : $specs[$name]['label'];
				$output .= '- ' . $label . ' [' . $name . ']' . ' => ' . $value . "\n";
			}

			$output .= "\n\n";
		}*/

		// display error logs
		$output .= "\n\nKB GROUPS ERROR LOG:\n==========\n\n";
		$logs = AMGP_Logging::get_logs();
		foreach( $logs as $log ) {
			$output .= empty($log['date']) ? '' : $log['date'] . " ";
			$output .= empty($log['kb']) ? '' : $log['kb'] . " ";
			$output .= empty($log['message']) ? '' : $log['message'] . " ";
			$output .= empty($log['trace']) ? '' : $log['trace'] . " ";
		}

		// display AMGP tables etc.
	//	$output .= "\n\nAMGP Data:\n========";
	/*	$all_kb_configs = amgp_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_access_config ) {
			$kb_id = $kb_access_config['id'];
			$output .= "\n\nKB: " . '[' . $kb_id . ']' . "\n\n";

			$kb_groups = amgp_get_instance()->db_kb_groups->get_groups( $kb_id );
			if ( $kb_groups === null ) {
				$output .= 'ERROR retrieving groups.';
				return $output;
			}
			if ( empty($kb_groups) ) {
				$output .= "\t<no groups>\n";
			}

			// list groups
			$output .= "\n";
			foreach( $kb_groups as $ix => $kb_group ) {

				$is_public_group = amgp_get_instance()->db_kb_public_groups->is_public_group( $kb_id, $kb_group->kb_group_id );
				if ( $is_public_group === null ) {
					$output .= 'ERROR: Cannot retrieve Public group';
				} else {
					$output .= "\t" . ($is_public_group ? 'PUBLIC ' : '' ) . "Group: " . $kb_group->name  . " - " . $kb_group->kb_group_id . '  [' . $kb_group->created_by . ']' . "\n";
				}

				$users = amgp_get_instance()->db_kb_group_users->get_group_users_config( $kb_id, $kb_group->kb_group_id );
				if ( $users === null ) {
					$output .= 'ERROR retrieving users: ' . $kb_group->kb_group_id;
					continue;
				}
				if ( empty($users) ) {
					$output .= "\t\t<no users>\n";
				}

				foreach( $users as $ix2 => $user ) {
					$output .= "\t\tUser: " . $user->wp_user_id . ' - ' . $user->kb_role_name . '  [' . $user->created_by . ']' . "\n";
				}

				$categories = amgp_get_instance()->db_access_kb_categories->get_group_categories( $kb_id, $kb_group->kb_group_id );
				if ( $categories === null ) {
					$output .= 'ERROR retrieving Group categories: ' . $kb_group->kb_group_id;
					continue;
				}
				if ( empty($categories) ) {
					$output .= "\t\t<no categories>\n";
				}

				foreach( $categories as $ix3 => $category ) {
					$output .= "\t\tCategory: " . $category->kb_category_id . '  [' . $category->created_by . ']' . "\n";
				}
			}

			// display READ-ONLY articles if any
			$kb_read_only_article_ids = amgp_get_instance()->db_access_read_only_articles->get_all_read_only_kb_articles_ids( $kb_id );
			if ( $kb_read_only_article_ids === null ) {
				$output .= 'ERROR retrieving articles.';
				return $output;
			}
			if ( empty($kb_read_only_article_ids) ) {
				$output .= "\t\t<no Read-only articles>\n";
			}

			foreach( $kb_read_only_article_ids as $kb_article_id_obj ) {
				$kb_article_id = $kb_article_id_obj->kb_article_id;
				$kb_articles = amgp_get_instance()->db_access_read_only_articles->get_read_only_article_groups( $kb_id, $kb_article_id );
				if ( empty($kb_articles) ) {
					$output .= 'ERROR retrieving read-only article record: ' . $kb_article_id;
					continue;
				}
				foreach( $kb_articles as $kb_article ) {
					$output .= "\t\tRead-only Article: " . $kb_article_id . ' - ' . $kb_article->kb_group_id . '  [' . $kb_article->created_by . ']' . "\n";
				}
			}

			// display READ-ONLY categories if any
			$kb_read_only_category_ids = amgp_get_instance()->db_access_read_only_categories->get_all_read_only_kb_category_ids( $kb_id );
			if ( $kb_read_only_category_ids === null ) {
				$output .= 'ERROR retrieving read-only categories.';
				return $output;
			} else if ( empty($kb_read_only_category_ids) ) {
				$output .= "\t\t<no read-only categories>\n";
			} else {

				foreach ( $kb_read_only_category_ids as $kb_category_id_obj ) {
					$kb_category_id = $kb_category_id_obj->kb_category_id;
					$kb_categories  = amgp_get_instance()->db_access_read_only_categories->get_read_only_category_records( $kb_id, $kb_category_id );
					if ( empty( $kb_categories ) ) {
						$output .= 'ERROR retrieving category record: ' . $kb_category_id;
						continue;
					}
					foreach ( $kb_categories as $kb_category ) {
						$output .= "\t\tRead-only Categories: " . $kb_category_id . ' - ' . $kb_category->kb_group_id . '  [' . $kb_category->created_by . ']' . "\n";
					}
				}
			}
		} */

		return $output_param . $output;
	}

	public function get_error_logs( $logs ) {
		return array_merge($logs, AMGP_Logging::get_logs());
	}

	public function reset_log() {

		// 1. Is User Admin ?
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMGP_Utilities::ajax_show_error_die( esc_html__( 'You do not have ability to change access privileges.', 'echo-knowledge-base' ) );
		}

		AMGP_Logging::reset_logs();
	}

	public function user_not_logged_in() {
		AMGP_Utilities::ajax_show_error_die( '<p>' . esc_html__( 'You are not logged in. Refresh your page and log in', 'echo-knowledge-base' ) . '.</p>', esc_html__( 'Cannot save your changes', 'echo-knowledge-base' ) );
	}
}