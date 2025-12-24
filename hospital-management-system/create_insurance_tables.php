<?php
/**
 * Create Insurance Tables
 */

// Include WordPress
require_once '../../../wp-config.php';
require_once ABSPATH . 'wp-includes/wp-db.php';

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_prefix = $wpdb->prefix;

echo "Creating/Updating Insurance Tables...\n\n";

// Insurance Providers Table
$table_name = $table_prefix . 'hms_insurance_providers';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    provider_id varchar(50) UNIQUE DEFAULT NULL,
    contact_person varchar(100) DEFAULT NULL,
    phone varchar(20) DEFAULT NULL,
    email varchar(100) DEFAULT NULL,
    address text DEFAULT NULL,
    website varchar(255) DEFAULT NULL,
    status varchar(20) DEFAULT 'active',
    notes text DEFAULT NULL,
    tpa_name varchar(255) DEFAULT NULL,
    api_details text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY name (name),
    KEY provider_id (provider_id),
    KEY status (status)
) $charset_collate;";

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
dbDelta($sql);

echo "[SUCCESS] Insurance providers table created/updated\n";

// Insurance Claims Table
$table_name = $table_prefix . 'hms_claims';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    patient_id mediumint(9) NOT NULL,
    bill_id mediumint(9) NOT NULL,
    insurance_provider_id mediumint(9) NOT NULL,
    policy_number varchar(100) DEFAULT NULL,
    claim_amount decimal(10,2) NOT NULL,
    status varchar(50) DEFAULT 'pending_preauth',
    preauth_request_id varchar(100) DEFAULT NULL,
    preauth_status varchar(50) DEFAULT 'pending',
    notes text DEFAULT NULL,
    initiated_by mediumint(9) NOT NULL,
    initiated_at datetime DEFAULT CURRENT_TIMESTAMP,
    settled_amount decimal(10,2) DEFAULT NULL,
    settled_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY patient_id (patient_id),
    KEY bill_id (bill_id),
    KEY insurance_provider_id (insurance_provider_id),
    KEY status (status),
    KEY initiated_by (initiated_by)
) $charset_collate;";

dbDelta($sql);

echo "[SUCCESS] Insurance claims table created/updated\n";

// Check if audit log table exists (needed for HMS_Helper::log_action)
$audit_table = $table_prefix . 'hms_audit_log';
$audit_exists = $wpdb->get_var("SHOW TABLES LIKE '$audit_table'");

if (!$audit_exists) {
    $sql = "CREATE TABLE IF NOT EXISTS $audit_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) DEFAULT NULL,
        action varchar(255) NOT NULL,
        object_type varchar(100) DEFAULT NULL,
        object_id mediumint(9) DEFAULT NULL,
        details text DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY action (action),
        KEY object_type (object_type),
        KEY object_id (object_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    dbDelta($sql);
    echo "[SUCCESS] Audit log table created\n";
} else {
    echo "[INFO] Audit log table already exists\n";
}

echo "\nTable creation completed.\n";
?>