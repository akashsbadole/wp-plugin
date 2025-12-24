<?php
/**
 * HMS Appointment Class
 *
 * Handles appointment scheduling, management, and related logic.
 * This class primarily interacts with the custom `wp_hms_appointments` table.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/includes
 * @author     HMS Development Team
 * @since      1.0.0
 */

class HMS_Appointment {

    /**
     * Constructor.
     * Hooks into WordPress actions for AJAX handling.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // AJAX hooks for appointment management
        add_action('wp_ajax_hms_schedule_appointment', array($this, 'ajax_schedule_appointment'));
        add_action('wp_ajax_hms_update_appointment', array($this, 'ajax_update_appointment'));
        add_action('wp_ajax_hms_cancel_appointment', array($this, 'ajax_cancel_appointment'));
        add_action('wp_ajax_hms_get_appointment', array($this, 'ajax_get_appointment'));
        add_action('wp_ajax_hms_get_appointments_list', array($this, 'ajax_get_appointments_list'));
        add_action('wp_ajax_hms_get_doctor_availability', array($this, 'ajax_get_doctor_availability'));
    }

    /**
     * Get a single appointment by ID from the custom table.
     *
     * @since    1.0.0
     * @param    int       $appointment_id    The appointment's database ID.
     * @return   object|null                  The appointment data object, or null if not found.
     */
    public function get_appointment(int $appointment_id): ?object {
        return HMS_Helper::hms_get_row('hms_appointments', array('id' => $appointment_id), array('%d'));
    }

