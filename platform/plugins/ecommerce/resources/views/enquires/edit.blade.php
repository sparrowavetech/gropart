@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div id="main-order-content">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-title">
                Enquiry information #GPO-10000326
            </div>
            <div class="card-body">
                @if ($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::REJECT)
                @php
                $class= "danger";
                @endphp
                @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::CONTACTED)
                @php
                $class= "success";
                @endphp
                @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::NOTAVAILABLE)
                @php
                $class= "warning";
                @endphp
                @else
                @php
                $class= "secondary";
                @endphp
                @endif
                <span class="badge bg-{{$class}} text-{{$class}}-fg d-flex align-items-center gap-1">

                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M4 19a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M11.5 17h-5.5v-14h-2"></path>
                        <path d="M6 5l14 1l-1 7h-13"></path>
                        <path d="M15 19l2 2l4 -4"></path>
                    </svg> {{ $enquiry->status->label() }}
                </span>

                <div class="row">
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
                                <li class="overflow-ellipsis"><span><i class="fa fa-address-book cursor-pointer mr5"></i></span>Address : {{ $enquiry->address  }}</li>
                                <li class="overflow-ellipsis"><span><i class="fa fa-map-marker cursor-pointer mr5"></i></span>City : {{ $enquiry->cityName->name  }}</li>
                                <li class="overflow-ellipsis"><span><i class="fa fa-globe cursor-pointer mr5"></i></span>State : {{ $enquiry->stateName->name  }}</li>
                                <li class="overflow-ellipsis"><span><i class="fa fa-code cursor-pointer mr5"></i></span>Zipcode : {{ $enquiry->zip_code  }}</li>
                                <li class="overflow-ellipsis"><span><i class="fa fa-paperclip cursor-pointer mr5"></i></span><a href="{{ RvMedia::getImageUrl($enquiry->attachment, 'small', false, RvMedia::getDefaultImage()) }}" download=""><i class="fa fa-download"></i> Attechment</a></li>
                                <li class="overflow-ellipsis"></li>
                                <li class="overflow-ellipsis">
                                    <a href="{{ route('enquires.contacted',$enquiry->id) }}" class="btn btn-success">{{ trans('plugins/ecommerce::enquiry.statuses.contacted') }}</a>
                                    <a href="{{ route('enquires.not_available',$enquiry->id) }}" class="btn btn-warning">{{ trans('plugins/ecommerce::enquiry.statuses.not_available') }}</a>
                                    <a href="{{ route('enquires.rejected',$enquiry->id) }}" class="btn btn-danger">{{ trans('plugins/ecommerce::enquiry.statuses.rejected') }}</a>

                                </li>
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
                                        <a href="{{ route('products.edit', $enquiry->product_id) }}" class="ww-bw text-no-bold" target="_blank">{{ $enquiry->product->name }}</a>
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
                                <strong>{{ trans('plugins/marketplace::store.store') }}</strong>
                                <ul class="p-sm-r mb-0">
                                    <li class="ws-nm">
                                        <a href="{{ $enquiry->product->store->url }}" class="ww-bw text-no-bold" target="_blank">{{ $enquiry->product->store->name }}</a>
                                        @if($enquiry->product->store->is_verified)
                                        <img class="verified-store-main" style="width: 20px;" src="{{ asset('/storage/stores/verified.png')}}" alt="Verified">
                                        @endif
                                        <small class="badge bg-warning text-white">{{ $enquiry->product->store->shop_category->label() }}</small>
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
@endsection