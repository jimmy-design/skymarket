"""Browser checks for delivery, retry safety, cursors, escaping and read receipts.

Run: python tests/Browser/messenger_smoke.py (requires Playwright and Chrome).
The API fixture is local to the browser; no customer messages are sent.
"""
import json
import re
from pathlib import Path

from playwright.sync_api import sync_playwright

ROOT = Path(__file__).resolve().parents[2]
HTML = '''<!doctype html><html><body>
<section data-sm-chat data-stream="https://chat.test/stream" data-viewer="customer" data-partner="Supplier" data-you="You" data-last-id="1">
<span data-chat-status></span><div data-chat-messages style="height:300px;overflow:auto"><article data-message-id="1" data-sender="customer"><div class="sm-message__bubble"><p>Initial enquiry</p><span data-read-state>Sent</span></div></article></div>
<form data-chat-form action="https://chat.test/reply" method="post"><textarea name="content"></textarea><button type="submit">Send</button><p data-chat-error></p></form>
</section></body></html>'''

with sync_playwright() as playwright:
    browser = playwright.chromium.launch(executable_path='C:/Program Files/Google/Chrome/Application/chrome.exe', headless=True)
    page = browser.new_page()
    state = {'requests': 0, 'attempts': 0, 'uuids': [], 'messages': [], 'after': []}
    date = '2026-11-17T12:00:00+03:00'

    def respond(route):
        url = route.request.url
        if '/stream' in url:
            after = int(re.search(r'after=(\d+)', url).group(1))
            state['after'].append(after)
            state['requests'] += 1
            route.fulfill(content_type='application/json', body=json.dumps({
                'messages': [item for item in state['messages'] if item['id'] > after],
                'last_read_id': 3 if state['messages'] else 0, 'has_more': False,
            }))
        elif '/reply' in url:
            body = route.request.post_data
            match = re.search(r'name="client_id"\r\n\r\n([^\r]+)', body)
            state['uuids'].append(match.group(1))
            state['attempts'] += 1
            if not state['messages']:
                state['messages'] = [
                    {'id': 2, 'sender': 'vendor', 'content': '<img src=x onerror="window.xss=true">', 'created_at': date, 'read_at': None},
                    {'id': 3, 'sender': 'customer', 'content': 'Can you supply 20 units?', 'created_at': date, 'read_at': None},
                ]
            if state['attempts'] == 1:
                # The server saved the message but its response was lost.
                route.abort('failed')
            else:
                route.fulfill(content_type='application/json', body=json.dumps({'message': state['messages'][1]}))
        else:
            route.fulfill(content_type='text/html', body=HTML)

    page.route('https://chat.test/**', respond)
    page.goto('https://chat.test/')
    page.add_script_tag(content=(ROOT / 'public/js/skymarket-messenger.js').read_text(encoding='utf-8'))
    page.wait_for_function("document.querySelector('[data-chat-status]').textContent === 'Live updates connected'")
    page.locator('textarea').fill('Can you supply 20 units?')
    page.get_by_role('button', name='Send', exact=True).click()
    page.wait_for_function("document.querySelector('[data-chat-error]').textContent.length > 0")
    assert page.locator('textarea').input_value() == 'Can you supply 20 units?'
    page.get_by_role('button', name='Send', exact=True).click()
    page.wait_for_function("document.querySelector('textarea').value === ''")
    page.wait_for_selector('[data-message-id="2"]')
    assert state['uuids'][0] == state['uuids'][1], 'Retry must reuse the idempotency key'
    assert page.locator('[data-message-id="3"]').count() == 1, 'POST and stream must not duplicate messages'
    assert page.locator('[data-message-id]').evaluate_all('(nodes) => nodes.map(node => Number(node.dataset.messageId))') == [1, 2, 3], 'Simultaneous messages must stay in conversation order'
    assert page.locator('[data-message-id="2"] img').count() == 0, 'Messages must render as text'
    assert page.locator('[data-message-id="2"] p').inner_text().startswith('<img')
    assert page.evaluate('window.xss !== true')
    page.wait_for_function("document.querySelector('[data-message-id=\"3\"] [data-read-state]').textContent === 'Read'")
    page.evaluate("Object.defineProperty(document,'hidden',{configurable:true,get:()=>true});document.dispatchEvent(new Event('visibilitychange'))")
    page.wait_for_timeout(100)
    before = state['requests']
    page.wait_for_timeout(3300)
    assert state['requests'] == before, 'Hidden tabs must stop polling'
    page.evaluate("Object.defineProperty(document,'hidden',{configurable:true,get:()=>false});document.dispatchEvent(new Event('visibilitychange'))")
    page.wait_for_timeout(300)
    assert state['requests'] > before
    assert 3 in state['after'], 'Stream cursor must eventually advance after all messages are seen'
    print('PASS: live delivery, lost-response retry, duplicate protection, simultaneous incoming messages, text escaping, read receipts, background pause/resume')
    browser.close()
