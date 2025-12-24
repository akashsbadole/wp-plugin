<div class="wrap hms-admin-wrap">
    <h1><?php esc_html_e('Lab & Radiology Tests', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order'); ?>" class="page-title-action"><?php esc_html_e('Order New Test', 'hospital-management-system'); ?></a>
    </h1>

    <?php if (isset($_GET['message'])) : ?>
        <div id="message" class="notice notice-<?php echo esc_attr(sanitize_text_field($_GET['type'])); ?> is-dismissible">
            <p><?php echo wp_kses_post(sanitize_text_field($_GET['message'])); ?></p>
        </div>
    <?php endif; ?>

    <div class="hms-filters-section">
        <form method="get" class="hms-filter-form">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>">
            
            <div class="hms-filter-row">
                <?php
                // Status filter dropdown
                $current_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                ?>
                <div class="hms-filter-group">
                    <label for="status_filter"><?php esc_html_e('Status:', 'hospital-management-system'); ?></label>
                    <select name="status" id="status_filter" class="hms-select">
                        <option value=""><?php esc_html_e('All Statuses', 'hospital-management-system'); ?></option>
                        <option value="pending" <?php selected($current_status, 'pending'); ?>><?php esc_html_e('Pending', 'hospital-management-system'); ?></option>
                        <option value="in-progress" <?php selected($current_status, 'in-progress'); ?>><?php esc_html_e('In Progress', 'hospital-management-system'); ?></option>
                        <option value="completed" <?php selected($current_status, 'completed'); ?>><?php esc_html_e('Completed', 'hospital-management-system'); ?></option>
                    </select>
                </div>
                
                <?php
                // Search input
                $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                ?>
                <div class="hms-filter-group">
                    <label for="search-input"><?php esc_html_e('Search:', 'hospital-management-system'); ?></label>
                    <input type="search" id="search-input" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php esc_html_e('Patient name or test name...', 'hospital-management-system'); ?>" class="hms-search-input">
                </div>
                
                <div class="hms-filter-actions">
                    <input type="submit" class="button button-primary" value="<?php esc_html_e('Filter', 'hospital-management-system'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=' . esc_attr($_REQUEST['page'])); ?>" class="button"><?php esc_html_e('Reset', 'hospital-management-system'); ?></a>
                </div>
            </div>
        </form>
    </div>

    <div class="hms-table-container">
        <table class="wp-list-table widefat fixed striped hms-data-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id"><?php esc_html_e('Order ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-patient"><?php esc_html_e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-test"><?php esc_html_e('Test Name', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-category"><?php esc_html_e('Category', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-doctor"><?php esc_html_e('Ordered By', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php esc_html_e('Order Date', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $hms_lab_radiology; // Assuming this instance is available globally or passed
                
                $status_filter = !empty($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                $search_term = !empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                $lab_test_orders = $hms_lab_radiology->get_lab_test_orders($status_filter, $search_term);

                if (!empty($lab_test_orders)) {
                    foreach ($lab_test_orders as $order) {
                        $patient_name = trim($order->patient_first_name . ' ' . $order->patient_last_name);
                        $doctor_name = trim($order->doctor_first_name . ' ' . $order->doctor_last_name);
                        
                        $status_class = 'hms-status-' . str_replace('-', '_', $order->status);
                        $status_label = ucwords(str_replace('-', ' ', $order->status));
                        
                        $order_date = date('M j, Y', strtotime($order->order_date));
                        ?>
                        <tr>
                            <td class="column-id"><?php echo esc_html($order->id); ?></td>
                            <td class="column-patient">
                                <strong><?php echo esc_html($patient_name ?: __('N/A', 'hospital-management-system')); ?></strong>
                            </td>
                            <td class="column-test">
                                <strong><?php echo esc_html($order->test_name ?: __('N/A', 'hospital-management-system')); ?></strong>
                            </td>
                            <td class="column-category"><?php echo esc_html($order->category ?: __('N/A', 'hospital-management-system')); ?></td>
                            <td class="column-doctor"><?php echo esc_html($doctor_name ?: __('N/A', 'hospital-management-system')); ?></td>
                            <td class="column-status">
                                <span class="hms-status-badge <?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                            <td class="column-date"><?php echo esc_html($order_date); ?></td>
                            <td class="column-actions">
                                <div class="hms-action-buttons">
                                    <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order&id=' . $order->id); ?>" 
                                       class="button button-small button-primary" 
                                       title="<?php esc_attr_e('View/Edit Test Order', 'hospital-management-system'); ?>">
                                        <?php esc_html_e('Edit', 'hospital-management-system'); ?>
                                    </a>
                                    
                                    <?php if (in_array($order->status, ['pending', 'in-progress'])): ?>
                                        <a href="<?php echo admin_url('admin.php?page=hms_lab_results&order_id=' . $order->id); ?>" 
                                           class="button button-small" 
                                           title="<?php esc_attr_e('Enter Test Results', 'hospital-management-system'); ?>">
                                            <?php esc_html_e('Results', 'hospital-management-system'); ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($order->status === 'completed' && !empty($order->results)): ?>
                                        <div class="hms-report-dropdown">
                                            <button type="button" class="button button-small hms-report-btn" 
                                                    title="<?php esc_attr_e('View Lab Report', 'hospital-management-system'); ?>">
                                                <?php esc_html_e('Report', 'hospital-management-system'); ?> ▼
                                            </button>
                                            <div class="hms-report-menu">
                                                <a href="<?php echo admin_url('admin.php?page=hms_lab_results&action=generate_report&order_id=' . $order->id . '&format=html'); ?>" 
                                                   target="_blank"><?php esc_html_e('View Report', 'hospital-management-system'); ?></a>
                                                <a href="<?php echo admin_url('admin.php?page=hms_lab_results&action=generate_report&order_id=' . $order->id . '&format=print'); ?>" 
                                                   target="_blank"><?php esc_html_e('Print Report', 'hospital-management-system'); ?></a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this test order?', 'hospital-management-system'); ?>');">
                                        <?php wp_nonce_field('hms_delete_lab_test_nonce', 'delete_nonce'); ?>
                                        <input type="hidden" name="test_id" value="<?php echo esc_attr($order->id); ?>">
                                        <input type="hidden" name="delete_lab_test" value="1">
                                        <button type="submit" class="button button-small button-link-delete" 
                                                title="<?php esc_attr_e('Delete Test Order', 'hospital-management-system'); ?>">
                                            <?php esc_html_e('Delete', 'hospital-management-system'); ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="8" class="hms-no-data">
                            <div class="hms-no-data-message">
                                <p><?php esc_html_e('No lab test orders found.', 'hospital-management-system'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order'); ?>" class="button button-primary">
                                    <?php esc_html_e('Order First Test', 'hospital-management-system'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.hms-report-dropdown {
    position: relative;
    display: inline-block;
}

.hms-report-btn {
    cursor: pointer;
}

.hms-report-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    border-radius: 3px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1000;
    min-width: 120px;
    right: 0;
    top: 100%;
    margin-top: 2px;
}

.hms-report-menu a {
    display: block;
    padding: 8px 12px;
    text-decoration: none;
    color: #333;
    border-bottom: 1px solid #eee;
}

.hms-report-menu a:last-child {
    border-bottom: none;
}

.hms-report-menu a:hover {
    background: #f5f5f5;
    color: #0073aa;
}

.hms-report-dropdown:hover .hms-report-menu {
    display: block;
}

/* Enhanced status badges */
.hms-status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: bold;
    text-transform: uppercase;
}

.hms-status-pending {
    background: #fef7e0;
    color: #8a6914;
    border: 1px solid #f4d03f;
}

.hms-status-in_progress {
    background: #e3f2fd;
    color: #1565c0;
    border: 1px solid #90caf9;
}

.hms-status-completed {
    background: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #81c784;
}

.hms-status-cancelled {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Enhanced dropdown functionality
    $('.hms-report-dropdown').on('click', '.hms-report-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close other dropdowns
        $('.hms-report-menu').not($(this).siblings('.hms-report-menu')).hide();
        
        // Toggle current dropdown
        $(this).siblings('.hms-report-menu').toggle();
    });
    
    // Close dropdowns when clicking outside
    $(document).on('click', function() {
        $('.hms-report-menu').hide();
    });
    
    // Prevent dropdown from closing when clicking inside
    $('.hms-report-menu').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>
