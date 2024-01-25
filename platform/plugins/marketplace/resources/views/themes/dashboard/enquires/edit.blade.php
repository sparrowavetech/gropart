@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('content')
<div class="max-width-1200" id="main-order-content">
    <div class="ui-layout">
        <div class="flexbox-layout-sections">
            @if ($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::REJECT)
            <div class="ui-layout__section">
                <div class="ui-layout__item">
                    <div class="ui-banner ui-banner--status-warning">
                        <div class="ui-banner__ribbon">
                            <svg class="svg-next-icon svg-next-icon-size-20">
                                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#alert-circle"></use>
                            </svg>
                        </div>
                        <div class="ui-banner__content">
                            <h2 class="ui-banner__title">{{ trans('_canceled') }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="flexbox-layout-section-secondary mt20">
            <div class="ui-layout__item">
                <div class="wrapper-content mb20">
                    <div class="next-card-section p-none-b">
                        <div class="flexbox-grid-default flexbox-align-items-center">
                            <div class="flexbox-auto-content-left">
                                <label class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::enquiry.information') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="next-card-section border-none-t">
                        <div class="mb5">
                            <strong class="text-capitalize">{{ $enquiry->name }}</strong>
                        </div>
                        <ul class="ws-nm text-infor-subdued">
                            <li class="overflow-ellipsis"><span><i class="fa fa-envelope cursor-pointer mr5"></i></span><a class="hover-underline" href="mailto:{{ $enquiry->email  }}">{{ $enquiry->email  }}</a>
                            </li>
                            <li class="overflow-ellipsis"><span><i class="fa fa-phone-square cursor-pointer mr5"></i></span><a class="hover-underline" href="tel:{{ $enquiry->phone  }}">{{ $enquiry->phone  }}</a></li>
                            <li class="overflow-ellipsis">Address : {{ $enquiry->address  }}</li>
                            <li class="overflow-ellipsis">City : {{ $enquiry->cityName->name  }}</li>
                            <li class="overflow-ellipsis">State : {{ $enquiry->stateName->name  }}</li>
                            <li class="overflow-ellipsis">Zipcode : {{ $enquiry->zip_code  }}</li>
                            <li class="overflow-ellipsis"><a href="{{ RvMedia::getImageUrl($enquiry->attachment, 'small', false, RvMedia::getDefaultImage()) }}" download=""><i class="fa fa-download"></i> Attechment</a></li>
                        </ul>
                    </div>
                </div>

                <div class="wrapper-content bg-gray-white mb20">
                    <div class="pd-all-20">
                        <div class="p-b10">
                            <strong>{{ trans('plugins/ecommerce::enquiry.status') }} </strong>
                        </div>
                        <br>
                        {!! $enquiry->status->toHtml() !!}
                    </div>
                </div>
                @if ($enquiry->product)
                <div class="wrapper-content bg-gray-white mb20">
                    <div class="pd-all-20">
                        <div class="p-b10">
                            <strong>{{ trans('plugins/ecommerce::products.product_name') }}</strong>
                            <ul class="p-sm-r mb-0">
                                <li class="ws-nm">
                                    <a href="{{ route('marketplace.vendor.products.edit', $enquiry->product_id) }}" class="ww-bw text-no-bold" target="_blank">{{ $enquiry->product->name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
                @if (is_plugin_active('marketplace') && $enquiry->product->store->name)
                <div class="wrapper-content bg-gray-white mb20">
                    <div class="pd-all-20">
                        <div class="p-b10">
                            <strong>{{ trans('plugins/marketplace::store.store') }}
                                @if($enquiry->product->store->is_verified)
                                    <img class="verified-store-main" style="width: 20px;" src="{{ asset('/storage/stores/verified.png')}}"alt="Verified">
                                @endif
                            </strong>
                            <small class="badge bg-warning text-dark">{{ $enquiry->product->store->shop_category->label() }}</small>
                            <ul class="p-sm-r mb-0">
                                <li class="ws-nm">
                                    <a href="{{ $enquiry->product->store->url }}" class="ww-bw text-no-bold" target="_blank">{{ $enquiry->product->store->name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@stop
