<?php
/**
 * Plugin utilities.
 *
 * @package miniOrange_LDAP_AD_Integration
 * @subpackage utils
 */

namespace MO_LDAP\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MO_LDAP_Local_Addon_List_Content' ) ) {
	/**
	 * MO_LDAP_Local_Addon_List_Content : Class to store the details of addons.
	 */
	class MO_LDAP_Local_Addon_List_Content {

		/**
		 * __construct
		 *
		 * @return void
		 */
		public function __construct() {
			define(
				'MO_LDAP_RECOMMENDED_ADDONS',
				maybe_serialize(
					array(

						'DIRECTORY_SYNC'               => array(
							'addonName'        => 'Sync Users LDAP Directory',
							'addonDescription' => 'Synchronize WordPress users with LDAP directory and vice versa. Schedules can be configured for the synchronization to run at a specific time and after a specific interval.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-configure-miniorange-directory-sync-add-on-for-wordpress',
							'addonVideo'       => 'https://www.youtube.com/embed/DqRtOauJjY8',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/sync_users.png',
						),
						'KERBEROS_NTLM'                => array(
							'addonName'        => 'Auto Login (SSO) using Kerberos/NTLM',
							'addonDescription' => 'Enable Auto-login (SSO) for your WordPress website on a domain joined machine.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-kerberos-single-sign-sso',
							'addonVideo'       => 'https://www.youtube.com/embed/JCVWurFle9I',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/auto_login.png',
						),
						'PASSWORD_SYNC'                => array(
							'addonName'        => 'Password Sync with LDAP Server',
							'addonDescription' => 'Synchronize your WordPress profile password with your LDAP user profile.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-password-sync-with-ldap-add-on',
							'addonVideo'       => 'https://www.youtube.com/embed/6XGUvlvjeUQ',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/password_sync.png',
						),
						'PROFILE_PICTURE_SYNC'         => array(
							'addonName'        => 'Profile Picture Sync',
							'addonDescription' => 'Update WordPress user profile picture with the thumbnail photo stored in your Active Directory/ LDAP server.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/configure-miniorange-profile-picture-map-add-on-for-wordpress',
							'addonVideo'       => 'https://www.youtube.com/embed/RL_TJ48kV5w',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/profile_picture.png',
						),
						'LDAP_SEARCH_WIDGET'           => array(
							'addonName'        => 'Search Staff from LDAP Directory',
							'addonDescription' => 'Search/display your directory users on your website using search widget and shortcode.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-miniorange-ldap-search-widget-add-on',
							'addonVideo'       => 'https://www.youtube.com/embed/GEw6dOx7hRo',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/search_staff.png',
						),
						'PAGE_POST_RESTRICTION'        => array(
							'addonName'        => 'Page/Post Restriction',
							'addonDescription' => 'Allows you to control access to your site\'s content (pages/posts) based on LDAP groups/WordPress roles.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-page-restriction',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/page_restriction.png',
						),
						'USER_META'                    => array(
							'addonName'        => 'Third Party Plugin User Profile Integration',
							'addonDescription' => 'Update profile information of any third-party plugin with information from LDAP Directory.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-third-party-user-profile-integration-with-ldap-add-on',
							'addonVideo'       => 'https://www.youtube.com/embed/KLKKe4tEiWI',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/third_party.png',
						),
						'CUSTOM_NOTIFICATION_WP_LOGIN' => array(
							'addonName'        => 'Custom Notifications on WordPress Login page',
							'addonDescription' => 'Add/Display customized messages on your WordPress login page.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/custom_notifications.png',
						),
					)
				)
			);

			define(
				'MO_LDAP_THIRD_PARTY_INTEGRATION_ADDONS',
				maybe_serialize(
					array(

						'BUDDYPRESS_PROFILE_SYNC'        => array(
							'addonName'        => 'BuddyPress Profile Integration',
							'addonDescription' => 'Sync your BuddyPress extended user profiles with the attributes present in your Active Directory/LDAP Server. You can also assign users to BuddyPress groups based on their groups memberships in Active Directory',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-miniorange-ldap-buddypress-integration-add-on',
							'addonVideo'       => 'https://www.youtube.com/embed/7itUoIINyTw',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/BuddyPress.png',
						),
						'BUDDYBOSS_PROFILE_INTEGRATION'  => array(
							'addonName'        => 'BuddyBoss Profile Integration',
							'addonDescription' => 'Integration with BuddyBoss to sync extended profile of users with LDAP attributes upon login.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/BuddyPress.png',
						),
						'ULTIMATE_MEMBER_PROFILE_INTEGRATION' => array(
							'addonName'        => 'Ultimate Member Add-On',
							'addonDescription' => 'Using LDAP credentials, login to Ultimate Member and integrate your Ultimate Member User Profile with LDAP attributes.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/guide-to-setup-ultimate-member-login-integration-with-ldap-credentials',
							'addonVideo'       => 'https://www.youtube.com/embed/-d2B_0rDFi0',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/ultimate_member.png',
						),
						'PAID_MEMBERSHIP_PRO_INTEGRATOR' => array(
							'addonName'        => 'Paid Membership Pro Integrator',
							'addonDescription' => 'WordPress Paid Memberships Pro Integrator will map the LDAP Security Groups to Paid Memberships Pro groups.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/paidmembership.png',
						),
						'LDAP_WP_GROUPS_INTEGRATION'     => array(
							'addonName'        => 'WP Groups Plugin Integration',
							'addonDescription' => 'Assign LDAP users to WordPress groups based on their group membership in LDAP Server.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/groups.png',
						),
						'GRAVITY_FORMS_INTEGRATION'      => array(
							'addonName'        => 'Gravity Forms Integration',
							'addonDescription' => 'Populate Gravity Form fields with information from LDAP. You can integrate with unlimited forms.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/gravity_forms.png',
						),
						'MEMBERPRESS_INTEGRATION'        => array(
							'addonName'        => 'MemberPress Plugin Integration',
							'addonDescription' => 'Login to MemberPress protected content with LDAP Credentials.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/memberpress.png',
						),
						'LEARNDASH_ADDON'                => array(
							'addonName'        => 'LearnDash Integration Add-On',
							'addonDescription' => 'Assign users to LearnDash groups based on their groups memberships in Active Directory. You can map any number of LearnDash groups to LDAP/AD groups.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/learndash.png',
						),
						'EMEMBER_INTEGRATION'            => array(
							'addonName'        => 'eMember Plugin Integration',
							'addonDescription' => 'Login to eMember profiles with LDAP Credentials.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/emember.png',
						),
						'WOOCOMMERCE_INTEGRATION'        => array(
							'addonName'        => 'WooCommerce Integration Add-On',
							'addonDescription' => 'Login to your WooCommerce site with LDAP Credentials and integrate your WooCommerce User Profile with LDAP attributes.',
							'addonLicense'     => 'ContactUs',
							'addonGuide'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonVideo'       => 'https://plugins.miniorange.com/wordpress-ldap-login-intranet-sites',
							'addonLogo'        => MO_LDAP_LOCAL_IMAGES . 'addon-images/woocommerce.png',
						),
					)
				)
			);

		}
	}
}
