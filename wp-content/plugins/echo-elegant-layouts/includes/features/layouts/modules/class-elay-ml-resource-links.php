<?php

/**
 *  Outputs the Resource Links module for KB Core Modular Main Page.
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class ELAY_ML_Resource_Links {

	const MAX_RESOURCES = 8;
	const DEFAULT_RESOURCE_IMAGE_SLUG = 'img/image-not-available.jpg';

	/**
	 * Display Resource Links
	 *
	 * @param $kb_config
	 */
	public static function display_resource_links( $kb_config ) {

		if ( ! empty( $kb_config['ml_resource_links_container_title_text'] ) ) {
			$title_html_tag_sanitized = ELAY_Utilities::sanitize_html_tag( $kb_config['ml_resource_links_container_title_html_tag'], 'h2' ); ?>
			<!-- Title -->
			<<?php echo esc_html( $title_html_tag_sanitized ); ?> class="elay-ml__module-resource-links__title"><?php echo esc_html( $kb_config['ml_resource_links_container_title_text'] ); ?></<?php echo esc_html( $title_html_tag_sanitized ); ?>><?php
		}

		if ( ! empty( $kb_config['ml_resource_links_container_description_text'] ) ) {    ?>
			<!-- Description -->
			<p class="elay-ml__module-resource-links__desc"><?php echo esc_html( $kb_config['ml_resource_links_container_description_text'] ); ?></p><?php
		}

		$resource_index = 1;
		$count_loops = 1;
		switch ( $kb_config['ml_resource_links_columns'] ) {
			case '2-col':
			default:
				$resources_per_row = 2;
				break;
			case '3-col':
				$resources_per_row = 3;
				break;
			case '4-col':
				$resources_per_row = 4;
				break;
		}

		$enabled_resources_number = 0;
		for ( $resource_number = 1; $resource_number <= self::MAX_RESOURCES; $resource_number ++ ) {
			 if ( $kb_config['ml_resource_links_' . $resource_number] == 'on' ) {
				 $enabled_resources_number++;
			 }
		}

		for ( $resource_number = 1; $resource_number <= self::MAX_RESOURCES; $resource_number ++ ) {

			if ( $kb_config['ml_resource_links_' . $resource_number] != 'on' ) {
				continue;
			}

			if ( $resource_index == 1 ) {   ?>
				<div class="elay-ml__module-resource-links__row">   <?php
			}

			self::resource_section( $kb_config, $resource_number );

			if ( $resource_index == $resources_per_row || $count_loops == $enabled_resources_number ) { ?>
				</div>  <?php
				$resource_index = 0;
			}

			$resource_index ++;
			$count_loops ++;
		}
	}

	private static function resource_section( $kb_config, $resource_number ) {

		$link_url = empty( $kb_config['ml_resource_links_' . $resource_number . '_button_url'] ) ? '#' : $kb_config['ml_resource_links_' . $resource_number . '_button_url'];
		$container_tag = 'section';
		$href = '';
		$icon_type = $kb_config['ml_resource_links_icon_type'];
		$location = $icon_type == 'none' ? 'none' : $kb_config['ml_resource_links_icon_location'];

		if ( $kb_config['ml_resource_links_option'] == 'link' ) {
			$container_tag = 'a';
			$href = 'href="' . esc_url( $link_url ) . '"';
		}

		echo '<' . $container_tag . ' ' . 'class="elay-resource-section"' . $href . '>';      ?>

		<div class="elay-resource-section__head elay-resource-section__head--<?php echo $location; ?>-location">        <?php

			if ( $icon_type != 'none' ) {   ?>
				<!-- Icon -->
				<div class="elay-resource-section__head_icon">  <?php
					if ( $icon_type == 'image' ) {
						$image_id = $kb_config['ml_resource_links_' . $resource_number . '_icon_image'];
						$image_url = wp_get_attachment_image_url( $image_id, 'full' );
						$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', TRUE );
						$image_title = get_the_title( $image_id );
						// set default values if the image url is empty
						if ( empty( $image_url ) ) {
							$image_url = Echo_Elegant_Layouts::$plugin_url . self::DEFAULT_RESOURCE_IMAGE_SLUG;
							$image_alt = __( 'Image not available', 'echo-knowledge-base' );
							$image_title = __( 'Image not available', 'echo-knowledge-base' );
						}   ?>
						<img class="epkb-cat-icon epkb-cat-icon--image " src="<?php echo empty( $image_url ) ? '' : esc_url( $image_url ); ?>" alt="<?php
								echo empty( $image_alt ) ? '' : esc_attr( $image_alt ); ?>" title="<?php echo empty( $image_title ) ? '' : esc_attr( $image_title ); ?>">    <?php
					} else {
						$icon_name = $kb_config['ml_resource_links_' . $resource_number . '_icon_font'];  ?>
						<span class="epkb-cat-icon epkb-cat-icon--font epkbfa <?php echo esc_attr( $icon_name ); ?>"></span>    <?php
					} ?>
				</div>  <?php
			}   ?>

			<!-- Title -->
			<div class="elay-resource-section__head_title">
				<div class="elay-resource-section__head_title__text"><?php echo esc_html( $kb_config['ml_resource_links_' . $resource_number . '_title_text'] ); ?></div>
			</div>

		</div>		<?php

		if ( ! empty( $kb_config['ml_resource_links_' . $resource_number . '_description_text'] ) ) {    ?>

			<!-- Section Body -->
			<div class="elay-resource-section__body">
				<p class="elay-resource-section__body_desc">
					<?php echo esc_html( $kb_config['ml_resource_links_' . $resource_number . '_description_text'] ); ?>
				</p>
			</div>      <?php
		}

		if ( $kb_config['ml_resource_links_option'] == 'button' ) { ?>
			<!-- Link -->
			<div class="elay-resource-section__link-container">
				<a href="<?php echo esc_url( $link_url ); ?>" class="elay-resource-section__button"><?php echo esc_html( $kb_config['ml_resource_links_' . $resource_number . '_button_text'] ); ?></a>
			</div>  <?php
		}   ?>

		</<?php echo $container_tag; ?>>  <?php
	}

	/**
	 * Returns inline styles for Resource Links Module; called from KB Core Modular Main Page.
	 *
	 * @param $output
	 * @param $kb_config
	 *
	 * @return string
	 */
	public static function get_inline_styles( $output, $kb_config ) {

		/*
		 * Legacy Layouts that have specific settings
		 */
		$legacy_layouts = [
			ELAY_Layout::BASIC_LAYOUT,
			ELAY_Layout::TABS_LAYOUT,
			ELAY_Layout::CATEGORIES_LAYOUT,
			ELAY_Layout::SIDEBAR_LAYOUT,
			ELAY_Layout::GRID_LAYOUT,
		];

		$output = '';

		// add configuration that is specific to Elegant Layouts
		$add_on_config = elay_get_instance()->kb_config_obj->get_kb_config_or_default( $kb_config['id'] );
		$kb_config = array_merge( $add_on_config, $kb_config );

		// Container -----------------------------------------/
		if ( ! empty( $kb_config['ml_resource_links_container_title_text'] ) ) {

			$output .= '
			.elay-ml__module-resource-links__title {
			' . ( empty( $kb_config['section_head_typography']['font-size'] ) ? '' : 'font-size:' . ( intval( $kb_config['section_head_typography']['font-size'] ) + 5 ) . 'px !important;' ) . '
			}
			#elay-ml__module-resource-links .elay-ml__module-resource-links__title,
			#elay-ml__module-resource-links .elay-ml__module-resource-links__desc {
				text-align: ' . $kb_config['ml_resource_links_container_text_alignment'] . ';
				color: ' . $kb_config['ml_resource_links_container_text_color'] . ';
			}';
		}
		$output .= '
			#elay-ml__module-resource-links {
				background-color: ' . $kb_config['ml_resource_links_container_background_color'] . ';
			}
		';

		// Resource Link  ------------------------------------/
		if ( $kb_config['kb_main_page_layout'] != ELAY_Layout::GRID_LAYOUT ) {

			$output .= '
				#elay-ml__module-resource-links .elay-resource-section {
					border-radius:' . $kb_config['section_border_radius']. 'px !important;
				} ';
		}
		if ( $kb_config[ 'ml_resource_links_border_width' ] == 'on' ) {
			$output .= '
				#elay-ml__module-resource-links .elay-resource-section {
					border-style: solid !important;
					border-width:' . $kb_config['section_border_width']. 'px !important;
				}';
		}

		// Box Shadows
		if ( $kb_config[ 'ml_resource_links_shadow' ] == 'on' && in_array( $kb_config['kb_main_page_layout'], $legacy_layouts ) ) {
			$setting_key_prefix = '';
			switch ( $kb_config['kb_main_page_layout'] ) {
				case ELAY_Layout::GRID_LAYOUT:
					$setting_key_prefix = 'grid_';
					break;
				case ELAY_Layout::SIDEBAR_LAYOUT:
					$setting_key_prefix = 'sidebar_';
					break;
				default: break;
			}
			switch ( $kb_config[$setting_key_prefix . 'section_box_shadow'] ) {
				case 'section_light_shadow':
					$output .= '#elay-ml__module-resource-links .elay-resource-section {
									box-shadow: 0px 3px 20px -10px rgba(0, 0, 0, 0.75);
								}';
					break;
				case 'section_medium_shadow':
					$output .= '#elay-ml__module-resource-links .elay-resource-section {
									box-shadow: 0px 3px 20px -4px rgba(0, 0, 0, 0.75);
								}';
					break;
				case 'section_bottom_shadow':
					$output .= '#elay-ml__module-resource-links .elay-resource-section {
									box-shadow: 0 2px 0 0 #E1E1E1;
								}';
					break;
				default:
					break;
			}
		}

		$output .= '
			#elay-ml__module-resource-links .elay-resource-section {
				border-color: ' . $kb_config['ml_resource_links_border_color'] . ';
				background-color: ' . $kb_config['ml_resource_links_background_color'] . ';
			}
			#elay-ml__module-resource-links .elay-resource-section__head_icon .epkb-cat-icon--image {
				width:  ' . $kb_config['ml_resource_links_icon_image_size'] . 'px;
				max-width:  ' . $kb_config['ml_resource_links_icon_image_size'] . 'px;
			}
			#elay-ml__module-resource-links .elay-resource-section__head_icon .epkb-cat-icon--font {
				font-size: ' . $kb_config['ml_resource_links_icon_image_size'] . 'px;
			}
			#elay-ml__module-resource-links .elay-resource- {
			 font-size: ' . ( empty( $kb_config['section_head_typography']['font-size'] ) ? 'inherit' :  $kb_config['section_head_typography']['font-size'] . 'px !important;' ) . '
		     font-weight: ' . ( empty( $kb_config['section_head_typography']['font-weight'] ) ? 'inherit' :  $kb_config['section_head_typography']['font-weight'] . '!important;' ) . '
			}
			#elay-ml__module-resource-links .elay-resource-section__body_desc {
				text-align: ' . $kb_config['ml_resource_links_description_text_alignment'] . ';
				color: ' . $kb_config['ml_resource_links_description_text_color'] . ';
			}
			#elay-ml__module-resource-links .elay-resource-section__head_icon {
				color: ' . $kb_config['ml_resource_links_icon_color'] . ';
			}
			#elay-ml__module-resource-links .elay-resource-section__link-container {
				text-align: ' . $kb_config['ml_resource_links_button_location'] . ';
			}
		';

		if ( $kb_config['ml_resource_links_option'] == 'button' ) {
			$output .= '
			#elay-ml__module-resource-links .elay-resource-section__button {
				color: ' . $kb_config['ml_resource_links_button_text_color'] . ';
				background-color: ' . $kb_config['ml_resource_links_button_background_color'] . ';
			}
			#elay-ml__module-resource-links .elay-resource-section__button:hover {
				background-color: ' . ELAY_Utilities::darken_hex_color( $kb_config['ml_resource_links_button_background_color'], 0.2 )  . '!important;
			}';
		} else {
			$output .= '
			#elay-ml__module-resource-links .elay-resource-section__link {
				color: ' . $kb_config['ml_resource_links_button_text_color'] . ';
			}
			#elay-ml__module-resource-links .elay-resource-section:hover {
				background-color: ' . $kb_config['ml_resource_links_background_hover_color'] . ';
			}';
		}

		return $output;
	}
}