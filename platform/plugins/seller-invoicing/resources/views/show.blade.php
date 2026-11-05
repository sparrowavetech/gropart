@extends($vendor ? MarketplaceHelper::viewPath('vendor-dashboard.layouts.master') : BaseHelper::getAdminMasterLayoutTemplate())

@if (! $vendor)
    @push('header-action')
        <x-core::button
            tag="a"
            href="{{ $document->download_url }}"
            color="primary"
            icon="ti ti-download"
        >
            {{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}
        </x-core::button>
        <x-core::button
            tag="a"
            href="{{ $document->print_url }}"
            target="_blank"
            icon="ti ti-printer"
        >
            {{ trans('plugins/seller-invoicing::seller-invoicing.print') }}
        </x-core::button>
        <x-core::button
            tag="a"
            href="{{ route('seller-invoicing.index') }}"
            icon="ti ti-arrow-left"
        >
            {{ trans('plugins/seller-invoicing::seller-invoicing.back_to_library') }}
        </x-core::button>
    @endpush
@endif

@section('content')
    @php
        $logo = get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option('logo_in_invoices') ?: Theme::getLogo());

        $issuer = $document->snapshot?->issuer ?? [];
        $issuerState = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveState($issuer['state'] ?? get_ecommerce_setting('company_state_for_invoicing', get_ecommerce_setting('store_state')));
        $issuerCountry = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveCountry($issuer['country'] ?? get_ecommerce_setting('company_country_for_invoicing', get_ecommerce_setting('store_country')));
        $issuerCity = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveCity($issuer['city'] ?? get_ecommerce_setting('company_city_for_invoicing', get_ecommerce_setting('store_city')));

        $recipient = $document->snapshot?->recipient ?? [];
        $recipientState = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveState($recipient['state'] ?? ($document->order?->store?->state));
        $recipientCountry = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveCountry($recipient['country'] ?? ($document->order?->store?->country));
        $recipientCity = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveCity($recipient['city'] ?? ($document->order?->store?->city_name ?: $document->order?->store?->city));
        $placeOfSupply = \SparroWave\SellerInvoicing\Supports\LocationResolver::resolveState($document->taxes['place_of_supply'] ?? $recipientState);
    @endphp

    <div class="row">
        <div class="col-12">
            @if (session('success_msg'))
                <div class="alert alert-success mb-3">{{ session('success_msg') }}</div>
            @endif
            @if (session('error_msg'))
                <div class="alert alert-danger mb-3">{{ session('error_msg') }}</div>
            @endif

            <x-core::card>
                <x-core::card.body class="p-4 p-md-5">
                    @if ($vendor)
                        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
                            <a href="{{ $document->vendor_download_url }}" class="btn btn-primary">
                                <x-core::icon name="ti ti-download" /> {{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}
                            </a>
                            <a href="{{ $document->vendor_print_url }}" target="_blank" class="btn btn-outline-secondary">
                                <x-core::icon name="ti ti-printer" /> {{ trans('plugins/seller-invoicing::seller-invoicing.print') }}
                            </a>
                            <a href="{{ route('marketplace.vendor.seller-invoicing.index') }}" class="btn btn-outline-dark">
                                <x-core::icon name="ti ti-arrow-left" /> {{ trans('plugins/seller-invoicing::seller-invoicing.back_to_library') }}
                            </a>
                        </div>
                    @endif

                    <!-- Invoice Header -->
                    <div class="row align-items-center mb-4">
                        <div class="col-md-7">
                            <h2 class="mb-1 text-primary fw-bold">
                                <x-core::icon name="ti ti-file-invoice" /> {{ trans('plugins/seller-invoicing::seller-invoicing.invoice') }} #{{ $document->number }}
                            </h2>
                            <div class="text-muted fs-5 mt-2">
                                @php
                                    $orderCode = $document->order?->code ?: ('#' . $document->order_id);
                                    $displayOrderCode = '#' . ltrim($orderCode, '#');
                                    $orderUrl = ! empty($vendor)
                                        ? route('marketplace.vendor.orders.edit', $document->order_id)
                                        : route('orders.edit', $document->order_id);
                                @endphp
                                <span>{{ trans('plugins/seller-invoicing::seller-invoicing.order') }}: 
                                    <a href="{{ $orderUrl }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                        {{ $displayOrderCode }} <x-core::icon name="ti ti-external-link" size="xs" />
                                    </a>
                                </span>
                                @if ($document->order?->created_at)
                                    <span class="mx-2">•</span>
                                    <span>{{ trans('plugins/seller-invoicing::seller-invoicing.order_date') }}: <strong>{{ $document->order->created_at->translatedFormat('M d, Y') }}</strong></span>
                                @endif
                                <span class="mx-2">•</span>
                                <span>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_date') }}: <strong>{{ $document->issued_at?->translatedFormat('M d, Y') ?: $document->created_at->translatedFormat('M d, Y') }}</strong></span>
                                <span class="mx-2">•</span>
                                <span class="badge bg-success-lt text-success border border-success">{{ ucfirst($document->status) }}</span>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            @if ($logo)
                                <img src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_title', 'Gropart') }}" style="max-height: 55px; max-width: 250px; width: auto !important; height: auto !important; object-fit: contain; display: inline-block;" />
                            @else
                                <h3 class="mb-0 text-primary">{{ theme_option('site_title', 'Gropart') }}</h3>
                            @endif
                        </div>
                    </div>

                    <div class="hr my-4"></div>

                    <!-- 2-Column Parties Section -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-body-tertiary">
                                <h5 class="text-uppercase text-muted fw-bold mb-2">
                                    {{ trans('plugins/seller-invoicing::seller-invoicing.service_provider') }}
                                </h5>
                                <h4 class="text-primary mb-1">{{ $issuer['name'] ?? get_ecommerce_setting('company_name_for_invoicing', get_ecommerce_setting('store_name')) }}</h4>
                                <p class="mb-1 text-muted">{{ $issuer['address'] ?? get_ecommerce_setting('company_address_for_invoicing', get_ecommerce_setting('store_address')) }}</p>
                                <p class="mb-1 text-muted">
                                    @if ($issuerCity){{ $issuerCity }}, @endif
                                    @if ($issuerState){{ $issuerState }}, @endif
                                    {{ $issuerCountry }}
                                </p>
                                <div class="mt-2">
                                    <strong>{{ $document->taxes['tax_id_label'] ?? 'GSTIN' }}:</strong>
                                    <span>{{ $issuer['tax_id'] ?? get_ecommerce_setting('company_tax_id_for_invoicing', get_ecommerce_setting('store_vat_number', '-')) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-body-tertiary">
                                <h5 class="text-uppercase text-muted fw-bold mb-2">
                                    {{ trans('plugins/seller-invoicing::seller-invoicing.seller_recipient') }}
                                </h5>
                                <h4 class="text-primary mb-1">{{ $recipient['name'] ?? $document->order?->store?->name }}</h4>
                                <p class="mb-1 text-muted">{{ $recipient['address'] ?? $document->order?->store?->address }}</p>
                                <p class="mb-1 text-muted">
                                    @if ($recipientCity){{ $recipientCity }}, @endif
                                    @if ($recipientState){{ $recipientState }}, @endif
                                    {{ $recipientCountry }}
                                </p>
                                <div class="mt-2">
                                    <strong>{{ $document->taxes['tax_id_label'] ?? 'GSTIN' }}:</strong>
                                    <span>{{ $recipient['tax_id'] ?? ($document->order?->store?->tax_id ?: '-') }}</span>
                                </div>
                                <div class="mt-2">
                                    <strong>{{ trans('plugins/seller-invoicing::seller-invoicing.place_of_supply') }}:</strong>
                                    <span class="badge bg-secondary text-white">{{ $placeOfSupply ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <h5 class="text-uppercase text-muted fw-bold mb-3">
                        {{ trans('plugins/seller-invoicing::seller-invoicing.invoice_items') }}
                    </h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-vcenter table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th>{{ trans('plugins/seller-invoicing::seller-invoicing.service_description') }}</th>
                                    <th class="text-center" style="width: 120px;">{{ trans('plugins/seller-invoicing::seller-invoicing.sac_code') }}</th>
                                    <th class="text-end" style="width: 160px;">{{ trans('plugins/seller-invoicing::seller-invoicing.taxable_value') }} ({{ $document->currency }})</th>
                                    <th class="text-center" style="width: 90px;">{{ trans('plugins/seller-invoicing::seller-invoicing.tax_rate') }}</th>
                                    @if ($document->taxes['is_india'] ?? true)
                                        @if ($document->taxes['is_igst'] ?? false)
                                            <th class="text-end" style="width: 140px;">IGST ({{ $document->currency }})</th>
                                        @else
                                            <th class="text-end" style="width: 120px;">CGST ({{ $document->currency }})</th>
                                            <th class="text-end" style="width: 120px;">SGST ({{ $document->currency }})</th>
                                        @endif
                                    @else
                                        <th class="text-end" style="width: 140px;">{{ $document->taxes['tax_label'] ?? 'Tax' }} ({{ $document->currency }})</th>
                                    @endif
                                    <th class="text-end" style="width: 160px;">{{ trans('plugins/seller-invoicing::seller-invoicing.total') }} ({{ $document->currency }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($document->lines as $index => $line)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $line['name'] }}</strong></td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $line['sac_code'] }}</span></td>
                                        <td class="text-end">{{ format_price($line['taxable_amount']) }}</td>
                                        <td class="text-center">{{ $line['tax_rate'] }}%</td>
                                        @if ($document->taxes['is_india'] ?? true)
                                            @if ($document->taxes['is_igst'] ?? false)
                                                <td class="text-end">{{ format_price($line['igst_amount']) }}</td>
                                            @else
                                                <td class="text-end">{{ format_price($line['cgst_amount']) }}</td>
                                                <td class="text-end">{{ format_price($line['sgst_amount']) }}</td>
                                            @endif
                                        @else
                                            <td class="text-end">{{ format_price($line['tax_amount']) }}</td>
                                        @endif
                                        <td class="text-end fw-bold">{{ format_price($line['total_amount']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals and Signatory Section -->
                    <div class="row align-items-end mt-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3 bg-body-tertiary rounded border">
                                <div class="text-muted small mb-1">
                                    <strong>{{ trans('plugins/seller-invoicing::seller-invoicing.tax_type') }}:</strong>
                                    {{ $document->taxes['rate_label'] ?? '-' }}
                                </div>
                                <div class="text-muted small">
                                    <strong>{{ trans('plugins/seller-invoicing::seller-invoicing.place_of_supply') }}:</strong>
                                    {{ $placeOfSupply ?: '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-body-tertiary">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ trans('plugins/seller-invoicing::seller-invoicing.subtotal') }}:</span>
                                    <strong>{{ format_price($document->subtotal) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ trans('plugins/seller-invoicing::seller-invoicing.tax') }}:</span>
                                    <strong>{{ format_price($document->tax_amount) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2">
                                    <span class="fs-3 fw-bold">{{ trans('plugins/seller-invoicing::seller-invoicing.grand_total') }}:</span>
                                    <strong class="fs-3 text-primary">{{ format_price($document->grand_total) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection
