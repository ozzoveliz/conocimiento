<?php

/**
 * HTML boxes and dialogs for admin pages
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_HTML_Forms {

	/********************************************************************************
	 *
	 *                                   NOTIFICATIONS
	 *
	 ********************************************************************************/

	/**
	 * HTML Notification box with Title and Body text.
	 *
	 * $values:
	 *  string $value['id']            ( Optional ) Container ID, used for targeting with other JS
	 *  string $value['type']          ( Required ) ( error, success, warning, info )
	 *  string $value['title']         ( Optional ) The big Bold Main text
	 *  HTML   $value['desc']          ( Required ) Any HTML P, List etc...
	 *
	 * @param array $args
	 * @param bool $return_html
	 *
	 * @return false|string|void
	 */
	public static function notification_box_popup( array $args = array(), $return_html=false ) {

		$icon = '';
		switch ( $args['type']) {
			case 'error':   $icon = 'epkbfa-exclamation-triangle';
				break;
			case 'error-no-icon':   $icon = '';
				break;
			case 'success': $icon = 'epkbfa-check-circle';
				break;
			case 'warning': $icon = 'epkbfa-exclamation-circle';
				break;
			case 'info':    $icon = 'epkbfa-info-circle';
				break;
		}

		if ( $return_html ) {
			ob_start();
		}   ?>

		<div <?php echo isset( $args['id'] ) ? 'id="' . esc_attr( $args['id'] ) . '"' : ''; ?> class="epkb-notification-box-popup <?php echo 'epkb-notification-box-popup--' . $args['type']; ?>">

			<div class="epkb-notification-box-popup__icon">
				<div class="epkb-notification-box-popup__icon__inner epkbfa <?php echo esc_html( $icon ); ?>"></div>
			</div>

			<div class="epkb-notification-box-popup__body">     <?php

				if ( ! empty( $args['title'] ) ) { ?>
					<h6 class="epkb-notification-box-popup__body__title">
						<?php echo wp_kses( $args['title'], array( 'a' => array(
							'href'  => array(),
							'title' => array()
						),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</h6>                <?php
				}

				if ( isset( $args['desc'] ) ) { ?>
					<div class="epkb-notification-box-popup__body__desc"><?php echo wp_kses( $args['desc'], array( 'a' => array(
							'href'  => array(),
							'title' => array()
						),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</div> <?php
				}

				if ( ! empty( $args['id'] ) ) {  ?>
					<div class="epkb-notification-box-popup__buttons-wrap">
						<span class="epkb-notification-box-popup__button-confirm epkb-notice-dismiss"<?php echo empty( $args['close_target'] ) ? '' : ' data-target="' . esc_html( $args['close_target'] ) . '"'; ?>
						      data-notice-id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['button_confirm'] ); ?></span>
					</div>     <?php
				}   ?>
			</div>

		</div>    <?php

		if ( $return_html ) {
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * This is the Top Notification Box
	 * Must be placed above the Admin Content ( #ekb-admin-page-wrap ). Used usually with hooks.
	 *
	 * @param array $args Array of Settings.
	 * @param bool $return_html Optional. Returns html if true, otherwise echo's out function html.
	 *
	 * @return string
	 */
	public static function notification_box_top( array $args = array(), $return_html=false ) {

		$icon = '';
		switch ( $args['type']) {
			case 'error':   $icon = 'epkbfa-exclamation-triangle';
				break;
			case 'error-no-icon':   $icon = '';
				break;
			case 'success': $icon = 'epkbfa-check-circle';
				break;
			case 'warning': $icon = 'epkbfa-exclamation-circle';
				break;
			case 'info':    $icon = 'epkbfa-info-circle';
				break;
		}

		if ( $return_html ) {
			ob_start();
		}        ?>

		<div <?php echo isset( $args['id'] ) ? 'id="' . $args['id'] . '"' : ''; ?> class="epkb-notification-box-top <?php echo 'epkb-notification-box-top--' . $args['type']; ?>">

			<div class="epkb-notification-box-top__icon">
				<div class="epkb-notification-box-top__icon__inner epkbfa <?php echo esc_html( $icon ); ?>"></div>
			</div>

			<div class="epkb-notification-box-top__body">                <?php
				if ( ! empty( $args['title'] ) ) { ?>
					<h6 class="epkb-notification-box-top__body__title">						<?php
						echo wp_kses( $args['title'], array( 'a' => array(
							'href'  => array(),
							'title' => array()
						),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</h6>                <?php
				}

				if ( isset( $args['desc'] ) ) { ?>
					<div class="epkb-notification-box-top__body__desc"><?php
						echo wp_kses( $args['desc'], array( 'a' => array(
							'href'  => array(),
							'title' => array()
						),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</div> <?php
				}

				if ( ! empty( $args['id'] ) ) {  ?>
					<div class="epkb-notification-box-top__buttons-wrap">
						<span class="epkb-notification-box-top__button-confirm epkb-notice-dismiss"<?php echo empty( $args['close_target'] ) ? '' : ' data-target="' . esc_html( $args['close_target'] ) . '"'; ?>
						      data-notice-id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['button_confirm'] ); ?></span>
					</div>     <?php
				}   ?>
			</div>

		</div>    <?php

		if ( $return_html ) {
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * This is the Middle Notification Box
	 * Must be placed within the Admin Content ( #ekb-admin-page-wrap ). Used inside boxes and within the Admin Content.
	 *
	 * @param array $args Array of Settings.
	 * @param bool $return_html Optional. Returns html if true, otherwise echo's out function html.
     *
     * Types - success, error, error-no-icon, warning, info
	 *
	 * @return string
	 */
	public static function notification_box_middle( array $args = array(), $return_html=false ) {

		$icon = '';
		switch ( $args['type']) {
			case 'error':   $icon = 'epkbfa-exclamation-triangle';
				break;
			case 'success': $icon = 'epkbfa-check-circle';
				break;
			case 'warning': $icon = 'epkbfa-exclamation-circle';
				break;
			case 'info':    $icon = 'epkbfa-info-circle';
				break;
			case 'error-no-icon':
			case 'success-no-icon':
			default:
				break;
		}

		if ( $return_html ) {
			ob_start();
		}        ?>

		<div <?php echo isset( $args['id'] ) ? 'id="' . esc_attr( $args['id'] ) . '"' : ''; ?> class="epkb-notification-box-middle <?php echo 'epkb-notification-box-middle--' . esc_attr( $args['type'] ); ?>">

			<div class="epkb-notification-box-middle__icon">
				<div class="epkb-notification-box-middle__icon__inner epkbfa <?php echo esc_html( $icon ); ?>"></div>
			</div>

			<div class="epkb-notification-box-middle__body">                <?php
				if ( ! empty( $args['title'] ) ) { ?>
					<h6 class="epkb-notification-box-middle__body__title">						<?php
						echo wp_kses( $args['title'], array( 'a' => array(
							'href'  => array(),
							'title' => array()
						),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</h6>                <?php
				}

				if ( isset( $args['desc'] ) ) { ?>
					<div class="epkb-notification-box-middle__body__desc"><?php
						echo wp_kses( $args['desc'], array(
							'a' => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
								'class'  => array(),
							),
							'span'      => array(
								'class' => array(),
							),
							'br'        => array(),
							'em'        => array(),
							'strong'    => array(),
							'ul'        => array(),
							'li'        => array(),
						) ); ?>
					</div> <?php
				}

				if ( ! empty( $args['id'] ) && ! empty( $args['button_confirm'] ) ) {  ?>
					<div class="epkb-notification-box-middle__buttons-wrap">
						<span class="epkb-notification-box-middle__button-confirm epkb-notice-dismiss"<?php echo empty( $args['close_target'] ) ? '' : ' data-target="' . esc_html( $args['close_target'] ) . '"'; ?>>
							<?php echo esc_html( $args['button_confirm'] ); ?></span>
					</div>     <?php
				}   ?>
			</div>

		</div>    <?php

		if ( $return_html ) {
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Show info or error message to the user
	 *
	 * @param $message
	 * @param string $title
	 * @param string $type
	 * @return string
	 */
	public static function notification_box_bottom( $message, $title='', $type='success' ) {

		$message = empty( $message ) ? '' : $message;

		return
			"<div class='eckb-bottom-notice-message'>
				<div class='contents'>
					<span class='" . esc_attr( $type ) . "'>" .
			( empty( $title ) ? '' : '<h4>' . esc_html( $title ) . '</h4>' ) . "
						<p> " . wp_kses_post( $message ) . "</p>
					</span>
				</div>
				<div class='epkb-close-notice epkbfa epkbfa-window-close'></div>
			</div>";
	}

	/**
	 * DIALOG BOX - User confirms action like delete records with OK or Cancel buttons.
	 *	$values ['id']                  CSS ID, used for JS targeting, no CSS styling.
	 *	$values ['title']               Top Title of Dialog Box.
	 *	$values ['body']                Text description.
	 *	$values ['form_inputs']         Form Inputs
	 *	$values ['accept_label']        Text for Accept button.
	 *	$values ['accept_type']         Text for Accept button. ( success, default, primary, error , warning )
	 *	$values ['show_cancel_btn']     ( yes, no )
	 *	$values ['show_close_btn']      ( yes, no )
	 *  $values ['hidden']              true/false hidden form or not, not required
	 *
	 * @param $values
	 */
	public static function dialog_confirm_action( $values ) { ?>

		<div id="<?php echo esc_attr( $values[ 'id' ] ); ?>" class="epkb-dialog-box-form" style="<?php echo empty( $values['hidden'] ) ? '' : 'display: none;'; ?>">

			<!---- Header ---->
			<div class="epkb-dbf__header">
				<h4><?php echo esc_html( $values['title'] ); ?></h4>
			</div>

			<!---- Body ---->
			<div class="epkb-dbf__body">				<?php
				echo empty( $values['body']) ? '' : wp_kses( $values['body'], ASEA_Utilities::get_admin_ui_extended_html_tags() ); ?>
			</div>

			<!---- Form ---->			<?php
			if ( ! empty( $values[ 'form_method' ] ) ) { 		?>
				<form class="epkb-dbf__form"  method="<?php echo esc_attr( $values['form_method'] ); ?>">				<?php
					if ( isset($values['form_inputs']) ) {
						foreach ( $values['form_inputs'] as $input ) {
							echo '<div class="epkb-dbf__form__input">' . wp_kses( $input, ASEA_Utilities::get_admin_ui_extended_html_tags() ) . '</div>';
						}
					} ?>
				</form>			<?php
			} 		?>

			<!---- Footer ---->
			<div class="epkb-dbf__footer">

				<div class="epkb-dbf__footer__accept <?php echo isset($values['accept_type']) ? 'epkb-dbf__footer__accept--' . esc_attr( $values['accept_type'] ) : 'epkb-dbf__footer__accept--success'; ?>">
					<span class="asea-accept-button epkb-dbf__footer__accept__btn">
						<?php echo $values['accept_label'] ? esc_html( $values['accept_label'] ) : esc_html__( 'Accept', 'echo-knowledge-base' ); ?>
					</span>
				</div>				<?php
				if ( ! empty( $values['show_cancel_btn' ] ) && $values['show_cancel_btn'] === 'yes' ) { 		?>
					<div class="epkb-dbf__footer__cancel">
						<span class="epkb-dbf__footer__cancel__btn"><?php esc_html_e( 'Cancel', 'echo-knowledge-base' ); ?></span>
					</div>				<?php
				} 		?>
			</div>  		           <?php

			if ( ! empty( $values['show_close_btn'] ) && $values['show_close_btn'] === 'yes' ) { 		?>
				<div class="epkb-dbf__close epkbfa epkbfa-times"></div>             <?php
			} 		?>

		</div>
		<div class="epkb-dialog-box-form-black-background"></div>		<?php
	}

}