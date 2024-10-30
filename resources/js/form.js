(function ($) {
  "use strict";

  $(function () {
    $("#booking_event_date").on("change", function () {
      const el = $(this).find(`option[data-id="${$(this).val()}"]`);
      const form = $("#fa-book__form");

      if (el) {
        $(form).find("#event-id").val($(el).data("id"));
        $(form).find("#event-status").val($(el).data("status"));
        $(form)
          .find(".js-modal__submit .btn_title")
          .text($(el).data("title"));

        $("#add_new_participant")
          .data("available-seats", $(el).data("available-seats"))
          .data("status", $(el).data("status"))
          .data("max-participants", $(el).data("max-participants"));

        $("#event_status_message").html($(el).data("status-message"));

        update_event_desc({
          title: $(el).data("name"),
          date: $(el).data("date-start")
        });

        if ($(el).data("status") === "booked") {
          remove_overbooked(el);
        }

        update_available_participants();
      }
    });

    function remove_overbooked(el) {
      const availableSeats = +$(el).data("available-seats");
      const currentParticipants = +$(".js-contact_participant:visible").length;
      const overbookedParticipants = currentParticipants - availableSeats;

      if (overbookedParticipants > 0) {
        for (let i = 0; i < overbookedParticipants; i++) {
          $(".js-contact_participant").last().remove();
        }
      }
    }

    function update_event_desc(param) {
      $(".js-event__date-title").html(param["title"]);
      $(".js-event__date-start").html(param["date"]);
    }

    /**
     * Reset form fields
     */
    function reset_form() {
      $("#fa-book__form").trigger("reset");
    }

    /**
     * Remove participants section
     */
    $("body").on(
      "click",
      ".contact_participant-template .js-participant-remove",
      function () {
        const status = $(this).data("status");
        const section = $(this).closest(".contact_participant-template");
        $(section).remove();

        if (status !== "reserved") {
          update_available_participants();
        }
      }
    );

    /**
     * Update Dietary field
     */
    $("#dietary_preferences").on("change", function () {
      const cd = $(this)
        .closest(".row")
        .find(".js-dietary_preferences_custom #dietary_preferences_custom");

      if ($(this).val()) {
        cd.attr("disabled", true);
        cd.val("");
      } else {
        cd.attr("disabled", false);
      }
    });

    /**
     * Add new Participant template
     */
    $("#add_new_participant").on("click", function () {
      let template = $("#participant_template").html();
      const status = $(this).data("status");

      template = template.replaceAll(
        "{%number%}",
        (Math.random() + 1).toString(36).substring(7)
      );

      if (status === "reserved") {
        $("#participant_template").before(template);
        return;
      }

      $("#participant_template").before(template);
      update_available_participants();
    });

    /**
     * Update available participants in form
     */
    function update_available_participants() {
      const $add_participant_btn = $("#add_new_participant");
      if ($add_participant_btn.length) {
        const status = $add_participant_btn.data("status");
        const participantCount = +$(".js-contact_participant:visible").length;

        if (status === "reserved") {
          $add_participant_btn.removeAttr("disabled");
          return;
        }

        const availableSeats = +$add_participant_btn.data("available-seats");

        if (participantCount >= availableSeats) {
          $add_participant_btn.prop("disabled", true);
        } else {
          $add_participant_btn.removeAttr("disabled");
        }
      }
    }

    /**
     * Get Isset Company by name
     */
    $("#corporate_name").on("blur", function () {
      let company_name = $(this).val();

      if (company_name.length === 0) {
        return;
      }

      //eslint-disable-next-line
      const ajaxUrl = casa_courses_config?.restUrl ?? "";
      //eslint-disable-next-line
      const ajax_nonce = casa_courses_config?.ajax_nonce ?? "";
      $.ajax({
        url: ajaxUrl + `companies/`,
        type: "post",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", ajax_nonce);
        },
        data: {
          ajax_nonce,
          company_name
        },
        success: (response) => {
          if (response.success === true) {
            const company = response.data.message;
            const result = response.data.result;

            if (company) {
              const conf = window.confirm(company);

              if (conf) {
                result.id?.length > 0 &&
                  $("#company_id").val(result.id);
                result.address?.address_row_1?.length > 0 &&
                  $("#invoice_address").val(result.address.address_row_1);
                result.address?.city?.length > 0 &&
                  $("#city").val(result.address.city);
                result.address?.zip_code?.length > 0 &&
                  $("#zip_code").val(result.address.zip_code);
                result.address?.email?.length > 0 &&
                  $("#e_invoice_address").val(result.address.email);
                result.corporate_id?.length > 0 &&
                  $("#corporate_id")?.val(result.corporate_id);
                result.sector?.length > 0 &&
                  $("#industry")?.val(result.sector);
              }
            }
          }
        }
      });
    });

    /**
     * Update Contact Participant fields
     */
    $("#contact_is_participant").on("change", function () {
      let checked = $(this).is(":checked");

      if (checked) {
        const participantCount = +$(".js-contact_participant:visible").length;
        const availableSeats = +$("#add_new_participant").data("available-seats");

        if ((participantCount - availableSeats) === 0) {
          $(".js-contact_participant").last().remove();
        }

        let template = $("#contact_participant_template").html();
        let active_item = $(".js-contact_participant:visible").length;
        $(".js-contact_participant:visible")?.first()?.remove();
        $("#contact_participant_template").before(template);
        $("#p-first-name0").val($("#first-name").val());
        $("#p-last-name0").val($("#last-name").val());
        $("#p-email0").val($("#email").val());
        $("#p-phone0").val($("#phone").val());

        active_item || update_available_participants();
      } else {
        $("#contact_participant").remove();

        update_available_participants();
      }
    });

    /**
     * Update Contact Participant fields after focus out
     */
    $("#first-name").on("focusout", function () {
      $("#p-first-name0").val($(this).val());
    });

    $("#last-name").on("focusout", function () {
      $("#p-last-name0").val($(this).val());
    });

    $("#email").on("focusout", function () {
      $("#p-email0").val($(this).val());
    });

    $("#phone").on("focusout", function () {
      $("#p-phone0").val($(this).val());
    });
    /***/

    /**
     * Sending form action
     */
    //eslint-disable-next-line

    if (casa_courses_config?.google_recaptcha_enabled) {
      grecaptcha?.ready(function () {
        $("#fa-book__form").on("submit", function (e) {
          e.preventDefault();

          //eslint-disable-next-line
          grecaptcha
              .execute(casa_courses_config?.google_recaptcha, {
                action: "submit"
              })
              .then((token) => {
                submitForm(this, token);
              });

          return false;
        });
      });
    } else {
      $("#fa-book__form").on("submit", function (e) {
        e.preventDefault();
        submitForm(this, null);
        return false;
      });
    }

    function show_alert_message(response, el_alert) {
      let html = "";
      el_alert.html(html);
      let alert_class = "alert-success";

      if (response.success !== true) {
        alert_class = "alert-danger";
      }

      if (typeof response.message === "object") {
        for (let e in response.message) {
          if (!Object.prototype.hasOwnProperty.call(response.message, e))
            continue;

          validationMessage(e, response.message[e]);
        }
      } else {
        el_alert.append(
          `<p class="alert ${alert_class}" style="">${response.message}</p>`
        );
      }

      el_alert.show();
    }

    function submitForm(form, token) {
      const inputs = [
        {
          property: 'last_name',
          input: 'input[name="participant_last_name[]"]'
        },
        {
          property: 'email',
          input: 'input[name="participant_email[]"]'
        },
        {
          property: 'cell_phone_number',
          input: 'input[name="participant_cell_phone_number[]"]'
        },
        {
          property: 'dietary_preferences',
          input: 'select[name="dietary_preference[]"]'
        },
        {
          property: 'dietary_preferences_custom',
          input: 'input[name="dietary_preferences_custom[]"]'
        },
      ];
      const el_alert = $(form).find(".alert-message");
      const el_form = $(form);
      let formData = {};

      el_form.find(".is-invalid").removeClass("is-invalid");
      //eslint-disable-next-line
      const ajaxUrl = casa_courses_config?.restUrl ?? "";
      //eslint-disable-next-line
      const ajax_nonce = casa_courses_config?.ajax_nonce ?? "";

      el_form.find(".submit").prop("disabled", true);
      el_form.find(".js-modal__submit-spinner").show();

      el_form.find('input,select').each(function () {
        let input = $(this);
        let inputName = input.attr('name');
        let inputValue = input.val();

        if (!inputName.endsWith('[]')) {
          formData[inputName] = inputValue;
        }
      });

      formData['participants'] = [];

      el_form.find('input[name="participant_first_name[]"]').each(function() {
        const input = $(this);
        formData['participants'].push({first_name: input.val()});
      });

      for(const input of inputs) {
        el_form.find(input.input).each(function(index) {
          formData['participants'][index][input.property] = $(this).val();
        });
      }

      $.ajax({
        url: ajaxUrl + "connect-event",
        type: "post",
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", ajax_nonce);
          if (token) {
            xhr.setRequestHeader("X-recaptcha-response", token);
          }
        },
        data: {
          ajax_nonce,
          form_data: formData
        },
        success: (response) => {
          if (response.success === true) {
            let data = response.data;

            el_alert.show();
            el_alert.html(
                `<p class="alert alert-success">${data.message}</p>`
            );
            reset_form();
            $(el_form).find(".submit").addClass("disabled").prop("disabled", true);

            show_success_block(data.labels, data.participants);
          } else {
            show_alert_message(response.data, el_alert);
          }
        },
        error: (response) => {
          if (response.status === 500) {
            show_error_block(response.message);
          } else {
            show_alert_message(response.responseJSON.data, el_alert);
          }
        },
        complete: () => {
          $(el_form).find(".submit").prop("disabled", false);
          $(el_form).find(".js-modal__submit-spinner").hide();
        }
      });
    }

    function validationMessage(e, message) {
      switch (e) {
        case "participants":
          validate_participants(message);
          break;
        case "company":
          validate_company(message);
          break;
        case "token":
          validate_token(message);
          break;
      }
    }

    /**
     * Validate Token
     */
    function validate_token(message) {
      //eslint-disable-next-line
      grecaptcha.reset();

      $(".alert-message").append(
        `<p class="alert alert-danger">Token: ${message[0]}</p>`
      );
    }

    /**
     * Show Success Section after submitting form
     * @param {Array} participants
     */
    function show_success_block(labels, participants) {
      $(".casa-form__section").hide();
      $(".casa-form__submit-section").show();

      if (
        "booked_on_event" in participants &&
        participants.booked_on_event.length > 0
      ) {
        add_participant_info(
          labels,
          participants.booked_on_event,
          $("#js-booked__participants")
        );
      }

      if (
        "not_booked_on_event" in participants &&
        participants.not_booked_on_event.length > 0
      ) {
        add_participant_info(
          labels,
          participants.not_booked_on_event,
          $("#js-not_booked__participants")
        );
      }
    }

    /**
     * Show Error Section after submitting form
     * @param {Array} participants
     */
    function show_error_block(participants) {
      $(".casa-form__section").hide();

      $(".casa-form__error-section").show();
    }

    function add_participant_info(labels, participants, el) {
      participants.forEach((e) => {
        let template = $("#template-book__participants").html();
        let html = "";

        if (e?.email && e?.email.length > 0) {
          html += `<li>${labels.email}: ${e.email}</li>`;
        }

        if (e?.cell_phone_number && e?.cell_phone_number.length > 0) {
          html += `<li>${labels.phone}: ${e.cell_phone_number}</li>`;
        }

        if (e?.participant_name && e?.participant_name.length > 0) {
          html += `<li>${labels.name}: ${e.participant_name}</li>`;
        }

        if (e?.status && e?.status.length > 0) {
          html += `<li>${labels.status}: ${labels[e.status]}</li>`;
        }

        template = template.replaceAll("{%items%}", html);

        el.append(template);
      });

      el.show();
    }

    /**
     * Validate company section
     */
    function validate_company(message) {
      if (typeof message === "object") {
        const company = $(".fa-book__form");

        for (let e in message) {
          const el = $(company).find(`[name*="company_${e}"]`);
          const el_invalid = el.siblings(`.invalid-feedback`);

          $(el).addClass("is-invalid");
          $(el_invalid).html(message[e][0]);
        }
      } else {
        $(".alert-message").append(
          `<p class="alert alert-danger">Company: ${message[0]}</p>`
        );
      }
    }

    /**
     * Validate participants section
     */
    function validate_participants(message) {
      if (Array.isArray(message) && typeof message[0] !== "string") {
        message.forEach((item, index) => {
          validate_participant(item, index);
        });
      } else {
        for (let e in message) {
          if (Array.isArray(message[e])) {
            $(".alert-message").append(
              `<p class="alert alert-danger">${message[e][0]}</p>`
            );
          } else {
            $(".alert-message").append(
              `<p class="alert alert-danger">Participants: ${message[0]}</p>`
            );
          }
        }
      }
    }

    /**
     * Validate participant section
     */
    function validate_participant(item, i) {
      const participant = $(".js-contact_participant");

      if (participant && typeof participant[i] !== "undefined") {
        if (typeof item["participant"] !== "undefined") {
          for (let e in item["participant"]) {
            const el = $(participant[i]).find(`[name*="participant_${e}"]`);
            const el_invalid = el.siblings(`.invalid-feedback`);

            $(el).addClass("is-invalid");
            $(el_invalid).html(item["participant"][e][0]);
          }
        }

        if (typeof item["dietary_preference"] !== "undefined") {
          $(participant[i])
            .find(`[name^="dietary_preference"]`)
            .addClass("is-invalid")
            .siblings(`.invalid-feedback`)
            .html(item["dietary_preference"][0]);
        }
      }
    }

    update_available_participants();
  });
})(jQuery);
