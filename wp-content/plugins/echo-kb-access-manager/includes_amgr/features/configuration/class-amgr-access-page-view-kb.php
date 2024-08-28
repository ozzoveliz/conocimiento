<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manage KB Level access
 */
class AMGR_Access_Page_View_KB {

    protected $html;
    private $kb_id;
    private $kb_name;

	/**
	 * AMGR_Page_Section_KB constructor.
	 *
	 * @param int $kb_id
	 */
    public function __construct( $kb_id ) {

			if ( ! current_user_can('admin_eckb_access_manager_page') ) {
				AMGR_Access_Utilities::output_inline_error_notice( __( 'You do not have permission.', 'echo-knowledge-base' ) . ' (E56)' );
				return;
			}

			$this->html = new EPKB_HTML_Elements();
			$this->kb_id = $kb_id;
			$this->kb_name = epkb_get_instance()->kb_config_obj->get_value( $this->kb_id, 'kb_name', '<unknown>' );

			add_filter( 'eckb_hide_kb_tabs', array( $this, 'hide_kb_tabs' ) );
    }

	/**
	 * Show KB KB Page
	 *
	 * @param $kb_id
	 */
    public function show_KB_section( $kb_id ) {

	    if ( ! current_user_can('admin_eckb_access_manager_page') ) {
		    AMGR_Access_Utilities::output_inline_error_notice(__( 'You do not have permission (A38).', 'echo-knowledge-base' ));
		    return;
	    }

	    $kb_access_level = AMGR_Access_Utilities::determine_kb_access_level( $kb_id );
	    if ( empty($kb_access_level) ) {
		    AMGR_Access_Utilities::output_inline_error_notice(__( 'An error occurred (A40).', 'echo-knowledge-base' ));
		    return;
	    }

	    // display KB status panel
	    if ( $kb_access_level == AMGR_Access_Utilities::AMGR_RESTRICTED_ACCESS_LEVEL ) {

		    $this->kb_section_status( array(
						'status-type'        => 'restricted',
						'kb-name'            => $this->kb_name,
						'description'        => '<p>This Knowledge Base is <strong><u>restricted</u></strong> and therefore its content is restricted to specific users.</p>',
						'icon'               => ' epkbfa-lock',
						'status-text'        => 'RESTRICTED',
						'form-id'            => 'amgr_set_kb_restricted_ajax',
						 'wpnonce'           => '_wpnonce_amgr_set_kb_restricted_ajax',
                   'action'             => 'amgr-make-public',
                   'value'              => 'MAKE the ' . $this->kb_name . ' PUBLIC',
            ) );

        } else if ( $kb_access_level === AMGR_Access_Utilities::AMGR_PUBLIC_ACCESS_LEVEL ) {

		    $this->kb_section_status( array(
						'status-type'        => 'public',
						'kb-name'            => $this->kb_name,
						'description'        => '<p>This Knowledge Base is <strong><u>NOT</u></strong> restricted and therefore anyone can access its content.</p>',
						'icon'               => ' epkbfa-unlock',
						'status-text'        => 'PUBLIC',
						'form-id'            => 'amgr_set_kb_public_ajax',
						'wpnonce'            => '_wpnonce_amgr_set_kb_public_ajax',
						'action'             => 'amgr-make-restricted',
						'value'              => 'Make the "' . $this->kb_name . '" RESTRICTED',
		    ) );
        } else if ( $kb_access_level === AMGR_Access_Utilities::AMGR_MIXED_ACCESS_LEVEL) {

            $this->kb_section_status( array(
						'status-type'        => 'mixed',
						'kb-name'            => $this->kb_name,
						'description'        => '<p>This Knowledge Base has <strong><u>mixed</u></strong> restricted to certain users while other content is publicly accessible to anyone.</p>',
						'icon'               => ' epkbfa-unlock-alt',
						'status-text'        => 'MIXED',
						'form-id'            => 'amgr_set_kb_restricted_ajax',
						'wpnonce'            => '_wpnonce_amgr_set_kb_restricted_ajax',
						'action'             => 'amgr-make-restricted',
						'value'              => 'Make the "' . $this->kb_name . '" RESTRICTED',
            ) );
        } else {
	    	echo "error occurred";
	    }

	    // display AMGR configuration
	    $this->display_info_section( 'Configuration', array( $this->display_amgr_config_option( $kb_id ) ) );

    }

