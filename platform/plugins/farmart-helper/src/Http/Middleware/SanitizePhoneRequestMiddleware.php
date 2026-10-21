<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizePhoneRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->routeIs(
            'public.orders.tracking', 'marketplace.store.store', 'marketplace.store.update',
            'marketplace.vendor.settings.post', 'ecommerce.store-locators.create',
            'ecommerce.store-locators.edit.post', 'ecommerce.settings.general.update',
            'ecommerce.settings.invoice.update'
        )) {
            return $next($request);
        }

        // Auto-merge phone fields when country code dropdown sends phone_display
        $phoneMap = [
            'store_phone' => 'store_phone_display',
            'company_phone_for_invoicing' => 'company_phone_for_invoicing_display',
            'phone' => 'phone_display',
        ];

        foreach ($phoneMap as $target => $display) {
            $val = $request->input($target);
            $disp = $request->input($display);

            if (($val === null || $val === '') && is_string($disp) && trim($disp) !== '') {
                $request->merge([$target => trim($disp)]);
            }
        }

        return $next($request);
    }
}
