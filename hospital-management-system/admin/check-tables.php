<?php
/**
 * Database Tables Check Script
 *
 * This script provides a comprehensive overview of the database tables
 * used by the Hospital Management System plugin.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if user has permission
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

?>

<div class="wrap">
    <h1><?php esc_html_e('Database Tables Status', 'hospital-management-system'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php esc_html_e('This page shows the status of all database tables used by the Hospital Management System plugin.', 'hospital-management-system'); ?></p>
    </div>

    <?php
    global $wpdb;
    
    // Define all required tables with their descriptions
    $required_tables = array(
        'hms_patients' => array(
            'name' => 'Patients',
            'description' => 'Stores patient information including personal details, contact information, and medical record numbers.',
            'key_columns' => array('id', 'patient_id', 'first_name', 'last_name', 'email', 'phone', 'created_at')
        ),
        'hms_appointments' => array(
            'name' => 'Appointments',
            'description' => 'Manages patient appointments with doctors, including scheduling and status tracking.',
            'key_columns' => array('id', 'patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'status')
        ),
        'hms_staff' => array(
            'name' => 'Staff',
            'description' => 'Contains information about hospital staff including doctors, nurses, and administrative personnel.',
            'key_columns' => array('id', 'staff_id', 'first_name', 'last_name', 'role', 'department', 'email')
        ),
        'hms_bills' => array(
            'name' => 'Bills',
            'description' => 'Tracks billing information for patient services, treatments, and procedures.',
            'key_columns' => array('id', 'bill_id', 'patient_id', 'total_amount', 'due_amount', 'status', 'created_at')
        ),
        'hms_prescriptions' => array(
            'name' => 'Prescriptions',
            'description' => 'Stores prescription information including medications, dosages, and instructions.',
            'key_columns' => array('id', 'patient_id', 'doctor_id', 'medicine_name', 'dosage', 'instructions', 'created_at')
        ),
        'hms_medicines' => array(
            'name' => 'Medicines',
            'description' => 'Medicine inventory management including stock levels, pricing, and supplier information.',
            'key_columns' => array('id', 'name', 'generic_name', 'manufacturer', 'price', 'stock_quantity', 'expiry_date')
        ),
        'hms_pharmacy_dispense' => array(
            'name' => 'Pharmacy Dispense',
            'description' => 'Records of medicines dispensed to patients from the pharmacy.',
            'key_columns' => array('id', 'patient_id', 'medicine_id', 'quantity', 'total_price', 'dispensed_at')
        ),
        'hms_insurance_providers' => array(
            'name' => 'Insurance Providers',
            'description' => 'Information about insurance companies and their coverage details.',
            'key_columns' => array('id', 'name', 'contact_person', 'phone', 'email', 'coverage_percentage')
        ),
        'hms_claims' => array(
            'name' => 'Insurance Claims',
            'description' => 'Tracks insurance claims processing and settlement status.',
            'key_columns' => array('id', 'patient_id', 'insurance_provider_id', 'claim_amount', 'status', 'initiated_at')
        ),
        'hms_lab_tests' => array(
            'name' => 'Lab Tests',
            'description' => 'Laboratory test orders and basic information.',
            'key_columns' => array('id', 'patient_id', 'test_name', 'test_type', 'status', 'ordered_at')
        ),
        'hms_lab_results' => array(
            'name' => 'Lab Results',
            'description' => 'Laboratory test results and reports.',
            'key_columns' => array('id', 'lab_test_id', 'result_value', 'normal_range', 'status', 'completed_at')
        )
    );
    
    $table_status = array();
    $total_records = 0;
    
    foreach ($required_tables as $table_name => $table_info) {
        $full_table_name = $wpdb->prefix . $table_name;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
        
        $status = array(
            'exists' => $table_exists,
            'info' => $table_info
        );
        
        if ($table_exists) {
            // Get table structure
            $columns = $wpdb->get_results("DESCRIBE $full_table_name");
            $status['columns'] = $columns;
            
            // Get record count
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name");
            $status['record_count'] = (int) $count;
            $total_records += (int) $count;
            
            // Get table size
            $size_query = $wpdb->prepare(
                "SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                FROM information_schema.TABLES 
                WHERE table_schema = %s 
                AND table_name = %s",
                DB_NAME,
                $full_table_name
            );
            $size_result = $wpdb->get_var($size_query);
            $status['size_mb'] = $size_result ? (float) $size_result : 0;
        }
        
        $table_status[$table_name] = $status;
    }
    
    // Calculate summary statistics
    $existing_tables = array_filter($table_status, function($status) { return $status['exists']; });
    $missing_tables = array_filter($table_status, function($status) { return !$status['exists']; });
    ?>
    
    <!-- Summary Cards -->
    <div class="summary-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="summary-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo count($existing_tables); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Tables Existing', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="summary-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #d63638;"><?php echo count($missing_tables); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Tables Missing', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="summary-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #0073aa;"><?php echo number_format($total_records); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Total Records', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="summary-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #f56e28;"><?php echo count($required_tables); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Required Tables', 'hospital-management-system'); ?></p>
        </div>
    </div>
    
    <!-- Detailed Table Information -->
    <div class="table-details">
        <h2><?php esc_html_e('Detailed Table Information', 'hospital-management-system'); ?></h2>
        
        <?php foreach ($table_status as $table_name => $status): ?>
            <div class="table-info-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px; overflow: hidden;">
                <div class="table-header" style="padding: 15px 20px; background: <?php echo $status['exists'] ? '#f0f8f0' : '#fdf2f2'; ?>; border-bottom: 1px solid #ccd0d4;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="margin: 0; color: #23282d;">
                                <span class="dashicons <?php echo $status['exists'] ? 'dashicons-yes-alt' : 'dashicons-dismiss'; ?>" 
                                      style="color: <?php echo $status['exists'] ? '#00a32a' : '#d63638'; ?>; margin-right: 8px;"></span>
                                <?php echo esc_html($status['info']['name']); ?>
                                <code style="font-size: 12px; color: #666; margin-left: 10px;"><?php echo esc_html($wpdb->prefix . $table_name); ?></code>
                            </h3>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
                                <?php echo esc_html($status['info']['description']); ?>
                            </p>
                        </div>
                        
                        <?php if ($status['exists']): ?>
                            <div style="text-align: right;">
                                <div style="font-size: 18px; font-weight: bold; color: #0073aa;">
                                    <?php echo number_format($status['record_count']); ?>
                                </div>
                                <div style="font-size: 12px; color: #666;">
                                    <?php esc_html_e('records', 'hospital-management-system'); ?>
                                    <?php if ($status['size_mb'] > 0): ?>
                                        | <?php echo esc_html($status['size_mb']); ?> MB
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="color: #d63638; font-weight: bold;">
                                <?php esc_html_e('MISSING', 'hospital-management-system'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="table-content" style="padding: 20px;">
                    <?php if ($status['exists']): ?>
                        <h4 style="margin: 0 0 10px 0;"><?php esc_html_e('Table Structure', 'hospital-management-system'); ?></h4>
                        <div style="overflow-x: auto;">
                            <table class="wp-list-table widefat fixed striped" style="margin: 0;">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Column', 'hospital-management-system'); ?></th>
                                        <th><?php esc_html_e('Type', 'hospital-management-system'); ?></th>
                                        <th><?php esc_html_e('Null', 'hospital-management-system'); ?></th>
                                        <th><?php esc_html_e('Key', 'hospital-management-system'); ?></th>
                                        <th><?php esc_html_e('Default', 'hospital-management-system'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($status['columns'] as $column): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($column->Field); ?></strong></td>
                                            <td><code><?php echo esc_html($column->Type); ?></code></td>
                                            <td><?php echo esc_html($column->Null); ?></td>
                                            <td><?php echo esc_html($column->Key); ?></td>
                                            <td><?php echo esc_html($column->Default ?: 'NULL'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="padding: 20px; background: #fdf2f2; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
                            <h4 style="color: #d63638; margin: 0 0 10px 0;"><?php esc_html_e('Table Missing', 'hospital-management-system'); ?></h4>
                            <p style="margin: 0 0 15px 0; color: #666;">
                                <?php esc_html_e('This table is required for the plugin to function properly.', 'hospital-management-system'); ?>
                            </p>
                            <a href="<?php echo admin_url('admin.php?page=hms_fix_tables'); ?>" class="button button-primary">
                                <?php esc_html_e('Fix Missing Tables', 'hospital-management-system'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <h5 style="margin: 0 0 8px 0; color: #666;"><?php esc_html_e('Key Columns:', 'hospital-management-system'); ?></h5>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ($status['info']['key_columns'] as $column): ?>
                                <span style="background: #f0f0f0; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-family: monospace;">
                                    <?php echo esc_html($column); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (!empty($missing_tables)): ?>
        <div class="missing-tables-notice" style="background: #fdf2f2; border: 1px solid #f5c6cb; border-radius: 4px; padding: 20px; margin-top: 20px;">
            <h3 style="color: #d63638; margin: 0 0 15px 0;">
                <span class="dashicons dashicons-warning" style="margin-right: 8px;"></span>
                <?php esc_html_e('Action Required', 'hospital-management-system'); ?>
            </h3>
            <p><?php esc_html_e('Some required database tables are missing. This may cause the plugin to malfunction.', 'hospital-management-system'); ?></p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=hms_fix_tables'); ?>" class="button button-primary">
                    <?php esc_html_e('Fix Missing Tables Now', 'hospital-management-system'); ?>
                </a>
            </p>
        </div>
    <?php else: ?>
        <div class="all-tables-ok" style="background: #f0f8f0; border: 1px solid #c3e6c3; border-radius: 4px; padding: 20px; margin-top: 20px; text-align: center;">
            <h3 style="color: #00a32a; margin: 0 0 10px 0;">
                <span class="dashicons dashicons-yes-alt" style="margin-right: 8px;"></span>
                <?php esc_html_e('All Tables Present', 'hospital-management-system'); ?>
            </h3>
            <p style="margin: 0;"><?php esc_html_e('All required database tables are present and ready for use.', 'hospital-management-system'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.summary-cards {
    margin: 20px 0;
}

.summary-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.table-info-card {
    transition: box-shadow 0.2s ease;
}

.table-info-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.wp-list-table th,
.wp-list-table td {
    padding: 8px 12px;
}

.wp-list-table code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 2px;
    font-size: 11px;
}

@media (max-width: 768px) {
    .summary-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .table-header > div {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
}
</style>