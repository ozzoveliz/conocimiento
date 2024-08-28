<?php
/**
 * Meta boxes for ajde_events
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Admin/ajde_events
 * @version     L 2.2.16
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evo_event_metaboxes{
	public $EVENT = false;
	public $event_data = array();
	public $helper;

	public function __construct(){
		add_action( 'add_meta_boxes', array($this,'metabox_init') );
		add_action( 'save_post', array($this,'eventon_save_meta_data'), 1, 2 );
		//add_action( 'post_submitbox_misc_actions', array($this,'ajde_events_settings_per_post' ));
	}

	// INIT meta boxes
		function metabox_init(){

			global $post;

			// get post type
			$postType = !empty($_GET['post_type'])? sanitize_text_field($_GET['post_type']): false;	   
	   		if(!$postType && !empty($_GET['post']))   	$postType = get_post_type( sanitize_text_field($_GET['post']));

	   		if( !$postType) return false;
	   		if( $postType != 'ajde_events' ) return false;

	   		// Custom editor // 2.8.5
	   		wp_enqueue_style('evo_wyg_editor');
	   		wp_enqueue_script('evo_wyg_editor');
			
			// initiate a event object
	   		$this->EVENT = $this->EVENT ? $this->EVENT: new EVO_Event($post->ID);
	   		$this->event_data = $this->EVENT->get_data();

			$evcal_opt1= get_option('evcal_options_evcal_1');

			// ajde_events meta boxes
			add_meta_box('ajdeevcal_mb1', esc_html__('Event Details','eventon'), array($this,'ajde_evcal_show_box'),'ajde_events', 'normal', 'high');

			add_meta_box('ajdeevcal_mb1_cmf', esc_html__('Event Custom Meta Fields','eventon'), array($this,'ajde_evcal_show_box_cmf'),'ajde_events', 'normal', 'high');		

			add_meta_box('ajdeevcal_mb3jd',esc_html__('Event Options','eventon'), 
				array($this,'meta_box_event_options'),'ajde_events', 'side', 'low');
			
			add_meta_box('ajdeevcal_mb2',esc_html__('Event Color','eventon'), 
				array($this,'meta_box_event_color'),'ajde_events', 'side', 'core');
			
			
			
			// if third party is enabled
			if( EVO()->cal->check_yn('evcal_paypal_pay','evcal_1')){
				add_meta_box('ajdeevcal_mb3',esc_html__('Third Party Settings','eventon'), array($this,'ajde_evcal_show_box_3'),'ajde_events', 'normal', 'high');
			}


			// @updated 2.6.7 to pass event object
			do_action('eventon_add_meta_boxes', $this->EVENT);
		}


	// EXTRA event settings for the page
		function meta_box_event_options(){
			
			// Global Event Props will be set initially right here
				$event = $this->EVENT;
			?>
			<div class='evo_event_opts evo_edit_field_box'>	
			<?php

			
			echo EVO()->elements->process_multiple_elements(
				array(
					array(
						'id'=>'evo_exclude_ev', 
						'type'=>'yesno_btn',
						'value'=> esc_attr( $event->get_prop('evo_exclude_ev') ),
						'input'=>true,
						'label'=>esc_html__('Exclude from calendar','eventon'),
						'tooltip'=>esc_html__('Set this to Yes to hide event from showing in all calendars','eventon'),
						'tooltip_position'=>'L'
					),
					array(
						'id'=>'_featured', 'type'=>'yesno_btn',
						'value'=> esc_attr( $event->get_prop('_featured') ),
						'input'=>true,
						'label'=>esc_html__('Featured Event','eventon'),
						'tooltip'=>esc_html__('Make this event a featured event','eventon'),
						'tooltip_position'=>'L'
					),
					array(
						'id'=>'_completed', 'type'=>'yesno_btn',
						'value'=> esc_attr( $event->get_prop('_completed') ),
						'input'=>true,
						'label'=>esc_html__('Event Completed','eventon'),
						'tooltip'=>esc_html__('Mark this event as completed','eventon'),
						'tooltip_position'=>'L'
					),
					array(
						'id'=>'_onlyloggedin', 'type'=>'yesno_btn',
						'value'=> esc_attr( $event->get_prop('_onlyloggedin') ),
						'input'=>true,
						'label'=>esc_html__('Only for loggedin users','eventon'),
						'tooltip'=>esc_html__('This will make this event only visible if the users are loggedin to this site','eventon'),
						'tooltip_position'=>'L',
					)
				)
			);
				// @since 2.2.28
				do_action('eventon_event_submitbox_misc_actions',$event);
			?>
		</div>
		<?php
		}
	
	// Event Color Meta Box	
		function meta_box_event_color(){
			include_once 'class-meta_boxes-color.php';
		}

	// MAIN META BOX CONTENT
		function ajde_evcal_show_box(){
			global $eventon, $post;
			
			$evcal_opt1= get_option('evcal_options_evcal_1');
			$evcal_opt2= get_option('evcal_options_evcal_2');

			EVO()->cal->set_cur('evcal_1');
			
			// Use nonce for verification
			wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename' );
			
			// The actual fields for data entry
			$p_id = get_the_ID();
			
			$EVENT = $this->EVENT;
			$ev_vals = $this->event_data;	

			$this->helper = new evo_helper();

			$select_a_arr= array('AM','PM');
						
		// array of all meta boxes
			$metabox_array = apply_filters('eventon_event_metaboxs', array(
				array(
					'id'=>'ev_subtitle',
					'name'=>esc_html__('Event Subtitle','eventon'),
					'variation'=>'customfield',	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-pencil',
					'iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_subtitle'
				),
				array(
					'id'=>'ev_status',
					'name'=>esc_html__('Event Status','eventon'),
					'variation'=>'customfield',	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-signal',
					'iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_status'
				),
				array(
					'id'=>'ev_attendance',
					'name'=>esc_html__('Event Attendance Mode','eventon'),
					'variation'=>'customfield',	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-clipboard',
					'iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_attendance'
				),
				array(
					'id'=>'ev_timedate',
					'name'=>esc_html__('Time and Date','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-clock-o','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_timedate'
				),
				array(
					'id'=>'ev_virtual',
					'name'=>esc_html__('Virtual Event','eventon'),	
					'iconURL'=>'fa-globe','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code','slug'=>'ev_virtual',
				),
				array(
					'id'=>'ev_health',
					'name'=>esc_html__('Health Guidelines','eventon'),	
					'iconURL'=>'fa-heartbeat','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code','slug'=>'ev_health',
				),
				array(
					'id'=>'ev_location',
					'name'=>esc_html__('Location and Venue','eventon'),	
					'iconURL'=>'fa-map-marker','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'slug'=>'ev_location',
				),
				array(
					'id'=>'ev_organizer',
					'name'=>esc_html__('Organizer','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-microphone','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_organizer'
				),array(
					'id'=>'ev_uint',
					'name'=>esc_html__('User Interaction for event click','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-street-view','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_uint',
					'guide'=>esc_html__('This define how you want the events to expand following a click on the eventTop by a user','eventon')
				),array(
					'id'=>'ev_learnmore',
					'name'=>esc_html__('Learn more about event link','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-random','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_learnmore',
					'guide'=>esc_html__('This will create a learn more link in the event card. Make sure your links start with http://','eventon')
				),array(
					'id'=>'ev_releated',
					'name'=>esc_html__('Related Events','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-calendar-plus','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_releated',
					'guide'=>esc_html__('Show events that are releated to this event','eventon')
				),array(
					'id'=>'ev_seo',
					'name'=>esc_html__('SEO Additions for Event','eventon'),	
					'hiddenVal'=>'',	
					'iconURL'=>'fa-search','variation'=>'customfield','iconPOS'=>'',
					'type'=>'code',
					'content'=>'',
					'slug'=>'ev_seo',
				)
			));

			
		
		// combine array with custom fields
		// $metabox_array = (!empty($evMB_custom) && count($evMB_custom)>0)? array_merge($metabox_array , $evMB_custom): $metabox_array;
		
		$closedmeta = eventon_get_collapse_metaboxes($p_id);
		
		?>	
			
			<div id='evo_mb' class='eventon_mb'>
				<input type='hidden' id='evo_collapse_meta_boxes' name='evo_collapse_meta_boxes' value=''/>
			<?php
				// initial values
					$visibility_types = array('all'=>esc_html__('Everyone','eventon'),'admin'=> esc_html__('Admin Only','eventon'),'loggedin'=>esc_html__('Loggedin Users Only','eventon'));

				// FOREACH metabox item
				foreach($metabox_array as $mBOX):
					
					// initials
						$icon_style = (!empty($mBOX['iconURL']))?
							'background-image:url('. esc_url( $mBOX['iconURL'] ) .')'
							:'background-position:'. esc_attr( $mBOX['iconPOS'] );
						$icon_class = (!empty($mBOX['iconPOS']))? 'evIcons':'evII';
										

						// visibility type ONLY for custom meta fields
							$visibility_type = (!empty($mBOX['visibility_type']))? 
								"<span class='visibility_type'>".esc_html__('Visibility Type:','eventon').' '. esc_attr( $visibility_types[$mBOX['visibility_type']] ) .'</span>': 
								false;
					
						$closed = (!empty($closedmeta) && in_array($mBOX['id'], $closedmeta))? 'closed':null;
			?>
				<div class='evomb_section' id='<?php echo esc_attr( $mBOX['id'] );?>'>			
					<div class='evomb_header'>
						<?php // custom field with icons
							if(!empty($mBOX['variation']) && $mBOX['variation']	=='customfield'):?>	
							<span class='evomb_icon <?php echo esc_attr($icon_class);?>'><i class='fa <?php echo esc_attr($mBOX['iconURL']); ?>'></i></span>
							
						<?php else:	?>
							<span class='evomb_icon <?php echo esc_attr($icon_class);?>' style='<?php echo esc_attr( $icon_style );?>'></span>
						<?php endif; ?>
						<p><?php echo esc_attr( $mBOX['name'] );?><?php echo (!empty($mBOX['hiddenVal']))?
							'<span class="hiddenVal">'.esc_attr( $mBOX['hiddenVal'] ).'</span>':null;?><?php EVO()->elements->print_tooltips( isset($mBOX['guide']) ? $mBOX['guide'] :null );?><?php echo  $visibility_type;?></p>
					</div>
					<div class='evomb_body <?php echo esc_attr( $closed );?>' box_id='<?php echo esc_attr( $mBOX['id'] );?>'>
					<?php 

					if(!empty($mBOX['content'])){
						echo $mBOX['content'];
					}else{
						switch($mBOX['id']){

							// VIRTUAL
							case 'ev_virtual':
								include_once 'class-meta_boxes-virtual.php';
							break;

							// health guidance
							case 'ev_health':
								include_once 'class-meta_boxes-health.php';
							break;

							// Event Status
							case 'ev_status':
								?><div class='evcal_data_block_style1 event_status_settings'>
									<div class='evcal_db_data'>
										<?php
										$_status = $EVENT->get_event_status();
										
										EVO()->elements->print_element( array(
											'type'=>'select_row',
											'row_class'=>'es_values',
											'name'=>'_status',
											'value'=> esc_attr( $_status ),
											'options'=> array_map('esc_html', $EVENT->get_status_array() )
										));
										?>
										<div class='cancelled_extra' style="display:<?php echo $_status =='cancelled'? 'block':'none';?>">
											<p><label><?php esc_html_e('Reason for cancelling','eventon');?></label><textarea name='_cancel_reason'><?php echo esc_textarea( $EVENT->get_prop('_cancel_reason') );?></textarea>
										</div>
										<div class='movedonline_extra' style="display:<?php echo $_status =='movedonline'? 'block':'none';?>">
											<p><label><?php esc_html_e('More details for online event','eventon');?></label><textarea name='_movedonline_reason'><?php echo esc_textarea( $EVENT->get_prop('_movedonline_reason') );?></textarea>
										</div>
										<div class='postponed_extra' style="display:<?php echo $_status =='postponed'? 'block':'none';?>">
											<p><label><?php esc_html_e('More details about postpone','eventon');?></label><textarea name='_postponed_reason'><?php echo esc_textarea( $EVENT->get_prop('_postponed_reason') );?></textarea>
										</div>
										<div class='rescheduled_extra' style="display:<?php echo $_status =='rescheduled'? 'block':'none';?>">
											<p><label><?php esc_html_e('More details about reschedule','eventon');?></label><textarea name='_rescheduled_reason'><?php echo esc_textarea( $EVENT->get_prop('_rescheduled_reason') );?></textarea>

											<?php /*
											<p>
												<label><?php esc_html_e('Previous start date (for SEO)','eventon');?></label></p>
											<div class='prev_start_date' style='background-color: #c3c3c3;padding: 10px; border-radius: 10px;'>
											<?php

												$wp_time_format = get_option('time_format');

												echo EVO()->elements->print_date_time_selector( array(
													'type'=>'prev',
													'unix'=> $EVENT->get_prop('_prevstartdate'),
													'time_format'=>$wp_time_format
												));

											?>	
											</div>
											*/?>
										</div>
									</div>
								</div>
								<?php
							break;

							// event attendance mode
							case 'ev_attendance':
								include_once 'class-meta_boxes-attendance.php';
							break;

							case 'ev_releated':
								include_once 'class-meta_boxes-related.php';								
							break;

							case 'ev_seo':
								echo "<div class='evo_meta_elements'>";
									EVO()->elements->print_process_multiple_elements(
										array(
											array(
												'type'=>'text',
												'name'=> esc_html__('Offer Price','eventon'),
												'tooltip'=> esc_html__('Offer price without the currency symbol.','eventon'),
												'id'=>'_seo_offer_price',
												'value'=> esc_attr( $EVENT->get_prop('_seo_offer_price') )
											),
											array(
												'type'=>'text',
												'name'=> esc_html__('Offer Price Currency Symbol','eventon'),
												'id'=>'_seo_offer_currency',
												'value'=> esc_attr( $EVENT->get_prop('_seo_offer_currency') )
											),array(
												'type'=>'notice',
												'name'=> esc_html__('NOTE: Leaving them blank may show a mild warning on google SEO, but will not effect your SEO rankings. For free events you can use 0.00 or Free as the Offer price.','eventon'),
											)
										)
									);
								
									echo "</div>";
							break;
							case 'ev_learnmore':
								echo "<div class='evo_meta_elements'>";
									
									EVO()->elements->print_process_multiple_elements(
										array(
											array(
												'type'=>'text',
												'name'=> esc_html__('Learn More Link','eventon'),
												'tooltip'=>'Type in your complete event link with http.',
												'id'=>'evcal_lmlink',
												'value'=> esc_attr( $EVENT->get_prop('evcal_lmlink') )
											),
											array(
												'type'=>'yesno_btn',
												'label'=> esc_html__('Open in New window','eventon'),
												'id'=>'evcal_lmlink_target',
												'value'=> esc_attr( $EVENT->get_prop('evcal_lmlink_target') ),
											),
										)
									);
								
								echo "</div>";

							break;
							case 'ev_lang':
								echo "<div class='evcal_data_block_style1'>
								<div class='evcal_db_data'>";
									?>
									<p><?php esc_html_e('You can select the eventon language corresponding to this event. Eg. If you have eventon language L2 in French and this event is in french select L2 as eventon language correspondant for this event.','eventon');?></p>
									<p>
										<label for="_evo_lang"><?php esc_html_e('Corresponding eventON language','eventon');?></label>
										<select name="_evo_lang">
										<?php 

										$_evo_lang = (!empty($ev_vals["_evo_lang"]))? $ev_vals["_evo_lang"][0]: 'L1';

										$lang_variables = apply_filters('eventon_lang_variation', array('L1','L2', 'L3'));

										foreach($lang_variables as $lang){
											$slected = ($lang == $_evo_lang)? 'selected="selected"':null;
											echo "<option value='". esc_attr( $lang )."' ". esc_attr( $slected ).">". esc_html( $lang )."</option>";
										}
										?></select>
									</p>

								<?php echo "</div></div>";
							break;
							case 'ev_uint':
								
								include_once ('class-meta_boxes-ui.php');
								
							break;

							case 'ev_location':

								// $opt = get_option( "evo_tax_meta");
								// print_r($opt);
								?>
								<div class='evcal_data_block_style1'>
									<p class='edb_icon evcal_edb_map'></p>
									<div class='evcal_db_data'>
										<div class='evcal_location_data_section'>										
											<div class='evo_singular_tax_for_event event_location' data-tax='event_location' data-eventid='<?php echo esc_attr( $p_id );?>'>
											<?php
												echo EVO()->taxonomies->get_meta_box_content( 'event_location' ,esc_attr( $p_id ), esc_html__('location','eventon'));
											?>
											</div>									
										</div>										
										<?php

											// if generate gmap enabled in settings
												$gen_gmap = !$EVENT->check_yn('evcal_gmap_gen') ? true: false;

											// yea no options for location
											foreach(array(
												'evo_access_control_location'=>array('evo_access_control_location',esc_html__('Make location information only visible to logged-in users','eventon')),
												'evcal_hide_locname'=>array('evo_locname',esc_html__('Hide Location Name from Event Card','eventon')),
												'evcal_gmap_gen'=>array('evo_genGmap',esc_html__('Generate Google Map from the address','eventon')),
												'evcal_name_over_img'=>array('evcal_name_over_img',esc_html__('Show location information over location image (If location image exist)','eventon')),
											) as $key=>$val){

												$variable_val = $EVENT->get_prop($key)? $EVENT->get_prop($key): 'no';

												if($variable_val == 'no' && $gen_gmap && $key=='evcal_gmap_gen')
														$variable_val = 'yes';

												EVO()->elements->print_element(
													array(
														'type'=>'yesno_btn',
														'label'=> esc_attr( $val[1] ), 
														'id'=> esc_attr( $key ),
														'value'=> esc_attr( $variable_val )
													)
												);
											}

											// check google maps API key
											if( !EVO()->cal->get_prop('evo_gmap_api_key','evcal_1')){
												echo "<p class='evo_notice'>".esc_html__('Google Maps API key is required for maps to show on event. Please add them via ','eventon') ."<a href='". esc_url( get_admin_url() ) .'admin.php?page=eventon#evcal_005'."'>".esc_html__('Settings','eventon'). "</a></p>";
											}
										?>									
									</div>
								</div>
								<?php
							break;

							case 'ev_organizer':
								?>
								<div class='evcal_data_block_style1'>
									<p class='edb_icon evcal_edb_map'></p>
									<div class='evcal_db_data'>
										<div class='evcal_location_data_section'>
											<div class='evo_singular_tax_for_event event_organizer' >
											<?php
												echo EVO()->taxonomies->get_meta_box_content( 'event_organizer', esc_attr( $p_id ), esc_html__('organizer','eventon'));
											?>
											</div>										
					                    </div><!--.evcal_location_data_section-->

					                    <?php
					                    EVO()->elements->print_process_multiple_elements(
											array(
												array(
													'type'=>'yesno_btn',
													'label'=> esc_html__('Hide Organizer field from EventCard','eventon'),
													'id'=>'evo_evcrd_field_org',
													'value'=> esc_attr( $EVENT->get_prop('evo_evcrd_field_org') ),
												),
												array(
													'type'=>'yesno_btn',
													'label'=> esc_html__('SEO: Use organizer information to also populate performer schema data for this event.','eventon'),
													'id'=>'evo_event_org_as_perf',
													'value'=> esc_attr( $EVENT->get_prop('evo_event_org_as_perf') ),
												),
											)
										);
					                    ?>
									</div>
								</div>
								<?php
							break;

							case 'ev_timedate':
								
								include_once ('class-meta_boxes-timedate.php');
								
							break;

							case 'ev_subtitle':
								?><div class='evcal_data_block_style1'><input type='text' id='evcal_subtitle' name='evcal_subtitle' value="<?php echo esc_html( $EVENT->get_prop('evcal_subtitle'));?>" style='width:100%'/></div>
								<?php
							break;
						}
						

					}
					?>					
					</div>
				</div>
			<?php	endforeach;	?>
					<div class='evomb_section additional_functionality' id='ev_add_func'>			
						<div class='evomb_header'>
							<span class="evomb_icon evII"><i class="fa fa-plug"></i></span>
							<p><?php esc_html( esc_html_e('Expand EventON Lite','eventon') );?></p>
						</div>
						<div class='evomb_body' style=''>
							<p style='padding:15px 25px; margin:0; background-color:#f9d29f;background: linear-gradient(45deg, #f9d29f, #ff9f5b); color:#474747; text-align:center;border-radius: 10px; ' class="evomb_body_additional">
								<span style='text-transform:uppercase; font-size:18px; display:block; font-weight:bold'><?php esc_html(esc_html_e('Need more cool features?','eventon'));?></span>
								<span style='font-weight:normal'><?php echo sprintf(
									__('Like selling tickets, front-end event submissions, RSVPing to events, sliders and etc.?<br/> <a class="evo_btn" href="%1$s" target="_blank" style="margin-top:10px;">EventON FUll</a><a class="evo_btn" href="%2$s" target="_blank" style="margin-top:10px;">EventON addons</a>',
										'eventon'
									), 
									'https://www.myeventon.com',
									'https://www.myeventon.com/addons/'
									);?></span>
							</p>
						</div>
					</div>	
			</div>
		<?php  
		}

	// for custom meta boxes
		function ajde_evcal_show_box_cmf(){
			?>
			<div id='evo_mb' class='eventon_mb'>
				<?php include_once( 'class-meta_boxes-cmf.php');?>
			</div>
			<?php
		}

	// THIRD PARTY event related settings 
		function ajde_evcal_show_box_3(){	
						
			$evcal_opt1= get_option('evcal_options_evcal_1');
				$evcal_opt2= get_option('evcal_options_evcal_2');
				
				// Use nonce for verification
				//wp_nonce_field( plugin_basename( __FILE__ ), 'evo_noncename_mb3' );
				
				// The actual fields for data entry
				$ev_vals = $this->event_data;
			
			?>
			<table id="meta_tb" class="form-table meta_tb evoThirdparty_meta" >
				<?php
					// (---) hook for addons
					if(has_action('eventon_post_settings_metabox_table'))
						do_action('eventon_post_settings_metabox_table');
				
					if(has_action('eventon_post_time_settings'))
						do_action('eventon_post_time_settings');

				// PAYPAL
					if($evcal_opt1['evcal_paypal_pay']=='yes'):
					?>
					<tr>
						<td colspan='2' class='evo_thirdparty_table_td'>
							<div class='evo3rdp_header'>
								<span class='evo3rdp_icon'><i class='fa fa-paypal'></i></span>
								<p><?php esc_html(esc_html_e('Paypal "BUY NOW" button','eventon'));?></p>
							</div>	
							<div class='evo_3rdp_inside'>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_text'><?php esc_html(esc_html_e('Text to show above buy now button','eventon'));?></label><br/>			
									<input type='text' id='evcal_paypal_text' name='evcal_paypal_text' value='<?php echo (!empty($ev_vals["evcal_paypal_text"]) )? esc_attr( $ev_vals["evcal_paypal_text"][0] ):null?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'><label for='evcal_paypal_item_price'><?php esc_html(esc_html_e('Enter the price for paypal buy now button <i>eg. 23.99</i> (WITHOUT currency symbol)')); ?><?php EVO()->elements->tooltips(esc_html__('Type the price without currency symbol to create a buy now button for this event. This will show on front-end calendar for this event','eventon'),'',true);?></label><br/>			
									<input placeholder='eg. 29.99' type='text' id='evcal_paypal_item_price' name='evcal_paypal_item_price' value='<?php echo (!empty($ev_vals["evcal_paypal_item_price"]) )? esc_attr( $ev_vals["evcal_paypal_item_price"][0] ):null; ?>' style='width:100%'/>
								</p>
								<p class='evo_thirdparty'>
									<label for='evcal_paypal_email'><?php esc_html_e('Custom Email address to receive payments','eventon')?><?php EVO()->elements->tooltips('This email address will override the email saved under eventON settings for paypal to accept payments to this email instead of paypal email saved in eventon settings.','',true);?></label><br/>			
									<input type='text' id='evcal_paypal_email' name='evcal_paypal_email' value='<?php echo (!empty($ev_vals["evcal_paypal_email"]) )? esc_attr( $ev_vals["evcal_paypal_email"][0] ) :null?>' style='width:100%'/>
								</p>
							</div>		
						</td>			
					</tr>
					<?php endif; ?>
				</table>
			<?php
		}
		
	// Save the Event data meta box
		function eventon_save_meta_data($post_id, $post){
			if($post->post_type!='ajde_events')
				return;
				
			// Stop WP from clearing custom fields on autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
				return;

			// Prevent quick edit from clearing custom fields
			if (defined('DOING_AJAX') && DOING_AJAX)
				return;

			
			// verify this came from the our screen and with proper authorization,
			// because save_post can be triggered at other times
			if( isset($_POST['evo_noncename']) ){
				if ( !wp_verify_nonce( $_POST['evo_noncename'], plugin_basename( __FILE__ ) ) ){
					return;
				}
			}
			// Check permissions
			if ( !current_user_can( 'edit_post', $post_id ) )
				return;	

			global $pagenow;
			$_allowed = array( 'post-new.php', 'post.php' );
			if(!in_array($pagenow, $_allowed)) return;

			$this->EVENT = $EVENT = new EVO_Event($post_id);
			
			$HELP = new evo_helper();
			$post_data = $HELP->recursive_sanitize_array_fields( $_POST );
						
			// $_POST FIELDS array
				$fields_ar =apply_filters('eventon_event_metafields', array(
					'evcal_allday',
					'evcal_event_color','evcal_event_color_n',
					'evcal_event_color2','evcal_event_color_n2',
					'evcal_exlink','evcal_lmlink','evcal_subtitle',
					'evcal_hide_locname','evcal_gmap_gen','evcal_name_over_img', 'evo_access_control_location',
					'evcal_mu_id','evcal_paypal_item_price','evcal_paypal_text','evcal_paypal_email',
					'evcal_repeat','_evcal_rep_series','_evcal_rep_endt','_evcal_rep_series_clickable','evcal_rep_freq','evcal_rep_gap','evcal_rep_num',
					'evp_repeat_rb','evo_repeat_wom','evo_rep_WK','evp_repeat_rb_wk','evo_rep_WKwk',
					'evcal_lmlink_target','_evcal_exlink_target','_evcal_exlink_option',
					'evo_hide_endtime','evo_span_hidden_end','evo_year_long','_evo_month_long',
					'evo_evcrd_field_org','evo_event_org_as_perf',

					'evo_exclude_ev',				
					'ev_releated',				
				), $post_id);

			// fields allowed to pass HTML
				$fields_with_html = apply_filters('evo_event_metafields_htmlcontent',
					array('evcal_subtitle',)
				);

			// append custom fields based on activated number
				$evcal_opt1= get_option('evcal_options_evcal_1');
				$num = evo_calculate_cmd_count($evcal_opt1);
				for($x =1; $x<=$num; $x++){	
					if(eventon_is_custom_meta_field_good($x)){
						$fields_ar[]= '_evcal_ec_f'.$x.'a1_cus';
						$fields_ar[]= '_evcal_ec_f'.$x.'a1_cusL';
						$fields_ar[]= '_evcal_ec_f'.$x.'_onw';
					}
				}


			// array of post meta fields that should be deleted from event post meta
				foreach(array(
					'evo_location_tax_id','evo_organizer_tax_id','_cancel'
				) as $ff){
					delete_post_meta($post_id, $ff);
				}

			// Backward compatible cancel event v 2.8.7
				if(!isset($post_data['_status']) && isset($post_data['_cancel']) && $post_data['_cancel'] == 'yes'){
					$post_data['_status'] = 'cancelled';
				}

			// Add _ event meta values
				foreach($post_data as $F=>$V){
					if(substr($F, 0,1) === '_'){
						$fields_ar[] = $F;
					}
				}

			// process the time pieces into unix values
				$proper_time = 	evoadmin_get_unix_time_fromt_post($post_id);
			

			// if Repeating event save repeating intervals
				if( eventon_is_good_repeat_data()  ){

					if(!empty($proper_time['unix_start'])){

						$unix_E = $end_range = (!empty($proper_time['unix_end']))? $proper_time['unix_end']: $proper_time['unix_start'];
						$repeat_intervals = eventon_get_repeat_intervals($proper_time['unix_start'], $unix_E);

						// save repeat interval array as post meta
						if ( !empty($repeat_intervals) ){

							$E = end($repeat_intervals);
							$end_range = $E[1];

							update_post_meta( $post_id, 'repeat_intervals', $repeat_intervals);
						}else{
							delete_post_meta( $post_id, 'repeat_intervals');
						}
					}
				}

			// full time converted to unix time stamp
				if ( !empty($proper_time['unix_start']) )
					update_post_meta( $post_id, 'evcal_srow', $proper_time['unix_start']);
				
				if ( !empty($proper_time['unix_end']) )
					update_post_meta( $post_id, 'evcal_erow', $proper_time['unix_end']);

			// save adjusted event times
				foreach( array( 'unix_start_ev', 'unix_end_ev', 'unix_vend_ev') as $f){
					if ( !empty($proper_time[ $f ]) ) 
						update_post_meta( $post_id,  '_'.$f , $proper_time[ $f ]);
				}

			// save previous start date for reschedule events
				if( isset($post_data['_status']) && $post_data['_status'] == 'rescheduled' && isset($post_data['event_prev_date_x'])
				){	
					$date = $post_data['event_prev_date_x'];
				}


			// run through all the custom meta fields @u4.5.5
				foreach($fields_ar as $f_val){
					
					// make sure values are not empty at $_POST level
					if(!empty($_POST[$f_val])){

						$post_value = ( $post_data[$f_val]);

						// urls passed on @2.3
						if( ($f_val == 'evcal_exlink' && $post_data['_evcal_exlink_option']=='2' ) 
							|| in_array($f_val, array('evcal_lmlink'))
						){
							$EVENT->set_prop( $f_val , esc_url_raw( $_POST[ $f_val ] )  );
							continue;
						}

						// for fields with HTML content @since 4.3.3
						if( in_array($f_val, $fields_with_html)){
							$EVENT->set_prop($f_val, $HELP->sanitize_html( $_POST[ $f_val ] ));
							continue;
						}

						// for saving custom meta fields @since 4.3.3 @4.5.5
						if( strpos($f_val, '_evcal_ec_f') !== false ){
							$post_value = $HELP->sanitize_html( $_POST[$f_val]);			
						}
						
						$EVENT->set_prop( $f_val , $post_value);

						// ux val for single events linking to event page	
						if($f_val=='evcal_exlink' && $post_data['_evcal_exlink_option']=='4'){
							$EVENT->set_prop( 'evcal_exlink' , get_permalink($post_id) );
						}

					}else{
						delete_post_meta($post_id, $f_val);
					}					
				}

			// Save all event data values
				if( isset($post_data['_edata']) ){
					$this->EVENT->set_prop('_edata', $post_data['_edata']);
				}							
			
			// Other data		
				//set event color code to 1 for none select colors
					if ( !isset( $post_data['evcal_event_color_n'] ) )
						update_post_meta( $post_id, 'evcal_event_color_n',1);
									
				// save featured event data default value no
					$_featured = get_post_meta($post_id, '_featured',true);
					if(empty( $_featured) )
						update_post_meta( $post_id, '_featured','no');

				// language corresponding
					if(empty($post_data['_evo_lang']))
						update_post_meta( $post_id, '_evo_lang','L1');
			
						
			// (---) hook for addons
			do_action('eventon_save_meta', $fields_ar, $post_id, $this->EVENT);

			// save user closed meta field boxes
			if(!empty($post_data['evo_collapse_meta_boxes']))
				eventon_save_collapse_metaboxes($post_id, $post_data['evo_collapse_meta_boxes'],true );
				
		}

	// Process metabox content
	// @since 4.2.3
		function process_content($array){
			$output = '';

			$visibility_types = array(
				'all'=>		esc_html__('Everyone','eventon'),
				'admin'=>	esc_html__('Admin Only','eventon'),
				'loggedin'=>esc_html__('Loggedin Users Only','eventon')
			);

			ob_start();

			foreach($array as $mBOX):

				if( empty($mBOX['content'])) continue;

				$closed = isset($mBOX['close']) && $mBOX['close'] ? 'closed' : '';

				// initials
					$icon_style = (!empty($mBOX['iconURL']))?
						'background-image:url('. esc_url( $mBOX['iconURL'] ).')'
						:'background-position:'. esc_attr( $mBOX['iconPOS'] );
					$icon_class = (!empty($mBOX['iconPOS']))? 'evIcons':'evII';
					
					
					$hiddenVal = (!empty($mBOX['hiddenVal']))?
						'<span class="hiddenVal">'. esc_attr( $mBOX['hiddenVal'] ).'</span>':null;

					// visibility type ONLY for custom meta fields
						$visibility_type = (!empty($mBOX['visibility_type'])) ? 
							"<span class='visibility_type'>". __('Visibility Type:','eventon').' '. esc_html( $visibility_types[$mBOX['visibility_type']] ) .'</span>': null;
				
				?>
				<div class='evomb_section' id='<?php echo esc_attr($mBOX['id']);?>'>			
					<div class='evomb_header <?php echo esc_attr( $closed );?>'>
						<?php // custom field with icons
							if(!empty($mBOX['variation']) && $mBOX['variation']	=='customfield'):?>	
							<span class='evomb_icon <?php echo esc_attr( $icon_class );?>'><i class='fa <?php echo esc_attr( $mBOX['iconURL'] ); ?>'></i></span>
							
						<?php else:	?>
							<span class='evomb_icon <?php echo esc_attr( $icon_class );?>' style='<?php echo esc_attr( $icon_style );?>'></span>
						<?php endif; ?>
						<p><?php echo esc_html( $mBOX['name'] );?><?php echo  $hiddenVal;?><?php EVO()->elements->print_tooltips( !empty($mBOX['guide']) ? $mBOX['guide'] : null );?><?php echo  $visibility_type;?></p>
					</div>
					<div class='evomb_body <?php echo esc_attr( $closed );?>' box_id='<?php echo esc_attr( $mBOX['id'] );?>'>
						<?php	 echo ( $mBOX['content'] );?>
					</div>
				</div>
			<?php 
			endforeach;

			return ob_get_clean();
		}
}
