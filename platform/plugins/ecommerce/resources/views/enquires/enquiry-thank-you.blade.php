@extends('plugins/ecommerce::orders.master')
@section('title')
{{ __('Enquiry successfully. Enquiry number :id', ['id' => $enquiry->code]) }}
@stop
@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-7 col-md-6 col-12 left">
            @include('plugins/ecommerce::orders.partials.logo')

            <div class="thank-you">
                <i class="fa fa-check-circle" aria-hidden="true"></i>
                <div class="d-inline-block">
                    <h3 class="thank-you-sentence">
                        {{ __('Your enquiry is successfully placed') }}
                    </h3>
                    <p>{{ __('Thank you for enquiring our products!') }}</p>
                </div>
            </div>

            <div class="order-customer-info">
                <h3> {{ __('Customer information') }}</h3>
                <p>
                    <span class="d-inline-block">{{ __('Full name') }}:</span>
                    <span class="order-customer-info-meta">{{ $enquiry->name }}</span>
                </p>
                @if ($enquiry->phone)
                <p>
                    <span class="d-inline-block">{{ __('Phone') }}:</span>
                    <span class="order-customer-info-meta">{{ $enquiry->phone }}</span>
                </p>
                @endif
                <p>
                    <span class="d-inline-block">{{ __('Email') }}:</span>
                    <span class="order-customer-info-meta">{{ $enquiry->email }}</span>
                </p>

                <p>
                    <span class="d-inline-block">{{ __('Address') }}:</span>
                    <span class="order-customer-info-meta">{{ $enquiry->address }}, {{ $enquiry->cityName->name }}, {{ $enquiry->stateName->name }}, {{ $enquiry->zip_code }}</span>
                </p>
            </div>
        </div>
        <!---------------------- start right column ------------------>
        <div class="col-lg-5 col-md-6 d-none d-md-block right">

            <div class="pt-3 mb-4">
                <div class="align-items-center">
                    <h6 class="d-inline-block">{{ __('Enquiry number') }}: {{ $enquiry->code }}</h6>
                </div>

                <div class="checkout-success-products">
                    <div class="row show-cart-row d-md-none p-2">
                        <div class="col-9">
                            <a class="show-cart-link" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="{{ '#cart-item-' . $enquiry->id }}">
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
                                    <img class="item-thumb img-thumbnail img-rounded" src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name . '(' . $product->sku . ')'}}">
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
            <hr>
        </div>
    </div>
</div>
@stop