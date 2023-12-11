<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\Settings\ProductSearchSettingRequest;
use Botble\Setting\Forms\SettingForm;

class ProductSearchForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.product_search.product_search_settings'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.product_search.product_search_settings_description'))
            ->setValidatorClass(ProductSearchSettingRequest::class)
            ->addCustomField('multiCheckList', MultiCheckListField::class)
            ->add('search_for_an_exact_phrase', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.product_search.form.search_for_an_exact_phrase'),
                'value' => get_ecommerce_setting('search_for_an_exact_phrase', false),
            ])
            ->add('search_products_by[]', 'multiCheckList', [
                'label' => trans('plugins/ecommerce::setting.product_search.form.search_products_by'),
                'choices' => [
                    'name' => trans('plugins/ecommerce::products.form.name'),
                    'sku' => trans('plugins/ecommerce::products.sku'),
                    'variation_sku' => trans('plugins/ecommerce::products.variation_sku'),
                    'description' => trans('plugins/ecommerce::products.form.description'),
                    'brand' => trans('plugins/ecommerce::products.form.brand'),
                    'tag' => trans('plugins/ecommerce::products.form.tags'),
                ],
                'value' => old('product_collections', EcommerceHelper::getProductsSearchBy()),
            ])
            ->add('enable_filter_products_by_brands', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.product_search.form.enable_filter_products_by_brands'),
                'value' => EcommerceHelper::isEnabledFilterProductsByBrands(),
            ])
            ->add('enable_filter_products_by_tags', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.product_search.form.enable_filter_products_by_tags'),
                'value' => EcommerceHelper::isEnabledFilterProductsByTags(),
            ])
            ->add('enable_filter_products_by_attributes', 'onOffCheckbox', [
                'label' => trans('plugins/ecommerce::setting.product_search.form.enable_filter_products_by_attributes'),
                'value' => EcommerceHelper::isEnabledFilterProductsByAttributes(),
            ]);
    }
}
