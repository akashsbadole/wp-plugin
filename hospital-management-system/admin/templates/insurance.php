<div class="wrap">
    <h1><?php _e('Insurance Management', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_insurance_details'); ?>" class="page-title-action"><?php _e('Add New Provider', 'hospital-management-system'); ?></a>
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
        <input type="hidden" name="page" value="hms_insurance">
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <!-- Bulk actions can be added here -->
            </div>
            <div class="alignleft actions">
                <label for="filter-provider" class="screen-reader-text"><?php _e('Filter by Provider Name', 'hospital-management-system'); ?></label>
                <input type="text" name="provider_name" id="filter-provider" value="<?php echo isset($_GET['provider_name']) ? esc_attr($_GET['provider_name']) : ''; ?>" placeholder="<?php _e('Provider Name', 'hospital-management-system'); ?>">

                <label for="filter-status" class="screen-reader-text"><?php _e('Filter by Status', 'hospital-management-system'); ?></label>
                <select name="status" id="filter-status">
                    <option value=""><?php _e('All Statuses', 'hospital-management-system'); ?></option>
                    <option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected="selected"' : ''; ?>><?php _e('Active', 'hospital-management-system'); ?></option>
                    <option value="inactive" <?php echo isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected="selected"' : ''; ?>><?php _e('Inactive', 'hospital-management-system'); ?></option>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
            </div>
            <div class="alignright actions">
                <!-- Search functionality can be added here -->
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list insurance-table">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="provider_id" class="manage-column column-provider_id column-primary"><?php _e('Provider ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="name" class="manage-column column-name"><?php _e('Provider Name', 'hospital-management-system'); ?></th>
                    <th scope="col" id="contact_person" class="manage-column column-contact_person"><?php _e('Contact Person', 'hospital-management-system'); ?></th>
                    <th scope="col" id="email" class="manage-column column-email"><?php _e('Email', 'hospital-management-system'); ?></th>
                    <th scope="col" id="phone" class="manage-column column-phone"><?php _e('Phone', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'hms_insurance_providers';
                
                $where_clauses = array();
                $prepare_values = array();

                if (!empty($_GET['provider_name'])) {
                    $where_clauses[] = "name LIKE %s";
                    $prepare_values[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['provider_name'])) . '%';
                }

                if (!empty($_GET['status']) && in_array($_GET['status'], array('active', 'inactive'))) {
                    $where_clauses[] = "status = %s";
                    $prepare_values[] = sanitize_text_field($_GET['status']);
                }

                $where_sql = '';
                if (!empty($where_clauses)) {
                    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
                }

                $query = "SELECT * FROM $table_name $where_sql ORDER BY name ASC";
                
                if (!empty($prepare_values)) {
                    $query = $wpdb->prepare($query, $prepare_values);
                }

                $providers = $wpdb->get_results($query);

                if ($providers) {
                    foreach ($providers as $provider) {
                        // Status badge
                        $status_badge_class = ($provider->status === 'active') ? 'success' : 'secondary';
                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo '<input type="checkbox" name="provider[]" value="' . esc_attr($provider->id) . '">';
                        echo '</th>';
                        echo '<td class="column-provider_id column-primary">' . esc_html($provider->provider_id) . '</td>';
                        echo '<td class="column-name">' . esc_html($provider->name) . '</td>';
                        echo '<td class="column-contact_person">' . esc_html($provider->contact_person) . '</td>';
                        echo '<td class="column-email">' . esc_html($provider->email) . '</td>';
                        echo '<td class="column-phone">' . esc_html($provider->phone) . '</td>';
                        echo '<td class="column-status"><span class="badge badge-' . esc_attr($status_badge_class) . '">' . esc_html(ucwords($provider->status)) . '</span></td>';
                        echo '<td class="column-actions">';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_insurance_details&id=' . $provider->id)) . '" class="button button-small">' . __('Edit', 'hospital-management-system') . '</a> ';
                        echo '<a href="#" class="button button-small delete-provider" data-id="' . esc_attr($provider->id) . '">' . __('Delete', 'hospital-management-system') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">' . __('No insurance providers found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>
                    <th scope="col" class="manage-column column-provider_id"><?php _e('Provider ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-name"><?php _e('Provider Name', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-contact_person"><?php _e('Contact Person', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php _e('Email', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-phone"><?php _e('Phone', 'hospital-management-system'); ?></th>
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

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle delete confirmation
    $('.delete-provider').on('click', function(e) {
        e.preventDefault();
        var providerId = $(this).data('id');
        var confirmDelete = confirm(hms_admin.strings.confirm_delete_provider || "<?php _e('Are you sure you want to delete this insurance provider? This action cannot be undone.', 'hospital-management-system'); ?>");
        if (confirmDelete) {
            $('<form>', {
                'method': 'POST',
                'action': ''
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'delete_provider',
                'value': '1'
            })).append($('<input>', {
                'type': 'hidden',
                'name': 'provider_id',
                'value': providerId
            })).append($('<input>', {
                'type': 'hidden',
                'name': '_wpnonce',
                'value': hms_admin.nonce
            })).appendTo('body').submit();
        }
    });
});
</script>
