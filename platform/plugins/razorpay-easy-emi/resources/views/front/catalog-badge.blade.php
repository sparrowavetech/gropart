@php
    /** @var array $resolution */
    $scheme = $resolution['scheme'] ?? 'standard';
    $customBadge = trim((string) ($resolution['custom_badge'] ?? ''));

    // On thumbnail catalog cards, badge text must be compact and punchy (never clipped with ellipsis)
    if ($customBadge !== '' && mb_strlen($customBadge) <= 15) {
        $badgeLabel = $customBadge;
        $badgeClass = 'rzp-badge-custom';
    } elseif ($scheme === 'no_cost') {
        $badgeLabel = trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.no_cost_emi');
        $badgeClass = 'rzp-badge-no-cost';
    } elseif ($scheme === 'low_cost') {
        $badgeLabel = trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.low_cost_emi');
        $badgeClass = 'rzp-badge-low-cost';
    } else {
        $badgeLabel = trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.standard_emi');
        $badgeClass = 'rzp-badge-standard';
    }
@endphp

@if (! empty($resolution['eligible']))
    <div class="razorpay-easy-emi-catalog-badge rzp-catalog-badge-{{ $scheme }}"
         data-product-id="{{ $resolution['product_id'] ?? '' }}"
         data-scheme="{{ $scheme }}"
         title="{{ $badgeLabel }}">
        <span class="rzp-badge-pill {{ $badgeClass }} rzp-shimmer">
            @if ($scheme === 'no_cost')
                <svg viewBox="0 0 16 18" fill="none" class="rzp-badge-icon">
                    <path d="M9.5 0L1 9.5H6.5L4 18L15 7H9.5L12 0H9.5Z" fill="#ffffff"/>
                </svg>
            @elseif ($scheme === 'low_cost')
                <svg viewBox="0 0 24 24" fill="none" class="rzp-badge-icon" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                    <polyline points="17 18 23 18 23 12"></polyline>
                </svg>
            @elseif (! empty($customBadge) && mb_strlen($customBadge) <= 15)
                <svg viewBox="0 0 24 24" fill="none" class="rzp-badge-icon" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            @else
                <svg viewBox="0 0 24 24" fill="none" class="rzp-badge-icon" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            @endif
            <span class="rzp-badge-text">{{ $badgeLabel }}</span>
        </span>
    </div>
@endif
