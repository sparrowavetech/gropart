@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    @php
        $customer = auth('customer')->user();
    @endphp
    <p>{!! BaseHelper::clean(
        __('Hello <strong>:name</strong> (not <strong>:name</strong>? <a class="text-primary" href=":link">Log out</a>)', [
            'name' => $customer->name,
            'link' => route('customer.logout'),
        ]),
    ) !!}</p>

    <p>{!! BaseHelper::clean(
        __(
            'From your account dashboard you can view your <a class="text-primary" href=":order">recent orders</a>, manage your <a class="text-primary" href=":addresses">shipping and billing addresses</a>, and <a class="text-primary" href=":edit_account">edit your password and account details</a>.',
            [
                'order' => route('customer.orders'),
                'addresses' => route('customer.address'),
                'edit_account' => route('customer.edit-account'),
            ],
        ),
    ) !!}</p>

    @if ($customer->orders()->count())
        <div
            class="alert alert-info d-flex align-items-center justify-content-between border-0"
            role="alert"
        >
            <div>
                <x-core::icon name="ti ti-circle-check" />
                <span class="ms-2">{{ __('No order has been made yet') }}.</span>
            </div>
            <a
                class="box-shadow"
                href="{{ route('public.products') }}"
            >{{ __('Browse products') }}</a>
        </div>
    @endif
@endsection
