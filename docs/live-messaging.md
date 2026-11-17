# SkyMarket live messaging

The original storefront layout is retained. The Alibaba-style homepage,
sourcing catalogue and bulk quote workflow have been removed.

Buyers can use **Chat with seller** on a supplier's product page while signed
in, or open an existing conversation under Account → Messages. Sellers use
Vendor dashboard → Messages. The existing store contact form also remains.
Starting a chat reopens the existing buyer/seller thread when one exists.

Open conversations update about every three seconds while visible. Buyer
and vendor inboxes refresh about every ten seconds, and the storefront
unread badge about every fifteen seconds. Read receipts, safe network
retries and automatic reconnection are retained. Background tabs pause
polling. The UI does not claim that a seller is currently online or typing.

Authentication, ownership checks, CSRF, throttling and escaped message text
apply. Conversations use the existing database schema; no migration is
required. Existing messages, including earlier quote enquiries, are kept.

Checks: `php vendor/phpunit/phpunit/phpunit --filter MarketplaceConversationTest`
and `python tests/Browser/messenger_smoke.py` (Playwright and Chrome required).
