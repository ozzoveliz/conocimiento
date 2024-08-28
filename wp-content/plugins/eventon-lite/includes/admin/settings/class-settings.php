<?php
/**
	EventON Settings Main Object
	@version Lite 2.2.16
*/
class EVO_Settings{
	
	public $page; 
	public $focus_tab;
	public $current_section, $options_pre;
	private $tab_props = false;

	public function __construct(){
		$this->page = (isset($_GET['page']) )? sanitize_text_field( $_GET['page'] ):false;
		//$this->focus_tab = (isset($_GET['tab']) )? sanitize_text_field( urldecode($_GET['tab'])):'evcal_1';
		$this->current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';
		

		// update focus tab based on page
			$page_tabs = array(
				'eventon'=>'evcal_1',
				'eventon-lang'=>'evcal_2',
				'eventon-styles'=>'evcal_3',
				'eventon-extend'=>'evcal_4',
				'eventon-support'=>'evcal_5',
			);
			if( $this->page && array_key_exists( $this->page, $page_tabs) ) $this->focus_tab = $page_tabs[ $this->page ];


		$this->options_pre = 'evcal_options_';
	}

// Styles and scripts
	public function load_styles_scripts(){
		wp_enqueue_media();	

		wp_enqueue_script('evcal_functions', EVO()->assets_path. 'js/eventon_functions.js', array('jquery'), EVO()->version ,true );

		wp_enqueue_style('settings_styles');
		wp_enqueue_script('settings_script');

		EVO()->elements->load_colorpicker();
	}

	public function register_ss(){
		$this->register_styles();
		$this->register_scripts();

		EVO()->elements->register_colorpicker();
	}
	public function register_styles(){
		wp_register_style( 'settings_styles',EVO()->assets_path.'lib/settings/settings.css','',EVO()->version);		
	}

