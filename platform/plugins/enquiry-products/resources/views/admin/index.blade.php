@extends(BaseHelper::getAdminMasterLayoutTemplate())
@section('content')
<div class="card"><div class="card-body">@include('plugins/enquiry-products::partials.enquiries', ['vendor' => false])</div></div>
@endsection
