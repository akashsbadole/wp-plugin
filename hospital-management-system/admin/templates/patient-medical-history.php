<?php
/**
 * Admin template for viewing and editing a patient's digital health record.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/templates
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
if (!$patient_id) {
    // If no patient ID is provided, show a patient selection form
    ?>
    <div class="wrap hms-wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('Patient Medical History', 'hospital-management-system'); ?></h1>
        <hr class="wp-header-end">
        
        <div class="hms-card">
            <div class="hms-card-body">
                <p><?php esc_html_e('Please select a patient to view their medical history.', 'hospital-management-system'); ?></p>
                
                <!-- Patient Search Container -->
                <div id="hms-patient-search-container" class="hms-patient-search-container">
                    <div class="hms-form-group">
                        <label for="hms-patient-search"><?php esc_html_e('Search Patient:', 'hospital-management-system'); ?></label>
                        <div class="hms-search-input-container">
                            <input type="text" id="hms-patient-search" class="hms-form-control" placeholder="<?php esc_html_e('Type patient name or ID to search...', 'hospital-management-system'); ?>">
                            <span id="hms-patient-search-spinner" class="hms-spinner" style="display: none;"></span>
                        </div>
                        <div id="hms-patient-search-results" class="hms-patient-search-results"></div>
                        <div id="hms-patient-search-no-results" class="hms-no-results" style="display: none;"><?php esc_html_e('No patients found matching your search.', 'hospital-management-system'); ?></div>
                        <button type="button" id="hms-patient-search-submit" class="hms-btn hms-btn-primary" style="display: none; margin-top: 10px;"><?php esc_html_e('View Medical History', 'hospital-management-system'); ?></button>
                    </div>
                </div>
                
                <form method="get" action="">
                    <input type="hidden" name="page" value="hms_patient_medical_history">
                    <div class="hms-form-group">
                        <label for="patient_select"><?php esc_html_e('Or Select Patient from List:', 'hospital-management-system'); ?></label>
                        <select name="patient_id" id="patient_select" class="hms-form-control" required>
                            <option value=""><?php esc_html_e('-- Select Patient --', 'hospital-management-system'); ?></option>
                            <?php
                            // Get all patients for the dropdown
                            if (!class_exists('HMS_Patient')) {
                                require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
                            }
                            $patient_obj = new HMS_Patient();
                            $patients = $patient_obj->get_patients();
                            
                            if (!empty($patients)) {
                                foreach ($patients as $patient) {
                                    echo '<option value="' . esc_attr($patient->id) . '">' . esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')') . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="hms-btn hms-btn-primary"><?php esc_html_e('View Medical History', 'hospital-management-system'); ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Ensure the HMS_Patient class is available
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}

$patient_obj = new HMS_Patient();
$patient = $patient_obj->get_patient($patient_id);

if (!$patient) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Patient not found.', 'hospital-management-system') . '</p></div>';
    return;
}

$previous_visits = $patient_obj->get_patient_previous_visits($patient_id);
?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline">
        <?php printf(
            /* translators: %s: Patient Name and ID */
            esc_html__('Digital Health Record for: %s', 'hospital-management-system'),
            esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')')
        ); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_comprehensive_medical_history&patient_id=' . $patient_id)); ?>" class="page-title-action"><?php esc_html_e('Comprehensive View', 'hospital-management-system'); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-patients')); ?>" class="page-title-action"><?php esc_html_e('&larr; Back to Patients', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <div id="hms-patient-medical-history-container" class="hms-container">
        <div class="hms-row">
            <!-- Patient Basic Info -->
            <div class="hms-col-md-4">
                <div class="hms-card">
                    <h2 class="hms-card-title"><?php esc_html_e('Patient Information', 'hospital-management-system'); ?></h2>
                    <div class="hms-card-body">
                        <p><strong><?php esc_html_e('Full Name:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?></p>
                        <p><strong><?php esc_html_e('Patient ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->patient_id); ?></p>
                        <p><strong><?php esc_html_e('Date of Birth:', 'hospital-management-system'); ?></strong> <?php echo esc_html(!empty($patient->date_of_birth) ? date_i18n(get_option('date_format'), strtotime($patient->date_of_birth)) : __('N/A', 'hospital-management-system')); ?></p>
                        <p><strong><?php esc_html_e('Age:', 'hospital-management-system'); ?></strong> <?php echo esc_html(HMS_Helper::calculate_age($patient->date_of_birth)); ?></p>
                        <p><strong><?php esc_html_e('Gender:', 'hospital-management-system'); ?></strong> <?php echo esc_html(ucfirst($patient->gender)); ?></p>
                        <p><strong><?php esc_html_e('Blood Group:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->blood_group); ?></p>
                        <p><strong><?php esc_html_e('Contact:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->contact_number); ?></p>
                        <p><strong><?php esc_html_e('Email:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->email); ?></p>
                        <p><strong><?php esc_html_e('Address:', 'hospital-management-system'); ?></strong> <?php echo esc_html(nl2br($patient->address)); ?></p>
                        <p><strong><?php esc_html_e('Emergency Contact:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->emergency_contact_name . ' (' . $patient->emergency_contact_number . ')'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Medical History & Allergies -->
            <div class="hms-col-md-8">
                <div class="hms-card">
                    <h2 class="hms-card-title"><?php esc_html_e('Medical History & Allergies', 'hospital-management-system'); ?></h2>
                    <div class="hms-card-body">
                        <form id="hms-medical-history-form" method="post">
                            <input type="hidden" name="patient_id" value="<?php echo esc_attr($patient_id); ?>">
                            <?php wp_nonce_field('hms_admin_nonce', 'medical_history_nonce'); ?>
                            
                            <div class="hms-form-group">
                                <label for="medical_history"><?php esc_html_e('Medical History:', 'hospital-management-system'); ?></label>
                                <textarea name="medical_history" id="medical_history" rows="8" class="hms-form-control" <?php echo current_user_can('manage_patients') ? '' : 'readonly'; ?>><?php echo esc_textarea($patient->medical_history); ?></textarea>
                            </div>

                            <div class="hms-form-group">
                                <label for="allergies"><?php esc_html_e('Known Allergies:', 'hospital-management-system'); ?></label>
                                <textarea name="allergies" id="allergies" rows="4" class="hms-form-control" <?php echo current_user_can('manage_patients') ? '' : 'readonly'; ?>><?php echo esc_textarea($patient->allergies); ?></textarea>
                            </div>

                            <?php if (current_user_can('manage_patients')) : ?>
                                <button type="submit" class="hms-btn hms-btn-primary"><?php esc_html_e('Save Changes', 'hospital-management-system'); ?></button>
                            <?php endif; ?>
                        </form>
                        <div id="hms-medical-history-feedback" class="hms-feedback" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient Vitals -->
        <div class="hms-row">
            <div class="hms-col-md-12">
                <div class="hms-card">
                    <h2 class="hms-card-title"><?php esc_html_e('Latest Patient Vitals', 'hospital-management-system'); ?></h2>
                    <div class="hms-card-body">
                        <?php
                        // Check if the vitals table exists
                        global $wpdb;
                        $vitals_table = $wpdb->prefix . 'hms_patient_vitals';
                        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$vitals_table'") === $vitals_table;
                        
                        if ($table_exists) {
                            // Ensure HMS_Clinical class is available
                            if (!class_exists('HMS_Clinical')) {
                                require_once HMS_PLUGIN_DIR . 'includes/class-hms-clinical.php';
                            }
                            $hms_clinical = new HMS_Clinical();
                            $latest_vitals = $hms_clinical->get_all_patient_vitals($patient_id, 1); // Get the latest record
                            
                            if (empty($latest_vitals)) {
                                echo '<p>' . esc_html__('No vitals recorded for this patient.', 'hospital-management-system') . '</p>';
                            } else {
                                $vitals = $latest_vitals[0];
                                ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Recorded At:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($vitals->recorded_at))); ?></div>
                                </div>
                                <?php if ($vitals->temperature) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Temperature:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->temperature . ' °F/°C'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->blood_pressure_systolic && $vitals->blood_pressure_diastolic) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Blood Pressure:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->blood_pressure_systolic . '/' . $vitals->blood_pressure_diastolic . ' mmHg'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->pulse) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Pulse:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->pulse . ' bpm'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->respiratory_rate) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Respiratory Rate:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->respiratory_rate . ' breaths/min'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->spo2) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('SpO2:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->spo2 . ' %'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->blood_sugar) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Blood Sugar:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html($vitals->blood_sugar . ' mg/dL'); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if ($vitals->notes) : ?>
                                <div class="hms-row">
                                    <div class="hms-col-md-3"><strong><?php esc_html_e('Notes:', 'hospital-management-system'); ?></strong></div>
                                    <div class="hms-col-md-9"><?php echo esc_html(nl2br($vitals->notes)); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php
                            }
                        } else {
                            echo '<div class="notice notice-warning"><p>' . esc_html__('Patient vitals table does not exist. Please reactivate the plugin to create all required database tables.', 'hospital-management-system') . '</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medication Log Summary -->
        <div class="hms-row">
            <div class="hms-col-md-12">
                <div class="hms-card">
                    <h2 class="hms-card-title"><?php esc_html_e('Recent Medication Administration', 'hospital-management-system'); ?></h2>
                    <div class="hms-card-body">
                        <?php
                        // Check if the medication logs table exists
                        global $wpdb;
                        $med_logs_table = $wpdb->prefix . 'hms_medication_logs';
                        $med_logs_table_exists = $wpdb->get_var("SHOW TABLES LIKE '$med_logs_table'") === $med_logs_table;
                        
                        if ($med_logs_table_exists) {
                            $medication_logs = $hms_clinical->get_all_medication_logs($patient_id, 5); // Get last 5 records
                            
                            if (empty($medication_logs)) {
                                echo '<p>' . esc_html__('No medication logs found for this patient.', 'hospital-management-system') . '</p>';
                            } else {
                                ?>
                                <div class="hms-table-responsive">
                                    <table class="hms-table hms-table-striped hms-table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('Administered At', 'hospital-management-system'); ?></th>
                                                <th><?php esc_html_e('Medicine', 'hospital-management-system'); ?></th>
                                                <th><?php esc_html_e('Dosage', 'hospital-management-system'); ?></th>
                                                <th><?php esc_html_e('Notes', 'hospital-management-system'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($medication_logs as $log) : ?>
                                                <tr>
                                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->administered_at))); ?></td>
                                                    <td><?php echo esc_html($log->medicine_name ?? __('Unknown', 'hospital-management-system')); ?></td>
                                                    <td><?php echo esc_html($log->dosage); ?></td>
                                                    <td><?php echo esc_html($log->notes ?? 'N/A'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="notice notice-warning"><p>' . esc_html__('Medication logs table does not exist. Please reactivate the plugin to create all required database tables.', 'hospital-management-system') . '</p></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Records -->
        <div class="hms-row">
            <div class="hms-col-md-12">
                <div class="hms-card">
                    <h2 class="hms-card-title">
                        <?php esc_html_e('Medical Records', 'hospital-management-system'); ?>
                        <?php if (current_user_can('manage_clinical')) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&patient_id=' . $patient_id)); ?>" class="hms-btn hms-btn-sm hms-btn-primary" style="float: right;">
                                <?php esc_html_e('Add New Record', 'hospital-management-system'); ?>
                            </a>
                        <?php endif; ?>
                    </h2>
                    <div class="hms-card-body">
                        <?php
                        // Get medical records for this patient
                        if (!class_exists('HMS_Medical_Records')) {
                            require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
                        }
                        $medical_records_obj = new HMS_Medical_Records();
                        $medical_records = $medical_records_obj->get_patient_medical_history($patient_id, 10); // Get last 10 records
                        
                        if (empty($medical_records)) : ?>
                            <div class="hms-no-records">
                                <p><?php esc_html_e('No medical records found for this patient.', 'hospital-management-system'); ?></p>
                                <?php if (current_user_can('manage_clinical')) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&patient_id=' . $patient_id)); ?>" class="hms-btn hms-btn-primary">
                                        <?php esc_html_e('Add First Medical Record', 'hospital-management-system'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <!-- Search and Filter for Medical Records -->
                            <div class="hms-medical-records-filters" style="margin-bottom: 20px;">
                                <div class="hms-filter-row">
                                    <input type="text" id="medical-records-search" placeholder="<?php esc_attr_e('Search by diagnosis, symptoms, or notes...', 'hospital-management-system'); ?>" class="hms-form-control" style="width: 300px; display: inline-block;">
                                    <select id="medical-records-type-filter" class="hms-form-control" style="width: 150px; display: inline-block; margin-left: 10px;">
                                        <option value=""><?php esc_html_e('All Types', 'hospital-management-system'); ?></option>
                                        <option value="consultation"><?php esc_html_e('Consultation', 'hospital-management-system'); ?></option>
                                        <option value="follow_up"><?php esc_html_e('Follow-up', 'hospital-management-system'); ?></option>
                                        <option value="emergency"><?php esc_html_e('Emergency', 'hospital-management-system'); ?></option>
                                        <option value="surgery"><?php esc_html_e('Surgery', 'hospital-management-system'); ?></option>
                                        <option value="discharge"><?php esc_html_e('Discharge', 'hospital-management-system'); ?></option>
                                    </select>
                                    <button type="button" id="medical-records-filter-btn" class="hms-btn hms-btn-secondary" style="margin-left: 10px;">
                                        <?php esc_html_e('Filter', 'hospital-management-system'); ?>
                                    </button>
                                    <button type="button" id="medical-records-clear-btn" class="hms-btn hms-btn-secondary" style="margin-left: 5px;">
                                        <?php esc_html_e('Clear', 'hospital-management-system'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Medical Records Timeline -->
                            <div id="medical-records-timeline" class="hms-medical-records-timeline">
                                <?php foreach ($medical_records as $record) : ?>
                                    <div class="hms-timeline-item" data-record-type="<?php echo esc_attr($record->record_type); ?>" data-record-id="<?php echo esc_attr($record->id); ?>">
                                        <div class="hms-timeline-marker hms-timeline-marker-<?php echo esc_attr($record->record_type); ?>">
                                            <i class="hms-icon hms-icon-<?php echo esc_attr($record->record_type); ?>"></i>
                                        </div>
                                        <div class="hms-timeline-content">
                                            <div class="hms-timeline-header">
                                                <div class="hms-timeline-date">
                                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->created_at))); ?>
                                                </div>
                                                <div class="hms-timeline-type">
                                                    <span class="hms-record-type hms-record-type-<?php echo esc_attr($record->record_type); ?>">
                                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $record->record_type))); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="hms-timeline-body">
                                                <div class="hms-timeline-doctor">
                                                    <strong><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></strong> 
                                                    <?php echo esc_html($record->doctor_name); ?>
                                                </div>
                                                
                                                <?php if (!empty($record->diagnosis)) : ?>
                                                    <div class="hms-timeline-diagnosis">
                                                        <strong><?php esc_html_e('Diagnosis:', 'hospital-management-system'); ?></strong>
                                                        <p><?php echo esc_html($record->diagnosis); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($record->symptoms)) : ?>
                                                    <div class="hms-timeline-symptoms">
                                                        <strong><?php esc_html_e('Symptoms:', 'hospital-management-system'); ?></strong>
                                                        <p><?php echo esc_html(wp_trim_words($record->symptoms, 20, '...')); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($record->treatment_plan)) : ?>
                                                    <div class="hms-timeline-treatment">
                                                        <strong><?php esc_html_e('Treatment Plan:', 'hospital-management-system'); ?></strong>
                                                        <p><?php echo esc_html(wp_trim_words($record->treatment_plan, 20, '...')); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($record->notes)) : ?>
                                                    <div class="hms-timeline-notes">
                                                        <strong><?php esc_html_e('Notes:', 'hospital-management-system'); ?></strong>
                                                        <p><?php echo esc_html(wp_trim_words($record->notes, 15, '...')); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="hms-timeline-actions">
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=view&id=' . $record->id)); ?>" 
                                                   class="hms-btn hms-btn-sm hms-btn-info">
                                                    <?php esc_html_e('View Full Record', 'hospital-management-system'); ?>
                                                </a>
                                                
                                                <?php if (current_user_can('manage_clinical')) : ?>
                                                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=edit&id=' . $record->id)); ?>" 
                                                       class="hms-btn hms-btn-sm hms-btn-secondary">
                                                        <?php esc_html_e('Edit', 'hospital-management-system'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Load More Button -->
                            <?php if (count($medical_records) >= 10) : ?>
                                <div class="hms-load-more-container" style="text-align: center; margin-top: 20px;">
                                    <button type="button" id="load-more-records" class="hms-btn hms-btn-secondary" data-patient-id="<?php echo esc_attr($patient_id); ?>" data-offset="10">
                                        <?php esc_html_e('Load More Records', 'hospital-management-system'); ?>
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <!-- View All Records Link -->
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_records&patient_id=' . $patient_id)); ?>" class="hms-btn hms-btn-primary">
                                    <?php esc_html_e('View All Medical Records', 'hospital-management-system'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Visits -->
        <div class="hms-row">
            <div class="hms-col-md-12">
                <div class="hms-card">
                    <h2 class="hms-card-title"><?php esc_html_e('Previous Visits & Records', 'hospital-management-system'); ?></h2>
                    <div class="hms-card-body">
                        <?php if (empty($previous_visits)) : ?>
                            <p><?php esc_html_e('No previous visit records found for this patient.', 'hospital-management-system'); ?></p>
                        <?php else : ?>
                            <div class="hms-table-responsive">
                                <table class="hms-table hms-table-striped hms-table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                                            <th><?php esc_html_e('Type', 'hospital-management-system'); ?></th>
                                            <th><?php esc_html_e('Details', 'hospital-management-system'); ?></th>
                                            <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                                            <th><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($previous_visits as $visit) : ?>
                                            <tr>
                                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($visit->date))); ?></td>
                                                <td>
                                                    <span class="hms-badge hms-badge-<?php echo esc_attr($visit->type); ?>">
                                                        <?php echo esc_html(ucfirst($visit->type)); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo esc_html($visit->details); ?></td>
                                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $visit->status ?? $visit->payment_status ?? 'N/A'))); ?></td>
                                                <td>
                                                    <?php if ($visit->type === 'appointment') : ?>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms-appointments&action=view&id=' . $visit->id)); ?>" class="hms-btn hms-btn-sm hms-btn-info"><?php esc_html_e('View', 'hospital-management-system'); ?></a>
                                                    <?php elseif ($visit->type === 'bill') : ?>
                                                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms-billing&action=view&id=' . $visit->id)); ?>" class="hms-btn hms-btn-sm hms-btn-info"><?php esc_html_e('View Bill', 'hospital-management-system'); ?></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#hms-medical-history-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $feedback = $('#hms-medical-history-feedback');
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'hms_update_patient_medical_history',
                nonce: $('#medical_history_nonce').val(),
                patient_id: $('input[name="patient_id"]').val(),
                medical_history: $('#medical_history').val(),
                allergies: $('#allergies').val()
            },
            dataType: 'json',
            beforeSend: function() {
                $form.find('button[type="submit"]').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                } else {
                    $feedback.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : __('An unexpected error occurred.', 'hospital-management-system');
                $feedback.addClass('notice notice-error').html('<p>' + errorMessage + '</p>').show();
            },
            complete: function() {
                $form.find('button[type="submit"]').prop('disabled', false);
                // Scroll to feedback
                $('html, body').animate({
                    scrollTop: $feedback.offset().top - 100
                }, 500);
            }
        });
    });

    // Medical Records Filtering and Search
    $('#medical-records-filter-btn').on('click', function() {
        filterMedicalRecords();
    });

    $('#medical-records-clear-btn').on('click', function() {
        $('#medical-records-search').val('');
        $('#medical-records-type-filter').val('');
        filterMedicalRecords();
    });

    $('#medical-records-search').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            filterMedicalRecords();
        }
    });

    function filterMedicalRecords() {
        var searchTerm = $('#medical-records-search').val().toLowerCase();
        var typeFilter = $('#medical-records-type-filter').val();
        
        $('.hms-timeline-item').each(function() {
            var $item = $(this);
            var recordType = $item.data('record-type');
            var content = $item.find('.hms-timeline-content').text().toLowerCase();
            
            var matchesSearch = searchTerm === '' || content.indexOf(searchTerm) !== -1;
            var matchesType = typeFilter === '' || recordType === typeFilter;
            
            if (matchesSearch && matchesType) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }

    // Load More Records
    $('#load-more-records').on('click', function() {
        var $button = $(this);
        var patientId = $button.data('patient-id');
        var offset = $button.data('offset');
        
        $button.prop('disabled', true).text('<?php esc_html_e('Loading...', 'hospital-management-system'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_load_more_medical_records',
                nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                patient_id: patientId,
                offset: offset,
                limit: 10
            },
            success: function(response) {
                if (response.success && response.data.records.length > 0) {
                    var records = response.data.records;
                    var $timeline = $('#medical-records-timeline');
                    
                    $.each(records, function(index, record) {
                        var recordHtml = buildTimelineItem(record);
                        $timeline.append(recordHtml);
                    });
                    
                    $button.data('offset', offset + records.length);
                    
                    if (records.length < 10) {
                        $button.hide();
                    }
                } else {
                    $button.hide();
                }
            },
            error: function() {
                alert('<?php esc_html_e('Error loading more records.', 'hospital-management-system'); ?>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php esc_html_e('Load More Records', 'hospital-management-system'); ?>');
            }
        });
    });

    function buildTimelineItem(record) {
        // This function would build the HTML for a timeline item
        // For now, we'll reload the page to show new records
        return '';
    }
});
</script>

