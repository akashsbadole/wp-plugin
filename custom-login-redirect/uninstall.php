<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Drop custom tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_redirect_rules");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_user_logins");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_ab_tests");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_ab_variants");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_time_redirects");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_device_redirects");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_referral_redirects");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_analytics_events");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_conditional_redirects");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_notification_history");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_page_visits");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}clr_custom_reports");

// Delete options
delete_option('clr_version');
delete_option('clr_ai_enabled');
delete_option('clr_geo_enabled');
delete_option('clr_ab_enabled');
delete_option('clr_page_builder_enabled');
delete_option('clr_time_based_enabled');
delete_option('clr_device_based_enabled');
delete_option('clr_referral_based_enabled');
delete_option('clr_analytics_enabled');
delete_option('clr_conditional_enabled');
delete_option('clr_email_notifications_enabled');
delete_option('clr_notification_events');
delete_option('clr_notification_email');
delete_option('clr_email_template');
delete_option('clr_email_custom_template');

// Delete premium feature options
delete_option('clr_ga_connected');
delete_option('clr_ga_tracking_id');
delete_option('clr_mailchimp_connected');
delete_option('clr_mailchimp_api_key');
delete_option('clr_mailchimp_list_id');
delete_option('clr_slack_connected');
delete_option('clr_slack_webhook_url');

// Delete user meta
 $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'clr_%'");