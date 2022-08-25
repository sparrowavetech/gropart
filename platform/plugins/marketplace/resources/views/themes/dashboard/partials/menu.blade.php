@php
    $menus = [
        [
            'key'   => 'marketplace.vendor.dashboard',
            'icon'  => 'icon-home',
            'name'  => __('Dashboard')
        ],
        [
            'key'   => 'marketplace.vendor.products.index',
            'icon'  => 'icon-database',
            'name'  => __('Products')
        ],
        [
            'key'   => 'marketplace.vendor.orders.index',
            'icon'  => 'icon-bag2',
            'name'  => __('Orders')
        ],
        [
            'key'   => 'marketplace.vendor.discounts.index',
            'icon'  => 'icon-gift',
            'name'  => __('Coupons')
        ],
        [
            'key'   => 'marketplace.vendor.withdrawals.index',
            'icon'  => 'icon-bag-dollar',
            'name'  => __('Withdrawals')
        ],
        [
            'key'   => 'marketplace.vendor.settings',
            'icon'  => 'icon-cog',
            'name'  => __('Settings')
        ],
    ];
@endphp

<ul class="menu">
    @foreach ($menus as $item)
        <li>
            <a @if (Route::currentRouteName() == $item['key']) class="active" @endif href="{{ route($item['key']) }}">
                <i class="{{ $item['icon'] }}"></i>{{ $item['name'] }}
            </a>
        </li>
    @endforeach
    <li><a href="{{ route('customer.overview') }}"><i class="icon-user"></i>{{ __('Customer dashboard') }}</a></li>
</ul>
