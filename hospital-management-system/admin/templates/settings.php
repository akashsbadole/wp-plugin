<?php
/**
 * Admin Settings Page Template for Hospital Management System
 *
 * @package Hospital_Management_System
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Check if the user has the required capability.
if (!current_user_can('manage_hms')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'hms_settings_group-options')) {
    // Handle role capabilities separately
    if (isset($_POST['hms_role_caps']) && is_array($_POST['hms_role_caps'])) {
        foreach ($_POST['hms_role_caps'] as $role_name => $capabilities) {
            $role = get_role($role_name);
            if ($role) {
                // Get all HMS capabilities
                $hms_capabilities = array(
                    'manage_hms',
                    'manage_patients', 
                    'manage_clinical',
                    'manage_billing',
                    'manage_pharmacy',
                    'manage_reports'
                );
                
                // Remove all HMS capabilities first
                foreach ($hms_capabilities as $cap) {
                    $role->remove_cap($cap);
                }
                
                // Add back the selected capabilities
                if (is_array($capabilities)) {
                    foreach ($capabilities as $cap => $value) {
                        if ($value && in_array($cap, $hms_capabilities)) {
                            $role->add_cap($cap);
                        }
                    }
                }
            }
        }
    }
}

// Get current settings
$hospital_name = get_option('hms_hospital_name', '');
$hospital_address = get_option('hms_hospital_address', '');
$hospital_phone = get_option('hms_hospital_phone', '');
$hospital_email = get_option('hms_hospital_email', '');
$hospital_logo_id = get_option('hms_hospital_logo_id', '');
$hospital_favicon_id = get_option('hms_hospital_favicon_id', '');
$currency = get_option('hms_currency', 'INR');
$time_zone = get_option('hms_time_zone', 'Asia/Kolkata');

// White-label branding settings
$primary_color = get_option('hms_primary_color', '#2271b1');
$secondary_color = get_option('hms_secondary_color', '#135e96');
$custom_css = get_option('hms_custom_css', '');
$hide_wp_branding = get_option('hms_hide_wp_branding', 'off');
$custom_login_logo = get_option('hms_custom_login_logo', 'off');

$appointment_duration = get_option('hms_appointment_duration', 30);
$advance_booking_days = get_option('hms_advance_booking_days', 7);
$cancellation_policy = get_option('hms_cancellation_policy', '');

$enable_sms = get_option('hms_enable_sms', 'off');
$enable_email = get_option('hms_enable_email', 'on');
$enable_whatsapp = get_option('hms_enable_whatsapp', 'off');
$sms_gateway = get_option('hms_sms_gateway', 'twilio');
$sms_api_key = get_option('hms_sms_api_key', '');
$sms_sender_id = get_option('hms_sms_sender_id', '');
$whatsapp_api_key = get_option('hms_whatsapp_api_key', '');
$email_from_name = get_option('hms_email_from_name', get_bloginfo('name'));
$email_from_address = get_option('hms_email_from_address', get_option('admin_email'));

$payment_gateway = get_option('hms_payment_gateway', 'razorpay');
$payment_gateway_keys = get_option('hms_payment_gateway_keys', array());
$lis_api_endpoint = get_option('hms_lis_api_endpoint', '');
$lis_api_key = get_option('hms_lis_api_key', '');
$emr_api_endpoint = get_option('hms_emr_api_endpoint', '');
$emr_api_key = get_option('hms_emr_api_key', '');

// Advanced settings
$enable_debug_mode = get_option('hms_enable_debug_mode', 'off');
$log_retention_days = get_option('hms_log_retention_days', 30);
$session_timeout = get_option('hms_session_timeout', 60);
$max_file_upload_size = get_option('hms_max_file_upload_size', 10);

// Default roles for capabilities section
$roles = get_editable_roles();

// Get the current active tab from the URL, default to 'general-settings'
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general-settings';
?>

<div class="wrap hms-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <p class="description"><?php esc_html_e('Configure the settings for the Hospital Management System plugin. Changes are saved automatically.', 'hospital-management-system'); ?></p>
    
    <?php
    // Display success message if settings were saved
    if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
        echo '<div class="hms-settings-message success">';
        echo '<p>' . esc_html__('Settings saved successfully!', 'hospital-management-system') . '</p>';
        echo '</div>';
    }
    ?>

    <?php if ($active_tab === 'premium') : ?>
        <!-- Premium Tab Content (License Management) -->
        <?php
        // Check if the user has the required capability to access the premium tab
        if (current_user_can('manage_hms')) {
            require_once plugin_dir_path(__FILE__) . 'license-management.php';
        } else {
            // Show an error message if the user doesn't have the required capability
            echo '<div class="notice notice-error"><p>' . esc_html__('You do not have sufficient permissions to access this tab.', 'hospital-management-system') . '</p></div>';
        }
        ?>
    <?php else : ?>
        <form method="post" action="options.php" id="hms-settings-form">
            <?php
            settings_fields('hms_settings_group');
            // This will output the nonce, action, and option_page fields for the settings group.
            ?>

            <div class="hms-settings-tabs">
                <h2 class="nav-tab-wrapper">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=general-settings')); ?>" class="nav-tab <?php echo $active_tab === 'general-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('General', 'hospital-management-system'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=branding-settings')); ?>" class="nav-tab <?php echo $active_tab === 'branding-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Branding', 'hospital-management-system'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=notification-settings')); ?>" class="nav-tab <?php echo $active_tab === 'notification-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Notifications', 'hospital-management-system'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=integration-settings')); ?>" class="nav-tab <?php echo $active_tab === 'integration-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Integrations', 'hospital-management-system'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=role-capabilities-settings')); ?>" class="nav-tab <?php echo $active_tab === 'role-capabilities-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Roles & Permissions', 'hospital-management-system'); ?></a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=hms-settings&tab=advanced-settings')); ?>" class="nav-tab <?php echo $active_tab === 'advanced-settings' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Advanced', 'hospital-management-system'); ?></a>
                </h2>
            </div>

            <div class="hms-settings-sections">

                <!-- General Settings -->
                <div id="general-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'general-settings' ? 'display:none;' : ''; ?>">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="hms_hospital_name"><?php esc_html_e('Hospital Name', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <input name="hms_hospital_name" type="text" id="hms_hospital_name" value="<?php echo esc_attr($hospital_name); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Enter the official name of your hospital.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_hospital_address"><?php esc_html_e('Hospital Address', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <textarea name="hms_hospital_address" id="hms_hospital_address" rows="3" class="large-text"><?php echo esc_textarea($hospital_address); ?></textarea>
                                    <p class="description"><?php esc_html_e('Enter the full address of your hospital.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_hospital_phone"><?php esc_html_e('Hospital Phone', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <input name="hms_hospital_phone" type="tel" id="hms_hospital_phone" value="<?php echo esc_attr($hospital_phone); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Enter the primary contact phone number.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_hospital_email"><?php esc_html_e('Hospital Email', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <input name="hms_hospital_email" type="email" id="hms_hospital_email" value="<?php echo esc_attr($hospital_email); ?>" class="regular-text">
                                    <p class="description"><?php esc_html_e('Enter the primary contact email address.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_hospital_logo_id"><?php esc_html_e('Hospital Logo', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <div class="hms-media-upload-wrapper">
                                        <input type="hidden" name="hms_hospital_logo_id" id="hms_hospital_logo_id" value="<?php echo esc_attr($hospital_logo_id); ?>">
                                        <div id="hms-logo-preview" class="hms-media-preview">
                                            <?php if ($hospital_logo_id) : ?>
                                                <?php echo wp_get_attachment_image($hospital_logo_id, 'medium'); ?>
                                            <?php endif; ?>
                                        </div>
                                        <button type="button" id="hms-upload-logo-button" class="button"><?php esc_html_e('Upload Logo', 'hospital-management-system'); ?></button>
                                        <button type="button" id="hms-remove-logo-button" class="button" style="<?php echo $hospital_logo_id ? '' : 'display:none;'; ?>"><?php esc_html_e('Remove Logo', 'hospital-management-system'); ?></button>
                                    </div>
                                    <p class="description"><?php esc_html_e('Upload the official logo for your hospital.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_currency"><?php esc_html_e('Currency', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <select name="hms_currency" id="hms_currency">
                                        <?php
                                        $currencies = array(
                                            'INR' => __('Indian Rupee (₹)', 'hospital-management-system'),
                                            'USD' => __('US Dollar ($)', 'hospital-management-system'),
                                            'EUR' => __('Euro (€)', 'hospital-management-system'),
                                            'GBP' => __('British Pound Sterling (£)', 'hospital-management-system'),
                                            // Add more currencies as needed
                                        );
                                        foreach ($currencies as $code => $name) {
                                            echo '<option value="' . esc_attr($code) . '" ' . selected($currency, $code, false) . '>' . esc_html($name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Select the default currency for billing.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_time_zone"><?php esc_html_e('Timezone', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <select name="hms_time_zone" id="hms_time_zone">
                                        <?php
                                        $timezone_identifiers = DateTimeZone::listIdentifiers();
                                        $selected_timezone = $time_zone;
                                        echo '<optgroup label="' . esc_attr__('Popular', 'hospital-management-system') . '">';
                                        echo '<option value="UTC"' . selected('UTC', $selected_timezone, false) . '>UTC</option>';
                                        echo '</optgroup>';
                                        foreach ($timezone_identifiers as $timezone) {
                                            if (strpos($timezone, '/') === false) {
                                                continue;
                                            }
                                            $continent = explode('/', $timezone, 2)[0];
                                            static $current_continent = null;
                                            if ($current_continent !== $continent) {
                                                if ($current_continent !== null) {
                                                    echo '</optgroup>';
                                                }
                                                echo '<optgroup label="' . esc_attr($continent) . '">';
                                                $current_continent = $continent;
                                            }
                                            echo '<option value="' . esc_attr($timezone) . '" ' . selected($timezone, $selected_timezone, false) . '>' . esc_html(str_replace('_', ' ', $timezone)) . '</option>';
                                        }
                                        if ($current_continent !== null) {
                                            echo '</optgroup>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Select the timezone for your hospital.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Branding Settings -->
                <div id="branding-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'branding-settings' ? 'display:none;' : ''; ?>">
                    <div class="hms-branding-section">
                        <h3><?php esc_html_e('Hospital Branding', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="hms_hospital_favicon_id"><?php esc_html_e('Hospital Favicon', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <div class="hms-media-upload-wrapper">
                                            <input type="hidden" name="hms_hospital_favicon_id" id="hms_hospital_favicon_id" value="<?php echo esc_attr($hospital_favicon_id); ?>">
                                            <div id="hms-favicon-preview" class="hms-media-preview">
                                                <?php if ($hospital_favicon_id) : ?>
                                                    <?php echo wp_get_attachment_image($hospital_favicon_id, array(32, 32)); ?>
                                                <?php else : ?>
                                                    <div class="hms-media-preview empty"><?php esc_html_e('No favicon selected', 'hospital-management-system'); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="hms-media-buttons">
                                                <button type="button" id="hms-upload-favicon-button" class="button"><?php esc_html_e('Upload Favicon', 'hospital-management-system'); ?></button>
                                                <button type="button" id="hms-remove-favicon-button" class="button" style="<?php echo $hospital_favicon_id ? '' : 'display:none;'; ?>"><?php esc_html_e('Remove Favicon', 'hospital-management-system'); ?></button>
                                            </div>
                                        </div>
                                        <p class="description"><?php esc_html_e('Upload a favicon for your hospital (recommended size: 32x32 pixels).', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_primary_color"><?php esc_html_e('Primary Color', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <div class="hms-color-picker-wrapper">
                                            <input name="hms_primary_color" type="text" id="hms_primary_color" value="<?php echo esc_attr($primary_color); ?>" class="hms-color-picker">
                                            <span class="hms-color-value"><?php echo esc_html($primary_color); ?></span>
                                        </div>
                                        <p class="description"><?php esc_html_e('Choose the primary color for your hospital branding.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_secondary_color"><?php esc_html_e('Secondary Color', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <div class="hms-color-picker-wrapper">
                                            <input name="hms_secondary_color" type="text" id="hms_secondary_color" value="<?php echo esc_attr($secondary_color); ?>" class="hms-color-picker">
                                            <span class="hms-color-value"><?php echo esc_html($secondary_color); ?></span>
                                        </div>
                                        <p class="description"><?php esc_html_e('Choose the secondary color for your hospital branding.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Custom Login Logo', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-radio-group">
                                            <label><input name="hms_custom_login_logo" type="radio" value="on" <?php checked($custom_login_logo, 'on'); ?> /> <?php esc_html_e('Enable', 'hospital-management-system'); ?></label>
                                            <label><input name="hms_custom_login_logo" type="radio" value="off" <?php checked($custom_login_logo, 'off'); ?> /> <?php esc_html_e('Disable', 'hospital-management-system'); ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e('Use hospital logo on WordPress login page.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Hide WordPress Branding', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-radio-group">
                                            <label><input name="hms_hide_wp_branding" type="radio" value="on" <?php checked($hide_wp_branding, 'on'); ?> /> <?php esc_html_e('Enable', 'hospital-management-system'); ?></label>
                                            <label><input name="hms_hide_wp_branding" type="radio" value="off" <?php checked($hide_wp_branding, 'off'); ?> /> <?php esc_html_e('Disable', 'hospital-management-system'); ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e('Hide WordPress branding in admin area and replace with hospital branding.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_custom_css"><?php esc_html_e('Custom CSS', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <textarea name="hms_custom_css" id="hms_custom_css" rows="10" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                                        <p class="description"><?php esc_html_e('Add custom CSS to further customize the appearance of your HMS installation.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-reset-defaults" data-section="branding"><?php esc_html_e('Reset to Defaults', 'hospital-management-system'); ?></button>
                        </p>
                    </div>
                </div>

                <!-- Appointment Settings -->
                <div id="appointment-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'appointment-settings' ? 'display:none;' : ''; ?>">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="hms_appointment_duration"><?php esc_html_e('Default Appointment Duration (minutes)', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <input name="hms_appointment_duration" type="number" id="hms_appointment_duration" value="<?php echo esc_attr($appointment_duration); ?>" class="small-text">
                                    <p class="description"><?php esc_html_e('Set the default duration for each appointment slot.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_advance_booking_days"><?php esc_html_e('Advance Booking Days', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <input name="hms_advance_booking_days" type="number" id="hms_advance_booking_days" value="<?php echo esc_attr($advance_booking_days); ?>" class="small-text">
                                    <p class="description"><?php esc_html_e('Set the maximum number of days in advance patients can book appointments.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="hms_cancellation_policy"><?php esc_html_e('Cancellation Policy', 'hospital-management-system'); ?></label></th>
                                <td>
                                    <textarea name="hms_cancellation_policy" id="hms_cancellation_policy" rows="5" class="large-text"><?php echo esc_textarea($cancellation_policy); ?></textarea>
                                    <p class="description"><?php esc_html_e('Enter the cancellation policy for appointments.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notification Settings -->
                <div id="notification-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'notification-settings' ? 'display:none;' : ''; ?>">
                    
                    <!-- Email Notifications -->
                    <div class="hms-notification-section">
                        <h3><?php esc_html_e('Email Notifications', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Enable Email Notifications', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-notification-toggle">
                                            <label class="toggle-switch">
                                                <input name="hms_enable_email" type="checkbox" value="on" <?php checked($enable_email, 'on'); ?>>
                                                <span class="slider"></span>
                                            </label>
                                            <span><?php esc_html_e('Send email notifications for appointments and other events', 'hospital-management-system'); ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_email === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_email_from_name"><?php esc_html_e('From Name', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_email_from_name" type="text" id="hms_email_from_name" value="<?php echo esc_attr($email_from_name); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Name that appears in the "From" field of emails.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_email === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_email_from_address"><?php esc_html_e('From Email Address', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_email_from_address" type="email" id="hms_email_from_address" value="<?php echo esc_attr($email_from_address); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Email address that appears in the "From" field of emails.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="email"><?php esc_html_e('Test Email Configuration', 'hospital-management-system'); ?></button>
                        </p>
                    </div>

                    <!-- SMS Notifications -->
                    <div class="hms-notification-section">
                        <h3><?php esc_html_e('SMS Notifications', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Enable SMS Notifications', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-notification-toggle">
                                            <label class="toggle-switch">
                                                <input name="hms_enable_sms" type="checkbox" value="on" <?php checked($enable_sms, 'on'); ?>>
                                                <span class="slider"></span>
                                            </label>
                                            <span><?php esc_html_e('Send SMS notifications for appointments and reminders', 'hospital-management-system'); ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_sms === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_sms_gateway"><?php esc_html_e('SMS Gateway', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <select name="hms_sms_gateway" id="hms_sms_gateway">
                                            <option value="twilio" <?php selected($sms_gateway, 'twilio'); ?>>Twilio</option>
                                            <option value="plivo" <?php selected($sms_gateway, 'plivo'); ?>>Plivo</option>
                                            <option value="msg91" <?php selected($sms_gateway, 'msg91'); ?>>MSG91</option>
                                            <option value="textlocal" <?php selected($sms_gateway, 'textlocal'); ?>>TextLocal</option>
                                            <option value="other" <?php selected($sms_gateway, 'other'); ?>><?php esc_html_e('Other', 'hospital-management-system'); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e('Select your SMS gateway provider.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_sms === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_sms_api_key"><?php esc_html_e('SMS Gateway API Key', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_sms_api_key" type="password" id="hms_sms_api_key" value="<?php echo esc_attr($sms_api_key); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API key for your SMS gateway.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_sms === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_sms_sender_id"><?php esc_html_e('SMS Sender ID', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_sms_sender_id" type="text" id="hms_sms_sender_id" value="<?php echo esc_attr($sms_sender_id); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Sender ID that appears in SMS messages (if supported by gateway).', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="sms"><?php esc_html_e('Test SMS Configuration', 'hospital-management-system'); ?></button>
                        </p>
                    </div>

                    <!-- WhatsApp Notifications -->
                    <div class="hms-notification-section">
                        <h3><?php esc_html_e('WhatsApp Notifications', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Enable WhatsApp Notifications', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-notification-toggle">
                                            <label class="toggle-switch">
                                                <input name="hms_enable_whatsapp" type="checkbox" value="on" <?php checked($enable_whatsapp, 'on'); ?>>
                                                <span class="slider"></span>
                                            </label>
                                            <span><?php esc_html_e('Send WhatsApp notifications for appointments and updates', 'hospital-management-system'); ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hms-notification-fields" style="<?php echo $enable_whatsapp === 'on' ? '' : 'display:none;'; ?>">
                                    <th scope="row"><label for="hms_whatsapp_api_key"><?php esc_html_e('WhatsApp API Key', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_whatsapp_api_key" type="password" id="hms_whatsapp_api_key" value="<?php echo esc_attr($whatsapp_api_key); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API key for your WhatsApp Business API.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="whatsapp"><?php esc_html_e('Test WhatsApp Configuration', 'hospital-management-system'); ?></button>
                        </p>
                    </div>
                </div>

                <!-- Integration Settings -->
                <div id="integration-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'integration-settings' ? 'display:none;' : ''; ?>">
                    
                    <!-- Payment Gateway Integration -->
                    <div class="hms-integration-section">
                        <h3><?php esc_html_e('Payment Gateway Integration', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="hms_payment_gateway"><?php esc_html_e('Payment Gateway', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <select name="hms_payment_gateway" id="hms_payment_gateway">
                                            <option value="cash" <?php selected($payment_gateway, 'cash'); ?>><?php esc_html_e('Cash (Offline)', 'hospital-management-system'); ?></option>
                                            <option value="razorpay" <?php selected($payment_gateway, 'razorpay'); ?>>Razorpay</option>
                                            <option value="stripe" <?php selected($payment_gateway, 'stripe'); ?>>Stripe</option>
                                            <option value="paypal" <?php selected($payment_gateway, 'paypal'); ?>>PayPal</option>
                                            <option value="payu" <?php selected($payment_gateway, 'payu'); ?>>PayU</option>
                                        </select>
                                        <p class="description"><?php esc_html_e('Select your primary payment gateway for online payments.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Gateway Configuration', 'hospital-management-system'); ?></th>
                                    <td>
                                        <?php
                                        // Gateway-specific configurations
                                        $razorpay_key_id = isset($payment_gateway_keys['razorpay']['key_id']) ? $payment_gateway_keys['razorpay']['key_id'] : '';
                                        $razorpay_key_secret = isset($payment_gateway_keys['razorpay']['key_secret']) ? $payment_gateway_keys['razorpay']['key_secret'] : '';
                                        
                                        $stripe_public_key = isset($payment_gateway_keys['stripe']['public_key']) ? $payment_gateway_keys['stripe']['public_key'] : '';
                                        $stripe_secret_key = isset($payment_gateway_keys['stripe']['secret_key']) ? $payment_gateway_keys['stripe']['secret_key'] : '';
                                        
                                        $paypal_client_id = isset($payment_gateway_keys['paypal']['client_id']) ? $payment_gateway_keys['paypal']['client_id'] : '';
                                        $paypal_client_secret = isset($payment_gateway_keys['paypal']['client_secret']) ? $payment_gateway_keys['paypal']['client_secret'] : '';
                                        ?>
                                        
                                        <!-- Razorpay Keys -->
                                        <div id="hms-razorpay-keys" class="hms-gateway-keys <?php echo $payment_gateway == 'razorpay' ? 'active' : ''; ?>">
                                            <input type="text" name="hms_payment_gateway_keys[razorpay][key_id]" placeholder="<?php esc_attr_e('Razorpay Key ID', 'hospital-management-system'); ?>" value="<?php echo esc_attr($razorpay_key_id); ?>" class="regular-text" />
                                            <input type="password" name="hms_payment_gateway_keys[razorpay][key_secret]" placeholder="<?php esc_attr_e('Razorpay Key Secret', 'hospital-management-system'); ?>" value="<?php echo esc_attr($razorpay_key_secret); ?>" class="regular-text" />
                                        </div>
                                        
                                        <!-- Stripe Keys -->
                                        <div id="hms-stripe-keys" class="hms-gateway-keys <?php echo $payment_gateway == 'stripe' ? 'active' : ''; ?>">
                                            <input type="text" name="hms_payment_gateway_keys[stripe][public_key]" placeholder="<?php esc_attr_e('Stripe Publishable Key', 'hospital-management-system'); ?>" value="<?php echo esc_attr($stripe_public_key); ?>" class="regular-text" />
                                            <input type="password" name="hms_payment_gateway_keys[stripe][secret_key]" placeholder="<?php esc_attr_e('Stripe Secret Key', 'hospital-management-system'); ?>" value="<?php echo esc_attr($stripe_secret_key); ?>" class="regular-text" />
                                        </div>
                                        
                                        <!-- PayPal Keys -->
                                        <div id="hms-paypal-keys" class="hms-gateway-keys <?php echo $payment_gateway == 'paypal' ? 'active' : ''; ?>">
                                            <input type="text" name="hms_payment_gateway_keys[paypal][client_id]" placeholder="<?php esc_attr_e('PayPal Client ID', 'hospital-management-system'); ?>" value="<?php echo esc_attr($paypal_client_id); ?>" class="regular-text" />
                                            <input type="password" name="hms_payment_gateway_keys[paypal][client_secret]" placeholder="<?php esc_attr_e('PayPal Client Secret', 'hospital-management-system'); ?>" value="<?php echo esc_attr($paypal_client_secret); ?>" class="regular-text" />
                                        </div>
                                        
                                        <!-- Cash (No configuration needed) -->
                                        <div id="hms-cash-keys" class="hms-gateway-keys <?php echo $payment_gateway == 'cash' ? 'active' : ''; ?>">
                                            <p class="description"><?php esc_html_e('No configuration required for cash payments. All payments will be processed offline.', 'hospital-management-system'); ?></p>
                                        </div>
                                        
                                        <p class="description"><?php esc_html_e('Enter the API keys for your selected payment gateway.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="payment"><?php esc_html_e('Test Payment Gateway', 'hospital-management-system'); ?></button>
                        </p>
                    </div>

                    <!-- Laboratory Information System (LIS) Integration -->
                    <div class="hms-integration-section">
                        <h3><?php esc_html_e('Laboratory Information System (LIS)', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="hms_lis_api_endpoint"><?php esc_html_e('LIS API Endpoint', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_lis_api_endpoint" type="url" id="hms_lis_api_endpoint" value="<?php echo esc_url($lis_api_endpoint); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API endpoint for Laboratory Information System integration.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_lis_api_key"><?php esc_html_e('LIS API Key', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_lis_api_key" type="password" id="hms_lis_api_key" value="<?php echo esc_attr($lis_api_key); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API key for LIS authentication.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="lis"><?php esc_html_e('Test LIS Connection', 'hospital-management-system'); ?></button>
                        </p>
                    </div>

                    <!-- Electronic Medical Record (EMR) Integration -->
                    <div class="hms-integration-section">
                        <h3><?php esc_html_e('Electronic Medical Record (EMR) System', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="hms_emr_api_endpoint"><?php esc_html_e('EMR API Endpoint', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_emr_api_endpoint" type="url" id="hms_emr_api_endpoint" value="<?php echo esc_url($emr_api_endpoint); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API endpoint for Electronic Medical Record system integration.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_emr_api_key"><?php esc_html_e('EMR API Key', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_emr_api_key" type="password" id="hms_emr_api_key" value="<?php echo esc_attr($emr_api_key); ?>" class="regular-text">
                                        <p class="description"><?php esc_html_e('Enter the API key for EMR authentication.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-test-connection" data-service="emr"><?php esc_html_e('Test EMR Connection', 'hospital-management-system'); ?></button>
                        </p>
                    </div>
                </div>

                <!-- Role Capabilities Settings -->
                <div id="role-capabilities-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'role-capabilities-settings' ? 'display:none;' : ''; ?>">
                    <p class="description"><?php esc_html_e('Manage capabilities for each user role. Unchecking a capability will remove it from the role.', 'hospital-management-system'); ?></p>
                    <table class="form-table" role="presentation">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Capability', 'hospital-management-system'); ?></th>
                                <?php foreach ($roles as $role_key => $role_info) : ?>
                                    <th><?php echo esc_html($role_info['name']); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Define the capabilities we want to manage for HMS
                            $hms_capabilities = array(
                                'manage_hms' => __('Manage HMS', 'hospital-management-system'),
                                'manage_patients' => __('Manage Patients', 'hospital-management-system'),
                                'manage_clinical' => __('Manage Clinical', 'hospital-management-system'),
                                'manage_billing' => __('Manage Billing', 'hospital-management-system'),
                                'manage_pharmacy' => __('Manage Pharmacy', 'hospital-management-system'),
                                'manage_reports' => __('Manage Reports', 'hospital-management-system'),
                            );

                            foreach ($hms_capabilities as $cap_key => $cap_name) :
                            ?>
                                <tr>
                                    <th scope="row"><?php echo esc_html($cap_name); ?></th>
                                    <?php foreach ($roles as $role_key => $role_info) :
                                        $role = get_role($role_key);
                                        $has_cap = $role && $role->has_cap($cap_key);
                                    ?>
                                        <td>
                                            <input type="checkbox" name="hms_role_caps[<?php echo esc_attr($role_key); ?>][<?php echo esc_attr($cap_key); ?>]" value="1" <?php checked($has_cap); ?> />
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="description"><?php esc_html_e('<strong>Note:</strong> Changes to role capabilities take effect immediately. Be careful when modifying capabilities for administrative roles.', 'hospital-management-system'); ?></p>
                </div>

                <!-- Advanced Settings -->
                <div id="advanced-settings" class="hms-settings-section" style="<?php echo $active_tab !== 'advanced-settings' ? 'display:none;' : ''; ?>">
                    <div class="hms-advanced-warning">
                        <strong><?php esc_html_e('Warning:', 'hospital-management-system'); ?></strong>
                        <?php esc_html_e('These are advanced settings that can affect system performance and security. Only modify these settings if you understand their implications.', 'hospital-management-system'); ?>
                    </div>
                    
                    <div class="hms-advanced-section">
                        <h3><?php esc_html_e('System Configuration', 'hospital-management-system'); ?></h3>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e('Debug Mode', 'hospital-management-system'); ?></th>
                                    <td>
                                        <div class="hms-radio-group">
                                            <label><input name="hms_enable_debug_mode" type="radio" value="on" <?php checked($enable_debug_mode, 'on'); ?> /> <?php esc_html_e('Enable', 'hospital-management-system'); ?></label>
                                            <label><input name="hms_enable_debug_mode" type="radio" value="off" <?php checked($enable_debug_mode, 'off'); ?> /> <?php esc_html_e('Disable', 'hospital-management-system'); ?></label>
                                        </div>
                                        <p class="description"><?php esc_html_e('Enable debug mode to log detailed information for troubleshooting. Disable in production.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_log_retention_days"><?php esc_html_e('Log Retention (Days)', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_log_retention_days" type="number" id="hms_log_retention_days" value="<?php echo esc_attr($log_retention_days); ?>" class="small-text" min="1" max="365">
                                        <p class="description"><?php esc_html_e('Number of days to keep system logs before automatic cleanup.', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_session_timeout"><?php esc_html_e('Session Timeout (Minutes)', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_session_timeout" type="number" id="hms_session_timeout" value="<?php echo esc_attr($session_timeout); ?>" class="small-text" min="5" max="480">
                                        <p class="description"><?php esc_html_e('Automatic logout time for inactive users (5-480 minutes).', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hms_max_file_upload_size"><?php esc_html_e('Max File Upload Size (MB)', 'hospital-management-system'); ?></label></th>
                                    <td>
                                        <input name="hms_max_file_upload_size" type="number" id="hms_max_file_upload_size" value="<?php echo esc_attr($max_file_upload_size); ?>" class="small-text" min="1" max="100">
                                        <p class="description"><?php esc_html_e('Maximum file size for uploads (medical documents, images, etc.).', 'hospital-management-system'); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit">
                            <button type="button" class="button hms-reset-defaults" data-section="advanced"><?php esc_html_e('Reset to Defaults', 'hospital-management-system'); ?></button>
                        </p>
                    </div>
                </div>

            </div>

            <?php submit_button(__('Save Settings', 'hospital-management-system')); ?>
        </form>
    <?php endif; ?>
</div>
