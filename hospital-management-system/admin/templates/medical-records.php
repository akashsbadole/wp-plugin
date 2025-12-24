<?php
/**
 * Admin template for medical records listing and management.
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

// Ensure the HMS_Medical_Records class is available
if (!class_exists('HMS_Medical_Records')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-medical-records.php';
}

$medical_records = new HMS_Medical_Records();

// Handle search and filtering
$filters = array();
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$patient_filter = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$record_type_filter = isset($_GET['record_type']) ? sanitize_text_field($_GET['record_type']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

if (!empty($search)) {
    $filters['search'] = $search;
}
if ($patient_filter > 0) {
    $filters['patient_id'] = $patient_filter;
}
if ($doctor_filter > 0) {
    $filters['doctor_id'] = $doctor_filter;
}
if (!empty($record_type_filter)) {
    $filters['record_type'] = $record_type_filter;
}
if (!empty($date_from)) {
    $filters['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $filters['date_to'] = $date_to;
}

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get records and total count
$records = $medical_records->get_medical_records($filters, $per_page, $offset);
$total_records = $medical_records->get_medical_records_count($filters);
$total_pages = ceil($total_records / $per_page);

// Get patients and doctors for filter dropdowns
if (!class_exists('HMS_Patient')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-patient.php';
}
if (!class_exists('HMS_Staff')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-staff.php';
}

$patient_obj = new HMS_Patient();
$staff_obj = new HMS_Staff();
$patients = $patient_obj->get_patients();
$doctors = $staff_obj->get_staff_by_role('doctor');
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Medical Records', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details')); ?>" class="page-title-action"><?php esc_html_e('Add New Record', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <?php
    // Display messages
    if (isset($_GET['message']) && isset($_GET['type'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = sanitize_text_field($_GET['type']);
        $class = $type === 'success' ? 'notice-success' : 'notice-error';
        echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <!-- Search and Filter Form -->
    <div class="hms-filters-section">
        <form method="get" action="">
            <input type="hidden" name="page" value="hms_medical_records">
            
            <div class="hms-filters-row">
                <div class="hms-filter-group">
                    <label for="search"><?php esc_html_e('Search:', 'hospital-management-system'); ?></label>
                    <input type="text" id="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search by patient name, diagnosis, or symptoms...', 'hospital-management-system'); ?>" class="regular-text">
                </div>
                
                <div class="hms-filter-group">
                    <label for="patient_id"><?php esc_html_e('Patient:', 'hospital-management-system'); ?></label>
                    <select name="patient_id" id="patient_id">
                        <option value=""><?php esc_html_e('All Patients', 'hospital-management-system'); ?></option>
                        <?php foreach ($patients as $patient) : ?>
                            <option value="<?php echo esc_attr($patient->id); ?>" <?php selected($patient_filter, $patient->id); ?>>
                                <?php echo esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="hms-filter-group">
                    <label for="doctor_id"><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></label>
                    <select name="doctor_id" id="doctor_id">
                        <option value=""><?php esc_html_e('All Doctors', 'hospital-management-system'); ?></option>
                        <?php foreach ($doctors as $doctor) : ?>
                            <option value="<?php echo esc_attr($doctor->id); ?>" <?php selected($doctor_filter, $doctor->id); ?>>
                                <?php echo esc_html($doctor->first_name . ' ' . $doctor->last_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="hms-filters-row">
                <div class="hms-filter-group">
                    <label for="record_type"><?php esc_html_e('Record Type:', 'hospital-management-system'); ?></label>
                    <select name="record_type" id="record_type">
                        <option value=""><?php esc_html_e('All Types', 'hospital-management-system'); ?></option>
                        <option value="consultation" <?php selected($record_type_filter, 'consultation'); ?>><?php esc_html_e('Consultation', 'hospital-management-system'); ?></option>
                        <option value="follow_up" <?php selected($record_type_filter, 'follow_up'); ?>><?php esc_html_e('Follow-up', 'hospital-management-system'); ?></option>
                        <option value="emergency" <?php selected($record_type_filter, 'emergency'); ?>><?php esc_html_e('Emergency', 'hospital-management-system'); ?></option>
                        <option value="surgery" <?php selected($record_type_filter, 'surgery'); ?>><?php esc_html_e('Surgery', 'hospital-management-system'); ?></option>
                        <option value="discharge" <?php selected($record_type_filter, 'discharge'); ?>><?php esc_html_e('Discharge', 'hospital-management-system'); ?></option>
                    </select>
                </div>
                
                <div class="hms-filter-group">
                    <label for="date_from"><?php esc_html_e('Date From:', 'hospital-management-system'); ?></label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                
                <div class="hms-filter-group">
                    <label for="date_to"><?php esc_html_e('Date To:', 'hospital-management-system'); ?></label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo esc_attr($date_to); ?>">
                </div>
                
                <div class="hms-filter-group">
                    <button type="submit" class="button"><?php esc_html_e('Filter', 'hospital-management-system'); ?></button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_records')); ?>" class="button"><?php esc_html_e('Clear', 'hospital-management-system'); ?></a>
                </div>
            </div>
        </form>
    </div>

    <!-- Records Table -->
    <div class="hms-table-container">
        <?php if (empty($records)) : ?>
            <div class="hms-no-records">
                <p><?php esc_html_e('No medical records found.', 'hospital-management-system'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details')); ?>" class="button button-primary"><?php esc_html_e('Add First Medical Record', 'hospital-management-system'); ?></a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column"><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Patient', 'hospital-management-system'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Doctor', 'hospital-management-system'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Type', 'hospital-management-system'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Diagnosis', 'hospital-management-system'); ?></th>
                        <th scope="col" class="manage-column"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($record->created_at))); ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html($record->patient_name); ?></strong><br>
                                <small><?php echo esc_html($record->patient_code); ?></small>
                            </td>
                            <td><?php echo esc_html($record->doctor_name); ?></td>
                            <td>
                                <span class="hms-record-type hms-record-type-<?php echo esc_attr($record->record_type); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $record->record_type))); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $diagnosis = !empty($record->diagnosis) ? $record->diagnosis : __('No diagnosis recorded', 'hospital-management-system');
                                echo esc_html(wp_trim_words($diagnosis, 10, '...'));
                                ?>
                            </td>
                            <td>
                                <div class="hms-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=view&id=' . $record->id)); ?>" 
                                       class="button button-small" title="<?php esc_attr_e('View Record', 'hospital-management-system'); ?>">
                                        <?php esc_html_e('View', 'hospital-management-system'); ?>
                                    </a>
                                    
                                    <?php if (current_user_can('manage_clinical')) : ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_medical_record_details&action=edit&id=' . $record->id)); ?>" 
                                           class="button button-small" title="<?php esc_attr_e('Edit Record', 'hospital-management-system'); ?>">
                                            <?php esc_html_e('Edit', 'hospital-management-system'); ?>
                                        </a>
                                        
                                        <form method="post" style="display: inline;" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this medical record?', 'hospital-management-system'); ?>');">
                                            <?php wp_nonce_field('hms_delete_medical_record_nonce', 'delete_nonce'); ?>
                                            <input type="hidden" name="record_id" value="<?php echo esc_attr($record->id); ?>">
                                            <button type="submit" name="delete_medical_record" class="button button-small button-link-delete" title="<?php esc_attr_e('Delete Record', 'hospital-management-system'); ?>">
                                                <?php esc_html_e('Delete', 'hospital-management-system'); ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1) : ?>
                <div class="hms-pagination">
                    <?php
                    $pagination_args = array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; Previous', 'hospital-management-system'),
                        'next_text' => __('Next &raquo;', 'hospital-management-system'),
                        'total' => $total_pages,
                        'current' => $current_page,
                        'show_all' => false,
                        'end_size' => 1,
                        'mid_size' => 2,
                        'type' => 'plain',
                    );
                    echo paginate_links($pagination_args);
                    ?>
                    
                    <div class="hms-pagination-info">
                        <?php
                        $start = ($current_page - 1) * $per_page + 1;
                        $end = min($current_page * $per_page, $total_records);
                        printf(
                            esc_html__('Showing %1$d to %2$d of %3$d records', 'hospital-management-system'),
                            $start,
                            $end,
                            $total_records
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.hms-filters-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
}

.hms-filters-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 10px;
}

.hms-filters-row:last-child {
    margin-bottom: 0;
}

.hms-filter-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.hms-filter-group label {
    font-weight: 600;
    margin-bottom: 5px;
}

.hms-filter-group input,
.hms-filter-group select {
    padding: 6px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.hms-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
}

.hms-no-records {
    text-align: center;
    padding: 40px 20px;
}

.hms-no-records p {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

.hms-record-type {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

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

.hms-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.hms-pagination {
    padding: 15px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hms-pagination-info {
    color: #666;
    font-size: 14px;
}

@media (max-width: 768px) {
    .hms-filters-row {
        flex-direction: column;
    }
    
    .hms-filter-group {
        min-width: auto;
    }
    
    .hms-actions {
        flex-direction: column;
    }
    
    .hms-pagination {
        flex-direction: column;
        gap: 10px;
    }
}
</style>