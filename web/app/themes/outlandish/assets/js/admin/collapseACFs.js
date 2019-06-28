// Collapse some ACFs on load / after save, to keep the EDIT page neat
(function() {
  var $ = jQuery;
  // Flexible content rows (i.e. RowBuilder rows)
  $(".acf-flexible-content .layout").addClass("-collapsed");

  // SEO & Social Metadata
  $("#acf-group_5a8d5300bdf11.postbox").addClass("closed");
})();
