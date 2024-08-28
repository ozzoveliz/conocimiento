<?php
/* EventON Shortcode Generator
* @version 2.9
*/

class EVO_Shortcode_Generator{
	private $_in_select_step=false;
	public $data;

	public function __construct(){
		include_once('class-shortcode-data.php');
		$this->data = new EVO_Shortcode_Data();
	}

	// GET Generator 
		public function get_content(){			
			
			return $this->get_inside(
				$this->data->get_shortcode_field_array(),
				'add_eventon'
			);
		}

	// depreciation connects
		function shortcode_default_field($A){
			return $this->data->shortcode_default_field($A);
		}


	// Get HTML of inside the generator
		public function print_get_inside_html($var){
			echo $this->get_inside_html($var);
		}
		public function get_inside_html($var){
			
			// initial values
				$line_class = array('fieldline');

			ob_start();		
			
			// GUIDE popup
			$guide = (!empty($var['guide']))? EVO()->elements->tooltips($var['guide'], 'L',false):null;

			// afterstatemnt class
			if(!empty($var['afterstatement'])){	$line_class[]='trig_afterst'; }

			// select step class
			if($this->_in_select_step){ $line_class[]='ss_in'; }

			if(!empty($var['type'])):

			switch($var['type']){
				// custom type and its html pluggability
				case has_action("ajde_shortcode_box_interpret_{$var['type']}"):
					do_action("ajde_shortcode_box_interpret_{$var['type']}", $var, $guide);
				break;
				case 'YN':
					$line_class[]='ajdeYN_row';

					echo "<div class='". esc_attr( implode(' ', $line_class) ) ."'>";
					EVO()->elements->print_yesno_btn(array(
						'var'=>		esc_attr( $var['var'] ),
						'default'=>	( ($var['default']=='no')? 'NO':null ),
						'guide'=>(	!empty($var['guide'])? esc_html( $var['guide'] ):''), 
						'guide_position'=>(!empty($var['guide_position'])? esc_attr( $var['guide_position'] ):'L'),
						'label'=> 	wp_kses_post( $var['name'] ),
						'abs'=>'yes',
						'attr'=> array('codevar'=> esc_attr( $var['var'] ) )
						));
					echo "</div>";					
				break;

				case 'customcode':	echo !empty($var['value'])? wp_kses_post( $var['value'] ):'';	break;
				
				case 'note':
					$line_class[]='note';
					echo 
					"<div class='". esc_attr( implode(' ', $line_class) )."'><p class='label'>". wp_kses_post( $var['name'] )."</p></div>";
				break;
				case 'collapsable':
					$line_class[] = 'collapsable';
					if( isset($var['closed']) && $var['closed'] ) $line_class[] = 'closed';
					echo 
					"<div style='' class='". esc_attr( implode(' ', $line_class) ) ."'><p class='label subheader'>". wp_kses_post( $var['name'] ) ."</p></div><div class='collapsable_fields' style='display:". ( ( isset($var['closed']) && $var['closed'] )? 'none':'')."'>";
				break;
				case 'subheader':
					echo 
					"<div style='background-color:#f7f7f7' class='". esc_attr( implode(' ', $line_class) )."'><p class='label subheader'>". wp_kses_post( $var['name'] ) ."</p></div>";
				break;
				case 'text':

					$guide = !empty( $guide ) ? wp_kses_post( $guide ) : null;

					echo 
					"<div class='".esc_attr( implode(' ', $line_class) )."'>
						<p class='label'><input class='ajdePOSH_input' type='text' codevar='".esc_attr( $var['var'] )."' placeholder='".( (!empty($var['placeholder']))? esc_attr( $var['placeholder'] ):null) ."'/> ". wp_kses_post( $var['name'] )."". $guide ."</p>
					</div>";
				break;

				case 'fmy':
					$line_class[]='fmy';
					$guide = !empty( $guide ) ? wp_kses_post( $guide ) : null;

					echo 
					"<div class='". esc_attr( implode(' ', $line_class) )."'>
						<p class='label'>
							<input class='ajdePOSH_input short' type='text' codevar='fixed_month' placeholder='eg. 11' title='Month'/><input class='ajdePOSH_input short' type='text' codevar='fixed_year' placeholder='eg. 2014' title='Year'/> ". wp_kses_post( $var['name'] )."".  $guide ."</p>
					</div>";
				break;
				case 'fdmy':
					$line_class[]='fdmy';

					$guide = !empty( $guide ) ? wp_kses_post( $guide ) : null;

					echo 
					"<div class='". esc_attr( implode(' ', $line_class) )."'>
						<p class='label'>
							<input class='ajdePOSH_input short shorter' type='text' codevar='fixed_date' placeholder='eg. 31' title='Date'/><input class='ajdePOSH_input short shorter' type='text' codevar='fixed_month' placeholder='eg. 11' title='Month'/><input class='ajdePOSH_input short shorter' type='text' codevar='fixed_year' placeholder='eg. 2014' title='Year'/> ". wp_kses_post( $var['name'] )."".  $guide ."</p>
					</div>";
				break;
				
				case 'taxonomy':
					EVO()->elements->print_element( array(
						'type'=> 'lightbox_select_vals',
						'field_class'=> 'ajdePOSH_input',
						'name'=> 		wp_kses_post( $var['name'] ),
						'default'=> 	(!empty($var['placeholder'])? esc_attr( $var['placeholder'] ):null),
						'taxonomy'=> 	esc_attr( $var['var'] ),
						'reverse_field'=>true,
						'row_class'=> 	'fieldline',
						'field_attr'=> 	array(
							'codevar'=>	esc_attr( $var['var'] )
						)
					));
				break;
				case 'select_in_lightbox':
					EVO()->elements->print_element( array(
						'type'=> 		'lightbox_select_cus_vals',
						'field_class'=> 'ajdePOSH_input',
						'name'=> 		wp_kses_post( $var['name'] ),
						'default'=> 	(!empty($var['placeholder'])? esc_attr( $var['placeholder'] ):null),
						'options'=> 	array_map('esc_html', $var['options'] ),
						'reverse_field'=>true,
						'row_class'=> 	'fieldline',
						'field_attr'=> array(
							'codevar'=> esc_attr( $var['var'] )
						)
					));
				break;
				
				case 'select':

					$guide = !empty( $guide ) ? wp_kses_post( $guide ) : null;

					echo 
					"<div class='".esc_attr( implode(' ', $line_class))."'>
						<p class='label'>
							<select class='ajdePOSH_select' codevar='". esc_attr( $var['var'] )."'>";
							$default = (!empty($var['default']))? $var['default']: null;
							
							foreach($var['options'] as $valf=>$val){
								echo "<option value='".esc_attr( $valf )."' ".( $default==$valf? 'selected="selected"':null).">".esc_attr( $val )."</option>";
							}						
							echo 
							"</select> ". wp_kses_post( $var['name'] )."". $guide  ."</p>
					</div>";
				break;

				// select steps
				case 'select_step':
					$line_class[]='select_step_line';
					$guide = !empty( $guide ) ? wp_kses_post( $guide ) : null;

					echo 
					"<div class='".esc_attr( implode(' ', $line_class))."'>
						<p class='label '>
							<select class='ajdePOSH_select_step' codevar='". esc_attr( $var['var'] )."'>";
							
							foreach($var['options'] as $f=>$val){
								echo (!empty($val))? "<option value='".esc_attr( $f )."'>".esc_attr( $val )."</option>":null;
							}		
							echo 
							"</select> ". wp_kses_post( $var['name'] ) .  $guide  ."</p>
					</div>";
				break;

				case 'open_select_steps':
					echo "<div id='".esc_attr( $var['id'] )."' class='ajde_open_ss select_step_".esc_attr( $var['id'] )."' style='display:none' data-step='".esc_attr( $var['id'] )."' >";
					$this->_in_select_step=true;	// set select step section to on
				break;

				case 'close_select_step':	echo "</div>";	$this->_in_select_step=false; break;
				case 'close_div':	echo "</div>"; break;
				
			}// end switch

			endif;

			// afterstatement
			if(!empty($var['afterstatement'])){
				echo "<div class='ajde_afterst ". esc_attr( $var['afterstatement'] ) ."' style='display:none'>";
			}

			// closestatement
			if(!empty($var['closestatement'])){
				echo "</div>";
			}
			
			return ob_get_clean();
		}
	// get the HTML content for the shortcode generator
		public function get_inside($shortcode_guide_array, $base_shortcode){
				
			$__text_a = __('Select option below to customize shortcode variable values','eventon');
			ob_start();

			?>		
				<div id='ajdePOSH_outter' class='<?php echo esc_attr( $base_shortcode );?>'>
					<h3 class='notifications '><em id='ajdePOSH_back' class='fa'></em><span id='ajdePOSH_subtitle' data-section='' data-bf='<?php echo esc_attr( $__text_a );?>'><?php echo esc_attr( $__text_a );?></span></h3>
					<div class='ajdePOSH_inner'>
						<div class='step1 steps'>
						<p style='    background-color: #ff896e; color: #fff;padding: 10px; font-size: 12px;display: none'><?php esc_html_e('WARNING! If you are interchangeably using shortcode parameters between other calendar shortcodes, bare in mind, that the shortcode parameters not available in its shortcode options may not be fully supported!','eventon');?></p>
						<?php					
							foreach($shortcode_guide_array as $options){
								$__step_2 = (empty($options['variables']))? ' nostep':null;
								
								echo "<div class='ajdePOSH_btn". esc_attr( $__step_2 )."' step2='".esc_attr( $options['id'] )."' code='". esc_attr( $options['code'] )."'>". esc_attr( $options['name'] )."</div>";
							}	
						?>				
						</div>
						<div class='step2 steps' >
							<?php
								foreach($shortcode_guide_array as $options){
									if(!empty($options['variables']) ) {
										echo "<div id='". esc_attr( $options['id'] )."' data-code='". esc_attr( $options['code'] ). "' class='step2_in' style='display:none'>";										
										
										// each shortcode option variable row
										foreach($options['variables'] as $var){
											$this->print_get_inside_html( $var );
										}	echo "</div>";
									}
								}						
							?>					
						</div><!-- step 2-->
						<div class='clear'></div>
					</div>
					<div class='ajdePOSH_footer'>
						<p id='ajdePOSH_var_'></p>
						<p id='ajdePOSH_code' data-defsc='<?php echo esc_attr( $base_shortcode );?>' data-curcode='<?php echo esc_attr( $base_shortcode );?>' code='<?php echo esc_attr( $base_shortcode );?>' >[<?php echo esc_attr( $base_shortcode);?>]</p>
						<span class='ajdePOSH_insert' title='<?php esc_html_e('Click to insert shortcode');?>'></span>
					</div>
				</div>
			
			<?php
			return ob_get_clean();
		
		}

}

//$GLOBALS['evo_shortcode_box'] = EVO()->shortcode_gen;