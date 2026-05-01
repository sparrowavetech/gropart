@php
    Assets::addScriptsDirectly('vendor/core/plugins/loyalty-points/js/member-adjustment.js');
@endphp

@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <div class="ui-layout">
            <div class="flexbox-layout-sections">
                <div class="flexbox-layout-section-primary mt-20">
                    <x-core::card class="mb-3">
                        <x-core::card.header>
                            <x-core::card.title>
                                <x-core::icon name="ti ti-user" />
                                {{ trans('plugins/loyalty-points::loyalty-points.members.details') }}
                            </x-core::card.title>
                        </x-core::card.header>
                        <x-core::card.body>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <x-core::datagrid>
                                        <x-core::datagrid.item class="mb-3">
                                            <x-slot:title>
                                                <x-core::icon name="ti ti-user" />
                                                {{ trans('plugins/ecommerce::customer.name') }}
                                            </x-slot:title>
                                            <span class="fw-semibold">{{ $member->customer?->name ?? '—' }}</span>
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item class="mb-3">
                                            <x-slot:title>
                                                <x-core::icon name="ti ti-mail" />
                                                {{ trans('plugins/ecommerce::customer.email') }}
                                            </x-slot:title>
                                            @if($member->customer?->email)
                                                <a href="mailto:{{ $member->customer->email }}" class="text-decoration-none">
                                                    {{ $member->customer->email }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </x-core::datagrid.item>

                                        @if($member->customer?->phone)
                                            <x-core::datagrid.item class="mb-3">
                                                <x-slot:title>
                                                    <x-core::icon name="ti ti-phone" />
                                                    {{ trans('plugins/ecommerce::customer.phone') }}
                                                </x-slot:title>
                                                <span>{{ $member->customer->phone }}</span>
                                            </x-core::datagrid.item>
                                        @endif
                                    </x-core::datagrid>
                                </div>

                                <div class="col-12 col-md-6">
                                    <x-core::datagrid>
                                        <x-core::datagrid.item class="mb-3">
                                            <x-slot:title>
                                                <x-core::icon name="ti ti-coins" />
                                                {{ trans('plugins/loyalty-points::loyalty-points.points.balance') }}
                                            </x-slot:title>
                                            <x-core::badge
                                                :color="$member->total_points > 0 ? 'success' : 'secondary'"
                                                :label="number_format($member->total_points)"
                                                class="fs-5"
                                            />
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item class="mb-3">
                                            <x-slot:title>
                                                <x-core::icon name="ti ti-chart-line" />
                                                {{ trans('plugins/loyalty-points::loyalty-points.points.lifetime') }}
                                            </x-slot:title>
                                            <span class="fw-bold">{{ number_format($member->lifetime_points) }}</span>
                                        </x-core::datagrid.item>

                                        <x-core::datagrid.item class="mb-3">
                                            <x-slot:title>
                                                <x-core::icon name="ti ti-award" />
                                                {{ trans('plugins/loyalty-points::loyalty-points.members.level') }}
                                            </x-slot:title>
                                            @if($member->level)
                                                {!! $member->level->toHtml() !!}
                                            @else
                                                <span class="badge bg-secondary text-white">{{ trans('plugins/loyalty-points::loyalty-points.levels.default_member') }}</span>
                                            @endif
                                        </x-core::datagrid.item>
                                    </x-core::datagrid>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-primary" data-bb-toggle="adjust-points">
                                    <x-core::icon name="ti ti-adjustments-horizontal" />
                                    {{ trans('plugins/loyalty-points::loyalty-points.adjustment.adjust_points') }}
                                </button>
                                <a href="{{ route('loyalty-points.members.index') }}" class="btn btn-secondary">
                                    <x-core::icon name="ti ti-arrow-left" />
                                    {{ trans('plugins/loyalty-points::loyalty-points.members.back') }}
                                </a>
                            </div>
                        </x-core::card.body>
                    </x-core::card>

                    <x-core::card>
                        <x-core::card.header>
                            <x-core::card.title>
                                <x-core::icon name="ti ti-history" />
                                {{ trans('plugins/loyalty-points::loyalty-points.transaction.history') }}
                            </x-core::card.title>
                        </x-core::card.header>
                        <x-core::card.body class="p-0">
                            {!! $transactionsTable->render('core/table::base-table') !!}
                        </x-core::card.body>
                    </x-core::card>
                </div>
            </div>
        </div>
    </div>

    <x-core::modal
        id="adjust-points-modal"
        :title="trans('plugins/loyalty-points::loyalty-points.adjustment.adjust_points_for', ['name' => $member->customer?->name ?? 'N/A'])"
        button-id="confirm-adjust-points-button"
        :button-label="trans('plugins/loyalty-points::loyalty-points.adjustment.adjust_points')"
    >
        <form data-action="{{ route('loyalty-points.members.adjust.store') }}">
            <input type="hidden" name="member_id" value="{{ $member->id }}">

            <div class="mb-3">
                <label class="form-label required" for="adjustment_type">
                    {{ trans('plugins/loyalty-points::loyalty-points.adjustment.type') }}
                </label>
                <select class="form-select" name="adjustment_type" id="adjustment_type" required>
                    <option value="add">{{ trans('plugins/loyalty-points::loyalty-points.adjustment.add_points') }}</option>
                    <option value="deduct">{{ trans('plugins/loyalty-points::loyalty-points.adjustment.deduct_points') }}</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label required" for="points">
                    {{ trans('plugins/loyalty-points::loyalty-points.adjustment.points') }}
                </label>
                <input type="number" class="form-control" name="points" id="points" min="1" step="1" required>
                <div class="form-hint">{{ trans('plugins/loyalty-points::loyalty-points.adjustment.points_help') }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label required" for="note">
                    {{ trans('plugins/loyalty-points::loyalty-points.adjustment.note') }}
                </label>
                <textarea class="form-control" name="note" id="note" rows="3" required></textarea>
                <div class="form-hint">{{ trans('plugins/loyalty-points::loyalty-points.adjustment.note_help') }}</div>
            </div>
        </form>
    </x-core::modal>
@endsection
