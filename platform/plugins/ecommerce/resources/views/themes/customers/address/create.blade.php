@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <h2 class="customer-page-title">{{ __('Add a new address') }}</h2>
    <br>
    <div class="profile-content">

        {!! Form::open(['route' => 'customer.address.create']) !!}
            @include('plugins/ecommerce::themes.customers.address.form', ['address' => new Botble\Ecommerce\Models\Address()])
        {!! Form::close() !!}
    </div>
@endsection
