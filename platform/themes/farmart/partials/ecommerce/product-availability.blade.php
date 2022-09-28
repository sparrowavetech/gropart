<div class="summary-meta">
    @if ($product->isOutOfStock())
        <div class="product-stock out-of-stock d-inline-block">
            <label>{{ __('Availability') }}:</label>{{ __('Out of stock') }}
        </div>
    @else
        @if (!$productVariation)
            <div class="product-stock out-of-stock d-inline-block">
                <label>{{ __('Availability') }}:</label>{{ __('Not available') }}
            </div>
        @else
            @if ($productVariation->isOutOfStock())
                <div class="product-stock out-of-stock d-inline-block">
                    <label>{{ __('Availability') }}:</label>{{ __('Out of stock') }}
                </div>
            @elseif  (!$productVariation->with_storehouse_management || $productVariation->quantity < 1)
                <div class="product-stock in-stock d-inline-block">
                    <label>{{ __('Availability') }}:</label> {!! BaseHelper::clean($productVariation->stock_status_html) !!}
                </div>
            @elseif ($productVariation->quantity)
                @if (EcommerceHelper::showNumberOfProductsInProductSingle())
                    <div class="product-stock in-stock d-inline-block">
                        <label>{{ __('Availability') }}:</label>
                        @if ($productVariation->quantity != 1)
                            {{ __(':number products available', ['number' => $productVariation->quantity]) }}
                        @else
                            {{ __(':number product available', ['number' => $productVariation->quantity]) }}
                        @endif
                    </div>
                @else
                    <div class="product-stock in-stock d-inline-block">
                        <label>{{ __('Availability') }}:</label>{{ __('In stock') }}
                    </div>
                @endif
            @endif
        @endif
    @endif
</div>
