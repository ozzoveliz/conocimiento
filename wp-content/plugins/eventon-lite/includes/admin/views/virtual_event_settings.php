<?php
/**
 * Virtual Event Settings 
 * @version 2.2.16
 */	


$vir_type = $EVENT->virtual_type();	
$vir_link_txt = esc_html__('Zoom Meeting URL to join','eventon');
$vir_pass_txt = esc_html__('Zoom Password','eventon');
$vir_o = '';

?>
<div id='evo_virtual_details_in' style='padding:25px;'>
<form class='evo_virtual_settings'>
<input type="hidden" name="event_id" value='<?php echo esc_attr( $EVENT->ID );?>'>
<input type="hidden" name="action" value='eventon_save_virtual_event_settings'>

<?php wp_nonce_field( 'evo_save_virtual_event_settings', 'evo_noncename' );?>

<?php 
if( !$EVENT->is_virtual_data_ready()): ?>
<p class='evo_notice'><?php esc_html_e('Either Virtual URL or Embed content is required!','eventon');?>!</p>
<?php endif;?>

<p class='row' style='padding-bottom: 15px;'>
	<label><?php esc_html_e('Virtual Event Boradcasting Method','eventon');?></label>
	<span style='display: flex'>
	<select name='_virtual_type' class='evo_eventedit_virtual_event'>
		<?php foreach(array(
			'zoom'=> array(
				esc_html__('Zoom','eventon'),
				esc_html__('Zoom Meeting URL to join','eventon'),
				esc_html__('Zoom Password','eventon'),
			),
			'youtube_live'=>array(
				esc_html__('Youtube Live','eventon'),
				esc_html__('Youtube Channel ID','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
				esc_html__('Find channel ID from https://www.youtube.com/account_advanced','eventon')
			),
			'youtube_private'=> array(
				esc_html__('Youtube Private Recorded Event','eventon'),
				esc_html__('Youtube Video URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
			'google_meet'=>array(
				esc_html__('Google Meet','eventon'),
				esc_html__('Google Meet URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
			'jitsi'=>array(
				esc_html__('Jit.si','eventon'),
				esc_html__('Jit.si meet URL ID','eventon'),
				esc_html__('Optional Password','eventon'),
			),
			'vimeo'=>array(
				esc_html__('Vimeo','eventon'),
				esc_html__('Vimeo Live Video URL','eventon'),
				esc_html__('Optional Vimeo Password','eventon'),
				esc_html__('Optional Vimeo Embed HTML code','eventon'),
			),
			'twitch'=>array(
				esc_html__('Twitch','eventon'),
				esc_html__('Twitch Live Video URL','eventon'),
				esc_html__('Optional Twitch Password','eventon'),
				esc_html__('Optional Twitch Embed HTML code','eventon'),
			),
			'facebook_live'=>array(
				esc_html__('Facebook Live','eventon'),
				esc_html__('Facebook Live Video URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
			'periscope'=>array(
				esc_html__('Periscope','eventon'),
				esc_html__('Periscope Video URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
			'wistia'=>array(
				esc_html__('Wistia','eventon'),
				esc_html__('Wistia Video URL','eventon'),
				esc_html__('Optional Wistia Password','eventon'),
				esc_html__('Optional Wistia Embed HTML code','eventon'),
			),
			'rtmp'=>array(
				esc_html__('RTMP Stream','eventon'),
				esc_html__('RTMP URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
				esc_html__('Optional RTMP Embed HTML code','eventon'),
			),
			'other_live'=>array(
				esc_html__('Other Live Stream','eventon'),
				esc_html__('Live Event URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
			'other_recorded'=>array(
				esc_html__('Other Pre-Recorded Video of Event','eventon'),
				esc_html__('Recorded Event Video URL','eventon'),
				esc_html__('Optional Access Pass Information','eventon'),
			),
		) as $F=>$V){
			if($vir_type == $F){
				$vir_link_txt = $V[1];
				$vir_pass_txt = $V[2];
				if(isset($V[3])) $vir_o = $V[3];
			}
			echo "<option value='". esc_attr( $F )."' ". ($vir_type ==$F ? 'selected="selected"':'') ." data-l='". esc_attr( $V[1] )."' data-p='". esc_attr( $V[2] )."' data-o='". (isset($V[3])? esc_attr( $V[3] ):''). "'>". esc_attr( $V[0] )."</option>";
		}?>
	</select>											
</p>


<p class='row vir_link'>
	<label><?php echo esc_attr( $vir_link_txt );?></label>
	<input name='_vir_url' value='<?php echo esc_url( $EVENT->get_virtual_url() );?>' type='text' style='width:100%'/>
	<em><?php echo ($vir_o) ? wp_kses_post( $vir_o ):''?></em>
</p>

<p class='row sel_moderator'>
	<?php 
		$btn_data = array(
			'lbvals'=> array(
				'lbc'=>'sel_moderator',
				't'=>esc_html__('Select moderator for event','eventon'),
				'ajax'=>'yes',
				'd'=> array(					
					'eid'=> $EVENT->ID,
					'action'=> 'eventon_select_virtual_moderator',
					'uid'=>'evo_get_vir_mod_events',
					'load_new_content'=>true
				)
			)
		);
	?>
	<label><?php esc_html_e('Select moderator for the virtual event','eventon')?></label>
	<span class='evo_btn evolb_trigger' <?php echo $this->helper->array_to_html_data($btn_data);?> data-popc='print_lightbox' data-lb_cl_nm='sel_moderator' data-lb_sz='small' data-t='<?php esc_html_e('Select Moderator for Virtual Event','eventon');?>' data-eid='<?php echo esc_attr( $EVENT->ID );?>' style='margin-right: 10px'><?php $EVENT->get_prop('_mod') ? esc_html_e('Update Moderator','eventon') : esc_html_e('Select Moderator','eventon');?></span>
</p>


<div class='evo_edit_field_box' style='background-color: #e0e0e0;' >
	<p style='font-size: 16px;'><b><?php esc_html_e('Other Information','eventon');?></b></p>
	<p class='row vir_pass'>
		<label><?php echo esc_attr( $vir_pass_txt );?></label>
		<input name='_vir_pass' value='<?php echo esc_attr( $EVENT->get_virtual_pass());?>' type='text' style='width:100%'/>
	</p>										
	<p class='row'>
		<label><?php esc_html_e('(Optional) Embed Event Video HTML Code','eventon');?></label>
		<textarea name='_vir_embed' style='width:100%'><?php echo esc_textarea( $EVENT->get_prop('_vir_embed') );?></textarea>
	</p>	
	<p class='row'>
		<label><?php esc_html_e('(Optional) Other Additional Event Access Details','eventon');?></label>
		<input name='_vir_other' value='<?php echo wp_kses_post( $EVENT->get_prop('_vir_other') );?>' type='text' type='text' style='width:100%'/>
	</p>
</div>


<?php
	echo EVO()->elements->process_multiple_elements(
		array(									
			array(
				'type'=>	'dropdown',
				'id'=>		'_vir_show', 
				'value'=>		esc_attr( $EVENT->get_prop('_vir_show') ),
				'input'=>	true,
				'options'=> apply_filters('evo_vir_show', array(
					'always'=>esc_html__('Always','eventon'),
					'10800'=>esc_html__('3 Hours before the event start','eventon'),	
					'7200'=>esc_html__('2 Hours before the event start','eventon'),	
					'3600'=>esc_html__('1 Hour before the event start','eventon'),	
					'1800'=>esc_html__('30 Minutes before the event start','eventon'),	
					'01'=>esc_html__('Right when event starts','eventon'),	
				)),
				'name'=> esc_html__('When to show the above virtual event information on event card', 'eventon'),
				'tooltip'=> esc_html__( 'This will set when to show the virtual event link and access information on the event card.','eventon')
			),	
			array(
				'type'=>	'yesno_btn',
				'id'=>		'_vir_hide', 
				'value'=>		esc_attr( $EVENT->get_prop('_vir_hide') ),
				'input'=>	true,
				'label'=> 	esc_html__('Hide above access information when the event is live', 'eventon'),
				'tooltip'=> esc_html__('Setting this will hide above access information when the event is live.','eventon'),
			),
			array(
				'type'=>	'yesno_btn',
				'id'=>		'_vir_nohiding', 
				'value'=>		esc_attr( $EVENT->get_prop('_vir_nohiding') ),
				'input'=>	true,
				'label'=> 	esc_html__('Disable redirecting and hiding virtual event link', 'eventon'),
				'tooltip'=> esc_html__('Enabling this will show virtual event link without hiding it behind a redirect url.','eventon'),
			),		
		)
	);	
?>


<?php do_action('evo_editevent_vir_options', $EVENT);?>

<p style='padding-top: 15px;'><em>
	<b class='evopadb10 evodfx evofz16'><?php esc_html_e('Other Recommendations','eventon');?></b>
	<?php esc_html_e('Set Event Status value to "Moved Online" so proper schema data will be added to event to help search engines identify event type.','eventon');?>
	<br/><?php echo sprintf( wp_kses_post( __('Use <a href="%s">Tickets Addon</a> to create paid virtual events or <a href="%s">RSVP Addon</a> to show virtual event access information after customers have RSVPed to event. <a href="%s">Countdown Addon</a> to show countdown till event goes live. <a href="%s">Reviewer Addon</a> to ask attendees to leave a review after the event. <a href="%s">Virtual Plus Addon</a> for even more features.','eventon'), array('a'=> array('href'=>array()) )  ), 
		esc_url('https://www.myeventon.com/addons/event-tickets/'), 
		esc_url('https://www.myeventon.com/addons/rsvp-events/'), 
		esc_url('https://www.myeventon.com/addons/event-countdown/'), 
		esc_url('https://www.myeventon.com/addons/event-reviewer/'), 
		esc_url('https://www.myeventon.com/addons/event-virtual-plus/') 
		) ;?></em></p>

<p><span class='evo_btn save_virtual_event_config ' data-eid='<?php echo esc_attr( $EVENT->ID );?>' style='margin-right: 10px'><?php esc_html_e('Save Changes','eventon');?></span></p>	

</form>

</div>
