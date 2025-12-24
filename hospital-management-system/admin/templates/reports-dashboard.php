<?php
/**
 * Admin Template for Reports Dashboard with KPIs and Analytics.
 *
 * @package Hospital_Management_System
 * @subpackage Admin/Templates
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Get current date ranges for default filters
$current_month_start = gmdate('Y-m-01');
$current_month_end = gmdate('Y-m-t');
$last_month_start = gmdate('Y-m-01', strtotime('-1 month'));
$last_month_end = gmdate('Y-m-t', strtotime('-1 month'));
?>

<div class="wrap hms-reports-dashboard">
    <h1><?php esc_html_e('Hospital Management System - Analytics Dashboard', 'hospital-management-system'); ?></h1>
    
    <!-- Dashboard Filters -->
    <div class="hms-dashboard-filters">
        <div class="hms-filter-row">
            <div class="hms-filter-group">
                <label for="dashboard_date_range"><?php esc_html_e('Date Range:', 'hospital-management-system'); ?></label>
                <select id="dashboard_date_range" class="hms-select">
                    <option value="current_month"><?php esc_html_e('Current Month', 'hospital-management-system'); ?></option>
                    <option value="last_month"><?php esc_html_e('Last Month', 'hospital-management-system'); ?></option>
                    <option value="last_3_months"><?php esc_html_e('Last 3 Months', 'hospital-management-system'); ?></option>
                    <option value="last_6_months"><?php esc_html_e('Last 6 Months', 'hospital-management-system'); ?></option>
                    <option value="current_year"><?php esc_html_e('Current Year', 'hospital-management-system'); ?></option>
                    <option value="custom"><?php esc_html_e('Custom Range', 'hospital-management-system'); ?></option>
                </select>
            </div>
            <div class="hms-filter-group hms-custom-date-range" style="display: none;">
                <label for="dashboard_start_date"><?php esc_html_e('Start Date:', 'hospital-management-system'); ?></label>
                <input type="date" id="dashboard_start_date" class="hms-input" value="<?php echo esc_attr($current_month_start); ?>">
            </div>
            <div class="hms-filter-group hms-custom-date-range" style="display: none;">
                <label for="dashboard_end_date"><?php esc_html_e('End Date:', 'hospital-management-system'); ?></label>
                <input type="date" id="dashboard_end_date" class="hms-input" value="<?php echo esc_attr($current_month_end); ?>">
            </div>
            <div class="hms-filter-group">
                <button id="refresh_dashboard" class="hms-button hms-button-primary"><?php esc_html_e('Refresh Dashboard', 'hospital-management-system'); ?></button>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="hms-kpi-grid">
        <div class="hms-kpi-card" id="kpi-total-patients">
            <div class="hms-kpi-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="hms-kpi-content">
                <h3><?php esc_html_e('Total Patients', 'hospital-management-system'); ?></h3>
                <div class="hms-kpi-value">
                    <span class="hms-kpi-number" data-kpi="total_patients">-</span>
                </div>
                <div class="hms-kpi-change">
                    <span class="hms-kpi-trend" data-trend="patients"></span>
                </div>
            </div>
        </div>

        <div class="hms-kpi-card" id="kpi-total-appointments">
            <div class="hms-kpi-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="hms-kpi-content">
                <h3><?php esc_html_e('Appointments', 'hospital-management-system'); ?></h3>
                <div class="hms-kpi-value">
                    <span class="hms-kpi-number" data-kpi="total_appointments">-</span>
                </div>
                <div class="hms-kpi-change">
                    <span class="hms-kpi-trend" data-trend="appointments"></span>
                </div>
            </div>
        </div>

        <div class="hms-kpi-card" id="kpi-total-revenue">
            <div class="hms-kpi-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="hms-kpi-content">
                <h3><?php esc_html_e('Total Revenue', 'hospital-management-system'); ?></h3>
                <div class="hms-kpi-value">
                    <span class="hms-kpi-number" data-kpi="total_revenue">-</span>
                </div>
                <div class="hms-kpi-change">
                    <span class="hms-kpi-trend" data-trend="revenue"></span>
                </div>
            </div>
        </div>

        <div class="hms-kpi-card" id="kpi-pending-bills">
            <div class="hms-kpi-icon">
                <span class="dashicons dashicons-warning"></span>
            </div>
            <div class="hms-kpi-content">
                <h3><?php esc_html_e('Pending Bills', 'hospital-management-system'); ?></h3>
                <div class="hms-kpi-value">
                    <span class="hms-kpi-number" data-kpi="pending_bills">-</span>
                </div>
                <div class="hms-kpi-change">
                    <span class="hms-kpi-trend" data-trend="pending_bills"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Visualizations -->
    <div class="hms-charts-grid">
        <!-- Patient Registration Trend Chart -->
        <div class="hms-chart-container">
            <div class="hms-chart-header">
                <h3><?php esc_html_e('Patient Registration Trend', 'hospital-management-system'); ?></h3>
                <div class="hms-chart-controls">
                    <button class="hms-chart-refresh" data-chart="patient_trend"><?php esc_html_e('Refresh', 'hospital-management-system'); ?></button>
                </div>
            </div>
            <div class="hms-chart-content">
                <canvas id="patient_trend_chart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Revenue by Department Chart -->
        <div class="hms-chart-container">
            <div class="hms-chart-header">
                <h3><?php esc_html_e('Revenue by Department', 'hospital-management-system'); ?></h3>
                <div class="hms-chart-controls">
                    <button class="hms-chart-refresh" data-chart="revenue_department"><?php esc_html_e('Refresh', 'hospital-management-system'); ?></button>
                </div>
            </div>
            <div class="hms-chart-content">
                <canvas id="revenue_department_chart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Appointment Status Distribution -->
        <div class="hms-chart-container">
            <div class="hms-chart-header">
                <h3><?php esc_html_e('Appointment Status Distribution', 'hospital-management-system'); ?></h3>
                <div class="hms-chart-controls">
                    <button class="hms-chart-refresh" data-chart="appointment_status"><?php esc_html_e('Refresh', 'hospital-management-system'); ?></button>
                </div>
            </div>
            <div class="hms-chart-content">
                <canvas id="appointment_status_chart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Top Diagnoses Chart -->
        <div class="hms-chart-container">
            <div class="hms-chart-header">
                <h3><?php esc_html_e('Top Diagnoses', 'hospital-management-system'); ?></h3>
                <div class="hms-chart-controls">
                    <button class="hms-chart-refresh" data-chart="top_diagnoses"><?php esc_html_e('Refresh', 'hospital-management-system'); ?></button>
                </div>
            </div>
            <div class="hms-chart-content">
                <canvas id="top_diagnoses_chart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Reports Section -->
    <div class="hms-quick-reports">
        <h2><?php esc_html_e('Quick Reports', 'hospital-management-system'); ?></h2>
        <div class="hms-quick-reports-grid">
            <div class="hms-quick-report-card">
                <h4><?php esc_html_e('Today\'s Summary', 'hospital-management-system'); ?></h4>
                <div id="todays_summary" class="hms-summary-content">
                    <div class="hms-loading"><?php esc_html_e('Loading...', 'hospital-management-system'); ?></div>
                </div>
            </div>
            
            <div class="hms-quick-report-card">
                <h4><?php esc_html_e('Department Performance', 'hospital-management-system'); ?></h4>
                <div id="department_performance" class="hms-summary-content">
                    <div class="hms-loading"><?php esc_html_e('Loading...', 'hospital-management-system'); ?></div>
                </div>
            </div>
            
            <div class="hms-quick-report-card">
                <h4><?php esc_html_e('Recent Activities', 'hospital-management-system'); ?></h4>
                <div id="recent_activities" class="hms-summary-content">
                    <div class="hms-loading"><?php esc_html_e('Loading...', 'hospital-management-system'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="hms-export-section">
        <h2><?php esc_html_e('Export Dashboard Data', 'hospital-management-system'); ?></h2>
        <div class="hms-export-options">
            <button id="export_dashboard_pdf" class="hms-button hms-button-secondary">
                <span class="dashicons dashicons-pdf"></span>
                <?php esc_html_e('Export as PDF', 'hospital-management-system'); ?>
            </button>
            <button id="export_dashboard_excel" class="hms-button hms-button-secondary">
                <span class="dashicons dashicons-media-spreadsheet"></span>
                <?php esc_html_e('Export as Excel', 'hospital-management-system'); ?>
            </button>
            <button id="schedule_report" class="hms-button hms-button-secondary">
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Schedule Report', 'hospital-management-system'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="hms-dashboard-loading" class="hms-loading-overlay" style="display: none;">
    <div class="hms-loading-content">
        <div class="hms-spinner"></div>
        <p><?php esc_html_e('Loading dashboard data...', 'hospital-management-system'); ?></p>
    </div>
</div>