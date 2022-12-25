<table class="table">
    <thead>
        <tr>
            <td><strong> Name</strong></td>
            <td><strong>Key</strong></td>
        </tr>
    </thead>
    <tbody>
        @foreach(config('plugins.sms.sms') as $key => $var)
        <tr>
            <td>{{ $var }}</td>
            <td> &#123;&#123; {{ $key }} &#125;&#125;</td>
        </tr>
        @endforeach
    </tbody>
</table>
