<div class="wrap hms-admin-wrap">
    <h1><?php esc_html_e('Reports & Analytics', 'hospital-management-system'); ?></h1>
    
    <div class="report-filters">
        <div class="filter-group">
            <label for="start-date"><?php esc_html_e('Start Date', 'hospital-management-system'); ?></label>
            <input type="date" id="start-date" name="start_date" value="<?php echo gmdate('Y-m-01'); ?>">
        </div>
        
        <div class="filter-group">
            <label for="end-date"><?php esc_html_e('End Date', 'hospital-management-system'); ?></label>
            <input type="date" id="end-date" name="end_date" value="<?php echo gmdate('Y-m-d'); ?>">
        </div>
        
        <div class="filter-group">
            <button type="button" class="button generate-report" data-report-type="patient_census"><?php esc_html_e('Patient Census', 'hospital-management-system'); ?></button>
        </div>
        
        <div class="filter-group">
            <button type="button" class="button generate-report" data-report-type="revenue"><?php esc_html_e('Revenue Report', 'hospital-management-system'); ?></button>
        </div>
        
        <div class="filter-group">
            <button type="button" class="button generate-report" data-report-type="insurance"><?php esc_html_e('Insurance Report', 'hospital-management-system'); ?></button>
        </div>
        
        <div class="filter-group">
            <button type="button" class="button generate-report" data-report-type="pharmacy_sales"><?php esc_html_e('Pharmacy Sales', 'hospital-management-system'); ?></button>
        </div>
    </div>
    
    <div id="report-container">
        <div class="hms-notice hms-notice-info">
            <p><?php esc_html_e('Select a date range and click on a report button to generate a report.', 'hospital-management-system'); ?></p>
        </div>
    </div>
</div>