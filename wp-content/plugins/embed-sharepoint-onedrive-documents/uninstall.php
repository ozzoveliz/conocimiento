<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ))
    exit();

delete_option('mo_sps_application_config');
delete_option('mo_sps_cloud_connector');
delete_option('mo_sps_auth_code');
delete_option('mo_sps_notice_message');
delete_option('mo_sps_feedback_config');
delete_option('mo_sps_all_sites');
delete_option('mo_sps_plugin_migration_completed');
delete_option('mo_sps_test_connection_user_details');
delete_option('mo_sps_refresh_token');
delete_option('mo_sps_test_connection_status');