<?php

namespace SparroWave\FarmartHelper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizePhoneRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Auto-merge phone fields when country code dropdown sends phone_display
        $phoneMap = [
            'store_phone' => 'store_phone_display',
            'company_phone_for_invoicing' => 'company_phone_for_invoicing_display',
            'phone' => 'phone_display',
        ];

        foreach ($phoneMap as $target => $display) {
            $val = $request->input($target);
            $disp = $request->input($display);

            if (empty($val) && ! empty($disp)) {
                $request->merge([$target => $disp]);
            }
        }

        return $next($request);
    }
}
