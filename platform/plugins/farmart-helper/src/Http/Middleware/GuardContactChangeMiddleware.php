<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Botble\Ecommerce\Models\Address;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardContactChangeMiddleware
{
    /**
     * Intercept front-end customer and vendor requests to lock phone & email.
     * Only administrators (guard: web) are authorized to update contact identity.
     */
    public function handle(Request $request, Closure $next)
    {
        // Admins authenticated in the admin panel retain full modification rights
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // Only enforce restrictions on state-mutating requests (POST, PUT, PATCH)
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        // 1. Customer Profile Edit (customer.edit-account)
        if ($this->isRouteOrPath($request, 'customer.edit-account', ['customer/edit-account'])) {
            if (Auth::guard('customer')->check()) {
                $customer = Auth::guard('customer')->user();
                if ($customer) {
                    $overrides = [];
                    if (! empty($customer->phone)) {
                        $overrides['phone'] = $customer->phone;
                        $overrides['phone_display'] = $customer->phone;
                    }
                    if (! empty($customer->email)) {
                        $overrides['email'] = $customer->email;
                    }
                    if (! empty($overrides)) {
                        $request->merge($overrides);
                    }
                }
            }
        }

        // 2. Customer Saved Address Edit (customer.address.edit)
        if ($this->isRouteOrPath($request, 'customer.address.edit', ['customer/address/edit'])) {
            if (Auth::guard('customer')->check()) {
                $customerId = Auth::guard('customer')->id();
                $addressId = $request->route('id') ?: $request->input('id');

                if ($addressId) {
                    $address = Address::query()
                        ->where('id', $addressId)
                        ->where('customer_id', $customerId)
                        ->first();

                    if ($address) {
                        $overrides = [];
                        if (! empty($address->phone)) {
                            $overrides['phone'] = $address->phone;
                            $overrides['phone_display'] = $address->phone;
                        }
                        if (! empty($address->email)) {
                            $overrides['email'] = $address->email;
                        }
                        if (! empty($overrides)) {
                            $request->merge($overrides);
                        }
                    }
                }
            }
        }

        // 3. Customer Saved Address Create (customer.address.create)
        if ($this->isRouteOrPath($request, 'customer.address.create', ['customer/address/create'])) {
            if (Auth::guard('customer')->check()) {
                $customer = Auth::guard('customer')->user();
                if ($customer) {
                    $overrides = [];
                    if (! empty($customer->phone)) {
                        $overrides['phone'] = $customer->phone;
                        $overrides['phone_display'] = $customer->phone;
                    }
                    if (! empty($customer->email)) {
                        $overrides['email'] = $customer->email;
                    }
                    if (! empty($overrides)) {
                        $request->merge($overrides);
                    }
                }
            }
        }

        // 4. Vendor Store Settings (marketplace.vendor.settings)
        if ($this->isRouteOrPath($request, 'marketplace.vendor.settings', ['marketplace/vendor/settings', 'vendor/settings'])) {
            if (Auth::guard('customer')->check()) {
                $customer = Auth::guard('customer')->user();
                $store = $customer ? $customer->store : null;

                if ($store) {
                    $overrides = [];
                    if (! empty($store->phone)) {
                        $overrides['phone'] = $store->phone;
                        $overrides['phone_display'] = $store->phone;
                    }
                    if (! empty($store->email)) {
                        $overrides['email'] = $store->email;
                    }
                    if (! empty($overrides)) {
                        $request->merge($overrides);
                    }
                }
            }
        }

        return $next($request);
    }

    /**
     * Helper to match route name or URL pattern
     */
    protected function isRouteOrPath(Request $request, string $routeName, array $pathPatterns = []): bool
    {
        if ($request->routeIs($routeName, $routeName . '.*', $routeName . '.post')) {
            return true;
        }

        foreach ($pathPatterns as $pattern) {
            if ($request->is('*' . trim($pattern, '/') . '*')) {
                return true;
            }
        }

        return false;
    }
}

