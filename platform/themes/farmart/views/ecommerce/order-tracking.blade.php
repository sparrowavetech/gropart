<div class="row py-5 mt-3 justify-content-center">
    <div class="col-md-9 col-lg-6">
        <div class="bg-light py-5 px-5">
            <h2 class="h6 fw-normal mb-3">{{ __('Tracking your order status') }}</h2>
            <form
                class="mt-3"
                method="GET"
                action="{{ route('public.orders.tracking') }}"
            >
                <div class="mb-3">
                    <input
                        class="form-control"
                        name="order_id"
                        type="text"
                        value="{{ old('order_id', request()->input('order_id')) }}"
                        required
                        placeholder="{{ __('Order ID') }} (*)"
                    >
                </div>
                <div class="mb-3">
                    <input
                        class="form-control"
                        name="email"
                        type="text"
                        value="{{ old('email', request()->input('email')) }}"
                        required
                        placeholder="{{ __('Email Address') }} (*)"
                        autocomplete="email"
                    >
                </div>
                <div class="d-grid">
                    <button
                        class="btn btn-primary"
                        type="submit"
                    >{{ __('Find') }}</button>
                </div>
            </form>
            <div class="mt-5">
                @include('plugins/ecommerce::themes.includes.order-tracking-detail')
            </div>
        </div>
    </div>
</div>
