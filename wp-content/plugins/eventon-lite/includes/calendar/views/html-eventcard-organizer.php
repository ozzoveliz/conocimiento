<?php 
/**
 * EventCard Organizer html content
 * @2.2.13
 */


$EO = $event_organizer;
						
// image
$img_src = (!empty($event_organizer->organizer_img_id)? 
	wp_get_attachment_image_src($event_organizer->organizer_img_id,'medium'): null);

$newdinwow = (!empty($event_organizer->organizer_link_target) && $event_organizer->organizer_link_target=='yes')? 'target="_blank"':'';

// Organizer link
	$org_link = '';
	if(!empty($EO->organizer_link) || !empty($EO->link) ){	

		if( !empty($EO->link) ) $org_link = $EO->link;
		if( !empty($EO->organizer_link) ) $org_link = $EO->organizer_link;

		$orgNAME = "<span class='evo_card_organizer_name_t marb5'><a ".( $newdinwow )." href='" . 
			evo_format_link( $org_link ) . "'>".$EO->name."</a></span>";
	}else{
		$orgNAME = "<span class='evo_card_organizer_name_t marb5'>". $EO->name."</span>";
	}	



$OT.= "<div class='evo_metarow_organizer evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
		<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_004', 'fa-headphones',$evOPT )."'></i></span>
		<div class='evcal_evdata_cell'>							
			<h3 class='evo_h3'>". evo_lang_get('evcal_evcard_org', 'Organizer') ."</h3>";

			$OT.= "<div class='evo_org_content'>";

			$OT.= (!empty($img_src)? 
				"<p class='evo_data_val evo_card_organizer_image'><img src='{$img_src[0]}'/></p>":null)."
			<div class='evo_card_organizer'>";

			$description = !empty($event_organizer->description) ? stripslashes($event_organizer->description): false;

			$org_data = '';
			$org_data .= "<h4 class='evo_h4 marb5'>" . $orgNAME . "</h4>" ;

			$org_data .= "<div class='evo_org_details'>";

				// description
				if( $description ) 
					$org_data .= "<div class='evo_card_organizer_description'>".$description."</div>";

				// contact
				if(!empty($event_organizer->organizer_contact) )
					$org_data .= "<span class='evo_card_organizer_contact'>". stripslashes($event_organizer->organizer_contact). "</span>";

				// address
				if( !empty($event_organizer->organizer_address) )
					$org_data .= "<span class='evo_card_organizer_address'>". stripslashes($event_organizer->organizer_address). "</span>";

			$org_data .= "</div>";

			// organizer social share
				$org_social = '';
				foreach($EVENT->get_organizer_social_meta_array() as $key=>$val){

					if( empty($EO->$key )) continue;

					$url = urldecode( $EO->$key );

					if( $key == 'twitter') $key = 'x-'. $key;
						
					$org_social .= "<a target='_blank' href='". $url . "'><i class='fa fa-{$key}'></i></a>";
				}
				if( !empty($org_social)) 
					$org_data .= "<div class='evo_card_organizer_social'>" .$org_social ."</div>";


			$OT .= apply_filters('evo_organizer_event_card', $org_data, $ED, $event_organizer->term_id);

			$OT .= "</div></div>							
		</div>
	</div>";

echo wp_kses_post( $OT );