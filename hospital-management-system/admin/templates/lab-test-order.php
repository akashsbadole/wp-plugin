<?php
// Get available tests, patients, and doctors
global $hms_lab_radiology;
$available_tests = $hms_lab_radiology->get_available_tests();
$patients = $hms_lab_radiology->get_patients();
$doctors = $hms_lab_radiology->get_doctors();

$is_edit = isset($order_data) && $order_data;
$order_id = $is_edit ? $order_data->id : null;
?>

<div class="wrap hms-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? esc_html__('Edit Lab Test Order', 'hospital-management-system') : esc_html__('Order New Lab Test', 'hospital-management-system'); ?>
    </h1>
    
    <?php if ($is_edit): ?>
        <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order'); ?>" class="page-title-action">
            <?php esc_html_e('Add New', 'hospital-management-system'); ?>
        </a>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['message'])) : ?>
        <div id="message" class="notice notice-<?php echo esc_attr(sanitize_text_field($_GET['type'])); ?> is-dismissible">
            <p><?php echo wp_kses_post(sanitize_text_field($_GET['message'])); ?></p>
        </div>
    <?php endif; ?>

    <div class="hms-form-container">
        <form method="post" id="hms-lab-test-order-form" class="hms-form">
            
            <div class="hms-form-section">
                <h3><?php esc_html_e('Test Order Information', 'hospital-management-system'); ?></h3>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-col-6">
                        <label for="patient_id" class="hms-label required">
                            <?php esc_html_e('Patient', 'hospital-management-system'); ?>
                        </label>
                        <select name="patient_id" id="patient_id" class="hms-select" required>
                            <option value=""><?php esc_html_e('Select Patient', 'hospital-management-system'); ?></option>
                            <?php foreach ($patients as $patient) : ?>
                                <option value="<?php echo esc_attr($patient->id); ?>" 
                                        <?php selected($is_edit ? $order_data->patient_id : '', $patient->id); ?>>
                                    <?php echo esc_html(trim($patient->first_name . ' ' . $patient->last_name)); ?>
                                    <?php if ($patient->phone): ?>
                                        - <?php echo esc_html($patient->phone); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="hms-form-group hms-col-6">
                        <label for="doctor_id" class="hms-label required">
                            <?php esc_html_e('Ordering Doctor', 'hospital-management-system'); ?>
                        </label>
                        <select name="doctor_id" id="doctor_id" class="hms-select" required>
                            <option value=""><?php esc_html_e('Select Doctor', 'hospital-management-system'); ?></option>
                            <?php foreach ($doctors as $doctor) : ?>
                                <option value="<?php echo esc_attr($doctor->id); ?>" 
                                        <?php selected($is_edit ? $order_data->doctor_id : '', $doctor->id); ?>>
                                    <?php echo esc_html(trim($doctor->first_name . ' ' . $doctor->last_name)); ?>
                                    (<?php echo esc_html(ucfirst($doctor->role)); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-col-12">
                        <label for="test_id" class="hms-label required">
                            <?php esc_html_e('Test', 'hospital-management-system'); ?>
                        </label>
                        <select name="test_id" id="test_id" class="hms-select" required>
                            <option value=""><?php esc_html_e('Select Test', 'hospital-management-system'); ?></option>
                            <?php 
                            $current_category = '';
                            foreach ($available_tests as $test) : 
                                if ($test->category !== $current_category) {
                                    if ($current_category !== '') echo '</optgroup>';
                                    echo '<optgroup label="' . esc_attr($test->category) . '">';
                                    $current_category = $test->category;
                                }
                            ?>
                                <option value="<?php echo esc_attr($test->id); ?>" 
                                        <?php selected($is_edit ? $order_data->test_id : '', $test->id); ?>
                                        data-cost="<?php echo esc_attr($test->cost); ?>"
                                        data-normal-range="<?php echo esc_attr($test->normal_range); ?>">
                                    <?php echo esc_html($test->test_name); ?>
                                    <?php if ($test->cost > 0): ?>
                                        - $<?php echo esc_html(number_format($test->cost, 2)); ?>
                                    <?php endif; ?>
                                </option>
                            <?php 
                            endforeach; 
                            if ($current_category !== '') echo '</optgroup>';
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Select the test to be performed from the available test catalog.', 'hospital-management-system'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-col-12">
                        <label for="notes" class="hms-label">
                            <?php esc_html_e('Clinical Notes / Reason for Test', 'hospital-management-system'); ?>
                        </label>
                        <textarea name="notes" id="notes" class="hms-textarea" rows="3" 
                                  placeholder="<?php esc_attr_e('Enter clinical notes, symptoms, or reason for ordering this test...', 'hospital-management-system'); ?>"><?php echo $is_edit ? esc_textarea($order_data->notes) : ''; ?></textarea>
                    </div>
                </div>
            </div>

            
            <?php if ($is_edit): ?>
            <div class="hms-form-section">
                <h3><?php esc_html_e('Test Results & Status', 'hospital-management-system'); ?></h3>
                <p class="description">
                    <?php esc_html_e('This section is for lab staff to enter test results and update status.', 'hospital-management-system'); ?>
                </p>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-col-6">
                        <label for="status" class="hms-label">
                            <?php esc_html_e('Test Status', 'hospital-management-system'); ?>
                        </label>
                        <select name="status" id="status" class="hms-select">
                            <option value="pending" <?php selected($order_data->status, 'pending'); ?>>
                                <?php esc_html_e('Pending', 'hospital-management-system'); ?>
                            </option>
                            <option value="in-progress" <?php selected($order_data->status, 'in-progress'); ?>>
                                <?php esc_html_e('In Progress', 'hospital-management-system'); ?>
                            </option>
                            <option value="completed" <?php selected($order_data->status, 'completed'); ?>>
                                <?php esc_html_e('Completed', 'hospital-management-system'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="hms-form-group hms-col-6">
                        <label class="hms-label">
                            <?php esc_html_e('Normal Range', 'hospital-management-system'); ?>
                        </label>
                        <input type="text" class="hms-input" readonly 
                               value="<?php echo esc_attr($order_data->normal_range ?: __('Not specified', 'hospital-management-system')); ?>">
                        <p class="description">
                            <?php esc_html_e('Reference range from test catalog', 'hospital-management-system'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="hms-form-row">
                    <div class="hms-form-group hms-col-12">
                        <label for="results" class="hms-label">
                            <?php esc_html_e('Test Results', 'hospital-management-system'); ?>
                        </label>
                        <?php
                        $editor_content = $order_data->results ?: '';
                        wp_editor($editor_content, 'results', array(
                            'textarea_name' => 'results',
                            'textarea_rows' => 8,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => array('buttons' => 'strong,em,ul,ol,li,link,close'),
                        ));
                        ?>
                        <p class="description">
                            <?php esc_html_e('Enter detailed test results, measurements, and observations.', 'hospital-management-system'); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="hms-form-actions">
                <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                <input type="hidden" name="save_lab_test" value="1">
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
                <?php endif; ?>
                
                <button type="submit" class="button button-primary button-large">
                    <?php echo $is_edit ? esc_html__('Update Test Order', 'hospital-management-system') : esc_html__('Create Test Order', 'hospital-management-system'); ?>
                </button>
                
                <a href="<?php echo admin_url('admin.php?page=hms_lab_tests'); ?>" class="button button-secondary button-large">
                    <?php esc_html_e('Cancel', 'hospital-management-system'); ?>
                </a>
                
                <?php if ($is_edit && $order_data->status === 'completed' && !empty($order_data->results)): ?>
                    <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order&id=' . $order_id . '&action=generate_report'); ?>" 
                       class="button button-secondary button-large" target="_blank">
                        <?php esc_html_e('Generate Report', 'hospital-management-system'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update normal range when test is selected
    $('#test_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var normalRange = selectedOption.data('normal-range');
        if (normalRange) {
            $('.hms-form-group input[readonly]').val(normalRange);
        }
    });
    
    // Form validation
    $('#hms-lab-test-order-form').on('submit', function(e) {
        var patientId = $('#patient_id').val();
        var doctorId = $('#doctor_id').val();
        var testId = $('#test_id').val();
        
        if (!patientId || !doctorId || !testId) {
            e.preventDefault();
            alert('<?php esc_js_e('Please fill in all required fields.', 'hospital-management-system'); ?>');
            return false;
        }
    });
});
</script>
