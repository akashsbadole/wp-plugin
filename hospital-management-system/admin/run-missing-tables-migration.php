<?php
/**
 * Missing Tables Migration Script
 *
 * This script checks for and creates any missing database tables
 * for the Hospital Management System plugin.
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
    <h1><?php esc_html_e('Fix Missing Tables', 'hospital-management-system'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php esc_html_e('This tool will check for and create any missing database tables required by the Hospital Management System.', 'hospital-management-system'); ?></p>
    </div>

    <?php
    // Handle form submission
    if (isset($_POST['run_migration']) && wp_verify_nonce($_POST['migration_nonce'], 'hms_run_migration')) {
        
        echo '<div class="notice notice-info"><p>' . esc_html__('Starting migration process...', 'hospital-management-system') . '</p></div>';
        
        // Include the activator class to run table creation
        require_once HMS_PLUGIN_DIR . 'includes/class-hms-activator.php';
        
        try {
            // Run the table creation process
            HMS_Activator::create_tables();
            
            echo '<div class="notice notice-success"><p>' . esc_html__('Migration completed successfully! All required tables have been checked and created if missing.', 'hospital-management-system') . '</p></div>';
            
            // Log the migration
            error_log('HMS: Missing tables migration completed successfully');
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(
                     /* translators: %s: error message */
                     esc_html__('Migration failed with error: %s', 'hospital-management-system'),
                     esc_html($e->getMessage())
                 ) . 
                 '</p></div>';
            
            // Log the error
            error_log('HMS: Missing tables migration failed: ' . $e->getMessage());
        }
    }
    ?>

    <div class="card">
        <h2><?php esc_html_e('Database Table Status', 'hospital-management-system'); ?></h2>
        
        <?php
        // Check current table status
        global $wpdb;
        
        $required_tables = array(
            'hms_patients' => 'Patients',
            'hms_appointments' => 'Appointments', 
            'hms_staff' => 'Staff',
            'hms_bills' => 'Bills',
            'hms_prescriptions' => 'Prescriptions',
            'hms_medicines' => 'Medicines',
            'hms_pharmacy_dispense' => 'Pharmacy Dispense',
            'hms_insurance_providers' => 'Insurance Providers',
            'hms_claims' => 'Insurance Claims',
            'hms_lab_tests' => 'Lab Tests',
            'hms_lab_results' => 'Lab Results'
        );
        
        $missing_tables = array();
        $existing_tables = array();
        
        foreach ($required_tables as $table_name => $display_name) {
            $full_table_name = $wpdb->prefix . $table_name;
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
            
            if ($table_exists) {
                $existing_tables[] = $display_name;
            } else {
                $missing_tables[] = $display_name;
            }
        }
        ?>
        
        <div class="table-status-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
            <div>
                <h3 style="color: #00a32a;"><?php esc_html_e('Existing Tables', 'hospital-management-system'); ?> (<?php echo count($existing_tables); ?>)</h3>
                <?php if (!empty($existing_tables)): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($existing_tables as $table): ?>
                            <li style="padding: 5px 0; color: #00a32a;">
                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                <?php echo esc_html($table); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?php esc_html_e('No tables found.', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
            
            <div>
                <h3 style="color: #d63638;"><?php esc_html_e('Missing Tables', 'hospital-management-system'); ?> (<?php echo count($missing_tables); ?>)</h3>
                <?php if (!empty($missing_tables)): ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($missing_tables as $table): ?>
                            <li style="padding: 5px 0; color: #d63638;">
                                <span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
                                <?php echo esc_html($table); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #00a32a;"><?php esc_html_e('All required tables exist!', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($missing_tables)): ?>
            <div class="migration-form" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #d63638;">
                <h3><?php esc_html_e('Run Migration', 'hospital-management-system'); ?></h3>
                <p><?php esc_html_e('Click the button below to create the missing database tables. This process is safe and will not affect existing data.', 'hospital-management-system'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('hms_run_migration', 'migration_nonce'); ?>
                    <p>
                        <input type="submit" name="run_migration" class="button button-primary" 
                               value="<?php esc_attr_e('Create Missing Tables', 'hospital-management-system'); ?>" 
                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to run the migration? This will create missing database tables.', 'hospital-management-system'); ?>');">
                    </p>
                </form>
            </div>
        <?php else: ?>
            <div style="margin-top: 30px; padding: 20px; background: #f0f8f0; border-left: 4px solid #00a32a;">
                <h3 style="color: #00a32a;"><?php esc_html_e('All Tables Present', 'hospital-management-system'); ?></h3>
                <p><?php esc_html_e('All required database tables are present. No migration is needed.', 'hospital-management-system'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Additional Information', 'hospital-management-system'); ?></h2>
        <p><?php esc_html_e('This migration tool is designed to be safe and will only create missing tables. It will not modify or delete existing data.', 'hospital-management-system'); ?></p>
        
        <h4><?php esc_html_e('What this migration does:', 'hospital-management-system'); ?></h4>
        <ul>
            <li><?php esc_html_e('Checks for the existence of all required database tables', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('Creates any missing tables with the correct structure', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('Preserves all existing data', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('Logs the migration process for debugging', 'hospital-management-system'); ?></li>
        </ul>
        
        <h4><?php esc_html_e('When to use this tool:', 'hospital-management-system'); ?></h4>
        <ul>
            <li><?php esc_html_e('After plugin installation if tables were not created properly', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('After plugin updates that introduce new tables', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('If you encounter database-related errors in the plugin', 'hospital-management-system'); ?></li>
            <li><?php esc_html_e('When moving the site to a new server or database', 'hospital-management-system'); ?></li>
        </ul>
    </div>
</div>

<style>
.table-status-grid {
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    overflow: hidden;
}

.table-status-grid > div {
    padding: 20px;
    background: #fff;
}

.table-status-grid > div:first-child {
    border-right: 1px solid #ccd0d4;
}

.table-status-grid h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 16px;
}

.table-status-grid ul li {
    display: flex;
    align-items: center;
    gap: 8px;
}

.migration-form {
    border-radius: 4px;
}

.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    color: #23282d;
}

.card h4 {
    margin-top: 20px;
    margin-bottom: 10px;
    color: #23282d;
}

.card ul {
    margin-left: 20px;
}

.card ul li {
    margin-bottom: 5px;
}
</style>