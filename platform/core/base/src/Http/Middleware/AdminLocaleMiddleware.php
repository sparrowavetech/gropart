<?php

namespace Botble\Base\Http\Middleware;

use Botble\ACL\Models\UserMeta;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Supports\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! AdminHelper::isInAdmin(true)) {
            return $next($request);
        }

        $siteLocale = setting()->get('locale', config()->get('core.base.general.locale', config()->get('app.locale')));
        $sessionLocale = $request->session()->get('site-locale');

        if (Auth::check()) {
            $userLocale = UserMeta::getMeta('locale', $sessionLocale ?: $siteLocale);

            if (array_key_exists($userLocale, Language::getAvailableLocales())) {
                app()->setLocale($userLocale);
                $request->setLocale($userLocale);
            }
        }

        return $next($request);
    }
}
