<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['id'])) {
            esc_html_e('Edit Staff Member', 'hospital-management-system');
        } else {
            esc_html_e('Add New Staff Member', 'hospital-management-system');
        }
        ?>
        <a href="<?php echo admin_url('admin.php?page=hms_staff'); ?>" class="page-title-action"><?php esc_html_e('&larr; Back to Staff', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    $staff = null;
    $is_edit = false;
    $staff_obj = new HMS_Staff();

    if (isset($_GET['id'])) {
        $is_edit = true;
        $staff_id = intval($_GET['id']);
        
        // Use the enhanced method to get staff with user data
        $staff = $staff_obj->get_staff_profile($staff_id);

        if (!$staff) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Staff member not found or database error occurred.', 'hospital-management-system') . '</p></div>';
            return;
        }
    }

    // Get departments from database for dropdown
    global $wpdb;
    $departments_table = $wpdb->prefix . 'hms_departments';
    $departments = $wpdb->get_results("SELECT * FROM $departments_table ORDER BY name ASC");
    
    // Fallback departments if table doesn't exist or is empty
    $default_departments = array(
        'cardiology' => __('Cardiology', 'hospital-management-system'),
        'neurology' => __('Neurology', 'hospital-management-system'),
        'orthopedics' => __('Orthopedics', 'hospital-management-system'),
        'pediatrics' => __('Pediatrics', 'hospital-management-system'),
        'radiology' => __('Radiology', 'hospital-management-system'),
        'laboratory' => __('Laboratory', 'hospital-management-system'),
        'pharmacy' => __('Pharmacy', 'hospital-management-system'),
        'administration' => __('Administration', 'hospital-management-system'),
        'emergency' => __('Emergency', 'hospital-management-system'),
        'oncology' => __('Oncology', 'hospital-management-system')
    );
    ?>

    // Handle form submission feedback
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <form id="staff-member-form" method="post" action="">
        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="page" value="hms_staff_details">
        <?php if ($is_edit): ?>
            <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff->id); ?>">
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
                                        <input type="submit" name="save_staff" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Staff', 'hospital-management-system') : __('Add Staff', 'hospital-management-system'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="personalinfo" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Personal Information', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="first_name"><?php esc_html_e('First Name', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" name="first_name" id="first_name" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->first_name) : (isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : ''); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="last_name"><?php esc_html_e('Last Name', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" name="last_name" id="last_name" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->last_name) : (isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : ''); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="contact_number"><?php esc_html_e('Contact Number', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="contact_number" id="contact_number" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->contact_number) : (isset($_POST['contact_number']) ? esc_attr($_POST['contact_number']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="email"><?php esc_html_e('Email', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="email" name="email" id="email" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->email) : (isset($_POST['email']) ? esc_attr($_POST['email']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="date_of_birth"><?php esc_html_e('Date of Birth', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="date" name="date_of_birth" id="date_of_birth" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->date_of_birth) : (isset($_POST['date_of_birth']) ? esc_attr($_POST['date_of_birth']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="gender"><?php esc_html_e('Gender', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="gender" id="gender" class="regular-text">
                                                <option value=""><?php esc_html_e('Select Gender', 'hospital-management-system'); ?></option>
                                                <option value="male" <?php echo ($is_edit && $staff->gender === 'male') || (!$is_edit && isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Male', 'hospital-management-system'); ?></option>
                                                <option value="female" <?php echo ($is_edit && $staff->gender === 'female') || (!$is_edit && isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Female', 'hospital-management-system'); ?></option>
                                                <option value="other" <?php echo ($is_edit && $staff->gender === 'other') || (!$is_edit && isset($_POST['gender']) && $_POST['gender'] === 'other') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Other', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="address"><?php esc_html_e('Address', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="address" id="address" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($staff->address) : (isset($_POST['address']) ? esc_textarea($_POST['address']) : ''); ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="professionalinfo" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Professional Information', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="staff_id_code"><?php esc_html_e('Staff ID Code', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" name="staff_id_code" id="staff_id_code" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->staff_id) : (isset($_POST['staff_id_code']) ? esc_attr($_POST['staff_id_code']) : ''); ?>" placeholder="<?php esc_html_e('e.g., STF-001', 'hospital-management-system'); ?>" required>
                                            <p class="description"><?php esc_html_e('A unique code to identify the staff member.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="role"><?php esc_html_e('Role', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <select name="role" id="role" class="regular-text" required>
                                                <option value=""><?php esc_html_e('Select Role', 'hospital-management-system'); ?></option>
                                                <option value="doctor" <?php echo ($is_edit && $staff->role === 'doctor') || (!$is_edit && isset($_POST['role']) && $_POST['role'] === 'doctor') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Doctor', 'hospital-management-system'); ?></option>
                                                <option value="nurse" <?php echo ($is_edit && $staff->role === 'nurse') || (!$is_edit && isset($_POST['role']) && $_POST['role'] === 'nurse') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Nurse', 'hospital-management-system'); ?></option>
                                                <option value="technician" <?php echo ($is_edit && $staff->role === 'technician') || (!$is_edit && isset($_POST['role']) && $_POST['role'] === 'technician') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Technician', 'hospital-management-system'); ?></option>
                                                <option value="admin" <?php echo ($is_edit && $staff->role === 'admin') || (!$is_edit && isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Administrator', 'hospital-management-system'); ?></option>
                                                <option value="receptionist" <?php echo ($is_edit && $staff->role === 'receptionist') || (!$is_edit && isset($_POST['role']) && $_POST['role'] === 'receptionist') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Receptionist', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="department"><?php esc_html_e('Department', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <select name="department" id="department" class="regular-text" required>
                                                <option value=""><?php esc_html_e('Select Department', 'hospital-management-system'); ?></option>
                                                <?php
                                                // Use database departments if available, otherwise use defaults
                                                if (!empty($departments)) {
                                                    foreach ($departments as $dept) {
                                                        $selected = ($is_edit && $staff->department === $dept->code) || (!$is_edit && isset($_POST['department']) && $_POST['department'] === $dept->code) ? 'selected="selected"' : '';
                                                        echo '<option value="' . esc_attr($dept->code) . '" ' . $selected . '>' . esc_html($dept->name) . '</option>';
                                                    }
                                                } else {
                                                    foreach ($default_departments as $code => $name) {
                                                        $selected = ($is_edit && $staff->department === $code) || (!$is_edit && isset($_POST['department']) && $_POST['department'] === $code) ? 'selected="selected"' : '';
                                                        echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="qualification"><?php esc_html_e('Qualification', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="qualification" id="qualification" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->qualification) : (isset($_POST['qualification']) ? esc_attr($_POST['qualification']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="specialization"><?php esc_html_e('Specialization', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="specialization" id="specialization" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->specialization) : (isset($_POST['specialization']) ? esc_attr($_POST['specialization']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="experience_years"><?php esc_html_e('Experience (Years)', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" name="experience_years" id="experience_years" class="small-text" min="0" value="<?php echo $is_edit ? esc_attr($staff->experience_years) : (isset($_POST['experience_years']) ? esc_attr($_POST['experience_years']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="joining_date"><?php esc_html_e('Joining Date', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="date" name="joining_date" id="joining_date" class="regular-text" value="<?php echo $is_edit ? esc_attr($staff->joining_date) : (isset($_POST['joining_date']) ? esc_attr($_POST['joining_date']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="status"><?php esc_html_e('Status', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="status" id="status" class="regular-text">
                                                <option value="active" <?php echo ($is_edit && $staff->status === 'active') || (!$is_edit && (!isset($_POST['status']) || $_POST['status'] === 'active')) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Active', 'hospital-management-system'); ?></option>
                                                <option value="inactive" <?php echo ($is_edit && $staff->status === 'inactive') || (!$is_edit && isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected="selected"' : ''; ?>><?php esc_html_e('Inactive', 'hospital-management-system'); ?></option>
                                                <option value="on_leave" <?php echo ($is_edit && $staff->status === 'on_leave') || (!$is_edit && isset($_POST['status']) && $_POST['status'] === 'on_leave') ? 'selected="selected"' : ''; ?>><?php esc_html_e('On Leave', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php if (!$is_edit): ?>
                                    <tr>
                                        <th scope="row">
                                            <label for="create_wp_user"><?php esc_html_e('WordPress User Account', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <label>
                                                <input type="checkbox" name="create_wp_user" id="create_wp_user" value="1" <?php echo isset($_POST['create_wp_user']) ? 'checked="checked"' : ''; ?>>
                                                <?php esc_html_e('Create WordPress user account for this staff member', 'hospital-management-system'); ?>
                                            </label>
                                            <p class="description"><?php esc_html_e('This will create a WordPress user account with appropriate HMS role and permissions.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="profileinfo" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php esc_html_e('Profile Information', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="profile_photo"><?php esc_html_e('Profile Photo URL', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="url" name="profile_photo" id="profile_photo" class="regular-text" value="<?php echo $is_edit && isset($staff->profile_photo) ? esc_attr($staff->profile_photo) : (isset($_POST['profile_photo']) ? esc_attr($_POST['profile_photo']) : ''); ?>" placeholder="<?php esc_html_e('https://example.com/photo.jpg', 'hospital-management-system'); ?>">
                                            <p class="description"><?php esc_html_e('Enter the URL of the staff member\'s profile photo.', 'hospital-management-system'); ?></p>
                                            <?php if ($is_edit && !empty($staff->profile_photo)): ?>
                                                <div class="profile-photo-preview" style="margin-top: 10px;">
                                                    <img src="<?php echo esc_url($staff->profile_photo); ?>" alt="<?php esc_html_e('Profile Photo', 'hospital-management-system'); ?>" style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="emergency_contact"><?php esc_html_e('Emergency Contact', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="emergency_contact" id="emergency_contact" class="regular-text" value="<?php echo $is_edit && isset($staff->emergency_contact) ? esc_attr($staff->emergency_contact) : (isset($_POST['emergency_contact']) ? esc_attr($_POST['emergency_contact']) : ''); ?>" placeholder="<?php esc_html_e('Name and phone number', 'hospital-management-system'); ?>">
                                            <p class="description"><?php esc_html_e('Emergency contact person and their phone number.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="license_number"><?php esc_html_e('License Number', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="license_number" id="license_number" class="regular-text" value="<?php echo $is_edit && isset($staff->license_number) ? esc_attr($staff->license_number) : (isset($_POST['license_number']) ? esc_attr($_POST['license_number']) : ''); ?>" placeholder="<?php esc_html_e('Professional license number', 'hospital-management-system'); ?>">
                                            <p class="description"><?php esc_html_e('Medical license or professional certification number.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="certifications"><?php esc_html_e('Certifications', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="certifications" id="certifications" rows="3" class="large-text" placeholder="<?php esc_html_e('List professional certifications...', 'hospital-management-system'); ?>"><?php echo $is_edit && isset($staff->certifications) ? esc_textarea($staff->certifications) : (isset($_POST['certifications']) ? esc_textarea($_POST['certifications']) : ''); ?></textarea>
                                            <p class="description"><?php esc_html_e('Professional certifications, training, and credentials.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                </tbody>
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
                                            <label for="notes"><?php esc_html_e('Notes', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($staff->notes) : (isset($_POST['notes']) ? esc_textarea($_POST['notes']) : ''); ?></textarea>
                                        </td>
                                    </tr>
                                    <?php if ($is_edit && !empty($staff->user_login)): ?>
                                    <tr>
                                        <th scope="row">
                                            <label><?php esc_html_e('WordPress User', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <p><strong><?php esc_html_e('Username:', 'hospital-management-system'); ?></strong> <?php echo esc_html($staff->user_login); ?></p>
                                            <p><strong><?php esc_html_e('Display Name:', 'hospital-management-system'); ?></strong> <?php echo esc_html($staff->display_name); ?></p>
                                            <p><strong><?php esc_html_e('User Registered:', 'hospital-management-system'); ?></strong> <?php echo esc_html(date('Y-m-d H:i:s', strtotime($staff->user_registered))); ?></p>
                                            <p class="description"><?php esc_html_e('This staff member is linked to a WordPress user account.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

