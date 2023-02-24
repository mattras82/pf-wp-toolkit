import $ from 'jquery';

let sortableFix = false;

function getWysiwygIDs(container, remove = false) {
  let ids = [];
  container.find('.field-label-wysiwyg').each(function () {
    if (typeof tinymce === 'undefined') return;
    let id = this.getAttribute('for');
    let editor = tinymce.editors[id];
    // Remove the editor instance so there are no conflicts later
    if (remove && typeof editor !== 'undefined') editor.remove();
    ids.push(id);
  });
  return ids;
}

//Re-initialize a wp_editor instance based on the given id.
//This code was copied from the class-wp-editor.php file from WP Core
function reInitWysiwyg(id, count = 0, customInit = null) {
  let init = customInit || tinyMCEPreInit.mceInit[id];
  let $wrap = tinymce.$('#wp-' + id + '-wrap');
  if (($wrap.length !== 1 || !$wrap.hasClass('tmce-active')) && count < 5) {
    // The $wrap element is initialized by some async WP core code.
    // We'll wait a bit and try to run this again to give time for that markup to get initialized
    setTimeout(() => {
      reInitWysiwyg(id, count + 1)
    }, 500);
  }

  if (($wrap.hasClass('tmce-active') || !tinyMCEPreInit.qtInit.hasOwnProperty(id)) && init && !init.wp_skip_init) {
    tinymce.init(init);
    if (typeof quicktags === 'function') quicktags(id);
  }
}

function reInitAll($container = null) {
  $container = $container || $(this);
  let ids = getWysiwygIDs($container, true);
  ids.forEach(id => {
    reInitWysiwyg(id);
  });
}

//Disable the drag & drop feature of the metabox because the tinyMCE iFrame doesn't like it
function handleSort() {
  let $sortables = $('.meta-box-sortables');
  if (!sortableFix && $sortables.length) {
    $sortables.on('sortstop', function() {
      reInitAll($(this));
    });
    $('.postbox .handle-order-higher, .postbox .handle-order-lower').on('click.postboxes', function () {
      setTimeout(function() {
        $sortables.each(function() {
          reInitAll($(this));
        });
      }, 500);
    });
    sortableFix = true;
  }
}

function meta_wysiwyg() {
  $('div[id$="-pf-metabox"]').each(function () {
    let wysiwygs = $(this).find('div.field-wysiwyg');
    if (wysiwygs.length > 0) {
      handleSort();
      $('html').addClass('wysiwyg-fix');
    }
  });
}

$(function () {
  meta_wysiwyg();
});

export { reInitWysiwyg, getWysiwygIDs };

export default meta_wysiwyg;
