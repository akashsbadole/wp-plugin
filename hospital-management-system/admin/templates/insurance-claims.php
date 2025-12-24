<div class="wrap">
    <h1><?php _e('Insurance Claims', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_claim_details'); ?>" class="page-title-action"><?php _e('Create New Claim', 'hospital-management-system'); ?></a>
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
        <input type="hidden" name="page" value="hms_insurance_claims">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <label for="filter-status" class="screen-reader-text"><?php _e('Filter by Status', 'hospital-management-system'); ?></label>
                <select name="status" id="filter-status">
                    <option value=""><?php _e('All Statuses', 'hospital-management-system'); ?></option>
                    <option value="pending_preauth" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending_preauth' ? 'selected="selected"' : ''; ?>><?php _e('Pending Pre-auth', 'hospital-management-system'); ?></option>
                    <option value="preauth_approved" <?php echo isset($_GET['status']) && $_GET['status'] == 'preauth_approved' ? 'selected="selected"' : ''; ?>><?php _e('Pre-auth Approved', 'hospital-management-system'); ?></option>
                    <option value="submitted_for_settlement" <?php echo isset($_GET['status']) && $_GET['status'] == 'submitted_for_settlement' ? 'selected="selected"' : ''; ?>><?php _e('Submitted for Settlement', 'hospital-management-system'); ?></option>
                    <option value="settled" <?php echo isset($_GET['status']) && $_GET['status'] == 'settled' ? 'selected="selected"' : ''; ?>><?php _e('Settled', 'hospital-management-system'); ?></option>
                    <option value="rejected" <?php echo isset($_GET['status']) && $_GET['status'] == 'rejected' ? 'selected="selected"' : ''; ?>><?php _e('Rejected', 'hospital-management-system'); ?></option>
                </select>

                <label for="filter-provider" class="screen-reader-text"><?php _e('Filter by Provider', 'hospital-management-system'); ?></label>
                <select name="provider_id" id="filter-provider">
                    <option value=""><?php _e('All Providers', 'hospital-management-system'); ?></option>
                    <?php
                    global $wpdb;
                    $providers_table = $wpdb->prefix . 'hms_insurance_providers';
                    $providers = $wpdb->get_results("SELECT id, name FROM $providers_table WHERE status = 'active' ORDER BY name ASC");
                    foreach ($providers as $provider) {
                        $selected = isset($_GET['provider_id']) && $_GET['provider_id'] == $provider->id ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr($provider->id) . '" ' . $selected . '>' . esc_html($provider->name) . '</option>';
                    }
                    ?>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list claims-table">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="claim_id" class="manage-column column-claim_id column-primary"><?php _e('Claim ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="patient" class="manage-column column-patient"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" id="provider" class="manage-column column-provider"><?php _e('Insurance Provider', 'hospital-management-system'); ?></th>
                    <th scope="col" id="policy_number" class="manage-column column-policy_number"><?php _e('Policy Number', 'hospital-management-system'); ?></th>
                    <th scope="col" id="claim_amount" class="manage-column column-claim_amount"><?php _e('Claim Amount', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="initiated_at" class="manage-column column-initiated_at"><?php _e('Date Initiated', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                // Get claims with filters
                $where_clauses = array();
                $prepare_values = array();

                if (!empty($_GET['status'])) {
                    $where_clauses[] = "c.status = %s";
                    $prepare_values[] = sanitize_text_field($_GET['status']);
                }

                if (!empty($_GET['provider_id'])) {
                    $where_clauses[] = "c.insurance_provider_id = %d";
                    $prepare_values[] = intval($_GET['provider_id']);
                }

                $where_sql = '';
                if (!empty($where_clauses)) {
                    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
                }

                $claims_table = $wpdb->prefix . 'hms_claims';
                $patients_table = $wpdb->prefix . 'hms_patients';
                $providers_table = $wpdb->prefix . 'hms_insurance_providers';

                $query = "SELECT c.*, p.first_name, p.last_name, pr.name as provider_name 
                         FROM $claims_table c 
                         LEFT JOIN $patients_table p ON c.patient_id = p.id 
                         LEFT JOIN $providers_table pr ON c.insurance_provider_id = pr.id 
                         $where_sql 
                         ORDER BY c.initiated_at DESC";
                
                if (!empty($prepare_values)) {
                    $query = $wpdb->prepare($query, $prepare_values);
                }

                $claims = $wpdb->get_results($query);

                if ($claims) {
                    foreach ($claims as $claim) {
                        // Status badge
                        $status_classes = array(
                            'pending_preauth' => 'warning',
                            'preauth_approved' => 'info',
                            'submitted_for_settlement' => 'info',
                            'settled' => 'success',
                            'rejected' => 'danger'
                        );
                        $status_badge_class = isset($status_classes[$claim->status]) ? $status_classes[$claim->status] : 'secondary';
                        
                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo '<input type="checkbox" name="claim[]" value="' . esc_attr($claim->id) . '">';
                        echo '</th>';
                        echo '<td class="column-claim_id column-primary">#' . esc_html($claim->id) . '</td>';
                        echo '<td class="column-patient">' . esc_html($claim->first_name . ' ' . $claim->last_name) . '</td>';
                        echo '<td class="column-provider">' . esc_html($claim->provider_name) . '</td>';
                        echo '<td class="column-policy_number">' . esc_html($claim->policy_number) . '</td>';
                        echo '<td class="column-claim_amount">$' . number_format($claim->claim_amount, 2) . '</td>';
                        echo '<td class="column-status"><span class="badge badge-' . esc_attr($status_badge_class) . '">' . esc_html(ucwords(str_replace('_', ' ', $claim->status))) . '</span></td>';
                        echo '<td class="column-initiated_at">' . esc_html(date('M j, Y', strtotime($claim->initiated_at))) . '</td>';
                        echo '<td class="column-actions">';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_claim_details&id=' . $claim->id)) . '" class="button button-small">' . __('View/Edit', 'hospital-management-system') . '</a> ';
                        echo '<a href="#" class="button button-small delete-claim" data-id="' . esc_attr($claim->id) . '">' . __('Delete', 'hospital-management-system') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9">' . __('No insurance claims found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>
                    <th scope="col" class="manage-column column-claim_id"><?php _e('Claim ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-patient"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-provider"><?php _e('Insurance Provider', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-policy_number"><?php _e('Policy Number', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-claim_amount"><?php _e('Claim Amount', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-initiated_at"><?php _e('Date Initiated', 'hospital-management-system'); ?></th>
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

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle delete confirmation
    $('.delete-claim').on('click', function(e) {
        e.preventDefault();
        var claimId = $(this).data('id');
        var confirmDelete = confirm(hms_admin.strings.confirm_delete_claim || "<?php _e('Are you sure you want to delete this insurance claim? This action cannot be undone.', 'hospital-management-system'); ?>");
        if (confirmDelete) {
            $('<form>', {
                'method': 'POST',
                'action': ''
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'delete_claim',
                'value': '1'
            })).append($('<input>', {
                'type': 'hidden',
                'name': 'claim_id',
                'value': claimId
            })).append($('<input>', {
                'type': 'hidden',
                'name': '_wpnonce',
                'value': hms_admin.nonce
            })).appendTo('body').submit();
        }
    });
});
</script>