@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row g-3">
        <div class="col-md-4">
            <!-- Store Profile Overview -->
            <x-core::card class="shadow-sm">
                <x-core::card.header>
                    <h4 class="card-title text-dark fw-bold mb-0">
                        <x-core::icon name="ti ti-building-store" class="text-primary me-2" />
                        {{ $store->name }}
                    </h4>
                </x-core::card.header>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="text-secondary small d-block">{{ trans('plugins/shipmozo::shipmozo.warehouses.vendor_owner') }}:</span>
                            <span class="fw-medium">{{ $store->customer ? $store->customer->name : 'N/A' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary small d-block">{{ trans('plugins/shipmozo::shipmozo.warehouses.phone') }}:</span>
                            <span class="fw-medium">{{ $store->phone ?: 'N/A' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary small d-block">{{ trans('plugins/shipmozo::shipmozo.warehouses.email') }}:</span>
                            <span class="fw-medium">{{ $store->email ?: 'N/A' }}</span>
                        </li>
                        <li class="mb-2">
                            <span class="text-secondary small d-block">{{ trans('plugins/shipmozo::shipmozo.warehouses.store_address') }}:</span>
                            <span>{{ $store->address ?: 'N/A' }} (PIN: {{ $store->zip_code ?: 'N/A' }})</span>
                        </li>
                        <li>
                            <span class="text-secondary small d-block">{{ trans('plugins/shipmozo::shipmozo.warehouses.current_mapping') }}:</span>
                            @if ($store->warehouse_id)
                                <span class="badge bg-success-lt font-monospace">{{ $store->warehouse_id }}</span>
                            @else
                                <span class="badge bg-danger-lt">{{ trans('plugins/shipmozo::shipmozo.warehouses.not_mapped') }}</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </x-core::card>
        </div>

        <div class="col-md-8">
            <x-core::card class="shadow-sm">
                <x-core::card.header>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h4 class="card-title text-dark fw-bold mb-0">
                            <x-core::icon name="ti ti-edit" class="text-primary me-2" />
                            {{ trans('plugins/shipmozo::shipmozo.warehouses.edit_warehouse_title', ['store' => $store->name]) }}
                        </h4>
                        <a href="{{ route('marketplace.shipmozo-warehouses.index') }}" class="btn btn-outline-secondary btn-sm">
                            <x-core::icon name="ti ti-arrow-left" class="me-1" />
                            {{ trans('plugins/shipmozo::shipmozo.warehouses.back_to_list') }}
                        </a>
                    </div>
                </x-core::card.header>

                <div class="card-body">
                    <!-- Form 1: Create or Update Warehouse in ShipMozo API -->
                    <form action="{{ route('marketplace.shipmozo-warehouses.update', $store->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action_mode" value="details">

                        <h5 class="fw-bold mb-3 text-primary">
                            <x-core::icon name="ti ti-cloud-upload" class="me-1" />
                            {{ trans('plugins/shipmozo::shipmozo.warehouses.sync_api_title') }}
                        </h5>
                        <p class="small text-muted mb-3">{{ trans('plugins/shipmozo::shipmozo.warehouses.sync_api_desc') }}</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('plugins/shipmozo::shipmozo.warehouses.address_title') }}</label>
                                <input type="text" name="address_title" class="form-control" required
                                       value="{{ old('address_title', Arr::get($currentWarehouse, 'address_title', $store->name . ' Warehouse')) }}"
                                       placeholder="e.g. {{ $store->name }} Origin">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('plugins/shipmozo::shipmozo.warehouses.contact_name') }}</label>
                                <input type="text" name="contact_name" class="form-control" required
                                       value="{{ old('contact_name', Arr::get($currentWarehouse, 'name', $store->customer ? $store->customer->name : $store->name)) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required">{{ trans('plugins/shipmozo::shipmozo.warehouses.phone') }}</label>
                                <input type="text" name="phone" class="form-control" required
                                       value="{{ old('phone', Arr::get($currentWarehouse, 'phone', $store->phone)) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">{{ trans('plugins/shipmozo::shipmozo.warehouses.email') }}</label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', Arr::get($currentWarehouse, 'email', $store->email)) }}">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label required">{{ trans('plugins/shipmozo::shipmozo.warehouses.pickup_address') }}</label>
                                <input type="text" name="address" class="form-control" required
                                       value="{{ old('address', Arr::get($currentWarehouse, 'address_line_one', $store->address)) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">{{ trans('plugins/shipmozo::shipmozo.warehouses.pincode') }}</label>
                                <input type="text" name="pincode" maxlength="6" class="form-control" required
                                       value="{{ old('pincode', Arr::get($currentWarehouse, 'pincode', $store->zip_code)) }}">
                            </div>

                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <x-core::icon name="ti ti-check" class="me-1" />
                                    {{ trans('plugins/shipmozo::shipmozo.warehouses.save_and_sync_btn') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- Form 2: Link an existing warehouse from ShipMozo account -->
                    <form action="{{ route('marketplace.shipmozo-warehouses.update', $store->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action_mode" value="link">

                        <h5 class="fw-bold mb-2 text-secondary">
                            <x-core::icon name="ti ti-link" class="me-1" />
                            {{ trans('plugins/shipmozo::shipmozo.warehouses.link_existing_title') }}
                        </h5>
                        <p class="small text-muted mb-3">{{ trans('plugins/shipmozo::shipmozo.warehouses.link_existing_desc') }}</p>

                        <div class="row align-items-end g-3">
                            <div class="col-md-8">
                                <label class="form-label">{{ trans('plugins/shipmozo::shipmozo.select_a_warehouse') }}</label>
                                <select name="warehouse_id" class="form-select">
                                    <option value="">-- {{ trans('plugins/shipmozo::shipmozo.select_a_warehouse') }} --</option>
                                    @foreach ($apiWarehouses as $w)
                                        <option value="{{ $w['id'] }}" @selected((string)$store->warehouse_id === (string)$w['id'])>
                                            {{ Arr::get($w, 'address_title', Arr::get($w, 'name', 'Warehouse')) }} - PIN: {{ Arr::get($w, 'pincode') }} (ID: {{ $w['id'] }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-dark w-100">
                                    <x-core::icon name="ti ti-link" class="me-1" />
                                    {{ trans('plugins/shipmozo::shipmozo.warehouses.link_warehouse_btn') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </x-core::card>
        </div>
    </div>
@endsection
