const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('public/js/skymarket-tiktok.js', 'utf8');

function browser({choice, events = [], gpc = false, pixelId = 'TESTPIXEL123', storage = new Map()} = {}) {
    const scripts = [];
    const listeners = {};
    const element = () => ({hidden: true, addEventListener(name, fn) { this[name] = fn; }});
    const banner = element();
    const preferences = element();
    const allow = Object.assign(element(), {dataset: {skymarketAds: 'allow'}});
    const deny = Object.assign(element(), {dataset: {skymarketAds: 'deny'}});
    let ajax;
    if (choice) storage.set('skymarket.advertising-consent.v1', choice);
    const window = {
        localStorage: {getItem: key => storage.get(key) || null, setItem: (key, value) => storage.set(key, value)},
        addEventListener: (key, fn) => { listeners[key] = fn; },
        jQuery: () => ({ajaxSuccess(fn) { ajax = fn; }}),
    };
    const elements = {
        'skymarket-tiktok-config': {textContent: JSON.stringify({pixelId, events})},
        'skymarket-ad-consent': banner,
        'skymarket-ad-preferences': preferences,
    };
    const document = {
        getElementById: id => elements[id],
        querySelectorAll: () => [allow, deny],
        createElement: () => ({}),
        head: {appendChild: script => scripts.push(script)},
    };
    vm.runInNewContext(source, {window, document, navigator: {globalPrivacyControl: gpc}, Set, Date, JSON});
    return {window, scripts, banner, preferences, allow: () => allow.click(), deny: () => deny.click(),
        ajax: response => ajax(null, {responseJSON: response}), listeners,
        commands: name => (window.ttq || []).filter(command => command[0] === name)};
}

test('missing Pixel ID never loads the SDK', () => {
    const b = browser({pixelId: '', choice: 'allow'});
    assert.equal(b.scripts.length, 0);
});
test('unknown or declined consent never contacts TikTok', () => {
    const b = browser();
    assert.equal(b.banner.hidden, false);
    assert.equal(b.scripts.length, 0);
    b.deny();
    assert.equal(b.scripts.length, 0);
    assert.equal(b.banner.hidden, true);
});
test('consent loads once and sends the current product view', () => {
    const b = browser({events: [{event: 'ViewContent', properties: {contents: [{content_id: '42'}]}}]});
    b.allow();
    b.allow();
    assert.equal(b.scripts.length, 1);
    assert.equal(b.commands('page').length, 1);
    assert.equal(b.commands('track').length, 1);
    assert.equal(b.commands('track')[0][2].contents[0].content_id, '42');
});
test('cart events require both a successful server response and consent', () => {
    const b = browser();
    const event = {event: 'AddToCart', properties: {value: 200}};
    b.ajax({error: false, data: {tiktok_event: event}});
    assert.equal(b.commands('track').length, 0);
    b.allow();
    b.ajax({error: true, data: {tiktok_event: event}});
    assert.equal(b.commands('track').length, 0);
    b.ajax({error: false, data: {tiktok_event: event}});
    assert.equal(b.commands('track').length, 1);
});
test('withdrawal blocks events and preferences can reopen', () => {
    const b = browser({choice: 'allow'});
    b.deny();
    b.window.skymarketTikTok.track({event: 'Search', properties: {query: 'phone'}});
    assert.equal(b.commands('track').length, 0);
    assert.equal(b.commands('revokeConsent').length, 1);
    b.preferences.click();
    assert.equal(b.banner.hidden, false);
    b.allow();
    b.window.skymarketTikTok.track({event: 'Search', properties: {query: 'phone'}});
    assert.equal(b.commands('track').length, 1);
});
test('Global Privacy Control overrides a saved allow choice', () => {
    const b = browser({choice: 'allow', gpc: true});
    b.allow();
    assert.equal(b.scripts.length, 0);
});
test('purchase IDs deduplicate on the current page and after a reload', () => {
    const event = {event: 'Purchase', properties: {value: 100}, event_id: 'order-7'};
    const storage = new Map();
    const b = browser({choice: 'allow', events: [event, event], storage});
    assert.equal(b.commands('track').length, 1);
    const reload = browser({events: [event], storage});
    assert.equal(reload.commands('track').length, 0);
});
test('withdrawal in another tab blocks subsequent events', () => {
    const b = browser({choice: 'allow'});
    b.listeners.storage({key: 'skymarket.advertising-consent.v1', newValue: 'deny'});
    b.window.skymarketTikTok.track({event: 'ViewContent', properties: {}});
    assert.equal(b.commands('track').length, 0);
});
