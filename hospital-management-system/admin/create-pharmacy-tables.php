<?php
/**
 * Create Pharmacy Tables Script
 * 
 * This script creates the new pharmacy-related tables for inventory management.
 */

// Include WordPress
require_once('../../../wp-config.php');

// Include the activator class
require_once(dirname(__FILE__) . '/../includes/class-hms-activator.php');

echo "Creating pharmacy inventory tables...\n";

global $wpdb;

$table_prefix = $wpdb->prefix;
$charset_collate = $wpdb->get_charset_collate();

// Create suppliers table
$table_name = $table_prefix . 'hms_suppliers';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    contact_person varchar(255) DEFAULT NULL,
    email varchar(100) DEFAULT NULL,
    phone varchar(20) DEFAULT NULL,
    address text DEFAULT NULL,
    city varchar(100) DEFAULT NULL,
    state varchar(100) DEFAULT NULL,
    postal_code varchar(20) DEFAULT NULL,
    country varchar(100) DEFAULT NULL,
    tax_number varchar(50) DEFAULT NULL,
    payment_terms varchar(100) DEFAULT NULL,
    status varchar(20) DEFAULT 'active',
    notes text DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY name (name),
    KEY status (status)
) $charset_collate;";

$result = $wpdb->query($sql);
if ($result === false) {
    echo "Error creating suppliers table: " . $wpdb->last_error . "\n";
} else {
    echo "✓ Suppliers table created successfully\n";
}

// Create purchase order items table
$table_name = $table_prefix . 'hms_purchase_order_items';
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    purchase_order_id mediumint(9) NOT NULL,
    medicine_id mediumint(9) NOT NULL,
    quantity_ordered int(11) NOT NULL,
    quantity_received int(11) DEFAULT 0,
    unit_price decimal(10,2) NOT NULL DEFAULT 0.00,
    total_price decimal(10,2) NOT NULL DEFAULT 0.00,
    batch_number varchar(100) DEFAULT NULL,
    expiry_date date DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY purchase_order_id (purchase_order_id),
    KEY medicine_id (medicine_id)
) $charset_collate;";

$result = $wpdb->query($sql);
if ($result === false) {
    echo "Error creating purchase order items table: " . $wpdb->last_error . "\n";
} else {
    echo "✓ Purchase order items table created successfully\n";
}

// Update medicines table to add missing columns
$medicines_table = $table_prefix . 'hms_medicines';

// Check if columns exist and add them if they don't
$columns_to_add = array(
    'generic_name' => "ALTER TABLE $medicines_table ADD COLUMN generic_name varchar(255) DEFAULT NULL AFTER name",
    'stock_quantity' => "ALTER TABLE $medicines_table ADD COLUMN stock_quantity int(11) DEFAULT 0 AFTER supplier_id",
    'unit_price' => "ALTER TABLE $medicines_table ADD COLUMN unit_price decimal(10,2) DEFAULT 0.00 AFTER reorder_level",
    'selling_price' => "ALTER TABLE $medicines_table ADD COLUMN selling_price decimal(10,2) DEFAULT 0.00 AFTER unit_price",
    'batch_number' => "ALTER TABLE $medicines_table ADD COLUMN batch_number varchar(100) DEFAULT NULL AFTER selling_price",
    'expiry_date' => "ALTER TABLE $medicines_table ADD COLUMN expiry_date date DEFAULT NULL AFTER batch_number",
    'status' => "ALTER TABLE $medicines_table ADD COLUMN status varchar(20) DEFAULT 'active' AFTER expiry_date"
);

foreach ($columns_to_add as $column => $sql) {
    // Check if column exists
    $column_exists = $wpdb->get_results($wpdb->prepare(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
        DB_NAME, $medicines_table, $column
    ));
    
    if (empty($column_exists)) {
        $result = $wpdb->query($sql);
        if ($result === false) {
            echo "Error adding $column column: " . $wpdb->last_error . "\n";
        } else {
            echo "✓ Added $column column to medicines table\n";
        }
    } else {
        echo "✓ Column $column already exists in medicines table\n";
    }
}

// Add some sample suppliers
$suppliers_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_prefix}hms_suppliers");
if ($suppliers_count == 0) {
    $sample_suppliers = array(
        array(
            'name' => 'MediCorp Pharmaceuticals',
            'contact_person' => 'John Smith',
            'email' => 'john@medicorp.com',
            'phone' => '+1-555-0123',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'payment_terms' => 'Net 30',
            'status' => 'active'
        ),
        array(
            'name' => 'Global Health Supplies',
            'contact_person' => 'Sarah Johnson',
            'email' => 'sarah@globalhealthsupplies.com',
            'phone' => '+1-555-0456',
            'city' => 'Chicago',
            'state' => 'IL',
            'country' => 'USA',
            'payment_terms' => 'Net 15',
            'status' => 'active'
        ),
        array(
            'name' => 'PharmaTech Solutions',
            'contact_person' => 'Michael Brown',
            'email' => 'michael@pharmatech.com',
            'phone' => '+1-555-0789',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'country' => 'USA',
            'payment_terms' => 'COD',
            'status' => 'active'
        )
    );
    
    foreach ($sample_suppliers as $supplier) {
        $wpdb->insert(
            $table_prefix . 'hms_suppliers',
            $supplier,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    echo "✓ Added sample suppliers\n";
} else {
    echo "✓ Suppliers already exist\n";
}

echo "\nPharmacy inventory tables setup completed!\n";
echo "You can now access the new inventory management features in the WordPress admin.\n";
?>