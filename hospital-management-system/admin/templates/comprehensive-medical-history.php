<?php
/**
 * Comprehensive Medical History Interface Template
 * 
 * This template provides an enhanced medical history interface with:
 * - Chronological record display
 * - Advanced search and filtering by diagnosis
 * - Medical record editing with audit trail
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
        <h1 class="wp-heading-inline"><?php esc_html_e('Comprehensive Medical History', 'hospital-management-system'); ?></h1>
        <hr class="wp-header-end">
        
        <div class="hms-card">
            <div class="hms-card-body">
                <p><?php esc_html_e('Please select a patient to view their comprehensive medical history.', 'hospital-management-system'); ?></p>
                
                <!-- Enhanced Patient Search -->
                <div id="hms-patient-search-container" class="hms-patient-search-container">
                    <div class="hms-form-group">
                        <label for="hms-patient-search"><?php esc_html_e('Search Patient:', 'hospital-management-system'); ?></label>
                        <div class="hms-search-input-container">
                            <input type="text" id="hms-patient-search" class="hms-form-control" placeholder="<?php esc_html_e('Type patient name, ID, or phone number...', 'hospital-management-system'); ?>">
                            <span id="hms-patient-search-spinner" class="hms-spinner" style="display: none;"></span>
                        </div>
                        <div id="hms-patient-search-results" class="hms-patient-search-results"></div>
                        <div id="hms-patient-search-no-results" class="hms-no-results" style="display: none;"><?php esc_html_e('No patients found matching your search.', 'hospital-management-system'); ?></div>
                    </div>
                </div>
                
                <form method="get" action="">
                    <input type="hidden" name="page" value="hms_comprehensive_medical_history">
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
                    <button type="submit" class="hms-btn hms-btn-primary"><?php esc_html_e('View Comprehensive History', 'hospital-management-system'); ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Ensure required classes are available
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}
if (!class_exists('HMS_Medical_Records')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
}
if (!class_exists('HMS_Staff')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-staff.php';
}

$patient_obj = new HMS_Patient();
$medical_records_obj = new HMS_Medical_Records();
$staff_obj = new HMS_Staff();

$patient = $patient_obj->get_patient($patient_id);

if (!$patient) {
    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Patient not found.', 'hospital-management-system') . '</p></div>';
    return;
}

// Get all medical records for this patient
$all_medical_records = $medical_records_obj->get_patient_medical_history($patient_id);
$doctors = $staff_obj->get_staff_by_role('doctor');
?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline">
        <?php printf(
            /* translators: %s: Patient Name and ID */
            esc_html__('Comprehensive Medical History: %s', 'hospital-management-system'),
            esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')')
        ); ?>
    </h1>
    <div class="hms-page-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&patient_id=' . $patient_id)); ?>" class="page-title-action">
            <?php esc_html_e('Add New Record', 'hospital-management-system'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms-patients')); ?>" class="page-title-action">
            <?php esc_html_e('Back to Patients', 'hospital-management-system'); ?>
        </a>
    </div>
    <hr class="wp-header-end">

    <div id="hms-comprehensive-history-container" class="hms-container">
        
        <!-- Patient Summary Card -->
        <div class="hms-patient-summary-card">
            <div class="hms-patient-info">
                <div class="hms-patient-avatar">
                    <span class="hms-avatar-initials"><?php echo esc_html(strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1))); ?></span>
                </div>
                <div class="hms-patient-details">
                    <h2><?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?></h2>
                    <div class="hms-patient-meta">
                        <span class="hms-patient-id"><?php esc_html_e('ID:', 'hospital-management-system'); ?> <?php echo esc_html($patient->patient_id); ?></span>
                        <span class="hms-patient-age"><?php esc_html_e('Age:', 'hospital-management-system'); ?> <?php echo esc_html(HMS_Helper::calculate_age($patient->date_of_birth)); ?></span>
                        <span class="hms-patient-gender"><?php echo esc_html(ucfirst($patient->gender)); ?></span>
                        <span class="hms-patient-blood"><?php esc_html_e('Blood:', 'hospital-management-system'); ?> <?php echo esc_html($patient->blood_group); ?></span>
                    </div>
                </div>
            </div>
            <div class="hms-patient-stats">
                <div class="hms-stat">
                    <span class="hms-stat-number"><?php echo count($all_medical_records); ?></span>
                    <span class="hms-stat-label"><?php esc_html_e('Total Records', 'hospital-management-system'); ?></span>
                </div>
                <div class="hms-stat">
                    <span class="hms-stat-number"><?php echo count(array_filter($all_medical_records, function($r) { return $r->record_type === 'consultation'; })); ?></span>
                    <span class="hms-stat-label"><?php esc_html_e('Consultations', 'hospital-management-system'); ?></span>
                </div>
                <div class="hms-stat">
                    <span class="hms-stat-number"><?php echo count(array_filter($all_medical_records, function($r) { return $r->record_type === 'emergency'; })); ?></span>
                    <span class="hms-stat-label"><?php esc_html_e('Emergencies', 'hospital-management-system'); ?></span>
                </div>
            </div>
        </div>

        <!-- Advanced Search and Filter Section -->
        <div class="hms-advanced-filters-section">
            <div class="hms-filters-header">
                <h3><?php esc_html_e('Search & Filter Medical Records', 'hospital-management-system'); ?></h3>
                <button type="button" id="toggle-advanced-filters" class="hms-btn hms-btn-secondary hms-btn-sm">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e('Advanced Filters', 'hospital-management-system'); ?>
                </button>
            </div>
            
            <div class="hms-filters-content">
                <!-- Basic Search -->
                <div class="hms-basic-search">
                    <div class="hms-search-input-wrapper">
                        <input type="text" id="comprehensive-search" class="hms-form-control hms-search-input" 
                               placeholder="<?php esc_attr_e('Search by diagnosis, symptoms, treatment, notes, or doctor name...', 'hospital-management-system'); ?>">
                        <span class="hms-search-icon dashicons dashicons-search"></span>
                        <button type="button" id="clear-search" class="hms-clear-search" style="display: none;">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Filters (Initially Hidden) -->
                <div id="advanced-filters-panel" class="hms-advanced-filters-panel" style="display: none;">
                    <div class="hms-filter-row">
                        <div class="hms-filter-group">
                            <label for="filter-record-type"><?php esc_html_e('Record Type:', 'hospital-management-system'); ?></label>
                            <select id="filter-record-type" class="hms-form-control">
                                <option value=""><?php esc_html_e('All Types', 'hospital-management-system'); ?></option>
                                <option value="consultation"><?php esc_html_e('Consultation', 'hospital-management-system'); ?></option>
                                <option value="follow_up"><?php esc_html_e('Follow-up', 'hospital-management-system'); ?></option>
                                <option value="emergency"><?php esc_html_e('Emergency', 'hospital-management-system'); ?></option>
                                <option value="surgery"><?php esc_html_e('Surgery', 'hospital-management-system'); ?></option>
                                <option value="discharge"><?php esc_html_e('Discharge', 'hospital-management-system'); ?></option>
                            </select>
                        </div>
                        
                        <div class="hms-filter-group">
                            <label for="filter-doctor"><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></label>
                            <select id="filter-doctor" class="hms-form-control">
                                <option value=""><?php esc_html_e('All Doctors', 'hospital-management-system'); ?></option>
                                <?php foreach ($doctors as $doctor) : ?>
                                    <option value="<?php echo esc_attr($doctor->id); ?>">
                                        <?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="hms-filter-group">
                            <label for="filter-diagnosis"><?php esc_html_e('Diagnosis Contains:', 'hospital-management-system'); ?></label>
                            <input type="text" id="filter-diagnosis" class="hms-form-control" 
                                   placeholder="<?php esc_attr_e('Filter by specific diagnosis...', 'hospital-management-system'); ?>">
                        </div>
                    </div>
                    
                    <div class="hms-filter-row">
                        <div class="hms-filter-group">
                            <label for="filter-date-from"><?php esc_html_e('Date From:', 'hospital-management-system'); ?></label>
                            <input type="date" id="filter-date-from" class="hms-form-control">
                        </div>
                        
                        <div class="hms-filter-group">
                            <label for="filter-date-to"><?php esc_html_e('Date To:', 'hospital-management-system'); ?></label>
                            <input type="date" id="filter-date-to" class="hms-form-control">
                        </div>
                        
                        <div class="hms-filter-group">
                            <label for="filter-sort"><?php esc_html_e('Sort By:', 'hospital-management-system'); ?></label>
                            <select id="filter-sort" class="hms-form-control">
                                <option value="date_desc"><?php esc_html_e('Date (Newest First)', 'hospital-management-system'); ?></option>
                                <option value="date_asc"><?php esc_html_e('Date (Oldest First)', 'hospital-management-system'); ?></option>
                                <option value="type"><?php esc_html_e('Record Type', 'hospital-management-system'); ?></option>
                                <option value="doctor"><?php esc_html_e('Doctor Name', 'hospital-management-system'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="hms-filter-actions">
                        <button type="button" id="apply-filters" class="hms-btn hms-btn-primary">
                            <span class="dashicons dashicons-search"></span>
                            <?php esc_html_e('Apply Filters', 'hospital-management-system'); ?>
                        </button>
                        <button type="button" id="clear-all-filters" class="hms-btn hms-btn-secondary">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Clear All', 'hospital-management-system'); ?>
                        </button>
                        <button type="button" id="export-filtered-records" class="hms-btn hms-btn-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Export Results', 'hospital-management-system'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Filter Status -->
            <div id="filter-status" class="hms-filter-status" style="display: none;">
                <span id="records-count-display"></span>
                <span class="hms-loading-spinner" id="filter-loading" style="display: none;"></span>
            </div>
        </div>

        <!-- Medical Records Timeline -->
        <div class="hms-medical-records-section">
            <div class="hms-section-header">
                <h3><?php esc_html_e('Medical Records Timeline', 'hospital-management-system'); ?></h3>
                <div class="hms-timeline-controls">
                    <button type="button" id="expand-all-records" class="hms-btn hms-btn-sm hms-btn-secondary">
                        <?php esc_html_e('Expand All', 'hospital-management-system'); ?>
                    </button>
                    <button type="button" id="collapse-all-records" class="hms-btn hms-btn-sm hms-btn-secondary">
                        <?php esc_html_e('Collapse All', 'hospital-management-system'); ?>
                    </button>
                </div>
            </div>
            
            <?php if (empty($all_medical_records)) : ?>
                <div class="hms-no-records">
                    <div class="hms-no-records-icon">
                        <span class="dashicons dashicons-clipboard"></span>
                    </div>
                    <h4><?php esc_html_e('No Medical Records Found', 'hospital-management-system'); ?></h4>
                    <p><?php esc_html_e('This patient has no medical records yet.', 'hospital-management-system'); ?></p>
                    <?php if (current_user_can('manage_clinical')) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&patient_id=' . $patient_id)); ?>" class="hms-btn hms-btn-primary">
                            <?php esc_html_e('Add First Medical Record', 'hospital-management-system'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <!-- Enhanced Timeline Display -->
                <div id="medical-records-timeline" class="hms-enhanced-timeline">
                    <?php foreach ($all_medical_records as $index => $record) : ?>
                        <div class="hms-timeline-item" 
                             data-record-id="<?php echo esc_attr($record->id); ?>"
                             data-record-type="<?php echo esc_attr($record->record_type); ?>"
                             data-doctor-id="<?php echo esc_attr($record->doctor_id); ?>"
                             data-created-date="<?php echo esc_attr($record->created_at); ?>"
                             data-diagnosis="<?php echo esc_attr(strtolower($record->diagnosis ?? '')); ?>"
                             data-symptoms="<?php echo esc_attr(strtolower($record->symptoms ?? '')); ?>"
                             data-treatment="<?php echo esc_attr(strtolower($record->treatment_plan ?? '')); ?>"
                             data-notes="<?php echo esc_attr(strtolower($record->notes ?? '')); ?>"
                             data-doctor-name="<?php echo esc_attr(strtolower($record->doctor_name ?? '')); ?>">
                             
                            <div class="hms-timeline-connector">
                                <?php if ($index === 0) : ?>
                                    <div class="hms-timeline-start"></div>
                                <?php endif; ?>
                                <div class="hms-timeline-marker hms-timeline-marker-<?php echo esc_attr($record->record_type); ?>">
                                    <span class="hms-timeline-icon">
                                        <?php
                                        $icon_map = [
                                            'consultation' => 'dashicons-stethoscope',
                                            'follow_up' => 'dashicons-update',
                                            'emergency' => 'dashicons-warning',
                                            'surgery' => 'dashicons-admin-tools',
                                            'discharge' => 'dashicons-migrate'
                                        ];
                                        $icon = $icon_map[$record->record_type] ?? 'dashicons-clipboard';
                                        ?>
                                        <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
                                    </span>
                                </div>
                                <?php if ($index < count($all_medical_records) - 1) : ?>
                                    <div class="hms-timeline-line"></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hms-timeline-content">
                                <div class="hms-timeline-header">
                                    <div class="hms-timeline-meta">
                                        <div class="hms-timeline-date">
                                            <span class="hms-date-primary">
                                                <?php echo esc_html(date_i18n('M j, Y', strtotime($record->created_at))); ?>
                                            </span>
                                            <span class="hms-date-time">
                                                <?php echo esc_html(date_i18n('g:i A', strtotime($record->created_at))); ?>
                                            </span>
                                        </div>
                                        <div class="hms-timeline-type">
                                            <span class="hms-record-badge hms-record-badge-<?php echo esc_attr($record->record_type); ?>">
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $record->record_type))); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="hms-timeline-actions">
                                        <button type="button" class="hms-btn hms-btn-sm hms-btn-ghost toggle-record-details" 
                                                data-record-id="<?php echo esc_attr($record->id); ?>">
                                            <span class="expand-text"><?php esc_html_e('Expand', 'hospital-management-system'); ?></span>
                                            <span class="collapse-text" style="display: none;"><?php esc_html_e('Collapse', 'hospital-management-system'); ?></span>
                                            <span class="dashicons dashicons-arrow-down-alt2 expand-icon"></span>
                                            <span class="dashicons dashicons-arrow-up-alt2 collapse-icon" style="display: none;"></span>
                                        </button>
                                        
                                        <?php if (current_user_can('manage_clinical')) : ?>
                                            <button type="button" class="hms-btn hms-btn-sm hms-btn-ghost edit-record-inline" 
                                                    data-record-id="<?php echo esc_attr($record->id); ?>"
                                                    title="<?php esc_attr_e('Quick Edit', 'hospital-management-system'); ?>">
                                                <span class="dashicons dashicons-edit"></span>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=view&id=' . $record->id)); ?>" 
                                           class="hms-btn hms-btn-sm hms-btn-ghost"
                                           title="<?php esc_attr_e('View Full Record', 'hospital-management-system'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Summary View (Always Visible) -->
                                <div class="hms-timeline-summary">
                                    <div class="hms-summary-doctor">
                                        <strong><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></strong> 
                                        <?php echo esc_html($record->doctor_name); ?>
                                    </div>
                                    
                                    <?php if (!empty($record->diagnosis)) : ?>
                                        <div class="hms-summary-diagnosis">
                                            <strong><?php esc_html_e('Diagnosis:', 'hospital-management-system'); ?></strong>
                                            <?php echo esc_html(wp_trim_words($record->diagnosis, 15, '...')); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Detailed View (Expandable) -->
                                <div class="hms-timeline-details" id="record-details-<?php echo esc_attr($record->id); ?>" style="display: none;">
                                    
                                    <?php if (!empty($record->symptoms)) : ?>
                                        <div class="hms-detail-section">
                                            <h4><?php esc_html_e('Symptoms', 'hospital-management-system'); ?></h4>
                                            <div class="hms-detail-content">
                                                <?php echo esc_html($record->symptoms); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record->diagnosis)) : ?>
                                        <div class="hms-detail-section">
                                            <h4><?php esc_html_e('Diagnosis', 'hospital-management-system'); ?></h4>
                                            <div class="hms-detail-content">
                                                <?php echo esc_html($record->diagnosis); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record->treatment_plan)) : ?>
                                        <div class="hms-detail-section">
                                            <h4><?php esc_html_e('Treatment Plan', 'hospital-management-system'); ?></h4>
                                            <div class="hms-detail-content">
                                                <?php echo esc_html($record->treatment_plan); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($record->notes)) : ?>
                                        <div class="hms-detail-section">
                                            <h4><?php esc_html_e('Additional Notes', 'hospital-management-system'); ?></h4>
                                            <div class="hms-detail-content">
                                                <?php echo esc_html($record->notes); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Audit Trail Information -->
                                    <div class="hms-detail-section hms-audit-trail">
                                        <h4><?php esc_html_e('Record Information', 'hospital-management-system'); ?></h4>
                                        <div class="hms-audit-info">
                                            <div class="hms-audit-item">
                                                <span class="hms-audit-label"><?php esc_html_e('Created:', 'hospital-management-system'); ?></span>
                                                <span class="hms-audit-value">
                                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->created_at))); ?>
                                                </span>
                                            </div>
                                            
                                            <?php if ($record->updated_at && $record->updated_at !== $record->created_at) : ?>
                                                <div class="hms-audit-item">
                                                    <span class="hms-audit-label"><?php esc_html_e('Last Modified:', 'hospital-management-system'); ?></span>
                                                    <span class="hms-audit-value">
                                                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->updated_at))); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="hms-audit-item">
                                                <span class="hms-audit-label"><?php esc_html_e('Record ID:', 'hospital-management-system'); ?></span>
                                                <span class="hms-audit-value">#<?php echo esc_html($record->id); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="hms-detail-actions">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=view&id=' . $record->id)); ?>" 
                                           class="hms-btn hms-btn-sm hms-btn-primary">
                                            <?php esc_html_e('View Full Record', 'hospital-management-system'); ?>
                                        </a>
                                        
                                        <?php if (current_user_can('manage_clinical')) : ?>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=edit&id=' . $record->id)); ?>" 
                                               class="hms-btn hms-btn-sm hms-btn-secondary">
                                                <?php esc_html_e('Edit Record', 'hospital-management-system'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Inline Edit Form (Hidden by default) -->
                                <?php if (current_user_can('manage_clinical')) : ?>
                                    <div class="hms-inline-edit-form" id="inline-edit-<?php echo esc_attr($record->id); ?>" style="display: none;">
                                        <form class="hms-quick-edit-form" data-record-id="<?php echo esc_attr($record->id); ?>">
                                            <?php wp_nonce_field('hms_quick_edit_nonce', 'quick_edit_nonce_' . $record->id); ?>
                                            
                                            <div class="hms-quick-edit-field">
                                                <label><?php esc_html_e('Diagnosis:', 'hospital-management-system'); ?></label>
                                                <textarea name="diagnosis" rows="2"><?php echo esc_textarea($record->diagnosis); ?></textarea>
                                            </div>
                                            
                                            <div class="hms-quick-edit-field">
                                                <label><?php esc_html_e('Treatment Plan:', 'hospital-management-system'); ?></label>
                                                <textarea name="treatment_plan" rows="2"><?php echo esc_textarea($record->treatment_plan); ?></textarea>
                                            </div>
                                            
                                            <div class="hms-quick-edit-field">
                                                <label><?php esc_html_e('Notes:', 'hospital-management-system'); ?></label>
                                                <textarea name="notes" rows="2"><?php echo esc_textarea($record->notes); ?></textarea>
                                            </div>
                                            
                                            <div class="hms-quick-edit-actions">
                                                <button type="submit" class="hms-btn hms-btn-sm hms-btn-primary">
                                                    <?php esc_html_e('Save Changes', 'hospital-management-system'); ?>
                                                </button>
                                                <button type="button" class="hms-btn hms-btn-sm hms-btn-secondary cancel-inline-edit">
                                                    <?php esc_html_e('Cancel', 'hospital-management-system'); ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Load More Records (if needed) -->
                <div id="load-more-container" class="hms-load-more-container" style="display: none;">
                    <button type="button" id="load-more-records" class="hms-btn hms-btn-secondary" 
                            data-patient-id="<?php echo esc_attr($patient_id); ?>" data-offset="0">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Load More Records', 'hospital-management-system'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Edit Modal (for complex edits) -->
<div id="hms-quick-edit-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content">
        <div class="hms-modal-header">
            <h3><?php esc_html_e('Quick Edit Medical Record', 'hospital-management-system'); ?></h3>
            <button type="button" class="hms-modal-close">&times;</button>
        </div>
        <div class="hms-modal-body">
            <!-- Modal content will be loaded dynamically -->
        </div>
    </div>
</div>