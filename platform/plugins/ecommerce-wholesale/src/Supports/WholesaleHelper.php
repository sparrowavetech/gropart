<?php

namespace Botble\EcommerceWholesale\Supports;

use Botble\Base\Supports\Language;
use Botble\Ecommerce\Models\Customer;
use Botble\EcommerceWholesale\Enums\CustomerGroupStatusEnum;
use Botble\EcommerceWholesale\Enums\WholesaleDisplayModeEnum;
use Botble\EcommerceWholesale\Enums\WholesaleStyleEnum;
use Botble\EcommerceWholesale\Plugin;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Collection;

class WholesaleHelper
{
    public function view(string $view, array $data = []): mixed
    {
        return view($this->viewPath($view), $data);
    }

    public function viewPath(string $view, bool $checkViewExists = true): string
    {
        if ($checkViewExists && view()->exists($themeView = Theme::getThemeNamespace('views.wholesale.' . $view))) {
            return $themeView;
        }

        return 'plugins/ecommerce-wholesale::themes.' . $view;
    }

    public function getSetting(string $key, string|int|array|null|bool $default = ''): string|int|array|null|bool
    {
        return setting($this->getSettingKey($key), $default);
    }

    public function getSettingKey(string $key = ''): string
    {
        return config('plugins.ecommerce-wholesale.general.prefix') . $key;
    }

    public function isEnabled(): bool
    {
        return (bool) setting('wholesale_enabled', config('plugins.ecommerce-wholesale.general.enabled', true));
    }

    public function isApprovalRequired(): bool
    {
        return (bool) setting('wholesale_require_approval', config('plugins.ecommerce-wholesale.general.require_approval', true));
    }

    public function showPricesToGuests(): bool
    {
        return (bool) setting('wholesale_show_prices_to_guests', config('plugins.ecommerce-wholesale.general.show_prices_to_guests', false));
    }

    public function allowMultipleGroups(): bool
    {
        return (bool) setting('wholesale_allow_multiple_groups', config('plugins.ecommerce-wholesale.general.allow_multiple_groups', true));
    }

    public function isVendorDashboardEnabled(): bool
    {
        return is_plugin_active('marketplace')
            && (bool) setting('wholesale_enable_vendor_dashboard', false);
    }

    public function isInVendorPanel(): bool
    {
        if (! is_plugin_active('marketplace')) {
            return false;
        }

        $vendorDir = config('plugins.marketplace.general.vendor_panel_dir', 'vendor');
        $segment = request()->segment(1);

        if ($segment && in_array($segment, Language::getLocaleKeys())) {
            $segment = request()->segment(2);
        }

        return $segment === $vendorDir;
    }

    public function getVendorStoreId(): ?int
    {
        $customer = auth('customer')->user();

        return $customer?->store?->id;
    }

    public function isEnabledForGuests(): bool
    {
        return (bool) setting('wholesale_enable_for_guests', config('plugins.ecommerce-wholesale.general.enable_for_guests', false));
    }

    public function isRegistrationEnabled(): bool
    {
        return (bool) setting('wholesale_enable_registration', true);
    }

    public function getDiscountResolution(): string
    {
        return config('plugins.ecommerce-wholesale.general.discount_resolution', 'highest');
    }

    public function getDefaultGroupId(): ?int
    {
        $groupId = setting('wholesale_default_group');

        return $groupId ? (int) $groupId : null;
    }

    public function getAssetVersion(): string
    {
        return Plugin::VERSION;
    }

    public function isWholesaleCustomer(?Customer $customer = null): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $customer = $customer ?? auth('customer')->user();

        if (! $customer) {
            return false;
        }

        return $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->exists();
    }

    public function showPricingTable(): bool
    {
        return (bool) setting('wholesale_show_pricing_table', true);
    }

    public function autoDisplay(): bool
    {
        return (bool) setting('wholesale_auto_display', true);
    }

    public function getStyle(): string
    {
        return setting('wholesale_style', WholesaleStyleEnum::MINIMAL);
    }

    public function getPrimaryColor(): string
    {
        return setting('wholesale_primary_color', '#3b82f6');
    }

    public function getDisplayMode(): string
    {
        return setting('wholesale_display_mode', WholesaleDisplayModeEnum::FULL);
    }

    public function showIcon(): bool
    {
        return (bool) setting('wholesale_show_icon', true);
    }

    public function getIcon(): string
    {
        return setting('wholesale_icon', 'ti ti-receipt');
    }

    public function showOriginalPrice(): bool
    {
        return (bool) setting('wholesale_show_original_price', true);
    }

    public function showSavings(): bool
    {
        return (bool) setting('wholesale_show_savings', true);
    }

    public function getHeaderColor(): string
    {
        return setting('wholesale_header_color', '#1e293b');
    }

    public function getPriceColor(): string
    {
        return setting('wholesale_price_color', '#b91c1c');
    }

    public function getBadgeColor(): string
    {
        return setting('wholesale_badge_color', '#3b82f6');
    }

    public function getSavingsColor(): string
    {
        return setting('wholesale_savings_color', '#166534');
    }

    public function getBorderColor(): string
    {
        return setting('wholesale_border_color', '#e5e7eb');
    }

    public function getIconOptions(): array
    {
        return [
            'ti ti-receipt' => '🧾 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.receipt'),
            'ti ti-package' => '📦 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.package'),
            'ti ti-building-store' => '🏪 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.store'),
            'ti ti-tag' => '🏷️ ' . trans('plugins/ecommerce-wholesale::wholesale.icons.tag'),
            'ti ti-discount-2' => '💰 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.discount'),
            'ti ti-shopping-cart' => '🛒 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.cart'),
            'ti ti-truck-delivery' => '🚚 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.delivery'),
            'ti ti-chart-bar' => '📊 ' . trans('plugins/ecommerce-wholesale::wholesale.icons.chart'),
        ];
    }

    public function getCustomerGroups(?Customer $customer = null): Collection
    {
        $customer = $customer ?? auth('customer')->user();

        if (! $customer) {
            return collect();
        }

        return $customer->wholesaleGroups()
            ->where('status', CustomerGroupStatusEnum::PUBLISHED)
            ->orderBy('priority')
            ->get();
    }
}
