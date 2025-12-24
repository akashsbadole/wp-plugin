<?php

/**
 * HMS Reporting & Analytics
 *
 * This class handles the reporting and analytics functionalities, including
 * chart generation, PDF export, and custom dashboards.
 *
 * @package HospitalManagementSystem
 * @subpackage Includes
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class HMS_Reporting_Analytics {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->hooks();
    }

    /**
     * Register the hooks for the reporting and analytics features.
     */
    private function hooks() {
        // Enqueue scripts and styles for admin reports page
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_reports_assets' ) );

        // Add AJAX handlers for report generation
        add_action( 'wp_ajax_hms_generate_report', array( $this, 'ajax_generate_report' ) );
        add_action( 'wp_ajax_hms_export_report_pdf', array( $this, 'ajax_export_report_pdf' ) );
        add_action( 'wp_ajax_hms_export_report_csv', array( $this, 'ajax_export_report_csv' ) );

        // Add dashboard widgets
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
    }

    /**
     * Enqueue scripts and styles for the reports page.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_reports_assets( $hook ) {
        // Only load on our reports page
        if ( 'hospital-management-system_page_hms-reports' !== $hook ) {
            return;
        }

        // Enqueue Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '3.9.1', // Use a specific version
            true
        );

        // Enqueue our custom report script
        wp_enqueue_script(
            'hms-reports-js',
            HMS_PLUGIN_URL . 'admin/js/hms-reports.js',
            array( 'jquery', 'chartjs' ),
            $this->version,
            true
        );

        // Localize script with AJAX URL and nonce
        wp_localize_script( 'hms-reports-js', 'hms_reports_vars', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'hms_reports_nonce' ),
        ) );

        // Enqueue styles for reports page
        wp_enqueue_style(
            'hms-reports-css',
            HMS_PLUGIN_URL . 'admin/css/hms-reports.css',
            array(),
            $this->version
        );
    }

    /**
     * Add custom dashboard widgets.
     */
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'hms_patient_census_widget', // Widget slug.
            esc_html__( 'Patient Census (Today)', 'hospital-management-system' ), // Title.
            array( $this, 'render_patient_census_widget' ) // Display function.
        );
        wp_add_dashboard_widget(
            'hms_revenue_widget',
            esc_html__( 'Revenue Overview (This Month)', 'hospital-management-system' ),
            array( $this, 'render_revenue_widget' )
        );
        // Add more widgets as needed
    }

    /**
     * Render the Patient Census dashboard widget.
     */
    public function render_patient_census_widget() {
        $today_new_patients = $this->get_today_new_patients_count();
        $today_appointments = $this->get_today_appointments_count();
        // You can add more metrics like discharged patients, etc.
        ?>
        <p>
            <strong><?php esc_html_e( 'New Patients:', 'hospital-management-system' ); ?></strong>
            <?php echo esc_html( $today_new_patients ); ?>
        </p>
        <p>
            <strong><?php esc_html_e( 'Scheduled Appointments:', 'hospital-management-system' ); ?></strong>
            <?php echo esc_html( $today_appointments ); ?>
        </p>
        <?php
    }

    /**
     * Render the Revenue Overview dashboard widget.
     */
    public function render_revenue_widget() {
        $monthly_revenue = $this->get_monthly_revenue();
        $pending_payments = $this->get_pending_payments_total();
        ?>
        <p>
            <strong><?php esc_html_e( 'Total Revenue:', 'hospital-management-system' ); ?></strong>
            <?php echo esc_html( number_format( $monthly_revenue, 2 ) ); ?>
        </p>
        <p>
            <strong><?php esc_html_e( 'Pending Payments:', 'hospital-management-system' ); ?></strong>
            <?php echo esc_html( number_format( $pending_payments, 2 ) ); ?>
        </p>
        <?php
    }

    /**
     * AJAX handler to generate report data.
     */
    public function ajax_generate_report() {
        check_ajax_referer( 'hms_reports_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_reports' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to generate reports.', 'hospital-management-system' ) ) );
        }

        $report_type = sanitize_text_field( $_POST['report_type'] );
        $start_date  = sanitize_text_field( $_POST['start_date'] );
        $end_date    = sanitize_text_field( $_POST['end_date'] );

        $data = array();

        switch ( $report_type ) {
            case 'patient_census':
                $data = $this->get_patient_census_data( $start_date, $end_date );
                break;
            case 'revenue_report':
                $data = $this->get_revenue_report_data( $start_date, $end_date );
                break;
            // Add more report types here
            default:
                wp_send_json_error( array( 'message' => esc_html__( 'Invalid report type.', 'hospital-management-system' ) ) );
                break;
        }

        wp_send_json_success( $data );
    }

    /**
     * AJAX handler to export report to PDF.
     */
    public function ajax_export_report_pdf() {
        check_ajax_referer( 'hms_reports_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_reports' ) ) {
            wp_die( esc_html__( 'You do not have permission to export reports.', 'hospital-management-system' ) );
        }

        $report_type = sanitize_text_field( $_POST['report_type'] );
        $start_date  = sanitize_text_field( $_POST['start_date'] );
        $end_date    = sanitize_text_field( $_POST['end_date'] );
        $data        = json_decode( stripslashes( $_POST['report_data'] ), true );

        // This is a placeholder for PDF generation logic.
        // You would typically use a library like mPDF or Dompdf.
        // Example:
        // require_once HMS_PLUGIN_DIR . 'vendor/autoload.php'; // If using Composer
        // $mpdf = new \Mpdf\Mpdf();
        // $html = $this->generate_pdf_html( $report_type, $data );
        // $mpdf->WriteHTML( $html );
        // $mpdf->Output( 'report.pdf', 'D' ); // 'D' for download

        // For now, we'll just simulate a download.
        $filename = sanitize_file_name( $report_type . '_' . $start_date . '_to_' . $end_date . '.pdf' );
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        // Output a dummy PDF content or a message
        echo "PDF generation for {$report_type} is not yet implemented.";
        wp_die();
    }

    /**
     * AJAX handler to export report to CSV.
     */
    public function ajax_export_report_csv() {
        check_ajax_referer( 'hms_reports_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_reports' ) ) {
            wp_die( esc_html__( 'You do not have permission to export reports.', 'hospital-management-system' ) );
        }

        $report_type = sanitize_text_field( $_POST['report_type'] );
        $start_date  = sanitize_text_field( $_POST['start_date'] );
        $end_date    = sanitize_text_field( $_POST['end_date'] );
        $data        = json_decode( stripslashes( $_POST['report_data'] ), true );

        $filename = sanitize_file_name( $report_type . '_' . $start_date . '_to_' . $end_date . '.csv' );
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $output = fopen( 'php://output', 'w' );

        // Add CSV headers based on report type
        switch ( $report_type ) {
            case 'patient_census':
                fputcsv( $output, array( 'Date', 'New Patients', 'Appointments' ) );
                foreach ( $data['labels'] as $index => $label ) {
                    fputcsv( $output, array(
                        $label,
                        $data['datasets'][0]['data'][$index],
                        $data['datasets'][1]['data'][$index]
                    ) );
                }
                break;
            case 'revenue_report':
                fputcsv( $output, array( 'Date', 'Revenue' ) );
                foreach ( $data['labels'] as $index => $label ) {
                    fputcsv( $output, array(
                        $label,
                        $data['datasets'][0]['data'][$index]
                    ) );
                }
                break;
            // Add more cases for other report types
        }

        fclose( $output );
        wp_die();
    }

    // --- Data Fetching Methods ---

    private function get_today_new_patients_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_patients';
        $today      = current_time( 'Y-m-d' );
        $count     = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE DATE(registration_date) = %s", $today ) );
        return $count ? $count : 0;
    }

    private function get_today_appointments_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_appointments';
        $today      = current_time( 'Y-m-d' );
        $count     = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE DATE(appointment_date) = %s", $today ) );
        return $count ? $count : 0;
    }

    private function get_monthly_revenue() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        $month      = current_time( 'm' );
        $year       = current_time( 'Y' );
        $revenue   = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(net_amount) FROM {$table_name} WHERE MONTH(created_at) = %s AND YEAR(created_at) = %s AND payment_status = 'Paid'", $month, $year ) );
        return $revenue ? $revenue : 0.00;
    }

    private function get_pending_payments_total() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hms_bills';
        $pending   = $wpdb->get_var( "SELECT SUM(net_amount - paid_amount) FROM {$table_name} WHERE payment_status != 'Paid'" );
        return $pending ? $pending : 0.00;
    }

    private function get_patient_census_data( $start_date, $end_date ) {
        // Placeholder logic for fetching patient census data
        // This should return data in a format suitable for Chart.js
        $labels = array();
        $new_patients_data = array();
        $appointments_data = array();

        // Example: Generate data for each day in the range
        $start = new DateTime( $start_date );
        $end   = new DateTime( $end_date );
        $interval = new DateInterval( 'P1D' );
        $period   = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );

        foreach ( $period as $dt ) {
            $labels[] = $dt->format( 'Y-m-d' );
            // Fetch actual counts from DB for each day
            $new_patients_data[] = rand( 5, 15 ); // Replace with actual DB query
            $appointments_data[] = rand( 20, 40 ); // Replace with actual DB query
        }

        return array(
            'labels'   => $labels,
            'datasets' => array(
                array(
                    'label'           => esc_html__( 'New Patients', 'hospital-management-system' ),
                    'data'            => $new_patients_data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor'     => 'rgba(54, 162, 235, 1)',
                    'borderWidth'     => 1,
                ),
                array(
                    'label'           => esc_html__( 'Appointments', 'hospital-management-system' ),
                    'data'            => $appointments_data,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor'     => 'rgba(255, 99, 132, 1)',
                    'borderWidth'     => 1,
                ),
            ),
        );
    }

    private function get_revenue_report_data( $start_date, $end_date ) {
        // Placeholder logic for fetching revenue data
        $labels = array();
        $revenue_data = array();

        $start = new DateTime( $start_date );
        $end   = new DateTime( $end_date );
        $interval = new DateInterval( 'P1D' );
        $period   = new DatePeriod( $start, $interval, $end->modify( '+1 day' ) );

        foreach ( $period as $dt ) {
            $labels[] = $dt->format( 'Y-m-d' );
            // Fetch actual revenue from DB for each day
            $revenue_data[] = rand( 1000, 5000 ); // Replace with actual DB query
        }

        return array(
            'labels'   => $labels,
            'datasets' => array(
                array(
                    'label'           => esc_html__( 'Revenue', 'hospital-management-system' ),
                    'data'            => $revenue_data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor'     => 'rgba(75, 192, 192, 1)',
                    'borderWidth'     => 1,
                    'fill'            => true, // For area chart
                ),
            ),
        );
    }

    // --- Placeholder for PDF HTML Generation ---
    private function generate_pdf_html( $report_type, $data ) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html( $report_type ); ?></title>
            <style>
                body { font-family: sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h1><?php echo esc_html( ucwords( str_replace( '_', ' ', $report_type ) ) ); ?></h1>
            <p><?php esc_html_e( 'Report generated on:', 'hospital-management-system' ); ?> <?php echo current_time( 'Y-m-d H:i:s' ); ?></p>
            <!-- Add more HTML structure to display $data -->
            <?php if ( isset( $data['labels'] ) && isset( $data['datasets'] ) ) : ?>
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date', 'hospital-management-system' ); ?></th>
                            <?php foreach ( $data['datasets'] as $dataset ) : ?>
                                <th><?php echo esc_html( $dataset['label'] ); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $data['labels'] as $index => $label ) : ?>
                            <tr>
                                <td><?php echo esc_html( $label ); ?></td>
                                <?php foreach ( $data['datasets'] as $dataset ) : ?>
                                    <td><?php echo esc_html( $dataset['data'][$index] ); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
