@php
    /** @var array $resolution */
@endphp

@if (! empty($resolution['eligible']))
    <span class="razorpay-easy-emi-catalog-badge" style="display: inline-flex; vertical-align: middle; margin: 3px 4px 3px 0;">
        @if (! empty($resolution['custom_badge']))
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10.5px; font-weight: 600; padding: 3px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;">
                <i class="ti ti-discount-2"></i> {{ $resolution['custom_badge'] }}
            </span>
        @elseif (($resolution['scheme'] ?? '') === 'no_cost')
            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10.5px; font-weight: 600; padding: 3px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;">
                <i class="ti ti-bolt"></i> {{ $resolution['badge'] ?? trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.no_cost_emi') }}
            </span>
        @elseif (($resolution['scheme'] ?? '') === 'low_cost')
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 10.5px; font-weight: 600; padding: 3px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;">
                <i class="ti ti-trending-down"></i> {{ $resolution['badge'] ?? trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.low_cost_emi') }}
            </span>
        @else
            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle" style="font-size: 10.5px; font-weight: 600; padding: 3px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px; white-space: nowrap;">
                <i class="ti ti-credit-card"></i> {{ $resolution['badge'] ?? trans('plugins/razorpay-easy-emi::razorpay-easy-emi.badges.standard_emi') }}
            </span>
        @endif
    </span>
@endif
