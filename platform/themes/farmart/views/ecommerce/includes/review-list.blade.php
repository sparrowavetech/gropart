<div class="product-comments-list">
    @foreach ($reviews as $review)
        @continue(! $review->is_approved && auth('customer')->id() != $review->customer_id)

        <div @class(['comment-container row pb-2 mb-3 border-bottom', 'opacity-50' => ! $review->is_approved])>
            <div class="col-auto">
                <img
                    class="rounded-circle"
                    src="{{ $review->user->avatar_url }}"
                    alt="{{ $review->user->name }}"
                    width="60"
                >
            </div>
            <div class="col">
                <div class="meta">
                    <strong class="review__author">{{ $review->user->name }}</strong>
                    <span class="review__dash ms-1 me-1">–</span>
                    <time
                        class="review__published-date"
                        datetime="{{ $review->created_at->translatedFormat('Y-m-d\TH:i:sP') }}"
                    >{{ $review->created_at->diffForHumans() }}</time>
                    @if ($review->order_created_at)
                        <span class="ms-2">{{ __('✅ Purchased :time', ['time' => $review->order_created_at->diffForHumans()]) }}</span>
                    @endif
                    @if (! $review->is_approved)
                        <span class="text-warning ms-2">{{ __('Waiting for approval') }}</span>
                    @endif
                </div>
                {!! Theme::partial('star-rating', ['avg' => $review->star]) !!}
                <div class="description mt-2">
                    <p>{{ $review->comment }}</p>
                </div>

                @if ($review->images && count($review->images))
                    <div class="review-images">
                        @foreach ($review->images as $image)
                            <a href="{{ RvMedia::getImageUrl($image) }}">
                                <img
                                    class="img-fluid rounded h-100"
                                    src="{{ RvMedia::getImageUrl($image, 'thumb') }}"
                                    alt="{{ $review->comment }}"
                                >
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="pagination">
        {{ $reviews->links() }}
    </div>
</div>
