@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core-setting::section
        :title="trans('plugins/ecommerce::setting.tax.tax_management')"
        :description="trans('plugins/ecommerce::setting.tax.tax_management_description')"
    >
        <div class="table-responsive">
            {!! $taxTable->render('core/table::base-table') !!}
        </div>
    </x-core-setting::section>

    {!! $form->renderForm() !!}
@endsection
