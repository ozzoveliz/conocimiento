<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display mappping of WP Roles to KB Roles.
 */
class AMCR_Access_Page_View_WP_Roles {

	private $kb_id;
	private $wp_roles_mappings = array();

	const KB_ROLES = array(
							'kb_role_subscriber'    => 'KB Subscriber',
							'kb_role_contributor'   => 'KB Contributor',
							'kb_role_author'        => 'KB Author',
							'kb_role_editor'        => 'KB Editor'
						  );

	public function __construct() {
		add_action( 'eckb_wp_roles_tab_content', array($this, 'show_roles_section') );
	}

	/**
	 * Display WP Roles Mapping section.
	 */
    public function show_roles_section() { ?>
	    <!-- KB Groups Content -->
	    <div class="amag-config-content" id="amcr-wp-roles-content"></div>    <?php
    }

	/**
	 * Called by AJAX after user makes changes.
	 *
	 * @param $kb_id
	 * @return bool
	 */
	public function ajax_update_tab_content( $kb_id ) {

		$html = new AMCR_HTML_Elements();
		$this->kb_id = $kb_id;

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			AMCR_Utilities::output_inline_error_notice(__( 'You do not have permission (E44).', 'echo-knowledge-base' ) );
			return false;
		}

	    // retrieve WP Role mapping
		$this->wp_roles_mappings = AMCR_KB_Core::get_wp_roles_mappings_for_kb( $kb_id );
		if ( is_wp_error($this->wp_roles_mappings) ) {
			AMCR_Logging::add_log( 'Error retrieving WP Role mapping', $this->wp_roles_mappings );
			AMCR_Utilities::output_inline_error_notice( 'Internal Error occurred (b243)' );
			return false;
		}

		$kb_groups = array();
		if ( AMCR_KB_Core::use_kb_groups() ) {

			$kb_groups = AMCR_KB_Core::get_kb_groups( $this->kb_id );
			if ( $kb_groups === null ) {
				AMCR_Logging::add_log( "Could not retrieve KB Groups", $this->kb_id );
				AMCR_Utilities::output_inline_error_notice( 'Internal Error occurred (a303)' );
				return false;
			} else if ( empty($kb_groups) ) {
				AMCR_Utilities::output_inline_error_notice( esc_html__( 'No KB Group defined. First create a KB Group.', 'echo-knowledge-base' ) );
				return true;
			}
		}     ?>

		<div id="amag-wp-role-maps-content" class="amcr-access-wp-roles-content-container">
			<section class="amag-page-header">
				<h2>WP Role Mappings</h2>
				<div class="amcr-add-wp-role-map-container">
					<div id="amcr-add-wp-role-map-input" class="amcr-wp-role-map-input">

						<div class="amcr-wp-role-map-input-options">
							<div class="amcr-role-name-list">
								<label>WP Role Name: </label>
								<select id="create-wp-role-name" class="amcr-wp-role-name">		<?php
									foreach( get_editable_roles() as $role_name => $role_info ) {
										if ( $role_name == 'administrator' ) {
											continue;
										}   ?>
										<option value="<?php echo esc_attr( $role_name ); ?>"><?php esc_html_e( $role_info['name'] ); ?></option>		<?php
									}       ?>
								</select>
							</div>		<?php

							if ( AMCR_KB_Core::use_kb_groups() ) {

								$kb_group_list = array();
								foreach ( $kb_groups as $kb_group ) {
									$kb_group_list[$kb_group->kb_group_id] = $kb_group->name;
								}

								$html->dropdown( array(
									'name'          => 'create-kb-group-id',
									'label'         => 'KB Group: ',
									'input_class'   => 'amcr-kb-group-id',
									'options'       => $kb_group_list
								) );
							}

							$html->dropdown( array(
								'name'          => 'create-kb-role-name',
								'label'         => 'KB Role Name: ',
								'input_class'   => 'amcr-kb-role-name',
								'options'       => self::KB_ROLES
							) );

