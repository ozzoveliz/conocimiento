<?php

/**
 * Configuration for the front end editor
 */
class ELAY_KB_Editor_Main_Page_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';
	const EDITOR_TAB_GLOBAL = 'global';
	const EDITOR_TAB_DISABLED = 'hidden';
	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	/**
	 * Category Zone - all articles and categories
	 * @return array
	 */
	private static function grid_categories_container_zone() {
		$settings = [

			// Content Tab

			// Style Tab
			'grid_background_color'         => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template #elay-content-container, .epkb-ml-grid-template .epkb-section-container',
				'style_name' => 'background-color'
			],
			'section_border_color'     => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-top-category-box,
					#epkb-ml-grid-layout .elay-top-category-box,
					#epkb-ml__module-articles-list .epkb-ml-article-list-container .epkb-ml-article-section,
					#epkb-ml__module-faqs .epkb-ml-faqs-cat-container,
					#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section',
				'style_name' => 'border-color',
				'separator_above' => 'yes',
			],
			'section_border_radius'    => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'postfix' => 'px',
				'styles' => [
					'.elay-grid-template .elay-top-category-box, #epkb-ml-grid-layout .elay-top-category-box' => 'border-radius',
					'.elay-grid-template .section-head, #epkb-ml-grid-layout .elay-top-category-box' => 'border-top-left-radius',
					'#epkb-ml__module-articles-list .epkb-ml-article-list-container .epkb-ml-article-section' => 'border-radius',
					'#epkb-ml__module-faqs .epkb-ml-faqs-cat-container' => 'border-radius',
					'#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section' => 'border-radius',
					'.elay-grid-template .section-head, #epkb-ml-grid-layout .elay-top-category-box' . ' ' => 'border-top-right-radius',// space is important to have different keys in array
				],
			],
			'section_border_width'     => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-top-category-box,
					#epkb-ml-grid-layout .elay-top-category-box,
					#epkb-ml__module-articles-list .epkb-ml-article-list-container .epkb-ml-article-section,
					#epkb-ml__module-faqs .epkb-ml-faqs-cat-container,
					#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section',
				'style_name' => 'border-width',
				'postfix' => 'px'
			],

			// Features Tab
			'grid_nof_columns'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],
			'grid_section_box_shadow'         => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'grid_section_box_hover'         => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],

			'grid_section_body_height'      => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes',
			],
			'grid_section_box_height_mode'  => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],

			// Advanced Tab

		];

		return [
			'grid_categories_zone' => [
				'title'     =>  __( 'Categories', 'echo-knowledge-base' ),
				'classes'   => '#elay-grid-layout-page-container .elay-section-container, #epkb-ml-grid-layout .epkb-section-container',
				'settings'  => $settings,
				'parent_zone_tab_title' => __( 'Categories', 'echo-knowledge-base' ),
			]];
	}

	/**
	 * Grid Categories zone
	 * @return array
	 */
	private static function grid_categories_zone() {

		$settings = [

			// Style Tab
			'grid_section_typography'                    => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eckb-categories-list h2.elay-grid-category-name,
					.epkb-ml-faqs-cat-container .epkb-ml-faqs__cat-header h3,
					#epkb-ml__module-articles-list .epkb-ml-article-section__head,
					#epkb-ml-cat-article-sidebar .epkb-ml-article-section__head',
			],
			'grid_section_description_typography' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-category-desc',
				'toggler' => 'grid_section_desc_text_on'
			],			
			'section_head_category_icon_color'              => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-icon-elem,
					#epkb-ml-grid-layout .elay-icon-elem',
				'style_name' => 'color'
			],
			'section_head_font_color'              => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-grid-category-name,
					#epkb-ml-grid-layout .elay-grid-category-name,
					#epkb-ml__module-faqs .epkb-ml-faqs__cat-header h3',
				'style_name' => 'color'
			],

			'section_head_background_color'        => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .section-head,
					#epkb-ml-grid-layout .section-head',
				'style_name' => 'background-color'
			],

			// Features Tab
			'grid_section_head_alignment'               => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '.elay-grid-template .section-head,
					#epkb-ml-grid-layout .section-head',
				'reload' => 1,
				'custom_defaults' => [
					'left' => [
						'grid_section_cat_name_padding_left' => 0
					],
					'center' => [
						'grid_section_cat_name_padding_left'    => 0,
						'grid_section_cat_name_padding_right'   => 0,
						'grid_section_cat_name_padding_top'     => 0,
						'grid_section_cat_name_padding_bottom'  => 0,
					]

				]
			],
			'grid_category_icon_location'               => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes',
				'custom_defaults' => [
					'left' => [
						'grid_section_cat_name_padding_left' => 0
					],
					'top' => [
						'grid_section_cat_name_padding_left' => 0
					]
				]
			],
			'grid_section_icon_size'                    => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'postfix' => 'px',
				'styles' => [
					'.elay-icon-elem' => 'font-size',
					'.elay-icon-elem img' => 'max-height',
				]
			],
			'grid_section_desc_text_on'                 => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes',
			],
			'section_head_description_font_color'  => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '.elay-grid-category-desc',
				'style_name' => 'color',
				'toggler' => 'grid_section_desc_text_on'
			],
			'grid_section_divider'                      => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes'
			],
			'section_divider_color'                => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector' => '.elay-grid-template .section-head, #epkb-ml-grid-layout .section-head',
				'style_name' => 'border-bottom-color',
				'toggler' => 'grid_section_divider'
			],
			'grid_section_divider_thickness'            => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'   => '.elay-grid-template .section-head, #epkb-ml-grid-layout .section-head',
				'style_name'        => 'border-bottom-width',
				'toggler'           => 'grid_section_divider',
				'postfix'           => 'px',
			],
			'section_hyperlink_text_on' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'separator_above'   => 'yes',
			],

			// Advanced Tab
			'grid_section_head_padding'                 => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Category Padding', 'echo-knowledge-base' ),
				'units' => 'px',
				'subfields' => [
					'grid_section_head_padding_left'    => [
						'target_selector'   => '#elay-content-container .section-head, #epkb-ml-grid-layout .section-head',
						'style_name'        => 'padding-left',
						'postfix'           => 'px'
					],
					'grid_section_head_padding_top'     => [
						'target_selector'   => '#elay-content-container .section-head, #epkb-ml-grid-layout .section-head',
						'style_name'        => 'padding-top',
						'postfix'           => 'px'
					],
					'grid_section_head_padding_right'   => [
						'target_selector'   => '#elay-content-container .section-head, #epkb-ml-grid-layout .section-head',
						'style_name'        => 'padding-right',
						'postfix'           => 'px'
					],
					'grid_section_head_padding_bottom'  => [
						'target_selector'   => '#elay-content-container .section-head, #epkb-ml-grid-layout .section-head',
						'style_name'        => 'padding-bottom',
						'postfix'           => 'px'
					],
				]
			],
			'grid_section_cat_name_padding'             => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Category Name Padding', 'echo-knowledge-base' ),
				'units' => 'px',
				'subfields' => [
					'grid_section_cat_name_padding_left'    => [
						'target_selector'   => '#elay-content-container .elay-grid-category-name, #epkb-ml-grid-layout .elay-grid-category-name',
						'style_name'        => 'padding-left',
						'postfix'           => 'px'
					],
					'grid_section_cat_name_padding_top'     => [
						'target_selector'   => '#elay-content-container .elay-grid-category-name, #epkb-ml-grid-layout .elay-grid-category-name',
						'style_name'        => 'padding-top',
						'postfix'           => 'px'
					],
					'grid_section_cat_name_padding_right'   => [
						'target_selector'   => '#elay-content-container .elay-grid-category-name, #epkb-ml-grid-layout .elay-grid-category-name',
						'style_name'        => 'padding-right',
						'postfix'           => 'px'
					],
					'grid_section_cat_name_padding_bottom'  => [
						'target_selector'   => '#elay-content-container .elay-grid-category-name, #epkb-ml-grid-layout .elay-grid-category-name',
						'style_name'        => 'padding-bottom',
						'postfix'           => 'px'
					],
				],
				'separator_above' => 'yes'
			],
			'grid_section_desc_padding'                 => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Description Padding', 'echo-knowledge-base' ),
				'units' => 'px',
				'subfields' => [
					'grid_section_desc_padding_left'    => [
						'target_selector'   => '#elay-content-container .elay-grid-category-desc, #epkb-ml-grid-layout .elay-grid-category-desc',
						'style_name'        => 'padding-left',
						'postfix'           => 'px'
					],
					'grid_section_desc_padding_top'     => [
						'target_selector'   => '#elay-content-container .elay-grid-category-desc, #epkb-ml-grid-layout .elay-grid-category-desc',
						'style_name'        => 'padding-top',
						'postfix'           => 'px'
					],
					'grid_section_desc_padding_right'   => [
						'target_selector'   => '#elay-content-container .elay-grid-category-desc, #epkb-ml-grid-layout .elay-grid-category-desc',
						'style_name'        => 'padding-right',
						'postfix'           => 'px'
					],
					'grid_section_desc_padding_bottom'  => [
						'target_selector'   => '#elay-content-container .elay-grid-category-desc, #epkb-ml-grid-layout .elay-grid-category-desc',
						'style_name'        => 'padding-bottom',
						'postfix'           => 'px'
					],
				]
			],
			'grid_section_icon_padding'                 => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Icon Padding', 'echo-knowledge-base' ),
				'units' => 'px',
				'subfields' => [
					'grid_section_icon_padding_left'    => [
						'target_selector'   => '#elay-content-container .elay-icon-elem, #epkb-ml-grid-layout .elay-icon-elem',
						'style_name'        => 'padding-left',
						'postfix'           => 'px'
					],
					'grid_section_icon_padding_top'     => [
						'target_selector'   => '#elay-content-container .elay-icon-elem, #epkb-ml-grid-layout .elay-icon-elem',
						'style_name'        => 'padding-top',
						'postfix'           => 'px'
					],
					'grid_section_icon_padding_right'   => [
						'target_selector'   => '#elay-content-container .elay-icon-elem, #epkb-ml-grid-layout .elay-icon-elem',
						'style_name'        => 'padding-right',
						'postfix'           => 'px'
					],
					'grid_section_icon_padding_bottom'  => [
						'target_selector'   => '#elay-content-container .elay-icon-elem, #epkb-ml-grid-layout .elay-icon-elem',
						'style_name'        => 'padding-bottom',
						'postfix'           => 'px'
					],
				]
			],

		];

		return [
			'grid_section_head_zone' => [
				'title'     =>  __( 'Category Header', 'echo-knowledge-base' ),
				'classes'   => '#elay-grid-layout-page-container .section-head, #epkb-ml-grid-layout .section-head',
				'parent_zone_tab_title' => __( 'Category Header', 'echo-knowledge-base' ),
				'settings'  => $settings
			]];
	}

	/**
	 * Grid Section Body zone
	 * @return array
	 */
	private static function grid_articles_zone() {

		$settings = [

			// Content Tab
			'category_empty_msg'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.elay_grid_empty_msg',
				'text' => '1'
			],
			'grid_category_link_text'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.elay_grid_link_text',
				'text' => '1'
			],
			'grid_article_count_text'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.elay_grid_count_text--single',
				'text' => '1'
			],
			'grid_article_count_plural_text'        => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.elay_grid_count_text--plural',
				'text' => '1'
			],

			// Style Tab
			'grid_section_article_typography'       => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#elay-grid-layout-page-container #elay-content-container .elay-section-body p,
					#epkb-ml-grid-layout .elay-section-body p',
			],
			'article_typography'                    => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#epkb-ml-cat-article-sidebar .epkb-article-inner,
					#epkb-ml__module-faqs .epkb-ml-faqs__item__question .epkb-ml-faqs__item__question__text,
					#epkb-ml__module-articles-list .epkb-article-inner',
			],
			'section_category_font_color'          => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-top-category-box,
					#epkb-ml-grid-layout .elay-top-category-box,
					#epkb-ml__module-articles-list .epkb-article-inner .epkb-article__text,
					#epkb-ml__module-faqs .epkb-ml-faqs__item__question .epkb-ml-faqs__item__question__text,
					#epkb-ml-cat-article-sidebar .epkb-article__text',
				'style_name' => 'color'
			],
			'section_body_background_color'    => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.elay-grid-template .elay-section-body,
					#epkb-ml__module-articles-list .epkb-ml-article-list-container .epkb-ml-article-section,
					#epkb-ml__module-faqs .epkb-ml-faqs-cat-container,
					#epkb-ml__module-categories-articles #epkb-ml-cat-article-sidebar .epkb-ml-article-section,
				#epkb-ml-grid-layout .elay-section-body',
				'style_name' => 'background-color'
			],

			// Features Tab
			'grid_section_article_count'            => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1
			],

			'grid_section_body_alignment'           => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'target_selector'   => '.elay-grid-template .elay-section-body p, #epkb-ml-grid-layout .elay-section-body p',
				'style_name'        => 'text-align',
			],

			// Advanced Tab
			'grid_section_body_padding'             => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'group_type' => self::EDITOR_GROUP_DIMENSIONS,
				'label' => __( 'Padding', 'echo-knowledge-base' ),
				'units' => 'px',
				'subfields' => [
					'grid_section_body_padding_left'    => [
						'target_selector'   => '.elay-grid-template .elay-section-body, #epkb-ml-grid-layout .elay-section-body',
						'style_name'        => 'padding-left',
						'postfix'           => 'px'
					],
					'grid_section_body_padding_top'     => [
						'target_selector'   => '.elay-grid-template .elay-section-body, #epkb-ml-grid-layout .elay-section-body',
						'style_name'        => 'padding-top',
						'postfix'           => 'px'
					],
					'grid_section_body_padding_right'   => [
						'target_selector'   => '.elay-grid-template .elay-section-body, #epkb-ml-grid-layout .elay-section-body',
						'style_name'        => 'padding-right',
						'postfix'           => 'px'
					],
					'grid_section_body_padding_bottom'  => [
						'target_selector'   => '.elay-grid-template .elay-section-body, #epkb-ml-grid-layout .elay-section-body',
						'style_name'        => 'padding-bottom',
						'postfix'           => 'px'
					],
				]
			],

		];

		return [
			'grid_section_body_zone' => [
				'title'     =>  __( 'Articles', 'echo-knowledge-base' ),
				'classes'   => '.elay-section-body',
				'settings'  => $settings
			]];
	}

	/**
	 * Retrieve Editor configuration
	 * @param $kb_config
	 * @return array
	 */
	public static function get_config( $kb_config ) {

		$editor_config = [];

		if ( $kb_config['kb_main_page_layout'] == ELAY_Layout::GRID_LAYOUT ) {
			$editor_config += self::grid_categories_container_zone();
			$editor_config += self::grid_categories_zone();
			$editor_config += self::grid_articles_zone();
		}

		if ( $kb_config['kb_main_page_layout'] == 'Sidebar' ) {
			$editor_config += ELAY_KB_Editor_Sidebar_Config::get_config( 'main-page', $kb_config );
		}

		return $editor_config;
	}
}