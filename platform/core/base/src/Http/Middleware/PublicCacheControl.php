<?php

namespace Botble\Base\Http\Middleware;

use Botble\Base\Facades\AdminHelper;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicCacheControl
{
    public function handleRequestHandled(RequestHandled $event): void
    {
        $request = $event->request;
        $response = $event->response;

        if (! $this->shouldApplyPublicCache($request, $response)) {
            return;
        }

        // A response is only safe for a shared/public cache when it carries no
        // per-visitor CSRF token. Botble bakes the session CSRF token into HTML
        // (hidden `_token` fields, `<meta name="csrf-token">`, or inline JS
        // headers); caching such a page would serve one visitor's token to
        // everyone and trigger 419 "page expired" errors on their next form or
        // AJAX submit. In that case leave the response fully dynamic instead of
        // advertising `public` and relying on Set-Cookie to deter caching — a
        // proxy told to ignore Set-Cookie would happily cache (and leak) it.
        if ($this->responseContainsCsrfTokens($response)) {
            return;
        }

        $maxAge = (int) config('core.base.general.public_cache_max_age', 600);

        $response->headers->set('Cache-Control', sprintf('public, max-age=%d, s-maxage=%d', $maxAge, $maxAge));
        $response->headers->remove('Pragma');

        // Safe now: no CSRF token in the body, so dropping per-visitor cookies
        // lets shared caches store the response without leaking a session.
        $this->removeSessionCookies($response);
    }

    protected function shouldApplyPublicCache(Request $request, mixed $response): bool
    {
        if (! config('core.base.general.enable_public_cache_control', false)) {
            return false;
        }

        if (! $request->isMethodSafe()) {
            return false;
        }

        if (! $response->isSuccessful()) {
            return false;
        }

        if (AdminHelper::isInAdmin()) {
            return false;
        }

        if (Auth::guard()->check()) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        return true;
    }

    protected function responseContainsCsrfTokens(mixed $response): bool
    {
        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return false;
        }

        return str_contains($content, 'name="_token"')   // <input name="_token"> (Blade @csrf)
            || str_contains($content, 'csrf-token')       // <meta name="csrf-token"> + lowercase refs
            || str_contains($content, 'X-CSRF-TOKEN')     // inline JS header setup (case-sensitive)
            || str_contains($content, 'csrf_token');      // csrf_token() helper in inline JS
    }

    protected function removeSessionCookies(mixed $response): void
    {
        // A publicly cacheable response must not carry ANY per-visitor Set-Cookie
        // (session, XSRF, ecommerce footprints, cookie-consent, etc.); otherwise
        // reverse proxies / CDNs refuse to store it. This runs only for safe,
        // non-admin, non-authenticated responses with no CSRF tokens (see caller),
        // so dropping all cookies is safe and lets the response be cached.
        $response->headers->remove('Set-Cookie');

        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
        }
    }
}
