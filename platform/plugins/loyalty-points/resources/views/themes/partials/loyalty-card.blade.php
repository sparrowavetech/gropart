@php
    $memberSince = $customer->created_at?->translatedFormat('M Y') ?? '-';
    $memberId = str_pad($customer->id, 9, '0', STR_PAD_LEFT);
@endphp

<div class="bb-customer-card loyalty-card-container mb-4">
    <div class="bb-customer-card-header">
        <h5 class="bb-customer-card-title">
            <x-core::icon name="ti ti-id-badge-2" class="me-2" />
            {{ trans('plugins/loyalty-points::loyalty-points.card.title') }}
        </h5>
    </div>
    <div class="bb-customer-card-body">
        <div class="loyalty-card">
            {{-- Card Header --}}
            <div class="loyalty-card-header">
                <div class="loyalty-card-brand">
                    @if($logo = theme_option('logo'))
                        <img src="{{ RvMedia::getImageUrl($logo) }}"
                             alt="{{ theme_option('site_title') }}"
                             class="loyalty-card-logo">
                    @else
                        <span class="loyalty-card-brand-text">{{ theme_option('site_title') }}</span>
                    @endif
                </div>
                <div class="loyalty-card-tier d-flex align-items-center gap-2">
                    @if($balance->level)
                        @if($balance->level->badge)
                            <img src="{{ RvMedia::getImageUrl($balance->level->badge) }}" alt="{{ $balance->level->name }}" class="loyalty-tier-badge-img" style="width: 32px; height: 32px; object-fit: contain;">
                        @endif
                        <span class="badge bg-warning text-dark">
                            @if(!$balance->level->badge)
                                <x-core::icon name="ti ti-crown" class="me-1" />
                            @endif
                            {{ $balance->level->name }}
                        </span>
                    @else
                        <span class="badge bg-secondary">
                            {{ trans('plugins/loyalty-points::loyalty-points.levels.default_member') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body --}}
            <div class="loyalty-card-body">
                <div class="row g-3 align-items-center">
                    {{-- QR Code Section --}}
                    <div class="col-md-4 text-center">
                        <div class="qr-code-wrapper">
                            {!! $qrCodeSvg !!}
                        </div>
                        <small class="text-muted d-block mt-2">
                            {{ trans('plugins/loyalty-points::loyalty-points.card.scan_qr') }}
                        </small>
                        <a href="{{ route('customer.loyalty-points.card.download') }}" class="small">
                            <x-core::icon name="ti ti-download" style="font-size: 12px;" />
                            {{ trans('plugins/loyalty-points::loyalty-points.card.download') }}
                        </a>
                    </div>

                    {{-- Info Section --}}
                    <div class="col-md-8">
                        <div class="loyalty-card-info">
                            <h4 class="loyalty-card-name mb-1 d-flex align-items-center gap-2">
                                {{ $customer->name }}
                                @if($balance->level && $balance->level->badge)
                                    <img src="{{ RvMedia::getImageUrl($balance->level->badge) }}" alt="{{ $balance->level->name }}" class="loyalty-name-badge" style="width: 24px; height: 24px; object-fit: contain;" title="{{ $balance->level->name }}">
                                @endif
                            </h4>
                            <p class="loyalty-card-member-since text-muted small mb-3">
                                {{ trans('plugins/loyalty-points::loyalty-points.card.member_since', ['date' => $memberSince]) }}
                            </p>

                            <div class="loyalty-card-points d-flex gap-4 mb-3">
                                <div>
                                    <span class="points-label text-muted small d-block">
                                        {{ trans('plugins/loyalty-points::loyalty-points.points.current_balance') }}
                                    </span>
                                    <span class="points-value fw-bold fs-4">
                                        {{ number_format($balance->total_points) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="points-label text-muted small d-block">
                                        {{ trans('plugins/loyalty-points::loyalty-points.points.lifetime') }}
                                    </span>
                                    <span class="points-value fw-bold fs-4 text-muted">
                                        {{ number_format($balance->lifetime_points) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Member ID --}}
                            <div class="member-id">
                                <small class="text-muted">
                                    {{ trans('plugins/loyalty-points::loyalty-points.card.member_id') }}:
                                    <code>L{{ $memberId }}</code>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="loyalty-card-footer">
                <small class="text-muted">
                    <x-core::icon name="ti ti-info-circle" class="me-1" />
                    {{ trans('plugins/loyalty-points::loyalty-points.card.description') }}
                </small>
            </div>
        </div>
    </div>
</div>
