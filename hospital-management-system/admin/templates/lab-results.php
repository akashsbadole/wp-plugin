<?php
/**
 * Lab Results Entry Template
 *
 * @package Hospital_Management_System
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $hms_lab_radiology;

// Get pending and in-progress tests
$pending_tests = $hms_lab_radiology->get_lab_test_orders('pending');
$in_progress_tests = $hms_lab_radiology->get_lab_test_orders('in-progress');
$all_pending = array_merge($pending_tests, $in_progress_tests);

// Handle individual test result entry
$selected_order = null;
if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $selected_order = $hms_lab_radiology->get_lab_test_order($order_id);
}
?>

<div class="wrap hms-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Lab Results Entry', 'hospital-management-system'); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <?php if (isset($_GET['message'])) : ?>
        <div id="message" class="notice notice-<?php echo esc_attr(sanitize_text_field($_GET['type'])); ?> is-dismissible">
            <p><?php echo wp_kses_post(sanitize_text_field($_GET['message'])); ?></p>
        </div>
    <?php endif; ?>

    <div class="hms-results-dashboard">
        
        <?php if (!$selected_order): ?>
        <!-- Lab Results Dashboard -->
        <div class="hms-results-dashboard-stats">
            <?php
            $stats = $hms_lab_radiology->get_lab_statistics();
            ?>
            <div class="hms-stats-grid">
                <div class="hms-stat-card">
                    <div class="hms-stat-number"><?php echo esc_html($stats['pending']); ?></div>
                    <div class="hms-stat-label"><?php esc_html_e('Pending Tests', 'hospital-management-system'); ?></div>
                </div>
                <div class="hms-stat-card">
                    <div class="hms-stat-number"><?php echo esc_html($stats['in_progress']); ?></div>
                    <div class="hms-stat-label"><?php esc_html_e('In Progress', 'hospital-management-system'); ?></div>
                </div>
                <div class="hms-stat-card">
                    <div class="hms-stat-number"><?php echo esc_html($stats['today_orders']); ?></div>
                    <div class="hms-stat-label"><?php esc_html_e('Today\'s Orders', 'hospital-management-system'); ?></div>
                </div>
                <div class="hms-stat-card">
                    <div class="hms-stat-number"><?php echo esc_html($stats['week_completed']); ?></div>
                    <div class="hms-stat-label"><?php esc_html_e('Week Completed', 'hospital-management-system'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Pending Tests Overview -->
        <div class="hms-results-section">
            <h2><?php esc_html_e('Pending Test Results', 'hospital-management-system'); ?></h2>
            <p class="description">
                <?php esc_html_e('Select a test to enter results and update status. Tests are organized by priority and status.', 'hospital-management-system'); ?>
            </p>
            
            <?php if (!empty($all_pending)): ?>
                <div class="hms-pending-tests-grid">
                    <?php foreach ($all_pending as $order): ?>
                        <div class="hms-test-card">
                            <div class="hms-test-card-header">
                                <h4><?php echo esc_html($order->test_name); ?></h4>
                                <span class="hms-status-badge hms-status-<?php echo esc_attr(str_replace('-', '_', $order->status)); ?>">
                                    <?php echo esc_html(ucwords(str_replace('-', ' ', $order->status))); ?>
                                </span>
                            </div>
                            
                            <div class="hms-test-card-body">
                                <p><strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong> 
                                   <?php echo esc_html(trim($order->patient_first_name . ' ' . $order->patient_last_name)); ?></p>
                                <p><strong><?php esc_html_e('Ordered by:', 'hospital-management-system'); ?></strong> 
                                   <?php echo esc_html(trim($order->doctor_first_name . ' ' . $order->doctor_last_name)); ?></p>
                                <p><strong><?php esc_html_e('Order Date:', 'hospital-management-system'); ?></strong> 
                                   <?php echo esc_html(date('M j, Y', strtotime($order->order_date))); ?></p>
                                <?php if ($order->category): ?>
                                    <p><strong><?php esc_html_e('Category:', 'hospital-management-system'); ?></strong> 
                                       <?php echo esc_html($order->category); ?></p>
                                <?php endif; ?>
                                <?php if ($order->normal_range): ?>
                                    <p><strong><?php esc_html_e('Normal Range:', 'hospital-management-system'); ?></strong> 
                                       <?php echo esc_html($order->normal_range); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hms-test-card-actions">
                                <a href="<?php echo admin_url('admin.php?page=hms_lab_results&order_id=' . $order->id); ?>" 
                                   class="button button-primary">
                                    <?php esc_html_e('Enter Results', 'hospital-management-system'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order&id=' . $order->id); ?>" 
                                   class="button button-secondary">
                                    <?php esc_html_e('View Details', 'hospital-management-system'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="hms-no-data">
                    <div class="hms-no-data-message">
                        <p><?php esc_html_e('No pending test results found.', 'hospital-management-system'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=hms_lab_tests'); ?>" class="button button-primary">
                            <?php esc_html_e('View All Tests', 'hospital-management-system'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <!-- Results Entry Form -->
        <div class="hms-results-section">
            <div class="hms-results-header">
                <h2><?php esc_html_e('Enter Test Results', 'hospital-management-system'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=hms_lab_results'); ?>" class="button button-secondary">
                    <?php esc_html_e('← Back to Pending Tests', 'hospital-management-system'); ?>
                </a>
            </div>
            
            <div class="hms-test-info-card">
                <div class="hms-test-info-row">
                    <div class="hms-test-info-col">
                        <h4><?php echo esc_html($selected_order->test_name); ?></h4>
                        <p class="hms-test-category"><?php echo esc_html($selected_order->category); ?></p>
                    </div>
                    <div class="hms-test-info-col">
                        <p><strong><?php esc_html_e('Patient:', 'hospital-management-system'); ?></strong><br>
                           <?php echo esc_html(trim($selected_order->patient_first_name . ' ' . $selected_order->patient_last_name)); ?></p>
                    </div>
                    <div class="hms-test-info-col">
                        <p><strong><?php esc_html_e('Ordered by:', 'hospital-management-system'); ?></strong><br>
                           <?php echo esc_html(trim($selected_order->doctor_first_name . ' ' . $selected_order->doctor_last_name)); ?></p>
                    </div>
                    <div class="hms-test-info-col">
                        <p><strong><?php esc_html_e('Order Date:', 'hospital-management-system'); ?></strong><br>
                           <?php echo esc_html(date('M j, Y g:i A', strtotime($selected_order->order_date))); ?></p>
                    </div>
                </div>
                
                <?php if ($selected_order->normal_range): ?>
                    <div class="hms-normal-range">
                        <strong><?php esc_html_e('Normal Range:', 'hospital-management-system'); ?></strong>
                        <?php echo esc_html($selected_order->normal_range); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($selected_order->notes): ?>
                    <div class="hms-clinical-notes">
                        <strong><?php esc_html_e('Clinical Notes:', 'hospital-management-system'); ?></strong>
                        <p><?php echo esc_html($selected_order->notes); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post" class="hms-results-form" id="hms-results-form">
                <div class="hms-form-section">
                    <div class="hms-form-row">
                        <div class="hms-form-group hms-col-6">
                            <label for="status" class="hms-label required">
                                <?php esc_html_e('Test Status', 'hospital-management-system'); ?>
                            </label>
                            <select name="status" id="status" class="hms-select" required>
                                <option value="pending" <?php selected($selected_order->status, 'pending'); ?>>
                                    <?php esc_html_e('Pending', 'hospital-management-system'); ?>
                                </option>
                                <option value="in-progress" <?php selected($selected_order->status, 'in-progress'); ?>>
                                    <?php esc_html_e('In Progress', 'hospital-management-system'); ?>
                                </option>
                                <option value="completed" <?php selected($selected_order->status, 'completed'); ?>>
                                    <?php esc_html_e('Completed', 'hospital-management-system'); ?>
                                </option>
                                <?php if ($selected_order->status !== 'completed'): ?>
                                <option value="cancelled">
                                    <?php esc_html_e('Cancelled', 'hospital-management-system'); ?>
                                </option>
                                <?php endif; ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Update the test status based on current progress.', 'hospital-management-system'); ?>
                            </p>
                        </div>
                        
                        <div class="hms-form-group hms-col-6">
                            <label class="hms-label">
                                <?php esc_html_e('Status History', 'hospital-management-system'); ?>
                            </label>
                            <div class="hms-status-timeline">
                                <div class="hms-status-item <?php echo $selected_order->status === 'pending' ? 'active' : 'completed'; ?>">
                                    <span class="hms-status-dot"></span>
                                    <span class="hms-status-text"><?php esc_html_e('Pending', 'hospital-management-system'); ?></span>
                                    <span class="hms-status-time"><?php echo esc_html(date('M j, g:i A', strtotime($selected_order->order_date))); ?></span>
                                </div>
                                <?php if (in_array($selected_order->status, ['in-progress', 'completed'])): ?>
                                <div class="hms-status-item <?php echo $selected_order->status === 'in-progress' ? 'active' : 'completed'; ?>">
                                    <span class="hms-status-dot"></span>
                                    <span class="hms-status-text"><?php esc_html_e('In Progress', 'hospital-management-system'); ?></span>
                                    <span class="hms-status-time"><?php echo esc_html(date('M j, g:i A', strtotime($selected_order->updated_at))); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($selected_order->status === 'completed'): ?>
                                <div class="hms-status-item active">
                                    <span class="hms-status-dot"></span>
                                    <span class="hms-status-text"><?php esc_html_e('Completed', 'hospital-management-system'); ?></span>
                                    <span class="hms-status-time"><?php echo esc_html(date('M j, g:i A', strtotime($selected_order->updated_at))); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hms-form-row">
                        <div class="hms-form-group hms-col-12">
                            <label for="results" class="hms-label">
                                <?php esc_html_e('Test Results', 'hospital-management-system'); ?>
                                <span class="hms-required-indicator" id="results-required" style="display: none;">*</span>
                            </label>
                            
                            <!-- Quick Result Templates -->
                            <div class="hms-result-templates" style="margin-bottom: 10px;">
                                <label class="hms-label-small"><?php esc_html_e('Quick Templates:', 'hospital-management-system'); ?></label>
                                <button type="button" class="button button-small hms-template-btn" data-template="normal">
                                    <?php esc_html_e('Normal Result', 'hospital-management-system'); ?>
                                </button>
                                <button type="button" class="button button-small hms-template-btn" data-template="abnormal">
                                    <?php esc_html_e('Abnormal Result', 'hospital-management-system'); ?>
                                </button>
                                <button type="button" class="button button-small hms-template-btn" data-template="critical">
                                    <?php esc_html_e('Critical Result', 'hospital-management-system'); ?>
                                </button>
                            </div>
                            
                            <?php
                            wp_editor($selected_order->results ?: '', 'results', array(
                                'textarea_name' => 'results',
                                'textarea_rows' => 12,
                                'media_buttons' => false,
                                'teeny'         => false,
                                'quicktags'     => array('buttons' => 'strong,em,ul,ol,li,link,close'),
                                'tinymce'       => array(
                                    'toolbar1' => 'bold,italic,underline,separator,bullist,numlist,separator,link,unlink,separator,undo,redo',
                                    'toolbar2' => 'forecolor,backcolor,separator,alignleft,aligncenter,alignright',
                                    'setup' => 'function(editor) {
                                        editor.on("change", function() {
                                            editor.save();
                                            jQuery("#results").trigger("change");
                                        });
                                    }'
                                ),
                            ));
                            ?>
                            <p class="description">
                                <?php esc_html_e('Enter detailed test results, measurements, and observations. Use formatting to highlight important values. Results are required when marking test as completed.', 'hospital-management-system'); ?>
                            </p>
                            
                            <!-- Result Validation Indicators -->
                            <div class="hms-result-indicators" style="margin-top: 10px;">
                                <div class="hms-indicator" id="results-length-indicator">
                                    <span class="hms-indicator-label"><?php esc_html_e('Content Length:', 'hospital-management-system'); ?></span>
                                    <span class="hms-indicator-value" id="results-char-count">0</span>
                                    <span class="hms-indicator-text"><?php esc_html_e('characters', 'hospital-management-system'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hms-form-row">
                        <div class="hms-form-group hms-col-12">
                            <label for="notes" class="hms-label">
                                <?php esc_html_e('Additional Notes', 'hospital-management-system'); ?>
                            </label>
                            <textarea name="notes" id="notes" class="hms-textarea" rows="4" 
                                      placeholder="<?php esc_attr_e('Add any additional notes, recommendations, or follow-up instructions...', 'hospital-management-system'); ?>"><?php echo esc_textarea($selected_order->notes ?: ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="hms-form-actions">
                    <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                    <input type="hidden" name="save_lab_results" value="1">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($selected_order->id); ?>">
                    
                    <button type="submit" class="button button-primary button-large">
                        <?php esc_html_e('Save Results', 'hospital-management-system'); ?>
                    </button>
                    
                    <a href="<?php echo admin_url('admin.php?page=hms_lab_results'); ?>" class="button button-secondary button-large">
                        <?php esc_html_e('Cancel', 'hospital-management-system'); ?>
                    </a>
                    
                    <?php if ($selected_order->status === 'completed' && !empty($selected_order->results)): ?>
                        <a href="<?php echo admin_url('admin.php?page=hms_lab_test_order&id=' . $selected_order->id . '&action=generate_report'); ?>" 
                           class="button button-secondary button-large" target="_blank">
                            <?php esc_html_e('Preview Report', 'hospital-management-system'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Result templates
    var templates = {
        normal: '<p><strong>Result:</strong> Normal</p><p><strong>Interpretation:</strong> All values are within normal limits.</p><p><strong>Recommendation:</strong> No further action required.</p>',
        abnormal: '<p><strong>Result:</strong> Abnormal</p><p><strong>Findings:</strong> [Describe abnormal findings]</p><p><strong>Clinical Significance:</strong> [Explain significance]</p><p><strong>Recommendation:</strong> [Provide recommendations]</p>',
        critical: '<p><strong>Result:</strong> <span style="color: red; font-weight: bold;">CRITICAL</span></p><p><strong>Critical Values:</strong> [List critical values]</p><p><strong>Immediate Action Required:</strong> [Describe urgent actions needed]</p><p><strong>Physician Notification:</strong> Ordering physician notified immediately.</p>'
    };
    
    // Template button handlers
    $('.hms-template-btn').on('click', function(e) {
        e.preventDefault();
        var template = $(this).data('template');
        if (templates[template]) {
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('results')) {
                tinyMCE.get('results').setContent(templates[template]);
            } else {
                $('#results').val(templates[template]);
            }
            updateCharCount();
        }
    });
    
    // Character count update
    function updateCharCount() {
        var content = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('results')) {
            content = tinyMCE.get('results').getContent({format: 'text'});
        } else {
            content = $('#results').val();
        }
        $('#results-char-count').text(content.length);
    }
    
    // Auto-save functionality
    var autoSaveTimer;
    var hasUnsavedChanges = false;
    
    function triggerAutoSave() {
        clearTimeout(autoSaveTimer);
        hasUnsavedChanges = true;
        autoSaveTimer = setTimeout(function() {
            // Auto-save implementation could go here
            console.log('Auto-save triggered');
        }, 30000); // 30 seconds
    }
    
    // Monitor content changes
    $('#results, #notes').on('input change', function() {
        triggerAutoSave();
        updateCharCount();
    });
    
    // TinyMCE change handler
    if (typeof tinyMCE !== 'undefined') {
        $(document).on('tinymce-editor-init', function(event, editor) {
            if (editor.id === 'results') {
                editor.on('change keyup', function() {
                    triggerAutoSave();
                    updateCharCount();
                });
            }
        });
    }
    
    // Status change handling with enhanced validation
    $('#status').on('change', function() {
        var status = $(this).val();
        var currentResults = '';
        
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('results')) {
            currentResults = tinyMCE.get('results').getContent({format: 'text'}).trim();
        } else {
            currentResults = $('#results').val().trim();
        }
        
        // Show/hide required indicator
        if (status === 'completed') {
            $('#results-required').show();
            if (!currentResults) {
                alert('<?php esc_js_e('Please enter test results before marking as completed.', 'hospital-management-system'); ?>');
                $(this).val('in-progress');
                return false;
            }
        } else {
            $('#results-required').hide();
        }
        
        // Update status timeline visual feedback
        updateStatusTimeline(status);
    });
    
    // Update status timeline visual
    function updateStatusTimeline(newStatus) {
        $('.hms-status-item').removeClass('active pending');
        
        if (newStatus === 'pending') {
            $('.hms-status-item:first').addClass('active');
        } else if (newStatus === 'in-progress') {
            $('.hms-status-item:first').addClass('completed');
            $('.hms-status-item:nth-child(2)').addClass('active');
        } else if (newStatus === 'completed') {
            $('.hms-status-item:first, .hms-status-item:nth-child(2)').addClass('completed');
            $('.hms-status-item:last').addClass('active');
        }
    }
    
    // Enhanced form validation
    $('#hms-results-form').on('submit', function(e) {
        var status = $('#status').val();
        var results = '';
        
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('results')) {
            results = tinyMCE.get('results').getContent({format: 'text'}).trim();
        } else {
            results = $('#results').val().trim();
        }
        
        // Validate required fields
        if (status === 'completed' && !results) {
            e.preventDefault();
            alert('<?php esc_js_e('Test results are required when marking as completed.', 'hospital-management-system'); ?>');
            
            // Focus on results field
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get('results')) {
                tinyMCE.get('results').focus();
            } else {
                $('#results').focus();
            }
            return false;
        }
        
        // Confirm critical results
        if (results.toLowerCase().includes('critical') || results.toLowerCase().includes('urgent')) {
            if (!confirm('<?php esc_js_e('This result contains critical values. Have you notified the ordering physician? Click OK to continue or Cancel to review.', 'hospital-management-system'); ?>')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).text('<?php esc_js_e('Saving...', 'hospital-management-system'); ?>');
        hasUnsavedChanges = false;
    });
    
    // Warn about unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return '<?php esc_js_e('You have unsaved changes. Are you sure you want to leave?', 'hospital-management-system'); ?>';
        }
    });
    
    // Initialize character count
    updateCharCount();
    
    // Initialize status timeline
    var currentStatus = $('#status').val();
    updateStatusTimeline(currentStatus);
});
</script>

<style>
.hms-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.hms-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.hms-stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 5px;
}

.hms-stat-label {
    font-size: 0.9em;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hms-status-timeline {
    border-left: 2px solid #ddd;
    padding-left: 20px;
    margin-left: 10px;
}

.hms-status-item {
    position: relative;
    margin-bottom: 15px;
    padding-bottom: 10px;
}

.hms-status-dot {
    position: absolute;
    left: -26px;
    top: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    border: 2px solid #fff;
}

.hms-status-item.completed .hms-status-dot {
    background: #28a745;
}

.hms-status-item.active .hms-status-dot {
    background: #0073aa;
    animation: pulse 2s infinite;
}

.hms-status-text {
    font-weight: bold;
    display: block;
    margin-bottom: 2px;
}

.hms-status-time {
    font-size: 0.85em;
    color: #666;
}

.hms-result-templates {
    margin-bottom: 15px;
}

.hms-template-btn {
    margin-right: 10px;
    margin-bottom: 5px;
}

.hms-result-indicators {
    display: flex;
    gap: 20px;
    font-size: 0.9em;
    color: #666;
}

.hms-indicator-value {
    font-weight: bold;
    color: #0073aa;
}

.hms-required-indicator {
    color: #d63638;
    font-weight: bold;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
</style>