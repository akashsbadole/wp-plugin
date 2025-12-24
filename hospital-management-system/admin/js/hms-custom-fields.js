(function ($) {
  "use strict";

  $(document).ready(function () {
    // Show/hide field options textarea based on field type
    $("#field_type").on("change", function () {
      var selectedType = $(this).val();
      if (
        selectedType === "select" ||
        selectedType === "checkbox" ||
        selectedType === "radio"
      ) {
        $("#field_options_row").show();
        $("#field_options").prop("required", true);
      } else {
        $("#field_options_row").hide();
        $("#field_options").prop("required", false);
      }
    });

    // Auto-generate field name from label
    $("#field_label").on("input", function () {
      var label = $(this).val();
      var fieldName = label
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");
      $("#field_name").val(fieldName);
    });

    // Handle form submission for adding/updating a custom field
    $("#hms-custom-field-form").on("submit", function (e) {
      e.preventDefault();

      var formData = $(this).serialize();
      var submitButton = $(this).find('input[type="submit"]');
      var originalButtonText = submitButton.val();

      submitButton.prop("disabled", true).val("Saving...");

      $.ajax({
        type: "POST",
        url: hms_custom_fields_ajax.ajax_url,
        data:
          formData +
          "&action=hms_save_custom_field&nonce=" +
          hms_custom_fields_ajax.nonce,
        dataType: "json",
        success: function (response) {
          if (response.success) {
            alert(response.data); // Show success message
            $("#hms-custom-field-form")[0].reset(); // Reset form
            loadExistingFields(); // Reload the list of fields
            $("#field_options_row").hide(); // Hide options row again
          } else {
            alert("Error: " + response.data); // Show error message
          }
        },
        error: function (xhr, status, error) {
          alert("An unexpected error occurred. Please try again.");
          console.error(xhr.responseText);
        },
        complete: function () {
          submitButton.prop("disabled", false).val(originalButtonText);
        },
      });
    });

    // Function to load and display existing custom fields
    function loadExistingFields() {
      $.ajax({
        type: "POST",
        url: hms_custom_fields_ajax.ajax_url,
        data: {
          action: "hms_get_custom_fields",
          nonce: hms_custom_fields_ajax.nonce,
        },
        dataType: "json",
        success: function (response) {
          if (response.success) {
            var fields = response.data;
            var fieldsListHtml = "";

            if (fields.length > 0) {
              fieldsListHtml +=
                '<table class="wp-list-table widefat fixed striped">';
              fieldsListHtml +=
                "<thead><tr><th>Context</th><th>Label</th><th>Name</th><th>Type</th><th>Required</th><th>Order</th><th>Actions</th></tr></thead>";
              fieldsListHtml += "<tbody>";
              $.each(fields, function (index, field) {
                fieldsListHtml += '<tr class="field-item">';
                fieldsListHtml +=
                  '<td><span class="field-context-badge">' +
                  field.context +
                  "</span></td>";
                fieldsListHtml +=
                  "<td><strong>" + field.label + "</strong></td>";
                fieldsListHtml += "<td><code>" + field.name + "</code></td>";
                fieldsListHtml += "<td>" + field.type + "</td>";
                fieldsListHtml +=
                  "<td>" + (field.required ? "Yes" : "No") + "</td>";
                fieldsListHtml += "<td>" + field.order + "</td>";
                fieldsListHtml += '<td class="field-actions">';
                fieldsListHtml +=
                  '<a href="#" class="delete-field" data-context="' +
                  field.context +
                  '" data-name="' +
                  field.name +
                  '">Delete</a>';
                // Add Edit functionality later if needed
                // fieldsListHtml += '<a href="#" class="edit-field" data-context="' + field.context + '" data-name="' + field.name + '">Edit</a>';
                fieldsListHtml += "</td>";
                fieldsListHtml += "</tr>";
              });
              fieldsListHtml += "</tbody>";
              fieldsListHtml += "</table>";
            } else {
              fieldsListHtml =
                "<p>No custom fields found. Add some using the form above.</p>";
            }
            $("#hms-fields-list-container").html(fieldsListHtml);
          } else {
            $("#hms-fields-list-container").html(
              "<p>Error loading fields: " + response.data + "</p>"
            );
          }
        },
        error: function () {
          $("#hms-fields-list-container").html(
            "<p>An unexpected error occurred while loading fields.</p>"
          );
        },
      });
    }

    // Initial load of existing fields
    loadExistingFields();

    // Handle delete field click (event delegation for dynamically added elements)
    $("#hms-fields-list-container").on("click", ".delete-field", function (e) {
      e.preventDefault();
      var context = $(this).data("context");
      var name = $(this).data("name");
      var fieldRow = $(this).closest("tr");

      if (
        confirm(
          "Are you sure you want to delete this custom field? This action cannot be undone."
        )
      ) {
        $.ajax({
          type: "POST",
          url: hms_custom_fields_ajax.ajax_url,
          data: {
            action: "hms_delete_custom_field",
            nonce: hms_custom_fields_ajax.nonce,
            context: context,
            name: name,
          },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              alert(response.data); // Show success message
              fieldRow.fadeOut(300, function () {
                $(this).remove();
                // If no more fields, show the 'no fields' message
                if ($("#hms-fields-list-container tbody tr").length === 0) {
                  loadExistingFields(); // Reload to show the "No fields" message
                }
              });
            } else {
              alert("Error: " + response.data); // Show error message
            }
          },
          error: function (xhr, status, error) {
            alert("An unexpected error occurred. Please try again.");
            console.error(xhr.responseText);
          },
        });
      }
    });
  });
})(jQuery);
