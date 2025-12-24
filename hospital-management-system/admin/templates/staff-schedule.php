<div class="wrap">
    <h1><?php esc_html_e('Staff Scheduling', 'hospital-management-system'); ?></h1>

    <?php
    // Display any notices or messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    $staff_obj = new HMS_Staff();
    ?>

    <div class="hms-scheduling">
        <!-- Add New Schedule -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('Add Staff Schedule', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <form method="post" action="">
                    <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                    <input type="hidden" name="add_schedule" value="1">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="staff_id"><?php esc_html_e('Staff Member', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                            </th>
                            <td>
                                <select name="staff_id" id="staff_id" class="regular-text" required>
                                    <option value=""><?php esc_html_e('Select Staff Member', 'hospital-management-system'); ?></option>
                                    <?php
                                    $staff_members = $staff_obj->get_staff_members(array('status' => 'active', 'per_page' => 100));
                                    foreach ($staff_members as $staff) {
                                        echo '<option value="' . esc_attr($staff->id) . '">';
                                        echo esc_html($staff->first_name . ' ' . $staff->last_name . ' (' . $staff->staff_id . ')');
                                        echo '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="schedule_date"><?php esc_html_e('Schedule Date', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                            </th>
                            <td>
                                <input type="date" name="schedule_date" id="schedule_date" class="regular-text" required min="<?php echo date('Y-m-d'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="start_time"><?php esc_html_e('Start Time', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                            </th>
                            <td>
                                <input type="time" name="start_time" id="start_time" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="end_time"><?php esc_html_e('End Time', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                            </th>
                            <td>
                                <input type="time" name="end_time" id="end_time" class="regular-text" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="break_start"><?php esc_html_e('Break Start Time', 'hospital-management-system'); ?></label>
                            </th>
                            <td>
                                <input type="time" name="break_start" id="break_start" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="break_end"><?php esc_html_e('Break End Time', 'hospital-management-system'); ?></label>
                            </th>
                            <td>
                                <input type="time" name="break_end" id="break_end" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                            </th>
                            <td>
                                <select name="status" id="status" class="regular-text">
                                    <option value="scheduled"><?php esc_html_e('Scheduled', 'hospital-management-system'); ?></option>
                                    <option value="confirmed"><?php esc_html_e('Confirmed', 'hospital-management-system'); ?></option>
                                    <option value="cancelled"><?php esc_html_e('Cancelled', 'hospital-management-system'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="notes"><?php esc_html_e('Notes', 'hospital-management-system'); ?></label>
                            </th>
                            <td>
                                <textarea name="notes" id="notes" rows="3" class="large-text" placeholder="<?php esc_html_e('Additional notes about this schedule...', 'hospital-management-system'); ?>"></textarea>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" class="button button-primary" value="<?php esc_html_e('Add Schedule', 'hospital-management-system'); ?>">
                    </p>
                </form>
            </div>
        </div>

        <!-- Current Week Schedule -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('Current Week Schedule', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <?php
                // Get current week dates
                $start_of_week = date('Y-m-d', strtotime('monday this week'));
                $end_of_week = date('Y-m-d', strtotime('sunday this week'));
                
                // Filter controls
                ?>
                <div class="schedule-filters" style="margin-bottom: 20px;">
                    <form method="get">
                        <input type="hidden" name="page" value="hms_staff_schedule">
                        
                        <label for="filter_staff"><?php esc_html_e('Filter by Staff:', 'hospital-management-system'); ?></label>
                        <select name="filter_staff" id="filter_staff">
                            <option value=""><?php esc_html_e('All Staff', 'hospital-management-system'); ?></option>
                            <?php
                            foreach ($staff_members as $staff) {
                                $selected = (isset($_GET['filter_staff']) && $_GET['filter_staff'] == $staff->id) ? 'selected' : '';
                                echo '<option value="' . esc_attr($staff->id) . '" ' . $selected . '>';
                                echo esc_html($staff->first_name . ' ' . $staff->last_name);
                                echo '</option>';
                            }
                            ?>
                        </select>
                        
                        <label for="filter_date"><?php esc_html_e('Week Starting:', 'hospital-management-system'); ?></label>
                        <input type="date" name="filter_date" id="filter_date" value="<?php echo isset($_GET['filter_date']) ? esc_attr($_GET['filter_date']) : $start_of_week; ?>">
                        
                        <input type="submit" class="button" value="<?php esc_html_e('Filter', 'hospital-management-system'); ?>">
                    </form>
                </div>

                <?php
                // Adjust dates based on filter
                if (isset($_GET['filter_date'])) {
                    $start_of_week = date('Y-m-d', strtotime('monday', strtotime($_GET['filter_date'])));
                    $end_of_week = date('Y-m-d', strtotime('sunday', strtotime($_GET['filter_date'])));
                }

                // Get schedules for the week
                $schedule_data = array();
                $filter_staff_id = isset($_GET['filter_staff']) ? intval($_GET['filter_staff']) : null;

                if ($filter_staff_id) {
                    $schedules = $staff_obj->get_staff_schedule($filter_staff_id, $start_of_week, $end_of_week);
                } else {
                    // Get all schedules for the week
                    global $wpdb;
                    $schedule_table = $wpdb->prefix . 'hms_staff_schedule';
                    $staff_table = $wpdb->prefix . 'hms_staff';
                    
                    $query = "
                        SELECT s.*, st.first_name, st.last_name, st.staff_id as staff_code, st.role, st.department
                        FROM $schedule_table s
                        LEFT JOIN $staff_table st ON s.staff_id = st.id
                        WHERE s.schedule_date BETWEEN %s AND %s
                        ORDER BY s.schedule_date ASC, s.start_time ASC
                    ";
                    
                    $schedules = $wpdb->get_results($wpdb->prepare($query, $start_of_week, $end_of_week));
                }

                // Organize schedules by date and staff
                $weekly_schedule = array();
                for ($i = 0; $i < 7; $i++) {
                    $date = date('Y-m-d', strtotime($start_of_week . ' +' . $i . ' days'));
                    $weekly_schedule[$date] = array();
                }

                foreach ($schedules as $schedule) {
                    $weekly_schedule[$schedule->schedule_date][] = $schedule;
                }
                ?>

                <div class="schedule-grid">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 100px;"><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                                <th style="width: 120px;"><?php esc_html_e('Day', 'hospital-management-system'); ?></th>
                                <th><?php esc_html_e('Scheduled Staff', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($weekly_schedule as $date => $day_schedules) {
                                $day_name = date('l', strtotime($date));
                                $formatted_date = date('M j', strtotime($date));
                                
                                echo '<tr>';
                                echo '<td><strong>' . esc_html($formatted_date) . '</strong></td>';
                                echo '<td>' . esc_html($day_name) . '</td>';
                                echo '<td>';
                                
                                if (!empty($day_schedules)) {
                                    foreach ($day_schedules as $schedule) {
                                        $staff_name = isset($schedule->first_name) ? 
                                            $schedule->first_name . ' ' . $schedule->last_name : 
                                            'Staff ID: ' . $schedule->staff_id;
                                        
                                        $time_range = date('g:i A', strtotime($schedule->start_time)) . ' - ' . 
                                                     date('g:i A', strtotime($schedule->end_time));
                                        
                                        $status_class = $schedule->status === 'confirmed' ? 'confirmed' : 
                                                       ($schedule->status === 'cancelled' ? 'cancelled' : 'scheduled');
                                        
                                        echo '<div class="schedule-entry ' . esc_attr($status_class) . '" style="margin-bottom: 8px; padding: 8px; border-left: 4px solid #2271b1; background: #f8f9fa;">';
                                        echo '<strong>' . esc_html($staff_name) . '</strong><br>';
                                        echo '<span class="time-range">' . esc_html($time_range) . '</span>';
                                        
                                        if (isset($schedule->role)) {
                                            echo ' <span class="role-badge">' . esc_html(ucwords($schedule->role)) . '</span>';
                                        }
                                        
                                        if ($schedule->break_start && $schedule->break_end) {
                                            $break_time = date('g:i A', strtotime($schedule->break_start)) . ' - ' . 
                                                         date('g:i A', strtotime($schedule->break_end));
                                            echo '<br><small>Break: ' . esc_html($break_time) . '</small>';
                                        }
                                        
                                        if ($schedule->notes) {
                                            echo '<br><small class="notes">' . esc_html($schedule->notes) . '</small>';
                                        }
                                        
                                        echo '<span class="status-badge status-' . esc_attr($schedule->status) . '">' . 
                                             esc_html(ucwords($schedule->status)) . '</span>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<em>' . esc_html__('No staff scheduled', 'hospital-management-system') . '</em>';
                                }
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Staff Availability Summary -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('Staff Availability Summary', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Staff Member', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Role', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('This Week Hours', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($staff_members as $staff) {
                            $staff_schedules = $staff_obj->get_staff_schedule($staff->id, $start_of_week, $end_of_week);
                            $total_hours = 0;
                            $schedule_count = 0;
                            
                            foreach ($staff_schedules as $schedule) {
                                if ($schedule->status !== 'cancelled') {
                                    $start = strtotime($schedule->start_time);
                                    $end = strtotime($schedule->end_time);
                                    $hours = ($end - $start) / 3600;
                                    
                                    // Subtract break time if applicable
                                    if ($schedule->break_start && $schedule->break_end) {
                                        $break_start = strtotime($schedule->break_start);
                                        $break_end = strtotime($schedule->break_end);
                                        $break_hours = ($break_end - $break_start) / 3600;
                                        $hours -= $break_hours;
                                    }
                                    
                                    $total_hours += $hours;
                                    $schedule_count++;
                                }
                            }
                            
                            echo '<tr>';
                            echo '<td><strong>' . esc_html($staff->first_name . ' ' . $staff->last_name) . '</strong><br>';
                            echo '<small>' . esc_html($staff->staff_id) . '</small></td>';
                            echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $staff->role))) . '</td>';
                            echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $staff->department))) . '</td>';
                            echo '<td>' . number_format($total_hours, 1) . ' hours (' . $schedule_count . ' shifts)</td>';
                            echo '<td>';
                            
                            if ($staff->status === 'active') {
                                if ($total_hours > 0) {
                                    echo '<span class="status-badge status-scheduled">Scheduled</span>';
                                } else {
                                    echo '<span class="status-badge status-available">Available</span>';
                                }
                            } else {
                                echo '<span class="status-badge status-' . esc_attr($staff->status) . '">' . 
                                     esc_html(ucwords(str_replace('_', ' ', $staff->status))) . '</span>';
                            }
                            
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .schedule-entry {
        position: relative;
    }
    
    .schedule-entry.confirmed {
        border-left-color: #28a745 !important;
    }
    
    .schedule-entry.cancelled {
        border-left-color: #dc3545 !important;
        opacity: 0.6;
    }
    
    .role-badge {
        background: #2271b1;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        text-transform: uppercase;
    }
    
    .status-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .status-scheduled { background: #2271b1; color: white; }
    .status-confirmed { background: #28a745; color: white; }
    .status-cancelled { background: #dc3545; color: white; }
    .status-available { background: #17a2b8; color: white; }
    .status-active { background: #28a745; color: white; }
    .status-inactive { background: #6c757d; color: white; }
    .status-on_leave { background: #ffc107; color: #212529; }
    
    .time-range {
        font-weight: 500;
        color: #2271b1;
    }
    
    .notes {
        color: #666;
        font-style: italic;
    }
    
    .schedule-filters {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #e9ecef;
    }
    
    .schedule-filters label {
        margin-right: 8px;
        font-weight: 500;
    }
    
    .schedule-filters select,
    .schedule-filters input[type="date"] {
        margin-right: 15px;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Auto-set end time when start time is selected
    $('#start_time').on('change', function() {
        var startTime = $(this).val();
        if (startTime && !$('#end_time').val()) {
            var start = new Date('2000-01-01 ' + startTime);
            start.setHours(start.getHours() + 8); // Default 8-hour shift
            var endTime = start.toTimeString().substr(0, 5);
            $('#end_time').val(endTime);
        }
    });
    
    // Validate time ranges
    $('#end_time').on('change', function() {
        var startTime = $('#start_time').val();
        var endTime = $(this).val();
        
        if (startTime && endTime && startTime >= endTime) {
            alert('<?php esc_html_e('End time must be after start time.', 'hospital-management-system'); ?>');
            $(this).focus();
        }
    });
});
</script>