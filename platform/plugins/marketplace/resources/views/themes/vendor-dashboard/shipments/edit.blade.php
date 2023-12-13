@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))
@section('content')
    <div class="max-width-1200">
        @include('plugins/ecommerce::shipments.notification')

        <div class="flexbox-grid no-pd-none">
            <div class="flexbox-content">
                @include('plugins/ecommerce::shipments.products', [
                    'productEditRouteName' => 'marketplace.vendor.products.edit',
                    'orderEditRouteName' => 'marketplace.vendor.orders.edit',
                ])

                @include('plugins/ecommerce::shipments.form', [
                    'updateStatusRouteName' => 'marketplace.vendor.orders.update-shipping-status',
                    'updateCodStatusRouteName' => 'marketplace.vendor.shipments.update-cod-status',
                ])

                @include('plugins/ecommerce::shipments.histories')
            </div>

            @include('plugins/ecommerce::shipments.information', [
                'orderEditRouteName' => 'marketplace.vendor.orders.edit',
            ])
        </div>
    </div>
@stop
