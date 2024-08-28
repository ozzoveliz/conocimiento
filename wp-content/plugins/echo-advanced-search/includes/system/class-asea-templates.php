<?php

/**
 * Handle loading ASEA templates
 
 * @copyright   Copyright (C) 2018, Echo Plugins 
 * Some code adapted from code in EDD/WooCommmerce (Copyright (c) 2017, Pippin Williamson) and WP.
 */
class ASEA_Templates {

	public function __construct() {

		// WP don't use template_include for block themes
		if ( ASEA_Utilities::is_block_theme() ) {
			add_action( 'init', array( $this, 'register_block' ) );
			add_filter( 'get_block_templates', array( __CLASS__, 'block_template_loader' ), 999999, 3 );
		} else {
			add_filter( 'template_include', array( __CLASS__, 'template_loader' ), 99999 );
		}

		add_action( 'epkb_editor_backend_mode_search_page_content', array( __CLASS__, 'editor_backend_mode_loader' ) );
	}

	/**
	 * Load article templates. Templates are in the 'templates' folder.
	 *
	 * Templates can be overriden in /theme/knowledgebase/ folder.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function template_loader( $template ) {
		/** @var WP_Query $wp_query */
        global $wp_query, $eckb_is_kb_main_page;

		if ( isset( $wp_query ) && $wp_query->is_404() ) {
			return $template;
		}

		// ignore non-page/post conditions
        if ( ! self::is_post_page() ) {
            return $template;
        }

		// get current post
		$post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : '';
		if ( empty( $post ) || ! $post instanceof WP_Post ) {
			return $template;
		}

        // ignore posts that are not KB Articles; KB Main Page should not be in a post
        if ( $post->post_type == 'post' ) {
            return $template;
        }

		// ignore WordPress search results page
		if ( isset( $wp_query ) && $wp_query->is_search() ) {
			return $template;
		}

		// is this KB Main Page ?
		if ( $eckb_is_kb_main_page ) {
			$kb_id = ASEA_Utilities::get_eckb_kb_id();
		} else {
			// if not KB Main Page is this KB Article Page ?
			$kb_id = ASEA_KB_Handler::get_kb_id_from_post_type( $post->post_type );
			if ( is_wp_error( $kb_id ) ) {
				return $template;
			}

		}

        // handle KB search results page (backward compatibility)
		$search_keywords = ASEA_Utilities::get( _x('search', 'search query parameter in URL', 'echo-advanced-search') );
		$search_keywords_new = ASEA_Utilities::get( ASEA_Core_Utilities::get_search_query_param( $kb_id ) );
		if ( empty( $search_keywords ) && empty( $search_keywords_new ) ) {
			return $template;
		}

		// for KB search results load the template; if not found return default WP template
		$located_template = self::locate_template( 'search-results.php' );
		if ( empty( $located_template)  ) {
			return $template;
		}

