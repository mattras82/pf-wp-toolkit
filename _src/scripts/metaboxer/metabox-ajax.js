import $ from 'jquery';
import meta_tabs from './metabox-tabs';
import meta_wysiwyg from './metabox-wysiwyg';
import meta_upload from './metabox-upload';

function refresh(container) {
  let metakey = container.find('.pf-metabox').data('pf-metakey');
  let action = `${metakey}_refresh`;
  if (document.body.classList.contains('block-editor-page')) {
    let formData = [
      { name: 'post_ID', value: document.querySelector('[name=post_ID]').value }
    ];
    let formFields = document.querySelectorAll(`[name^="${metakey}"]`);
    // Manually serialize the metabox fields we're updating
    formFields.forEach($f => {
      if (['checkbox', 'radio'].indexOf($f.type) > -1 && !$f.checked) return;
      formData.push({
        name: $f.getAttribute('name'),
        value: $f.value
      });
    });
    // Save all of the WYSIWYG IDs so we can re-init them later
    let wysiwygIDs = getWysiwygIDs(container);
    refreshMetabox(container, action, formData, function () {
      wysiwygIDs.forEach(id => reInitWysiwyg(id));
      return true;
    });
  } else {
    let formData = $('form#post').serializeArray();
    // Save all of the WYSIWYG IDs so we can re-init them later
    let wysiwygIDs = getWysiwygIDs(container);
    refreshMetabox(container, action, formData, function () {
      wysiwygIDs.forEach(id => reInitWysiwyg(id));
      return true;
    });
  }
}

function getWysiwygIDs(container) {
  let ids = [];
  container.find('.field-label-wysiwyg').each(function () {
    if (typeof tinymce === 'undefined') return;
    let editor = tinymce.editors[this.getAttribute('for')];
    // Remove the editor instance so there's no conflicts later
    if (typeof editor !== 'undefined') editor.remove();
    ids.push(this.getAttribute('for'));
  });
  return ids;
}

//Re-initialize a wp_editor instance based on the given id.
//This code was copied from the class-wp-editor.php file from WP Core
function reInitWysiwyg(id, count = 0) {
  let init = tinyMCEPreInit.mceInit[id];
  let $wrap = tinymce.$('#wp-' + id + '-wrap');
  if ($wrap.length !== 1 && count < 5) {
    // The $wrap element is initialized by some async WP core code.
    // We'll wait a bit and try to run this again to give time for that markup to get initialized
    setTimeout(() => {
      reInitWysiwyg(id, count + 1)
    }, 500);
  }

  if (($wrap.hasClass('tmce-active') || !tinyMCEPreInit.qtInit.hasOwnProperty(id)) && !init.wp_skip_init) {
    tinymce.init(init);
    if (typeof quicktags === 'function') quicktags(id);
  }
}

function refreshMetabox(container, action, formData, callback = null) {
  container.slideToggle('fast', function () {
    $(this).html('<span class="spinner is-active" style="float: none"></span>').slideToggle('fast');
  });
  $.ajax
    ({
      type: "POST",
      url: ajaxurl,
      data: { 'action': action, 'form': formData },
      success: function (data) {
        container.slideToggle('fast', function () {
          if (callback && typeof callback === 'function') {
            let cb = callback();
            if (!cb) return cb;
          }
          $(this).html(data).slideToggle(450, function () {
            $(this).removeAttr('style');
          });
          meta_tabs();
          meta_wysiwyg();
          meta_upload();
          addListeners();
        });
      },
      error: function (data, status) {
        console.log(data);
        console.log(status);
      }
    });
}

//Adds refresh event listeners to applicable fields
function addListeners() {
  $('[id*="pf-metabox"] [data-refresh-on]').each(function () {
    let action = $(this).data('refresh-on');
    let container = $(this).parentsUntil('.inside').parent('.inside');
    $(this).on(action, function () {
      refresh(container);
    });
  });
}

$(document).ready(function () {
  //Adds a panel to a gallery type in the metabox
  $('[id*="pf-metabox"]').on('click', '[data-gallery-id-add]', function () {
    let id = $(this).data('gallery-id-add');
    let num = $('#' + id).val();
    let max = this.dataset.galleryMax || 10;
    if (num == max) {
      alert('You have reached the maximum number of items.');
      return false;
    }
    num++;
    $('#' + id).val(num);
    let container = $(this).parentsUntil('.inside').parent('.inside');
    refresh(container);
  });

  //Removes a panel from a gallery and resets the indices of all remaining panels
  $('[id*="pf-metabox"]').on('click', '[data-gallery-id-remove]', function () {
    if (!confirm('Removing this panel will delete the data inside, which will not be recoverable. Are you sure?')) return false;
    let toRemove = parseInt($(this).data('remove-num'));
    let id = $(this).data('gallery-id-remove');
    let num = $('#' + id).val();
    num--;
    $('#' + id).val(num);
    let container = $(this).parentsUntil('.inside').parent('.inside');
    container.find('.tabs-content').each(function () {
      let thisNum = parseInt($(this).find('[data-remove-num]').data('remove-num'));
      if (thisNum <= toRemove) {
        return true;
      } else if (thisNum > toRemove) {
        $(this).find('[name]').each(function () {
          let name = $(this).attr('name');
          $(this).attr('name', name.replace(`_${thisNum}`, `_${thisNum - 1}`));
        });
      } else {
        $(this).remove();
      }
    });
    refresh(container);
  });

  addListeners();
});
