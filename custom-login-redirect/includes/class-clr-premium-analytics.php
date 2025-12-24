<?php
/**
 * Premium Analytics Features for Custom Login Redirect
 *
 * This class implements advanced analytics features that are available
 * only to premium users with a valid license key.
 */

class CLR_Premium_Analytics {
    public function __construct() {
        // Initialize premium analytics features
        add_action('init', array($this, 'init_premium_features'));
        
        // Add AJAX handlers for premium features
        add_action('wp_ajax_clr_load_heatmap_data', array($this, 'ajax_load_heatmap_data'));
        add_action('wp_ajax_clr_load_user_journey', array($this, 'ajax_load_user_journey'));
        add_action('wp_ajax_clr_load_conversion_funnel', array($this, 'ajax_load_conversion_funnel'));
        add_action('wp_ajax_clr_load_custom_report', array($this, 'ajax_load_custom_report'));
        add_action('wp_ajax_clr_save_custom_report', array($this, 'ajax_save_custom_report'));
        add_action('wp_ajax_clr_export_report', array($this, 'ajax_export_report'));
    }
    
    /**
     * Initialize premium features if license is valid
     */
    public function init_premium_features() {
        if (clr_is_premium()) {
            // Add premium tracking
            add_action('wp_footer', array($this, 'track_user_interactions'));
            add_action('comment_post', array($this, 'track_comment_activity'), 10, 2);
            add_action('transition_post_status', array($this, 'track_content_interaction'), 10, 3);
            
            // Add premium analytics menu
            add_action('admin_menu', array($this, 'add_premium_analytics_menu'));
            
            // Add real-time notifications
            add_action('wp_ajax_clr_check_real_time_data', array($this, 'ajax_check_real_time_data'));
        }
    }
    
    /**
     * Add premium analytics menu page
     */
    public function add_premium_analytics_menu() {
        add_submenu_page(
            'clr-settings',
            __('Premium Analytics', 'clr'),
            __('Premium Analytics', 'clr'),
            'manage_options',
            'clr-premium-analytics',
            array($this, 'render_premium_analytics_page')
        );
    }
    
    /**
     * Render premium analytics page
     */
    public function render_premium_analytics_page() {
        require_once CLR_PLUGIN_DIR . 'admin/partials/premium-analytics-page.php';
    }
    
    /**
     * Track user interactions on the site
     */
    public function track_user_interactions() {
        if (!is_user_logged_in() || !get_option('clr_analytics_enabled')) {
            return;
        }
        
        $user_id = get_current_user_id();
        $session_id = get_user_meta($user_id, 'clr_current_session', true);
        
        if (!$session_id) {
            return;
        }
        
        // Add tracking script to footer
        ?>
        <script type="text/javascript">
        (function() {
            if (typeof jQuery === 'undefined') {
                return;
            }
            
            var clrTrackInteraction = function(eventType, eventData) {
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'clr_track_interaction',
                    nonce: '<?php echo wp_create_nonce('clr_track_nonce'); ?>',
                    user_id: '<?php echo $user_id; ?>',
                    session_id: '<?php echo $session_id; ?>',
                    event_type: eventType,
                    event_data: JSON.stringify(eventData)
                });
            };
            
            // Track clicks
            jQuery(document).on('click', 'a, button, input[type="submit"], input[type="button"]', function(e) {
                var $target = jQuery(this);
                var eventData = {
                    element: $target.prop('tagName').toLowerCase(),
                    text: $target.text().trim(),
                    id: $target.attr('id') || '',
                    class: $target.attr('class') || '',
                    href: $target.attr('href') || '',
                    timestamp: Date.now()
                };
                
                clrTrackInteraction('click', eventData);
            });
            
            // Track form submissions
            jQuery(document).on('submit', 'form', function(e) {
                var $form = jQuery(this);
                var eventData = {
                    form_id: $form.attr('id') || '',
                    form_class: $form.attr('class') || '',
                    form_action: $form.attr('action') || '',
                    timestamp: Date.now()
                };
                
                clrTrackInteraction('form_submit', eventData);
            });
            
            // Track scroll depth
            var maxScroll = 0;
            jQuery(window).on('scroll', function() {
                var scrollPercent = Math.round((jQuery(window).scrollTop() / (jQuery(document).height() - jQuery(window).height())) * 100);
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                    
                    // Track at 25%, 50%, 75%, and 90%
                    if (scrollPercent === 25 || scrollPercent === 50 || scrollPercent === 75 || scrollPercent === 90) {
                        clrTrackInteraction('scroll', { depth: scrollPercent });
                    }
                }
            });
            
            // Track time on page
            var timeIntervals = [30, 60, 120, 300, 600]; // 30s, 1m, 2m, 5m, 10m
            var timeOnPage = 0;
            
            setInterval(function() {
                timeOnPage += 10;
                if (timeIntervals.includes(timeOnPage)) {
                    clrTrackInteraction('time_on_page', { seconds: timeOnPage });
                }
            }, 10000); // Check every 10 seconds
        })();
        </script>
        <?php
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
     * Track comment activity
     */
    public function track_comment_activity($comment_id, $comment_approved) {
        if (!clr_is_premium() || !get_option('clr_analytics_enabled')) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $user_id = $comment->user_id;
        
        if (!$user_id) {
            return;
        }
        
        $session_id = get_user_meta($user_id, 'clr_current_session', true);
        
        if (!$session_id) {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists != $table_name) {
            return;
        }
        
        $event_data = array(
            'comment_id' => $comment_id,
            'post_id' => $comment->comment_post_ID,
            'approved' => $comment_approved === '1',
            'comment_content' => substr($comment->comment_content, 0, 100)
        );
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'event_type' => 'comment',
                'event_data' => json_encode($event_data),
                'timestamp' => current_time('mysql'),
                'ip_address' => $this->get_user_ip()
            )
        );
    }
    
    /**
     * Track content interaction (post creation, editing, etc.)
     */
    public function track_content_interaction($new_status, $old_status, $post) {
        if (!clr_is_premium() || !get_option('clr_analytics_enabled')) {
            return;
        }
        
        if ($post->post_type === 'revision' || $post->post_type === 'nav_menu_item') {
            return;
        }
        
        $user_id = $post->post_author;
        
        if (!$user_id) {
            return;
        }
        
        $session_id = get_user_meta($user_id, 'clr_current_session', true);
        
        if (!$session_id) {
            return;
        }
        
        // Only track specific transitions
        if (($old_status === 'new' || $old_status === 'auto-draft') && $new_status === 'publish') {
            $event_type = 'post_published';
        } else if ($old_status === 'publish' && $new_status !== 'publish') {
            $event_type = 'post_unpublished';
        } else if ($old_status !== 'trash' && $new_status === 'trash') {
            $event_type = 'post_trashed';
        } else if ($old_status === 'trash' && $new_status !== 'trash') {
            $event_type = 'post_restored';
        } else {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'clr_analytics_events';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists != $table_name) {
            return;
        }
        
        $event_data = array(
            'post_id' => $post->ID,
            'post_type' => $post->post_type,
            'post_title' => $post->post_title,
            'old_status' => $old_status,
            'new_status' => $new_status
        );
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'event_type' => $event_type,
                'event_data' => json_encode($event_data),
                'timestamp' => current_time('mysql'),
                'ip_address' => $this->get_user_ip()
            )
        );
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
}