@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div id="main-order-content">
        <div class="row row-cards">
            <div class="col-md-12">
                <x-core::card class="mb-3">
                    <x-core::card.header class="justify-content-between">
                        <x-core::card.title>
                            {{ trans('plugins/ecommerce::enquiry.enquiry_information') }} {{ $enquiry->code }}
                        </x-core::card.title>
                        @if ($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::REJECT)
                            <x-core::badge color="danger" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-circle-x"></x-core::icon>
                                {{ trans('plugins/ecommerce::enquiry.statuses.rejected') }}
                            </x-core::badge>
                        @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::CONTACTED)
                            <x-core::badge color="success" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-circle-check"></x-core::icon>
                                {{ trans('plugins/ecommerce::enquiry.statuses.contacted') }}
                            </x-core::badge>
                        @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::NOTAVAILABLE)
                            <x-core::badge color="warning" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-circle-off"></x-core::icon>
                                {{ trans('plugins/ecommerce::enquiry.statuses.not_available') }}
                            </x-core::badge>
                        @else
                            <x-core::badge color="info" class="d-flex align-items-center gap-1">
                                <x-core::icon name="ti ti-alert-circle"></x-core::icon>
                                {{ trans('plugins/ecommerce::enquiry.statuses.pending') }}
                            </x-core::badge>
                        @endif
                    </x-core::card.header>
                    <x-core::card.body>
                        <x-core::table :hover="false" :striped="false" class="table-borderless">
                            <x-core::table.body>
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.customer_name') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <x-core::icon name="ti ti-user" /> {{ $enquiry->name }}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.customer_email') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <a class="hover-underline" href="mailto:{{ $enquiry->email  }}"><x-core::icon name="ti ti-mail" /> {{ $enquiry->email  }}</a>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.customer_phone') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <a class="hover-underline" href="tel:{{ $enquiry->phone  }}"><x-core::icon name="ti ti-phone" /> {{ $enquiry->phone  }}</a>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.customer_address') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <address class="mb-0">{{ $enquiry->address.', '.$enquiry->cityName->name.' - '.$enquiry->zip_code.', '.$enquiry->stateName->name }}</address>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.status') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        @if ($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::REJECT)
                                            <x-core::badge color="danger">
                                                <x-core::icon name="ti ti-circle-x"></x-core::icon>
                                                {{ trans('plugins/ecommerce::enquiry.statuses.rejected') }}
                                            </x-core::badge>
                                        @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::CONTACTED)
                                            <x-core::badge color="success">
                                                <x-core::icon name="ti ti-circle-check"></x-core::icon>
                                                {{ trans('plugins/ecommerce::enquiry.statuses.contacted') }}
                                            </x-core::badge>
                                        @elseif($enquiry->status == \Botble\Ecommerce\Enums\EnquiryStatusEnum::NOTAVAILABLE)
                                            <x-core::badge color="warning">
                                                <x-core::icon name="ti ti-circle-off"></x-core::icon>
                                                {{ trans('plugins/ecommerce::enquiry.statuses.not_available') }}
                                            </x-core::badge>
                                        @else
                                            <x-core::badge color="info">
                                                <x-core::icon name="ti ti-alert-circle"></x-core::icon>
                                                {{ trans('plugins/ecommerce::enquiry.statuses.pending') }}
                                            </x-core::badge>
                                        @endif
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                @if(RvMedia::getImageUrl($enquiry->attachment))
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <strong>{{ trans('plugins/ecommerce::enquiry.customer_attachment') }}</strong>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <a href="{{ RvMedia::getImageUrl($enquiry->attachment, 'small', false, RvMedia::getDefaultImage()) }}" download=""><x-core::icon name="ti ti-paperclip" /> {{ trans('plugins/ecommerce::enquiry.download_attachment') }}</a>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                                @endif
                                @if ($enquiry->product)
                                    <x-core::table.body.row>
                                        <x-core::table.body.cell>
                                            <strong>{{ trans('plugins/ecommerce::enquiry.customer_product') }}</strong>
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            <a href="{{ route('products.edit', $enquiry->product_id) }}" class="ww-bw text-no-bold" target="_blank"><x-core::icon name="ti ti-external-link" /> {{ $enquiry->product->name }}</a>
                                        </x-core::table.body.cell>
                                    </x-core::table.body.row>
                                @endif
                                @if (is_plugin_active('marketplace') && $enquiry->product->store->name)
                                    <x-core::table.body.row>
                                        <x-core::table.body.cell>
                                            <strong>{{ trans('plugins/ecommerce::enquiry.customer_store') }}</strong>
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            <a href="{{ $enquiry->product->store->url }}" class="ww-bw text-no-bold" target="_blank"><x-core::icon name="ti ti-external-link" /> {{ $enquiry->product->store->name }}</a>
                                            @if($enquiry->product->store->is_verified)
                                                <img class="verified-store-main" style="width: 20px;" src="{{ asset('/storage/stores/verified.png')}}" alt="Verified">
                                            @endif
                                            <small class="badge bg-warning text-white">{{ $enquiry->product->store->shop_category->label() }}</small>
                                        </x-core::table.body.cell>
                                    </x-core::table.body.row>
                                @endif
                            </x-core::table.body>
                        </x-core::table>
                    </x-core::card.body>
                    <x-core::card.footer>
                        <div class="btn-list">
                            <x-core::button tag="a" icon="ti ti-circle-check" class="btn btn-success" :href="route('enquires.contacted', ['id' => $enquiry->id])">
                                {{ trans('plugins/ecommerce::enquiry.statuses.contacted') }}
                            </x-core::button>
                            <x-core::button tag="a" icon="ti ti-circle-off" class="btn btn-warning" :href="route('enquires.not_available', ['id' => $enquiry->id])">
                                {{ trans('plugins/ecommerce::enquiry.statuses.not_available') }}
                            </x-core::button>
                            <x-core::button tag="a" icon="ti ti-circle-x" class="btn btn-danger" :href="route('enquires.rejected', ['id' => $enquiry->id])">
                                {{ trans('plugins/ecommerce::enquiry.statuses.reject') }}
                            </x-core::button>
                        </div>
                    </x-core::card.footer>
                </x-core::card>
            </div>
        </div>
    </div>
@endsection
