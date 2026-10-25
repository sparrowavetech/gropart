@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-12">
            <x-core::card>
                <x-core::card.header>
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div>
                            <h4 class="card-title text-dark fw-bold mb-1">
                                <x-core::icon name="ti ti-file-text" class="text-primary me-2" />
                                {{ trans('plugins/shipmozo::shipmozo.logs.page_title') }}
                            </h4>
                            <div class="small text-muted">
                                {{ trans('plugins/shipmozo::shipmozo.logs.retention_notice') }}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ Route::has('ecommerce.settings.shipping') ? route('ecommerce.settings.shipping') : url('admin/ecommerce/settings/shipping') }}" class="btn btn-outline-secondary">
                                <x-core::icon name="ti ti-arrow-left" class="me-1" />
                                {{ trans('plugins/shipmozo::shipmozo.back_to_settings') }}
                            </a>
                        </div>
                    </div>
                </x-core::card.header>

                <div class="table-responsive">
                    <table class="table table-hover table-striped card-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">{{ trans('plugins/shipmozo::shipmozo.logs.filename') }}</th>
                                <th style="width: 15%;">{{ trans('plugins/shipmozo::shipmozo.logs.size') }}</th>
                                <th style="width: 20%;">{{ trans('plugins/shipmozo::shipmozo.logs.log_date') }}</th>
                                <th style="width: 20%;">{{ trans('plugins/shipmozo::shipmozo.logs.last_modified') }}</th>
                                <th class="text-end" style="width: 10%;">{{ trans('core/base::tables.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm bg-primary-lt text-primary me-2">
                                                <x-core::icon name="ti ti-file-description" />
                                            </span>
                                            <div>
                                                <a href="{{ route('ecommerce.shipments.shipmozo.logs.show', $log['file']) }}" class="fw-bold text-decoration-none">
                                                    {{ $log['file'] }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-lt font-monospace">{{ $log['size'] }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $log['created_at']->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $log['updated_at']->format('d M Y, h:i A') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ecommerce.shipments.shipmozo.logs.show', $log['file']) }}" 
                                               class="btn btn-outline-primary" 
                                               title="{{ trans('plugins/shipmozo::shipmozo.logs.view') }}">
                                                <x-core::icon name="ti ti-eye" />
                                            </a>
                                            <a href="{{ route('ecommerce.shipments.shipmozo.logs.download', $log['file']) }}" 
                                               class="btn btn-outline-secondary" 
                                               title="{{ trans('plugins/shipmozo::shipmozo.logs.download') }}">
                                                <x-core::icon name="ti ti-download" />
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger delete-log-btn" 
                                                    data-url="{{ route('ecommerce.shipments.shipmozo.logs.destroy', $log['file']) }}"
                                                    data-file="{{ $log['file'] }}"
                                                    title="{{ trans('plugins/shipmozo::shipmozo.logs.delete') }}">
                                                <x-core::icon name="ti ti-trash" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <x-core::icon name="ti ti-folder-off" class="d-block mx-auto mb-2" style="font-size: 32px;" />
                                        {{ trans('plugins/shipmozo::shipmozo.logs.no_logs_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-core::card>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-log-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-url');
                    const file = this.getAttribute('data-file');
                    
                    if (confirm("{{ trans('plugins/shipmozo::shipmozo.logs.confirm_delete') }}".replace(':file', file))) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        form.innerHTML = `
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