		return $located_template;
	}

	/**
	 * Retrieve the name of the highest priority template file that exists.
	 *
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that CHILD THEME which
	 * inherit from a PARENT THEME can just overload one file. If the template is
	 * not found in either of those, it looks in KB template folder last
	 *
	 * Taken from bbPress
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @return false|string The template filename if one is located.
	 */
	public static function locate_template( $template_names ) {

		// No file found yet
		$located = false;

		// loop through hierarchy of template names
		foreach ( (array) $template_names as $template_name ) {

			// Continue if template is empty
			if ( empty( $template_name ) ) {
				continue;
			}

			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// loop through hierarchy of template file locations ( child -> parent -> our theme )
			foreach( self::get_theme_template_paths() as $template_path ) {
				if ( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break;
				}
			}

			if ( $located ) {
				break;
			}
		}

		return $located;
	}

	/**
	 * Returns a list of paths to check for template locations:
	 * 1. Child Theme Template
	 * 2. Parent Theme Template
	 * 3. KB Template
	 *
	 * @return array
	 */
	private static function get_theme_template_paths() {

		$template_dir = self::get_theme_template_dir_name();

		$file_paths = array(
			1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
			10 => trailingslashit( get_template_directory() ) . $template_dir,
			100 => self::get_templates_dir()
		);

		$file_paths = apply_filters( 'asea_template_paths', $file_paths );

		// sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	/**
	 * Retrieves a template part
	 *
	 * Taken from bbPress
	 *
	 * @param string $slug
	 * @param string $name Optional. Default null
	 * @param $kb_config - used in templates
	 * @param $article
	 * @param bool $load
	 *
	 * @return string
	 */
	public static function get_template_part( $slug, $name, /** @noinspection PhpUnusedParameterInspection */ $kb_config,
		/** @noinspection PhpUnusedParameterInspection */$article, $load = true ) {
		// Execute code for this part
		do_action( 'asea_get_template_part_' . $slug, $slug, $name );

		$load_template = apply_filters( 'asea_allow_template_part_' . $slug . '_' . $name, true );
		if ( false === $load_template ) {
			return '';
		}

		// Setup possible parts
		$templates = array();
		if ( isset( $name ) )
			$templates[] = $slug . '-' . $name . '.php';
		$templates[] = $slug . '.php';

		// Allow template parts to be filtered
		$templates = apply_filters( 'asea_get_template_part', $templates, $slug, $name );

		// Return the part that is found
		$template_path = self::locate_template( $templates );
		if ( ( true == $load ) && ! empty( $template_path ) ) {
			include( $template_path );
		}

		return $template_path;
	}

	/**
	 * Check if current post/page could be KB one
	 *
	 * @return bool
	 */
	public static function is_post_page() {
		global $wp_query;

		if ( ( isset( $wp_query->is_archive ) && $wp_query->is_archive ) ||
		     ( isset( $wp_query->is_embed ) && $wp_query->is_embed ) ||
		     ( isset( $wp_query->is_category ) && $wp_query->is_category ) ||
		     ( isset( $wp_query->is_tag ) && $wp_query->is_tag ) ||
		     ( isset( $wp_query->is_attachment ) && $wp_query->is_attachment ) ) {
			return false;
		}

		$post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : '';
		if ( empty( $post ) || ! $post instanceof WP_Post || empty( $post->post_type ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns the path to the EP templates directory
	 * @return string
	 */
	private static function get_templates_dir() {
		return Echo_Advanced_Search::$plugin_dir . 'templates';
	}

	/**
	 * Returns name of directory inside child or parent theme folder where KB templates are located
	 * Themes can filter this by using the asea_templates_dir filter.
	 *
	 * @return string
	 */
	private static function get_theme_template_dir_name() {
		return trailingslashit( apply_filters( 'asea_templates_dir', 'kb_templates' ) );
	}

	/**
	 * Load search template for editor in backend mode
	 */
	public static function editor_backend_mode_loader() {
		$hide_header_footer = true;
		include_once( self::locate_template( 'search-results.php' ) );
	}

	/***********************************************************************************************************************
	 *
	 * BLOCK THEMES FUNCTIONS
	 *
	 ***********************************************************************************************************************/

	/**
	 * Add the block template objects to be used. For KB pages, replace theme template with our own.
	 *
	 * @param array $query_result Array of template objects.
	 * @param array $query Optional. Arguments to retrieve templates.
	 * @param array $template_type wp_template or wp_template_part.
	 * @return array
	 */
	public static function block_template_loader( $query_result, $query, $template_type ) {

		// get KB template name or nothing
		$kb_template_name = self::template_loader( '' );

		// return if this is not KB page
		if ( ! $kb_template_name ) {
			return $query_result;
		}

		// WP has templates for the page with page-{slug} i.e. page-knowledge-base and in general for post/page ( 'single' general slug ).
		// We need only template for the page, not for all general single/posts
		$outside_kb_slugs = [ 'single', '404', 'index', 'archive' ];
		if ( empty( $query['slug__in'] ) || array_intersect( $outside_kb_slugs, $query['slug__in'] ) ) {
			return $query_result;
		}

		// random text
		$template_slug = $query['slug__in'][0];

		// we don't allow user themes with our KB files so this content not inside file, but like a string
		$template_content = '<!-- wp:template-part {"slug":"header"} /-->
							 <!-- wp:asea/content-block {} /-->
							 <!-- wp:template-part {"slug":"footer"} /-->';
		$kb_content = self::gutenberg_inject_theme_attribute_in_content( $template_content );

		// wp class to create custom templates based on the file content
		$template          = new \WP_Block_Template();
		$template->id             = 'asea' . '//' . $template_slug;     // theme//slug
		$template->theme          = 'asea';     // kb "theme"
		$template->source         = 'plugin';
		$template->slug           = $template_slug;
		$template->type           = $template_type;
		$template->title          = __( 'Knowledge Base Template', 'echo-knowledge-base' ); 		// Not used anywhere
		$template->content        = $kb_content; 		// file content + theme styles
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->origin         = 'plugin';
		// Templates loaded from the file system aren't custom. Ones that have been edited and loaded from the DB are.
		$template->is_custom      = false;
		// Don't appear in any Edit Post template selector dropdown.
		$template->post_types     = array();
		$template->area           = 'uncategorized';

		// Addons should rewrite all current templates (from kb and the theme) in $query_result and return only one right.
		return [ $template ];
	}

	/**
	 * Parses wp_template content and injects the current theme's
	 * stylesheet and blocks as a theme attribute into each wp_template_part
	 * Based on WooCommerce function of the same name.
	 *
	 * @param string $template_content serialized wp_template content.
	 *
	 * @return string Updated wp_template content.
	 */
	private static function gutenberg_inject_theme_attribute_in_content( $template_content ) {
		$has_updated_content = false;
		$new_content         = '';
		$template_blocks     = parse_blocks( $template_content );

		$blocks = self::gutenberg_flatten_blocks( $template_blocks );
		foreach ( $blocks as &$block ) {
			if (
				'core/template-part' === $block['blockName'] &&
				! isset( $block['attrs']['theme'] )
			) {
				$block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
				$has_updated_content     = true;
			}
		}

		if ( $has_updated_content ) {
			foreach ( $template_blocks as &$block ) {
				$new_content .= serialize_block( $block );
			}

			return $new_content;
		}

		return $template_content;
	}

	/**
	 * Returns an array containing the references of
	 * the passed blocks and their inner blocks.
	 *
	 * @param array $blocks array of blocks.
	 *
	 * @return array block references to the passed blocks and their inner blocks.
	 */
	private static function gutenberg_flatten_blocks( &$blocks ) {
		$all_blocks = array();
		$queue      = array();
		foreach ( $blocks as &$block ) {
			$queue[] = &$block;
		}
		$queue_count = count( $queue );

		while ( $queue_count > 0 ) {
			$block = &$queue[0];
			array_shift( $queue );
			$all_blocks[] = &$block;

			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as &$inner_block ) {
					$queue[] = &$inner_block;
				}
			}

			$queue_count = count( $queue );
		}

		return $all_blocks;
	}

	/**
	 * Register block, so it can be used in .html block theme templates
	 */
	public function register_block() {
		register_block_type( 'asea/content-block', [ 'render_callback' => [ $this, 'block_render_callback' ] ] );
	}

	/**
	 * ASEA block will use usual way to show kb. This block can be used by the user theme if need
	 * @param $attributes
	 * @param $content
	 * @return false|string
	 */
	public function block_render_callback( $attributes, $content ) {

		$template = self::template_loader( '' );
		$hide_header_footer = true;

		ob_start();

		if ( $template ) {
			require_once( $template );
		}

		return ob_get_clean();
	}
}