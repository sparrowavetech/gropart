<?php

namespace Botble\Marketplace\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Marketplace\Enums\ShopTypeEnum;
use Botble\Location\Models\State;
use Botble\Location\Repositories\Interfaces\StateInterface;
use Botble\Ecommerce\Repositories\Interfaces\CustomerInterface;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\ContentFieldOption;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\FieldOptions\DescriptionFieldOption;
use Botble\Base\Forms\FieldOptions\EmailFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NameFieldOption;
use Botble\Base\Forms\Fields\EditorField;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextareaField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Concerns\HasLocationFields;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Forms\Concerns\HasSubmitButton;
use Botble\Marketplace\Http\Requests\StoreRequest;
use Botble\Marketplace\Models\Store;

class StoreForm extends FormAbstract
{
    use HasLocationFields;
    use HasSubmitButton;

    public function setup(): void
    {
        Assets::addScriptsDirectly([
            'vendor/core/plugins/marketplace/js/store.js',
        ]);

        $isAdmin = is_in_admin(true);

        $this
            ->setupModel(new Store())
            ->setValidatorClass(StoreRequest::class)
            ->columns(6)
            ->contentOnly()
            ->hasFiles()
            ->add('name', TextField::class, NameFieldOption::make()->required()->colspan(3)->toArray())
            ->add('company', TextField::class, [
                'label' => trans('plugins/marketplace::store.forms.company'),
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.company_placeholder'),
                    'data-counter' => 255,
                ],
                'colspan' => 3,
            ])
            ->add('slug', HtmlField::class,
                HtmlFieldOption::make()
                    ->content(view('plugins/marketplace::stores.partials.shop-url-field', ['store' => $this->getModel()])->render())
                    ->colspan(3)
            )
            ->add('customer_id', SelectField::class, [
                'label' => trans('plugins/marketplace::store.forms.store_owner'),
                'required' => true,
                'choices' => [0 => trans('plugins/marketplace::store.forms.select_store_owner')] + Customer::query()
                    ->where('is_vendor', true)
                    ->pluck('name', 'id')
                    ->all(),
                'colspan' => 3,
            ])
            ->add('email', EmailField::class, EmailFieldOption::make()->required()->colspan(3)->toArray())
            ->add('phone', TextField::class, [
                'label' => trans('plugins/marketplace::store.forms.phone'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/marketplace::store.forms.phone_placeholder'),
                    'data-counter' => 15,
                ],
                'colspan' => 3,
            ])
            ->add('shop_category', SelectField::class, [
                'label'      => trans('plugins/marketplace::store.forms.shop_category'),
                'required' => true,
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'choices'    => ShopTypeEnum::labels(),
                'colspan' => 3,
            ])
            ->add('status', SelectField::class, [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
                'help_block' => [
                    TextField::class => trans('plugins/marketplace::marketplace.helpers.store_status', [
                        'customer' => CustomerStatusEnum::LOCKED()->label(),
                        'status' => BaseStatusEnum::PUBLISHED()->label(),
                    ]),
                ],
                'colspan' => 3,
            ])
            ->when($isAdmin, function ($form) {
                $form
                    ->add('is_verified', OnOffCheckboxField::class, [
                        'label'         => trans('plugins/marketplace::store.forms.is_verified'),
                        'label_attr'    => ['class' => 'control-label'],
                        'default_value' => false,
                        'colspan' => 3,
                    ])
                    ->add('is_manage_shipping', OnOffCheckboxField::class, [
                        'label'         => trans('plugins/marketplace::store.forms.is_manage_shipping'),
                        'label_attr'    => ['class' => 'control-label'],
                        'default_value' => false,
                        'colspan' => 3,
                    ]);
            })
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->colspan(6)->toArray())
            ->add('content', EditorField::class, ContentFieldOption::make()->colspan(6)->toArray())
            ->addLocationFields()
            ->add(
                'logo',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Logo'))
                    ->colspan(3)
                    ->toArray()
            )
            ->add(
                'cover_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(__('Cover Image'))
                    ->colspan(6)
                    ->toArray()
            )
            ->addSubmitButton(trans('core/base::forms.save_and_continue'), attributes: ['colspan' => 6]);
    }
}
