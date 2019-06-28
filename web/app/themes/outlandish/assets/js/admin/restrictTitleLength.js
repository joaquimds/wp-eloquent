(function() {
  var $ = jQuery;
  var $titleContainer = $("#titlewrap");

  if ($titleContainer.length > 0) {
    var limits = window.restrict_title_length.limits;
    var postType = window.restrict_title_length.post_type;
    if (postType in limits) {
      var limit = limits[postType];
      var $input = $titleContainer.find("input");
      $input.on("keyup", onChange);
      $input.on("keypress", onChange);
      var $limitContainer = $('<div class="character-limit"></div>').appendTo(
        $titleContainer
      );
      var $submitButton = $("#publishing-action").find("input[type=submit]");

      function onChange() {
        var length = $input.val().length;
        var remaining = limit - length;
        $limitContainer
          .text("Characters remaining: " + remaining)
          .toggleClass("warning", remaining < 0);
        $submitButton.prop("disabled", remaining < 0);
        $submitButton.prop(
          "title",
          remaining < 0 ? "Cannot update: title is too long" : ""
        );
      }

      onChange();
    }
  }
})();
