<?php
/**
 * Event Edit Meta box Health Guidance
 * @1.0
 */

?>
<div class='evcal_data_block_style1 event_health_settings'>
	<div class='evcal_db_data'>

		<p class='yesno_row evo single_main_yesno_field'>
			<?php 	
			EVO()->elements->print_yesno_btn(array(
				'id'=>		'_health', 
				'var'=>		esc_attr( $EVENT->get_prop('_health') ),
				'input'=>	true,
				'attr'=>	array('afterstatement'=>'evo_health_details')
			));
			?>						
			<label class='single_yn_label' for='_health'><?php esc_html_e('Enable health guidelines for this event', 'eventon')?></label>
		</p>

		<div id='evo_health_details' class='evo_edit_field_box Xevo_metabox_secondary evo_meta_elements' style='display:<?php echo $EVENT->check_yn('_health')?'block':'none';?>'>

			<?php

			$EVENT->localize_edata('_edata');
			
			EVO()->elements->print_process_multiple_elements(
				array(
					array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Face masks required', 'eventon'),
						'id'=> '_edata[_health_mask]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_mask")),
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Temperature will be checked at entrance', 'eventon'),
						'id'=> '_edata[_health_temp]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_temp")),
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Physical distance maintained event', 'eventon'),
						'id'=> '_edata[_health_pdis]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_pdis")),
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Event area sanitized before event', 'eventon'),
						'id'=> '_edata[_health_san]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_san")),
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Event is held outside', 'eventon'),
						'id'=> '_edata[_health_out]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_out")),
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Vaccination Required', 'eventon'),
						'id'=> '_edata[_health_vac]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_vac")),
					),array(
						'type'=>'textarea',
						'name'=> esc_html__('Other additional health guidelines', 'eventon'),
						'id'=> '_edata[_health_other]',
						'value'=> esc_attr( $EVENT->get_eprop("_health_other")),
					),
				)
			);

			?>
		
		</div>
	</div>									
</div>