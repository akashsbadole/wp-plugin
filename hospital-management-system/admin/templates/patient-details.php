<?php
/**
 * Patient details meta box template
 */
?>
<div class="patient-details">
    <div class="form-field">
        <label for="patient_id"><?php _e('Patient ID', 'hospital-management-system'); ?></label>
        <input type="text" id="patient_id" name="patient_id" value="<?php echo esc_attr($patient_id); ?>" readonly>
        <p class="description"><?php _e('Unique patient identifier (auto-generated)', 'hospital-management-system'); ?></p>
    </div>
    
    <div class="form-field">
        <label for="first_name"><?php _e('First Name', 'hospital-management-system'); ?></label>
        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($first_name); ?>" required>
    </div>
    
    <div class="form-field">
        <label for="last_name"><?php _e('Last Name', 'hospital-management-system'); ?></label>
        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($last_name); ?>" required>
    </div>
    
    <div class="form-field">
        <label for="dob"><?php _e('Date of Birth', 'hospital-management-system'); ?></label>
        <input type="date" id="dob" name="dob" value="<?php echo esc_attr($dob); ?>" required>
    </div>
    
    <div class="form-field">
        <label for="gender"><?php _e('Gender', 'hospital-management-system'); ?></label>
        <select id="gender" name="gender" required>
            <option value=""><?php _e('Select Gender', 'hospital-management-system'); ?></option>
            <option value="Male" <?php selected($gender, 'Male'); ?>><?php _e('Male', 'hospital-management-system'); ?></option>
            <option value="Female" <?php selected($gender, 'Female'); ?>><?php _e('Female', 'hospital-management-system'); ?></option>
            <option value="Other" <?php selected($gender, 'Other'); ?>><?php _e('Other', 'hospital-management-system'); ?></option>
        </select>
    </div>
    
    <div class="form-field">
        <label for="phone"><?php _e('Phone Number', 'hospital-management-system'); ?></label>
        <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($phone); ?>" required>
    </div>
    
    <div class="form-field">
        <label for="email"><?php _e('Email Address', 'hospital-management-system'); ?></label>
        <input type="email" id="email" name="email" value="<?php echo esc_attr($email); ?>">
    </div>
    
    <div class="form-field">
        <label for="address"><?php _e('Address', 'hospital-management-system'); ?></label>
        <textarea id="address" name="address" rows="3"><?php echo esc_textarea($address); ?></textarea>
    </div>

    <h3><?php _e('Notification Preferences', 'hospital-management-system'); ?></h3>
    <div class="form-field">
        <label for="notify_email">
            <input type="checkbox" id="notify_email" name="notify_email" value="1" <?php checked($notify_email, 1); ?>>
            <?php _e('Email Notifications', 'hospital-management-system'); ?>
        </label>
        <p class="description"><?php _e('Receive appointment confirmations and other updates via email.', 'hospital-management-system'); ?></p>
    </div>
    <div class="form-field">
        <label for="notify_sms">
            <input type="checkbox" id="notify_sms" name="notify_sms" value="1" <?php checked($notify_sms, 1); ?>>
            <?php _e('SMS Notifications', 'hospital-management-system'); ?>
        </label>
        <p class="description"><?php _e('Receive appointment reminders and updates via SMS.', 'hospital-management-system'); ?></p>
    </div>
    <div class="form-field">
        <label for="notify_whatsapp">
            <input type="checkbox" id="notify_whatsapp" name="notify_whatsapp" value="1" <?php checked($notify_whatsapp, 1); ?>>
            <?php _e('WhatsApp Notifications', 'hospital-management-system'); ?>
        </label>
        <p class="description"><?php _e('Receive appointment reminders and updates via WhatsApp.', 'hospital-management-system'); ?></p>
    </div>
</div>

