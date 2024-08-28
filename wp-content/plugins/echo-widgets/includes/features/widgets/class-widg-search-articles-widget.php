<?php

/**
 * Widget - Search articles
 *
 * @copyright   Copyright (c) 2018, Echo Plugins
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class WIDG_Search_Articles_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( 'widg_search_articles', 'Echo KB - ' . __( 'Search', 'echo-widgets' ),
			array( 
				'description' => __( 'Knowledge Base Search box.', 'echo-widgets' )
            )
		);

        add_action( 'wp_enqueue_scripts', 'widg_load_public_resources_now' );
    }

    /** 
     * Output the widget content.
     * @see WP_Widget::widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
	    global $eckb_kb_id;

		widg_load_public_resources_enqueue();
		
        // theme-specific HTML that surrounds this widget
        echo $args['before_widget'];

		$preset = empty($instance['preset']) ? 'widg-search-preset-style-1' : $instance['preset'];

        // allows to adjust the widget title
		$instance_title = empty( $instance['title'] ) ? '' : $instance['title'];
		$instance_widget_id = empty( $instance['widget_id'] ) ? '' : $instance['widget_id'];
        $title = apply_filters( 'widget_title', $instance_title, $instance_widget_id );
        $title = empty($title) ? esc_html__( 'Search Articles', 'echo-widgets' ) : esc_html( $title );

        // get add-on configuration
        $kb_id = empty( $instance['kb_id'] ) ? ( empty($eckb_kb_id) ? WIDG_KB_Config_DB::DEFAULT_KB_ID : $eckb_kb_id ) : $instance['kb_id'];
		
        $kb_id = WIDG_Utilities::sanitize_int( $kb_id, WIDG_KB_Config_DB::DEFAULT_KB_ID );

        $add_on_config = widg_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_id );

        $search_box_hint =  empty( $instance['search_box_hint'] ) ?
                                WIDG_Utilities::get_kb_option( $kb_id, 'search_box_hint', __( 'Search the documentation...', 'echo-widgets' ), false ) :
                                strip_tags($instance['search_box_hint']);

        $search_button_name = empty( $instance['search_button_name'] ) ?
                                WIDG_Utilities::get_kb_option( $kb_id, 'search_button_name', __( 'Search', 'echo-widgets' ), false ) :
                                strip_tags($instance['search_button_name']);


		$search_results_limit = empty( $instance['search_results_limit'] ) ?
										widg_get_instance()->kb_config_obj->get_value( $kb_id, 'widg_search_results_limit', 8 ) :
										strip_tags( $instance['search_results_limit'] );

        // DISPLAY SEARCH
        $css_reset = $add_on_config['widg_widget_css_reset'] === 'on' ? 'widg-reset' : '';
        $css_default = $add_on_config['widg_widget_css_defaults'] === 'on' ? 'defaults-reset' : '';

	    if ( $preset === 'widg-search-preset-style-1' ) { 	?>
            <div class="widg-widget-doc-search-container <?php echo esc_attr($preset); ?>">

                <div class="<?php  echo esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ); ?>  widg-widget-search-contents">
                    <h4 <?php //echo $style3; ?>> <?php echo esc_html( $title ); ?></h4>
                    <form class="widg-search-form" <?php //echo $form_style . ' ' . $class1; ?> method="get" action="">

                        <div class="widg-search-box">
                            <input type="text" <?php //echo $style4; ?> class="widg-search-terms" name="widg-search-terms" value="" placeholder="<?php echo esc_attr( $search_box_hint ); ?>" />
                            <input type="hidden" id="widg_kb_id" value="<?php echo esc_attr( $kb_id ); ?>"/>
							<input type="hidden" id="search_results_limit" value="<?php echo esc_attr( $search_results_limit ); ?>"/>						    <?php

						    if ( empty( $instance['search_button_name'] ) ) {   ?>
                                <button id="widg-search-kb" class="ep_font_icon_search" <?php //echo $style2; ?>></button>						    <?php
                            } else {        ?>
                                <button id="widg-search-kb" class="widg-text-search" <?php //echo $style2; ?>><?php echo esc_html( $search_button_name ); ?> </button>			    <?php
                            }						    ?>

                            <div class="widg-loading-spinner"></div>
                        </div>
                        <div class="widg-search-results"></div>

                    </form>
                </div>
            </div>	    <?php 
			
			} else { 	?>
			
	            <div class="widg-widget-doc-search-container <?php echo esc_attr($preset); ?>">

	                <div class="<?php  echo esc_attr( $css_reset ) . ' ' . esc_attr( $css_default ); ?>  widg-widget-search-contents">
	                    <form class="widg-search-form" method="get" action="">

	                        <div class="widg-search-box">
	                            <input type="text" class="widg-search-terms" name="widg-search-terms" value="" placeholder="<?php echo esc_attr( $search_box_hint ); ?>" />
	                            <input type="hidden" id="widg_kb_id" value="<?php echo $kb_id; ?>"/>
								<input type="hidden" id="search_results_limit" value="<?php echo $search_results_limit; ?>"/>
	                            <button id="widg-search-kb" class="ep_font_icon_search"></button>
	                            <div class="widg-loading-spinner"></div>
	                        </div>
	                        <div class="widg-search-results"></div>

	                    </form>
	                </div>
	            </div>	    <?php 
				
			} 	
			
        // theme-specific HTML that surrounds this widget
        echo $args['after_widget'];
    }

    /**
     * Shows widget form to collect its parameters.
     * @see WP_Widget::form
     *
     * @param array $instance
     * @return string|void
     */
    public function form( $instance ) {

        // Set up some default widget settings.
        $defaults = array(
            'title'                 => __( 'KB Search', 'echo-widgets' ),
            'kb_id'                 => WIDG_KB_Config_DB::DEFAULT_KB_ID,
            'search_box_hint'       => '',
            'search_button_name'    => '',
			'search_results_limit'  => 8,
			'preset'				=> 1
        );

        $instance = wp_parse_args( (array) $instance, $defaults );      ?>
        
        <!-- Title -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'echo-widgets' ) ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>

        <!-- Search Box Hint -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'search_box_hint' ) ); ?>"><?php _e( 'Search Box Hint: (optional)', 'echo-widgets' ) ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'search_box_hint' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'search_box_hint' ) ); ?>" type="text" value="<?php echo $instance['search_box_hint']; ?>" />
        </p>

        <!-- Search Button Name -->
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'search_button_name' ) ); ?>"><?php _e( 'Search Button Name: (optional)', 'echo-widgets' ) ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'search_button_name' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'search_button_name' ) ); ?>" type="text" value="<?php echo $instance['search_button_name']; ?>" />
        </p>

        <!-- KB ID -->
        <?php if ( defined( 'EM'.'KB_PLUGIN_NAME' ) ) {    ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"><?php _e( 'KB ID:', 'echo-widgets' ) ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'kb_id' ) ); ?>"
                       name="<?php echo esc_attr( $this->get_field_name( 'kb_id' ) ); ?>" type="text" value="<?php echo $instance['kb_id']; ?>" />
            </p>            <?php
        }   ?>

		<!-- Search Style Presets -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'preset' ) ); ?>"><?php _e( 'Search Style Presets: ', 'echo-widgets' ) ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'preset' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'preset' ) ); ?>">
				<?php for ( $i = 1; $i <= 7; $i++ ) { ?>
					<option <?php selected( $instance['preset'], 'widg-search-preset-style-' . $i ); ?> value="<?php echo 'widg-search-preset-style-' . $i; ?>"><?php echo __( 'Style ', 'echo-widgets' ) . $i; ?></option>
				<?php } ?>
			</select>
		</p>

		<!-- Search Results Limit -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'search_results_limit' ) ); ?>"><?php _e( 'Search results limit: (optional)', 'echo-widgets' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'search_results_limit' ) ); ?>"
				   name="<?php echo esc_attr( $this->get_field_name( 'search_results_limit' ) ); ?>" type="number" min="1" max="50" placeholder="8" value="<?php echo $instance['search_results_limit']; ?>" />
		</p>        <?php
    }

    /**
     * Process widget form input when user saves.
     * @see WP_Widget::update
     *
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

		$instance['title']                  = strip_tags( $new_instance['title'] );
		$instance['kb_id']                  = isset( $new_instance['kb_id'] ) ? strip_tags($new_instance['kb_id']) : WIDG_KB_Config_DB::DEFAULT_KB_ID;
		$instance['search_box_hint']        = isset( $new_instance['search_box_hint'] ) ? strip_tags( trim($new_instance['search_box_hint']) ) : __( 'Search the documentation...', 'echo-widgets' );
		$instance['search_button_name']     = isset( $new_instance['search_button_name'] ) ? strip_tags( trim($new_instance['search_button_name']) ) : __( 'Search', 'echo-widgets' );
		$instance['preset']                 = isset( $new_instance['preset'] ) ? strip_tags( trim($new_instance['preset']) ) : 1;
		$instance['search_results_limit']   = isset( $new_instance['search_results_limit'] ) ? strip_tags( trim($new_instance['search_results_limit']) ) : '';

        return $instance;
    }
}