	/**
     * Display KB Status section
     *
	 * @param array $args
	 */
    private function kb_section_status( $args=array() ) {     ?>
        <div class="amgr-kb-status-container  amgr-<?php echo $args[ 'status-type' ]; ?>">
            <section class="amgr-access-cta">
                <h2><?php echo "Status"; //$args[ 'kb-name' ]; ?></h2>				<?php

						$amgr_access_page = new AMGR_Access_Page();
						$amgr_access_page->log_errors(); ?>
	            
                <div class="amgr-access-status-icon-container">
                    <div class="amgr-access-status-icon epkbfa <?php echo $args[ 'icon' ]; ?>"></div>
                    <p class="amgr-access-status-text"><span><?php echo $args[ 'status-text' ]; ?></span></p>
                </div>
                <div><?php echo $args[ 'description' ]; ?></div>
            </section>
        </div>    <?php
    }

	/**
	 * Display AMGR config option.
	 *
	 * @param $kb_id
	 * @return string
	 */
	private function display_amgr_config_option( $kb_id ) {

		ob_start();		?>

		<form id="epkb-access-config">

			<!--  KB NAME and other global settings -->
			<div class="callout callout_default">
				<h4>AMGR Settings</h4>
                <br>
				<div class="amgr-access-config-inner">					<?php

					$feature_specs = AMGR_KB_Config_Specs::get_fields_specification( $kb_id );
					$form = new EPKB_HTML_Elements();
					$html = New AMGR_KB_Config_Elements();
					$amgr_config = epkb_get_instance()->kb_access_config_obj->get_kb_config_or_default( $kb_id );					?>

                    <div id="amgr-enable-private-config" class="epkb-amgr-form-field">                        <?php
                        $form->checkbox( $feature_specs['show_private_article_prefix'] + array(
                                'value'             => $amgr_config['show_private_article_prefix'],
                                'id'                => 'show_private_article_prefix',
                            ) );                        ?>
                    </div>
                    <br>
                    <div class="epkb-amgr-form-field">                        <?php
                        $form->text_basic( $feature_specs['no_access_title'] + array(
                                'value'             => $amgr_config['no_access_title'],
                                'id'                => 'no_access_title',
                            ) );                        ?>
                    </div>
                    <br>
					<div class="epkb-amgr-form-field">
						<ul><?php
							$html->radio_buttons_vertical_v2(
								$feature_specs['no_access_action_user_without_login'] + array(
									'current' => $amgr_config['no_access_action_user_without_login'],
								) ); ?>
						</ul>
					</div>
					<br>
					<div class="epkb-amgr-form-field">                        <?php
						$form->text_basic( $feature_specs['no_access_text'] + array(
								'value'             => $amgr_config['no_access_text'],
								'id'                => 'no_access_text',
							) );                        ?>
					</div>
					<br>
					<div class="epkb-amgr-form-field">
						<ul><?php
							$html->radio_buttons_vertical_v2(
								$feature_specs['no_access_action_user_with_login'] + array(
									'current' => $amgr_config['no_access_action_user_with_login'],
								) ); ?>
						</ul>
					</div>
					<br>
					<div class="epkb-amgr-form-field">                        <?php
						$form->text_basic( $feature_specs['no_access_text_logged'] + array(
								'value'             => $amgr_config['no_access_text_logged'],
								'id'                => 'no_access_text_logged',
							) );                        ?>
					</div>
					<br><br>
					<div class="epkb-amgr-form-field">                        <?php
						$form->text_basic( $feature_specs['no_access_redirect_to_custom_page'] + array(
								'value'             => $amgr_config['no_access_redirect_to_custom_page'],
								'id'                => 'no_access_redirect_to_custom_page',
							) );                        ?>
					</div>
					<br>
					<br><?php

					$form->submit_button_v2( __( 'Save', 'echo-knowledge-base' ), 'epkb_save_amgr_settings', 'amgr-submit', '', true, '', 'epkb-primary-btn' );  ?>

				</div>
			</div>

		</form>	<?php

		return ob_get_clean();
	}

	/**
	 * Show one specific configuration option section.
	 * @param $title
	 * @param $content
	 */
	private function display_info_section( $title , $content ) {             ?>
		<section class="amgr-info-section-container">
			<div class="amgr-info-section-header">
				<div class="amgr-info-section-title"><?php echo $title; ?></div>
				<div class="amgr-info-section-brief"><span class="amgr-info-section-icon ep_font_icon_gear"></span>Additional Global Settings</div>
			</div>
			<div class="amgr-info-section-content">				<?php
				foreach ( $content as $item ){
					echo $item;
				}       ?>
			</div>
		</section>	<?php
	}
}