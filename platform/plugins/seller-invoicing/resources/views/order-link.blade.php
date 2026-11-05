<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">
            <x-core::icon name="ti ti-file-invoice" /> {{ trans('plugins/seller-invoicing::seller-invoicing.seller_invoice_card_title') }}
        </h4>
        @if ($document)
            <span class="badge bg-success text-success-fg">{{ $document->number }}</span>
        @endif
    </div>
    <div class="card-body">
        @if ($document)
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <span class="text-muted">{{ trans('plugins/seller-invoicing::seller-invoicing.subtotal') }}:</span>
                    <strong class="d-block">{{ format_price($document->subtotal) }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">{{ trans('plugins/seller-invoicing::seller-invoicing.tax') }}:</span>
                    <strong class="d-block">{{ format_price($document->tax_amount) }}</strong>
                </div>
                <div class="col-12 border-top pt-2 mt-2">
                    <span class="text-muted">{{ trans('plugins/seller-invoicing::seller-invoicing.grand_total') }}:</span>
                    <strong class="text-primary fs-5 d-block">{{ format_price($document->grand_total) }}</strong>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $document->download_url }}" class="btn btn-sm btn-primary">
                    <x-core::icon name="ti ti-download" /> {{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}
                </a>
                <a href="{{ $document->print_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <x-core::icon name="ti ti-printer" /> {{ trans('plugins/seller-invoicing::seller-invoicing.print') }}
                </a>
                <a href="{{ route('seller-invoicing.show', $document->getKey()) }}" class="btn btn-sm btn-outline-info">
                    <x-core::icon name="ti ti-eye" /> {{ trans('plugins/seller-invoicing::seller-invoicing.view_details') }}
                </a>
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0">{{ trans('plugins/seller-invoicing::seller-invoicing.not_generated_yet') }}</p>
                @if ($canManage)
                    <form method="post" action="{{ route('seller-invoicing.orders.generate', $order->getKey()) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <x-core::icon name="ti ti-plus" /> {{ trans('plugins/seller-invoicing::seller-invoicing.generate_invoice') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
