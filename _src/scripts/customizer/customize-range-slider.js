import $ from 'jquery';

$(document).ready(function() {
  let $sliders = $('.pfwp-customize-range-slider');

  if ($sliders.length > 0) {
    $sliders.each(function () {
      let $slider = $(this);
      let $range = $slider.find('input[type="range"]');
      let $text = $slider.find('input[type="number "]');

      $range.on('change', function () {
        $text.val($range.val());
      });

      $text.on('keyup', function () {
        $range.val($text.val());
      });
    });
  }
});
