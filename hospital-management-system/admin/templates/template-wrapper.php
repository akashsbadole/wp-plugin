<?php
/**
 * HMS Template Wrapper
 * 
 * Provides a standardized wrapper for all HMS admin pages with
 * consistent header, breadcrumbs, and footer structure.
 * 
 * @package Hospital_Management_System
 * @subpackage Admin/Templates
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render HMS page with standardized wrapper
 * 
 * @param array $args {
 *     Page configuration arguments
 *     
 *     @type string $title Page title
 *     @type string $description Optional page description
 *     @type array $breadcrumbs Array of breadcrumb items
 *     @type string $add_new_url URL for "Add New" button
 *     @type string $add_new_text Text for "Add New" button
 *     @type string $content_template Path to content template
 *     @type array $template_vars Variables to pass to content template
 *     @type bool $show_header Whether to show page header (default: true)
 *     @type bool $show_breadcrumbs Whether to show breadcrumbs (default: true)
 *     @type string $page_class Additional CSS class for page wrapper
 * }
 */
function hms_render_admin_page($args = array()) {
    $defaults = array(
        'title' => '',
        'description' => '',
        'breadcrumbs' => array(),
        'add_new_url' => '',
        'add_new_text' => '',
        'content_template' => '',
        'template_vars' => array(),
        'show_header' => true,
        'show_breadcrumbs' => true,
        'page_class' => '',
    );
    
    $args = wp_parse_args($args, $defaults);
    
    // Extract template variables for use in content template
    if (!empty($args['template_vars'])) {
        extract($args['template_vars'], EXTR_SKIP);
    }
    ?>
    <div class="wrap hms-wrap <?php echo esc_attr($args['page_class']); ?>">
        
        <?php if ($args['show_breadcrumbs'] && !empty($args['breadcrumbs'])): ?>
            <?php HMS_UI_Framework::render_breadcrumbs($args['breadcrumbs']); ?>
        <?php endif; ?>
        
        <?php if ($args['show_header']): ?>
            <?php HMS_UI_Framework::render_page_header(
                $args['title'], 
                $args['add_new_url'], 
                $args['add_new_text']
            ); ?>
        <?php endif; ?>
        
        <?php if ($args['description']): ?>
            <div class="hms-page-description">
                <p class="hms-text-muted"><?php echo esc_html($args['description']); ?></p>
            </div>
        <?php endif; ?>
        
        <?php
        // Display WordPress admin notices
        settings_errors();
        ?>
        
        <div class="hms-page-content">
            <?php
            if ($args['content_template'] && file_exists($args['content_template'])) {
                include $args['content_template'];
            } else {
                echo '<div class="hms-notice hms-notice-error"><p>' . 
                     esc_html__('Content template not found.', 'hospital-management-system') . 
                     '</p></div>';
            }
            ?>
        </div>
        
    </div>
    <?php
}

/**
 * Generate breadcrumbs for HMS pages
 */
