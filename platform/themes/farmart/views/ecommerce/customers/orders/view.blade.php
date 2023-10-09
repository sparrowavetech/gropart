@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    @php Theme::set('pageName', __('Order information')) @endphp
    <div class="card">
        <div class="card-header">
            <h3>{{ __('Order information') }}</h3>
        </div>
        <div class="card-body">
            <div class="customer-order-detail">
                @include('plugins/ecommerce::themes.includes.order-tracking-detail')
                <br>
                <div>
                    @if ($order->isInvoiceAvailable())
                        <a
                            class="btn btn-lg btn-secondary me-2"
                            href="{{ route('customer.print-order', $order->id) }}"
                        >
                            <i class="fa fa-download"></i> {{ __('Download invoice') }}
                        </a>
                    @endif

                    @if ($order->canBeCanceled())
                        <a
                            class="btn btn-lg btn-danger"
                            href="{{ route('customer.orders.cancel', $order->id) }}"
                            onclick="return confirm('{{ __('Are you sure?') }}')"
                        >
                            {{ __('Cancel order') }}
                        </a>
                    @endif

                    @if ($order->canBeReturned())
                        <a
                            class="btn btn-lg btn-danger"
                            href="{{ route('customer.order_returns.request_view', $order->id) }}"
                        >
                            {{ __('Return Product(s)') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
