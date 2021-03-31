import $ from 'jquery';

function addTinyMCEListeners($editors) {
  if (tinyMCE.get($editors[0].id)) {
    $editors.each(function () {
      let $textarea  = $( this );
      let id         = $textarea.attr( 'id' );
      let editor     = tinyMCE.get( id );

      if ( editor ) {
        editor.on('keyup input change', function() {
          editor.save();
          $textarea.trigger('change');
        });
      }
    });
  } else {
    setTimeout(function () {
      addTinyMCEListeners($editors);
    }, 300);
  }
}

$(function () {
  let $editors = $('textarea.wp-editor-area');
  if ($editors.length && typeof tinyMCE !== 'undefined') {
    addTinyMCEListeners($editors);
  }
});
