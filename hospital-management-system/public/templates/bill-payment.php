<div class="hms-patient-portal-wrap">
    <div class="hms-bill-payment-page">
        <h2><?php esc_html_e('Your Bills', 'hospital-management-system'); ?></h2>

        <?php if (!empty($bills)) : ?>
            <table class="hms-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Bill ID', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Total Amount', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Paid Amount', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Balance', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                        <th><?php esc_html_e('Action', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bills as $bill) : 
                        $total_amount = floatval(get_post_meta($bill->ID, '_net_amount', true));
                        $paid_amount = floatval(get_post_meta($bill->ID, '_paid_amount', true));
                        $balance = $total_amount - $paid_amount;
                        $payment_status = get_post_meta($bill->ID, '_payment_status', true);
                    ?>
                        <tr>
                            <td><?php echo esc_html(get_post_meta($bill->ID, '_bill_id', true) ?: '#' . $bill->ID); ?></td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($bill->post_date))); ?></td>
                            <td><?php echo esc_html(number_format_i18n($total_amount, 2)); ?></td>
                            <td><?php echo esc_html(number_format_i18n($paid_amount, 2)); ?></td>
                            <td><?php echo esc_html(number_format_i18n($balance, 2)); ?></td>
                            <td><?php echo esc_html($payment_status); ?></td>
                            <td>
                                <a href="#" class="hms-view-bill-link hms-btn hms-btn-small" data-bill-id="<?php echo esc_attr($bill->ID); ?>"><?php esc_html_e('View Details', 'hospital-management-system'); ?></a>
                                <?php if ($payment_status !== 'Paid' && $balance > 0) : ?>
                                    <a href="#" class="hms-pay-bill-link hms-btn hms-btn-success hms-btn-small" data-bill-id="<?php echo esc_attr($bill->ID); ?>" data-amount="<?php echo esc_attr($balance); ?>"><?php esc_html_e('Pay', 'hospital-management-system'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e('You have no bills at this time.', 'hospital-management-system'); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for Bill Details -->
<div id="hms-bill-details-modal" class="hms-modal">
    <div class="hms-modal-content">
        <span class="hms-modal-close">&times;</span>
        <div id="hms-bill-details-content"></div>
    </div>
</div>
