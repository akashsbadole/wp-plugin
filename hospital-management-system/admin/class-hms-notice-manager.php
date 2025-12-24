<?php
/**
 * HMS Notice Manager
 * 
 * Manages admin notices with WordPress standards compliance
 * and accessibility features.
 * 
 * @package Hospital_Management_System
 * @subpackage Admin
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class HMS_Notice_Manager {

    /**
     * The single instance of the class.
     *
     * @var HMS_Notice_Manager
     */
    protected static $_instance = null;

    /**
     * Stored notices
     *
     * @var array
     */
    private $notices = array();

    /**
     * Main HMS_Notice_Manager Instance.
     *
     * @return HMS_Notice_Manager - Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'display_notices'));
        add_action('wp_ajax_hms_dismiss_notice', array($this, 'dismiss_notice'));
        add_action('admin_init', array($this, 'handle_notice_actions'));
    }

    /**
     * Add a notice
     *
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     * @param array $args Additional arguments
     */
    public function add_notice($message, $type = 'info', $args = array()) {
        $defaults = array(
            'dismissible' => true,
            'persistent' => false,
            'capability' => 'manage_options',
            'screen' => '',
            'id' => '',
            'actions' => array(),
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Generate ID if not provided
        if (empty($args['id'])) {
            $args['id'] = 'hms_notice_' . md5($message . $type);
        }
        
        // Check if user has capability to see this notice
        if (!current_user_can($args['capability'])) {
            return;
        }
        
        // Check screen restriction
        if (!empty($args['screen'])) {
            $current_screen = get_current_screen();
            if ($current_screen && $current_screen->id !== $args['screen']) {
                return;
            }
        }
        
        // Check if notice was dismissed
        if ($args['dismissible'] && $this->is_notice_dismissed($args['id'])) {
            return;
        }
        
        $this->notices[] = array(
            'message' => $message,
            'type' => $type,
            'args' => $args,
        );
    }

    /**
     * Add success notice
     */
    public function add_success($message, $args = array()) {
        $this->add_notice($message, 'success', $args);
    }

    /**
     * Add error notice
     */
    public function add_error($message, $args = array()) {
        $this->add_notice($message, 'error', $args);
    }

    /**
     * Add warning notice
     */
    public function add_warning($message, $args = array()) {
        $this->add_notice($message, 'warning', $args);
    }

    /**
     * Add info notice
     */
    public function add_info($message, $args = array()) {
        $this->add_notice($message, 'info', $args);
    }

    /**
     * Display all notices
     */
    public function display_notices() {
        foreach ($this->notices as $notice) {
            $this->render_notice($notice);
        }
        
        // Clear non-persistent notices
        $this->notices = array_filter($this->notices, function($notice) {
            return $notice['args']['persistent'];
        });
    }

    /**
     * Render a single notice
     */
    private function render_notice($notice) {
        $message = $notice['message'];
        $type = $notice['type'];
        $args = $notice['args'];
        
        $classes = array('notice', 'hms-notice', 'hms-notice-' . $type);
        
        if ($args['dismissible']) {
            $classes[] = 'is-dismissible';
            $classes[] = 'hms-notice-dismissible';
        }
        
        $class_string = implode(' ', $classes);
        
        // ARIA attributes for accessibility
        $aria_attrs = '';
        if ($type === 'error') {
            $aria_attrs = 'role="alert"';
        } else {
            $aria_attrs = 'role="status" aria-live="polite"';
        }
        
        ?>
        <div class="<?php echo esc_attr($class_string); ?>" 
             data-notice-id="<?php echo esc_attr($args['id']); ?>"
             <?php echo $aria_attrs; ?>>
            
            <p><?php echo wp_kses_post($message); ?></p>
            
            <?php if (!empty($args['actions'])): ?>
                <p class="hms-notice-actions">
                    <?php foreach ($args['actions'] as $action): ?>
                        <a href="<?php echo esc_url($action['url']); ?>" 
                           class="button <?php echo esc_attr($action['class'] ?? 'button-secondary'); ?>">
                            <?php echo esc_html($action['text']); ?>
                        </a>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            
            <?php if ($args['dismissible']): ?>
                <button type="button" 
                        class="notice-dismiss hms-notice-dismiss" 
                        data-notice-id="<?php echo esc_attr($args['id']); ?>"
                        aria-label="<?php esc_attr_e('Dismiss this notice', 'hospital-management-system'); ?>">
                    <span class="screen-reader-text">
                        <?php esc_html_e('Dismiss this notice.', 'hospital-management-system'); ?>
                    </span>
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Check if notice was dismissed
     */
    private function is_notice_dismissed($notice_id) {
        $dismissed_notices = get_user_meta(get_current_user_id(), 'hms_dismissed_notices', true);
        return is_array($dismissed_notices) && in_array($notice_id, $dismissed_notices);
    }

    /**
     * Dismiss notice via AJAX
     */
    public function dismiss_notice() {
        check_ajax_referer('hms_dismiss_notice', 'nonce');
        
        $notice_id = sanitize_text_field($_POST['notice_id'] ?? '');
        
        if (empty($notice_id)) {
            wp_die('Invalid notice ID');
        }
        
        $user_id = get_current_user_id();
        $dismissed_notices = get_user_meta($user_id, 'hms_dismissed_notices', true);
        
        if (!is_array($dismissed_notices)) {
            $dismissed_notices = array();
        }
        
        if (!in_array($notice_id, $dismissed_notices)) {
            $dismissed_notices[] = $notice_id;
            update_user_meta($user_id, 'hms_dismissed_notices', $dismissed_notices);
        }
        
        wp_send_json_success();
    }

    /**
     * Handle notice actions from URL parameters
     */
    public function handle_notice_actions() {
        // Handle success messages from redirects
        if (isset($_GET['hms_notice'])) {
            $notice_type = sanitize_text_field($_GET['hms_notice']);
            $message = '';
            
            switch ($notice_type) {
                case 'patient_saved':
                    $message = __('Patient information saved successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'appointment_saved':
                    $message = __('Appointment saved successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'prescription_saved':
                    $message = __('Prescription saved successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'bill_saved':
                    $message = __('Bill saved successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'settings_saved':
                    $message = __('Settings saved successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'data_exported':
                    $message = __('Data exported successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'data_imported':
                    $message = __('Data imported successfully.', 'hospital-management-system');
                    $this->add_success($message);
                    break;
                case 'error':
                    $error_message = sanitize_text_field($_GET['message'] ?? '');
                    $message = !empty($error_message) ? $error_message : __('An error occurred.', 'hospital-management-system');
                    $this->add_error($message);
                    break;
            }
        }
        
        // Handle database status notices
        $this->check_system_status();
    }

    /**
     * Check system status and add notices if needed
     */
    private function check_system_status() {
        // Only show on HMS pages
        if (!$this->is_hms_page()) {
            return;
        }
        
        // Check database tables
        if (class_exists('HMS_Activator')) {
            $missing_tables = HMS_Activator::check_required_tables();
            if (!empty($missing_tables)) {
                $message = sprintf(
                    __('HMS is missing %d database tables. This may cause functionality issues.', 'hospital-management-system'),
                    count($missing_tables)
                );
                
                $actions = array(
                    array(
                        'text' => __('Fix Tables', 'hospital-management-system'),
                        'url' => admin_url('admin.php?page=hms_fix_tables'),
                        'class' => 'button-primary'
                    ),
                    array(
                        'text' => __('View Details', 'hospital-management-system'),
                        'url' => admin_url('admin.php?page=hms_database_status'),
                        'class' => 'button-secondary'
                    )
                );
                
                $this->add_warning($message, array(
                    'id' => 'hms_missing_tables',
                    'actions' => $actions,
                    'dismissible' => true
                ));
            }
        }
        
        // Check database version
        $db_version = get_option('hms_db_version', '');
        if (empty($db_version)) {
            $message = __('HMS database version is not set. Please run the database setup.', 'hospital-management-system');
            
            $actions = array(
                array(
                    'text' => __('Run Setup', 'hospital-management-system'),
                    'url' => admin_url('admin.php?page=hms_database_setup'),
                    'class' => 'button-primary'
                )
            );
            
            $this->add_error($message, array(
                'id' => 'hms_no_db_version',
                'actions' => $actions,
                'dismissible' => false
            ));
        }
        
        // Check for plugin updates or important announcements
        $this->check_plugin_announcements();
    }

    /**
     * Check for plugin announcements
     */
    private function check_plugin_announcements() {
        // Check if there are any important announcements
        $announcements = get_transient('hms_announcements');
        
        if ($announcements && is_array($announcements)) {
            foreach ($announcements as $announcement) {
                if (!$this->is_notice_dismissed('hms_announcement_' . $announcement['id'])) {
                    $this->add_notice(
                        $announcement['message'],
                        $announcement['type'] ?? 'info',
                        array(
                            'id' => 'hms_announcement_' . $announcement['id'],
                            'dismissible' => true,
                            'actions' => $announcement['actions'] ?? array()
                        )
                    );
                }
            }
        }
    }

    /**
     * Check if current page is an HMS page
     */
    private function is_hms_page() {
        $current_screen = get_current_screen();
        if (!$current_screen) {
            return false;
        }
        
        return strpos($current_screen->id, 'hms_') !== false || 
               strpos($current_screen->id, 'hospital-management-system') !== false ||
               (isset($_GET['page']) && strpos($_GET['page'], 'hms_') === 0);
    }

    /**
     * Add JavaScript for notice handling
     */
    public function add_notice_scripts() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Handle notice dismissal
            $(document).on('click', '.hms-notice-dismiss', function(e) {
                e.preventDefault();
                
                var $notice = $(this).closest('.hms-notice');
                var noticeId = $(this).data('notice-id');
                
                if (noticeId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'hms_dismiss_notice',
                            notice_id: noticeId,
                            nonce: '<?php echo wp_create_nonce('hms_dismiss_notice'); ?>'
                        },
                        success: function() {
                            $notice.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    });
                } else {
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
            
            // Auto-dismiss non-persistent notices after 5 seconds
            $('.hms-notice:not(.hms-notice-persistent)').each(function() {
                var $notice = $(this);
                setTimeout(function() {
                    if ($notice.is(':visible')) {
                        $notice.find('.hms-notice-dismiss').click();
                    }
                }, 5000);
            });
        });
        </script>
        <?php
    }
}

// Initialize the Notice Manager
add_action('init', function() {
    HMS_Notice_Manager::instance();
});

// Add notice scripts to admin footer
add_action('admin_footer', function() {
    if (HMS_Notice_Manager::instance()) {
        HMS_Notice_Manager::instance()->add_notice_scripts();
    }
});