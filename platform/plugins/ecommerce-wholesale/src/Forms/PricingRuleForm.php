<?php

namespace Botble\EcommerceWholesale\Forms;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingDiscountTypeEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Http\Requests\PricingRuleRequest;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;

class PricingRuleForm extends FormAbstract
{
    public function setup(): void
    {
        $products = Product::query()
            ->where('is_variation', false)
            ->pluck('name', 'id')
            ->all();

        $categories = ProductCategory::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        $groups = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->pluck('name', 'id')
            ->all();

        $this
            ->setupModel(new GroupPricingRule())
            ->setValidatorClass(PricingRuleRequest::class)

            // -- Target Section --
            ->add(
                'target_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3">' . trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.target_section') . '</h4>')
            )
            ->add(
                'scope',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope'))
                    ->choices(PricingRuleScopeEnum::labels())
                    ->required()
                    ->defaultValue(PricingRuleScopeEnum::PRODUCT)
            )
            ->add(
                'scope_context_banner',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('info')
                    ->content(
                        '<span id="scope-context-text">'
                        . trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope_product_info')
                        . '</span>'
                    )
            )
            ->add(
                'product_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.product'))
                    ->choices($products)
                    ->searchable()
            )
            ->add(
                'category_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.category'))
                    ->choices($categories)
                    ->searchable()
            )
            ->add(
                'customer_group_id',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.customer_group'))
                    ->choices($groups)
                    ->searchable()
                    ->allowClear()
                    ->emptyValue(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.all_groups'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.group_help'))
            )

            // -- Pricing Section --
            ->add(
                'pricing_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3 mt-4 pt-3 border-top">' . trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.pricing_section') . '</h4>')
            )
            ->add('quantity_row_open', HtmlField::class, [
                'html' => '<div class="row g-3">',
            ])
            ->add(
                'min_quantity',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.min_quantity'))
                    ->required()
                    ->defaultValue(1)
                    ->wrapperAttributes(['class' => 'col-md-6 mb-3'])
            )
            ->add(
                'max_quantity',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_quantity'))
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.max_quantity_help'))
                    ->wrapperAttributes(['class' => 'col-md-6 mb-3'])
            )
            ->add('quantity_row_close', HtmlField::class, [
                'html' => '</div>',
            ])
            ->add(
                'discount_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_type'))
                    ->choices(PricingDiscountTypeEnum::labels())
                    ->required()
            )
            ->add(
                'discount_value',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value'))
                    ->required()
                    ->helperText(trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_value_help'))
            )
            ->add(
                'discount_preview',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content(
                        '<div id="discount-preview-box" class="card bg-light border-0 mb-3" style="display:none;">'
                        . '<div class="card-body py-2 px-3">'
                        . '<div class="d-flex align-items-center">'
                        . '<span class="badge bg-blue-lt me-2">'
                        . trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.discount_preview')
                        . '</span>'
                        . '<span id="discount-preview-text" class="text-muted small"></span>'
                        . '</div>'
                        . '</div>'
                        . '</div>'
                    )
            )

            // -- Status Section --
            ->add(
                'status_section',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->content('<h4 class="mb-3 mt-4 pt-3 border-top">' . trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.status_section') . '</h4>')
            )
            ->add(
                'status',
                SelectField::class,
                StatusFieldOption::make()->choices(CustomerGroupStatusEnum::labels())
            );

        $this->addInlineScopeToggleScript();
    }

    protected function addInlineScopeToggleScript(): void
    {
        $scopeMessages = json_encode([
            'product' => [
                'text' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope_product_info'),
                'type' => 'info',
            ],
            'category' => [
                'text' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope_category_info'),
                'type' => 'warning',
            ],
            'global' => [
                'text' => trans('plugins/ecommerce-wholesale::wholesale.pricing_rule.scope_global_info'),
                'type' => 'success',
            ],
        ]);

        $currencySymbol = get_application_currency()
            ? get_application_currency()->symbol
            : '$';

        add_filter(BASE_FILTER_AFTER_FORM_CREATED, function ($form) use ($scopeMessages, $currencySymbol) {
            if (! $form instanceof self) {
                return $form;
            }

            $script = <<<JS
<script>
document.addEventListener('DOMContentLoaded', function () {
    var scopeField = document.querySelector('[name="scope"]');
    if (!scopeField) return;

    var scopeMessages = {$scopeMessages};
    var currencySymbol = '{$currencySymbol}';

    // -- Scope Toggle + Banner --
    function toggleFields() {
        var scope = scopeField.value;
        var productField = document.querySelector('[name="product_id"]');
        var categoryField = document.querySelector('[name="category_id"]');
        var productWrapper = productField ? productField.closest('.mb-3') : null;
        var categoryWrapper = categoryField ? categoryField.closest('.mb-3') : null;

        if (productWrapper) productWrapper.style.display = scope === 'product' ? '' : 'none';
        if (categoryWrapper) categoryWrapper.style.display = scope === 'category' ? '' : 'none';

        var bannerText = document.getElementById('scope-context-text');
        if (bannerText && scopeMessages[scope]) {
            bannerText.innerHTML = scopeMessages[scope].text;
        }

        var alertEl = bannerText ? bannerText.closest('.alert') : null;
        if (alertEl && scopeMessages[scope]) {
            alertEl.className = alertEl.className.replace(/alert-\\w+/g, '');
            alertEl.classList.add('alert', 'alert-' + scopeMessages[scope].type);
        }
    }

    scopeField.addEventListener('change', toggleFields);
    toggleFields();

    // -- Discount Preview --
    var discountType = document.querySelector('[name="discount_type"]');
    var discountValue = document.querySelector('[name="discount_value"]');
    var minQty = document.querySelector('[name="min_quantity"]');
    var previewBox = document.getElementById('discount-preview-box');
    var previewText = document.getElementById('discount-preview-text');

    function updatePreview() {
        if (!discountType || !discountValue || !previewBox || !previewText) return;
        var type = discountType.value;
        var val = parseFloat(discountValue.value);
        var qty = parseInt(minQty ? minQty.value : 1) || 1;

        if (!type || isNaN(val) || val <= 0) {
            previewBox.style.display = 'none';
            return;
        }

        var basePrice = 100;
        var result = '';

        if (type === 'percentage') {
            var finalPrice = basePrice * (1 - val / 100);
            result = 'Buy ' + qty + '+: ' + val + '% off each unit (' + currencySymbol + finalPrice.toFixed(2) + ' on a ' + currencySymbol + basePrice.toFixed(2) + ' item)';
        } else if (type === 'fixed') {
            var finalPrice = Math.max(0, basePrice - val);
            result = 'Buy ' + qty + '+: Save ' + currencySymbol + val.toFixed(2) + ' per unit (' + currencySymbol + finalPrice.toFixed(2) + ' on a ' + currencySymbol + basePrice.toFixed(2) + ' item)';
        } else if (type === 'fixed_price') {
            result = 'Buy ' + qty + '+: ' + currencySymbol + val.toFixed(2) + ' per unit';
        }

        previewText.innerHTML = result;
        previewBox.style.display = '';
    }

    if (discountType) discountType.addEventListener('change', updatePreview);
    if (discountValue) discountValue.addEventListener('input', updatePreview);
    if (minQty) minQty.addEventListener('input', updatePreview);
    updatePreview();
});
</script>
JS;

            add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function (?string $html) use ($script) {
                return ($html ?? '') . $script;
            }, 99);

            return $form;
        }, 999, 1);
    }
}
