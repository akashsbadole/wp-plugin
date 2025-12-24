<?php
/**
 * HMS Appointments Class
 *
 * Handles appointment management, including CRUD operations and status updates.
 */

class HMS_Appointments {

    public function __construct() {
        // AJAX hooks for appointment management
        add_action('wp_ajax_hms_add_appointment', array($this, 'ajax_add_appointment'));
        add_action('wp_ajax_hms_update_appointment', array($this, 'ajax_update_appointment'));
        add_action('wp_ajax_hms_delete_appointment', array($this, 'ajax_delete_appointment'));
        add_action('wp_ajax_hms_cancel_appointment', array($this, 'ajax_cancel_appointment'));
        add_action('wp_ajax_hms_get_appointment', array($this, 'ajax_get_appointment'));
        add_action('wp_ajax_hms_get_appointments_list', array($this, 'ajax_get_appointments_list'));
    }

    /**
     * Get a single appointment by ID.
     */
    public function get_appointment($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $appointment_id));
    }

    /**
     * Get all appointments, with optional filtering.
     */
    public function get_appointments($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $defaults = array(
            'doctor_id' => 0,
            'patient_id' => 0,
            'status' => '',
            'start_date' => '',
            'end_date' => '',
            'orderby' => 'appointment_date',
            'order' => 'DESC',
            'offset' => 0,
            'per_page' => 20
        );
        $args = wp_parse_args($args, $defaults);

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['doctor_id'])) {
            $where_clauses[] = "doctor_id = %d";
            $prepare_values[] = intval($args['doctor_id']);
        }
        if (!empty($args['patient_id'])) {
            $where_clauses[] = "patient_id = %d";
            $prepare_values[] = intval($args['patient_id']);
        }
        if (!empty($args['status'])) {
            $where_clauses[] = "status = %s";
            $prepare_values[] = sanitize_text_field($args['status']);
        }
        if (!empty($args['start_date'])) {
            $where_clauses[] = "appointment_date >= %s";
            $prepare_values[] = sanitize_text_field($args['start_date']);
        }
        if (!empty($args['end_date'])) {
            $where_clauses[] = "appointment_date <= %s";
            $prepare_values[] = sanitize_text_field($args['end_date']);
        }

        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $query = "SELECT * FROM $table_name $where_sql ORDER BY " . sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']) . " LIMIT %d OFFSET %d";
        $prepare_values[] = intval($args['per_page']);
        $prepare_values[] = intval($args['offset']);

        return $wpdb->get_results($wpdb->prepare($query, $prepare_values));
    }

    /**
     * Get total count of appointments for pagination.
     */
    public function get_appointments_count($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $where_clauses = array();
        $prepare_values = array();

        if (!empty($args['doctor_id'])) {
            $where_clauses[] = "doctor_id = %d";
            $prepare_values[] = intval($args['doctor_id']);
        }
        if (!empty($args['patient_id'])) {
            $where_clauses[] = "patient_id = %d";
            $prepare_values[] = intval($args['patient_id']);
        }
        if (!empty($args['status'])) {
            $where_clauses[] = "status = %s";
            $prepare_values[] = sanitize_text_field($args['status']);
        }
        if (!empty($args['start_date'])) {
            $where_clauses[] = "appointment_date >= %s";
            $prepare_values[] = sanitize_text_field($args['start_date']);
        }
        if (!empty($args['end_date'])) {
            $where_clauses[] = "appointment_date <= %s";
            $prepare_values[] = sanitize_text_field($args['end_date']);
        }
        
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $query = "SELECT COUNT(*) FROM $table_name $where_sql";
        if (!empty($prepare_values)) {
            return $wpdb->get_var($wpdb->prepare($query, $prepare_values));
        }
        return $wpdb->get_var($query);
    }

    /**
     * Add a new appointment.
     */
    public function add_appointment($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $appointment_data = array(
            'patient_id' => intval($data['patient_id']),
            'doctor_id' => intval($data['doctor_id']),
            'department_id' => !empty($data['department_id']) ? intval($data['department_id']) : null,
            'appointment_date' => sanitize_text_field($data['appointment_date']),
            'appointment_time' => sanitize_text_field($data['appointment_time']),
            'status' => sanitize_text_field($data['status']),
            'reason_for_visit' => sanitize_textarea_field($data['reason_for_visit']),
            'notes' => sanitize_textarea_field($data['notes']),
            'created_at' => current_time('mysql'),
        );

        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s');
        
        $result = $wpdb->insert($table_name, $appointment_data, $format);

        if ($result) {
            // Log the action
            HMS_Helper::log_action('Appointment Added', 'Appointment', $wpdb->insert_id, json_encode($appointment_data));
            
            // Send notification (optional)
            $this->send_appointment_notification($wpdb->insert_id, 'created');

            return $wpdb->insert_id;
        }
        return false;
    }

    /**
     * Update an existing appointment.
     */
    public function update_appointment($appointment_id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';

        $appointment_data = array(
            'patient_id' => intval($data['patient_id']),
            'doctor_id' => intval($data['doctor_id']),
            'department_id' => !empty($data['department_id']) ? intval($data['department_id']) : null,
            'appointment_date' => sanitize_text_field($data['appointment_date']),
            'appointment_time' => sanitize_text_field($data['appointment_time']),
            'status' => sanitize_text_field($data['status']),
            'reason_for_visit' => sanitize_textarea_field($data['reason_for_visit']),
            'notes' => sanitize_textarea_field($data['notes']),
            'updated_at' => current_time('mysql'),
        );

        $where = array('id' => $appointment_id);
        $format = array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        $where_format = array('%d');

        $result = $wpdb->update($table_name, $appointment_data, $where, $format, $where_format);
        
        if ($result !== false) {
            // Log the action
            HMS_Helper::log_action('Appointment Updated', 'Appointment', $appointment_id, json_encode($appointment_data));

            // Send notification if status changed
            $this->send_appointment_notification($appointment_id, 'updated');
            
            return true;
        }
        return false;
    }

    /**
     * Cancel an appointment by updating status to 'cancelled'.
     */
    public function cancel_appointment($appointment_id) {
        $result = $this->update_appointment($appointment_id, array('status' => 'cancelled'));
        
        if ($result) {
            HMS_Helper::log_action('Appointment Cancelled', 'Appointment', $appointment_id);
            return true;
        }
        return false;
    }

    /**
     * Delete an appointment.
     */
    public function delete_appointment($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        
        $result = $wpdb->delete($table_name, array('id' => $appointment_id), array('%d'));
        
        if ($result) {
            HMS_Helper::log_action('Appointment Deleted', 'Appointment', $appointment_id);
            return true;
        }
        return false;
    }

    /**
     * Get appointments for a specific patient.
     *
     * @param int $patient_id The patient's database ID.
     * @return array An array of appointment objects.
     */
    public function get_patient_appointments($patient_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE patient_id = %d 
             ORDER BY appointment_date DESC, appointment_time DESC",
            $patient_id
        );
        
        return $wpdb->get_results($query);
    }

    /**
     * AJAX handler to add a new appointment.
     */
    public function ajax_add_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
            wp_send_json_error(__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'doctor_id' => intval($_POST['doctor_id']),
            'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'status' => !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'scheduled',
            'reason_for_visit' => sanitize_textarea_field($_POST['reason_for_visit']),
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $appointment_id = $this->add_appointment($data);

        if ($appointment_id) {
            $appointment = $this->get_appointment($appointment_id);
            wp_send_json_success(array(
                'message' => __('Appointment scheduled successfully.', 'hospital-management-system'),
                'appointment' => $appointment,
                'redirect_url' => admin_url('admin.php?page=hms_appointments&message=' . urlencode(__('Appointment scheduled successfully.', 'hospital-management-system')) . '&type=success')
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $appointment_id = intval($_POST['appointment_id']);
        
        // Basic server-side validation
        if (empty($_POST['patient_id']) || empty($_POST['doctor_id']) || empty($_POST['appointment_date']) || empty($_POST['appointment_time'])) {
            wp_send_json_error(__('Please fill in all required fields.', 'hospital-management-system'));
        }

        $data = array(
            'patient_id' => intval($_POST['patient_id']),
            'doctor_id' => intval($_POST['doctor_id']),
            'department_id' => !empty($_POST['department_id']) ? intval($_POST['department_id']) : null,
            'appointment_date' => sanitize_text_field($_POST['appointment_date']),
            'appointment_time' => sanitize_text_field($_POST['appointment_time']),
            'status' => sanitize_text_field($_POST['status']),
            'reason_for_visit' => sanitize_textarea_field($_POST['reason_for_visit']),
            'notes' => sanitize_textarea_field($_POST['notes']),
        );

        $updated = $this->update_appointment($appointment_id, $data);

        if ($updated) {
            $appointment = $this->get_appointment($appointment_id);
            wp_send_json_success(array(
                'message' => __('Appointment updated successfully.', 'hospital-management-system'),
                'appointment' => $appointment,
                'redirect_url' => admin_url('admin.php?page=hms_appointments&message=' . urlencode(__('Appointment updated successfully.', 'hospital-management-system')) . '&type=success')
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $appointment_id = intval($_POST['appointment_id']);
        $cancelled = $this->cancel_appointment($appointment_id);

        if ($cancelled) {
            wp_send_json_success(array(
                'message' => __('Appointment cancelled successfully.', 'hospital-management-system')
            ));
        } else {
            wp_send_json_error(__('Failed to cancel appointment.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to delete an appointment.
     */
    public function ajax_delete_appointment() {
        check_ajax_referer('hms_delete_appointment_nonce', 'delete_nonce'); // Different nonce for delete
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $appointment_id = intval($_POST['appointment_id']);
        $deleted = $this->delete_appointment($appointment_id);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => __('Appointment deleted successfully.', 'hospital-management-system'),
                'redirect_url' => admin_url('admin.php?page=hms_appointments&message=' . urlencode(__('Appointment deleted successfully.', 'hospital-management-system')) . '&type=success')
            ));
        } else {
            wp_send_json_error(__('Failed to delete appointment.', 'hospital-management-system'));
        }
    }

    /**
     * AJAX handler to get a single appointment's details.
     */
    public function ajax_get_appointment() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
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
            wp_send_json_error(__('You do not have permission to perform this action.', 'hospital-management-system'));
        }

        $args = array(
            'doctor_id' => isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0,
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '',
            'start_date' => isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '',
            'end_date' => isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '',
            'offset' => isset($_GET['start']) ? intval($_GET['start']) : 0,
            'per_page' => isset($_GET['length']) ? intval($_GET['length']) : 20,
        );
        
        $appointments = $this->get_appointments($args);
        $total_appointments = $this->get_appointments_count($args);
        
        $data = array();
        foreach ($appointments as $appointment) {
            // Get patient and doctor names from the custom tables
            $patient = HMS_Helper::hms_get_row('hms_patients', array('id' => $appointment->patient_id), array('%d'));
            $doctor = HMS_Helper::hms_get_row('hms_staff', array('id' => $appointment->doctor_id), array('%d'));
            $department = $appointment->department_id ? HMS_Helper::hms_get_row('hms_departments', array('id' => $appointment->department_id), array('%d')) : null;
            
            $patient_name = $patient ? $patient->first_name . ' ' . $patient->last_name : __('N/A', 'hospital-management-system');
            $doctor_name = $doctor ? $doctor->first_name . ' ' . $doctor->last_name : __('N/A', 'hospital-management-system');
            $department_name = $department ? $department->name : __('N/A', 'hospital-management-system');
            
            $data[] = array(
                'id' => $appointment->id,
                'date' => date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)),
                'time' => date_i18n(get_option('time_format'), strtotime($appointment->appointment_time)),
                'patient' => $patient_name,
                'doctor' => $doctor_name,
                'department' => $department_name,
                'status' => ucfirst(str_replace('-', ' ', $appointment->status)),
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
     * Send notification for appointment creation/update.
     * This is a placeholder and can be expanded with email/SMS logic.
     */
    private function send_appointment_notification($appointment_id, $action) {
        // TODO: Implement email/SMS notifications
        // For now, we'll just log it.
        HMS_Helper::log_action("Appointment Notification Sent ($action)", 'Appointment', $appointment_id);
        
        // Make sure no output is generated during notification sending
        if (ob_get_level()) {
            ob_clean();
        }
    }

    /**
     * AJAX handler to get appointments for a specific patient.
     */
    public function ajax_get_patient_appointments() {
        check_ajax_referer('hms_admin_nonce', 'nonce');
        if (!current_user_can('manage_appointments')) {
            wp_send_json_error(esc_html__('You do not have permission to perform this action.', 'hospital-management-system'), 403);
        }

        $patient_id = intval($_POST['patient_id']);
        if ($patient_id <= 0) {
            wp_send_json_error(esc_html__('Invalid patient ID.', 'hospital-management-system'));
        }

        $appointments = $this->get_patient_appointments($patient_id);
        
        wp_send_json_success(array(
            'appointments' => $appointments
        ));
    }
}
