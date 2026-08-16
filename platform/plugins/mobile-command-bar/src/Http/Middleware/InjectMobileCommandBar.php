<?php

namespace Botble\MobileCommandBar\Http\Middleware;

use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Throwable;

class InjectMobileCommandBar
{
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        try {
            $this->inject($request, $response);
        } catch (Throwable) {
            // Never let a rendering problem in this plugin break the page.
        }

        return $response;
    }

    protected function inject(Request $request, BaseResponse $response): void
    {
        if ($response instanceof JsonResponse) {
            return;
        }

        if (! $response instanceof Response) {
            return;
        }

        if ($response->getStatusCode() >= 300) {
            return;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return;
        }

        if (! MobileCommandBarHelper::shouldRender($request)) {
            return;
        }

        $content = (string) $response->getContent();

        if ($content === '' || ! str_contains($content, '</body>')) {
            return;
        }

        $bar = view('plugins/mobile-command-bar::frontend.command-bar', MobileCommandBarHelper::viewData($request))->render();
        $bar = MobileCommandBarHelper::minifyHtmlFragment($bar);

        $content = preg_replace('/<\/body>/i', $bar . '</body>', $content, 1);

        $response->setContent($content);
    }
}
