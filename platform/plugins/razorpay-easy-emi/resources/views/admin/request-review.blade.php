@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
@php
    $noCostOffers = $cachedOffers->filter(fn ($o) => $o->isNoCostEmi());
    $lowCostOffers = $cachedOffers->filter(fn ($o) => $o->isLowCostEmi() && ! $o->isNoCostEmi());
    $emiCashbackOffers = $cachedOffers->filter(fn ($o) => $o->isEmiApplicable() && ! $o->isNoCostEmi() && ! $o->isLowCostEmi());
@endphp
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0 fw-bold">
                        <i class="ti ti-credit-card text-primary me-2"></i>
                        {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.request_details') }} #{{ $requestItem->id }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.product') }}</th>
                                    <td>
                                        @if ($requestItem->product)
                                            <a href="{{ route('products.edit', $requestItem->product->id) }}" target="_blank" class="fw-bold">
                                                {{ $requestItem->product->name }}
                                                <i class="ti ti-external-link ms-1 small"></i>
                                            </a>
                                            <div class="small text-muted mt-1">
                                                Price: <strong>{{ format_price($requestItem->product->front_sale_price_with_taxes ?: $requestItem->product->price) }}</strong>
                                            </div>
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.store') }}</th>
                                    <td>
                                        @if ($requestItem->store)
                                            <span class="fw-semibold">{{ $requestItem->store->name }}</span>
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.scheme') }}</th>
                                    <td>{!! $requestItem->requested_scheme?->toHtml() !!}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.subvention') }}</th>
                                    <td>{!! $requestItem->subvention_willingness?->toHtml() !!}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('core/base::tables.status') }}</th>
                                    <td>{!! $requestItem->status?->toHtml() !!}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.notes') }}</th>
                                    <td>{{ $requestItem->notes ?: trans('core/base::base.no_data') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('core/base::tables.created_at') }}</th>
                                    <td>{{ $requestItem->created_at->toDayDateTimeString() }}</td>
                                </tr>
                                @if ($requestItem->reviewed_at)
                                    <tr>
                                        <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.reviewed_by') }}</th>
                                        <td>
                                            {{ $requestItem->reviewer?->name ?? 'Admin' }} ({{ $requestItem->reviewed_at->toDayDateTimeString() }})
                                        </td>
                                    </tr>
                                    @if ($requestItem->admin_remarks)
                                        <tr>
                                            <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.admin_remarks') }}</th>
                                            <td>{{ $requestItem->admin_remarks }}</td>
                                        </tr>
                                    @endif
                                    @if ($requestItem->approved_offer_id)
                                        <tr>
                                            <th>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.offer_id') }}</th>
                                            <td><span class="badge bg-success">{{ $requestItem->approved_offer_id }}</span></td>
                                        </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @if ($requestItem->status->isPending())
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.take_action') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="form-review-action">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="offer_id" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_label') }}</label>
                                <select class="form-select" name="offer_id" id="offer_id">
                                    <option value="">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.no_offer') }}</option>

                                    @if ($noCostOffers->isNotEmpty())
                                        <optgroup label="⭐ {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.group_no_cost') }}">
                                            @foreach ($noCostOffers as $offer)
                                                <option value="{{ $offer->id }}">
                                                    {{ $offer->clean_title }} [{{ $offer->bank_issuer }}]
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif

                                    @if ($lowCostOffers->isNotEmpty())
                                        <optgroup label="📉 {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.group_low_cost') }}">
                                            @foreach ($lowCostOffers as $offer)
                                                <option value="{{ $offer->id }}">
                                                    {{ $offer->clean_title }} [{{ $offer->bank_issuer }}]
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif

                                    @if ($emiCashbackOffers->isNotEmpty())
                                        <optgroup label="💳 {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.group_emi_cashback') }}">
                                            @foreach ($emiCashbackOffers as $offer)
                                                <option value="{{ $offer->id }}">
                                                    {{ $offer->clean_title }} [{{ $offer->bank_issuer }}]
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <small class="text-muted">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.metabox.offer_help') }}</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="admin_remarks" class="form-label fw-bold">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.tables.admin_remarks') }}</label>
                                <textarea class="form-control" name="admin_remarks" id="admin_remarks" rows="3" placeholder="{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.remarks_placeholder') }}"></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" formaction="{{ route('razorpay-easy-emi.requests.approve', $requestItem->id) }}" class="btn btn-success d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="ti ti-check"></i>
                                    <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.actions.approve') }}</span>
                                </button>
                                <button type="submit" formaction="{{ route('razorpay-easy-emi.requests.reject', $requestItem->id) }}" class="btn btn-outline-danger d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="ti ti-x"></i>
                                    <span>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.actions.reject') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-4">
                        <i class="ti ti-circle-check text-success fs-1 mb-2 d-block"></i>
                        <h5>{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.request_processed') }}</h5>
                        <p class="text-muted small mb-3">{{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.request_processed_desc') }}</p>
                        <a href="{{ route('razorpay-easy-emi.requests.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i> {{ trans('plugins/razorpay-easy-emi::razorpay-easy-emi.review.back_to_list') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

