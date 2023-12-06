<div class="row cart-page-content py-md-5 mt-md-3">
    <div class="col-12">
        <form class="form--shopping-cart cart-form" method="post" action="{{ route('public.cart.update') }}">
            @csrf
            @if (count($products) > 0)
            <div class="row mt-4">
                <div class="col-sm-9">
                    <table class="table cart-form__contents" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="product-thumbnail"></th>
                                <th class="product-name">{{ __('Product') }}</th>
                                <th class="product-price product-md d-md-table-cell d-none">{{ __('Price') }}</th>
                                <th class="product-quantity product-md d-md-table-cell d-none">{{ __('Quantity') }}</th>
                                <th class="product-subtotal product-md d-md-table-cell d-none">{{ __('Total') }}</th>
                                <th class="product-remove"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (Cart::instance('cart')->content() as $key => $cartItem)
                                @php
                                    $product = $products->find($cartItem->id);
                                @endphp

                                @if (!empty($product))
                                    <tr class="cart-form__cart-item cart_item">
                                        <td class="product-thumbnail">
                                            <input type="hidden" name="items[{{ $key }}][rowId]" value="{{ $cartItem->rowId }}">

                                            <a href="{{ $product->original_product->url }}"
                                                style="max-width: 74px; display: inline-block;">
                                                <img class="lazyload" src="{{ image_placeholder(RvMedia::getImageUrl($cartItem->options['image'], 'thumb', false, RvMedia::getDefaultImage())) }}" data-src="{{ RvMedia::getImageUrl($cartItem->options['image'], 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                    alt="{{ $product->original_product->name }}">
                                            </a>
                                        </td>
                                        <td class="product-name d-md-table-cell d-block" data-title="{{ __('Product') }}">
                                            <a href="{{ $product->original_product->url }}">{{ $product->original_product->name }}</a>
                                            @if (is_plugin_active('marketplace') && $product->original_product->store->id)
                                                <div class="variation-group">
                                                    <span class="text-secondary">{{ __('Vendor') }}: </span>
                                                    <span class="text-primary ms-1">
                                                        <a href="{{ $product->original_product->store->url }}">{{ $product->original_product->store->name }}</a>
                                                    </span>
                                                </div>
                                            @endif
                                            @if ($attributes = Arr::get($cartItem->options, 'attributes'))
                                                <p class="mb-0">
                                                    <small>{{ $attributes }}</small>
                                                </p>
                                            @endif
                                            @if (EcommerceHelper::isEnabledProductOptions() && !empty($cartItem->options['options']))
                                                {!! render_product_options_html(
                                                    $cartItem->options['options'],
                                                    $product->original_product->front_sale_price_with_taxes,
                                                ) !!}
                                            @endif

                                            @include(
                                                'plugins/ecommerce::themes.includes.cart-item-options-extras',
                                                ['options' => $cartItem->options]
                                            )
                                        </td>
                                        <td class="product-price product-md d-md-table-cell d-block" data-title="Price">
                                            <div class="box-price">
                                                <span class="d-md-none title-price">{{ __('Price') }}: </span>
                                                <span class="quantity">
                                                    <span class="price-amount amount">
                                                        <bdi>{{ format_price($cartItem->price) }} @if ($product->front_sale_price != $product->price)
                                                                <small><del>{{ format_price($product->price) }}</del></small>
                                                            @endif</bdi>
                                                    </span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="product-quantity product-md d-md-table-cell d-block" data-title="{{ __('Quantity') }}">
                                            <div class="product-button">
                                                {!! Theme::partial('ecommerce.product-quantity', compact('product') + [
                                                        'name' => 'items[' . $key . '][values][qty]',
                                                        'value' => $cartItem->qty,
                                                    ],) !!}
                                            </div>
                                        </td>
                                        <td class="product-subtotal product-md d-md-table-cell d-block" data-title="{{ __('Total') }}">
                                            <div class="box-price">
                                                <span class="d-md-none title-price">{{ __('Total') }}: </span>
                                                <span class="fw-bold amount">
                                                    <span class="price-current">{{ format_price($cartItem->price * $cartItem->qty) }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="product-remove">
                                            <a class="fs-4 remove btn remove-cart-item" href="#"
                                                data-url="{{ route('public.cart.remove', $cartItem->rowId) }}"
                                                aria-label="Remove this item">
                                                <span class="svg-icon">
                                                    <svg>
                                                        <use href="#svg-icon-trash" xlink:href="#svg-icon-trash"></use>
                                                    </svg>
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="actions my-4 pb-4 border-bottom d-none d-sm-block d-md-block d-lg-block d-xl-block">
                        <div class="actions__button-wrapper row justify-content-between">
                            <div class="col-sm-12">
                                <div class="actions__left d-grid d-md-block">
                                    <a class="btn btn-secondary mb-2" href="{{ route('public.products') }}">
                                        <span class="svg-icon">
                                            <svg>
                                                <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
                                            </svg>
                                        </span> {{ __('Continue Shopping') }}
                                    </a>
                                    <a class="btn btn-secondary mb-2 ms-md-2" href="{{ route('public.index') }}">
                                        <span class="svg-icon">
                                            <svg>
                                                <use href="#svg-icon-home" xlink:href="#svg-icon-home"></use>
                                            </svg>
                                        </span> {{ __('Back to Home') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    @if ($couponDiscountAmount > 0 && session('applied_coupon_code'))
                        <div class="alert alert-success d-flex justify-content-around couponbox" role="alert">
                            <svg fill="#198754" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 300 300" xml:space="preserve"
                            style="height: 40px; width: 40px; margin-right:10px"><g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier"> <g> <g> <g> <rect x="107.563" y="119.408" width="10.375"
                            height="12.281"></rect>
                            <path d="M170.944,134.285c0-8.997-7.467-16.376-16.381-16.376c-8.995,0-16.376,7.291-16.376,16.376 c0,9.181,7.381,16.376,16.376,16.376C163.476,150.661,170.944,143.376,170.944,134.285z M147.456,134.285 c0-3.958,3.239-7.109,7.107-7.109c3.87,0,7.107,3.149,7.107,7.109c0,3.96-3.237,7.109-7.107,7.109 C150.695,141.394,147.456,138.246,147.456,134.285z"></path> <rect x="107.563" y="168.531" width="10.375" height="12.278"></rect> <rect x="138.38" y="145.544" transform="matrix(0.4696 -0.8829 0.8829 0.4696 -40.0464 234.8049)" width="74.035" height="10.375"></rect> <rect x="107.563" y="143.967" width="10.375" height="12.278"></rect> <path d="M196.828,150.124c-8.997-0.034-16.407,7.231-16.438,16.319c-0.036,9.179,7.319,16.407,16.322,16.444 c8.912,0.031,16.402-7.234,16.438-16.322C213.181,157.563,205.74,150.156,196.828,150.124z M196.768,173.615 c-3.865,0-7.107-3.151-7.107-7.112c0-3.96,3.242-7.107,7.107-7.107c3.87,0,7.112,3.146,7.112,7.107 S200.638,173.615,196.768,173.615z"></path> <path d="M149.997,0C67.157,0,0,67.157,0,150c0,82.841,67.157,150,149.997,150C232.841,300,300,232.838,300,150 C300,67.157,232.841,0,149.997,0z M238.489,185.004c0,8.045-7.462,14.568-16.661,14.568h-103.89v-6.484h-10.375v6.484H78.175 c-9.202,0-16.664-6.526-16.664-14.568v-69.795c0-8.043,7.462-14.566,16.664-14.566h29.388v6.484h10.375v-6.484h103.89 c9.2,0,16.661,6.523,16.661,14.566V185.004z"></path>
                            </g> </g> </g> </g></svg>
                            <span style="max-width:240px" class="fw-bold text-success">{{ __('Applied coupon ":code" successfully!', ['code' => session('applied_coupon_code')]) }}</span>
                            <span>(<small><a class="btn-remove-coupon-code text-danger" data-url="{{ route('public.coupon.remove') }}"
                                href="#" data-processing-text="{{ __('Removing...') }}">{{ __('Remove') }}</a>
                            </small>)</span>
                        </div>
                    @else
                        <div class="col-coupon form-coupon-wrapper">
                            <div class="coupon">
                                <label for="coupon_code">
                                    <h6>{{ __('Using A Promo Code?') }}</h6>
                                </label>
                                <div class="coupon-input input-group mt-2 mb-3">
                                    <input class="form-control coupon-code" type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="{{ __('Enter coupon code') }}">
                                    <button class="btn btn-primary lh-1 btn-apply-coupon-code" type="button" data-url="{{ route('public.coupon.apply') }}">{{ __('Apply') }}</button>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="cart_totals bg-light p-4 rounded">
                        <h5 class="mb-3">{{ __('Cart totals') }}</h5>
                        <div class="cart_totals-table">
                            <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                <span class="title fw-bold">{{ __('Subtotal') }}:</span>
                                <span class="amount fw-bold">
                                    <span class="price-current">{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
                                </span>
                            </div>
                            @if (EcommerceHelper::isTaxEnabled() && Cart::instance('cart')->rawTax() > 0)
                                <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                    <span class="title fw-bold">{{ __('Tax') }}:</span>
                                    <span class="amount fw-bold">
                                        <span class="price-current text-success">(+) {{ format_price(Cart::instance('cart')->rawTax()) }}</span>
                                    </span>
                                </div>
                            @endif
                            @if ($couponDiscountAmount > 0 && session('applied_coupon_code'))
                                <div class="cart-subtotal d-flex justify-content-between border-bottom pb-3 mb-3">
                                    <span class="title">
                                        <span class="fw-bold text-success">{{ __('Coupon code: :code', ['code' => session('applied_coupon_code')]) }}</span>
                                        (<small>
                                            <a class="btn-remove-coupon-code text-danger" data-url="{{ route('public.coupon.remove') }}"
                                            href="#" data-processing-text="{{ __('Removing...') }}">{{ __('Remove') }}</a>
                                        </small>)
                                    </span>

                                    <span class="amount fw-bold text-danger">(-) {{ format_price($couponDiscountAmount) }}</span>
                                </div>
                            @endif
                            @if ($promotionDiscountAmount)
                                <div class="ps-block__header">
                                    <p class="text-danger">{{ __('Discount promotion') }} <span>(-) {{ format_price($promotionDiscountAmount) }}</span></p>
                                </div>
                            @endif
                            <div class="order-total d-flex justify-content-between mb-3">
                                <span class="title">
                                    <h6 class="mb-0">{{ __('Total') }} :</h6>
                                </span>
                                <span class="amount fw-bold fs-6">
                                    <span class="price-current">{{ $promotionDiscountAmount + $couponDiscountAmount > Cart::instance('cart')->rawTotal() ? format_price(0) : format_price(Cart::instance('cart')->rawTotal() - $promotionDiscountAmount - $couponDiscountAmount) }}</span>
                                </span>
                            </div>
                            <div class="cart-shipping-text d-flex justify-content-between mb-3">
                                <span class="title">
                                    <h6 class="mb-0">{{ __('Shipping fee') }} :</h6>
                                </span>
                                <span class="amount fw-bold fs-7">
                                    <span class="cart-shipping-price">{{ __('(Shipping fees not included)') }}</span>
                                </span>
                            </div>
                        </div>
                        @if (session('tracked_start_checkout'))
                            <div class="proceed-to-checkout">
                                <div class="d-grid gap-2">
                                    <a class="checkout-button btn btn-primary" href="{{ route('public.checkout.information', session('tracked_start_checkout')) }}">{{ __('Proceed to checkout') }}</a>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="actions my-4 py-4 border-bottom border-top d-block d-sm-none d-md-none d-lg-none d-xl-none">
                        <div class="actions__button-wrapper row justify-content-between">
                            <div class="col-sm-12">
                                <div class="actions__left d-grid d-md-block">
                                    <a class="btn btn-secondary mb-2" href="{{ route('public.products') }}">
                                        <span class="svg-icon">
                                            <svg>
                                                <use href="#svg-icon-arrow-left" xlink:href="#svg-icon-arrow-left"></use>
                                            </svg>
                                        </span> {{ __('Continue Shopping') }}
                                    </a>
                                    <a class="btn btn-secondary mb-2 ms-md-2" href="{{ route('public.index') }}">
                                        <span class="svg-icon">
                                            <svg>
                                                <use href="#svg-icon-home" xlink:href="#svg-icon-home"></use>
                                            </svg>
                                        </span> {{ __('Back to Home') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
                <h3 class="text-center text-danger">{{ __('Your cart is empty!') }}</h3>
            @endif
        </form>

        @if (count($crossSellProducts) > 0)
            <div class="row align-items-center mb-2 widget-header cross-sale-products">
                <h2 class="col-auto mb-3">{{ __('Customers who bought this item also bought') }}</h2>
            </div>
            <div class="row row-cols-xl-6 row-cols-lg-6 row-cols-md-3 row-cols-sm-3 row-cols-2 g-0 products-with-border">
                @foreach($crossSellProducts as $crossSellProduct)
                    <div class="col">
                        <div class="product-inner">
                            {!! Theme::partial('ecommerce.product-item', ['product' => $crossSellProduct]) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
