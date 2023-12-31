@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-plugins-ecommerce::intro
        :title="trans('plugins/ecommerce::review.intro.title')"
        :subtitle="trans('plugins/ecommerce::review.intro.description')"
    >
        <x-slot:icon>
            <img
                src="{{ asset('vendor/core/plugins/ecommerce/images/empty-customer.png') }}"
                alt="image"
            >
        </x-slot:icon>
    </x-plugins-ecommerce::intro>
@stop
