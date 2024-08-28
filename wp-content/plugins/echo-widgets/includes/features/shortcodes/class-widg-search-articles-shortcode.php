<?php

/**
 * Shortcode - Search articles
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Search_Articles_Shortcode {

	public function __construct() {
		add_shortcode( 'widg-search-articles', array( $this, 'output_shortcode' ) );
	}

	public function output_shortcode( $attributes ) {
        global $eckb_kb_id;

		widg_load_public_resources_enqueue();

		// Presets parameter
		$preset = empty( $attributes['preset'] ) ? '' : WIDG_Utilities::sanitize_int( $attributes['preset'] );
		$preset = empty( $preset ) || $preset > 7 || $preset < 1 ? '1' : $preset;
		$preset = 'widg-search-preset-style-' . $preset;

        // allows to adjust the widget title
        $title = empty( $attributes['title'] ) ? '' : strip_tags( trim( $attributes['title'] ) );
        $title = empty( $title ) ? esc_html__( 'Search Articles', 'echo-widgets' ) : esc_html( $title );

        // get add-on configuration
        $kb_id = empty( $attributes['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $attributes['kb_id'];
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $search_box_hint = empty( $attributes['search_box_hint'] ) ?
                            WIDG_Utilities::get_kb_option( $kb_id, 'search_box_hint', __( 'Search the documentation...', 'echo-widgets' ), false ) :
                            strip_tags( $attributes['search_box_hint'] );
        $search_button_name = empty( $attributes['search_button_name'] ) ?
                              WIDG_Utilities::get_kb_option( $kb_id, 'search_button_name', __( 'Search', 'echo-widgets' ), false ) :
                              strip_tags( $attributes['search_button_name'] );

		$search_results_limit = empty( $attributes['search_results_limit'] ) ?
											widg_get_instance()->kb_config_obj->get_value( $kb_id, 'widg_search_results_limit', 8 ) :
											strip_tags( $attributes['search_results_limit'] );

        /*
		$style1 = $this->get_inline_style( 'background-color:: ' . $prefix . 'search_background_color' );
		$style2 = $this->get_inline_style( 'background-color:: ' . $prefix . 'search_btn_background_color, background:: ' . $prefix . 'search_btn_background_color,
		                                    border-width:: ' . $prefix . 'search_input_border_width, border-color:: ' . $prefix . 'search_btn_border_color' );
		$style3 = $this->get_inline_style( 'color:: ' . $prefix . 'search_title_font_color' );
		$style4 = $this->get_inline_style( 'border-width:: ' . $prefix . 'search_input_border_width, border-color:: ' . $prefix . 'search_text_input_border_color,
		                                    background-color:: ' . $prefix . 'search_text_input_background_color, background:: ' . $prefix . 'search_text_input_background_color' );
		$class1 = $this->get_css_class( 'widg-search, :: ' . $prefix . 'search_layout' );

		$search_input_width = $this->kb_config[$prefix . 'search_box_input_width'];
		$form_style = $this->get_inline_style('width:'. $search_input_width . '%' );*/

        $css_reset = $add_on_config['widg_shortcode_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_shortcode_css_defaults'] === 'on' ? 'defaults-reset' : '';

        // DISPLAY SEARCH BOX
        ob_start();

        if ( $preset === 'widg-search-preset-style-1' ) { 	?>
		
            <div class="widg-shortcode-doc-search-container <?php echo esc_attr($preset); ?>">

                <div class="<?php echo esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ); ?>  widg-shortcode-search-contents">
                    <h4 <?php //echo $style3; ?>> <?php echo esc_html( $title ); ?></h4>
                    <form class="widg-search-form" <?php //echo $form_style . ' ' . $class1; ?> method="get" action="">

                        <div class="widg-search-box">
                            <input type="text" <?php //echo $style4; ?> class="widg-search-terms" name="widg-search-terms" value="" placeholder="<?php echo esc_attr( $search_box_hint ); ?>" />
                            <input type="hidden" id="widg_kb_id" value="<?php echo esc_attr( $kb_id ); ?>"/>
							<input type="hidden" id="search_results_limit" value="<?php echo esc_attr( $search_results_limit ); ?>"/>						        <?php

					        if ( empty( $search_button_name ) ) {   ?>
                                <button id="widg-search-kb" class="ep_font_icon_search" <?php //echo $style2; ?>></button>					        <?php
                            } else {                                    ?>
                                <button id="widg-search-kb" class="widg-text-search" <?php //echo $style2; ?>><?php echo esc_html( $search_button_name ); ?> </button>	        <?php
                            }					        ?>

                            <div class="widg-loading-spinner"></div>
                        </div>
                        <div class="widg-search-results"></div>

                    </form>

                </div><!-- widg-shortcode-search-contents -->
            </div><!-- widg-shortcode-doc-search-container -->        <?php 
			
		} else { 	?>
			
            <div class="widg-shortcode-doc-search-container <?php echo esc_attr($preset); ?>">

                <div class="<?php echo esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ); ?>  widg-shortcode-search-contents">

                    <form class="widg-search-form" method="get" action="">

                        <div class="widg-search-box">
                            <input type="text" class="widg-search-terms" name="widg-search-terms" value="" placeholder="<?php echo esc_attr( $search_box_hint ); ?>" />
                            <input type="hidden" id="widg_kb_id" value="<?php echo esc_attr( $kb_id ); ?>"/>
							<input type="hidden" id="search_results_limit" value="<?php echo esc_attr( $search_results_limit ); ?>"/>
							<button id="widg-search-kb" class="ep_font_icon_search"></button>
                            <div class="widg-loading-spinner"></div>
                        </div>
                        <div class="widg-search-results"></div>

                    </form>

                </div><!-- widg-shortcode-search-contents -->
            </div><!-- widg-shortcode-doc-search-container -->        <?php
		}

        return ob_get_clean();
    }
}
