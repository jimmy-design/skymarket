(function () {
    'use strict';

    if (window.skymarketTikTok || !document.getElementById('skymarket-tiktok-config')) return;
    var config = JSON.parse(document.getElementById('skymarket-tiktok-config').textContent);
    if (!/^[A-Za-z0-9_-]{5,64}$/.test(config.pixelId || '')) return;

    var storageKey = 'skymarket.advertising-consent.v1';
    var banner = document.getElementById('skymarket-ad-consent');
    var pending = config.events || [];
    var allowed = false;
    var loaded = false;
    var sent = new Set();

    function stored(key) {
        try { return window.localStorage.getItem(key); } catch (e) { return null; }
    }
    function store(key, value) {
        try { window.localStorage.setItem(key, value); } catch (e) { /* Session-only choice. */ }
    }
    function loadPixel() {
        if (loaded) return;
        loaded = true;
        window.TiktokAnalyticsObject = 'ttq';
        var ttq = window.ttq = window.ttq || [];
        ttq.methods = ['page', 'track', 'identify', 'instances', 'debug', 'on', 'off', 'once', 'ready', 'alias', 'group', 'enableCookie', 'disableCookie', 'holdConsent', 'revokeConsent', 'grantConsent'];
        ttq.setAndDefer = function (target, method) {
            target[method] = function () { target.push([method].concat(Array.prototype.slice.call(arguments))); };
        };
        ttq.methods.forEach(function (method) { ttq.setAndDefer(ttq, method); });
        ttq.instance = function (id) {
            var instance = ttq._i[id] || [];
            ttq.methods.forEach(function (method) { ttq.setAndDefer(instance, method); });
            return instance;
        };
        ttq.load = function (id, options) {
            var url = 'https://analytics.tiktok.com/i18n/pixel/events.js';
            ttq._i = ttq._i || {};
            ttq._i[id] = [];
            ttq._i[id]._u = url;
            ttq._t = ttq._t || {};
            ttq._t[id] = +new Date();
            ttq._o = ttq._o || {};
            ttq._o[id] = options || {};
            var script = document.createElement('script');
            script.async = true;
            script.src = url + '?sdkid=' + encodeURIComponent(id) + '&lib=ttq';
            document.head.appendChild(script);
        };
        ttq.grantConsent();
        ttq.load(config.pixelId);
        ttq.page();
    }
    function track(event) {
        if (!allowed || !event || !['ViewContent', 'Search', 'AddToCart', 'InitiateCheckout', 'Purchase'].includes(event.event)) return;
        var id = event.event_id;
        var dedupKey = id ? 'skymarket.tiktok.' + config.pixelId + '.' + id : null;
        if (id && (sent.has(id) || stored(dedupKey))) return;
        if (id) {
            window.ttq.track(event.event, event.properties, {event_id: id});
            sent.add(id);
            store(dedupKey, '1');
        } else {
            window.ttq.track(event.event, event.properties);
        }
    }
    function choose(value, persist) {
        allowed = value === 'allow' && navigator.globalPrivacyControl !== true;
        if (persist) store(storageKey, allowed ? 'allow' : 'deny');
        banner.hidden = true;
        if (allowed) {
            loadPixel();
            window.ttq.grantConsent();
            window.ttq.enableCookie();
            pending.splice(0).forEach(track);
        } else {
            pending.length = 0;
            if (loaded) {
                window.ttq.revokeConsent();
                window.ttq.disableCookie();
            }
        }
    }
    document.querySelectorAll('[data-skymarket-ads]').forEach(function (button) {
        button.addEventListener('click', function () { choose(button.dataset.skymarketAds, true); });
    });
    document.getElementById('skymarket-ad-preferences').addEventListener('click', function () { banner.hidden = false; });
    window.addEventListener('storage', function (event) {
        if (event.key === storageKey) choose(event.newValue, false);
    });
    window.skymarketTikTok = {track: track};
    if (window.jQuery) {
        window.jQuery(document).ajaxSuccess(function (_event, xhr) {
            var response = xhr.responseJSON;
            if (response && !response.error && response.data && response.data.tiktok_event) {
                track(response.data.tiktok_event);
            }
        });
    }
    var choice = stored(storageKey);
    if (navigator.globalPrivacyControl === true) choose('deny', false);
    else if (choice === 'allow' || choice === 'deny') choose(choice, false);
    else banner.hidden = false;
})();
