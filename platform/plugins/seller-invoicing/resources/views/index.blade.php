@extends($vendor ? MarketplaceHelper::viewPath('vendor-dashboard.layouts.master') : BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <x-core::icon name="ti ti-file-invoice" /> {{ trans('plugins/seller-invoicing::seller-invoicing.library') }}
            </h3>
            @if (! $vendor && (auth()->user()?->hasPermission('seller-invoicing.settings') || auth()->user()?->hasPermission('seller-invoicing.manage')))
                <div>
                    <a href="{{ route('seller-invoicing.settings') }}" class="btn btn-outline-primary btn-sm">
                        <x-core::icon name="ti ti-settings" /> {{ trans('plugins/seller-invoicing::seller-invoicing.settings.title') }}
                    </a>
                </div>
            @endif
        </div>

        @if (session('success_msg'))
            <div class="alert alert-success m-3">{{ session('success_msg') }}</div>
        @endif
        @if (session('error_msg'))
            <div class="alert alert-danger m-3">{{ session('error_msg') }}</div>
        @endif

        @if ($documents->count() > 0)
            <div class="table-responsive">
                <table class="table table-vcenter table-hover card-table">
                    <thead>
                        <tr>
                            <th>{{ trans('plugins/seller-invoicing::seller-invoicing.invoice_number') }}</th>
                            <th>{{ trans('plugins/seller-invoicing::seller-invoicing.order') }}</th>
                            @if (! $vendor)
                                <th>{{ trans('plugins/seller-invoicing::seller-invoicing.seller') }}</th>
                            @endif
                            <th>{{ trans('plugins/seller-invoicing::seller-invoicing.date') }}</th>
                            <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.subtotal') }}</th>
                            <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.tax') }}</th>
                            <th class="text-end">{{ trans('plugins/seller-invoicing::seller-invoicing.grand_total') }}</th>
                            <th class="text-center">{{ trans('plugins/seller-invoicing::seller-invoicing.status') }}</th>
                            <th class="text-center" style="width: 140px;">{{ trans('plugins/seller-invoicing::seller-invoicing.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <a href="{{ $vendor ? route('marketplace.vendor.seller-invoicing.show', $document->getKey()) : route('seller-invoicing.show', $document->getKey()) }}" class="fw-bold text-primary">
                                        {{ $document->number }}
                                    </a>
                                </td>
                                <td>
                                    @if ($document->order)
                                        <a href="{{ $vendor ? route('marketplace.vendor.orders.edit', $document->order->getKey()) : route('orders.edit', $document->order->getKey()) }}">
                                            #{{ ltrim($document->order->code ?: (string)$document->order->getKey(), '#') }}
                                        </a>
                                    @else
                                        #{{ $document->order_id }}
                                    @endif
                                </td>
                                @if (! $vendor)
                                    <td>
                                        <strong>{{ $document->order?->store?->name ?: ($document->seller?->name ?: 'Vendor #' . $document->seller_id) }}</strong>
                                    </td>
                                @endif
                                <td>
                                    {{ $document->issued_at?->translatedFormat('M d, Y') ?: $document->created_at->translatedFormat('M d, Y') }}
                                </td>
                                <td class="text-end">{{ format_price($document->subtotal) }}</td>
                                <td class="text-end">{{ format_price($document->tax_amount) }}</td>
                                <td class="text-end fw-bold text-dark">{{ format_price($document->grand_total) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ $vendor ? $document->vendor_download_url : $document->download_url }}" class="btn btn-outline-primary" title="{{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}">
                                            <x-core::icon name="ti ti-download" />
                                        </a>
                                        <a href="{{ $vendor ? $document->vendor_print_url : $document->print_url }}" target="_blank" class="btn btn-outline-secondary" title="{{ trans('plugins/seller-invoicing::seller-invoicing.print') }}">
                                            <x-core::icon name="ti ti-printer" />
                                        </a>
                                        <a href="{{ $vendor ? route('marketplace.vendor.seller-invoicing.show', $document->getKey()) : route('seller-invoicing.show', $document->getKey()) }}" class="btn btn-outline-info" title="{{ trans('plugins/seller-invoicing::seller-invoicing.view_details') }}">
                                            <x-core::icon name="ti ti-eye" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($documents->hasPages())
                <div class="card-footer">
                    {{ $documents->links() }}
                </div>
            @endif
        @else
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <x-core::icon name="ti ti-file-invoice" class="text-muted" style="font-size: 3rem;" />
                </div>
                <h4>{{ trans('plugins/seller-invoicing::seller-invoicing.no_documents') }}</h4>
                <p class="text-muted mb-0">
                    {{ trans('plugins/seller-invoicing::seller-invoicing.no_documents_desc') }}
                </p>
            </div>
        @endif
    </div>
@endsection
