<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['action']) && $_GET['action'] === 'print') {
            esc_html_e('Print Prescription', 'hospital-management-system');
        } elseif (isset($_GET['id'])) {
            esc_html_e('Edit Prescription', 'hospital-management-system');
        } else {
            esc_html_e('Add New Prescription', 'hospital-management-system');
        }
        ?>
        <?php if (!isset($_GET['action']) || $_GET['action'] !== 'print'): ?>
            <a href="<?php echo admin_url('admin.php?page=hms-prescriptions'); ?>" class="page-title-action"><?php esc_html_e('&larr; Back to Prescriptions', 'hospital-management-system'); ?></a>
        <?php endif; ?>
    </h1>

    <?php
    $prescription = null;
    $prescription_items = array();
    $is_edit = false;
    $is_print = false;

    if (isset($_GET['id'])) {
        $is_edit = true;
        $prescription_id = intval($_GET['id']);
        global $wpdb;
        $prescriptions_table = $wpdb->prefix . 'hms_prescriptions';
        $prescription_items_table = $wpdb->prefix . 'hms_prescription_items';
        
        $prescription = $wpdb->get_row($wpdb->prepare("SELECT * FROM $prescriptions_table WHERE id = %d", $prescription_id));
        $prescription_items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $prescription_items_table WHERE prescription_id = %d ORDER BY id ASC", $prescription_id));

        if (!$prescription) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Prescription not found.', 'hospital-management-system') . '</p></div>';
            return;
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
        <div id="prescription-print-area" style="font-family: Arial, sans-serif; width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ccc;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0;"><?php echo esc_html(get_option('hms_hospital_name', 'Hospital Name')); ?></h2>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_address', 'Hospital Address')); ?></p>
                <p style="margin: 5px 0;"><?php echo esc_html(get_option('hms_hospital_phone', 'Phone')); ?> | <?php echo esc_html(get_option('hms_hospital_email', 'Email')); ?></p>
                <hr style="margin-top: 15px;">
            </div>
            
            <h3 style="text-align: center; margin-bottom: 20px;"><?php esc_html_e('MEDICAL PRESCRIPTION', 'hospital-management-system'); ?></h3>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong>
                    <?php
                    if ($prescription && $prescription->patient_id) {
                        global $wpdb;
                        $patient = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}hms_patients WHERE id = %d", $prescription->patient_id));
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
                    <p style="margin: 2px 0;"><strong><?php esc_html_e('Prescription Number:', 'hospital-management-system'); ?></strong> <?php echo esc_html($prescription->prescription_number); ?></p>
                    <p style="margin: 2px 0;"><strong><?php esc_html_e('Issue Date:', 'hospital-management-system'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($prescription->issue_date)); ?></p>
                    <p style="margin: 2px 0;"><strong><?php esc_html_e('Doctor:', 'hospital-management-system'); ?></strong>
                    <?php
                    // Try to get doctor from hms_staff table first
                    $doctor = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}hms_staff WHERE id = %d AND role = 'doctor'", $prescription->doctor_id));
                    if ($doctor) {
                        echo esc_html($doctor->first_name . ' ' . $doctor->last_name);
                    } else {
                        // Fallback to WordPress user
                        $wp_user = get_user_by('ID', $prescription->doctor_id);
                        echo $wp_user ? esc_html($wp_user->display_name) : esc_html__('N/A', 'hospital-management-system');
                    }
                    ?>
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <p><strong><?php esc_html_e('Diagnosis / Chief Complaint:', 'hospital-management-system'); ?></strong></p>
                <p><?php echo nl2br(esc_html($prescription->diagnosis)); ?></p>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?php esc_html_e('Medicine Name', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php esc_html_e('Dosage', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php esc_html_e('Frequency', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php esc_html_e('Duration', 'hospital-management-system'); ?></th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;"><?php esc_html_e('Instructions', 'hospital-management-system'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($prescription_items): ?>
                        <?php foreach ($prescription_items as $item): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($item->medicine_name); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->dosage); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->frequency); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php echo esc_html($item->duration); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo esc_html($item->instructions); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?php esc_html_e('No medicines found.', 'hospital-management-system'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 30px;">
                <p><strong><?php esc_html_e('Notes / Advice:', 'hospital-management-system'); ?></strong></p>
                <p><?php echo nl2br(esc_html($prescription->notes)); ?></p>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: space-between;">
                <div>
                    <p>_________________________</p>
                    <p><strong><?php esc_html_e('Doctor\'s Signature', 'hospital-management-system'); ?></strong></p>
                </div>
                <div style="text-align: right;">
                    <p>_________________________</p>
                    <p><strong><?php esc_html_e('Patient\'s Signature', 'hospital-management-system'); ?></strong></p>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                window.print();
            });
        </script>
    <?php else: ?>
        <form id="prescription-details-form" method="post" action="">
            <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
            <input type="hidden" name="page" value="hms_prescription_details">
            <?php if ($is_edit): ?>
                <input type="hidden" name="prescription_id" value="<?php echo esc_attr($prescription->id); ?>">
            <?php endif; ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main content area -->
                    </div>

                    <div id="postbox-container-1" class="postbox-container">
                        <div id="submitdiv" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Save', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <div class="submitbox" id="submitpost">
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <input type="submit" name="save_prescription" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Prescription', 'hospital-management-system') : __('Create Prescription', 'hospital-management-system'); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($is_edit): ?>
                        <div id="print-action" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Print', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=hms_prescription_details&action=print&id=' . $prescription->id)); ?>" class="button button-large" target="_blank"><?php esc_html_e('Print Prescription', 'hospital-management-system'); ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <div id="prescriptiondetails" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Prescription Details', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="patient_id"><?php esc_html_e('Patient', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <?php
                                                global $wpdb;
                                                $patients_table = $wpdb->prefix . 'hms_patients';
                                                $patients = $wpdb->get_results("SELECT id, first_name, last_name, patient_id FROM $patients_table ORDER BY first_name, last_name");
                                                ?>
                                                <select name="patient_id" id="patient_id" class="regular-text" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                                                    <option value=""><?php esc_html_e('&mdash; Select Patient &mdash;', 'hospital-management-system'); ?></option>
                                                    <?php
                                                    $selected_patient_id = $is_edit ? $prescription->patient_id : (isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0);
                                                    foreach ($patients as $patient) {
                                                        $display_name = $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')';
                                                        echo '<option value="' . esc_attr($patient->id) . '" ' . selected($selected_patient_id, $patient->id, false) . '>' . esc_html($display_name) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <?php if ($is_edit): ?>
                                                    <input type="hidden" name="patient_id" value="<?php echo esc_attr($prescription->patient_id); ?>">
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="doctor_id"><?php esc_html_e('Doctor', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <?php
                                                // Fetch doctors from hms_staff table
                                                global $wpdb;
                                                $doctors = $wpdb->get_results(
                                                    "SELECT id, first_name, last_name FROM {$wpdb->prefix}hms_staff WHERE role = 'doctor' ORDER BY first_name, last_name"
                                                );
                                                
                                                // If no doctors found in staff table, fallback to WordPress users
                                                if (empty($doctors)) {
                                                    $doctors = get_users(array('role' => 'hms_doctor'));
                                                    if (empty($doctors)) {
                                                        // If no doctors found, try getting administrators
                                                        $doctors = get_users(array('role' => 'administrator'));
                                                    }
                                                    if (empty($doctors)) {
                                                        // If still no doctors found, get all users
                                                        $doctors = get_users(array());
                                                    }
                                                }
                                                ?>
                                                <select name="doctor_id" id="doctor_id" class="regular-text" required <?php echo $is_edit ? 'disabled' : ''; ?>>
                                                    <option value=""><?php esc_html_e('&mdash; Select Doctor &mdash;', 'hospital-management-system'); ?></option>
                                                    <?php
                                                    $selected_doctor_id = $is_edit ? $prescription->doctor_id : (isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : get_current_user_id());
                                                    foreach ($doctors as $doctor) {
                                                        if (isset($doctor->id)) {
                                                            echo '<option value="' . esc_attr($doctor->id) . '" ' . selected($selected_doctor_id, $doctor->id, false) . '>' . esc_html($doctor->first_name . ' ' . $doctor->last_name) . '</option>';
                                                        } else {
                                                            echo '<option value="' . esc_attr($doctor->ID) . '" ' . selected($selected_doctor_id, $doctor->ID, false) . '>' . esc_html($doctor->display_name) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <?php if ($is_edit): ?>
                                                    <input type="hidden" name="doctor_id" value="<?php echo esc_attr($prescription->doctor_id); ?>">
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="appointment_id"><?php esc_html_e('Link to Appointment (Optional)', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <?php
                                                global $wpdb;
                                                $appointments_table = $wpdb->prefix . 'hms_appointments';
                                                // Get appointments with patient and doctor information for better display
                                                $appointments = $wpdb->get_results($wpdb->prepare(
                                                    "SELECT a.id, a.appointment_date, a.appointment_time, a.patient_id, a.doctor_id,
                                                            p.first_name as patient_first_name, p.last_name as patient_last_name, p.patient_id as patient_number,
                                                            s.first_name as doctor_first_name, s.last_name as doctor_last_name
                                                     FROM $appointments_table a
                                                     LEFT JOIN {$wpdb->prefix}hms_patients p ON a.patient_id = p.id
                                                     LEFT JOIN {$wpdb->prefix}hms_staff s ON a.doctor_id = s.id
                                                     WHERE a.patient_id = %d OR %d = 0
                                                     ORDER BY a.appointment_date DESC
                                                     LIMIT 50",
                                                    intval($selected_patient_id), intval($selected_patient_id)
                                                ));
                                                ?>
                                                <select name="appointment_id" id="appointment_id" class="regular-text">
                                                    <option value=""><?php esc_html_e('&mdash; Select Appointment &mdash;', 'hospital-management-system'); ?></option>
                                                    <?php
                                                    $selected_appointment_id = $is_edit ? $prescription->appointment_id : (isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0);
                                                    foreach ($appointments as $appointment) {
                                                        $patient_name = $appointment->patient_first_name . ' ' . $appointment->patient_last_name . ' (' . $appointment->patient_number . ')';
                                                        $doctor_name = $appointment->doctor_first_name ? $appointment->doctor_first_name . ' ' . $appointment->doctor_last_name : 'N/A';
                                                        $display_appointment = date_i18n(get_option('date_format'), strtotime($appointment->appointment_date)) . ' ' . 
                                                                             date_i18n(get_option('time_format'), strtotime($appointment->appointment_time)) . 
                                                                             ' - ' . $patient_name . ' with Dr. ' . $doctor_name;
                                                        echo '<option value="' . esc_attr($appointment->id) . '" ' . 
                                                             'data-patient-id="' . esc_attr($appointment->patient_id) . '" ' .
                                                             'data-doctor-id="' . esc_attr($appointment->doctor_id) . '" ' .
                                                             selected($selected_appointment_id, $appointment->id, false) . '>' . 
                                                             esc_html('#' . $appointment->id . ' - ' . $display_appointment) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <p class="description"><?php esc_html_e('Selecting an appointment will automatically fill patient and doctor fields', 'hospital-management-system'); ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="issue_date"><?php esc_html_e('Issue Date', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                            </th>
                                            <td>
                                                <input type="date" name="issue_date" id="issue_date" class="regular-text" value="<?php echo $is_edit ? esc_attr($prescription->issue_date) : (isset($_POST['issue_date']) ? esc_attr($_POST['issue_date']) : current_time('Y-m-d')); ?>" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <select name="status" id="status" class="regular-text">
                                                    <?php
                                                    $statuses = array('active', 'completed', 'cancelled');
                                                    $selected_status = $is_edit ? $prescription->status : (isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active');
                                                    foreach ($statuses as $status_key => $status_value) {
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

                        <div id="diagnosis" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Diagnosis / Chief Complaint', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="diagnosis"><?php esc_html_e('Details', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <textarea name="diagnosis" id="diagnosis" rows="4" class="large-text"><?php echo $is_edit ? esc_textarea($prescription->diagnosis) : (isset($_POST['diagnosis']) ? esc_textarea($_POST['diagnosis']) : ''); ?></textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="prescriptionitems" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Medicines', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="wp-list-table widefat fixed striped prescription-items-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php esc_html_e('Medicine Name', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php esc_html_e('Dosage', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php esc_html_e('Frequency', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php esc_html_e('Duration', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php esc_html_e('Instructions', 'hospital-management-system'); ?></th>
                                            <th scope="col"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="prescription-items-tbody">
                                        <?php
                                        $items_to_loop = $prescription_items;
                                        if (isset($_POST['prescription_items']) && is_array($_POST['prescription_items'])) {
                                            $items_to_loop = $_POST['prescription_items']; // Use POST data if form was submitted and failed
                                        }
                                        if ($items_to_loop):
                                            foreach ($items_to_loop as $index => $item):
                                                $item_id = isset($item['id']) ? intval($item['id']) : 0;
                                                $medicine_name = isset($item['medicine_name']) ? sanitize_text_field($item['medicine_name']) : '';
                                                $dosage = isset($item['dosage']) ? sanitize_text_field($item['dosage']) : '';
                                                $frequency = isset($item['frequency']) ? sanitize_text_field($item['frequency']) : '';
                                                $duration = isset($item['duration']) ? sanitize_text_field($item['duration']) : '';
                                                $instructions = isset($item['instructions']) ? sanitize_textarea_field($item['instructions']) : '';
                                        ?>
                                        <tr class="prescription-item-row">
                                            <td>
                                                <input type="text" name="prescription_items[<?php echo $index; ?>][medicine_name]" value="<?php echo esc_attr($medicine_name); ?>" class="regular-text" placeholder="<?php esc_html_e('e.g., Paracetamol', 'hospital-management-system'); ?>" required>
                                                <input type="hidden" name="prescription_items[<?php echo $index; ?>][id]" value="<?php echo esc_attr($item_id); ?>">
                                            </td>
                                            <td><input type="text" name="prescription_items[<?php echo $index; ?>][dosage]" value="<?php echo esc_attr($dosage); ?>" class="small-text" placeholder="<?php esc_html_e('e.g., 500mg', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[<?php echo $index; ?>][frequency]" value="<?php echo esc_attr($frequency); ?>" class="small-text" placeholder="<?php esc_html_e('e.g., Twice daily', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[<?php echo $index; ?>][duration]" value="<?php echo esc_attr($duration); ?>" class="small-text" placeholder="<?php esc_html_e('e.g., 5 days', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[<?php echo $index; ?>][instructions]" value="<?php echo esc_attr($instructions); ?>" class="regular-text" placeholder="<?php esc_html_e('e.g., After meals', 'hospital-management-system'); ?>"></td>
                                            <td><button type="button" class="button button-link-delete remove-item-row"><?php esc_html_e('Remove', 'hospital-management-system'); ?></button></td>
                                        </tr>
                                            <?php endforeach;
                                        else: ?>
                                        <tr class="prescription-item-row">
                                            <td><input type="text" name="prescription_items[0][medicine_name]" value="" class="regular-text" placeholder="<?php esc_html_e('e.g., Paracetamol', 'hospital-management-system'); ?>" required><input type="hidden" name="prescription_items[0][id]" value="0"></td>
                                            <td><input type="text" name="prescription_items[0][dosage]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., 500mg', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[0][frequency]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., Twice daily', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[0][duration]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., 5 days', 'hospital-management-system'); ?>" required></td>
                                            <td><input type="text" name="prescription_items[0][instructions]" value="" class="regular-text" placeholder="<?php esc_html_e('e.g., After meals', 'hospital-management-system'); ?>"></td>
                                            <td><button type="button" class="button button-link-delete remove-item-row"><?php esc_html_e('Remove', 'hospital-management-system'); ?></button></td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <button type="button" id="add-item-row" class="button"><?php esc_html_e('+ Add Medicine', 'hospital-management-system'); ?></button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div id="additionalinfo" class="postbox">
                            <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Additional Information', 'hospital-management-system'); ?></span></h2>
                            <div class="inside">
                                <table class="form-table">
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <label for="notes"><?php esc_html_e('Notes / Advice', 'hospital-management-system'); ?></label>
                                            </th>
                                            <td>
                                                <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($prescription->notes) : (isset($_POST['notes']) ? esc_textarea($_POST['notes']) : ''); ?></textarea>
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
    // Add new item row
    $('#add-item-row').on('click', function(e) {
        e.preventDefault();
        var index = $('.prescription-item-row').length;
        var newRow = `
            <tr class="prescription-item-row">
                <td><input type="text" name="prescription_items[${index}][medicine_name]" value="" class="regular-text" placeholder="<?php esc_html_e('e.g., Paracetamol', 'hospital-management-system'); ?>" required><input type="hidden" name="prescription_items[${index}][id]" value="0"></td>
                <td><input type="text" name="prescription_items[${index}][dosage]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., 500mg', 'hospital-management-system'); ?>" required></td>
                <td><input type="text" name="prescription_items[${index}][frequency]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., Twice daily', 'hospital-management-system'); ?>" required></td>
                <td><input type="text" name="prescription_items[${index}][duration]" value="" class="small-text" placeholder="<?php esc_html_e('e.g., 5 days', 'hospital-management-system'); ?>" required></td>
                <td><input type="text" name="prescription_items[${index}][instructions]" value="" class="regular-text" placeholder="<?php esc_html_e('e.g., After meals', 'hospital-management-system'); ?>"></td>
                <td><button type="button" class="button button-link-delete remove-item-row"><?php esc_html_e('Remove', 'hospital-management-system'); ?></button></td>
            </tr>
        `;
        $('#prescription-items-tbody').append(newRow);
    });

    // Remove item row
    $(document).on('click', '.remove-item-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    });

    // Handle appointment selection for auto-filling patient and doctor
    $('#appointment_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var patientId = selectedOption.data('patient-id');
        var doctorId = selectedOption.data('doctor-id');
        
        if (patientId && doctorId) {
            $('#patient_id').val(patientId);
            $('#doctor_id').val(doctorId);
        }
    });

    // Form submission validation
    $('#prescription-details-form').on('submit', function(e) {
        // Debug: Log the diagnosis field value
        console.log('Diagnosis field value:', "'" + $('#diagnosis').val() + "'");
        console.log('Trimmed diagnosis value:', "'" + $('#diagnosis').val().trim() + "'");
        console.log('Diagnosis length:', $('#diagnosis').val().length);
        console.log('Trimmed diagnosis length:', $('#diagnosis').val().trim().length);
        
        if ($('#patient_id').val() === '') {
            alert('<?php esc_html_e('Please select a patient.', 'hospital-management-system'); ?>');
            $('#patient_id').focus();
            e.preventDefault();
            return false;
        }
        if ($('#doctor_id').val() === '') {
            alert('<?php esc_html_e('Please select a doctor.', 'hospital-management-system'); ?>');
            $('#doctor_id').focus();
            e.preventDefault();
            return false;
        }
        // Diagnosis field is now optional - no validation needed
        if ($('.prescription-item-row').length === 0) {
            alert('<?php esc_html_e('Please add at least one medicine to the prescription.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
        
        // Check if at least one medicine has a name
        var hasMedicine = false;
        $('.prescription-item-row').each(function() {
            if ($(this).find('input[name*="[medicine_name]"]').val().trim() !== '') {
                hasMedicine = true;
                return false; // Break the loop
            }
        });
        
        if (!hasMedicine) {
            alert('<?php esc_html_e('Please add at least one medicine to the prescription.', 'hospital-management-system'); ?>');
            e.preventDefault();
            return false;
        }
        
        // If we get here, validation passed
        console.log('Form validation passed, submitting...');
    });
    
    // Add a button click handler to check the diagnosis field value
    $('#publish').on('click', function(e) {
        var diagnosisValue = $('#diagnosis').val();
        console.log('Button clicked - Diagnosis value:', "'" + diagnosisValue + "'");
        console.log('Button clicked - Trimmed diagnosis:', "'" + diagnosisValue.trim() + "'");
    });
});
</script>
