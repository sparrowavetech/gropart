<?php
namespace SparroWave\EnquiryProducts\Http\Requests;

class SettingsRequest extends \Botble\Support\Http\Requests\Request
{
    public function rules(): array
    {
        return [
            'ep_catalog_title' => ['required', 'string', 'max:200'],
            'ep_catalog_subtitle' => ['nullable', 'string', 'max:500'],
            'ep_reference_prefix' => ['nullable', 'string', 'max:30'],
            'ep_reference_suffix' => ['nullable', 'string', 'max:30'],
            'ep_catalog_slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', function ($attribute, $value, $fail) {
                if ($value === setting('ep_catalog_slug', 'equipments-enquiry-products')) { return; }
                $reserved = ['admin', 'api', 'customer', 'vendor', 'products', 'product-categories', 'cart', 'checkout', 'login', 'register', 'search', 'storage', config('core.base.general.admin_dir', 'admin'), config('plugins.marketplace.general.vendor_panel_dir', 'vendor')];
                $collision = in_array($value, $reserved, true) || \Botble\Slug\Models\Slug::where('key', $value)->exists();
                foreach (app('router')->getRoutes() as $route) {
                    if (explode('/', $route->uri())[0] === $value) { $collision = true; }
                }
                if ($collision) { $fail(__('plugins/enquiry-products::messages.this_url_slug_is_already_in_use_choose_another')); }
            }],
        ];
    }
}
