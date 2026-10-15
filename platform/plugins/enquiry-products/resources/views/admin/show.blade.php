@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
@php
    $statusLabel = __('plugins/enquiry-products::messages.' . $enquiry->status);
    $statusColor = match($enquiry->status) { 'contacted' => 'success', 'not_available' => 'warning', 'rejected' => 'danger', default => 'info' };
    $product = $enquiry->product;
@endphp
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title">{{ __('plugins/enquiry-products::messages.enquiry_information') }} {{ $enquiry->code }}</h3>
        <span class="badge bg-{{ $statusColor }} text-white"><x-core::icon name="ti ti-info-circle" /> {{ $statusLabel }}</span>
    </div>
    <div class="card-body p-4">
        <dl class="row mb-0">
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.customer_name') }}</dt>
            <dd class="col-md-9 mb-4"><x-core::icon name="ti ti-user" /> {{ $enquiry->name }}</dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.customer_email') }}</dt>
            <dd class="col-md-9 mb-4 text-break"><a href="mailto:{{ $enquiry->email }}"><x-core::icon name="ti ti-mail" /> {{ $enquiry->email }}</a></dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.customer_mobile_number') }}</dt>
            <dd class="col-md-9 mb-4"><a href="tel:{{ $enquiry->phone }}"><x-core::icon name="ti ti-phone" /> {{ $enquiry->phone }}</a></dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.customer_address') }}</dt>
            <dd class="col-md-9 mb-4 text-break">{{ $enquiry->address }} {{ $enquiry->zip_code }}</dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.enquiry_status') }}</dt>
            <dd class="col-md-9 mb-4"><span class="badge bg-{{ $statusColor }} text-white">{{ $statusLabel }}</span></dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.product_name') }}</dt>
            <dd class="col-md-9 mb-4">
                @if($product?->slugable?->key)<a href="{{ route('public.enquiry-products.show', $product->slugable->key) }}" target="_blank" rel="noopener noreferrer"><x-core::icon name="ti ti-external-link" /> {{ $product->name }}</a>@else{{ $product?->name ?: __('plugins/enquiry-products::messages.deleted_product') }}@endif
            </dd>
            @if($store)
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.product_store') }}</dt>
            <dd class="col-md-9 mb-4"><a href="{{ $store->url }}" target="_blank" rel="noopener noreferrer"><x-core::icon name="ti ti-external-link" /> {{ $store->name }}</a>
                @if(is_plugin_active('vendor-verified-badge')){!! \SparroWave\VendorVerifiedBadge\Supports\VendorBadgeHelper::renderBadges($store) !!}@elseif($store->is_verified)<span class="text-success" title="{{ __('plugins/enquiry-products::messages.verified_seller') }}">✓</span>@endif
            </dd>
            @endif
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.quantity') }}</dt><dd class="col-md-9 mb-4">{{ $enquiry->quantity ?: 1 }}</dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.requirement') }}</dt><dd class="col-md-9 mb-4 text-break" style="white-space:pre-wrap">{{ $enquiry->description ?: '—' }}</dd>
            <dt class="col-md-3 mb-2 mb-md-4">{{ __('plugins/enquiry-products::messages.submitted_at') }}</dt><dd class="col-md-9 mb-4">{{ $enquiry->created_at }}</dd>
            @if($enquiry->attachment)<dt class="col-md-3 mb-2">{{ __('plugins/enquiry-products::messages.attachment') }}</dt><dd class="col-md-9"><a class="btn btn-outline-primary btn-sm" href="{{ route('enquiry-products.attachment', $enquiry->id) }}"><x-core::icon name="ti ti-download" /> {{ __('plugins/enquiry-products::messages.download_attachment') }}</a></dd>@endif
        </dl>
    </div>
    @if(auth()->user()->hasPermission('enquiry-products.edit'))
    <div class="card-footer d-flex flex-wrap gap-2">
        @foreach(['contacted' => ['success', 'ti ti-circle-check', 'plugins/enquiry-products::messages.enquiry_contacted'], 'not_available' => ['warning', 'ti ti-ban', 'plugins/enquiry-products::messages.not_available'], 'rejected' => ['danger', 'ti ti-circle-x', 'plugins/enquiry-products::messages.reject'], 'pending' => ['secondary', 'ti ti-clock', 'plugins/enquiry-products::messages.mark_pending']] as $value => [$color, $icon, $label])
            @if($enquiry->status !== $value)
            <form method="post" action="{{ route('enquiry-products.update', $enquiry->id) }}">@csrf
                <input type="hidden" name="status" value="{{ $value }}">
                <input type="hidden" name="admin_notes" value="{{ $enquiry->admin_notes }}">
                <button class="btn btn-{{ $color }}" type="submit"><x-core::icon :name="$icon" /> {{ __($label) }}</button>
            </form>
            @endif
        @endforeach
    </div>
    @endif
</div>
<div class="row">
    @if(auth()->user()->hasPermission('enquiry-products.edit'))
    <div class="col-lg-6 mb-4"><div class="card h-100">
        <div class="card-header"><h3 class="card-title">{{ __('plugins/enquiry-products::messages.internal_notes') }}</h3></div>
        <form class="card-body" method="post" action="{{ route('enquiry-products.update', $enquiry->id) }}">@csrf
            <input type="hidden" name="status" value="{{ $enquiry->status }}">
            <label for="ep-admin-notes" class="form-label">{{ __('plugins/enquiry-products::messages.notes_visible_only_to_administrators') }}</label>
            <textarea id="ep-admin-notes" class="form-control mb-3" name="admin_notes" rows="4" maxlength="10000">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>
            @error('admin_notes')<div class="text-danger mb-3">{{ $message }}</div>@enderror
            <button class="btn btn-primary" type="submit"><x-core::icon name="ti ti-device-floppy" /> {{ __('plugins/enquiry-products::messages.save_notes') }}</button>
        </form>
    </div></div>
    @endif
    <div class="col-lg-6 mb-4"><div class="card h-100">
        <div class="card-header"><h3 class="card-title">{{ __('plugins/enquiry-products::messages.status_history') }}</h3></div>
        <div class="list-group list-group-flush">
            @forelse($history as $change)<div class="list-group-item d-flex justify-content-between flex-wrap gap-2"><span>{{ __('plugins/enquiry-products::messages.' . $change->status) }}</span><span class="text-muted">{{ $change->created_at }}</span></div>
            @empty<div class="card-body text-muted">{{ __('plugins/enquiry-products::messages.no_status_changes_yet') }}</div>@endforelse
        </div>
    </div></div>
</div>
@endsection
