@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <section class="rp-card-report-statics">
        <div class="mb-1 text-end">
            <button
                class="select-date-range-btn date-range-picker"
                data-format-value="{{ trans('plugins/ecommerce::reports.date_range_format_value', ['from' => '__from__', 'to' => '__to__']) }}"
                data-format="{{ Str::upper(config('core.base.general.date_format.js.date')) }}"
                data-href="{{ route('marketplace.reports.index') }}"
                data-start-date="{{ $startDate }}"
                data-end-date="{{ $endDate }}"
            >
                <i class="fa fa-calendar me-1"></i>
                <span>
                    <span>{{ trans('plugins/ecommerce::reports.date_range_format_value', [
                        'from' => BaseHelper::formatDate($startDate),
                        'to' => BaseHelper::formatDate($endDate),
                    ]) }}</span>
                </span>
            </button>
        </div>

        <div>
            {!! $widget->render(MARKETPLACE_MODULE_SCREEN_NAME) !!}
        </div>
    </section>
@stop

@push('footer')
    <script>
        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = BotbleVariables.languages || {};
        BotbleVariables.languages.reports = {!! json_encode(trans('plugins/ecommerce::reports.ranges'), JSON_HEX_APOS) !!}
    </script>
@endpush
