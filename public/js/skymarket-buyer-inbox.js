(function () {
    'use strict';
    var inbox = document.querySelector('[data-sm-buyer-inbox]');
    if (!inbox || inbox.dataset.initialized) return;
    inbox.dataset.initialized = '1';
    var timer, busy = false, stopped = false;
    async function refresh() {
        if (busy || stopped || document.hidden) return;
        busy = true;
        var controller = new AbortController();
        var timeout = setTimeout(function () { controller.abort(); }, 12000);
        try {
            var response = await fetch(inbox.dataset.url, {headers: {Accept: 'application/json'}, credentials: 'same-origin', cache: 'no-store', signal: controller.signal});
            if ([401, 403, 404].includes(response.status) || response.redirected) { stopped = true; return; }
            if (!response.ok) return;
            var data = await response.json();
            if (!Array.isArray(data.conversations)) return;
            var fragment = document.createDocumentFragment();
            if (!data.conversations.length) {
                var empty = document.createElement('div');
                empty.className = 'text-center text-muted py-5';
                empty.textContent = 'You have no conversations yet.';
                fragment.appendChild(empty);
            }
            data.conversations.forEach(function (item) {
                var url = new URL(item.url, window.location.href);
                if (url.origin !== window.location.origin) return;
                var link = document.createElement('a');
                link.href = url.href;
                link.className = 'd-flex gap-3 align-items-start py-3 border-bottom text-decoration-none text-dark';
                var body = document.createElement('div');
                body.className = 'flex-grow-1';
                var heading = document.createElement('div');
                heading.className = 'd-flex justify-content-between gap-3';
                var title = document.createElement('strong'); title.textContent = item.supplier;
                var time = document.createElement('small'); time.className = 'text-muted'; time.textContent = new Date(item.updated_at).toLocaleString();
                heading.append(title, time);
                var preview = document.createElement('div'); preview.className = 'text-muted mt-1' + (item.unread ? ' fw-bold' : ''); preview.textContent = item.preview;
                body.append(heading, preview); link.appendChild(body);
                if (item.unread) { var badge = document.createElement('span'); badge.className = 'badge bg-danger rounded-pill'; badge.textContent = 'New'; link.appendChild(badge); }
                fragment.appendChild(link);
            });
            // Avoid replacing a link while it has keyboard focus.
            if (!inbox.contains(document.activeElement)) inbox.replaceChildren(fragment);
        } catch (error) { /* Preserve the last working inbox when offline. */ }
        finally { clearTimeout(timeout); busy = false; clearTimeout(timer); if (!stopped && !document.hidden) timer = setTimeout(refresh, 10000); }
    }
    document.addEventListener('visibilitychange', function () { clearTimeout(timer); if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', function () { stopped = true; clearTimeout(timer); });
    window.addEventListener('pageshow', function (event) { if (event.persisted) { stopped = false; refresh(); } });
    refresh();
})();
