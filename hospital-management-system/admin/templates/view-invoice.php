<?php
/**
 * Template for viewing and printing invoices
 *
 * @package Hospital_Management_System
 * @subpackage Admin/Templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get bill ID from URL
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;
if (!$bill_id) {
    wp_die(__('Invalid bill ID.', 'hospital-management-system'));
}

// Check if this is a print request
$is_print = isset($_GET['print']) && $_GET['print'] == '1';

// Get billing instance
global $hms_billing;
if (!$hms_billing) {
    wp_die(__('Billing system not initialized.', 'hospital-management-system'));
}

// Get bill details
$bill = $hms_billing->get_bill_with_details($bill_id);
if (!$bill) {
    wp_die(__('Bill not found.', 'hospital-management-system'));
}

// If this is a print request, output the invoice HTML directly
if ($is_print) {
    echo $hms_billing->generate_invoice_html($bill_id, true);
    return;
}
?>

<div class="wrap hms-invoice-wrap">
    <h1 class="wp-heading-inline"><?php _e('Invoice', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_billing')); ?>" class="page-title-action"><?php _e('← Back to Bills', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <div class="invoice-actions" style="margin-bottom: 20px;">
        <a href="<?php echo esc_url(add_query_arg('print', '1')); ?>" class="button button-primary" target="_blank">
            <span class="dashicons dashicons-printer" style="vertical-align: middle;"></span>
            <?php _e('Print Invoice', 'hospital-management-system'); ?>
        </a>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_bill_details&id=' . $bill_id)); ?>" class="button">
            <span class="dashicons dashicons-edit" style="vertical-align: middle;"></span>
            <?php _e('Edit Bill', 'hospital-management-system'); ?>
        </a>
        
        <?php if ($bill->net_amount > $bill->paid_amount): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_payment_info&bill_id=' . $bill_id)); ?>" class="button button-secondary">
                <span class="dashicons dashicons-money-alt" style="vertical-align: middle;"></span>
                <?php _e('Process Payment', 'hospital-management-system'); ?>
            </a>
        <?php endif; ?>
        
        <button type="button" id="download-pdf" class="button">
            <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
            <?php _e('Download PDF', 'hospital-management-system'); ?>
        </button>
    </div>

    <div class="invoice-preview" style="border: 1px solid #ddd; padding: 20px; background: #fff;">
        <?php echo $hms_billing->generate_invoice_html($bill_id, false); ?>
    </div>
</div>

<style>
.hms-invoice-wrap .invoice-preview {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.invoice-actions .button {
    margin-right: 10px;
}

.invoice-actions .dashicons {
    margin-right: 5px;
}

@media print {
    .wrap > h1,
    .invoice-actions,
    #wpadminbar,
    #adminmenumain,
    #wpfooter {
        display: none !important;
    }
    
    .invoice-preview {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Download PDF functionality (placeholder - would need a PDF library)
    $('#download-pdf').click(function() {
        // For now, just open print dialog
        // In a real implementation, you'd use a library like jsPDF or send to server for PDF generation
        window.print();
    });
    
    // Print functionality
    $('.button[target="_blank"]').click(function(e) {
        if ($(this).attr('href').indexOf('print=1') !== -1) {
            e.preventDefault();
            var printWindow = window.open($(this).attr('href'), '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    });
});
</script>