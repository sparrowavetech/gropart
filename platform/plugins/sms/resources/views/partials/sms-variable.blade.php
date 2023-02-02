<table class="table">
    <thead>
        <tr>
            <td><strong> Name</strong></td>
            <td><strong>Key</strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Site Title</td>
            <td> &#123;&#123; site_title &#125;&#125;</td>
        </tr>
        <tr>
            <td>Site Url</td>
            <td> &#123;&#123; site_url &#125;&#125;</td>
        </tr>
        <tr>
            <td>Site Email</td>
            <td> &#123;&#123; site_admin_email &#125;&#125;</td>
        </tr>
        @foreach(config('plugins.sms.sms') as $key => $var)
        <tr>
            <td>{{ $var }}</td>
            <td> &#123;&#123; {{ $key }} &#125;&#125;</td>
        </tr>
        @endforeach
    </tbody>
</table>