<?php
/**
 * Template for processing payments
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

// Calculate balance due
$balance_due = floatval($bill->net_amount) - floatval($bill->paid_amount);

// Handle form submission
if (isset($_POST['process_payment']) && wp_verify_nonce($_POST['nonce'], 'hms_admin_nonce')) {
    $payment_data = array(
        'amount' => floatval($_POST['payment_amount']),
        'payment_method' => sanitize_text_field($_POST['payment_method']),
        'payment_date' => sanitize_text_field($_POST['payment_date']),
        'transaction_id' => sanitize_text_field($_POST['transaction_id']),
        'notes' => sanitize_textarea_field($_POST['payment_notes'])
    );
    
    $payment_id = $hms_billing->process_payment($bill_id, $payment_data);
    
    if ($payment_id) {
        $redirect_url = admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill_id . '&message=' . urlencode(__('Payment processed successfully.', 'hospital-management-system')) . '&type=success');
        wp_redirect($redirect_url);
        exit;
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to process payment. Please try again.', 'hospital-management-system') . '</p></div>';
    }
}
?>

<div class="wrap hms-payment-wrap">
    <h1 class="wp-heading-inline"><?php _e('Process Payment', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill_id)); ?>" class="page-title-action"><?php _e('← Back to Invoice', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <div class="payment-container">
        <div class="payment-form-container">
            <div class="postbox">
                <h2 class="hndle"><?php _e('Bill Summary', 'hospital-management-system'); ?></h2>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Bill Number:', 'hospital-management-system'); ?></th>
                            <td><?php echo esc_html($bill->bill_number); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Patient:', 'hospital-management-system'); ?></th>
                            <td><?php echo esc_html($bill->patient->first_name . ' ' . $bill->patient->last_name); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Total Amount:', 'hospital-management-system'); ?></th>
                            <td><?php echo esc_html(number_format($bill->net_amount, 2)); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Amount Paid:', 'hospital-management-system'); ?></th>
                            <td><?php echo esc_html(number_format($bill->paid_amount, 2)); ?></td>
                        </tr>
                        <tr>
                            <th><?php _e('Balance Due:', 'hospital-management-system'); ?></th>
                            <td><strong style="color: #d63384;"><?php echo esc_html(number_format($balance_due, 2)); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if ($balance_due > 0): ?>
            <div class="postbox">
                <h2 class="hndle"><?php _e('Payment Information', 'hospital-management-system'); ?></h2>
                <div class="inside">
                    <form method="post" action="">
                        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="payment_amount"><?php _e('Payment Amount', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                </th>
                                <td>
                                    <input type="number" name="payment_amount" id="payment_amount" 
                                           class="regular-text" step="0.01" min="0.01" 
                                           max="<?php echo esc_attr($balance_due); ?>" 
                                           value="<?php echo esc_attr($balance_due); ?>" required>
                                    <p class="description"><?php printf(__('Maximum amount: %s', 'hospital-management-system'), number_format($balance_due, 2)); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="payment_method"><?php _e('Payment Method', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                </th>
                                <td>
                                    <select name="payment_method" id="payment_method" class="regular-text" required>
                                        <option value=""><?php _e('Select Payment Method', 'hospital-management-system'); ?></option>
                                        <option value="cash"><?php _e('Cash', 'hospital-management-system'); ?></option>
                                        <option value="credit_card"><?php _e('Credit Card', 'hospital-management-system'); ?></option>
                                        <option value="debit_card"><?php _e('Debit Card', 'hospital-management-system'); ?></option>
                                        <option value="bank_transfer"><?php _e('Bank Transfer', 'hospital-management-system'); ?></option>
                                        <option value="upi"><?php _e('UPI', 'hospital-management-system'); ?></option>
                                        <option value="cheque"><?php _e('Cheque', 'hospital-management-system'); ?></option>
                                        <option value="insurance"><?php _e('Insurance', 'hospital-management-system'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="payment_date"><?php _e('Payment Date', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <input type="datetime-local" name="payment_date" id="payment_date" 
                                           class="regular-text" value="<?php echo current_time('Y-m-d\TH:i'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="transaction_id"><?php _e('Transaction ID / Reference', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="transaction_id" id="transaction_id" 
                                           class="regular-text" placeholder="<?php _e('Optional reference number', 'hospital-management-system'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="payment_notes"><?php _e('Notes', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <textarea name="payment_notes" id="payment_notes" rows="3" 
                                              class="large-text" placeholder="<?php _e('Optional payment notes', 'hospital-management-system'); ?>"></textarea>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="process_payment" class="button button-primary" 
                                   value="<?php _e('Process Payment', 'hospital-management-system'); ?>">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill_id)); ?>" 
                               class="button"><?php _e('Cancel', 'hospital-management-system'); ?></a>
                        </p>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="notice notice-success">
                <p><?php _e('This bill has been fully paid.', 'hospital-management-system'); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($bill->payments)): ?>
            <div class="postbox">
                <h2 class="hndle"><?php _e('Payment History', 'hospital-management-system'); ?></h2>
                <div class="inside">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                <th><?php _e('Amount', 'hospital-management-system'); ?></th>
                                <th><?php _e('Method', 'hospital-management-system'); ?></th>
                                <th><?php _e('Reference', 'hospital-management-system'); ?></th>
                                <th><?php _e('Notes', 'hospital-management-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bill->payments as $payment): ?>
                            <tr>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payment->payment_date)); ?></td>
                                <td><?php echo esc_html(number_format($payment->amount, 2)); ?></td>
                                <td><?php echo esc_html(ucwords(str_replace('_', ' ', $payment->payment_method))); ?></td>
                                <td><?php echo esc_html($payment->transaction_id ?: '—'); ?></td>
                                <td><?php echo esc_html($payment->notes ?: '—'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.hms-payment-wrap .payment-container {
    max-width: 800px;
}

.hms-payment-wrap .postbox {
    margin-bottom: 20px;
}

.hms-payment-wrap .form-table th {
    width: 200px;
}

.hms-payment-wrap .description {
    color: #666;
    font-style: italic;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-calculate payment amount based on balance
    $('#payment_amount').on('input', function() {
        var amount = parseFloat($(this).val());
        var balance = parseFloat(<?php echo $balance_due; ?>);
        
        if (amount > balance) {
            $(this).val(balance.toFixed(2));
        }
    });
    
    // Payment method specific fields
    $('#payment_method').change(function() {
        var method = $(this).val();
        var $transactionRow = $('#transaction_id').closest('tr');
        
        if (method === 'cash') {
            $transactionRow.hide();
        } else {
            $transactionRow.show();
        }
    });
});
</script>