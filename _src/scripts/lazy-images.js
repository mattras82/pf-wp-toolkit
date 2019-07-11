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
  queue = queue.filter(function ($el) {
    if ($el === $singleEl || isInViewport($el)) {
      show($el);
      return false;
    }
    return true;
  });
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

function show($el) {
  if ($el.dataset.srcset) {
    $el.setAttribute('srcset', $el.dataset.srcset);
  }
  $el.setAttribute('src', $el.dataset.src);
  $el.classList.remove('queued');
  $el.classList.add('loading');
  if ($el.complete) {
    $el.classList.remove('loading');
    $el.classList.add('loaded');
  } else {
    $el.addEventListener('load', function () {
      this.classList.remove('loading');
      this.classList.add('loaded');
    }, {once: true});
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
}

window.addEventListener('load', lazyload);
