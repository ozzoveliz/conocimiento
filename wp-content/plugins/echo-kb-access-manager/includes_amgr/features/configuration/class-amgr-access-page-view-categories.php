<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display view of KB Categories access controls.
 */
class AMGR_Access_Page_View_Categories {

	private $html;
	private $kb_id;
	private $kb_groups;
	private $full_access_categories_ids;
	private $read_only_categories_ids;

	public function __construct( $kb_id ) {
		$this->html = new EPKB_HTML_Elements();
		$this->kb_id = $kb_id;
	}

	/**
	 * Called by AJAX after user makes changes.
	 *
	 * @param $kb_group_id
	 * @return bool
	 */
	public function ajax_update_tab_content( $kb_group_id ) {

		if ( ! current_user_can('admin_eckb_access_manager_page') ) {
			EPKB_Utilities::ajax_show_error_die( __( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E46)' );
		}

		$kb_groups = epkb_get_instance()->db_kb_groups->get_groups( $this->kb_id );
		if ( $kb_groups === null ) {
			AMGR_Logging::add_log( "Could not get KB Groups", $this->kb_id );
			AMGR_Access_Utilities::output_inline_error_notice( 'Internal Error occurred (a243)' );
			return false;
		}   ?>

		<section class="amag-page-header">
            <h2><?php esc_html_e( 'Group Access to Categories', 'echo-knowledge-base' ); ?></h2>
        </section>      <?php

		if ( empty($kb_groups) ) {  ?>
            <div class="callout callout_error">
                <h4><?php esc_html_e( 'No KB Groups have been created', 'echo-knowledge-base' ); ?></h4>
                <p><?php esc_html_e( 'First create your group(s).', 'echo-knowledge-base' ); ?></p>
            </div>     <?php
			return true;
		}

		// prepare data
		$this->kb_groups = $kb_groups;  ?>

		<div class="amgr-access-categories-content-container">    <?php
			 $this->display_categories_access( $kb_group_id );    ?>
		</div>  <?php

		return true;
	}

	/**
	 * Show Full KB level access
	 *
	 * @param string $kb_group_id
	 * @return bool
	 */
	private function display_categories_access( $kb_group_id ) {     ?>

        <p><?php esc_html_e( 'Choose your group, then click on Categories they have access to.', 'echo-knowledge-base' ); ?></p>

        <label><?php esc_html_e( 'Group:', 'echo-knowledge-base' ); ?></label>
        <select id="amgr-access-tabs-kb-group-list">
            <option value="0"><?php esc_html_e( 'Choose a Group', 'echo-knowledge-base' ); ?></option>   <?php
			foreach( $this->kb_groups as $kb_group ) {
				$is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group->kb_group_id );
				if ( $is_group_public === null ) {
					AMGR_Logging::add_log( "Could not verify Public Group", $this->kb_id );
					return false;
				}

				if ( $is_group_public || AMGR_WP_Roles::use_kb_groups() ) {
					echo '<option ' . ( $kb_group->kb_group_id == $kb_group_id ? 'selected' : '' ) . ' value="' . esc_attr( $kb_group->kb_group_id ) . '">' . esc_html( $kb_group->name ) . '</option>';
				}
			}                ?>
        </select>   <?php

		if ( empty($kb_group_id) || ! AMGR_Access_Utilities::is_kb_group_id_in_array( $kb_group_id, $this->kb_groups ) ) {     ?>
            <div id="amgr-categories-checkboxes-content"></div>     <?php
			return true;
		}   ?>

        <div class="amgr-content-categories">

            <!-- Display Controls -->
            <div class="amgr-category-controls">

                <fieldset>
                    <legend>1. Choose Access Level</legend>
                    <!-- Edit Mode -->
                    <div class="amgr-edit-mode-container">
                        <ul>
                            <li class="amgr-edit-btn-no-access amgr-edit-active-no">
                                <input type="radio" name="amgr-edit-mode" checked id="amgr-no-access" value="no">
                                <label for="amgr-no-access">No Access</label>
                            </li>
                            <li class="amgr-edit-btn-read-only-access">
                                <input type="radio" name="amgr-edit-mode" id="amgr-ready-only" value="read">
                                <label for="amgr-ready-only">Read Only</label>
                            </li>  <?php
	                        $is_group_public = epkb_get_instance()->db_kb_public_groups->is_public_group( $this->kb_id, $kb_group_id );



	                        if ( AMGR_WP_ROLES::use_kb_groups() ) {
		                        if ( ! $is_group_public ) {             ?>
			                        <li class="amgr-edit-btn-full-access">
				                        <input type="radio" name="amgr-edit-mode" id="amgr-full-access" value="full">
				                        <label for="amgr-full-access">Full Access</label>
			                        </li>                            <?php
		                        }
	                        }       ?>
                        </ul>
	                    <?php
	                    if ( $is_group_public ) {
	                    	echo '<p class="amgr-group-access-message">Only KB Managers and WP Admin can control Public access to KB Categories and Articles. For more information click ' .
	                    	'<a target="_blank" class="thickbox" href="https://www.echoknowledgebase.com/documentation/2-2-public-vs-protected-access/">here.</a><p>';
	                    } ?>

                    </div>
                </fieldset>

            </div>

	        <!-- Display Categories -->     <?php
	        $isSuccess = $this->display_hierarchy_of_categories( $kb_group_id ); ?>

        </div>

        <input type="hidden" class="amgr_kb_group_id" name="amgr_kb_group_id" value="<?php echo esc_attr($kb_group_id); ?>" />   <?php

		$this->html->submit_button_v2( __( 'Save', 'echo-knowledge-base' ), 'amgr_save_categories_access_ajax', 'epkb-btn-wrap--plain', '', true, '', 'amag-primary-btn' );

		return $isSuccess;
	}

