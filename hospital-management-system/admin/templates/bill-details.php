<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'print') {
            _e('Print Bill', 'hospital-management-system');
        } elseif (isset($_GET['id'])) {
            _e('Edit Bill', 'hospital-management-system');
        } else {
            _e('Add New Bill', 'hospital-management-system');
        }
        ?>
        <?php if (!isset($_GET['action']) || $_GET['action'] !== 'print'): ?>
            <a href="<?php echo admin_url('admin.php?page=hms_billing'); ?>" class="page-title-action"><?php _e('&larr; Back to Billing', 'hospital-management-system'); ?></a>
        <?php endif; ?>
    </h1>

    <?php
        global $hms_billing;
        $bill = null;
        $bill_items = array();
        $bill_payments = array();
        $patient = null;
        $is_edit = false;
        $is_print = isset($_GET['action']) && $_GET['action'] === 'print';

        if (isset($_GET['id'])) {
            $is_edit = true;
            $bill_id = intval($_GET['id']);
            
            // Use the billing class to get complete bill data
            if ($hms_billing) {
                $bill = $hms_billing->get_bill_with_details($bill_id);
                
                if (!$bill) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . __('Bill not found.', 'hospital-management-system') . '</p></div>';
                    return;
                }
                
                // Extract related data
                $bill_items = $bill->items ?? array();
                $bill_payments = $bill->payments ?? array();
                $patient = $bill->patient ?? null;
                
                // Normalize the bill object - CRITICAL FIX
                $bill->total_amount = floatval($bill->total_amount);
                $bill->discount_amount = floatval($bill->discount_amount);
                $bill->tax_amount = floatval($bill->tax_amount);
                $bill->tax_percentage = floatval($bill->tax_percentage);
                
                // FIX: Use paid_amount as the source of truth (from your data dump)
                $bill->paid_amount = floatval($bill->paid_amount);
                $bill->amount_paid = $bill->paid_amount; // Sync both fields
                
                // FIX: Handle payment_method being '0.000000' or numeric
                if ($bill->payment_method === '0.000000' || is_numeric($bill->payment_method)) {
                    $bill->payment_method = '';
                }
                
                // FIX: Handle notes being '0' or numeric
                if ($bill->notes === '0' || $bill->notes === 0) {
                    $bill->notes = '';
                }
                
                // FIX: Calculate net_amount if it's 0 or not set
                if (empty($bill->net_amount) || $bill->net_amount == 0) {
                    $bill->net_amount = $bill->total_amount - $bill->discount_amount + $bill->tax_amount;
                }
                
                // Debug
                error_log('Bill ID ' . $bill_id . ' has ' . count($bill_items) . ' items');
                error_log('Bill data: ' . print_r($bill, true));
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Billing system not available.', 'hospital-management-system') . '</p></div>';
                return;
            }
        }
        ?>

    <?php if ($is_print): ?>
        <div id="bill-print-area" style="font-family: Arial, sans-serif; width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ccc;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0;"><?php echo esc_html(get_option('hms_hospital_name', 'Hospital Name')); ?></h2>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_address', 'Hospital Address')); ?></p>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_phone', 'Phone')); ?> | <?php echo esc_html(get_option('hms_hospital_email', 'Email')); ?></p>
                <hr style="margin-top: 15px;">
            </div>
            
            <h3 style="text-align: center; margin-bottom: 20px;"><?php _e('INVOICE / BILL', 'hospital-management-system'); ?></h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <strong><?php _e('Bill To:', 'hospital-management-system'); ?></strong>
                    <?php
                    if ($patient) {
                        echo '<p style="margin: 2px 0;"><strong>' . esc_html($patient->first_name . ' ' . $patient->last_name) . '</strong></p>';
                        echo '<p style="margin: 2px 0;">' . esc_html($patient->address) . '</p>';
                        echo '<p style="margin: 2px 0;">' . esc_html($patient->contact_number) . '</p>';
                        echo '<p style="margin: 2px 0;">' . esc_html($patient->email) . '</p>';
                    }
                    ?>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 2px 0;"><strong><?php _e('Bill Number:', 'hospital-management-system'); ?></strong> <?php echo esc_html($bill->bill_id); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Issue Date:', 'hospital-management-system'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($bill->issue_date)); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Due Date:', 'hospital-management-system'); ?></strong> <?php echo !empty($bill->due_date) ? date_i18n(get_option('date_format'), strtotime($bill->due_date)) : __('N/A', 'hospital-management-system'); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Payment Method:', 'hospital-management-system'); ?></strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $bill->payment_method ?: 'N/A'))); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Payment Status:', 'hospital-management-system'); ?></strong> <?php echo esc_html(ucwords(str_replace('_', ' ', $bill->payment_status ?: 'N/A'))); ?></p>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?php _e('Description', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('Qty', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php _e('Price', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php _e('Total', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bill_items): ?>
                        <?php foreach ($bill_items as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($item->description); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->quantity); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($item->price, 2)); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($item->quantity * $item->price, 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('No items found.', 'hospital-management-system'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Subtotal:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($bill->total_amount - $bill->tax_amount + $bill->discount_amount, 2)); ?></td>
                    </tr>
                    <?php if (isset($bill->discount_amount) && $bill->discount_amount > 0): ?>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Discount Amount:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($bill->discount_amount, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isset($bill->tax_amount) && $bill->tax_amount > 0): ?>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Tax Amount:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($bill->tax_amount, 2)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Total Amount:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 1.2em;"><?php echo esc_html(number_format($bill->total_amount, 2)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Amount Paid:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo esc_html(number_format($bill->paid_amount, 2)); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: right;"><strong><?php _e('Balance Due:', 'hospital-management-system'); ?></strong></td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; color: #d9534f;"><?php echo esc_html(number_format($bill->total_amount - $bill->paid_amount, 2)); ?></td>
                    </tr>
                </tfoot>
            </table>
            
            <div style="margin-top: 30px;">
                <p><strong><?php _e('Notes / Terms:', 'hospital-management-system'); ?></strong></p>
                <p><?php echo nl2br(esc_html($bill->notes)); ?></p>
            </div>

            <div style="text-align: center; margin-top: 40px;">
                <p><?php _e('Thank you for your business!', 'hospital-management-system'); ?></p>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                window.print();
            });
        </script>
    <?php else: ?>
        <form id="bill-details-form" method="post" action="">
            <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
            <input type="hidden" name="page" value="hms_bill_details">
            <?php if ($is_edit): ?>
                <input type="hidden" name="bill_id" value="<?php echo esc_attr($bill->id); ?>">
            <?php endif; ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main content area -->
                    </div>

                    <div id="postbox-container-1" class="postbox-container">
                        <div id="submitdiv" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Save', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="save_bill" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Bill', 'hospital-management-system') : __('Generate Bill', 'hospital-management-system'); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($is_edit): ?>
                        <div id="print-action" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Print & View', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill->id)); ?>" class="button button-large" target="_blank"><?php _e('View Invoice', 'hospital-management-system'); ?></a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill->id . '&print=1')); ?>" class="button button-large" target="_blank"><?php _e('Print Invoice', 'hospital-management-system'); ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <div id="billdetails" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Bill Details', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="patient_id"><?php _e('Patient', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <?php
                                                global $wpdb;
                                                $patients_table = $wpdb->prefix . 'hms_patients';
                                                $patients = $wpdb->get_results("SELECT id, first_name, last_name, patient_id FROM $patients_table ORDER BY first_name, last_name");
                                                ?>
                                                <select name="patient_id" id="patient_id" class="regular-text" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                                                    <option value=""><?php _e('&mdash; Select Patient &mdash;', 'hospital-management-system'); ?></option>
                                                    <?php
                                                    $selected_patient_id = $is_edit ? $bill->patient_id : (isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0);
                                                    foreach ($patients as $patient) {
                                                        $display_name = $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')';
                                                        echo '<option value="' . esc_attr($patient->id) . '" ' . selected($selected_patient_id, $patient->id, false) . '>' . esc_html($display_name) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <?php if ($is_edit): ?>
                                                    <input type="hidden" name="patient_id" value="<?php echo esc_attr($bill->patient_id); ?>">
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="appointment_id"><?php _e('Link to Appointment (Optional)', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <?php
                                                global $wpdb;
                                                $appointments_table = $wpdb->prefix . 'hms_appointments';
                                                $appointments = $wpdb->get_results("SELECT id, appointment_date, appointment_time FROM $appointments_table WHERE patient_id = " . intval($selected_patient_id) . " ORDER BY appointment_date DESC");
                                                ?>
                                                <select name="appointment_id" id="appointment_id" class="regular-text">
                                                    <option value=""><?php _e('&mdash; Select Appointment &mdash;', 'hospital-management-system'); ?></option>
                                                    <?php
                                                    $selected_appointment_id = $is_edit ? $bill->appointment_id : (isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0);
                                                    foreach ($appointments as $appointment) {
                                                        $display_appointment = date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)) . ' ' . date_i18n(get_option('time_format'), strtotime($appointment->appointment_time));
                                                        echo '<option value="' . esc_attr($appointment->id) . '" ' . selected($selected_appointment_id, $appointment->id, false) . '>' . esc_html('#' . $appointment->id . ' - ' . $display_appointment) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="issue_date"><?php _e('Issue Date', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <input type="date" name="issue_date" id="issue_date" class="regular-text" value="<?php echo $is_edit && $bill && !empty($bill->issue_date) ? esc_attr(date('Y-m-d', strtotime($bill->issue_date))) : (isset($_POST['issue_date']) ? esc_attr($_POST['issue_date']) : current_time('Y-m-d')); ?>" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="due_date"><?php _e('Due Date', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <input type="date" name="due_date" id="due_date" class="regular-text" value="<?php echo $is_edit && $bill && isset($bill->due_date) && $bill->due_date !== null && !empty($bill->due_date) ? esc_attr($bill->due_date) : (isset($_POST['due_date']) ? esc_attr($_POST['due_date']) : ''); ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="status"><?php _e('Status', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <select name="status" id="status" class="regular-text">
                                                    <?php
                                                    $statuses = array('unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled');
                                                    $selected_status = $is_edit && $bill && isset($bill->status) && $bill->status !== null ? $bill->status : (isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'unpaid');
                                                    foreach ($statuses as $status_value) {
                                                        echo '<option value="' . esc_attr($status_value) . '" ' . selected($selected_status, $status_value, false) . '>' . esc_html(ucwords(str_replace('_', ' ', $status_value))) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="billitems" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Bill Items', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="wp-list-table widefat fixed striped bill-items-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php _e('Description', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Qty', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Price', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Total', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Actions', 'hospital-management-system'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bill-items-tbody">
                                        <?php
                                        $items_to_loop = $bill_items;
                                        if (isset($_POST['bill_items']) && is_array($_POST['bill_items'])) {
                                            $items_to_loop = $_POST['bill_items']; // Use POST data if form was submitted and failed
                                        }
                                        
                                        // Debug: Log the items we're about to display
                                        error_log('Items to loop: ' . print_r($items_to_loop, true));
                                        error_log('Is edit mode: ' . ($is_edit ? 'Yes' : 'No'));
                                        error_log('Bill items count: ' . count($bill_items));
                                        
                                        if ($items_to_loop):
                                            foreach ($items_to_loop as $index => $item):
                                                // Handle both POST data and database data
                                                if (is_array($item)) {
                                                    $item_id = isset($item['id']) ? intval($item['id']) : 0;
                                                    $description = isset($item['description']) ? sanitize_text_field($item['description']) : '';
                                                    $quantity = isset($item['quantity']) ? floatval($item['quantity']) : 1;
                                                    $price = isset($item['price']) ? floatval($item['price']) : 0;
                                                } else {
                                                    // Database object - ensure proper type casting
                                                    $item_id = $item->id;
                                                    $description = $item->description;
                                                    $quantity = floatval($item->quantity);
                                                    $price = floatval($item->price);
                                                }
                                                
                                                // Debug: Log each item
                                                error_log("Item $index: ID=$item_id, Description=$description, Quantity=$quantity, Price=$price");
                                        ?>
                                        <tr class="bill-item-row">
                                            <td>
                                                <input type="text" name="bill_items[<?php echo $index; ?>][description]" value="<?php echo esc_attr($description); ?>" class="regular-text" placeholder="<?php _e('e.g., Consultation Fee', 'hospital-management-system'); ?>" required>
                                                <input type="hidden" name="bill_items[<?php echo $index; ?>][id]" value="<?php echo esc_attr($item_id); ?>">
                                            </td>
                                            <td><input type="number" name="bill_items[<?php echo $index; ?>][quantity]" value="<?php echo esc_attr($quantity); ?>" class="small-text" step="0.01" min="1" required></td>
                                            <td><input type="number" name="bill_items[<?php echo $index; ?>][price]" value="<?php echo esc_attr($price); ?>" class="small-text" step="0.01" min="0" required></td>
                                            <td class="item-total"><?php echo esc_html(number_format($quantity * $price, 2)); ?></td>
                                            <td><button type="button" class="button button-link-delete remove-item-row"><?php _e('Remove', 'hospital-management-system'); ?></button></td>
                                        </tr>
                                            <?php endforeach;
                                        else: ?>
                                        <tr class="bill-item-row">
                                            <td><input type="text" name="bill_items[0][description]" value="" class="regular-text" placeholder="<?php _e('e.g., Consultation Fee', 'hospital-management-system'); ?>" required><input type="hidden" name="bill_items[0][id]" value="0"></td>
                                            <td><input type="number" name="bill_items[0][quantity]" value="1" class="small-text" step="0.01" min="1" required></td>
                                            <td><input type="number" name="bill_items[0][price]" value="0" class="small-text" step="0.01" min="0" required></td>
                                            <td class="item-total">0.00</td>
                                            <td><button type="button" class="button button-link-delete remove-item-row"><?php _e('Remove', 'hospital-management-system'); ?></button></td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" id="add-item-row" class="button"><?php _e('+ Add Item', 'hospital-management-system'); ?></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: right;"><strong><?php _e('Subtotal:', 'hospital-management-system'); ?></strong></td>
                                            <td id="subtotal-amount" style="text-align: right; font-weight: bold;">0.00</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: right;"><strong><?php _e('Discount Amount:', 'hospital-management-system'); ?></strong></td>
                                            <td><input type="number" name="discount_amount" id="discount_amount" class="small-text" value="<?php echo $is_edit && $bill ? number_format($bill->discount_amount, 2, '.', '') : (isset($_POST['discount_amount']) ? esc_attr($_POST['discount_amount']) : '0.00'); ?>" step="0.01" min="0"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: right;"><strong><?php _e('Tax (%):', 'hospital-management-system'); ?></strong></td>
                                            <td><input type="number" name="tax_percentage" id="tax_percentage" class="small-text" value="<?php echo $is_edit && $bill ? number_format($bill->tax_percentage, 2, '.', '') : (isset($_POST['tax_percentage']) ? esc_attr($_POST['tax_percentage']) : '0.00'); ?>" step="0.01" min="0" max="100"></td>
                                            <td></td>
                                        </tr>
                                         <tr>
                                            <td colspan="3" style="text-align: right;"><strong><?php _e('Tax Amount:', 'hospital-management-system'); ?></strong></td>
                                            <td id="tax-amount" style="text-align: right; font-weight: bold;"><?php echo $is_edit && $bill ? number_format($bill->tax_amount, 2) : '0.00'; ?></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" style="text-align: right;"><strong><?php _e('Total Amount:', 'hospital-management-system'); ?></strong></td>
                                            <td id="total-amount" style="text-align: right; font-weight: bold; font-size: 1.1em;"><?php echo $is_edit && $bill ? number_format($bill->total_amount, 2) : '0.00'; ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="paymentinfo" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Payment Information', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="amount_paid"><?php _e('Amount Paid', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <?php 
                                                // FIX: Use paid_amount from database
                                                $amount_paid_value = '0.00';
                                                if ($is_edit && $bill) {
                                                    $amount_paid_value = number_format($bill->paid_amount, 2, '.', '');
                                                } elseif (isset($_POST['amount_paid'])) {
                                                    $amount_paid_value = esc_attr($_POST['amount_paid']);
                                                }
                                                ?>
                                                <input type="number" name="amount_paid" id="amount_paid" class="regular-text" value="<?php echo $amount_paid_value; ?>" step="0.01" min="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="payment_method"><?php _e('Payment Method', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <select name="payment_method" id="payment_method" class="regular-text">
                                                    <?php
                                                    $payment_methods = array('cash', 'credit_card', 'debit_card', 'insurance', 'online', 'other');
                                                    $selected_method = $is_edit && $bill ? (isset($bill->payment_method) && $bill->payment_method !== null && $bill->payment_method !== '0.000000' && !is_numeric($bill->payment_method) ? $bill->payment_method : '') : (isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '');
                                                    foreach ($payment_methods as $method) {
                                                        echo '<option value="' . esc_attr($method) . '" ' . selected($selected_method, $method, false) . '>' . esc_html(ucwords(str_replace('_', ' ', $method))) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="payment_status"><?php _e('Payment Status', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <select name="payment_status" id="payment_status" class="regular-text">
                                                    <?php
                                                    $payment_statuses = array('unpaid', 'partially_paid', 'paid', 'refunded');
                                                    $selected_payment_status = $is_edit && $bill ? (isset($bill->payment_status) && $bill->payment_status !== null ? $bill->payment_status : 'unpaid') : (isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : 'unpaid');
                                                    foreach ($payment_statuses as $status) {
                                                        echo '<option value="' . esc_attr($status) . '" ' . selected($selected_payment_status, $status, false) . '>' . esc_html(ucwords(str_replace('_', ' ', $status))) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                         
                        <div id="additionalinfo" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Additional Information', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                        <th scope="row">
                                            <label for="notes"><?php _e('Notes / Terms', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <?php
                                            // FIX: Handle notes being '0'
                                            $notes_value = '';
                                            if ($is_edit && $bill && isset($bill->notes) && $bill->notes !== '0' && $bill->notes !== 0 && !empty($bill->notes)) {
                                                $notes_value = esc_textarea($bill->notes);
                                            } elseif (isset($_POST['notes'])) {
                                                $notes_value = esc_textarea($_POST['notes']);
                                            }
                                            ?>
                                            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $notes_value; ?></textarea>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Function to calculate totals
    function calculateTotals() {
        var subtotal = 0;
        $('.bill-item-row').each(function() {
            var row = $(this);
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
            var total = quantity * price;
            row.find('.item-total').text(total.toFixed(2));
            subtotal += total;
        });

        $('#subtotal-amount').text(subtotal.toFixed(2));
        
        var discountAmount = parseFloat($('#discount_amount').val()) || 0;
        var subtotalAfterDiscount = subtotal - discountAmount;
        
        var taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        var taxAmount = subtotalAfterDiscount * (taxPercentage / 100);
        $('#tax-amount').text(taxAmount.toFixed(2));
        
        var totalAmount = subtotalAfterDiscount + taxAmount;
        $('#total-amount').text(totalAmount.toFixed(2));
    }

    // Initialize values when editing
    <?php if ($is_edit && $bill): ?>
    // Set initial values from database
    function initializeEditValues() {
        // Check if values exist before setting them
        var discountAmount = <?php echo floatval($bill->discount_amount); ?>;
        var taxPercentage = <?php echo floatval($bill->tax_percentage); ?>;
        var amountPaid = <?php echo floatval($bill->paid_amount); ?>; // FIX: Use paid_amount
        var paymentMethod = '<?php echo ($bill->payment_method && $bill->payment_method !== '0.000000') ? esc_js($bill->payment_method) : ''; ?>';
        var paymentStatus = '<?php echo esc_js($bill->payment_status); ?>';
        var status = '<?php echo esc_js($bill->status); ?>';
        var notes = <?php echo json_encode($bill->notes && $bill->notes !== '0' ? $bill->notes : ''); ?>;

        console.log('Edit values:', {discountAmount, taxPercentage, amountPaid, paymentMethod, paymentStatus});

        $('#discount_amount').val(discountAmount.toFixed(2));
        $('#tax_percentage').val(taxPercentage.toFixed(2));
        $('#amount_paid').val(amountPaid.toFixed(2));
        $('#payment_method').val(paymentMethod);
        $('#payment_status').val(paymentStatus);
        $('#status').val(status);
        $('#notes').val(notes);
                
        console.log('Initializing edit values:', {
            discountAmount: discountAmount,
            taxPercentage: taxPercentage,
            amountPaid: amountPaid,
            paymentMethod: paymentMethod,
            paymentStatus: paymentStatus,
            status: status,
            notes: notes
        });
        
        // Calculate totals after setting initial values
        setTimeout(function() {
            calculateTotals();
        }, 100);
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initializeEditValues();
    });
    <?php endif; ?>
    
    // Calculate on page load and on change
    $(document).ready(function() {
        // Always calculate totals on page load
        calculateTotals();
    });
    $(document).on('input change', '.bill-item-row input, #tax_percentage, #discount_amount', calculateTotals);

    // Add new item row
    $('#add-item-row').on('click', function(e) {
        e.preventDefault();
        var index = $('.bill-item-row').length;
        var newRow = `
            <tr class="bill-item-row">
                <td><input type="text" name="bill_items[${index}][description]" value="" class="regular-text" placeholder="<?php _e('e.g., Consultation Fee', 'hospital-management-system'); ?>" required><input type="hidden" name="bill_items[${index}][id]" value="0"></td>
                <td><input type="number" name="bill_items[${index}][quantity]" value="1" class="small-text" step="0.01" min="1" required></td>
                <td><input type="number" name="bill_items[${index}][price]" value="0" class="small-text" step="0.01" min="0" required></td>
                <td class="item-total">0.00</td>
                <td><button type="button" class="button button-link-delete remove-item-row"><?php _e('Remove', 'hospital-management-system'); ?></button></td>
            </tr>
        `;
        $('#bill-items-tbody').append(newRow);
    });

    // Remove item row
    $(document).on('click', '.remove-item-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        calculateTotals(); // Recalculate after removing a row
    });

    // Form submission validation
    $('#bill-details-form').on('submit', function(e) {
        if ($('#patient_id').val() === '') {
            alert('<?php _e('Please select a patient.', 'hospital-management-system'); ?>');
            $('#patient_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('.bill-item-row').length === 0) {
            alert('<?php _e('Please add at least one item to the bill.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
    });
});
</script>
