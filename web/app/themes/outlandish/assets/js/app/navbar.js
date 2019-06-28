/**
 * Hides a fixed nav on mobile devices when scrolling down
 * Nav re-appears as soon as scrolling up starts
 */
const navHider = {
  hideOnMobile: ($fixed, $toggler) => {
    // only hide if menu isn't open
    if ($toggler.attr("aria-expanded") === "false") {
      if (scrollDetector.direction === "up") {
        $fixed.removeClass("js-hide-nav");
      } else {
        $fixed.toggleClass(
          "js-hide-nav",
          $(document).scrollTop() > $fixed.height()
        );
      }
    }
  },
  init: () => {
    $(document).ready(() => {
      const $fixed = $(".navbar.fixed-top");

      if ($fixed.length) {
        const $toggler = $(".navbar-toggler");

        scrollDetector.init();
        $(window).scroll(() => navHider.hideOnMobile($fixed, $toggler));
      }
    });
  }
};

const scrollDetector = {
  position: $(window).scrollTop(),
  direction: null,
  init: () => {
    $(window).scroll(() => {
      let scroll = $(window).scrollTop();
      if (scroll > scrollDetector.position) {
        scrollDetector.direction = "down";
      } else {
        scrollDetector.direction = "up";
      }
      scrollDetector.position = scroll;
    });
  }
};

navHider.init();
