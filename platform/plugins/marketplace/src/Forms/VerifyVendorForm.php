<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use Botble\Marketplace\Http\Requests\VerifyVendorRequest;
use Botble\Ecommerce\Models\Customer;
use Html;

class VerifyVendorForm extends FormAbstract
{

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Customer)
            ->withCustomFields()
            ->add('name', 'text', [
                'label'      => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                    'disabled'     => true,
                ],
            ])
            ->add('email', 'text', [
                'label'      => trans('plugins/marketplace::unverified-vendor.forms.email'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'data-counter' => 120,
                    'disabled'     => true,
                ],
            ])
            ->add('avatar', 'html', [
                'label'      => trans('core/base::forms.email'),
                'label_attr' => ['class' => 'control-label'],
                'html' => '<div class="form-group"><img src="' . $this->getModel()->avatar_url . '" alt="avatar" /></div>',
            ])
            ->add('store_name', 'text', [
                'label'      => trans('plugins/marketplace::unverified-vendor.forms.store_name'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'disabled'     => true,
                ],
                'value' => $this->getModel()->store->name
            ])
            ->add('store_phone', 'text', [
                'label'      => trans('plugins/marketplace::unverified-vendor.forms.store_phone'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'disabled'     => true,
                ],
                'value' => $this->getModel()->store->phone
            ])
            ->setActionButtons(view('core/base::forms.partials.form-actions', [
                'only_save' => true,
                'saveTitle' => trans('plugins/marketplace::unverified-vendor.forms.verify_vendor'),
                'saveIcon'  => 'fas fa-certificate'])->render())
            ;
    }
}
