@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ trans('plugins/ecommerce-wholesale::wholesale.wholesale_products') }}</h4>
        </div>
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 60px;"></th>
                                <th>{{ trans('core/base::tables.name') }}</th>
                                <th>{{ trans('plugins/ecommerce-wholesale::wholesale.products.sku') }}</th>
                                <th>{{ trans('plugins/ecommerce-wholesale::wholesale.products.pricing_tiers') }}</th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <img src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                             alt="{{ $product->name }}"
                                             class="img-thumbnail"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $product->sku ?: 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @foreach($product->groupPricingRules as $rule)
                                            <span class="badge bg-cyan text-cyan-fg me-1 mb-1">
                                                {{ $rule->min_quantity }}-{{ $rule->max_quantity ?? '∞' }}:
                                                @if($rule->discount_type->getValue() === 'percentage')
                                                    {{ $rule->discount_value }}% off
                                                @elseif($rule->discount_type->getValue() === 'fixed')
                                                    -{{ format_price($rule->discount_value) }}
                                                @else
                                                    {{ format_price($rule->discount_value) }}
                                                @endif
                                                @if($rule->customerGroup)
                                                    <small>({{ $rule->customerGroup->name }})</small>
                                                @endif
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('marketplace.vendor.products.edit', $product->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <x-core::icon name="ti ti-edit" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <x-core::icon name="ti ti-package-off" style="font-size: 48px; color: #ccc;" />
                    <p class="mt-3 text-muted">{{ trans('plugins/ecommerce-wholesale::wholesale.products.no_products') }}</p>
                </div>
            @endif
        </div>
    </div>
@stop
