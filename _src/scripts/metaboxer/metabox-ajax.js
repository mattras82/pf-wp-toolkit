import $ from "jquery";
import meta_tabs from "./metabox-tabs";
import meta_wysiwyg from "./metabox-wysiwyg";
import { reInitWysiwyg, getWysiwygIDs } from "./metabox-wysiwyg";
import meta_upload from "./metabox-upload";

function refresh(container) {
  let box = container.find(".pf-metabox");
  let metakey = box.data("pf-metakey");
  let action = `${metakey}_refresh`;
  let formData = null;
  if (document.body.classList.contains("block-editor-page")) {
    formData = [
      {
        name: "post_ID",
        value: document.querySelector("[name=post_ID]").value,
      },
    ];
    let formFields = document.querySelectorAll(`[name^="${metakey}"]`);
    // Manually serialize the metabox fields we're updating
    formFields.forEach(($f) => {
      if (["checkbox", "radio"].indexOf($f.type) > -1 && !$f.checked) return;
      formData.push({
        name: $f.getAttribute("name"),
        value: $f.value,
      });
    });
  } else if (box.hasClass("pf-metabox--term")) {
    formData = $("form#edittag").serializeArray();
  } else {
    formData = $("form#post").serializeArray();
  }
  // Save all of the WYSIWYG IDs so we can re-init them later
  let wysiwygIDs = getWysiwygIDs(container, true);
  refreshMetabox(container, action, formData, function () {
    if (wysiwygIDs.length) {
      wysiwygIDs.forEach((id) => reInitWysiwyg(id));
      let newIDs = getWysiwygIDs(container).filter(
        (id) => wysiwygIDs.indexOf(id) === -1
      );
      if (newIDs.length) {
        newIDs.forEach((id) => {
          // We've got a new WYSIWYG editor. It won't have a PreInit object, so we'll grab an existing one and initialize it with that
          let init = tinyMCEPreInit.mceInit[wysiwygIDs[0]];
          init.selector = `#${id}`;
          reInitWysiwyg(id, 0, init);
        });
      }
    }
    return true;
  });
}

function refreshMetabox(container, action, formData, callback = null) {
  container.slideToggle("fast", function () {
    $(this)
      .html(
        '<span class="spinner is-active" style="float: none"></span><p>Saving...</p>'
      )
      .slideToggle("fast");
  });
  $.ajax({
    type: "POST",
    url: ajaxurl,
    data: { action: action, form: formData },
    success: function (data) {
      container.slideToggle("fast", function () {
        $(this)
          .html(data)
          .slideToggle(450, function () {
            $(this).removeAttr("style");
          });
        meta_tabs();
        meta_wysiwyg();
        meta_upload();
        addListeners();
        container.dispatchEvent(
          new CustomEvent("pf-metabox-refresh", { bubbles: true })
        );
        if (callback && typeof callback === "function") {
          return callback();
        }
      });
    },
    error: function (data, status) {
      console.log(data);
      console.log(status);
    },
  });
}

//Adds refresh event listeners to applicable fields
function addListeners() {
  $('[id*="pf-metabox"] [data-refresh-on]').each(function () {
    let action = $(this).data("refresh-on");
    let container = $(this).parentsUntil(".inside").parent(".inside");
    $(this).on(action, function () {
      refresh(container);
    });
  });
}

$(function () {
  //Adds a panel to a gallery type in the metabox
  $('[id*="pf-metabox"]').on("click", "[data-gallery-id-add]", function () {
    let id = $(this).data("gallery-id-add");
    let num = $("#" + id).val();
    let max = this.dataset.galleryMax || 10;
    if (num == max) {
      alert("You have reached the maximum number of items.");
      return false;
    }
    num++;
    $("#" + id).val(num);
    let container = $(this).parentsUntil(".inside").parent(".inside");
    refresh(container);
  });

  //Removes a panel from a gallery and resets the indices of all remaining panels
  $('[id*="pf-metabox"]').on("click", "[data-gallery-id-remove]", function () {
    if (
      !confirm(
        "Removing this panel will delete the data inside, which will not be recoverable. Are you sure?"
      )
    )
      return false;
    let toRemove = parseInt($(this).data("remove-num"));
    let id = $(this).data("gallery-id-remove");
    let num = $("#" + id).val();
    num--;
    $("#" + id).val(num);
    let container = $(this).parentsUntil(".inside").parent(".inside");
    container.find(".tabs-content").each(function () {
      let thisNum = parseInt(
        $(this).find("[data-remove-num]").data("remove-num")
      );
      if (thisNum <= toRemove) {
        return true;
      } else if (thisNum > toRemove) {
        $(this)
          .find("[name]")
          .each(function () {
            let name = $(this).attr("name");
            $(this).attr(
              "name",
              name.replace(`_${thisNum}`, `_${thisNum - 1}`)
            );
          });
      } else {
        $(this).remove();
      }
    });
    refresh(container);
  });

  addListeners();
});