	/**
	 * Display list of all Categories for selected Groups.
	 *
	 * @param $kb_group_id
	 * @return bool
	 */
	private function display_hierarchy_of_categories( $kb_group_id ) {

		// retrieve current KB and KB Group category access
		$this->full_access_categories_ids = AMGR_Access_Utilities::get_group_categories_ids( $this->kb_id, $kb_group_id );
		if ( $this->full_access_categories_ids === null ) {
			return false;
		}

		// get read-only group categories
		$this->read_only_categories_ids = epkb_get_instance()->db_access_read_only_categories->get_group_read_only_categories_ids( $this->kb_id, $kb_group_id );
		if ( $this->read_only_categories_ids === null ) {
			return false;
		}

		// retrieve all KB categories
		$categories = EPKB_Core_Utilities::get_kb_categories_unfiltered( $this->kb_id );
		if ( $categories === null ) {
			return false;
		}   ?>


		<div id="amgr-category-access-levels">
			<span class="amgr-category-access-heading">
				<span class="amgr-category-access-heading-title">
				2. Update Category Access
				</span>
				<span class="amgr-category-access-heading-legend">
					<span class="amgr-public-access-icon">P</span> - categories accessible to general public.
				</span>

			</span>

			<ul  class="amgr-level-1">                <?php

				// display all categories
				foreach ( $categories as $l1_category ) {

					// first get top-level categories
					$l1_category_id = $l1_category->term_id;
					if ( $l1_category->parent > 0 ) {
						continue;
					}       ?>

					<!-- DISPLAY LEVEL 1 -->
					<li id="<?php echo $l1_category_id; ?>" class="<?php echo $this->get_category_access_level( $l1_category_id ); ?>">                    <?php

						$this->display_category_level_info( 1, $l1_category, $l1_category_id );
						foreach ( $categories as $l2_category ) {

							// get 2nd level categories
							$l2_category_id = $l2_category->term_id;
							if ( $l2_category->parent != $l1_category_id ) {
								continue;
							}   ?>

							<!-- DISPLAY LEVEL 2 -->
							<ul class="amgr-level-2">

								<li id="<?php echo $l2_category_id; ?>"  class="<?php echo $this->get_category_access_level( $l2_category_id ); ?>">                                <?php
									$this->display_category_level_info( 2, $l2_category , $l2_category_id , $l1_category_id );
									foreach ( $categories as $l3_category ) {

										// get 3rd level categories
										$l3_category_id = $l3_category->term_id;
										if ( $l3_category->parent != $l2_category_id ) {
											continue;
										}                                    ?>

										<!-- DISPLAY LEVEL 3 -->
										<ul class="amgr-level-3">
											<li id="<?php echo $l3_category_id; ?> " class="<?php echo $this->get_category_access_level( $l3_category_id ); ?>">	            <?php
												$this->display_category_level_info( 3, $l3_category, $l3_category_id, $l1_category_id, $l2_category_id ); 
												foreach ( $categories as $l4_category ) {

													// get 4rd level categories
													$l4_category_id = $l4_category->term_id;
													if ( $l4_category->parent != $l3_category_id ) {
														continue;
													}                                    ?>

													<!-- DISPLAY LEVEL 4 -->
													<ul class="amgr-level-4">
														<li id="<?php echo $l4_category_id; ?> " class="<?php echo $this->get_category_access_level( $l4_category_id ); ?>">	            <?php
															$this->display_category_level_info( 4, $l4_category, $l4_category_id, $l1_category_id, $l2_category_id, $l3_category_id ); 
															
															foreach ( $categories as $l5_category ) {

																// get 5rd level categories
																$l5_category_id = $l5_category->term_id;
																if ( $l5_category->parent != $l4_category_id ) {
																	continue;
																}                                    ?>

																<!-- DISPLAY LEVEL 5 -->
																<ul class="amgr-level-5">
																	<li id="<?php echo $l5_category_id; ?> " class="<?php echo $this->get_category_access_level( $l5_category_id ); ?>">	            <?php
																		$this->display_category_level_info( 5, $l5_category, $l5_category_id, $l1_category_id, $l2_category_id, $l3_category_id, $l4_category_id ); ?>
																	</li>
																</ul>   <?php
															}    ?>
														</li>
													</ul>   <?php
												}    ?>
											</li>
										</ul>   <?php
									}    ?>
								</li>
							</ul>           <?php
						}   ?>
					</li>          <?php
				}   ?>
			</ul>
		</div>



		<?php

		return true;
	}

