<?php
class CLR_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers for basic redirect rules
        add_action('wp_ajax_clr_load_rules', array($this, 'ajax_load_rules'));
        add_action('wp_ajax_clr_save_rule', array($this, 'ajax_save_rule'));
        add_action('wp_ajax_clr_delete_rule', array($this, 'ajax_delete_rule'));
        
        // AJAX handlers for A/B testing
        add_action('wp_ajax_clr_load_ab_variants', array($this, 'ajax_load_ab_variants'));
        add_action('wp_ajax_clr_load_ab_results', array($this, 'ajax_load_ab_results'));
        add_action('wp_ajax_clr_save_ab_variant', array($this, 'ajax_save_ab_variant'));
        add_action('wp_ajax_clr_delete_ab_variant', array($this, 'ajax_delete_ab_variant'));
        
        // AJAX handlers for Time-Based redirects
        add_action('wp_ajax_clr_load_time_rules', array($this, 'ajax_load_time_rules'));
        add_action('wp_ajax_clr_save_time_rule', array($this, 'ajax_save_time_rule'));
        add_action('wp_ajax_clr_delete_time_rule', array($this, 'ajax_delete_time_rule'));
        
        // AJAX handlers for Device-Based redirects
        add_action('wp_ajax_clr_load_device_rules', array($this, 'ajax_load_device_rules'));
        add_action('wp_ajax_clr_save_device_rule', array($this, 'ajax_save_device_rule'));
        add_action('wp_ajax_clr_delete_device_rule', array($this, 'ajax_delete_device_rule'));
        
        // AJAX handlers for Referral-Based redirects
        add_action('wp_ajax_clr_load_referral_rules', array($this, 'ajax_load_referral_rules'));
        add_action('wp_ajax_clr_save_referral_rule', array($this, 'ajax_save_referral_rule'));
        add_action('wp_ajax_clr_delete_referral_rule', array($this, 'ajax_delete_referral_rule'));
        
        // AJAX handlers for Analytics
        add_action('wp_ajax_clr_load_analytics', array($this, 'ajax_load_analytics'));
        add_action('wp_ajax_clr_load_user_analytics', array($this, 'ajax_load_user_analytics'));
        
        // AJAX handlers for Conditional redirects
        add_action('wp_ajax_clr_load_conditional_rules', array($this, 'ajax_load_conditional_rules'));
        add_action('wp_ajax_clr_save_conditional_rule', array($this, 'ajax_save_conditional_rule'));
        add_action('wp_ajax_clr_delete_conditional_rule', array($this, 'ajax_delete_conditional_rule'));
        add_action('wp_ajax_clr_toggle_conditional_rule', array($this, 'ajax_toggle_conditional_rule'));
        add_action('wp_ajax_clr_test_conditional_rules', array($this, 'ajax_test_conditional_rules'));
        
        // AJAX handlers for Import/Export
        add_action('wp_ajax_clr_export_rules', array($this, 'ajax_export_rules'));
        add_action('wp_ajax_clr_import_rules', array($this, 'ajax_import_rules'));
        add_action('wp_ajax_clr_download_template', array($this, 'ajax_download_template'));
        
        // AJAX handlers for Email Notifications
        add_action('wp_ajax_clr_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_clr_load_notification_history', array($this, 'ajax_load_notification_history'));
        
        // AJAX handler for license activation
        add_action('wp_ajax_clr_activate_license', 'clr_activate_license');
        
        // AJAX handlers for premium analytics
        add_action('wp_ajax_clr_track_interaction', array($this, 'ajax_track_interaction'));
        add_action('wp_ajax_clr_load_heatmap_data', array($this, 'ajax_load_heatmap_data'));
        add_action('wp_ajax_clr_load_user_journey', array($this, 'ajax_load_user_journey'));
        add_action('wp_ajax_clr_load_conversion_funnel', array($this, 'ajax_load_conversion_funnel'));
        add_action('wp_ajax_clr_load_custom_reports', array($this, 'ajax_load_custom_reports'));
        add_action('wp_ajax_clr_save_custom_report', array($this, 'ajax_save_custom_report'));
        add_action('wp_ajax_clr_delete_custom_report', array($this, 'ajax_delete_custom_report'));
        add_action('wp_ajax_clr_load_custom_report', array($this, 'ajax_load_custom_report'));
        add_action('wp_ajax_clr_export_report', array($this, 'ajax_export_report'));
        add_action('wp_ajax_clr_check_real_time_data', array($this, 'ajax_check_real_time_data'));
        add_action('wp_ajax_clr_connect_ga', array($this, 'ajax_connect_ga'));
        add_action('wp_ajax_clr_disconnect_ga', array($this, 'ajax_disconnect_ga'));
        add_action('wp_ajax_clr_connect_mailchimp', array($this, 'ajax_connect_mailchimp'));
        add_action('wp_ajax_clr_disconnect_mailchimp', array($this, 'ajax_disconnect_mailchimp'));
        add_action('wp_ajax_clr_connect_slack', array($this, 'ajax_connect_slack'));
        add_action('wp_ajax_clr_disconnect_slack', array($this, 'ajax_disconnect_slack'));
    }

    public function add_menu_page() {
        add_menu_page(
            __('Login Redirect', 'clr'),
            __('Login Redirect', 'clr'),
            'manage_options',
            'clr-settings',
            array($this, 'render_settings_page'),
            'dashicons-admin-generic'
        );
        
        add_submenu_page(
            'clr-settings',
            __('Redirect Rules', 'clr'),
            __('Redirect Rules', 'clr'),
            'manage_options',
            'clr-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('A/B Testing', 'clr'),
            __('A/B Testing', 'clr'),
            'manage_options',
            'clr-ab-testing',
            array($this, 'render_ab_testing_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Time-Based Redirects', 'clr'),
            __('Time-Based', 'clr'),
            'manage_options',
            'clr-time-based',
            array($this, 'render_time_based_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Device-Based Redirects', 'clr'),
            __('Device-Based', 'clr'),
            'manage_options',
            'clr-device-based',
            array($this, 'render_device_based_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Referral-Based Redirects', 'clr'),
            __('Referral-Based', 'clr'),
            'manage_options',
            'clr-referral-based',
            array($this, 'render_referral_based_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('User Behavior Analytics', 'clr'),
            __('Analytics', 'clr'),
            'manage_options',
            'clr-analytics',
            array($this, 'render_analytics_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Conditional Redirects', 'clr'),
            __('Conditional', 'clr'),
            'manage_options',
            'clr-conditional',
            array($this, 'render_conditional_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Bulk Import/Export', 'clr'),
            __('Import/Export', 'clr'),
            'manage_options',
            'clr-import-export',
            array($this, 'render_import_export_page')
        );
        
        add_submenu_page(
            'clr-settings',
            __('Email Notifications', 'clr'),
            __('Email', 'clr'),
            'manage_options',
            'clr-email-notifications',
            array($this, 'render_email_notifications_page')
        );
        
        // Add premium analytics menu if premium features are enabled
        if (clr_is_premium()) {
            add_submenu_page(
                'clr-settings',
                __('Premium Analytics', 'clr'),
                __('Premium Analytics', 'clr'),
                'manage_options',
                'clr-premium-analytics',
                array($this, 'render_premium_analytics_page')
            );
        }
    }

    public function register_settings() {
        register_setting('clr_settings', 'clr_ai_enabled');
        register_setting('clr_settings', 'clr_geo_enabled');
        register_setting('clr_settings', 'clr_geo_api_token');
        register_setting('clr_settings', 'clr_license_key');
        register_setting('clr_settings', 'clr_ab_enabled');
        register_setting('clr_settings', 'clr_page_builder_enabled');
        register_setting('clr_settings', 'clr_time_based_enabled');
        register_setting('clr_settings', 'clr_device_based_enabled');
        register_setting('clr_settings', 'clr_referral_based_enabled');
        register_setting('clr_settings', 'clr_analytics_enabled');
        register_setting('clr_settings', 'clr_conditional_enabled');
        register_setting('clr_email_notifications', 'clr_email_notifications_enabled');
        register_setting('clr_email_notifications', 'clr_notification_events');
        register_setting('clr_email_notifications', 'clr_notification_email');
        register_setting('clr_email_notifications', 'clr_email_template');
        register_setting('clr_email_notifications', 'clr_email_custom_template');
        register_setting('clr_settings', 'clr_header_code');
        register_setting('clr_settings', 'clr_footer_code');
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'clr') !== false) {
            wp_enqueue_style('clr-admin', CLR_PLUGIN_URL . 'assets/css/admin.css', array(), CLR_VERSION);
            wp_enqueue_script('clr-admin', CLR_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CLR_VERSION, true);
            
            wp_localize_script('clr-admin', 'clrAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('clr_admin_nonce')
            ));
            
            // Add body class for premium analytics page
            if ($hook === 'custom-login-redirect_page_clr-premium-analytics') {
                add_filter('admin_body_class', function($classes) {
                    return $classes . ' clr-premium-analytics-page';
                });
            }
        }
    }

    public function render_settings_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/settings-page.php';
    }

    public function render_ab_testing_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/ab-testing-page.php';
    }
    
    public function render_time_based_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/time-based-page.php';
    }
    
    public function render_device_based_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/device-based-page.php';
    }
    
    public function render_referral_based_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/referral-based-page.php';
    }
    
    public function render_analytics_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/analytics-page.php';
    }
    
    public function render_conditional_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/conditional-page.php';
    }
    
    public function render_import_export_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/import-export-page.php';
    }
    
    public function render_email_notifications_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/email-notifications-page.php';
    }
    
    public function render_premium_analytics_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/premium-analytics-page.php';
    }
    
    /**
     * AJAX handler to load basic redirect rules
     */
    public function ajax_load_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        $rules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY role, id");
        
        if (empty($rules)) {
            echo '<div class="notice notice-info"><p>' . __('No redirect rules found. Add your first rule to get started.', 'clr') . '</p></div>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . __('ID', 'clr') . '</th>';
            echo '<th>' . __('Role', 'clr') . '</th>';
            echo '<th>' . __('Redirect URL', 'clr') . '</th>';
            echo '<th>' . __('Conditions', 'clr') . '</th>';
            echo '<th>' . __('Actions', 'clr') . '</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($rules as $rule) {
                echo '<tr>';
                echo '<td>' . esc_html($rule->id) . '</td>';
                echo '<td>' . esc_html($rule->role) . '</td>';
                echo '<td>' . esc_html($rule->redirect_url) . '</td>';
                echo '<td>' . (!empty($rule->conditions) ? esc_html($rule->conditions) : '-') . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-rule" data-id="' . esc_attr($rule->id) . '" data-role="' . esc_attr($rule->role) . '" data-url="' . esc_attr($rule->redirect_url) . '" data-conditions="' . esc_attr($rule->conditions) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-rule" data-id="' . esc_attr($rule->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save basic redirect rule
     */
    public function ajax_save_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $role = sanitize_text_field($_POST['role']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $conditions = isset($_POST['conditions']) ? sanitize_textarea_field($_POST['conditions']) : '';
        
        if (empty($role) || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all required fields.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        
        if ($rule_id > 0) {
            // Update existing rule
            $result = $wpdb->update(
                $table_name,
                array(
                    'role' => $role,
                    'redirect_url' => $redirect_url,
                    'conditions' => $conditions
                ),
                array('id' => $rule_id)
            );
        } else {
            // Add new rule
            $result = $wpdb->insert(
                $table_name,
                array(
                    'role' => $role,
                    'redirect_url' => $redirect_url,
                    'conditions' => $conditions,
                    'created_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete basic redirect rule
     */
    public function ajax_delete_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_redirect_rules';
        
        // Delete the rule
        $result = $wpdb->delete($table_name, array('id' => $rule_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load A/B testing variants
     */
    public function ajax_load_ab_variants() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_ab_variants';
        $variants = $wpdb->get_results("SELECT * FROM $table_name ORDER BY role, id");
        
        if (empty($variants)) {
            echo '<tr><td colspan="4">' . __('No variants found. Add your first variant to get started.', 'clr') . '</td></tr>';
        } else {
            foreach ($variants as $variant) {
                echo '<tr>';
                echo '<td>' . esc_html($variant->id) . '</td>';
                echo '<td>' . esc_html($variant->role) . '</td>';
                echo '<td>' . esc_html($variant->redirect_url) . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-variant" data-id="' . esc_attr($variant->id) . '" data-role="' . esc_attr($variant->role) . '" data-url="' . esc_attr($variant->redirect_url) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-variant" data-id="' . esc_attr($variant->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to load A/B testing results
     */
    public function ajax_load_ab_results() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $variants_table = $wpdb->prefix . 'clr_ab_variants';
        $tests_table = $wpdb->prefix . 'clr_ab_tests';
        
        $results = $wpdb->get_results("
            SELECT v.id, v.role, v.redirect_url,
                   COUNT(t.id) as total_tests,
                   SUM(t.converted) as conversions
            FROM $variants_table v
            LEFT JOIN $tests_table t ON v.id = t.variant_id
            GROUP BY v.id
            ORDER BY v.role, v.id
        ");
        
        if (empty($results)) {
            echo '<tr><td colspan="6">' . __('No test results available yet.', 'clr') . '</td></tr>';
        } else {
            foreach ($results as $result) {
                $conversion_rate = $result->total_tests > 0 ? ($result->conversions / $result->total_tests) * 100 : 0;
                $rate_class = $conversion_rate >= 5 ? 'high' : ($conversion_rate >= 2 ? 'medium' : 'low');
                
                echo '<tr>';
                echo '<td>' . esc_html($result->id) . '</td>';
                echo '<td>' . esc_html($result->role) . '</td>';
                echo '<td>' . esc_html($result->redirect_url) . '</td>';
                echo '<td>' . esc_html($result->total_tests) . '</td>';
                echo '<td>' . esc_html($result->conversions) . '</td>';
                echo '<td><span class="clr-conversion-rate ' . esc_attr($rate_class) . '">' . number_format($conversion_rate, 2) . '%</span></td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save A/B testing variant
     */
    public function ajax_save_ab_variant() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
        $role = sanitize_text_field($_POST['role']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        
        if (empty($role) || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all fields.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_ab_variants';
        
        if ($variant_id > 0) {
            // Update existing variant
            $result = $wpdb->update(
                $table_name,
                array(
                    'role' => $role,
                    'redirect_url' => $redirect_url
                ),
                array('id' => $variant_id)
            );
        } else {
            // Add new variant
            $result = $wpdb->insert(
                $table_name,
                array(
                    'role' => $role,
                    'redirect_url' => $redirect_url
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving variant.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete A/B testing variant
     */
    public function ajax_delete_ab_variant() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $variant_id = intval($_POST['variant_id']);
        
        if ($variant_id <= 0) {
            wp_send_json_error(__('Invalid variant ID.', 'clr'));
        }
        
        global $wpdb;
        $variants_table = $wpdb->prefix . 'clr_ab_variants';
        $tests_table = $wpdb->prefix . 'clr_ab_tests';
        
        // Delete the variant
        $result = $wpdb->delete($variants_table, array('id' => $variant_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting variant.', 'clr'));
        }
        
        // Also delete any test records associated with this variant
        $wpdb->delete($tests_table, array('variant_id' => $variant_id));
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load time-based rules
     */
    public function ajax_load_time_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        $rules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, day_of_week, start_hour");
        
        if (empty($rules)) {
            echo '<tr><td colspan="6">' . __('No time-based rules found. Add your first rule to get started.', 'clr') . '</td></tr>';
        } else {
            $days = array(
                'any' => __('Any Day', 'clr'),
                'monday' => __('Monday', 'clr'),
                'tuesday' => __('Tuesday', 'clr'),
                'wednesday' => __('Wednesday', 'clr'),
                'thursday' => __('Thursday', 'clr'),
                'friday' => __('Friday', 'clr'),
                'saturday' => __('Saturday', 'clr'),
                'sunday' => __('Sunday', 'clr')
            );
            
            foreach ($rules as $rule) {
                echo '<tr>';
                echo '<td>' . esc_html($rule->id) . '</td>';
                echo '<td>' . esc_html($days[$rule->day_of_week]) . '</td>';
                echo '<td>' . esc_html($rule->start_hour) . ':00 - ' . esc_html($rule->end_hour) . ':00</td>';
                echo '<td>' . esc_html($rule->redirect_url) . '</td>';
                echo '<td>' . esc_html($rule->priority) . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-time-rule" data-id="' . esc_attr($rule->id) . '" data-day="' . esc_attr($rule->day_of_week) . '" data-start="' . esc_attr($rule->start_hour) . '" data-end="' . esc_attr($rule->end_hour) . '" data-url="' . esc_attr($rule->redirect_url) . '" data-priority="' . esc_attr($rule->priority) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-time-rule" data-id="' . esc_attr($rule->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save time-based rule
     */
    public function ajax_save_time_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $day_of_week = sanitize_text_field($_POST['day_of_week']);
        $start_hour = intval($_POST['start_hour']);
        $end_hour = intval($_POST['end_hour']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $priority = intval($_POST['priority']);
        
        if (empty($day_of_week) || $start_hour < 0 || $start_hour > 23 || $end_hour < 0 || $end_hour > 23 || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all fields correctly.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        if ($rule_id > 0) {
            // Update existing rule
            $result = $wpdb->update(
                $table_name,
                array(
                    'day_of_week' => $day_of_week,
                    'start_hour' => $start_hour,
                    'end_hour' => $end_hour,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority
                ),
                array('id' => $rule_id)
            );
        } else {
            // Add new rule
            $result = $wpdb->insert(
                $table_name,
                array(
                    'day_of_week' => $day_of_week,
                    'start_hour' => $start_hour,
                    'end_hour' => $end_hour,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority,
                    'created_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete time-based rule
     */
    public function ajax_delete_time_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_time_redirects';
        
        // Delete the rule
        $result = $wpdb->delete($table_name, array('id' => $rule_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load device-based rules
     */
    public function ajax_load_device_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        $rules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, device_type, device_os, browser");
        
        if (empty($rules)) {
            echo '<tr><td colspan="7">' . __('No device-based rules found. Add your first rule to get started.', 'clr') . '</td></tr>';
        } else {
            $device_types = array(
                'any' => __('Any Device', 'clr'),
                'desktop' => __('Desktop', 'clr'),
                'mobile' => __('Mobile', 'clr'),
                'tablet' => __('Tablet', 'clr')
            );
            
            $device_os = array(
                'any' => __('Any OS', 'clr'),
                'windows' => __('Windows', 'clr'),
                'mac' => __('Mac', 'clr'),
                'linux' => __('Linux', 'clr'),
                'android' => __('Android', 'clr'),
                'ios' => __('iOS', 'clr')
            );
            
            $browsers = array(
                'any' => __('Any Browser', 'clr'),
                'chrome' => __('Chrome', 'clr'),
                'firefox' => __('Firefox', 'clr'),
                'safari' => __('Safari', 'clr'),
                'edge' => __('Edge', 'clr'),
                'opera' => __('Opera', 'clr'),
                'ie' => __('Internet Explorer', 'clr')
            );
            
            foreach ($rules as $rule) {
                echo '<tr>';
                echo '<td>' . esc_html($rule->id) . '</td>';
                echo '<td>' . esc_html($device_types[$rule->device_type]) . '</td>';
                echo '<td>' . esc_html($device_os[$rule->device_os]) . '</td>';
                echo '<td>' . esc_html($browsers[$rule->browser]) . '</td>';
                echo '<td>' . esc_html($rule->redirect_url) . '</td>';
                echo '<td>' . esc_html($rule->priority) . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-device-rule" data-id="' . esc_attr($rule->id) . '" data-type="' . esc_attr($rule->device_type) . '" data-os="' . esc_attr($rule->device_os) . '" data-browser="' . esc_attr($rule->browser) . '" data-url="' . esc_attr($rule->redirect_url) . '" data-priority="' . esc_attr($rule->priority) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-device-rule" data-id="' . esc_attr($rule->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save device-based rule
     */
    public function ajax_save_device_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $device_type = sanitize_text_field($_POST['device_type']);
        $device_os = sanitize_text_field($_POST['device_os']);
        $browser = sanitize_text_field($_POST['browser']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $priority = intval($_POST['priority']);
        
        if (empty($device_type) || empty($device_os) || empty($browser) || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all fields.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        if ($rule_id > 0) {
            // Update existing rule
            $result = $wpdb->update(
                $table_name,
                array(
                    'device_type' => $device_type,
                    'device_os' => $device_os,
                    'browser' => $browser,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority
                ),
                array('id' => $rule_id)
            );
        } else {
            // Add new rule
            $result = $wpdb->insert(
                $table_name,
                array(
                    'device_type' => $device_type,
                    'device_os' => $device_os,
                    'browser' => $browser,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority,
                    'created_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete device-based rule
     */
    public function ajax_delete_device_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_device_redirects';
        
        // Delete the rule
        $result = $wpdb->delete($table_name, array('id' => $rule_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load referral-based rules
     */
    public function ajax_load_referral_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        $rules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, source, medium, campaign");
        
        if (empty($rules)) {
            echo '<tr><td colspan="7">' . __('No referral-based rules found. Add your first rule to get started.', 'clr') . '</td></tr>';
        } else {
            foreach ($rules as $rule) {
                echo '<tr>';
                echo '<td>' . esc_html($rule->id) . '</td>';
                echo '<td>' . esc_html($rule->source) . '</td>';
                echo '<td>' . esc_html($rule->medium) . '</td>';
                echo '<td>' . esc_html($rule->campaign) . '</td>';
                echo '<td>' . esc_html($rule->redirect_url) . '</td>';
                echo '<td>' . esc_html($rule->priority) . '</td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-referral-rule" data-id="' . esc_attr($rule->id) . '" data-source="' . esc_attr($rule->source) . '" data-medium="' . esc_attr($rule->medium) . '" data-campaign="' . esc_attr($rule->campaign) . '" data-url="' . esc_attr($rule->redirect_url) . '" data-priority="' . esc_attr($rule->priority) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-referral-rule" data-id="' . esc_attr($rule->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save referral-based rule
     */
    public function ajax_save_referral_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $source = sanitize_text_field($_POST['source']);
        $medium = sanitize_text_field($_POST['medium']);
        $campaign = sanitize_text_field($_POST['campaign']);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $priority = intval($_POST['priority']);
        
        if (empty($source) || empty($medium) || empty($campaign) || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all fields.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        if ($rule_id > 0) {
            // Update existing rule
            $result = $wpdb->update(
                $table_name,
                array(
                    'source' => $source,
                    'medium' => $medium,
                    'campaign' => $campaign,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority
                ),
                array('id' => $rule_id)
            );
        } else {
            // Add new rule
            $result = $wpdb->insert(
                $table_name,
                array(
                    'source' => $source,
                    'medium' => $medium,
                    'campaign' => $campaign,
                    'redirect_url' => $redirect_url,
                    'priority' => $priority,
                    'created_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete referral-based rule
     */
    public function ajax_delete_referral_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_referral_redirects';
        
        // Delete the rule
        $result = $wpdb->delete($table_name, array('id' => $rule_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load analytics data
     */
    public function ajax_load_analytics() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        $analytics = new CLR_Analytics();
        $data = $analytics->get_redirect_analytics($days);
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX handler to track user interactions
     */
    public function ajax_track_interaction() {
        check_ajax_referer('clr_track_nonce', 'nonce');
        
        if (!clr_is_premium()) {
            wp_die();
        }
        
        $user_id = intval($_POST['user_id']);
        $session_id = sanitize_text_field($_POST['session_id']);
        $event_type = sanitize_text_field($_POST['event_type']);
        $event_data = json_decode(stripslashes($_POST['event_data']), true);
        
        if (empty($user_id) || empty($session_id) || empty($event_type)) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists != $table_name) {
            wp_die();
        }
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'event_type' => 'interaction_' . $event_type,
                'event_data' => json_encode($event_data),
                'timestamp' => current_time('mysql'),
                'ip_address' => $this->get_user_ip()
            )
        );
        
        wp_die();
    }
    
    /**
     * AJAX handler to load heatmap data
     */
    public function ajax_load_heatmap_data() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $post_id = intval($_POST['post_id']);
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        if (!$post_id) {
            wp_send_json_error(__('Invalid post ID.', 'clr'));
        }
        
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Get click events for this post
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $click_events = $wpdb->get_results($wpdb->prepare(
            "SELECT event_data FROM $events_table
            WHERE event_type = 'interaction_click'
            AND timestamp >= %s",
            $start_date
        ));
        
        $heatmap_data = array();
        
        foreach ($click_events as $event) {
            $event_data = json_decode($event->event_data, true);
            
            // Only include events that have valid data
            if (isset($event_data['href']) && strpos($event_data['href'], '?p=' . $post_id) !== false) {
                $heatmap_data[] = array(
                    'x' => isset($event_data['x']) ? intval($event_data['x']) : 0,
                    'y' => isset($event_data['y']) ? intval($event_data['y']) : 0,
                    'element' => $event_data['element'],
                    'text' => $event_data['text']
                );
            }
        }
        
        wp_send_json_success($heatmap_data);
    }
    
    /**
     * AJAX handler to load user journey
     */
    public function ajax_load_user_journey() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $user_id = intval($_POST['user_id']);
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        if (!$user_id) {
            wp_send_json_error(__('Invalid user ID.', 'clr'));
        }
        
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Get all events for this user
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $events_table
            WHERE user_id = %d
            AND timestamp >= %s
            ORDER BY timestamp ASC",
            $user_id, $start_date
        ));
        
        $journey = array();
        $current_session = null;
        $session_data = array();
        
        foreach ($events as $event) {
            $event_data = json_decode($event->event_data, true);
            
            // Group by session
            if ($current_session !== $event->session_id) {
                if ($current_session !== null) {
                    $journey[] = $session_data;
                }
                
                $current_session = $event->session_id;
                $session_data = array(
                    'session_id' => $current_session,
                    'events' => array()
                );
            }
            
            $session_data['events'][] = array(
                'type' => $event->event_type,
                'data' => $event_data,
                'timestamp' => $event->timestamp
            );
        }
        
        // Add the last session
        if (!empty($session_data['events'])) {
            $journey[] = $session_data;
        }
        
        wp_send_json_success($journey);
    }
    
    /**
     * AJAX handler to load conversion funnel data
     */
    public function ajax_load_conversion_funnel() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Get all events for the specified time period
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $events_table
            WHERE timestamp >= %s
            ORDER BY timestamp ASC",
            $start_date
        ));
        
        // Define funnel steps
        $funnel_steps = array(
            'login' => array('event_type' => 'login'),
            'page_view' => array('event_type' => 'page_view'),
            'interaction_click' => array('event_type' => 'interaction_click'),
            'interaction_form_submit' => array('event_type' => 'interaction_form_submit'),
            'comment' => array('event_type' => 'comment'),
            'post_published' => array('event_type' => 'post_published')
        );
        
        // Initialize funnel data
        $funnel_data = array();
        foreach ($funnel_steps as $step_name => $step_config) {
            $funnel_data[$step_name] = array(
                'count' => 0,
                'users' => array()
            );
        }
        
        // Process events
        foreach ($events as $event) {
            $event_type = $event->event_type;
            
            // Check if this event type is in our funnel
            foreach ($funnel_steps as $step_name => $step_config) {
                if ($event_type === $step_config['event_type']) {
                    $funnel_data[$step_name]['count']++;
                    $funnel_data[$step_name]['users'][$event->user_id] = true;
                    break;
                }
            }
        }
        
        // Calculate conversion rates
        $previous_count = null;
        foreach ($funnel_data as $step_name => $step_data) {
            $funnel_data[$step_name]['unique_users'] = count($step_data['users']);
            
            if ($previous_count !== null && $previous_count > 0) {
                $funnel_data[$step_name]['conversion_rate'] = ($step_data['count'] / $previous_count) * 100;
            } else {
                $funnel_data[$step_name]['conversion_rate'] = 100;
            }
            
            $previous_count = $step_data['count'];
        }
        
        wp_send_json_success($funnel_data);
    }
    
    /**
     * AJAX handler to load custom reports
     */
    public function ajax_load_custom_reports() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        global $wpdb;
        $reports_table = $wpdb->prefix . 'clr_custom_reports';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$reports_table'");
        if ($table_exists != $reports_table) {
            wp_send_json_success(array());
        }
        
        $reports = $wpdb->get_results("SELECT * FROM $reports_table ORDER BY created_at DESC");
        
        wp_send_json_success($reports);
    }
    
    /**
     * AJAX handler to save custom report
     */
    public function ajax_save_custom_report() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_text_field($_POST['description']);
        $config = json_decode(stripslashes($_POST['config']), true);
        
        if (empty($name) || empty($config)) {
            wp_send_json_error(__('Please fill in all required fields.', 'clr'));
        }
        
        global $wpdb;
        $reports_table = $wpdb->prefix . 'clr_custom_reports';
        
        // Create table if it doesn't exist
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$reports_table'");
        if ($table_exists != $reports_table) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $reports_table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(100) NOT NULL,
                description text NOT NULL,
                config text NOT NULL,
                created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        
        if ($report_id > 0) {
            // Update existing report
            $result = $wpdb->update(
                $reports_table,
                array(
                    'name' => $name,
                    'description' => $description,
                    'config' => json_encode($config),
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $report_id)
            );
        } else {
            // Add new report
            $result = $wpdb->insert(
                $reports_table,
                array(
                    'name' => $name,
                    'description' => $description,
                    'config' => json_encode($config),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving report.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete custom report
     */
    public function ajax_delete_custom_report() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $report_id = intval($_POST['report_id']);
        
        if (!$report_id) {
            wp_send_json_error(__('Invalid report ID.', 'clr'));
        }
        
        global $wpdb;
        $reports_table = $wpdb->prefix . 'clr_custom_reports';
        
        // Delete the report
        $result = $wpdb->delete($reports_table, array('id' => $report_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting report.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to load custom report
     */
    public function ajax_load_custom_report() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $report_id = intval($_POST['report_id']);
        
        if (!$report_id) {
            wp_send_json_error(__('Invalid report ID.', 'clr'));
        }
        
        global $wpdb;
        $reports_table = $wpdb->prefix . 'clr_custom_reports';
        
        $report = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $reports_table WHERE id = %d",
            $report_id
        ));
        
        if (!$report) {
            wp_send_json_error(__('Report not found.', 'clr'));
        }
        
        $report_config = json_decode($report->config, true);
        $report_data = $this->generate_custom_report($report_config);
        
        wp_send_json_success(array(
            'report' => $report,
            'data' => $report_data
        ));
    }
    
    /**
     * Generate custom report data based on configuration
     */
    private function generate_custom_report($config) {
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Get date range
        $days = isset($config['days']) ? intval($config['days']) : 30;
        $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        // Build query based on report type
        switch ($config['type']) {
            case 'user_activity':
                $query = $wpdb->prepare(
                    "SELECT user_id, COUNT(*) as activity_count
                    FROM $events_table
                    WHERE timestamp >= %s
                    GROUP BY user_id
                    ORDER BY activity_count DESC
                    LIMIT %d",
                    $start_date, isset($config['limit']) ? intval($config['limit']) : 10
                );
                break;
                
            case 'event_frequency':
                $query = $wpdb->prepare(
                    "SELECT event_type, COUNT(*) as event_count
                    FROM $events_table
                    WHERE timestamp >= %s
                    GROUP BY event_type
                    ORDER BY event_count DESC",
                    $start_date
                );
                break;
                
            case 'time_series':
                $query = $wpdb->prepare(
                    "SELECT DATE(timestamp) as date, event_type, COUNT(*) as event_count
                    FROM $events_table
                    WHERE timestamp >= %s
                    GROUP BY date, event_type
                    ORDER BY date ASC",
                    $start_date
                );
                break;
                
            default:
                return array();
        }
        
        $results = $wpdb->get_results($query);
        return $results;
    }
    
    /**
     * AJAX handler to export report
     */
    public function ajax_export_report() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $report_id = intval($_POST['report_id']);
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        
        if (!$report_id) {
            wp_send_json_error(__('Invalid report ID.', 'clr'));
        }
        
        global $wpdb;
        $reports_table = $wpdb->prefix . 'clr_custom_reports';
        
        $report = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $reports_table WHERE id = %d",
            $report_id
        ));
        
        if (!$report) {
            wp_send_json_error(__('Report not found.', 'clr'));
        }
        
        $report_config = json_decode($report->config, true);
        $report_data = $this->generate_custom_report($report_config);
        
        // Generate filename
        $filename = 'clr-report-' . sanitize_title($report->name) . '-' . date('Y-m-d') . '.' . $format;
        
        // Set headers
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=' . $filename);
            
            // Output CSV
            $output = fopen('php://output', 'w');
            
            // Add header row
            if (!empty($report_data)) {
                fputcsv($output, array_keys((array)$report_data[0]));
                
                // Add data rows
                foreach ($report_data as $row) {
                    fputcsv($output, (array)$row);
                }
            }
            
            fclose($output);
        } else if ($format === 'json') {
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename=' . $filename);
            
            echo json_encode($report_data);
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to check for real-time data
     */
    public function ajax_check_real_time_data() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $last_check = isset($_POST['last_check']) ? intval($_POST['last_check']) : 0;
        
        global $wpdb;
        $events_table = $wpdb->prefix . 'clr_analytics_events';
        
        // Get events since last check
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT event_type, COUNT(*) as count
            FROM $events_table
            WHERE timestamp >= %s
            GROUP BY event_type",
            date('Y-m-d H:i:s', $last_check)
        ));
        
        // Get total active sessions in the last 5 minutes
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT session_id)
            FROM $events_table
            WHERE timestamp >= %s",
            date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ));
        
        wp_send_json_success(array(
            'events' => $events,
            'active_sessions' => $active_sessions,
            'timestamp' => time()
        ));
    }
    
    /**
     * AJAX handler to connect Google Analytics
     */
    public function ajax_connect_ga() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $tracking_id = sanitize_text_field($_POST['tracking_id']);
        
        if (empty($tracking_id)) {
            wp_send_json_error(__('Please enter a valid tracking ID.', 'clr'));
        }
        
        // Validate tracking ID format (UA-XXXXXXXX-X or G-XXXXXXXXXX)
        if (!preg_match('/^(UA-\d{4,10}-\d{1,4}|G-\w{10})$/', $tracking_id)) {
            wp_send_json_error(__('Invalid tracking ID format.', 'clr'));
        }
        
        // Save options
        update_option('clr_ga_connected', 1);
        update_option('clr_ga_tracking_id', $tracking_id);
        
        wp_send_json_success(__('Google Analytics connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Google Analytics
     */
    public function ajax_disconnect_ga() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_ga_connected', 0);
        delete_option('clr_ga_tracking_id');
        
        wp_send_json_success(__('Google Analytics disconnected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to connect Mailchimp
     */
    public function ajax_connect_mailchimp() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $api_key = sanitize_text_field($_POST['api_key']);
        $list_id = sanitize_text_field($_POST['list_id']);
        
        if (empty($api_key) || empty($list_id)) {
            wp_send_json_error(__('Please enter both API key and list ID.', 'clr'));
        }
        
        // Validate API key by making a test request
        $response = $this->test_mailchimp_connection($api_key);
        
        if (is_wp_error($response)) {
            wp_send_json_error(__('Invalid API key. Please check and try again.', 'clr'));
        }
        
        // Save options
        update_option('clr_mailchimp_connected', 1);
        update_option('clr_mailchimp_api_key', $api_key);
        update_option('clr_mailchimp_list_id', $list_id);
        
        wp_send_json_success(__('Mailchimp connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Mailchimp
     */
    public function ajax_disconnect_mailchimp() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_mailchimp_connected', 0);
        delete_option('clr_mailchimp_api_key');
        delete_option('clr_mailchimp_list_id');
        
        wp_send_json_success(__('Mailchimp disconnected successfully.', 'clr'));
    }
    
    /**
     * Test Mailchimp connection
     */
    private function test_mailchimp_connection($api_key) {
        $data_center = substr($api_key, strpos($api_key, '-') + 1);
        $url = 'https://' . $data_center . '.api.mailchimp.com/3.0/ping';
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('user:' . $api_key)
            )
        ));
        
        return $response;
    }
    
    /**
     * AJAX handler to connect Slack
     */
    public function ajax_connect_slack() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        $webhook_url = esc_url_raw($_POST['webhook_url']);
        
        if (empty($webhook_url)) {
            wp_send_json_error(__('Please enter a valid webhook URL.', 'clr'));
        }
        
        // Validate webhook URL
        if (!filter_var($webhook_url, FILTER_VALIDATE_URL) || strpos($webhook_url, 'hooks.slack.com') === false) {
            wp_send_json_error(__('Invalid webhook URL.', 'clr'));
        }
        
        // Test webhook by sending a test message
        $test_message = array(
            'text' => 'Custom Login Redirect plugin connected successfully!',
            'attachments' => array(
                array(
                    'text' => 'You will now receive notifications for important user events.',
                    'color' => '#36a64f'
                )
            )
        );
        
        $response = wp_remote_post($webhook_url, array(
            'body' => json_encode($test_message),
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(__('Failed to connect to Slack. Please check your webhook URL.', 'clr'));
        }
        
        // Save options
        update_option('clr_slack_connected', 1);
        update_option('clr_slack_webhook_url', $webhook_url);
        
        wp_send_json_success(__('Slack connected successfully.', 'clr'));
    }
    
    /**
     * AJAX handler to disconnect Slack
     */
    public function ajax_disconnect_slack() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options') || !clr_is_premium()) {
            wp_die();
        }
        
        // Remove options
        update_option('clr_slack_connected', 0);
        delete_option('clr_slack_webhook_url');
        
        wp_send_json_success(__('Slack disconnected successfully.', 'clr'));
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * AJAX handler to load user analytics data
     */
    public function ajax_load_user_analytics() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $user_search = sanitize_text_field($_POST['user_search']);
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        // Find user by username or email
        $user = get_user_by('login', $user_search);
        if (!$user) {
            $user = get_user_by('email', $user_search);
        }
        
        if (!$user) {
            wp_send_json_error(__('User not found.', 'clr'));
        }
        
        $analytics = new CLR_Analytics();
        $data = $analytics->get_user_analytics($user->ID, $days);
        
        // Add user info to the response
        $data['user_info'] = array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles
        );
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX handler to load conditional rules
     */
    public function ajax_load_conditional_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        $rules = $wpdb->get_results("SELECT * FROM $table_name ORDER BY priority DESC, name");
        
        if (empty($rules)) {
            echo '<tr><td colspan="7">' . __('No conditional rules found. Add your first rule to get started.', 'clr') . '</td></tr>';
        } else {
            foreach ($rules as $rule) {
                $status_class = $rule->status === 'active' ? 'active' : 'inactive';
                $status_text = $rule->status === 'active' ? __('Active', 'clr') : __('Inactive', 'clr');
                
                echo '<tr>';
                echo '<td>' . esc_html($rule->id) . '</td>';
                echo '<td>' . esc_html($rule->name) . '</td>';
                echo '<td>' . esc_html($rule->description) . '</td>';
                echo '<td>' . esc_html($rule->redirect_url) . '</td>';
                echo '<td>' . esc_html($rule->priority) . '</td>';
                echo '<td><span class="clr-status ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></td>';
                echo '<td>';
                echo '<button type="button" class="button button-small clr-edit-conditional-rule" data-id="' . esc_attr($rule->id) . '" data-name="' . esc_attr($rule->name) . '" data-description="' . esc_attr($rule->description) . '" data-conditions="' . esc_attr($rule->conditions) . '" data-url="' . esc_attr($rule->redirect_url) . '" data-priority="' . esc_attr($rule->priority) . '" data-status="' . esc_attr($rule->status) . '">' . __('Edit', 'clr') . '</button> ';
                echo '<button type="button" class="button button-small clr-toggle-conditional-rule" data-id="' . esc_attr($rule->id) . '">' . ($rule->status === 'active' ? __('Deactivate', 'clr') : __('Activate', 'clr')) . '</button> ';
                echo '<button type="button" class="button button-small clr-delete-conditional-rule" data-id="' . esc_attr($rule->id) . '">' . __('Delete', 'clr') . '</button>';
                echo '</td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler to save conditional rule
     */
    public function ajax_save_conditional_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_text_field($_POST['description']);
        $conditions = json_decode(stripslashes($_POST['conditions']), true);
        $redirect_url = esc_url_raw($_POST['redirect_url']);
        $priority = intval($_POST['priority']);
        $status = sanitize_text_field($_POST['status']);
        
        if (empty($name) || empty($conditions) || empty($redirect_url)) {
            wp_send_json_error(__('Please fill in all required fields.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        if ($rule_id > 0) {
            // Update existing rule
            $result = $wpdb->update(
                $table_name,
                array(
                    'name' => $name,
                    'description' => $description,
                    'conditions' => json_encode($conditions),
                    'redirect_url' => $redirect_url,
                    'priority' => $priority,
                    'status' => $status,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $rule_id)
            );
        } else {
            // Add new rule
            $result = $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'description' => $description,
                    'conditions' => json_encode($conditions),
                    'redirect_url' => $redirect_url,
                    'priority' => $priority,
                    'status' => $status,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
        }
        
        if ($result === false) {
            wp_send_json_error(__('Error saving rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to delete conditional rule
     */
    public function ajax_delete_conditional_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_conditional_redirects';
        
        // Delete the rule
        $result = $wpdb->delete($table_name, array('id' => $rule_id));
        
        if ($result === false) {
            wp_send_json_error(__('Error deleting rule.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to toggle conditional rule status
     */
    public function ajax_toggle_conditional_rule() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $rule_id = intval($_POST['rule_id']);
        
        if ($rule_id <= 0) {
            wp_send_json_error(__('Invalid rule ID.', 'clr'));
        }
        
        $conditional = new CLR_Conditional();
        $result = $conditional->toggle_rule_status($rule_id);
        
        if ($result === false) {
            wp_send_json_error(__('Error toggling rule status.', 'clr'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * AJAX handler to test conditional rules
     */
    public function ajax_test_conditional_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $user_role = sanitize_text_field($_POST['user_role']);
        $device_type = sanitize_text_field($_POST['device_type']);
        $time_hour = intval($_POST['time_hour']);
        $referral_source = sanitize_text_field($_POST['referral_source']);
        
        // Create a mock user ID for testing
        $mock_user_id = 0;
        
        // Create mock user data
        $mock_user = new stdClass();
        $mock_user->ID = $mock_user_id;
        $mock_user->roles = array($user_role);
        
        // Create mock device info
        $device_info = array(
            'device_type' => $device_type,
            'device_os' => 'unknown',
            'browser' => 'unknown',
            'user_agent' => 'Mozilla/5.0 (Test User Agent)'
        );
        
        // Create mock referral info
        $referral_info = array(
            'source' => $referral_source,
            'medium' => 'none',
            'campaign' => 'none',
            'domain' => '',
            'query_params' => array()
        );
        
        // Create mock time info
        $time_info = array(
            'hour' => $time_hour,
            'day_of_week' => strtolower(date('l')),
            'day_of_month' => (int)date('j'),
            'month' => (int)date('n'),
            'year' => (int)date('Y')
        );
        
        // Create mock geo info
        $geo_info = array();
        
        // Test conditional rules
        $conditional = new CLR_Conditional();
        
        // Get all rules
        $rules = $conditional->get_all_rules();
        
        $matched_rule = null;
        
        // Check each rule
        foreach ($rules as $rule) {
            if ($rule->status !== 'active') {
                continue;
            }
            
            $conditions = json_decode($rule->conditions, true);
            
            if ($conditional->evaluate_conditions($conditions, $mock_user_id, $user_role, $device_info, $referral_info, $time_info, $geo_info)) {
                $matched_rule = $rule;
                break;
            }
        }
        
        if ($matched_rule) {
            wp_send_json_success(array(
                'matched' => true,
                'rule_name' => $matched_rule->name,
                'redirect_url' => $matched_rule->redirect_url
            ));
        } else {
            wp_send_json_success(array(
                'matched' => false,
                'message' => __('No rules matched the test scenario.', 'clr')
            ));
        }
    }
    
    /**
     * AJAX handler to export rules
     */
    public function ajax_export_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'json';
        
        $import_export = new CLR_Import_Export();
        $export_data = $import_export->export_rules($format);
        
        $filename = 'clr-rules-export-' . date('Y-m-d') . '.' . ($format === 'json' ? 'json' : 'csv');
        
        header('Content-Type: ' . ($format === 'json' ? 'application/json' : 'text/csv'));
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo $export_data;
        wp_die();
    }
    
    /**
     * AJAX handler to import rules
     */
    public function ajax_import_rules() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        if (empty($_FILES['import_file'])) {
            wp_send_json_error(__('No file uploaded.', 'clr'));
        }
        
        $file = $_FILES['import_file'];
        
        // Check file type
        $file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        $allowed_types = array('json', 'csv');
        
        if (!in_array(strtolower($file_type['ext']), $allowed_types)) {
            wp_send_json_error(__('Invalid file type. Please upload a JSON or CSV file.', 'clr'));
        }
        
        // Check file size
        if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
            wp_send_json_error(__('File is too large. Maximum size is 2MB.', 'clr'));
        }
        
        $format = strtolower($file_type['ext']);
        
        // Check if we should overwrite existing rules
        $overwrite = isset($_POST['overwrite']) && intval($_POST['overwrite']) === 1;
        
        if ($overwrite) {
            // Delete existing rules
            global $wpdb;
            
            // Delete basic rules
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_redirect_rules");
            
            // Delete time-based rules
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_time_redirects");
            
            // Delete device-based rules
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_device_redirects");
            
            // Delete referral-based rules
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_referral_redirects");
            
            // Delete conditional rules
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_conditional_redirects");
            
            // Delete A/B testing variants
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}clr_ab_variants");
        }
        
        // Import the rules
        $import_export = new CLR_Import_Export();
        $result = $import_export->import_rules($file['tmp_name'], $format);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX handler to download template
     */
    public function ajax_download_template() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $template_type = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'basic';
        
        $import_export = new CLR_Import_Export();
        
        // Create template data based on type
        $template_data = array(
            'version' => CLR_VERSION,
            'export_date' => current_time('mysql'),
            'rules' => array()
        );
        
        switch ($template_type) {
            case 'basic':
                $template_data['rules']['basic'] = array(
                    array(
                        'type' => 'basic',
                        'role' => 'subscriber',
                        'redirect_url' => 'https://example.com/subscriber-dashboard'
                    ),
                    array(
                        'type' => 'basic',
                        'role' => 'administrator',
                        'redirect_url' => 'https://example.com/wp-admin'
                    )
                );
                break;
                
            case 'time_based':
                $template_data['rules']['time_based'] = array(
                    array(
                        'type' => 'time_based',
                        'day_of_week' => 'monday',
                        'start_hour' => 9,
                        'end_hour' => 17,
                        'redirect_url' => 'https://example.com/weekdays',
                        'priority' => 10
                    ),
                    array(
                        'type' => 'time_based',
                        'day_of_week' => 'saturday',
                        'start_hour' => 10,
                        'end_hour' => 14,
                        'redirect_url' => 'https://example.com/weekends',
                        'priority' => 10
                    )
                );
                break;
                
            case 'device_based':
                $template_data['rules']['device_based'] = array(
                    array(
                        'type' => 'device_based',
                        'device_type' => 'mobile',
                        'device_os' => 'any',
                        'browser' => 'any',
                        'redirect_url' => 'https://example.com/mobile',
                        'priority' => 10
                    ),
                    array(
                        'type' => 'device_based',
                        'device_type' => 'desktop',
                        'device_os' => 'any',
                        'browser' => 'any',
                        'redirect_url' => 'https://example.com/desktop',
                        'priority' => 10
                    )
                );
                break;
                
            case 'referral_based':
                $template_data['rules']['referral_based'] = array(
                    array(
                        'type' => 'referral_based',
                        'source' => 'google',
                        'medium' => 'organic',
                        'campaign' => 'none',
                        'redirect_url' => 'https://example.com/google-visitors',
                        'priority' => 10
                    ),
                    array(
                        'type' => 'referral_based',
                        'source' => 'facebook',
                        'medium' => 'social',
                        'campaign' => 'none',
                        'redirect_url' => 'https://example.com/facebook-visitors',
                        'priority' => 10
                    )
                );
                break;
                
            case 'complete':
                $template_data['rules']['basic'] = array(
                    array(
                        'type' => 'basic',
                        'role' => 'subscriber',
                        'redirect_url' => 'https://example.com/subscriber-dashboard'
                    )
                );
                
                $template_data['rules']['time_based'] = array(
                    array(
                        'type' => 'time_based',
                        'day_of_week' => 'monday',
                        'start_hour' => 9,
                        'end_hour' => 17,
                        'redirect_url' => 'https://example.com/weekdays',
                        'priority' => 10
                    )
                );
                
                $template_data['rules']['device_based'] = array(
                    array(
                        'type' => 'device_based',
                        'device_type' => 'mobile',
                        'device_os' => 'any',
                        'browser' => 'any',
                        'redirect_url' => 'https://example.com/mobile',
                        'priority' => 10
                    )
                );
                
                $template_data['rules']['referral_based'] = array(
                    array(
                        'type' => 'referral_based',
                        'source' => 'google',
                        'medium' => 'organic',
                        'campaign' => 'none',
                        'redirect_url' => 'https://example.com/google-visitors',
                        'priority' => 10
                    )
                );
                break;
        }
        
        // Convert to CSV
        $csv_data = $import_export->export_to_csv($template_data);
        
        $filename = 'clr-' . $template_type . '-template.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        echo $csv_data;
        wp_die();
    }
    
    /**
     * AJAX handler to test email notification
     */
    public function ajax_test_email() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $email_notifications = new CLR_Email_Notifications();
        $result = $email_notifications->test_email_notification();
        
        if ($result) {
            wp_send_json_success(__('Test email sent successfully. Please check your inbox.', 'clr'));
        } else {
            wp_send_json_error(__('Failed to send test email. Please check your email configuration.', 'clr'));
        }
    }
    
    /**
     * AJAX handler to load notification history
     */
    public function ajax_load_notification_history() {
        check_ajax_referer('clr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_notification_history';
        $history = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 20");
        
        if (empty($history)) {
            echo '<tr><td colspan="6">' . __('No notification history found.', 'clr') . '</td></tr>';
        } else {
            foreach ($history as $item) {
                $status_class = $item->status === 'sent' ? 'success' : 'error';
                $status_text = $item->status === 'sent' ? __('Sent', 'clr') : __('Failed', 'clr');
                
                echo '<tr>';
                echo '<td>' . esc_html($item->id) . '</td>';
                echo '<td>' . esc_html($item->created_at) . '</td>';
                echo '<td>' . esc_html($item->event_type) . '</td>';
                echo '<td>' . esc_html($item->user_login) . '</td>';
                echo '<td>' . esc_html($item->recipient) . '</td>';
                echo '<td><span class="clr-status ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></td>';
                echo '</tr>';
            }
        }
        
        wp_die();
    }
}