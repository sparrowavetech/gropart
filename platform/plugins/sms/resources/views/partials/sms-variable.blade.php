<p>
    @foreach(config('plugins.sms.sms') as $var)
    <b>  {{ $var }},</b>&nbsp;
    @endforeach
</p>