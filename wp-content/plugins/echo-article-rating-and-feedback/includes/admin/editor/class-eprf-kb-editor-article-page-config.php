<?php

/**
 * Configuration for the front end editor
 */

class EPRF_KB_Editor_Article_Page_Config {

	// Frontend Editor Tabs
	const EDITOR_TAB_CONTENT = 'content';
	const EDITOR_TAB_STYLE = 'style';
	const EDITOR_TAB_FEATURES = 'features';
	const EDITOR_TAB_ADVANCED = 'advanced';

	const EDITOR_GROUP_DIMENSIONS = 'dimensions';

	/**
	 * Rating Element Zone
	 * @return array[]
	 */
	private static function article_rating_element_zone() {

		$settings = [

			// Content Tab
			'rating_text_value'                 => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-stars-module__text, .eprf-like-dislike-module__text',
				'text' => '1',
			],
			'rating_header_button_text'         => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'Button Text', 'echo-article-rating-and-feedback' ),
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike',
					'rating_like_style' => 'rating_like_style_4'
				]
			],
			'rating_like_style_yes_button'      => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-rate-like .epkbfa',
				'text' => 1,
				'style' => 'inline',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike',
					'rating_like_style' => 'rating_like_style_4'
				]
			],
			'rating_like_style_no_button'       => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-rate-dislike .epkbfa',
				'text' => 1,
				'style' => 'inline',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike',
					'rating_like_style' => 'rating_like_style_4'
				]
			],

			'rating_header_statistics'          => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'Ratings Statistics Pop-up', 'echo-article-rating-and-feedback' ),
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
			],
			'rating_stars_text'                 => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'style' => 'inline',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				]
			],
			'rating_stars_text_1'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline',
				'separator_above' => 'yes'
			],
			'rating_stars_text_2'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline'
			],
			'rating_stars_text_3'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline'
			],
			'rating_stars_text_4'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline'
			],
			'rating_stars_text_5'               => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline'
			],
			'rating_out_of_stars_text'          => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				],
				'style' => 'inline',
				'separator_above' => 'yes'
			],
			'rating_header_messages'            => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'Ratings Messages', 'echo-article-rating-and-feedback' ),
			],
			'rating_confirmation_positive'      => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
			],
			'rating_confirmation_negative'      => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
			],
			'rating_header_form'                => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'header',
				'content' => __( 'User Feedback Form', 'echo-article-rating-and-feedback' ),
			],
			'rating_feedback_title'             => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-article-feedback__title h5',
				'text' => '1',
			],
			'rating_feedback_title_information'     => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'raw_html',
				'content' => '<div class="epkb-editor__info"><a href="https://www.echoknowledgebase.com/documentation/article-rating-feedback-overview/" target="_blank">' . __( 'Learn More', 'echo-article-rating-and-feedback' ) . '</a></div>'
			],
            'rating_feedback_required_title'             => [
                'editor_tab' => self::EDITOR_TAB_CONTENT,
                'target_selector' => '.eprf-article-feedback__required-title h5',
                'text' => '1',
            ],
			'rating_feedback_required_title_information'     => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'type' => 'raw_html',
				'content' => '<div class="epkb-editor__info"><a href="https://www.echoknowledgebase.com/documentation/article-rating-feedback-overview/" target="_blank">' . __( 'Learn More', 'echo-article-rating-and-feedback' ) . '</a></div>'
			],
			'rating_feedback_name'              => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#eprf-form-name',
				'target_attr' => 'placeholder',
			],
			'rating_feedback_email'             => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#eprf-form-email',
				'target_attr' => 'placeholder',
			],
			'rating_feedback_description'       => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '#eprf-form-text',
				'target_attr' => 'placeholder',
			],
			'rating_feedback_support_link_text' => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-article-feedback__support-link a',
				'text' => '1',
			],
			'rating_feedback_support_link_url'  => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-article-feedback__support-link a',
				'target_attr' => 'href',
				'reload' => 1,
			],
			'rating_feedback_button_text'       => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-article-feedback__submit button',
				'text' => '1',
			],
			'rating_open_form_button_text'             => [
				'editor_tab' => self::EDITOR_TAB_CONTENT,
				'target_selector' => '.eprf-open_feedback_form',
				'text' => '1',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],

			// Style Tab
			'rating_text_typography'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#eprf-current-rating, #eprf-article-feedback-container, .eprf-stars-module__text, .eprf-like-dislike-module__text',
			],
			'rating_text_color'                 => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '#eprf-current-rating, #eprf-article-feedback-container, .eprf-stars-module__text, .eprf-like-dislike-module__text',
				'style_name' => 'color',
			],
			'rating_element_color'              => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-stars-container, .eprf-article-meta__star-rating',
				'style_name' => 'color',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				]
			],
			'rating_dropdown_color'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-article-meta__statistics-toggle, .eprf-show-statistics-toggle',
				'style_name' => 'color',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				]
			],
			'rating_like_color'                 => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-rate-like .epkbfa',
				'style_name' => 'color',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike'
				]
			],
			'rating_dislike_color'              => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-rate-dislike .epkbfa',
				'style_name' => 'color',
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike'
				]
			],
			'rating_feedback_button_color'      => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'styles' => [
					'.eprf-article-feedback__submit button' => 'background-color',
					'.eprf-leave-feedback-form--close' => 'color',
				],
				'separator_above' => 'yes'
			],
			'link_to_article_content_style'     => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'type' => 'raw_html',
				'toggler'           => 'article_content_enable_rows',
				'content' => '<div class="epkb-editor__info"><span class="epkbfa epkbfa-info-circle"></span> <a href="#" data-zone="article_content"
                                    class="epkb-editor-navigation__link">' . __( 'Edit the row style here', 'echo-article-rating-and-feedback' ) . '</a></div>'
			],
			'rating_header_open_form_button'              => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'type' => 'header',
				'separator_above' => 'yes',
				'content' => __( 'Open Feedback Button', 'echo-article-rating-and-feedback' ),
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_color'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form',
				'style_name' => 'color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_color_hover'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form:hover',
				'style_name' => 'color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_background_color'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form',
				'style_name' => 'background-color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_background_color_hover'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form:hover',
				'style_name' => 'background-color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_border_color'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form',
				'style_name' => 'border-color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_border_color_hover'             => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form:hover',
				'style_name' => 'border-color',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_border_radius' => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form',
				'style_name' => 'border-radius',
				'postfix' => 'px',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],
			'rating_open_form_button_border_width'  => [
				'editor_tab' => self::EDITOR_TAB_STYLE,
				'target_selector' => '.eprf-open_feedback_form',
				'style_name' => 'border-width',
				'postfix' => 'px',
				'toggler' => [
					'rating_open_form_button_enable' => 'on'
				]
			],

			// Features Tab
			'article_content_enable_rating_element' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => '1'
			],
			'rating_element_row'                => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler'           => 'article_content_enable_rows',
				'description' => __( 'Control the row settings in the Article Content section.', 'echo-article-rating-and-feedback' ),
				'reload' => '1'
			],
			'rating_element_alignment'          => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler' => [
					'article_content_enable_rows' => 'on',
					'rating_element_row' => '!article-bottom',
				],
				'reload' => '1'
			],
			'rating_element_sequence'           => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler' => [
					'article_content_enable_rows' => 'on',
					'rating_element_row' => '!article-bottom',
				],
				'target_selector' => '.eckb-article-content-rating-element-container',
				'reload' => '1'
			],
			'rating_mode'                       => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'separator_above' => 'yes',
			],
			'rating_like_style'                 => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike'
				]
			],
			'rating_layout'                     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'rating_header_form_2'              => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'type' => 'header',
				'content' => __( 'Form Settings', 'echo-article-rating-and-feedback' ),
			],
			'rating_feedback_trigger_stars'     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-five-stars'
				]
			],
            'rating_feedback_required_stars'     => [
                'editor_tab' => self::EDITOR_TAB_FEATURES,
                'toggler' => [
                    'rating_mode' => 'eprf-rating-mode-five-stars',
                ],
            ],
			'rating_feedback_name_prompt'       => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'rating_feedback_email_prompt'      => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'rating_feedback_trigger_like'      => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
				'toggler' => [
					'rating_mode' => 'eprf-rating-mode-like-dislike'
				]
			],
            'rating_feedback_required_like'     => [
                'editor_tab' => self::EDITOR_TAB_FEATURES,
                'toggler' => [
                    'rating_mode' => 'eprf-rating-mode-like-dislike',
                ],
            ],
			'rating_open_form_button_enable'     => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],

			// Advanced Tab
			'rating_element_size'               => [
				'editor_tab' => self::EDITOR_TAB_ADVANCED,
				'target_selector' => '.eprf-show-statistics-toggle, .eprf-stars-container, #eprf-article-buttons-container .eprf-like-dislike-module__buttons ',
				'style_name' => 'font-size',
				'style' => 'slider',
				'postfix' => 'px',
			],

		];

		return [
			'rating_element_zone' => [
				'title'     =>  __( 'Rating Element', 'echo-article-rating-and-feedback' ),
				'classes'   => '#eprf-article-buttons-container',
				'settings'  => $settings,
				'disabled_settings' => [
					'article_content_enable_rating_element' => 'off'
				]
			]];
	}

	private static function article_rating_statistics_zone() {

		$settings = [
			'article_content_enable_rating_stats'  => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
			'rating_statistics_row'                => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler'           => 'article_content_enable_rows',
				'description' => 'Control the row settings in the Article Content section.',
				'reload' => '1'
			],
			'rating_statistics_alignment'          => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler'           => 'article_content_enable_rows',
				'reload' => '1'
			],
			'rating_statistics_sequence'           => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'toggler'           => 'article_content_enable_rows',
				'reload' => '1'
			],
		];

		return [
			'rating_statistics_zone' => [
				'title'     =>  __( 'Rating Statistics', 'echo-article-rating-and-feedback' ),
				'classes'   => '.eckb-article-content-rating-element-container',
				'settings'  => $settings,
				'disabled_settings' => [
					'article_content_enable_rating_stats' => 'off',
					'article_content_enable_rating_element' => 'off'
				]
			]];
	}

	private static function article_rating_statistics_footer_zone() {
		
		$settings = [
			'rating_stats_footer_toggle' => [
				'editor_tab' => self::EDITOR_TAB_FEATURES,
				'reload' => 1,
			],
		];
		
		return [
			'metadata_footer' => [
				'settings'  => $settings,
			]
		];
	}

	/**
	 * Retrieve Editor configuration
	 * @return array
	 */
	public static function get_config() {

		$editor_config = [];

		$editor_config += self::article_rating_element_zone();
		$editor_config += self::article_rating_statistics_zone();
		$editor_config += self::article_rating_statistics_footer_zone();

		return $editor_config;
	}
}