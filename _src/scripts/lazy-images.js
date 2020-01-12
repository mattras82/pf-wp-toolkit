const offset = 250;

let queue = [];
let eventOptions = false;

/**
 * Polyfills for missing functions on IE11
 */
function polyfills() {
  if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
      thisArg = thisArg || window;
      for (let i = 0; i < this.length; i++) {
        callback.call(thisArg, this[i], i, this);
      }
    };
  }
  let passiveSupported = false;
  try {
    let options = Object.defineProperty({}, "passive", {
      get: function() {
        passiveSupported = true;
      }
    });

    window.addEventListener("test", options, options);
    window.removeEventListener("test", options, options);
  } catch(err) {
    passiveSupported = false;
  }
  if (passiveSupported) {
    eventOptions = {passive: true};
  }
}

function addListener() {
  window.addEventListener('scroll', watchScroll, eventOptions);
  window.addEventListener('resize', watchScroll, eventOptions);
}

function checkListener() {
  if (queue.length === 0) {
    window.removeEventListener('scroll', watchScroll, eventOptions);
    window.removeEventListener('resize', watchScroll, eventOptions);
  }
}

function checkQueue($singleEl) {
  let length = queue.length;
  queue = queue.filter(function ($el) {
    if ($el === $singleEl || isInViewport($el)) {
      show($el);
      return false;
    }
    return true;
  });
  if ($singleEl && (queue.length === 0 || queue.length === length)) {
    show($singleEl);
  }
}

function watchScroll() {
  checkQueue();
  checkListener();
}

function getOffset() {
  return window.innerWidth > 800 ? offset * 2 : offset;
}

function isInViewport($el) {
  let rect = $el.getBoundingClientRect();
  let offset = getOffset();
  let elTop = rect.top;
  let elBottom = rect.bottom;

  return ((elBottom + offset >= 0 && elBottom <= window.innerHeight) || (elTop >= 0 && elTop - offset <= window.innerHeight )) && (rect.width > 0 && rect.height > 0);
}

function imageLoaded($el) {
  if ($el.getAttribute('width') != $el.naturalWidth
    && $el.naturalWidth > 0) {
    $el.setAttribute('width', $el.naturalWidth);
  }
  if ($el.getAttribute('height') != $el.naturalHeight
    && $el.naturalHeight > 0) {
    $el.setAttribute('height', $el.naturalHeight);
  }
  $el.classList.remove('loading');
  $el.classList.add('loaded');
  if ($el.dataset.src.endsWith('.svg')) {
    swapSVG($el);
  }
}

function show($el) {
  if ($el.dataset.srcset) {
    $el.setAttribute('srcset', $el.dataset.srcset);
  }
  if ($el.dataset.src) {
    $el.setAttribute('src', $el.dataset.src);
  }
  $el.classList.remove('queued');
  $el.classList.add('loading');
  if ($el.complete) {
    imageLoaded($el);
  } else {
    $el.addEventListener('load', function () {
      imageLoaded(this);
    }, {once: true});
  }
}

function swapSVG($el) {
  if (typeof SVGRect !== 'undefined' && typeof $el.replaceWith === 'function') {
    let div = document.createElement('div');
    let id = $el.id;
    let cls = $el.getAttribute('class');
    let src = $el.getAttribute('src');

    fetch(src).then(r => {
      if (r.status === 200) {
        r.text().then(txt => {
          div.innerHTML = txt;
          let $svg = div.querySelector('svg');

          if ($svg) {
            // we've got a valid SVG element. let's swap it

            if (id)
              $svg.id = id;

            if (cls)
              $svg.setAttribute('class', `${cls} replaced-svg`);

            // Remove any invalid XML tags as per https://validator.w3.org
            $svg.removeAttribute('xmlns:a');

            // Check if the viewport is set, if the viewport is not set the SVG wont't scale.
            if (!$svg.getAttribute('viewBox') && $svg.getAttribute('height') && $svg.getAttribute('width')) {
              $svg.setAttribute('viewBox', '0 0 ' + $svg.getAttribute('height') + ' ' + $svg.getAttribute('width'));
            }

            $el.replaceWith($svg);
          }
        }).catch(e => {
          console.error('Error in SVG swap', e);
        });
      }
    }).catch(e => {
      console.error('Error in SVG swap', e);
    });
  }
}

/**
 * Use this function to manually load an image element
 * @param $el
 */
function loadImage($el) {
  checkQueue($el);
}

function lazyload() {
  return Promise.resolve({
    then: f => {
      polyfills();
      document.querySelectorAll('img[data-src]').forEach(function ($this) {
        $this.classList.add('queued');
        if (isInViewport($this)) {
          show($this);
        } else {
          queue.push($this);
        }
      });
      if (queue.length > 0) {
        addListener();
      }
      f();
    }
  });
}

window.addEventListener('load', () => {lazyload().catch(e => { console.warn('Error in lazy images',e)})});