<?php
/**
 * Suppliers Management Template
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
            case 'add_supplier':
                $result = $pharmacy->create_supplier($_POST);
                if ($result) {
                    $message = __('Supplier added successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = __('Failed to add supplier.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
                
            case 'update_supplier':
                $supplier_id = intval($_POST['supplier_id']);
                $result = $pharmacy->update_supplier($supplier_id, $_POST);
                if ($result && !is_wp_error($result)) {
                    $message = __('Supplier updated successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = is_wp_error($result) ? $result->get_error_message() : __('Failed to update supplier.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
                
            case 'delete_supplier':
                $supplier_id = intval($_POST['supplier_id']);
                $result = $pharmacy->delete_supplier($supplier_id);
                if ($result && !is_wp_error($result)) {
                    $message = __('Supplier deleted successfully.', 'hospital-management-system');
                    $type = 'success';
                } else {
                    $message = is_wp_error($result) ? $result->get_error_message() : __('Failed to delete supplier.', 'hospital-management-system');
                    $type = 'error';
                }
                break;
        }
    }
}

// Get filter parameters
$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

// Get suppliers
$per_page = 20;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $per_page;

$suppliers = $pharmacy->get_suppliers($search, $status, $offset, $per_page);
?>

<div class="wrap">
    <h1><?php _e('Suppliers Management', 'hospital-management-system'); ?>
        <button type="button" class="page-title-action" id="add-supplier-btn"><?php _e('Add Supplier', 'hospital-management-system'); ?></button>
    </h1>

    <?php if (isset($message)): ?>
        <div id="message" class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" class="hms-filters">
        <input type="hidden" name="page" value="hms_suppliers">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search suppliers...', 'hospital-management-system'); ?>">
                
                <select name="status">
                    <option value=""><?php _e('All Status', 'hospital-management-system'); ?></option>
                    <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'hospital-management-system'); ?></option>
                    <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inactive', 'hospital-management-system'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
                <a href="<?php echo admin_url('admin.php?page=hms_suppliers'); ?>" class="button"><?php _e('Clear', 'hospital-management-system'); ?></a>
            </div>
        </div>
    </form>

    <!-- Suppliers Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Name', 'hospital-management-system'); ?></th>
                <th><?php _e('Contact Person', 'hospital-management-system'); ?></th>
                <th><?php _e('Email', 'hospital-management-system'); ?></th>
                <th><?php _e('Phone', 'hospital-management-system'); ?></th>
                <th><?php _e('City', 'hospital-management-system'); ?></th>
                <th><?php _e('Payment Terms', 'hospital-management-system'); ?></th>
                <th><?php _e('Status', 'hospital-management-system'); ?></th>
                <th><?php _e('Actions', 'hospital-management-system'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($suppliers): ?>
                <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><strong><?php echo esc_html($supplier->name); ?></strong></td>
                        <td><?php echo esc_html($supplier->contact_person ?: '-'); ?></td>
                        <td><?php echo esc_html($supplier->email ?: '-'); ?></td>
                        <td><?php echo esc_html($supplier->phone ?: '-'); ?></td>
                        <td><?php echo esc_html($supplier->city ?: '-'); ?></td>
                        <td><?php echo esc_html($supplier->payment_terms ?: '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $supplier->status === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo esc_html(ucfirst($supplier->status)); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="button button-small edit-supplier-btn" 
                                    data-supplier='<?php echo esc_attr(json_encode($supplier)); ?>'>
                                <?php _e('Edit', 'hospital-management-system'); ?>
                            </button>
                            <button type="button" class="button button-small button-link-delete delete-supplier-btn" 
                                    data-id="<?php echo esc_attr($supplier->id); ?>" 
                                    data-name="<?php echo esc_attr($supplier->name); ?>">
                                <?php _e('Delete', 'hospital-management-system'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8"><?php _e('No suppliers found.', 'hospital-management-system'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Supplier Modal -->
<div id="supplier-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content">
        <div class="hms-modal-header">
            <h2 id="supplier-modal-title"><?php _e('Add Supplier', 'hospital-management-system'); ?></h2>
            <span class="hms-modal-close">&times;</span>
        </div>
        <div class="hms-modal-body">
            <form id="supplier-form" method="post">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" id="supplier_action" name="action" value="add_supplier">
                <input type="hidden" id="supplier_id" name="supplier_id">
                
                <table class="form-table">
                    <tr>
                        <th><label for="supplier_name"><?php _e('Supplier Name', 'hospital-management-system'); ?> *</label></th>
                        <td><input type="text" id="supplier_name" name="name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="contact_person"><?php _e('Contact Person', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="contact_person" name="contact_person" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_email"><?php _e('Email', 'hospital-management-system'); ?></label></th>
                        <td><input type="email" id="supplier_email" name="email" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_phone"><?php _e('Phone', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="supplier_phone" name="phone" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_address"><?php _e('Address', 'hospital-management-system'); ?></label></th>
                        <td><textarea id="supplier_address" name="address" class="large-text" rows="3"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_city"><?php _e('City', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="supplier_city" name="city" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_state"><?php _e('State', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="supplier_state" name="state" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="postal_code"><?php _e('Postal Code', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="postal_code" name="postal_code" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="supplier_country"><?php _e('Country', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="supplier_country" name="country" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="tax_number"><?php _e('Tax Number', 'hospital-management-system'); ?></label></th>
                        <td><input type="text" id="tax_number" name="tax_number" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="payment_terms"><?php _e('Payment Terms', 'hospital-management-system'); ?></label></th>
                        <td>
                            <select id="payment_terms" name="payment_terms">
                                <option value=""><?php _e('Select Payment Terms', 'hospital-management-system'); ?></option>
                                <option value="Net 30"><?php _e('Net 30 Days', 'hospital-management-system'); ?></option>
                                <option value="Net 15"><?php _e('Net 15 Days', 'hospital-management-system'); ?></option>
                                <option value="COD"><?php _e('Cash on Delivery', 'hospital-management-system'); ?></option>
                                <option value="Advance"><?php _e('Advance Payment', 'hospital-management-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="supplier_status"><?php _e('Status', 'hospital-management-system'); ?></label></th>
                        <td>
                            <select id="supplier_status" name="status">
                                <option value="active"><?php _e('Active', 'hospital-management-system'); ?></option>
                                <option value="inactive"><?php _e('Inactive', 'hospital-management-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="supplier_notes"><?php _e('Notes', 'hospital-management-system'); ?></label></th>
                        <td><textarea id="supplier_notes" name="notes" class="large-text" rows="3"></textarea></td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" id="supplier-submit-btn" class="button-primary" value="<?php _e('Add Supplier', 'hospital-management-system'); ?>">
                    <button type="button" class="button hms-modal-close"><?php _e('Cancel', 'hospital-management-system'); ?></button>
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-supplier-modal" class="hms-modal" style="display: none;">
    <div class="hms-modal-content">
        <div class="hms-modal-header">
            <h2><?php _e('Delete Supplier', 'hospital-management-system'); ?></h2>
            <span class="hms-modal-close">&times;</span>
        </div>
        <div class="hms-modal-body">
            <p><?php _e('Are you sure you want to delete this supplier?', 'hospital-management-system'); ?></p>
            <p><strong id="delete-supplier-name"></strong></p>
            <p class="description"><?php _e('This action cannot be undone. The supplier will only be deleted if there are no associated medicines or purchase orders.', 'hospital-management-system'); ?></p>
            
            <form id="delete-supplier-form" method="post">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="delete_supplier">
                <input type="hidden" id="delete_supplier_id" name="supplier_id">
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Delete Supplier', 'hospital-management-system'); ?>">
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
    max-height: 90vh;
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
</style>

<script>
jQuery(document).ready(function($) {
    // Add supplier modal
    $('#add-supplier-btn').click(function() {
        resetSupplierForm();
        $('#supplier-modal-title').text('<?php _e('Add Supplier', 'hospital-management-system'); ?>');
        $('#supplier_action').val('add_supplier');
        $('#supplier-submit-btn').val('<?php _e('Add Supplier', 'hospital-management-system'); ?>');
        $('#supplier-modal').show();
    });
    
    // Edit supplier modal
    $('.edit-supplier-btn').click(function() {
        var supplier = $(this).data('supplier');
        
        resetSupplierForm();
        $('#supplier-modal-title').text('<?php _e('Edit Supplier', 'hospital-management-system'); ?>');
        $('#supplier_action').val('update_supplier');
        $('#supplier-submit-btn').val('<?php _e('Update Supplier', 'hospital-management-system'); ?>');
        
        // Populate form fields
        $('#supplier_id').val(supplier.id);
        $('#supplier_name').val(supplier.name);
        $('#contact_person').val(supplier.contact_person);
        $('#supplier_email').val(supplier.email);
        $('#supplier_phone').val(supplier.phone);
        $('#supplier_address').val(supplier.address);
        $('#supplier_city').val(supplier.city);
        $('#supplier_state').val(supplier.state);
        $('#postal_code').val(supplier.postal_code);
        $('#supplier_country').val(supplier.country);
        $('#tax_number').val(supplier.tax_number);
        $('#payment_terms').val(supplier.payment_terms);
        $('#supplier_status').val(supplier.status);
        $('#supplier_notes').val(supplier.notes);
        
        $('#supplier-modal').show();
    });
    
    // Delete supplier modal
    $('.delete-supplier-btn').click(function() {
        var supplierId = $(this).data('id');
        var supplierName = $(this).data('name');
        
        $('#delete_supplier_id').val(supplierId);
        $('#delete-supplier-name').text(supplierName);
        $('#delete-supplier-modal').show();
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
    
    function resetSupplierForm() {
        $('#supplier-form')[0].reset();
        $('#supplier_id').val('');
    }
});
</script>