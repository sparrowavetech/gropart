@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <plugin-list></plugins-list>
@endsection

@push('footer')
    <script>
        window.trans = {{ Js::from([
            'base' => trans('packages/plugin-management::marketplace'),
        ]) }};

        window.marketplace = {
            route: {
                list: "{{ route('plugins.marketplace.ajax.list') }}",
                detail: "{{ route('plugins.marketplace.ajax.detail', [':id']) }}",
                install: "{{ route('plugins.marketplace.ajax.install', [':id']) }}",
                active: "{{ route('plugins.change.status') }}"
            },
            installed: {{ Js::from(get_installed_plugins()) }},
            activated: {{ Js::from(get_active_plugins()) }},
            token: "{{ csrf_token() }}",
            coreVersion: "{{ get_cms_version() }}"
        };
    </script>
@endpush
