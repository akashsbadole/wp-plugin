<?php
/**
 * Admin template for managing the patient queue.
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if the current user has the required capability (e.g., Doctor, Admin, Nurse)
if (!current_user_can('manage_clinical')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

// Get the current doctor's ID. In a real scenario, this would be linked to the logged-in user's staff profile.
// For now, we'll assume it's passed via a GET parameter or a setting.
$current_doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : get_current_user_id();

// Fetch all doctors for the dropdown, if the current user is an admin or has the capability
$all_doctors = array();
if (current_user_can('manage_hms')) {
    global $wpdb;
    $all_doctors = $wpdb->get_results("SELECT id, first_name, last_name FROM {$wpdb->prefix}hms_staff WHERE role = 'doctor' AND status = 'active' ORDER BY first_name, last_name");
} else {
    // If not an admin, the user can only view their own queue
    $staff_id = HMS_Helper::hms_get_var('hms_staff', 'id', array('user_id' => get_current_user_id()), array('%d'));
    if ($staff_id) {
        $current_doctor_id = $staff_id; // Assuming staff_id is the same as doctor_id in hms_staff table
    }
}

// Initialize queue manager trait - we'll use a temporary class to access the trait methods
$queue_manager = new class {
    use HMS_Queue_Manager;
};

// Get today's queue for the selected doctor using the trait method
$queue_date = gmdate('Y-m-d');
$today_queue = $queue_manager->get_current_queue($current_doctor_id);

// Get the currently active token (in-progress)
$active_token = HMS_Helper::get_active_token($current_doctor_id);

// Get the next patient in the queue
$next_in_queue = $queue_manager->get_next_in_queue($current_doctor_id);

?>

<div class="wrap">
    <h1><?php esc_html_e('Patient Queue Management', 'hospital-management-system'); ?></h1>

    <?php if (!empty($all_doctors)) : ?>
    <form method="get" action="">
        <input type="hidden" name="page" value="hms_queue">
        <label for="doctor_id_select"><?php esc_html_e('Select Doctor:', 'hospital-management-system'); ?></label>
        <select name="doctor_id" id="doctor_id_select">
            <?php foreach ($all_doctors as $doctor) : ?>
                <option value="<?php echo esc_attr($doctor->id); ?>" <?php selected($current_doctor_id, $doctor->id); ?>>
                    <?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php submit_button(__('View Queue', 'hospital-management-system'), 'secondary', 'view_queue_submit', false); ?>
    </form>
    <hr>
    <?php endif; ?>

    <!-- Notification Area -->
    <div id="hms-notifications" style="margin: 20px 0;"></div>

    <div id="hms-queue-container">
        <h2><?php
        /* translators: 1: Doctor name, 2: Current date */
        global $wpdb;
        $doctor_name = $wpdb->get_var($wpdb->prepare("SELECT CONCAT(first_name, ' ', last_name) FROM {$wpdb->prefix}hms_staff WHERE id = %d", $current_doctor_id));
        if (!$doctor_name) {
            $doctor_name = __('Unknown Doctor', 'hospital-management-system');
        }
        echo sprintf(__('Queue for %1$s - %2$s', 'hospital-management-system'), esc_html($doctor_name), date_i18n(get_option('date_format')));
        ?></h2>

        <!-- Queue Statistics Section -->
        <div class="hms-queue-stats" style="display: flex; gap: 20px; margin: 20px 0;">
            <?php
            $waiting_count = 0;
            $in_progress_count = 0;
            $completed_count = 0;
            $total_today = count($today_queue);
            
            foreach ($today_queue as $entry) {
                switch ($entry->status) {
                    case 'waiting':
                        $waiting_count++;
                        break;
                    case 'in-progress':
                        $in_progress_count++;
                        break;
                    case 'completed':
                        $completed_count++;
                        break;
                }
            }
            
            $average_wait_time = $waiting_count * 15; // 15 minutes per patient
            ?>
            <div class="hms-stat-card" style="background: #fff3cd; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;">
                <h4 style="margin: 0; color: #856404;"><?php esc_html_e('Waiting', 'hospital-management-system'); ?></h4>
                <div style="font-size: 24px; font-weight: bold; color: #856404;"><?php echo $waiting_count; ?></div>
            </div>
            <div class="hms-stat-card" style="background: #d1ecf1; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;">
                <h4 style="margin: 0; color: #0c5460;"><?php esc_html_e('In Progress', 'hospital-management-system'); ?></h4>
                <div style="font-size: 24px; font-weight: bold; color: #0c5460;"><?php echo $in_progress_count; ?></div>
            </div>
            <div class="hms-stat-card" style="background: #d4edda; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;">
                <h4 style="margin: 0; color: #155724;"><?php esc_html_e('Completed', 'hospital-management-system'); ?></h4>
                <div style="font-size: 24px; font-weight: bold; color: #155724;"><?php echo $completed_count; ?></div>
            </div>
            <div class="hms-stat-card" style="background: #e2e3e5; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;">
                <h4 style="margin: 0; color: #383d41;"><?php esc_html_e('Total Today', 'hospital-management-system'); ?></h4>
                <div style="font-size: 24px; font-weight: bold; color: #383d41;"><?php echo $total_today; ?></div>
            </div>
            <div class="hms-stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; min-width: 140px;">
                <h4 style="margin: 0; color: #495057;"><?php esc_html_e('Avg. Wait Time', 'hospital-management-system'); ?></h4>
                <div style="font-size: 18px; font-weight: bold; color: #495057;">
                    <?php echo $average_wait_time > 0 ? sprintf(__('%d min', 'hospital-management-system'), $average_wait_time) : __('No wait', 'hospital-management-system'); ?>
                </div>
            </div>
        </div>

        <!-- Queue Management Controls -->
        <div class="hms-queue-controls" style="background: #fff; padding: 15px; margin: 20px 0; border: 1px solid #ddd; border-radius: 5px;">
            <h3><?php esc_html_e('Queue Management Controls', 'hospital-management-system'); ?></h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" id="call-all-waiting" class="button button-secondary">
                    <?php esc_html_e('Call All Waiting', 'hospital-management-system'); ?>
                </button>
                <button type="button" id="clear-completed" class="button button-secondary">
                    <?php esc_html_e('Clear Completed', 'hospital-management-system'); ?>
                </button>
                <button type="button" id="reset-queue" class="button button-secondary" style="color: #dc3232;">
                    <?php esc_html_e('Reset Queue', 'hospital-management-system'); ?>
                </button>
                <button type="button" id="export-queue" class="button button-secondary">
                    <?php esc_html_e('Export Today\'s Queue', 'hospital-management-system'); ?>
                </button>
                <div style="margin-left: auto;">
                    <label>
                        <input type="checkbox" id="auto-refresh" checked> 
                        <?php esc_html_e('Auto-refresh (60s)', 'hospital-management-system'); ?>
                    </label>
                </div>
            </div>
        </div>

        <!-- Add Patient to Queue Section -->
        <div class="hms-add-to-queue-section" style="background: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 4px solid #0073aa;">
            <h3><?php esc_html_e('Add Patient to Queue', 'hospital-management-system'); ?></h3>
            <form id="add-to-queue-form" style="display: flex; gap: 10px; align-items: end;">
                <div>
                    <label for="patient_select"><?php esc_html_e('Select Patient:', 'hospital-management-system'); ?></label><br>
                    <select id="patient_select" name="patient_id" style="min-width: 200px;">
                        <option value=""><?php esc_html_e('-- Select Patient --', 'hospital-management-system'); ?></option>
                        <?php
                        global $wpdb;
                        $patients = $wpdb->get_results("SELECT id, first_name, last_name, patient_id FROM {$wpdb->prefix}hms_patients ORDER BY first_name, last_name");
                        foreach ($patients as $patient) {
                            echo '<option value="' . esc_attr($patient->id) . '">' . esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')') . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Add to Queue', 'hospital-management-system'); ?></button>
                </div>
            </form>
        </div>

        <?php if ($active_token) : ?>
        <div class="notice notice-info is-dismissible">
            <h3><?php esc_html_e('Currently With Doctor', 'hospital-management-system'); ?></h3>
            <p>
                <strong><?php esc_html_e('Token:', 'hospital-management-system'); ?></strong> <?php echo esc_html($active_token->token_number); ?><br>
                <strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong> <?php echo esc_html($active_token->first_name . ' ' . $active_token->last_name); ?><br>
                <strong><?php esc_html_e('Called At:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($active_token->called_at))); ?>
            </p>
            <button type="button" class="button button-primary" id="complete-current-btn" data-queue-id="<?php echo esc_attr($active_token->id); ?>">
                <?php esc_html_e('Mark as Completed', 'hospital-management-system'); ?>
            </button>
        </div>
        <?php else : ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('No patient is currently with the doctor.', 'hospital-management-system'); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($next_in_queue) : ?>
        <div class="notice notice-success">
            <h3><?php esc_html_e('Next in Queue', 'hospital-management-system'); ?></h3>
            <p>
                <strong><?php esc_html_e('Token:', 'hospital-management-system'); ?></strong> <?php echo esc_html($next_in_queue->token_number); ?><br>
                <strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong> <?php echo esc_html($next_in_queue->first_name . ' ' . $next_in_queue->last_name); ?>
                <?php if (isset($next_in_queue->unique_patient_id)) : ?>
                    <br><strong><?php esc_html_e('Patient ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($next_in_queue->unique_patient_id); ?>
                <?php endif; ?>
            </p>
            <button type="button" class="button button-primary" id="call-next-btn" data-queue-id="<?php echo esc_attr($next_in_queue->id); ?>">
                <?php esc_html_e('Call Next Patient', 'hospital-management-system'); ?>
            </button>
        </div>
        <?php else : ?>
        <div class="notice notice-info">
            <p><?php esc_html_e('The queue is currently empty.', 'hospital-management-system'); ?></p>
        </div>
        <?php endif; ?>

        <h3><?php esc_html_e('Today\'s Full Queue', 'hospital-management-system'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e('Token No.', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Patient Name', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Patient ID', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Estimated Time', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Called At', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Completed At', 'hospital-management-system'); ?></th>
                    <th scope="col"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php if (!empty($today_queue)) : ?>
                    <?php 
                    $position = 1;
                    $average_consultation_time = 15; // 15 minutes average per patient
                    foreach ($today_queue as $queue_entry) : 
                        $patient_name = $queue_entry->first_name . ' ' . $queue_entry->last_name;
                        $status_class = 'waiting' === $queue_entry->status ? 'status-waiting' : ('in-progress' === $queue_entry->status ? 'status-in-progress' : ('completed' === $queue_entry->status ? 'status-completed' : 'status-cancelled'));
                        
                        // Calculate estimated time for waiting patients
                        $estimated_time = '';
                        if ($queue_entry->status === 'waiting') {
                            $waiting_ahead = 0;
                            foreach ($today_queue as $check_entry) {
                                if ($check_entry->status === 'waiting' && intval($check_entry->token_number) < intval($queue_entry->token_number)) {
                                    $waiting_ahead++;
                                }
                            }
                            if ($active_token) {
                                $waiting_ahead++; // Add current patient
                            }
                            $estimated_minutes = $waiting_ahead * $average_consultation_time;
                            if ($estimated_minutes > 0) {
                                $estimated_time = sprintf(__('%d minutes', 'hospital-management-system'), $estimated_minutes);
                            } else {
                                $estimated_time = __('Next', 'hospital-management-system');
                            }
                        }
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($queue_entry->token_number); ?></strong></td>
                            <td><?php echo esc_html($patient_name); ?></td>
                            <td><?php echo esc_html($queue_entry->unique_patient_id ?? ''); ?></td>
                            <td><span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst(str_replace('-', ' ', $queue_entry->status))); ?></span></td>
                            <td><?php echo esc_html($estimated_time); ?></td>
                            <td><?php echo $queue_entry->called_at ? esc_html(date_i18n(get_option('time_format'), strtotime($queue_entry->called_at))) : '-'; ?></td>
                            <td><?php echo $queue_entry->completed_at ? esc_html(date_i18n(get_option('time_format'), strtotime($queue_entry->completed_at))) : '-'; ?></td>
                            <td>
                                <?php if ('waiting' === $queue_entry->status && (!$next_in_queue || $next_in_queue->id == $queue_entry->id)) : ?>
                                    <button type="button" class="button button-small call-next-btn" data-queue-id="<?php echo esc_attr($queue_entry->id); ?>"><?php esc_html_e('Call', 'hospital-management-system'); ?></button>
                                <?php endif; ?>
                                <?php if ('in-progress' === $queue_entry->status) : ?>
                                    <button type="button" class="button button-small complete-current-btn" data-queue-id="<?php echo esc_attr($queue_entry->id); ?>"><?php esc_html_e('Complete', 'hospital-management-system'); ?></button>
                                <?php endif; ?>
                                <?php if (in_array($queue_entry->status, array('waiting', 'in-progress'))) : ?>
                                    <button type="button" class="button button-small cancel-token-btn" data-queue-id="<?php echo esc_attr($queue_entry->id); ?>"><?php esc_html_e('Cancel', 'hospital-management-system'); ?></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e('No patients in the queue for today.', 'hospital-management-system'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .status-waiting { 
        color: #ff9000; 
        font-weight: bold; 
        background: #fff3cd;
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid #ffeaa7;
    }
    .status-in-progress { 
        color: #0073aa; 
        font-weight: bold; 
        background: #d1ecf1;
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid #bee5eb;
    }
    .status-completed { 
        color: #46b450; 
        font-weight: bold; 
        background: #d4edda;
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid #c3e6cb;
    }
    .status-cancelled { 
        color: #dc3232; 
        font-weight: bold; 
        text-decoration: line-through; 
        background: #f8d7da;
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid #f5c6cb;
    }
    
    .hms-add-to-queue-section {
        border-radius: 5px;
    }
    
    .hms-add-to-queue-section h3 {
        margin-top: 0;
        color: #0073aa;
    }
    
    .wp-list-table th {
        background: #f1f1f1;
    }
    
    .wp-list-table tbody tr:hover {
        background: #f9f9f9;
    }
    
    .notice h3 {
        margin-top: 0;
    }
    
    .button-small {
        margin-right: 5px;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function refreshQueue() {
        const doctorId = $('#doctor_id_select').val() || <?php echo intval($current_doctor_id); ?>;
        // Reload the page to refresh the queue display
        window.location.href = '?page=hms_queue&doctor_id=' + doctorId;
    }

    // Auto-refresh functionality
    let autoRefreshInterval;
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(function() {
            if ($('#auto-refresh').is(':checked')) {
                refreshQueue();
            }
        }, 60000);
    }
    
    startAutoRefresh();

    // Handle add to queue form submission
    $('#add-to-queue-form').on('submit', function(e) {
        e.preventDefault();
        const patientId = $('#patient_select').val();
        const doctorId = <?php echo intval($current_doctor_id); ?>;

        if (!patientId) {
            alert('<?php echo esc_js(__('Please select a patient.', 'hospital-management-system')); ?>');
            return;
        }

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_add_patient_to_queue',
                nonce: hms_admin.nonce,
                patient_id: patientId,
                doctor_id: doctorId,
                appointment_id: 0 // No specific appointment
            },
            beforeSend: function() {
                $('#add-to-queue-form button').prop('disabled', true).text('<?php echo esc_js(__('Adding...', 'hospital-management-system')); ?>');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#patient_select').val(''); // Clear selection
                    refreshQueue(); // Refresh the queue display
                } else {
                    alert(response.message || '<?php echo esc_js(__('Failed to add patient to queue.', 'hospital-management-system')); ?>');
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('AJAX request failed.', 'hospital-management-system')); ?>');
            },
            complete: function() {
                $('#add-to-queue-form button').prop('disabled', false).text('<?php echo esc_js(__('Add to Queue', 'hospital-management-system')); ?>');
            }
        });
    });

    // Handle queue management button clicks
    $(document).on('click', '.call-next-btn, .complete-current-btn, .cancel-token-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const action = btn.hasClass('call-next-btn') ? 'call_next' : (btn.hasClass('complete-current-btn') ? 'complete_current' : 'cancel_token');
        const queueId = btn.data('queue-id');

        if (!queueId) {
            alert('<?php echo esc_js(__('Invalid queue entry.', 'hospital-management-system')); ?>');
            return;
        }

        // Confirm action for cancel
        if (action === 'cancel_token') {
            if (!confirm('<?php echo esc_js(__('Are you sure you want to cancel this token?', 'hospital-management-system')); ?>')) {
                return;
            }
        }

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_manage_queue',
                nonce: hms_admin.nonce,
                queue_action: action,
                queue_id: queueId,
                doctor_id: <?php echo intval($current_doctor_id); ?>
            },
            beforeSend: function() {
                btn.prop('disabled', true).text('<?php echo esc_js(__('Processing...', 'hospital-management-system')); ?>');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message briefly
                    const successMsg = $('<div class="notice notice-success is-dismissible"><p>' + response.message + '</p></div>');
                    $('.wrap h1').after(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut();
                    }, 3000);
                    
                    // Refresh the queue display
                    setTimeout(refreshQueue, 1000);
                } else {
                    alert(response.message || '<?php echo esc_js(__('An error occurred.', 'hospital-management-system')); ?>');
                    btn.prop('disabled', false).text(btn.data('original-text'));
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('AJAX request failed.', 'hospital-management-system')); ?>');
                btn.prop('disabled', false).text(btn.data('original-text'));
            }
        });
    });

    // Store original button text to restore it if needed
    $('button[data-queue-id]').each(function() {
        $(this).data('original-text', $(this).text());
    });

    // Add visual feedback for queue status
    $('.status-waiting').parent().parent().css('background-color', '#fff3cd');
    $('.status-in-progress').parent().parent().css('background-color', '#d1ecf1');
    $('.status-completed').parent().parent().css('background-color', '#d4edda');
    $('.status-cancelled').parent().parent().css('background-color', '#f8d7da');

    // Queue management controls
    $('#auto-refresh').on('change', function() {
        if ($(this).is(':checked')) {
            startAutoRefresh();
            showNotification('Auto-refresh enabled', 'success');
        } else {
            if (autoRefreshInterval) clearInterval(autoRefreshInterval);
            showNotification('Auto-refresh disabled', 'info');
        }
    });

    $('#clear-completed').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to clear all completed entries from today\'s queue?', 'hospital-management-system')); ?>')) {
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_clear_completed_queue',
                nonce: hms_admin.nonce,
                doctor_id: <?php echo intval($current_doctor_id); ?>
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(refreshQueue, 1000);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('<?php echo esc_js(__('Failed to clear completed entries.', 'hospital-management-system')); ?>', 'error');
            }
        });
    });

    $('#reset-queue').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Are you sure you want to reset the entire queue? This will cancel all waiting patients.', 'hospital-management-system')); ?>')) {
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_reset_queue',
                nonce: hms_admin.nonce,
                doctor_id: <?php echo intval($current_doctor_id); ?>
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    setTimeout(refreshQueue, 1000);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('<?php echo esc_js(__('Failed to reset queue.', 'hospital-management-system')); ?>', 'error');
            }
        });
    });

    $('#export-queue').on('click', function() {
        window.open('<?php echo admin_url('admin-ajax.php'); ?>?action=hms_export_queue&doctor_id=<?php echo intval($current_doctor_id); ?>&nonce=' + hms_admin.nonce, '_blank');
    });

    // Notification system
    function showNotification(message, type) {
        const notificationClass = type === 'success' ? 'notice-success' : 
                                 type === 'error' ? 'notice-error' : 
                                 type === 'warning' ? 'notice-warning' : 'notice-info';
        
        const notification = $('<div class="notice ' + notificationClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('#hms-notifications').html(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notification.fadeOut();
        }, 5000);
    }

    // Show initial notification if queue is empty
    <?php if (empty($today_queue)) : ?>
    showNotification('<?php echo esc_js(__('Queue is empty. Add patients to get started.', 'hospital-management-system')); ?>', 'info');
    <?php endif; ?>
});
</script>
