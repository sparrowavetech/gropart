@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row" id="review-section-wrapper">
        <div class="col-md-7 mb-3 mb-md-0">
            <x-core::card>
                <x-core::card.header>
                    <h4 class="card-title d-flex justify-content-between align-items-center w-100">
                        @include('plugins/ecommerce::reviews.partials.rating', [
                            'star' => $review->star,
                        ])
                        {!! BaseHelper::clean($review->status->toHtml()) !!}
                    </h4>
                </x-core::card.header>
                <x-core::card.body>
                    <p class="card-text">
                        {{ $review->comment }}
                    </p>

                    <div class="d-flex justify-content-end">
                        <div class="btn-list">
                            <x-core::button
                                color="danger"
                                :outlined="true"
                                data-bb-toggle="review-delete"
                                data-target="{{ route('reviews.destroy', $review) }}"
                            >
                                {{ trans('plugins/ecommerce::review.delete') }}
                            </x-core::button>
                            @if ($review->status == Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                                <x-core::button
                                    color="warning"
                                    data-id="{{ $review->getKey() }}"
                                    data-bb-toggle="review-unpublish"
                                >{{ trans('plugins/ecommerce::review.unpublish') }}</x-core::button>
                            @else
                                <x-core::button
                                    color="primary"
                                    data-id="{{ $review->getKey() }}"
                                    data-bb-toggle="review-publish"
                                >{{ trans('plugins/ecommerce::review.publish') }}</x-core::button>
                            @endif
                        </div>
                    </div>
                </x-core::card.body>

                <x-core::card.footer>
                    {{ $review->user->name }}
                    (<a href="mailto:{{ $review->user->email }}">{{ $review->user->email }}</a>)
                </x-core::card.footer>
            </x-core::card>
        </div>
        <div class="col-md-5">
            <x-core::card>
                <x-core::card.header>
                    <h4 class="card-title">
                        {{ trans('plugins/ecommerce::review.product') }}
                    </h4>
                </x-core::card.header>

                <x-core::card.body>
                    <div class="d-flex gap-3 align-items-start">
                        <img
                            class="img-thumbnail"
                            src="{{ RvMedia::getImageUrl($review->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                            alt="{{ $review->product->name }}"
                            style="width: 15%"
                        >
                        <div>
                            <h5>
                                <a href="{{ route('products.edit', $review->product) }}">
                                    {{ $review->product->name }}
                                </a>
                            </h5>
                            <div>
                                @include('plugins/ecommerce::reviews.partials.rating', [
                                    'star' => $review->product->reviews_avg_star,
                                ])
                                <span>({{ number_format($review->product->reviews_count) }})</span>
                            </div>
                        </div>
                    </div>
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection

@push('footer')
    <x-core::modal.action
        type="danger"
        id="delete-review-modal"
        :title="trans('plugins/ecommerce::review.delete_modal.title')"
        :description="trans('plugins/ecommerce::review.delete_modal.description')"
        :submit-button-label="trans('plugins/ecommerce::review.delete')"
        :submit-button-attrs="['id' => 'confirm-delete-review-button']"
    />
@endpush
