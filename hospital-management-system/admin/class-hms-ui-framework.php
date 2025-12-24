<?php
/**
 * HMS UI Framework Manager
 * 
 * Manages the consistent UI framework across all HMS admin pages.
 * Handles CSS/JS enqueuing, breadcrumb generation, and standardized layouts.
 * 
 * @package Hospital_Management_System
 * @subpackage Admin
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class HMS_UI_Framework {

    /**
     * The single instance of the class.
     *
     * @var HMS_UI_Framework
     */
    protected static $_instance = null;

    /**
     * Main HMS_UI_Framework Instance.
     *
     * Ensures only one instance of HMS_UI_Framework is loaded or can be loaded.
     *
     * @return HMS_UI_Framework - Main instance.
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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_head', array($this, 'add_admin_styles'));
        add_action('admin_footer', array($this, 'add_admin_scripts'));
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueue_scripts($hook) {
        // Only load on HMS pages
        if (!$this->is_hms_page($hook)) {
            return;
        }

        // Enqueue UI Framework CSS
        wp_enqueue_style(
            'hms-ui-framework',
            HMS_PLUGIN_URL . 'admin/css/hms-ui-framework.css',
            array(),
            HMS_VERSION
        );

        // Enqueue Responsive CSS
        wp_enqueue_style(
            'hms-responsive',
            HMS_PLUGIN_URL . 'admin/css/hms-responsive.css',
            array('hms-ui-framework'),
            HMS_VERSION
        );

        // Enqueue UI Framework JS
        wp_enqueue_script(
            'hms-ui-framework',
            HMS_PLUGIN_URL . 'admin/js/hms-ui-framework.js',
            array('jquery'),
            HMS_VERSION,
            true
        );

        // Enqueue Accessibility JS
        wp_enqueue_script(
            'hms-accessibility',
            HMS_PLUGIN_URL . 'admin/js/hms-accessibility.js',
            array('jquery', 'hms-ui-framework'),
            HMS_VERSION,
            true
        );

        // Enqueue DataTables if needed
        if ($this->page_needs_datatable($hook)) {
            wp_enqueue_style(
                'hms-datatables',
                'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',
                array(),
                '1.13.6'
            );
            wp_enqueue_script(
                'hms-datatables',
                'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
                array('jquery'),
                '1.13.6',
                true
            );
        }

        // Localize script with admin data
        wp_localize_script('hms-ui-framework', 'hmsUI', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'nonce' => wp_create_nonce('hms_ui_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'hospital-management-system'),
                'saving' => __('Saving...', 'hospital-management-system'),
                'saved' => __('Saved!', 'hospital-management-system'),
                'error' => __('Error occurred', 'hospital-management-system'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'hospital-management-system'),
                'required_field' => __('This field is required.', 'hospital-management-system'),
                'invalid_email' => __('Please enter a valid email address.', 'hospital-management-system'),
                'invalid_phone' => __('Please enter a valid phone number.', 'hospital-management-system'),
            )
        ));

        // Localize accessibility strings
        wp_localize_script('hms-accessibility', 'hmsA11Y', array(
            'strings' => array(
                'skip_to_content' => __('Skip to main content', 'hospital-management-system'),
                'skip_to_navigation' => __('Skip to navigation', 'hospital-management-system'),
                'required_field' => __('This field is required', 'hospital-management-system'),
                'form_error_single' => __('There is 1 error in the form', 'hospital-management-system'),
                'form_error_multiple' => __('There are %d errors in the form', 'hospital-management-system'),
                'form_submitting' => __('Form is being submitted', 'hospital-management-system'),
                'loading' => __('Loading content', 'hospital-management-system'),
                'content_updated' => __('Content has been updated', 'hospital-management-system'),
                'table_updated' => __('Table data has been updated', 'hospital-management-system'),
                'sortable_column' => __('Sortable column', 'hospital-management-system'),
                'column_sorted' => __('Column %s sorted %s', 'hospital-management-system'),
                'close_modal' => __('Close dialog', 'hospital-management-system'),
            )
        ));
    }

    /**
     * Add inline admin styles for better integration.
     */
    public function add_admin_styles() {
        if (!$this->is_hms_page()) {
            return;
        }
        ?>
        <style>
        /* WordPress Admin Integration */
        .hms-wrap .wp-heading-inline {
            margin-bottom: 10px;
        }
        
        .hms-wrap .page-title-action {
            margin-left: 10px;
        }
        
        .hms-wrap .wp-header-end {
            margin-bottom: 20px;
        }
        
        /* Loading Spinner */
        .hms-loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #2271b1;
            border-radius: 50%;
            animation: hms-spin 1s linear infinite;
        }
        
        @keyframes hms-spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        
        .hms-loading {
            position: relative;
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Character Counter */
        .hms-char-counter {
            font-size: 12px;
            color: #646970;
            text-align: right;
            margin-top: 2px;
        }
        
        .hms-char-counter.hms-text-danger {
            color: #d63638;
        }
        
        /* Modal Open Body */
        body.hms-modal-open {
            overflow: hidden;
        }
        
        /* DataTables Integration */
        .hms-table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .hms-table-length label,
        .hms-table-search label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .hms-table-search input {
            padding: 6px 10px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
        }
        
        .dataTables_info {
            color: #646970;
            font-size: 14px;
        }
        
        .dataTables_paginate {
            margin-top: 15px;
        }
        
        .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            background: #f6f7f7;
            color: #50575e;
            text-decoration: none;
            cursor: pointer;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        
        .dataTables_paginate .paginate_button.current {
            background: #2271b1;
            color: #fff;
            border-color: #2271b1;
        }
        
        .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 782px) {
            .hms-table-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .hms-table-length,
            .hms-table-search {
                width: 100%;
            }
            
            .hms-table-length label,
            .hms-table-search label {
                justify-content: space-between;
            }
        }
        </style>
        <?php
    }

    /**
     * Add inline admin scripts for initialization.
     */
    public function add_admin_scripts() {
        if (!$this->is_hms_page()) {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Initialize HMS UI Framework
            if (typeof HMS_UI !== 'undefined') {
                HMS_UI.init();
            }
            
            // Add HMS classes to WordPress elements for consistent styling
            $('.wrap').addClass('hms-wrap');
            
            // Convert WordPress notices to HMS notices
            $('.notice').each(function() {
                var $notice = $(this);
                var type = 'info';
                
                if ($notice.hasClass('notice-success')) type = 'success';
                else if ($notice.hasClass('notice-warning')) type = 'warning';
                else if ($notice.hasClass('notice-error')) type = 'error';
                
                $notice.addClass('hms-notice hms-notice-' + type);
                
                if ($notice.hasClass('is-dismissible') && !$notice.find('.hms-notice-dismiss').length) {
                    $notice.addClass('hms-notice-dismissible');
                    if (!$notice.find('.notice-dismiss').length) {
                        $notice.append('<button type="button" class="hms-notice-dismiss">&times;</button>');
                    }
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Check if current page is an HMS page.
     */
    private function is_hms_page($hook = null) {
        if ($hook === null) {
            $hook = get_current_screen()->id ?? '';
        }
        
        // Check if it's an HMS admin page
        return strpos($hook, 'hms_') !== false || 
               strpos($hook, 'hospital-management-system') !== false ||
               (isset($_GET['page']) && strpos($_GET['page'], 'hms_') === 0);
    }

    /**
     * Check if current page needs DataTables.
     */
    private function page_needs_datatable($hook) {
        $datatable_pages = array(
            'hms_patients',
            'hms_appointments', 
            'hms_prescriptions',
            'hms_billing',
            'hms_pharmacy',
            'hms_lab_tests',
            'hms_medical_records',
            'hms_staff',
            'hms_reports'
        );
        
        $current_page = $_GET['page'] ?? '';
        return in_array($current_page, $datatable_pages);
    }

    /**
     * Generate standardized page header.
     */
    public static function render_page_header($title, $add_new_url = '', $add_new_text = '') {
        ?>
        <div class="hms-page-header">
            <h1 class="wp-heading-inline"><?php echo esc_html($title); ?></h1>
            <?php if ($add_new_url && $add_new_text): ?>
                <a href="<?php echo esc_url($add_new_url); ?>" class="page-title-action"><?php echo esc_html($add_new_text); ?></a>
            <?php endif; ?>
            <hr class="wp-header-end">
        </div>
        <?php
    }

    /**
     * Generate breadcrumb navigation.
     */
    public static function render_breadcrumbs($breadcrumbs = array()) {
        if (empty($breadcrumbs)) {
            return;
        }
        ?>
        <nav aria-label="<?php esc_attr_e('Breadcrumb', 'hospital-management-system'); ?>">
            <ol class="hms-breadcrumb">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php $is_last = $index === count($breadcrumbs) - 1; ?>
                    <li class="hms-breadcrumb-item <?php echo $is_last ? 'active' : ''; ?>">
                        <?php if (!$is_last && !empty($crumb['url'])): ?>
                            <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['text']); ?></a>
                        <?php else: ?>
                            <?php echo esc_html($crumb['text']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
    }

    /**
     * Render standardized form section.
     */
    public static function render_form_section($title, $content, $description = '') {
        ?>
        <div class="hms-form-section">
            <?php if ($title): ?>
                <h3><?php echo esc_html($title); ?></h3>
            <?php endif; ?>
            <?php if ($description): ?>
                <p class="hms-text-muted"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
            <?php echo $content; ?>
        </div>
        <?php
    }

    /**
     * Render form field with label and validation.
     */
    public static function render_form_field($args) {
        $defaults = array(
            'type' => 'text',
            'name' => '',
            'id' => '',
            'label' => '',
            'value' => '',
            'placeholder' => '',
            'required' => false,
            'description' => '',
            'options' => array(),
            'attributes' => array(),
            'wrapper_class' => 'hms-form-group',
            'field_class' => '',
        );
        
        $args = wp_parse_args($args, $defaults);
        
        if (empty($args['id'])) {
            $args['id'] = $args['name'];
        }
        
        $field_class = 'hms-' . $args['type'];
        if ($args['field_class']) {
            $field_class .= ' ' . $args['field_class'];
        }
        
        $attributes = '';
        foreach ($args['attributes'] as $attr => $val) {
            $attributes .= ' ' . esc_attr($attr) . '="' . esc_attr($val) . '"';
        }
        
        if ($args['required']) {
            $attributes .= ' required';
        }
        
        if ($args['placeholder']) {
            $attributes .= ' placeholder="' . esc_attr($args['placeholder']) . '"';
        }
        ?>
        <div class="<?php echo esc_attr($args['wrapper_class']); ?>">
            <?php if ($args['label']): ?>
                <label for="<?php echo esc_attr($args['id']); ?>" class="hms-label <?php echo $args['required'] ? 'required' : ''; ?>">
                    <?php echo esc_html($args['label']); ?>
                </label>
            <?php endif; ?>
            
            <?php if ($args['type'] === 'select'): ?>
                <select name="<?php echo esc_attr($args['name']); ?>" id="<?php echo esc_attr($args['id']); ?>" class="<?php echo esc_attr($field_class); ?>" <?php echo $attributes; ?>>
                    <?php foreach ($args['options'] as $value => $text): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($args['value'], $value); ?>>
                            <?php echo esc_html($text); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($args['type'] === 'textarea'): ?>
                <textarea name="<?php echo esc_attr($args['name']); ?>" id="<?php echo esc_attr($args['id']); ?>" class="<?php echo esc_attr($field_class); ?>" <?php echo $attributes; ?>><?php echo esc_textarea($args['value']); ?></textarea>
            <?php else: ?>
                <input type="<?php echo esc_attr($args['type']); ?>" name="<?php echo esc_attr($args['name']); ?>" id="<?php echo esc_attr($args['id']); ?>" class="<?php echo esc_attr($field_class); ?>" value="<?php echo esc_attr($args['value']); ?>" <?php echo $attributes; ?>>
            <?php endif; ?>
            
            <?php if ($args['description']): ?>
                <span class="hms-help-text"><?php echo esc_html($args['description']); ?></span>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render metric cards for dashboard.
     */
    public static function render_metric_cards($metrics) {
        if (empty($metrics)) {
            return;
        }
        ?>
        <div class="hms-row hms-mb-4">
            <?php foreach ($metrics as $metric): ?>
                <div class="hms-col-3 hms-col-sm-6 hms-col-xs-12">
                    <div class="hms-metric-card">
                        <div class="hms-metric-number" style="color: <?php echo esc_attr($metric['color'] ?? '#2271b1'); ?>">
                            <?php echo esc_html($metric['value']); ?>
                        </div>
                        <div class="hms-metric-label">
                            <?php echo esc_html($metric['label']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render action buttons for tables.
     */
    public static function render_action_buttons($actions, $item_id) {
        if (empty($actions)) {
            return '';
        }
        
        $html = '<div class="hms-btn-group">';
        
        foreach ($actions as $action) {
            $url = str_replace('{id}', $item_id, $action['url']);
            $class = 'hms-btn hms-btn-sm hms-btn-' . ($action['type'] ?? 'secondary');
            
            if (!empty($action['confirm'])) {
                $class .= '" data-confirm="' . esc_attr($action['confirm']);
            }
            
            $html .= sprintf(
                '<a href="%s" class="%s" title="%s">%s</a>',
                esc_url($url),
                esc_attr($class),
                esc_attr($action['title'] ?? $action['text']),
                esc_html($action['text'])
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }
}

// Initialize the UI Framework
HMS_UI_Framework::instance();