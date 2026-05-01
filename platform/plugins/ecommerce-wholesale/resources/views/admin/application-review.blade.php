@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row row-cards">
        <div class="col-md-4">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('core/base::tables.status') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    <div class="mb-3">
                        {!! $application->status->toHtml() !!}
                    </div>

                    @if($application->reviewer)
                        <div class="mb-2">
                            <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.application.reviewed_by') }}:</strong>
                            {{ $application->reviewer->name }}
                        </div>
                    @endif

                    @if($application->reviewed_at)
                        <div class="mb-2">
                            <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.application.reviewed_at') }}:</strong>
                            {{ BaseHelper::formatDate($application->reviewed_at) }}
                        </div>
                    @endif

                    @if($application->assignedGroup)
                        <div class="mb-2">
                            <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.application.assigned_group') }}:</strong>
                            {{ $application->assignedGroup->name }}
                        </div>
                    @endif

                    @if($application->rejection_reason)
                        <div class="mb-2 text-danger">
                            <strong>{{ trans('plugins/ecommerce-wholesale::wholesale.application.rejection_reason') }}:</strong>
                            {{ $application->rejection_reason }}
                        </div>
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
        <div class="col-md-8">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/ecommerce-wholesale::wholesale.application.details') }}
                    </x-core::card.title>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::datagrid>
                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.name') }}</x-slot:title>
                            {{ $application->name }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.email') }}</x-slot:title>
                            {{ $application->email }}
                        </x-core::datagrid.item>

                        @if($application->phone)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.phone') }}</x-slot:title>
                                {{ $application->phone }}
                            </x-core::datagrid.item>
                        @endif

                        @if($application->company_name)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.company_name') }}</x-slot:title>
                                {{ $application->company_name }}
                            </x-core::datagrid.item>
                        @endif

                        @if($application->tax_id)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.tax_id') }}</x-slot:title>
                                {{ $application->tax_id }}
                            </x-core::datagrid.item>
                        @endif

                        @if($application->business_type)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.business_type') }}</x-slot:title>
                                {{ $application->business_type }}
                            </x-core::datagrid.item>
                        @endif

                        @if($application->expected_volume)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.expected_volume') }}</x-slot:title>
                                {{ $application->expected_volume }}
                            </x-core::datagrid.item>
                        @endif

                        <x-core::datagrid.item>
                            <x-slot:title>{{ trans('core/base::tables.created_at') }}</x-slot:title>
                            {{ BaseHelper::formatDateTime($application->created_at) }}
                        </x-core::datagrid.item>

                        @if($application->notes)
                            <x-core::datagrid.item>
                                <x-slot:title>{{ trans('plugins/ecommerce-wholesale::wholesale.application.notes') }}</x-slot:title>
                                {{ $application->notes }}
                            </x-core::datagrid.item>
                        @endif
                    </x-core::datagrid>
                </x-core::card.body>

                @if($application->isPending())
                    <x-core::card.footer class="text-end">
                        <x-core::button
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#reject-application-modal"
                            icon="ti ti-x"
                        >
                            {{ trans('plugins/ecommerce-wholesale::wholesale.application.reject') }}
                        </x-core::button>
                        <x-core::button
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#approve-application-modal"
                            color="primary"
                            icon="ti ti-check"
                        >
                            {{ trans('plugins/ecommerce-wholesale::wholesale.application.approve') }}
                        </x-core::button>
                    </x-core::card.footer>
                @endif
            </x-core::card>
        </div>
    </div>
@endsection

@if($application->isPending())
    @push('footer')
        <x-core::modal
            id="approve-application-modal"
            type="warning"
            :title="trans('plugins/ecommerce-wholesale::wholesale.application.approve_confirmation')"
            :form-action="route('wholesale.applications.approve', $application->id)"
            size="sm"
            :close-button="false"
            button-id="confirm-application-approve"
            :button-label="trans('plugins/ecommerce-wholesale::wholesale.application.approve')"
            button-class="btn-warning"
        >
            <div class="text-muted text-break mb-3">
                {!! trans('plugins/ecommerce-wholesale::wholesale.application.approve_confirmation_description', ['name' => e($application->name)]) !!}
            </div>
            <div class="mb-3 text-start">
                <label class="form-label required">{{ trans('plugins/ecommerce-wholesale::wholesale.customer_group.name') }}</label>
                <select name="customer_group_id" class="form-select" required>
                    <option value="">{{ trans('core/base::forms.select') }}</option>
                    @foreach($groups as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </x-core::modal>

        <x-core::modal
            id="reject-application-modal"
            type="danger"
            :title="trans('plugins/ecommerce-wholesale::wholesale.application.reject_confirmation')"
            :form-action="route('wholesale.applications.reject', $application->id)"
            size="sm"
            :close-button="false"
            button-id="confirm-application-reject"
            :button-label="trans('plugins/ecommerce-wholesale::wholesale.application.reject')"
            button-class="btn-danger"
        >
            <div class="text-muted text-break mb-3">
                {!! trans('plugins/ecommerce-wholesale::wholesale.application.reject_confirmation_description', ['name' => e($application->name)]) !!}
            </div>
            <div class="mb-3 text-start">
                <label class="form-label required">{{ trans('plugins/ecommerce-wholesale::wholesale.application.rejection_reason') }}</label>
                <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
            </div>
        </x-core::modal>

        <script>
            $(() => {
                $(document).on('click', '#confirm-application-approve, #confirm-application-reject', (event) => {
                    event.preventDefault()

                    const $button = $(event.currentTarget)
                    const $form = $button.closest('form')
                    const $modal = $button.closest('.modal')

                    $httpClient
                        .make()
                        .withButtonLoading($button)
                        .post($form.prop('action'), $form.serialize())
                        .then(({ data }) => {
                            $modal.modal('hide')

                            if (data.error) {
                                Botble.showError(data.message)
                            } else {
                                Botble.showSuccess(data.message)
                                setTimeout(() => {
                                    window.location.href = '{{ route('wholesale.applications.index') }}'
                                }, 3000)
                            }
                        })
                })
            })
        </script>
    @endpush
@endif
