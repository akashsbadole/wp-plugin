<div class="hms-patient-portal-wrap">
    <div class="hms-appointment-booking-form">
        <h2><?php esc_html_e('Book an Appointment', 'hospital-management-system'); ?></h2>
        
        <div id="hms-appointment-message"></div>

        <form id="hms-appointment-booking-form">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="department_id"><?php esc_html_e('Department', 'hospital-management-system'); ?></label>
                    <select name="department_id" id="department_id" class="form-control" required>
                        <option value=""><?php esc_html_e('Select Department', 'hospital-management-system'); ?></option>
                        <?php // Note: $departments variable needs to be passed by the shortcode handler in class-hms-public.php ?>
                        <?php if (!empty($departments)) : ?>
                            <?php foreach ($departments as $department) : ?>
                                <option value="<?php echo esc_attr($department->ID); ?>"><?php echo esc_html($department->post_title); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="doctor_id"><?php esc_html_e('Doctor', 'hospital-management-system'); ?></label>
                    <select name="doctor_id" id="doctor_id" class="form-control" required>
                        <option value=""><?php esc_html_e('Select Doctor', 'hospital-management-system'); ?></option>
                        <?php // Note: $doctors variable needs to be passed by the shortcode handler in class-hms-public.php ?>
                        <?php if (!empty($doctors)) : ?>
                            <?php foreach ($doctors as $doctor) : ?>
                                <option value="<?php echo esc_attr($doctor->ID); ?>"><?php echo esc_html($doctor->post_title); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="appointment_date"><?php esc_html_e('Preferred Date', 'hospital-management-system'); ?></label>
                    <input type="date" name="date_time" id="appointment_date" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="appointment_time"><?php esc_html_e('Preferred Time', 'hospital-management-system'); ?></label>
                    <!-- The original handler expects date and time combined in 'date_time'. 
                         For simplicity, this template will just use date. A more advanced version would combine date and time inputs. -->
                    <input type="hidden" name="date_time" id="date_time_hidden" value="">
                    <input type="time" name="time_input" id="appointment_time" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="reason"><?php esc_html_e('Reason for Visit', 'hospital-management-system'); ?></label>
                <textarea name="reason" id="reason" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary"><?php esc_html_e('Book Appointment', 'hospital-management-system'); ?></button>
            </div>
            <input type="hidden" name="action" value="hms_patient_book_appointment">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('hms_public_nonce'); ?>">
        </form>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Combine date and time into the 'date_time' field expected by the AJAX handler
                $('#hms-appointment-booking-form').on('submit', function() {
                    var date = $('#appointment_date').val();
                    var time = $('#appointment_time').val();
                    if (date && time) {
                        $('#date_time_hidden').val(date + ' ' + time);
                    } else {
                        // Fallback if one is missing, though 'required' should prevent this
                        $('#date_time_hidden').val(date ? date : time); 
                    }
                });
            });
        </script>
    </div>
</div>
