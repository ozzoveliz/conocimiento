<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elements of form UI and others
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class AMGP_HTML_Elements {

	// Form Elements------------------------------------------------------------------------------------------/

	/**
	 * Add Default Fields
	 *
	 * @param array $input_array
	 * @param array $custom_defaults
	 *
	 * @return array
	 */
	public static function add_defaults( array $input_array, array $custom_defaults=array() ) {

		$defaults = array(
			'id'                => '',
			'name'              => 'text',
			'value'             => '',
			'label'             => '',
			'title'             => '',
			'class'             => '',
			'main_label_class'  => '',
			'label_class'       => '',
			'input_class'       => '',
			'input_group_class' => '',
			'radio_class'       => '',
			'action_class'      => '',
			'container_class'   => '',
			'desc'              => '',
			'info'              => '',
			'placeholder'       => '',
			'readonly'          => false,  // will not be submitted
			'required'          => '',
			'autocomplete'      => false,
			'data'              => false,
			'disabled'          => false,
			'max'               => 50,
			'options'           => array(),
			'label_wrapper'     => '',
			'input_wrapper'     => '',
			'icon_color'        => '',
			'return_html'       => false,
			'unique'            => true,
			'text_class'        => '',
			'icon'              => '',
			'list'              => array(),
			'btn_text'          => '',
			'btn_url'           => '',
			'more_info_text'    => '',
			'more_info_url'     => '',
			'tooltip_title'     => '',
			'tooltip_body'      => '',
			'tooltip_args'      => array(),
			'tooltip_external_links'      => array(),
			'is_pro'            => '',
			'is_pro_feature_ad' => '',
			'pro_tooltip_args'  => array(),
            'input_size'        => 'medium',
			'group_data'        => false
		);
		$defaults = array_merge( $defaults, $custom_defaults );
		return array_merge( $defaults, $input_array );
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @param array $args Arguments for the text field
	 * @param bool $return_html
	 * @return false|string
	 */
	public static function text( $args = array(), $return_html=false ) {

		if ( $return_html ) {
			ob_start();
		}

		$args = self::add_defaults( $args );

		$readonly = $args['readonly'] ? ' readonly' : '';
		$required = empty( $args['required'] ) ? '' : ' required';

		$group_data_escaped = self::get_data_escaped( $args['group_data'] );
		$data_escaped = self::get_data_escaped( $args['data'] );  ?>

		<div class="epkb-input-group epkb-admin__text-field <?php echo esc_html( $args['input_group_class'] ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>_group" <?php echo $group_data_escaped;  /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>

			<label class="<?php echo esc_attr( $args['label_class'] ); ?>" for="<?php echo esc_attr( $args['name'] ); ?>">  <?php
			    echo wp_kses_post( $args['label'] );

				self::display_tooltip( $args['tooltip_title'], $args['tooltip_body'], $args['tooltip_args'], $args['tooltip_external_links'] );

				if ( ! empty( $args['desc'] ) ) {
					echo wp_kses_post( $args['desc'] );
				}   ?>
			</label>

			<div class="input_container <?php echo esc_attr( $args['input_class'] ); ?>">
			    <input type="text"
			           class="epkb-input--<?php echo esc_attr( $args['input_size'] ); ?>"
			           name="<?php echo esc_attr( $args['name'] ); ?>"
			           id="<?php echo  esc_attr( $args['name'] ); ?>"
			           autocomplete="<?php echo ( $args[ 'autocomplete' ] ? 'on' : 'off' ); ?>"
			           value="<?php echo esc_attr( $args['value'] ); ?>"
			           placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"						<?php
			           echo $data_escaped . esc_attr( $readonly . $required );						?>
			           maxlength="<?php echo esc_attr( $args['max'] ); ?>"
			    >
			</div>

		</div>		<?php

		if ( $return_html ) {
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Renders several HTML radio buttons in a column
	 * Type of Radio buttons: use the input_group_class
	 *          epkb-radio-vertical-group-container           Regular Radio Group
	 *          epkb-radio-vertical-button-group-container    Button Style Radio Group
	 *
	 * @param array $args
	 * @return false|string
	 */
	public static function radio_buttons_vertical($args = array() ) {

		$defaults = array(
			'id'                => 'radio',
			'name'              => 'radio-buttons',
		);
		$args = self::add_defaults( $args, $defaults );

		$ix = 0;

		$group_data_escaped = self::get_data_escaped( $args['group_data'] );

		if ( $args['return_html'] ) {
			ob_start();
		}   ?>
        <div class="epkb-input-group <?php echo esc_attr( $args['input_group_class'] ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>_group" <?php echo $group_data_escaped; ?>>

			<span class="epkb-main_label <?php echo esc_attr( $args['main_label_class'] ); ?>">                <?php
				echo wp_kses_post( $args['label'] );
                self::display_tooltip( $args['tooltip_title'], $args['tooltip_body'], $args['tooltip_args'], $args['tooltip_external_links'] ); ?>
            </span>

            <div class="epkb-radio-buttons-container <?php echo esc_attr( $args['input_class'] ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>">              <?php

				foreach( $args['options'] as $key => $label ) { ?>
                    <div class="epkb-input-container">

                        <input class="epkb-input" type="radio"
                               name="<?php echo esc_attr( $args['name'] ); ?>"
                               id="<?php echo esc_attr( $args['name'] . $ix ); ?>"
                               value="<?php echo esc_attr( $key ); ?>"  <?php
								checked( $key, $args['value'] );	?>
                        >
                        <label class="epkb-label" for="<?php echo esc_attr( $args['name'] . $ix ); ?>">
                            <span class="epkb-label__text"><?php echo esc_html( $label ); ?></span>
                        </label>


                    </div> <?php

					$ix++;
				} //foreach				?>

            </div> <?php

			if ( $args['desc'] ) {
				echo wp_kses_post( $args['desc'] );
			} ?>

        </div>	<?php

		if ( $args['return_html'] ) {
			return ob_get_clean();
		}
	}

	/**
	 * Output submit button
	 *
	 * @param string $button_label
	 * @param string $action
	 * @param string $main_class
	 * @param string $html - any additional hidden fields
	 * @param bool $unique_button - is this unique button or a group of buttons - use 'ID' for the first and 'class' for the other
	 * @param bool $return_html
	 * @param string $inputClass
	 * @return string
	 */
	public static function submit_button_v2( $button_label, $action, $main_class='', $html='', $unique_button=true, $return_html=false, $inputClass='' ) {

		if ( $return_html ) {
			ob_start();
		}		?>

		<div class="epkb-submit <?php echo esc_attr( $main_class ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">     <?php

			if ( $unique_button ) {  ?>
				<input type="hidden" name="_wpnonce_epkb_ajax_action" value="<?php echo wp_create_nonce( "_wpnonce_epkb_ajax_action" ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  ?>">
				<input type="submit" id="<?php echo esc_attr( $action ); ?>" class="<?php echo esc_attr( $inputClass ); ?>" value="<?php echo esc_attr( $button_label ); ?>" >  <?php
			} else {    ?>
				<input type="submit" class="<?php echo esc_attr( $action ) . ' ' . esc_attr( $inputClass ); ?>" value="<?php echo esc_attr( $button_label ); ?>" >  <?php
			}

			echo wp_kses_post( $html );  ?>
		</div>  <?php

		if ( $return_html ) {
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Renders an HTML Text field
	 * This has Wrappers because you need to be able to wrap both elements ( Label , Input )
	 *
	 * @param array $args Arguments for the text field
	 * @return string Text field
	 */
	public static function text_basic( $args = array() ) {

		$args = self::add_defaults( $args );
		$id             = $args['name'];
		$label_wrap_open_escaped  = '';
		$label_wrap_close_escaped = '';
		$group_data_escaped = self::get_data_escaped( $args['group_data'] );
		$data_escaped = self::get_data_escaped( $args['data'] );

		if ( ! empty( $args['label_wrapper']) ) {
			$label_wrap_open_escaped   = '<' . esc_html( $args['label_wrapper'] ) . ' class="' . esc_attr( $args['main_label_class'] ) . '">';
			$label_wrap_close_escaped  = '</' . esc_html( $args['label_wrapper'] ) . '>';
		}
		if ( ! empty( $args['input_wrapper']) ) {
			$label_wrap_open_escaped   = '<' . esc_html( $args['input_wrapper'] ) . ' class="' . esc_attr( $args['input_group_class'] ) . '" ' . $group_data_escaped . '>';
			$label_wrap_close_escaped  = '<' . esc_html( $args['input_wrapper'] ) . '>';
		}

		if ( ! empty( $args['return_html'] ) ) {
			ob_start();
		}

		echo $label_wrap_open_escaped;  ?>
		<label class="<?php echo esc_attr( $args['label_class'] ); ?>" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $args['label'] ); ?></label>		<?php
		echo $label_wrap_close_escaped; ?>

		<input type="text" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $args['input_class'] ); ?>"
		       autocomplete="<?php echo ( $args['autocomplete'] ? 'on' : 'off' ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>"
		       placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" maxlength="<?php echo esc_attr( $args['max'] ); ?>" <?php echo $data_escaped . ( $args['readonly'] ? ' readonly' : '' ); ?> >		<?php

		if ( ! empty( $args['return_html'] ) ) {
			return ob_get_clean();
		}
		return '';
	}

	/**
	 * Display a tooltip for admin form fields.
	 *
	 * @param string $title - The title of the tooltip.
	 * @param string $body_escaped - The content/body of the tooltip.
	 * @param array $args - Additional arguments for the tooltip.
	 * @param array $external_links - An array of external link for the tooltip. //// [ [ 'link_text' => string, 'link_url' => string ], [...] ]
	 *
	 * @return void
	 */
	public static function display_tooltip( $title, $body_escaped, $args = array(), $external_links = array() ) {
		if ( empty( $body_escaped ) && empty( $external_links ) ) {
			return;
		}

		$defaults = array(
			'class'         => '',
			'open-icon'     => 'info-circle',
			'open-text'     => '',
			'link_text'     => esc_html__( 'Learn More', 'echo-knowledge-base' ),
			'link_url'      => '',
			'link_target'   => '_blank',
			'show_link_below'     => false
		);
		$args = array_merge( $defaults, $args );

		if ( ! empty( $args['show_link_below'] ) ) {

		}		?>

		<div class="epkb__option-tooltip <?php echo esc_attr( $args['class'] ); ?>">
			<span class="epkb__option-tooltip__button <?php echo $args['open-icon'] ? 'epkbfa epkbfa-' . esc_attr( $args['open-icon'] ) : ''; ?>">  <?php
				echo esc_html( $args['open-text'] );  ?>
			</span>
			<div class="epkb__option-tooltip__contents">    <?php
				if ( ! empty( $title ) ) {   ?>
					<div class="epkb__option-tooltip__header">						<?php
						echo esc_html( $title );  ?>
					</div>  <?php
				}   ?>
				<div class="epkb__option-tooltip__body">					<?php
					//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $body_escaped;

					if ( ! empty( $external_links ) ) {
						foreach ( $external_links as $external_link ) { ?>
							<div class="epkb__option-tooltip__body__external_link">
								<a target="_blank" href="<?php echo esc_url( $external_link['link_url'] ); ?>"><?php echo esc_html( $external_link['link_text'] ); ?></a><span class="epkbfa epkbfa-external-link"></span>
							</div> <?php
						}
					} ?>

				</div>  <?php
				if ( ! empty( $args['link_url'] ) ) { ?>
					<div class="epkb__option-tooltip__footer">
						<a href="<?php echo esc_url( $args['link_url'] ); ?>" class="epkb__option-tooltip__button" target="<?php echo esc_attr( $args['link_target'] ); ?>">  <?php
							echo esc_html( $args['link_text'] );    ?>
						</a>
					</div>  <?php
				}  ?>
			</div>
		</div>  <?php
	}

	/**
	 * Return data attributes with escaped keys and values
	 *
	 * @param $data
	 * @return string
	 */
	public static function get_data_escaped( $data ) {
		$data_escaped = '';

		if ( empty( $data ) ) {
			return $data_escaped;
		}

		foreach ( $data as $key => $value ) {
			$data_escaped .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}

		return $data_escaped;
	}
}
