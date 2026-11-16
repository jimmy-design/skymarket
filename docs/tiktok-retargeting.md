# SkyMarket TikTok retargeting

The integration is off by default and requires no migration, Composer install or asset build.

## Activate later

1. Deploy the changed `platform/` files and `public/js/skymarket-tiktok.js`.
   On cPanel, `platform/` lives in `skymarket-app/platform/`; the JavaScript
   lives in `public_html/js/`. Preserve the live `.env` and bootstrap files.
   If `skymarket-app/bootstrap/cache/routes-v7.php` exists, delete that route
   cache file so Laravel registers the new catalogue route.
2. In Admin → Ecommerce → Settings → Tracking, enter the TikTok Pixel ID,
   enable TikTok Pixel and save. An enabled configuration requires a valid ID.
3. Add the feed URL `https://skymarket.co.ke/feeds/tiktok-products.csv` to
   TikTok Catalog Manager, schedule refreshes and link the catalogue to the
   same Pixel. Feed `sku_id` and event `content_id` use the parent product's
   numeric database ID, including when a variation is purchased.
4. Validate with TikTok Pixel Helper and Events Manager Test Events after
   allowing advertising. Create the paid retargeting campaign in Ads Manager.
   Availability of catalogue campaigns depends on your TikTok account/market.

No TikTok script loads before advertising consent. Visitors can decline,
withdraw through “Ad preferences”, and change their choice later. Global
Privacy Control blocks tracking. Choices persist when local storage is
available; otherwise they last for the page. Update the site's privacy
notice before activation to explain TikTok advertising data sharing.

## Events

- Page view on consented page loads.
- `ViewContent` on a product detail page.
- `Search` on submitted product searches (not each autocomplete keystroke).
- `AddToCart` only after a successful server response; requested quantity and
  actual cart unit price are used, rather than the accumulated cart quantity.
- `InitiateCheckout` when checkout renders, using actual cart contents.
- `Purchase` on the thank-you page only for completed payments, including
  marketplace orders. Pending COD/bank transfers and failed payments are
  excluded. Order-based event IDs and browser deduplication reduce refresh
  duplicates. Purchases paid later without a return visit need Events API.

Prices use the store's base currency because cart/order amounts are stored
in that currency. The public feed includes published parent products with
images, current default-variation prices, availability and brand (or
“Unbranded”). Condition is `new`; review before advertising used goods.
Review image formats/resolutions and catalogue diagnostics in TikTok. Drafts,
private customer data and order records are not included in the feed.

The browser integration does not send email addresses or phone numbers.
Manual advanced matching and server-side Events API are not implemented in
this change: they need a separate access token, consent handling and server
deduplication. Do not place an Events API token in browser code or GitHub.
Disable Automatic Advanced Matching in TikTok unless you have separately
reviewed and disclosed that additional data collection.

## Checks before enabling

Verify no request to `analytics.tiktok.com` before consent or after a decline.
Allow advertising, verify one page view and the correct product ID/value,
then test failed/successful cart additions, checkout and a paid test order.
Refresh the thank-you page and confirm no duplicate Purchase. Withdraw
consent and verify events stop. Confirm feed IDs match tracked products.
Ad blockers and unavailable browser storage can affect delivery/deduplication.

References:
- https://ads.tiktok.com/resources/help/article/standard-events-parameters
- https://business-api.tiktok.com/portal/docs/pixel-cookie-consent-mode/v1.3
- https://ads.tiktok.com/resources/help/article/catalog-product-parameters