<style>
/* Basic styling for the template, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-container { margin-top: 20px; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
.hms-col-md-4, .hms-col-md-8, .hms-col-md-12 { position: relative; width: 100%; padding-right: 15px; padding-left: 15px; }
@media (min-width: 768px) {
    .hms-col-md-4 { flex: 0 0 33.333333%; max-width: 33.333333%; }
    .hms-col-md-8 { flex: 0 0 66.666667%; max-width: 66.666667%; }
    .hms-col-md-12 { flex: 0 0 100%; max-width: 100%; }
}
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-title { padding: 12px 15px; margin: 0; border-bottom: 1px solid #ccd0d4; font-size: 1.1em; background-color: #f8f9f9; border-top-left-radius: 3px; border-top-right-radius: 3px; }
.hms-card-body { padding: 15px; }
.hms-form-group { margin-bottom: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-btn-info { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }
.hms-btn-info:hover { background-color: #138496; border-color: #117a8b; }
.hms-btn-sm { padding: .25rem .5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
.hms-feedback { margin-top: 1rem; }
.hms-table-responsive { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hms-table { width: 100%; margin-bottom: 1rem; background-color: transparent; border-collapse: collapse; }
.hms-table th, .hms-table td { padding: .75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
.hms-table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
.hms-table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
.hms-badge { display: inline-block; padding: .25em .4em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
.hms-badge-appointment { background-color: #d1ecf1; color: #0c5460; }
.hms-badge-bill { background-color: #d4edda; color: #155724; }

/* Patient Search Styles */
.hms-patient-search-container {
    margin-bottom: 20px;
}

