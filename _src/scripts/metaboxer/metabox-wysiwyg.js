import $ from 'jquery';

let sortableFix = false;

//Disable the drag & drop feature of the metabox because the tinyMCE iFrame doesn't like it
function removeSort(e) {
  let sortables = $('.meta-box-sortables');
  if (sortables.length) {
    sortables.sortable({
      cancel: '.no-sort'
    });
    sortableFix = true;
  }
}

function meta_wysiwyg() {
  $('div[id$="-pf-metabox"]').each(function(){
    let wysiwygs = $(this).find('div.field-wysiwyg');
    if (wysiwygs.length > 0) {
      removeSort();
      $(this).find('h2').addClass('no-sort');
      $('html').addClass('wysiwyg-fix');
    }
  });
}

$(document).ready(function() {
  meta_wysiwyg();
});

export default meta_wysiwyg;
