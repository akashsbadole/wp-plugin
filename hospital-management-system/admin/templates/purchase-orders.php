<?php
/**
 * Purchase Orders Management Template
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
            case 'create_purchase_order':
                $items = json_decode(stripslashes($_POST['items']), true);
                $data = array(
                    'supplier_id' => intval($_POST['supplier_id']),
                    'expected_delivery_date' => sanitize_text_field($_POST['expected_delivery_date']),
                    'total_amount' => floatval($_POST['total_amount']),
                    'notes' => sanitize_textarea_field($_POST['notes']),
                    'items' => $items
                );
                
                $result = $pharmacy->create_purchase_order($data);
                if ($result) {
                    $message = __('Purchase order created successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = __('Failed to create purchase order.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
        }
    }
}

// Get filter parameters
$supplier_id = isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0;
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Get purchase orders
$per_page = 20;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

$purchase_orders = $pharmacy->get_purchase_orders($supplier_id, $status, $offset, $per_page);

// Get suppliers for dropdown
$suppliers = $pharmacy->get_suppliers('', 'active');

// Get medicines for purchase order creation
$medicines = $pharmacy->get_medicines('', '', 'active');
?>

<div class="wrap">
    <h1><?php _e('Purchase Orders', 'hospital-management-system'); ?>
        <button type="button" class="page-title-action" id="create-po-btn"><?php _e('Create Purchase Order', 'hospital-management-system'); ?></button>
    </h1>

    <?php if (isset($message)): ?>
        <div id="message" class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" class="hms-filters">
        <input type="hidden" name="page" value="hms_purchase_orders">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="supplier_id">
                    <option value=""><?php _e('All Suppliers', 'hospital-management-system'); ?></option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo esc_attr($supplier->id); ?>" <?php selected($supplier_id, $supplier->id); ?>>
                            <?php echo esc_html($supplier->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status">
                    <option value=""><?php _e('All Status', 'hospital-management-system'); ?></option>
                    <option value="Pending" <?php selected($status, 'Pending'); ?>><?php _e('Pending', 'hospital-management-system'); ?></option>
                    <option value="Partially Received" <?php selected($status, 'Partially Received'); ?>><?php _e('Partially Received', 'hospital-management-system'); ?></option>
                    <option value="Completed" <?php selected($status, 'Completed'); ?>><?php _e('Completed', 'hospital-management-system'); ?></option>
                    <option value="Cancelled" <?php selected($status, 'Cancelled'); ?>><?php _e('Cancelled', 'hospital-management-system'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hms_purchase_orders'); ?>" class="button"><?php _e('Clear', 'hospital-management-system'); ?></a>
            </div>
        </div>
    </form>

    <!-- Purchase Orders Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Order Number', 'hospital-management-system'); ?></th>
                <th><?php _e('Supplier', 'hospital-management-system'); ?></th>
                <th><?php _e('Order Date', 'hospital-management-system'); ?></th>
                <th><?php _e('Expected Delivery', 'hospital-management-system'); ?></th>
                <th><?php _e('Total Amount', 'hospital-management-system'); ?></th>
                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($purchase_orders): ?>
                <?php foreach ($purchase_orders as $po): ?>
                    <tr>
                        <td><strong><?php echo esc_html($po->order_number); ?></strong></td>
                        <td><?php echo esc_html($po->supplier_name ?: 'N/A'); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($po->order_date))); ?></td>
                        <td>
                            <?php if ($po->expected_delivery_date): ?>
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($po->expected_delivery_date))); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(number_format($po->total_amount, 2)); ?></td>
                        <td>
                            <?php
                            $status_class = '';
                            switch ($po->status) {
                                case 'Pending':
                                    $status_class = 'warning';
                                    break;
                                case 'Partially Received':
                                    $status_class = 'info';
                                    break;
                                case 'Completed':
                                    $status_class = 'success';
                                    break;
                                case 'Cancelled':
                                    $status_class = 'danger';
                                    break;
                                default:
                                    $status_class = 'secondary';
                            }
                            ?>
                            <span class="badge badge-<?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($po->status); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=hms_purchase_order_details&id=' . $po->id); ?>" 
                               class="button button-small"><?php _e('View Details', 'hospital-management-system'); ?></a>
                            <?php if ($po->status === 'Pending' || $po->status === 'Partially Received'): ?>
                                <button type="button" class="button button-small receive-items-btn" 
                                        data-id="<?php echo esc_attr($po->id); ?>" 
                                        data-number="<?php echo esc_attr($po->order_number); ?>">
                                    <?php _e('Receive Items', 'hospital-management-system'); ?>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7"><?php _e('No purchase orders found.', 'hospital-management-system'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create Purchase Order Modal -->
<div id="create-po-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content" style="width: 90%; max-width: 800px;">
        <div class="hms-modal-header">
            <h2><?php _e('Create Purchase Order', 'hospital-management-system'); ?></h2>
            <span class="hms-modal-close">&times;</span>
        </div>
        <div class="hms-modal-body">
            <form id="create-po-form" method="post">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="create_purchase_order">
                <input type="hidden" id="po_items" name="items">
                <input type="hidden" id="po_total_amount" name="total_amount">
                
                <table class="form-table">
                    <tr>
                        <th><label for="po_supplier_id"><?php _e('Supplier', 'hospital-management-system'); ?> *</label></th>
                        <td>
                            <select id="po_supplier_id" name="supplier_id" required>
                                <option value=""><?php _e('Select Supplier', 'hospital-management-system'); ?></option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo esc_attr($supplier->id); ?>">
                                        <?php echo esc_html($supplier->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="po_expected_delivery"><?php _e('Expected Delivery Date', 'hospital-management-system'); ?></label></th>
                        <td><input type="date" id="po_expected_delivery" name="expected_delivery_date" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="po_notes"><?php _e('Notes', 'hospital-management-system'); ?></label></th>
                        <td><textarea id="po_notes" name="notes" class="large-text" rows="3"></textarea></td>
                    </tr>
                </table>
                
                <h3><?php _e('Order Items', 'hospital-management-system'); ?></h3>
                
                <div class="po-items-section">
                    <div class="po-item-controls">
                        <select id="medicine_select">
                            <option value=""><?php _e('Select Medicine to Add', 'hospital-management-system'); ?></option>
                            <?php foreach ($medicines as $medicine): ?>
                                <option value="<?php echo esc_attr($medicine->id); ?>" 
                                        data-name="<?php echo esc_attr($medicine->name); ?>"
                                        data-unit="<?php echo esc_attr($medicine->unit); ?>"
                                        data-price="<?php echo esc_attr($medicine->unit_price); ?>">
                                    <?php echo esc_html($medicine->name . ' (' . $medicine->unit . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="add-medicine-btn" class="button"><?php _e('Add Medicine', 'hospital-management-system'); ?></button>
                    </div>
                    
                    <table id="po-items-table" class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Medicine', 'hospital-management-system'); ?></th>
                                <th><?php _e('Quantity', 'hospital-management-system'); ?></th>
                                <th><?php _e('Unit Price', 'hospital-management-system'); ?></th>
                                <th><?php _e('Total', 'hospital-management-system'); ?></th>
                                <th><?php _e('Action', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="po-items-tbody">
                            <tr id="no-items-row">
                                <td colspan="5"><?php _e('No items added yet.', 'hospital-management-system'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3"><?php _e('Total Amount:', 'hospital-management-system'); ?></th>
                                <th id="po-total-display">0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Create Purchase Order', 'hospital-management-system'); ?>">
                    <button type="button" class="button hms-modal-close"><?php _e('Cancel', 'hospital-management-system'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<style>
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
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
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
    margin: 2% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 4px;
    max-height: 95vh;
    overflow-y: auto;
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

.po-items-section {
    margin-top: 20px;
}

.po-item-controls {
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.po-item-controls select {
    min-width: 300px;
}

#po-items-table {
    margin-top: 15px;
}

#po-items-table input[type="number"] {
    width: 80px;
}

.remove-item-btn {
    color: #d63638;
    text-decoration: none;
    font-weight: bold;
}

.remove-item-btn:hover {
    color: #d63638;
}
</style>

<script>
jQuery(document).ready(function($) {
    var poItems = [];
    
    // Create PO modal
    $('#create-po-btn').click(function() {
        resetPOForm();
        $('#create-po-modal').show();
    });
    
    // Add medicine to PO
    $('#add-medicine-btn').click(function() {
        var medicineSelect = $('#medicine_select');
        var selectedOption = medicineSelect.find('option:selected');
        
        if (!selectedOption.val()) {
            alert('<?php _e('Please select a medicine.', 'hospital-management-system'); ?>');
            return;
        }
        
        var medicineId = selectedOption.val();
        var medicineName = selectedOption.data('name');
        var medicineUnit = selectedOption.data('unit');
        var unitPrice = parseFloat(selectedOption.data('price')) || 0;
        
        // Check if medicine already added
        if (poItems.find(item => item.medicine_id == medicineId)) {
            alert('<?php _e('This medicine is already added to the order.', 'hospital-management-system'); ?>');
            return;
        }
        
        var item = {
            medicine_id: medicineId,
            medicine_name: medicineName,
            medicine_unit: medicineUnit,
            quantity_ordered: 1,
            unit_price: unitPrice,
            total_price: unitPrice
        };
        
        poItems.push(item);
        updatePOItemsTable();
        medicineSelect.val('');
    });
    
    // Update PO items table
    function updatePOItemsTable() {
        var tbody = $('#po-items-tbody');
        tbody.empty();
        
        if (poItems.length === 0) {
            tbody.append('<tr id="no-items-row"><td colspan="5"><?php _e('No items added yet.', 'hospital-management-system'); ?></td></tr>');
        } else {
            poItems.forEach(function(item, index) {
                var row = '<tr>' +
                    '<td>' + item.medicine_name + ' (' + item.medicine_unit + ')</td>' +
                    '<td><input type="number" class="quantity-input" data-index="' + index + '" value="' + item.quantity_ordered + '" min="1"></td>' +
                    '<td><input type="number" class="price-input" data-index="' + index + '" value="' + item.unit_price + '" step="0.01" min="0"></td>' +
                    '<td class="total-cell">' + item.total_price.toFixed(2) + '</td>' +
                    '<td><a href="#" class="remove-item-btn" data-index="' + index + '"><?php _e('Remove', 'hospital-management-system'); ?></a></td>' +
                    '</tr>';
                tbody.append(row);
            });
        }
        
        updatePOTotal();
    }
    
    // Update quantity
    $(document).on('input', '.quantity-input', function() {
        var index = $(this).data('index');
        var quantity = parseInt($(this).val()) || 1;
        
        poItems[index].quantity_ordered = quantity;
        poItems[index].total_price = quantity * poItems[index].unit_price;
        
        $(this).closest('tr').find('.total-cell').text(poItems[index].total_price.toFixed(2));
        updatePOTotal();
    });
    
    // Update price
    $(document).on('input', '.price-input', function() {
        var index = $(this).data('index');
        var price = parseFloat($(this).val()) || 0;
        
        poItems[index].unit_price = price;
        poItems[index].total_price = poItems[index].quantity_ordered * price;
        
        $(this).closest('tr').find('.total-cell').text(poItems[index].total_price.toFixed(2));
        updatePOTotal();
    });
    
    // Remove item
    $(document).on('click', '.remove-item-btn', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        poItems.splice(index, 1);
        updatePOItemsTable();
    });
    
    // Update total
    function updatePOTotal() {
        var total = poItems.reduce(function(sum, item) {
            return sum + item.total_price;
        }, 0);
        
        $('#po-total-display').text(total.toFixed(2));
        $('#po_total_amount').val(total);
    }
    
    // Submit form
    $('#create-po-form').submit(function(e) {
        if (poItems.length === 0) {
            e.preventDefault();
            alert('<?php _e('Please add at least one item to the purchase order.', 'hospital-management-system'); ?>');
            return false;
        }
        
        $('#po_items').val(JSON.stringify(poItems));
    });
    
    // Modal close functionality
    $('.hms-modal-close').click(function() {
        $('.hms-modal').hide();
    });
    
    $(window).click(function(event) {
        if ($(event.target).hasClass('hms-modal')) {
            $('.hms-modal').hide();
        }
    });
    
    function resetPOForm() {
        $('#create-po-form')[0].reset();
        poItems = [];
        updatePOItemsTable();
    }
});
</script>