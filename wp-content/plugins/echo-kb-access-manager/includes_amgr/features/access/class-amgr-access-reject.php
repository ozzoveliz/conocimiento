<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle case where user is denied access to page, category, article etc.
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class AMGR_Access_Reject {

	public static function display_denied_message() {
        $kb_id = EPKB_KB_Handler::get_current_kb_id();
        if ( empty($kb_id) ) {
            $kb_id = EPKB_KB_Config_DB::DEFAULT_KB_ID;
        }
        $amgr_config = epkb_get_instance()->kb_access_config_obj->get_kb_config_or_default( $kb_id );
        $title =  $amgr_config['no_access_title'];
        $message = self::get_denied_message( $amgr_config );

        return self::display_access_denied_message( $title, $message );
	}

	public static function reject_user_access( $kb_id, $error_code = '' ) {

		$kb_id = EPKB_Utilities::sanitize_int( $kb_id, EPKB_KB_Config_DB::DEFAULT_KB_ID );
		$amgr_config = epkb_get_instance()->kb_access_config_obj->get_kb_config_or_default( $kb_id );
		$title = $amgr_config['no_access_title'];

		$message = self::get_denied_message( $amgr_config );
		$message .= self::get_user_access_debug_message( $kb_id, $error_code );

		$action = AMGR_Access_Utilities::is_logged_off() ? $amgr_config['no_access_action_user_without_login'] :
														   $amgr_config['no_access_action_user_with_login'];

		// handle user no-access scenario
		switch( $action ) {

			case 'redirect_to_login_page':
				if ( headers_sent() || ! AMGR_Access_Utilities::is_logged_off() ) {
					$request_uri = empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'];
					$url = wp_login_url( $request_uri );
					echo '<span id="amgr-redirect-to-page" data-amgr-redirect-url="' . esc_url( $url ) . '" display="hidden"/>';
					return self::display_access_denied_message( $title, $message );
				} else {
					auth_redirect();
				}
				break;

			case 'redirect_to_custom_page':
				if ( headers_sent() ) {
					//$message .= '<a href="' . esc_url( $amgr_config['no_access_redirect_to_custom_page'] ) . '" target="_blank">Login Here</a>';
					return self::display_access_denied_message( $title, $message );
				} else {
					wp_redirect( $amgr_config['no_access_redirect_to_custom_page'] );
				}
				break;

			case 'return_403_http_code':
				wp_die( $message, $title, [ 'response' => 403 ] );

			case 'display_404_page':
				/** @noinspection PhpIncludeInspection */
				global $wp_query;

				$wp_query->set_404();
				$wp_query->max_num_pages = 0;
				status_header( 404 );

				if ( headers_sent() ) {
					ob_start();
					$result = get_template_part( 404 );
					if ( $result === false ) {
						get_template_part( 'index' );
					}

					return ob_get_clean();
				}

				return '';

			case 'show_access_denied_msg':
			default:
				return self::display_access_denied_message( $title, $message );
		}

		exit;
	}

	private static function display_access_denied_message( $title, $message ) {

		return
		'<style>
			.amgr-access-denied-notification {
		 		border: solid 1px #666666;
		  		font-size: 14px;
		  		margin: 15px 0;
		  	}
		  	.amgr-access-denied-notification .amgr-access-denied-header {
				background-color: #d9534f;
				display: block;
				
				padding: 20px;
			}
			.amgr-access-denied-notification .amgr-access-denied-header h3 {
				  color: #fff;
			  	font-size: 2em;
			  	margin: 0;
			  	padding: 0;
			  	text-align: center;
			}
		  	.amgr-access-denied-notification .amgr-access-denied-body {
				
				padding: 20px 20px;
				text-align: center;
				background-color: #fff; 
			}
		</style>
		<div class="amgr-access-denied-notification">
			<section class="amgr-access-denied-header">
				<h3>' . $title . '</h3>
			</section>
			<section class="amgr-access-denied-body">
				<p><strong>' . $message . '</strong></p>
			</section>
		</div>';
	}

	/**
	 * @param $kb_config
	 * @return mixed
	 */
	public static function get_denied_message( $kb_config ) {

		if ( empty( $kb_config['no_access_text_logged'] ) ) {
			return $kb_config['no_access_text'];
		}

		return AMGR_Access_Utilities::is_logged_off() ? $kb_config['no_access_text'] : $kb_config['no_access_text_logged'];
	}

	private static function get_user_access_debug_message( $kb_id, $error_code = '' ) {
		global $wp_query, $post_type, $amgr_access_action_code, $wpdb;

		// check if user logged in and go out if not to prevent DB queries
		$current_user = AMGR_Access_Utilities::get_current_user();
		if ( empty( $current_user ) ) {
			return '';
		}

		$debug_user_access_id = get_transient( AMGR_Debug_User_Access::AMGR_USER_ACCESS_DEBUG );
		if ( empty( $debug_user_access_id ) ) {
			return '';
		}

		// only show debug info for the user being debugged
		if ( $current_user->ID != $debug_user_access_id ) {
			return '';
		}

		// show current page info
		$message = '<br><br>Current url: ' . $_SERVER['REQUEST_URI'] . '<br>';

        // is this a KB article?
        if ( EPKB_KB_Handler::is_kb_post_type( $post_type ) && ! empty( $wp_query->query ) && ! empty( $wp_query->query['name'] ) ) {

            $posts = $wpdb->get_results( " SELECT * " .
                " FROM $wpdb->posts " . /** @secure 02.17 */
                " WHERE post_name = '" . $wp_query->query['name'] . "' ");

            if ( $posts ) {
                $author = get_user_by( 'id', $posts[0]->post_author );
                $author_name = $author ? $author->display_name : 'unknown';
                $message .= '<br>Current post: <br>';
                $message .= '- Author: ' . $author_name . '<br>';
                $message .= '- Status: ' . $posts[0]->post_status . '<br>';
                $message .= '- Type: ' . $posts[0]->post_type . '<br>';
                $message .= '- ID: ' . $posts[0]->ID . '<br>';
            }

        } else {
            $queried_object = get_queried_object();
            if ( ! empty( $queried_object ) ) {
                $message .= '<br><br>Current post/taxonomy: <br>';
                foreach( $queried_object as $key => $value ) {
                    $message .= '- ' . $key . ': ' . $value . '<br>';
                }
                $message .= '<br>';
            }
        }

		// show user info
		$message .= '<br>' . 'Current User Name: ' . $current_user->user_login . ', ID: ' . $current_user->ID . ', status: ' . $current_user->user_status . ' <br>';
		$message .= 'WordPress roles: ' . implode( ', ', $current_user->roles ) . '<br>';

		// show AMGR groups and roles
		$message .= 'User AMGR roles: ' . ( empty( $kb_id ) ? 'no KB ID' : '' ) . '<br>';
		if ( ! empty( $kb_id ) ) {

			// if KB Groups enabled then display roles per group
			if ( AMGR_WP_Roles::use_kb_groups() ) {

				$user_groups = epkb_get_instance()->db_kb_group_users->get_groups_for_given_user( $kb_id );
				if ( $user_groups === null ) {
					$message .= 'User group: no valid group <br>';
				} else {
					foreach( $user_groups as $user_group ) {
						$group_data = epkb_get_instance()->db_kb_groups->get_group( $kb_id, $user_group->kb_group_id );
						if ( empty( $group_data) ) {
							continue;
						}

						$message .= ' - User group: ' . $group_data->name . ' (ID: ' . $user_group->kb_group_id . '), role: ' . $user_group->kb_role_name . '<br>';
					}
				}
			} else {
				$user_role = AMGR_WP_Roles::get_user_highest_kb_role_based_on_wp_role( $kb_id, $current_user );
				$message .= 'User role: ' . $user_role . '<br>';
			}
		}

		$message .= '<br>'. 'Error code: ' . $error_code . '<br>';
		$message .= 'Action code: ' . ( empty( $amgr_access_action_code ) ? 'none' : $amgr_access_action_code ) . '<br>';
		
		$message .= '<pre style="overflow:auto;">' . AMGR_Logging::generateStackTrace() . '</pre>';

		return $message;
	}
}
