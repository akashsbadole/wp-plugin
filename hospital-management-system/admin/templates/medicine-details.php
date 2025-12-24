<?php
/**
 * Admin template for adding and editing medicine details.
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

// Ensure the HMS_Pharmacy class is available
if (!class_exists('HMS_Pharmacy')) {
    require_once HMS_PLUGIN_DIR . 'includes/class-hms-pharmacy.php';
}

$pharmacy_obj = new HMS_Pharmacy();

// Get medicine ID from URL for editing
$medicine_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$medicine = null;
$page_title = __('Add New Medicine', 'hospital-management-system');

if (!empty($medicine_id)) {
    $medicine = $pharmacy_obj->get_medicine($medicine_id);
    if (!$medicine) {
        wp_die(__('Medicine not found.', 'hospital-management-system'));
    }
    $page_title = __('Edit Medicine', 'hospital-management-system');
}

?>

<div class="wrap hms-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-pharmacy')); ?>" class="page-title-action"><?php esc_html_e('Back to Pharmacy', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <div class="hms-card">
        <div class="hms-card-header">
            <h4 class="hms-mb-0"><?php echo esc_html($page_title); ?></h4>
        </div>
        <div class="hms-card-body">
            <form id="hms-medicine-form" method="post">
                <?php if ($medicine) : ?>
                    <input type="hidden" name="medicine_id" value="<?php echo esc_attr($medicine->id); ?>">
                <?php endif; ?>
                <?php wp_nonce_field('hms_admin_nonce', 'medicine_nonce'); ?>
                
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-6">
                        <label for="name"><?php esc_html_e('Medicine Name*', 'hospital-management-system'); ?></label>
                        <input type="text" name="name" id="name" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->name) : ''; ?>" required>
                    </div>
                    <div class="hms-col-md-6">
                        <label for="generic_name"><?php esc_html_e('Generic Name', 'hospital-management-system'); ?></label>
                        <input type="text" name="generic_name" id="generic_name" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->generic_name) : ''; ?>">
                    </div>
                </div>
                
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-4">
                        <label for="category"><?php esc_html_e('Category*', 'hospital-management-system'); ?></label>
                        <input type="text" name="category" id="category" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->category) : ''; ?>" required>
                    </div>
                    <div class="hms-col-md-4">
                        <label for="manufacturer"><?php esc_html_e('Manufacturer', 'hospital-management-system'); ?></label>
                        <input type="text" name="manufacturer" id="manufacturer" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->manufacturer) : ''; ?>">
                    </div>
                    <div class="hms-col-md-4">
                        <label for="unit"><?php esc_html_e('Unit (e.g., Tablet, Bottle, ml)*', 'hospital-management-system'); ?></label>
                        <input type="text" name="unit" id="unit" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->unit) : ''; ?>" required>
                    </div>
                </div>

                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-6">
                        <label for="strength"><?php esc_html_e('Strength/Dosage (e.g., 500mg, 10ml)', 'hospital-management-system'); ?></label>
                        <input type="text" name="strength" id="strength" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->strength) : ''; ?>">
                    </div>
                    <div class="hms-col-md-6">
                        <label for="description"><?php esc_html_e('Description', 'hospital-management-system'); ?></label>
                        <textarea name="description" id="description" class="hms-form-control" rows="2"><?php echo $medicine ? esc_textarea($medicine->description) : ''; ?></textarea>
                    </div>
                </div>

                <h5 class="hms-mt-4 hms-mb-3"><?php esc_html_e('Stock Information', 'hospital-management-system'); ?></h5>
                
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-4">
                        <label for="stock_quantity"><?php esc_html_e('Current Stock Quantity*', 'hospital-management-system'); ?></label>
                        <input type="number" name="stock_quantity" id="stock_quantity" class="hms-form-control" min="0" value="<?php echo $medicine ? esc_attr($medicine->stock_quantity) : '0'; ?>" required>
                    </div>
                    <div class="hms-col-md-4">
                        <label for="reorder_level"><?php esc_html_e('Reorder Level (Low Stock Alert)*', 'hospital-management-system'); ?></label>
                        <input type="number" name="reorder_level" id="reorder_level" class="hms-form-control" min="0" value="<?php echo $medicine ? esc_attr($medicine->reorder_level) : '10'; ?>" required>
                    </div>
                    <div class="hms-col-md-4">
                        <label for="unit_price"><?php esc_html_e('Unit Purchase Price*', 'hospital-management-system'); ?></label>
                        <input type="number" name="unit_price" id="unit_price" class="hms-form-control" min="0" step="0.01" value="<?php echo $medicine ? esc_attr($medicine->unit_price) : '0.00'; ?>" required>
                    </div>
                </div>

                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-6">
                        <label for="selling_price"><?php esc_html_e('Unit Selling Price*', 'hospital-management-system'); ?></label>
                        <input type="number" name="selling_price" id="selling_price" class="hms-form-control" min="0" step="0.01" value="<?php echo $medicine ? esc_attr($medicine->selling_price) : '0.00'; ?>" required>
                    </div>
                    <div class="hms-col-md-6">
                        <label for="batch_number"><?php esc_html_e('Batch Number', 'hospital-management-system'); ?></label>
                        <input type="text" name="batch_number" id="batch_number" class="hms-form-control" value="<?php echo $medicine ? esc_attr($medicine->batch_number) : ''; ?>">
                    </div>
                </div>
                
                <div class="hms-row hms-mb-3">
                    <div class="hms-col-md-6">
                        <label for="expiry_date"><?php esc_html_e('Expiry Date', 'hospital-management-system'); ?></label>
                        <input type="date" name="expiry_date" id="expiry_date" class="hms-form-control" value="<?php echo $medicine && $medicine->expiry_date ? esc_attr(gmdate('Y-m-d', strtotime($medicine->expiry_date))) : ''; ?>">
                    </div>
                    <div class="hms-col-md-6">
                        <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                        <select name="status" id="status" class="hms-form-control">
                            <option value="active" <?php echo ($medicine && $medicine->status === 'active') ? 'selected' : ''; ?>><?php esc_html_e('Active', 'hospital-management-system'); ?></option>
                            <option value="inactive" <?php echo ($medicine && $medicine->status === 'inactive') ? 'selected' : ''; ?>><?php esc_html_e('Inactive', 'hospital-management-system'); ?></option>
                            <option value="discontinued" <?php echo ($medicine && $medicine->status === 'discontinued') ? 'selected' : ''; ?>><?php esc_html_e('Discontinued', 'hospital-management-system'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div id="hms-medicine-feedback" class="hms-feedback hms-mt-3" style="display: none;"></div>

                <div class="hms-mt-4">
                    <button type="submit" class="hms-btn hms-btn-primary" id="hms-save-medicine-btn">
                        <?php echo $medicine ? esc_html__('Update Medicine', 'hospital-management-system') : esc_html__('Add Medicine', 'hospital-management-system'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var $form = $('#hms-medicine-form');
    var $feedback = $('#hms-medicine-feedback');
    var $saveBtn = $('#hms-save-medicine-btn');

    $form.on('submit', function(e) {
        e.preventDefault();
        $feedback.hide().removeClass('notice notice-success notice-error').html('');

        var formData = $form.serialize();
        var action = $form.find('input[name="medicine_id"]').length ? 'hms_update_medicine' : 'hms_create_medicine';
        
        // Add action and nonce to formData
        formData += '&action=' + action + '&nonce=<?php echo wp_create_nonce('hms_admin_nonce'); ?>';

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $saveBtn.prop('disabled', true).text('<?php esc_html_e('Saving...', 'hospital-management-system'); ?>');
            },
            success: function(response) {
                if (response.success) {
                    $feedback.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                    // If it was a create action, redirect to edit page for the new medicine
                    if (action === 'hms_create_medicine' && response.data.medicine_id) {
                        setTimeout(function() {
                            window.location.href = '<?php echo esc_url(admin_url('admin.php?page=hms-medicine-details&id=')); ?>' + response.data.medicine_id;
                        }, 1000);
                    }
                } else {
                    $feedback.addClass('notice notice-error').html('<p>' + response.data + '</p>').show();
                }
            },
            error: function(xhr) {
                var errorMessage = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : '<?php esc_html_e('An unexpected error occurred.', 'hospital-management-system'); ?>';
                $feedback.addClass('notice notice-error').html('<p>' + errorMessage + '</p>').show();
            },
            complete: function() {
                $saveBtn.prop('disabled', false).text('<?php echo $medicine ? esc_html__('Update Medicine', 'hospital-management-system') : esc_html__('Add Medicine', 'hospital-management-system'); ?>');
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
.hms-mt-3 { margin-top: 1rem !important; }
.hms-mt-4 { margin-top: 1.5rem !important; }
.hms-g-3, .hms-row > * { --bs-gutter-x: 1rem; }
.hms-row { display: flex; flex-wrap: wrap; margin-right: calc(var(--bs-gutter-x) * -0.5); margin-left: calc(var(--bs-gutter-x) * -0.5); }
.hms-col-md-4 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) { .hms-col-md-4 { flex: 0 0 33.333333%; max-width: 33.333333%; } }
.hms-col-md-6 { flex: 0 0 auto; width: 100%; padding-right: calc(var(--bs-gutter-x) * 0.5); padding-left: calc(var(--bs-gutter-x) * 0.5); }
@media (min-width: 768px) { .hms-col-md-6 { flex: 0 0 50%; max-width: 50%; } }
.hms-card { border: 1px solid #ccd0d4; background-color: #fff; border-radius: 4px; margin-bottom: 20px; }
.hms-card-header { padding: 1rem 1rem; border-bottom: 1px solid #dee2e6; border-top-left-radius: calc(.3rem - 1px); border-top-right-radius: calc(.3rem - 1px); background-color: #f8f9fa; }
.hms-card-body { padding: 1rem; }
.hms-form-control { display: block; width: 100%; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: .25rem; transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn { display: inline-block; font-weight: 400; text-align: center; white-space: nowrap; vertical-align: middle; user-select: none; border: 1px solid transparent; padding: .375rem .75rem; font-size: 1rem; line-height: 1.5; border-radius: .25rem; transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out; }
.hms-btn-primary { color: #fff; background-color: #007bff; border-color: #007bff; }
.hms-btn-primary:hover { background-color: #0069d9; border-color: #0062cc; }
.hms-feedback { margin-top: 1rem; }
</style>
