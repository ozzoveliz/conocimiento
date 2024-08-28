<?php
/**
 * EventON General Calendar Elements
 * @version L2.2.19

Items //
print_date_time_selector
print_time_selector
yesno_btn
get_icon
tooltips
icons
button - name, unqiue_class
start_table_header

register_shortcode_generator_styles_scripts
enqueue_shortcode_generator
load_colorpicker
register_colorpicker
 */

class EVO_General_Elements{	

	public $svg;

	public function __construct(){
		include_once 'class-elements-svg.php';
		$this->svg = new EVO_Elements_SVG();
	}

// standard form elements
	public function print_element( $A){
		echo $this->get_element( $A);
	}
	function get_element($A){ 
		$A = array_merge( array(
			'id'=>'',
			'index'=>'',// referance index
			'name'=>'',	
			'label'=>'',		
			'hideable'=> false,
			'value'=>'','default'=>'','values'=> array(),'values_array'=> array(),
			'value_2'=>'',
			'max'=>'','min'=>'','step'=>'','readonly'=>false,
			'TD'=>'eventon', // text domain
			'legend'=>'','tooltip'=>'',
			'tooltip_position'=>'',
			'description'=>'',
			'options'=> false, 'select_multi_options'=> false,
			'type'=>'', 'field_type'=>'text','field_attr'=>array(),'field_class'=> '',
			'reverse_field' => false,
			'afterstatement'=>'',
			'row_class'=>'', 'select_option_class'=>'','unqiue_class'=>'','class_2'=>'',
			'inputAttr'=>'','attr'=>'',
			'nesting_start'=> '', 'nesting_end'=> false, // pass nesting class name
			'row_style'=> '',// pass styles 
			'content'=> '', 'field_after_content'=>'', 'field_before_content'=>'',
			'support_input'=>false,
			'close'=>false,

		), $A);
		extract($A);

		// prelim
			// reuses
				$legend_code = !empty($tooltip) ? $this->tooltips($tooltip, $tooltip_position, false): null;
				if(!empty($field_attr) && count($field_attr)>0){
					$field_attr = array_map(function($v,$k){
						return $k .'="'. $v .'"';
					}, array_values($field_attr), array_keys($field_attr));
					
				}
				$field_attr = !empty($field_attr) ? implode(' ', $field_attr) : null;

			// validation
				if(empty($type)) return false;


			// nesting
				$_nesting_start = $_nesting_end = '';
				if(!empty($nesting_start)) $_nesting_start = "<div class='evo_nesting {$nesting_start}'>";
				if( $nesting_end ) $_nesting_end = "</div>";
			
		ob_start();

		if( !empty( $_nesting_start ) ) echo wp_kses_post( $_nesting_start );

		switch($type){
			// notices
			case 'notice':
				echo "<p class='evo_elm_row evo_elm_notice ". esc_attr( $row_class )."' style='" . esc_attr( $row_style )."'>". esc_attr( $name ) ."</p>";
			break;

			// custom code field
			case 'custom_code':
			case 'code':
				echo $content;
			break;

			// hidden input field
			case 'hidden':
				$name = (!empty($name)) ? $name : $id;
				echo "<input type='hidden' name='". esc_attr( $name )."' value='". esc_attr( $value ) ."'/>";
			break;

			// image
			case 'image':
				$image_id = !empty($value) ? $value: false;

				// image soruce array
				$img_src = ($image_id)? 	wp_get_attachment_image_src($image_id,'medium'): null;
					$img_src = (!empty($img_src))? $img_src[0]: null;

				$__button_text = ($image_id)? __('Remove Image','eventon'): __('Choose Image','eventon');
				$__button_text_not = ($image_id)? __('Remove Image','eventon'): __('Choose Image','eventon');
				$__button_class = ($image_id)? 'removeimg':'chooseimg';
				?>
				<p class='evo_metafield_image'>
					<label><?php echo esc_attr( $name ) .$legend_code; ?></label>
					
					<input class='field <?php echo esc_attr( $id );?> custom_upload_image evo_meta_img' name="<?php echo esc_attr( $id );?>" type="hidden" value="<?php echo ($image_id)? esc_attr( $image_id ): null;?>" /> 
            		
            		<input class="custom_upload_image_button button <?php echo esc_attr( $__button_class );?>" data-txt='<?php echo esc_attr( $__button_text_not );?>' type="button" value="<?php echo esc_attr( $__button_text );?>" /><br/>
            		<span class='evo_loc_image_src image_src'>
            			<img src='<?php echo !empty( $img_src ) ? esc_url( $img_src ) : '';?>' style='<?php echo !empty($image_id)?'':'display:none';?>'/>
            		</span>
            		
            	</p>
				<?php
			break;

			// GENERAL Text field
			case 'text':
			case 'input':
				echo "<div class='evo_elm_row ". esc_attr( $id )."' style='". esc_attr( $row_style ) ."'>";
				$placeholder = (!empty($default) )? 'placeholder="'. esc_attr( $default ) .'"':null;				

				$show_val = false; $hideable_text = '';
				if( $hideable && !empty($value)){
					$show_val = true;
					$hideable_text = "<span class='evo_hideable_show' data-t='". __('Hide', 'eventon') ."'>". __('Show','eventon'). "</span>";
				}
				
				echo"<p class='evo_field_label'>". esc_attr( $name ) .$legend_code. $hideable_text. "</p><p class='evo_field_container'>";

				if($show_val && $hideable){
					echo "<input class='". esc_attr( $field_class ). "' type='password' style='' name='". esc_attr( $id ) ."'";
					echo'value="'. htmlspecialchars( $value , ENT_QUOTES ) .'"';
				}else{
					echo "<input class='". esc_attr( $field_class )."' type='". esc_attr( $field_type )."' name='". esc_attr( $id )."' max='". esc_attr( $max )."' min='". esc_attr( $min )."' step='". esc_attr( $step )."'";

					if( $readonly ) echo 'readonly="true"';
					$__values = htmlspecialchars( $value , ENT_QUOTES);
					//$__values =  $value ;
					echo 'value="'. $__values .'"';
				}				
				echo $placeholder."/>";

				if(!empty($description)) echo "<em>". esc_html( $description ) ."</em>";

				echo "</p></div>";
			break;

			// color picker field
			case 'colorpicker':

				$vis_input_field = !empty($support_input) && $support_input ? true: false;

				echo "<div class='evo_elm_row ". esc_attr( $id )."' style='". esc_attr( $row_style )."'>";

				echo"<p class='evo_field_label'>".esc_html( $name ) .$legend_code. "</p>";
				echo "<p class='evo_field_container ". ( $vis_input_field? 'visi':'') ."'>";
				echo "<em class='evo_elm_color' style='background-color:#". esc_attr( $value )."'></em>";

				if($vis_input_field ):
					echo "<input class='evo_elm_hex' type='text' name='". esc_attr( $id )."' value='". esc_attr( $value )."'/>";
				else:
					echo "<input class='evo_elm_hex' type='hidden' name='". esc_attr( $id )."' value='". esc_attr( $value )."'/>";
				endif;
				
				//echo "<input class='evo_elm_rgb' type='hidden' name='{$rgb_field_name}' value='{$rgb_num}'/>";

				echo "</p></div>";
			break;

			// bigger color picker @4.5
			case 'colorpicker_2':

				$clean_hex = str_replace('#', '', $value);
				$fcl = !eventon_is_hex_dark( $value ) ? 'ffffff':'000000';

				echo "<div class='evo_elm_row evo_color_selector {$index}' id='{$id}' >
					<p class='evselectedColor evo_set_color' style='background-color:{$value}; color: #{$fcl}'>
						<span class='evcal_color_hex evcal_chex'  >{$value}</span>
						<span class='evo_mb_color_caption'>{$label}</span>
					</p>
					<input class='evo_color_hex' type='hidden' name='evcal_event_color{$index}' value='{$clean_hex}'/>
					<input class='evo_color_n' type='hidden' name='evcal_event_color_n{$index}' value='{$value_2}'/>
				</div>";
			break;

			case 'plusminus':

				echo "<div class='evo_elm_row ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";

				if( !empty( $field_before_content ) ) echo wp_kses_post( $field_before_content );

				echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code. "</p><p class='evo_field_container evo_field_plusminus_container'>";
				?>
					<span class="evo_plusminus_adjuster">
						<b class="min evo_plusminus_change <?php echo esc_attr( $unqiue_class );?>">-</b>
						<input class='evo_plusminus_change_input <?php echo esc_attr( $class_2 );?>' type='text' name='<?php echo esc_attr( $id );?>' value='<?php echo esc_attr( $value );?>' data-max='<?php echo esc_attr( $max );?>'/>
						<b class="plu evo_plusminus_change <?php echo esc_attr( $unqiue_class );?> <?php echo (!empty($max) && $max==1 )? 'reached':'';?>">+</b>						
					</span>
				<?php

				echo "</p>";

				if( !empty( $field_after_content ) ) echo wp_kses_post($field_after_content );

				echo "</div>";

			break;

			// textarea
			case 'textarea':

				$__value = empty( $value ) ? null : wp_kses_post( $value );
				$placeholder = (!empty($default) )? 'placeholder="'. esc_attr( $default ).'"':null;		
				
				echo "<div class='evo_elm_row ". esc_attr( $id )."' style='". esc_attr( $row_style )."'>";
				echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p><p class='evo_field_container'>";

				$height = !empty($height)? "height:". esc_attr( $height ):'';
				echo "<textarea class='". esc_attr( $field_class )."' name='". esc_attr( $id )."' style='width:100%; ". esc_attr( $height )."' ". $placeholder .">". $__value ."</textarea>";

				echo "</p></div>";

			break;
			// wysiwyg
			case 'wysiwyg':

				$__value = empty( $value ) ? null : wp_kses_post( $value );

				echo "<div class='evo_elm_row trumbowyg ". esc_attr( $id )."' style='". esc_attr( $row_style )."'>";
				echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p><p class='evo_field_container'>";

				echo "<textarea class='evoelm_trumbowyg' name='".esc_attr( $id )."' style='width:100%; min-height:300px;'>". $__value ."</textarea>";

				echo "</p></div>";

			break;

			// Select in a lightbox -- for taxonomy values
			case 'lightbox_select_vals':

				echo "<div class='evo_elm_row evo_elm_lb_select ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";
				// get values to show
					$values = !empty($value)? explode(',', $value): array();

					if(count($values_array) == 0){
						$values_array = array();
						if(!empty($taxonomy)){
							$t = get_terms( array('taxonomy'=> $taxonomy,'hide_empty'=>false));
							if(!empty($t) && !is_wp_error($t)){
								foreach($t as $term){
									$values_array[ $term->term_id ] = $term->name;
								}
							}
						}
					}

				if(count($values_array)>0):
					echo "
					<div class='evo_elm_lb_window' style='display:none'>
						<div class='eelb_in'>
						<div class='eelb_i_i'>";
						foreach($values_array as $f=>$v){
							echo "<span class='". (in_array($f, $values)?'select':'') ."' value='". esc_attr( $f )."'>". esc_html( $v )."</span>";
						}
					echo "</div></div></div>";
				endif;

				$placeholder = (!empty($default) )? 'placeholder="'. esc_attr( $default ).'"':null;	

				echo "<div class='evo_elm_lb_fields'>";
					if(!$reverse_field) echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p>";					
					echo "<p class='evo_field_container evo_elm_lb_field'>";
					echo "<input class='evo_elm_lb_field_input ". esc_attr( $field_class )."' type='". esc_attr( $field_type )."' ". esc_attr( $field_attr )." name='". esc_attr( $id )."' ". $placeholder ." " . 'value="'. esc_attr( $value ) .'"/>';
					echo "</p>";
					if($reverse_field) echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p>";				
				echo "</div>";
				echo "</div>";
			break;

			// Select in a lightbox -- for other general values
			case 'lightbox_select_cus_vals':

				echo "<div class='evo_elm_row evo_elm_lb_select ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";
								

				if(count($options)>0):
					echo "
					<div class='evo_elm_lb_window' style='display:none'>
						<div class='eelb_in'>
						<div class='eelb_i_i'>";
						foreach($options as $f=>$v){
							echo "<span class='". (in_array($f, $values)?'select':'') ."' value='". esc_attr( $f )."'>". wp_kses_post( $v )."</span>";
						}
					echo "</div></div></div>";
				endif;

				$placeholder = (!empty($default) )? 'placeholder="'. esc_attr( $default ).'"':null;	

				echo "<div class='evo_elm_lb_fields'>";
					if(!$reverse_field) echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p>";					
					echo "<p class='evo_field_container evo_elm_lb_field'>";
					echo "<input class='evo_elm_lb_field_input ". esc_attr( $field_class )."' type='". esc_attr( $field_type )."' ". esc_attr( $field_attr )." name='". esc_attr( $id )."' ". $placeholder ." " . 'value="'. esc_attr( $value ) .'"/>';
					echo "</p>";
					if($reverse_field) echo"<p class='evo_field_label'>". esc_html( $name ) .$legend_code . "</p>";				
				echo "</div>";
				echo "</div>";
			break;

			// select row 
			case 'select_row':
				?>
				<p class='evo_elm_row evo_row_select <?php echo esc_attr( $row_class );?> <?php echo $select_multi_options? 'multi':'';?>' style='<?php echo esc_attr( $row_style );?>'>
					<input type='hidden' name='<?php echo esc_attr( $name );?>' value='<?php echo esc_attr( $value );?>'/>
					
					<?php if(!empty($label)):?> 
						<label style='margin-right: 10px;'><?php echo esc_attr( $label ).' '. $legend_code;?></label>
					<?php endif;?>
					
					<span class='values <?php echo esc_attr( $name );?>'>
					<?php 

					$vals = array();
					if($select_multi_options && !empty($value)){
						$vals = explode(',', $value);
					}

					foreach($options as $F=>$V){

						$selected = '';
						if($select_multi_options){
							if( in_array($F, $vals)) $selected = ' select';
						}else{
							if($F==$value) $selected = ' select';
						}


						echo "<span value='". esc_attr( $F )."' class='evo_row_select_opt opt". esc_html( $selected )." ". esc_attr( $select_option_class )."'>". esc_html( $V )."</span>";
					}?>
					</span>
				</p><?php
			break;

			// DROP Down select field
			case 'dropdown':					
						
				echo "<p class='evo_elm_row evo_elm_select ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";
				echo "<label>". esc_html( $name )." $legend_code</label>"; 
				echo "<select class='ajdebe_dropdown ". esc_attr( $field_class ) ."' name='". esc_attr( $id ) ."'>";

				if(is_array($options)){
					$dropdown_opt = !empty($value)? $value: (!empty($default)? $default :'');		
					foreach($options as $option=>$option_val){
						echo"<option name='". esc_attr( $id ) ."' value='". esc_attr( $option )."' "
						.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  .">". esc_html( $option_val ) ."</option>";
					}	
				}					
				echo  "</select>";
					// legend for under the field
					if(!empty( $legend )){
						echo "<br/><i style='opacity:0.6'>". esc_html( $legend ) ."</i>";
					}
				echo "</p>";						
			break;
			// DROP Down select field -- select2
			case 'dropdownS2':					
						
				echo "<p class='evo_elm_row evo_elm_select ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";
				echo "<label>". esc_html( $name )." $legend_code</label>"; 
				echo "<select class='ajdebe_dropdown evo_select2' name='". esc_attr( $id )."' style='width:100%'>";

				if(is_array($options)){
					$dropdown_opt = !empty($value)? $value: (!empty($default)? $default :'');		
					foreach($options as $option=>$option_val){
						echo"<option name='". esc_attr( $id )."' value='". esc_attr( $option )."' "
						.  ( ($option == $dropdown_opt)? 'selected=\"selected\"':null)  .">". esc_html( $option_val )."</option>";
					}	
				}					
				echo  "</select>";
					// legend for under the field
					if(!empty( $legend )){
						echo "<br/><i style='opacity:0.6'>". esc_html( $legend ) ."</i>";
					}
				echo "</p>";						
			break;

			// YES NO
			case 'yesno':						
				if(empty( $value) ) $value = 'no';
				echo "<p class='evo_elm_row yesno_row ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";

				$this->print_yesno_btn(array(
					'id'=> 		esc_attr( $id ),
					'var'=> 	esc_attr( $value ),
					'afterstatement'=> esc_attr( $afterstatement ),
					'input'=> 	true,
					'guide'=> 	esc_html( $tooltip ),
					'guide_position'=> esc_attr( $tooltip_position ),
					'label'=> 	esc_html( $label ),
				));

				echo "<span class='field_name'>". esc_html( $name ) ."{$legend_code}</span>";

					// description text for this field
					if(!empty( $legend )){
						echo"<i style='opacity:0.6; padding-top:8px; display:block'>". esc_html( $legend ) ."</i>";
					}
				echo'</p>';
			break;
			case 'yesno_btn':						
				if(empty( $value) ) $value = 'no';
				
				echo "<p class='evo_elm_row yesno_row ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";

				$this->print_yesno_btn(array(
					'id'=>			esc_attr( $id ),
					'var'=> 		esc_attr( $value ),
					'afterstatement'=> esc_attr( $afterstatement ),
					'input'=> 		true,
					'guide'=> 		esc_html( $tooltip ), 
					'guide_position'=> esc_attr( $tooltip_position ),
					'label'=> 		esc_html( $label ),
					'inputAttr'=>	esc_attr( $inputAttr ),
					'attr'=>		esc_attr( $attr ),
				));

				echo'</p>';	
			break;

			case 'angle_field':						
				$value = empty( $value) ? '0' : (int)$value;
				
				echo "<div class='evo_elm_row angle ". esc_attr( $id )." ". esc_attr( $row_class )." style='". esc_attr( $row_style )."'>
					<div class='evo_elm_ang_hold'>
						<span class='evo_elm_ang_center' style='transform:rotate(". esc_attr( $value )."deg);'>
							<span class='evo_elm_ang_pointer'></span>
						</span>	
					</div>
					<input class='evo_elm_ang_inp' name='". esc_attr( $id )."' value='". esc_attr( $value )."°'/>
				";

					// description text for this field
					if(!empty( $legend )){
						echo"<i style='opacity:0.6; padding-top:8px; display:block'>". esc_html( $legend ) ."</i>";
					}
				echo'</div>';
			break;

			case 'button':
				$data = empty($data) ? '' : $data;
				echo "<p class='evo_elm_row btn ". esc_attr( $id )." ". esc_attr( $row_class )."' style='". esc_attr( $row_style )."'>";
				echo "<a class='evo_btn ". esc_attr( $unqiue_class )."' data-d='". esc_attr( $data )."'>". esc_html( $name )."</a>";
				echo'</p>';
			break;
			case 'icon_select':
				$value = empty( $value) ? '' : $value;
				
				$close_ = $close ? '<em class="ajde_icon_close">X</em>':'';
				
				echo "<p class='evo_elm_row icon faicon'>
						<i class='evo_icons ajde_icons default fa ". esc_attr( $value )." ". (!$close ?'so':'')."' data-val='". esc_attr( $value )."'>". $close_ ."</i> 
						<input type='hidden' name='". esc_attr( $id )."' id='". esc_attr( $id )."' value='". esc_attr( $value )."'></p>";			
				if( !empty($legend)) echo "<p class='description'>". esc_html( $legend ) ."</p>";
			break;
			case 'begin_afterstatement': 						
				$yesno_val = (!empty($value))? $value:'no';				
				echo"<div class='evo_elm_afterstatement ' id='". esc_attr( $id )."' style='display:".(($yesno_val=='yes')?'block':'none')."'>";
			break;
			case 'end_afterstatement': echo "</div>"; break;
		}

		echo $_nesting_end;

		return ob_get_clean();
	}

