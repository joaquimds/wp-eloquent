import Cookies from "js-cookie";

/**
 * Handles the cookie banner closing. Gets and sets a cookie.
 */
const cookieBanner = {
  cookie: {
    name: "cookie_consent",
    value: "1",
    daysDuration: 365
  },
  init: () => {
    $(document).ready(() => {
      const $banner = $(".js-cookie-banner");

      if ($banner.length) {
        if (
          Cookies.get(cookieBanner.cookie.name) !== cookieBanner.cookie.value
        ) {
          const $closeButton = $(".js-close-cookie-banner");

          $banner.show();
          $closeButton.click(() => {
            // close banner
            $banner.fadeOut(50);
            // store cookie
            Cookies.set(cookieBanner.cookie.name, cookieBanner.cookie.value, {
              expires: cookieBanner.cookie.daysDuration
            });
          });
        }
      }
    });
  }
};

cookieBanner.init();
