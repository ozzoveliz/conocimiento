<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display view of users access controls.
 */
class AMGP_Access_Page_View_Users {

	/** @var  AMGP_HTML_Elements */
	private $html;
	private $kb_id;
	private $kb_groups;

	const KB_MANAGERS = 999999;

	public function __construct() {

		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::output_inline_error_notice(__( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E39)');
			return;
		}

		add_action( 'eckb_kb_users_tab_content', array($this, 'show_users_section') );
	}

	/**
	 * Display USERS section.
	 */
    public function show_users_section() { ?>
	    <!-- Users Content -->
	    <div class="amag-config-content" id="amgp-users-content"></div>    <?php
    }

	/**
	 * Called by AJAX after user makes changes.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $active_role
	 *
	 * @return bool
	 */
	public function ajax_update_tab_content( $kb_id, $kb_group_id, $active_role ) {

		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_users') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E10)');
		}

		$this->html = new AMGP_HTML_Elements();
		$this->kb_id = $kb_id;

	    // get all existing kb_groups for KB
	    $this->kb_groups = amgp_get_instance()->db_kb_groups->get_private_groups( $this->kb_id );
		if ( $this->kb_groups === null ) {
			AMGP_Logging::add_log( "Could not get KB Groups", $this->kb_id );
			AMGP_Utilities::output_inline_error_notice( 'Internal Error occurred (a203)' );
			return false;
		}  

		return $this->display_users_list( $kb_group_id, $active_role );
    }

	/**
	 * Display drop-down list of existing groups and Add/Remove/Update buttons.
	 *
	 * @param int $kb_group_id
	 * @param string $active_role
	 * @return bool
	 */
	private function display_users_list( $kb_group_id= 0, $active_role='' ) {    ?>

        <section class="amag-page-header">
            <h2>Users</h2>
        </section> 
		<section id="amgp-user-tabs-section" class="amgp-group-control-container">
			<label>Group:</label>
	        <select id="amgp-user-tabs-kb-group-list">
	            <option value="0">Choose a Group</option>   <?php

		        // first All Groups for KB managers
	            if ( current_user_can('manage_options') ) {
		            echo '<option ' . ( $kb_group_id === self::KB_MANAGERS ? 'selected' : '' ) . ' value="999999" style="font-weight:bold; color: goldenrod;">All Groups (KB Managers)</option>';
	            }

	            // next display individual KB Groups
				foreach( $this->kb_groups as $kb_group ) {
					echo '<option ' . ( $kb_group->kb_group_id == $kb_group_id ? 'selected' : '' ) . ' value="' . esc_attr($kb_group->kb_group_id) . '">' . esc_html($kb_group->name) . '</option>';
				}                ?>
	        </select>   <?php

			$result = true;
	        if ( $kb_group_id === self::KB_MANAGERS && current_user_can('manage_options') ) {
		        $result = $this->display_kb_manager_role_tab( $kb_group_id );
	        } else if ( ! empty($kb_group_id) && AMGP_Access_Utilities::is_kb_group_id_in_array( $kb_group_id, $this->kb_groups ) ) {
		        $result = $this->display_user_roles_tab( $kb_group_id, $active_role);
	        } ?>
		</section>		<?php

        return $result;
	}

	/**
	 * Show KB Managers
	 *
	 * @param $kb_group_id
	 * @return bool
	 */
	private function display_kb_manager_role_tab( $kb_group_id ) {   ?>

        <h4>KB Managers administer the whole Knowledge Base outside of any group.</h4>
        <section class="amag-tabs-container amgp-user-tabs-container">
            <ul class="amag-nav-tabs">
                <li id="leaders" class="amag-active-tab">  <span class="amag-kb-prefix">KB: </span> Managers</li>
            </ul>
            <div class="amag-tab-content">
                <div class="amag-tab-panel amag-active-panel" id="subscribers-panel">			<?php
					if ( ! $this->display_role_users( $kb_group_id, AMGP_KB_Role::KB_ROLE_MANAGER ) ) {
						return false;
					}   ?>
                </div>
            </div>
        </section>  <?php

		return true;
	}

	/**
	 * Show User Roles in Tabs after user selects a specific KB Group
	 *
	 * @param $kb_group_id
	 * @param $active_role
	 * @return bool
	 */
	private function display_user_roles_tab( $kb_group_id, $active_role ) {

		$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group_id );
		if ( $is_group_public === null ) {
			return false;
		}    ?>

        <section class="amag-tabs-container amgp-user-tabs-container">
            <ul class="amag-nav-tabs">                <?php

	            $active_role = empty($active_role) ? ( $is_group_public ? 'authors' : 'subscribers' ) : $active_role;

                //Setup the Active Tab and Panel if no Post value set subscribers other wise get value and set the last active tab.
                $tab_class = array(
                   'subscribers' => '',
                   'contributors' => '',
                   'authors' => '',
                   'editors' => ''
                );
                $tab_panel_class = array(
                    'subscribers' => '',
                    'contributors' => '',
                    'authors' => '',
                    'editors' => ''
                );

                $tab_class[$active_role]       = 'class="amag-active-tab"';
                $tab_panel_class[$active_role] = ' amag-active-panel'; ?>
				<li id="subscribers" <?php echo $tab_class['subscribers'] ?>><span class="amag-kb-prefix">KB: </span> Subscribers</li>
	            <li id="contributors" <?php echo $tab_class['contributors'] ?>>  <span class="amag-kb-prefix">KB: </span>Contributors</li>
                <li id="authors"     <?php echo $tab_class['authors'] ?>>  <span class="amag-kb-prefix">KB: </span>Authors</li>
                <li id="editors"     <?php echo $tab_class['editors'] ?>>  <span class="amag-kb-prefix">KB: </span> Editors</li>
            </ul>
            <div class="amag-tab-content">
                <div class="amag-tab-panel <?php echo $tab_panel_class['subscribers'] ?>" id="subscribers-panel">            <?php
                    if ( $is_group_public ) {
                        echo 'Everyone is a subscriber to the Public Group.';
                    } else {
	                    if ( ! $this->display_role_users( $kb_group_id, AMGP_KB_Role::KB_ROLE_SUBSCRIBER ) ) {
		                    return false;
	                    }
                    }       ?>
                </div>
	            <div class="amag-tab-panel <?php echo $tab_panel_class['contributors'] ?>" id="contributors-panel">			<?php
		            if ( ! $this->display_role_users( $kb_group_id, AMGP_KB_Role::KB_ROLE_CONTRIBUTOR ) ) {
			            return false;
		            }   ?>
	            </div>
                <div class="amag-tab-panel <?php echo $tab_panel_class['authors'] ?>" id="authors-panel">			<?php
	                if ( ! $this->display_role_users( $kb_group_id, AMGP_KB_Role::KB_ROLE_AUTHOR ) ) {
		                return false;
	                }   ?>
                </div>
                <div class="amag-tab-panel <?php echo $tab_panel_class['editors'] ?>" id="editors-panel">			<?php
	                if ( ! $this->display_role_users( $kb_group_id, AMGP_KB_Role::KB_ROLE_EDITOR ) ) {
		                return false;
	                }   ?>
                </div>
            </div>
        </section>  <?php

        return true;
	}

	/**
	 * For given KB Group, display KB ROLES and for each role its WP USERS.
	 *
	 * @param $kb_group_id
	 * @param $kb_role_name
	 * @return bool
	 */
	private function display_role_users( $kb_group_id, $kb_role_name ) {

		// handle KB Managers
		if ( $kb_role_name === AMGP_KB_Role::KB_ROLE_MANAGER ) {

		    if ( ! current_user_can('manage_options') ) {
		        return false;
            }

			$kb_manager_ids = AMGP_Groups::get_kb_managers();
			if ( $kb_manager_ids === null ) {
				return false;
			}

			$group_users = array();
			foreach( $kb_manager_ids as $kb_manager_id ) {
				$tmp_user = new WP_User($kb_manager_id);
				if ( empty($tmp_user->ID) ) {
					// TODO AMGP_Logging::add_log( "Found invalid user id: ", $kb_manager_id );
					//return false;
					continue;
				}

				$group_users[] = array( 'wp_user_id' => $tmp_user->ID );
			}

		// handle other KB roles
		} else {

			$users_tmp = amgp_get_instance()->db_kb_group_users->get_group_role_users_config( $this->kb_id, $kb_group_id, $kb_role_name );
			if ( $users_tmp === null ) {
				AMGP_Logging::add_log( "Could not retrieve users for given role", $kb_group_id );
				return false;
			}

			$group_users = array();
			foreach( $users_tmp as $user_tmp ) {
				$group_users[] = array( 'wp_user_id' => $user_tmp->wp_user_id );
			}
		}

		$wp_users = $this->get_wp_users();

		// get users from mapping of WP Roles
		$wp_role_map_user_ids = AMGP_WP_Roles::get_wp_role_user_ids_for_group( $this->kb_id, $kb_group_id, $kb_role_name );
		if ( $wp_role_map_user_ids === false ) {
			return false;
		}

		// do not show mapped users if they are part of the group which takes precedence
		$group_users_id = array();
		foreach ( $group_users as $group_user ) {
			$group_users_id[] = $group_user['wp_user_id'];
		}
		foreach( $wp_role_map_user_ids as $ix => $wp_role_map_user_id ) {
			if ( in_array($wp_role_map_user_id, $group_users_id) ) {
				unset($wp_role_map_user_ids[$ix]);
			}
		}

		//Add Styling and Heading to Group list
		$manually_assigned_group_class   = '';
		$manually_assigned_group_heading = '';
		if ( defined( 'AM'.'CR_PLUGIN_NAME' ) && count( $wp_role_map_user_ids ) > 0 ) {
			$manually_assigned_group_class    = 'amgp-manually-assigned-roles-list';
			$manually_assigned_group_heading  = '<h3>Group Users</h3>';
		}		?>

        <div class="amgp-group-roles" data-kb_group_user_id="<?php echo $kb_group_id; ?>">

	        <!-- Tab Title -->
            <section class="amag-header">
                <input type="hidden" class="amgp_kb_role_name" value="<?php echo $kb_role_name; ?>"/>
            </section>

	        <!-- Group User selection -->
			<section id="amgp-control-<?php echo $kb_role_name; ?>"class="amgp-control">				<?php
				$page_number = AMGP_Utilities::get('amag_current_page_number', 1);
				$return = self::get_user_page( $this->kb_id, $kb_group_id, $kb_role_name, $page_number );
				if ( $return == false ) {
					return false;
				}   ?>
			</section>

	        <!-- List of users assigned with Groups add-on -->
	        <section class="amag-list <?php echo $manually_assigned_group_class; ?>">

		        <?php echo $manually_assigned_group_heading; ?>
		        <div class="amag-list-heading">
			        <span class="amag-list-username">Username</span>
			        <span class="amag-list-name">Name</span>
			        <span class="amag-list-email">Email</span>
			        <span class="amag-list-action">Action</span>
		        </div>                						<?php

		        // does any group have users?
		        if ( count($group_users) > 0 ) {    ?>

			        <ol>	                    <?php
				        foreach ( $group_users as $group_user ) {
					        $this->display_user_row( $wp_users, $group_user['wp_user_id'] );
				        }   ?>
			        </ol>	                 <?php

		        } else {    ?>
			        <div class="amgp-no-users-found">No users found</div>   <?php
		        }   ?>

	        </section>

			<!-- List of users auto assigned with Role mapping add-on -->	        <?php
	        if ( defined( 'AM'.'CR_PLUGIN_NAME' ) && count( $wp_role_map_user_ids ) > 0 ) {         ?>
		        <section class="amag-list amgp-mapped-roles-list">

				        <h3>Users Mapped to the Group</h3>
				        <div class="amag-list-heading">
					        <span class="amag-list-username"> Username</span >
					        <span class="amag-list-name"> Name</span >
					        <span class="amag-list-email"> Email</span >
					        <span class="amag-list-action"> Action</span >
				        </div >
				        <ol>                        <?php
					        foreach ( $wp_role_map_user_ids as $wp_user_id ) {
						        $this->display_user_row( $wp_users, $wp_user_id, false );
					        }       ?>
				        </ol>

		        </section>			<?php
	        }       ?>

        </div>    <?php

        return true;
	}

	/**
	 * Get single page of users.
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_role_name
	 * @param int $page_number
	 * @param string $user_search_filter
	 * @return bool
    */
	public static function get_user_page( $kb_id, $kb_group_id, $kb_role_name, $page_number, $user_search_filter='' ) {

		$html = new AMGP_HTML_Elements();

		// get users for given role and group
		$kb_group_user_role_wp_ids = self::get_group_users( $kb_id, $kb_group_id, $kb_role_name );
		if ( $kb_group_user_role_wp_ids === false ) {
			return false;
		}

		// get users from mapping of WP Roles
		$wp_role_map_user_ids = AMGP_WP_Roles::get_wp_role_user_ids_for_group( $kb_id, $kb_group_id, $kb_role_name );
		
		$page_number = empty($page_number) ? 1 : $page_number;

		$user_search_filter_adj = empty($user_search_filter) ? '' : '*' . $user_search_filter . '*';

		// get user total count
		$count_args  = array(
			'role__not_in' => array('Administrator'),
			'search'    => $user_search_filter_adj,
			'fields'    => 'all_with_meta',
			'number'    => 100000
		);
		$user_count_query = new WP_User_Query($count_args);
		$user_count = $user_count_query->get_results();
		$user_count = count($user_count); //$user_count < 0 ? 0 : $user_count;

		// how many users to show per page
		$users_per_page = 20;

		// calculate the total number of pages.
		$offset = $users_per_page * ($page_number - 1);

		// find users based on filter
		$args  = array(
			'role__not_in' => array('Administrator'),
			'search'    => $user_search_filter_adj,
			'orderby'   => 'display_name',
			// return all fields
			'fields'    => 'all_with_meta',
			'number'    => $users_per_page,
			'offset'    => $offset // skip the number of users that we have per page
		);

		// Create the WP_User_Query object
		$wp_user_query = new WP_User_Query($args);
		$users = $wp_user_query->get_results();		 ?>

		<div id="amgp-kb-group-search-record-container">
			<ul class="amgp-kb-group-search-record-inputs">
				<li><label>Search for User:</label></li>				<?php
				$html->text( array(
						'label'       => __( 'Keyword', 'echo-knowledge-base' ),
						'name'        => 'amgp_user_filter' . '-' . $kb_role_name,
						'type'        => AMGP_Input_Filter::TEXT,
						'value'       => $user_search_filter,
						'max'         => '50',
						'label_class' => '',
						'input_class' => ''
				) );			    ?>
				<li><?php $html->submit_button( __( 'Search', 'echo-knowledge-base' ), 'amgp_filter_users', '', '', true, '', 'primary-btn' ); ?></li>
			</ul>
			<input type="hidden" id="_wpnonce_amgp_get_user_page_ajax" name="_wpnonce_amgp_get_user_page_ajax" value="<?php echo wp_create_nonce( "_wpnonce_amgp_get_user_page_ajax" ); ?>"/>
		</div> <?php

		// check to see if we have users
		if ( ! empty($users) ) {      ?>

			<div id="amgp-kb-group-users-list">

				<input type="hidden" id="ammgp-page-number" data-page-number="<?php echo $page_number; ?>"/> <?php

				foreach ($users as $user) {

					$user_added_class       = '';
					$user_role_msg    = '';
					$user_mapped_msg        = '';
					$user_mapped            = false;

					$user_info = get_userdata($user->ID);

					// If the user is already part of the group then add class to style their profile
					if ( in_array( $user->ID, array_keys($kb_group_user_role_wp_ids) ) ) {
						$user_added_class = ' amgp-user-added ';
						$kb_role_name = $kb_group_user_role_wp_ids[$user->ID];
						$user_role_msg = '<span class="amgp-user-record-kb-manager-text">' . AMGP_KB_Role::get_kb_role_name($kb_role_name) . '</span>';
					}

					// If there mapped users, find out who they are and add a message in their profile and remove the Add button.
					if ( $wp_role_map_user_ids ) {

						//If the user is Mapped, output a message in the corner saying Mapped.
					    if ( in_array($user->ID, $wp_role_map_user_ids) ) {

					    	$user_mapped_msg = '<span class="amgp-user-record-mapped-text">Mapped</span>';
					    	$user_added_class .= ' amgp-user-mapped ';

							//Setting this true will set the condition down below to hide the add button.
							$user_mapped = true;
						}
					}
										?>

					<div class="amgp-user-record <?php echo $user_added_class; ?>">

						<div class="amgp-user-record-inner">							<?php

							echo $user_role_msg;
							echo $user_mapped_msg;

							// Only show users not already in the group
							if ( in_array( $user->ID, array_keys($kb_group_user_role_wp_ids) ) ) {
								echo '<span class="amgp-user-record-added-text">Added</span>';
							} else {
								//If user is already mapped do not show the Add button.
								if ( ! $user_mapped ) {
									$html->submit_button( __( 'Add', 'echo-knowledge-base' ), 'amgp_add_kb_group_user_ajax', 'amag-btn-wrap--plain amag-btn-wrap--float-right', '', false, '', 'primary-btn' );
								}
							}   ?>

							<div class="amgp-user-record-img"><?php echo get_avatar( $user->ID, 50 ); ?></div>
							<ul class="amgp-user-record-info">
								<li class="amgp-user-record-display-name"><strong>Display name</strong> :  <?php echo $user_info->display_name; ?></li>
								<li class="amgp-user-record-first-name">  <strong>First name</strong> :    <?php echo $user_info->first_name; ?></li>
								<li class="amgp-user-record-last-name">   <strong>Last name</strong> :     <?php echo $user_info->last_name; ?></li>
								<li class="amgp-user-record-email">       <strong>Email</strong> : <?php echo $user_info->user_email; ?></li>
							</ul>
							<input type="hidden" class="amgp-wp-user-id" value="<?php echo esc_attr( $user->ID ); ?>"/>

						</div>
					</div>				<?php
				}

			echo '</div>';

		} else {
			echo '<div class="amgp-user-record-no-results">No users found</div>';
		}

		$total_pages = ceil($user_count / $users_per_page);
		echo '<div class="amgp-user-record-pagination-container">';
			echo paginate_links( array(
				'base' => admin_url('edit.php?page=amag-access-mgr&type=amag-user-page-number'),
				'format' => '&p=%#%', // this defines the query parameter that will be used, in this case "p"
				'prev_text' => __('Previous'), // text for previous page
				'next_text' => __('Next'), // text for next page
				'total' => $total_pages, // the total number of pages we have
				'current' => $page_number, // the current page
				'end_size' => 1,
				'mid_size' => 5,
				'type' => 'list'
			));
		echo '</div>';
		return true;
	}

	/**
     * Get users for given KB, group and role
	 * @param $kb_id
	 * @param $kb_group_id
	 * @param $kb_role_name
     *
     * @return array|bool
    */
	static function get_group_users( $kb_id, $kb_group_id, $kb_role_name ) {

		// handle KB Managers
		$kb_role_user_wp_ids = array();
		if ( $kb_role_name === AMGP_KB_Role::KB_ROLE_MANAGER ) {

		    if ( ! current_user_can('manage_options') ) {
		        return false;
            }

			$kb_manager_ids = AMGP_Groups::get_kb_managers();
			if ( $kb_manager_ids === null ) {
				return false;
			}

			foreach( $kb_manager_ids as $kb_manager_id ) {
				$tmp_user = new WP_User($kb_manager_id);
				if ( empty($tmp_user->ID) ) {
					// TODO AMGP_Logging::add_log( "Found invalid user id: ", $kb_manager_id );
					//return false;
					continue;
				}

				$kb_role_user_wp_ids[$tmp_user->ID] = AMGP_KB_Role::KB_ROLE_MANAGER;
			}

		// handle other KB roles
		} else {

			// get all users for this KB Group to ensure that newly added user does not have KB Role already
			$kb_role_user_wp_ids = self::get_group_user_ids( $kb_id, $kb_group_id );
			if ( $kb_role_user_wp_ids === false ) {
				return false;
			}
		}

		return $kb_role_user_wp_ids;
	}

	private function display_user_row( $wp_users, $wp_user_id, $remove_on=true ) {
		/** @var WP_User $wp_user */
		$wp_user = empty( $wp_users[ $wp_user_id ] ) ? null : $wp_users[ $wp_user_id ];
		$user_name = empty( $wp_user ) || empty( $wp_user->user_nicename ) ? '<unknown>' : $wp_user->user_nicename;
		$name = empty( $wp_user ) || empty( $wp_user->display_name ) ? '<unknown>' : $wp_user->display_name;
		$email = empty( $wp_user ) || empty( $wp_user->user_email ) ? '<unknown>' : $wp_user->user_email; ?>
		<li>
			<span class="amag-list-username"><?php echo esc_html( $user_name ); ?></span>
			<span class="amag-list-name"><?php echo esc_html( $name ); ?></span>
			<span class="amag-list-email"><?php echo esc_html( $email ); ?></span>      <?php

			if ( $remove_on ) {				?>
				<span class="amag-list-action">
			        <input type="hidden" class="amgp-wp-user-id" value="<?php echo esc_attr( $wp_user_id ); ?>"/> <?php
						$this->html->submit_button( __( 'Remove', 'echo-knowledge-base' ), 'amgp_remove_kb_group_user_ajax', 'amag-btn-wrap--plain', '', true, '', 'amgp_remove_kb_group_user_ajax amag-remove-user' );
						?>
		        </span>     <?php
			}				?>
		</li>                        <?php
	}

	/**
	 * Get KB Group users from both AMGP Config and AMGP KB Group Users table
	 *
	 * @param $kb_id
	 * @param $kb_group_id
	 *
	 * @return array|bool
	*/
	static function get_group_user_ids( $kb_id, $kb_group_id ) {

		// 1. get KB Managers
		$kb_managers_ids = AMGP_Groups::get_kb_managers();
		if ( $kb_managers_ids === null ) {
			return false;
		}

		$kb_role_user_wp_ids = array();
		foreach( $kb_managers_ids as $kb_managers_id ) {
			$kb_role_user_wp_ids[$kb_managers_id] = AMGP_KB_Role::KB_ROLE_MANAGER;
		}

		// 2. get all other KB Role users already part of this KB Group
		$wp_users = amgp_get_instance()->db_kb_group_users->get_group_users_config( $kb_id, $kb_group_id );
		if ( $wp_users === null ) {
			AMGP_Logging::add_log( "Could not retrieve user IDs for given role", $kb_group_id );
			return false;
		}

		foreach( $wp_users as $wp_user ) {
			$kb_role_user_wp_ids[$wp_user->wp_user_id] = $wp_user->kb_role_name;
		}

		return $kb_role_user_wp_ids;
	}

	/**
	 * Get WP Users except administrators.
	 * @return array
	 */
	private function get_wp_users() {
		$args = array(
			'role__not_in' => array('Administrator'),
			'orderby'      => 'login',
			'order'        => 'ASC',
			'count_total'  => false,
			'number'       => 100000,
		);

		$wp_users = get_users( $args );
		$wp_user_names = array();
		foreach( $wp_users as $wp_user ) {
			$wp_user_names[$wp_user->ID] = $wp_user->data;
		}

		return $wp_user_names;
	}
}