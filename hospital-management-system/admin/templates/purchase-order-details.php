<?php
/**
 * Purchase Order Details Template
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

// Get purchase order ID
$po_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$po_id) {
    wp_die(__('Invalid purchase order ID.', 'hospital-management-system'));
}

// Get purchase order details
$po = $pharmacy->get_purchase_order($po_id);
if (!$po) {
    wp_die(__('Purchase order not found.', 'hospital-management-system'));
}

// Get supplier details
$supplier = $pharmacy->get_supplier($po->supplier_id);

// Get purchase order items
$items_table = $wpdb->prefix . 'hms_purchase_order_items';
$medicines_table = $wpdb->prefix . 'hms_medicines';

$po_items = $wpdb->get_results($wpdb->prepare(
    "SELECT poi.*, m.name as medicine_name, m.unit, m.generic_name 
     FROM {$items_table} poi 
     LEFT JOIN {$medicines_table} m ON poi.medicine_id = m.id 
     WHERE poi.purchase_order_id = %d 
     ORDER BY poi.id",
    $po_id
));

// Handle receiving items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'receive_items') {
    check_admin_referer('hms_admin_nonce', 'nonce');
    
    $received_items = array();
    foreach ($_POST['received_items'] as $item_id => $data) {
        if (intval($data['quantity_received']) > 0) {
            $received_items[$item_id] = array(
                'quantity_received' => intval($data['quantity_received']),
                'batch_number' => sanitize_text_field($data['batch_number']),
                'expiry_date' => sanitize_text_field($data['expiry_date'])
            );
        }
    }
    
    if (!empty($received_items)) {
        $result = $pharmacy->receive_purchase_order($po_id, $received_items);
        if ($result && !is_wp_error($result)) {
            $message = __('Items received successfully.', 'hospital-management-system');
            $type = 'success';
            
            // Refresh data
            $po = $pharmacy->get_purchase_order($po_id);
            $po_items = $wpdb->get_results($wpdb->prepare(
                "SELECT poi.*, m.name as medicine_name, m.unit, m.generic_name 
                 FROM {$items_table} poi 
                 LEFT JOIN {$medicines_table} m ON poi.medicine_id = m.id 
                 WHERE poi.purchase_order_id = %d 
                 ORDER BY poi.id",
                $po_id
            ));
        } else {
            $message = is_wp_error($result) ? $result->get_error_message() : __('Failed to receive items.', 'hospital-management-system');
            $type = 'error';
        }
    } else {
        $message = __('No items to receive.', 'hospital-management-system');
        $type = 'warning';
    }
}
?>

<div class="wrap">
    <h1><?php _e('Purchase Order Details', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_purchase_orders'); ?>" class="page-title-action"><?php _e('Back to Purchase Orders', 'hospital-management-system'); ?></a>
    </h1>

    <?php if (isset($message)): ?>
        <div id="message" class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="po-details-container">
        <!-- Purchase Order Header -->
        <div class="po-header">
            <div class="po-info-grid">
                <div class="po-info-section">
                    <h3><?php _e('Order Information', 'hospital-management-system'); ?></h3>
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Order Number:', 'hospital-management-system'); ?></th>
                            <td><strong><?php echo esc_html($po->order_number); ?></strong></td>
                        </tr>
                        <tr>
                            <th><?php _e('Order Date:', 'hospital-management-system'); ?></th>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($po->order_date))); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Expected Delivery:', 'hospital-management-system'); ?></th>
                            <td>
                                <?php if ($po->expected_delivery_date): ?>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($po->expected_delivery_date))); ?>
                                <?php else: ?>
                                    <?php _e('Not specified', 'hospital-management-system'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Status:', 'hospital-management-system'); ?></th>
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
                        </tr>
                        <tr>
                            <th><?php _e('Total Amount:', 'hospital-management-system'); ?></th>
                            <td><strong><?php echo esc_html(number_format($po->total_amount, 2)); ?></strong></td>
                        </tr>
                    </table>
                </div>

                <div class="po-info-section">
                    <h3><?php _e('Supplier Information', 'hospital-management-system'); ?></h3>
                    <?php if ($supplier): ?>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Name:', 'hospital-management-system'); ?></th>
                                <td><strong><?php echo esc_html($supplier->name); ?></strong></td>
                            </tr>
                            <tr>
                                <th><?php _e('Contact Person:', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html($supplier->contact_person ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Email:', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html($supplier->email ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Phone:', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html($supplier->phone ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Payment Terms:', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html($supplier->payment_terms ?: '-'); ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Supplier information not available.', 'hospital-management-system'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($po->notes): ?>
                <div class="po-notes">
                    <h3><?php _e('Notes', 'hospital-management-system'); ?></h3>
                    <p><?php echo esc_html($po->notes); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Purchase Order Items -->
        <div class="po-items">
            <h3><?php _e('Order Items', 'hospital-management-system'); ?></h3>
            
            <?php if ($po->status === 'Pending' || $po->status === 'Partially Received'): ?>
                <form method="post" id="receive-items-form">
                    <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                    <input type="hidden" name="action" value="receive_items">
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Medicine', 'hospital-management-system'); ?></th>
                        <th><?php _e('Ordered Qty', 'hospital-management-system'); ?></th>
                        <th><?php _e('Received Qty', 'hospital-management-system'); ?></th>
                        <th><?php _e('Unit Price', 'hospital-management-system'); ?></th>
                        <th><?php _e('Total Price', 'hospital-management-system'); ?></th>
                        <?php if ($po->status === 'Pending' || $po->status === 'Partially Received'): ?>
                            <th><?php _e('Receive Now', 'hospital-management-system'); ?></th>
                            <th><?php _e('Batch Number', 'hospital-management-system'); ?></th>
                            <th><?php _e('Expiry Date', 'hospital-management-system'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($po_items): ?>
                        <?php foreach ($po_items as $item): ?>
                            <?php
                            $remaining_qty = $item->quantity_ordered - $item->quantity_received;
                            $is_fully_received = $remaining_qty <= 0;
                            ?>
                            <tr class="<?php echo $is_fully_received ? 'fully-received' : ''; ?>">
                                <td>
                                    <strong><?php echo esc_html($item->medicine_name); ?></strong>
                                    <?php if ($item->generic_name): ?>
                                        <br><small><?php echo esc_html($item->generic_name); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($item->quantity_ordered . ' ' . $item->unit); ?></td>
                                <td>
                                    <span class="<?php echo $is_fully_received ? 'text-success' : 'text-warning'; ?>">
                                        <?php echo esc_html($item->quantity_received . ' ' . $item->unit); ?>
                                    </span>
                                    <?php if (!$is_fully_received): ?>
                                        <br><small class="text-muted"><?php printf(__('Remaining: %s', 'hospital-management-system'), $remaining_qty . ' ' . $item->unit); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(number_format($item->unit_price, 2)); ?></td>
                                <td><?php echo esc_html(number_format($item->total_price, 2)); ?></td>
                                
                                <?php if ($po->status === 'Pending' || $po->status === 'Partially Received'): ?>
                                    <td>
                                        <?php if (!$is_fully_received): ?>
                                            <input type="number" 
                                                   name="received_items[<?php echo $item->id; ?>][quantity_received]" 
                                                   min="0" 
                                                   max="<?php echo $remaining_qty; ?>" 
                                                   value="0" 
                                                   class="small-text">
                                        <?php else: ?>
                                            <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$is_fully_received): ?>
                                            <input type="text" 
                                                   name="received_items[<?php echo $item->id; ?>][batch_number]" 
                                                   placeholder="<?php _e('Batch #', 'hospital-management-system'); ?>" 
                                                   class="regular-text">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$is_fully_received): ?>
                                            <input type="date" 
                                                   name="received_items[<?php echo $item->id; ?>][expiry_date]" 
                                                   class="regular-text">
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo ($po->status === 'Pending' || $po->status === 'Partially Received') ? '8' : '5'; ?>">
                                <?php _e('No items found.', 'hospital-management-system'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($po->status === 'Pending' || $po->status === 'Partially Received'): ?>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Receive Selected Items', 'hospital-management-system'); ?>">
                </p>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.po-details-container {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
}

.po-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.po-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 20px;
}

.po-info-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #23282d;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 5px;
}

.po-notes {
    margin-top: 20px;
}

.po-notes h3 {
    margin-bottom: 10px;
}

.po-items h3 {
    margin-bottom: 15px;
    color: #23282d;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 5px;
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
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
.badge-secondary { background-color: #6c757d; }

.fully-received {
    background-color: #f0f9ff;
}

.text-success {
    color: #46b450;
    font-weight: bold;
}

.text-warning {
    color: #ffb900;
    font-weight: bold;
}

.text-muted {
    color: #666;
}

.small-text {
    width: 80px;
}

@media (max-width: 768px) {
    .po-info-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-fill batch number and expiry date when quantity is entered
    $('input[name*="[quantity_received]"]').on('input', function() {
        var quantity = parseInt($(this).val()) || 0;
        var row = $(this).closest('tr');
        var batchInput = row.find('input[name*="[batch_number]"]');
        var expiryInput = row.find('input[name*="[expiry_date]"]');
        
        if (quantity > 0) {
            batchInput.prop('required', true);
            expiryInput.prop('required', true);
            row.addClass('receiving-items');
        } else {
            batchInput.prop('required', false);
            expiryInput.prop('required', false);
            row.removeClass('receiving-items');
        }
    });
    
    // Form validation
    $('#receive-items-form').submit(function(e) {
        var hasItems = false;
        $('input[name*="[quantity_received]"]').each(function() {
            if (parseInt($(this).val()) > 0) {
                hasItems = true;
                return false;
            }
        });
        
        if (!hasItems) {
            e.preventDefault();
            alert('<?php _e('Please enter quantities for items to receive.', 'hospital-management-system'); ?>');
            return false;
        }
    });
});
</script>