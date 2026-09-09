(function ($) {
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    $("#page-content-wrapper").css("min-height", $(document).height());

    $("select.select2").each(function () {
        var $this = $(this);

        $this.select2({
            placeholder: $this.data("placeholder"),
            minimumResultsForSearch:
                $this.data("minimum-results-for-search") || 10,
        });
    });

    $(".sidebar-nav .submenu-toggle").on("click", function (e) {
        e.preventDefault();
        var $parentLi = $(this).closest(".has-submenu");
        $parentLi.toggleClass("active");
        $parentLi.find(".submenu").slideToggle(250);
    });

    $(".sidebar-nav .has-submenu").each(function () {
        if ($(this).hasClass("active")) {
            $(this).find(".submenu").show();
        } else {
            $(this).find(".submenu").hide();
        }
    });

    $("#save-and-back").on("click", function () {
        $(this).before(
            '<input type="hidden" name="save_and_back" value="1" />'
        );
    });

    $("button").on("click", function () {
        $("button").removeClass("clicking");
        $(this).addClass("clicking");
    });

    /*** GENERATE DATA LABELS FOR RESPONSIVE SCREENS */
    $(".table").each(function () {
        const $table = $(this);
        const $headers = $table.find("thead th");

        $table.find("tbody tr").each(function () {
            $(this)
                .find("td")
                .each(function (index) {
                    const label = $headers.eq(index).text().trim();
                    $(this).attr("data-label", label);
                });
        });
    });

    /** GENERATE LABEL AND CONNECT TEXT FIELDS WITH LABELS */
    $('input.form-control,select.form-control').each(function(index, elem) {
        const name = $(elem).attr('name');
        $(elem).prev('label').addClass('form-label').attr('for', name);
        $(elem).attr('id', $(elem).attr('name'));
    });
})($);
