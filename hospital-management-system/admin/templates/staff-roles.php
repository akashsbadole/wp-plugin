<div class="wrap">
    <h1><?php esc_html_e('Staff Roles & Permissions', 'hospital-management-system'); ?></h1>

    <?php
    // Display any notices or messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    $staff_obj = new HMS_Staff();
    ?>

    <div class="hms-roles-management">
        <!-- HMS Roles Setup Section -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('HMS WordPress Roles Setup', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <p><?php esc_html_e('Create or update HMS-specific WordPress roles with appropriate capabilities.', 'hospital-management-system'); ?></p>
                
                <form method="post" action="">
                    <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                    <input type="hidden" name="manage_roles" value="1">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Available HMS Roles', 'hospital-management-system'); ?></th>
                            <td>
                                <ul style="list-style: disc; margin-left: 20px;">
                                    <li><strong><?php esc_html_e('HMS Doctor', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Full clinical access, can manage patients, prescriptions, and medical records', 'hospital-management-system'); ?></li>
                                    <li><strong><?php esc_html_e('HMS Nurse', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Patient care access, can view and edit medical records, manage queue', 'hospital-management-system'); ?></li>
                                    <li><strong><?php esc_html_e('HMS Technician', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Lab and radiology access, can manage test orders and results', 'hospital-management-system'); ?></li>
                                    <li><strong><?php esc_html_e('HMS Administrator', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Full system access including staff, billing, and settings management', 'hospital-management-system'); ?></li>
                                    <li><strong><?php esc_html_e('HMS Receptionist', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Front desk access, can manage appointments, patients, and queue', 'hospital-management-system'); ?></li>
                                    <li><strong><?php esc_html_e('HMS Staff', 'hospital-management-system'); ?></strong> - <?php esc_html_e('Basic access for general staff members', 'hospital-management-system'); ?></li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="create_hms_roles" class="button button-primary" value="<?php esc_html_e('Create/Update HMS Roles', 'hospital-management-system'); ?>">
                    </p>
                </form>
            </div>
        </div>

        <!-- Current WordPress Roles -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('Current WordPress Roles', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Role Name', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Display Name', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Capabilities', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('User Count', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        global $wp_roles;
                        $roles = $wp_roles->get_names();
                        
                        foreach ($roles as $role_key => $role_name) {
                            $role = get_role($role_key);
                            $user_count = count_users();
                            $role_count = isset($user_count['avail_roles'][$role_key]) ? $user_count['avail_roles'][$role_key] : 0;
                            
                            echo '<tr>';
                            echo '<td><code>' . esc_html($role_key) . '</code></td>';
                            echo '<td>' . esc_html($role_name) . '</td>';
                            echo '<td>';
                            
                            $hms_caps = array_filter($role->capabilities, function($cap, $key) {
                                return strpos($key, 'hms') !== false || strpos($key, 'manage_') !== false;
                            }, ARRAY_FILTER_USE_BOTH);
                            
                            if (!empty($hms_caps)) {
                                echo '<span class="hms-capabilities">' . implode(', ', array_keys($hms_caps)) . '</span>';
                            } else {
                                echo '<span class="no-hms-caps">' . esc_html__('No HMS capabilities', 'hospital-management-system') . '</span>';
                            }
                            
                            echo '</td>';
                            echo '<td>' . esc_html($role_count) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Staff Role Assignment -->
        <div class="postbox" style="margin-top: 20px;">
            <h2 class="hndle"><span><?php esc_html_e('Staff Role Assignment', 'hospital-management-system'); ?></span></h2>
            <div class="inside">
                <p><?php esc_html_e('Assign WordPress roles to staff members and manage their permissions.', 'hospital-management-system'); ?></p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Staff Member', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Current HMS Role', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('WordPress User', 'hospital-management-system'); ?></th>
                            <th><?php esc_html_e('Actions', 'hospital-management-system'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $staff_members = $staff_obj->get_staff_members(array('per_page' => 100));
                        
                        if ($staff_members) {
                            foreach ($staff_members as $staff) {
                                $wp_user = null;
                                if (!empty($staff->email)) {
                                    $wp_user = get_user_by('email', $staff->email);
                                }
                                
                                echo '<tr>';
                                echo '<td><strong>' . esc_html($staff->first_name . ' ' . $staff->last_name) . '</strong><br>';
                                echo '<small>' . esc_html($staff->staff_id) . '</small></td>';
                                echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $staff->role))) . '</td>';
                                echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $staff->department))) . '</td>';
                                echo '<td>';
                                
                                if ($wp_user) {
                                    echo '<span class="dashicons dashicons-yes-alt" style="color: green;"></span> ';
                                    echo esc_html($wp_user->user_login) . '<br>';
                                    echo '<small>Role: ' . esc_html(implode(', ', $wp_user->roles)) . '</small>';
                                } else {
                                    echo '<span class="dashicons dashicons-no-alt" style="color: red;"></span> ';
                                    echo esc_html__('No WordPress user', 'hospital-management-system');
                                }
                                
                                echo '</td>';
                                echo '<td>';
                                
                                if ($wp_user) {
                                    echo '<button type="button" class="button button-small update-role-btn" data-staff-id="' . esc_attr($staff->id) . '" data-current-role="' . esc_attr($staff->role) . '" data-current-department="' . esc_attr($staff->department) . '">';
                                    echo esc_html__('Update Role', 'hospital-management-system');
                                    echo '</button>';
                                } else {
                                    echo '<button type="button" class="button button-small create-user-btn" data-staff-id="' . esc_attr($staff->id) . '">';
                                    echo esc_html__('Create WP User', 'hospital-management-system');
                                    echo '</button>';
                                }
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">' . esc_html__('No staff members found.', 'hospital-management-system') . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Role Update Modal -->
    <div id="role-update-modal" style="display: none;">
        <form method="post" action="">
            <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
            <input type="hidden" name="manage_roles" value="1">
            <input type="hidden" name="staff_id" id="modal-staff-id">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="new_role"><?php esc_html_e('New HMS Role', 'hospital-management-system'); ?></label>
                    </th>
                    <td>
                        <select name="new_role" id="new_role" class="regular-text" required>
                            <option value="doctor"><?php esc_html_e('Doctor', 'hospital-management-system'); ?></option>
                            <option value="nurse"><?php esc_html_e('Nurse', 'hospital-management-system'); ?></option>
                            <option value="technician"><?php esc_html_e('Technician', 'hospital-management-system'); ?></option>
                            <option value="admin"><?php esc_html_e('Administrator', 'hospital-management-system'); ?></option>
                            <option value="receptionist"><?php esc_html_e('Receptionist', 'hospital-management-system'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new_department"><?php esc_html_e('Department', 'hospital-management-system'); ?></label>
                    </th>
                    <td>
                        <select name="new_department" id="new_department" class="regular-text">
                            <option value="cardiology"><?php esc_html_e('Cardiology', 'hospital-management-system'); ?></option>
                            <option value="neurology"><?php esc_html_e('Neurology', 'hospital-management-system'); ?></option>
                            <option value="orthopedics"><?php esc_html_e('Orthopedics', 'hospital-management-system'); ?></option>
                            <option value="pediatrics"><?php esc_html_e('Pediatrics', 'hospital-management-system'); ?></option>
                            <option value="radiology"><?php esc_html_e('Radiology', 'hospital-management-system'); ?></option>
                            <option value="laboratory"><?php esc_html_e('Laboratory', 'hospital-management-system'); ?></option>
                            <option value="pharmacy"><?php esc_html_e('Pharmacy', 'hospital-management-system'); ?></option>
                            <option value="administration"><?php esc_html_e('Administration', 'hospital-management-system'); ?></option>
                            <option value="emergency"><?php esc_html_e('Emergency', 'hospital-management-system'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="update_staff_role" class="button button-primary" value="<?php esc_html_e('Update Role', 'hospital-management-system'); ?>">
                <button type="button" class="button" onclick="jQuery('#role-update-modal').hide();"><?php esc_html_e('Cancel', 'hospital-management-system'); ?></button>
            </p>
        </form>
    </div>
</div>

<style type="text/css">
    .hms-capabilities {
        color: #2271b1;
        font-weight: 500;
    }
    
    .no-hms-caps {
        color: #666;
        font-style: italic;
    }
    
    #role-update-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 9999;
        min-width: 400px;
    }
    
    #role-update-modal::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: -1;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('.update-role-btn').on('click', function() {
        var staffId = $(this).data('staff-id');
        var currentRole = $(this).data('current-role');
        var currentDepartment = $(this).data('current-department');
        
        $('#modal-staff-id').val(staffId);
        $('#new_role').val(currentRole);
        $('#new_department').val(currentDepartment);
        
        $('#role-update-modal').show();
    });
    
    $('.create-user-btn').on('click', function() {
        var staffId = $(this).data('staff-id');
        
        if (confirm('<?php esc_html_e('Create WordPress user account for this staff member?', 'hospital-management-system'); ?>')) {
            // This would need an AJAX handler to create the user
            alert('<?php esc_html_e('This feature requires additional implementation.', 'hospital-management-system'); ?>');
        }
    });
});
</script>