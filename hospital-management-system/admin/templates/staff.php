<div class="wrap">
    <h1><?php esc_html_e('Staff Management', 'hospital-management-system'); ?>
        <a href="<?php echo admin_url('admin.php?page=hms_staff_details'); ?>" class="page-title-action"><?php esc_html_e('Add New Staff', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    // Display any notices or messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    // Display staff statistics
    $staff_obj = new HMS_Staff();
    $total_active = $staff_obj->get_staff_members_count(array('status' => 'active'));
    $total_inactive = $staff_obj->get_staff_members_count(array('status' => 'inactive'));
    $total_on_leave = $staff_obj->get_staff_members_count(array('status' => 'on_leave'));
    $total_doctors = $staff_obj->get_staff_members_count(array('role' => 'doctor'));
    $total_nurses = $staff_obj->get_staff_members_count(array('role' => 'nurse'));
    ?>
    
    <div class="hms-staff-summary" style="background: #fff; padding: 15px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">
        <h3><?php esc_html_e('Staff Overview', 'hospital-management-system'); ?></h3>
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="stat-item">
                <strong><?php echo esc_html($total_active); ?></strong>
                <span><?php esc_html_e('Active Staff', 'hospital-management-system'); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php echo esc_html($total_doctors); ?></strong>
                <span><?php esc_html_e('Doctors', 'hospital-management-system'); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php echo esc_html($total_nurses); ?></strong>
                <span><?php esc_html_e('Nurses', 'hospital-management-system'); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php echo esc_html($total_on_leave); ?></strong>
                <span><?php esc_html_e('On Leave', 'hospital-management-system'); ?></span>
            </div>
            <div class="stat-item">
                <strong><?php echo esc_html($total_inactive); ?></strong>
                <span><?php esc_html_e('Inactive', 'hospital-management-system'); ?></span>
            </div>
        </div>
    </div>
    
    <form method="get">
        <input type="hidden" name="page" value="hms_staff">
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <!-- Bulk actions can be added here -->
            </div>
            <div class="alignleft actions">
                <label for="filter-name" class="screen-reader-text"><?php esc_html_e('Filter by Name', 'hospital-management-system'); ?></label>
                <input type="text" name="name" id="filter-name" value="<?php echo isset($_GET['name']) ? esc_attr($_GET['name']) : ''; ?>" placeholder="<?php esc_html_e('Staff Name', 'hospital-management-system'); ?>">

                <label for="filter-role" class="screen-reader-text"><?php esc_html_e('Filter by Role', 'hospital-management-system'); ?></label>
                <select name="role" id="filter-role">
                    <option value=""><?php esc_html_e('All Roles', 'hospital-management-system'); ?></option>
                    <option value="doctor" <?php echo isset($_GET['role']) && $_GET['role'] == 'doctor' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Doctor', 'hospital-management-system'); ?></option>
                    <option value="nurse" <?php echo isset($_GET['role']) && $_GET['role'] == 'nurse' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Nurse', 'hospital-management-system'); ?></option>
                    <option value="technician" <?php echo isset($_GET['role']) && $_GET['role'] == 'technician' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Technician', 'hospital-management-system'); ?></option>
                    <option value="admin" <?php echo isset($_GET['role']) && $_GET['role'] == 'admin' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Administrator', 'hospital-management-system'); ?></option>
                    <option value="receptionist" <?php echo isset($_GET['role']) && $_GET['role'] == 'receptionist' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Receptionist', 'hospital-management-system'); ?></option>
                </select>
                
                <label for="filter-department" class="screen-reader-text"><?php esc_html_e('Filter by Department', 'hospital-management-system'); ?></label>
                <select name="department" id="filter-department">
                    <option value=""><?php esc_html_e('All Departments', 'hospital-management-system'); ?></option>
                    <option value="cardiology" <?php echo isset($_GET['department']) && $_GET['department'] == 'cardiology' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Cardiology', 'hospital-management-system'); ?></option>
                    <option value="neurology" <?php echo isset($_GET['department']) && $_GET['department'] == 'neurology' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Neurology', 'hospital-management-system'); ?></option>
                    <option value="orthopedics" <?php echo isset($_GET['department']) && $_GET['department'] == 'orthopedics' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Orthopedics', 'hospital-management-system'); ?></option>
                    <option value="pediatrics" <?php echo isset($_GET['department']) && $_GET['department'] == 'pediatrics' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Pediatrics', 'hospital-management-system'); ?></option>
                    <option value="radiology" <?php echo isset($_GET['department']) && $_GET['department'] == 'radiology' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Radiology', 'hospital-management-system'); ?></option>
                    <option value="laboratory" <?php echo isset($_GET['department']) && $_GET['department'] == 'laboratory' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Laboratory', 'hospital-management-system'); ?></option>
                    <option value="pharmacy" <?php echo isset($_GET['department']) && $_GET['department'] == 'pharmacy' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Pharmacy', 'hospital-management-system'); ?></option>
                    <option value="administration" <?php echo isset($_GET['department']) && $_GET['department'] == 'administration' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Administration', 'hospital-management-system'); ?></option>
                </select>

                <label for="filter-status" class="screen-reader-text"><?php esc_html_e('Filter by Status', 'hospital-management-system'); ?></label>
                <select name="status" id="filter-status">
                    <option value=""><?php esc_html_e('All Statuses', 'hospital-management-system'); ?></option>
                    <option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Active', 'hospital-management-system'); ?></option>
                    <option value="inactive" <?php echo isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected="selected"' : ''; ?>><?php esc_html_e('Inactive', 'hospital-management-system'); ?></option>
                    <option value="on_leave" <?php echo isset($_GET['status']) && $_GET['status'] == 'on_leave' ? 'selected="selected"' : ''; ?>><?php esc_html_e('On Leave', 'hospital-management-system'); ?></option>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php esc_html_e('Filter', 'hospital-management-system'); ?>">
            </div>
            <div class="alignright actions">
                <!-- Search functionality can be added here -->
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list staff-table">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" id="staff_id" class="manage-column column-staff_id column-primary"><?php esc_html_e('Staff ID', 'hospital-management-system'); ?></th>
                    <th scope="col" id="name" class="manage-column column-name"><?php esc_html_e('Name', 'hospital-management-system'); ?></th>
                    <th scope="col" id="role" class="manage-column column-role"><?php esc_html_e('Role', 'hospital-management-system'); ?></th>
                    <th scope="col" id="department" class="manage-column column-department"><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
                    <th scope="col" id="contact_number" class="manage-column column-contact_number"><?php esc_html_e('Contact Number', 'hospital-management-system'); ?></th>
                    <th scope="col" id="email" class="manage-column column-email"><?php esc_html_e('Email', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                // Use the HMS_Staff class for better data handling
                $staff_obj = new HMS_Staff();
                
                $filter_args = array();
                
                if (!empty($_GET['name'])) {
                    $filter_args['name'] = sanitize_text_field($_GET['name']);
                }

                if (!empty($_GET['role']) && in_array($_GET['role'], array('doctor', 'nurse', 'technician', 'admin', 'receptionist'))) {
                    $filter_args['role'] = sanitize_text_field($_GET['role']);
                }

                if (!empty($_GET['department'])) {
                    $filter_args['department'] = sanitize_text_field($_GET['department']);
                }

                if (!empty($_GET['status']) && in_array($_GET['status'], array('active', 'inactive', 'on_leave'))) {
                    $filter_args['status'] = sanitize_text_field($_GET['status']);
                }

                // Set pagination parameters
                $filter_args['per_page'] = 50; // Show more records per page
                $filter_args['offset'] = 0;

                $staff_members = $staff_obj->get_staff_members($filter_args);
                $total_staff = $staff_obj->get_staff_members_count($filter_args);

                if ($staff_members) {
                    foreach ($staff_members as $staff) {
                        // Status badge
                        $status_badge_class = ($staff->status === 'active') ? 'success' : (($staff->status === 'on_leave') ? 'warning' : 'secondary');
                        echo '<tr>';
                        echo '<th scope="row" class="check-column">';
                        echo '<input type="checkbox" name="staff[]" value="' . esc_attr($staff->id) . '">';
                        echo '</th>';
                        echo '<td class="column-staff_id column-primary">' . esc_html($staff->staff_id) . '</td>';
                        echo '<td class="column-name">' . esc_html($staff->first_name . ' ' . $staff->last_name) . '</td>';
                        echo '<td class="column-role">' . esc_html(ucwords(str_replace('_', ' ', $staff->role))) . '</td>';
                        echo '<td class="column-department">' . esc_html(ucwords(str_replace('_', ' ', $staff->department))) . '</td>';
                        echo '<td class="column-contact_number">' . esc_html($staff->contact_number) . '</td>';
                        echo '<td class="column-email">' . esc_html($staff->email) . '</td>';
                        echo '<td class="column-status"><span class="badge badge-' . esc_attr($status_badge_class) . '">' . esc_html(ucwords(str_replace('_', ' ', $staff->status))) . '</span></td>';
                        echo '<td class="column-actions">';
                        echo '<a href="' . esc_url(admin_url('admin.php?page=hms_staff_details&id=' . $staff->id)) . '" class="button button-small">' . __('Edit', 'hospital-management-system') . '</a> ';
                        echo '<a href="#" class="button button-small delete-staff" data-id="' . esc_attr($staff->id) . '">' . __('Delete', 'hospital-management-system') . '</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9">' . __('No staff members found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>
                    <th scope="col" class="manage-column column-staff_id"><?php esc_html_e('Staff ID', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-name"><?php esc_html_e('Name', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-role"><?php esc_html_e('Role', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-department"><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-contact_number"><?php esc_html_e('Contact Number', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-email"><?php esc_html_e('Email', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
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
    
    .hms-staff-summary .stat-item {
        text-align: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        min-width: 100px;
    }
    
    .hms-staff-summary .stat-item strong {
        display: block;
        font-size: 24px;
        color: #2271b1;
        margin-bottom: 5px;
    }
    
    .hms-staff-summary .stat-item span {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Handle delete confirmation
    $('.delete-staff').on('click', function(e) {
        e.preventDefault();
        var staffId = $(this).data('id');
        var confirmDelete = confirm(hms_admin.strings.confirm_delete_staff || "<?php esc_html_e('Are you sure you want to delete this staff member? This action cannot be undone.', 'hospital-management-system'); ?>");
        if (confirmDelete) {
            $('<form>', {
                'method': 'POST',
                'action': ''
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'delete_staff',
                'value': '1'
            })).append($('<input>', {
                'type': 'hidden',
                'name': 'staff_id',
                'value': staffId
            })).append($('<input>', {
                'type': 'hidden',
                'name': '_wpnonce',
                'value': hms_admin.nonce
            })).appendTo('body').submit();
        }
    });
});
</script>
