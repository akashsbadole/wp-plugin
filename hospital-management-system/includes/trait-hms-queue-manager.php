<?php
/**
 * HMS Queue Manager Trait
 *
 * Handles OPD queue management, token generation, and queue status updates.
 */

trait HMS_Queue_Manager {

    /**
     * Adds a patient to the OPD queue and generates a token.
     *
     * @param int $patient_id The ID of the patient.
     * @param int $doctor_id The ID of the doctor for the queue.
     * @return string|false The generated token number or false on failure.
     */
    public function add_patient_to_queue($patient_id, $doctor_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_queue';
        $today = current_time('Y-m-d');

        // Generate a unique token number for the day for the specific doctor
        $last_token = $wpdb->get_row($wpdb->prepare(
            "SELECT token_number FROM $table_name WHERE doctor_id = %d AND queue_date = %s ORDER BY CAST(token_number AS UNSIGNED) DESC LIMIT 1",
            $doctor_id, $today
        ));

        $next_token_number = ($last_token) ? intval($last_token->token_number) + 1 : 1;
        $token_number = str_pad($next_token_number, 3, '0', STR_PAD_LEFT); // e.g., 001, 002

        $queue_data = array(
            'patient_id' => $patient_id,
            'doctor_id' => $doctor_id,
            'token_number' => $token_number,
            'status' => 'waiting',
            'queue_date' => $today,
            'created_at' => current_time('mysql'),
        );

        $format = array('%d', '%d', '%s', '%s', '%s', '%s');

        $result = $wpdb->insert($table_name, $queue_data, $format);

        if ($result) {
            // Log the action
            HMS_Helper::log_action('Patient Added to Queue', 'Queue', $wpdb->insert_id, json_encode($queue_data));
            return $token_number;
        }

        return false;
    }

    /**
     * Updates the status of a patient in the queue.
     *
     * @param int $queue_id The ID of the queue entry.
     * @param string $status The new status (e.g., 'in-progress', 'completed').
     * @return bool True on success, false on failure.
     */
    public function update_queue_status($queue_id, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_queue';

        $update_data = array('status' => $status);
        
        // Set timestamp based on status
        if ($status === 'in-progress') {
            $update_data['called_at'] = current_time('mysql');
        } elseif ($status === 'completed') {
            $update_data['completed_at'] = current_time('mysql');
        }

        $where = array('id' => $queue_id);
        $format = array('%s');
        $where_format = array('%d');

        $result = $wpdb->update($table_name, $update_data, $where, $format, $where_format);

        if ($result !== false) {
            // Log the action
            HMS_Helper::log_action('Queue Status Updated', 'Queue', $queue_id, json_encode(array('status' => $status)));
            return true;
        }

        return false;
    }

    /**
     * Gets the current queue for a specific doctor for today.
     *
     * @param int $doctor_id The ID of the doctor.
     * @return array List of patients in the queue.
     */
    public function get_current_queue($doctor_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_queue';
        $today = current_time('Y-m-d');

        $query = $wpdb->prepare(
            "SELECT q.*, p.first_name, p.last_name, p.patient_id as unique_patient_id 
             FROM $table_name q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.doctor_id = %d AND q.queue_date = %s 
             ORDER BY CAST(q.token_number AS UNSIGNED) ASC",
            $doctor_id, $today
        );

        return $wpdb->get_results($query);
    }

    /**
     * Gets the next patient in the queue for a specific doctor.
     *
     * @param int $doctor_id The ID of the doctor.
     * @return object|false The next patient's queue entry or false if queue is empty.
     */
    public function get_next_in_queue($doctor_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_queue';
        $today = current_time('Y-m-d');

        $query = $wpdb->prepare(
            "SELECT q.*, p.first_name, p.last_name, p.patient_id as unique_patient_id 
             FROM $table_name q 
             JOIN {$wpdb->prefix}hms_patients p ON q.patient_id = p.id 
             WHERE q.doctor_id = %d AND q.queue_date = %s AND q.status = 'waiting' 
             ORDER BY CAST(q.token_number AS UNSIGNED) ASC 
             LIMIT 1",
            $doctor_id, $today
        );

        return $wpdb->get_row($query);
    }
}
