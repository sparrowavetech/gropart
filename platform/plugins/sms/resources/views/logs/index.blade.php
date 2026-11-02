@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::card>
        <x-core::card.header>
            <div>
                <h3 class="card-title mb-1">{{ trans('plugins/sms::sms.logs.title') }}</h3>
                <div class="text-muted small">{{ trans('plugins/sms::sms.logs.description') }}</div>
            </div>
        </x-core::card.header>

        <div class="table-responsive">
            <table class="table table-hover card-table mb-0">
                <thead>
                    <tr>
                        <th>{{ trans('plugins/sms::sms.logs.file') }}</th>
                        <th>{{ trans('plugins/sms::sms.logs.size') }}</th>
                        <th>{{ trans('plugins/sms::sms.logs.modified') }}</th>
                        <th class="text-end">{{ trans('core/base::tables.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>
                                <a class="fw-semibold" href="{{ route('sms.logs.show', $log['file']) }}">
                                    <x-core::icon name="ti ti-file-text" class="me-2 text-primary" />{{ $log['file'] }}
                                </a>
                            </td>
                            <td class="text-muted font-monospace">{{ $log['size'] }}</td>
                            <td class="text-muted">{{ $log['modified']->format('d M Y, h:i A') }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('sms.logs.show', $log['file']) }}">
                                    <x-core::icon name="ti ti-eye" class="me-1" />{{ trans('plugins/sms::sms.logs.view') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">{{ trans('plugins/sms::sms.logs.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-core::card>
@endsection