	public function print_process_multiple_elements( $A){
		echo $this->process_multiple_elements( $A);
	}
	function process_multiple_elements($A){
		$output = '';
		foreach($A as $key=>$AD){
			$output .= $this->get_element( $AD);
		}
		return $output;
	}

	// @since 4.3.5
	function print_hidden_inputs( $array){
		foreach( $array as $name=>$value){
			echo "<input type='hidden' name='". esc_attr( $name )."' value='". esc_attr( $value )."'>";
		}
	}
// Ligthbox triggering button @since 4.3.5
	function print_trigger_element($args, $type){
		$help = new evo_helper();

		switch($type){
			case 'trig_lb':
				/*
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'id'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_title'=>'',	
					'ajax_data'=>array(),

				*/
				$opt = extract( array_merge(array(					
					'class_attr'=>'', // pass class to replace default
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'id'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_title'=>'',
					'lb_size'=>'', // mid, small
					'lb_padding'=>'evopad30',
					'lb_loader'=> false,			
					'lb_load_new_content'=> true,			
					'ajax'=>'yes',
					'ajax_data'=>'',
					'end'=>'admin',// client or admin
					'ajax_action'=>'',// @since 4.4
					'ajax_type'=>'', // @since 4.4
					//'content_id'=>'',
					//'content'=>'', // pass dynamic content
				), $args) );

				$btn_data = array(
					'lbvals'=> array(
						'lbc'=> 		esc_attr( $lb_class ),
						'lbsz'=> 		esc_attr( $lb_size ),
						'lb_padding'=> 	esc_attr( $lb_padding ),
						't'=> 			esc_attr( $lb_title ),
						'ajax'=> 		esc_attr( $ajax ),
						'd'=> 			array_map('esc_html', $ajax_data ),
						'uid'=> 		esc_attr( $uid ),
						'load_new_content'=> $lb_load_new_content ,
						'lightbox_loader'=> $lb_loader,
					)
				);

				if( $end != 'admin' ) $btn_data['lbvals']['end'] = $end;
				if( !empty($ajax_action) ) $btn_data['lbvals']['ajax_action'] = $ajax_action; // @since 4.4
				if( !empty($ajax_type) ) $btn_data['lbvals']['ajax_type'] = $ajax_type; // @since 4.4

				$class_attr = empty($class_attr) ? 'evo_btn evolb_trigger ': $class_attr;
				?><<?php echo esc_html( $dom_element );?> <?php echo !empty($id) ? "id='". esc_attr( $id )."'" :null;?> class='<?php echo esc_attr( $class_attr . $extra_classes );?>' <?php echo $help->array_to_html_data($btn_data);?>  style='<?php echo esc_attr( $styles );?>'><?php echo esc_html( $title );?></<?php echo esc_html( $dom_element );?>>
				<?php

			break;
			case 'trig_form_submit':
				/* easy copy
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
				*/

				$opt = extract( array_merge(array(
					'class_attr'=>'', // pass class to replace default
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_loader'=> false,			
					'lb_hide'=> false,			
					'lb_hide_message'=> false,			
					'lb_load_new_content'=> false,			
					'load_new_content_id'=> '',		
					'end'=>'admin',// client or admin
					//'content_id'=>'',
					//'content'=>'', // pass dynamic content
				), $args) );

				$btn_data = array(
					'd'=> array( 'uid'=> esc_attr( $uid ),
						'lightbox_key'=> esc_attr( $lb_class ),
						'lightbox_loader'=>esc_attr( $lb_loader ),
						'end'=>$end,
						'hide_lightbox'=> 		esc_attr( $lb_hide ),
						'hide_message'=> 		esc_attr( $lb_hide_message ),
						'load_new_content'=>	esc_attr( $lb_load_new_content ),
						'load_new_content_id'=> esc_attr( $load_new_content_id )
					)
				);

				$class_attr = empty($class_attr) ? 'evo_btn evolb_trigger_save ': $class_attr;
				?><<?php echo esc_html( $dom_element );?> class='<?php echo esc_attr( $class_attr . $extra_classes );?>' <?php echo $help->array_to_html_data($btn_data);?> style='<?php echo esc_attr( $styles );?>'><?php echo esc_html( $title );?></<?php echo esc_html( $dom_element );?>>
				<?php
			break;
			case 'trig_ajax':
				/* easy copy
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'lb_class' =>'',
					'lb_load_new_content'=> false,			
					'load_new_content_id'=> '',	
					'ajax_data' =>array(),
				*/

				$opt = extract( array_merge(array(
					'class_attr'=>'',
					'extra_classes'=>'',
					'styles'=> '',
					'title'=>'',
					'dom_element'=> 'span',
					'uid'=>'',
					'ajax_data'=>'',
					'lb_class' =>'',
					'lb_loader'=> false,	
					'lb_hide'=> false,			
					'lb_hide_message'=> false,					
					'lb_load_new_content'=> false,			
					'load_new_content_id'=> '',		
					'end'=>'admin',// client or admin
					//'content_id'=>'',
					//'content'=>'', // pass dynamic content
				), $args) );

				$btn_data = array(
					'd'=> array( 'uid'=> $uid,
						'lightbox_key'=>$lb_class,
						'lightbox_loader'=>$lb_loader,
						'end'=>$end,
						'load_new_content'=>$lb_load_new_content,
						'ajaxdata'=> $ajax_data
					)
				);

				if( !empty($load_new_content_id)) $btn_data['d']['load_new_content_id'] = $load_new_content_id;
				if( $lb_hide) $btn_data['d']['hide_lightbox'] = $lb_hide;
				if( $lb_hide_message) $btn_data['d']['hide_message'] = $lb_hide_message;

				$class_attr = empty($class_attr) ? 'evo_btn evo_trigger_ajax_run ': $class_attr;

				?>
				<<?php echo esc_html( $dom_element );?> class='<?php echo esc_attr( $class_attr . $extra_classes );?>' <?php echo $help->array_to_html_data($btn_data);?> style='<?php echo esc_attr( $styles );?>'><?php echo esc_html( $title );?></<?php echo esc_html( $dom_element );?>>
				<?php
			break;
		}
	}


// date time selector
	function print_date_time_selector($A){
		$D = array(
			'disable_date_editing'=> false,
			'minute_increment'=> 1,
			'time_format'=> 'H:i:s',
			'date_format'=> 'Y/m/d',
			'date_format_hidden'=>'Y/m/d',
			'unix'=> '',				
			'type'=>'start',
			'assoc'=>'reg',
			'names'=>true,
			'rand'=>'',
			'time_opacity'=> 1,
			'selector'=>'both', // both, date, time
		);
		$A = array_merge($D, $A);

		extract($A);

		$rand = (empty($rand))? wp_rand(10000,99999): $rand;

		$hr24 = false;

		if(!empty($time_format) && ( strpos($time_format, 'H')!== false || strpos($time_format, 'G') !== false ) )   $hr24 = true;

		// processings
		$unix = !empty($unix)? (int)$unix : current_time('timestamp');
		
		$DD =  new DateTime();
		$DD->setTimezone( EVO()->calendar->timezone0 );
		$DD->setTimestamp( $unix);

		$date_val = $DD->format( $date_format );
		$date_val_x = $DD->format(  $date_format_hidden );
		$hour = $DD->format( ($hr24? 'H':'h') );
		$minute = $DD->format( 'i');
		$ampm = $DD->format( 'a');

		echo "<span class='evo_date_time_select ". esc_attr( $type )."' data-id='". esc_attr( $rand )."' data-unix='". esc_attr( $unix )."'> ";
			
		if($selector != 'time' ):
			echo " <span class='evo_date_edit'>
				<input id='evo_". esc_attr( $type ). "_date_". esc_attr( $rand ). "' class='". ($disable_date_editing?'':"datepicker". esc_attr( $type ). "date")." ". ($assoc != 'rp'? 'req':'')." ". esc_attr( $type ). " evo_dpicker ' readonly='true' type='text' data-role='none' name='event_". esc_attr( $type ). "_date' value='". esc_attr( $date_val ) ."' data-assoc='". esc_attr( $assoc ). "' />	
				<input type='hidden' name='event_". esc_attr( $type )."_dateformat' value='". esc_attr( $date_format )."'/>

				<input type='hidden' name='".($names? "event_". esc_attr( $type )."_date_x":'')."' class='evo_". esc_attr( $type )."_alt_date alt_date' value='". esc_attr( $date_val_x )."'/>
				<input type='hidden' class='alt_date_format' name='event_". esc_attr( $type ). "_dateformat_alt' value='". esc_attr( _evo_dateformat_PHP_to_jQueryUI($date_format_hidden) ) ."'/>

			</span>";

		endif;

		if($selector != 'date' ):
			echo "<span class='evo_time_edit' style='opacity:". esc_attr( $time_opacity ) . "}'>
				<span class='time_select'>";
				if($disable_date_editing){
					echo "<span>". esc_html( $hour ) ."</span>";
				}else{													
					echo "<select class='evo_time_select _". esc_attr( $type )."_hour' name='".($names? "_". esc_attr( $type )."_hour":'')."' data-role='none'>";

					for($x=1; $x< ($hr24? 25:13 );$x++){	
						$y = ($hr24)? sprintf("%02d",($x-1)): $x;							
						echo "<option value='". esc_attr( $y )."'".(($hour==$y)?'selected="selected"':'').">". esc_html( $y )."</option>";
					}
					echo "</select>";
				}
				echo "</span>";

				echo "<span class='time_select'>";
				if($disable_date_editing){
					echo "<span>". esc_html( $minute ) ."</span>";
				}else{	
					echo "<select class='evo_time_select _". esc_attr( $type )."_minute' name='".($names? "_". esc_attr( $type )."_minute":'')."' data-role='none'>";

					$minute_adjust = (int)(60/$minute_increment);
					for($x=0; $x<$minute_adjust;$x++){
						$min = $minute_increment * $x;
						$min = ($min<10)?('0'.$min):$min;
						echo "<option value='". esc_attr( $min )."'".(($minute==$min)?'selected="selected"':'').">". esc_html( $min )."</option>";
					}
					echo "</select>";
				}
				echo "</span>";

				// AM PM
				if(!$hr24){
					echo "<span class='time_select'>";
					if($disable_date_editing){
						echo "<span>". esc_html( $ampm ) ."</span>";
					}else{	
						echo "<select name='".($names? "_". esc_attr( $type )."_ampm":'')."' class='_". esc_attr( $type )."_ampm ampm_sel'>";													
						foreach(array('am'=> evo_lang_get('evo_lang_am','AM'),'pm'=> evo_lang_get('evo_lang_pm','PM') ) as $f=>$sar){
							echo "<option value='". esc_attr( $f ) ."' ".(($ampm==$f)?'selected="selected"':'').">". esc_html( $sar )."</option>";
						}							
						echo "</select>";
						echo "</span>";
					}
				}
				
			echo "</span>";
		endif;

		echo "</span>";
	}

// ONLY time selector
	function print_time_selector($A){
		$D = array(
			'disable_date_editing'=> false,
			'minute_increment'=> 1,
			'time_format'=> 'H:i:s',
			'minutes'=> 0,		
			'var'=>'_unix',		
			'type'=> 'hm', // (hm) hour/min OR (tod) time of day
		);
		$A = array_merge($D, $A);

		extract($A);

		$hr24 = false;
		if(!empty($time_format) && strpos($time_format, 'H')!== false) $hr24 = true;

		$unix = $minutes * 60;

		// processings
		$hour = gmdate( ($hr24? 'H':'h'), $unix);
		$minute = gmdate( 'i', $unix);
		$ampm = gmdate( 'a', $unix);

		echo "<span class='evo_date_time_select time_select ". esc_attr( $type )."' > 
			<span class='evo_time_edit'>
				<input type='hidden' name='". esc_attr( $var )."' value='". esc_attr( $unix )."'/>
				<span class='time_select'>";
				if($disable_date_editing){
					echo "<span>". esc_html( $hour ) ."</span>";
				}else{													
					echo "<select class='evo_timeselect_only _hour' name='_hour' data-role='none'>";

					for($x=1; $x< ($hr24? 25:13 );$x++){	
						$y = ($hr24)? sprintf("%02d",($x-1)): $x;							
						echo "<option value='". esc_attr( $y )."'".(($hour==$y)?'selected="selected"':'').">". esc_html( $y )."</option>";
					}
					echo "</select>";
				}
				echo " Hr </span>";

				echo "<span class='time_select'>";
				if($disable_date_editing){
					echo "<span>". esc_html( $minute ) ."</span>";
				}else{	
					echo "<select class='evo_timeselect_only _minute' name='_minute' data-role='none'>";

					$minute_adjust = (int)(60/$minute_increment);
					for($x=0; $x<$minute_adjust;$x++){
						$min = $minute_increment * $x;
						$min = ($min<10)?('0'.$min):$min;
						echo "<option value='". esc_attr( $min ). "'".(($minute==$min)?'selected="selected"':'').">". esc_html( $min )."</option>";
					}
					echo "</select>";
				}
				echo " Min </span>";

				// AM PM
				if(!$hr24 && $type == 'tod'){
					echo "<span class='time_select'>";
					if($disable_date_editing){
						echo "<span>". esc_html( $ampm ) ."</span>";
					}else{	
						echo "<select name='_ampm' class='evo_timeselect_only _ampm'>";													
						foreach(array(
							'am'=> evo_lang_get('evo_lang_am','AM'),
							'pm'=> evo_lang_get('evo_lang_pm','PM') ) as $f=>$sar
						){
							echo "<option value='". esc_attr( $f )."' ".(($ampm==$f)?'selected="selected"':'').">". esc_html( $sar ) ."</option>";
						}							
						echo "</select>";
						echo "</span>";
					}
				}
				
			echo "</span>
		</span>";
	}

