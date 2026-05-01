@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ trans('plugins/ecommerce-wholesale::wholesale.wholesale_products') }}</h4>
        </div>
        <div class="card-body">
            @if(is_plugin_active('marketplace'))
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}"
                           href="{{ route('wholesale.products.index', ['tab' => 'all']) }}">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.products.all') }}
                            <span class="badge bg-azure text-azure-fg ms-1">{{ $inhouseCount + $sellerCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'inhouse' ? 'active' : '' }}"
                           href="{{ route('wholesale.products.index', ['tab' => 'inhouse']) }}">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.products.inhouse') }}
                            <span class="badge bg-blue text-blue-fg ms-1">{{ $inhouseCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'seller' ? 'active' : '' }}"
                           href="{{ route('wholesale.products.index', ['tab' => 'seller']) }}">
                            {{ trans('plugins/ecommerce-wholesale::wholesale.products.seller') }}
                            <span class="badge bg-teal text-teal-fg ms-1">{{ $sellerCount }}</span>
                        </a>
                    </li>
                </ul>
            @endif

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
                                        @if(is_plugin_active('marketplace') && $product->store_id)
                                            <br>
                                            <small class="text-info">
                                                <x-core::icon name="ti ti-building-store" />
                                                {{ $product->store?->name ?? 'Seller' }}
                                            </small>
                                        @endif
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
                                        <a href="{{ route('products.edit', $product->id) }}"
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
                    {{ $products->appends(['tab' => $tab])->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <x-core::icon name="ti ti-package-off" style="font-size: 48px; color: #ccc;" />
                    <p class="mt-3 text-muted">{{ trans('plugins/ecommerce-wholesale::wholesale.products.no_products') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
