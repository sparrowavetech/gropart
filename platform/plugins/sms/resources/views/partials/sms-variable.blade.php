@php
    $variables = config('plugins.sms.sms.variables', []);
    $templateVariables = config('plugins.sms.sms.template_variables', []);
    $coreVariables = [
        'site_title' => 'Site Title',
        'site_url' => 'Site Url',
        'site_admin_email' => 'Site Email',
    ];
@endphp

<table class="table">
    <thead>
        <tr>
            <td><strong> Name</strong></td>
            <td><strong>Key</strong></td>
        </tr>
    </thead>
    <tbody>
        @foreach($coreVariables as $key => $var)
        <tr data-sms-variable-row data-sms-core-variable="1">
            <td>{{ $var }}</td>
            <td> &#123;&#123; {{ $key }} &#125;&#125;</td>
        </tr>
        @endforeach
        @foreach($variables as $key => $var)
        <tr data-sms-variable-row data-sms-variable="{{ $key }}">
            <td>{{ $var }}</td>
            <td> &#123;&#123; {{ $key }} &#125;&#125;</td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    window.BotbleSmsTemplateVariables = @json($templateVariables);
    window.BotbleSmsSelectedTemplate = @json($selectedTemplate ?? null);

    (function () {
        var lastSelectedTemplate = null;

        function getSelectedTemplate() {
            var field = document.querySelector('select[name="name"], [name="name"]');

            return field && field.value ? field.value : window.BotbleSmsSelectedTemplate;
        }

        function updateSmsVariables() {
            var selected = getSelectedTemplate();
            var allowed = window.BotbleSmsTemplateVariables[selected] || [];

            document.querySelectorAll('[data-sms-variable-row]').forEach(function (row) {
                var variable = row.getAttribute('data-sms-variable');

                row.style.display = !variable || !selected || allowed.indexOf(variable) !== -1 ? '' : 'none';
            });

            lastSelectedTemplate = selected;
        }

        document.addEventListener('change', function (event) {
            if (event.target && event.target.name === 'name') {
                updateSmsVariables();
            }
        });

        if (window.jQuery) {
            window.jQuery(document).on('change select2:select', 'select[name="name"], [name="name"]', updateSmsVariables);
        }

        updateSmsVariables();
        setTimeout(updateSmsVariables, 300);
        setInterval(function () {
            if (getSelectedTemplate() !== lastSelectedTemplate) {
                updateSmsVariables();
            }
        }, 500);
    })();
</script>