	// @2.2.9
	function _get_date_picker_data(){
		$date_format = ( EVO()->cal->check_yn('evo_usewpdateformat','evcal_1') ) ? esc_attr( get_option('date_format') ) : 'Y/m/d';

		return array(
			'date_format' => esc_attr( $date_format ),
			'js_date_format' => esc_attr( _evo_dateformat_PHP_to_jQueryUI( $date_format  ) ),
			'time_format' =>  esc_attr( EVO()->calendar->time_format ) ,
			'sow'=> esc_attr( get_option('start_of_week') ),
		);
	}
	function _print_date_picker_values(){			
		$data_str = wp_json_encode($this->_get_date_picker_data());

		echo "<div class='evo_dp_data' data-d='".  $data_str ."'></div>";
	}

// Yes No Buttons
	public function print_yesno_btn( $args =''){
		echo $this->yesno_btn( $args );
	}
	function yesno_btn($args=''){
		$defaults = array(
			'id'=>'',
			'var'=>'', // the value yes/no
			'no'=>'',
			'default'=>'',
			'input'=>false,
			'inputAttr'=>'',
			'label'=>'',
			'guide'=>'',
			'guide_position'=>'',
			'abs'=>'no',// absolute positioning of the button
			'attr'=>'', // array
			'afterstatement'=>'',
			'nesting'=>false
		);
		
		$args = shortcode_atts($defaults, $args);

		extract($args);

		$_attr = $no = '';

		if(!empty($args['var'])){
			$args['var'] = (is_array($args['var']))? $args['var']: strtolower($args['var']);
			$no = ($args['var']	=='yes')? 
				 null: 
				 ( (!empty($args['default']) && $args['default']=='yes')? null:'NO');
		}else{
			$no = (!empty($args['default']) && $args['default']=='yes')? null:'NO';
		}


		if(!empty($args['attr'])){
			foreach($args['attr'] as $at=>$av){
				$_attr .= esc_attr( $at ) .'="'. esc_attr( $av ) .'" ';
			}
		}

		// afterstatement
			if(!empty($args['afterstatement'])){
				$_attr .= 'afterstatement="' . esc_attr( $args['afterstatement'] ) .'"';
			}
			
		// input field
		$input = '';
		if($args['input']){
			$input_value = (!empty($args['var']))? 
				$args['var']: (!empty($args['default'])? esc_attr( $args['default'] ) :'no');

			// Attribut values for input field
			$inputAttr = '';
			if(!empty($args['inputAttr'])){
				foreach($args['inputAttr'] as $at=>$av){
					$inputAttr .= esc_attr( $at ).'="'. esc_attr( $av ).'" ';
				}
			}

			// input field
			$input = "<input id='". esc_attr( $args['id'] ). "_input' {$inputAttr} type='hidden' name='". esc_attr( $args['id'] )."' value='". esc_attr( $input_value )."'/>";
		}

		$guide = '';
		if(!empty($args['guide'])){
			$guide = $this->tooltips($args['guide'], esc_attr( $args['guide_position'] ) );
		}

		$label = '';
		if(!empty($args['label']))
			$label = "<label class='ajde_yn_btn_label evo_elm' for='". esc_attr( $args['id'] )."_input'>". esc_html( $args['label'] )."{$guide}</label>";

		// nesting
			$nesting_start = $nesting_end = '';
			if($args['nesting']){
				$nesting_start = "<p class='yesno_row'>";
				$nesting_end = "</p>";
			}

		return $nesting_start.'<span id="'. esc_attr( $args['id'] ) .'" class="evo_elm ajde_yn_btn '.($no? 'NO':null).''.(($args['abs']=='yes')? ' absolute':null).'" '. $_attr.'><span class="btn_inner" style=""><span class="catchHandle"></span></span></span>'.$input.$label.$nesting_end;
	}

// DEFAULT CSS style colors @since 4.3
	function get_def_css(){
		$preset_data = array(
			'evo_color_1' => '202124',
			'evo_color_2' => '656565',
			'evo_color_link' => '656565',
			'evo_color_prime' => '00aafb',
			'evo_color_second' => 'fed584',
			'evo_font_1' => "'Poppins', sans-serif",
			'evo_font_2' => "'Noto Sans',arial",
		);
		return $preset_data;
	}

// SVG icons
	public function get_icon($name){
		if( $name == 'live'){
			return '<svg version="1.1" x="0px" y="0px" viewBox="0 0 73 53" enable-background="new 0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1, 0, 0, 1, -13.792313, -23.832699)"><g><path  d="M75.505,25.432c-0.56-0.578-1.327-0.906-2.132-0.913c-0.008,0-0.015,0-0.022,0    c-0.796,0-1.56,0.316-2.123,0.88l-0.302,0.302c-1.156,1.158-1.171,3.029-0.033,4.206c5.274,5.451,8.18,12.63,8.18,20.214    c0,7.585-2.905,14.764-8.18,20.214c-1.141,1.178-1.124,3.054,0.037,4.211l0.303,0.302c0.562,0.561,1.324,0.875,2.118,0.875    c0.009,0,0.018,0,0.026,0c0.803-0.007,1.569-0.336,2.128-0.912C81.95,68.158,85.5,59.39,85.5,50.121    C85.5,40.853,81.95,32.085,75.505,25.432z"/><path d="M20.928,50.121c0-7.583,2.905-14.762,8.18-20.214c1.14-1.177,1.124-3.051-0.036-4.209l-0.303-0.302    c-0.563-0.562-1.325-0.877-2.12-0.877c-0.008,0-0.017,0-0.025,0c-0.804,0.007-1.571,0.335-2.13,0.913    C18.049,32.085,14.5,40.853,14.5,50.121c0,9.269,3.549,18.037,9.995,24.689c0.56,0.578,1.327,0.906,2.131,0.913    c0.008,0,0.016,0,0.024,0c0.795,0,1.559-0.315,2.121-0.879l0.303-0.303c1.158-1.158,1.174-3.03,0.035-4.207    C23.833,64.884,20.928,57.705,20.928,50.121z"/><path  d="M65.611,36.945c-0.561-0.579-1.33-0.907-2.136-0.913c-0.006,0-0.013,0-0.019,0    c-0.799,0-1.565,0.319-2.128,0.886l-0.147,0.148c-1.151,1.159-1.164,3.026-0.028,4.201c2.311,2.387,3.583,5.532,3.583,8.854    c0,3.323-1.272,6.468-3.582,8.854c-1.137,1.175-1.125,3.042,0.027,4.201l0.147,0.148c0.562,0.567,1.329,0.886,2.128,0.886    c0.006,0,0.013,0,0.019,0c0.806-0.005,1.575-0.334,2.136-0.912c3.44-3.551,5.335-8.23,5.335-13.177    C70.946,45.175,69.052,40.496,65.611,36.945z"/><path d="M38.812,37.06l-0.148-0.148c-0.562-0.563-1.326-0.879-2.121-0.879c-0.008,0-0.016,0-0.024,0    c-0.804,0.006-1.571,0.335-2.131,0.913c-3.439,3.55-5.333,8.229-5.333,13.176c0,4.947,1.894,9.627,5.334,13.177    c0.559,0.577,1.327,0.905,2.131,0.912c0.008,0,0.016,0,0.023,0c0.795,0,1.559-0.315,2.121-0.879l0.148-0.148    c1.158-1.158,1.173-3.03,0.035-4.208c-2.31-2.387-3.583-5.53-3.583-8.854c0-3.322,1.272-6.467,3.583-8.854    C39.986,40.09,39.971,38.217,38.812,37.06z"/></g><circle cx="50" cy="50.009" r="6.5"/> </g></svg>';
		}
	}

// Tool Tips updated 4.0.2
// central tooltip generating function
	function tooltips($content, $position='', $echo = false, $handleClass= false, $class = ''){
		// tool tip position
			if(!empty($position)){
				$L = ' L';
				
				if($position=='UL')
					$L = ' UL';
				if($position=='U')
					$L = ' U';
			}else{
				$L = null;
			}

		$output = "<span class='ajdeToolTip". esc_attr( $L )." fa". ($handleClass? ' handle':'')." ". esc_attr( $class )."' data-d='". wp_kses_post( $content )."' data-handle='". esc_attr( $handleClass )."'></span>";

		if(!$echo)
			return $output;			
		
		echo $output;
	}
	public function print_tooltips($content ='' , $position=''){
		if( empty($content)) return;
		$this->tooltips($content, $position,true);
	}
	function echo_tooltips($content, $position=''){
		$this->tooltips($content, $position,true);
	}

	

// Icon Selector -@updated 4.5.2

