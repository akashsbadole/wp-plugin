<?php
/**
 * HMS Data Export and Import Template
 *
 * @package HMS
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('export_data') && !current_user_can('import_data')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'hospital-management-system'));
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Data Export & Import', 'hospital-management-system'); ?></h1>
    
    <!-- Breadcrumb Navigation -->
    <div class="hms-breadcrumb">
        <a href="<?php echo esc_url(admin_url('admin.php?page=hms_dashboard')); ?>"><?php esc_html_e('Dashboard', 'hospital-management-system'); ?></a>
        <span class="separator">&raquo;</span>
        <span><?php esc_html_e('Data Export & Import', 'hospital-management-system'); ?></span>
    </div>

    <div class="hms-export-import-container">
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper">
            <a href="#export-tab" class="nav-tab nav-tab-active" id="export-tab-link"><?php esc_html_e('Export Data', 'hospital-management-system'); ?></a>
            <a href="#import-tab" class="nav-tab" id="import-tab-link"><?php esc_html_e('Import Data', 'hospital-management-system'); ?></a>
        </nav>

        <!-- Export Tab Content -->
        <div id="export-tab" class="tab-content active">
            <div class="hms-export-section">
                <h2><?php esc_html_e('Export HMS Data', 'hospital-management-system'); ?></h2>
                <p><?php esc_html_e('Select the modules you want to export and choose your preferred format. The export will include all data from the selected modules.', 'hospital-management-system'); ?></p>

                <form id="hms-export-form" class="hms-form">
                    <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                    
                    <!-- Module Selection -->
                    <div class="form-section">
                        <h3><?php esc_html_e('Select Modules to Export', 'hospital-management-system'); ?></h3>
                        <div class="module-selection-grid" id="export-modules-grid">
                            <!-- Modules will be loaded via AJAX -->
                            <div class="loading-spinner">
                                <span class="spinner is-active"></span>
                                <span><?php esc_html_e('Loading available modules...', 'hospital-management-system'); ?></span>
                            </div>
                        </div>
                        
                        <div class="module-actions">
                            <button type="button" id="select-all-modules" class="button"><?php esc_html_e('Select All', 'hospital-management-system'); ?></button>
                            <button type="button" id="deselect-all-modules" class="button"><?php esc_html_e('Deselect All', 'hospital-management-system'); ?></button>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="form-section">
                        <h3><?php esc_html_e('Export Options', 'hospital-management-system'); ?></h3>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="export-format"><?php esc_html_e('Export Format', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <select id="export-format" name="format" required>
                                        <option value="csv"><?php esc_html_e('CSV (Comma Separated Values)', 'hospital-management-system'); ?></option>
                                        <option value="excel"><?php esc_html_e('Excel (XLSX)', 'hospital-management-system'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Choose the format for your exported data.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="date-range"><?php esc_html_e('Date Range', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <select id="date-range" name="date_range">
                                        <option value="all"><?php esc_html_e('All Data', 'hospital-management-system'); ?></option>
                                        <option value="last_30_days"><?php esc_html_e('Last 30 Days', 'hospital-management-system'); ?></option>
                                        <option value="last_90_days"><?php esc_html_e('Last 90 Days', 'hospital-management-system'); ?></option>
                                        <option value="last_year"><?php esc_html_e('Last Year', 'hospital-management-system'); ?></option>
                                        <option value="custom"><?php esc_html_e('Custom Range', 'hospital-management-system'); ?></option>
                                    </select>
                                    <p class="description"><?php esc_html_e('Filter data by creation date.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="custom-date-range" style="display: none;">
                                <th scope="row">
                                    <label><?php esc_html_e('Custom Date Range', 'hospital-management-system'); ?></label>
                                </th>
                                <td>
                                    <input type="date" id="start-date" name="start_date" />
                                    <span><?php esc_html_e('to', 'hospital-management-system'); ?></span>
                                    <input type="date" id="end-date" name="end_date" />
                                    <p class="description"><?php esc_html_e('Select start and end dates for custom range.', 'hospital-management-system'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Export Actions -->
                    <div class="form-section">
                        <button type="submit" id="start-export" class="button button-primary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Start Export', 'hospital-management-system'); ?>
                        </button>
                    </div>
                </form>

                <!-- Export Progress -->
                <div id="export-progress" class="progress-section" style="display: none;">
                    <h3><?php esc_html_e('Export Progress', 'hospital-management-system'); ?></h3>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="export-progress-fill"></div>
                        </div>
                        <span class="progress-text" id="export-progress-text">0%</span>
                    </div>
                    <div class="progress-details" id="export-progress-details">
                        <p><?php esc_html_e('Preparing export...', 'hospital-management-system'); ?></p>
                    </div>
                    <div class="progress-actions" id="export-progress-actions" style="display: none;">
                        <button type="button" id="download-export" class="button button-primary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Download Export File', 'hospital-management-system'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Tab Content -->
        <div id="import-tab" class="tab-content">
            <div class="hms-import-section">
                <h2><?php esc_html_e('Import HMS Data', 'hospital-management-system'); ?></h2>
                <p><?php esc_html_e('Upload a CSV or Excel file to import data into HMS. The file will be validated before import to ensure data integrity.', 'hospital-management-system'); ?></p>

                <!-- Import Steps -->
                <div class="import-steps">
                    <div class="step active" id="step-1">
                        <span class="step-number">1</span>
                        <span class="step-title"><?php esc_html_e('Upload File', 'hospital-management-system'); ?></span>
                    </div>
                    <div class="step" id="step-2">
                        <span class="step-number">2</span>
                        <span class="step-title"><?php esc_html_e('Validate & Map', 'hospital-management-system'); ?></span>
                    </div>
                    <div class="step" id="step-3">
                        <span class="step-number">3</span>
                        <span class="step-title"><?php esc_html_e('Import Data', 'hospital-management-system'); ?></span>
                    </div>
                </div>

                <!-- Step 1: File Upload -->
                <div id="import-step-1" class="import-step active">
                    <h3><?php esc_html_e('Step 1: Upload Import File', 'hospital-management-system'); ?></h3>
                    
                    <form id="hms-import-upload-form" class="hms-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                        
                        <div class="file-upload-area" id="file-upload-area">
                            <div class="upload-icon">
                                <span class="dashicons dashicons-upload"></span>
                            </div>
                            <div class="upload-text">
                                <p><strong><?php esc_html_e('Choose a file or drag it here', 'hospital-management-system'); ?></strong></p>
                                <p><?php esc_html_e('Supported formats: CSV, Excel (.xlsx)', 'hospital-management-system'); ?></p>
                                <p><?php esc_html_e('Maximum file size: 10MB', 'hospital-management-system'); ?></p>
                            </div>
                            <input type="file" id="import-file" name="import_file" accept=".csv,.xlsx,.xls" style="display: none;" />
                            <button type="button" id="select-file-btn" class="button"><?php esc_html_e('Select File', 'hospital-management-system'); ?></button>
                        </div>
                        
                        <div id="file-info" class="file-info" style="display: none;">
                            <div class="file-details">
                                <span class="dashicons dashicons-media-default"></span>
                                <div class="file-meta">
                                    <div class="file-name" id="selected-file-name"></div>
                                    <div class="file-size" id="selected-file-size"></div>
                                </div>
                                <button type="button" id="remove-file" class="button-link-delete"><?php esc_html_e('Remove', 'hospital-management-system'); ?></button>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" id="upload-file-btn" class="button button-primary" disabled>
                                <?php esc_html_e('Upload & Continue', 'hospital-management-system'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Validation & Mapping -->
                <div id="import-step-2" class="import-step">
                    <h3><?php esc_html_e('Step 2: Validate & Map Fields', 'hospital-management-system'); ?></h3>
                    
                    <form id="hms-import-validate-form" class="hms-form">
                        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                        
                        <div class="form-section">
                            <label for="target-module"><?php esc_html_e('Target Module', 'hospital-management-system'); ?></label>
                            <select id="target-module" name="target_module" required>
                                <option value=""><?php esc_html_e('Select target module...', 'hospital-management-system'); ?></option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                            <p class="description"><?php esc_html_e('Choose which HMS module this data should be imported into.', 'hospital-management-system'); ?></p>
                        </div>
                        
                        <div id="validation-results" class="validation-results" style="display: none;">
                            <!-- Validation results will be displayed here -->
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" id="back-to-upload" class="button"><?php esc_html_e('Back to Upload', 'hospital-management-system'); ?></button>
                            <button type="submit" id="validate-file-btn" class="button button-primary" disabled>
                                <?php esc_html_e('Validate File', 'hospital-management-system'); ?>
                            </button>
                            <button type="button" id="proceed-to-import" class="button button-primary" style="display: none;">
                                <?php esc_html_e('Proceed to Import', 'hospital-management-system'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Import Process -->
                <div id="import-step-3" class="import-step">
                    <h3><?php esc_html_e('Step 3: Import Data', 'hospital-management-system'); ?></h3>
                    
                    <form id="hms-import-process-form" class="hms-form">
                        <?php wp_nonce_field('hms_admin_nonce', 'nonce'); ?>
                        
                        <div class="form-section">
                            <h4><?php esc_html_e('Duplicate Handling', 'hospital-management-system'); ?></h4>
                            <div class="duplicate-options">
                                <label>
                                    <input type="radio" name="duplicate_handling" value="skip" checked />
                                    <strong><?php esc_html_e('Skip Duplicates', 'hospital-management-system'); ?></strong>
                                    <p class="description"><?php esc_html_e('Skip records that already exist in the database.', 'hospital-management-system'); ?></p>
                                </label>
                                <label>
                                    <input type="radio" name="duplicate_handling" value="update" />
                                    <strong><?php esc_html_e('Update Existing', 'hospital-management-system'); ?></strong>
                                    <p class="description"><?php esc_html_e('Update existing records with new data from the import file.', 'hospital-management-system'); ?></p>
                                </label>
                                <label>
                                    <input type="radio" name="duplicate_handling" value="create_new" />
                                    <strong><?php esc_html_e('Create New Records', 'hospital-management-system'); ?></strong>
                                    <p class="description"><?php esc_html_e('Create new records even if duplicates exist.', 'hospital-management-system'); ?></p>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" id="back-to-validation" class="button"><?php esc_html_e('Back to Validation', 'hospital-management-system'); ?></button>
                            <button type="submit" id="start-import-btn" class="button button-primary">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e('Start Import', 'hospital-management-system'); ?>
                            </button>
                        </div>
                    </form>

                    <!-- Import Progress -->
                    <div id="import-progress" class="progress-section" style="display: none;">
                        <h4><?php esc_html_e('Import Progress', 'hospital-management-system'); ?></h4>
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill" id="import-progress-fill"></div>
                            </div>
                            <span class="progress-text" id="import-progress-text">0%</span>
                        </div>
                        <div class="progress-details" id="import-progress-details">
                            <p><?php esc_html_e('Preparing import...', 'hospital-management-system'); ?></p>
                        </div>
                        <div class="import-summary" id="import-summary" style="display: none;">
                            <!-- Import summary will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>