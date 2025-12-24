<?php
/**
 * Setup Prescription Test Data Script
 *
 * This script creates sample prescription data for testing purposes.
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
    <h1><?php esc_html_e('Setup Prescription Test Data', 'hospital-management-system'); ?></h1>
    
    <div class="notice notice-warning">
        <p><strong><?php esc_html_e('Warning:', 'hospital-management-system'); ?></strong> 
        <?php esc_html_e('This tool creates sample data for testing purposes. Use only on development or testing environments.', 'hospital-management-system'); ?></p>
    </div>

    <?php
    global $wpdb;
    
    $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';
    $patients_table = $wpdb->prefix . 'hms_patients';
    $staff_table = $wpdb->prefix . 'hms_staff';
    $medicines_table = $wpdb->prefix . 'hms_medicines';
    
    // Handle form submission
    if (isset($_POST['create_test_data']) && wp_verify_nonce($_POST['test_data_nonce'], 'hms_create_test_data')) {
        
        echo '<div class="notice notice-info"><p>' . esc_html__('Creating prescription test data...', 'hospital-management-system') . '</p></div>';
        
        try {
            $created_count = 0;
            $errors = array();
            
            // Sample prescription data
            $sample_prescriptions = array(
                array(
                    'patient_id' => 1,
                    'doctor_id' => 1,
                    'medicine_name' => 'Amoxicillin 500mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Three times daily',
                    'duration' => '7 days',
                    'instructions' => 'Take with food. Complete the full course.',
                    'diagnosis_notes' => 'Bacterial infection',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 1,
                    'doctor_id' => 1,
                    'medicine_name' => 'Paracetamol 500mg',
                    'dosage' => '1-2 tablets',
                    'frequency' => 'Every 6 hours as needed',
                    'duration' => '5 days',
                    'instructions' => 'For fever and pain relief. Do not exceed 8 tablets in 24 hours.',
                    'diagnosis_notes' => 'Fever and body ache',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 2,
                    'doctor_id' => 2,
                    'medicine_name' => 'Lisinopril 10mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Once daily',
                    'duration' => '30 days',
                    'instructions' => 'Take at the same time each day. Monitor blood pressure.',
                    'diagnosis_notes' => 'Hypertension',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 2,
                    'doctor_id' => 2,
                    'medicine_name' => 'Metformin 500mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Twice daily',
                    'duration' => '30 days',
                    'instructions' => 'Take with meals. Monitor blood sugar levels.',
                    'diagnosis_notes' => 'Type 2 Diabetes',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 3,
                    'doctor_id' => 1,
                    'medicine_name' => 'Omeprazole 20mg',
                    'dosage' => '1 capsule',
                    'frequency' => 'Once daily before breakfast',
                    'duration' => '14 days',
                    'instructions' => 'Take 30 minutes before eating. Swallow whole.',
                    'diagnosis_notes' => 'Gastroesophageal reflux disease (GERD)',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 3,
                    'doctor_id' => 1,
                    'medicine_name' => 'Cetirizine 10mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Once daily at bedtime',
                    'duration' => '10 days',
                    'instructions' => 'May cause drowsiness. Avoid alcohol.',
                    'diagnosis_notes' => 'Allergic rhinitis',
                    'status' => 'completed'
                ),
                array(
                    'patient_id' => 4,
                    'doctor_id' => 3,
                    'medicine_name' => 'Atorvastatin 20mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Once daily at bedtime',
                    'duration' => '30 days',
                    'instructions' => 'Take with or without food. Regular liver function tests required.',
                    'diagnosis_notes' => 'High cholesterol',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 4,
                    'doctor_id' => 3,
                    'medicine_name' => 'Aspirin 75mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Once daily',
                    'duration' => '30 days',
                    'instructions' => 'Take with food to reduce stomach irritation.',
                    'diagnosis_notes' => 'Cardiovascular protection',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 5,
                    'doctor_id' => 2,
                    'medicine_name' => 'Salbutamol Inhaler',
                    'dosage' => '2 puffs',
                    'frequency' => 'As needed for breathlessness',
                    'duration' => '30 days',
                    'instructions' => 'Shake well before use. Rinse mouth after use.',
                    'diagnosis_notes' => 'Asthma',
                    'status' => 'active'
                ),
                array(
                    'patient_id' => 5,
                    'doctor_id' => 2,
                    'medicine_name' => 'Prednisolone 5mg',
                    'dosage' => '2 tablets',
                    'frequency' => 'Once daily in the morning',
                    'duration' => '5 days',
                    'instructions' => 'Take with food. Do not stop suddenly.',
                    'diagnosis_notes' => 'Asthma exacerbation',
                    'status' => 'completed'
                )
            );
            
            foreach ($sample_prescriptions as $prescription) {
                // Add timestamps
                $prescription['created_at'] = current_time('mysql');
                $prescription['updated_at'] = current_time('mysql');
                
                // Add random dates within the last 30 days
                $random_days = rand(1, 30);
                $prescription['prescribed_date'] = date('Y-m-d', strtotime("-$random_days days"));
                
                $insert_result = $wpdb->insert($prescriptions_table, $prescription);
                
                if ($insert_result !== false) {
                    $created_count++;
                } else {
                    $errors[] = sprintf(
                        /* translators: %1$s: medicine name, %2$s: error message */
                        esc_html__('Failed to create prescription for %1$s: %2$s', 'hospital-management-system'),
                        $prescription['medicine_name'],
                        $wpdb->last_error
                    );
                }
            }
            
            if (empty($errors)) {
                echo '<div class="notice notice-success"><p>' . 
                     sprintf(
                         /* translators: %d: number of prescriptions created */
                         esc_html__('Successfully created %d test prescriptions.', 'hospital-management-system'),
                         $created_count
                     ) . 
                     '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>' . 
                     sprintf(
                         /* translators: %d: number of prescriptions created */
                         esc_html__('Created %d prescriptions, but encountered some errors:', 'hospital-management-system'),
                         $created_count
                     ) . 
                     '</p><ul>';
                foreach ($errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul></div>';
            }
            
            // Log the operation
            error_log('HMS: Prescription test data setup completed. Created: ' . $created_count . ' prescriptions');
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(
                     /* translators: %s: error message */
                     esc_html__('Test data creation failed with error: %s', 'hospital-management-system'),
                     esc_html($e->getMessage())
                 ) . 
                 '</p></div>';
            
            // Log the error
            error_log('HMS: Prescription test data setup failed: ' . $e->getMessage());
        }
    }
    
    // Handle cleanup
    if (isset($_POST['cleanup_test_data']) && wp_verify_nonce($_POST['cleanup_nonce'], 'hms_cleanup_test_data')) {
        
        echo '<div class="notice notice-info"><p>' . esc_html__('Cleaning up test prescription data...', 'hospital-management-system') . '</p></div>';
        
        try {
            // Delete test prescriptions (those with common test medicine names)
            $test_medicines = array(
                'Amoxicillin 500mg',
                'Paracetamol 500mg',
                'Lisinopril 10mg',
                'Metformin 500mg',
                'Omeprazole 20mg',
                'Cetirizine 10mg',
                'Atorvastatin 20mg',
                'Aspirin 75mg',
                'Salbutamol Inhaler',
                'Prednisolone 5mg'
            );
            
            $placeholders = implode(',', array_fill(0, count($test_medicines), '%s'));
            $delete_query = "DELETE FROM $prescriptions_table WHERE medicine_name IN ($placeholders)";
            $delete_result = $wpdb->query($wpdb->prepare($delete_query, $test_medicines));
            
            if ($delete_result !== false) {
                echo '<div class="notice notice-success"><p>' . 
                     sprintf(
                         /* translators: %d: number of prescriptions deleted */
                         esc_html__('Successfully deleted %d test prescriptions.', 'hospital-management-system'),
                         $delete_result
                     ) . 
                     '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . 
                     esc_html__('Failed to delete test prescriptions: ', 'hospital-management-system') . 
                     esc_html($wpdb->last_error) . 
                     '</p></div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . 
                 sprintf(
                     /* translators: %s: error message */
                     esc_html__('Cleanup failed with error: %s', 'hospital-management-system'),
                     esc_html($e->getMessage())
                 ) . 
                 '</p></div>';
        }
    }
    
    // Get current prescription statistics
    $total_prescriptions = $wpdb->get_var("SELECT COUNT(*) FROM $prescriptions_table");
    $active_prescriptions = $wpdb->get_var("SELECT COUNT(*) FROM $prescriptions_table WHERE status = 'active'");
    $recent_prescriptions = $wpdb->get_var("SELECT COUNT(*) FROM $prescriptions_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)");
    ?>
    
    <!-- Statistics Cards -->
    <div class="stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #0073aa;"><?php echo number_format($total_prescriptions); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Total Prescriptions', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php echo number_format($active_prescriptions); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Active Prescriptions', 'hospital-management-system'); ?></p>
        </div>
        
        <div class="stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #f56e28;"><?php echo number_format($recent_prescriptions); ?></h3>
            <p style="margin: 0; color: #666;"><?php esc_html_e('Recent (7 days)', 'hospital-management-system'); ?></p>
        </div>
    </div>
    
    <!-- Create Test Data Form -->
    <div class="create-form" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <h2><?php esc_html_e('Create Test Prescription Data', 'hospital-management-system'); ?></h2>
        <p><?php esc_html_e('This will create 10 sample prescriptions with realistic medical data for testing purposes.', 'hospital-management-system'); ?></p>
        
        <div style="margin-bottom: 20px; padding: 15px; background: #f0f6ff; border-left: 4px solid #0073aa;">
            <h4><?php esc_html_e('Test data includes:', 'hospital-management-system'); ?></h4>
            <ul>
                <li><?php esc_html_e('Common medications with proper dosages', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Realistic prescription instructions', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Various medical conditions and diagnoses', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Different prescription statuses (active, completed)', 'hospital-management-system'); ?></li>
                <li><?php esc_html_e('Random prescription dates within the last 30 days', 'hospital-management-system'); ?></li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('hms_create_test_data', 'test_data_nonce'); ?>
            <p>
                <input type="submit" name="create_test_data" class="button button-primary" 
                       value="<?php esc_attr_e('Create Test Prescriptions', 'hospital-management-system'); ?>" 
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to create test prescription data? This will add sample prescriptions to your database.', 'hospital-management-system'); ?>');">
            </p>
        </form>
    </div>
    
    <!-- Cleanup Form -->
    <div class="cleanup-form" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
        <h2><?php esc_html_e('Cleanup Test Data', 'hospital-management-system'); ?></h2>
        <p><?php esc_html_e('Remove all test prescription data created by this tool.', 'hospital-management-system'); ?></p>
        
        <div style="margin-bottom: 20px; padding: 15px; background: #fdf2f2; border-left: 4px solid #d63638;">
            <h4 style="color: #d63638;"><?php esc_html_e('Warning:', 'hospital-management-system'); ?></h4>
            <p><?php esc_html_e('This will permanently delete all prescriptions with the following medicine names:', 'hospital-management-system'); ?></p>
            <ul style="columns: 2; column-gap: 20px;">
                <li>Amoxicillin 500mg</li>
                <li>Paracetamol 500mg</li>
                <li>Lisinopril 10mg</li>
                <li>Metformin 500mg</li>
                <li>Omeprazole 20mg</li>
                <li>Cetirizine 10mg</li>
                <li>Atorvastatin 20mg</li>
                <li>Aspirin 75mg</li>
                <li>Salbutamol Inhaler</li>
                <li>Prednisolone 5mg</li>
            </ul>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('hms_cleanup_test_data', 'cleanup_nonce'); ?>
            <p>
                <input type="submit" name="cleanup_test_data" class="button button-secondary" 
                       value="<?php esc_attr_e('Delete Test Prescriptions', 'hospital-management-system'); ?>" 
                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete all test prescription data? This action cannot be undone.', 'hospital-management-system'); ?>');">
            </p>
        </form>
    </div>
    
    <!-- Information Section -->
    <div class="info-section" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
        <h2><?php esc_html_e('About This Tool', 'hospital-management-system'); ?></h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <h4><?php esc_html_e('Purpose:', 'hospital-management-system'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Test prescription management features', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Demonstrate system capabilities', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Provide realistic data for development', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Train staff on system usage', 'hospital-management-system'); ?></li>
                </ul>
            </div>
            
            <div>
                <h4><?php esc_html_e('Best Practices:', 'hospital-management-system'); ?></h4>
                <ul>
                    <li><?php esc_html_e('Use only on development/testing environments', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Clean up test data before going live', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Ensure patient and staff records exist first', 'hospital-management-system'); ?></li>
                    <li><?php esc_html_e('Review created data for accuracy', 'hospital-management-system'); ?></li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
            <h4><?php esc_html_e('Requirements:', 'hospital-management-system'); ?></h4>
            <p><?php esc_html_e('This tool requires existing patient and staff records in your database. The test prescriptions will reference patient IDs 1-5 and doctor IDs 1-3. Make sure these records exist before creating test data.', 'hospital-management-system'); ?></p>
        </div>
    </div>
</div>

<style>
.stats-cards {
    margin: 20px 0;
}

.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    ul[style*="columns"] {
        columns: 1 !important;
    }
}
</style>