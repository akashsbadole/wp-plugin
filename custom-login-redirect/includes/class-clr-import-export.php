<?php
class CLR_Import_Export {
    public function __construct() {
        // No specific hooks needed here, functionality is called from admin pages
    }

    /**
     * Export all redirect rules to a file
     */
    public function export_rules($format = 'json') {
        $data = array(
            'version' => CLR_VERSION,
            'export_date' => current_time('mysql'),
            'rules' => array()
        );
        
        // Get basic redirect rules
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        $basic_rules = $wpdb->get_results("SELECT role, redirect_url FROM $table_name");
        
        foreach ($basic_rules as $rule) {
            $data['rules']['basic'][] = array(
                'type' => 'basic',
                'role' => $rule->role,
                'redirect_url' => $rule->redirect_url
            );
        }
        
        // Get time-based rules if enabled
        if (get_option('clr_time_based_enabled')) {
            $table_name = $wpdb->prefix . 'clr_time_redirects';
            $time_rules = $wpdb->get_results("SELECT * FROM $table_name");
            
            foreach ($time_rules as $rule) {
                $data['rules']['time_based'][] = array(
                    'type' => 'time_based',
                    'day_of_week' => $rule->day_of_week,
                    'start_hour' => $rule->start_hour,
                    'end_hour' => $rule->end_hour,
                    'redirect_url' => $rule->redirect_url,
                    'priority' => $rule->priority
                );
            }
        }
        
        // Get device-based rules if enabled
        if (get_option('clr_device_based_enabled')) {
            $table_name = $wpdb->prefix . 'clr_device_redirects';
            $device_rules = $wpdb->get_results("SELECT * FROM $table_name");
            
            foreach ($device_rules as $rule) {
                $data['rules']['device_based'][] = array(
                    'type' => 'device_based',
                    'device_type' => $rule->device_type,
                    'device_os' => $rule->device_os,
                    'browser' => $rule->browser,
                    'redirect_url' => $rule->redirect_url,
                    'priority' => $rule->priority
                );
            }
        }
        
        // Get referral-based rules if enabled
        if (get_option('clr_referral_based_enabled')) {
            $table_name = $wpdb->prefix . 'clr_referral_redirects';
            $referral_rules = $wpdb->get_results("SELECT * FROM $table_name");
            
            foreach ($referral_rules as $rule) {
                $data['rules']['referral_based'][] = array(
                    'type' => 'referral_based',
                    'source' => $rule->source,
                    'medium' => $rule->medium,
                    'campaign' => $rule->campaign,
                    'redirect_url' => $rule->redirect_url,
                    'priority' => $rule->priority
                );
            }
        }
        
        // Get conditional rules if enabled
        if (get_option('clr_conditional_enabled')) {
            $table_name = $wpdb->prefix . 'clr_conditional_redirects';
            $conditional_rules = $wpdb->get_results("SELECT * FROM $table_name");
            
            foreach ($conditional_rules as $rule) {
                $data['rules']['conditional'][] = array(
                    'type' => 'conditional',
                    'name' => $rule->name,
                    'description' => $rule->description,
                    'conditions' => json_decode($rule->conditions, true),
                    'redirect_url' => $rule->redirect_url,
                    'priority' => $rule->priority,
                    'status' => $rule->status
                );
            }
        }
        
        // Get A/B testing variants if enabled
        if (get_option('clr_ab_enabled')) {
            $table_name = $wpdb->prefix . 'clr_ab_variants';
            $variants = $wpdb->get_results("SELECT * FROM $table_name");
            
            foreach ($variants as $variant) {
                $data['rules']['ab_testing'][] = array(
                    'type' => 'ab_testing',
                    'role' => $variant->role,
                    'redirect_url' => $variant->redirect_url
                );
            }
        }
        
        // Format the output based on the requested format
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
                
            case 'csv':
                return $this->export_to_csv($data);
                
            default:
                return json_encode($data, JSON_PRETTY_PRINT);
        }
    }
    
    /**
     * Convert rules data to CSV format
     */
    private function export_to_csv($data) {
        $output = fopen('php://temp', 'r+');
        
        // Add CSV header
        fputcsv($output, array('Type', 'Role', 'Device Type', 'Device OS', 'Browser', 'Source', 'Medium', 'Campaign', 'Day of Week', 'Start Hour', 'End Hour', 'Name', 'Description', 'Conditions', 'Redirect URL', 'Priority', 'Status'));
        
        // Add basic rules
        if (isset($data['rules']['basic'])) {
            foreach ($data['rules']['basic'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    $rule['role'],
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    $rule['redirect_url'],
                    '', ''
                ));
            }
        }
        
        // Add time-based rules
        if (isset($data['rules']['time_based'])) {
            foreach ($data['rules']['time_based'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    '', '', '', '', '', '', '',
                    $rule['day_of_week'],
                    $rule['start_hour'],
                    $rule['end_hour'],
                    '', '', '', '',
                    $rule['redirect_url'],
                    $rule['priority'],
                    ''
                ));
            }
        }
        
        // Add device-based rules
        if (isset($data['rules']['device_based'])) {
            foreach ($data['rules']['device_based'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    '',
                    $rule['device_type'],
                    $rule['device_os'],
                    $rule['browser'],
                    '', '', '', '', '', '', '', '', '',
                    $rule['redirect_url'],
                    $rule['priority'],
                    ''
                ));
            }
        }
        
        // Add referral-based rules
        if (isset($data['rules']['referral_based'])) {
            foreach ($data['rules']['referral_based'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    '', '', '', '',
                    $rule['source'],
                    $rule['medium'],
                    $rule['campaign'],
                    '', '', '', '', '', '',
                    $rule['redirect_url'],
                    $rule['priority'],
                    ''
                ));
            }
        }
        
        // Add conditional rules
        if (isset($data['rules']['conditional'])) {
            foreach ($data['rules']['conditional'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    '', '', '', '', '', '', '', '', '', '',
                    $rule['name'],
                    $rule['description'],
                    json_encode($rule['conditions']),
                    $rule['redirect_url'],
                    $rule['priority'],
                    $rule['status']
                ));
            }
        }
        
        // Add A/B testing variants
        if (isset($data['rules']['ab_testing'])) {
            foreach ($data['rules']['ab_testing'] as $rule) {
                fputcsv($output, array(
                    $rule['type'],
                    $rule['role'],
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    $rule['redirect_url'],
                    '', ''
                ));
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    /**
     * Import rules from a file
     */
    public function import_rules($file, $format = 'json') {
        $content = file_get_contents($file);
        
        if (!$content) {
            return array(
                'success' => false,
                'message' => __('Could not read file.', 'clr')
            );
        }
        
        $rules_data = array();
        
        // Parse the content based on the format
        switch ($format) {
            case 'json':
                $rules_data = json_decode($content, true);
                
                if (!$rules_data) {
                    return array(
                        'success' => false,
                        'message' => __('Invalid JSON format.', 'clr')
                    );
                }
                break;
                
            case 'csv':
                $rules_data = $this->parse_csv($content);
                
                if (!$rules_data) {
                    return array(
                        'success' => false,
                        'message' => __('Invalid CSV format.', 'clr')
                    );
                }
                break;
                
            default:
                return array(
                    'success' => false,
                    'message' => __('Unsupported format.', 'clr')
                );
        }
        
        // Import the rules
        $import_results = array(
            'success' => true,
            'message' => __('Rules imported successfully.', 'clr'),
            'imported' => array(
                'basic' => 0,
                'time_based' => 0,
                'device_based' => 0,
                'referral_based' => 0,
                'conditional' => 0,
                'ab_testing' => 0
            ),
            'errors' => array()
        );
        
        // Import basic rules
        if (isset($rules_data['rules']['basic'])) {
            foreach ($rules_data['rules']['basic'] as $rule) {
                if ($this->import_basic_rule($rule)) {
                    $import_results['imported']['basic']++;
                } else {
                    $import_results['errors'][] = __('Failed to import basic rule.', 'clr');
                }
            }
        }
        
        // Import time-based rules
        if (isset($rules_data['rules']['time_based'])) {
            foreach ($rules_data['rules']['time_based'] as $rule) {
                if ($this->import_time_based_rule($rule)) {
                    $import_results['imported']['time_based']++;
                } else {
                    $import_results['errors'][] = __('Failed to import time-based rule.', 'clr');
                }
            }
        }
        
        // Import device-based rules
        if (isset($rules_data['rules']['device_based'])) {
            foreach ($rules_data['rules']['device_based'] as $rule) {
                if ($this->import_device_based_rule($rule)) {
                    $import_results['imported']['device_based']++;
                } else {
                    $import_results['errors'][] = __('Failed to import device-based rule.', 'clr');
                }
            }
        }
        
        // Import referral-based rules
        if (isset($rules_data['rules']['referral_based'])) {
            foreach ($rules_data['rules']['referral_based'] as $rule) {
                if ($this->import_referral_based_rule($rule)) {
                    $import_results['imported']['referral_based']++;
                } else {
                    $import_results['errors'][] = __('Failed to import referral-based rule.', 'clr');
                }
            }
        }
        
        // Import conditional rules
        if (isset($rules_data['rules']['conditional'])) {
            foreach ($rules_data['rules']['conditional'] as $rule) {
                if ($this->import_conditional_rule($rule)) {
                    $import_results['imported']['conditional']++;
                } else {
                    $import_results['errors'][] = __('Failed to import conditional rule.', 'clr');
                }
            }
        }
        
        // Import A/B testing variants
        if (isset($rules_data['rules']['ab_testing'])) {
            foreach ($rules_data['rules']['ab_testing'] as $rule) {
                if ($this->import_ab_testing_rule($rule)) {
                    $import_results['imported']['ab_testing']++;
                } else {
                    $import_results['errors'][] = __('Failed to import A/B testing variant.', 'clr');
                }
            }
        }
        
        return $import_results;
    }
    
    /**
     * Parse CSV content to rules data
     */
    private function parse_csv($content) {
        $lines = explode("\n", $content);
        $header = str_getcsv(array_shift($lines));
        
        $data = array(
            'version' => '1.0',
            'export_date' => current_time('mysql'),
            'rules' => array(
                'basic' => array(),
                'time_based' => array(),
                'device_based' => array(),
                'referral_based' => array(),
                'conditional' => array(),
                'ab_testing' => array()
            )
        );
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $values = str_getcsv($line);
            $row = array_combine($header, $values);
            
            $type = $row['Type'];
            
            switch ($type) {
                case 'basic':
                    $data['rules']['basic'][] = array(
                        'type' => 'basic',
                        'role' => $row['Role'],
                        'redirect_url' => $row['Redirect URL']
                    );
                    break;
                    
                case 'time_based':
                    $data['rules']['time_based'][] = array(
                        'type' => 'time_based',
                        'day_of_week' => $row['Day of Week'],
                        'start_hour' => $row['Start Hour'],
                        'end_hour' => $row['End Hour'],
                        'redirect_url' => $row['Redirect URL'],
                        'priority' => $row['Priority']
                    );
                    break;
                    
                case 'device_based':
                    $data['rules']['device_based'][] = array(
                        'type' => 'device_based',
                        'device_type' => $row['Device Type'],
                        'device_os' => $row['Device OS'],
                        'browser' => $row['Browser'],
                        'redirect_url' => $row['Redirect URL'],
                        'priority' => $row['Priority']
                    );
                    break;
                    
                case 'referral_based':
                    $data['rules']['referral_based'][] = array(
                        'type' => 'referral_based',
                        'source' => $row['Source'],
                        'medium' => $row['Medium'],
                        'campaign' => $row['Campaign'],
                        'redirect_url' => $row['Redirect URL'],
                        'priority' => $row['Priority']
                    );
                    break;
                    
                case 'conditional':
                    $conditions = json_decode($row['Conditions'], true);
                    $data['rules']['conditional'][] = array(
                        'type' => 'conditional',
                        'name' => $row['Name'],
                        'description' => $row['Description'],
                        'conditions' => $conditions,
                        'redirect_url' => $row['Redirect URL'],
                        'priority' => $row['Priority'],
                        'status' => $row['Status']
                    );
                    break;
                    
                case 'ab_testing':
                    $data['rules']['ab_testing'][] = array(
                        'type' => 'ab_testing',
                        'role' => $row['Role'],
                        'redirect_url' => $row['Redirect URL']
                    );
                    break;
            }
        }
        
        return $data;
    }
    
    /**
     * Import a basic rule
     */
    private function import_basic_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        
        return $wpdb->insert(
            $table_name,
            array(
                'role' => $rule['role'],
                'redirect_url' => $rule['redirect_url'],
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Import a time-based rule
     */
    private function import_time_based_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'day_of_week' => $rule['day_of_week'],
                'start_hour' => $rule['start_hour'],
                'end_hour' => $rule['end_hour'],
                'redirect_url' => $rule['redirect_url'],
                'priority' => $rule['priority'],
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Import a device-based rule
     */
    private function import_device_based_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'device_type' => $rule['device_type'],
                'device_os' => $rule['device_os'],
                'browser' => $rule['browser'],
                'redirect_url' => $rule['redirect_url'],
                'priority' => $rule['priority'],
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Import a referral-based rule
     */
    private function import_referral_based_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'source' => $rule['source'],
                'medium' => $rule['medium'],
                'campaign' => $rule['campaign'],
                'redirect_url' => $rule['redirect_url'],
                'priority' => $rule['priority'],
                'created_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Import a conditional rule
     */
    private function import_conditional_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        return $wpdb->insert(
            $table_name,
            array(
                'name' => $rule['name'],
                'description' => $rule['description'],
                'conditions' => json_encode($rule['conditions']),
                'redirect_url' => $rule['redirect_url'],
                'priority' => $rule['priority'],
                'status' => $rule['status'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            )
        );
    }
    
    /**
     * Import an A/B testing variant
     */
    private function import_ab_testing_rule($rule) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_ab_variants';
        
        return $wpdb->insert(
            $table_name,
            array(
                'role' => $rule['role'],
                'redirect_url' => $rule['redirect_url']
            )
        );
    }
}