    /**
     * Get all appointments, with optional pagination, search, and filtering.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter appointments.
     * @param    string    $status             Optional. Filter by appointment status.
     * @param    int       $doctor_id          Optional. Filter by doctor ID.
     * @param    int       $patient_id         Optional. Filter by patient ID.
     * @param    string    $date_from          Optional. Filter by appointment date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by appointment date to (Y-m-d).
     * @param    int       $offset             Optional. The offset for pagination.
     * @param    int       $per_page           Optional. The number of appointments to return per page.
     * @return   array                          An array of appointment data objects.
     */
    public function get_appointments(string $search = '', string $status = '', int $doctor_id = 0, int $patient_id = 0, string $date_from = '', string $date_to = '', int $offset = 0, int $per_page = 20): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        
        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            // Assuming patient name is stored in a related patient table or as a meta field.
            // For simplicity, we'll search by patient_id if it's numeric, or notes.
            $where_clauses[] = "(notes LIKE %s OR patient_id LIKE %s)";
            $where_values = array_fill(0, 2, $search_term);
        }

        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }

        if ($doctor_id > 0) {
            $where_clauses[] = "doctor_id = %d";
            $where_values[] = $doctor_id;
        }

        if ($patient_id > 0) {
            $where_clauses[] = "patient_id = %d";
            $where_values[] = $patient_id;
        }

        if (!empty($date_from)) {
            $where_clauses[] = "appointment_date >= %s";
            $where_values[] = $date_from;
        }

        if (!empty($date_to)) {
            $where_clauses[] = "appointment_date <= %s";
            $where_values[] = $date_to;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY appointment_date DESC, appointment_time DESC LIMIT %d OFFSET %d";
        $values = array_merge($where_values, array($per_page, $offset));
        
        return $wpdb->get_results($wpdb->prepare($query, $values));
    }

    /**
     * Get total count of appointments for pagination.
     *
     * @since    1.0.0
     * @param    string    $search             Optional. A search term to filter appointments.
     * @param    string    $status             Optional. Filter by appointment status.
     * @param    int       $doctor_id          Optional. Filter by doctor ID.
     * @param    int       $patient_id         Optional. Filter by patient ID.
     * @param    string    $date_from          Optional. Filter by appointment date from (Y-m-d).
     * @param    string    $date_to            Optional. Filter by appointment date to (Y-m-d).
     * @return   int                             The total number of appointments.
     */
    public function get_appointments_count(string $search = '', string $status = '', int $doctor_id = 0, int $patient_id = 0, string $date_from = '', string $date_to = ''): int {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $where_clauses = array();
        $where_values = array();

        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(notes LIKE %s OR patient_id LIKE %s)";
            $where_values = array_fill(0, 2, $search_term);
        }
        if (!empty($status)) {
            $where_clauses[] = "status = %s";
            $where_values[] = $status;
        }
        if ($doctor_id > 0) {
            $where_clauses[] = "doctor_id = %d";
            $where_values[] = $doctor_id;
        }
        if ($patient_id > 0) {
            $where_clauses[] = "patient_id = %d";
            $where_values[] = $patient_id;
        }
        if (!empty($date_from)) {
            $where_clauses[] = "appointment_date >= %s";
            $where_values[] = $date_from;
        }
        if (!empty($date_to)) {
            $where_clauses[] = "appointment_date <= %s";
            $where_values[] = $date_to;
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = "WHERE " . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        return (int) $wpdb->get_var($wpdb->prepare($query, $where_values));
    }

    /**
     * Schedule a new appointment.
     *
     * @since    1.0.0
     * @param    array     $data              An associative array of appointment data.
     * @return   int|false                     The new appointment's database ID, or false on failure.
     */
    public function schedule_appointment(array $data) {
        $table_name = 'hms_appointments'; // No prefix here as HMS_Helper adds it.

        // Basic validation
        if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return false;
        }

        // Check for slot availability before scheduling
        if (!$this->is_slot_available($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            return new WP_Error('slot_not_available', __('The selected time slot is not available.', 'hospital-management-system'));
        }

        $appointment_data = array(
            'patient_id'       => intval($data['patient_id']),
            'doctor_id'        => intval($data['doctor_id']),
            'department_id'    => !empty($data['department_id']) ? intval($data['department_id']) : null,
            'appointment_date' => sanitize_text_field($data['appointment_date']),
            'appointment_time' => sanitize_text_field($data['appointment_time']),
            'status'           => !empty($data['status']) ? sanitize_text_field($data['status']) : 'scheduled',
            'notes'            => sanitize_textarea_field($data['notes'] ?? ''),
            'created_at'       => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s');
        
        $appointment_id = HMS_Helper::hms_create($table_name, $appointment_data, $format);

        if ($appointment_id) {
            HMS_Helper::log_action('Appointment Scheduled', 'Appointment', $appointment_id, json_encode($appointment_data));
            
            // Send confirmation notification
            $this->send_appointment_confirmation($appointment_id);

            // Optionally, add patient to queue if it's an OPD appointment for today
            if ($data['appointment_date'] === gmdate('Y-m-d') && $data['status'] === 'scheduled') {
                if (class_exists('HMS_Patient')) {
                    $patient_manager = new HMS_Patient();
                    $patient_manager->add_patient_to_queue($data['patient_id'], $data['doctor_id'], $appointment_id);
                }
            }
            return $appointment_id;
        }
        return false;
    }

    /**
     * Update an existing appointment.
     *
     * @since    1.0.0
     * @param    int       $appointment_id    The appointment's database ID.
     * @param    array     $data              An associative array of appointment data to update.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function update_appointment(int $appointment_id, array $data) {
        $table_name = 'hms_appointments';
        $where = array('id' => $appointment_id);
        $where_format = array('%d');

        $existing_appointment = $this->get_appointment($appointment_id);
        if (!$existing_appointment) {
            return new WP_Error('appointment_not_found', __('Appointment not found.', 'hospital-management-system'));
        }

        // If date, time, or doctor is changing, check availability again
        if (isset($data['appointment_date']) || isset($data['appointment_time']) || isset($data['doctor_id'])) {
            $new_date = $data['appointment_date'] ?? $existing_appointment->appointment_date;
            $new_time = $data['appointment_time'] ?? $existing_appointment->appointment_time;
            $new_doctor_id = $data['doctor_id'] ?? $existing_appointment->doctor_id;

            if (!$this->is_slot_available($new_doctor_id, $new_date, $new_time, $appointment_id)) {
                return new WP_Error('slot_not_available', __('The selected time slot is not available.', 'hospital-management-system'));
            }
        }
        
        $appointment_data = array();
        $format = array();

        $fields_to_update = array('patient_id', 'doctor_id', 'department_id', 'appointment_date', 'appointment_time', 'status', 'notes');
        foreach ($fields_to_update as $field) {
            if (isset($data[$field])) {
                $appointment_data[$field] = sanitize_text_field($data[$field]);
                if (in_array($field, array('patient_id', 'doctor_id', 'department_id'))) {
                    $format[] = '%d';
                } else {
                    $format[] = '%s';
                }
            }
        }
        
        if (empty($appointment_data)) {
            return true; // No data to update
        }

        $updated = HMS_Helper::hms_update($table_name, $appointment_data, $where, $format, $where_format);
        
        if ($updated !== false) {
            HMS_Helper::log_action('Appointment Updated', 'Appointment', $appointment_id, json_encode($appointment_data));
            // If status changed to 'cancelled', handle related actions (e.g., free up queue slot)
            if (isset($data['status']) && $data['status'] === 'cancelled' && $existing_appointment->status !== 'cancelled') {
                // Handle cancellation logic, e.g., remove from queue if applicable
                if (class_exists('HMS_Helper') && method_exists('HMS_Helper', 'update_queue_status')) {
                    // This requires knowing the queue entry ID. A more direct link might be needed.
                    // For now, we'll assume a method exists to find and update the queue entry by appointment_id.
                    // This is a placeholder for more complex logic.
                    error_log("Appointment ID {$appointment_id} was cancelled. Queue slot should be freed if applicable.");
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Cancel (delete) an appointment.
     *
     * @since    1.0.0
     * @param    int       $appointment_id    The appointment's database ID.
     * @return   bool|WP_Error                True on success, WP_Error on failure.
     */
    public function cancel_appointment(int $appointment_id) {
        // Instead of deleting, we'll update the status to 'cancelled'
        return $this->update_appointment($appointment_id, array('status' => 'cancelled'));
    }

    /**
     * Check if a time slot is available for a doctor on a given date.
     *
     * @since    1.0.0
     * @param    int       $doctor_id          The doctor's database ID.
     * @param    string    $date               The date (Y-m-d format).
     * @param    string    $time               The time slot (H:i:s format).
     * @param    int|null  $exclude_appointment_id Optional. Exclude an appointment from the check (e.g., when updating).
     * @return   bool                          True if available, false otherwise.
     */
    public function is_slot_available(int $doctor_id, string $date, string $time, ?int $exclude_appointment_id = null): bool {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $where_clause = $wpdb->prepare("doctor_id = %d AND appointment_date = %s AND appointment_time = %s AND status != %s", $doctor_id, $date, $time, 'cancelled');
        $values = array($doctor_id, $date, $time, 'cancelled');

        if ($exclude_appointment_id) {
            $where_clause .= $wpdb->prepare(" AND id != %d", $exclude_appointment_id);
        }
        
        $query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $count = (int) $wpdb->get_var($query, $values);

        return $count === 0;
    }
    
    /**
     * Get available time slots for a doctor on a given date.
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The doctor's database ID.
     * @param    string    $date         The date (Y-m-d format).
     * @return   array                   An array of available time slots (e.g., ['09:00:00', '09:30:00']).
     */
    public function get_available_slots(int $doctor_id, string $date): array {
        $appointment_duration = (int) get_option('hms_appointment_duration', 30); // in minutes
        $working_hours_start = get_option('hms_working_hours_start', '09:00');
        $working_hours_end = get_option('hms_working_hours_end', '17:00');

        $start_time = strtotime($working_hours_start);
        $end_time = strtotime($working_hours_end);
        $interval = $appointment_duration * 60; // in seconds
        
        $available_slots = array();
        $booked_slots = $this->get_booked_slots($doctor_id, $date);

        for ($current_time = $start_time; $current_time < $end_time; $current_time += $interval) {
            $time_str = gmdate('H:i:s', $current_time);
            if (!in_array($time_str, $booked_slots)) {
                $available_slots[] = $time_str;
            }
        }
        
        return $available_slots;
    }

    /**
     * Get booked time slots for a doctor on a given date.
     *
     * @since    1.0.0
     * @param    int       $doctor_id    The doctor's database ID.
     * @param    string    $date         The date (Y-m-d format).
     * @return   array                   An array of booked time slots.
     */
    private function get_booked_slots(int $doctor_id, string $date): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        
        $query = $wpdb->prepare(
            "SELECT appointment_time FROM {$table_name} WHERE doctor_id = %d AND appointment_date = %s AND status != %s",
            $doctor_id,
            $date,
            'cancelled'
        );
        
        $results = $wpdb->get_col($query);
        return $results ? $results : array();
    }


    // --- AJAX Handlers ---

    /**
     * AJAX handler to schedule a new appointment.
     */
    public function ajax_schedule_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $data = array(
            'patient_id'       => intval($_POST['patient_id']),
            'doctor_id'        => intval($_POST['doctor_id']),
            'department_id'    => intval($_POST['department_id']),
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'status'           => sanitize_text_field($_POST['status'] ?? 'scheduled'),
            'notes'            => sanitize_textarea_field($_POST['notes'] ?? ''),
        );

        $appointment_id = $this->schedule_appointment($data);

        if (is_wp_error($appointment_id)) {
            wp_send_json_error($appointment_id->get_error_message());
        } elseif ($appointment_id) {
            $appointment = $this->get_appointment($appointment_id);
            wp_send_json_success(array(
                'message' => __('Appointment scheduled successfully.', 'hospital-management-system'),
                'appointment' => $appointment
            ));
        } else {
            wp_send_json_error(__('Failed to schedule appointment.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to update an appointment.
     */
    public function ajax_update_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $appointment_id = intval($_POST['appointment_id']);
        $data = array(
            'patient_id'       => intval($_POST['patient_id']),
            'doctor_id'        => intval($_POST['doctor_id']),
            'department_id'    => intval($_POST['department_id']),
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'status'           => sanitize_text_field($_POST['status']),
            'notes'            => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_appointment($appointment_id, $data);

        if (is_wp_error($updated)) {
            wp_send_json_error($updated->get_error_message());
        } elseif ($updated) {
            $appointment = $this->get_appointment($appointment_id);
            wp_send_json_success(array(
                'message' => __('Appointment updated successfully.', 'hospital-management-system'),
                'appointment' => $appointment
            ));
        } else {
            wp_send_json_error(__('Failed to update appointment.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to cancel an appointment.
     */
    public function ajax_cancel_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $appointment_id = intval($_POST['appointment_id']);
        $cancelled = $this->cancel_appointment($appointment_id);

        if (is_wp_error($cancelled)) {
            wp_send_json_error($cancelled->get_error_message());
        } elseif ($cancelled) {
            wp_send_json_success(array('message' => __('Appointment cancelled successfully.', 'hospital-management-system')));
        } else {
            wp_send_json_error(__('Failed to cancel appointment.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single appointment's details.
     */
    public function ajax_get_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }
        $appointment_id = intval($_GET['appointment_id']);
        $appointment = $this->get_appointment($appointment_id);
        if ($appointment) {
            wp_send_json_success($appointment);
        } else {
            wp_send_json_error(__('Appointment not found.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a list of appointments for DataTables or similar.
     */
    public function ajax_get_appointments_list() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
        $patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        $offset = isset($_GET['start']) ? intval($_GET['start']) : 0;
        $per_page = isset($_GET['length']) ? intval($_GET['length']) : 20;
        
        $appointments = $this->get_appointments($search, $status, $doctor_id, $patient_id, $date_from, $date_to, $offset, $per_page);
        $total_appointments = $this->get_appointments_count($search, $status, $doctor_id, $patient_id, $date_from, $date_to);
        
        $data = array();
        foreach ($appointments as $appt) {
            // Fetch patient and doctor names for display
            $patient_name = 'N/A';
            $doctor_name = 'N/A';
            if (class_exists('HMS_Patient')) {
                $patient_obj = new HMS_Patient();
                $patient = $patient_obj->get_patient($appt->patient_id);
                if ($patient) {
                    $patient_name = $patient->first_name . ' ' . $patient->last_name;
                }
            }
            $doctor = get_user_by('ID', $appt->doctor_id);
            if ($doctor) {
                $doctor_name = $doctor->display_name;
            }

            $data[] = array(
                'id' => $appt->id,
                'date' => date_i18n(get_option('date_format'), strtotime($appt->appointment_date)),
                'time' => date_i18n(get_option('time_format'), strtotime($appt->appointment_time)),
                'patient' => $patient_name,
                'doctor' => $doctor_name,
                'status' => ucfirst($appt->status),
                'actions' => '' // Action buttons will be added by JS
            );
        }

        wp_send_json(array(
            'draw' => intval($_GET['draw']),
            'recordsTotal' => $total_appointments,
            'recordsFiltered' => $total_appointments,
            'data' => $data
        ));
    }

    /**
     * AJAX handler to get a doctor's available slots for a given date.
     */
    public function ajax_get_doctor_availability() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $doctor_id = intval($_POST['doctor_id']);
        $date = sanitize_text_field($_POST['date']);

        if (empty($doctor_id) || empty($date)) {
            wp_send_json_error(__('Invalid doctor or date.', 'hospital-management-system'));
        }

        $available_slots = $this->get_available_slots($doctor_id, $date);
        wp_send_json_success($available_slots);
    }

    /**
     * Send an appointment confirmation email to the patient.
     *
     * @since    1.0.0
     * @param    int       $appointment_id    The appointment's database ID.
     * @return   bool                         True on success, false on failure.
     */
    private function send_appointment_confirmation(int $appointment_id): bool {
        $appointment = $this->get_appointment($appointment_id);
        if (!$appointment) {
            return false;
        }

        // Ensure HMS_Patient class is available
        if (!class_exists('HMS_Patient')) {
            return false;
        }
        $patient_manager = new HMS_Patient();
        $patient = $patient_manager->get_patient($appointment->patient_id);

        if (!$patient || empty($patient->email)) {
            return false; // Cannot send email without patient or email
        }

        $patient_email = $patient->email;
        $patient_name = $patient->first_name . ' ' . $patient->last_name;
        
        $appointment_date = date_i18n(get_option('date_format'), strtotime($appointment->appointment_date));
        $appointment_time = date_i18n(get_option('time_format'), strtotime($appointment->appointment_time));
        
        $doctor = get_user_by('ID', $appointment->doctor_id);
        $doctor_name = $doctor ? $doctor->display_name : __('N/A', 'hospital-management-system');
        
        $subject = __('Appointment Confirmation', 'hospital-management-system');
        $message = sprintf(
            /* translators: 1: Patient Name, 2: Doctor Name, 3: Appointment Date, 4: Appointment Time */
            __('Dear %1$s,\n\nYour appointment with Dr. %2$s has been scheduled for %3$s at %4$s.\n\nPlease arrive 15 minutes before your appointment time.\n\nIf you need to cancel or reschedule, please contact us.\n\nThank you,\nHospital Management Team', 'hospital-management-system'),
            $patient_name,
            $doctor_name,
            $appointment_date,
            $appointment_time
        );
        
        return HMS_Helper::send_email($patient_email, $subject, $message);
    }
}
