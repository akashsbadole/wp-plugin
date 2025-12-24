<?php
/**
 * Admin template for managing items within a specific bill.
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

// Ensure the HMS_Billing class is available
if (!class_exists('HMS_Billing')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-billing.php';
}

$billing_obj = new HMS_Billing();

// Get bill ID from URL
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;

if (empty($bill_id)) {
    wp_die(esc_html__('Bill ID is required.', 'hospital-management-system'));
}

$bill = $billing_obj->get_bill($bill_id);

if (!$bill) {
    wp_die(__('Bill not found.', 'hospital-management-system'));
}

// Get patient and staff info for display
$patient_obj = new HMS_Patient();
$patient = $patient_obj->get_patient($bill->patient_id);
$staff_user = get_user_by('ID', $bill->created_by);
$created_by_name = $staff_user ? $staff_user->display_name : __('N/A', 'hospital-management-system');

// Get existing bill items
$bill_items = $billing_obj->get_bill_items($bill_id);
$total_amount = 0;
foreach ($bill_items as $item) {
    $total_amount += floatval($item->quantity) * floatval($item->price);
}
$bill_total = !empty($bill->total_amount) ? floatval($bill->total_amount) : $total_amount;
$paid_amount = !empty($bill->paid_amount) ? floatval($bill->paid_amount) : 0;
$due_amount = $bill_total - $paid_amount;

?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Manage Bill Items', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-billing')); ?>" class="page-title-action"><?php esc_html_e('Back to Billing', 'hospital-management-system'); ?></a>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-bill-details&id=' . $bill_id)); ?>" class="page-title-action"><?php esc_html_e('View Bill Details', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <!-- Bill Summary Card -->
    <div class="hms-card hms-mb-3">
        <div class="hms-card-body">
            <div class="hms-row">
                <div class="hms-col-md-6">
                    <h4><?php esc_html_e('Bill Information', 'hospital-management-system'); ?></h4>
                    <p><strong><?php esc_html_e('Bill ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($bill->bill_id); ?></p>
                    <p><strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong> <?php echo esc_html($patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')'); ?></p>
                    <p><strong><?php esc_html_e('Bill Date:', 'hospital-management-system'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($bill->bill_date)); ?></p>
                    <p><strong><?php esc_html_e('Status:', 'hospital-management-system'); ?></strong> <span class="hms-badge hms-badge-<?php echo esc_attr($bill->status); ?>"><?php echo esc_html(ucfirst($bill->status)); ?></span></p>
                </div>
                <div class="hms-col-md-6">
                    <h4><?php esc_html_e('Payment Summary', 'hospital-management-system'); ?></h4>
                    <p><strong><?php esc_html_e('Total Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html(number_format($bill_total, 2)); ?></p>
                    <p><strong><?php esc_html_e('Paid Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html(number_format($paid_amount, 2)); ?></p>
                    <p><strong><?php esc_html_e('Due Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html(number_format($due_amount, 2)); ?></p>
                    <p><strong><?php esc_html_e('Created By:', 'hospital-management-system'); ?></strong> <?php echo esc_html($created_by_name); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bill Items Management -->
    <div class="hms-card">
        <div class="hms-card-header d-flex justify-content-between align-items-center">
            <h4 class="hms-mb-0"><?php esc_html_e('Bill Items', 'hospital-management-system'); ?></h4>
            <button type="button" class="hms-btn hms-btn-primary" id="hms-add-bill-item-btn"><?php esc_html_e('+ Add New Item', 'hospital-management-system'); ?></button>
        </div>
        <div class="hms-card-body">
            <div class="hms-table-responsive">
                <table id="hms-bill-items-table" class="hms-table hms-table-striped hms-table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Description', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Quantity', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Unit Price', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Total Price', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bill_items)) : ?>
                            <?php foreach ($bill_items as $item) : ?>
                                <tr data-item-id="<?php echo esc_attr($item->id); ?>">
                                    <td><?php echo esc_html($item->description); ?></td>
                                    <td><?php echo esc_html($item->quantity); ?></td>
                                    <td><?php echo esc_html(number_format($item->price, 2)); ?></td>
                                    <td><?php echo esc_html(number_format(floatval($item->quantity) * floatval($item->price), 2)); ?></td>
                                    <td>
                                        <button type="button" class="hms-btn hms-btn-sm hms-btn-info hms-edit-bill-item-btn" data-item-id="<?php echo esc_attr($item->id); ?>" title="<?php esc_attr_e('Edit', 'hospital-management-system'); ?>">
                                            <i class="dashicons dashicons-edit"></i>
                                        </button>
                                        <button type="button" class="hms-btn hms-btn-sm hms-btn-danger hms-delete-bill-item-btn" data-item-id="<?php echo esc_attr($item->id); ?>" title="<?php esc_attr_e('Delete', 'hospital-management-system'); ?>">
                                            <i class="dashicons dashicons-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="hms-text-center"><?php esc_html_e('No items found for this bill.', 'hospital-management-system'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Bill Item Modal -->
<div class="hms-modal fade" id="hms-bill-item-modal" tabindex="-1" aria-labelledby="billItemModalLabel" aria-hidden="true">
    <div class="hms-modal-dialog">
        <div class="hms-modal-content">
            <div class="hms-modal-header">
                <h5 class="hms-modal-title" id="hms-bill-item-modal-title"><?php esc_html_e('Add New Bill Item', 'hospital-management-system'); ?></h5>
                <button type="button" class="hms-btn-close" data-bs-dismiss="hms-modal" aria-label="Close"></button>
            </div>
            <div class="hms-modal-body">
                <form id="hms-bill-item-form">
                    <input type="hidden" name="bill_id" value="<?php echo esc_attr($bill_id); ?>">
                    <input type="hidden" name="item_id" id="item_id" value="">
                    <?php wp_nonce_field('hms_admin_nonce', 'bill_item_nonce'); ?>
                    
                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-12">
                            <label for="description"><?php esc_html_e('Description*', 'hospital-management-system'); ?></label>
                            <input type="text" name="description" id="description" class="hms-form-control" required>
                        </div>
                    </div>
                    <div class="hms-row hms-mb-3">
                        <div class="hms-col-md-6">
                            <label for="quantity"><?php esc_html_e('Quantity*', 'hospital-management-system'); ?></label>
                            <input type="number" name="quantity" id="quantity" class="hms-form-control" min="1" value="1" required>
                        </div>
                        <div class="hms-col-md-6">
                            <label for="price"><?php esc_html_e('Unit Price*', 'hospital-management-system'); ?></label>
                            <input type="number" name="price" id="price" class="hms-form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div id="hms-bill-item-feedback" class="hms-feedback hms-mt-3" style="display: none;"></div>
                </form>
            </div>
            <div class="hms-modal-footer">
                <button type="button" class="hms-btn hms-btn-secondary" data-bs-dismiss="hms-modal"><?php esc_html_e('Close', 'hospital-management-system'); ?></button>
                <button type="button" class="hms-btn hms-btn-primary" id="hms-save-bill-item-btn"><?php esc_html_e('Save Item', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var billItemModal = new bootstrap.Modal(document.getElementById('hms-bill-item-modal'));

    // Add New Bill Item button
    $('#hms-add-bill-item-btn').on('click', function(e) {
        e.preventDefault();
        $('#hms-bill-item-modal-title').text('<?php esc_html_e('Add New Bill Item', 'hospital-management-system'); ?>');
        $('#hms-bill-item-form')[0].reset();
        $('#item_id').val('');
        $('#hms-bill-item-feedback').hide();
        billItemModal.show();
    });

    // Edit Bill Item button (delegated)
    $('#hms-bill-items-table tbody').on('click', '.hms-edit-bill-item-btn', function(e) {
        e.preventDefault();
        var itemId = $(this).data('item-id');
        $('#hms-bill-item-modal-title').text('<?php esc_html_e('Edit Bill Item', 'hospital-management-system'); ?>');
        $('#hms-bill-item-form')[0].reset();
        $('#item_id').val(itemId);
        $('#hms-bill-item-feedback').hide();
        
        // Fetch item data and populate form
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'hms_get_bill_item',
                nonce: '<?php echo esc_js(wp_create_nonce('hms_admin_nonce')); ?>',
                item_id: itemId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var item = response.data;
                    $('#description').val(item.description);
                    $('#quantity').val(item.quantity);
                    $('#price').val(item.price);
                    billItemModal.show();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Delete Bill Item button (delegated)
    $('#hms-bill-items-table tbody').on('click', '.hms-delete-bill-item-btn', function(e) {
        e.preventDefault();
        var itemId = $(this).data('item-id');
        var $row = $(this).closest('tr');
        
        if (confirm('<?php esc_html_e('Are you sure you want to delete this item? This action cannot be undone.', 'hospital-management-system'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_delete_bill_item',
                    nonce: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>',
                    item_id: itemId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(400, function() { $(this).remove(); });
                        // Optionally, update the bill summary totals or reload the page
                        location.reload(); 
                    } else {
                        alert(response.data);
                    }
                }
            });
        }
    });

    // Save Bill Item button
    $('#hms-save-bill-item-btn').on('click', function(e) {
        e.preventDefault();
        var $form = $('#hms-bill-item-form');
        var $feedback = $('#hms-bill-item-feedback');
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        var formData = $form.serializeArray();
        var action = $('#item_id').val() ? 'hms_update_bill_item' : 'hms_create_bill_item';
        
        // Add action and nonce to formData
        formData.push({name: 'action', value: action});
        formData.push({name: 'nonce', value: '<?php echo wp_create_nonce('hms_admin_nonce'); ?>'});

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: $.param(formData),
            dataType: 'json',
            beforeSend: function() {
                $('#hms-save-bill-item-btn').prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                    // Reload the page to see the updated list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $feedback.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                }
            },
            error: function(xhr) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : '<?php esc_html_e('An unexpected error occurred.', 'hospital-management-system'); ?>';
                $feedback.addClass('notice notice-error').html('<p>' + errorMessage + '</p>').show();
            },
            complete: function() {
                $('#hms-save-bill-item-btn').prop('disabled', false).text('<?php esc_html_e('Save Item', 'hospital-management-system'); ?>');
            }
        });
    });
});
</script>

<style>
/* Basic styling, can be moved to hms-admin.css */
.hms-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
.hms-mb-0 { margin-bottom: 0 !important; }
.hms-mb-3 { margin-bottom: 1rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-6 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) { .hms-col-md-6 { flex: 0 0 50%; max-width: 50%; } }
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-header { padding: 1rem 1rem; border-bottom: 1px solid #dee2e6; border-top-left-radius: calc(.3rem - 1px); border-top-right-radius: calc(.3rem - 1px); background-color: #f8f9fa; }
.hms-card-body { padding: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-btn-secondary { color: #fff; background-color: #6c757d; border-color: #6c757d; }
.hms-btn-secondary:hover { background-color: #5a6268; border-color: #545b62; }
.hms-btn-info { color: #fff; background-color: #17a2b8; border-color: #17a2b8; }
.hms-btn-info:hover { background-color: #138496; border-color: #117a8b; }
.hms-btn-danger { color: #fff; background-color: #dc3545; border-color: #dc3545; }
.hms-btn-danger:hover { background-color: #c82333; border-color: #bd2130; }
.hms-btn-sm { padding: .25rem .5rem; font-size: .875rem; line-height: 1.5; border-radius: .2rem; }
.hms-btn-close { box-sizing: content-box; width: 1em; height: 1em; padding: .25em .25em; color: #000; background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat; border: 0; border-radius: .25rem; opacity: .5; }
.hms-btn-close:hover { color: #000; text-decoration: none; opacity: .75; }
.hms-modal { position: fixed; top: 0; left: 0; z-index: 1050; display: none; width: 100%; height: 100%; overflow: hidden; background-color: rgba(0,0,0,0.5); }
.hms-modal-dialog { position: relative; width: auto; margin: 1.75rem; pointer-events: none; }
@media (min-width: 576px) { .hms-modal-dialog { max-width: 500px; margin: 1.75rem auto; } }
.hms-modal-content { position: relative; display: flex; flex-direction: column; width: 100%; pointer-events: auto; background-color: #fff; background-clip: padding-box; border: 1px solid rgba(0,0,0,.2); border-radius: .3rem; outline: 0; }
.hms-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1rem; border-bottom: 1px solid #dee2e6; border-top-left-radius: calc(.3rem - 1px); border-top-right-radius: calc(.3rem - 1px); }
.hms-modal-title { margin-bottom: 0; line-height: 1.5; }
.hms-modal-body { position: relative; flex: 1 1 auto; padding: 1rem; }
.hms-modal-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; padding: .75rem; border-top: 1px solid #dee2e6; border-bottom-right-radius: calc(.3rem - 1px); border-bottom-left-radius: calc(.3rem - 1px); }
.hms-feedback { margin-top: 1rem; }
.hms-d-flex { display: flex !important; }
.hms-justify-content-between { justify-content: space-between !important; }
.hms-align-items-center { align-items: center !important; }
.hms-table-responsive { display: block; width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.hms-table { width: 100%; margin-bottom: 1rem; background-color: transparent; border-collapse: collapse; }
.hms-table th, .hms-table td { padding: .75rem; vertical-align: top; border-top: 1px solid #dee2e6; }
.hms-table thead th { vertical-align: bottom; border-bottom: 2px solid #dee2e6; background-color: #f8f9fa; }
.hms-table tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,.05); }
.hms-table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
.hms-text-center { text-align: center !important; }
.hms-badge { display: inline-block; padding: .25em .4em; font-size: 75%; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
.hms-badge-paid { color: #fff; background-color: #28a745; }
.hms-badge-unpaid { color: #fff; background-color: #dc3545; }
.hms-badge-partial { color: #212529; background-color: #ffc107; }
</style>
