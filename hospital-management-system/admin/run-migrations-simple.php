<?php
/**
 * Simple Migration Runner for HMS
 * 
 * This file provides a simple interface to run database migrations
 * for the Hospital Management System plugin.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if the migration class exists
if (!class_exists('HMS_Migration')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-migration.php';
}

// Handle migration execution if form is submitted
if (isset($_POST['run_migrations']) && check_admin_referer('hms_run_migrations_nonce')) {
    $messages = HMS_Migration::run_migrations();
    
    echo '<div class="wrap">';
    echo '<h1>' . __('Migration Results', 'hospital-management-system') . '</h1>';
    
    if (!empty($messages)) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>' . __('Migration completed successfully!', 'hospital-management-system') . '</strong></p>';
        echo '<ul>';
        foreach ($messages as $message) {
            echo '<li>' . esc_html($message) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p>' . __('No migrations were needed.', 'hospital-management-system') . '</p>';
        echo '</div>';
    }
    
    echo '<p><a href="' . admin_url('admin.php?page=hms_dashboard') . '" class="button button-primary">' . __('Return to Dashboard', 'hospital-management-system') . '</a></p>';
    echo '</div>';
    
} else {
    // Display the migration form
    ?>
    <div class="wrap">
        <h1><?php _e('Run Database Migrations', 'hospital-management-system'); ?></h1>
        
        <div class="notice notice-info">
            <p><strong><?php _e('Important:', 'hospital-management-system'); ?></strong></p>
            <p><?php _e('This tool will update your database schema to the latest version. It is safe to run multiple times as it will only apply changes that haven\'t been made yet.', 'hospital-management-system'); ?></p>
            <p><?php _e('Before running migrations, we recommend backing up your database.', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="card">
            <h2><?php _e('Migration Information', 'hospital-management-system'); ?></h2>
            <p><?php _e('The following migrations will be checked and applied if needed:', 'hospital-management-system'); ?></p>
            <ul>
                <li><?php _e('Add appointment_time column to appointments table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add issue_date and due_date columns to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add issue_date and prescription_number columns to prescriptions table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add bill_id, tax_amount, paid_amount, and payment_method columns to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add payment_status and status columns to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add insurance_provider_id column to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add insurance_covered column to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add notes column to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add tax_percentage and discount_amount columns to bills table', 'hospital-management-system'); ?></li>
                <li><?php _e('Add price column to bill_items table', 'hospital-management-system'); ?></li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('hms_run_migrations_nonce'); ?>
            <p>
                <input type="submit" name="run_migrations" class="button button-primary" value="<?php _e('Run Migrations Now', 'hospital-management-system'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hms_dashboard'); ?>" class="button"><?php _e('Cancel', 'hospital-management-system'); ?></a>
            </p>
        </form>
    </div>
    <?php
}
?>
