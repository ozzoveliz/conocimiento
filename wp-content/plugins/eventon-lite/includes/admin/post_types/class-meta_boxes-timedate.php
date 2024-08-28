<?php
/**
 * Event Meta box time and date fields
 * @version L2.2.13
 */
							

ob_start();

$wp_time_format = get_option('time_format');

$dt_formats = extract( EVO()->elements->_get_date_picker_data() );

$wp_date_format = $date_format;

?>

<div id='evcal_dates' date_format='<?php echo esc_attr( $js_date_format );?>'>	
	<?php

	// --- TIME variations	

		$hr24 = (strpos($wp_time_format, 'H')!==false || strpos($wp_time_format, 'G')!==false)? true:false;	
		$used_timeFormat = $hr24?'24h':'12h';
		
		$time_hour_span= $hr24 ? 25:13;

		// Minute increment	
		$minIncre = !empty($evcal_opt1['evo_minute_increment'])? (int)$evcal_opt1['evo_minute_increment']:1;
		$minIncre = 60/ $minIncre;	

	?>
	
	<!-- date and time formats to use -->
	<input type='hidden' name='_evo_date_format' value='Y/m/d'/>
	<input type='hidden' name='_evo_time_format' value='<?php echo esc_attr( $used_timeFormat );?>'/>

	<!-- Event Time -->
	<div class='evo_datetimes evo_edit_field_box' style='background-color: #f5c485;background: linear-gradient(45deg, #f9d29f, #ffae5b);border-radius: 20px;' data-s='<?php echo esc_attr($EVENT->get_prop('evcal_srow'));?>' data-e='<?php echo esc_attr($EVENT->get_prop('evcal_erow'));?>' data-es="<?php echo esc_attr($EVENT->get_prop('_unix_start_ev'));?>" data-ee="<?php echo esc_attr($EVENT->get_prop('_unix_end_ev') );?>">

		<div class='evo_date_time_elem evo_start'>
			<p class='evo_event_time_label' id='evcal_start_date_label'><?php esc_html_e('Event Start', 'eventon')?></p>
			<?php

			EVO()->elements->_print_date_picker_values();
			
			$rand = 457973;
			
			EVO()->elements->print_date_time_selector(
				array(
					'date_format_hidden'=>'Y/m/d',
					'minute_increment'=> $minIncre,
					'date_format'=> $wp_date_format,
					'time_format'=> $wp_time_format,
					'unix'=> $EVENT->get_prop('evcal_srow'),
					'type'=>'start',
					'rand'=> $rand,
					'time_opacity'=> ($EVENT->is_all_day() ? '0.5':1),
				)
			);
			?>			
		</div>
		<div class='evo_date_time_elem evo_end' style='<?php echo $EVENT->check_yn('evo_hide_endtime')?'opacity:0.5':null;?>'>
			<p class='evo_event_time_label'><?php esc_html_e('Event End','eventon')?></p>
			<?php

			EVO()->elements->print_date_time_selector(
				array(
					'date_format_hidden'=>'Y/m/d',
					'minute_increment'=> $minIncre,
					'date_format'=> $wp_date_format,
					'time_format'=> $wp_time_format,
					'unix'=> $EVENT->get_prop('evcal_erow'),
					'type'=>'end',
					'rand'=> $rand,
					'time_opacity'=> ($EVENT->is_all_day() ? '0.5':1),
				)
			);
			?>			
		</div>
	</div>

	<!-- Time extended type selection -->
	<div class='evo_time_edit_extensions evo_edit_field_box' style='background-color: #f4f4f4'>
		<p class=''><?php esc_html_e('Event Time Extended Type','eventon');?> <?php EVO()->elements->tooltips(esc_html__('Select if you want to extend this event time to longer ranges based on event start time.','eventon'),'',true);?></p>
		<?php 
			$time_etx_type = $EVENT->get_time_ext_type();

			EVO()->elements->print_element( array(
				'type'=>'select_row',
				'row_class'=>'extended_values',
				'name'=>'_time_ext_type',
				'value'=>	esc_attr( $time_etx_type ),
				'options'=> array(
					'n' => esc_html__('None','eventon'),
					'dl' => esc_html__('Day Long','eventon'),
					'ml' => esc_html__('Month Long','eventon'),
					'yl' => esc_html__('Year Long','eventon'),
				)
			));
		?>
	</div>
	
	<!-- timezone value -->	
	<div class='evo_edit_field_box'>	
			
		<?php 

		$help = new evo_helper();
		
		// calendar time
			$DD = new DateTime();
			$DD->setTimezone( EVO()->calendar->cal_tz );
			$DD->modify('now');
			$cal_time = $DD->format( EVO()->calendar->date_format . ' '. EVO()->calendar->time_format) .' ('. EVO()->calendar->cal_tz_gmt . ' '. EVO()->calendar->cal_tz_string .')';

		EVO()->elements->print_process_multiple_elements( array(
			array(
				'type'=>'dropdown',
				'id'=>'_evo_tz',
				'value'=> esc_attr( $EVENT->get_timezone_key() ),
				'name'=> esc_html__('Event Timezone','eventon'),
				'options'=> array_map('esc_html', $help->get_timezone_array( ) ),
				'row_style'=>'padding-bottom:10px;',
			),
			array(
				'type'=>'notice',
				'name'=> esc_html__('Calendar time: ','eventon') . esc_html( $cal_time ),
				'row_class'=>'padb10',
				'row_style'=>'padding-bottom:10px;',
			),
			
		));
		?>
	</div>
		
		
	<?php
	// date time related yes no values
		EVO()->elements->print_process_multiple_elements(
			array(
			array(
				'type'=>'yesno_btn',
				'label'=> esc_html__('Hide End Time from calendar', 'eventon'), 
				'id'=> 'evo_hide_endtime',
				'value'=> esc_attr( $EVENT->get_prop('evo_hide_endtime') ),											
				'afterstatement'=> '_evo_span_hidden_end',
			),
			array(
				'type'=>'begin_afterstatement',
				'id'=>'_evo_span_hidden_end',
				'value'=> esc_attr( $EVENT->get_prop('evo_hide_endtime') )
			),
				array(
					'type'=>'yesno_btn',
					'label'=> esc_html__('Span the event until hidden end time', 'eventon'), 
					'tooltip'=> esc_html__('If event end time goes beyond start time +  and you want the event to show in the calendar until end time expire, select this.','eventon'),
					'id'=> 'evo_span_hidden_end',
					'value'=> esc_attr( $EVENT->get_prop('evo_span_hidden_end') )
				),
			array('type'=>'end_afterstatement',	),
			
			array(
				'type'=>'yesno_btn',
				'label'=> esc_html__('Hide live event progress bar', 'eventon'),
				'tooltip'=> esc_html__('This will hide live event progress bar and time left from eventtop','eventon'), 
				'id'=> '_edata[hide_progress]',
				'value'=> esc_attr( $EVENT->get_eprop('hide_progress') )
			),
			array(
				'type'=>'yesno_btn',
				'label'=> esc_html__('Repeating Event - Enable repeating instances for this event', 'eventon'),
				'id'=> 'evcal_repeat',
				'value'=> esc_attr( $EVENT->get_prop('evcal_repeat') ),
				'afterstatement'=> 'evo_editevent_repeatevents'
			),
			)
		);
		
	?>
			
	<?php 
		// Recurring events 
		$evcal_repeat = (!empty($ev_vals["evcal_repeat"]) )? $ev_vals["evcal_repeat"][0]:null;
	

		// initial values
		$display = (!empty($ev_vals["evcal_repeat"]) && $evcal_repeat=='yes')? '':'none';
		
		// repeat frequency array
		$repeat_freq= apply_filters('evo_repeat_intervals', array(
			esc_html__('daily','eventon') =>esc_html__('days','eventon'),
			esc_html__('weekly','eventon') =>esc_html__('weeks','eventon'),
			esc_html__('monthly','eventon') =>esc_html__('months','eventon'),
			esc_html__('yearly','eventon') =>esc_html__('years','eventon'),
			esc_html__('custom','eventon') =>esc_html__('custom','eventon')) 
		);
		
		$evcal_rep_freq = $EVENT->get_prop('evcal_rep_freq');
		
	?>
	<div id='evo_editevent_repeatevents' class='evcalr_2 evo_edit_field_box' style='display:<?php echo esc_attr($display); ?>'>
		
		<!-- REPEAT SERIES -->
		<div class='repeat_series'>
		<?php

			EVO()->elements->print_process_multiple_elements(
				array(
				array(
					'type'=>'yesno_btn',
					'label'=> esc_html__('Show other future repeating instances of this event on event card','eventon'),
					'id'=> '_evcal_rep_series',
					'value'=> esc_attr( $EVENT->get_prop('_evcal_rep_series') ),
					'afterstatement'=> '_evcal_rep_series_as',
					'tooltip'=> esc_html__('This will only show future repeating instances'),
					'tooltip_position'=>'L'
				),
				array(
					'type'=>'begin_afterstatement',
					'id'=>'_evcal_rep_series_as',
					'value'=> esc_attr( $EVENT->get_prop('_evcal_rep_series') )
				),

					array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Show end time of repeating instances as well on eventcard','eventon'),
						'id'=> '_evcal_rep_endt',
						'value'=> esc_attr( $EVENT->get_prop('_evcal_rep_endt') )
					),array(
						'type'=>'yesno_btn',
						'label'=> esc_html__('Allow repeat dates to be clickable','eventon'),
						'id'=> '_evcal_rep_series_clickable',
						'value'=> esc_attr( $EVENT->get_prop('_evcal_rep_series_clickable') )
					),
				array('type'=>'end_afterstatement'),
				)
			);
		?>

		</div>

		<?php 
		// REPEAT TYPE
		$evcal_rep_freq = $EVENT->get_prop('evcal_rep_freq');?>

		<div class='evo_editevent_repeat_field' data-t='<?php echo wp_json_encode($repeat_freq);?>'>
			<span class='evo_form_label'><?php esc_html_e('Event Repeat Type','eventon');?></span>
			<?php														
				
				EVO()->elements->print_element(
					array(
						'type'=>'select_row',
						'name'=>'evcal_rep_freq',
						'value'=> esc_attr( $evcal_rep_freq ),
						'select_option_class'=>'evo_repeat_type_val',
						'options'=> apply_filters('evo_repeat_intervals_ly', array(
							'daily'=>esc_html__('Daily','eventon'),
							'weekly'=>esc_html__('Weekly','eventon'),
							'monthly'=>esc_html__('Monthly','eventon'),
							'yearly'=>esc_html__('Yearly','eventon'),
							'custom'=>esc_html__('Custom','eventon')) 
						)
					)
				);
			?>
		</div>
		
		<div class='evo_preset_repeat_settings' style='display:<?php echo (!empty($ev_vals['evcal_rep_freq']) && $ev_vals['evcal_rep_freq'][0]=='custom')? 'none':'block';?>'>		
			
			<?php
			// Gap between repeats
				$evcal_rep_gap = $EVENT->get_prop('evcal_rep_gap')? $EVENT->get_prop('evcal_rep_gap'):1;
				$freq = '';
				if($evcal_rep_freq) $freq = $repeat_freq[ $evcal_rep_freq ];

				EVO()->elements->print_element(
					array(
						'type'=>'plusminus',
						'name'=> esc_html__('Gap between repeats','eventon'),
						'id'=>'evcal_rep_gap',
						'value'=> esc_attr( $evcal_rep_gap ),
						'field_after_content'=> "<span id='evcal_re' style='padding:0 5px 5px 15px'>{$freq}</span>"	,
						'row_class'=>'evo_editevent_repeat_field'		
					)
				);
			
			// repeat number
				$evcal_rep_num = $EVENT->get_prop('evcal_rep_num')? $EVENT->get_prop('evcal_rep_num'):1;

				EVO()->elements->print_element(
					array(
						'type'=>'plusminus',
						'name'=> esc_html__('Number of repeats','eventon'),
						'id'=> 'evcal_rep_num',
						'value'=> esc_attr( $evcal_rep_num ),	
						'row_class'=>'evo_editevent_repeat_field'					
					)
				);
			?>

		
		<?php 
			// Weekly view only 
			$evp_repeat_rb_wk = $EVENT->get_prop('evp_repeat_rb_wk');				
		?>
			<div class='repeat_weekly_only repeat_section_extra' style='display:<?php echo ( $EVENT->get_prop('evcal_rep_freq') =='weekly')? 'block':'none';?>'>					

				<div class='evo_editevent_repeat_field'>
					<span class='evo_form_label'><?php esc_html_e('Repeat mode','eventon');?></span>
					<?php														
						
						EVO()->elements->print_element(
							array(
								'type'=>'select_row',
								'name'=>'evp_repeat_rb_wk',
								'value'=> esc_attr( $evp_repeat_rb_wk ),
								'row_class'=>'repeat_mode_selection',
								'options'=> array(
									'sing'=> esc_html__('Single Day','eventon'),
									'dow'=> esc_html__('Days of the week','eventon'),
								)
							)
						);
					?>
				</div>
				<div class='evo_editevent_repeat_field evo_days_list repeat_modes evo_rep_week_dow' style='display: <?php echo ($evp_repeat_rb_wk=='dow'?'flex':'none');?>;'>
					<span class='evo_form_label'><?php esc_html_e('Repeat on selected days','eventon');?></span>
					<?php

						// legacy filter
						$evo_rep_WKwk = $EVENT->get_prop('evo_rep_WKwk');
						
						if(is_array($evo_rep_WKwk) && count($evo_rep_WKwk)>0){
							$evo_rep_WKwk = implode(',', $evo_rep_WKwk);
						}		
						
						EVO()->elements->print_element(
							array(
								'type'=>'select_row',
								'name'=>'evo_rep_WKwk',
								'value'=> esc_attr( $evo_rep_WKwk ),
								'select_multi_options'=> true,
								'options'=> array(
									'_0'=> esc_html__('S','eventon'),
									'1'=> esc_html__('M','eventon'),
									'2'=> esc_html__('T','eventon'),
									'3'=> esc_html__('W','eventon'),
									'4'=> esc_html__('T','eventon'),
									'5'=> esc_html__('F','eventon'),
									'6'=> esc_html__('S','eventon')
								)
							)
						);
					?>
				</div>
			</div>
		<?php 
			// monthly only 
			$__display_none_1 =  $EVENT->get_prop('evcal_rep_freq') == 'monthly' ? 'flex': 'none';
			$__display_none_2 =  ($__display_none_1=='flex' && $EVENT->get_prop('evp_repeat_rb') =='dow')? 'block': 'none';

			// repeat by
				$evp_repeat_rb = $EVENT->get_prop('evp_repeat_rb');
		?>
			<div class='repeat_monthly_only repeat_section_extra'>
				
				<div class='evo_editevent_repeat_field evo_rep_month' style='display:<?php echo esc_attr($__display_none_1);?>'>
					<span class='evo_form_label'><?php esc_html_e('Repeat mode','eventon');?></span>
					<?php														
						
						EVO()->elements->print_element(
							array(
								'type'=>'select_row',
								'name'=>'evp_repeat_rb',
								'value'=> esc_attr( $evp_repeat_rb ),
								'row_class'=>'repeat_mode_selection',
								'options'=> array(
									'dom'=>esc_html__('Day of the month','eventon'),
									'dow'=>esc_html__('Days of the week','eventon'),
								)
							)
						);
					?>
				</div>

				<div class='repeat_modes repeat_monthly_modes' style='display:<?php echo esc_attr($__display_none_2);?>'>
					<div class='evo_editevent_repeat_field evo_days_list evo_rep_month_2 evo_rep_month_dow'>
						<span class='evo_form_label'><?php esc_html_e('Repeat on selected days','eventon');?></span>
						<?php

							// legacy filter
							$evo_rep_WK = $EVENT->get_prop('evo_rep_WK');
							
							if(is_array($evo_rep_WK) && count($evo_rep_WK)>0){
								$evo_rep_WK = implode(',', $evo_rep_WK);
							}			



							EVO()->elements->print_element(
								array(
									'type'=>'select_row',
									'name'=>'evo_rep_WK',
									'value'=> esc_attr( $evo_rep_WK ),
									'select_multi_options'=> true,
									'options'=> array(
										'_0'=>'S',
										'1'=>'M',
										'2'=>'T',
										'3'=>'W',
										'4'=>'T',
										'5'=>'F',
										'6'=>'S'
									)
								)
							);
						?>
					</div>

					<div class='evo_editevent_repeat_field evcalr_2_p evo_rep_month_2'>
						<span class='evo_form_label'><?php esc_html_e('Week of month to repeat','eventon');?></span>
						<?php

							// legacy filter
							$evo_repeat_wom = $EVENT->get_prop('evo_repeat_wom');
							
							if(is_array($evo_repeat_wom) && count($evo_repeat_wom)>0){
								$evo_repeat_wom = implode(',', $evo_repeat_wom);
							}							
							
							EVO()->elements->print_element(
								array(
									'type'=>'select_row',
									'name'=>'evo_repeat_wom',
									'value'=> esc_attr($evo_repeat_wom),
									'select_multi_options'=> true,
									'options'=> array(
										'1'=>esc_html__('First','eventon'),
										'2'=>esc_html__('Second','eventon'),
										'3'=>esc_html__('Third','eventon'),
										'4'=>esc_html__('Fourth','eventon'),
										'5'=>esc_html__('Fifth','eventon'),
										'-1'=>esc_html__('Last','eventon'),
									)
								)
							);
						?>
					</div>

				</div>
			</div>									
			
		</div><!--evo_preset_repeat_settings-->
		
		<!-- Custom repeat -->
		<div class='repeat_information' style='display:<?php echo ( $EVENT->get_prop('evcal_rep_freq')=='custom')? 'block':'none';?>'>
			<p><?php esc_html_e('CUSTOM REPEAT TIMES','eventon');?><br/><i style='opacity:0.7'><?php esc_html_e('NOTE: Initial time is the original event time, while other times are repeat instances of the original event time.','eventon');?></i></p>										
			<?php

				// Important messages about repeats
				$important_msg_for_repeats = apply_filters('evo_repeats_admin_notice','', $ev_vals);
				if($important_msg_for_repeats)	echo "<p><i style='opacity:0.7'>". esc_html( $important_msg_for_repeats ) ."</i></p>";


				echo "<p id='no_repeats' style='display:none;opacity:0.7'>There are no additional custom repeats!</p>";

				echo "<ul class='evo_custom_repeat_list'>";
				$count =0;
				if(!empty($ev_vals['repeat_intervals'])){

					$DD = new DateTime( null, EVO()->calendar->timezone0);

					$repeat_times = (unserialize($ev_vals['repeat_intervals'][0]));
					
					// datre format sting to display for repeats
					$date_format_string = $wp_date_format.' '.( $hr24? 'G:i':'h:ia');
					
					foreach($repeat_times as $rt){
						
						$DD->setTimestamp((int)$rt[0]);
						$start_unix = $DD->format('U');
						$start_dt = $DD->format($date_format_string);

						$DD->setTimestamp((int)$rt[1]);
						$end_unix = $DD->format('U');
						$end_dt = $DD->format($date_format_string);


						echo '<li data-cnt="'.esc_attr($count).'" style="display:'.(( $count>3)?'none':'flex').'" class="'.($count==0?'initial':'').($count>3?' over':'').'">'. ($count==0? '<dd>'.esc_html__('Initial','eventon').'</dd>':'').'<span>'.esc_html__('from','eventon').'</span> '. esc_attr($start_dt) .' <span class="e">End</span> '. esc_attr($end_dt) .'<em alt="Delete">x</em>
						<input type="hidden" name="repeat_intervals['.esc_attr($count).'][0]" value="'.esc_attr($start_unix).'"/><input type="hidden" name="repeat_intervals['.esc_attr($count).'][1]" value="'.esc_attr($end_unix).'"/></li>';
						$count++;
					}								
				}
				echo "</ul>";
				echo ( !empty($ev_vals['repeat_intervals']))? 
					"<p class='evo_custom_repeat_list_count' data-cnt='". esc_attr($count)."' style='padding-bottom:20px'>There are ". esc_attr($count-1)." repeat intervals. ". ($count>3? "<span class='evo_repeat_interval_view_all' data-show='no'>".esc_html__('View All','eventon')."</span>":'') ."</p>"
					:null;
			?>
			<div class='evo_repeat_interval_new evo_edit_field_box' style='display:none'>

				<div class='evo_date_time_elem evo_start'>
					<p class='evo_event_time_label'><?php esc_html_e('New Repeat Start', 'eventon')?></p>
					<?php

					
					$rand = 478933;
					
					EVO()->elements->print_date_time_selector(
						array(
							'date_format_hidden'=>'Y/m/d',
							'minute_increment'=> $minIncre,
							'date_format'=> $wp_date_format,
							'time_format'=> $wp_time_format,
							'unix'=> $EVENT->get_prop('evcal_srow'),
							'type'=>'new_repeat_start',
							'rand'=> $rand,
							'time_opacity'=> ($EVENT->is_all_day() ? '0.5':1),
						)
					);
					?>			
				</div>
				<div class='evo_date_time_elem evo_end' >
					<p class='evo_event_time_label'><?php esc_html_e('New Repeat End','eventon')?></p>
					<?php

					EVO()->elements->print_date_time_selector(
						array(
							'date_format_hidden'=>'Y/m/d',
							'minute_increment'=> $minIncre,
							'date_format'=> $wp_date_format,
							'time_format'=> $wp_time_format,
							'unix'=> $EVENT->get_prop('evcal_erow'),
							'type'=>'new_repeat_end',
							'rand'=> $rand,
							'time_opacity'=> ($EVENT->is_all_day() ? '0.5':1),
						)
					);
					?>			
				</div>				
			</div>
			<p class='evo_repeat_interval_button'><a id='evo_add_repeat_interval' class='button_evo'>+ <?php esc_html_e('Add New Repeat Interval','eventon');?></a><span></span></p>
		</div>	
	</div>
</div><!--evcal_dates-->
