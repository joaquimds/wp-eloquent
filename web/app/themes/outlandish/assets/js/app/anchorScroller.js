/**
 * Handles anchor links to sections on the same page
 * Ensures they smoothly scroll to the target element
 */
const anchorScroller = {
  scrollTo: anchor => {
    if (anchor === "#") {
      return;
    }
    const $target = $(anchor);
    if ($target.length) {
      let scrollTop = $target.offset().top;
      const $fixedNavbar = $(".navbar.fixed-top");
      const $wpAdminBar = $("#wpadminbar");
      if ($fixedNavbar.length) {
        scrollTop -= $fixedNavbar.height();
      }
      if ($wpAdminBar.length) {
        scrollTop -= $wpAdminBar.height();
      }
      $("html, body").animate(
        {
          scrollTop: scrollTop
        },
        200
      );
    }
  },
  init: () => {
    $(document).ready(() => {
      const $allAnchors = $('a[href^="#"]');
      if ($allAnchors.length) {
        $allAnchors.click(event => {
          event.preventDefault();
          anchorScroller.scrollTo($(event.target).attr("href"));
        });
      }
    });
  }
};

anchorScroller.init();
