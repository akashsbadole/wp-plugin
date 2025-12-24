<div class="wrap">
    <h1><?php echo isset($_GET['id']) ? __('Edit Appointment', 'hospital-management-system') : __('Add New Appointment', 'hospital-management-system'); ?></h1>

    <?php
    $appointment = null;
    $is_edit = false;

    if (isset($_GET['id'])) {
        $is_edit = true;
        $appointment_id = intval($_GET['id']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $appointment_id));

        if (!$appointment) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Appointment not found.', 'hospital-management-system') . '</p></div>';
            return;
        }
    }

    // Handle form submission feedback
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <form id="appointment-details-form" method="post" action="">
        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="page" value="hms_appointment_details">
        <?php if ($is_edit): ?>
            <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment->id); ?>">
        <?php endif; ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <label for="title"><?php _e('Title (optional)', 'hospital-management-system'); ?></label>
                            <input type="text" name="title" id="title" size="30" value="<?php echo $is_edit ? esc_attr($appointment->reason_for_visit) : ''; ?>" placeholder="<?php _e('e.g., General Checkup, Follow-up', 'hospital-management-system'); ?>">
                        </div>
                    </div>
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="submitdiv" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Save', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="save_appointment" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Appointment', 'hospital-management-system') : __('Schedule Appointment', 'hospital-management-system'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="appointmentdetails" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Appointment Details', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="patient_id"><?php _e('Patient', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <?php
                                            // Get all patients from the custom table
                                            global $wpdb;
                                            $patients_table = $wpdb->prefix . 'hms_patients';
                                            $patients = $wpdb->get_results("SELECT id, first_name, last_name, patient_id FROM $patients_table ORDER BY first_name, last_name");
                                            ?>
                                            <select name="patient_id" id="patient_id" class="regular-text" required>
                                                <option value=""><?php _e('&mdash; Select Patient &mdash;', 'hospital-management-system'); ?></option>
                                                <?php
                                                $selected_patient_id = $is_edit ? $appointment->patient_id : (isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0);
                                                foreach ($patients as $patient) {
                                                    $display_name = $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')';
                                                    echo '<option value="' . esc_attr($patient->id) . '" ' . selected($selected_patient_id, $patient->id, false) . '>' . esc_html($display_name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <p class="description"><?php _e('Start typing to search for a patient.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="doctor_id"><?php _e('Doctor', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <?php
                                            global $wpdb;
                                            $staff_table = $wpdb->prefix . 'hms_staff';
                                            $departments_table = $wpdb->prefix . 'hms_departments';
                                            
                                            // Get doctors with their department IDs
                                            $doctors = $wpdb->get_results("
                                                SELECT s.id, s.first_name, s.last_name, s.department as department_name, d.id as department_id
                                                FROM $staff_table s
                                                LEFT JOIN $departments_table d ON s.department = d.name
                                                WHERE s.role = 'Doctor' AND s.status = 'active'
                                                ORDER BY s.first_name, s.last_name
                                            ");
                                            
                                            // Get all departments for the JavaScript filtering
                                            $all_departments = $wpdb->get_results("SELECT id, name FROM $departments_table ORDER BY name");
                                            
                                            // Debug: Check if we have doctors
                                            if (empty($doctors)) {
                                                echo '<!-- Debug: No doctors found in staff table -->';
                                            } else {
                                                echo '<!-- Debug: Found ' . count($doctors) . ' doctors -->';
                                            }
                                            ?>
                                            <select name="doctor_id" id="doctor_id" class="regular-text" required>
                                                <option value=""><?php _e('&mdash; Select Doctor &mdash;', 'hospital-management-system'); ?></option>
                                                <?php
                                                $selected_doctor_id = $is_edit ? $appointment->doctor_id : (isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0);
                                                if (!empty($doctors)) {
                                                    foreach ($doctors as $doctor) {
                                                        $display_name = $doctor->first_name . ' ' . $doctor->last_name;
                                                        if (!empty($doctor->department_name)) {
                                                            $display_name .= ' (' . $doctor->department_name . ')';
                                                        }
                                                        echo '<option value="' . esc_attr($doctor->id) . '" data-department="' . esc_attr($doctor->department_id) . '" ' . selected($selected_doctor_id, $doctor->id, false) . '>' . esc_html($display_name) . '</option>';
                                                    }
                                                } else {
                                                    echo '<option value="" disabled>' . __('No doctors available. Please add doctors in Staff Management.', 'hospital-management-system') . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if (empty($doctors)) : ?>
                                                <p class="description" style="color: #d63638;">
                                                    <?php printf(__('No doctors found. <a href="%s">Add doctors</a> in Staff Management first.', 'hospital-management-system'), admin_url('admin.php?page=hms_staff_details')); ?>
                                                </p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="department_id"><?php _e('Department', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <?php
                                            // Departments are already fetched above for the JavaScript functionality
                                            ?>
                                            <select name="department_id" id="department_id" class="regular-text">
                                                <option value=""><?php _e('&mdash; Select Department &mdash;', 'hospital-management-system'); ?></option>
                                                <?php
                                                $selected_department_id = $is_edit ? $appointment->department_id : (isset($_POST['department_id']) ? intval($_POST['department_id']) : 0);
                                                if (!empty($all_departments)) {
                                                    foreach ($all_departments as $department) {
                                                        echo '<option value="' . esc_attr($department->id) . '" ' . selected($selected_department_id, $department->id, false) . '>' . esc_html($department->name) . '</option>';
                                                    }
                                                } else {
                                                    echo '<option value="" disabled>' . __('No departments available.', 'hospital-management-system') . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if (empty($all_departments)) : ?>
                                                <p class="description" style="color: #d63638;">
                                                    <?php _e('No departments found. Please reactivate the plugin to create default departments.', 'hospital-management-system'); ?>
                                                </p>
                                            <?php else : ?>
                                                <p class="description">
                                                    <?php printf(__('Found %d departments available.', 'hospital-management-system'), count($all_departments)); ?>
                                                </p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="appointment_date"><?php _e('Date', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="date" name="appointment_date" id="appointment_date" class="regular-text" value="<?php echo $is_edit ? esc_attr($appointment->appointment_date) : (isset($_POST['appointment_date']) ? esc_attr($_POST['appointment_date']) : ''); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="appointment_time"><?php _e('Time', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="time" name="appointment_time" id="appointment_time" class="regular-text" value="<?php echo $is_edit ? esc_attr($appointment->appointment_time) : (isset($_POST['appointment_time']) ? esc_attr($_POST['appointment_time']) : ''); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="status"><?php _e('Status', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="status" id="status" class="regular-text">
                                                <?php
                                                $statuses = array('scheduled', 'confirmed', 'in-progress', 'completed', 'cancelled', 'no-show');
                                                $selected_status = $is_edit ? $appointment->status : (isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'scheduled');
                                                foreach ($statuses as $status_key => $status_value) {
                                                    echo '<option value="' . esc_attr($status_value) . '" ' . selected($selected_status, $status_value, false) . '>' . esc_html(ucwords(str_replace('-', ' ', $status_value))) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="additionalinfo" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Additional Information', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="reason_for_visit"><?php _e('Reason for Visit', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="reason_for_visit" id="reason_for_visit" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($appointment->reason_for_visit) : (isset($_POST['reason_for_visit']) ? esc_textarea($_POST['reason_for_visit']) : ''); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="notes"><?php _e('Internal Notes', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($appointment->notes) : (isset($_POST['notes']) ? esc_textarea($_POST['notes']) : ''); ?></textarea>
                                            <p class="description"><?php _e('These notes are for internal use and will not be seen by the patient.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Store all doctor options for filtering
    var allDoctorOptions = $('#doctor_id option').clone();
    
    // Function to filter doctors based on selected department
    function filterDoctorsByDepartment() {
        var selectedDepartment = $('#department_id').val();
        var $doctorSelect = $('#doctor_id');
        var selectedDoctorId = $doctorSelect.val();
        
        // Clear current options except the first one
        $doctorSelect.find('option:not(:first)').remove();
        
        if (selectedDepartment === '') {
            // If no department is selected, show all doctors
            allDoctorOptions.each(function() {
                if ($(this).val() !== '') {
                    $doctorSelect.append($(this).clone());
                }
            });
        } else {
            // Filter doctors by selected department
            allDoctorOptions.each(function() {
                var doctorDepartment = $(this).data('department');
                if ($(this).val() !== '' && doctorDepartment == selectedDepartment) {
                    $doctorSelect.append($(this).clone());
                }
            });
        }
        
        // Restore the previously selected doctor if it's still available
        if (selectedDoctorId && $doctorSelect.find('option[value="' + selectedDoctorId + '"]').length > 0) {
            $doctorSelect.val(selectedDoctorId);
        }
        
        // Refresh Select2 if it's being used
        if ($.fn.select2) {
            $doctorSelect.trigger('change.select2');
        }
    }
    
    // Filter doctors when department changes
    $('#department_id').on('change', function() {
        filterDoctorsByDepartment();
    });
    
    // Initial filter on page load
    filterDoctorsByDepartment();
    
    // Enhance select2 for patient and doctor dropdowns if Select2 is available
    if ($.fn.select2) {
        $('#patient_id').select2({
            placeholder: "<?php _e('Search for a patient...', 'hospital-management-system'); ?>",
            allowClear: true
        });
        $('#doctor_id').select2({
            placeholder: "<?php _e('Select a doctor', 'hospital-management-system'); ?>",
            allowClear: true
        });
        $('#department_id').select2({
            placeholder: "<?php _e('Select a department', 'hospital-management-system'); ?>",
            allowClear: true
        });
    }

    // Form submission with AJAX (optional, can be a normal form post too)
    $('#appointment-details-form').on('submit', function(e) {
        // Basic client-side validation
        if ($('#patient_id').val() === '') {
            alert('<?php _e('Please select a patient.', 'hospital-management-system'); ?>');
            $('#patient_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#doctor_id').val() === '') {
            alert('<?php _e('Please select a doctor.', 'hospital-management-system'); ?>');
            $('#doctor_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#appointment_date').val() === '' || $('#appointment_time').val() === '') {
            alert('<?php _e('Please select both date and time for the appointment.', 'hospital-management-system'); ?>');
            $('#appointment_date').focus();
            e.preventDefault();
            return false;
        }
        // Allow form to submit normally or handle via AJAX
        // For now, we let it submit normally.
    });
});
</script>
