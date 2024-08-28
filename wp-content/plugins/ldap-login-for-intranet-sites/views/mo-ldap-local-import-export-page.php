<?php
/**
 * Display Import Export Configuration
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage views
 */

?>
<div class="mo_ldap_local_imp_exp_outer">

	<div class="mo_ldap_local_outer">
		<div class="mo_ldap_local_imp_exp_headings">Export Configuration</div>
		<form method="post" action="" id="mo_ldap_local_save_config" name="mo_ldap_local_save_config">
			<?php wp_nonce_field( 'enable_config' ); ?>
			<input type="hidden" name="option" value="enable_config" />
			<div>
				<input type="checkbox" id="enable_save_config" name="enable_save_config" class="mo_ldap_local_toggle_switch_hide" value="1" onchange="this.form.submit()" <?php checked( esc_attr( strcasecmp( get_option( 'en_save_config' ), '1' ) === 0 ) ); ?> />
				<label for="enable_save_config" class="mo_ldap_local_toggle_switch"></label>
				<label for="enable_save_config" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">
					Keep configuration upon deactivation
				</label>
			</div>
		</form>
		<br>
		<form method="post" action="" name="mo_export_pass" id="mo_export_pass">
			<?php wp_nonce_field( 'mo_ldap_pass' ); ?>
			<input type="hidden" name="option" value="mo_ldap_pass" />
			<div>
				<input type="checkbox" id="enable_ldap_login" name="enable_ldap_login" class="mo_ldap_local_toggle_switch_hide" value="1" onchange="this.form.submit()" <?php checked( esc_attr( strcasecmp( get_option( 'mo_ldap_export' ), '1' ) === 0 ) ); ?> />
				<label for="enable_ldap_login" class="mo_ldap_local_toggle_switch"></label>
				<label for="enable_ldap_login" class="mo_ldap_local_d_inline mo_ldap_local_bold_label">
					Export Service Account password
				</label>
			</div>
		</form>

		<div class="mo_ldap_test_authentication_heading">
			<svg width="18" height="18" viewBox="0 0 26 26" fill="none">
				<g clip-path="url(#clip0_95_2082)">
					<path d="M13 26C20.1797 26 26 20.1797 26 13C26 5.8203 20.1797 0 13 0C5.8203 0 0 5.8203 0 13C0 20.1797 5.8203 26 13 26Z" fill="#b9b9b9"/>
					<path d="M12 12C12 11.4477 12.4477 11 13 11V11C13.5523 11 14 11.4477 14 12V19C14 19.5523 13.5523 20 13 20V20C12.4477 20 12 19.5523 12 19V12Z" fill="white"/>
					<ellipse cx="13" cy="8" rx="1" ry="1" transform="rotate(-180 13 8)" fill="white"/>
				</g>
				<defs>
					<clipPath id="clip0_95_2082">
						<rect width="26" height="26" fill="white"/>
					</clipPath>
				</defs>
			</svg>
			<div class="mo_ldap_local_note_inner">
				This will lead to your service account password to be exported in encrypted fashion in a file.
				<br>
				Enable this only when server password is needed.
			</div>
		</div>
		<br>
		<form method="post" action="" name="mo_export">
			<?php wp_nonce_field( 'mo_ldap_export' ); ?>
			<input type="hidden" name="option" value="mo_ldap_export"/>
			<input type="button" class="mo_ldap_save_user_mapping" onclick="document.forms['mo_export'].submit();" value= "Export configuration" />
		</form>
	</div>

	<div class="mo_ldap_local_outer mo_ldap_local_premium_box">
		<div style="top: 20%; height: 75%; right: 0;" class="mo_ldap_local_premium_role_mapping_banner mo_ldap_d_none">
			<div><h1>Premium Plan</h1></div>
			<div style="font-size: 16px;">This is available in premium version of the plugin</div>
			<div class="">
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_upgrade_now1 mo_ldap_local_unset_link_affect">
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'arrow.svg' ); ?>" height="10px" width="20px"></span> Upgrade Today
				</a>
			</div>
		</div>

		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'pricing' ), $filtered_current_page_url ) ); ?>" class="mo_ldap_local_unset_link_affect">
			<div class="mo_ldap_local_premium_feature_btn">
				<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'crown.svg' ); ?>" height="20px" width="20px"></span> Premium Feature
			</div>
		</a>

		<div class="mo_ldap_local_premium_feature_box">
			<div class="mo_ldap_local_imp_exp_headings">
				Import Configuration
			</div>
			<div class="mo_ldap_test_authentication_heading">
				<svg width="18" height="18" viewBox="0 0 26 26" fill="none">
					<g clip-path="url(#clip0_95_2082)">
						<path d="M13 26C20.1797 26 26 20.1797 26 13C26 5.8203 20.1797 0 13 0C5.8203 0 0 5.8203 0 13C0 20.1797 5.8203 26 13 26Z" fill="#b9b9b9"/>
						<path d="M12 12C12 11.4477 12.4477 11 13 11V11C13.5523 11 14 11.4477 14 12V19C14 19.5523 13.5523 20 13 20V20C12.4477 20 12 19.5523 12 19V12Z" fill="white"/>
						<ellipse cx="13" cy="8" rx="1" ry="1" transform="rotate(-180 13 8)" fill="white"/>
					</g>
					<defs>
						<clipPath id="clip0_95_2082">
							<rect width="26" height="26" fill="white"/>
						</clipPath>
					</defs>
				</svg>
				<div class="mo_ldap_local_note_inner">
					This feature will allow you to import your plugin configuration from a previously exported JSON file.
				</div>
			</div>

			<div class="mo_ldap_local_import_file_disabled">
				<div>
					<span><img src="<?php echo esc_url( MO_LDAP_LOCAL_IMAGES . 'export.svg' ); ?>" height="20px" width="20px"></span>
				</div>
				<br>
				<div>
					Drag & Drop or Choose file to upload
				</div>
			</div>

			<button class="mo_ldap_local_disabled_button">
				Import Configuration
			</button>
		</div>
	</div>
</div>
