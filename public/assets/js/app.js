// UniVerse shared UI behaviour: navigation, reading progress and copy helpers.
(function () {
  'use strict';

  var header = document.querySelector('[data-site-header]');
  var navToggle = document.querySelector('[data-nav-toggle]');
  var nav = document.querySelector('[data-nav]');

  function closeNav() {
    if (!nav || !navToggle) return;
    nav.classList.remove('is-open');
    navToggle.setAttribute('aria-expanded', 'false');
  }

  if (navToggle && nav) {
    navToggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
      if (!nav.classList.contains('is-open')) return;
      if (nav.contains(event.target) || navToggle.contains(event.target)) return;
      closeNav();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeNav();
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 860) closeNav();
    });
  }

  function updateHeader() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 8);
  }

  updateHeader();
  window.addEventListener('scroll', updateHeader, { passive: true });

  // Reading progress for knowledge articles.
  var progress = document.querySelector('[data-reading-progress]');
  var article = document.querySelector('[data-article]');

  if (progress && article) {
    progress.hidden = false;
    var ticking = false;

    var renderProgress = function () {
      ticking = false;
      var rect = article.getBoundingClientRect();
      var total = rect.height - window.innerHeight;
      var scrolled = -rect.top;
      var ratio = total > 0 ? scrolled / total : 0;
      ratio = Math.max(0, Math.min(1, ratio));
      progress.style.width = (ratio * 100).toFixed(2) + '%';
    };

    var requestProgress = function () {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(renderProgress);
    };

    renderProgress();
    window.addEventListener('scroll', requestProgress, { passive: true });
    window.addEventListener('resize', requestProgress);
  }

  async function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return;
    }

    var area = document.createElement('textarea');
    area.value = text;
    area.setAttribute('readonly', '');
    area.style.position = 'fixed';
    area.style.opacity = '0';
    document.body.appendChild(area);
    area.select();
    document.execCommand('copy');
    document.body.removeChild(area);
  }

  function flashLabel(button, label) {
    if (!button.dataset.originalHtml) {
      button.dataset.originalHtml = button.innerHTML;
    }
    button.textContent = label;
    window.setTimeout(function () {
      button.innerHTML = button.dataset.originalHtml;
    }, 1800);
  }

  document.querySelectorAll('[data-copy-link]').forEach(function (button) {
    button.addEventListener('click', async function () {
      try {
        await copyText(window.location.href);
        flashLabel(button, 'Link copied');
      } catch (error) {
        flashLabel(button, 'Copy failed');
      }
    });
  });

  document.querySelectorAll('[data-copy-citation]').forEach(function (button) {
    button.addEventListener('click', async function () {
      var source = document.querySelector('[data-citation]');
      if (!source) return;
      try {
        await copyText(source.textContent.trim());
        flashLabel(button, 'Citation copied');
      } catch (error) {
        flashLabel(button, 'Copy failed');
      }
    });
  });

  // Reset tool forms back to their initial state and clear the shared URL.
  document.querySelectorAll('form[data-tool]').forEach(function (form) {
    form.addEventListener('reset', function () {
      window.setTimeout(function () {
        var url = new URL(window.location.href);
        url.search = '';
        window.history.replaceState({}, '', url);
        form.dispatchEvent(new Event('input', { bubbles: true }));
      }, 0);
    });
  });
})();
