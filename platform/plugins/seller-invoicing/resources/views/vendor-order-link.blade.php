@if ($document)
    <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div class="mb-2 mb-md-0">
            <strong>
                <x-core::icon name="ti ti-file-invoice" /> {{ trans('plugins/seller-invoicing::seller-invoicing.seller_invoice_card_title') }}:
            </strong>
            <span class="badge bg-primary text-white ms-1">{{ $document->number }}</span>
            <span class="ms-2">
                {{ trans('plugins/seller-invoicing::seller-invoicing.grand_total') }}:
                <strong>{{ format_price($document->grand_total) }}</strong>
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ $document->vendor_download_url }}" class="btn btn-sm btn-primary">
                <x-core::icon name="ti ti-download" /> {{ trans('plugins/seller-invoicing::seller-invoicing.download_pdf') }}
            </a>
            <a href="{{ $document->vendor_print_url }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <x-core::icon name="ti ti-printer" /> {{ trans('plugins/seller-invoicing::seller-invoicing.print') }}
            </a>
        </div>
    </div>
@endif

