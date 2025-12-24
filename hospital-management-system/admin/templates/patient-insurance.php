<div class="wrap">
    <h1><?php _e('Patient Insurance Coverage', 'hospital-management-system'); ?></h1>

    <?php
    // Display any notices or messages
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'success';
        echo '<div id="message" class="notice notice-' . $type . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }
    ?>
    
    <form method="get">
        <input type="hidden" name="page" value="hms_patient_insurance">
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <label for="filter-patient" class="screen-reader-text"><?php _e('Filter by Patient', 'hospital-management-system'); ?></label>
                <input type="text" name="patient_name" id="filter-patient" value="<?php echo isset($_GET['patient_name']) ? esc_attr($_GET['patient_name']) : ''; ?>" placeholder="<?php _e('Patient Name', 'hospital-management-system'); ?>">

                <label for="filter-provider" class="screen-reader-text"><?php _e('Filter by Provider', 'hospital-management-system'); ?></label>
                <select name="provider_id" id="filter-provider">
                    <option value=""><?php _e('All Providers', 'hospital-management-system'); ?></option>
                    <?php
                    global $wpdb;
                    $providers_table = $wpdb->prefix . 'hms_insurance_providers';
                    $providers = $wpdb->get_results("SELECT id, name FROM $providers_table WHERE status = 'active' ORDER BY name ASC");
                    foreach ($providers as $provider) {
                        $selected = isset($_GET['provider_id']) && $_GET['provider_id'] == $provider->id ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr($provider->id) . '" ' . $selected . '>' . esc_html($provider->name) . '</option>';
                    }
                    ?>
                </select>

                <input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _e('Filter', 'hospital-management-system'); ?>">
            </div>
            <br class="clear">
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list patient-insurance-table">
            <thead>
                <tr>
                    <th scope="col" id="patient" class="manage-column column-patient column-primary"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" id="provider" class="manage-column column-provider"><?php _e('Insurance Provider', 'hospital-management-system'); ?></th>
                    <th scope="col" id="policy_number" class="manage-column column-policy_number"><?php _e('Policy Number', 'hospital-management-system'); ?></th>
                    <th scope="col" id="coverage_type" class="manage-column column-coverage_type"><?php _e('Coverage Type', 'hospital-management-system'); ?></th>
                    <th scope="col" id="coverage_limit" class="manage-column column-coverage_limit"><?php _e('Coverage Limit', 'hospital-management-system'); ?></th>
                    <th scope="col" id="status" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" id="actions" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </thead>

            <tbody id="the-list">
                <?php
                // For now, we'll create a simple view showing patients and their potential insurance coverage
                // In a full implementation, this would be a separate table for patient insurance coverage
                
                $where_clauses = array();
                $prepare_values = array();

                if (!empty($_GET['patient_name'])) {
                    $where_clauses[] = "(p.first_name LIKE %s OR p.last_name LIKE %s)";
                    $search_term = '%' . $wpdb->esc_like(sanitize_text_field($_GET['patient_name'])) . '%';
                    $prepare_values[] = $search_term;
                    $prepare_values[] = $search_term;
                }

                $where_sql = '';
                if (!empty($where_clauses)) {
                    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
                }

                $patients_table = $wpdb->prefix . 'hms_patients';
                
                // Get patients and their claims to show insurance relationships
                $query = "SELECT DISTINCT p.id, p.first_name, p.last_name, p.email, p.phone
                         FROM $patients_table p 
                         $where_sql 
                         ORDER BY p.first_name ASC 
                         LIMIT 50";
                
                if (!empty($prepare_values)) {
                    $query = $wpdb->prepare($query, $prepare_values);
                }

                $patients = $wpdb->get_results($query);

                if ($patients) {
                    foreach ($patients as $patient) {
                        // Get insurance claims for this patient to show coverage
                        $claims_table = $wpdb->prefix . 'hms_claims';
                        $providers_table = $wpdb->prefix . 'hms_insurance_providers';
                        
                        $patient_claims = $wpdb->get_results($wpdb->prepare("
                            SELECT DISTINCT c.insurance_provider_id, c.policy_number, pr.name as provider_name
                            FROM $claims_table c 
                            LEFT JOIN $providers_table pr ON c.insurance_provider_id = pr.id 
                            WHERE c.patient_id = %d
                        ", $patient->id));
                        
                        if ($patient_claims) {
                            foreach ($patient_claims as $claim) {
                                echo '<tr>';
                                echo '<td class="column-patient column-primary">' . esc_html($patient->first_name . ' ' . $patient->last_name) . '</td>';
                                echo '<td class="column-provider">' . esc_html($claim->provider_name) . '</td>';
                                echo '<td class="column-policy_number">' . esc_html($claim->policy_number) . '</td>';
                                echo '<td class="column-coverage_type">' . __('General Coverage', 'hospital-management-system') . '</td>';
                                echo '<td class="column-coverage_limit">' . __('As per policy', 'hospital-management-system') . '</td>';
                                echo '<td class="column-status"><span class="badge badge-success">' . __('Active', 'hospital-management-system') . '</span></td>';
                                echo '<td class="column-actions">';
                                echo '<a href="' . esc_url(admin_url('admin.php?page=hms_insurance_claims&patient_id=' . $patient->id)) . '" class="button button-small">' . __('View Claims', 'hospital-management-system') . '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            // Show patients without insurance coverage
                            echo '<tr>';
                            echo '<td class="column-patient column-primary">' . esc_html($patient->first_name . ' ' . $patient->last_name) . '</td>';
                            echo '<td class="column-provider">' . __('No Coverage', 'hospital-management-system') . '</td>';
                            echo '<td class="column-policy_number">-</td>';
                            echo '<td class="column-coverage_type">-</td>';
                            echo '<td class="column-coverage_limit">-</td>';
                            echo '<td class="column-status"><span class="badge badge-secondary">' . __('No Coverage', 'hospital-management-system') . '</span></td>';
                            echo '<td class="column-actions">';
                            echo '<a href="' . esc_url(admin_url('admin.php?page=hms_claim_details&patient_id=' . $patient->id)) . '" class="button button-small">' . __('Add Coverage', 'hospital-management-system') . '</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                } else {
                    echo '<tr><td colspan="7">' . __('No patients found.', 'hospital-management-system') . '</td></tr>';
                }
                ?>
            </tbody>

            <tfoot>
                <tr>
                    <th scope="col" class="manage-column column-patient"><?php _e('Patient', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-provider"><?php _e('Insurance Provider', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-policy_number"><?php _e('Policy Number', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-coverage_type"><?php _e('Coverage Type', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-coverage_limit"><?php _e('Coverage Limit', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'hospital-management-system'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'hospital-management-system'); ?></th>
                </tr>
            </tfoot>
        </table>
    </form>

    <div class="postbox" style="margin-top: 20px;">
        <h2 class="hndle ui-sortable-handle"><span><?php _e('Insurance Coverage Summary', 'hospital-management-system'); ?></span></h2>
        <div class="inside">
            <?php
            // Get coverage statistics
            $total_patients = $wpdb->get_var("SELECT COUNT(*) FROM $patients_table");
            $patients_with_claims = $wpdb->get_var("SELECT COUNT(DISTINCT patient_id) FROM $claims_table");
            $patients_without_coverage = $total_patients - $patients_with_claims;
            
            echo '<div class="hms-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">';
            
            echo '<div class="hms-stat-card" style="background: #f9f9f9; padding: 20px; border-radius: 5px; text-align: center;">';
            echo '<h3 style="margin: 0; color: #0073aa;">' . number_format($total_patients) . '</h3>';
            echo '<p style="margin: 5px 0 0 0;">' . __('Total Patients', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            echo '<div class="hms-stat-card" style="background: #f9f9f9; padding: 20px; border-radius: 5px; text-align: center;">';
            echo '<h3 style="margin: 0; color: #28a745;">' . number_format($patients_with_claims) . '</h3>';
            echo '<p style="margin: 5px 0 0 0;">' . __('Patients with Coverage', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            echo '<div class="hms-stat-card" style="background: #f9f9f9; padding: 20px; border-radius: 5px; text-align: center;">';
            echo '<h3 style="margin: 0; color: #dc3545;">' . number_format($patients_without_coverage) . '</h3>';
            echo '<p style="margin: 5px 0 0 0;">' . __('Patients without Coverage', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            $coverage_percentage = $total_patients > 0 ? round(($patients_with_claims / $total_patients) * 100, 1) : 0;
            echo '<div class="hms-stat-card" style="background: #f9f9f9; padding: 20px; border-radius: 5px; text-align: center;">';
            echo '<h3 style="margin: 0; color: #17a2b8;">' . $coverage_percentage . '%</h3>';
            echo '<p style="margin: 5px 0 0 0;">' . __('Coverage Rate', 'hospital-management-system') . '</p>';
            echo '</div>';
            
            echo '</div>';
            ?>
        </div>
    </div>
</div>

<style type="text/css">
    .badge {
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
        color: #fff;
    }
    .badge-success { background-color: #28a745; }
    .badge-info { background-color: #17a2b8; }
    .badge-warning { background-color: #ffc107; color: #212529; }
    .badge-danger { background-color: #dc3545; }
    .badge-secondary { background-color: #6c757d; }
</style>