<?php

namespace Ashikul\IndiaSmsGateway\Http\Middleware;

use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InjectFrontendOtpAssets
{
    public function __construct(private SettingsRepository $settings)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || ! $this->settings->bool('otp_enabled', true)) {
            return $response;
        }

        $path = strtolower(trim($request->path(), '/'));
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'odmin') || str_starts_with($path, 'india-sms')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));
        if (! str_contains($contentType, 'text/html') || ! method_exists($response, 'getContent')) {
            return $response;
        }

        $html = (string) $response->getContent();
        if ($html === '' || str_contains($html, 'data-india-sms-bootstrap="1"')) {
            return $response;
        }

        try {
            $bootstrap = view('plugins/india-sms-gateway::frontend.bootstrap', [
                'config' => [
                    'registration' => $this->settings->bool('registration_otp'),
                    'login' => $this->settings->bool('login_otp'),
                    'passwordReset' => $this->settings->bool('password_reset_otp'),
                    'checkout' => $this->settings->bool('checkout_otp'),
                    'codeLength' => (int) $this->settings->get('otp_length', 6),
                    'resendCooldown' => (int) $this->settings->get('otp_resend_cooldown', 60),
                    'requestUrl' => route('india-sms.frontend.otp.request'),
                    'verifyUrl' => route('india-sms.frontend.otp.verify'),
                    'loginUrl' => route('india-sms.frontend.otp.login'),
                    'resetUrl' => route('india-sms.frontend.otp.reset-password'),
                    'checkoutMinimum' => (float) $this->settings->get('checkout_min_total', 0),
                ],
            ])->render();

            $bootstrap = '<div data-india-sms-bootstrap="1" style="display:none"></div>' . $bootstrap;
            $html = str_contains(strtolower($html), '</body>')
                ? preg_replace('/<\/body>/i', $bootstrap . '</body>', $html, 1)
                : $html . $bootstrap;
            $response->setContent($html);
        } catch (Throwable) {
        }

        return $response;
    }
}
