(function ($) {
  "use strict";

  $(document).ready(function () {
    /** tabs */
    $(".com-tab-group li a").on("click", function (e) {
      e.preventDefault();

      let data_key = "." + $(this).data("key");

      $(".com-tab-group li.active").removeClass("active");
      $(this).parent().addClass("active");

      $(".metadata-wrap").addClass("hidden");
      $(data_key).removeClass("hidden");
    });

    /** color field */
    $(".color_field").each(function () {
      $(this).wpColorPicker({
        mode: "hsl",
        controls: {
          horiz: "s", // horizontal defaults to saturation
          vert: "l", // vertical defaults to lightness
          strip: "h" // right strip defaults to hue
        }
        //palettes: ["#eee", "#F0544F", "#222", "#1E72BD", "#666", "#191c5c"],
      });
    });

    $("#casa_courses_synchronization").on("click", function () {
      //eslint-disable-next-line
      const ajaxUrl = casa_courses_config?.restUrl ?? "";
      //eslint-disable-next-line
      const ajaxNonce = casa_courses_config?.ajax_nonce ?? "";

      $(this).prop("disabled", true);

      $.ajax({
        url: ajaxUrl + "sync",
        type: "post",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", ajaxNonce);
        },
        data: {
          ajax_nonce: ajaxNonce
        },
        success: (response) => {
          let message = JSON.parse(response.data.message);
          $(this).after(
            `<span class="alert_message success">${message}</span>`
          );
        },
        error: (response) => {
          let message = JSON.parse(response.data.message);
          $(this).after(`<span class="alert_message error">${message}</span>`);
        },
        complete: () => {
          $(this).prop("disabled", false);
          setTimeout(() => {
            $(".alert_message").remove();
          }, 7000);
        }
      });
    });

    /** repeater field */
    $(".add-row").on("click", function () {
      let row = $(this)
        .parent("p")
        .siblings("table")
        .find("tbody tr.empty-row.screen-reader-text")
        .clone(true);
      row.removeClass("empty-row screen-reader-text");
      row.insertBefore(
        $(this).parent("p").siblings("table").find("tbody tr:last")
      );
      return false;
    });

    $(".remove-row").on("click", function () {
      $(this).parents("tr").remove();
      return false;
    });

    $(".com_upload_image").on("click", function (e) {
      e.preventDefault();

      var com_upload_button = $(this);

      let frame;

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media.frames.customBackground = wp.media({
        title: "choose image",
        library: {
          type: "image"
        },
        button: {
          text: "Upload"
        },
        multyple: false
      });

      frame.on("select", function (e) {
        var attachment = frame.state().get("selection").first();

        // and show the image's data
        var image_id = attachment.id;

        var image_url = attachment.attributes.url;

        // pace an id
        com_upload_button.parent().find(".com_upload_image_save").val(image_id);

        // show an image
        com_upload_button
          .parent()
          .find(".com_upload_image_show")
          .attr("src", image_url);
        com_upload_button.parent().find(".com_upload_image_show").show();

        // show "remove button"
        com_upload_button.parent().find(".com_upload_image_remove").show();

        // hide "upload" button
        com_upload_button.hide();
      });
      frame.open();
    });

    // remove image
    $(".com_upload_image_remove").on("click", function (e) {
      var remove_button = $(this);

      e.preventDefault();

      // remove an id
      remove_button.parent().find(".com_upload_image_save").val("");

      // hide an image
      remove_button.parent().find(".com_upload_image_show").attr("src", "");
      remove_button.parent().find(".com_upload_image_show").hide();

      // show "Upload button"
      remove_button.parent().find(".com_upload_image").show();

      // hide "remove" button
      remove_button.hide();
    });

    // Industry required validation
    $(`input[name="casa_courses_industry_required"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_required"]:checked'
        );
        if ($(this).val() === "true" && $(company_r).val() !== "true") {
          return false;
        }

        return true;
      }
    );

    // Industry visible validation
    $(`input[name="casa_courses_industry_visible"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_visible"]:checked'
        );
        if ($(this).val() === "true" && $(company_r).val() !== "true") {
          return false;
        }

        return true;
      }
    );

    // Company ID required validation
    $(`input[name="casa_courses_company_id_required"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_required"]:checked'
        );
        if ($(this).val() === "true" && $(company_r).val() !== "true") {
          return false;
        }

        return true;
      }
    );

    // Company ID visible validation
    $(`input[name="casa_courses_company_id_visible"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_visible"]:checked'
        );
        if ($(this).val() === "true" && $(company_r).val() !== "true") {
          return false;
        }

        return true;
      }
    );

    // Company required validation
    $(`input[name="casa_courses_company_required"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_required"]:checked'
        );

        if ($(this).val() === "false") {
          const industry = $(
            'input[name="casa_courses_industry_required"]'
          );
          const company_id = $(
            'input[name="casa_courses_company_id_required"]'
          );

          $(industry[0]).prop("checked", "");
          $(industry[1]).prop("checked", "checked");
          $(company_id[0]).prop("checked", "");
          $(company_id[1]).prop("checked", "checked");
        }
      }
    );

    // Company visible validation
    $(`input[name="casa_courses_company_visible"]`).on(
      "click",
      function (e) {
        const company_r = $(
          'input[name="casa_courses_company_visible"]:checked'
        );
        if ($(this).val() === "false") {
          const industry = $('input[name="casa_courses_industry_visible"]');
          const company_id = $(
            'input[name="casa_courses_company_id_visible"]'
          );

          $(industry[0]).prop("checked", "");
          $(industry[1]).prop("checked", "checked");

          $(company_id[0]).prop("checked", "");
          $(company_id[1]).prop("checked", "checked");
        }
      }
    );

    $('#_add_areas_image').on("click", function () {
      wp.media.editor.send.attachment = function (props, attachment) {
        $('#casa_courses_areas_image-view').attr("src", attachment.url).show();
        $('.casa__courses-link').show();
        $('.casa-courses-areas-image').val(attachment.id);
      }
      wp.media.editor.open(this);
      return false;
    });

    $('#_remove_areas_image').on("click", function (e) {
      e.preventDefault();
      $('.casa-courses-areas-image').val("");
      $('#casa_courses_areas_image-view').attr("src", "").hide();
      $('.casa__courses-link').hide();
    });

    $('.casa-upload-image').on("click", function () {
      let id = $(this).attr("id").replace("_add_image_", "");

      wp.media.editor.send.attachment = function (props, attachment) {
        $('.' + id + '-wrap #casa_courses_image-view').attr("src", attachment.url).show();
        $('.' + id + '-wrap .casa__courses-link').show();
        $('.' + id + '-wrap .casa-courses-image').val(attachment.id);
      }
      wp.media.editor.open(this);
      return false;
    });

    $('.casa-remove-image').on("click", function () {
      let id = $(this).attr("id").replace("_remove_image_", "");
      $('.' + id + '-wrap .casa-courses-image').val("");
      $('.' + id + '-wrap #casa_courses_image-view').attr("src", "").hide();
      $('.' + id + '-wrap .casa__courses-link').hide();
      return false;
    });
  });
})(jQuery);
