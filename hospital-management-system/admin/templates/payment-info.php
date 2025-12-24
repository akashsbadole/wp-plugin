<?php
/**
 * Admin template for processing payments for a bill.
 *
 * @package    Hospital_Management_System
 * @subpackage Hospital_Management_System/admin/templates
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Get bill_id from the URL query parameter
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;
if ($bill_id <= 0) {
    wp_die(__('Invalid Bill ID.', 'hospital-management-system'));
}

// Instantiate the billing class
global $hms_billing;
if (is_null($hms_billing)) {
    // This assumes the main plugin file instantiates and globalizes $hms_billing
    // If not, you might need to instantiate it here: $hms_billing = new HMS_Billing();
    wp_die(__('Billing system not initialized.', 'hospital-management-system'));
}

// Fetch bill details
$bill = $hms_billing->get_bill($bill_id);
if (!$bill) {
    wp_die(__('Bill not found.', 'hospital-management-system'));
}

// Fetch patient details
global $hms_patient;
if (is_null($hms_patient)) {
    wp_die(__('Patient system not initialized.', 'hospital-management-system'));
}
$patient = $hms_patient->get_patient_by_id($bill->patient_id);

// Fetch existing payments for this bill
$payments = $hms_billing->get_payments_for_bill($bill_id);

// Calculate due amount
$due_amount = floatval($bill->net_amount) - floatval($bill->amount_paid);

// Nonce for payment processing
$process_payment_nonce = wp_create_nonce('hms_process_payment_nonce');

?>
<div class="wrap hms-admin-wrap">
    <h1 class="wp-heading-inline"><?php _e('Process Payment', 'hospital-management-system'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-billing&action=view&bill_id=' . $bill_id)); ?>" class="page-title-action"><?php _e('&larr; Back to Bill Details', 'hospital-management-system'); ?></a>
    <hr class="wp-header-end">

    <div id="hms-payment-container" class="hms-container">
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Bill & Patient Information', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <div class="hms-row">
                    <div class="hms-col-md-6">
                        <h4><?php _e('Bill Details', 'hospital-management-system'); ?></h4>
                        <table class="form-table">
                            <tr>
                                <th><?php _e('Bill Number', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html($bill->bill_id); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Bill Date', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($bill->created_at))); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Total Amount', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html(number_format($bill->total_amount, 2)); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Discount', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html(number_format($bill->discount_amount, 2)); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Tax', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html(number_format($bill->tax_amount, 2)); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Net Amount', 'hospital-management-system'); ?></th>
                                <td><strong><?php echo esc_html(number_format($bill->net_amount, 2)); ?></strong></td>
                            </tr>
                            <tr>
                                <th><?php _e('Amount Paid', 'hospital-management-system'); ?></th>
                                <td><?php echo esc_html(number_format($bill->amount_paid, 2)); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Payment Status', 'hospital-management-system'); ?></th>
                                <td><span class="hms-badge hms-badge-<?php echo esc_attr(strtolower($bill->payment_status)); ?>"><?php echo esc_html(ucfirst($bill->payment_status)); ?></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="hms-col-md-6">
                        <h4><?php _e('Patient Details', 'hospital-management-system'); ?></h4>
                        <?php if ($patient) : ?>
                            <table class="form-table">
                                <tr>
                                    <th><?php _e('Patient ID', 'hospital-management-system'); ?></th>
                                    <td><?php echo esc_html($patient->patient_id); ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Name', 'hospital-management-system'); ?></th>
                                    <td><?php echo esc_html($patient->first_name . ' ' . $patient->last_name); ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Contact Number', 'hospital-management-system'); ?></th>
                                    <td><?php echo esc_html($patient->contact_number); ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e('Email', 'hospital-management-system'); ?></th>
                                    <td><?php echo esc_html($patient->email); ?></td>
                                </tr>
                            </table>
                        <?php else : ?>
                            <p><?php _e('Patient details not found.', 'hospital-management-system'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($due_amount > 0) : ?>
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Record New Payment', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <form id="hms-process-payment-form" method="post">
                    <input type="hidden" name="bill_id" value="<?php echo esc_attr($bill_id); ?>">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($process_payment_nonce); ?>">
                    <?php wp_nonce_field('hms_process_payment_action', 'security'); ?>

                    <div class="hms-row">
                        <div class="hms-col-md-6">
                            <div class="hms-form-group">
                                <label for="payment_amount"><?php _e('Amount to Pay', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                                <input type="number" id="payment_amount" name="payment_amount" class="hms-form-control" step="0.01" min="0.01" max="<?php echo esc_attr($due_amount); ?>" value="<?php echo esc_attr($due_amount); ?>" required>
                                <small class="form-text text-muted"><?php _e('Due Amount:', 'hospital-management-system'); ?> <?php echo esc_html(number_format($due_amount, 2)); ?></small>
                            </div>
                        </div>
                        <div class="hms-col-md-6">
                            <div class="hms-form-group">
                                <label for="payment_method"><?php _e('Payment Method', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                                <select id="payment_method" name="payment_method" class="hms-form-control" required>
                                    <option value=""><?php _e('Select Payment Method', 'hospital-management-system'); ?></option>
                                    <option value="cash"><?php _e('Cash', 'hospital-management-system'); ?></option>
                                    <option value="upi"><?php _e('UPI', 'hospital-management-system'); ?></option>
                                    <option value="card"><?php _e('Card (Credit/Debit)', 'hospital-management-system'); ?></option>
                                    <option value="net_banking"><?php _e('Net Banking', 'hospital-management-system'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hms-row">
                        <div class="hms-col-md-6">
                            <div class="hms-form-group" id="transaction_id_group" style="display: none;">
                                <label for="transaction_id"><?php _e('Transaction ID', 'hospital-management-system'); ?> <span class="hms-required">*</span></label>
                                <input type="text" id="transaction_id" name="transaction_id" class="hms-form-control">
                                <small class="form-text text-muted"><?php _e('Enter the transaction ID from your payment gateway or UPI app.', 'hospital-management-system'); ?></small>
                            </div>
                        </div>
                         <div class="hms-col-md-6">
                            <div class="hms-form-group">
                                <label for="payment_notes"><?php _e('Notes (Optional)', 'hospital-management-system'); ?></label>
                                <textarea id="payment_notes" name="payment_notes" class="hms-form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="hms-form-group">
                        <button type="submit" name="process_payment" class="hms-btn hms-btn-primary"><?php _e('Process Payment', 'hospital-management-system'); ?></button>
                        <span id="hms-payment-spinner" class="spinner"></span>
                    </div>
                </form>
                <div id="hms-payment-message"></div>
            </div>
        </div>
        <?php else : ?>
            <div class="notice notice-success"><p><?php _e('This bill has been fully paid. No further payments can be made.', 'hospital-management-system'); ?></p></div>
        <?php endif; ?>

        <?php if (!empty($payments)) : ?>
        <div class="hms-card">
            <div class="hms-card-header">
                <h2><?php _e('Payment History', 'hospital-management-system'); ?></h2>
            </div>
            <div class="hms-card-body">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Payment Date', 'hospital-management-system'); ?></th>
                            <th><?php _e('Method', 'hospital-management-system'); ?></th>
                            <th><?php _e('Transaction ID', 'hospital-management-system'); ?></th>
                            <th><?php _e('Amount', 'hospital-management-system'); ?></th>
                            <th><?php _e('Received By', 'hospital-management-system'); ?></th>
                            <th><?php _e('Notes', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment) : 
                            $received_by_user = get_userdata($payment->received_by);
                            $received_by_name = $received_by_user ? $received_by_user->display_name : __('N/A', 'hospital-management-system');
                        ?>
                        <tr>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payment->payment_date))); ?></td>
                            <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $payment->payment_method))); ?></td>
                            <td><?php echo !empty($payment->transaction_id) ? esc_html($payment->transaction_id) : 'N/A'; ?></td>
                            <td><?php echo esc_html(number_format($payment->amount, 2)); ?></td>
                            <td><?php echo esc_html($received_by_name); ?></td>
                            <td><?php echo esc_html($payment->notes); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Show/hide transaction ID field based on payment method
    $('#payment_method').on('change', function() {
        var selectedMethod = $(this).val();
        if (selectedMethod === 'upi' || selectedMethod === 'card' || selectedMethod === 'net_banking') {
            $('#transaction_id_group').show();
            $('#transaction_id').prop('required', true);
        } else {
            $('#transaction_id_group').hide();
            $('#transaction_id').prop('required', false).val('');
        }
    });

    // Handle form submission via AJAX
    $('#hms-process-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var $spinner = $('#hms-payment-spinner');
        var $messageContainer = $('#hms-payment-message');

        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');
        $messageContainer.removeClass('notice notice-success notice-error').html('');

        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress global variable for admin-ajax.php
            data: {
                action: 'hms_process_payment', // AJAX action hook
                security: $form.find('input[name="security"]').val(),
                bill_id: $form.find('input[name="bill_id"]').val(),
                payment_amount: $form.find('input[name="payment_amount"]').val(),
                payment_method: $form.find('select[name="payment_method"]').val(),
                transaction_id: $form.find('input[name="transaction_id"]').val(),
                payment_notes: $form.find('textarea[name="payment_notes"]').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $messageContainer.addClass('notice notice-success').html('<p>' + response.data.message + '</p>');
                    // Optionally, reload the page or update the payment history dynamically
                    setTimeout(function() {
                        location.reload(); // Reload to see updated payment history and status
                    }, 2000);
                } else {
                    $messageContainer.addClass('notice notice-error').html('<p>' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.data : '<?php _e('An unknown error occurred.', 'hospital-management-system'); ?>';
                $messageContainer.addClass('notice notice-error').html('<p>' + errorMessage + '</p>');
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
</script>
