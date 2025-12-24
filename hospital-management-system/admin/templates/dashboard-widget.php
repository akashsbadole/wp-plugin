<?php
/**
 * Dashboard Widget Template for Hospital Management System
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$today = gmdate('Y-m-d');

// Get lab statistics
$lab_radiology = new HMS_Lab_Radiology();
$lab_stats = $lab_radiology->get_lab_statistics();
$lab_activities = $lab_radiology->get_recent_activities(3);

// Fetch data for the widget
// NOTE: These are placeholder functions. You will need to implement them.
$today_appointments_count = 5; // Placeholder: HMS_Reports::get_appointments_count($today);
$new_patients_count = 3; // Placeholder: HMS_Reports::get_new_patients_count($today);
$today_revenue = 15000.00; // Placeholder: HMS_Billing::get_today_revenue();
$queue_count = 10; // Placeholder: HMS_Helper::get_queue_count($today);
?>

<div class="hms-dashboard-widget">
    <h3><?php _e('Hospital Management System Overview', 'hospital-management-system'); ?></h3>
    
    <div class="hms-widget-summary">
        <div class="hms-widget-summary-item">
            <span class="hms-widget-label"><?php _e("Today's Appts", 'hospital-management-system'); ?>:</span>
            <span class="hms-widget-value"><?php echo esc_html($today_appointments_count); ?></span>
        </div>
        <div class="hms-widget-summary-item">
            <span class="hms-widget-label"><?php _e('New Patients', 'hospital-management-system'); ?>:</span>
            <span class="hms-widget-value"><?php echo esc_html($new_patients_count); ?></span>
        </div>
        <div class="hms-widget-summary-item">
            <span class="hms-widget-label"><?php _e('Pending Tests', 'hospital-management-system'); ?>:</span>
            <span class="hms-widget-value hms-pending-tests"><?php echo esc_html($lab_stats['pending'] + $lab_stats['in_progress']); ?></span>
        </div>
        <div class="hms-widget-summary-item">
            <span class="hms-widget-label"><?php _e('Queue Length', 'hospital-management-system'); ?>:</span>
            <span class="hms-widget-value"><?php echo esc_html($queue_count); ?></span>
        </div>
    </div>
    
    <div class="hms-widget-lab-summary">
        <h4><?php _e('Lab Tests Overview', 'hospital-management-system'); ?></h4>
        <div class="hms-lab-stats">
            <div class="hms-lab-stat-item">
                <span class="hms-lab-stat-number"><?php echo esc_html($lab_stats['pending']); ?></span>
                <span class="hms-lab-stat-label"><?php _e('Pending', 'hospital-management-system'); ?></span>
            </div>
            <div class="hms-lab-stat-item">
                <span class="hms-lab-stat-number"><?php echo esc_html($lab_stats['in_progress']); ?></span>
                <span class="hms-lab-stat-label"><?php _e('In Progress', 'hospital-management-system'); ?></span>
            </div>
            <div class="hms-lab-stat-item">
                <span class="hms-lab-stat-number"><?php echo esc_html($lab_stats['completed']); ?></span>
                <span class="hms-lab-stat-label"><?php _e('Completed', 'hospital-management-system'); ?></span>
            </div>
            <div class="hms-lab-stat-item">
                <span class="hms-lab-stat-number"><?php echo esc_html($lab_stats['today_orders']); ?></span>
                <span class="hms-lab-stat-label"><?php _e('Today', 'hospital-management-system'); ?></span>
            </div>
        </div>
    </div>

    <div class="hms-widget-activities">
        <h4><?php _e('Recent Lab Activities', 'hospital-management-system'); ?></h4>
        <ul class="hms-activity-list-widget">
            <?php if (!empty($lab_activities)) : ?>
                <?php foreach ($lab_activities as $activity) : ?>
                    <li>
                        <span class="hms-activity-time"><?php echo esc_html(human_time_diff(strtotime($activity->updated_at), current_time('timestamp'))); ?> <?php _e('ago', 'hospital-management-system'); ?></span>
                        <span class="hms-activity-description">
                            <?php 
                            $patient_name = trim($activity->patient_first_name . ' ' . $activity->patient_last_name);
                            printf(
                                /* translators: 1: Test name, 2: Patient name, 3: Status */
                                __('%1$s for %2$s - %3$s', 'hospital-management-system'),
                                esc_html($activity->test_name),
                                esc_html($patient_name),
                                esc_html(ucwords(str_replace('-', ' ', $activity->status)))
                            );
                            ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li><?php _e('No recent lab activity.', 'hospital-management-system'); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="hms-widget-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_lab_tests')); ?>" class="button button-primary"><?php _e('View Lab Tests', 'hospital-management-system'); ?></a>
        <?php if ($lab_stats['pending'] + $lab_stats['in_progress'] > 0): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_lab_results')); ?>" class="button button-secondary"><?php _e('Enter Results', 'hospital-management-system'); ?></a>
        <?php endif; ?>
    </div>
</div>
