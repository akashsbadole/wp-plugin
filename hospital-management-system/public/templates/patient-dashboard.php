<div class="hms-patient-portal-wrap">
    <div class="hms-patient-dashboard">
        <h2><?php esc_html_e('Patient Dashboard', 'hospital-management-system'); ?></h2>
        
        <?php if ($patient && !is_wp_error($patient)) : ?>
            <div class="hms-welcome-message">
                <?php 
                /* translators: %s: Patient full name */
                printf(esc_html__('Welcome, %s!', 'hospital-management-system'), esc_html(get_post_meta($patient->ID, '_first_name', true) . ' ' . get_post_meta($patient->ID, '_last_name', true)));
                ?>
                <p><strong><?php esc_html_e('Patient ID:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_post_meta($patient->ID, '_patient_id', true)); ?></p>
                <p><strong><?php esc_html_e('Email:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_post_meta($patient->ID, '_email', true)); ?></p>
                <p><strong><?php esc_html_e('Phone:', 'hospital-management-system'); ?></strong> <?php echo esc_html(get_post_meta($patient->ID, '_phone', true)); ?></p>
            </div>

            <div class="hms-dashboard-sections">
                <!-- Appointments Section -->
                <div class="hms-section hms-appointments-section">
                    <h4><?php esc_html_e('Upcoming Appointments', 'hospital-management-system'); ?></h4>
                    <?php if (!empty($appointments)) : ?>
                        <table class="hms-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Time', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Department', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Doctor', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment) : 
                                    $appointment_date = get_post_meta($appointment->ID, '_appointment_date', true);
                                    // Only show upcoming appointments
                                    if (strtotime($appointment_date) >= strtotime('today')) :
                                        $doctor_post = get_post($appointment->ID); // Assuming doctor_id is a post ID for hms_staff
                                        $doctor_name = $doctor_post ? get_the_title($doctor_post->ID) : esc_html__('N/A', 'hospital-management-system');
                                ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($appointment_date))); ?></td>
                                        <td><?php echo esc_html(get_post_meta($appointment->ID, '_appointment_time', true)); ?></td>
                                        <td><?php echo esc_html(get_post_meta($appointment->ID, '_department', true)); ?></td>
                                        <td><?php echo esc_html($doctor_name); ?></td>
                                        <td><?php echo esc_html(get_post_meta($appointment->ID, '_status', true)); ?></td>
                                    </tr>
                                <?php endif; endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p><?php esc_html_e('No upcoming appointments found.', 'hospital-management-system'); ?></p>
                    <?php endif; ?>
                    <p><a href="<?php echo esc_url(home_url('/appointment-booking/')); ?>" class="btn btn-secondary"><?php esc_html_e('Book New Appointment', 'hospital-management-system'); ?></a></p>
                </div>

                <!-- Bills Section -->
                <div class="hms-section hms-bills-section">
                    <h4><?php esc_html_e('Recent Bills', 'hospital-management-system'); ?></h4>
                    <?php if (!empty($bills)) : ?>
                        <table class="hms-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Bill ID', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Total Amount', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Paid Amount', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Status', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Action', 'hospital-management-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bills as $bill) : ?>
                                    <tr>
                                        <td><?php echo esc_html(get_post_meta($bill->ID, '_bill_id', true) ?: '#' . $bill->ID); ?></td>
                                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($bill->post_date))); ?></td>
                                        <td><?php echo esc_html(get_post_meta($bill->ID, '_net_amount', true)); ?></td>
                                        <td><?php echo esc_html(get_post_meta($bill->ID, '_paid_amount', true)); ?></td>
                                        <td><?php echo esc_html(get_post_meta($bill->ID, '_payment_status', true)); ?></td>
                                        <td><a href="#" class="hms-view-bill-link" data-bill-id="<?php echo esc_attr($bill->ID); ?>"><?php esc_html_e('View/Pay', 'hospital-management-system'); ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p><?php esc_html_e('No recent bills found.', 'hospital-management-system'); ?></p>
                    <?php endif; ?>
                    <p><a href="<?php echo esc_url(home_url('/bill-payment/')); ?>" class="btn btn-secondary"><?php esc_html_e('View All Bills', 'hospital-management-system'); ?></a></p>
                </div>

                <!-- Prescriptions Section -->
                <div class="hms-section hms-prescriptions-section">
                    <h4><?php esc_html_e('Recent Prescriptions', 'hospital-management-system'); ?></h4>
                    <?php if (!empty($prescriptions)) : ?>
                        <table class="hms-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Date', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Doctor', 'hospital-management-system'); ?></th>
                                    <th><?php esc_html_e('Prescription', 'hospital-management-system'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prescriptions as $prescription_data) : 
                                    $doctor_post = get_post($prescription_data['doctor_id']); // Assuming doctor_id is a post ID for hms_staff
                                    $doctor_name = $doctor_post ? get_the_title($doctor_post->ID) : esc_html__('N/A', 'hospital-management-system');
                                ?>
                                    <tr>
                                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($prescription_data['appointment_date']))); ?></td>
                                        <td><?php echo esc_html($doctor_name); ?></td>
                                        <td><?php echo esc_html(nl2br($prescription_data['prescription'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p><?php esc_html_e('No recent prescriptions found.', 'hospital-management-system'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <p><?php esc_html_e('Patient information could not be loaded.', 'hospital-management-system'); ?></p>
        <?php endif; ?>
    </div>
</div>
<!-- Modal for Bill Details -->
<div id="hms-bill-details-modal" class="hms-modal">
    <div class="hms-modal-content">
        <span class="hms-modal-close">&times;</span>
        <div id="hms-bill-details-content"></div>
    </div>
</div>
