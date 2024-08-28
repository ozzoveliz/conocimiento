<?php
/**
 * Event Edit Meta box Health Guidance
 * @2.2.16
 */


$related_events = $EVENT->get_prop('ev_releated');


echo "<div class='evcal_data_block_style1'>
<div class='evcal_db_data evo_rel_events_box'>
	<input type='hidden' class='evo_rel_events_sel_list' name='ev_releated' value='". esc_attr( $related_events )."' />";

	if($EVENT->is_repeating_event()){
		echo "<p>".esc_html__('NOTE: You can not select a repeat instance of this event as related event.','eventon').'</p>';
	}
	?>
	<span class='ev_rel_events_list'><?php
		if($related_events){
			$D = json_decode($related_events, true);

			$rel_events = array();

			foreach($D as $I=>$N){
				$id = explode('-', $I);
				$EE = new EVO_Event($id[0]);
				$x = isset($id[1])? $id[1]:'0';
				$time = $EE->get_formatted_smart_time($x);
				
				$rel_events[ $I.'.'. $EE->get_start_time() ] =  "<span class='l' data-id='{$I}'><span class='t'>{$time}</span><span class='n'>{$N}</span><i class='fa fa-close'></i></span>";
			}

			//krsort($rel_events);

			foreach($rel_events as $html){
				echo wp_kses_post( $html );
			}
			
		}
	?></span>

	<?php
		$btn_data = array(
			'lbvals'=> array(
				'lbc'=>'evo_related_events_lb',
				't'=> esc_html__('Configure Related Event Details','eventon'),
				'ajax'=>'yes',
				'd'=> array(					
					'eventid'=> esc_attr( $EVENT->ID ),
					'action'=> 'eventon_rel_event_list',
					'EVs'=> esc_attr( $related_events ),
					'uid'=>'evo_get_related_events',
				)
			)
		);
	?>
	<span class='evo_btn evolb_trigger' <?php echo $this->helper->array_to_html_data($btn_data);?> ><?php esc_html_e('Add related event','eventon');?></span>

<?php echo "</div></div>";