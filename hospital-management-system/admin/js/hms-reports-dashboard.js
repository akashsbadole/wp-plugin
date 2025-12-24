/**
 * HMS Reports Dashboard JavaScript
 * 
 * Handles dashboard interactions, chart rendering, and data visualization
 */

(function($) {
    'use strict';

    // Dashboard object to manage all functionality
    const HMSDashboard = {
        charts: {},
        currentDateRange: 'current_month',
        
        init: function() {
            this.bindEvents();
            this.loadDashboard();
        },

        bindEvents: function() {
            // Date range selector
            $('#dashboard_date_range').on('change', this.handleDateRangeChange.bind(this));
            
            // Refresh dashboard
            $('#refresh_dashboard').on('click', this.loadDashboard.bind(this));
            
            // Chart refresh buttons
            $('.hms-chart-refresh').on('click', this.refreshChart.bind(this));
            
            // Export buttons
            $('#export_dashboard_pdf').on('click', this.exportDashboardPDF.bind(this));
            $('#export_dashboard_excel').on('click', this.exportDashboardExcel.bind(this));
            $('#schedule_report').on('click', this.scheduleReport.bind(this));
        },

        handleDateRangeChange: function(e) {
            const selectedRange = $(e.target).val();
            this.currentDateRange = selectedRange;
            
            if (selectedRange === 'custom') {
                $('.hms-custom-date-range').show();
            } else {
                $('.hms-custom-date-range').hide();
                this.setDateRangeValues(selectedRange);
            }
        },

        setDateRangeValues: function(range) {
            const today = new Date();
            let startDate, endDate;

            switch (range) {
                case 'current_month':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'last_month':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'last_3_months':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                    endDate = today;
                    break;
                case 'last_6_months':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 6, 1);
                    endDate = today;
                    break;
                case 'current_year':
                    startDate = new Date(today.getFullYear(), 0, 1);
                    endDate = today;
                    break;
                default:
                    return;
            }

            $('#dashboard_start_date').val(this.formatDate(startDate));
            $('#dashboard_end_date').val(this.formatDate(endDate));
        },

        formatDate: function(date) {
            return date.toISOString().split('T')[0];
        },

        getDateRange: function() {
            if (this.currentDateRange === 'custom') {
                return {
                    start_date: $('#dashboard_start_date').val(),
                    end_date: $('#dashboard_end_date').val()
                };
            } else {
                this.setDateRangeValues(this.currentDateRange);
                return {
                    start_date: $('#dashboard_start_date').val(),
                    end_date: $('#dashboard_end_date').val()
                };
            }
        },

        showLoading: function() {
            $('#hms-dashboard-loading').show();
        },

        hideLoading: function() {
            $('#hms-dashboard-loading').hide();
        },

        loadDashboard: function() {
            this.showLoading();
            
            Promise.all([
                this.loadKPIs(),
                this.loadCharts(),
                this.loadQuickReports()
            ]).then(() => {
                this.hideLoading();
            }).catch((error) => {
                console.error('Dashboard loading error:', error);
                this.hideLoading();
                this.showError('Failed to load dashboard data. Please try again.');
            });
        },

        loadKPIs: function() {
            const dateRange = this.getDateRange();
            
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_dashboard_kpis',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    this.updateKPIs(response.data);
                } else {
                    console.error('KPI loading failed:', response.data);
                }
            });
        },

        updateKPIs: function(data) {
            // Update KPI values
            $('[data-kpi="total_patients"]').text(this.formatNumber(data.total_patients || 0));
            $('[data-kpi="total_appointments"]').text(this.formatNumber(data.total_appointments || 0));
            $('[data-kpi="total_revenue"]').text(this.formatCurrency(data.total_revenue || 0));
            $('[data-kpi="pending_bills"]').text(this.formatNumber(data.pending_bills || 0));

            // Update trends
            this.updateTrend('patients', data.patients_trend);
            this.updateTrend('appointments', data.appointments_trend);
            this.updateTrend('revenue', data.revenue_trend);
            this.updateTrend('pending_bills', data.pending_bills_trend);
        },

        updateTrend: function(type, trendData) {
            const trendElement = $(`[data-trend="${type}"]`);
            
            if (!trendData) {
                trendElement.text('No data').removeClass('positive negative neutral').addClass('neutral');
                return;
            }

            const { percentage, direction } = trendData;
            const trendText = `${direction === 'up' ? '↑' : direction === 'down' ? '↓' : '→'} ${Math.abs(percentage)}%`;
            
            trendElement.text(trendText)
                .removeClass('positive negative neutral')
                .addClass(direction === 'up' ? 'positive' : direction === 'down' ? 'negative' : 'neutral');
        },

        loadCharts: function() {
            const dateRange = this.getDateRange();
            
            return Promise.all([
                this.loadPatientTrendChart(dateRange),
                this.loadRevenueDepartmentChart(dateRange),
                this.loadAppointmentStatusChart(dateRange),
                this.loadTopDiagnosesChart(dateRange)
            ]);
        },

        loadPatientTrendChart: function(dateRange) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_patient_trend_data',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    this.renderPatientTrendChart(response.data);
                }
            });
        },

        renderPatientTrendChart: function(data) {
            const ctx = document.getElementById('patient_trend_chart');
            if (!ctx) return;

            // Destroy existing chart if it exists
            if (this.charts.patientTrend) {
                this.charts.patientTrend.destroy();
            }

            this.charts.patientTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'New Patients',
                        data: data.values || [],
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        },

        loadRevenueDepartmentChart: function(dateRange) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_revenue_department_data',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    this.renderRevenueDepartmentChart(response.data);
                }
            });
        },

        renderRevenueDepartmentChart: function(data) {
            const ctx = document.getElementById('revenue_department_chart');
            if (!ctx) return;

            if (this.charts.revenueDepartment) {
                this.charts.revenueDepartment.destroy();
            }

            this.charts.revenueDepartment = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.values || [],
                        backgroundColor: [
                            '#0073aa', '#00a32a', '#ff6900', '#e74c3c',
                            '#9b59b6', '#f39c12', '#1abc9c', '#34495e'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        loadAppointmentStatusChart: function(dateRange) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_appointment_status_data',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    this.renderAppointmentStatusChart(response.data);
                }
            });
        },

        renderAppointmentStatusChart: function(data) {
            const ctx = document.getElementById('appointment_status_chart');
            if (!ctx) return;

            if (this.charts.appointmentStatus) {
                this.charts.appointmentStatus.destroy();
            }

            this.charts.appointmentStatus = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Appointments',
                        data: data.values || [],
                        backgroundColor: '#0073aa',
                        borderColor: '#005a87',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        },

        loadTopDiagnosesChart: function(dateRange) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_top_diagnoses_data',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    this.renderTopDiagnosesChart(response.data);
                }
            });
        },

        renderTopDiagnosesChart: function(data) {
            const ctx = document.getElementById('top_diagnoses_chart');
            if (!ctx) return;

            if (this.charts.topDiagnoses) {
                this.charts.topDiagnoses.destroy();
            }

            this.charts.topDiagnoses = new Chart(ctx, {
                type: 'horizontalBar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Cases',
                        data: data.values || [],
                        backgroundColor: '#00a32a',
                        borderColor: '#007a20',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        },

        loadQuickReports: function() {
            const dateRange = this.getDateRange();
            
            return Promise.all([
                this.loadTodaysSummary(),
                this.loadDepartmentPerformance(dateRange),
                this.loadRecentActivities()
            ]);
        },

        loadTodaysSummary: function() {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_todays_summary',
                    nonce: hms_admin_vars.nonce
                }
            }).done((response) => {
                if (response.success) {
                    $('#todays_summary').html(response.data.html);
                } else {
                    $('#todays_summary').html('<p>Unable to load today\'s summary.</p>');
                }
            });
        },

        loadDepartmentPerformance: function(dateRange) {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_department_performance',
                    nonce: hms_admin_vars.nonce,
                    ...dateRange
                }
            }).done((response) => {
                if (response.success) {
                    $('#department_performance').html(response.data.html);
                } else {
                    $('#department_performance').html('<p>Unable to load department performance.</p>');
                }
            });
        },

        loadRecentActivities: function() {
            return $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'hms_get_recent_activities',
                    nonce: hms_admin_vars.nonce
                }
            }).done((response) => {
                if (response.success) {
                    $('#recent_activities').html(response.data.html);
                } else {
                    $('#recent_activities').html('<p>Unable to load recent activities.</p>');
                }
            });
        },

        refreshChart: function(e) {
            const chartType = $(e.target).data('chart');
            const dateRange = this.getDateRange();
            
            switch (chartType) {
                case 'patient_trend':
                    this.loadPatientTrendChart(dateRange);
                    break;
                case 'revenue_department':
                    this.loadRevenueDepartmentChart(dateRange);
                    break;
                case 'appointment_status':
                    this.loadAppointmentStatusChart(dateRange);
                    break;
                case 'top_diagnoses':
                    this.loadTopDiagnosesChart(dateRange);
                    break;
            }
        },

        exportDashboardPDF: function() {
            this.showError('PDF export functionality will be implemented in a future update.');
        },

        exportDashboardExcel: function() {
            this.showError('Excel export functionality will be implemented in a future update.');
        },

        scheduleReport: function() {
            this.showError('Report scheduling functionality will be implemented in a future update.');
        },

        formatNumber: function(num) {
            return new Intl.NumberFormat().format(num);
        },

        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        showError: function(message) {
            // Create a simple error notification
            const errorDiv = $('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>');
            $('.hms-reports-dashboard').prepend(errorDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                errorDiv.fadeOut(() => errorDiv.remove());
            }, 5000);
        }
    };

    // Initialize dashboard when document is ready
    $(document).ready(function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is required for dashboard functionality');
            return;
        }
        
        HMSDashboard.init();
    });

})(jQuery);