@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <!-- Summary KPI Cards -->
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-lg-4">
                    <x-core::card class="shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-primary-lt text-primary me-3">
                                    <x-core::icon name="ti ti-building-store" />
                                </span>
                                <div>
                                    <div class="text-secondary small fw-medium">{{ trans('plugins/shipmozo::shipmozo.warehouses.total_stores') }}</div>
                                    <h3 class="mb-0 fw-bold">{{ $totalStores }}</h3>
                                </div>
                            </div>
                        </div>
                    </x-core::card>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <x-core::card class="shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-success-lt text-success me-3">
                                    <x-core::icon name="ti ti-building-warehouse" />
                                </span>
                                <div>
                                    <div class="text-secondary small fw-medium">{{ trans('plugins/shipmozo::shipmozo.warehouses.mapped_stores') }}</div>
                                    <h3 class="mb-0 fw-bold text-success">{{ $mappedStores }}</h3>
                                </div>
                            </div>
                        </div>
                    </x-core::card>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <x-core::card class="shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-md bg-warning-lt text-warning me-3">
                                    <x-core::icon name="ti ti-alert-triangle" />
                                </span>
                                <div>
                                    <div class="text-secondary small fw-medium">{{ trans('plugins/shipmozo::shipmozo.warehouses.unmapped_stores') }}</div>
                                    <h3 class="mb-0 fw-bold text-warning">{{ $unmappedStores }}</h3>
                                </div>
                            </div>
                        </div>
                    </x-core::card>
                </div>
            </div>

            <x-core::card class="shadow-sm">
                <x-core::card.header>
                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-2">
                        <div>
                            <h4 class="card-title text-dark fw-bold mb-0">
                                <x-core::icon name="ti ti-building-warehouse" class="text-primary me-2" />
                                {{ trans('plugins/shipmozo::shipmozo.warehouses.page_title') }}
                            </h4>
                            <div class="small text-muted">
                                {{ trans('plugins/shipmozo::shipmozo.warehouses.subtitle') }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <form action="{{ route('marketplace.shipmozo-warehouses.sync-all') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary" onclick="return confirm('{{ trans('plugins/shipmozo::shipmozo.warehouses.confirm_sync_all') }}')">
                                    <x-core::icon name="ti ti-refresh" class="me-1" />
                                    {{ trans('plugins/shipmozo::shipmozo.warehouses.sync_unmapped_btn') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </x-core::card.header>

                <div class="card-body border-bottom bg-light py-2 px-3">
                    <form method="GET" action="{{ route('marketplace.shipmozo-warehouses.index') }}" class="row align-items-center g-2">
                        <div class="col-md-5">
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <x-core::icon name="ti ti-search" />
                                </span>
                                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="{{ trans('plugins/shipmozo::shipmozo.warehouses.search_placeholder') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" @selected($statusFilter === 'all')>{{ trans('plugins/shipmozo::shipmozo.warehouses.filter_all') }} ({{ $totalStores }})</option>
                                <option value="mapped" @selected($statusFilter === 'mapped')>{{ trans('plugins/shipmozo::shipmozo.warehouses.filter_mapped') }} ({{ $mappedStores }})</option>
                                <option value="unmapped" @selected($statusFilter === 'unmapped')>{{ trans('plugins/shipmozo::shipmozo.warehouses.filter_unmapped') }} ({{ $unmappedStores }})</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <x-core::icon name="ti ti-filter" class="me-1" />
                                {{ trans('plugins/shipmozo::shipmozo.warehouses.filter_btn') }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped card-table">
                        <thead>
                            <tr>
                                <th style="width: 25%;">{{ trans('plugins/shipmozo::shipmozo.warehouses.col_store') }}</th>
                                <th style="width: 20%;">{{ trans('plugins/shipmozo::shipmozo.warehouses.col_warehouse_id') }}</th>
                                <th style="width: 25%;">{{ trans('plugins/shipmozo::shipmozo.warehouses.col_address') }}</th>
                                <th style="width: 15%;">{{ trans('plugins/shipmozo::shipmozo.warehouses.col_shipping_mode') }}</th>
                                <th class="text-end" style="width: 15%;">{{ trans('core/base::tables.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stores as $store)
                                @php
                                    $wId = $store->warehouse_id;
                                    $apiData = $wId ? ($apiWarehouses[(string) $wId] ?? null) : null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-md bg-light text-primary me-2 border">
                                                <x-core::icon name="ti ti-building-store" />
                                            </span>
                                            <div>
                                                <a href="{{ route('marketplace.store.edit', $store->id) }}" class="fw-bold text-decoration-none">
                                                    {{ $store->name }}
                                                </a>
                                                <div class="small text-muted">
                                                    @if ($store->customer)
                                                        {{ $store->customer->name }} ({{ $store->customer->email }})
                                                    @else
                                                        {{ $store->email ?: 'N/A' }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($wId)
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-success-lt font-monospace text-wrap">{{ $wId }}</span>
                                                @if ($apiData)
                                                    <span class="badge bg-green text-white" title="Verified in ShipMozo API">
                                                        <x-core::icon name="ti ti-check" />
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($apiData)
                                                <div class="small text-muted mt-1">
                                                    <strong>{{ Arr::get($apiData, 'address_title', Arr::get($apiData, 'name', '')) }}</strong>
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-danger-lt">
                                                <x-core::icon name="ti ti-x" class="me-1" />
                                                {{ trans('plugins/shipmozo::shipmozo.warehouses.not_mapped') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($apiData)
                                            <div class="small">
                                                <div>{{ Arr::get($apiData, 'address_line_one', Arr::get($apiData, 'address', '')) }}</div>
                                                <div class="text-muted">
                                                    <x-core::icon name="ti ti-map-pin" class="me-1" />{{ Arr::get($apiData, 'pincode', '') }}
                                                    @if (Arr::get($apiData, 'phone'))
                                                        <span class="mx-1">•</span>
                                                        <x-core::icon name="ti ti-phone" class="me-1" />{{ Arr::get($apiData, 'phone') }}
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="small text-muted">
                                                <div>{{ $store->address ?: trans('plugins/shipmozo::shipmozo.warehouses.no_address') }}</div>
                                                <div>PIN: {{ $store->zip_code ?: 'N/A' }}</div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($store->vendor_managed_shipping)
                                            <span class="badge bg-purple-lt" title="{{ trans('plugins/shipmozo::shipmozo.warehouses.self_shipping_hint') }}">
                                                <x-core::icon name="ti ti-package" class="me-1" />
                                                {{ trans('plugins/shipmozo::shipmozo.warehouses.self_shipping') }}
                                            </span>
                                        @else
                                            <span class="badge bg-azure-lt" title="{{ trans('plugins/shipmozo::shipmozo.warehouses.shipmozo_shipping_hint') }}">
                                                <x-core::icon name="ti ti-truck" class="me-1" />
                                                {{ trans('plugins/shipmozo::shipmozo.warehouses.shipmozo_shipping') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('marketplace.shipmozo-warehouses.edit', $store->id) }}" 
                                               class="btn btn-outline-primary" 
                                               title="{{ trans('core/base::forms.edit') }}">
                                                <x-core::icon name="ti ti-edit" />
                                            </a>
                                            @if ($wId)
                                                <form action="{{ route('marketplace.shipmozo-warehouses.destroy', $store->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('{{ trans('plugins/shipmozo::shipmozo.warehouses.confirm_unlink') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger" 
                                                            title="{{ trans('plugins/shipmozo::shipmozo.warehouses.unlink_btn') }}">
                                                        <x-core::icon name="ti ti-unlink" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <x-core::icon name="ti ti-building-warehouse" class="d-block mx-auto mb-2" style="font-size: 32px;" />
                                        {{ trans('plugins/shipmozo::shipmozo.warehouses.no_stores_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($stores->hasPages())
                    <div class="card-footer d-flex align-items-center justify-content-end py-2">
                        {{ $stores->links() }}
                    </div>
                @endif
            </x-core::card>
        </div>
    </div>
@endsection
