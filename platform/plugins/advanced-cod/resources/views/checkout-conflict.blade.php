<div class="alert alert-warning mt-3 mb-3">
    <p>
        <strong>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px; margin-right: 4px;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
            {{ trans('plugins/advanced-cod::advanced-cod.conflict_title') }}
        </strong>
    </p>
    <p>{!! trans('plugins/advanced-cod::advanced-cod.conflict_body') !!}</p>
    @if(!empty($ineligibleProducts))
    <ul class="mb-2">
        @foreach($ineligibleProducts as $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
    @endif
    <div class="d-flex gap-2">
        <a href="{{ route('public.cart') }}" class="btn btn-sm btn-danger">
            {{ trans('plugins/advanced-cod::advanced-cod.remove') }}
        </a>
        <span class="text-muted mt-1">{{ trans('plugins/advanced-cod::advanced-cod.choose_prepaid_for_all') }}</span>
    </div>
</div>