	private function display_category_level_info( $level, $category, $kb_category_id, $parent_level_1='', $parent_level_2='', $parent_level_3='', $parent_level_4='' ) {

		$data_parent_level_1 = $parent_level_1 ? 'data-amgr-parent-level-1="' . $parent_level_1 . '"' : '';
		$data_parent_level_2 = $parent_level_2 ? 'data-amgr-parent-level-2="' . $parent_level_2 . '"' : '';
		$data_parent_level_3 = $parent_level_3 ? 'data-amgr-parent-level-2="' . $parent_level_3 . '"' : '';
		$data_parent_level_4 = $parent_level_4 ? 'data-amgr-parent-level-2="' . $parent_level_4 . '"' : '';
		$public_category_indicator = AMGR_Access_Utilities::is_category_public( $this->kb_id, $kb_category_id ) ? ' <span class="amgr-public-access-icon">P</span> ' : '';         ?>

        <div class="amgr-level-<?php echo $level; ?>-category">
            <input type="hidden"
                   value="<?php echo $kb_category_id; ?>"
                   data-amgr-category-id="<?php echo $kb_category_id; ?>"
                   data-amgr-category-access-level="<?php echo $this->get_category_access_level( $kb_category_id ); ?>"
                   <?php echo $data_parent_level_1; ?>
                   <?php echo $data_parent_level_2; ?>
		           <?php echo $data_parent_level_3; ?>
		           <?php echo $data_parent_level_4; ?>
            >
            <div class="amgr-category-name amgr-level-<?php echo $level; ?>-name"><?php echo $public_category_indicator . sanitize_text_field( $category->name ); ?></div>
        </div>    <?php
	}

	private function get_category_access_level( $kb_category_id ) {
		$access = in_array($kb_category_id, $this->full_access_categories_ids) ? 'amgr-full-access' : ( in_array($kb_category_id, $this->read_only_categories_ids) ? 'amgr-read-access' : 'amgr-no-access' );

		// if Public category then obviously the group has read-only access
		if ( $access === 'amgr-no-access' && AMGR_Access_Utilities::is_category_public( $this->kb_id, $kb_category_id ) ) {
			return 'amgr-read-access';
		}

		return $access;
	}
}