function hms_get_page_breadcrumbs($current_page = '') {
    $breadcrumbs = array();
    
    // Always start with Dashboard
    $breadcrumbs[] = array(
        'text' => __('Dashboard', 'hospital-management-system'),
        'url' => admin_url('admin.php?page=hms_dashboard')
    );
    
    // Page-specific breadcrumbs
    $page_map = array(
        // Patient Management
        'hms_patients' => array(
            'text' => __('Patients', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_patient_registration' => array(
            'text' => __('Patient Registration', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Patients', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_patients')
            )
        ),
        'hms_comprehensive_medical_history' => array(
            'text' => __('Medical History', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Patients', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_patients')
            )
        ),
        
        // Appointment Management
        'hms_appointments' => array(
            'text' => __('Appointments', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_appointment_details' => array(
            'text' => __('Appointment Details', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Appointments', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_appointments')
            )
        ),
        
        // Clinical Management
        'hms_clinical' => array(
            'text' => __('Clinical Dashboard', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_medical_records' => array(
            'text' => __('Medical Records', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Clinical', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_clinical')
            )
        ),
        'hms_prescriptions' => array(
            'text' => __('Prescriptions', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Clinical', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_clinical')
            )
        ),
        'hms_prescription_details' => array(
            'text' => __('Prescription Details', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Prescriptions', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_prescriptions')
            )
        ),
        
        // Billing Management
        'hms_billing' => array(
            'text' => __('Billing', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_bill_details' => array(
            'text' => __('Bill Details', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Billing', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_billing')
            )
        ),
        'hms_insurance' => array(
            'text' => __('Insurance', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Billing', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_billing')
            )
        ),
        
        // Pharmacy Management
        'hms_pharmacy' => array(
            'text' => __('Pharmacy', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_medicine_inventory' => array(
            'text' => __('Medicine Inventory', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Pharmacy', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_pharmacy')
            )
        ),
        
        // Laboratory Management
        'hms_lab_tests' => array(
            'text' => __('Lab Tests', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_lab_test_order' => array(
            'text' => __('Lab Test Order', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Lab Tests', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_lab_tests')
            )
        ),
        'hms_lab_results' => array(
            'text' => __('Lab Results', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Lab Tests', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_lab_tests')
            )
        ),
        
        // Staff Management
        'hms_staff' => array(
            'text' => __('Staff', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_staff_details' => array(
            'text' => __('Staff Details', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Staff', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_staff')
            )
        ),
        
        // Queue Management
        'hms_queue' => array(
            'text' => __('Queue Management', 'hospital-management-system'),
            'parent' => null
        ),
        
        // Reports
        'hms_reports' => array(
            'text' => __('Reports', 'hospital-management-system'),
            'parent' => null
        ),
        'hms_reports_dashboard' => array(
            'text' => __('Reports Dashboard', 'hospital-management-system'),
            'parent' => array(
                'text' => __('Reports', 'hospital-management-system'),
                'url' => admin_url('admin.php?page=hms_reports')
            )
        ),
        
        // Settings
        'hms_settings' => array(
            'text' => __('Settings', 'hospital-management-system'),
            'parent' => null
        ),
        
        // Data Management
        'hms_export_import_data' => array(
            'text' => __('Export/Import Data', 'hospital-management-system'),
            'parent' => null
        ),
    );
    
    if (isset($page_map[$current_page])) {
        $page_info = $page_map[$current_page];
        
        // Add parent if exists
        if ($page_info['parent']) {
            $breadcrumbs[] = $page_info['parent'];
        }
        
        // Add current page
        $breadcrumbs[] = array(
            'text' => $page_info['text'],
            'url' => null // Current page, no URL
        );
    }
    
    return $breadcrumbs;
}

/**
 * Get standardized action buttons for table rows
 */
