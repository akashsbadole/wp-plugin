<?php
/**
 * Admin template for exporting HMS data.
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if the current user has the required capability
if (!current_user_can('manage_reports')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

// Handle export form submission
if (isset($_POST['hms_export_nonce']) && wp_verify_nonce($_POST['hms_export_nonce'], 'hms_export_data')) {
    $tables_to_export = isset($_POST['hms_export_tables']) ? $_POST['hms_export_tables'] : array();
    $export_format = sanitize_text_field($_POST['hms_export_format']);

    if (!empty($tables_to_export)) {
        if (count($tables_to_export) == 1) {
            // Single table export
            hms_export_table_data($tables_to_export[0], $export_format);
        } else {
            // Multiple tables export as ZIP
            hms_export_multiple_tables($tables_to_export, $export_format);
        }
        exit; // Stop execution after sending the file
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Please select at least one table to export.', 'hospital-management-system') . '</p></div>';
    }
}

/**
 * Function to export table data to CSV or Excel (CSV format for Excel).
 *
 * @param string $table_name The table name without the WordPress prefix.
 * @param string $format The export format ('csv' or 'excel').
 */
function hms_export_table_data($table_name, $format = 'csv') {
    global $wpdb;
    $full_table_name = $wpdb->prefix . $table_name;

    // Validate table name to prevent SQL injection
    $allowed_tables = hms_get_allowed_export_tables();

    if (!in_array($table_name, array_keys($allowed_tables))) {
        wp_die(__('Invalid table selected for export.', 'hospital-management-system'));
    }

    // Fetch column names
    $columns = $wpdb->get_col("DESCRIBE {$full_table_name}", 0);

    if (empty($columns)) {
        wp_die(__('Could not retrieve table columns.', 'hospital-management-system'));
    }

    // Fetch all rows from the table
    $rows = $wpdb->get_results("SELECT * FROM {$full_table_name}", ARRAY_A);

    if ($wpdb->last_error) {
        wp_die(__('Database error: ', 'hospital-management-system') . $wpdb->last_error);
    }

    $filename = sanitize_file_name($table_name . '_export_' . gmdate('Y-m-d') . '.csv');
    $filename = ($format === 'excel') ? str_replace('.csv', '.xlsx', $filename) : $filename; // Note: True Excel .xlsx requires a library like PhpSpreadsheet. We'll output CSV for simplicity.

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM to fix Excel encoding issues
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add column headers
    fputcsv($output, $columns);

    // Add data rows
    if (!empty($rows)) {
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
}

/**
 * Function to export multiple tables as a ZIP file.
 *
 * @param array $table_names Array of table names without the WordPress prefix.
 * @param string $format The export format ('csv' or 'excel').
 */
function hms_export_multiple_tables($table_names, $format = 'csv') {
    if (!class_exists('ZipArchive')) {
        wp_die(__('ZipArchive class is not available. Cannot create ZIP file for multiple table export.', 'hospital-management-system'));
    }

    $allowed_tables = hms_get_allowed_export_tables();
    $zip = new ZipArchive();
    $zip_filename = 'hms_export_' . gmdate('Y-m-d_H-i-s') . '.zip';
    $zip_path = sys_get_temp_dir() . '/' . $zip_filename;

    if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
        wp_die(__('Cannot create ZIP file for export.', 'hospital-management-system'));
    }

    global $wpdb;

    foreach ($table_names as $table_name) {
        $table_name = sanitize_text_field($table_name);
        
        if (!in_array($table_name, array_keys($allowed_tables))) {
            continue; // Skip invalid tables
        }

        $full_table_name = $wpdb->prefix . $table_name;
        
        // Fetch column names
        $columns = $wpdb->get_col("DESCRIBE {$full_table_name}", 0);
        if (empty($columns)) continue;

        // Fetch all rows from the table
        $rows = $wpdb->get_results("SELECT * FROM {$full_table_name}", ARRAY_A);
        if ($wpdb->last_error) continue;

        // Create CSV content
        $csv_content = '';
        
        // Add UTF-8 BOM
        $csv_content .= chr(0xEF).chr(0xBB).chr(0xBF);
        
        // Add column headers
        $csv_content .= implode(',', array_map(function($col) {
            return '"' . str_replace('"', '""', $col) . '"';
        }, $columns)) . "\n";

        // Add data rows
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $csv_content .= implode(',', array_map(function($val) {
                    return '"' . str_replace('"', '""', $val) . '"';
                }, $row)) . "\n";
            }
        }

        $csv_filename = $table_name . '_export_' . gmdate('Y-m-d') . '.csv';
        $zip->addFromString($csv_filename, $csv_content);
    }

    $zip->close();

    // Send the ZIP file
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename=' . $zip_filename);
    header('Content-Length: ' . filesize($zip_path));
    readfile($zip_path);
    unlink($zip_path); // Clean up temp file
}

