<?php

namespace SparroWave\ProductFreeShipping\Providers;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Supports\ServiceProvider;
use Botble\Ecommerce\Events\ProductVariationCreated;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Tables\ProductTable;
use Botble\Marketplace\Forms\VendorProductForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use SparroWave\ProductFreeShipping\Supports\ProductFreeShippingHelper;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Form Injections (Admin & Marketplace Vendor)
        ProductForm::extend(function (ProductForm $form) {
            $this->injectProductFormField($form);
        });

        if (class_exists(VendorProductForm::class)) {
            VendorProductForm::extend(function (VendorProductForm $form) {
                $this->injectProductFormField($form);
            });
        }

        // 2. Save Hooks (Admin & Vendor)
        Event::listen([CreatedContentEvent::class, UpdatedContentEvent::class], [$this, 'saveProductFreeShippingField']);

        // 3. Inherit on Variation Created
        Event::listen(ProductVariationCreated::class, [$this, 'inheritVariationFreeShipping']);

        // 4. Shipping Fee & Weight Deduction Hook (Priority 125 to run after all shipping gateways)
        add_filter('handle_shipping_fee', [$this, 'handleShippingRatesAndWeight'], 125, 3);

        // 5. Product Detail Page Badge (Injected right alongside COD Available)
        add_filter('ecommerce_before_product_description', [$this, 'renderProductDetailBadge'], 125, 2);

        // 6. Cart & Checkout Item Badge
        add_filter('ecommerce_cart_after_item_content', [$this, 'renderCartItemBadge'], 115, 2);

        // 7. Admin Products DataTable Column & Badges
        add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addTableHeading'], 155, 3);
        add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addColumnToProductTable'], 155, 3);

        // 8. Universal Product Loop / Card Badge Hooks (For all standard Botble themes)
        add_filter('ecommerce_product_item_badges', [$this, 'renderProductCardBadge'], 125, 2);
        add_filter('ecommerce_after_product_price', [$this, 'renderProductCardBadge'], 125, 2);
        add_filter('ecommerce_product_loop_extra_html', [$this, 'renderProductCardBadge'], 125, 2);
    }

    public function renderProductCardBadge(?string $html, mixed $product = null): ?string
    {
        if (! ProductFreeShippingHelper::isEnabled() || ! $product) {
            return $html;
        }

        if (! ProductFreeShippingHelper::isProductFreeShipping($product)) {
            return $html;
        }

        $badgeText = ProductFreeShippingHelper::getBadgeText();

        $badgeHtml = '<span class="product-free-shipping-badge">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 12px !important; height: 12px !important; max-width: 12px !important; max-height: 12px !important; display: inline-block; vertical-align: middle; flex-shrink: 0;">'
            . '<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>'
            . '</svg>'
            . '<span>' . e($badgeText) . '</span>'
            . '</span>';

        return ($html ?? '') . $badgeHtml;
    }

    public function addTableHeading(array $headings, mixed $model, mixed $table): array
    {
        if (! ProductFreeShippingHelper::isEnabled() || ! is_in_admin(true)) {
            return $headings;
        }

        if ($model && $model::class === Product::class && $table instanceof ProductTable) {
            $headings['product_free_shipping'] = \Botble\Table\Columns\Column::make('product_free_shipping')
                ->title(trans('plugins/product-free-shipping::product-free-shipping.free_delivery'))
                ->alignCenter()
                ->width(100)
                ->orderable(false)
                ->searchable(false);
        }

        return $headings;
    }

    public function addColumnToProductTable(mixed $data, mixed $model, mixed $table = null): mixed
    {
        if (! ProductFreeShippingHelper::isEnabled() || ! is_in_admin(true) || ! $model) {
            return $data;
        }

        if (! is_a($model, Product::class, true)) {
            return $data;
        }

        if (is_object($data) && method_exists($data, 'addColumn')) {
            return $data->addColumn('product_free_shipping', function ($item) {
                if ($item->product_free_shipping) {
                    return new HtmlString('<span class="badge bg-success text-success-fg">' . trans('plugins/product-free-shipping::product-free-shipping.free_delivery') . '</span>');
                }

                return new HtmlString('&mdash;');
            });
        }

        return $data;
    }

    public function injectProductFormField(FormAbstract $form): void
    {
        if (! ProductFreeShippingHelper::isEnabled()) {
            return;
        }

        $model = $form->getModel();

        $form->addAfter(
            'is_featured',
            'product_free_shipping',
            OnOffField::class,
            OnOffFieldOption::make()
                ->label(trans('plugins/product-free-shipping::product-free-shipping.form_field_label'))
                ->helperText(trans('plugins/product-free-shipping::product-free-shipping.form_field_help'))
                ->defaultValue(false)
                ->value((bool) ($model ? $model->product_free_shipping : false))
        );
    }

    public function saveProductFreeShippingField(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        if (! ProductFreeShippingHelper::isEnabled()) {
            return;
        }

        $model = $event->data;

        if (! $model instanceof Product) {
            return;
        }

        $request = $event->request;

        if ($request->has('product_free_shipping')) {
            $model->product_free_shipping = (bool) $request->input('product_free_shipping');
            $model->saveQuietly();
        }
    }

    public function inheritVariationFreeShipping(ProductVariationCreated $event): void
    {
        if (! ProductFreeShippingHelper::isEnabled()) {
            return;
        }

        $variation = $event->productVariation;

        if (! $variation instanceof ProductVariation) {
            return;
        }

        $parentProduct = $variation->configurableProduct;
        $product = $variation->product;

        if ($parentProduct && $product) {
            $product->product_free_shipping = (bool) $parentProduct->product_free_shipping;
            $product->saveQuietly();
        }
    }

    public function handleShippingRatesAndWeight(mixed $rates = [], mixed $products = [], mixed $shippingData = []): array
    {
        return ProductFreeShippingHelper::calculateShippingRates($rates, $products, $shippingData);
    }

    public function renderProductDetailBadge(?string $html, mixed $product = null): ?string
    {
        if (! ProductFreeShippingHelper::isEnabled() || ! $product) {
            return $html;
        }

        if (! ProductFreeShippingHelper::isProductFreeShipping($product)) {
            return $html;
        }

        $badgeText = ProductFreeShippingHelper::getBadgeText();

        $badgeHtml = '<span class="badge bg-success ms-1" style="background-color: #28a745 !important; color: white !important; padding: 5px 10px; font-size: 11px; border-radius: 4px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 13px !important; height: 13px !important; max-width: 13px !important; max-height: 13px !important; display: inline-block; vertical-align: middle; flex-shrink: 0;">'
            . '<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>'
            . '</svg>'
            . '<span>' . e($badgeText) . '</span>'
            . '</span>';

        if ($html && str_contains($html, 'product-cod-label')) {
            return str_replace('</div>', ' ' . $badgeHtml . '</div>', $html);
        }

        return '<div class="product-cod-label mt-2 mb-2">' . $badgeHtml . '</div>' . ($html ?? '');
    }

    public function renderCartItemBadge(?string $html, mixed $cartItem = null): ?string
    {
        if (! ProductFreeShippingHelper::isEnabled() || ! $cartItem) {
            return $html;
        }

        $isFree = (bool) Arr::get($cartItem->options, 'product_free_shipping', false);
        if (! $isFree && isset($cartItem->id)) {
            $product = Product::find($cartItem->id);
            $isFree = ProductFreeShippingHelper::isProductFreeShipping($product);
        }

        if (! $isFree) {
            return $html;
        }

        $badgeText = ProductFreeShippingHelper::getBadgeText();

        $badgeHtml = '<div class="ec-cart-item-free-shipping-wrap" style="margin-top: 4px; margin-bottom: 2px;">'
            . '<span class="free-shipping-shimmer-badge" style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; font-size: 11px; font-weight: 600; color: #065f46; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; line-height: 1.3;">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width: 13px !important; height: 13px !important; max-width: 13px !important; max-height: 13px !important; display: inline-block; vertical-align: middle; flex-shrink: 0;">'
            . '<rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle>'
            . '</svg>'
            . '<span>' . e($badgeText) . '</span>'
            . '</span>'
            . '</div>';

        return ($html ?? '') . $badgeHtml;
    }
}
