<?php
/**
 * Medicine Inventory Management Template
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/templates
 * @author     HMS Development Team
 * @since      1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$pharmacy = new HMS_Pharmacy();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        check_admin_referer('hms_admin_nonce', 'nonce');
        
        switch ($_POST['action']) {
            case 'add_medicine':
                $result = $pharmacy->create_medicine($_POST);
                if ($result) {
                    $message = __('Medicine added successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = __('Failed to add medicine.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
                
            case 'update_stock':
                $medicine_id = intval($_POST['medicine_id']);
                $quantity_change = intval($_POST['quantity_change']);
                $reason = sanitize_text_field($_POST['reason']);
                
                $result = $pharmacy->update_stock($medicine_id, $quantity_change, $reason);
                if ($result && !is_wp_error($result)) {
                    $message = __('Stock updated successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = is_wp_error($result) ? $result->get_error_message() : __('Failed to update stock.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
        }
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$stock_filter = isset($_GET['stock_filter']) ? sanitize_text_field($_GET['stock_filter']) : '';

// Get medicines
$per_page = 20;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

$medicines = $pharmacy->get_medicines($search, $category, $status, $offset, $per_page);
$total_medicines = $pharmacy->get_medicines_count($search, $category, $status);

// Filter by stock level if requested
if ($stock_filter === 'low_stock') {
    $medicines = $pharmacy->get_low_stock_medicines();
    $total_medicines = count($medicines);
} elseif ($stock_filter === 'expiring') {
    $medicines = $pharmacy->get_expiring_medicines(30);
    $total_medicines = count($medicines);
}

// Get categories for filter dropdown
$categories = $wpdb->get_col("SELECT DISTINCT category FROM {$wpdb->prefix}hms_medicines WHERE category IS NOT NULL AND category != '' ORDER BY category");
?>

<div class="wrap">
    <h1><?php _e('Medicine Inventory Management', 'hospital-management-system'); ?>
        <button type="button" class="page-title-action" id="add-medicine-btn"><?php _e('Add Medicine', 'hospital-management-system'); ?></button>
    </h1>

    <?php if (isset($message)): ?>
        <div id="message" class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Inventory Alerts -->
    <div class="hms-inventory-alerts" style="margin-bottom: 20px;">
        <?php
        $low_stock_count = count($pharmacy->get_low_stock_medicines());
        $expiring_count = count($pharmacy->get_expiring_medicines(30));
        ?>
        
        <?php if ($low_stock_count > 0): ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php _e('Low Stock Alert:', 'hospital-management-system'); ?></strong>
                    <?php printf(_n('%d medicine is running low on stock.', '%d medicines are running low on stock.', $low_stock_count, 'hospital-management-system'), $low_stock_count); ?>
                    <a href="<?php echo admin_url('admin.php?page=hms_medicine_inventory&stock_filter=low_stock'); ?>" class="button button-small"><?php _e('View Low Stock', 'hospital-management-system'); ?></a>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($expiring_count > 0): ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php _e('Expiry Alert:', 'hospital-management-system'); ?></strong>
                    <?php printf(_n('%d medicine is expiring within 30 days.', '%d medicines are expiring within 30 days.', $expiring_count, 'hospital-management-system'), $expiring_count); ?>
                    <a href="<?php echo admin_url('admin.php?page=hms_medicine_inventory&stock_filter=expiring'); ?>" class="button button-small"><?php _e('View Expiring', 'hospital-management-system'); ?></a>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <form method="get" class="hms-filters">
        <input type="hidden" name="page" value="hms_medicine_inventory">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search medicines...', 'hospital-management-system'); ?>">
                
                <select name="category">
                    <option value=""><?php _e('All Categories', 'hospital-management-system'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>" <?php selected($category, $cat); ?>><?php echo esc_html($cat); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status">
                    <option value=""><?php _e('All Status', 'hospital-management-system'); ?></option>
                    <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'hospital-management-system'); ?></option>
                    <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inactive', 'hospital-management-system'); ?></option>
                </select>
                
                <select name="stock_filter">
                    <option value=""><?php _e('All Stock Levels', 'hospital-management-system'); ?></option>
                    <option value="low_stock" <?php selected($stock_filter, 'low_stock'); ?>><?php _e('Low Stock', 'hospital-management-system'); ?></option>
                    <option value="expiring" <?php selected($stock_filter, 'expiring'); ?>><?php _e('Expiring Soon', 'hospital-management-system'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hms_medicine_inventory'); ?>" class="button"><?php _e('Clear', 'hospital-management-system'); ?></a>
            </div>
        </div>
    </form>

    <!-- Medicines Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Name', 'hospital-management-system'); ?></th>
                <th><?php _e('Generic Name', 'hospital-management-system'); ?></th>
                <th><?php _e('Category', 'hospital-management-system'); ?></th>
                <th><?php _e('Manufacturer', 'hospital-management-system'); ?></th>
                <th><?php _e('Stock Quantity', 'hospital-management-system'); ?></th>
                <th><?php _e('Reorder Level', 'hospital-management-system'); ?></th>
                <th><?php _e('Unit Price', 'hospital-management-system'); ?></th>
                <th><?php _e('Selling Price', 'hospital-management-system'); ?></th>
                <th><?php _e('Expiry Date', 'hospital-management-system'); ?></th>
                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($medicines): ?>
                <?php foreach ($medicines as $medicine): ?>
                    <tr>
                        <td><strong><?php echo esc_html($medicine->name); ?></strong></td>
                        <td><?php echo esc_html($medicine->generic_name ?: '-'); ?></td>
                        <td><?php echo esc_html($medicine->category ?: '-'); ?></td>
                        <td><?php echo esc_html($medicine->manufacturer ?: '-'); ?></td>
                        <td>
                            <span class="stock-quantity <?php echo ($medicine->stock_quantity <= $medicine->reorder_level) ? 'low-stock' : ''; ?>">
                                <?php echo esc_html($medicine->stock_quantity); ?> <?php echo esc_html($medicine->unit ?: ''); ?>
                            </span>
                            <?php if ($medicine->stock_quantity <= $medicine->reorder_level): ?>
                                <span class="dashicons dashicons-warning" style="color: #d63638;" title="<?php _e('Low Stock', 'hospital-management-system'); ?>"></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($medicine->reorder_level); ?></td>
                        <td><?php echo esc_html(number_format($medicine->unit_price, 2)); ?></td>
                        <td><?php echo esc_html(number_format($medicine->selling_price, 2)); ?></td>
                        <td>
                            <?php if ($medicine->expiry_date): ?>
                                <?php 
                                $expiry_date = new DateTime($medicine->expiry_date);
                                $today = new DateTime();
                                $days_to_expiry = $today->diff($expiry_date)->days;
                                $is_expiring = $expiry_date <= $today->add(new DateInterval('P30D'));
                                ?>
                                <span class="<?php echo $is_expiring ? 'expiring-soon' : ''; ?>">
                                    <?php echo esc_html($expiry_date->format('Y-m-d')); ?>
                                </span>
                                <?php if ($is_expiring): ?>
                                    <span class="dashicons dashicons-clock" style="color: #d63638;" title="<?php _e('Expiring Soon', 'hospital-management-system'); ?>"></span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $medicine->status === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo esc_html(ucfirst($medicine->status)); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="button button-small edit-medicine-btn" data-id="<?php echo esc_attr($medicine->id); ?>"><?php _e('Edit', 'hospital-management-system'); ?></button>
                            <button type="button" class="button button-small update-stock-btn" data-id="<?php echo esc_attr($medicine->id); ?>" data-name="<?php echo esc_attr($medicine->name); ?>"><?php _e('Update Stock', 'hospital-management-system'); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11"><?php _e('No medicines found.', 'hospital-management-system'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_medicines > $per_page): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $total_pages = ceil($total_medicines / $per_page);
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $page
                );
                echo paginate_links($pagination_args);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Medicine Modal -->
<div id="add-medicine-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content">
        <div class="hms-modal-header">
            <h2><?php _e('Add Medicine', 'hospital-management-system'); ?></h2>
            <span class="hms-modal-close">&times;</span>
        </div>
        <div class="hms-modal-body">
            <form id="add-medicine-form" method="post">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="add_medicine">
                
                <table class="form-table">
                    <tr>
                        <th><label for="name"><?php _e('Medicine Name', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="text" id="name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="generic_name"><?php _e('Generic Name', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="generic_name" name="generic_name" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="category"><?php _e('Category', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="text" id="category" name="category" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="manufacturer"><?php _e('Manufacturer', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="manufacturer" name="manufacturer" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="unit"><?php _e('Unit', 'hospital-management-system'); ?> *</label></th>
                        <td>
                            <select id="unit" name="unit" required>
                                <option value=""><?php _e('Select Unit', 'hospital-management-system'); ?></option>
                                <option value="tablet"><?php _e('Tablet', 'hospital-management-system'); ?></option>
                                <option value="capsule"><?php _e('Capsule', 'hospital-management-system'); ?></option>
                                <option value="ml"><?php _e('ML', 'hospital-management-system'); ?></option>
                                <option value="mg"><?php _e('MG', 'hospital-management-system'); ?></option>
                                <option value="bottle"><?php _e('Bottle', 'hospital-management-system'); ?></option>
                                <option value="vial"><?php _e('Vial', 'hospital-management-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="strength"><?php _e('Strength', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="strength" name="strength" class="regular-text" placeholder="e.g., 500mg, 10ml"></td>
                    </tr>
                    <tr>
                        <th><label for="stock_quantity"><?php _e('Initial Stock Quantity', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="number" id="stock_quantity" name="stock_quantity" class="regular-text" min="0" required></td>
                    </tr>
                    <tr>
                        <th><label for="reorder_level"><?php _e('Reorder Level', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="number" id="reorder_level" name="reorder_level" class="regular-text" min="0" value="10" required></td>
                    </tr>
                    <tr>
                        <th><label for="unit_price"><?php _e('Unit Price', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="number" id="unit_price" name="unit_price" class="regular-text" step="0.01" min="0" required></td>
                    </tr>
                    <tr>
                        <th><label for="selling_price"><?php _e('Selling Price', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="number" id="selling_price" name="selling_price" class="regular-text" step="0.01" min="0" required></td>
                    </tr>
                    <tr>
                        <th><label for="batch_number"><?php _e('Batch Number', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="batch_number" name="batch_number" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="expiry_date"><?php _e('Expiry Date', 'hospital-management-system'); ?></label></th>
                        <td><input type="date" id="expiry_date" name="expiry_date" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="description"><?php _e('Description', 'hospital-management-system'); ?></label></th>
                        <td><textarea id="description" name="description" class="large-text" rows="3"></textarea></td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Add Medicine', 'hospital-management-system'); ?>">
                    <button type="button" class="button hms-modal-close"><?php _e('Cancel', 'hospital-management-system'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="update-stock-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content">
        <div class="hms-modal-header">
            <h2><?php _e('Update Stock', 'hospital-management-system'); ?></h2>
            <span class="hms-modal-close">&times;</span>
        </div>
        <div class="hms-modal-body">
            <form id="update-stock-form" method="post">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="update_stock">
                <input type="hidden" id="stock_medicine_id" name="medicine_id">
                
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Medicine', 'hospital-management-system'); ?></label></th>
                        <td><strong id="stock_medicine_name"></strong></td>
                    </tr>
                    <tr>
                        <th><label for="quantity_change"><?php _e('Quantity Change', 'hospital-management-system'); ?> *</label></th>
                        <td>
                            <input type="number" id="quantity_change" name="quantity_change" class="regular-text" required>
                            <p class="description"><?php _e('Enter positive number to add stock, negative to reduce stock.', 'hospital-management-system'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="reason"><?php _e('Reason', 'hospital-management-system'); ?> *</label></th>
                        <td>
                            <select id="reason" name="reason" required>
                                <option value=""><?php _e('Select Reason', 'hospital-management-system'); ?></option>
                                <option value="Purchase"><?php _e('Purchase', 'hospital-management-system'); ?></option>
                                <option value="Return"><?php _e('Return', 'hospital-management-system'); ?></option>
                                <option value="Adjustment"><?php _e('Stock Adjustment', 'hospital-management-system'); ?></option>
                                <option value="Damage"><?php _e('Damage/Loss', 'hospital-management-system'); ?></option>
                                <option value="Expiry"><?php _e('Expiry', 'hospital-management-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Update Stock', 'hospital-management-system'); ?>">
                    <button type="button" class="button hms-modal-close"><?php _e('Cancel', 'hospital-management-system'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<style>
.low-stock {
    color: #d63638;
    font-weight: bold;
}

.expiring-soon {
    color: #d63638;
    font-weight: bold;
}

.badge {
    padding: 0.25em 0.4em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    color: #fff;
}

.badge-success { background-color: #28a745; }
.badge-secondary { background-color: #6c757d; }

.hms-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.hms-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 4px;
}

.hms-modal-header {
    padding: 15px 20px;
    background-color: #f1f1f1;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.hms-modal-header h2 {
    margin: 0;
}

.hms-modal-close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.hms-modal-close:hover {
    color: black;
}

.hms-modal-body {
    padding: 20px;
}

.hms-inventory-alerts .notice {
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Modal functionality
    $('#add-medicine-btn').click(function() {
        $('#add-medicine-modal').show();
    });
    
    $('.update-stock-btn').click(function() {
        var medicineId = $(this).data('id');
        var medicineName = $(this).data('name');
        
        $('#stock_medicine_id').val(medicineId);
        $('#stock_medicine_name').text(medicineName);
        $('#update-stock-modal').show();
    });
    
    $('.hms-modal-close').click(function() {
        $('.hms-modal').hide();
    });
    
    $(window).click(function(event) {
        if ($(event.target).hasClass('hms-modal')) {
            $('.hms-modal').hide();
        }
    });
});
</script>