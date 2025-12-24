(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(function () {
    // SEO Meta Box functionality
    var $titleField = $("#_seo_simple_title");
    var $descriptionField = $("#_seo_simple_description");
    var $focusKeywordField = $("#_seo_simple_focus_keyword");
    var $titleCount = $(".seo-simple-title-count");
    var $descriptionCount = $(".seo-simple-description-count");
    var $titlePreview = $("#seo-simple-title-preview");
    var $descriptionPreview = $("#seo-simple-description-preview");
    var $analyzeSeoButton = $("#seo-simple-analyze-seo");
    var $seoSpinner = $("#seo-simple-seo-spinner");

    // Social Media functionality
    var $socialTitleField = $("#_seo_simple_social_title");
    var $socialDescriptionField = $("#_seo_simple_social_description");
    var $socialImageIdField = $("#_seo_simple_social_image_id");
    var $socialImageField = $("#_seo_simple_social_image");
    var $useSeoDataField = $("#_seo_simple_use_seo_data");
    var $socialTitleCount = $(".seo-simple-social-title-count");
    var $socialDescriptionCount = $(".seo-simple-social-description-count");
    var $uploadSocialImageButton = $("#seo-simple-upload-social-image");
    var $removeSocialImageButton = $("#seo-simple-remove-social-image");
    var $socialImagePreview = $("#seo-simple-social-image-preview");

    // Social preview elements
    var $facebookTitle = $("#seo-simple-facebook-title");
    var $facebookDescription = $("#seo-simple-facebook-description");
    var $facebookImage = $("#seo-simple-facebook-image");
    var $twitterTitle = $("#seo-simple-twitter-title");
    var $twitterDescription = $("#seo-simple-twitter-description");
    var $twitterImage = $("#seo-simple-twitter-image");

    // Character count and preview update functions
    function updateTitlePreview() {
      // Check if elements exist before trying to use them
      if (!$titleField.length || !$titlePreview.length || !$titleCount.length) {
        return;
      }

      var title = $titleField.val() || $titlePreview.data("default") || "";
      title = title.toString(); // Ensure it's a string
      $titlePreview.text(title);
      $titleCount.text(title.length);

      // Change color if over limit
      if (title.length > 60) {
        $titleCount.css("color", "red");
      } else {
        $titleCount.css("color", "#666");
      }
    }

    function updateDescriptionPreview() {
      // Check if elements exist before trying to use them
      if (
        !$descriptionField.length ||
        !$descriptionPreview.length ||
        !$descriptionCount.length
      ) {
        return;
      }

      var description =
        $descriptionField.val() || $descriptionPreview.data("default") || "";
      description = description.toString(); // Ensure it's a string
      $descriptionPreview.text(description);
      $descriptionCount.text(description.length);

      // Change color if over limit
      if (description.length > 160) {
        $descriptionCount.css("color", "red");
      } else {
        $descriptionCount.css("color", "#666");
      }
    }

    // Store default values
    $titlePreview.data("default", $titlePreview.text());
    $descriptionPreview.data("default", $descriptionPreview.text());

    // Update previews on input
    $titleField.on("input", updateTitlePreview);
    $descriptionField.on("input", updateDescriptionPreview);
    
    // Update SEO title when post title changes
    var $postTitleField = $("#title");
    if ($postTitleField.length) {
      $postTitleField.on("input", function() {
        // Only auto-update if SEO title is empty or matches the default format
        var currentSeoTitle = $titleField.val();
        var postTitle = $(this).val();
        var siteName = $("#seo-simple-site-name").data("site-name") || "";
        var defaultTitle = postTitle + " | " + siteName;
        
        // If SEO title is empty or matches the default format, update it
        if (!currentSeoTitle || currentSeoTitle === $titlePreview.data("default")) {
          $titleField.val(postTitle);
          updateTitlePreview();
        }
      });
    }

    // Initialize previews
    updateTitlePreview();
    updateDescriptionPreview();

    // Copy title to clipboard functionality
    $titlePreview.on("click", function () {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val($(this).text()).select();
      document.execCommand("copy");
      $temp.remove();

      // Show copied message
      var originalText = $(this).text();
      $(this).text("Copied to clipboard!");
      setTimeout(function () {
        $titlePreview.text(originalText);
      }, 1000);
    });

    // SEO Analysis functionality
    var postId = $("#post_ID").val();

    // Analyze SEO button click handler
    $analyzeSeoButton.on("click", function () {
      // Get content from editor
      var content = "";

      // Try to get content from WordPress editor
      if (
        typeof tinyMCE !== "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden()
      ) {
        content = tinyMCE.activeEditor.getContent();
      } else if ($("#content").length) {
        content = $("#content").val();
      }

      var title = $titleField.val();
      var description = $descriptionField.val();
      var focusKeyword = $focusKeywordField.val();

      if (!focusKeyword.trim()) {
        alert("Please enter a focus keyword before analyzing SEO.");
        return;
      }

      // Show loading state
      $analyzeSeoButton.prop("disabled", true);
      $seoSpinner.show();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_analyze_seo",
          nonce: $("#seo_simple_meta_box_nonce").val(),
          post_id: postId,
          title: title,
          description: description,
          content: content,
          focus_keyword: focusKeyword,
        },
        success: function (response) {
          if (response.success) {
            updateSeoDisplay(response.data);
          } else {
            alert("Error: " + response.data.message);
          }
        },
        error: function () {
          alert("An error occurred while analyzing SEO.");
        },
        complete: function () {
          // Hide loading state
          $analyzeSeoButton.prop("disabled", false);
          $seoSpinner.hide();
        },
      });
    });

    // Update SEO display with new data
    function updateSeoDisplay(data) {
      // Update overall score
      if (data.overall) {
        $("#seo-simple-overall-score-fill")
          .css("width", data.overall.score + "%")
          .css("background-color", getStatusColor(data.overall.status));

        $(".seo-simple-score-number").text(data.overall.score);
        $(".seo-simple-score-status")
          .removeClass(
            "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
          )
          .addClass("seo-simple-status-" + data.overall.status)
          .text(data.overall.recommendation);
      }

      // Update individual checks
      var checks = [
        { id: "keyword-title", key: "keyword_in_title" },
        { id: "keyword-description", key: "keyword_in_description" },
        { id: "keyword-url", key: "keyword_in_url" },
        { id: "keyword-first-paragraph", key: "keyword_in_first_paragraph" },
        { id: "keyword-headings", key: "keyword_in_headings" },
        { id: "word-count", key: "word_count" },
        { id: "keyword-density", key: "keyword_density" },
        { id: "links", key: "links" },
        { id: "image-alt", key: "image_alt_text" },
      ];

      checks.forEach(function (check) {
        if (data[check.key]) {
          var $indicator = $(
            "#seo-simple-check-" + check.id + " .seo-simple-indicator-dot"
          );
          var $details = $("#seo-simple-check-" + check.id)
            .closest(".seo-simple-check-item")
            .find(".seo-simple-check-details");

          // Update indicator
          $indicator
            .removeClass(
              "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
            )
            .addClass("seo-simple-status-" + data[check.key].status);

          // Update details
          if (check.key === "word_count") {
            $details.html(
              "Word count: " +
                data[check.key].word_count +
                ". " +
                data[check.key].recommendation
            );
          } else if (check.key === "keyword_density") {
            $details.html(
              "Density: " +
                data[check.key].density +
                "%. " +
                data[check.key].recommendation
            );
          } else if (check.key === "links") {
            $details.html(
              "Internal links: " +
                data[check.key].internal_links +
                ". " +
                data[check.key].recommendation
            );
          } else if (check.key === "image_alt_text") {
            if (data[check.key].total_images > 0) {
              $details.html(
                "Images with alt text: " +
                  data[check.key].images_with_alt +
                  "/" +
                  data[check.key].total_images +
                  " (" +
                  data[check.key].percentage_with_alt +
                  "%). " +
                  data[check.key].recommendation
              );
            } else {
              $details.html(data[check.key].recommendation);
            }
          } else {
            $details.text(data[check.key].recommendation);
          }
        }
      });
    }

    // Get color for status
    function getStatusColor(status) {
      switch (status) {
        case "good":
          return "#46b450";
        case "ok":
          return "#ffb900";
        case "bad":
        default:
          return "#dc3232";
      }
    }

    // Auto-analyze SEO when content changes (with debounce)
    var seoAnalyzeTimeout;
    function scheduleAutoSeoAnalysis() {
      clearTimeout(seoAnalyzeTimeout);
      seoAnalyzeTimeout = setTimeout(function () {
        // Only auto-analyze if focus keyword is set
        if ($focusKeywordField.val().trim()) {
          $analyzeSeoButton.trigger("click");
        }
      }, 3000); // Wait 3 seconds after user stops typing
    }

    // Hook into content editor changes
    if (typeof tinyMCE !== "undefined") {
      tinyMCE.on("AddEditor", function (e) {
        e.editor.on("change", function () {
          scheduleAutoSeoAnalysis();
        });
      });
    }

    $("#content").on("input", function () {
      scheduleAutoSeoAnalysis();
    });

    // Hook into title, description, and focus keyword changes
    $titleField.on("input", function () {
      scheduleAutoSeoAnalysis();
    });

    $descriptionField.on("input", function () {
      scheduleAutoSeoAnalysis();
    });

    $focusKeywordField.on("input", function () {
      // Clear SEO analysis when focus keyword changes
      $(".seo-simple-score-number").text("0");
      $(".seo-simple-score-status")
        .removeClass(
          "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
        )
        .addClass("seo-simple-status-bad")
        .text("Set a focus keyword to analyze your content.");

      $("#seo-simple-overall-score-fill")
        .css("width", "0%")
        .css("background-color", "#dc3232");

      // Reset all check indicators
      $(".seo-simple-indicator-dot")
        .removeClass(
          "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
        )
        .addClass("seo-simple-status-bad");

      // Schedule new analysis if focus keyword is not empty
      if ($(this).val().trim()) {
        scheduleAutoSeoAnalysis();
      }
    });

    // Social Media functionality

    // Store default values for social previews
    function getSocialTitleValue() {
      if ($useSeoDataField.is(":checked")) {
        return $titleField.val() || $titlePreview.data("default");
      }
      return (
        $socialTitleField.val() ||
        $titleField.val() ||
        $titlePreview.data("default")
      );
    }

    function getSocialDescriptionValue() {
      if ($useSeoDataField.is(":checked")) {
        return $descriptionField.val() || $descriptionPreview.data("default");
      }
      return (
        $socialDescriptionField.val() ||
        $descriptionField.val() ||
        $descriptionPreview.data("default")
      );
    }

    // Update social preview functions
    function updateSocialPreviews() {
      var title = getSocialTitleValue();
      var description = getSocialDescriptionValue();
      var imageId = $socialImageIdField.val();
      var imageUrl = "";

      // Get image URL
      if (imageId) {
        // Try to get the actual image URL
        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "seo_simple_get_image_url",
            image_id: imageId,
            nonce: $("#seo_simple_meta_box_nonce").val(),
          },
          success: function (response) {
            if (response.success && response.data.url) {
              imageUrl = response.data.url;
              updateSocialPreviewImages(imageUrl);
            }
          },
        });
      }

      // Update title and description in previews
      $facebookTitle.text(title);
      $facebookDescription.text(description);
      $twitterTitle.text(title);
      $twitterDescription.text(description);
    }

    function updateSocialPreviewImages(imageUrl) {
      if (imageUrl) {
        $facebookImage.html('<img src="' + imageUrl + '" alt="" />');
        $twitterImage.html('<img src="' + imageUrl + '" alt="" />');
      } else {
        $facebookImage.html('<div class="seo-simple-facebook-no-image"></div>');
        $twitterImage.html('<div class="seo-simple-twitter-no-image"></div>');
      }
    }

    // Update social character counts
    function updateSocialTitleCount() {
      // Check if elements exist before trying to use them
      if (!$socialTitleCount.length) {
        return;
      }

      var title = getSocialTitleValue() || "";
      title = title.toString(); // Ensure it's a string
      var length = title.length;
      $socialTitleCount.text(length);

      if (length > 100) {
        $socialTitleCount.css("color", "red");
      } else {
        $socialTitleCount.css("color", "#666");
      }
    }

    function updateSocialDescriptionCount() {
      // Check if elements exist before trying to use them
      if (!$socialDescriptionCount.length) {
        return;
      }

      var description = getSocialDescriptionValue() || "";
      description = description.toString(); // Ensure it's a string
      var length = description.length;
      $socialDescriptionCount.text(length);

      if (length > 200) {
        $socialDescriptionCount.css("color", "red");
      } else {
        $socialDescriptionCount.css("color", "#666");
      }
    }

    // Handle use SEO data checkbox
    $useSeoDataField.on("change", function () {
      if ($(this).is(":checked")) {
        // Disable social fields and use SEO data
        $socialTitleField.prop("readonly", true);
        $socialDescriptionField.prop("readonly", true);
      } else {
        // Enable social fields
        $socialTitleField.prop("readonly", false);
        $socialDescriptionField.prop("readonly", false);
      }
      updateSocialPreviews();
      updateSocialTitleCount();
      updateSocialDescriptionCount();
    });

    // Update social previews on input
    $socialTitleField.on("input", function () {
      updateSocialPreviews();
      updateSocialTitleCount();
    });

    $socialDescriptionField.on("input", function () {
      updateSocialPreviews();
      updateSocialDescriptionCount();
    });

    $titleField.on("input", function () {
      if ($useSeoDataField.is(":checked")) {
        updateSocialPreviews();
        updateSocialTitleCount();
      }
    });

    $descriptionField.on("input", function () {
      if ($useSeoDataField.is(":checked")) {
        updateSocialPreviews();
        updateSocialDescriptionCount();
      }
    });

    // Image upload functionality
    $uploadSocialImageButton.on("click", function (e) {
      e.preventDefault();

      var imageFrame = wp.media({
        title: "Select or Upload Social Image",
        button: {
          text: "Use this image",
        },
        multiple: false,
      });

      imageFrame.on("select", function () {
        var attachment = imageFrame.state().get("selection").first().toJSON();

        // Update hidden fields
        $socialImageIdField.val(attachment.id);
        $socialImageField.val(attachment.url);

        // Update preview
        $socialImagePreview.html(
          '<img src="' +
            attachment.url +
            '" alt="Social Image Preview" />' +
            '<div class="seo-simple-image-actions">' +
            '<button type="button" class="button seo-simple-remove-image" id="seo-simple-remove-social-image">' +
            "Remove Image" +
            "</button>" +
            "</div>"
        );

        // Re-bind remove button event
        $("#seo-simple-remove-social-image").on("click", removeSocialImage);

        // Update social previews
        updateSocialPreviews();
      });

      imageFrame.open();
    });

    // Remove image functionality
    function removeSocialImage(e) {
      e.preventDefault();

      // Clear fields
      $socialImageIdField.val("");
      $socialImageField.val("");

      // Update preview
      $socialImagePreview.html(
        '<div class="seo-simple-no-image">' +
          "<p>No image selected</p>" +
          "</div>"
      );

      // Update social previews
      updateSocialPreviews();
    }

    $removeSocialImageButton.on("click", removeSocialImage);

    // Initialize social previews
    updateSocialPreviews();
    updateSocialTitleCount();
    updateSocialDescriptionCount();

    // Initialize use SEO data state
    $useSeoDataField.trigger("change");

    // Readability Analysis functionality
    var $analyzeReadabilityButton = $("#seo-simple-analyze-readability");
    var $readabilitySpinner = $("#seo-simple-readability-spinner");
    var $contentEditor = $("#content");

    // Analyze readability button click handler
    $analyzeReadabilityButton.on("click", function () {
      // Get content from editor
      var content = "";

      // Try to get content from WordPress editor
      if (
        typeof tinyMCE !== "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden()
      ) {
        content = tinyMCE.activeEditor.getContent();
      } else if ($contentEditor.length) {
        content = $contentEditor.val();
      }

      // Check for content more thoroughly
      var textContent = content.replace(/<[^>]*>/g, ''); // Strip HTML tags
      textContent = textContent.replace(/\s+/g, ' ').trim(); // Normalize whitespace
      
      if (!textContent || textContent.length < 10) {
        alert("Please add some content before analyzing readability.");
        return;
      }

      // Show loading state
      $analyzeReadabilityButton.prop("disabled", true);
      $readabilitySpinner.show();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_analyze_readability",
          nonce: $("#seo_simple_meta_box_nonce").val(),
          post_id: postId,
          content: content,
        },
        success: function (response) {
          if (response.success) {
            updateReadabilityDisplay(response.data);
          } else {
            alert("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred while analyzing readability. " + error);
        },
        complete: function () {
          // Hide loading state
          $analyzeReadabilityButton.prop("disabled", false);
          $readabilitySpinner.hide();
        },
      });
    });

    // Update readability display with new data
    function updateReadabilityDisplay(data) {
      // Update overall score
      if (data.overall) {
        $("#seo-simple-overall-score-fill")
          .css("width", data.overall.score + "%")
          .css("background-color", getStatusColor(data.overall.status));

        $(".seo-simple-score-number").text(data.overall.score);
        $(".seo-simple-score-status")
          .removeClass(
            "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
          )
          .addClass("seo-simple-status-" + data.overall.status)
          .text(data.overall.recommendation);
      }

      // Update individual metrics
      var metrics = [
        "sentence_length",
        "paragraph_length",
        "subheading_distribution",
        "passive_voice",
        "transition_words",
        "flesch_reading_ease",
      ];

      metrics.forEach(function (metric) {
        if (data[metric]) {
          var $metricElement = $(".seo-simple-metric").has(
            '.seo-simple-metric-title:contains("' +
              getMetricTitle(metric) +
              '")'
          );

          if ($metricElement.length) {
            // Update score
            $metricElement
              .find(".seo-simple-metric-score")
              .removeClass(
                "seo-simple-status-good seo-simple-status-ok seo-simple-status-bad"
              )
              .addClass("seo-simple-status-" + data[metric].status)
              .text(data[metric].score + "/100");

            // Update progress bar
            $metricElement
              .find(".seo-simple-metric-fill")
              .css("width", data[metric].score + "%")
              .css("background-color", getStatusColor(data[metric].status));

            // Update details
            var $detailElement = $metricElement.find(
              ".seo-simple-metric-detail"
            );
            if ($detailElement.length && data[metric].average) {
              $detailElement.text(
                "Average: " + data[metric].average + " words per sentence"
              );
            } else if ($detailElement.length && data[metric].total_paragraphs) {
              $detailElement.text(
                data[metric].total_paragraphs +
                  " total paragraphs, " +
                  data[metric].long_paragraphs +
                  " too long"
              );
            } else if (
              $detailElement.length &&
              data[metric].h2_count !== undefined
            ) {
              $detailElement.text(
                data[metric].h2_count +
                  " H2 headings, " +
                  data[metric].h3_count +
                  " H3 headings"
              );
            } else if (
              $detailElement.length &&
              data[metric].percentage_passive
            ) {
              $detailElement.text(
                data[metric].percentage_passive + "% passive voice usage"
              );
            } else if (
              $detailElement.length &&
              data[metric].percentage_transition
            ) {
              $detailElement.text(
                data[metric].percentage_transition + "% transition words usage"
              );
            }

            // Update recommendation
            $metricElement
              .find(".seo-simple-metric-recommendation")
              .text(data[metric].recommendation);
          }
        }
      });
    }

    // Get human-readable title for metric
    function getMetricTitle(metric) {
      var titles = {
        sentence_length: "Sentence Length",
        paragraph_length: "Paragraph Length",
        subheading_distribution: "Subheading Distribution",
        passive_voice: "Passive Voice",
        transition_words: "Transition Words",
        flesch_reading_ease: "Flesch Reading Ease",
      };
      return titles[metric] || metric;
    }

    // Auto-analyze readability when content changes (with debounce)
    var analyzeTimeout;
    function scheduleAutoReadabilityAnalysis() {
      clearTimeout(analyzeTimeout);
      analyzeTimeout = setTimeout(function () {
        // Only auto-analyze if readability tab is active
        if ($("#seo-simple-readability-tab").is(":visible")) {
          $analyzeReadabilityButton.trigger("click");
        }
      }, 2000); // Wait 2 seconds after user stops typing
    }

    // Hook into content editor changes for readability analysis
    if (typeof tinyMCE !== "undefined") {
      tinyMCE.on("AddEditor", function (e) {
        e.editor.on("change", function () {
          scheduleAutoReadabilityAnalysis();
        });
      });
    }

    $contentEditor.on("input", function () {
      scheduleAutoReadabilityAnalysis();
    });

    // Analyze on tab switch to readability
    $('.seo-simple-tab-nav-item[data-tab="readability"]').on(
      "click",
      function () {
        // Analyze after a short delay to ensure tab is visible
        setTimeout(function () {
          if (
            !$(".seo-simple-metric")
              .first()
              .find(".seo-simple-metric-score")
              .text()
          ) {
            $analyzeReadabilityButton.trigger("click");
          }
        }, 100);
      }
    );

    // Analyze SEO on tab switch to SEO
    $('.seo-simple-tab-nav-item[data-tab="seo"]').on("click", function () {
      // Analyze after a short delay to ensure tab is visible
      setTimeout(function () {
        if (
          ($focusKeywordField.val().trim() &&
            !$(".seo-simple-score-number").text()) ||
          $(".seo-simple-score-number").text() === "0"
        ) {
          $analyzeSeoButton.trigger("click");
        }
      }, 100);
    });

    // Dashboard Widget functionality
    var $refreshWidgetButton = $("#seo-simple-refresh-widget");

    // Refresh dashboard widget
    $refreshWidgetButton.on("click", function (e) {
      e.preventDefault();

      var $button = $(this);
      $button.addClass("seo-simple-loading");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_refresh_dashboard_widget",
          nonce: seo_simple_admin.nonce,
        },
        success: function (response) {
          if (response.success) {
            $(".seo-simple-dashboard-widget").replaceWith(response.data.html);
            // Re-bind the refresh button
            $("#seo-simple-refresh-widget").on("click", arguments.callee);
          } else {
            alert("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred while refreshing the dashboard widget. " + error);
        },
        complete: function () {
          $button.removeClass("seo-simple-loading");
        },
      });
    });

    // Bulk Editor functionality
    var $bulkForm = $("#seo-simple-bulk-form");
    var $selectAllCheckbox = $("#seo-simple-select-all");
    var $postCheckboxes = $(".seo-simple-post-checkbox");
    var $saveAllButton = $("#seo-simple-save-all");
    var $bulkStatus = $("#seo-simple-bulk-status");
    var $filterForm = $("#seo-simple-filter-form");

    // Select all checkboxes
    $selectAllCheckbox.on("change", function () {
      $postCheckboxes.prop("checked", $(this).prop("checked"));
    });

    // Update select all checkbox when individual checkboxes change
    $postCheckboxes.on("change", function () {
      var totalCheckboxes = $postCheckboxes.length;
      var checkedCheckboxes = $postCheckboxes.filter(":checked").length;

      if (checkedCheckboxes === 0) {
        $selectAllCheckbox.prop("indeterminate", false);
        $selectAllCheckbox.prop("checked", false);
      } else if (checkedCheckboxes === totalCheckboxes) {
        $selectAllCheckbox.prop("indeterminate", false);
        $selectAllCheckbox.prop("checked", true);
      } else {
        $selectAllCheckbox.prop("indeterminate", true);
      }
    });

    // Update character count for title fields
    $(document).on("input", ".seo-simple-title-field", function () {
      var $field = $(this);
      var $count = $field
        .closest(".seo-simple-field-group")
        .find(".seo-simple-length");
      var length = $field.val().length;

      $count.text(length);
      $count.removeClass("warning error");

      if (length > 60) {
        $count.addClass("error");
      } else if (length > 50) {
        $count.addClass("warning");
      }
    });

    // Update character count for description fields
    $(document).on("input", ".seo-simple-description-field", function () {
      var $field = $(this);
      var $count = $field
        .closest(".seo-simple-field-group")
        .find(".seo-simple-length");
      var length = $field.val().length;

      $count.text(length);
      $count.removeClass("warning error");

      if (length > 160) {
        $count.addClass("error");
      } else if (length > 150) {
        $count.addClass("warning");
      }
    });

    // Generate auto title
    $(document).on("click", ".seo-simple-generate-title", function () {
      var $button = $(this);
      var postId = $button.data("post-id");
      var $titleField = $button.closest("td").find(".seo-simple-title-field");

      $button.prop("disabled", true);

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_generate_auto_title",
          nonce: seo_simple_admin.nonce,
          post_id: postId,
        },
        success: function (response) {
          if (response.success) {
            $titleField.val(response.data.title);
            $titleField.trigger("input");
          } else {
            alert("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred while generating the title. " + error);
        },
        complete: function () {
          $button.prop("disabled", false);
        },
      });
    });

    // Generate auto description
    $(document).on("click", ".seo-simple-generate-description", function () {
      var $button = $(this);
      var postId = $button.data("post-id");
      var $descriptionField = $button
        .closest("td")
        .find(".seo-simple-description-field");

      $button.prop("disabled", true);

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_generate_auto_description",
          nonce: seo_simple_admin.nonce,
          post_id: postId,
        },
        success: function (response) {
          if (response.success) {
            $descriptionField.val(response.data.description);
            $descriptionField.trigger("input");
          } else {
            alert("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          alert("An error occurred while generating the description. " + error);
        },
        complete: function () {
          $button.prop("disabled", false);
        },
      });
    });

    // Save individual row
    $(document).on("click", ".seo-simple-save-row", function () {
      var $button = $(this);
      var postId = $button.data("post-id");
      var $row = $button.closest("tr");

      // Collect data from the row
      var data = {};
      data[postId] = {
        title: $row.find(".seo-simple-title-field").val(),
        description: $row.find(".seo-simple-description-field").val(),
        focus_keyword: $row.find(".seo-simple-keyword-field").val(),
        noindex: $row
          .find('input[name="noindex[' + postId + ']"]')
          .is(":checked")
          ? 1
          : 0,
        nofollow: $row
          .find('input[name="nofollow[' + postId + ']"]')
          .is(":checked")
          ? 1
          : 0,
        nonce: $row.find('input[name="nonce[' + postId + ']"]').val(),
      };

      $button.prop("disabled", true);
      $bulkStatus.text("Saving...");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_bulk_save",
          nonce: seo_simple_admin.nonce,
          data: JSON.stringify(data),
        },
        success: function (response) {
          if (response.success) {
            $bulkStatus.text(response.data.message);
            setTimeout(function () {
              $bulkStatus.text("");
            }, 3000);
          } else {
            $bulkStatus.text("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          $bulkStatus.text("An error occurred while saving. " + error);
        },
        complete: function () {
          $button.prop("disabled", false);
        },
      });
    });

    // Save all changes
    $saveAllButton.on("click", function () {
      var $checkedPosts = $(".seo-simple-post-checkbox:checked");

      if ($checkedPosts.length === 0) {
        alert("Please select at least one post to save.");
        return;
      }

      var data = {};

      $checkedPosts.each(function () {
        var postId = $(this).val();
        var $row = $(this).closest("tr");

        data[postId] = {
          title: $row.find(".seo-simple-title-field").val(),
          description: $row.find(".seo-simple-description-field").val(),
          focus_keyword: $row.find(".seo-simple-keyword-field").val(),
          noindex: $row
            .find('input[name="noindex[' + postId + ']"]')
            .is(":checked")
            ? 1
            : 0,
          nofollow: $row
            .find('input[name="nofollow[' + postId + ']"]')
            .is(":checked")
            ? 1
            : 0,
          nonce: $row.find('input[name="nonce[' + postId + ']"]').val(),
        };
      });

      $saveAllButton.prop("disabled", true);
      $bulkStatus.text("Saving...");

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_bulk_save",
          nonce: seo_simple_admin.nonce,
          data: JSON.stringify(data),
        },
        success: function (response) {
          if (response.success) {
            $bulkStatus.text(response.data.message);
            setTimeout(function () {
              $bulkStatus.text("");
            }, 3000);
          } else {
            $bulkStatus.text("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
          }
        },
        error: function (xhr, status, error) {
          $bulkStatus.text("An error occurred while saving. " + error);
        },
        complete: function () {
          $saveAllButton.prop("disabled", false);
        },
      });
    });

    // Handle filter form submission with AJAX
    if ($filterForm.length) {
      $filterForm.on("submit", function (e) {
        e.preventDefault();

        var filters = {
          missing_title: $(this)
            .find('input[name="missing_title"]')
            .is(":checked")
            ? 1
            : 0,
          missing_description: $(this)
            .find('input[name="missing_description"]')
            .is(":checked")
            ? 1
            : 0,
          missing_focus_keyword: $(this)
            .find('input[name="missing_focus_keyword"]')
            .is(":checked")
            ? 1
            : 0,
          post_type: $(this).find('select[name="post_type"]').val(),
          search: $(this).find('input[name="search"]').val(),
          limit: 20,
          offset: 0,
        };

        $.ajax({
          url: ajaxurl,
          type: "POST",
          data: {
            action: "seo_simple_bulk_filter",
            nonce: seo_simple_admin.nonce,
            ...filters,
          },
          success: function (response) {
            if (response.success) {
              $(".seo-simple-bulk-table tbody").replaceWith(response.data.html);
              $bulkStatus.text("");

              // Update pagination info
              $(".seo-simple-bulk-actions-right").text(
                "Showing 1-" +
                  Math.min(response.data.limit, response.data.total) +
                  " of " +
                  response.data.total +
                  " posts"
              );

              // Update pagination links
              updatePaginationLinks(
                filters,
                response.data.total,
                response.data.limit,
                0
              );
            } else {
              alert("Error: " + (response.data && response.data.message ? response.data.message : "Unknown error occurred"));
            }
          },
          error: function (xhr, status, error) {
            alert("An error occurred while filtering posts. " + error);
          },
        });
      });
    }

    // Update pagination links
    function updatePaginationLinks(filters, total, limit, offset) {
      var $pagination = $(".seo-simple-pagination");
      var currentPage = Math.floor(offset / limit) + 1;
      var totalPages = Math.ceil(total / limit);
      var baseUrl = window.location.href.split("?")[0];

      // Build query string
      var queryString = "page=seo-simple-bulk-editor";
      for (var key in filters) {
        if (filters[key] && filters[key] !== 0 && filters[key] !== "") {
          queryString += "&" + key + "=" + encodeURIComponent(filters[key]);
        }
      }

      var $paginationHtml = $('<div class="seo-simple-pagination"></div>');

      // Previous button
      if (currentPage > 1) {
        var prevOffset = (currentPage - 2) * limit;
        $(
          '<a href="' +
            baseUrl +
            "?" +
            queryString +
            "&offset=" +
            prevOffset +
            '" class="button">&laquo; Previous</a>'
        ).appendTo($paginationHtml);
      }

      // Page info
      $(
        '<span class="seo-simple-pagination-info">Page ' +
          currentPage +
          " of " +
          totalPages +
          "</span>"
      ).appendTo($paginationHtml);

      // Next button
      if (currentPage < totalPages) {
        var nextOffset = currentPage * limit;
        $(
          '<a href="' +
            baseUrl +
            "?" +
            queryString +
            "&offset=" +
            nextOffset +
            '" class="button">Next &raquo;</a>'
        ).appendTo($paginationHtml);
      }

      $pagination.replaceWith($paginationHtml);
    }

    // Schema functionality
    var $schemaTypeField = $("#_seo_simple_schema_type");
    var $customSchemaField = $("#_seo-simple-custom-schema");
    var $customSchemaContainer = $(".seo-simple-custom-schema-field");
    var $validateSchemaButton = $(".seo-simple-validate-schema-button");
    var $schemaSpinner = $(".seo-simple-schema-spinner");
    var $schemaValidationResult = $(".seo-simple-schema-validation-result");
    var $previewSchemaButton = $(".seo-simple-preview-schema-button");
    var $previewSpinner = $(".seo-simple-preview-spinner");
    var $schemaPreview = $(".seo-simple-schema-preview");
    var $addFaqButton = $("#seo-simple-add-faq");

    // Handle schema type change
    $schemaTypeField.on("change", function () {
      var schemaType = $(this).val();

      // Hide all schema-specific fields
      $customSchemaContainer.hide();
      $(".seo-simple-faq-schema-field").hide();

      // Show relevant field based on selection
      if (schemaType === "custom") {
        $customSchemaContainer.show();
      } else if (schemaType === "faq") {
        $(".seo-simple-faq-schema-field").show();
      }
    });

    // Initialize schema type field
    $schemaTypeField.trigger("change");

    // Validate schema button click handler
    $validateSchemaButton.on("click", function () {
      var customSchema = $customSchemaField.val();

      if (!customSchema.trim()) {
        $schemaValidationResult
          .removeClass("success error")
          .addClass("error")
          .text("Please enter a JSON-LD schema.")
          .show();
        return;
      }

      // Show loading state
      $validateSchemaButton.prop("disabled", true);
      $schemaSpinner.show();
      $schemaValidationResult.hide();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_validate_schema",
          nonce: $("#seo_simple_meta_box_nonce").val(),
          schema: customSchema,
        },
        success: function (response) {
          if (response.success) {
            $schemaValidationResult
              .removeClass("error")
              .addClass("success")
              .text(response.data.message)
              .show();

            // Update schema with sanitized version if provided
            if (response.data.sanitized_schema) {
              $customSchemaField.val(response.data.sanitized_schema);
            }
          } else {
            $schemaValidationResult
              .removeClass("success")
              .addClass("error")
              .text(response.data.message)
              .show();
          }
        },
        error: function (xhr, status, error) {
          $schemaValidationResult
            .removeClass("success")
            .addClass("error")
            .text("An error occurred while validating the schema. " + error)
            .show();
        },
        complete: function () {
          // Hide loading state
          $validateSchemaButton.prop("disabled", false);
          $schemaSpinner.hide();
        },
      });
    });

    // Preview schema button click handler
    $previewSchemaButton.on("click", function () {
      // Show loading state
      $previewSchemaButton.prop("disabled", true);
      $previewSpinner.show();
      $schemaPreview.hide();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_preview_schema",
          nonce: $("#seo_simple_meta_box_nonce").val(),
          post_id: $("#post_ID").val(),
        },
        success: function (response) {
          if (response.success) {
            $schemaPreview.html(
              "<pre>" + JSON.stringify(response.data.schema, null, 2) + "</pre>"
            );
            $schemaPreview.show();
          } else {
            $schemaPreview.html(
              '<div class="error">' + (response.data && response.data.message ? response.data.message : "Unknown error occurred") + "</div>"
            );
            $schemaPreview.show();
          }
        },
        error: function (xhr, status, error) {
          $schemaPreview.html(
            '<div class="error">An error occurred while previewing the schema. ' + error + '</div>'
          );
          $schemaPreview.show();
        },
        complete: function () {
          // Hide loading state
          $previewSchemaButton.prop("disabled", false);
          $previewSpinner.hide();
        },
      });
    });

    // Add FAQ shortcode button click handler
    $addFaqButton.on("click", function () {
      var faqShortcode =
        '[seo_simple_faq question="Your Question"]Your Answer[/seo_simple_faq]';

      // Try to insert into content editor
      if (
        typeof tinyMCE !== "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden()
      ) {
        tinyMCE.activeEditor.execCommand(
          "mceInsertContent",
          false,
          faqShortcode
        );
      } else if ($("#content").length) {
        var contentField = $("#content");
        var currentPosition = contentField.prop("selectionStart");
        var currentValue = contentField.val();
        var newValue =
          currentValue.substring(0, currentPosition) +
          faqShortcode +
          currentValue.substring(currentPosition);
        contentField.val(newValue);
      } else {
        // Fallback: copy to clipboard
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(faqShortcode).select();
        document.execCommand("copy");
        $temp.remove();

        alert("FAQ shortcode copied to clipboard: " + faqShortcode);
      }
    });

    // Format JSON in custom schema field
    $customSchemaField.on("blur", function () {
      var value = $(this).val();

      if (!value.trim()) {
        return;
      }

      try {
        var json = JSON.parse(value);
        var formatted = JSON.stringify(json, null, 2);
        $(this).val(formatted);
      } catch (e) {
        // Invalid JSON, don't format
      }
    });

    // Add FAQ shortcode to meta box SEO tab
    $("#seo-simple-add-faq").on("click", function () {
      var faqShortcode =
        '[seo_simple_faq question="Your Question"]Your Answer[/seo_simple_faq]';

      // Try to insert into content editor
      if (
        typeof tinyMCE !== "undefined" &&
        tinyMCE.activeEditor &&
        !tinyMCE.activeEditor.isHidden()
      ) {
        tinyMCE.activeEditor.execCommand(
          "mceInsertContent",
          false,
          faqShortcode
        );
      } else if ($("#content").length) {
        var contentField = $("#content");
        var currentPosition = contentField.prop("selectionStart");
        var currentValue = contentField.val();
        var newValue =
          currentValue.substring(0, currentPosition) +
          faqShortcode +
          currentValue.substring(currentPosition);
        contentField.val(newValue);
      } else {
        // Fallback: copy to clipboard
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(faqShortcode).select();
        document.execCommand("copy");
        $temp.remove();

        alert("FAQ shortcode copied to clipboard: " + faqShortcode);
      }
    });

    // Validate schema in SEO tab
    $("#seo-simple-validate-schema").on("click", function () {
      var customSchema = $("#_seo_simple_custom_schema").val();

      if (!customSchema.trim()) {
        $("#seo-simple-schema-validation-result")
          .removeClass("success error")
          .addClass("error")
          .text("Please enter a JSON-LD schema.")
          .show();
        return;
      }

      // Show loading state
      $(this).prop("disabled", true);
      $("#seo-simple-schema-spinner").show();
      $("#seo-simple-schema-validation-result").hide();

      // Send AJAX request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "seo_simple_validate_schema",
          nonce: $("#seo_simple_meta_box_nonce").val(),
          schema: customSchema,
        },
        success: function (response) {
          if (response.success) {
            $("#seo-simple-schema-validation-result")
              .removeClass("error")
              .addClass("success")
              .text(response.data.message)
              .show();

            // Update schema with sanitized version if provided
            if (response.data.sanitized_schema) {
              $("#_seo_simple_custom_schema").val(
                response.data.sanitized_schema
              );
            }
          } else {
            $("#seo-simple-schema-validation-result")
              .removeClass("success")
              .addClass("error")
              .text(response.data.message)
              .show();
          }
        },
        error: function (xhr, status, error) {
          $("#seo-simple-schema-validation-result")
            .removeClass("success")
            .addClass("error")
            .text("An error occurred while validating the schema. " + error)
            .show();
        },
        complete: function () {
          // Hide loading state
          $("#seo-simple-validate-schema").prop("disabled", false);
          $("#seo-simple-schema-spinner").hide();
        },
      });
    });

    // Handle schema type change in SEO tab
    $("#_seo_simple_schema_type").on("change", function () {
      var schemaType = $(this).val();

      // Hide all schema-specific fields
      $("#seo-simple-custom-schema-field").hide();
      $("#seo-simple-faq-schema-field").hide();

      // Show relevant field based on selection
      if (schemaType === "custom") {
        $("#seo-simple-custom-schema-field").show();
      } else if (schemaType === "faq") {
        $("#seo-simple-faq-schema-field").show();
      }
    });

    // Initialize schema type field in SEO tab
    $("#_seo_simple_schema_type").trigger("change");
  });
})(jQuery);
