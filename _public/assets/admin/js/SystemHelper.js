var SystemHelper = {
    version: "1.0",

    initEditor: function (selector) {
        ClassicEditor.create(document.querySelector(selector)).catch(
            (error) => {
                console.error(error);
            }
        );
    },

    ajaxSubmit: function ($forms, ajaxSuccess = null) {
        if (!$forms.length) return;

        $forms.each(function () {
            const $form = $(this);

            $form.on("submit", function (e) {
                e.preventDefault();

                const nativeForm = this;

                if (!(nativeForm instanceof HTMLFormElement)) {
                    console.error("Element is not a valid form", nativeForm);
                    return;
                }

                const enctype = $form.attr("enctype");
                const isMultipart = enctype === "multipart/form-data";

                const formData = isMultipart
                    ? new FormData(nativeForm)
                    : $form.serialize();

                const options = {
                    url: $form.attr("action"),
                    method: $form.attr("method") || "POST",
                    data: formData,
                };

                if (isMultipart) {
                    options.processData = false;
                    options.contentType = false;
                }

                const button = $form.find("button.clicking");
                const width = button.outerWidth();
                const html = button.html();

                $.ajax({
                    ...options,
                    beforeSend: function () {
                        button.css("min-width", width).html(`
                        <div class="spinner-border spinner-border-sm text-white" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>`);
                    },
                    success: function (response) {
                        $.jGrowl(response.message, {
                            header: "Success",
                            theme: "jgrowl-success",
                        });

                        /** if there is a closure function call it */
                        if (ajaxSuccess && typeof ajaxSuccess == "function") {
                            ajaxSuccess();
                        }

                        /** if has an redirect url then go there */
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }

                        /** if ask for form reset, reset it */
                        if (response.reset_form) {
                            $form[0]?.reset();
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            Object.values(xhr.responseJSON.errors)
                                .map((b) => b[0])
                                .map((errorMessage) => {
                                    $.jGrowl(errorMessage, {
                                        header: "Error",
                                        theme: "jgrowl-error",
                                    });
                                });
                        } else {
                            $.jGrowl("An unknown error occurred.", {
                                header: "Error",
                                theme: "jgrowl-error",
                            });
                        }
                    },
                    complete: function () {
                        button.html(html);
                    },
                });
            });
        });
    },
};


$(document).ready(function() {
    function renderFormControls()
    {
        $(document).find('.form-control[required]').each(function() {
            var $input = $(this);
            var $label = $input.closest('.form-group, .mb-3, .form-floating').find('label').first();
            
            if ($label.length && !$label.hasClass('required-marked')) {
                $label.html($label.html() + '<strong class="ms-1" style="color: red">*</strong> ');
                $label.addClass('required-marked'); 
            }
        });
    };

    window.renderFormControls = renderFormControls;
    
    renderFormControls();
});