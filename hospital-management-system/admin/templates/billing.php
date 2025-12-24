<div class="wrap">
    <h1><?php _e('Billing', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_bill_details'); ?>" class="page-title-action"><?php _e('Add New Bill', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    // Handle bulk actions and filtering if needed in the future
    ?>

    <form method="get">
        <input type="hidden" name="page" value="hms_billing">
        <?php
        // Display any notices or messages
        if (isset($_GET['message'])) {
            $message = sanitize_text_field($_GET['message']);
            $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
            echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        ?>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <!-- Bulk actions can be added here -->
            </div>
            <div class="alignleft actions">
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

                <label for="filter-status" class="screen-reader-text"><?php _e('Filter by Status', 'hospital-management-system'); ?></label>
                <select name="status" id="filter-status">
                    <option value=""><?php _e('All Statuses', 'hospital-management-system'); ?></option>
                    <?php
                    $statuses = array('unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled');
                    $selected_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                    foreach ($statuses as $status) {
                        echo '<option value="' . esc_attr($status) . '" ' . selected($selected_status, $status, false) . '>' . esc_html(ucwords(str_replace('_', ' ', $status))) . '</option>';
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
                <label for="search-bills" class="screen-reader-text"><?php _e('Search Bills', 'hospital-management-system'); ?></label>
                <input type="search" name="search" id="search-bills" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>" placeholder="<?php _e('Search bills...', 'hospital-management-system'); ?>">
                <input type="submit" name="search_action" class="button" value="<?php _e('Search', 'hospital-management-system'); ?>">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list billing-table">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="bill_id" class="manage-column column-bill_id column-primary"><?php _e('Bill ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="patient_name" class="manage-column column-patient_name"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" id="appointment_id" class="manage-column column-appointment_id"><?php _e('Appointment ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="issue_date" class="manage-column column-issue_date"><?php _e('Issue Date', 'hospital-management-system'); ?></th>
                    <th scope="col" id="due_date" class="manage-column column-due_date"><?php _e('Due Date', 'hospital-management-system'); ?></th>
                    <th scope="col" id="total_amount" class="manage-column column-total_amount"><?php _e('Total Amount', 'hospital-management-system'); ?></th>
                    <th scope="col" id="amount_paid" class="manage-column column-amount_paid"><?php _e('Amount Paid', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'hms_bills';
                
                $where_clauses = array();
                $prepare_values = array();

                if (!empty($_GET['patient_id'])) {
                    $where_clauses[] = "patient_id = %d";
                    $prepare_values[] = intval($_GET['patient_id']);
                }

                if (!empty($_GET['status'])) {
                    $where_clauses[] = "status = %s";
                    $prepare_values[] = sanitize_text_field($_GET['status']);
                }

                if (!empty($_GET['date_from'])) {
                    $where_clauses[] = "issue_date >= %s";
                    $prepare_values[] = sanitize_text_field($_GET['date_from']) . ' 00:00:00';
                }

                if (!empty($_GET['date_to'])) {
                    $where_clauses[] = "issue_date <= %s";
                    $prepare_values[] = sanitize_text_field($_GET['date_to']) . ' 23:59:59';
                }

                $where_sql = '';
                if (!empty($where_clauses)) {
                    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
                }

                $query = "SELECT * FROM $table_name $where_sql ORDER BY issue_date DESC";
                
                if (!empty($prepare_values)) {
                    $query = $wpdb->prepare($query, $prepare_values);
                }

                // Use the billing class for better data retrieval
                global $hms_billing;
                if ($hms_billing) {
                    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                    $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
                    $patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : '';
                    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
                    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
                    
                    $bills = $hms_billing->get_bills($search, $status, $patient_id, $date_from, $date_to, 0, 50);
                } else {
                    $bills = $wpdb->get_results($query);
                }

                if ($bills) {
                    foreach ($bills as $bill) {
                        // Patient name is already included in the query from get_bills method
                        $patient_name = '';
                        if (isset($bill->first_name) && isset($bill->last_name)) {
                            $patient_name = $bill->first_name . ' ' . $bill->last_name;
                            if (isset($bill->patient_number)) {
                                $patient_name .= ' (' . $bill->patient_number . ')';
                            }
                        } else {
                            // Fallback for old query method
                            $patient = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name, patient_id FROM {$wpdb->prefix}hms_patients WHERE id = %d", $bill->patient_id));
                            $patient_name = $patient ? $patient->first_name . ' ' . $patient->last_name . ' (' . $patient->patient_id . ')' : __('N/A', 'hospital-management-system');
                        }
                        
                        // Format dates
                        $issue_date = date_i18n(get_option('date_format'), strtotime($bill->issue_date));
                        $due_date = !empty($bill->due_date) ? date_i18n(get_option('date_format'), strtotime($bill->due_date)) : __('N/A', 'hospital-management-system');
                        
                        // Status badge - use payment_status
                        $payment_status = $bill->payment_status ?? 'unpaid';
                        $status_badge_class = '';
                        switch ($payment_status) {
                            case 'unpaid':
                                $status_badge_class = 'warning';
                                break;
                            case 'partially_paid':
                                $status_badge_class = 'info';
                                break;
                            case 'paid':
                                $status_badge_class = 'success';
                                break;
                            case 'overdue':
                                $status_badge_class = 'danger';
                                break;
                            case 'cancelled':
                                $status_badge_class = 'secondary';
                                break;
                            default:
                                $status_badge_class = 'secondary';
                        }
                        
                        // Calculate balance
                        $total_amount = floatval($bill->total_amount);
                        $paid_amount = floatval($bill->paid_amount);
                        $balance = $total_amount - $paid_amount;
                        
                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo '<input type="checkbox" name="bill[]" value="' . esc_attr($bill->id) . '">';
                        echo '</th>';
                        echo '<td class="column-bill_id column-primary">' . esc_html($bill->bill_number ?? $bill->bill_id ?? '#' . $bill->id) . '</td>';
                        echo '<td class="column-patient_name">' . esc_html($patient_name) . '</td>';
                        echo '<td class="column-appointment_id">' . esc_html($bill->appointment_id ? '#' . $bill->appointment_id : __('N/A', 'hospital-management-system')) . '</td>';
                        echo '<td class="column-issue_date">' . esc_html($issue_date) . '</td>';
                        echo '<td class="column-due_date">' . esc_html($due_date) . '</td>';
                        echo '<td class="column-total_amount">' . esc_html(number_format($total_amount, 2)) . '</td>';
                        echo '<td class="column-amount_paid">' . esc_html(number_format($paid_amount, 2)) . '</td>';
                        echo '<td class="column-status"><span class="badge badge-' . esc_attr($status_badge_class) . '">' . esc_html(ucwords(str_replace('_', ' ', $payment_status))) . '</span></td>';
                        echo '<td class="column-actions">';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_bill_details&id=' . $bill->id)) . '" class="button button-small">' . __('View/Edit', 'hospital-management-system') . '</a> ';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_view_invoice&bill_id=' . $bill->id)) . '" class="button button-small" target="_blank">' . __('View Invoice', 'hospital-management-system') . '</a> ';
                        if ($balance > 0) {
                            echo '<a href="' . esc_url(admin_url('admin.php?page=hms_payment_info&bill_id=' . $bill->id)) . '" class="button button-small button-primary">' . __('Pay', 'hospital-management-system') . '</a> ';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="10">' . __('No bills found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>
                    <th scope="col" class="manage-column column-bill_id"><?php _e('Bill ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-patient_name"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-appointment_id"><?php _e('Appointment ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-issue_date"><?php _e('Issue Date', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-due_date"><?php _e('Due Date', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-total_amount"><?php _e('Total Amount', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-amount_paid"><?php _e('Amount Paid', 'hospital-management-system'); ?></th>
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
