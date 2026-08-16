<?php

namespace Ashikul\IndiaSmsGateway\Http\Middleware;

use Ashikul\IndiaSmsGateway\Services\LicenseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireLicenseActivation
{
    public function __construct(private LicenseManager $licenses)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->licenses->canUsePlugin()) {
            return redirect()->route('india-sms.activation.index')
                ->with('error_msg', 'Your 100-SMS trial has ended. Activate an Indian SMS license to continue.');
        }

        return $next($request);
    }
}