	public function register_scripts(){
		wp_register_script('settings_script',EVO()->assets_path.'lib/settings/settings.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), EVO()->version, true );
		
		EVO()->elements->register_shortcode_generator_styles_scripts();
	}

// CONTENT
	function print_page(){
		// Settings Tabs array
			$evcal_tabs = apply_filters('eventon_settings_tabs',array(
				'eventon'=>__('Settings', 'eventon'), 
				'eventon-lang'=>__('Language', 'eventon'),
				'eventon-styles'=>__('Styles', 'eventon'),
				'eventon-extend'=>__('Extend', 'eventon'),
				'eventon-support'=>__('Support', 'eventon'),
			));		
			
			// Get current section
				$this->current_section = (isset($_GET['section']) )? sanitize_text_field( urldecode($_GET['section'])):'';	

			// Update or add options
				$this->evo_save_settings();
				
			// Load eventon settings values for current tab
				$evcal_opt = $this->get_current_tab_values();	
			
			// OTHER options
				$genral_opt = get_option('evcal_options_evcal_1');

		// TABBBED HEADER	
		$this->header_wraps(array(
			'version'=>get_option('eventon_plugin_version'),
			'title'=>__('EventON Lite Settings','eventon'),
			'tabs'=>$evcal_tabs,
			'tab_page'=>'?page=',
			'tab_attr_field'=>'evcal_meta',
			'tab_attr_pre'=>'evcal_',
			'tab_id'=>'evcal_settings'
		));	

		require_once(AJDE_EVCAL_PATH.'/includes/admin/settings/class-settings-content.php');
	}

// INITIATION
	function get_current_tab_values(){		
		$current_tab_number = substr($this->focus_tab, -1);
		EVO()->cal->reload_option_data( $this->focus_tab );

		$tab_props = $this->tab_props = EVO()->cal->get_op( $this->focus_tab  );

		return array( $current_tab_number => $tab_props );
	}

	function get_prop($field){
		if(!isset($this->tab_props[$field])) return false;
		return $this->tab_props[$field];
	}

// Event Edit Settings
// @since 4.2.1 @updated 4.5
	function get_event_edit_settings($data){
		ob_start();

		$args = array(
			'hidden_fields'=> array(),
			'form_class'=>'',
			'container_class'=>'',
			'fields'=> array(),
			'save_btn_data'=> array(),
			'nonce_action'=>'eventon',// nonce field name
			'footer_btns'=> array(
				'save_changes'=> array(
					'label'=> __('Save Changes','eventon'),
					'data'=> array(),
					'class'=> 'evo_btn evolb_trigger_save',
					'href'=>'',
					'target'=> ''
				)
			)
		);

		$args = array_merge($args, $data);
		extract($args);
		?>
		<div class='<?php echo esc_attr( $container_class );?>'>
			<form class='<?php echo esc_attr( $form_class );?>'>
				<?php 

				// include nonce field
				wp_nonce_field( $nonce_action, 'evo_noncename' );
				
				foreach($hidden_fields as $k=>$v){
					echo "<input type='hidden' name='". esc_attr($k)."' value='". esc_html( $v )."'>";
				}

				EVO()->elements->print_process_multiple_elements( $fields );

				?>
				<p>					
					<?php 
					foreach( $footer_btns as $btn):
						if(!isset( $btn['label'] )) continue;
						$href = isset($btn['href']) && !empty( $btn['href'] )?  $btn['href'] :'#';
						$target = isset($btn['target']) && !empty( $btn['target'] ) ? $btn['target'] : '';

						?><a href='<?php echo esc_url( $href ); ?>' target='<?php echo esc_attr( $target );?>' class='<?php echo esc_attr( $btn['class'] );?>' data-d='<?php echo wp_json_encode($btn['data']);?>' style=''><?php echo esc_html( $btn['label'] );?></a>
					<?php endforeach;?>
					
				</p>	
			</form>
		</div>
		<?php 
		return ob_get_clean();
	}

// OTHER
	function header_wraps($args){
		?>
		<div class="wrap ajde_settings <?php echo esc_attr( $this->focus_tab );?>" id='<?php echo esc_attr($args['tab_id']);?>'>
			<div class='evo_settings_header'>
				<h2><?php echo esc_html($args['title']);?> (ver <?php echo esc_html($args['version']);?>)</h2>
				<h2 class='nav-tab-wrapper' id='meta_tabs'>
					<?php					
						foreach($args['tabs'] as $key=>$val){
							
							echo "<a href='". esc_url( $args['tab_page']) ."". esc_html($key)."' class='nav-tab ".( ($this->page == $key)? 'nav-tab-active':null)." ". esc_attr($key)."' ". 
								( (!empty($args['tab_attr_field']) && !empty($args['tab_attr_pre']))? 
									esc_attr( $args['tab_attr_field'] ) . "='". esc_attr( $args['tab_attr_pre'] . $key ). "'":'') . ">". esc_html( $val)."</a>";
						}			
					?>		
				</h2>
			</div>
		<?php
	}

	function settings_tab_start($args){
		?>
		<form method="post" action="">
			<?php settings_fields($args['field_group']); ?>
			<?php wp_nonce_field( $args['nonce_key'], $args['nonce_field'] );?>
		<div id="<?php echo esc_attr($args['tab_id']);?>" class="<?php echo esc_attr( implode(' ', $args['classes']) );?>">
			<div class="<?php echo esc_attr( implode(' ', $args['inside_classes']) );?>">
				<?php
	}
	function settings_tab_end(){
		?></div></div><?php
	}

	// deprecating
	function save_settings($nonce_key, $nonce_field, $options_pre){
		if( isset($_POST[$nonce_field]) && isset( $_POST ) ){
			if ( wp_verify_nonce( $_POST[$nonce_field], $nonce_key ) ){
				foreach($_POST as $pf=>$pv){
					$pv = (is_array($pv))? $pv: addslashes(esc_html(stripslashes(($pv)))) ;
					$options[$pf] = $pv;
				}
				EVO()->cal->set_cur( $this->focus_tab );
				EVO()->cal->set_option_values( $options );
			}
		}
	}

	function evo_save_settings(){
		$focus_tab = $this->focus_tab;
		$current_section = $this->current_section;

		if( isset($_POST['evcal_noncename']) && isset( $_POST ) ){
			if ( wp_verify_nonce( $_POST['evcal_noncename'], AJDE_EVCAL_BASENAME ) ){

				$evcal_options = array();
				
				// run through all post values
				foreach($_POST as $pf=>$pv){
					// skip fields
					if(in_array($pf, array('option_page', 'action','_wpnonce','_wp_http_referer','evcal_noncename'))) continue;

					$none_san_fields = array('evo_ecl','evcal_sort_options');

					if( ($pf!='evcal_styles' && $focus_tab!='evcal_4') || !in_array($pf, $none_san_fields) ){
						
						// none array values
						if( !is_array($pv) )	$pv = sanitize_text_field( $pv );

						$evcal_options[$pf] = $pv;
					}
					if( in_array($pf, $none_san_fields) ){
						$evcal_options[$pf] = $pv;
					}					
				}
				
				// General settings page - write styles to head option
				if($focus_tab=='evcal_1' && isset($_POST['evcal_css_head']) && $_POST['evcal_css_head']=='yes'){
					EVO()->evo_admin->update_dynamic_styles();
				}		

				// Hook
					do_action('evo_before_settings_saved', $focus_tab, $current_section,  $evcal_options);			
				
				//language tab
					if($focus_tab=='evcal_2'){						
						
						$new_lang_opt = array();
						$_lang_version = (!empty($_GET['lang']))? sanitize_text_field($_GET['lang']): 'L1';

						// process duplicates
						foreach($evcal_options as $F=>$V){
							if(strpos($F, '_v_') !== false && !empty($V) ){
								$_F = str_replace('_v_', '', $F);

								$evcal_options[ $_F ] = $V;
							}
						}

						$lang_opt = get_option('evcal_options_evcal_2');
						if(!empty($lang_opt) ){
							$new_lang_opt[$_lang_version] = $evcal_options;
							$new_lang_opt = array_merge($lang_opt, $new_lang_opt);
						}else{
							$new_lang_opt[$_lang_version] =$evcal_options;
						}
						
						update_option('evcal_options_evcal_2', $new_lang_opt);
						
					}

				elseif($focus_tab == 'evcal_1' || empty($focus_tab)){
					// store custom meta box count
					$cmd_count = evo_calculate_cmd_count();
					$evcal_options['cmd_count'] = $cmd_count;

					update_option('evcal_options_'.$focus_tab, $evcal_options);

				// all other settings tabs
				}else{
					//do_action('evo_save_settings',$focus_tab, $evcal_options);
					$evcal_options = apply_filters('evo_save_settings_optionvals', $evcal_options, $focus_tab);
					update_option('evcal_options_'.$focus_tab, $evcal_options);
				}
				
				// STYLES
				if( isset($_POST['evcal_styles']) )
					update_option('evcal_styles', wp_strip_all_tags(stripslashes($_POST['evcal_styles'])) );

				// PHP Codes
				if( isset($_POST['evcal_php']) ){
					update_option('evcal_php', wp_strip_all_tags(stripslashes($_POST['evcal_php'])) );
				}

				// Hoook for when settings are saved
					do_action('evo_after_settings_saved', $focus_tab, $current_section,  $evcal_options);
				
				$_POST['settings-updated']='true';			
			
				// update dynamic styles file
					EVO()->evo_admin->generate_dynamic_styles_file();

				// update the global values with new saved settings values
				$this->tab_props = $evcal_options;
				$GLOBALS['EVO_Settings'][$this->options_pre .$focus_tab] = $this->tab_props;

			// nonce check
			}else{
				echo '<div class="notice error"><p>'. esc_html( __('Settings not saved, nonce verification failed! Please try again later!','eventon') ).'</p></div>';
			}	
		}
	}
	
// Print Form Content
	
	public function print_ajde_customization_form($cutomization_pg_array, $ajdePT, $extra_tabs=''){
		
		
		// initial variables
			$font_sizes = array('10px','11px','12px','13px','14px','16px','18px','20px', '22px', '24px','28px','30px','36px','42px','48px','54px','60px');
			$opacity_values = array('0.0','0.1','0.2','0.3','0.4','0.5','0.6','0.7','0.8','0.9','1',);
			$font_styles = array('normal','bold','italic','bold-italic');
			
			$__no_hr_types = array('begin_afterstatement','end_afterstatement','hiddensection_open','hiddensection_close','sub_section_open','sub_section_close');
		
			//define variables
			$leftside=$rightside='';
			$count=1;

		// icon selection
			$rightside.= EVO()->elements->get_icon_html();
		
		// different types of content
			/*
				notice, image, icon, subheader, note, checkbox, text. textarea, font_size, font_style, border_radius, color, fontation, multicolor, radio, dropdown, checkboxes, yesno, begin_afterstatement, end_afterstatement, hiddensection_open, hiddensection_close, customcode
			*/

		foreach($cutomization_pg_array as $cpa=>$cpav){								
			// left side tabs with different level colors
			$ls_level_code = (isset($cpav['level']))? 'class="'. esc_attr( $cpav['level'] ).'"': null;
			

			$leftside .= '<li ' . $ls_level_code . '><a class="' . (($count == 1) ? 'focused' : '') . '" data-c_id="' . esc_attr($cpav['id']) . '" title="' . esc_attr($cpav['tab_name']) . '"><i class="fa fa-' . esc_attr(!empty($cpav['icon']) ? $cpav['icon'] : 'edit') . '"></i>' .  $cpav['tab_name']  . '</a></li>';

					
			$tab_type = (isset($cpav['tab_type'] ) )? $cpav['tab_type']:'';
			if( $tab_type !='empty'){ // to not show the right side

				
				// RIGHT SIDE
				$display_default = (!empty($cpav['display']) && $cpav['display']=='show')?'':'display:none';
				
				$rightside.= "<div id='".$cpav['id']."' style='".$display_default."' class='nfer'>
					<h3>".  esc_html( $cpav['name'] ) ."</h3>";

					if(!empty($cpav['description']))
						$rightside.= "<p class='tab_description'>".$cpav['description']."</p>";
				
				$rightside.="<em class='hr_line'></em>";					
					// font awesome
					require_once(AJDE_EVCAL_PATH.'/assets/fonts/fa_fonts.php');	

					$rightside.= "<div style='display:none' class='fa_icons_selection'><div class='fai_in'><ul class='faicon_ul'>";
					
					// $font_ passed from incldued font awesome file above
					if(!empty($font_)){
						$C = 1;
						foreach($font_ as $fa){
							$rightside.= "<li class='{$C}'><i data-name='".$fa."' class='fa ".$fa."' title='{$fa}'></i></li>";
							$C++;
						}
					}
					$rightside.= "</ul>";
					$rightside.= "</div></div>";

				// EACH field
				foreach($cpav['fields'] as $field){

					if( !isset($field['type'])) continue;

					if($field['type']=='text' || $field['type']=='textarea'){
						$FIELDVALUE = (!empty($ajdePT[ $field['id']]))? 
								htmlspecialchars( stripslashes($ajdePT[ $field['id']]) ): 
								null;
					}
					
					// LEGEND or tooltip
					$legend_code = (!empty($field['legend']) )? EVO()->elements->tooltips($field['legend'], 'L', false):
						( (!empty($field['tooltip']) )? EVO()->elements->tooltips($field['tooltip'], 'L', false): null );

					// new label
					if( isset($field['ver']) && $field['ver'] == EVO()->version){
						$legend_code .= "<span class='new evonewtag' title='".__('New in version','eventon') .' '. EVO()->version."'>new</span>";
					}
									
					switch ($field['type']){
						// notices
						case 'notice':
							$rightside.= "<div class='ajdes_notice'>". wp_kses_post( $field['name'] )."</div>";
						break;
						//IMAGE
						case 'image':
							$image = ''; 
							$meta = isset($ajdePT[$field['id']])? $ajdePT[$field['id']]:false;
							
							$preview_img_size = (empty($field['preview_img_size']))?'medium'
								: $field['preview_img_size'];
							
							$rightside.= "<div id='pa_".$field['id']."'><p>".$field['name'].$legend_code."</p>";
							
							if ($meta) { $image = wp_get_attachment_image_src($meta, $preview_img_size); $image = $image[0]; } 
							
							$display_saved_image = (!empty($image))?'block':'none';
							$opp = ($display_saved_image=='block')? 'none':'block';

							$rightside.= "<p class='ajde_image_selector'>";
							$rightside.= "<span class='ajt_image_holder' style='display:{$display_saved_image}'><b class='ajde_remove_image'>X</b><img src='{$image}'/></span>";
							$rightside.= "<input type='hidden' class='ajt_image_id' name='{$field['id']}' value='{$meta}'/>";
							$rightside.= "<input type='button' class='ajt_choose_image button' style='display:{$opp}' value='".__('Choose an Image','ajde')."'/>";
							$rightside.= "</p></div>";
							
						break;
						
						case 'icon':
							$field_value = (!empty($ajdePT[ $field['id']]) )? 
								$ajdePT[ $field['id']]:$field['default'];

							$rightside.= "<div class='row_faicons'><p class='fieldname'>". wp_kses_post( $field['name'] )."</p>";
							
							// code
							$rightside .= EVO()->elements->get_element(array(
								'type'=>'icon_select',
								'id'=> $field['id'],
								'value'=> $field_value,
								'close'=>false,
							));
							
							$rightside.= "<div class='clear'></div></div>";
						break;

						case 'subheader':
							$rightside.= "<h4 class='acus_subheader'>". wp_kses_post( $field['name'] ) ."</h4>";
						break;
						case 'note':
							$rightside.= "<p class='ajde_note'><i>". wp_kses_post( $field['name'] ) ."</i></p>";
						break;
						case 'hr': $rightside.= "<em class='hr_line'></em>"; break;
						case 'checkbox':
							$this_value= (!empty($ajdePT[ $field['id']]))? $ajdePT[ $field['id']]: null;						
							$rightside.= "<p><input type='checkbox' name='".$field['id']."' value='yes' ".(($this_value=='yes')?'checked="/checked"/':'')."/> ".$field['name']."</p>";
						break;
						case 'text':
							$placeholder = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;

							$show_val = false; $hideable_text = '';
							if(isset($field['hideable']) && $field['hideable'] && !empty($FIELDVALUE)){
								$show_val = true;
								$hideable_text = "<span class='evo_hideable_show' data-t='". __('Hide', 'eventon') ."'>". __('Show','eventon'). "</span>";
							}
							
							$rightside.= "<p>". wp_kses_post( $field['name'] ) .$legend_code. $hideable_text. "</p><p class='field_container'><span class='nfe_f_width'>";

							if($show_val ){
								$rightside.= "<input type='password' style='' name='".$field['id']."'";
								$rightside.= 'value="'. $FIELDVALUE .'"';
							}else{
								$rightside.= "<input type='text' name='".$field['id']."'";
								$rightside.= 'value="'. $FIELDVALUE .'"';
							}
							
							$rightside.= $placeholder."/></span></p>";
						break;
						case 'password':
							$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
							
							$rightside.= "<p>". wp_kses_post( $field['name'] ) .$legend_code."</p><p><span class='nfe_f_width'><input type='password' name='".$field['id']."'";
							$rightside.= 'value="'.$FIELDVALUE.'"';
							$rightside.= $default_value."/></span></p>";
						break;
						case 'textarea':
							$default_value = (!empty($field['default']) )? 'placeholder="'.$field['default'].'"':null;
							$rightside.= "<p>". wp_kses_post( $field['name'] ) .$legend_code."</p><p><span class='nfe_f_width'><textarea name='".$field['id']."' {$default_value}>".$FIELDVALUE."</textarea></span></p>";
						break;
						case 'font_size':
							$rightside.= "<p>". wp_kses_post( $field['name'] ) ." <select name='".$field['id']."'>";
								$ajde_fval = $ajdePT[ $field['id'] ];
								
								foreach($font_sizes as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'opacity_value':
							$rightside.= "<p>". wp_kses_post( $field['name'] ) ." <select name='".$field['id']."'>";
								$ajde_fval = $ajdePT[ $field['id'] ];
								
								foreach($opacity_values as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'font_style':
							$rightside.= "<p>". wp_kses_post( $field['name'] ) ." <select name='".$field['id']."'>";
								$ajde_fval = $ajdePT[ $field['id'] ];
								foreach($font_styles as $fs){
									$selected = ($ajde_fval == $fs)?"selected='selected'":null;	
									$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
								}
							$rightside.= "</select></p>";
						break;
						case 'border_radius':
							$rightside.= "<p>". wp_kses_post( $field['name'] ) ." <select name='".$field['id']."'>";
									$ajde_fval = $ajdePT[ $field['id'] ];
									$border_radius = array('0px','2px','3px','4px','5px','6px','8px','10px');
									foreach($border_radius as $br){
										$selected = ($ajde_fval == $br)?"selected='selected'":null;	
										$rightside.=  "<option value='$br' ".$selected.">$br</option>";
									}
							$rightside.= "</select></p>";
						break;
						case 'color':

							// default hex color
							$hex_color = (!empty($ajdePT[ $field['id']]) )? 
								$ajdePT[ $field['id']]:$field['default'];
							$hex_color_val = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]: null;

							// RGB Color for the color box
							$rgb_color_val = (!empty($field['rgbid']) && !empty($ajdePT[ $field['rgbid'] ]))? $ajdePT[ $field['rgbid'] ]: null;
							$__em_class = (!empty($field['rgbid']))? ' rgb': null;

							$rightside.= "<p class='acus_line color'>
								<em><span class='colorselector{$__em_class}' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$hex_color."'></span>
								<input name='".$field['id']."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='".$field['default']."'/>";
							if(!empty($field['rgbid'])){
								$rightside .= "<input name='".$field['rgbid']."' class='rgb' type='hidden' value='".$rgb_color_val."' />";
							}
							$rightside .= "</em>". wp_kses_post( $field['name'] ) ." </p>";					
						break;					

						case 'fontation':

							$variations = $field['variations'];
							$rightside.= "<div class='row_fontation'><p class='fieldname'>". wp_kses_post( $field['name'] ) ."</p>";

							foreach($variations as $variation){
								switch($variation['type']){
									case 'color':
										// default hex color
										$hex_color = (!empty($ajdePT[ $variation['id']]) )? 
											$ajdePT[ $variation['id']]:$variation['default'];
										$hex_color_val = (!empty($ajdePT[ $variation['id'] ]))? $ajdePT[ $variation['id'] ]: null;
										
										$title = (!empty($variation['name']))? $variation['name']:$hex_color;
										$_has_title = (!empty($variation['name']))? true:false;

										// code
										$rightside.= "<p class='acus_line color'>
											<em><span id='{$variation['id']}' class='colorselector ".( ($_has_title)? 'hastitle': '')."' style='background-color:#".$hex_color."' hex='".$hex_color."' title='".$title."' alt='".$title."'></span>
											<input name='".$variation['id']."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='".$variation['default']."'/></em></p>";

									break;

									case 'font_style':
										$rightside.= "<p style='margin:0'><select title='".__('Font Style', 'eventon')."' name='".$variation['id']."'>";
												$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
													$ajdePT[ $variation['id'] ]:$variation['default'] ;
												foreach($font_styles as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;

									case 'font_size':
										$rightside.= "<p style='margin:0'><select title='".__('Font Size', 'eventon')."' name='".$variation['id']."'>";
												
												$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
													$ajdePT[ $variation['id'] ]:$variation['default'] ;
												
												foreach($font_sizes as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;
									
									case 'opacity_value':
										$rightside.= "<p style='margin:0'><select title='".__('Opacity Value', 'eventon')."' name='".$variation['id']."'>";
												
												$f1_fs = (!empty($ajdePT[ $variation['id'] ]))?
													$ajdePT[ $variation['id'] ]:$variation['default'] ;
												
												foreach($opacity_values as $fs){
													$selected = ($f1_fs == $fs)?"selected='selected'":null;	
													$rightside.= "<option value='$fs' ".$selected.">$fs</option>";
												}
										$rightside.= "</select></p>";
									break;
								}

								
							}
							$rightside.= "<div class='clear'></div></div>";
						break;

						case 'multicolor':

							$variations = $field['variations'];

							$rightside.= "<div class='row_multicolor' style='padding-top:10px'>";

							foreach($variations as $variation){
								// default hex color
								$hex_color = (!empty($ajdePT[ $variation['id']]) )? 
									$ajdePT[ $variation['id']]:$variation['default'];
								$hex_color_val = (!empty($ajdePT[ $variation['id'] ]))? $ajdePT[ $variation['id'] ]: null;

								$rightside.= "<p class='acus_line color'>
								<em data-name='". esc_attr( $variation['name'] ) ."'><span id='{$variation['id']}' class='colorselector' style='background-color:#".esc_attr( $hex_color )."' hex='".esc_attr( $hex_color )."' title='".$hex_color."'></span>
								<input name='". esc_attr( $variation['id'] )."' class='backender_colorpicker evocolorp_val' type='hidden' value='".$hex_color_val."' default='". esc_attr( $variation['default'] )."'/></em></p>";
							}

							$rightside.= "<p class='multicolor_alt'></p></div>";

						break;

						case 'radio':
							$rightside.= "<p class='acus_line acus_radio'>". wp_kses_post( $field['name'] ) ."</br>";
							$cnt =0;
							foreach($field['options'] as $option=>$option_val){
								$this_value = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]:null;
								
								$checked_or_not = ((!empty($this_value) && ($option == $this_value) ) || (empty($this_value) && $cnt==0) )?
									'checked=\"checked\"':null;

								$option_id = $field['id'].'_'. (str_replace(' ', '_', $option_val));
								
								$rightside.="<em><input id='".$option_id."' type='radio' name='".$field['id']."' value='".$option."' "
								.  $checked_or_not  ."/><label class='ajdebe_radio_btn' for='".$option_id."'><span class='fa'></span>". $option_val ."</label></em>";
								
								$cnt++;
							}						
							$rightside.= $legend_code."</p>";
							
						break;
						case 'dropdown':
							
							$dropdown_opt = (!empty($ajdePT[ $field['id'] ]))? $ajdePT[ $field['id'] ]
								:( !empty($field['default'])? $field['default']:null);
							
							$rightside.= "<p class='acus_line {$field['id']}'>". wp_kses_post( $field['name'] ) ." <select class='ajdebe_dropdown' name='".$field['id']."'>";
							
							if(is_array($field['options'])){
								foreach($field['options'] as $option=>$option_val){
									$rightside.="<option name='".$field['id']."' value='".$option."' "
									.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  ."> ".$option_val."</option>";
								}	
							}					
							$rightside.= "</select>";

								// description text for this field
								if(!empty( $field['desc'] )){
									$rightside.= "<br/><i style='opacity:0.6'>".$field['desc']."</i>";
								}
							$rightside.= $legend_code."</p>";						
						break;
						case 'checkboxes':
							
							$meta_arr= (!empty($ajdePT[ $field['id'] ]) )? $ajdePT[ $field['id'] ]: null;
							$default_arr= (!empty($field['default'] ) )? $field['default']: null;

							ob_start();
							
							echo "<p class='acus_line acus_checks'><span style='padding-bottom:10px;'>". wp_kses_post( $field['name'] ) ."</span>";
							
							// foreach checkbox
							foreach($field['options'] as $option=>$option_val){
								$checked='';
								if(!empty($meta_arr) && is_array($meta_arr)){
									$checked = (in_array($option, $meta_arr))?'checked':'';
								}elseif(!empty($default_arr)){
									$checked = (in_array($option, $default_arr))?'checked':'';
								}

								// option ID
								$option_id = $field['id'].'_'. (str_replace(' ', '_', $option_val));
								
								echo "<span><input id='". esc_attr( $option_id )."' type='checkbox' 
								name='". esc_attr( $field['id'] )."[]' value='".esc_attr( $option )."' ". esc_attr( $checked )."/>
								<label for='".esc_attr($option_id)."'><span class='fa'></span>". esc_html($option_val)."</label></span>";
							}						
							echo  "</p>";

							$rightside.= ob_get_clean();
						break;

						// rearrange field
							// fields_array - array(key=>var)
							// order_var
							// selected_var
							// title
							// (o)notes
						case 'rearrange':

							ob_start();
								$_ORDERVAR = $field['order_var'];
								$_SELECTEDVAR = $field['selected_var'];
								$_FIELDSar = $field['fields_array']; // key(var) => value(name)

								
								// saved order
								if(!empty($ajdePT[$_ORDERVAR])){								
									
									$allfields_ = explode(',',$ajdePT[$_ORDERVAR]);
									$fieldsx = array();
									//print_r($allfields_);
									foreach($allfields_ as $fielders){									
										if(!in_array($fielders, $fieldsx)){
											$fieldsx[]= $fielders;
										}
									}
									//print_r($fieldsx);
									$allfields = implode(',', $fieldsx);

									$SAVED_ORDER = array_filter(explode(',', $allfields));
									
								}else{
									$SAVED_ORDER = false;
									$allfields = '';
								}

								$SELECTED = (!empty($ajdePT[$_SELECTEDVAR]))?
									( (is_array( $ajdePT[$_SELECTEDVAR] ))?
										$ajdePT[$_SELECTEDVAR]:
										array_filter( explode(',', $ajdePT[$_SELECTEDVAR]))):
									false;

								$SELECTED_VALS = (is_array($SELECTED))? implode(',', $SELECTED): $SELECTED;

								echo '<h4 class="acus_subheader">'. wp_kses_post( $field['title'] ).'</h4>';
								echo !empty($field['notes'])? '<p><i>'. wp_kses_post( $field['notes'] ).'</i></p>':'';
								echo '<input class="ajderearrange_order" name="'.esc_attr( $_ORDERVAR ).'" value="'. esc_attr( $allfields ).'" type="hidden"/>
									<input class="ajderearrange_selected" type="hidden" name="'.esc_attr($_SELECTEDVAR) .'" value="'.( (!empty($SELECTED_VALS))? esc_attr( $SELECTED_VALS) :null).'"/>
									<div id="ajdeEVC_arrange_box" class="ajderearrange_box '. esc_attr( $field['id']) .'">';


								// if an order array exists already
								if($SAVED_ORDER){
									// for each saved order
									foreach($SAVED_ORDER as $VAL){
										if(!isset($_FIELDSar[$VAL])) continue;

										$FF = (is_array($_FIELDSar[$VAL]))? 
											$_FIELDSar[$VAL][1]:
											$_FIELDSar[$VAL];
										echo (array_key_exists($VAL, $_FIELDSar))? 
											"<p val='".esc_attr( $VAL) ."' class='evo_data_item'><span class='fa ". ( !empty($SELECTED) && in_array($VAL, $SELECTED)?
												'':'hide') ."'></span>". wp_kses_post( $FF).
												//"<input type='hidden' name='_evo_data_fields[]' value='{$VAL}'/>".
											"</p>":	null;
									}	
									
									// if there are new values in possible items add them to the bottom
									foreach($_FIELDSar as $f=>$v){
										$FF = (is_array($v))? $v[1]:$v;
										echo (!in_array($f, $SAVED_ORDER))? 
											"<p val='". esc_attr($f)."'><span class='fa ". ( !empty($SELECTED) && in_array($f, $SELECTED)?'':'hide') ."'></span>". wp_kses_post($FF)."</p>": null;
									}
										
								}else{
								// if there isnt a saved order	
									foreach($_FIELDSar as $f=>$v){
										$FF = (is_array($v))? $v[1]:$v;
										echo "<p val='".esc_attr($f)."'><span class='fa ". ( !empty($SELECTED) && in_array($f, $SELECTED)?'':'hide') ."'></span>".wp_kses_post($FF)."</p>";
									}				
								}

								echo "</div>";

							$rightside .= ob_get_clean();

						break;
						
						case 'yesno':						
							$yesno_value = (!empty( $ajdePT[$field['id'] ]) )? 
								$ajdePT[$field['id']]:'no';
							
							$after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

							$__default = (!empty( $field['default'] ) && $ajdePT[$field['id'] ]!='yes' )? 
								$field['default']
								:$yesno_value;

							$rightside.= "<p class='yesno_row'>".EVO()->elements->yesno_btn(array('var'=>$__default,'attr'=>array('afterstatement'=>$after_statement) ))."<input type='hidden' name='".esc_attr($field['id'])."' value='".(($__default=='yes')?'yes':'no')."'/><span class='field_name'>". esc_html( $field['name'] );
							$rightside.= $legend_code;
							$rightside.="</span>";

								// description text for this field
								if(!empty( $field['desc'] )){
									$rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>". wp_kses_post( $field['desc'] )."</i>";
								}
							$rightside .= '</p>';
						break;

						case 'yesnoALT':
						   $__default = (!empty( $field['default'] ) )?
						      $field['default']
						      :'no';

						   $yesno_value = (!empty( $ajdePT[$field['id'] ]) )?
						      $ajdePT[$field['id']]:$__default;
						   
						   $after_statement = (isset($field['afterstatement']) )?$field['afterstatement']:'';

						   $rightside.= "<p class='yesno_row'>".$wp_admin->html_yesnobtn(array('var'=>$yesno_value,'attr'=>array('afterstatement'=>$after_statement) ))."<input type='hidden' name='".esc_attr($field['id'])."' value='".$yesno_value."'/><span class='field_name'>". esc_html( $field['name'] ) ."". wp_kses_post( $legend_code )."</span>";

						      // description text for this field
						      if(!empty( $field['desc'] )){
						         $rightside.= "<i style='opacity:0.6; padding-top:8px; display:block'>".wp_kses_post($field['desc'])."</i>";
						      }
						   $rightside .= '</p>';
						break;

						case 'begin_afterstatement': 
							
							$yesno_val = (!empty($ajdePT[$field['id']]))? $ajdePT[$field['id']]:'no';
							
							$rightside.= "<div class='backender_yn_sec' id='". esc_attr( $field['id'] )."' style='display:".(($yesno_val=='yes')?'block':'none')."'><div class='evosettings_field_child'>";
						break;
						case 'end_afterstatement': $rightside.= "</div><em class='hr_line evosettings_end_field'></em></div>"; break;
						
						
						// hidden section open
						case 'hiddensection_open':
							
							$__display = (!empty($field['display']) && $field['display']=='none')? 'display:none':null;
							$__diclass = (!empty($field['display']) && $field['display']=='none')? '':'open';
							
							$rightside.="<div class='ajdeSET_hidden_open ".esc_attr($__diclass)."'><h4>{$field['name']}{$legend_code}</h4></div>
							<div class='ajdeSET_hidden_body' style='". esc_attr($__display) ."'><div class='evo_in'>";
							
						break;					
						case 'hiddensection_close':	$rightside.="</div></div>";	break;

						case 'sub_section_open':
							$rightside.="<div class='evo_settings_subsection'><h4 class='acus_subheader'>". esc_html( $field['name'] ) ."</h4><div class='evo_in'>";
						break;
						case 'sub_section_close': $rightside.="</div></div>";
							if( isset($field['em']) && $field['em'])	$rightside.= "<em class='hr_line'></em>'";
						break;
						
						// custom code
						case 'customcode':						
							$rightside .= (!empty($field['code'])? $field['code']:'');						
						break;
					}
					if(!empty($field['type']) && !in_array($field['type'], $__no_hr_types) ){ 
						$rightside.= "<em class='hr_line'></em>";}
					
				}		
				$rightside.= "</div><!-- nfer-->";
			}
			$count++;
		}
		
		//built out the backender section
		
		?>
		<table id='ajde_customization'>
			<tr><td class='backender_left' valign='top'>
				<div id='acus_left'>
					<ul><?php echo  $leftside; ?></ul>								
				</div>
				<div class="ajde-collapse-menu" id='collapse-button'>
					<span class="collapse-button-icon"></span>
					<span class="collapse-button-label" style='font-size:12px;'><?php esc_html_e('Collapse Menu','eventon');?></span>
				</div>
				</td><td class='evo_settings_right' width='100%'  valign='top'>
					<div id='acus_right' class='ajde_backender_uix'>
						<p id='acus_arrow' style='top:14px'></p>
						<div class='customization_right_in'>
							<div style='display:none' id='ajde_color_guide'>Loading</div>
							<div id='ajde_clr_picker' class="cp cp-default" style='display:none'></div>
							<?php echo $rightside.$extra_tabs ;?>
						</div>

						<div class='evo_diag actual'>
							<!-- save settings -->
							<input type="submit" class="evo_admin_btn btn_prime" value="<?php esc_html_e('Save Changes'); ?>" /> <a id='resetColor' style='display:none' class='evo_admin_btn btn_secondary'><?php esc_html_e('Reset to default colors','eventon');?></a>
						</div>	

					</div>
				</td>
			</tr>
		</table>	
		<?php
		
	}

}