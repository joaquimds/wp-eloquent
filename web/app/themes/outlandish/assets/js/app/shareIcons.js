/**
 * Fixes the sticky share icons when scrolling down
 */
const stickyShareIcons = {
  fixOnScroll: ($element, $container) => {
    const scrollTop = $(document).scrollTop();
    const fixedPositionLeft = $container.offset().left;

    // fix them in the center of the screen
    const fixedPositionTop = $(window).height() / 2 - $element.height() / 2;

    const pastTop = scrollTop + fixedPositionTop >= $container.offset().top;
    const pastBottom =
      scrollTop + fixedPositionTop >=
      $container.offset().top + $container.height() - $element.height();

    let styles = {
      position: "absolute",
      top: 0,
      left: 0,
      bottom: "auto"
    };

    if (pastTop && !pastBottom) {
      styles = {
        position: "fixed",
        top: fixedPositionTop + "px",
        left: fixedPositionLeft + "px",
        bottom: "auto"
      };
    }

    if (pastBottom) {
      styles = {
        position: "absolute",
        top: "auto",
        left: 0,
        bottom: 0
      };
    }

    $element.css(styles);
  },
  init: () => {
    $(document).ready(() => {
      const $element = $(".js-sticky-share");

      if ($element.length) {
        const $container = $(".js-sticky-share-container");

        if ($container.height() > $element.height()) {
          stickyShareIcons.fixOnScroll($element, $container);
          $(window)
            .scroll(() => stickyShareIcons.fixOnScroll($element, $container))
            .resize(() => stickyShareIcons.fixOnScroll($element, $container));
        }
      }
    });
  }
};

stickyShareIcons.init();
