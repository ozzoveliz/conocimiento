<?php	
/** 
 * Quick edit content
 * @version 2.2.16
 */

	// get time format
	$wp_time_format = get_option('time_format');
	$evcal_date_format = '12h';
	$evcal_date_format =  (strpos($wp_time_format, 'H')!==false)?'24h':'12h';

	$help = new evo_helper();
?>

<fieldset class="inline-edit-col">
	<div id="eventon-fields" class="inline-edit-col evo_qedit_fields">
		<legend class='inline-edit-legend'><?php esc_html_e( 'Event Data', 'eventon' ); ?></legend>
		<fieldset class="inline-edit-col-left">
			<div id="eventon-fields" class="inline-edit-col">
			<input type='hidden' name='_evo_date_format' value=''/>
			<input type='hidden' name='_evo_time_format' value=''/>
			<label>
			    <span class="title"><?php esc_html_e( 'Start Date', 'eventon' ); ?></span>
			    <span class="input-text-wrap">
					<input type="text" name="evcal_start_date" class="text" placeholder="<?php esc_html_e( 'Event Start Date', 'eventon' ); ?>" value="">
				</span>
			</label>	
			<label>
			    <span class="title"><?php esc_html_e( 'Start Time', 'eventon' ); ?></span>
			    <span class="input-text-wrap">
					<span class='input_time'>
						<input type="text" name="evcal_start_time_hour" class="text" placeholder="<?php esc_html_e( 'Event Start Hour', 'eventon' ); ?>" value="">
						<em>Hr</em>
					</span>
					<span class='input_time'>
						<input type="text" name="evcal_start_time_min" class="text" placeholder="<?php esc_html_e( 'Event Start Minutes', 'eventon' ); ?>" value="">
						<em>Min</em>
					</span>
					<?php if($evcal_date_format=='12h'):?>
					<span class='input_time'>
						<input type="text" name="evcal_st_ampm" class="text" placeholder="<?php esc_html_e( 'Event Start AM/PM', 'eventon' ); ?>" value="">
						<em>AM/PM</em>
					</span>
					<?php endif;?>
				</span>
			</label>
			
			<?php // end time date?>
			<label>
			    <span class="title"><?php esc_html_e( 'End Date', 'eventon' ); ?></span>
			    <span class="input-text-wrap">
					<input type="text" name="evcal_end_date" class="text" placeholder="<?php esc_html_e( 'Event End Date', 'eventon' ); ?>" value="">
				</span>
			</label>	
			<label>
			    <span class="title"><?php esc_html_e( 'End Time', 'eventon' ); ?></span>
			    <span class="input-text-wrap">
					<span class='input_time'>
						<input type="text" name="evcal_end_time_hour" class="text" placeholder="<?php esc_html_e( 'Event End Hour', 'eventon' ); ?>" value="">
						<em>Hr</em>
					</span>
					<span class='input_time'>
						<input type="text" name="evcal_end_time_min" class="text" placeholder="<?php esc_html_e( 'Event End Minutes', 'eventon' ); ?>" value="">
						<em>Min</em>
					</span>
					<?php if($evcal_date_format=='12h'):?>
					<span class='input_time'>
						<input type="text" name="evcal_et_ampm" class="text" placeholder="<?php esc_html_e( 'Event End AM/PM', 'eventon' ); ?>" value="">
						<em>AM/PM</em>
					</span>
					<?php endif;?>
				</span>
			</label>

			<?php
			/*
				// timezone field 4.6
				echo EVO()->elements->get_element(array(
					'type'=>'dropdown',
					'id'=>'_evo_tz',
					'value'=> '',
					'name'=> esc_html__('Event Timezone','eventon'),
					'options'=> $help->get_timezone_array( ),
				));
				*/
			?>

			<label>
			    <span class="title"><?php esc_html_e( 'Subtitle', 'eventon' ); ?></span>
			    <span class="input-text-wrap">
					<input type="text" name="evcal_subtitle" class="text" placeholder="<?php esc_html_e( 'Event Sub Title', 'eventon' ); ?>" value="">
				</span>
			</label>
			</div>
		</fieldset>
		<fieldset class="inline-edit-col-right evo_quickedit_events_fields" >
			<div id="eventon-fields" class="inline-edit-col">
			<?php
				$fields = apply_filters('evo_quick_edit_event_add_fields',array(
					
					'evo_hide_endtime'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Hide end time from calendar','eventon')
					),
					'evo_span_hidden_end'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Span the event until hidden end time','eventon')
					),	
					'_time_ext_type'=> array(
						'type'=>'select',
						'label'=>esc_html__('Event Time extended type','eventon'),
						'O'=> array(
							'n' => esc_html__('None','eventon'),
							'dl' => esc_html__('Day Long','eventon'),
							'ml' => esc_html__('Month Long','eventon'),
							'yl' => esc_html__('Year Long','eventon'),
						)
					),							
					'_featured'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Featured event','eventon')
					),
					'_ev_status'=> array(
						'type'=>'select',
						'label'=>esc_html__('Event Status','eventon'),
						'O'=> EVO()->cal->get_status_array('back')
					),
					'evo_exclude_ev'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Exclude from calendar','eventon')
					),
					'location'=> array(
						'type'=>'subheader',
						'label'=>esc_html__('Location Data','eventon')
					),
					'evcal_gmap_gen'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Generate google map from the address','eventon')
					),
					'evcal_hide_locname'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Hide location name from the event card','eventon')
					),
					'evo_access_control_location'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Make location information only visible to logged-in users','eventon')
					),
					'organizer'=> array(
						'type'=>'subheader',
						'label'=>esc_html__('Organizer Data','eventon')
					),
					'evo_evcrd_field_org'=> array(
						'type'=>'yesno',
						'label'=>esc_html__('Hide organizer field from event card','eventon')
					),
				));

				foreach($fields as $field=>$val){
					switch($val['type']){
						case 'yesno': ?>
							<p class="yesno_row evo">
							<?php
								echo EVO()->elements->yesno_btn(array(
									'id'=> esc_attr( $field ),
									'label'=> esc_attr( $val['label'] ),
									'input'=>true
								));
							?>
							</p>	
						<?php
						break;
						case 'subheader':
							?><p class='evo_subheader'><?php echo esc_attr( $val['label'] );?></p><?php
						break;
						case 'select':
							?>
							<span class='title'><?php echo esc_attr( $val['label'] );?></span>
							<select name='<?php echo esc_attr( $field );?>'>
							<?php 
								foreach($val['O'] as $F=>$V){
									echo "<option value='". esc_attr( $F )."'>". esc_attr( $V )."</option>";
								}
							?>
							</select>
							<?php
						break;
					}
				}
			?>
			<input type="hidden" name="eventon_quick_edit" value="1" />
			<input type="hidden" name="eventon_quick_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'eventon_quick_edit_nonce' ) ); ?>" />
			</div>
		</fieldset>
	</div>
</fieldset>