							$html->submit_button_v2( esc_html__( 'Create', 'echo-knowledge-base' ), 'amcr_add_wp_role_map_ajax', 'amag-btn-wrap--plain', '', true, '', 'amag-success-btn' );								?>

						</div>
					</div>
				</div>
			</section>
		</div>  <?php


		if ( empty($this->wp_roles_mappings) ) {

			EPKB_HTML_Forms::notification_box_middle( array(
				'type' => 'info',
				'desc' => '<p>' . sprintf( esc_html__( 'No WP Roles have been mapped.', 'echo-knowledge-base' ).'</p>'
			) ) );
			?>
              <?php
			return true;
		}

		// prepare data
		$this->kb_id = $kb_id;  ?>

		<div class="amcr-access-wp-roles-content-container">    <?php
			 $this->display_wp_roles_content();    ?>
		</div>  <?php

		return true;
	}

	/**
	 * Show WP Role Mapping tab content
	 *
	 * @return bool
	 */
	private function display_wp_roles_content() {	?>

        <section class="amag-list">
            <div class="amag-list-heading">
                <span class="amag-list-name">WP Role</span>     <?php
	            if ( AMCR_KB_Core::use_kb_groups() ) { ?>
		            <span class="amag-list-name">KB Group</span>    <?php
	            }           ?>
	            <span class="amag-list-name">KB Role</span>
                <span class="amag-list-action">Action</span>
            </div>  <?php
            if ( ! $this->display_wp_roles_list() ) {
                return false;
            }  ?>
        </section>

		<input type="hidden" id="_wpnonce_amcr_wp_role_ajax" name="_wpnonce_amcr_wp_role_ajax" value="<?php echo wp_create_nonce( "_wpnonce_amcr_wp_role_ajax" ); ?>"/> <?php

		return true;
	}

	/**
	 * Display list of WP Role mapping.
	 *
	 * @return bool
	 */
	private function display_wp_roles_list() {

		// retrieve KB Group names if any
		$kb_group_names = array();
		if ( AMCR_KB_Core::use_kb_groups() ) {
			$kb_groups = AMCR_KB_Core::get_kb_groups( $this->kb_id );
			if ( $kb_groups === null ) {
				AMCR_Logging::add_log( "Could not retrieve KB Groups", $this->kb_id );
				AMCR_Utilities::output_inline_error_notice( 'Internal Error occurred (a303)' );
			}

			// get current KB Group names
			$kb_group_names = array();
			foreach ( $kb_groups as $kb_group ) {
				$kb_group_names[$kb_group->kb_group_id] = $kb_group->name;
			}
		}    ?>

		<ol>    <?php

		// display each mapping
		$wp_roles_mappings = $this->wp_roles_mappings;
		foreach( $wp_roles_mappings as $wp_role => $data ) {
			foreach( $data as $kb_group_id => $kb_role_name ) {
				$kb_roles = self::KB_ROLES;				?>
				<li>
					<span class="amag-list-name amcr_wp_role_name"><?php echo esc_html($wp_role); ?></span>    <?php
					if ( AMCR_KB_Core::use_kb_groups() ) {
						$kb_group_name = empty($kb_group_names[$kb_group_id]) ? ' --- ' : $kb_group_names[$kb_group_id];  ?>
						<span class="amag-list-name amcr_kb_group_name"><?php echo esc_html($kb_group_name); ?></span>
						<input type="hidden" class="amcr_kb_group_id" name="amcr_kb_group_id" value="<?php esc_attr_e( $kb_group_id ); ?>"/>  <?php
					}           ?>
					<span class="amag-list-name amcr_kb_role_name"><?php echo esc_html($kb_roles[$kb_role_name]); ?></span>
					<span class="amag-list-action">
					<button id="amcr-delete-wp-role-map" class="amag-error-btn">Delete</button>
					</span>
				</li>            <?php
			}
		}       ?>

		</ol>   <?php

		return true;
	}
}