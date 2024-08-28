<?php
/** 
 * EventON Admin forms interface
 * A global forms object that will be used by all eventon products
 * only Admin 
 */

class EVO_Forms{

	function get_view($fields, $values= array()){

		ob_start();
		echo "<div class='evo_admin_form'>";

		foreach($fields as $key=>$data){

			$v = isset($values[$key])? $values[$key]: '';
			$rq = isset($data['rq']) && $data['rq']? true: false;
			$F = isset($data['F'])? $data['F']: $key;

			switch($key){
				case 'plain':	?><<?php echo esc_html( $data['markup']);?>><?php echo esc_html($data['name']);?></<?php echo esc_html($data['markup']);?>><?php	break;
				case 'hidden':
					?><input class='evo_admin_field ' type='hidden' name='<?php echo esc_attr($F);?>' value='<?php echo esc_html($v);?>'/><?php
				break;
				case 'input_base':
					?><p class='<?php echo esc_attr( $key );?>'>
						<input class='evo_admin_field ' type='text' name='<?php echo esc_attr($F);?>' value='<?php echo esc_html($v);?>'/>
					</p><?php
				break;
				case 'input_2':
					?><p class='<?php echo esc_attr( $key );?>'>
					<span class='t'><?php echo esc_html( $data['name'] );?>: <?php echo $rq?'*':'';?></span>
					<input type='text' class='evo_admin_field <?php echo $rq?'rq':'';?>' name='<?php echo esc_attr( $F );?>' value='<?php echo esc_html($v);?>'/><?php

					if(isset($data['description'])) echo "<em>" . wp_kses_post( $data['description'] ) . "</em>";
					echo "</p>";
				break;

				case 'textarea':
					?><p class='<?php echo esc_attr($key );?>'>
					<span class='t'><?php echo esc_html( $data['name'] );?>: <?php echo $rq?'*':'';?></span>
					<textarea class='evo_admin_field <?php echo $rq?'rq':'';?>' name='<?php echo esc_attr($F);?>'><?php echo esc_textarea($v);?></textarea><?php

					if(isset($data['description'])) echo "<em>" . esc_textarea( $data['description'] ) . "</em>";

					echo "</p>";
				break;
				case 'submit':
					$attrs = '';
					if(isset($data['attrs'])){
						foreach($data['attrs'] as $k=>$kk){
							$attrs .= esc_attr( $k ) .'="'. esc_html( $kk ) .'" ';
						}
					}

					$cancel = '';
					if(isset($data['cancel'])){
						$cancel = " <a class='". esc_attr($data['cancel_class'])."'>". esc_html( $data['cancel_name']) ."</a>";
					}

					?><p class='<?php echo esc_attr($key);?>'><a class='<?php echo esc_attr($data['class']);?>' <?php echo $attrs;?>><?php echo esc_attr( $data['name'] );?></a><?php echo wp_kses_post( $cancel );?></p><?php
				break;
			}
		}
		
		echo "</div>";

		return ob_get_clean();

	}
}