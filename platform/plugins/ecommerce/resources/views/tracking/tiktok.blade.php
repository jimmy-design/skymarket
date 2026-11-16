<script type="application/json" id="skymarket-tiktok-config">{!! json_encode(['pixelId' => $pixelId, 'events' => $events], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
<div id="skymarket-ad-consent" hidden role="region" aria-label="{{ __('Advertising preferences') }}" style="position:fixed;bottom:16px;left:16px;right:16px;z-index:1000000;background:#fff;color:#222;padding:18px;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 20px #0002;max-width:580px">
    <p>{{ __('May SkyMarket share your product browsing and shopping activity with TikTok to measure ads and show relevant products? Optional advertising tracking starts only if you allow it.') }}</p>
    <button type="button" data-skymarket-ads="allow">{{ __('Allow advertising') }}</button>
    <button type="button" data-skymarket-ads="deny">{{ __('Decline') }}</button>
</div>
<button type="button" id="skymarket-ad-preferences" style="position:fixed;bottom:0;right:8px;z-index:99999;font-size:12px">{{ __('Ad preferences') }}</button>
<script src="{{ asset('js/skymarket-tiktok.js') }}?v=1" defer></script>