<?php if ($post->ID) : // Only show consent section if patient is saved ?>
<h3><?php _e('Patient Consents', 'hospital-management-system'); ?></h3>
<div id="hms-patient-consents-wrapper" class="hms-patient-consents-wrapper">
    <p><?php _e('Manage patient consents for data processing and treatment as per compliance regulations (HIPAA/GDPR/DISHA).', 'hospital-management-system'); ?></p>

    <!-- Add New Consent Form -->
    <div class="hms-add-consent-form">
        <h4><?php _e('Add New Consent', 'hospital-management-system'); ?></h4>
        <form id="hms-consent-form">
            <?php wp_nonce_field('hms_admin_nonce', 'consent_nonce'); ?>
            <input type="hidden" name="patient_id" value="<?php echo esc_attr($post->ID); ?>">
            <div class="form-field">
                <label for="consent_type"><?php _e('Consent Type', 'hospital-management-system'); ?></label>
                <select id="consent_type" name="consent_type" required>
                    <option value=""><?php _e('Select Consent Type', 'hospital-management-system'); ?></option>
                    <option value="general_treatment"><?php _e('General Treatment Consent', 'hospital-management-system'); ?></option>
                    <option value="data_processing"><?php _e('Data Processing (GDPR)', 'hospital-management-system'); ?></option>
                    <option value="phi_disclosure"><?php _e('PHI Disclosure (HIPAA)', 'hospital-management-system'); ?></option>
                    <option value="telemedicine"><?php _e('Telemedicine Consultation', 'hospital-management-system'); ?></option>
                    <option value="photo_video"><?php _e('Photo/Video Usage', 'hospital-management-system'); ?></option>
                    <option value="research"><?php _e('Clinical Research', 'hospital-management-system'); ?></option>
                </select>
            </div>
            <div class="form-field">
                <label for="consent_text"><?php _e('Consent Text', 'hospital-management-system'); ?></label>
                <textarea id="consent_text" name="consent_text" rows="6" required class="large-text"></textarea>
                <p class="description"><?php _e('Enter the full consent text that the patient has agreed to.', 'hospital-management-system'); ?></p>
            </div>
            <div class="form-field">
                <label for="consent_status">
                    <input type="checkbox" id="consent_status" name="status" value="active" checked>
                    <?php _e('Consent is Active', 'hospital-management-system'); ?>
                </label>
            </div>
            <p>
                <button type="submit" class="button button-primary"><?php _e('Save Consent', 'hospital-management-system'); ?></button>
            </p>
        </form>
    </div>

    <!-- Existing Consents List -->
    <div class="hms-consents-list">
        <h4><?php _e('Existing Consents', 'hospital-management-system'); ?></h4>
        <div id="hms-consents-list-container">
            <p class="description"><?php _e('Loading consents...', 'hospital-management-system'); ?></p>
            <!-- Consents will be loaded here via AJAX -->
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var patientId = <?php echo json_encode($post->ID); ?>;
    var consentForm = $('#hms-consent-form');
    var consentsListContainer = $('#hms-consents-list-container');

    // Function to load consents
    function loadConsents() {
        consentsListContainer.html('<p class="description"><?php _e('Loading...', 'hospital-management-system'); ?></p>');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_get_patient_consents',
                patient_id: patientId,
                nonce: '<?php echo wp_create_nonce("hms_admin_nonce"); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderConsentsList(response.data);
                } else {
                    consentsListContainer.html('<p><?php _e("No consents found or an error occurred.", "hospital-management-system"); ?></p>');
                }
            },
            error: function() {
                consentsListContainer.html('<p><?php _e("Error loading consents.", "hospital-management-system"); ?></p>');
            }
        });
    }

    // Function to render the consents list
    function renderConsentsList(consents) {
        if (consents.length === 0) {
            consentsListContainer.html('<p><?php _e("No consents recorded for this patient.", "hospital-management-system"); ?></p>');
            return;
        }

        var html = '<table class="wp-list-table widefat fixed striped">';
        html += '<thead><tr>';
        html += '<th><?php _e("Type", "hospital-management-system"); ?></th>';
        html += '<th><?php _e("Status", "hospital-management-system"); ?></th>';
        html += '<th><?php _e("Given At", "hospital-management-system"); ?></th>';
        html += '<th><?php _e("Given By", "hospital-management-system"); ?></th>';
        html += '<th><?php _e("Actions", "hospital-management-system"); ?></th>';
        html += '</tr></thead><tbody>';

        consents.forEach(function(consent) {
            html += '<tr>';
            html += '<td>' + $('<div>').text(consent.consent_type.replace(/_/g, ' ').toUpperCase()).html() + '</td>';
            html += '<td><span class="hms-consent-status hms-consent-status-' + consent.status + '">' + $('<div>').text(consent.status.toUpperCase()).html() + '</span></td>';
            html += '<td>' + $('<div>').text(new Date(consent.given_at).toLocaleString()).html() + '</td>';
            html += '<td>' + $('<div>').text(consent.given_by_name).html() + '</td>';
            html += '<td>';
            if (consent.status === 'active') {
                html += '<button type="button" class="button button-small hms-revoke-consent" data-consent-id="' + consent.id + '"><?php _e("Revoke", "hospital-management-system"); ?></button>';
            }
            html += '</td>';
            html += '</tr>';
            if (consent.consent_text) {
                 html += '<tr class="hms-consent-detail-row"><td colspan="5"><strong><?php _e("Consent Text:", "hospital-management-system"); ?></strong><br><pre>' + $('<div>').text(consent.consent_text).html() + '</pre></td></tr>';
            }
        });

        html += '</tbody></table>';
        consentsListContainer.html(html);
    }

    // Handle consent form submission
    consentForm.on('submit', function(e) {
        e.preventDefault();
        var submitButton = consentForm.find('button[type="submit"]');
        var originalButtonText = submitButton.text();

        submitButton.prop('disabled', true).text('<?php _e("Saving...", "hospital-management-system"); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: consentForm.serialize() + '&action=hms_save_patient_consent&nonce=<?php echo wp_create_nonce("hms_admin_nonce"); ?>',
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    consentForm[0].reset(); // Reset form
                    loadConsents(); // Reload the list
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('<?php _e("An error occurred. Please try again.", "hospital-management-system"); ?>');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Handle consent revocation
    consentsListContainer.on('click', '.hms-revoke-consent', function() {
        if (!confirm('<?php _e("Are you sure you want to revoke this consent?", "hospital-management-system"); ?>')) {
            return;
        }
        var consentId = $(this).data('consent-id');
        var revokeButton = $(this);

        revokeButton.prop('disabled', true).text('<?php _e("Revoking...", "hospital-management-system"); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hms_revoke_patient_consent',
                consent_id: consentId,
                nonce: '<?php echo wp_create_nonce("hms_admin_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadConsents(); // Reload the list
                } else {
                    alert(response.data);
                    revokeButton.prop('disabled', false).text('<?php _e("Revoke", "hospital-management-system"); ?>');
                }
            },
            error: function() {
                alert('<?php _e("An error occurred. Please try again.", "hospital-management-system"); ?>');
                revokeButton.prop('disabled', false).text('<?php _e("Revoke", "hospital-management-system"); ?>');
            }
        });
    });

    // Initial load of consents
    loadConsents();
});
</script>

<style>
.hms-patient-consents-wrapper {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ccd0d4;
    background-color: #f8f9f9;
}
.hms-add-consent-form,
.hms-consents-list {
    margin-bottom: 20px;
}
.hms-add-consent-form h4,
.hms-consents-list h4 {
    margin-top: 0;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
}
.hms-consent-status {
    padding: 3px 8px;
    border-radius: 3px;
    color: #fff;
    font-weight: bold;
    font-size: 12px;
}
.hms-consent-status-active {
    background-color: #46b450;
}
.hms-consent-status-revoked {
    background-color: #dc3232;
}
.hms-consent-detail-row td {
    padding-top: 0 !important;
    border-top: none !important;
    background-color: #f1f1f1;
}
.hms-consent-detail-row pre {
    white-space: pre-wrap; /* Wrap long lines */
    word-wrap: break-word;
    background-color: #fff;
    border: 1px solid #ddd;
    padding: 10px;
    margin-top: 5px;
}
</style>
<?php endif; // End check for saved patient ?>
