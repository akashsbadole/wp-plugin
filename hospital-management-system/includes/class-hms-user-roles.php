<?php
/**
 * HMS User Roles and Permissions Management Class
 *
 * Handles user roles and permissions for the Hospital Management System.
 *
 * @package Hospital_Management_System
 * @subpackage Includes
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class HMS_User_Roles {

    /**
     * HMS capabilities
     */
    private $hms_capabilities = array(
        'manage_hms' => 'Manage HMS System',
        'manage_patients' => 'Manage Patients',
        'manage_clinical' => 'Manage Clinical Records',
        'manage_billing' => 'Manage Billing',
        'manage_pharmacy' => 'Manage Pharmacy',
        'manage_reports' => 'Manage Reports',
        'view_patients' => 'View Patients',
        'edit_patients' => 'Edit Patients',
        'delete_patients' => 'Delete Patients',
        'view_appointments' => 'View Appointments',
        'edit_appointments' => 'Edit Appointments',
        'delete_appointments' => 'Delete Appointments',
        'view_medical_records' => 'View Medical Records',
        'edit_medical_records' => 'Edit Medical Records',
        'delete_medical_records' => 'Delete Medical Records',
        'view_prescriptions' => 'View Prescriptions',
        'edit_prescriptions' => 'Edit Prescriptions',
        'delete_prescriptions' => 'Delete Prescriptions',
        'view_billing' => 'View Billing',
        'edit_billing' => 'Edit Billing',
        'delete_billing' => 'Delete Billing',
        'view_pharmacy' => 'View Pharmacy',
        'edit_pharmacy' => 'Edit Pharmacy',
        'manage_inventory' => 'Manage Inventory',
        'view_reports' => 'View Reports',
        'export_data' => 'Export Data',
        'import_data' => 'Import Data',
        'manage_staff' => 'Manage Staff',
        'manage_departments' => 'Manage Departments',
        'manage_queue' => 'Manage Patient Queue',
        'manage_lab_tests' => 'Manage Lab Tests',
        'manage_insurance' => 'Manage Insurance'
    );

    /**
     * HMS roles with their default capabilities
     */
    private $hms_roles = array(
        'hms_administrator' => array(
            'name' => 'HMS Administrator',
            'capabilities' => array(
                'manage_hms',
                'manage_patients',
                'manage_clinical',
                'manage_billing',
                'manage_pharmacy',
                'manage_reports',
                'manage_staff',
                'manage_departments',
                'manage_queue',
                'manage_lab_tests',
                'manage_insurance',
                'view_patients',
                'edit_patients',
                'delete_patients',
                'view_appointments',
                'edit_appointments',
                'delete_appointments',
                'view_medical_records',
                'edit_medical_records',
                'delete_medical_records',
                'view_prescriptions',
                'edit_prescriptions',
                'delete_prescriptions',
                'view_billing',
                'edit_billing',
                'delete_billing',
                'view_pharmacy',
                'edit_pharmacy',
                'manage_inventory',
                'view_reports',
                'export_data',
                'import_data'
            )
        ),
        'hms_doctor' => array(
            'name' => 'Doctor',
            'capabilities' => array(
                'view_patients',
                'edit_patients',
                'view_appointments',
                'edit_appointments',
                'view_medical_records',
                'edit_medical_records',
                'view_prescriptions',
                'edit_prescriptions',
                'manage_lab_tests',
                'view_reports'
            )
        ),
        'hms_nurse' => array(
            'name' => 'Nurse',
            'capabilities' => array(
                'view_patients',
                'edit_patients',
                'view_appointments',
                'view_medical_records',
                'edit_medical_records',
                'view_prescriptions',
                'manage_queue',
                'view_reports'
            )
        ),
        'hms_receptionist' => array(
            'name' => 'Receptionist',
            'capabilities' => array(
                'view_patients',
                'edit_patients',
                'view_appointments',
                'edit_appointments',
                'manage_queue',
                'view_billing',
                'edit_billing'
            )
        ),
        'hms_pharmacist' => array(
            'name' => 'Pharmacist',
            'capabilities' => array(
                'view_patients',
                'view_prescriptions',
                'view_pharmacy',
                'edit_pharmacy',
                'manage_inventory'
            )
        ),
        'hms_lab_technician' => array(
            'name' => 'Lab Technician',
            'capabilities' => array(
                'view_patients',
                'manage_lab_tests',
                'view_reports'
            )
        ),
        'hms_billing_clerk' => array(
            'name' => 'Billing Clerk',
            'capabilities' => array(
                'view_patients',
                'view_billing',
                'edit_billing',
                'manage_insurance',
                'view_reports'
            )
        ),
        'hms_manager' => array(
            'name' => 'Hospital Manager',
            'capabilities' => array(
                'view_patients',
                'view_appointments',
                'view_medical_records',
                'view_prescriptions',
                'view_billing',
                'view_pharmacy',
                'view_reports',
                'export_data',
                'manage_staff',
                'manage_departments'
            )
        )
    );

    /**
     * Initialize the user roles class
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }

    /**
     * Initialize hooks and filters
     */
    public function init() {
        // Add capability checks to menu items
        add_filter('user_has_cap', array($this, 'check_hms_capabilities'), 10, 3);
        
        // Add role management to user profile
        add_action('show_user_profile', array($this, 'add_hms_role_fields'));
        add_action('edit_user_profile', array($this, 'add_hms_role_fields'));
        add_action('personal_options_update', array($this, 'save_hms_role_fields'));
        add_action('edit_user_profile_update', array($this, 'save_hms_role_fields'));
    }

    /**
     * Create HMS roles and capabilities
     */
    public function create_roles() {
        // Add HMS capabilities to administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($this->hms_capabilities as $cap => $description) {
                $admin_role->add_cap($cap);
            }
        }

        // Create HMS-specific roles
        foreach ($this->hms_roles as $role_key => $role_data) {
            // Remove role if it exists to recreate with updated capabilities
            remove_role($role_key);
            
            // Create role with basic WordPress capabilities
            add_role($role_key, $role_data['name'], array(
                'read' => true,
                'upload_files' => true
            ));

            // Add HMS capabilities to the role
            $role = get_role($role_key);
            if ($role) {
                foreach ($role_data['capabilities'] as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }

    /**
     * Remove HMS roles and capabilities
     */
    public function remove_roles() {
        // Remove HMS capabilities from administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($this->hms_capabilities as $cap => $description) {
                $admin_role->remove_cap($cap);
            }
        }

        // Remove HMS-specific roles
        foreach ($this->hms_roles as $role_key => $role_data) {
            remove_role($role_key);
        }
    }

    /**
     * Check HMS capabilities
     *
     * @param array $allcaps All capabilities
     * @param array $caps Required capabilities
     * @param array $args Arguments
     * @return array Modified capabilities
     */
    public function check_hms_capabilities($allcaps, $caps, $args) {
        // If user has manage_hms capability, grant access to all HMS features
        if (isset($allcaps['manage_hms']) && $allcaps['manage_hms']) {
            foreach ($this->hms_capabilities as $cap => $description) {
                $allcaps[$cap] = true;
            }
        }

        return $allcaps;
    }

    /**
     * Add HMS role fields to user profile
     *
     * @param WP_User $user User object
     */
    public function add_hms_role_fields($user) {
        if (!current_user_can('manage_hms')) {
            return;
        }

        $user_hms_role = get_user_meta($user->ID, 'hms_role', true);
        ?>
        <h3><?php esc_html_e('HMS Role', 'hospital-management-system'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="hms_role"><?php esc_html_e('HMS Role', 'hospital-management-system'); ?></label></th>
                <td>
                    <select name="hms_role" id="hms_role">
                        <option value=""><?php esc_html_e('No HMS Role', 'hospital-management-system'); ?></option>
                        <?php foreach ($this->hms_roles as $role_key => $role_data) : ?>
                            <option value="<?php echo esc_attr($role_key); ?>" <?php selected($user_hms_role, $role_key); ?>>
                                <?php echo esc_html($role_data['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('Select the HMS role for this user. This will determine their permissions within the Hospital Management System.', 'hospital-management-system'); ?></p>
                </td>
            </tr>
        </table>

        <?php if (!empty($user_hms_role) && isset($this->hms_roles[$user_hms_role])) : ?>
        <h4><?php esc_html_e('HMS Capabilities', 'hospital-management-system'); ?></h4>
        <table class="form-table">
            <tr>
                <td colspan="2">
                    <p><?php esc_html_e('This user has the following HMS capabilities:', 'hospital-management-system'); ?></p>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <?php foreach ($this->hms_roles[$user_hms_role]['capabilities'] as $cap) : ?>
                            <li><?php echo esc_html($this->hms_capabilities[$cap] ?? $cap); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            </tr>
        </table>
        <?php endif; ?>
        <?php
    }

    /**
     * Save HMS role fields
     *
     * @param int $user_id User ID
     */
    public function save_hms_role_fields($user_id) {
        if (!current_user_can('manage_hms')) {
            return;
        }

        if (isset($_POST['hms_role'])) {
            $hms_role = sanitize_text_field($_POST['hms_role']);
            
            // Remove previous HMS role
            $previous_role = get_user_meta($user_id, 'hms_role', true);
            if (!empty($previous_role)) {
                $user = new WP_User($user_id);
                $user->remove_role($previous_role);
            }

            // Add new HMS role
            if (!empty($hms_role) && isset($this->hms_roles[$hms_role])) {
                $user = new WP_User($user_id);
                $user->add_role($hms_role);
                update_user_meta($user_id, 'hms_role', $hms_role);
            } else {
                delete_user_meta($user_id, 'hms_role');
            }
        }
    }

    /**
     * Get HMS roles
     *
     * @return array HMS roles
     */
    public function get_hms_roles() {
        return $this->hms_roles;
    }

    /**
     * Get HMS capabilities
     *
     * @return array HMS capabilities
     */
    public function get_hms_capabilities() {
        return $this->hms_capabilities;
    }

    /**
     * Get users by HMS role
     *
     * @param string $role HMS role
     * @return array Users with the specified HMS role
     */
    public function get_users_by_hms_role($role) {
        $users = get_users(array(
            'meta_key' => 'hms_role',
            'meta_value' => $role
        ));

        return $users;
    }

    /**
     * Check if user has HMS capability
     *
     * @param int $user_id User ID
     * @param string $capability Capability to check
     * @return bool True if user has capability
     */
    public function user_has_hms_capability($user_id, $capability) {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return false;
        }

        return $user->has_cap($capability);
    }

    /**
     * Get user's HMS role
     *
     * @param int $user_id User ID
     * @return string|false HMS role or false if none
     */
    public function get_user_hms_role($user_id) {
        return get_user_meta($user_id, 'hms_role', true);
    }

    /**
     * Get role capabilities matrix for settings page
     *
     * @return array Role capabilities matrix
     */
    public function get_role_capabilities_matrix() {
        $matrix = array();
        $roles = get_editable_roles();

        foreach ($roles as $role_key => $role_info) {
            $role = get_role($role_key);
            if ($role) {
                $matrix[$role_key] = array(
                    'name' => $role_info['name'],
                    'capabilities' => array()
                );

                foreach ($this->hms_capabilities as $cap => $description) {
                    $matrix[$role_key]['capabilities'][$cap] = $role->has_cap($cap);
                }
            }
        }

        return $matrix;
    }

    /**
     * Update role capability
     *
     * @param string $role_name Role name
     * @param string $capability Capability name
     * @param bool $enabled Whether to enable or disable the capability
     * @return bool True on success, false on failure
     */
    public function update_role_capability($role_name, $capability, $enabled) {
        $role = get_role($role_name);
        if (!$role) {
            return false;
        }

        if (!array_key_exists($capability, $this->hms_capabilities)) {
            return false;
        }

        if ($enabled) {
            $role->add_cap($capability);
        } else {
            $role->remove_cap($capability);
        }

        return true;
    }

    /**
     * Get capability groups for better organization
     *
     * @return array Capability groups
     */
    public function get_capability_groups() {
        return array(
            'system' => array(
                'name' => __('System Management', 'hospital-management-system'),
                'capabilities' => array('manage_hms', 'manage_staff', 'manage_departments')
            ),
            'patients' => array(
                'name' => __('Patient Management', 'hospital-management-system'),
                'capabilities' => array('view_patients', 'edit_patients', 'delete_patients', 'manage_patients')
            ),
            'appointments' => array(
                'name' => __('Appointment Management', 'hospital-management-system'),
                'capabilities' => array('view_appointments', 'edit_appointments', 'delete_appointments')
            ),
            'clinical' => array(
                'name' => __('Clinical Management', 'hospital-management-system'),
                'capabilities' => array('view_medical_records', 'edit_medical_records', 'delete_medical_records', 'manage_clinical')
            ),
            'prescriptions' => array(
                'name' => __('Prescription Management', 'hospital-management-system'),
                'capabilities' => array('view_prescriptions', 'edit_prescriptions', 'delete_prescriptions')
            ),
            'billing' => array(
                'name' => __('Billing Management', 'hospital-management-system'),
                'capabilities' => array('view_billing', 'edit_billing', 'delete_billing', 'manage_billing', 'manage_insurance')
            ),
            'pharmacy' => array(
                'name' => __('Pharmacy Management', 'hospital-management-system'),
                'capabilities' => array('view_pharmacy', 'edit_pharmacy', 'manage_inventory', 'manage_pharmacy')
            ),
            'laboratory' => array(
                'name' => __('Laboratory Management', 'hospital-management-system'),
                'capabilities' => array('manage_lab_tests')
            ),
            'queue' => array(
                'name' => __('Queue Management', 'hospital-management-system'),
                'capabilities' => array('manage_queue')
            ),
            'reports' => array(
                'name' => __('Reports & Data', 'hospital-management-system'),
                'capabilities' => array('view_reports', 'manage_reports', 'export_data', 'import_data')
            )
        );
    }

    /**
     * Reset role capabilities to defaults
     *
     * @param string $role_name Role name
     * @return bool True on success, false on failure
     */
    public function reset_role_capabilities($role_name) {
        $role = get_role($role_name);
        if (!$role) {
            return false;
        }

        // Remove all HMS capabilities
        foreach ($this->hms_capabilities as $cap => $description) {
            $role->remove_cap($cap);
        }

        // Add default capabilities if it's an HMS role
        if (isset($this->hms_roles[$role_name])) {
            foreach ($this->hms_roles[$role_name]['capabilities'] as $cap) {
                $role->add_cap($cap);
            }
        }

        return true;
    }
}