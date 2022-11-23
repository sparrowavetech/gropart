<div class="pt-3 mb-4">
    <div class="align-items-center">
        <h6 class="d-inline-block">{{ __('Enquiry number') }}: {{ $enquiry->code }}</h6>
    </div>

    <div class="checkout-success-products">
        <div class="row show-cart-row d-md-none p-2">
            <div class="col-9">
                <a class="show-cart-link"
                   href="javascript:void(0);"
                   data-bs-toggle="collapse"
                   data-bs-target="{{ '#cart-item-' . $enquiry->id }}">
                    {{ __('Enquiry information :enquiry', ['enquiry' => $enquiry->code]) }} <i class="fa fa-angle-down" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <div id="{{ 'cart-item-' . $enquiry->id }}" class="collapse collapse-products">
            @php
            $product = $enquiry->product;
            @endphp
                @if ($enquiry->product)
                    <div class="row cart-item">
                    <div class="col-lg-3 col-md-3">
                        <div class="checkout-product-img-wrapper">
                            <img class="item-thumb img-thumbnail img-rounded"
                                 src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                 alt="{{ $product->name . '(' . $product->sku . ')'}}">
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5">
                        <p class="mb-0">{{ $product->name }}</p>
                    </div>
                    <div class="col-lg-4 col-md-4 col-4 float-end text-end">
                        <p>{{ format_price($product->price) }}</p>
                    </div>
                </div> <!--  /item -->
                @endif
         

           
        </div>
    </div>
</div>
