import $ from 'jquery';

$(function() {
  $('form#post').on('submit', function(e){
    let valid = true;
    $(this).find('input[required], select[required], textarea[required]').each(function() {
      if ($(this).val() === '' || !$(this).val()) {
        valid = false;
        $(this).addClass('validation-error');
        $(this).on('change', function() {
         if ($(this).val() && $(this).val() !== '') {
           $(this).removeClass('validation-error');
         }
        });
      }
    });
    if (!valid) {
      e.preventDefault();
      alert('Please fill in all required fields');
    }
  });
});
