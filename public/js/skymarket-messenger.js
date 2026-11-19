(function () {
    'use strict';
    function initializeMessenger(root) {
    (root || document).querySelectorAll('[data-sm-chat]').forEach(function (chat) {
        if (chat.dataset.initialized) return;
        chat.dataset.initialized = '1';
        var log = chat.querySelector('[data-chat-messages]');
        var status = chat.querySelector('[data-chat-status]');
        var form = chat.querySelector('[data-chat-form]');
        var error = chat.querySelector('[data-chat-error]');
        var lastId = Number(chat.dataset.lastId || 0);
        var failures = 0, polling = false, stopped = false, sending = false;
        var timer, retryId, retryContent;
        var seen = new Set(Array.from(log.querySelectorAll('[data-message-id]'), function (node) { return Number(node.dataset.messageId); }));
        function append(item) {
            if (!item || !Number.isSafeInteger(item.id) || seen.has(item.id)) return;
            var nearBottom = log.scrollHeight - log.scrollTop - log.clientHeight < 100;
            var own = item.sender === chat.dataset.viewer;
            var article = document.createElement('article');
            article.className = 'sm-message' + (own ? ' sm-message--own' : '');
            article.dataset.messageId = String(item.id);
            article.dataset.sender = item.sender;
            var bubble = document.createElement('div');
            bubble.className = 'sm-message__bubble';
            var name = document.createElement('strong');
            name.textContent = own ? chat.dataset.you : chat.dataset.partner;
            var text = document.createElement('p');
            text.textContent = item.content;
            var footer = document.createElement('footer');
            var time = document.createElement('time');
            time.dateTime = item.created_at;
            time.textContent = new Date(item.created_at).toLocaleString([], {hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short'});
            footer.appendChild(time);
            if (own) {
                var receipt = document.createElement('span');
                receipt.dataset.readState = '';
                receipt.textContent = item.read_at ? 'Read' : 'Sent';
                footer.appendChild(receipt);
            }
            bubble.append(name, text, footer);
            article.appendChild(bubble);
            var following = Array.from(log.querySelectorAll('[data-message-id]')).find(function (node) {
                return Number(node.dataset.messageId) > item.id;
            });
            if (following) log.insertBefore(article, following);
            else log.appendChild(article);
            seen.add(item.id);
            // Only GET stream responses advance the cursor. A POST acknowledgement
            // arriving before another person's message must not skip that message.
            if (nearBottom || own) log.scrollTop = log.scrollHeight;
        }
        function schedule(delay) {
            clearTimeout(timer);
            if (!stopped && !document.hidden) timer = setTimeout(poll, delay);
        }
        async function poll() {
            if (polling || stopped || document.hidden) return;
            polling = true;
            var controller = new AbortController();
            var timeout = setTimeout(function () { controller.abort(); }, 15000);
            var more = false;
            try {
                var url = new URL(chat.dataset.stream, window.location.href);
                url.searchParams.set('after', String(lastId));
                var response = await fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin', cache: 'no-store', signal: controller.signal});
                if ([401, 403, 404].includes(response.status) || response.redirected) {
                    stopped = true;
                    throw new Error('Conversation unavailable. Please refresh and sign in again.');
                }
                if (!response.ok) throw new Error('Connection interrupted. Retrying…');
                var data = await response.json();
                data.messages.forEach(function (item) { append(item); lastId = Math.max(lastId, item.id); });
                log.querySelectorAll('[data-message-id][data-sender="' + chat.dataset.viewer + '"]').forEach(function (node) {
                    if (Number(node.dataset.messageId) <= data.last_read_id) {
                        var receipt = node.querySelector('[data-read-state]');
                        if (receipt) receipt.textContent = 'Read';
                    }
                });
                more = data.has_more;
                failures = 0;
                status.textContent = 'Live updates connected';
            } catch (exception) {
                failures++;
                status.textContent = stopped ? exception.message : 'Reconnecting… Your messages are saved.';
            } finally {
                clearTimeout(timeout);
                polling = false;
                schedule(more ? 100 : Math.min(3000 * Math.pow(2, failures), 30000));
            }
        }
        if (form) {
            var input = form.querySelector('textarea');
            var button = form.querySelector('button[type="submit"]');
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); form.requestSubmit(); }
            });
            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                var content = input.value.trim();
                if (sending || !content || stopped) return;
                sending = true;
                button.disabled = true;
                error.textContent = '';
                if (!retryId || retryContent !== content) { retryId = crypto.randomUUID(); retryContent = content; }
                var data = new FormData(form);
                data.set('content', content);
                data.set('client_id', retryId);
                var controller = new AbortController();
                var timeout = setTimeout(function () { controller.abort(); }, 20000);
                try {
                    var response = await fetch(form.action, {method: 'POST', body: data, credentials: 'same-origin', headers: {Accept: 'application/json'}, signal: controller.signal});
                    var result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Message could not be sent. Please retry.');
                    append(result.message);
                    if (input.value.trim() === content) input.value = '';
                    retryId = retryContent = null;
                    input.focus();
                    schedule(100);
                } catch (exception) {
                    error.textContent = exception.name === 'AbortError' ? 'Connection timed out. Retry to confirm delivery; the same message will not be sent twice.' : (exception.message || 'Message could not be sent. Please retry.');
                } finally {
                    clearTimeout(timeout);
                    sending = false;
                    button.disabled = false;
                }
            });
        }
        document.addEventListener('visibilitychange', function () { clearTimeout(timer); if (!document.hidden) poll(); });
        window.addEventListener('pagehide', function () { stopped = true; clearTimeout(timer); });
        window.addEventListener('pageshow', function (event) { if (event.persisted) { stopped = false; poll(); } });
        log.scrollTop = log.scrollHeight;
        poll();
    });
    }

    window.SkymarketInitializeMessenger = initializeMessenger;
    initializeMessenger(document);
})();
