import $ from 'jquery';

function meta_wysiwyg() {
  $('div[id$="-pf-metabox"]').each(function(){
    let wysiwygs = $(this).find('div.field-wysiwyg');
    if (wysiwygs.length > 0) {
      $(this).find('h2').removeAttr('class'); //Disable the drag & drop feature of the metabox because the tinyMCE iFrame doesn't like it
    }
  });
}

$(document).ready(function() {
  meta_wysiwyg();
});

export default meta_wysiwyg;
