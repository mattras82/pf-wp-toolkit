import $ from 'jquery';

$(function() {
  const selPrefix = 'pfwp-customize-accordion';
  const footerSel = '.' + selPrefix + '-footer';
  const headerSel = '.' + selPrefix + '-heading';
  const $headings = $(headerSel);

  if($headings.length > 0) {
    $headings.each(function() {
      const $this = $(this);

      $this.nextUntil(footerSel).addClass(selPrefix + '-item').addClass('pfwp-customize-control');
      $this.next().addClass(selPrefix + '-item-first');

      $this.on('click', function() {
          if($this.hasClass('active')) {
              $this.removeClass('active');
              $this.nextUntil(headerSel).removeClass('active');
          } else {
              $(headerSel + ',' + footerSel + ', .' + selPrefix + '-item').removeClass('active');
              $this.addClass('active');
              $this.nextUntil(headerSel).addClass('active');
          }
      });

      const $enable = $this.next().find('.switch');
      if($enable.length) {
        const $checkbox = $enable.find('input[type="checkbox"]');
        $this.find('.pfwp-accordion-heading').append($('<span/>', {
            'class' : 'pfwp-accordion-enable-flag ' + ($checkbox.is(':checked') ? 'enabled' : 'disabled')
          }));

          const $enableFlag = $this.find('.pfwp-accordion-enable-flag');
          $this.addClass($checkbox.is(':checked') ? 'enabled' : 'disabled');

          $checkbox.on('change', function() {
            if($checkbox.is(':checked')) {
              $this.addClass('enabled');
              $this.removeClass('disabled');

              $enableFlag.removeClass('disabled');
              $enableFlag.addClass('enabled');
            } else {
              $this.removeClass('enabled');
              $this.addClass('disabled');

              $enableFlag.removeClass('enabled');
              $enableFlag.addClass('disabled');
            }
          });
        }
    });
  }
});
