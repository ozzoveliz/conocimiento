<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display Access Manager page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGR_Access_Page {

	var $kb_config = array();

	public function __construct() {

		// ensure user has correct permissions
		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			wp_die( esc_html__( 'You do not have permission to change access', 'echo-knowledge-base' ), 403 );
		}

		add_filter( 'eckb_hide_kb_tabs', array( $this, 'hide_kb_tabs' ) );
		add_action( 'kb_overview_add_on_errors', array( $this, 'log_errors' ) );
	}

    public function hide_kb_tabs( $screen_id ) {
        return $screen_id == 'EKB_SCREEN_page_amag-access-mgr' ? true: $screen_id;
    }

	/**
	 * Display the Access Manager page with all permission configuration.
	 */
	public function display_access_manager_page() {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    return;
	    }

	    if ( defined( 'EPKB_PLUGIN_NAME' ) ) {
		    AMGR_Access_Utilities::output_inline_error_notice( 'Please uninstall Knowledge Base for Documents and FAQs and re-activate this Access Manager plugin. Error (110)' );
		    return;
	    }

		// true if the plugin was not properly activated
		$amgr_plugin_version = EPKB_Utilities::get_wp_option( 'amag_version', null );
		if ( empty($amgr_plugin_version) ) {
			AMGR_Access_Utilities::output_inline_error_notice( 'We noticed that your installation steps are not complete. Pleae deactivate and then active this Access Manager plugin. Error (111)' );
		   return;
		}

		// retrieve current KB configuration
		$post_type = EPKB_Utilities::get( 'post_type' );
		$kb_id = EPKB_KB_Handler::get_kb_id_from_post_type( $post_type );
		if ( is_wp_error( $kb_id ) ) {
			AMGR_Logging::add_log('Could not retrieve KB ID.');
			EPKB_HTML_Forms::notification_box_bottom('Could not retrieve KB ID', '', 'error');
			return;
		}

		/* get KB Core configuration */
		$this->kb_config = epkb_get_instance()->kb_config_obj->get_kb_config( $kb_id );
		if ( is_wp_error( $this->kb_config ) ) {
			AMGR_Logging::add_log('Could not retrieve KB configuration.');
			EPKB_HTML_Forms::notification_box_bottom('Could not retrieve KB configuration', '', 'error');
			return;
		}

		ob_start(); ?>

		<div id="amag-access-manager-container">

		   <div id="ekb-admin-page-wrap" class="ekb-admin-page-wrap">

		       <div class="wrap" id="ekb_core_top_heading"></div>
		       <div class="eckb-top-notice-message"></div>
		       <h1><?php esc_html_e( 'Access Manager', 'echo-knowledge-base' ); ?></h1>

		       <div id="amgr-config-main-nav">

		           <!-- KB Drop down -->
		           <div class="amgr-nav-section amgr-kb-name-section">     <?php
		               $this->display_list_of_kbs();   ?>
		           </div>

		           <!-- KB Access Status -->
		           <div class="amgr-nav-section amgr-access-status">                        <?php

		              $kb_access_level = AMGR_Access_Utilities::determine_kb_access_level( $kb_id );
		              $icon = '';
		              $class = '';
		               if ( $kb_access_level === AMGR_Access_Utilities::AMGR_RESTRICTED_ACCESS_LEVEL ) {
		                  $icon       = 'epkbfa-lock';
		                  $level_name = 'RESTRICTED';
		                  $class      = 'amgr-restricted-access';
		               } else if ( $kb_access_level == AMGR_Access_Utilities::AMGR_PUBLIC_ACCESS_LEVEL ) {
		                   $icon           = 'epkbfa-unlock';
		                   $level_name    = 'PUBLIC';
		                   $class          = 'amgr-public-access';
		               } else if ( $kb_access_level == AMGR_Access_Utilities::AMGR_MIXED_ACCESS_LEVEL ) {
		                  $icon           = 'epkbfa-unlock-alt';
		                  $level_name    = 'MIXED';
		                  $class          = 'amgr-mixed-access';
		               } else {
		                  $level_name    = 'Error occurred';
		               }                       ?>
		               <div class="amgr-access-status-icon epkbfa <?php echo esc_attr( $icon . ' ' . $class ); ?>"></div><span><?php echo esc_html( $level_name ); ?></span>

		           </div>

		           <div class="amgr-nav-buttons-group">

		               <!-- KB -->
		               <div id="amgr-KB" class="amgr-nav-section amag-nav-button amgr-active-nav">
		                   <div class="amgr-nav-icon-container">
		                       <p>KB</p>
		                       <div class="amgr-nav-icon dashicons dashicons-welcome-learn-more" ></div>
		                   </div>
		               </div>
		               <!-- Categories -->
		               <div id="amgr-categories" class="amgr-nav-section amag-nav-button">
		                   <div class="amgr-nav-icon-container">
		                       <p>Access to Categories</p>
		                       <div class="amgr-nav-icon epkbfa epkbfa-folder-o"></div>
		                   </div>
		               </div>
		              <!-- Articles -->
		              <div id="amgr-articles" class="amgr-nav-section amag-nav-button">
		                 <div class="amgr-nav-icon-container">
		                    <p>Access to Articles</p>
		                    <div class="amgr-nav-icon epkbfa epkbfa-file-text-o"></div>
		                 </div>
		              </div>
		              <!-- WP Role Mappings -->     <?php
		              if ( defined('AM'.'CR_PLUGIN_NAME') ) {    ?>
		                 <div id="amag-wp-roles" class="amgr-nav-section amag-nav-button">
		                    <div class="amgr-nav-icon-container">
			                    <p>Role Mappings</p>
			                    <div class="amgr-nav-icon epkbfa epkbfa-map-signs"></div>
		                    </div>
		                 </div>  <?php
		              }     ?>

		              <!-- KB Groups plugin -->     <?php
		              if ( defined('AM'.'GP_PLUGIN_NAME') ) {    ?>
		                 <div id="amag-groups" class="amgr-nav-section amag-nav-button">
		                     <div class="amgr-nav-icon-container">
		                         <p>Groups</p>
		                         <div class="amgr-nav-icon epkbfa epkbfa-users"></div>
		                     </div>
		                 </div>
		                 <div id="amag-users" class="amgr-nav-section amag-nav-button">
		                    <div class="amgr-nav-icon-container">
			                    <p>Users</p>
			                    <div class="amgr-nav-icon epkbfa epkbfa-address-book"></div>
		                    </div>
		                 </div>  <?php
		              }     ?>

		               <!-- Configuration -->
		               <div id="amgr-configuration" class="amgr-nav-section amag-nav-button">
						<div class="amgr-nav-icon-container">
							<p>Configuration</p>
							<div class="amgr-nav-icon ep_font_icon_gear" ></div>
						</div>
					</div>

		           </div>

		       </div>
		       <div id="amgr-config-container">

		           <!-- KB Content -->
		           <div class="amag-config-content amag-active-content" id="amgr-KB-content"> <?php
		               $kb_section = new AMGR_Access_Page_View_KB( $this->kb_config['id'] );
		               $kb_section->show_kb_section( $this->kb_config['id'] );          ?>
		           </div>

		           <!-- Categories Content -->
		           <div  class="amag-config-content" id="amgr-categories-content"></div>

		          <!-- Articles Content -->
		          <div  class="amag-config-content" id="amgr-articles-content"></div>

		          <!-- WP Custom Roles Content -->     <?php
		          if ( defined('AM'.'CR_PLUGIN_NAME') ) {
		             do_action( 'eckb_wp_roles_tab_content' );
		          }           ?>

		          <!-- KB Groups and Users -->      <?php
		          if ( AMGR_WP_ROLES::use_kb_groups() ) {
		             do_action( 'eckb_kb_groups_tab_content' );
		             do_action( 'eckb_kb_users_tab_content' );
		          }		    ?>

		           <!-- Configuration Content -->
		           <div  class="amag-config-content" id="amgr-configuration-content">
		           </div>

		       </div>

		      <form>
		         <input type="hidden"  id="amag_kb_id" value="<?php echo esc_attr( $this->kb_config['id'] ); ?>"/>
		         <input type="hidden" id="_wpnonce_amar_access_content_action_ajax" name="_wpnonce_amar_access_content_action_ajax"
		                value="<?php echo wp_create_nonce( "_wpnonce_amar_access_content_action_ajax" ); ?>"/>
		      </form>

		      <div class="eckb-bottom-notice-message"></div>

		   </div>
		</div>        <?php

		echo ob_get_clean();
	}

	/**
	 * Show errors if any at the top of Access Manager page
	 */
	public function log_errors() {

		$html = new EPKB_HTML_Elements();

		$add_on_logs = apply_filters( 'amag_get_error_logs', array() );
		$add_on_logs = is_array($add_on_logs) ? $add_on_logs : array();

        $logs = array_merge(AMGR_Logging::get_logs(), EPKB_Logging::get_logs());
		$logs = array_merge($logs, $add_on_logs);
		if ( empty($logs) ) {
		    return;
        }     ?>

        <div class="callout callout_error">
            <h4><?php
                $html->submit_button_v2( esc_html__( 'Clear Logs', 'echo-knowledge-base' ), 'amgr_reset_logs_ajax', '', '', true, '', 'amag-primary-btn' ); ?>
            </h4>			<?php

			$ix = 0;
			foreach( $logs as $log ) {
				if ( ! empty($log['plugin']) && $log['plugin'] === AMAG_PLUGIN_NAME ) {
					echo $ix++ > 0 ? '' : '<p><strong>Access Manager encountered critical errors:</strong></p>';
					echo '<div>' . esc_html( $log['date'] . ' - ' . $log['message'] . ' (' . $log['kb'] . ')' ) . '</div>';
				}
			}			?>
        </div>	<?php
    }

	/**
	 * Should list of KBs at the top
	 */
	private function display_list_of_kbs() {

		if ( ! defined('EM' . 'KB_PLUGIN_NAME') ) {
			$kb_name = epkb_get_instance()->kb_config_obj->get_value( $this->kb_config['id'], 'kb_name', '<unknown>' );
			echo '<h1 class="amgr-kb-name">' . esc_html( $kb_name ) . '</h1>';
			return;
		}

		// output the list
		$list_output = '<select class="amgr-kb-name" id="amgr-list-of-kbs">';
		$all_kb_configs = epkb_get_instance()->kb_config_obj->get_kb_configs();
		foreach ( $all_kb_configs as $kb_id => $one_kb_config ) {

		   $kb_status = epkb_get_instance()->kb_config_obj->get_value( $kb_id, 'status' );
			if ( $kb_id !== AMGR_KB_Access_Config_DB::DEFAULT_KB_ID && EPKB_Core_Utilities::is_kb_archived($kb_status ) ) {
				continue;
			}

			$kb_name = epkb_get_instance()->kb_config_obj->get_value( $kb_id, 'kb_name', '<unknown>' );
			$active = ( $this->kb_config['id'] == $kb_id ? 'selected' : '' );
			$tab_url = 'edit.php?post_type=' . EPKB_KB_Handler::KB_POST_TYPE_PREFIX . $kb_id . '&page=amag-access-mgr';

			$list_output .= '<option value="' . esc_attr( $kb_id ) . '" ' . esc_attr( $active ) . ' data-kb-admin-url=' . esc_url( $tab_url ) . '>' . esc_html( $kb_name ) . '</option>';
			$list_output .= '</a>';
		}

		$list_output .= '</select>';

		echo $list_output;  //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

