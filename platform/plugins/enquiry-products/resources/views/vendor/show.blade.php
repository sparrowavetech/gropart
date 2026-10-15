@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">{{ $enquiry->code ?: __('plugins/enquiry-products::messages.equipment_enquiry') }}</h3></div><div class="card-body">
<a class="btn btn-secondary mb-3" href="{{ route('enquiry-products.vendor.index') }}">{{ __('plugins/enquiry-products::messages.back_to_enquiries') }}</a>
@include('plugins/enquiry-products::partials.enquiry-detail', ['vendor' => true])
</div></div>
@endsection
