(function () {
    'use strict';
    var badge = document.querySelector('[data-sm-unread]');
    if (!badge || badge.dataset.initialized) return;
    badge.dataset.initialized = '1';
    var timer, busy = false, stopped = false;
    async function refresh() {
        if (busy || stopped || document.hidden) return;
        busy = true;
        var controller = new AbortController();
        var timeout = setTimeout(function () { controller.abort(); }, 12000);
        try {
            var response = await fetch(badge.dataset.url, {headers: {Accept: 'application/json'}, credentials: 'same-origin', cache: 'no-store', signal: controller.signal});
            if ([401, 403, 404].includes(response.status) || response.redirected) { stopped = true; return; }
            if (!response.ok) return;
            var data = await response.json();
            if (!Number.isInteger(data.unread) || data.unread < 0) return;
            badge.textContent = data.unread > 99 ? '99+' : String(data.unread);
            badge.style.display = data.unread ? '' : 'none';
            badge.setAttribute('aria-label', String(data.unread) + ' unread messages');
        } catch (error) { /* Keep the existing count when offline. */ }
        finally {
            clearTimeout(timeout);
            busy = false;
            clearTimeout(timer);
            if (!stopped && !document.hidden) timer = setTimeout(refresh, 15000);
        }
    }
    document.addEventListener('visibilitychange', function () { clearTimeout(timer); if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', function () { stopped = true; clearTimeout(timer); });
    window.addEventListener('pageshow', function (event) { if (event.persisted) { stopped = false; refresh(); } });
    refresh();
})();