/* Medical Records Timeline Styles */
.hms-medical-records-filters {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.hms-filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.hms-medical-records-timeline {
    position: relative;
    padding-left: 30px;
}

.hms-medical-records-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.hms-timeline-item {
    position: relative;
    margin-bottom: 30px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hms-timeline-marker {
    position: absolute;
    left: -22px;
    top: 20px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #fff;
}

.hms-timeline-marker-consultation {
    background-color: #007bff;
}

.hms-timeline-marker-follow_up {
    background-color: #28a745;
}

.hms-timeline-marker-emergency {
    background-color: #dc3545;
}

.hms-timeline-marker-surgery {
    background-color: #ffc107;
}

.hms-timeline-marker-discharge {
    background-color: #6c757d;
}

.hms-timeline-content {
    padding: 20px;
}

.hms-timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.hms-timeline-date {
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.hms-timeline-type .hms-record-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.hms-timeline-body > div {
    margin-bottom: 12px;
}

.hms-timeline-body > div:last-child {
    margin-bottom: 0;
}

.hms-timeline-body strong {
    color: #333;
    font-size: 13px;
}

.hms-timeline-body p {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.hms-timeline-doctor {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

.hms-timeline-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 8px;
}

.hms-btn-secondary {
    color: #6c757d;
    background-color: transparent;
    border-color: #6c757d;
}

.hms-btn-secondary:hover {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.hms-no-records {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.hms-no-records p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Record Type Badges */
.hms-record-type-consultation {
    background-color: #d1ecf1;
    color: #0c5460;
}

.hms-record-type-follow_up {
    background-color: #d4edda;
    color: #155724;
}

.hms-record-type-emergency {
    background-color: #f8d7da;
    color: #721c24;
}

.hms-record-type-surgery {
    background-color: #fff3cd;
    color: #856404;
}

.hms-record-type-discharge {
    background-color: #e2e3e5;
    color: #383d41;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hms-filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .hms-filter-row input,
    .hms-filter-row select,
    .hms-filter-row button {
        width: 100%;
        margin-left: 0 !important;
        margin-bottom: 10px;
    }
    
    .hms-timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .hms-timeline-actions {
        flex-direction: column;
    }
    
    .hms-medical-records-timeline {
        padding-left: 20px;
    }
    
    .hms-timeline-marker {
        left: -12px;
    }
}
.hms-search-input-container {
    position: relative;
}
.hms-spinner {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    border: 2px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top-color: #007bff;
    animation: hms-spin 1s ease-in-out infinite;
}
@keyframes hms-spin {
    to { transform: translateY(-50%) rotate(360deg); }
}
.hms-patient-search-results {
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    margin-top: 5px;
}
.hms-patient-search-result {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
.hms-patient-search-result:last-child {
    border-bottom: none;
}
.hms-patient-search-result:hover,
.hms-patient-search-result.selected {
    background-color: #f8f9fa;
}
.hms-patient-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.hms-patient-id {
    color: #6c757d;
    font-size: 0.9em;
    margin-left: 10px;
}
.hms-no-results {
    padding: 10px 15px;
    color: #6c757d;
    font-style: italic;
}
</style>
