<?php

namespace SparroWave\Shipmozo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckoutAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (setting('shipping_shipmozo_status') != 1
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $path = 'vendor/core/plugins/shipmozo/js/checkout.js';
        if (! is_file(public_path($path))) {
            return $response;
        }

        // Replace only this checkout script, keeping the upstream layout and all
        // other assets intact. Both runtimes must never run on the same page.
        $html = preg_replace_callback(
            '~(<script\b[^>]*\bsrc=["\'])[^"\']*/vendor/core/plugins/ecommerce/js/checkout\.js(?:\?[^"\']*)?(["\'])~i',
            fn (array $match): string => $match[1]
                . htmlspecialchars(asset($path) . '?v=' . filemtime(public_path($path)), ENT_QUOTES, 'UTF-8')
                . $match[2],
            $response->getContent()
        );

        $response->setContent($html);

        return $response;
    }
}
