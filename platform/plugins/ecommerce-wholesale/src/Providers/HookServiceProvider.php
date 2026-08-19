<?php

namespace Botble\EcommerceWholesale\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\MetaBox;
use Botble\Ecommerce\Facades\Cart;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\CustomerForm;
use Botble\Ecommerce\Forms\ProductForm;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Botble\EcommerceWholesale\Enums\ApplicationStatusEnum;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\PricingRuleScopeEnum;
use Botble\EcommerceWholesale\Enums\ProductVisibilityEnum;
use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Botble\EcommerceWholesale\Models\CustomerGroup;
use Botble\EcommerceWholesale\Models\GroupPricingRule;
use Botble\EcommerceWholesale\Models\ProductGroupAccess;
use Botble\EcommerceWholesale\Models\ProductMOQ;
use Botble\EcommerceWholesale\Models\ProductVisibility;
use Botble\EcommerceWholesale\Models\WholesaleApplication;
use Botble\EcommerceWholesale\Scopes\WholesaleProductVisibilityScope;
use Botble\EcommerceWholesale\Services\MOQValidationService;
use Botble\EcommerceWholesale\Services\PricingRuleService;
use Botble\EcommerceWholesale\Services\ProductVisibilityService;
use Botble\EcommerceWholesale\Services\ProductWholesaleBoxRenderer;
use Botble\EcommerceWholesale\Services\WholesalePriceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->registerDynamicRelations();
            $this->registerFormHooks();
            $this->registerProductWholesaleMetaBox();
            $this->registerMarketplaceProductFormHook();
            $this->registerProductWholesaleSaveHooks();
            $this->registerMOQValidationHooks();
            $this->registerProductVisibilityScope();
            $this->registerPendingApplicationsBadge();
            $this->registerWholesalePriceFilters();
            $this->registerCartItemPriceUpdate();
            $this->registerCartWholesaleInfo();
            $this->registerOrderWholesaleInfo();
            $this->registerAdminOrderWholesaleInfo();
            $this->registerOrderProductPriceHooks();
            $this->registerProductPricingTable();
            $this->registerCustomerDashboardWholesaleCard();
            $this->registerCustomerDashboardMenu();
            $this->registerVendorDashboardMenu();
        });
    }

    protected function registerDynamicRelations(): void
    {
        Customer::resolveRelationUsing('wholesaleGroups', function ($model) {
            return $model->belongsToMany(
                CustomerGroup::class,
                'ws_customer_group_assignments',
                'customer_id',
                'customer_group_id'
            )
                ->withPivot(['assigned_at', 'expires_at', 'assigned_by'])
                ->withTimestamps();
        });

        Product::resolveRelationUsing('wholesaleVisibility', function ($model) {
            return $model->hasOne(ProductVisibility::class, 'product_id');
        });

        Product::resolveRelationUsing('wholesaleGroupAccess', function ($model) {
            return $model->hasMany(ProductGroupAccess::class, 'product_id');
        });

        Product::resolveRelationUsing('groupPricingRules', function ($model) {
            return $model->hasMany(GroupPricingRule::class, 'product_id')
                ->where('scope', PricingRuleScopeEnum::PRODUCT)
                ->orderBy('min_quantity', 'asc');
        });
    }

    protected function registerFormHooks(): void
    {
        add_filter(BASE_FILTER_AFTER_FORM_CREATED, [$this, 'registerCustomerFormFields'], 127, 2);
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveCustomerGroupAssignments'], 127, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveCustomerGroupAssignments'], 127, 3);
    }

    public function registerCustomerFormFields(FormAbstract $form, array|Model|string|null $data): FormAbstract
    {
        if (! $form instanceof CustomerForm) {
            return $form;
        }

        $groups = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->pluck('name', 'id')
            ->all();

        if (empty($groups)) {
            return $form;
        }

        $selectedGroups = [];
        if ($data instanceof Customer && $data->id) {
            $selectedGroups = $data->wholesaleGroups()->pluck('ws_customer_groups.id')->all();
        }

        $form->addAfter(
            'status',
            'wholesale_groups',
            SelectField::class,
            SelectFieldOption::make()
                ->label(trans('plugins/ecommerce-wholesale::wholesale.customer_groups'))
                ->choices($groups)
                ->selected($selectedGroups)
                ->searchable()
                ->multiple()
                ->allowClear()
                ->helperText(trans('plugins/ecommerce-wholesale::wholesale.customer_group.assign_help'))
        );

        return $form;
    }

    public function saveCustomerGroupAssignments(string $type, Request $request, Model|string|null $object): bool
    {
        if (! $object instanceof Customer) {
            return false;
        }

        $groupIds = $request->input('wholesale_groups', []);

        if (! is_array($groupIds)) {
            $groupIds = [];
        }

        $syncData = [];
        foreach ($groupIds as $groupId) {
            $syncData[$groupId] = [
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ];
        }

        $object->wholesaleGroups()->sync($syncData);

        return true;
    }

    protected function registerProductWholesaleMetaBox(): void
    {
        add_filter(BASE_FILTER_AFTER_FORM_CREATED, function (FormAbstract $form, $data) {
            if (! $form instanceof ProductForm) {
                return $form;
            }

            if (! $data instanceof Product || ! $data->exists) {
                return $form;
            }

            if (request()->segment(1) !== BaseHelper::getAdminPrefix()) {
                return $form;
            }

            $form->addMetaBox(
                MetaBox::make('wholesale_settings')
                    ->title(trans('plugins/ecommerce-wholesale::wholesale.name'))
                    ->content(view(
                        'plugins/ecommerce-wholesale::admin.product-wholesale-settings',
                        $this->getWholesaleMetaBoxData($data)
                    )->render())
            );

            return $form;
        }, 130, 2);
    }

    protected function registerMarketplaceProductFormHook(): void
    {
        if (! is_plugin_active('marketplace') || ! class_exists(\Botble\Marketplace\Forms\ProductForm::class)) {
            return;
        }

        if (! WholesaleHelper::isVendorDashboardEnabled()) {
            return;
        }

        \Botble\Marketplace\Forms\ProductForm::beforeRendering(function ($form) {
            $product = $form->getModel();

            if (! $product || ! $product->id) {
                return $form;
            }

            $storeId = $product->store_id ?? WholesaleHelper::getVendorStoreId();

            $data = $this->getWholesaleMetaBoxData($product, $storeId);
            $data['isVendor'] = true;

            $form->addMetaBoxes([
                'wholesale_settings' => [
                    'title' => trans('plugins/ecommerce-wholesale::wholesale.name'),
                    'content' => view(
                        'plugins/ecommerce-wholesale::admin.product-wholesale-settings',
                        $data
                    )->render(),
                    'priority' => 50,
                ],
            ]);

            return $form;
        });
    }

    protected function getWholesaleMetaBoxData(Product $product, ?int $storeId = null): array
    {
        $moq = ProductMOQ::query()
            ->where('product_id', $product->id)
            ->whereNull('customer_group_id')
            ->first();

        $visibility = ProductVisibility::query()
            ->where('product_id', $product->id)
            ->first();

        $groupAccess = ProductGroupAccess::query()
            ->where('product_id', $product->id)
            ->pluck('customer_group_id')
            ->all();

        $groups = CustomerGroup::query()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->pluck('name', 'id')
            ->all();

        $rulesQuery = GroupPricingRule::query()
            ->where('product_id', $product->id)
            ->where('scope', PricingRuleScopeEnum::PRODUCT)
            ->with('customerGroup')
            ->orderBy('min_quantity');

        if ($storeId !== null) {
            $rulesQuery->where('store_id', $storeId);
        }

        $rules = $rulesQuery->get();

        return [
            'product' => $product,
            'moq' => $moq,
            'visibility' => $visibility,
            'selectedVisibility' => $visibility?->visibility_type?->getValue() ?? ProductVisibilityEnum::PRODUCT_PUBLIC,
            'visibilityOptions' => ProductVisibilityEnum::labels(),
            'groupAccess' => $groupAccess,
            'groups' => $groups,
            'rules' => $rules,
        ];
    }

    protected function registerProductWholesaleSaveHooks(): void
    {
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveProductMOQ'], 128, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveProductMOQ'], 128, 3);
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveProductVisibility'], 129, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveProductVisibility'], 129, 3);
        add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveProductPricingRules'], 130, 3);
        add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveProductPricingRules'], 130, 3);
    }

    public function saveProductMOQ(string $type, Request $request, Model|string|null $object): void
    {
        if (! $object instanceof Product) {
            return;
        }

        $minQty = (int) $request->input('wholesale_min_quantity', 1);
        $increment = (int) $request->input('wholesale_quantity_increment', 1);

        if ($minQty > 1 || $increment > 1) {
            ProductMOQ::query()->updateOrCreate(
                ['product_id' => $object->id, 'customer_group_id' => null],
                ['min_quantity' => max(1, $minQty), 'quantity_increment' => max(1, $increment)]
            );
        } else {
            ProductMOQ::query()
                ->where('product_id', $object->id)
                ->whereNull('customer_group_id')
                ->delete();
        }
    }

    public function saveProductPricingRules(string $type, Request $request, Model|string|null $object): void
    {
        if (! $object instanceof Product) {
            return;
        }

        $isVendor = WholesaleHelper::isInVendorPanel();

        if ($isVendor && ! WholesaleHelper::isVendorDashboardEnabled()) {
            return;
        }

        if (! $request->has('has_wholesale_pricing_rules')) {
            return;
        }

        $rules = $request->input('wholesale_pricing_rules', []);

        if (! is_array($rules)) {
            $rules = [];
        }

        $storeId = null;

        if ($isVendor) {
            $storeId = $object->store_id ?? WholesaleHelper::getVendorStoreId();

            if (! $storeId) {
                return;
            }
        }

        $existingRulesQuery = GroupPricingRule::query()
            ->where('product_id', $object->id)
            ->where('scope', PricingRuleScopeEnum::PRODUCT);

        if ($isVendor) {
            $existingRulesQuery->where('store_id', $storeId);
        } else {
            $existingRulesQuery->whereNull('store_id');
        }

        $existingRuleIds = $existingRulesQuery->pluck('id')->all();

        $updatedRuleIds = [];

        foreach ($rules as $ruleData) {
            $ruleId = ! empty($ruleData['id']) ? $ruleData['id'] : null;

            $ruleValues = [
                'scope' => PricingRuleScopeEnum::PRODUCT,
                'product_id' => $object->id,
                'customer_group_id' => $ruleData['customer_group_id'] ?: null,
                'min_quantity' => max(1, (int) $ruleData['min_quantity']),
                'max_quantity' => ! empty($ruleData['max_quantity']) ? (int) $ruleData['max_quantity'] : null,
                'discount_type' => $ruleData['discount_type'],
                'discount_value' => max(0, (float) $ruleData['discount_value']),
                'status' => CustomerGroupStatusEnum::PUBLISHED,
            ];

            if ($isVendor) {
                $ruleValues['store_id'] = $storeId;
            }

            $lookupKey = ['id' => $ruleId, 'product_id' => $object->id];

            if ($isVendor && $ruleId) {
                $lookupKey['store_id'] = $storeId;
            }

            $rule = GroupPricingRule::query()->updateOrCreate(
                $lookupKey,
                $ruleValues
            );

            $updatedRuleIds[] = $rule->id;
        }

        $rulesToDelete = array_diff($existingRuleIds, $updatedRuleIds);
        if (! empty($rulesToDelete)) {
            GroupPricingRule::query()->whereIn('id', $rulesToDelete)->delete();
        }
    }

    protected function registerMOQValidationHooks(): void
    {
        add_filter('ecommerce_validate_cart_before_checkout', function ($result) {
            $customer = auth('customer')->user();

            if (! $customer) {
                return $result;
            }

            $service = app(MOQValidationService::class);
            $cart = Cart::instance('cart')->content();
            $validation = $service->validateCart($cart, $customer);

            if (! $validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode('<br>', $validation['errors']),
                ];
            }

            return $result;
        }, 120);
    }

    public function saveProductVisibility(string $type, Request $request, Model|string|null $object): void
    {
        if (! $object instanceof Product) {
            return;
        }

        if (WholesaleHelper::isInVendorPanel()) {
            return;
        }

        $visibilityType = $request->input('wholesale_visibility', ProductVisibilityEnum::PRODUCT_PUBLIC);
        $groupIds = $request->input('wholesale_group_access', []);

        if (! is_array($groupIds)) {
            $groupIds = [];
        }

        $service = app(ProductVisibilityService::class);

        if ($visibilityType === ProductVisibilityEnum::PRODUCT_PUBLIC) {
            $service->removeVisibility($object);
        } else {
            $service->setVisibility(
                $object,
                (new ProductVisibilityEnum())->make($visibilityType),
                $groupIds
            );
        }
    }

    protected function registerProductVisibilityScope(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        Product::addGlobalScope(new WholesaleProductVisibilityScope());
    }

    protected function registerPendingApplicationsBadge(): void
    {
        if (! WholesaleHelper::isApprovalRequired()) {
            return;
        }

        add_filter(BASE_FILTER_APPEND_MENU_NAME, function (string $name, string $menuId): string {
            if ($menuId !== 'cms-plugins-wholesale-applications') {
                return $name;
            }

            $pendingCount = WholesaleApplication::query()
                ->where('status', ApplicationStatusEnum::PENDING)
                ->count();

            if ($pendingCount > 0) {
                return $name . ' <span class="badge bg-yellow-lt">' . $pendingCount . '</span>';
            }

            return $name;
        }, 120, 2);

        add_filter(BASE_FILTER_MENU_ITEMS_COUNT, function (array $items): array {
            $pendingCount = WholesaleApplication::query()
                ->where('status', ApplicationStatusEnum::PENDING)
                ->count();

            if ($pendingCount > 0) {
                $items[] = [
                    'key' => 'wholesale-pending-applications',
                    'value' => $pendingCount,
                ];
            }

            return $items;
        }, 120);
    }

    protected function registerWholesalePriceFilters(): void
    {
        if (is_in_admin()) {
            return;
        }

        add_filter('ecommerce_cart_raw_subtotal', function (float $subtotal, $cartContent) {
            $this->applyWholesalePricesToCart();

            return $this->recalculateCartSubtotal($cartContent);
        }, 100, 2);

        add_filter('ecommerce_cart_subtotal', function (float $subtotal, $cartContent) {
            return $this->recalculateCartSubtotal($cartContent);
        }, 100, 2);

        add_filter('ecommerce_cart_raw_subtotal_by_items', function (float $subtotal, $cartContent) {
            return $this->recalculateCartSubtotal($cartContent);
        }, 100, 2);

        // Cart total and tax are intentionally NOT re-filtered. Wholesale prices are
        // applied in place to each cart item (updateQuietly), so the core total/tax -
        // computed from $cartItem->price - is already wholesale-correct at any scope
        // (full cart or per-vendor slice). A subtotal-ratio rescale here only risked
        // re-introducing the multivendor shipping inflation it was meant to avoid.
    }

    protected function registerCartItemPriceUpdate(): void
    {
        if (is_in_admin()) {
            return;
        }

        $this->app['events']->listen(['cart.added', 'cart.updated'], function ($cartItem): void {
            $this->updateCartItemWholesalePrice($cartItem);
        });

        add_filter(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, function ($products) {
            $this->applyWholesalePricesToCart();

            return $products;
        }, 1);

        add_filter('ecommerce_cart_data_for_response', function (array $data) {
            $this->applyWholesalePricesToCart();

            $cart = Cart::instance('cart');
            $data['count'] = $cart->count();
            $data['total_price'] = format_price($cart->rawSubTotal());
            $data['content'] = $cart->content();

            return $data;
        }, 90);
    }

    protected function registerCartWholesaleInfo(): void
    {
        if (is_in_admin()) {
            return;
        }

        add_filter('ecommerce_cart_after_item_content', function (?string $html, object $cartItem): ?string {
            $wholesaleInfo = $this->getCartItemWholesaleInfo($cartItem);

            if (! $wholesaleInfo) {
                return $html;
            }

            $badge = view('plugins/ecommerce-wholesale::themes.partials.cart-wholesale-badge', $wholesaleInfo)->render();

            return ($html ?? '') . $badge;
        }, 25, 2);
    }

    protected function getCartItemWholesaleInfo(object $cartItem): ?array
    {
        if (! $cartItem->id || ! WholesaleHelper::isEnabled()) {
            return null;
        }

        $customer = auth('customer')->user();

        if (! $customer && ! WholesaleHelper::isEnabledForGuests()) {
            return null;
        }

        if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! WholesaleHelper::isEnabledForGuests()) {
            return null;
        }

        $product = Product::query()->find($cartItem->id);

        if (! $product) {
            return null;
        }

        $originalProduct = $product->is_variation ? $product->original_product : $product;
        // Convert the product's own currency price to the store default currency before applying wholesale discounts.
        $basePrice = $product->isOnSale() ? $product->front_sale_price : $product->getConvertedPrice();
        $groupIds = app(WholesalePriceService::class)->getApplicableGroupIds($customer);

        if (empty($groupIds) && ! WholesaleHelper::isEnabledForGuests()) {
            return null;
        }

        $service = app(PricingRuleService::class);
        $bestPrice = $service->getBestPriceForQuantity(
            $originalProduct,
            $cartItem->qty,
            $groupIds,
            $product->store_id ?? null,
            $basePrice
        );

        if (! $bestPrice) {
            return null;
        }

        $rule = $bestPrice['rule'];

        return [
            'original_price' => $bestPrice['original_price'],
            'wholesale_price' => $bestPrice['final_price'],
            'savings' => $bestPrice['discount'],
            'min_quantity' => $rule->min_quantity,
            'discount_type' => $rule->discount_type->getValue(),
            'discount_value' => $rule->discount_value,
            'quantity' => $cartItem->qty,
        ];
    }

    protected function registerOrderWholesaleInfo(): void
    {
        if (is_in_admin()) {
            return;
        }

        add_filter('ecommerce_thank_you_order_product_item', function (?string $html, $orderProduct): ?string {
            $options = is_array($orderProduct->options) ? $orderProduct->options : json_decode($orderProduct->options, true) ?? [];
            $discountLabel = $options['extras']['wholesale_discount'] ?? null;

            if (! $discountLabel) {
                return $html;
            }

            $badge = view('plugins/ecommerce-wholesale::themes.partials.order-wholesale-badge', [
                'discountLabel' => $discountLabel,
            ])->render();

            return ($html ?? '') . $badge;
        }, 25, 2);
    }

    protected function registerAdminOrderWholesaleInfo(): void
    {
        add_filter('ecommerce_order_product_item_extra_info', function (?string $html, $orderProduct, $order): ?string {
            $options = is_array($orderProduct->options) ? $orderProduct->options : json_decode($orderProduct->options, true) ?? [];
            $discountLabel = $options['extras']['wholesale_discount'] ?? null;

            if (! $discountLabel) {
                return $html;
            }

            $badge = '<div class="mt-1 mb-1">'
                . '<span class="badge" style="background: #206bc4; color: #fff; font-size: 11px; font-weight: 500; padding: 3px 8px;">'
                . '<i class="ti ti-building-store" style="font-size: 12px;"></i> '
                . e(trans('plugins/ecommerce-wholesale::wholesale.cart.wholesale_price_applied', ['discount' => $discountLabel]))
                . '</span>'
                . '</div>';

            return ($html ?? '') . $badge;
        }, 25, 3);
    }

    protected function updateCartItemWholesalePrice($cartItem): void
    {
        if (! $cartItem || ! $cartItem->id) {
            return;
        }

        $customer = auth('customer')->user();

        if (! $customer && ! WholesaleHelper::isEnabledForGuests()) {
            return;
        }

        if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! WholesaleHelper::isEnabledForGuests()) {
            return;
        }

        $product = Product::query()->find($cartItem->id);

        if (! $product) {
            return;
        }

        $service = app(WholesalePriceService::class);
        $wholesalePrice = $service->getWholesalePrice($product, $cartItem->qty, $customer);

        $cartInstance = Cart::instance('cart');

        if ($wholesalePrice !== null) {
            $wholesalePriceWithOptions = $this->applyOptionPriceToWholesale($wholesalePrice, $cartItem);

            if ($wholesalePriceWithOptions != $cartItem->price && $cartInstance->get($cartItem->rowId)) {
                $cartInstance->updateQuietly($cartItem->rowId, [
                    'price' => $wholesalePriceWithOptions,
                ]);
            }
        } else {
            $originalPrice = $product->price()->getPrice(false);
            $originalPriceWithOptions = $this->applyOptionPriceToWholesale($originalPrice, $cartItem);

            if ($originalPriceWithOptions != $cartItem->price && $cartInstance->get($cartItem->rowId)) {
                $cartInstance->updateQuietly($cartItem->rowId, [
                    'price' => $originalPriceWithOptions,
                ]);
            }
        }
    }

    protected function applyWholesalePricesToCart(): void
    {
        $customer = auth('customer')->user();

        if (! $customer && ! WholesaleHelper::isEnabledForGuests()) {
            return;
        }

        if ($customer && ! WholesaleHelper::isWholesaleCustomer($customer) && ! WholesaleHelper::isEnabledForGuests()) {
            return;
        }

        $service = app(WholesalePriceService::class);
        $cart = Cart::instance('cart');

        foreach ($cart->content() as $cartItem) {
            if (! $cartItem || ! $cartItem->id) {
                continue;
            }

            $product = Product::query()->find($cartItem->id);

            if (! $product) {
                continue;
            }

            $wholesalePrice = $service->getWholesalePrice($product, $cartItem->qty, $customer);

            if ($wholesalePrice !== null) {
                $wholesalePriceWithOptions = $this->applyOptionPriceToWholesale($wholesalePrice, $cartItem);

                if ($wholesalePriceWithOptions != $cartItem->price && $cart->get($cartItem->rowId)) {
                    $cart->updateQuietly($cartItem->rowId, [
                        'price' => $wholesalePriceWithOptions,
                    ]);
                }
            } else {
                $originalPrice = $product->price()->getPrice(false);
                $originalPriceWithOptions = $this->applyOptionPriceToWholesale($originalPrice, $cartItem);

                if ($originalPriceWithOptions != $cartItem->price && $cart->get($cartItem->rowId)) {
                    $cart->updateQuietly($cartItem->rowId, [
                        'price' => $originalPriceWithOptions,
                    ]);
                }
            }
        }
    }

    protected function applyOptionPriceToWholesale(float $wholesalePrice, object $cartItem): float
    {
        if (
            EcommerceHelper::isEnabledProductOptions() &&
            ($productOptions = Arr::get($cartItem->options->toArray(), 'options', [])) &&
            is_array($productOptions)
        ) {
            // getPriceByOptions() returns ['price' => float, 'option_price_once' => float];
            // the wholesale hook only updates the per-unit price, so extract the 'price' key.
            $priceResult = Cart::instance('cart')->getPriceByOptions($wholesalePrice, $productOptions);

            return (float) Arr::get($priceResult, 'price', $wholesalePrice);
        }

        return $wholesalePrice;
    }

    protected function recalculateCartSubtotal($cartContent): float
    {
        $subtotal = 0;

        foreach ($cartContent as $cartItem) {
            if (! $cartItem) {
                continue;
            }

            if (EcommerceHelper::isTaxEnabled() && $cartItem->options->get('price_includes_tax', false) && $cartItem->taxRate > 0) {
                $basePrice = $cartItem->price / (1 + $cartItem->taxRate / 100);
                $subtotal += EcommerceHelper::roundPrice($cartItem->qty * $basePrice);
            } else {
                $subtotal += $cartItem->qty * $cartItem->price;
            }
        }

        return $subtotal;
    }

    protected function registerOrderProductPriceHooks(): void
    {
        // Order product prices and totals are handled by SaveOrderWholesalePrices listener
        // via OrderPlacedEvent to ensure order totals are recalculated consistently.
    }

    protected function registerProductPricingTable(): void
    {
        if (is_in_admin()) {
            return;
        }

        add_filter('ecommerce_after_product_description', function (?string $html, Product $product) {
            return $html . app(ProductWholesaleBoxRenderer::class)->render($product);
        }, 100, 2);
    }

    protected function registerCustomerDashboardWholesaleCard(): void
    {
        if (is_in_admin()) {
            return;
        }

        add_filter('ecommerce_customer_overview_extra', function (?string $html, ?Customer $customer = null): string {
            $customer = $customer ?? auth('customer')->user();

            if (! $customer) {
                return $html ?? '';
            }

            $application = WholesaleApplication::query()
                ->where('customer_id', $customer->getKey())
                ->with('assignedGroup')
                ->latest()
                ->first();

            if (! $application) {
                return $html ?? '';
            }

            return ($html ?? '') . view(
                'plugins/ecommerce-wholesale::themes.partials.customer-wholesale-status',
                compact('application')
            )->render();
        }, 120, 2);
    }

    protected function registerCustomerDashboardMenu(): void
    {
        DashboardMenu::for('customer')->beforeRetrieving(function (): void {
            $customer = auth('customer')->user();

            if (! $customer) {
                return;
            }

            $hasApplication = WholesaleApplication::query()
                ->where('customer_id', $customer->getKey())
                ->exists();

            if (! $hasApplication && ! WholesaleHelper::isRegistrationEnabled()) {
                return;
            }

            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-customer-wholesale',
                    'priority' => 25,
                    'name' => trans('plugins/ecommerce-wholesale::wholesale.frontend.wholesale_account'),
                    'url' => fn () => $hasApplication
                        ? route('customer.wholesale.index')
                        : route('public.wholesale.register'),
                    'icon' => 'ti ti-building-store',
                ]);
        });
    }

    protected function registerVendorDashboardMenu(): void
    {
        if (! is_plugin_active('marketplace') || ! WholesaleHelper::isVendorDashboardEnabled()) {
            return;
        }

        DashboardMenu::for('vendor')->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'wholesale.vendor.wholesale-products',
                    'priority' => 50,
                    'name' => trans('plugins/ecommerce-wholesale::wholesale.wholesale_products'),
                    'url' => fn () => route('marketplace.vendor.wholesale-products.index'),
                    'icon' => 'ti ti-percentage',
                ]);
        });
    }
}
