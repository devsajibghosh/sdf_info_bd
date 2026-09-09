(function ($) {
  "use strict";

  // ==========================================
  //      Start Document Ready function
  // ==========================================
  $(document).ready(function () {

    // ============== Header Hide Click On Body Js Start ========
    $('.header-button').on('click', function () {
      $('.body-overlay').toggleClass('show')
    });
    $('.body-overlay').on('click', function () {
      $('.header-button').trigger('click')
      $(this).removeClass('show');
    });
    // =============== Header Hide Click On Body Js End =========

    // ========================== Header Hide Scroll Bar Js Start =====================
    $('.navbar-toggler.header-button').on('click', function () {
      $('body').toggleClass('scroll-hide-sm')
    });
    $('.body-overlay').on('click', function () {
      $('body').removeClass('scroll-hide-sm')
    });
    // ========================== Header Hide Scroll Bar Js End =====================

    // ========================== Mobile Sidebar Esc Key Js Start =====================
    $(document).on('keydown', function (e) {
      if (e.key === 'Escape' && $('#navbarSupportedContent').hasClass('show')) {
        $('.header-button').trigger('click');
      }
    });
    // ========================== Mobile Sidebar Esc Key Js End =====================

    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js Start =====================
    $('.dropdown-item').on('click', function () {
      $(this).closest('.dropdown-menu').addClass('d-block')
    });
    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js End =====================

    // ========================== Add Attribute For Bg Image Js Start =====================
    $(".bg-img").css('background', function () {
      var bg = ('url(' + $(this).data("background-image") + ')');
      return bg;
    });
    // ========================== Add Attribute For Bg Image Js End =====================



    // ================== Password Show Hide Js Start ==========
    $(".toggle-password").on('click', function () {
      $(this).toggleClass(" fa-eye-slash");
      var input = $($(this).attr("id"));
      if (input.attr("type") == "password") {
        input.attr("type", "text");
      } else {
        input.attr("type", "password");
      }
    });
    // =============== Password Show Hide Js End =================

    // ========================= Slick Slider Js Start ==============
    $('.banner-slider').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 5000,
      speed: 900,
      dots: true,
      pauseOnHover: true,
      arrows: false,
      prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-long-arrow-alt-left"></i></button>',
      nextArrow: '<button type="button" class="slick-next"><i class="fas fa-long-arrow-alt-right"></i></button>',

    });
    // ========================= Slick Slider Js End ===================

    // ========================= Client Slider Js Start ===============
    $('.client-slider').slick({
      slidesToShow: 6,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 1000,
      pauseOnHover: true,
      speed: 2000,
      dots: false,
      arrows: false,
      prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-long-arrow-alt-left"></i></button>',
      nextArrow: '<button type="button" class="slick-next"><i class="fas fa-long-arrow-alt-right"></i></button>',
      responsive: [
        {
          breakpoint: 1199,
          settings: {
            slidesToShow: 6,
          }
        },
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 5
          }
        },
        {
          breakpoint: 767,
          settings: {
            slidesToShow: 4
          }
        },
        {
          breakpoint: 400,
          settings: {
            slidesToShow: 3
          }
        }
      ]
    });
    // ========================= Client Slider Js End ===================

    // ================== Sidebar Menu Js Start ===============
    // Sidebar Dropdown Menu Start
    $(".has-dropdown > a").click(function () {
      $(".sidebar-submenu").slideUp(200);
      if (
        $(this)
          .parent()
          .hasClass("active")
      ) {
        $(".has-dropdown").removeClass("active");
        $(this)
          .parent()
          .removeClass("active");
      } else {
        $(".has-dropdown").removeClass("active");
        $(this)
          .next(".sidebar-submenu")
          .slideDown(200);
        $(this)
          .parent()
          .addClass("active");
      }
    });
    // Sidebar Dropdown Menu End
    // Sidebar Icon & Overlay js 
    $(".dashboard-body__bar-icon").on("click", function () {
      $(".sidebar-menu").addClass('show-sidebar');
      $(".sidebar-overlay").addClass('show');
    });
    $(".sidebar-menu__close, .sidebar-overlay").on("click", function () {
      $(".sidebar-menu").removeClass('show-sidebar');
      $(".sidebar-overlay").removeClass('show');
    });
    // Sidebar Icon & Overlay js 
    // ===================== Sidebar Menu Js End =================

    // ==================== Dashboard User Profile Dropdown Start ==================
    $('.user-info__button').on('click', function (event) {
      event.stopPropagation(); // Prevent the click event from propagating to the body
      $('.user-info-dropdown').toggleClass('show');
    });

    $('.user-info-dropdown__link').on('click', function (event) {
      event.stopPropagation(); // Prevent the click event from propagating to the body
      $('.user-info-dropdown').addClass('show')
    });

    $('body').on('click', function () {
      $('.user-info-dropdown').removeClass('show');
    })
    // ==================== Dashboard User Profile Dropdown End ==================

    // ==================== Custom Sidebar Dropdown Menu Js Start ==================
    $('.has-submenu').on('click', function (event) {
      event.preventDefault(); // Prevent the default anchor link behavior

      // Check if this submenu is currently visible
      var isOpen = $(this).find('.sidebar-submenu').is(':visible');

      // Hide all submenus initially
      $('.sidebar-submenu').slideUp();

      // Remove the "active" class from all li elements
      $('.sidebar-menu__item').removeClass('active');

      // If this submenu was not open, toggle its visibility and add the "active" class to the clicked li
      if (!isOpen) {
        $(this).find('.sidebar-submenu').slideToggle(500);
        $(this).addClass('active');
      }
    });
    // ==================== Custom Sidebar Dropdown Menu Js End ==================

    // ========================= Odometer Counter Up Js End ==========
    $(".counterup-item").each(function () {
      $(this).isInViewport(function (status) {
        if (status === "entered") {
          for (var i = 0; i < document.querySelectorAll(".odometer").length; i++) {
            var el = document.querySelectorAll('.odometer')[i];
            el.innerHTML = el.getAttribute("data-odometer-final");
          }
        }
      });
    });
    // ========================= Odometer Up Counter Js End =====================

  });
  // ==========================================
  //      End Document Ready function
  // ==========================================

  // ========================= Preloader Js Start =====================
  $(window).on("load", function () {
    $('.preloader').fadeOut();
  })
  // ========================= Preloader Js End=====================

  // ========================= Header Sticky Js Start ==============
  $(window).on('scroll', function () {
    if ($(window).scrollTop() >= 300) {
      $('.header').addClass('fixed-header');
    }
    else {
      $('.header').removeClass('fixed-header');
    }
  });
  // ========================= Header Sticky Js End===================


  $(document).ready(function () {
    $('.popup-gallery').magnificPopup({
        delegate: 'a.popup-item',
        type: 'image',
        gallery: {
            enabled: true
        },
        image: {
            titleSrc: 'title' // <-- use the title attribute
        }
    });
  });

  //============================ Scroll To Top Icon Js Start =========
  var btn = $('.scroll-top');

  $(window).scroll(function () {
    if ($(window).scrollTop() > 300) {
      btn.addClass('show');
    } else {
      btn.removeClass('show');
    }
  });

  btn.on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, '300');
  });
  //========================= Scroll To Top Icon Js End ======================

})(jQuery);
