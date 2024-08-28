<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display Links Editor on article page
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 */
class KBLK_Article_Editor_View {

	public function __construct() {
		add_action( 'edit_form_after_title', array( $this, 'show_link_editor' ) );
	}

	/**
	 * Show button switch.
	 *
	 * @param $post
	 */
	public function show_link_editor( $post ) {

		// do not load if blocks are loaded
		if ( did_action( 'enqueue_block_editor_assets' ) ) {
			return;
		}

		$link_editor_config = KBLK_Utilities::get_postmeta( $post->ID, 'kblk-link-editor-data', array(), true, true );
		if ( is_wp_error( $link_editor_config ) ) {
			echo KBLK_Utilities::report_generic_error( 12, $link_editor_config );
			return;
		}

		$is_link_editor_on = empty($link_editor_config) ? false : ! empty($link_editor_config['link-editor-on']);
		$is_link_editor_on = empty($is_link_editor_on) ? KBLK_Utilities::get( 'linked-editor', '' ) == 'yes' : $is_link_editor_on;
		$switch             = $is_link_editor_on ? 'kblk-checked' : '';
		$switch_content     = $is_link_editor_on ? 'kblk-checked-content' : '';
		$switch_checkbox    = $is_link_editor_on ? 'checked' : '';
		$switch_name        = $is_link_editor_on ? 'Use Default Editor' : 'Switch to Link';
		$switch_icon        = $is_link_editor_on ? 'epkbfa-id-card-o' : 'epkbfa-link';  ?>

		<div class="kblk-article-link-switch <?php echo $switch; ?>">
			<label for="kblk-link-editor-on">
				<input type="checkbox" name="kblk_link_editor_on" id="kblk-link-editor-on" value="yes" <?php echo $switch_checkbox; ?>>
				<input type="hidden" name="kblk_link_editor_mode" id="kblk_link_editor_mode" value="<?php echo $is_link_editor_on ? 'yes' : 'no'; ?>">
				<i class="kblk-switch-icon epkbfa <?php echo $switch_icon; ?>"></i>
				<span class="kblk-switch-text"><?php echo $switch_name; ?></span>
			</label>
		</div>    <?php

		$esc_url_value = empty($link_editor_config['url']) ? '' : 'value="' . esc_url($link_editor_config['url']) . '"';
		$esc_title_attr_value = empty($link_editor_config['title-attribute']) ? '' : 'value="' . esc_attr($link_editor_config['title-attribute']) . '"';
		$new_tab = empty($link_editor_config['open-new-tab']) ? '' : 'checked';
		$selected_icon = empty($link_editor_config['icon']) ? '' : $link_editor_config['icon'];

		// add search keywords box
		$search_terms = KBLK_Utilities::get_postmeta( $post->ID, 'kblk_search_terms', '', false, true );
		if ( is_wp_error( $search_terms ) ) {
			echo KBLK_Utilities::report_generic_error( 12, $search_terms );
			return;
		}   ?>

		<div class="kblk-article-link-container <?php esc_attr_e($switch_content); ?>">
			<div class="kblk-article-url-container">
				<label class="kblk-custom-url">URL Link</label>
				<input id="kblk-link-url" name="kblk_link_url" type="text" placeholder="URL link (http://example.com)" <?php echo $esc_url_value; ?>>
				<input id="kblk-open-new-tab" name="kblk_open_new_tab" type="checkbox" <?php echo esc_attr($new_tab); ?>>
			    <label for="kblk-open-new-tab">Open link in a new Tab</label>
			</div>

			<div class="kblk-article-link-title-container">
				<label class="kblk-link-title">Link Title Attribute</label>
				<input id="kblk-link-title-attribute" name="kblk_link_title_attribute" type="text"
				placeholder="Briefly Explain the link content" <?php echo $esc_title_attr_value; ?>>
				<i>(optional)</i>
				<a target="_blank" href="https://www.echoknowledgebase.com/documentation/links-editor-overview/">Learn More</a>
			</div>

			<div class="kblk-article-search-keywords-container">
				<label class="kblk-search-label">Search Keywords</label>
				<div class="kblk-input-container">
					<textarea name="kblk_search_terms" rows="1" cols="50"><?php	echo esc_html($search_terms); ?></textarea>
					<span class="kblk-input-tip">*Add keywords for KB Search Results.  Example: computer, tablet, phone, laptop</span>
					<span class="kblk-search-info-link">
						<i>(Recommended)</i>
						<a target="_blank" href="https://www.echoknowledgebase.com/documentation/links-editor-overview/">Learn More</a>
					</span>
				</div>

			</div>

			<div class="kblk-article-icon-selection">
				<h3>Common KB Icons</h3>
				<ul>    <?php

				$common_kb_icons = KBLK_Article_Link_Icons::get_common_icons();
				foreach( $common_kb_icons as $key => $value ) {
					$radio_checked = $selected_icon === $value ? 'checked' : '';   ?>
					<li>
						<div>
							<input type="radio" name="kblk_icon" id="epkbfa-<?php echo $value; ?>" value="epkbfa-<?php echo $value; ?>" <?php echo $radio_checked; ?>>
							<i class="epkbfa epkbfa-<?php echo $value; ?>"></i>
							<label for="epkbfa-<?php echo $value; ?>"><?php echo esc_html($key); ?></label>
						</div>
					</li>   <?php
				}   ?>

				</ul>
				<div class="kblk-misc-icons">
					<h3 class="kblk-more-icon">Other Icons <i class="epkbfa epkbfa-plus-square"></i><i>( View More )</i></h3>
					<ul>    <?php

					$other_icons = KBLK_Article_Link_Icons::get_other_icons();
					foreach( $other_icons as $key => $value ) {
						$radio_checked = $selected_icon === $value ? 'checked' : '';   ?>
						<li>
							<div>
								<input type="radio" name="kblk_icon" id="epkbfa-<?php echo $value; ?>" value="epkbfa-<?php echo $value; ?>" <?php echo $radio_checked; ?>>
								<i class="epkbfa epkbfa-<?php echo $value; ?>"></i>
								<label for="epkbfa-<?php echo $value; ?>"><?php echo $key; ?></label>
							</div>
						</li>   <?php
					}  ?>
					</ul>
				</div>
			</div>
		</div>  <?php

		wp_create_nonce( "_wpnonce_kblk_save_link_data" );
	}
}
