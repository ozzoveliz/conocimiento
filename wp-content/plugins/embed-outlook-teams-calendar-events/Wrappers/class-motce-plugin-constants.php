<?php
/**
 * This includes all plugin constants.
 *
 * @package embed-outlook-teams-calendar-events/Views/Wrappers
 */

namespace MOTCE\Wrappers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to hold all plugin constants.
 */
class MOTCE_Plugin_Constants {

	const HOSTNAME                                  = 'https://login.xecurify.com';
	const NOTICE_MESSAGE                            = 'notice_message';
	const APP_CONFIG                                = 'application_config';
	const MAIL_CONFIG                               = 'mail_config';
	const UPN                                       = 'upn_id';
	const ADMIN_EMAIL                               = 'admin_email';
	const ADMIN_PASSWORD                            = 'admin_password';
	const ADMIN_OBSERVER_NC                         = 'admin_observer';
	const ADMIN_CONTROLLER_NC                       = 'admin_controller';
	const EMBED_CALENDAR_AJAX_NC                    = 'calendar_embed__nonce';
	const APP_CONFIG_KEYS                           = array( 'client_id', 'client_secret', 'tenant_id', 'upn_id' );
	const MAIL_CONFIG_KEYS                          = array( 'mailFrom', 'mailTo', 'saveToSentItems' );
	const INTERNET_CONNECTION_ERROR_MESSAGE         = array(
		'error'             => 'Request timeout',
		'error_description' => 'Unexpected error occurred! Please check your internet connection and try again.',
	);
	const INVALID_PLUGIN_CONFIGURATIONS_MESSAGE     = array(
		'error'             => 'Unexpected Error',
		'error_description' => 'Check your configurations once again',
	);
	const UNAUTHORIZED_OPERATION_MESSAGE            = array(
		'error'             => 'Unauthorized',
		'error_description' => 'Unexpected error occured',
	);
	const INCORRECT_APP_CONFIGURATION_INPUT_MESSAGE = 'Input is empty or present in the incorrect format.';
	const APP_CONFIG_SUCCESS_MESSAGE                = 'Settings Saved Successfully.';
	const MAIL_SENT_SUCCESS_MESSAGE                 = 'Mail Sent Successfully.';
	const MAIL_SENT_ERROR_MESSAGE                   = 'Mail was not sent, please check the emails in the configuration and try again.';
}
