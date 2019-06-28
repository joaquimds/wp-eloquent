/**
 * Back to top button, only appears on mobile
 */
const backToTop = {
  onScroll: $button => {
    $button.toggleClass("to-top--visible", window.scrollY > 1000);
  },
  onClick: () => {
    $("html, body").animate(
      {
        scrollTop: 0
      },
      200
    );
  },
  init: () => {
    $(document).ready(() => {
      const $button = $(".js-to-top");
      if ($button.length) {
        $(window)
          .scroll(() => backToTop.onScroll($button))
          .resize(() => backToTop.onScroll($button));

        $button.click(backToTop.onClick);
      }
    });
  }
};

backToTop.init();
