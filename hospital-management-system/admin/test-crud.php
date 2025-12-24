<?php
/**
 * CRUD Operations Test Script
 *
 * This script tests Create, Read, Update, Delete operations
 * for the Hospital Management System database tables.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if user has permission
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

?>

<div class="wrap">
    <h1><?php esc_html_e('CRUD Operations Test', 'hospital-management-system'); ?></h1>
    
    <div class="notice notice-warning">
        <p><strong><?php esc_html_e('Warning:', 'hospital-management-system'); ?></strong> 
        <?php esc_html_e('This is a testing tool for developers. Use with caution on production sites.', 'hospital-management-system'); ?></p>
    </div>

    <?php
    global $wpdb;
    
    $test_results = array();
    $overall_success = true;
    
    // Handle form submission
    if (isset($_POST['run_crud_tests']) && wp_verify_nonce($_POST['crud_test_nonce'], 'hms_crud_test')) {
        
        echo '<div class="notice notice-info"><p>' . esc_html__('Running CRUD tests...', 'hospital-management-system') . '</p></div>';
        
        // Test 1: Patients Table CRUD
        $test_results['patients'] = test_patients_crud();
        
        // Test 2: Staff Table CRUD
        $test_results['staff'] = test_staff_crud();
        
        // Test 3: Appointments Table CRUD
        $test_results['appointments'] = test_appointments_crud();
        
        // Test 4: Bills Table CRUD
        $test_results['bills'] = test_bills_crud();
        
        // Check overall success
        foreach ($test_results as $test) {
            if (!$test['success']) {
                $overall_success = false;
                break;
            }
        }
        
        if ($overall_success) {
            echo '<div class="notice notice-success"><p>' . esc_html__('All CRUD tests passed successfully!', 'hospital-management-system') . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html__('Some CRUD tests failed. Please check the results below.', 'hospital-management-system') . '</p></div>';
        }
    }
    
    /**
     * Test Patients table CRUD operations
     */
    function test_patients_crud() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';
        $test_result = array('success' => true, 'operations' => array());
        
        try {
            // CREATE
            $insert_data = array(
                'patient_id' => 'TEST_' . time(),
                'first_name' => 'Test',
                'last_name' => 'Patient',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'address' => 'Test Address',
                'created_at' => current_time('mysql')
            );
            
            $insert_result = $wpdb->insert($table_name, $insert_data);
            $test_id = $wpdb->insert_id;
            
            $test_result['operations']['create'] = array(
                'success' => $insert_result !== false,
                'message' => $insert_result !== false ? 'Patient created successfully' : 'Failed to create patient: ' . $wpdb->last_error,
                'id' => $test_id
            );
            
            if ($insert_result === false) {
                $test_result['success'] = false;
                return $test_result;
            }
            
            // READ
            $read_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $test_id));
            $test_result['operations']['read'] = array(
                'success' => $read_result !== null,
                'message' => $read_result !== null ? 'Patient read successfully' : 'Failed to read patient',
                'data' => $read_result
            );
            
            if ($read_result === null) {
                $test_result['success'] = false;
            }
            
            // UPDATE
            $update_data = array('first_name' => 'Updated Test');
            $update_result = $wpdb->update($table_name, $update_data, array('id' => $test_id));
            
            $test_result['operations']['update'] = array(
                'success' => $update_result !== false,
                'message' => $update_result !== false ? 'Patient updated successfully' : 'Failed to update patient: ' . $wpdb->last_error
            );
            
            if ($update_result === false) {
                $test_result['success'] = false;
            }
            
            // DELETE
            $delete_result = $wpdb->delete($table_name, array('id' => $test_id));
            $test_result['operations']['delete'] = array(
                'success' => $delete_result !== false,
                'message' => $delete_result !== false ? 'Patient deleted successfully' : 'Failed to delete patient: ' . $wpdb->last_error
            );
            
            if ($delete_result === false) {
                $test_result['success'] = false;
            }
            
        } catch (Exception $e) {
            $test_result['success'] = false;
            $test_result['error'] = $e->getMessage();
        }
        
        return $test_result;
    }
    
    /**
     * Test Staff table CRUD operations
     */
    function test_staff_crud() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_staff';
        $test_result = array('success' => true, 'operations' => array());
        
        try {
            // CREATE
            $insert_data = array(
                'staff_id' => 'STAFF_TEST_' . time(),
                'first_name' => 'Test',
                'last_name' => 'Doctor',
                'email' => 'testdoctor@example.com',
                'phone' => '0987654321',
                'role' => 'doctor',
                'department' => 'general',
                'created_at' => current_time('mysql')
            );
            
            $insert_result = $wpdb->insert($table_name, $insert_data);
            $test_id = $wpdb->insert_id;
            
            $test_result['operations']['create'] = array(
                'success' => $insert_result !== false,
                'message' => $insert_result !== false ? 'Staff created successfully' : 'Failed to create staff: ' . $wpdb->last_error,
                'id' => $test_id
            );
            
            if ($insert_result === false) {
                $test_result['success'] = false;
                return $test_result;
            }
            
            // READ
            $read_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $test_id));
            $test_result['operations']['read'] = array(
                'success' => $read_result !== null,
                'message' => $read_result !== null ? 'Staff read successfully' : 'Failed to read staff'
            );
            
            // UPDATE
            $update_data = array('department' => 'cardiology');
            $update_result = $wpdb->update($table_name, $update_data, array('id' => $test_id));
            
            $test_result['operations']['update'] = array(
                'success' => $update_result !== false,
                'message' => $update_result !== false ? 'Staff updated successfully' : 'Failed to update staff: ' . $wpdb->last_error
            );
            
            // DELETE
            $delete_result = $wpdb->delete($table_name, array('id' => $test_id));
            $test_result['operations']['delete'] = array(
                'success' => $delete_result !== false,
                'message' => $delete_result !== false ? 'Staff deleted successfully' : 'Failed to delete staff: ' . $wpdb->last_error
            );
            
            if ($read_result === null || $update_result === false || $delete_result === false) {
                $test_result['success'] = false;
            }
            
        } catch (Exception $e) {
            $test_result['success'] = false;
            $test_result['error'] = $e->getMessage();
        }
        
        return $test_result;
    }
    
    /**
     * Test Appointments table CRUD operations
     */
    function test_appointments_crud() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        $test_result = array('success' => true, 'operations' => array());
        
        try {
            // CREATE
            $insert_data = array(
                'patient_id' => 1, // Assuming patient ID 1 exists
                'doctor_id' => 1,  // Assuming doctor ID 1 exists
                'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                'appointment_time' => '10:00:00',
                'status' => 'scheduled',
                'notes' => 'Test appointment',
                'created_at' => current_time('mysql')
            );
            
            $insert_result = $wpdb->insert($table_name, $insert_data);
            $test_id = $wpdb->insert_id;
            
            $test_result['operations']['create'] = array(
                'success' => $insert_result !== false,
                'message' => $insert_result !== false ? 'Appointment created successfully' : 'Failed to create appointment: ' . $wpdb->last_error,
                'id' => $test_id
            );
            
            if ($insert_result === false) {
                $test_result['success'] = false;
                return $test_result;
            }
            
            // READ
            $read_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $test_id));
            $test_result['operations']['read'] = array(
                'success' => $read_result !== null,
                'message' => $read_result !== null ? 'Appointment read successfully' : 'Failed to read appointment'
            );
            
            // UPDATE
            $update_data = array('status' => 'completed');
            $update_result = $wpdb->update($table_name, $update_data, array('id' => $test_id));
            
            $test_result['operations']['update'] = array(
                'success' => $update_result !== false,
                'message' => $update_result !== false ? 'Appointment updated successfully' : 'Failed to update appointment: ' . $wpdb->last_error
            );
            
            // DELETE
            $delete_result = $wpdb->delete($table_name, array('id' => $test_id));
            $test_result['operations']['delete'] = array(
                'success' => $delete_result !== false,
                'message' => $delete_result !== false ? 'Appointment deleted successfully' : 'Failed to delete appointment: ' . $wpdb->last_error
            );
            
            if ($read_result === null || $update_result === false || $delete_result === false) {
                $test_result['success'] = false;
            }
            
        } catch (Exception $e) {
            $test_result['success'] = false;
            $test_result['error'] = $e->getMessage();
        }
        
        return $test_result;
    }
    
    /**
     * Test Bills table CRUD operations
     */
    function test_bills_crud() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        $test_result = array('success' => true, 'operations' => array());
        
        try {
            // CREATE
            $insert_data = array(
                'bill_id' => 'BILL_TEST_' . time(),
                'patient_id' => 1, // Assuming patient ID 1 exists
                'total_amount' => 100.00,
                'due_amount' => 100.00,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            );
            
            $insert_result = $wpdb->insert($table_name, $insert_data);
            $test_id = $wpdb->insert_id;
            
            $test_result['operations']['create'] = array(
                'success' => $insert_result !== false,
                'message' => $insert_result !== false ? 'Bill created successfully' : 'Failed to create bill: ' . $wpdb->last_error,
                'id' => $test_id
            );
            
            if ($insert_result === false) {
                $test_result['success'] = false;
                return $test_result;
            }
            
            // READ
            $read_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $test_id));
            $test_result['operations']['read'] = array(
                'success' => $read_result !== null,
                'message' => $read_result !== null ? 'Bill read successfully' : 'Failed to read bill'
            );
            
            // UPDATE
            $update_data = array('status' => 'paid', 'due_amount' => 0.00);
            $update_result = $wpdb->update($table_name, $update_data, array('id' => $test_id));
            
            $test_result['operations']['update'] = array(
                'success' => $update_result !== false,
                'message' => $update_result !== false ? 'Bill updated successfully' : 'Failed to update bill: ' . $wpdb->last_error
            );
            
            // DELETE
            $delete_result = $wpdb->delete($table_name, array('id' => $test_id));
            $test_result['operations']['delete'] = array(
                'success' => $delete_result !== false,
                'message' => $delete_result !== false ? 'Bill deleted successfully' : 'Failed to delete bill: ' . $wpdb->last_error
            );
            
            if ($read_result === null || $update_result === false || $delete_result === false) {
                $test_result['success'] = false;
            }
            
        } catch (Exception $e) {
            $test_result['success'] = false;
            $test_result['error'] = $e->getMessage();
        }
        
        return $test_result;
    }
    ?>

    <!-- Test Results Display -->
    <?php if (!empty($test_results)): ?>
        <div class="test-results">
            <h2><?php esc_html_e('Test Results', 'hospital-management-system'); ?></h2>
            
            <?php foreach ($test_results as $table_name => $result): ?>
                <div class="test-result-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px; overflow: hidden;">
                    <div class="test-header" style="padding: 15px 20px; background: <?php echo $result['success'] ? '#f0f8f0' : '#fdf2f2'; ?>; border-bottom: 1px solid #ccd0d4;">
                        <h3 style="margin: 0; color: #23282d;">
                            <span class="dashicons <?php echo $result['success'] ? 'dashicons-yes-alt' : 'dashicons-dismiss'; ?>" 
                                  style="color: <?php echo $result['success'] ? '#00a32a' : '#d63638'; ?>; margin-right: 8px;"></span>
                            <?php echo esc_html(ucwords($table_name)); ?> Table CRUD Test
                        </h3>
                        <?php if (isset($result['error'])): ?>
                            <p style="margin: 5px 0 0 0; color: #d63638; font-size: 14px;">
                                <strong><?php esc_html_e('Error:', 'hospital-management-system'); ?></strong> 
                                <?php echo esc_html($result['error']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="test-operations" style="padding: 20px;">
                        <?php if (isset($result['operations'])): ?>
                            <div class="operations-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <?php foreach ($result['operations'] as $operation => $op_result): ?>
                                    <div class="operation-result" style="padding: 15px; border: 1px solid #ccd0d4; border-radius: 4px; background: <?php echo $op_result['success'] ? '#f9f9f9' : '#fdf2f2'; ?>;">
                                        <h4 style="margin: 0 0 8px 0; color: #23282d; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;">
                                            <span class="dashicons <?php echo $op_result['success'] ? 'dashicons-yes' : 'dashicons-no'; ?>" 
                                                  style="color: <?php echo $op_result['success'] ? '#00a32a' : '#d63638'; ?>; font-size: 16px; margin-right: 5px;"></span>
                                            <?php echo esc_html($operation); ?>
                                        </h4>
                                        <p style="margin: 0; font-size: 13px; color: #666;">
                                            <?php echo esc_html($op_result['message']); ?>
                                        </p>
                                        <?php if (isset($op_result['id'])): ?>
                                            <p style="margin: 5px 0 0 0; font-size: 11px; color: #999;">
                                                ID: <?php echo esc_html($op_result['id']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Test Form -->
    <div class="test-form-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-top: 20px;">
        <h2><?php esc_html_e('Run CRUD Tests', 'hospital-management-system'); ?></h2>
        <p><?php esc_html_e('This will test Create, Read, Update, and Delete operations on the main database tables. Test data will be created and then cleaned up automatically.', 'hospital-management-system'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('hms_crud_test', 'crud_test_nonce'); ?>
            <p>
                <input type="submit" name="run_crud_tests" class="button button-primary" 
                       value="<?php esc_attr_e('Run CRUD Tests', 'hospital-management-system'); ?>" 
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to run CRUD tests? This will create and delete test data.', 'hospital-management-system'); ?>');">
            </p>
        </form>
        
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa;">
            <h4><?php esc_html_e('What these tests do:', 'hospital-management-system'); ?></h4>
            <ul>
                <li><?php esc_html_e('Create test records in each table', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Read the created records to verify they exist', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Update the records with new data', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Delete the test records to clean up', 'hospital-management-system'); ?></li>
            </ul>
            <p><strong><?php esc_html_e('Note:', 'hospital-management-system'); ?></strong> 
            <?php esc_html_e('All test data is automatically cleaned up after testing. No permanent data is left in your database.', 'hospital-management-system'); ?></p>
        </div>
    </div>
</div>

<style>
.test-result-card {
    transition: box-shadow 0.2s ease;
}

.test-result-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.operations-grid {
    margin-top: 15px;
}

.operation-result {
    transition: transform 0.2s ease;
}

.operation-result:hover {
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .operations-grid {
        grid-template-columns: 1fr;
    }
}
</style>