@if ($customer?->is_vendor && $customer?->has_verified_badge)
    <span
        class="vendor-verified-badge"
        title="{{ __('Verified vendor') }}"
        aria-label="{{ __('Verified vendor') }}"
    >✓</span>
@endif