function hms_get_table_actions($type, $item_id) {
    $actions = array();
    
    switch ($type) {
        case 'patient':
            $actions = array(
                array(
                    'text' => __('View', 'hospital-management-system'),
                    'title' => __('View Patient Details', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_patient_registration&id={id}'),
                    'type' => 'info'
                ),
                array(
                    'text' => __('History', 'hospital-management-system'),
                    'title' => __('Medical History', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_comprehensive_medical_history&patient_id={id}'),
                    'type' => 'primary'
                ),
                array(
                    'text' => __('Appointments', 'hospital-management-system'),
                    'title' => __('View Appointments', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_appointments&patient_id={id}'),
                    'type' => 'secondary'
                ),
                array(
                    'text' => __('Delete', 'hospital-management-system'),
                    'title' => __('Delete Patient', 'hospital-management-system'),
                    'url' => '#',
                    'type' => 'danger',
                    'confirm' => __('Are you sure you want to delete this patient? This action cannot be undone.', 'hospital-management-system')
                )
            );
            break;
            
        case 'appointment':
            $actions = array(
                array(
                    'text' => __('View', 'hospital-management-system'),
                    'title' => __('View Appointment Details', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_appointment_details&id={id}'),
                    'type' => 'info'
                ),
                array(
                    'text' => __('Edit', 'hospital-management-system'),
                    'title' => __('Edit Appointment', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_appointment_details&id={id}&action=edit'),
                    'type' => 'primary'
                ),
                array(
                    'text' => __('Cancel', 'hospital-management-system'),
                    'title' => __('Cancel Appointment', 'hospital-management-system'),
                    'url' => '#',
                    'type' => 'warning',
                    'confirm' => __('Are you sure you want to cancel this appointment?', 'hospital-management-system')
                ),
                array(
                    'text' => __('Delete', 'hospital-management-system'),
                    'title' => __('Delete Appointment', 'hospital-management-system'),
                    'url' => '#',
                    'type' => 'danger',
                    'confirm' => __('Are you sure you want to delete this appointment? This action cannot be undone.', 'hospital-management-system')
                )
            );
            break;
            
        case 'prescription':
            $actions = array(
                array(
                    'text' => __('View', 'hospital-management-system'),
                    'title' => __('View Prescription Details', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_prescription_details&id={id}'),
                    'type' => 'info'
                ),
                array(
                    'text' => __('Edit', 'hospital-management-system'),
                    'title' => __('Edit Prescription', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_prescription_details&id={id}&action=edit'),
                    'type' => 'primary'
                ),
                array(
                    'text' => __('Print', 'hospital-management-system'),
                    'title' => __('Print Prescription', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_prescription_details&id={id}&action=print'),
                    'type' => 'secondary'
                )
            );
            break;
            
        case 'bill':
            $actions = array(
                array(
                    'text' => __('View', 'hospital-management-system'),
                    'title' => __('View Bill Details', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_bill_details&id={id}'),
                    'type' => 'info'
                ),
                array(
                    'text' => __('Edit', 'hospital-management-system'),
                    'title' => __('Edit Bill', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_bill_details&id={id}&action=edit'),
                    'type' => 'primary'
                ),
                array(
                    'text' => __('Invoice', 'hospital-management-system'),
                    'title' => __('Generate Invoice', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_view_invoice&bill_id={id}'),
                    'type' => 'success'
                )
            );
            break;
            
        case 'staff':
            $actions = array(
                array(
                    'text' => __('View', 'hospital-management-system'),
                    'title' => __('View Staff Details', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_staff_details&id={id}'),
                    'type' => 'info'
                ),
                array(
                    'text' => __('Edit', 'hospital-management-system'),
                    'title' => __('Edit Staff', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_staff_details&id={id}&action=edit'),
                    'type' => 'primary'
                ),
                array(
                    'text' => __('Schedule', 'hospital-management-system'),
                    'title' => __('Manage Schedule', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_staff_schedule&staff_id={id}'),
                    'type' => 'secondary'
                )
            );
            break;
    }
    
    return HMS_UI_Framework::render_action_buttons($actions, $item_id);
}

/**
 * Render standardized data table
 */
function hms_render_data_table($args = array()) {
    $defaults = array(
        'id' => 'hms-data-table',
        'class' => 'hms-table hms-table-striped hms-table-hover',
        'columns' => array(),
        'data' => array(),
        'ajax_url' => '',
        'ajax_action' => '',
        'show_search' => true,
        'show_pagination' => true,
        'page_length' => 25,
        'responsive' => true,
    );
    
    $args = wp_parse_args($args, $defaults);
    ?>
    <div class="hms-table-responsive">
        <table id="<?php echo esc_attr($args['id']); ?>" class="<?php echo esc_attr($args['class']); ?>" style="width:100%">
            <thead>
                <tr>
                    <?php foreach ($args['columns'] as $column): ?>
                        <th><?php echo esc_html($column['title']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($args['data'])): ?>
                    <?php foreach ($args['data'] as $row): ?>
                        <tr>
                            <?php foreach ($args['columns'] as $column): ?>
                                <td>
                                    <?php 
                                    if (isset($column['render']) && is_callable($column['render'])) {
                                        echo call_user_func($column['render'], $row[$column['data']], $row);
                                    } else {
                                        echo esc_html($row[$column['data']] ?? '');
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo count($args['columns']); ?>" class="hms-text-center">
                            <?php esc_html_e('No data available', 'hospital-management-system'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($args['ajax_url']): ?>
        <script>
        jQuery(document).ready(function($) {
            $('#<?php echo esc_js($args['id']); ?>').DataTable({
                ajax: {
                    url: '<?php echo esc_url($args['ajax_url']); ?>',
                    type: 'POST',
                    data: {
                        action: '<?php echo esc_js($args['ajax_action']); ?>',
                        nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>'
                    }
                },
                columns: <?php echo json_encode(array_map(function($col) {
                    return array('data' => $col['data']);
                }, $args['columns'])); ?>,
                responsive: <?php echo $args['responsive'] ? 'true' : 'false'; ?>,
                pageLength: <?php echo intval($args['page_length']); ?>,
                searching: <?php echo $args['show_search'] ? 'true' : 'false'; ?>,
                paging: <?php echo $args['show_pagination'] ? 'true' : 'false'; ?>
            });
        });
        </script>
    <?php endif; ?>
    <?php
}