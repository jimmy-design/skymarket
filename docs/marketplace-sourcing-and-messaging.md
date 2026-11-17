# SkyMarket retail, wholesale and messaging

The homepage now provides product/category discovery, supplier profiles and
retail/wholesale entry points. The existing retail product pages, cart and
checkout remain available. Data comes from the real catalogue and approved
supplier records; verification badges use existing account verification.

## Buying and requesting quotes

- Open `/sourcing`, search by keyword or filter by category.
- Open a product to buy normally, or choose **Request quote** / **Chat now**.
- Sign in as a buyer. Submit a quantity, optional target budget, delivery
  location and requirements. A private conversation opens with the product's
  actual supplier. A buyer cannot substitute a different supplier/customer ID
  or enquire about their own store.
- Requests are enquiries, not orders, payments, reserved inventory or promises
  of a bulk discount. Suppliers confirm terms in the conversation. No
  automatic tier pricing, binding quotations or escrow are claimed.
- Products without supplier accounts continue through the retail product
  page; they do not show unsupported chat/quote buttons.

## Messaging

Buyers use Account → Messages; sellers use Vendor dashboard → Messages.
Messages and quotes use the existing `mp_messages` conversations and replies.
No database migration, Composer dependency or third-party chat service is
needed. The existing marketplace messaging setting must be enabled.

Open conversations refresh about every three seconds while visible. Replies
send without a reload. Delivery checks back off during connection failures,
and hidden tabs stop polling. The storefront's unread badge refreshes about
every 15 seconds. The buyer's latest inbox and vendor's message table also
refresh about every 10 seconds while visible, so new enquiries appear
without a manual refresh. Read receipts reflect messages opened by the
other party; the interface does not claim that a supplier is online or
currently typing.

Sending a reply uses a client UUID and a short-lived server cache lock so a
lost network response can be retried without creating a duplicate reply.
Polling cursors advance only through streamed messages, preventing a POST
acknowledgement from skipping a simultaneous incoming reply. Backlogs are
fetched in pages of 100. Replies are scoped to their parent conversation.

Authentication, conversation ownership, CSRF and request throttling apply to
the endpoints. User message content is escaped in Blade and rendered through
`textContent` in the browser. Guest enquiries retain the existing email-only
reply path; sign-in is required for the new live chat and quote workflow.

## Deployment and validation

Push to `main` to use the configured cPanel deployment. The change preserves
the live database, existing conversations and uploaded files. New public
assets are under `public/css/` and `public/js/`, which the deployer maps to
`public_html/`. The deployer invalidates Laravel's route/config caches.

Checks:

```text
php vendor/phpunit/phpunit/phpunit --filter MarketplaceConversationTest
python tests/Browser/messenger_smoke.py
```

PHP tests use an isolated in-memory SQLite database. The browser test uses a
stub API and checks retry safety, simultaneous replies, read receipts,
escaping and background pause/resume; it sends no real customer messages.
The browser test requires Playwright and a local Chrome installation.

The storefront has also been checked locally at desktop/mobile widths for
search, empty results and the guest sign-in requirement for enquiries.
Before relying on messaging at high traffic, monitor database/cache load and
consider WebSocket infrastructure. The current implementation uses polling
to work on this cPanel account without shell or a persistent worker.
