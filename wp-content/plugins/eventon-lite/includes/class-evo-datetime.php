<?php
/**
 * Eventon date time class.
 *
 * @class 		EVO_generator
 * @version		L2.2.16
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_datetime{		

	public $wp_time_format, $wp_date_format;

	/**	Construction function	 */
		public function __construct(){
			$this->wp_time_format = EVO()->calendar->time_format;
			$this->wp_date_format = EVO()->calendar->date_format;
		}

	// RETURN UNIX		
		// return just UNIX timestamps corrected for repeat intervals
			public function get_correct_event_repeat_time($post_meta, $repeat_interval=''){
				if(!empty($repeat_interval) && !empty($post_meta['repeat_intervals']) && $repeat_interval!='0'){
					$intervals = unserialize($post_meta['repeat_intervals'][0]);

					return array(
						'start'=> (isset($intervals[$repeat_interval][0])? 
							$intervals[$repeat_interval][0]:
							$intervals[0][0]),
						'end'=> (isset($intervals[$repeat_interval][1])? 
							$intervals[$repeat_interval][1]:
							$intervals[0][1]) ,
					);

				}else{// no repeat interval values saved
					$start = !empty($post_meta['evcal_srow'])? $post_meta['evcal_srow'][0] :0;
					return array(
						'start'=> $start,
						'end'=> ( !empty($post_meta['evcal_erow'])? $post_meta['evcal_erow'][0]: $start)
					);
				}
			}		
	

	// convert unix to lang formatted readable string
	// +3.0.3 u4.5.7
		public function get_readable_formatted_date($unix, $format = '', $tz = ''){

			if(empty($format)) $format = EVO()->calendar->date_format.' '.EVO()->calendar->time_format;

			return $this->__get_lang_formatted_timestr(
				$format, 
				eventon_get_formatted_time( $unix , $tz )
			);
			
		}

	// return datetime string for a given format using date-time data array
		public function date($dateformat, $array){	
			return $this->__get_lang_formatted_timestr($dateformat, $array);
		} 

		// return event date/time in given date format using date item array
		function __get_lang_formatted_timestr($dateform, $datearray){
			$time = str_split($dateform);
			$newtime = '';
			$count = 0;
			foreach($time as $timestr){
				// check previous chractor
					if( strpos($time[ $count], '\\') !== false ){ 
						//echo $timestr;
						$newtime .='';
					}elseif($count!= 0 &&  strpos($time[ $count-1 ], '\\') !== false ){
						$newtime .= $timestr;
					}else{
						$newtime .= (is_array($datearray) && array_key_exists($timestr, $datearray))? $datearray[$timestr]: $timestr;
					}
				
				$count ++;
			}
			return $newtime;
		}



}