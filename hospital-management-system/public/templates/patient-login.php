<div class="hms-patient-portal-wrap">
    <div class="hms-patient-login-form">
        <h2><?php esc_html_e('Patient Login', 'hospital-management-system'); ?></h2>
        <p><?php esc_html_e('Please enter your Patient ID and registered phone number to login.', 'hospital-management-system'); ?></p>
        
        <div id="hms-login-message"></div>

        <form id="hms-patient-login-form">
            <div class="form-group">
                <label for="patient_id"><?php esc_html_e('Patient ID', 'hospital-management-system'); ?></label>
                <input type="text" name="patient_id" id="patient_id" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="phone"><?php esc_html_e('Phone Number', 'hospital-management-system'); ?></label>
                <input type="tel" name="phone" id="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php esc_html_e('Login', 'hospital-management-system'); ?></button>
            </div>
            <input type="hidden" name="action" value="patient_login">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hms_admin_nonce'); ?>">
        </form>
    </div>
</div>
