<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'print') {
            _e('Print Dispense Record', 'hospital-management-system');
        } elseif (isset($_GET['id'])) {
            _e('Edit Dispense Record', 'hospital-management-system');
        } else {
            _e('Dispense Medicine', 'hospital-management-system');
        }
        ?>
        <?php if (!isset($_GET['action']) || $_GET['action'] !== 'print'): ?>
            <a href="<?php echo admin_url('admin.php?page=hms_pharmacy'); ?>" class="page-title-action"><?php _e('&larr; Back to Pharmacy', 'hospital-management-system'); ?></a>
        <?php endif; ?>
    </h1>

    <?php
    $dispense = null;
    $dispense_items = array();
    $prescription = null;
    $prescription_items = array();
    $is_edit = false;
    $is_print = false;

    if (isset($_GET['id'])) {
        $is_edit = true;
        $dispense_id = intval($_GET['id']);
        global $wpdb;
        $dispenses_table = $wpdb->prefix . 'hms_pharmacy_dispenses';
        $dispense_items_table = $wpdb->prefix . 'hms_pharmacy_dispense_items';
        
        $dispense = $wpdb->get_row($wpdb->prepare("SELECT * FROM $dispenses_table WHERE id = %d", $dispense_id));
        $dispense_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $dispense_items_table WHERE dispense_id = %d ORDER BY id ASC", $dispense_id));

        if (!$dispense) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Dispense record not found.', 'hospital-management-system') . '</p></div>';
            return;
        }

        // Fetch related prescription and its items
        $prescription = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}hms_prescriptions WHERE prescription_number = %s", $dispense->prescription_number));
        if ($prescription) {
            $prescription_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}hms_prescription_items WHERE prescription_id = %d ORDER BY id ASC", $prescription->id));
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'print') {
        $is_print = true;
    }

    // Handle form submission feedback
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <?php if ($is_print): ?>
        <div id="dispense-print-area" style="font-family: Arial, sans-serif; width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ccc;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0;"><?php echo esc_html(get_option('hms_hospital_name', 'Hospital Name')); ?></h2>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_address', 'Hospital Address')); ?></p>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_phone', 'Phone')); ?> | <?php echo esc_html(get_option('hms_hospital_email', 'Email')); ?></p>
                <hr style="margin-top: 15px;">
            </div>
            
            <h3 style="text-align: center; margin-bottom: 20px;"><?php _e('MEDICINE DISPENSE RECORD', 'hospital-management-system'); ?></h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <strong><?php _e('Patient:', 'hospital-management-system'); ?></strong>
                    <?php
                    if ($dispense && $dispense->patient_id) {
                        global $wpdb;
                        $patient = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}hms_patients WHERE id = %d", $dispense->patient_id));
                        if ($patient) {
                            echo '<p style="margin: 2px 0;"><strong>' . esc_html($patient->first_name . ' ' . $patient->last_name) . '</strong></p>';
                            echo '<p style="margin: 2px 0;">' . esc_html($patient->address) . '</p>';
                            echo '<p style="margin: 2px 0;">' . esc_html($patient->contact_number) . '</p>';
                            echo '<p style="margin: 2px 0;">' . esc_html($patient->email) . '</p>';
                        }
                    }
                    ?>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 2px 0;"><strong><?php _e('Dispense ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html($dispense->id); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Prescription Number:', 'hospital-management-system'); ?></strong> <?php echo esc_html($dispense->prescription_number); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Dispense Date:', 'hospital-management-system'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($dispense->dispense_date)); ?></p>
                    <p style="margin: 2px 0;"><strong><?php _e('Dispensed By:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_user_by('ID', $dispense->dispensed_by)->display_name); ?></p>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <p><strong><?php _e('Prescribed by:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_user_by('ID', $dispense->doctor_id)->display_name); ?></p>
                <p><strong><?php _e('Diagnosis:', 'hospital-management-system'); ?></strong> <?php echo nl2br(esc_html($prescription->diagnosis)); ?></p>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?php _e('Medicine Name', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('Prescribed Qty', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('Dispensed Qty', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('Batch No', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('Expiry Date', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?php _e('Instructions', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dispense_items): ?>
                        <?php foreach ($dispense_items as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($item->medicine_name); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->prescribed_quantity); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->dispensed_quantity); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->batch_number); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->expiry_date); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($item->instructions); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php _e('No dispensed medicines found.', 'hospital-management-system'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 30px;">
                <p><strong><?php _e('Notes:', 'hospital-management-system'); ?></strong></p>
                <p><?php echo nl2br(esc_html($dispense->notes)); ?></p>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: space-between;">
                <div>
                    <p>_________________________</p>
                    <p><strong><?php _e('Pharmacist\'s Signature', 'hospital-management-system'); ?></strong></p>
                </div>
                <div style="text-align: right;">
                    <p>_________________________</p>
                    <p><strong><?php _e('Patient\'s Signature', 'hospital-management-system'); ?></strong></p>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                window.print();
            });
        </script>
    <?php else: ?>
        <form id="dispense-medicine-form" method="post" action="">
            <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
            <input type="hidden" name="page" value="hms_dispense_medicine">
            <?php if ($is_edit): ?>
                <input type="hidden" name="dispense_id" value="<?php echo esc_attr($dispense->id); ?>">
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
                                            <input type="submit" name="save_dispense" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Dispense Record', 'hospital-management-system') : __('Dispense Medicine', 'hospital-management-system'); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($is_edit): ?>
                        <div id="print-action" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Print', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_dispense_medicine&action=print&id=' . $dispense->id)); ?>" class="button button-large" target="_blank"><?php _e('Print Dispense Record', 'hospital-management-system'); ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <div id="dispensedetails" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Dispense Details', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="prescription_number"><?php _e('Prescription Number', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <?php if ($is_edit): ?>
                                                    <input type="text" name="prescription_number" id="prescription_number" class="regular-text" value="<?php echo esc_attr($dispense->prescription_number); ?>" readonly>
                                                    <input type="hidden" name="prescription_id" value="<?php echo esc_attr($prescription->id); ?>">
                                                    <input type="hidden" name="patient_id" value="<?php echo esc_attr($dispense->patient_id); ?>">
                                                    <input type="hidden" name="doctor_id" value="<?php echo esc_attr($dispense->doctor_id); ?>">
                                                <?php else: ?>
                                                    <input type="text" name="prescription_number" id="prescription_number" class="regular-text" placeholder="<?php _e('e.g., RX-202309-0001', 'hospital-management-system'); ?>" required>
                                                    <button type="button" id="fetch-prescription" class="button"><?php _e('Fetch Details', 'hospital-management-system'); ?></button>
                                                    <div id="prescription-info" style="margin-top: 10px; display: none;">
                                                        <!-- Patient and Doctor info will be loaded here -->
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="dispense_date"><?php _e('Dispense Date', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <input type="date" name="dispense_date" id="dispense_date" class="regular-text" value="<?php echo $is_edit ? esc_attr($dispense->dispense_date) : (isset($_POST['dispense_date']) ? esc_attr($_POST['dispense_date']) : current_time('Y-m-d')); ?>" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="status"><?php _e('Status', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <select name="status" id="status" class="regular-text">
                                                    <?php
                                                    $statuses = array('pending', 'partially_dispensed', 'dispensed', 'cancelled');
                                                    $selected_status = $is_edit ? $dispense->status : (isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending');
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

                        <div id="dispenseitems" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php _e('Medicines to Dispense', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="wp-list-table widefat fixed striped dispense-items-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php _e('Medicine Name', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Prescribed Qty', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Dispensed Qty', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Batch No', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Expiry Date', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php _e('Instructions', 'hospital-management-system'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="dispense-items-tbody">
                                        <?php
                                        $items_to_loop = $dispense_items;
                                        if (isset($_POST['dispense_items']) && is_array($_POST['dispense_items'])) {
                                            $items_to_loop = $_POST['dispense_items']; // Use POST data if form was submitted and failed
                                        }
                                        if ($items_to_loop):
                                            foreach ($items_to_loop as $index => $item):
                                                $item_id = isset($item['id']) ? intval($item['id']) : 0;
                                                $medicine_name = isset($item['medicine_name']) ? sanitize_text_field($item['medicine_name']) : '';
                                                $prescribed_quantity = isset($item['prescribed_quantity']) ? intval($item['prescribed_quantity']) : 0;
                                                $dispensed_quantity = isset($item['dispensed_quantity']) ? intval($item['dispensed_quantity']) : 0;
                                                $batch_number = isset($item['batch_number']) ? sanitize_text_field($item['batch_number']) : '';
                                                $expiry_date = isset($item['expiry_date']) ? sanitize_text_field($item['expiry_date']) : '';
                                                $instructions = isset($item['instructions']) ? sanitize_textarea_field($item['instructions']) : '';
                                        ?>
                                        <tr class="dispense-item-row">
                                            <td>
                                                <input type="text" name="dispense_items[<?php echo $index; ?>][medicine_name]" value="<?php echo esc_attr($medicine_name); ?>" class="regular-text" readonly>
                                                <input type="hidden" name="dispense_items[<?php echo $index; ?>][prescribed_quantity]" value="<?php echo esc_attr($prescribed_quantity); ?>">
                                                <input type="hidden" name="dispense_items[<?php echo $index; ?>][id]" value="<?php echo esc_attr($item_id); ?>">
                                            </td>
                                            <td><?php echo esc_html($prescribed_quantity); ?></td>
                                            <td><input type="number" name="dispense_items[<?php echo $index; ?>][dispensed_quantity]" value="<?php echo esc_attr($dispensed_quantity); ?>" class="small-text" min="0" required></td>
                                            <td><input type="text" name="dispense_items[<?php echo $index; ?>][batch_number]" value="<?php echo esc_attr($batch_number); ?>" class="small-text" required></td>
                                            <td><input type="date" name="dispense_items[<?php echo $index; ?>][expiry_date]" value="<?php echo esc_attr($expiry_date); ?>" class="small-text" required></td>
                                            <td><input type="text" name="dispense_items[<?php echo $index; ?>][instructions]" value="<?php echo esc_attr($instructions); ?>" class="regular-text"></td>
                                        </tr>
                                            <?php endforeach;
                                        else: ?>
                                        <tr>
                                            <td colspan="6"><?php _e('Please enter a prescription number and click "Fetch Details" to load medicines.', 'hospital-management-system'); ?></td>
                                        </tr>
                                        <?php endif; ?>
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
                                                <label for="notes"><?php _e('Notes', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($dispense->notes) : (isset($_POST['notes']) ? esc_textarea($_POST['notes']) : ''); ?></textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="dispensed_by"><?php _e('Dispensed By', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <input type="text" name="dispensed_by_display" id="dispensed_by_display" class="regular-text" value="<?php echo $is_edit ? esc_html(get_user_by('ID', $dispense->dispensed_by)->display_name) : esc_html(wp_get_current_user()->display_name); ?>" readonly>
                                                <input type="hidden" name="dispensed_by" id="dispensed_by" value="<?php echo $is_edit ? esc_attr($dispense->dispensed_by) : esc_attr(get_current_user()->ID); ?>">
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
    // Fetch prescription details when number is entered
    $('#fetch-prescription').on('click', function(e) {
        e.preventDefault();
        var prescriptionNumber = $('#prescription_number').val();
        if (!prescriptionNumber) {
            alert('<?php _e('Please enter a prescription number.', 'hospital-management-system'); ?>');
            return;
        }

        $.ajax({
            url: hms_admin.ajax_url,
            type: 'GET',
            data: {
                action: 'hms_get_prescription_for_dispense',
                prescription_number: prescriptionNumber,
                nonce: hms_admin.nonce
            },
            beforeSend: function() {
                $('#prescription-info').html('<p><?php _e('Loading...', 'hospital-management-system'); ?></p>').show();
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#prescription-info').html(
                        '<p><strong><?php _e('Patient:', 'hospital-management-system'); ?></strong> ' + data.patient_name + '</p>' +
                        '<p><strong><?php _e('Doctor:', 'hospital-management-system'); ?></strong> ' + data.doctor_name + '</p>'
                    ).show();
                    
                    // Populate hidden fields
                    $('input[name="prescription_id"]').val(data.prescription_id);
                    $('input[name="patient_id"]').val(data.patient_id);
                    $('input[name="doctor_id"]').val(data.doctor_id);

                    // Populate dispense items table
                    var tbody = $('#dispense-items-tbody');
                    tbody.empty(); // Clear existing rows
                    if (data.prescription_items && data.prescription_items.length > 0) {
                        $.each(data.prescription_items, function(index, item) {
                            var newRow = `
                                <tr class="dispense-item-row">
                                    <td>
                                        <input type="text" name="dispense_items[${index}][medicine_name]" value="${item.medicine_name}" class="regular-text" readonly>
                                        <input type="hidden" name="dispense_items[${index}][prescribed_quantity]" value="${item.quantity}">
                                        <input type="hidden" name="dispense_items[${index}][id]" value="0">
                                    </td>
                                    <td>${item.quantity}</td>
                                    <td><input type="number" name="dispense_items[${index}][dispensed_quantity]" value="${item.quantity}" class="small-text" min="0" required></td>
                                    <td><input type="text" name="dispense_items[${index}][batch_number]" value="" class="small-text" required></td>
                                    <td><input type="date" name="dispense_items[${index}][expiry_date]" value="" class="small-text" required></td>
                                    <td><input type="text" name="dispense_items[${index}][instructions]" value="${item.instructions}" class="regular-text"></td>
                                </tr>
                            `;
                            tbody.append(newRow);
                        });
                    } else {
                        tbody.html('<tr><td colspan="6"><?php _e('No medicines found in this prescription.', 'hospital-management-system'); ?></td></tr>');
                    }
                } else {
                    $('#prescription-info').html('<p style="color: red;">' + response.data.message + '</p>').show();
                    $('#dispense-items-tbody').html('<tr><td colspan="6"><?php _e('Could not load prescription details.', 'hospital-management-system'); ?></td></tr>');
                }
            },
            error: function() {
                $('#prescription-info').html('<p style="color: red;"><?php _e('An error occurred while fetching prescription details.', 'hospital-management-system'); ?></p>').show();
            }
        });
    });

    // Form submission validation
    $('#dispense-medicine-form').on('submit', function(e) {
        if ($('#prescription_number').val() === '') {
            alert('<?php _e('Please enter a prescription number.', 'hospital-management-system'); ?>');
            $('#prescription_number').focus();
            e.preventDefault();
            return false;
        }
        if ($('.dispense-item-row').length === 0) {
            alert('<?php _e('Please fetch a prescription to dispense medicines.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
        // Add more validation as needed
    });
});
</script>
