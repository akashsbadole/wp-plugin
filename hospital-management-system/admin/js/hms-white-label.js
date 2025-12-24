(function ($) {
  "use strict";

  $(document).ready(function () {
    // Media Uploader
    var mediaUploader;

    // When the 'Upload Image' button is clicked
    $(".media-upload-button").on("click", function (e) {
      e.preventDefault();

      var button = $(this);
      var wrapper = button.closest(".media-upload-wrapper");
      var preview = wrapper.find(".media-upload-preview");
      var input = wrapper.find('input[type="hidden"]');
      var removeButton = wrapper.find(".media-remove-button");

      // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      // Extend the wp.media object
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Image",
        button: {
          text: "Choose Image",
        },
        multiple: false,
      });

      // When a file is selected, grab the URL and set it as the preview
      mediaUploader.on("select", function () {
        var attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        var imageUrl = attachment.url;

        preview.attr("src", imageUrl).show();
        input.val(attachment.id);
        removeButton.show();
      });

      // Open the uploader dialog
      mediaUploader.open();
    });

    // When the 'Remove Image' button is clicked
    $(".media-remove-button").on("click", function (e) {
      e.preventDefault();

      var button = $(this);
      var wrapper = button.closest(".media-upload-wrapper");
      var preview = wrapper.find(".media-upload-preview");
      var input = wrapper.find('input[type="hidden"]');

      preview.attr("src", "").hide();
      input.val("");
      button.hide();
    });

    // Color Picker Preview Text Update
    $('input[type="color"]').on("input", function () {
      $(this).next(".color-preview-text").text($(this).val());
    });

    // Initialize color picker preview text on page load
    $('input[type="color"]').each(function () {
      $(this).next(".color-preview-text").text($(this).val());
    });
  });
})(jQuery);
