<?php

namespace SparroWave\Shipmozo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebhookMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! setting('shipping_shipmozo_webhooks', 0)) {
            abort(404);
        }

        $configuredToken = (string) setting('shipping_shipmozo_webhook_secret', '');
        $providedToken = (string) ($request->bearerToken()
            ?: $request->header('X-Shipmozo-Token')
            ?: $request->header('X-Webhook-Token')
            ?: $request->input('_token', ''));

        if ($configuredToken === '' || $providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
            return response()->json(['message' => 'Unauthorized webhook request.'], 401);
        }

        return $next($request);
    }
}
