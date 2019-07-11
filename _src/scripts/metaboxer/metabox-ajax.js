import $ from 'jquery';
import meta_tabs from './metabox-tabs';
import meta_wysiwyg from './metabox-wysiwyg';
import meta_upload from './metabox-upload';

function refresh(container) {
  let formData = $('form#post').serializeArray();
  let action = container.find('.pf-metabox').data('pf-metakey') + '_refresh';
  if (document.body.classList.contains('block-editor-page')) {
    $('.editor-post-publish-button').click();
    alert('Your changes are being saved. Please refresh this page to view them.');
  } else if (container.find('.field-wysiwyg').length > 0) {
    $('input#publish').click();
  } else {
    container.slideToggle('fast', function() {
      $(this).html('<span class="spinner is-active" style="float: none"></span>').slideToggle('fast');
    });
    $.ajax
    ({
      type: "POST",
      url: ajaxurl,
      data: {'action' : action, 'form' : formData},
      success: function (data){
        container.slideToggle('fast', function(){
          $(this).html(data).slideToggle(450, function() {
            $(this).removeAttr('style');
          });
          meta_tabs();
          meta_wysiwyg();
          meta_upload();
          addListeners();
        });
      },
      error: function(data, status) {
        console.log(data);
        console.log(status);
      }
    });
  }
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

$(document).ready(function() {
  //Adds a panel to a gallery type in the metabox
  $('[id*="pf-metabox"]').on('click', '[data-gallery-id-add]', function() {
    let id = $(this).data('gallery-id-add');
    let num = $('#'+id).val();
    if (num == 9) {
      alert('You have reached the maximum number of panels.');
      return false;
    }
    num++;
    $('#'+id).val(num);
    let container= $(this).parentsUntil('.inside').parent('.inside');
    refresh(container);
  });

  //Removes a panel from a gallery and resets the indices of all remaining panels
  $('[id*="pf-metabox"]').on('click', '[data-gallery-id-remove]', function () {
    if (!confirm('Removing this panel will delete the data inside, which will not be recoverable. Are you sure?')) return false;
    let toRemove = $(this).data('remove-num');
    let id = $(this).data('gallery-id-remove');
    let num = $('#'+id).val();
    num--;
    $('#'+id).val(num);
    let container= $(this).parentsUntil('.inside').parent('.inside');
    container.find('.tabs-content').each(function () {
      let thisId = $(this).attr('id');
      let thisNum = thisId.substring(thisId.length - 1);
      if (thisNum <= toRemove) {
        return true;
      } else if (thisNum > toRemove) {
        $(this).find('[name]').each(function() {
          let name = $(this).attr('name');
          $(this).attr('name', name.replace(thisNum, thisNum-1));
        });
      } else {
        $(this).remove();
      }
    });
    refresh(container);
  });

  addListeners();
});