	// @since 4.5.2
	public function print_get_icon_html(){
		echo $this->get_icon_html();
	}
	function get_icon_html(){
		include_once( AJDE_EVCAL_PATH.'/assets/fonts/fa_fonts.php' );

		ob_start();

		?>
		<div id='evo_icons_data' style='display:none'>
			<p class='evo_icon_search_bar evomar0'>
				<input id='evo_icon_search' type='search' class='evo_icon_search' placeholder='<?php esc_html_e('Type name to search icons','eventon');?>'/></p>
			<div class="evo_icon_selector fai_in">
				<ul class="faicon_ul">
				<?php
				// $font_ passed from incldued font awesome file above
				if(!empty($font_)){
					foreach($font_ as $fa){
						echo "<li data-v='". esc_attr( $fa )."'><i data-name='". esc_attr( $fa )."' class='fa ".esc_attr( $fa )."' title='". esc_attr( $fa )."'></i></li>";
					}
				}
				?>						
			</ul>
		</div></div>
		<?php
		return ob_get_clean();
	}
	function get_font_icons_data(){
		include_once( AJDE_EVCAL_PATH.'/assets/fonts/fa_fonts.php' );
		return $font_;
	}

// Import box +@version 4.3.5
	function print_import_box_html($args){
		$defaults = array(
			'box_id'=>'',
			'title'=>'',
			'message'=>'',
			'file_type'=>'.csv',
			'button_label'=> __('Upload','eventon'),
			'type'=>'popup',
		);
		$args = !empty($args)? array_merge($defaults, $args): $defaults;

		extract($args);

		?>
		<div class='evo_data_upload_window <?php echo esc_attr( $type );?>' data-id="<?php echo esc_attr( $box_id );?>" id='import_box' style='display:<?php echo $type == 'popup'? 'none':'';?>'>
			<span id="close" class='evo_data_upload_window_close'>X</span>
			<form id="evo_settings_import_form" action="" method="POST" data-link='<?php echo esc_url( AJDE_EVCAL_PATH );?> '>
					
				<h3 style='padding-bottom: 10px'><?php echo esc_html( $title );?></h3>
				<p ><i><?php echo wp_kses_post( $message );?></i></p>
				
				<input style=''type="file" id="file-select" name="settings[]" multiple="" accept="<?php echo esc_attr( $file_type );?>" data-file_type='<?php echo esc_attr( $file_type );?>'>
				
				<p><button type="submit" id="upload_settings_button" class='upload_settings_button evo_admin_btn btn_prime'><?php echo esc_html( $button_label );?></button></p>
			</form>
			<p class="msg" style='display:none'><?php _e('File Uploading','eventon');?></p>
		</div>
		<?php
	}

// wp Admin Tables
	function start_table_header($id, $column_headers, $args=''){ 

		$defaults = array(
			'classes'=>'',
			'display'=>'table'
		);
		$args = !empty($args)? array_merge($defaults, $args): $defaults;
		?>
		<table id="<?php echo esc_attr( $id );?>" class='evo_admin_table <?php echo !empty($args['classes'])? esc_attr( implode(' ',$args['classes']) ) :'';?>' style='display:<?php echo esc_attr( $args['display'] );?>'>
			<thead width="100%">
				<tr>
					<?php
					foreach($column_headers as $key=>$value){
						// width for column
						$width = (!empty($args['width'][$key]))? 'width="'. esc_attr( $args['width'][$key] ).'px"':'';
						echo "<th id='". esc_attr( $key ). "' class='column column-". esc_attr( $key )."' ". wp_kses_post( $width ).">". wp_kses_post( $value ) ."</th>";
					}
					?>
				</tr>
			</thead>
			<tbody id='list_items' width="100%">
		<?php
	}
	function table_row($data='', $args=''){
		$defaults = array(
			'classes'=>'',
			'tr_classes'=>'',
			'tr_attr'=>'',
			'colspan'=>'none'
		);
		$args = !empty($args) ?array_merge($defaults, $args): $defaults;

		// attrs
			$tr_attr = '';
			if(!empty($args['tr_attr']) && sizeof($args['tr_attr'])>0){
				foreach($args['tr_attr'] as $key=>$value){
					$tr_attr .= esc_attr( $key ) ."='". wp_kses_post( $value ) ."' ";
				}
			}
		
		if($args['colspan']=='all'){
			echo "<tr class='colspan-row ".(!empty($args['tr_classes'])? esc_attr( implode(' ',$args['tr_classes']) ) :'')."' ". esc_attr( $tr_attr ) .">";
			echo "<td class='column span_column ".(!empty($args['classes'])? esc_attr( implode(' ',$args['classes']) ) :'')."' colspan='". esc_attr( $args['colspan_count'] )."'>". wp_kses_post( $args['content'] ) ."</td>";
		}else{
			echo "<tr class='regular-row ".(!empty($args['tr_classes'])? esc_attr( implode(' ',$args['tr_classes']) ):'') ."' ". esc_attr( $tr_attr ).">";
			foreach($data as $key=>$value){
			
				echo "<td class='column column-". esc_attr( $key )." ".(!empty($args['classes'])? esc_attr( implode(' ',$args['classes']) ):'')."'>". wp_kses_post( $value )."</td>";
			}
		}
		
		echo "</tr>";
	}
	function table_footer(){
		?>
		</tbody>
		</table>
		<?php
	}



// styles and scripts
	function register_styles_scripts(){
		wp_register_style( 'evo_elements',EVO()->assets_path.'css/lib/elements.css',array(), EVO()->version);
		wp_register_script( 'evo_elements_js',EVO()->assets_path.'js/lib/elements.js',array(), EVO()->version, true);
	}
	function enqueue(){
		wp_enqueue_style( 'evo_elements' );
		wp_enqueue_script( 'evo_elements_js' );
	}

// shortcode generator - only in admin side
	function register_shortcode_generator_styles_scripts(){
		wp_register_style( 'evo_shortcode_generator',EVO()->assets_path.'lib/shortcode_generator/shortcode_generator.css',array(), EVO()->version);
		wp_register_script( 'evo_shortcode_generator_js',EVO()->assets_path.'lib/shortcode_generator/shortcode_generator.js',array(), EVO()->version, true);
	}
	function enqueue_shortcode_generator(){
		wp_enqueue_style( 'evo_shortcode_generator' );
		wp_enqueue_script( 'evo_shortcode_generator_js' );
	}

// Color picker
	function load_colorpicker(){
		wp_enqueue_style('colorpicker_styles');
		wp_enqueue_script('backender_colorpicker');
	}
	function register_colorpicker(){
		wp_register_script('backender_colorpicker',EVO()->assets_path.'lib/colorpicker/colorpicker.js' ,array('jquery'),EVO()->version, true);
		wp_register_style( 'colorpicker_styles',EVO()->assets_path.'lib/colorpicker/colorpicker_styles.css','',EVO()->version);
	}

}