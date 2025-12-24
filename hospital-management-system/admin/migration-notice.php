<?php
/**
 * Migration Notice for Hospital Management System
 * 
 * This file handles displaying migration notices to administrators
 * when database schema updates are needed.
 *
 * @package Hospital_Management_System
 * @author Your Name
 * @license GPL-2.0+
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class HMS_Migration_Notice
 * 
 * Handles the display of migration notices in the admin area.
 */
class HMS_Migration_Notice {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'display_migration_notice'));
    }
    
    /**
     * Display migration notice if needed
     */
    public function display_migration_notice() {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if migration is needed
        $current_db_version = get_option('hms_db_version', '0');
        $plugin_version = HMS_VERSION;
        
        if (version_compare($current_db_version, $plugin_version, '<')) {
            $this->show_migration_needed_notice($current_db_version, $plugin_version);
        }
    }
    
    /**
     * Show the migration needed notice
     * 
     * @param string $current_db_version Current database version
     * @param string $plugin_version Current plugin version
     */
    private function show_migration_needed_notice($current_db_version, $plugin_version) {
        $migration_url = admin_url('admin.php?page=hms_run_migrations');
        
        ?>
        <div class="notice notice-warning is-dismissible">
            <h3><?php _e('Hospital Management System - Database Update Required', 'hospital-management-system'); ?></h3>
            <p>
                <?php 
                printf(
                    __('The Hospital Management System plugin has been updated to version %s, but your database is at version %s. A database update is required to ensure compatibility.', 'hospital-management-system'),
                    '<strong>' . esc_html($plugin_version) . '</strong>',
                    '<strong>' . esc_html($current_db_version) . '</strong>'
                );
                ?>
            </p>
            <p>
                <a href="<?php echo esc_url($migration_url); ?>" class="button button-primary">
                    <?php _e('Run Database Update', 'hospital-management-system'); ?>
                </a>
                <span class="description">
                    <?php _e('It is recommended to backup your database before running the update.', 'hospital-management-system'); ?>
                </span>
            </p>
        </div>
        <?php
    }
}

// Initialize the migration notice class
new HMS_Migration_Notice();