/**=====================
  Range-slider js Start
==========================**/
(function ($) {
  "use strict";

  var range_slider_custom = {
    init: function () {
        var min = $('#price-range').data("min");
        var max = $('#price-range').data("max");
        var symbol = $('#price-range').data("symbol");
        var position = $('#price-range').data("position");
        $("#price-range").ionRangeSlider($.extend({
          type: "double",
          skin: "round",
          min: min,
          max: max,
          from: min,
          to: max,
          hide_min_max: true, // show/hide MIN and MAX labels
          decorate_both: true,
        }, position === "right" ? { postfix: " " + symbol } : { prefix: symbol }));
    },
  };

  $("#distance-slider").ionRangeSlider({
    grid: true,
    skin: "modern",
    from: 3,
    hide_min_max: true, // show/hide MIN and MAX labels
    values: [
      "10 km",
      "20 km",
      "30 km",
      "40 km",
      "50 km",
      "60 km",
    ],
  });

  range_slider_custom.init();
})(jQuery);

/**=====================
  Range-slider Ends
==========================**/
