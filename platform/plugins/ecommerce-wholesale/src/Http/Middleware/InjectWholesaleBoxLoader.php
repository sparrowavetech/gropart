<?php

namespace Botble\EcommerceWholesale\Http\Middleware;

use Botble\EcommerceWholesale\Facades\WholesaleHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Injects the wholesale auto-display loader script before </body> on front product
 * pages whose theme did NOT render the box itself (i.e. it does not call the
 * `ecommerce_after_product_description` hook). This makes the pricing box appear
 * without requiring any theme edit. It never injects when the box is already present
 * (no duplicates) and never on non-product or admin pages.
 */
class InjectWholesaleBoxLoader
{
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        if (! $this->shouldInject($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || ! str_contains($content, '</body>')) {
            return $response;
        }

        // Already rendered by the theme hook, or not a product page -> leave untouched.
        if (str_contains($content, 'data-wholesale-box') || ! str_contains($content, 'cart/add-to-cart')) {
            return $response;
        }

        $response->setContent(Str::replaceLast('</body>', $this->buildScriptTag() . '</body>', $content));

        return $response;
    }

    protected function shouldInject(Request $request, BaseResponse $response): bool
    {
        return $response instanceof Response
            && $response->getStatusCode() === 200
            && $request->isMethod('GET')
            && ! $request->ajax()
            && ! is_in_admin()
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && WholesaleHelper::isEnabled()
            && WholesaleHelper::autoDisplay();
    }

    protected function buildScriptTag(): string
    {
        $src = asset('vendor/core/plugins/ecommerce-wholesale/js/wholesale-auto-display.js')
            . '?v=' . WholesaleHelper::getAssetVersion();

        // Base box URL without the id; the loader appends the product id client-side.
        // Built via route() so locale / sub-folder prefixes are honored.
        $endpoint = preg_replace('/__PID__$/', '', route('public.wholesale.box', ['productId' => '__PID__']));

        return sprintf(
            '<script src="%s" data-wholesale-endpoint="%s" defer></script>',
            e($src),
            e($endpoint)
        );
    }
}
