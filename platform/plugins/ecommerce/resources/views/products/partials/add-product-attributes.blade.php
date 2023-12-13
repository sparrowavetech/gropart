@if ($productAttributeSets->isNotEmpty())
    <div class="add-new-product-attribute-wrap">
        <input
            id="is_added_attributes"
            name="is_added_attributes"
            type="hidden"
            value="0"
        >
        <p class="text-muted">{{ trans('plugins/ecommerce::products.form.add_new_attributes_description') }}</p>
        <div class="list-product-attribute-values-wrap" style="display: none">
            <div class="product-select-attribute-item-template"></div>
        </div>

        <x-core::form.fieldset class="list-product-attribute-wrap" style="display: none">
            <div class="list-product-attribute-items-wrap"></div>

            <div class="btn-list">
                <x-core::button
                    class="btn-trigger-add-attribute-item"
                    @style(['display: none;' => $productAttributeSets->count() < 2])
                >
                    {{ trans('plugins/ecommerce::products.form.add_more_attribute') }}
                </x-core::button>
                @if (!empty($addAttributeToProductUrl))
                    <x-core::button
                        type="button"
                        color="info"
                        class="btn-trigger-add-attribute-to-simple-product"
                        :data-target="$addAttributeToProductUrl"
                        :tooltip="trans('plugins/ecommerce::products.this_action_will_reload_page')"
                    >
                        {{ trans('plugins/ecommerce::products.form.continue') }}
                    </x-core::button>
                @endif
            </div>
            @if ($product && is_object($product) && $product->id)
                <x-core::alert type="warning" class="mt-3 mb-0">
                    {{ trans('plugins/ecommerce::products.this_action_will_reload_page') }}
                </x-core::alert>
            @endif
        </x-core::form.fieldset>
    </div>
@elseif (is_in_admin(true) && Auth::check() && Auth::user()->hasPermission('product-attribute-sets.create'))
    <p class="text-muted mb-0">
        {!! trans('plugins/ecommerce::products.form.create_product_variations', [
            'link' => Html::link(
                route('product-attribute-sets.create'),
                trans('plugins/ecommerce::products.form.add_new_attributes'),
                ['target' => '_blank']
            ),
        ]) !!}
    </p>
@endif

@push('footer')
    <x-core::custom-template id="attribute_item_wrap_template">
        <div class="product-attribute-set-item mb-3 mb-md-0" id="__id__">
            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <x-core::form.select
                        :label="trans('plugins/ecommerce::products.form.attribute_name')"
                        class="product-select-attribute-item"
                    />
                </div>
                <div class="col-sm-6 col-md-4">
                    <x-core::form.select
                        :label="trans('plugins/ecommerce::products.form.value')"
                        class="product-select-attribute-item-value"
                    />
                </div>

                <div class="col-md-4 col-sm-2 product-set-item-delete-action" style="display: none">
                    <x-core::button
                        type="button"
                        color="danger"
                        icon="ti ti-trash"
                        :icon-only="true"
                        style="margin-top: 1.75rem"
                    />
                </div>
            </div>
        </div>
    </x-core::custom-template>
@endpush
