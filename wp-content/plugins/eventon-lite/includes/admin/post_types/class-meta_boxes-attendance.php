<?php
/**
 * Event Edit Meta box Attendance Mode
 * @version 2.2.16
 */

?>
<div class='evcal_data_block_style1 event_attendance_settings'>
	<div class='evcal_db_data'>
		<?php
		
		EVO()->elements->print_element( array(
			'type'=>'select_row',
			'row_class'=>'eatt_values',
			'name'=>'_attendance_mode',
			'value'=>	esc_attr( $EVENT->get_attendance_mode() ),
			'options'=>	array_map('esc_html', EVO()->cal->get_attendance_modes() )
		));
		?>		
	</div>
</div>