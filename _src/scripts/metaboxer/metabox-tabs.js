import $ from 'jquery';

function meta_tabs() {
  let $tabs = $('[data-pf-tabs]');
  if($tabs.length === 0)
    return false;

  // Watch all tabs-title anchors clicked
  $tabs.on('click', '.tabs-title a', function(e) {
    e.preventDefault();

    // The anchor clicked
    let $this = $(this);

    // If we clicked on an already active tab, we do nothing.
    // When we click on a non active tab, we can update all
    // tabs belonging to the same set. here we rely on the
    // id of the tab set.
    if(!$this.closest('.tabs-title').hasClass('is-active')) {
      let $set = $this.closest('[data-pf-tabs]');

      // Make all other tabs inactive by removing the class
      $set.find('.tabs-title').removeClass('is-active');
      $('[data-pf-tabs-content="' + $set.attr('id') + '"]' ).find('.tabs-content').removeClass('is-active');

      // Add the active class to the correct set
      $this.closest('.tabs-title').addClass('is-active');
      $($this.attr('href')).addClass('is-active');
    }
  });
}

$(document).ready(function() {
  meta_tabs();
});

export default meta_tabs;
