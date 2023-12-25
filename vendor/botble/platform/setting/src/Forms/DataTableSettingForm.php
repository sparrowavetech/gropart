<?php

namespace Botble\Setting\Forms;

use Botble\Setting\Http\Requests\DataTableSettingRequest;

class DataTableSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('core/setting::setting.datatable.title'))
            ->setSectionDescription(trans('core/setting::setting.datatable.description'))
            ->setValidatorClass(DataTableSettingRequest::class)
            ->add('datatables_default_show_column_visibility', 'onOffCheckbox', [
                'label' => trans('core/setting::setting.datatable.form.show_column_visibility'),
                'value' => setting('datatables_default_show_column_visibility', false),
            ])
            ->add('datatables_default_show_export_button', 'onOffCheckbox', [
                'label' => trans('core/setting::setting.datatable.form.show_export_button'),
                'value' => setting('datatables_default_show_export_button', false),
                'wrapper' => [
                    'class' => 'mb-0',
                ],
            ]);
    }
}
