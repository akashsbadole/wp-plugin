jQuery(document).ready(function ($) {
  // Function to initialize the report form
  function initializeReportForm() {
    const reportForm = $("#hms-report-form");
    if (!reportForm.length) {
      return; // Exit if form not found
    }

    const reportCanvas = $("#hms-report-canvas");
    const reportTypeSelect = $("#report_type");
    const startDateInput = $("#start_date");
    const endDateInput = $("#end_date");
    const generateReportBtn = $("#generate_report_btn");
    const exportPdfBtn = $("#export_pdf_btn");
    const exportCsvBtn = $("#export_csv_btn");

    let currentChartInstance = null;
    let currentReportData = null;

    // Set default dates (e.g., last 7 days)
    const today = new Date();
    const lastWeek = new Date(today);
    lastWeek.setDate(today.getDate() - 7);

    startDateInput.val(lastWeek.toISOString().split("T")[0]);
    endDateInput.val(today.toISOString().split("T")[0]);

    // Event listener for generating the report
    generateReportBtn.on("click", function (e) {
      e.preventDefault();
      generateReport();
    });

    // Event listener for exporting to PDF
    exportPdfBtn.on("click", function (e) {
      e.preventDefault();
      exportReport("pdf");
    });

    // Event listener for exporting to CSV
    exportCsvBtn.on("click", function (e) {
      e.preventDefault();
      exportReport("csv");
    });

    function generateReport() {
      const reportType = reportTypeSelect.val();
      const startDate = startDateInput.val();
      const endDate = endDateInput.val();

      if (!reportType || !startDate || !endDate) {
        alert(
          hms_reports_vars.i18n_please_fill_all_fields ||
            "Please fill in all fields."
        );
        return;
      }

      generateReportBtn
        .prop("disabled", true)
        .text(hms_reports_vars.i18n_generating || "Generating...");
      reportCanvas
        .closest(".card-body")
        .find(".spinner-border")
        .removeClass("d-none");

      $.ajax({
        url: hms_reports_vars.ajax_url,
        type: "POST",
        data: {
          action: "hms_generate_report",
          nonce: hms_reports_vars.nonce,
          report_type: reportType,
          start_date: startDate,
          end_date: endDate,
        },
        success: function (response) {
          if (response.success) {
            currentReportData = response.data;
            renderChart(currentReportData);
            exportPdfBtn.prop("disabled", false);
            exportCsvBtn.prop("disabled", false);
          } else {
            alert(
              response.data.message ||
                hms_reports_vars.i18n_error_generating_report ||
                "Error generating report."
            );
            console.error(response);
          }
        },
        error: function (xhr, status, error) {
          alert(hms_reports_vars.i18n_ajax_error || "An AJAX error occurred.");
          console.error(xhr.responseText);
        },
        complete: function () {
          generateReportBtn
            .prop("disabled", false)
            .text(hms_reports_vars.i18n_generate_report || "Generate Report");
          reportCanvas
            .closest(".card-body")
            .find(".spinner-border")
            .addClass("d-none");
        },
      });
    }

    function renderChart(data) {
      const ctx = reportCanvas[0].getContext("2d");

      // Destroy previous chart instance if it exists
      if (currentChartInstance) {
        currentChartInstance.destroy();
      }

      // Determine chart type based on report data or a hidden input
      const chartType = $("#chart_type").val() || "line"; // Default to line chart

      const chartConfig = {
        type: chartType,
        data: data,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "top",
            },
            title: {
              display: true,
              text:
                reportTypeSelect.find("option:selected").text() +
                " (" +
                startDateInput.val() +
                " to " +
                endDateInput.val() +
                ")",
            },
            tooltip: {
              mode: "index",
              intersect: false,
            },
          },
          scales: {
            // You might need to adjust scales based on chart type and data
            y: {
              beginAtZero: true,
            },
          },
        },
      };

      // Specific configurations for different chart types
      if (chartType === "bar") {
        chartConfig.options.scales = {
          y: {
            beginAtZero: true,
          },
        };
      } else if (chartType === "pie" || chartType === "doughnut") {
        // Pie/Doughnut charts typically don't need x/y scales in the same way
        delete chartConfig.options.scales;
      }

      currentChartInstance = new Chart(ctx, chartConfig);
    }

    function exportReport(format) {
      if (!currentReportData) {
        alert(
          hms_reports_vars.i18n_no_data_to_export ||
            "No data to export. Please generate a report first."
        );
        return;
      }

      const reportType = reportTypeSelect.val();
      const startDate = startDateInput.val();
      const endDate = endDateInput.val();

      const exportBtn = format === "pdf" ? exportPdfBtn : exportCsvBtn;
      const originalBtnText = exportBtn.text();
      exportBtn
        .prop("disabled", true)
        .text(hms_reports_vars.i18n_exporting || "Exporting...");

      $.ajax({
        url: hms_reports_vars.ajax_url,
        type: "POST",
        data: {
          action:
            format === "pdf"
              ? "hms_export_report_pdf"
              : "hms_export_report_csv",
          nonce: hms_reports_vars.nonce,
          report_type: reportType,
          start_date: startDate,
          end_date: endDate,
          report_data: JSON.stringify(currentReportData), // Send data as a JSON string
        },
        xhrFields: {
          responseType: "blob", // Important for file downloads
        },
        success: function (response, status, xhr) {
          const filename =
            getFilenameFromHeader(xhr) || reportType + "." + format;
          const blob = new Blob([response], {
            type: xhr.getResponseHeader("Content-Type"),
          });
          const link = document.createElement("a");
          link.href = window.URL.createObjectURL(blob);
          link.download = filename;
          document.body.appendChild(link);
          link.dispatchEvent(new Event('click', { bubbles: true }));
          document.body.removeChild(link);
          window.URL.revokeObjectURL(link.href);
        },
        error: function (xhr, status, error) {
          // Try to read error message if response is not a blob
          if (xhr.responseText) {
            try {
              const errResponse = JSON.parse(xhr.responseText);
              alert(
                errResponse.data.message ||
                  hms_reports_vars.i18n_error_exporting_report ||
                  "Error exporting report."
              );
            } catch (e) {
              alert(
                hms_reports_vars.i18n_error_exporting_report ||
                  "Error exporting report."
              );
            }
            console.error(xhr.responseText);
          } else {
            alert(
              hms_reports_vars.i18n_error_exporting_report ||
                "Error exporting report."
            );
            console.error(xhr);
          }
        },
        complete: function () {
          exportBtn.prop("disabled", false).text(originalBtnText);
        },
      });
    }

    function getFilenameFromHeader(xhr) {
      const disposition = xhr.getResponseHeader("Content-Disposition");
      if (disposition && disposition.indexOf("attachment") !== -1) {
        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
        const matches = filenameRegex.exec(disposition);
        if (matches != null && matches[1]) {
          return matches[1].replace(/['"]/g, "");
        }
      }
      return null;
    }
  }

  // Initialize on page load
  initializeReportForm();

  // You might also want to re-initialize if the form is loaded dynamically via AJAX
  // $(document).on('hms_content_loaded', function() {
  //     initializeReportForm();
  // });
});
