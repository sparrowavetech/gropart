@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <h2 class="customer-page-title">{{ __('Address books') }}</h2>
    <br>
    <div class="profile-content">

        {!! Form::open(['route' => ['customer.address.edit', $address->id]]) !!}
            @include('plugins/ecommerce::themes.customers.address.form', compact('address'))
        {!! Form::close() !!}
    </div>
@endsection
