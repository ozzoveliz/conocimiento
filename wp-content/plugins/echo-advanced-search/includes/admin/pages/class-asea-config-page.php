<?php  if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display plugin settings
 *
 * @copyright   Copyright (C) 2018, Echo Plugins
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class ASEA_Config_Page {

	public function __construct() {

		$asea_debug = get_transient( '_epkb_advanced_search_debug_activated' );
		if ( $asea_debug ) {
			add_action( 'eckb_admin_config_menu', array( $this, 'display_asea_admin_menu' ) );

			// Add Search View to KB Configuration page
			add_filter( 'eckb_admin_config_page_views', array( $this, 'search_config_view' ), 10, 2 );
		}
		
		// OUTDATED - update to new admin UI
		// add_action( 'eckb_admin_config_page_views', array( $this, 'TODO show_tabs_body' ) );

		add_action( 'wp_ajax_asea_search_config_save', array($this, 'asea_search_config_save') );
		add_action( 'wp_ajax_nopriv_asea_search_config_save', array($this, 'asea_search_config_save') );
		
		add_action( 'wp_ajax_asea_search_audit_clear', array($this, 'asea_search_audit_clear') );
		add_action( 'wp_ajax_nopriv_asea_search_audit_clear', array($this, 'asea_search_audit_clear') );
	}

	public function display_asea_admin_menu( $kb_id ) {	?>
		<!--  Search Menu BUTTON -->
		<div class="epkb-info-section epkb-info-pages" id="epkb-search-page-button">
			<div class="page-icon-container">
				<p><?php _e( 'Search', 'echo-advanced-search' ); ?></p>
				<div class="page-icon epkbfa epkbfa-search" id="asea-search-config-page"></div>
			</div>
		</div>		<?php
	}

	/**
	 * Show selected tab content.  TODO
	 * @param $kb_id
	 * @param $kb_config
	 */
	private function show_tabs_body( $kb_id, $kb_config ) { ?>

		<div class="asea-config-content" id="kb_<?php echo $kb_id; ?>" data-kb_id="<?php echo $kb_id; ?>">

			<!-- TABS--->
			<div class="asea-config-content__header">
				<div class="asea-config-content__tab-button active" data-target="#asea_config_info">
					<i class="epkbfa epkbfa-search"></i><?php esc_html_e( 'Info', 'echo-advanced-search' ); ?>
				</div>
				<!--<div class="asea-config-content__tab-button" data-target="#asea_config_debug">
					<i class="epkbfa epkbfa-search"></i><?php // esc_html_e( 'Debug', 'echo-advanced-search' ); ?></div> -->
			</div>

			<!-- Info Tab--->
			<div id="asea_config_info" class="asea-config-content__tab active">
			<?php /* 
				<div class="asea-condig-content__intro">					<?php
					esc_html_e( 'Advanced Search engine has the following features:', 'echo-advanced-search' ); ?>
				</div>
				<div class="asea-condig-content__intro">					<?php
					esc_html_e( 'Tags. Matching Tags of each article against the user-entered keywords. Rule: If a tag exactly matches a keyword then include the article in search results', 'echo-advanced-search' ); ?>
				</div>
				<div class="asea-condig-content__intro">					<?php
					esc_html_e( 'Article Words. Matching article words against keywords. Rule: If a word in the article starts with the keyword (additional characters at the end is OK) then include the article in search results.', 'echo-advanced-search' ); ?>
				</div>
				<div class="asea-condig-content__intro">					<?php
					esc_html_e( 'Article Stop Words (currently only English). Excluding Stop Words from searches. Rule: Keywords that are considered Stop Words are not included in the search.', 'echo-advanced-search' ); ?>
				</div>
				<div class="asea-condig-content__intro">					<?php
				esc_html_e( 'HTML/CSS Keywords. Exclude common HTML/CSS keywords from article content search. Rule: Do not consider any HTML/CSS keywords that are inside article content for search.', 'echo-advanced-search' ); ?>
				</div> */ ?>
			</div>

			<!-- Debug Tab--->
			<!-- <div id="asea_config_debug" class="asea-config-content__tab">
				<div class="asea-condig-content__intro">
					<div class="asea-condig-content__intro">					<?php
				      esc_html_e( 'When a user triggers a search, the advanced search engine has to determine what language to use. ' .
				               'If debug is enabled, the language used for search is displayed at the end of the search results.', 'echo-advanced-search' ); ?>
					</div>
				</div>
			</div>-->

		</div>   <?php
	}

	/**
	 * User saves Search configuration
	 */
	public function asea_search_config_save() {

		// verify that request is authentic
		if ( ! isset( $_REQUEST['_wpnonce_asea_config'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_asea_config'], '_wpnonce_asea_config' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) );
			return;
		}

		// ensure user has correct permissions
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) );
		}

		$action = ASEA_Utilities::post( 'data_action' );
		if ( empty($action) || ! in_array($action, ['asea_synonyms_update','asea_debug_update']) ) {
			return;
		}

		$kb_id = empty($_POST['asea_kb_id']) ? '' : ASEA_Utilities::sanitize_get_id( $_POST['asea_kb_id'] );
		if ( is_wp_error( $kb_id ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'Error occurred. Please try again later.', 'echo-advanced-search' ) );
		}

		//Update ASEA config

		/* if ( $action == 'asea_synonyms_update' ) {

		   $value = ASEA_Utilities::post( 'asea_search_synonyms' );
		   if ( ! empty($value) && count(explode(",", $value)) > 20 ) {
		        ASEA_Utilities::ajax_show_error_die( __( 'Maximum 20 keywords allowed', 'echo-advanced-search' ) . '(46)' );
		   }

		   $this->update_asea_config( $kb_id, 'asea_synonyms', $value, __( 'Synonyms keywords updated', 'echo-advanced-search' ) );

		} else */
		if ( $action == 'asea_debug_update' ) {

			$value = ASEA_Utilities::post( 'asea_debug' );
			$this->update_asea_config( $kb_id, 'asea_debug', $value, __( 'Debug Settings Saved', 'echo-advanced-search' ) );
		}

		return;
	}

	private function update_asea_config( $kb_id, $key, $value, $success_msg ) {

        $result = asea_get_instance()->kb_config_obj->set_value($kb_id, $key, $value);
        if ( is_wp_error($result) ) {
            ASEA_Utilities::ajax_show_error_die( __( 'Error occurred. Please try again later.', 'echo-advanced-search' ) . '(48) - ' . $key );
        }

        ASEA_Utilities::ajax_show_info_die( $success_msg );
	}
	
	/**
	 * Clear audit debug logs 
	 */
	public function asea_search_audit_clear() {  // TODO move to controller class

		// verify that request is authentic
		if ( ! isset( $_REQUEST['_wpnonce_asea_config'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce_asea_config'], '_wpnonce_asea_config' ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'You do not have permission to edit this knowledge base', 'echo-knowledge-base' ) );
		}

		$action = ASEA_Utilities::post( 'action' );
		if ( empty($action) || $action != 'asea_search_audit_clear' ) {
			return;
		}

		$kb_id = empty($_POST['asea_kb_id']) ? '' : ASEA_Utilities::sanitize_get_id( $_POST['asea_kb_id'] );
		if ( is_wp_error( $kb_id ) ) {
			ASEA_Utilities::ajax_show_error_die( __( 'Invalid parameter. Please refresh your page', 'echo-knowledge-base' ) );
		}

		$result = ASEA_Utilities::save_wp_option( 'asea_search_audit', '', true );
		if ( is_wp_error( $result ) ) {
			ASEA_Logging::add_log( 'Error resetting audit log', $result );
			ASEA_Utilities::ajax_show_error_die( __( 'Error occurred. Please try again later.', 'echo-advanced-search' ) );
		}

		ASEA_Utilities::ajax_show_info_die( esc_html__( 'Debug Log was cleared', 'echo-advanced-search' ) );
	}

	/**
	 * Get configuration array for Search view on KB Configuration page
	 *
	 * @param $views_config
	 * @param $kb_config
	 *
	 * @return mixed
	 */
	public function search_config_view( $views_config, $kb_config ) {

		$views_config[] = array(

			// Shared
			'list_key' => 'asea-search',

			// Top Panel Item
			'label_text' => __( 'Search', 'echo-knowledge-base' ),
			'icon_class' => 'epkbfa epkbfa-search',

			// Boxes List
			'list_top_actions_html' => self::get_search_view_top_actions_row_html( $kb_config ),
			'boxes_list' => array(

				// Box: Info
				array(
					'title' => __( 'Info', 'echo-knowledge-base' ),
					'html' => self::get_search_view_info_box_html( $kb_config ),
				),
			),
		);
		return $views_config;
	}

	/**
	 * Get HTML for actions row in Search view
	 *
	 * @param $kb_config
	 *
	 * @return false|string
	 */
	private static function get_search_view_top_actions_row_html( $kb_config ) {

		ob_start();     ?>

		<div class="epkb-admin__list-actions-row">
			<a class="epkb-primary-btn" id="asea_audit_download" href="#" download="audit.txt"><?php esc_html_e( 'Download file', 'echo-advanced-search' ); ?></a>
			<button class="epkb-error-btn" id="asea_audit_clear" data-nonce="<?php echo wp_create_nonce( "_wpnonce_asea_config" ); ?>" data-kb_id="<?php echo $kb_config['id']; ?>"><?php esc_html_e( 'Clear Log', 'echo-advanced-search' ); ?></button>
		</div>      <?php

		return ob_get_clean();
	}

	/**
	 * Get HTML for Info box in Search view
	 *
	 * @param $kb_config
	 *
	 * @return false|string
	 */
	private static function get_search_view_info_box_html( $kb_config ) {

		ob_start();     ?>

		<div class="asea-config-container">

			<div id="kb_<?php echo $kb_config['id']; ?>" data-kb_id="<?php echo $kb_config['id']; ?>">     <?php

				$current_audit_text = ASEA_Utilities::get_wp_option( 'asea_search_audit', '', false, true );
				if ( is_wp_error( $current_audit_text ) ) {
					ASEA_Logging::add_log( 'Error retrieving audit' );
					$current_audit_text = '';
				}		?>

				<div class="asea-debug-form asea-config-form">
					<textarea id="asea_audit_textarea" disabled rows="100"><?php echo $current_audit_text; ?></textarea>
				</div>

			</div>

		</div>      <?php

		return ob_get_clean();
	}
}