/**
 * Get allowed tables for export.
 *
 * @return array
 */
function hms_get_allowed_export_tables() {
    return array(
        'hms_patients'           => __('Patients', 'hospital-management-system'),
        'hms_appointments'       => __('Appointments', 'hospital-management-system'),
        'hms_medical_records'    => __('Medical Records', 'hospital-management-system'),
        'hms_bills'              => __('Bills', 'hospital-management-system'),
        'hms_bill_items'         => __('Bill Items', 'hospital-management-system'),
        'hms_payments'           => __('Payments', 'hospital-management-system'),
        'hms_prescriptions'      => __('Prescriptions', 'hospital-management-system'),
        'hms_prescription_items' => __('Prescription Items', 'hospital-management-system'),
        'hms_medicines'          => __('Medicines', 'hospital-management-system'),
        'hms_pharmacy_stock'     => __('Pharmacy Stock', 'hospital-management-system'),
        'hms_pharmacy_dispenses' => __('Pharmacy Dispenses', 'hospital-management-system'),
        'hms_staff'              => __('Staff', 'hospital-management-system'),
        'hms_departments'        => __('Departments', 'hospital-management-system'),
        'hms_services'           => __('Services', 'hospital-management-system'),
        'hms_insurance_providers' => __('Insurance Providers', 'hospital-management-system'),
        'hms_claims'             => __('Insurance Claims', 'hospital-management-system'),
        'hms_queue'              => __('Patient Queue', 'hospital-management-system'),
        'hms_lab_tests'          => __('Lab Tests', 'hospital-management-system'),
        'hms_lab_test_orders'    => __('Lab Test Orders', 'hospital-management-system'),
        'hms_audit_log'          => __('Audit Log', 'hospital-management-system'),
        'hms_purchase_orders'    => __('Purchase Orders', 'hospital-management-system'),
    );
}

// Get a list of HMS tables for the checkboxes
$hms_tables_for_export = hms_get_allowed_export_tables();
?>

