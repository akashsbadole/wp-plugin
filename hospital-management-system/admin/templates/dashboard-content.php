<?php
/**
 * Dashboard Content Template
 * 
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Key Metrics Row -->
<?php
$metrics = array(
    array(
        'value' => $today_appointments_count,
        'label' => __("Today's Appointments", 'hospital-management-system'),
        'color' => '#2271b1'
    ),
    array(
        'value' => $new_patients_count,
        'label' => __('New Patients Today', 'hospital-management-system'),
        'color' => '#00a32a'
    ),
    array(
        'value' => '₹' . number_format_i18n($today_revenue, 2),
        'label' => __("Today's Revenue", 'hospital-management-system'),
        'color' => '#dba617'
    ),
    array(
        'value' => $queue_count,
        'label' => __('Patients in Queue', 'hospital-management-system'),
        'color' => '#d63638'
    )
);
HMS_UI_Framework::render_metric_cards($metrics);
?>

<!-- System Status Section -->
<?php
// Check system status
$missing_tables = array();
if (class_exists('HMS_Activator')) {
    $missing_tables = HMS_Activator::check_required_tables();
}
$db_version = get_option('hms_db_version', 'Not Set');

if (!empty($missing_tables) || $db_version === 'Not Set') :
?>
<div class="hms-notice hms-notice-warning hms-notice-dismissible">
    <p><strong><?php _e('System Status Alert:', 'hospital-management-system'); ?></strong></p>
    <?php if (!empty($missing_tables)) : ?>
        <p><?php printf(__('Missing %d database tables. ', 'hospital-management-system'), count($missing_tables)); ?></p>
    <?php endif; ?>
    <?php if ($db_version === 'Not Set') : ?>
        <p><?php _e('Database version not set. ', 'hospital-management-system'); ?></p>
    <?php endif; ?>
    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_database_status')); ?>" class="hms-btn hms-btn-primary"><?php _e('Check Database Status', 'hospital-management-system'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_fix_tables')); ?>" class="hms-btn hms-btn-secondary hms-ml-2"><?php _e('Fix Issues', 'hospital-management-system'); ?></a>
    </p>
</div>
<?php endif; ?>

<div class="hms-row">
    <!-- Column 1 -->
    <div class="hms-col-4">
        <!-- Quick Links -->
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Quick Links', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <div class="hms-row">
                    <div class="hms-col-6 hms-mb-2">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_patient_registration')); ?>" class="hms-btn hms-btn-primary hms-w-100">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Register Patient', 'hospital-management-system'); ?>
                        </a>
                    </div>
                    <div class="hms-col-6 hms-mb-2">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_appointment_details')); ?>" class="hms-btn hms-btn-success hms-w-100">
                            <span class="dashicons dashicons-calendar-plus"></span>
                            <?php _e('Schedule Appointment', 'hospital-management-system'); ?>
                        </a>
                    </div>
                    <div class="hms-col-6 hms-mb-2">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_bill_details')); ?>" class="hms-btn hms-btn-warning hms-w-100">
                            <span class="dashicons dashicons-media-default"></span>
                            <?php _e('Generate Bill', 'hospital-management-system'); ?>
                        </a>
                    </div>
                    <div class="hms-col-6 hms-mb-2">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_queue')); ?>" class="hms-btn hms-btn-info hms-w-100">
                            <span class="dashicons dashicons-list-view"></span>
                            <?php _e('Manage Queue', 'hospital-management-system'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Recent Activity', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <ul class="hms-activity-list">
                    <?php if (!empty($recent_activities)) : ?>
                        <?php foreach ($recent_activities as $activity) : ?>
                            <li class="hms-d-flex hms-justify-between hms-align-center hms-mb-2">
                                <span class="hms-activity-description"><?php echo esc_html(isset($activity->description) ? $activity->description : __('Activity', 'hospital-management-system')); ?></span>
                                <span class="hms-activity-time hms-text-muted">
                                    <?php 
                                    $timestamp = isset($activity->timestamp) ? $activity->timestamp : current_time('mysql');
                                    echo esc_html(human_time_diff(strtotime($timestamp), current_time('timestamp'))); 
                                    ?> <?php _e('ago', 'hospital-management-system'); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="hms-text-center hms-text-muted"><?php _e('No recent activity.', 'hospital-management-system'); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Column 2 -->
    <div class="hms-col-4">
        <!-- Upcoming Appointments -->
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e("Today's Upcoming Appointments", 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <div class="hms-table-responsive">
                    <table class="hms-table hms-table-striped">
                        <thead>
                            <tr>
                                <th><?php _e('Time', 'hospital-management-system'); ?></th>
                                <th><?php _e('Patient', 'hospital-management-system'); ?></th>
                                <th><?php _e('Doctor', 'hospital-management-system'); ?></th>
                                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcoming_appointments)) : ?>
                                <?php foreach ($upcoming_appointments as $appointment) :
                                    $patient_id = get_post_meta($appointment->ID, '_patient_id', true);
                                    $doctor_id = get_post_meta($appointment->ID, '_doctor_id', true);
                                    $appointment_time = get_post_meta($appointment->ID, '_appointment_time', true);
                                    $status = get_post_meta($appointment->ID, '_status', true);

                                    $patient = get_userdata($patient_id);
                                    $doctor = get_userdata($doctor_id);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html(gmdate('H:i', strtotime($appointment_time))); ?></td>
                                        <td><?php echo $patient ? esc_html($patient->display_name) : __('N/A', 'hospital-management-system'); ?></td>
                                        <td><?php echo $doctor ? esc_html($doctor->display_name) : __('N/A', 'hospital-management-system'); ?></td>
                                        <td><span class="hms-badge hms-badge-<?php echo esc_attr(strtolower($status)); ?>"><?php echo esc_html(ucfirst($status)); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="hms-text-center hms-text-muted"><?php _e('No upcoming appointments for today.', 'hospital-management-system'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="hms-card-footer hms-text-right">
                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_appointments')); ?>" class="hms-btn hms-btn-secondary"><?php _e('View All Appointments', 'hospital-management-system'); ?></a>
            </div>
        </div>
    </div>

    <!-- Column 3 -->
    <div class="hms-col-4">
        <!-- Revenue Chart -->
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Weekly Revenue', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <canvas id="hms-revenue-chart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <!-- Appointments Chart -->
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Weekly Appointments', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <canvas id="hms-appointments-chart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Enqueue Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    jQuery(document).ready(function($) {
        // Revenue Chart
        const revenueCtx = document.getElementById('hms-revenue-chart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($revenue_chart_data['labels']); ?>,
                    datasets: [{
                        label: '<?php _e('Revenue (₹)', 'hospital-management-system'); ?>',
                        data: <?php echo json_encode($revenue_chart_data['data']); ?>,
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Appointments Chart
        const appointmentCtx = document.getElementById('hms-appointments-chart');
        if (appointmentCtx) {
            new Chart(appointmentCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($appointment_chart_data['labels']); ?>,
                    datasets: [{
                        label: '<?php _e('Appointments', 'hospital-management-system'); ?>',
                        data: <?php echo json_encode($appointment_chart_data['data']); ?>,
                        backgroundColor: '#00a32a',
                        borderColor: '#00a32a',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>