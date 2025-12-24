<div class="wrap">
    <h1>
        <?php
        if (isset($_GET['id'])) {
            _e('Edit Insurance Provider', 'hospital-management-system');
        } else {
            _e('Add New Insurance Provider', 'hospital-management-system');
        }
        ?>
        <a href="<?php echo admin_url('admin.php?page=hms_insurance'); ?>" class="page-title-action"><?php _e('&larr; Back to Insurance Providers', 'hospital-management-system'); ?></a>
    </h1>

    <?php
    $provider = null;
    $is_edit = false;

    if (isset($_GET['id'])) {
        $is_edit = true;
        $provider_id = intval($_GET['id']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_insurance_providers';
        $provider = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $provider_id));

        if (!$provider) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Insurance provider not found.', 'hospital-management-system') . '</p></div>';
            return;
        }
    }

    // Handle form submission feedback
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>

    <form id="insurance-provider-form" method="post" action="">
        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
        <input type="hidden" name="page" value="hms_insurance_details">
        <?php if ($is_edit): ?>
            <input type="hidden" name="provider_id" value="<?php echo esc_attr($provider->id); ?>">
        <?php endif; ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <!-- Main content area -->
                </div>

                <div id="postbox-container-1" class="postbox-container">
                    <div id="submitdiv" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Save', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <span class="spinner"></span>
                                        <input type="submit" name="save_provider" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? __('Update Provider', 'hospital-management-system') : __('Add Provider', 'hospital-management-system'); ?>">
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="providerdetails" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Provider Details', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="name"><?php _e('Provider Name', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" name="name" id="name" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->name) : (isset($_POST['name']) ? esc_attr($_POST['name']) : ''); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="provider_id_code"><?php _e('Provider ID Code', 'hospital-management-system'); ?> <span class="description">(required)</span></label>
                                        </th>
                                        <td>
                                            <input type="text" name="provider_id_code" id="provider_id_code" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->provider_id) : (isset($_POST['provider_id_code']) ? esc_attr($_POST['provider_id_code']) : ''); ?>" placeholder="<?php _e('e.g., INS-001', 'hospital-management-system'); ?>" required>
                                            <p class="description"><?php _e('A unique code to identify the insurance provider.', 'hospital-management-system'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="contact_person"><?php _e('Contact Person', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="contact_person" id="contact_person" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->contact_person) : (isset($_POST['contact_person']) ? esc_attr($_POST['contact_person']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="email"><?php _e('Email', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="email" name="email" id="email" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->email) : (isset($_POST['email']) ? esc_attr($_POST['email']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="phone"><?php _e('Phone', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="phone" id="phone" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->phone) : (isset($_POST['phone']) ? esc_attr($_POST['phone']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="address"><?php _e('Address', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="address" id="address" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($provider->address) : (isset($_POST['address']) ? esc_textarea($_POST['address']) : ''); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="status"><?php _e('Status', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <select name="status" id="status" class="regular-text">
                                                <option value="active" <?php echo ($is_edit && $provider->status === 'active') || (!$is_edit && (!isset($_POST['status']) || $_POST['status'] === 'active')) ? 'selected="selected"' : ''; ?>><?php _e('Active', 'hospital-management-system'); ?></option>
                                                <option value="inactive" <?php echo ($is_edit && $provider->status === 'inactive') || (!$is_edit && isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected="selected"' : ''; ?>><?php _e('Inactive', 'hospital-management-system'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div id="additionalinfo" class="postbox">
                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Additional Information', 'hospital-management-system'); ?></span></h2>
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <label for="website"><?php _e('Website', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <input type="url" name="website" id="website" class="regular-text" value="<?php echo $is_edit ? esc_attr($provider->website) : (isset($_POST['website']) ? esc_attr($_POST['website']) : ''); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="notes"><?php _e('Notes', 'hospital-management-system'); ?></label>
                                        </th>
                                        <td>
                                            <textarea name="notes" id="notes" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($provider->notes) : (isset($_POST['notes']) ? esc_textarea($_POST['notes']) : ''); ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Form submission validation
    $('#insurance-provider-form').on('submit', function(e) {
        if ($('#name').val() === '') {
            alert('<?php _e('Please enter a provider name.', 'hospital-management-system'); ?>');
            $('#name').focus();
            e.preventDefault();
            return false;
        }
        if ($('#provider_id_code').val() === '') {
            alert('<?php _e('Please enter a provider ID code.', 'hospital-management-system'); ?>');
            $('#provider_id_code').focus();
            e.preventDefault();
            return false;
        }
        // Add more validation as needed
    });
});
</script>
