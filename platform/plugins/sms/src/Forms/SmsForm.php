<?php

namespace Botble\Sms\Forms;

use Botble\Sms\Models\Sms;
use Botble\Base\Forms\FormAbstract;
use Botble\Sms\Enums\SmsEnum;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Sms\Http\Requests\SmsRequest;

class SmsForm extends FormAbstract
{

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Sms)
            ->setValidatorClass(SmsRequest::class)
            ->withCustomFields()
            ->add('name', 'customSelect', [
                'label'      => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => SmsEnum::labels(),
            ])
            ->add('template_id', 'number', [
                'label'      => trans('plugins/sms::sms.template_id'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/sms::sms.template_id_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->addMetaBoxes([
                'general' => [
                    'title' => trans('plugins/sms::sms.variable'),
                    'content' => view('plugins/sms::partials.sms-variable')->render()
                ]])
            ->add('template', 'textarea', [
                'label'      => trans('plugins/sms::sms.template'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/sms::sms.template_placeholder'),
                    'data-counter' => 500,
                ],
                 'content' => view(
                        'plugins/sms::partials.sms-variable')->render(),
            ])
            
            ->add('status', 'customSelect', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => BaseStatusEnum::labels(),
            ])
            
            ->setBreakFieldPoint('status');
    }
}
