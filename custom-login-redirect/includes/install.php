<?php
/**
 * Install database tables for the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create database tables
 */
function clr_install_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for basic redirect rules
    $table_name = $wpdb->prefix . 'clr_redirect_rules';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        role varchar(50) NOT NULL,
        redirect_url text NOT NULL,
        priority int(11) NOT NULL DEFAULT 10,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Table for A/B testing variants
    $table_name = $wpdb->prefix . 'clr_ab_variants';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        role varchar(50) NOT NULL,
        redirect_url text NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for A/B testing results
    $table_name = $wpdb->prefix . 'clr_ab_tests';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        variant_id mediumint(9) NOT NULL,
        user_id bigint(20) NOT NULL,
        converted tinyint(1) NOT NULL DEFAULT 0,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        KEY variant_id (variant_id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for time-based redirects
    $table_name = $wpdb->prefix . 'clr_time_redirects';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        day_of_week varchar(20) NOT NULL,
        start_hour int(11) NOT NULL,
        end_hour int(11) NOT NULL,
        redirect_url text NOT NULL,
        priority int(11) NOT NULL DEFAULT 10,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for device-based redirects
    $table_name = $wpdb->prefix . 'clr_device_redirects';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        device_type varchar(20) NOT NULL,
        device_os varchar(20) NOT NULL,
        browser varchar(20) NOT NULL,
        redirect_url text NOT NULL,
        priority int(11) NOT NULL DEFAULT 10,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for referral-based redirects
    $table_name = $wpdb->prefix . 'clr_referral_redirects';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        source varchar(100) NOT NULL,
        medium varchar(100) NOT NULL,
        campaign varchar(100) NOT NULL,
        redirect_url text NOT NULL,
        priority int(11) NOT NULL DEFAULT 10,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for analytics data
    $table_name = $wpdb->prefix . 'clr_analytics_events';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        session_id varchar(100) NOT NULL,
        event_type varchar(50) NOT NULL,
        event_data text NOT NULL,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        ip_address varchar(45) NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY session_id (session_id),
        KEY event_type (event_type),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for user login tracking
    $table_name = $wpdb->prefix . 'clr_user_logins';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        role varchar(50) NOT NULL,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        ip varchar(45) NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for conditional redirects
    $table_name = $wpdb->prefix . 'clr_conditional_redirects';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        description text NOT NULL,
        conditions text NOT NULL,
        redirect_url text NOT NULL,
        priority int(11) NOT NULL DEFAULT 10,
        status varchar(20) NOT NULL DEFAULT 'active',
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for email notification history
    $table_name = $wpdb->prefix . 'clr_notification_history';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_type varchar(50) NOT NULL,
        user_id bigint(20) NOT NULL,
        user_login varchar(100) NOT NULL,
        recipient varchar(255) NOT NULL,
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'sent',
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        KEY event_type (event_type),
        KEY user_id (user_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for page visits tracking
    $table_name = $wpdb->prefix . 'clr_page_visits';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        post_id bigint(20) NOT NULL,
        visit_count int(11) NOT NULL DEFAULT 1,
        last_visit datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY post_id (post_id)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    // Table for custom reports
    $table_name = $wpdb->prefix . 'clr_custom_reports';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        description text NOT NULL,
        config text NOT NULL,
        created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    dbDelta($sql);
}

/**
 * Install the plugin
 */
function clr_install() {
    // Create database tables
    clr_install_tables();
    
    // Set default options
    add_option('clr_version', CLR_VERSION);
    add_option('clr_ai_enabled', 0);
    add_option('clr_geo_enabled', 0);
    add_option('clr_ab_enabled', 0);
    add_option('clr_page_builder_enabled', 0);
    add_option('clr_time_based_enabled', 0);
    add_option('clr_device_based_enabled', 0);
    add_option('clr_referral_based_enabled', 0);
    add_option('clr_analytics_enabled', 0);
    add_option('clr_conditional_enabled', 0);
    add_option('clr_email_notifications_enabled', 0);
    add_option('clr_notification_events', array());
    add_option('clr_notification_email', '');
    add_option('clr_email_template', 'default');
    add_option('clr_email_custom_template', '');
    
    // Set default options for premium features
    add_option('clr_ga_connected', 0);
    add_option('clr_ga_tracking_id', '');
    add_option('clr_mailchimp_connected', 0);
    add_option('clr_mailchimp_api_key', '');
    add_option('clr_mailchimp_list_id', '');
    add_option('clr_slack_connected', 0);
    add_option('clr_slack_webhook_url', '');
}

/**
 * Create a specific table if it doesn't exist
 *
 * @param string $table_name The table name without prefix
 */
function clr_create_table_if_not_exists($table_name) {
    global $wpdb;
    
    $full_table_name = $wpdb->prefix . $table_name;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
    
    if ($table_exists != $full_table_name) {
        // Create the specific table based on its name
        switch ($table_name) {
            case 'clr_analytics_events':
                $sql = "CREATE TABLE $full_table_name (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) NOT NULL,
                    session_id varchar(100) NOT NULL,
                    event_type varchar(50) NOT NULL,
                    event_data text NOT NULL,
                    timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    ip_address varchar(45) NOT NULL,
                    PRIMARY KEY  (id),
                    KEY user_id (user_id),
                    KEY session_id (session_id),
                    KEY event_type (event_type),
                    KEY timestamp (timestamp)
                ) $charset_collate;";
                break;
                
            default:
                return false; // Table not supported
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return true;
    }
    
    return false;
}

/**
 * Fix missing analytics table
 */
function clr_fix_missing_tables() {
    // Create the missing analytics events table
    $created = clr_create_table_if_not_exists('clr_analytics_events');
    
    if ($created) {
        // Update version to indicate fix was applied
        update_option('clr_tables_fixed', '1.0.1');
    }
    
    return $created;
}