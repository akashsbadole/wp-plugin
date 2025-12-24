<div class="wrap">
    <h1><?php _e('Pharmacy Management', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_dispense_medicine'); ?>" class="page-title-action"><?php _e('Dispense Medicine', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    // Display any notices or messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    <form method="get">
        <input type="hidden" name="page" value="hms_pharmacy">
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <!-- Bulk actions can be added here -->
            </div>
            <div class="alignleft actions">
                <label for="filter-prescription" class="screen-reader-text"><?php _e('Filter by Prescription ID', 'hospital-management-system'); ?></label>
                <input type="text" name="prescription_number" id="filter-prescription" value="<?php echo isset($_GET['prescription_number']) ? esc_attr($_GET['prescription_number']) : ''; ?>" placeholder="<?php _e('Prescription Number', 'hospital-management-system'); ?>">

                <label for="filter-patient" class="screen-reader-text"><?php _e('Filter by Patient', 'hospital-management-system'); ?></label>
                <select name="patient_id" id="filter-patient">
                    <option value=""><?php _e('All Patients', 'hospital-management-system'); ?></option>
                    <?php
                    global $wpdb;
                    $patients_table = $wpdb->prefix . 'hms_patients';
                    $patients = $wpdb->get_results("SELECT id, first_name, last_name, patient_id FROM $patients_table ORDER BY first_name, last_name");
                    $selected_patient = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
                    foreach ($patients as $patient) {
                        $display_name = $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')';
                        echo '<option value="' . esc_attr($patient->id) . '" ' . selected($selected_patient, $patient->id, false) . '>' . esc_html($display_name) . '</option>';
                    }
                    ?>
                </select>

                <label for="filter-date-from" class="screen-reader-text"><?php _e('Filter by Date From', 'hospital-management-system'); ?></label>
                <input type="date" name="date_from" id="filter-date-from" value="<?php echo isset($_GET['date_from']) ? esc_attr($_GET['date_from']) : ''; ?>" placeholder="<?php _e('Date From', 'hospital-management-system'); ?>">
                
                <label for="filter-date-to" class="screen-reader-text"><?php _e('Filter by Date To', 'hospital-management-system'); ?></label>
                <input type="date" name="date_to" id="filter-date-to" value="<?php echo isset($_GET['date_to']) ? esc_attr($_GET['date_to']) : ''; ?>" placeholder="<?php _e('Date To', 'hospital-management-system'); ?>">

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
            </div>
            <div class="alignright actions">
                <!-- Search functionality can be added here -->
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list pharmacy-table">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="dispense_id" class="manage-column column-dispense_id column-primary"><?php _e('Dispense ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="prescription_number" class="manage-column column-prescription_number"><?php _e('Prescription Number', 'hospital-management-system'); ?></th>
                    <th scope="col" id="patient_name" class="manage-column column-patient_name"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" id="doctor_name" class="manage-column column-doctor_name"><?php _e('Doctor', 'hospital-management-system'); ?></th>
                    <th scope="col" id="dispense_date" class="manage-column column-dispense_date"><?php _e('Dispense Date', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'hms_pharmacy_dispenses';
                
                $where_clauses = array();
                $prepare_values = array();

                if (!empty($_GET['prescription_number'])) {
                    $where_clauses[] = "prescription_number LIKE %s";
                    $prepare_values[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['prescription_number'])) . '%';
                }

                if (!empty($_GET['patient_id'])) {
                    $where_clauses[] = "patient_id = %d";
                    $prepare_values[] = intval($_GET['patient_id']);
                }

                if (!empty($_GET['date_from'])) {
                    $where_clauses[] = "dispense_date >= %s";
                    $prepare_values[] = sanitize_text_field($_GET['date_from']);
                }

                if (!empty($_GET['date_to'])) {
                    $where_clauses[] = "dispense_date <= %s";
                    $prepare_values[] = sanitize_text_field($_GET['date_to']);
                }

                $where_sql = '';
                if (!empty($where_clauses)) {
                    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
                }

                $query = "SELECT * FROM $table_name $where_sql ORDER BY dispense_date DESC";
                
                if (!empty($prepare_values)) {
                    $query = $wpdb->prepare($query, $prepare_values);
                }

                $dispenses = $wpdb->get_results($query);

                if ($dispenses) {
                    foreach ($dispenses as $dispense) {
                        // Get patient name
                        global $wpdb;
                        $patient = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name, patient_id FROM {$wpdb->prefix}hms_patients WHERE id = %d", $dispense->patient_id));
                        $patient_name = $patient ? $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')' : __('N/A', 'hospital-management-system');
                        
                        // Get doctor name
                        $doctor = get_user_by('ID', $dispense->doctor_id);
                        $doctor_name = $doctor ? $doctor->display_name : __('N/A', 'hospital-management-system');
                        
                        // Format dates
                        $dispense_date = date_i18n(get_option('date_format'), strtotime($dispense->dispense_date));
                        
                        // Status badge
                        $status_badge_class = '';
                        switch ($dispense->status) {
                            case 'dispensed':
                                $status_badge_class = 'success';
                                break;
                            case 'partially_dispensed':
                                $status_badge_class = 'warning';
                                break;
                            case 'pending':
                                $status_badge_class = 'info';
                                break;
                            case 'cancelled':
                                $status_badge_class = 'danger';
                                break;
                            default:
                                $status_badge_class = 'secondary';
                        }
                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo '<input type="checkbox" name="dispense[]" value="' . esc_attr($dispense->id) . '">';
                        echo '</th>';
                        echo '<td class="column-dispense_id column-primary">' . esc_html($dispense->id) . '</td>';
                        echo '<td class="column-prescription_number">' . esc_html($dispense->prescription_number) . '</td>';
                        echo '<td class="column-patient_name">' . esc_html($patient_name) . '</td>';
                        echo '<td class="column-doctor_name">' . esc_html($doctor_name) . '</td>';
                        echo '<td class="column-dispense_date">' . esc_html($dispense_date) . '</td>';
                        echo '<td class="column-status"><span class="badge badge-' . esc_attr($status_badge_class) . '">' . esc_html(ucwords(str_replace('_', ' ', $dispense->status))) . '</span></td>';
                        echo '<td class="column-actions">';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_dispense_medicine&id=' . $dispense->id)) . '" class="button button-small">' . __('View/Edit', 'hospital-management-system') . '</a> ';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_dispense_medicine&action=print&id=' . $dispense->id)) . '" class="button button-small" target="_blank">' . __('Print', 'hospital-management-system') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">' . __('No dispense records found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>
                    <th scope="col" class="manage-column column-dispense_id"><?php _e('Dispense ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-prescription_number"><?php _e('Prescription Number', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-patient_name"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-doctor_name"><?php _e('Doctor', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-dispense_date"><?php _e('Dispense Date', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<style type="text/css">
    .badge {
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        color: #fff;
    }
    .badge-success { background-color: #28a745; }
    .badge-info { background-color: #17a2b8; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-danger { background-color: #dc3545; }
    .badge-secondary { background-color: #6c757d; }
</style>
