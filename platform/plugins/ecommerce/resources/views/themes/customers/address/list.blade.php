@extends(EcommerceHelper::viewPath('customers.master'))

@section('content')
    <h2 class="customer-page-title">
        {{ __('Address books') }}</h2>
    <div class="dashboard-address">
        @if ($addresses->isNotEmpty())
            <div class="row row-cols-md-2 row-cols-1 gx-2 mb-3">
                @foreach ($addresses as $address)
                    <div class="col mt-3">
                        <div class="card mb-3 p-3">
                            <p>
                                {{ $address->name }}
                                @if ($address->is_default)
                                    <x-core::badge color="info">{{ __('Default') }}</x-core::badge>
                                @endif
                            </p>
                            <p><x-core::icon name="ti ti-book" class="me-1" /> {{ $address->full_address }}
                            </p>
                            <p><x-core::icon name="ti ti-phone" class="me-1" />{{ $address->phone }}</p>
                            <p class="mt-3 text-end mb-0">
                                <a
                                    class="text-info"
                                    href="{{ route('customer.address.edit', $address->id) }}"
                                >{{ __('Edit') }}</a> |
                                <a
                                    class="text-danger btn-trigger-delete-address"
                                    data-url="{{ route('customer.address.destroy', $address->id) }}"
                                    href="#"
                                >{{ __('Remove') }}</a>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <a
            class="btn btn-primary"
            href="{{ route('customer.address.create') }}"
        >
            <x-core::icon name="ti ti-plus" class="me-1" />
            <span>{{ __('Add a new address') }}</span>
        </a>
    </div>

    <div
        class="modal fade"
        id="confirm-delete-modal"
        role="dialog"
        aria-hidden="true"
        tabindex="-1"
    >
        <div class="modal-dialog modal-xs">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><strong>{{ __('Confirm delete') }}</strong></h4>
                    <button
                        class="btn-close"
                        data-bs-dismiss="modal"
                        type="button"
                    ></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Do you really want to delete this address?') }}</p>
                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        type="button"
                    >{{ __('Cancel') }}</button>
                    <button
                        class="btn btn-danger btn-confirm-delete"
                        type="submit"
                    >{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div><!-- /.modal -->
@endsection
