<script src="{{ asset('vendor/core/packages/theme/js/toast.js') }}"></script>

@if (
    session()->has('success_msg')
    || session()->has('error_msg')
    || (isset($errors) && $errors->count() > 0)
    || isset($error_msg)
)
    <script type="text/javascript">
        window.onload = function() {
            @if (session()->has('success_msg'))
                Theme.showSuccess('{{ BaseHelper::clean(addslashes(session('success_msg'))) }}');
            @endif

            @if (session()->has('error_msg'))
                Theme.showError('{{ BaseHelper::clean(addslashes(session('error_msg'))) }}');
            @endif

            @if (isset($error_msg))
                Theme.showError('{{ BaseHelper::clean(addslashes($error_msg)) }}');
            @endif

            @if (isset($errors))
                @foreach ($errors->all() as $error)
                    Theme.showError('{{ BaseHelper::clean(addslashes($error)) }}');
                @endforeach
            @endif
        };
    </script>
@endif
