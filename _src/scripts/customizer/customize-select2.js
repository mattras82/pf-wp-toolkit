import $ from 'jquery';

$(function() {
  let $select2 = $('select.select2');

  if ($select2.length > 0) {
    $select2.each(function() {
      $(this).select2({
        placeholder: 'Select one',
        width: '100%'
      });
    });
  }
});
