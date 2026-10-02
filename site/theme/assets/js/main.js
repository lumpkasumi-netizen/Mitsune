(function () {
  'use strict';

  function copyToClipboard(text, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function () { showCopied(btn); });
    } else {
      var ta = document.createElement('textarea');
      ta.value = text;
      ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      showCopied(btn);
    }
  }

  function showCopied(btn) {
    var orig = btn.textContent;
    btn.textContent = '✓';
    btn.classList.add('copied');
    setTimeout(function () {
      btn.textContent = orig;
      btn.classList.remove('copied');
    }, 1500);
  }

  function initCopyButtons() {
    // .prompt-entry-en にコピーボタンを追加
    document.querySelectorAll('.prompt-entry-en').forEach(function (el) {
      if (el.querySelector('.copy-btn')) return;
      var btn = document.createElement('button');
      btn.className = 'copy-btn';
      btn.textContent = 'コピー';
      btn.setAttribute('aria-label', 'プロンプトをコピー');
      btn.addEventListener('click', function () {
        copyToClipboard(el.dataset.prompt || el.textContent.trim(), btn);
      });
      el.appendChild(btn);
    });

    // pre.prompt-block にもコピーボタンを追加
    document.querySelectorAll('pre.prompt-block').forEach(function (el) {
      if (el.querySelector('.copy-btn')) return;
      var btn = document.createElement('button');
      btn.className = 'copy-btn';
      btn.textContent = 'コピー';
      btn.addEventListener('click', function () {
        copyToClipboard(el.innerText.replace(/コピー|✓/g, '').trim(), btn);
      });
      el.appendChild(btn);
    });
  }

  document.addEventListener('DOMContentLoaded', initCopyButtons);
})();
/* mitsune-accessible-mobile-navigation:start */
(function () {
  'use strict';
  function initMitsuneNavigation() {
    var toggle = document.querySelector('.mitsune-menu-toggle');
    if (!toggle) return;
    var navigation = document.getElementById(toggle.getAttribute('aria-controls'));
    if (!navigation) return;
    function setOpen(open, restoreFocus) {
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      navigation.classList.toggle('is-open', open);
      if (!open && restoreFocus) toggle.focus();
    }
    toggle.addEventListener('click', function () {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true', false);
    });
    navigation.addEventListener('click', function (event) {
      if (event.target.closest('a')) setOpen(false, false);
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false, true);
      }
    });
    document.addEventListener('click', function (event) {
      if (toggle.getAttribute('aria-expanded') === 'true' &&
          !navigation.contains(event.target) && !toggle.contains(event.target)) {
        setOpen(false, false);
      }
    });
    var desktop = window.matchMedia('(min-width: 960px)');
    var closeForDesktop = function (event) { if (event.matches) setOpen(false, false); };
    if (desktop.addEventListener) desktop.addEventListener('change', closeForDesktop);
    else if (desktop.addListener) desktop.addListener(closeForDesktop);
    // Add the enhancement class only after the control is fully wired.  If
    // JavaScript fails or is disabled, the navigation remains visible.
    toggle.hidden = false;
    document.documentElement.classList.add('mitsune-nav-js');
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMitsuneNavigation);
  } else {
    initMitsuneNavigation();
  }
}());
/* mitsune-accessible-mobile-navigation:end */
