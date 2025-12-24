jQuery(document).ready(function ($) {
  "use strict";

  // --- Settings Page Tabs ---
  $(".hms-settings-page .nav-tab-wrapper a").on("click", function (e) {
    e.preventDefault();

    var target = $(this).attr("href");

    // Hide all sections
    $(".hms-settings-section").hide();

    // Show target section
    $(target).show();

    // Update active tab
    $(".nav-tab").removeClass("nav-tab-active");
    $(this).addClass("nav-tab-active");

    // Update URL hash without triggering a jump
    history.pushState(
      null,
      null,
      window.location.pathname + window.location.search + target
    );
  });

  // On page load, show the correct tab based on URL hash
  var hash = window.location.hash;
  if (hash) {
    $('.hms-settings-page .nav-tab-wrapper a[href="' + hash + '"]').trigger(
      "click"
    );
  }

  // --- Media Upload for Logo ---
  var mediaUploader;

  $("#hms-upload-logo-button").on("click", function (e) {
    e.preventDefault();

    // If the uploader object has already been created, reopen the dialog
    if (mediaUploader) {
      mediaUploader.open();
      return;
    }

    // Extend the wp.media object
    mediaUploader = wp.media.frames.file_frame = wp.media({
      title: hms_admin_vars.strings.choose_logo || "Choose Logo",
      button: {
        text: hms_admin_vars.strings.choose_logo || "Choose Logo",
      },
      multiple: false,
    });

    // When a file is selected, grab the URL and set it as the text field's value
    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#hms_hospital_logo_id").val(attachment.id);
      $("#hms-logo-preview").html(
        '<img src="' + attachment.url + '" alt="' + attachment.alt + '" />'
      );
      $("#hms-remove-logo-button").show();
    });

    // Open the uploader dialog
    mediaUploader.open();
  });

  $("#hms-remove-logo-button").on("click", function (e) {
    e.preventDefault();
    $("#hms_hospital_logo_id").val("");
    $("#hms-logo-preview").html("");
    $(this).hide();
  });

  // --- Payment Gateway Keys Dynamic Display ---
  $("#hms_payment_gateway")
    .on("change", function () {
      var selectedGateway = $(this).val();
      $(".hms-gateway-keys").hide(); // Hide all gateway key fields

      if (selectedGateway && $("#hms-" + selectedGateway + "-keys").length) {
        $("#hms-" + selectedGateway + "-keys").show(); // Show relevant key fields
      }
    })
    .trigger("change"); // Trigger on page load to show correct fields

  // --- Role Capabilities Saving ---
  // This is handled by the standard WordPress settings form submission.
  // The form fields are named in a way that WordPress should save them as an array.
  // We will need a custom sanitizer/handler for 'hms_role_caps_json' if we use that approach.
  // For now, we rely on the form submission and a custom save action in the admin class.

  // --- Common Confirmation Dialogs ---
  $(".hms-confirm-delete").on("click", function (e) {
    if (
      !confirm(
        hms_admin_vars.strings.confirm_delete_message ||
          "Are you sure you want to delete this item?"
      )
    ) {
      e.preventDefault();
    }
  });
});
