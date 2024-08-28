<?php
/**
 *	Event edit custom meta field data
 *	@version 2.2.16
 */

$metabox_array = array();
$p_id = get_the_ID();

$EVENT = new EVO_Event( $p_id );

// Custom Meta fields for events
	$num = evo_calculate_cmd_count( EVO()->cal->get_op('evcal_1') );
	for($x =1; $x<=$num; $x++){	
		if(!eventon_is_custom_meta_field_good($x)) continue;

		$fa_icon_class = EVO()->cal->get_prop('evcal__fai_00c'.$x);		

		$visibility_type = (!empty($evcal_opt1['evcal_ec_f'.$x.'a4']) )? $evcal_opt1['evcal_ec_f'.$x.'a4']:'all' ;

		$metabox_array[] = array(
			'id'=>'evcal_ec_f'.$x.'a1',
			'variation'=>'customfield',
			'name'=>	EVO()->cal->get_prop('evcal_ec_f'.$x.'a1'),		
			'iconURL'=> $fa_icon_class,
			'iconPOS'=>'',
			'x'=>$x,
			'visibility_type'=>$visibility_type,
			'type'=>'code',
			'content'=>'',
			'slug'=>'evcal_ec_f'.$x.'a1'
		);
	}

$closedmeta = eventon_get_collapse_metaboxes($EVENT->ID);

if( count($metabox_array)>0):
	foreach($metabox_array as $index=>$mBOX){
		ob_start();

		
		$x = $mBOX['x'];
		$__field_id = '_evcal_ec_f'. esc_attr( $x ) .'a1_cus';
		$__field_type = esc_attr( EVO()->cal->get_prop('evcal_ec_f'.$x.'a2') );

		echo "<div class='evcal_data_block_style1'>
				<div class='evcal_db_data ' data-id='". esc_attr( $__field_id )."'>";

				
			// FIELD - saved field value
				$__saved_field_value = ( !empty( $EVENT->get_prop( $__field_id ) ) ) ? 
					 $EVENT->get_prop( $__field_id ) : null ;

			switch ($__field_type) {
				case 'textarea':
					$__value = empty( $__saved_field_value ) ? ' ' : $__saved_field_value;
					wp_editor( $__value, $__field_id, array('wpautop' => true ));
					break;

				case 'textarea_trumbowig':
					EVO()->elements->print_element(array(
						'type'=> 'wysiwyg',
						'id'=> esc_attr( $__field_id ),
						'name'=> '',
						'value'=> $__saved_field_value
					));	
					break;

				case 'textarea_basic':

					EVO()->elements->print_element(array(
						'type'=> 'textarea',
						'id'=> esc_attr( $__field_id ),
						'name'=> '',
						'value'=>  $__saved_field_value 
					));	
					break;

				case 'button':
					$__saved_field_link = ($EVENT->get_prop("_evcal_ec_f".$x."a1_cusL")  )? $EVENT->get_prop("_evcal_ec_f".$x."a1_cusL"):null ;

					echo "<input type='text' id='". esc_attr( $__field_id )."' name='_evcal_ec_f". esc_attr( $x ) ."a1_cus' ";
					echo 'value="'.  ( empty( $__saved_field_value ) ? null: wp_kses_post( $__saved_field_value ) )  .'"';						
					echo "style='width:100%' placeholder='". __('Button Text','eventon')."' title='Button Text'/>";

					echo "<input type='text' id='_evcal_ec_f". esc_attr($x) ."a1_cusL' name='_evcal_ec_f". esc_attr($x) ."a1_cusL' ";
					echo 'value="'. esc_attr($__saved_field_link).'"';						
					echo "style='width:100%' placeholder='".esc_html__('Button Link','eventon')."' title='Button Link'/>";

						$onw = ($EVENT->get_prop("_evcal_ec_f". esc_attr( $x ) ."_onw") )? esc_attr( $EVENT->get_prop("_evcal_ec_f". esc_attr( $x ) ."_onw") ):null ;
					?>

					<span class='yesno_row evo'>
						<?php 	
						EVO()->elements->print_yesno_btn(array(
							'id'=>'_evcal_ec_f'. esc_attr( $x ) . '_onw',
							'var'=> esc_attr( $EVENT->get_prop('_evcal_ec_f'.$x . '_onw') ),
							'input'=>true,
							'label'=> esc_html__('Open in New window','eventon')
						));?>											
					</span>
				<?php
					break;
				default:
					EVO()->elements->print_element(array(
						'type'=> 'input',
						'id'=> esc_attr( $__field_id ),
						'name'=> '',
						'value'=>  $__saved_field_value 
					));	
					break;	
			}


		echo "</div></div>";

		$metabox_array[$index]['content'] = ob_get_clean();
		$metabox_array[$index]['close'] = ( $closedmeta && in_array($mBOX['id'], $closedmeta) ? true:false);
	}

	// process for visibility
	echo EVO()->evo_admin->metaboxes->process_content( $metabox_array );

else:
	echo '<p class="pad20"><span class="evomarb10" style="display:block">' . esc_html__('You do not have any custom meta fields activated.')  . '</span><a class="evo_btn" href="'. esc_url( get_admin_url(null, 'admin.php?page=eventon#evcal_009','admin') ) .'">'.  esc_html__('Activate Custom Meta Fields','eventon')  . '</a></p>';
endif;

//print_r($metabox_array);





