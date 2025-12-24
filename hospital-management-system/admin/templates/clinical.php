<?php
/**
 * Clinical Management Template - Enhanced Clinical Dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_clinical')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

global $wpdb;

// Initialize clinical features
$clinical_features = new HMS_Clinical_Features('hospital-management-system', '1.0');

// Get selected patient ID if any
$selected_patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Get statistics
$patients_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hms_patients");
$appointments_today = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}hms_appointments WHERE DATE(appointment_date) = %s",
    current_time('Y-m-d')
));
$pending_prescriptions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}hms_prescriptions WHERE status = 'pending'");
$queue_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->prefix}hms_queue WHERE queue_date = %s AND status = 'waiting'",
    current_time('Y-m-d')
));

// Get clinical data
$pending_tasks = $clinical_features->get_pending_tasks();
$clinical_alerts = $clinical_features->get_clinical_alerts();
$recent_activities = $clinical_features->get_recent_activities(array('limit' => 10));

// Get patient summary if patient is selected
$patient_summary = array();
if ($selected_patient_id) {
    $patient_summary = $clinical_features->get_patient_summary($selected_patient_id);
}

// Get all patients for dropdown
$all_patients = $wpdb->get_results("SELECT id, first_name, last_name FROM {$wpdb->prefix}hms_patients ORDER BY first_name, last_name");

?>

<div class="wrap">
    <h1><?php _e('Clinical Dashboard', 'hospital-management-system'); ?></h1>
    
    <!-- Clinical Alerts -->
    <?php if (!empty($clinical_alerts)): ?>
    <div class="hms-clinical-alerts" style="margin: 20px 0;">
        <?php foreach ($clinical_alerts as $alert): ?>
            <div class="notice notice-<?php echo $alert['priority'] === 'high' ? 'error' : 'warning'; ?> is-dismissible">
                <p><strong><?php echo esc_html($alert['message']); ?></strong></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Patient Selection -->
    <div class="hms-patient-selector" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Patient Workspace', 'hospital-management-system'); ?></h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="hms_clinical">
            <label for="patient_select"><?php _e('Select Patient:', 'hospital-management-system'); ?></label>
            <select id="patient_select" name="patient_id" style="width: 300px; margin: 0 10px;">
                <option value=""><?php _e('Select a patient...', 'hospital-management-system'); ?></option>
                <?php foreach ($all_patients as $patient): ?>
                    <option value="<?php echo esc_attr($patient->id); ?>" <?php selected($selected_patient_id, $patient->id); ?>>
                        <?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" class="button button-primary" value="<?php _e('Load Patient', 'hospital-management-system'); ?>">
            <?php if ($selected_patient_id): ?>
                <a href="<?php echo admin_url('admin.php?page=hms_clinical'); ?>" class="button button-secondary"><?php _e('Clear Selection', 'hospital-management-system'); ?></a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if ($selected_patient_id && !empty($patient_summary)): ?>
    <!-- Patient Summary Display -->
    <div class="hms-patient-summary" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Patient Summary', 'hospital-management-system'); ?> - <?php echo esc_html($patient_summary['patient']->first_name . ' ' . $patient_summary['patient']->last_name); ?></h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <!-- Patient Info -->
            <div class="patient-info">
                <h3><?php _e('Patient Information', 'hospital-management-system'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Patient ID:', 'hospital-management-system'); ?></th>
                        <td><?php echo esc_html($patient_summary['patient']->id); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Date of Birth:', 'hospital-management-system'); ?></th>
                        <td><?php echo esc_html($patient_summary['patient']->date_of_birth); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Phone:', 'hospital-management-system'); ?></th>
                        <td><?php echo esc_html($patient_summary['patient']->phone); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Email:', 'hospital-management-system'); ?></th>
                        <td><?php echo esc_html($patient_summary['patient']->email); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Quick Actions -->
            <div class="patient-actions">
                <h3><?php _e('Quick Actions', 'hospital-management-system'); ?></h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="<?php echo admin_url('admin.php?page=hms_appointment_details&patient_id=' . $selected_patient_id); ?>" class="button button-primary">
                        <?php _e('Schedule Appointment', 'hospital-management-system'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=hms_prescription_details&patient_id=' . $selected_patient_id); ?>" class="button button-secondary">
                        <?php _e('Create Prescription', 'hospital-management-system'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=hms_medical_record_details&patient_id=' . $selected_patient_id); ?>" class="button button-secondary">
                        <?php _e('Add Medical Record', 'hospital-management-system'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order&patient_id=' . $selected_patient_id); ?>" class="button button-secondary">
                        <?php _e('Order Lab Test', 'hospital-management-system'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Patient Clinical Data Tabs -->
        <div class="hms-clinical-tabs" style="margin-top: 30px;">
            <div class="nav-tab-wrapper">
                <a href="#appointments" class="nav-tab nav-tab-active" onclick="showTab(event, 'appointments')"><?php _e('Recent Appointments', 'hospital-management-system'); ?></a>
                <a href="#prescriptions" class="nav-tab" onclick="showTab(event, 'prescriptions')"><?php _e('Recent Prescriptions', 'hospital-management-system'); ?></a>
                <a href="#records" class="nav-tab" onclick="showTab(event, 'records')"><?php _e('Medical Records', 'hospital-management-system'); ?></a>
                <a href="#lab-tests" class="nav-tab" onclick="showTab(event, 'lab-tests')"><?php _e('Lab Tests', 'hospital-management-system'); ?></a>
                <a href="#clinical-notes" class="nav-tab" onclick="showTab(event, 'clinical-notes')"><?php _e('Clinical Notes', 'hospital-management-system'); ?></a>
            </div>
            
            <!-- Appointments Tab -->
            <div id="appointments" class="tab-content" style="display: block;">
                <?php if (!empty($patient_summary['recent_appointments'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                <th><?php _e('Time', 'hospital-management-system'); ?></th>
                                <th><?php _e('Doctor', 'hospital-management-system'); ?></th>
                                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patient_summary['recent_appointments'] as $appointment): ?>
                                <tr>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($appointment->appointment_date))); ?></td>
                                    <td><?php echo esc_html($appointment->appointment_time); ?></td>
                                    <td><?php echo esc_html($appointment->doctor_first_name . ' ' . $appointment->doctor_last_name); ?></td>
                                    <td><span class="status-<?php echo esc_attr($appointment->status); ?>"><?php echo esc_html(ucfirst($appointment->status)); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No recent appointments found.', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Prescriptions Tab -->
            <div id="prescriptions" class="tab-content" style="display: none;">
                <?php if (!empty($patient_summary['recent_prescriptions'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                <th><?php _e('Doctor', 'hospital-management-system'); ?></th>
                                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patient_summary['recent_prescriptions'] as $prescription): ?>
                                <tr>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($prescription->created_at))); ?></td>
                                    <td><?php echo esc_html($prescription->doctor_first_name . ' ' . $prescription->doctor_last_name); ?></td>
                                    <td><span class="status-<?php echo esc_attr($prescription->status); ?>"><?php echo esc_html(ucfirst($prescription->status)); ?></span></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=hms_prescription_details&id=' . $prescription->id); ?>" class="button button-small">
                                            <?php _e('View', 'hospital-management-system'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No recent prescriptions found.', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Medical Records Tab -->
            <div id="records" class="tab-content" style="display: none;">
                <?php if (!empty($patient_summary['recent_records'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                <th><?php _e('Diagnosis', 'hospital-management-system'); ?></th>
                                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patient_summary['recent_records'] as $record): ?>
                                <tr>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($record->created_at))); ?></td>
                                    <td><?php echo esc_html($record->diagnosis); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=hms_medical_record_details&id=' . $record->id); ?>" class="button button-small">
                                            <?php _e('View', 'hospital-management-system'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No medical records found.', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Lab Tests Tab -->
            <div id="lab-tests" class="tab-content" style="display: none;">
                <?php if (!empty($patient_summary['recent_lab_tests'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                <th><?php _e('Test Name', 'hospital-management-system'); ?></th>
                                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patient_summary['recent_lab_tests'] as $test): ?>
                                <tr>
                                    <td><?php echo esc_html(date('Y-m-d', strtotime($test->created_at))); ?></td>
                                    <td><?php echo esc_html($test->test_name); ?></td>
                                    <td><span class="status-<?php echo esc_attr($test->status); ?>"><?php echo esc_html(ucfirst($test->status)); ?></span></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=hms_lab_results&id=' . $test->id); ?>" class="button button-small">
                                            <?php _e('View', 'hospital-management-system'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No lab tests found.', 'hospital-management-system'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Clinical Notes Tab -->
            <div id="clinical-notes" class="tab-content" style="display: none;">
                <div class="clinical-notes-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><?php _e('Clinical Notes', 'hospital-management-system'); ?></h3>
                    <button type="button" class="button button-primary" onclick="showAddNoteForm()"><?php _e('Add Note', 'hospital-management-system'); ?></button>
                </div>
                
                <!-- Add Note Form (Initially Hidden) -->
                <div id="add-note-form" style="display: none; background: #f9f9f9; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                    <h4><?php _e('Add Clinical Note', 'hospital-management-system'); ?></h4>
                    <form id="clinical-note-form">
                        <input type="hidden" name="patient_id" value="<?php echo esc_attr($selected_patient_id); ?>">
                        <table class="form-table">
                            <tr>
                                <th><label for="note_type"><?php _e('Note Type:', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <select id="note_type" name="note_type" required>
                                        <option value="general"><?php _e('General', 'hospital-management-system'); ?></option>
                                        <option value="consultation"><?php _e('Consultation', 'hospital-management-system'); ?></option>
                                        <option value="treatment"><?php _e('Treatment', 'hospital-management-system'); ?></option>
                                        <option value="follow_up"><?php _e('Follow-up', 'hospital-management-system'); ?></option>
                                        <option value="discharge"><?php _e('Discharge', 'hospital-management-system'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="note_title"><?php _e('Title:', 'hospital-management-system'); ?></label></th>
                                <td><input type="text" id="note_title" name="note_title" class="regular-text" placeholder="<?php _e('Brief title for the note', 'hospital-management-system'); ?>"></td>
                            </tr>
                            <tr>
                                <th><label for="note_content"><?php _e('Note Content:', 'hospital-management-system'); ?></label></th>
                                <td><textarea id="note_content" name="note_content" rows="5" class="large-text" required placeholder="<?php _e('Enter clinical note details...', 'hospital-management-system'); ?>"></textarea></td>
                            </tr>
                            <tr>
                                <th><label for="priority"><?php _e('Priority:', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <select id="priority" name="priority">
                                        <option value="normal"><?php _e('Normal', 'hospital-management-system'); ?></option>
                                        <option value="high"><?php _e('High', 'hospital-management-system'); ?></option>
                                        <option value="urgent"><?php _e('Urgent', 'hospital-management-system'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="is_private" name="is_private" value="1">
                                        <?php _e('Private note (visible only to authorized staff)', 'hospital-management-system'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Save Note', 'hospital-management-system'); ?></button>
                            <button type="button" class="button button-secondary" onclick="hideAddNoteForm()"><?php _e('Cancel', 'hospital-management-system'); ?></button>
                        </p>
                    </form>
                </div>
                
                <!-- Clinical Notes List -->
                <div id="clinical-notes-list">
                    <?php
                    $clinical_notes = $clinical_features->get_patient_clinical_notes($selected_patient_id, array('limit' => 10));
                    if (!empty($clinical_notes)):
                    ?>
                        <div class="clinical-notes-container">
                            <?php foreach ($clinical_notes as $note): ?>
                                <div class="clinical-note-item" style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                                    <div class="note-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <div class="note-meta">
                                            <span class="note-type-<?php echo esc_attr($note->note_type); ?>" style="background: #e7f3ff; color: #2271b1; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $note->note_type))); ?>
                                            </span>
                                            <?php if ($note->priority !== 'normal'): ?>
                                                <span class="priority-<?php echo esc_attr($note->priority); ?>" style="background: #fef7f7; color: #d63638; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; margin-left: 5px;">
                                                    <?php echo esc_html(ucfirst($note->priority)); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($note->is_private): ?>
                                                <span class="private-note" style="background: #f0f0f0; color: #666; padding: 2px 8px; border-radius: 3px; font-size: 12px; margin-left: 5px;">
                                                    <?php _e('Private', 'hospital-management-system'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="note-date">
                                            <small><?php echo esc_html(date('M j, Y g:i A', strtotime($note->created_at))); ?></small>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($note->note_title)): ?>
                                        <h4 style="margin: 0 0 10px 0; color: #1d2327;"><?php echo esc_html($note->note_title); ?></h4>
                                    <?php endif; ?>
                                    
                                    <div class="note-content" style="margin-bottom: 10px; line-height: 1.5;">
                                        <?php echo nl2br(esc_html($note->note_content)); ?>
                                    </div>
                                    
                                    <div class="note-footer" style="border-top: 1px solid #eee; padding-top: 10px; font-size: 12px; color: #666;">
                                        <?php _e('By:', 'hospital-management-system'); ?> <?php echo esc_html($note->created_by_name); ?>
                                        <?php if ($note->updated_at && $note->updated_at !== $note->created_at): ?>
                                            | <?php _e('Updated:', 'hospital-management-system'); ?> <?php echo esc_html(date('M j, Y g:i A', strtotime($note->updated_at))); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?php _e('No clinical notes found for this patient.', 'hospital-management-system'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Clinical Dashboard Cards -->
    <div class="hms-dashboard-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
        
        <div class="hms-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #2271b1;"><?php _e('Total Patients', 'hospital-management-system'); ?></h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($patients_count); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hms_patients'); ?>" class="button button-primary" style="margin-top: 10px;">
                <?php _e('View All Patients', 'hospital-management-system'); ?>
            </a>
        </div>
        
        <div class="hms-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #d63638;"><?php _e('Today\'s Appointments', 'hospital-management-system'); ?></h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($pending_tasks['todays_appointments']); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hms_appointments'); ?>" class="button button-primary" style="margin-top: 10px;">
                <?php _e('View Appointments', 'hospital-management-system'); ?>
            </a>
        </div>
        
        <div class="hms-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #00a32a;"><?php _e('Pending Prescriptions', 'hospital-management-system'); ?></h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($pending_tasks['pending_prescriptions']); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hms_prescriptions'); ?>" class="button button-primary" style="margin-top: 10px;">
                <?php _e('View Prescriptions', 'hospital-management-system'); ?>
            </a>
        </div>
        
        <div class="hms-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; color: #f56e28;"><?php _e('Patient Queue', 'hospital-management-system'); ?></h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: #333;"><?php echo number_format($pending_tasks['queue_count']); ?></p>
            <a href="<?php echo admin_url('admin.php?page=hms_queue'); ?>" class="button button-primary" style="margin-top: 10px;">
                <?php _e('Manage Queue', 'hospital-management-system'); ?>
            </a>
        </div>
        
    </div>
    
    <!-- Quick Actions -->
    <div class="hms-quick-actions" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Quick Actions', 'hospital-management-system'); ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
            
            <a href="<?php echo admin_url('admin.php?page=hms_patient_registration'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Register New Patient', 'hospital-management-system'); ?></strong>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=hms_appointment_details'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Schedule Appointment', 'hospital-management-system'); ?></strong>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=hms_prescription_details'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Create Prescription', 'hospital-management-system'); ?></strong>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=hms_bill_details'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Generate Bill', 'hospital-management-system'); ?></strong>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Order Lab Test', 'hospital-management-system'); ?></strong>
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=hms_dispense_medicine'); ?>" class="button button-secondary" style="padding: 15px; text-align: center; text-decoration: none;">
                <strong><?php _e('Dispense Medicine', 'hospital-management-system'); ?></strong>
            </a>
            
        </div>
    </div>
    
    <!-- Recent Clinical Activities -->
    <div class="hms-recent-activities" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
        <h2><?php _e('Recent Clinical Activities', 'hospital-management-system'); ?></h2>
        
        <?php if (!empty($recent_activities)): ?>
            <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th><?php _e('Type', 'hospital-management-system'); ?></th>
                        <th><?php _e('Patient', 'hospital-management-system'); ?></th>
                        <th><?php _e('Staff', 'hospital-management-system'); ?></th>
                        <th><?php _e('Date', 'hospital-management-system'); ?></th>
                        <th><?php _e('Status', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activities as $activity): ?>
                        <tr>
                            <td>
                                <span class="activity-type-<?php echo esc_attr($activity->type); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $activity->type))); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($activity->patient_name); ?></td>
                            <td><?php echo esc_html($activity->staff_name); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($activity->activity_date))); ?></td>
                            <td><span class="status-<?php echo esc_attr($activity->status); ?>"><?php echo esc_html(ucfirst($activity->status)); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No recent activities found.', 'hospital-management-system'); ?></p>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
.status-scheduled { color: #00a32a; font-weight: bold; }
.status-completed { color: #2271b1; font-weight: bold; }
.status-cancelled { color: #d63638; font-weight: bold; }
.status-pending { color: #f56e28; font-weight: bold; }
.status-active { color: #00a32a; font-weight: bold; }
.status-waiting { color: #f56e28; font-weight: bold; }

.activity-type-appointment { 
    background: #e7f3ff; 
    color: #2271b1; 
    padding: 4px 8px; 
    border-radius: 4px; 
    font-size: 12px; 
    font-weight: bold; 
}
.activity-type-prescription { 
    background: #f0f9ff; 
    color: #00a32a; 
    padding: 4px 8px; 
    border-radius: 4px; 
    font-size: 12px; 
    font-weight: bold; 
}

.tab-content {
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-top: none;
    background: #fff;
}

.nav-tab-wrapper {
    border-bottom: 1px solid #ccd0d4;
}

.hms-clinical-alerts .notice {
    margin: 5px 0;
}

.patient-info .form-table th {
    width: 150px;
    font-weight: 600;
}

.patient-actions .button {
    width: 100%;
    text-align: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .hms-dashboard-cards {
        grid-template-columns: 1fr !important;
    }
    
    .hms-patient-summary > div {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
function showTab(evt, tabName) {
    var i, tabcontent, tablinks;
    
    // Hide all tab content
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    
    // Remove active class from all tabs
    tablinks = document.getElementsByClassName("nav-tab");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("nav-tab-active");
    }
    
    // Show the selected tab and mark it as active
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("nav-tab-active");
}

// Clinical Notes Functions
function showAddNoteForm() {
    document.getElementById('add-note-form').style.display = 'block';
    document.getElementById('note_content').focus();
}

function hideAddNoteForm() {
    document.getElementById('add-note-form').style.display = 'none';
    document.getElementById('clinical-note-form').reset();
}

// Auto-submit patient selection form when dropdown changes
document.addEventListener('DOMContentLoaded', function() {
    var patientSelect = document.getElementById('patient_select');
    if (patientSelect) {
        patientSelect.addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    }
    
    // Clinical note form submission
    var noteForm = document.getElementById('clinical-note-form');
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            formData.append('action', 'hms_add_clinical_note');
            formData.append('nonce', '<?php echo wp_create_nonce("hms_admin_nonce"); ?>');
            
            var submitButton = this.querySelector('button[type="submit"]');
            var originalText = submitButton.textContent;
            submitButton.textContent = '<?php _e("Saving...", "hospital-management-system"); ?>';
            submitButton.disabled = true;
            
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    var notice = document.createElement('div');
                    notice.className = 'notice notice-success is-dismissible';
                    notice.innerHTML = '<p>' + data.data.message + '</p>';
                    document.querySelector('.wrap').insertBefore(notice, document.querySelector('.wrap').firstChild);
                    
                    // Reset form and hide it
                    hideAddNoteForm();
                    
                    // Reload the page to show the new note
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert('<?php _e("Error:", "hospital-management-system"); ?> ' + data.data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php _e("An error occurred while saving the note.", "hospital-management-system"); ?>');
            })
            .finally(() => {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            });
        });
    }
});
</script>