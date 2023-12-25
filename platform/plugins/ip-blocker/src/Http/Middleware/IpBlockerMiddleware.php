<?php

namespace ArchiElite\IpBlocker\Http\Middleware;

use ArchiElite\IpBlocker\IpBlocker;
use ArchiElite\IpBlocker\Models\History;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IpBlockerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && is_in_admin()) {
            return $next($request);
        }

        $response = IpBlocker::callAPI();

        $clientIp = $request->ip();

        if (isset($response['hasRateLimit']) && is_array(json_decode(IpBlocker::getSettings('ip')))) {
            if (
                ! (in_array($clientIp, json_decode(IpBlocker::getSettings('ip'), true))
                    || IpBlocker::checkwildcardIPAddress() === false)
            ) {
                return $next($request);
            }

            History::query()->updateOrCreate([
                'ip_address' => $clientIp,
            ])->increment('count_requests');

            return $this->showErrors();
        }
        if (
            is_array(json_decode(IpBlocker::getSettings('ip')))
            && (in_array($clientIp, json_decode(IpBlocker::getSettings('ip'), true)))
            || IpBlocker::checkwildcardIPAddress() === false
            || IpBlocker::checkIpsByCountryCode() === false
        ) {
            History::query()->updateOrCreate([
                'ip_address' => $clientIp,
            ])->increment('count_requests');

            return $this->showErrors();
        }

        return $next($request);
    }

    protected function showErrors(): Response
    {
        return response()->view('plugins/ip-blocker::errors.403', [
            'code' => 403,
            'message' => trans('plugins/ip-blocker::ip-blocker.message'),
        ]);
    }
}
