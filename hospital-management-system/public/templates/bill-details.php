<div class="hms-bill-details">
    <h3><?php esc_html_e('Bill Details', 'hospital-management-system'); ?></h3>
    <?php if ($bill && !is_wp_error($bill)) : ?>
        <p><strong><?php esc_html_e('Bill ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_post_meta($bill->ID, '_bill_id', true) ?: '#' . $bill->ID); ?></p>
        <p><strong><?php esc_html_e('Date:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($bill->post_date))); ?></p>
        <p><strong><?php esc_html_e('Patient ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_post_meta($bill->ID, '_patient_id', true)); ?></p>

        <h4><?php esc_html_e('Bill Items', 'hospital-management-system'); ?></h4>
        <?php $bill_items_data = maybe_unserialize($bill_items); ?>
        <?php if (!empty($bill_items_data) && is_array($bill_items_data)) : ?>
            <table class="hms-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Description', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Quantity', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Price', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Total', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bill_items_data as $item) : ?>
                        <tr>
                            <td><?php echo esc_html($item['description']); ?></td>
                            <td><?php echo esc_html($item['quantity']); ?></td>
                            <td><?php echo esc_html($item['price']); ?></td>
                            <td><?php echo esc_html($item['total']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e('No bill items found.', 'hospital-management-system'); ?></p>
        <?php endif; ?>

        <div class="hms-bill-summary">
            <p><strong><?php esc_html_e('Total Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html($total_amount); ?></p>
            <p><strong><?php esc_html_e('Discount:', 'hospital-management-system'); ?></strong> <?php echo esc_html($discount); ?></p>
            <p><strong><?php esc_html_e('Tax:', 'hospital-management-system'); ?></strong> <?php echo esc_html($tax); ?></p>
            <hr>
            <p><strong><?php esc_html_e('Net Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html($net_amount); ?></p>
            <p><strong><?php esc_html_e('Paid Amount:', 'hospital-management-system'); ?></strong> <?php echo esc_html($paid_amount); ?></p>
            <p><strong><?php esc_html_e('Payment Status:', 'hospital-management-system'); ?></strong> <?php echo esc_html($payment_status); ?></p>
        </div>

        <?php if ($payment_status !== 'Paid' && floatval($net_amount) > floatval($paid_amount)) : ?>
            <div class="hms-bill-payment-form">
                <h4><?php esc_html_e('Pay Now', 'hospital-management-system'); ?></h4>
                <div id="hms-payment-message"></div>
                <form id="hms-bill-payment-form">
                    <div class="form-group">
                        <label for="payment_amount"><?php esc_html_e('Amount to Pay:', 'hospital-management-system'); ?></label>
                        <input type="number" name="amount" id="payment_amount" class="form-control" value="<?php echo esc_attr(floatval($net_amount) - floatval($paid_amount)); ?>" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method"><?php esc_html_e('Payment Method:', 'hospital-management-system'); ?></label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="cash"><?php esc_html_e('Cash (Pay at Hospital)', 'hospital-management-system'); ?></option>
                            <option value="upi"><?php esc_html_e('UPI', 'hospital-management-system'); ?></option>
                            <option value="card"><?php esc_html_e('Credit/Debit Card', 'hospital-management-system'); ?></option>
                            <option value="net_banking"><?php esc_html_e('Net Banking', 'hospital-management-system'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success"><?php esc_html_e('Process Payment', 'hospital-management-system'); ?></button>
                    <input type="hidden" name="bill_id" value="<?php echo esc_attr($bill->ID); ?>">
                    <input type="hidden" name="action" value="pay_bill">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hms_admin_nonce'); ?>">
                </form>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <p><?php esc_html_e('Bill details could not be loaded.', 'hospital-management-system'); ?></p>
    <?php endif; ?>
</div>
