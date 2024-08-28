<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display view of KB Groups access controls.
 */
class AMGP_Access_Page_View_Groups {

	private $html;
	private $kb_id;
	private $kb_groups;

	public function __construct() {

		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::output_inline_error_notice(__( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E12)');
			return;
		}

		add_action( 'eckb_kb_groups_tab_content', array($this, 'show_group_section') );
	}

	/**
	 * Display GROUPS section.
	 */
    public function show_group_section() { ?>
	    <!-- KB Groups Content -->
	    <div class="amag-config-content" id="amgp-kb-groups-content"></div>    <?php
    }

	/**
	 * Called by AJAX after user makes changes.
	 *
	 * @param $kb_id
	 * @return bool
	 */
	public function ajax_update_tab_content( $kb_id ) {

		$this->html = new AMGP_HTML_Elements();
		$this->kb_id = $kb_id;

		if ( ! current_user_can('admin_eckb_access_manager_page') || ! current_user_can('admin_eckb_access_crud_groups') ) {
			AMGP_Utilities::ajax_show_error_die(__( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E49)');
		}

		$this->kb_groups = amgp_get_instance()->db_kb_groups->get_private_groups( $this->kb_id );
		if ( $this->kb_groups === null ) {
			AMGP_Logging::add_log( "Could not retrieve KB Groups", $this->kb_id );
			AMGP_Utilities::output_inline_error_notice( 'Internal Error occurred (a303)' );
			return false;
		}

		$this->display_groups_list();

	    return true;
    }

	/**
	 * Display drop-down list of existing groups and Add/Remove/Update buttons.
	 * @return bool
	 */
	private function display_groups_list() {    ?>

		<section class="amag-page-header">
			<h2>Groups</h2>
			<div class="amgp-add-group-container">
				<button id="amgp-add-group"    class="amag-success-btn">Add New Group</button>
				<div id="amgp-add-group-input" class="amgp-group-input">				    <?php
					$this->html->text_basic( array(
						'name'          => 'create-group-name',
						'label'         => 'Group Name: ',
						'input_class'   => 'amgp-group-name' ) );
					$this->html->submit_button( __( 'Create', 'echo-knowledge-base' ), 'amgp_add_kb_group_ajax', 'amag-btn-wrap--plain', '', true, '', 'amag-success-btn' );
					?>
				</div>
			</div>
		</section>

		<section class="amag-list">
			<div class="amag-list-heading">
				<span class="amag-list-name">Name</span>
				<span class="amag-list-action amgp-list-action">Action</span>
			</div>
			<ol>    <?php

				foreach( $this->kb_groups as $kb_group ) {    ?>
					<li>
						<span class="amag-list-name"><?php echo esc_html($kb_group->name); ?></span>    <?php

						$is_group_public = amgp_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group->kb_group_id );

						if ( $is_group_public === false ) {  ?>
							<span class="amag-list-action amgp-list-action">
                            <button class="amag-success-btn amag-rename-group-toggle">Rename</button>
                            <div class="amgp-rename-group-input">                             <?php
	                            $this->html->text_basic( array(
		                            'name'        => 'rename-group-name' . $kb_group->kb_group_id,
		                            'label'       => 'New Name: ',
		                            'input_class' => 'amgp-new-name'
	                            ) );
	                            $this->html->submit_button( __( 'Save', 'echo-knowledge-base' ), 'amgp_rename_kb_group_ajax' . $kb_group->kb_group_id, 'amag-btn-wrap--plain', '', true, '', 'amag-success-btn' );
	                            ?>
                            </div>
                            <input type="hidden" class="amgp_kb_group_id" name="amgp_kb_group_id" value="<?php echo esc_attr($kb_group->kb_group_id); ?>" />
                            <input type="hidden" class="amgp_kb_group_name" name="amgp_kb_group_name" value="<?php echo esc_attr($kb_group->name) ?>" />
                            <button id="amgp-delete-kb-group<?php echo $kb_group->kb_group_id; ?>" class="amag-error-btn">Delete</button>
                        </span>     <?php
						}                    ?>

					</li>            <?php
				}   ?>
			</ol>
		</section>


		<?php

		return true;
	}
}