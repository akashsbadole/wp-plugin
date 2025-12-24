<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['id'])) {
            _e('Edit Insurance Claim', 'hospital-management-system');
        } else {
            _e('Create New Insurance Claim', 'hospital-management-system');
        }
        ?>
        <a href="<?php echo admin_url('admin.php?page=hms_insurance_claims'); ?>" class="page-title-action"><?php _e('&larr; Back to Claims', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    $claim = null;
    $is_edit = false;

    if (isset($_GET['id'])) {
        $is_edit = true;
        $claim_id = intval($_GET['id']);
        
        // Get claim details with related data
        global $wpdb;
        $claims_table = $wpdb->prefix . 'hms_claims';
        $patients_table = $wpdb->prefix . 'hms_patients';
        $providers_table = $wpdb->prefix . 'hms_insurance_providers';
        $bills_table = $wpdb->prefix . 'hms_bills';
        
        $claim = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, p.first_name, p.last_name, p.email as patient_email, 
                   pr.name as provider_name, b.total_amount as bill_amount
            FROM $claims_table c 
            LEFT JOIN $patients_table p ON c.patient_id = p.id 
            LEFT JOIN $providers_table pr ON c.insurance_provider_id = pr.id 
            LEFT JOIN $bills_table b ON c.bill_id = b.id 
            WHERE c.id = %d
        ", $claim_id));

        if (!$claim) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Insurance claim not found.', 'hospital-management-system') . '</p></div>';
            return;
        }
    }

    // Handle form submission feedback
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <form id="claim-form" method="post" action="">
        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="page" value="hms_claim_details">
        <?php if ($is_edit): ?>
            <input type="hidden" name="claim_id" value="<?php echo esc_attr($claim->id); ?>">
        <?php endif; ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="submitdiv" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Save', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="save_claim" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Claim', 'hospital-management-system') : __('Create Claim', 'hospital-management-system'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($is_edit): ?>
                    <div id="claimstatus" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Claim Status', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="status"><?php _e('Claim Status', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="status" id="status" class="regular-text">
                                                <option value="pending_preauth" <?php echo ($claim->status === 'pending_preauth') ? 'selected="selected"' : ''; ?>><?php _e('Pending Pre-auth', 'hospital-management-system'); ?></option>
                                                <option value="preauth_approved" <?php echo ($claim->status === 'preauth_approved') ? 'selected="selected"' : ''; ?>><?php _e('Pre-auth Approved', 'hospital-management-system'); ?></option>
                                                <option value="submitted_for_settlement" <?php echo ($claim->status === 'submitted_for_settlement') ? 'selected="selected"' : ''; ?>><?php _e('Submitted for Settlement', 'hospital-management-system'); ?></option>
                                                <option value="settled" <?php echo ($claim->status === 'settled') ? 'selected="selected"' : ''; ?>><?php _e('Settled', 'hospital-management-system'); ?></option>
                                                <option value="rejected" <?php echo ($claim->status === 'rejected') ? 'selected="selected"' : ''; ?>><?php _e('Rejected', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="preauth_status"><?php _e('Pre-auth Status', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="preauth_status" id="preauth_status" class="regular-text">
                                                <option value="pending" <?php echo ($claim->preauth_status === 'pending') ? 'selected="selected"' : ''; ?>><?php _e('Pending', 'hospital-management-system'); ?></option>
                                                <option value="submitted" <?php echo ($claim->preauth_status === 'submitted') ? 'selected="selected"' : ''; ?>><?php _e('Submitted', 'hospital-management-system'); ?></option>
                                                <option value="approved" <?php echo ($claim->preauth_status === 'approved') ? 'selected="selected"' : ''; ?>><?php _e('Approved', 'hospital-management-system'); ?></option>
                                                <option value="rejected" <?php echo ($claim->preauth_status === 'rejected') ? 'selected="selected"' : ''; ?>><?php _e('Rejected', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="claimdetails" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Claim Details', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="patient_id"><?php _e('Patient', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <select name="patient_id" id="patient_id" class="regular-text" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                                                <option value=""><?php _e('Select Patient', 'hospital-management-system'); ?></option>
                                                <?php
                                                $patients_table = $wpdb->prefix . 'hms_patients';
                                                $patients = $wpdb->get_results("SELECT id, first_name, last_name FROM $patients_table ORDER BY first_name ASC");
                                                foreach ($patients as $patient) {
                                                    $selected = ($is_edit && $claim->patient_id == $patient->id) ? 'selected="selected"' : '';
                                                    echo '<option value="' . esc_attr($patient->id) . '" ' . $selected . '>' . esc_html($patient->first_name . ' ' . $patient->last_name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if ($is_edit): ?>
                                                <input type="hidden" name="patient_id" value="<?php echo esc_attr($claim->patient_id); ?>">
                                                <p class="description"><?php _e('Patient cannot be changed after claim creation.', 'hospital-management-system'); ?></p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="bill_id"><?php _e('Bill', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <select name="bill_id" id="bill_id" class="regular-text" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                                                <option value=""><?php _e('Select Bill', 'hospital-management-system'); ?></option>
                                                <?php
                                                $bills_table = $wpdb->prefix . 'hms_bills';
                                                $patients_table = $wpdb->prefix . 'hms_patients';
                                                $bills = $wpdb->get_results("
                                                    SELECT b.id, b.total_amount, p.first_name, p.last_name 
                                                    FROM $bills_table b 
                                                    LEFT JOIN $patients_table p ON b.patient_id = p.id 
                                                    ORDER BY b.created_at DESC
                                                ");
                                                foreach ($bills as $bill) {
                                                    $selected = ($is_edit && $claim->bill_id == $bill->id) ? 'selected="selected"' : '';
                                                    echo '<option value="' . esc_attr($bill->id) . '" ' . $selected . '>Bill #' . esc_html($bill->id) . ' - ' . esc_html($bill->first_name . ' ' . $bill->last_name) . ' ($' . number_format($bill->total_amount, 2) . ')</option>';
                                                }
                                                ?>
                                            </select>
                                            <?php if ($is_edit): ?>
                                                <input type="hidden" name="bill_id" value="<?php echo esc_attr($claim->bill_id); ?>">
                                                <p class="description"><?php _e('Bill cannot be changed after claim creation.', 'hospital-management-system'); ?></p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="insurance_provider_id"><?php _e('Insurance Provider', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <select name="insurance_provider_id" id="insurance_provider_id" class="regular-text" required>
                                                <option value=""><?php _e('Select Insurance Provider', 'hospital-management-system'); ?></option>
                                                <?php
                                                $providers_table = $wpdb->prefix . 'hms_insurance_providers';
                                                $providers = $wpdb->get_results("SELECT id, name FROM $providers_table WHERE status = 'active' ORDER BY name ASC");
                                                foreach ($providers as $provider) {
                                                    $selected = ($is_edit && $claim->insurance_provider_id == $provider->id) ? 'selected="selected"' : '';
                                                    echo '<option value="' . esc_attr($provider->id) . '" ' . $selected . '>' . esc_html($provider->name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="policy_number"><?php _e('Policy Number', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="policy_number" id="policy_number" class="regular-text" value="<?php echo $is_edit ? esc_attr($claim->policy_number) : ''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="claim_amount"><?php _e('Claim Amount', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="number" name="claim_amount" id="claim_amount" class="regular-text" step="0.01" min="0" value="<?php echo $is_edit ? esc_attr($claim->claim_amount) : ''; ?>" required>
                                        </td>
                                    </tr>
                                    <?php if (!$is_edit): ?>
                                    <tr>
                                        <td colspan="2">
                                            <input type="hidden" name="initiated_by" value="<?php echo get_current_user_id(); ?>">
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($is_edit && $claim->settled_amount): ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="settled_amount"><?php _e('Settled Amount', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" name="settled_amount" id="settled_amount" class="regular-text" step="0.01" min="0" value="<?php echo esc_attr($claim->settled_amount); ?>">
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="notes"><?php _e('Notes', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($claim->notes) : ''; ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($is_edit): ?>
                    <div id="claimhistory" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Claim History', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th><?php _e('Date', 'hospital-management-system'); ?></th>
                                        <th><?php _e('Status', 'hospital-management-system'); ?></th>
                                        <th><?php _e('Details', 'hospital-management-system'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($claim->initiated_at))); ?></td>
                                        <td><?php _e('Claim Initiated', 'hospital-management-system'); ?></td>
                                        <td><?php printf(__('Claim created for $%s', 'hospital-management-system'), number_format($claim->claim_amount, 2)); ?></td>
                                    </tr>
                                    <?php if ($claim->preauth_request_id): ?>
                                    <tr>
                                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($claim->initiated_at))); ?></td>
                                        <td><?php _e('Pre-auth Submitted', 'hospital-management-system'); ?></td>
                                        <td><?php printf(__('Pre-auth request ID: %s', 'hospital-management-system'), esc_html($claim->preauth_request_id)); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($claim->settled_at): ?>
                                    <tr>
                                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($claim->settled_at))); ?></td>
                                        <td><?php _e('Claim Settled', 'hospital-management-system'); ?></td>
                                        <td><?php printf(__('Settled for $%s', 'hospital-management-system'), number_format($claim->settled_amount, 2)); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Form submission validation
    $('#claim-form').on('submit', function(e) {
        if ($('#patient_id').val() === '') {
            alert('<?php _e('Please select a patient.', 'hospital-management-system'); ?>');
            $('#patient_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#bill_id').val() === '') {
            alert('<?php _e('Please select a bill.', 'hospital-management-system'); ?>');
            $('#bill_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#insurance_provider_id').val() === '') {
            alert('<?php _e('Please select an insurance provider.', 'hospital-management-system'); ?>');
            $('#insurance_provider_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#claim_amount').val() === '' || parseFloat($('#claim_amount').val()) <= 0) {
            alert('<?php _e('Please enter a valid claim amount.', 'hospital-management-system'); ?>');
            $('#claim_amount').focus();
            e.preventDefault();
            return false;
        }
    });

    // Auto-populate claim amount from bill amount when bill is selected
    $('#bill_id').on('change', function() {
        var billText = $(this).find('option:selected').text();
        var amountMatch = billText.match(/\$([0-9,]+\.?[0-9]*)/);
        if (amountMatch) {
            var amount = amountMatch[1].replace(/,/g, '');
            $('#claim_amount').val(amount);
        }
    });
});
</script>