<div class="wrap">
    <h1><?php esc_html_e('Export HMS Data', 'hospital-management-system'); ?></h1>
    <p><?php esc_html_e('Select one or more tables and format to export data. Multiple tables will be exported as a ZIP file.', 'hospital-management-system'); ?></p>

    <form method="post" action="">
        <?php wp_nonce_field('hms_export_data', 'hms_export_nonce'); ?>
        
        <div class="hms-export-form" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
            
            <h2><?php esc_html_e('Select Tables to Export', 'hospital-management-system'); ?></h2>
            <p class="description"><?php esc_html_e('Check the tables you want to export. You can select multiple tables.', 'hospital-management-system'); ?></p>
            
            <div class="hms-table-selection" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
                
                <div class="hms-table-group">
                    <h3><?php esc_html_e('Patient Management', 'hospital-management-system'); ?></h3>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_patients"> <?php echo esc_html($hms_tables_for_export['hms_patients']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_appointments"> <?php echo esc_html($hms_tables_for_export['hms_appointments']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_medical_records"> <?php echo esc_html($hms_tables_for_export['hms_medical_records']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_queue"> <?php echo esc_html($hms_tables_for_export['hms_queue']); ?></label>
                </div>
                
                <div class="hms-table-group">
                    <h3><?php esc_html_e('Billing & Finance', 'hospital-management-system'); ?></h3>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_bills"> <?php echo esc_html($hms_tables_for_export['hms_bills']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_bill_items"> <?php echo esc_html($hms_tables_for_export['hms_bill_items']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_payments"> <?php echo esc_html($hms_tables_for_export['hms_payments']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_insurance_providers"> <?php echo esc_html($hms_tables_for_export['hms_insurance_providers']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_claims"> <?php echo esc_html($hms_tables_for_export['hms_claims']); ?></label>
                </div>
                
                <div class="hms-table-group">
                    <h3><?php esc_html_e('Clinical & Pharmacy', 'hospital-management-system'); ?></h3>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_prescriptions"> <?php echo esc_html($hms_tables_for_export['hms_prescriptions']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_prescription_items"> <?php echo esc_html($hms_tables_for_export['hms_prescription_items']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_medicines"> <?php echo esc_html($hms_tables_for_export['hms_medicines']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_pharmacy_stock"> <?php echo esc_html($hms_tables_for_export['hms_pharmacy_stock']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_pharmacy_dispenses"> <?php echo esc_html($hms_tables_for_export['hms_pharmacy_dispenses']); ?></label>
                </div>
                
                <div class="hms-table-group">
                    <h3><?php esc_html_e('Lab & Administration', 'hospital-management-system'); ?></h3>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_lab_tests"> <?php echo esc_html($hms_tables_for_export['hms_lab_tests']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_lab_test_orders"> <?php echo esc_html($hms_tables_for_export['hms_lab_test_orders']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_staff"> <?php echo esc_html($hms_tables_for_export['hms_staff']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_departments"> <?php echo esc_html($hms_tables_for_export['hms_departments']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_services"> <?php echo esc_html($hms_tables_for_export['hms_services']); ?></label>
                </div>
                
                <div class="hms-table-group">
                    <h3><?php esc_html_e('System & Audit', 'hospital-management-system'); ?></h3>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_purchase_orders"> <?php echo esc_html($hms_tables_for_export['hms_purchase_orders']); ?></label><br>
                    <label><input type="checkbox" name="hms_export_tables[]" value="hms_audit_log"> <?php echo esc_html($hms_tables_for_export['hms_audit_log']); ?></label>
                </div>
                
            </div>
            
            <div class="hms-bulk-actions" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px;">
                <button type="button" id="select-all-tables" class="button"><?php esc_html_e('Select All', 'hospital-management-system'); ?></button>
                <button type="button" id="deselect-all-tables" class="button"><?php esc_html_e('Deselect All', 'hospital-management-system'); ?></button>
                <button type="button" id="select-patient-tables" class="button"><?php esc_html_e('Select Patient Data', 'hospital-management-system'); ?></button>
                <button type="button" id="select-billing-tables" class="button"><?php esc_html_e('Select Billing Data', 'hospital-management-system'); ?></button>
            </div>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="hms_export_format"><?php esc_html_e('Export Format', 'hospital-management-system'); ?></label>
                    </th>
                    <td>
                        <select name="hms_export_format" id="hms_export_format" class="regular-text">
                            <option value="csv" selected><?php esc_html_e('CSV (Comma Separated Values)', 'hospital-management-system'); ?></option>
                            <option value="excel"><?php esc_html_e('Excel (CSV Format for Excel)', 'hospital-management-system'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Note: Multiple tables will be exported as a ZIP file containing individual CSV files.', 'hospital-management-system'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Export Selected Data', 'hospital-management-system'), 'primary', 'hms_export_submit'); ?>
            
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Select all tables
    $('#select-all-tables').click(function() {
        $('input[name="hms_export_tables[]"]').prop('checked', true);
    });
    
    // Deselect all tables
    $('#deselect-all-tables').click(function() {
        $('input[name="hms_export_tables[]"]').prop('checked', false);
    });
    
    // Select patient-related tables
    $('#select-patient-tables').click(function() {
        $('input[name="hms_export_tables[]"]').prop('checked', false);
        $('input[value="hms_patients"]').prop('checked', true);
        $('input[value="hms_appointments"]').prop('checked', true);
        $('input[value="hms_medical_records"]').prop('checked', true);
        $('input[value="hms_queue"]').prop('checked', true);
    });
    
    // Select billing-related tables
    $('#select-billing-tables').click(function() {
        $('input[name="hms_export_tables[]"]').prop('checked', false);
        $('input[value="hms_bills"]').prop('checked', true);
        $('input[value="hms_bill_items"]').prop('checked', true);
        $('input[value="hms_payments"]').prop('checked', true);
        $('input[value="hms_insurance_providers"]').prop('checked', true);
        $('input[value="hms_claims"]').prop('checked', true);
    });
});
</script>

<style>
.hms-table-group {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #2271b1;
}

.hms-table-group h3 {
    margin: 0 0 10px 0;
    color: #2271b1;
    font-size: 14px;
    font-weight: 600;
}

.hms-table-group label {
    display: block;
    margin: 5px 0;
    font-size: 13px;
}

.hms-table-group input[type="checkbox"] {
    margin-right: 8px;
}
</style>
