@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core::form>
        <discount-component
            currency="{{ get_application_currency()->symbol }}"
            date-format="{{ config('core.base.general.date_format.date') }}"
        ></discount-component>
    </x-core::form>
@stop

@push('header')
    <script>
        'use strict';

        window.trans = window.trans || {};

        window.trans.discount = {{ Js::from(trans('plugins/ecommerce::discount')) }}

        $(document).ready(function() {
            $(document).on('click', 'body', function(e) {
                let container = $('.box-search-advance');

                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    container.find('.panel').addClass('hidden');
                }
            });
        });
    </script>
@endpush

@push('footer')
    {!! $jsValidation !!}
@endpush
