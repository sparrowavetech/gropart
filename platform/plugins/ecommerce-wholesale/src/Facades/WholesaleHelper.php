<?php

namespace Botble\EcommerceWholesale\Facades;

use Botble\EcommerceWholesale\Supports\WholesaleHelper as WholesaleHelperSupport;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed view(string $view, array $data = [])
 * @method static string viewPath(string $view, bool $checkViewExists = true)
 * @method static array|string|int|bool|null getSetting(string $key, array|string|int|bool|null $default = '')
 * @method static string getSettingKey(string $key = '')
 * @method static bool isEnabled()
 * @method static bool isApprovalRequired()
 * @method static bool showPricesToGuests()
 * @method static bool allowMultipleGroups()
 * @method static bool isVendorDashboardEnabled()
 * @method static bool isInVendorPanel()
 * @method static int|null getVendorStoreId()
 * @method static bool isEnabledForGuests()
 * @method static bool isRegistrationEnabled()
 * @method static bool isWholesaleCustomer(\Botble\Ecommerce\Models\Customer|null $customer = null)
 * @method static bool showPricingTable()
 * @method static string getDiscountResolution()
 * @method static int|null getDefaultGroupId()
 * @method static string getAssetVersion()
 * @method static \Illuminate\Support\Collection getCustomerGroups(\Botble\Ecommerce\Models\Customer|null $customer = null)
 *
 * @see \Botble\EcommerceWholesale\Supports\WholesaleHelper
 */
class WholesaleHelper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WholesaleHelperSupport::class;
    }
}
