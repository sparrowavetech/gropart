<?php

namespace Ashikul\IndiaSmsGateway\Http\Controllers;

use Ashikul\IndiaSmsGateway\Services\GatewayRegistry;
use Ashikul\IndiaSmsGateway\Services\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(GatewayRegistry $gateways, SettingsRepository $settings)
    {
        page_title()->setTitle('India SMS Settings');

        return view('plugins/india-sms-gateway::settings-v116', [
            'gateways' => $gateways->all(),
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, GatewayRegistry $gateways, SettingsRepository $settings)
    {
        $keys = array_keys($gateways->all());
        $data = $request->validate([
            'default_gateway' => ['required', Rule::in($keys)],
            'fallback_gateway' => ['nullable', Rule::in($keys)],
            'second_fallback_gateway' => ['nullable', Rule::in($keys)],
            'request_timeout' => ['required', 'integer', 'min:3', 'max:120'],
            'connect_timeout' => ['required', 'integer', 'min:1', 'max:30'],
            'retry_attempts' => ['required', 'integer', 'min:0', 'max:5'],
            'queue_name' => ['required', 'string', 'max:100'],
            'max_per_recipient_hour' => ['required', 'integer', 'min:0', 'max:1000'],
            'global_per_minute' => ['required', 'integer', 'min:0', 'max:10000'],
            'otp_length' => ['required', 'integer', 'min:4', 'max:8'],
            'otp_ttl' => ['required', 'integer', 'min:60', 'max:3600'],
            'otp_max_attempts' => ['required', 'integer', 'min:1', 'max:20'],
            'otp_resend_cooldown' => ['required', 'integer', 'min:10', 'max:3600'],
            'otp_requests_phone_hour' => ['required', 'integer', 'min:1', 'max:100'],
            'otp_requests_ip_hour' => ['required', 'integer', 'min:1', 'max:1000'],
            'verification_remember_minutes' => ['nullable', 'integer', 'min:1', 'max:525600'],
            'checkout_min_total' => ['nullable', 'numeric', 'min:0'],
            'admin_phone_numbers' => ['nullable', 'string', 'max:1000'],
        ]);

        // Keep updates compatible with an older cached settings form.
        // Missing newly-added fields retain their saved value or a safe default.
        $data['verification_remember_minutes'] = $data['verification_remember_minutes']
            ?? (int) $settings->get('verification_remember_minutes', 1440);
        $data['admin_phone_numbers'] = array_key_exists('admin_phone_numbers', $data)
            ? (string) ($data['admin_phone_numbers'] ?? '')
            : (string) $settings->get('admin_phone_numbers', '');

        foreach ([
            'enabled',
            'queue_enabled',
            'queue_worker_confirmed',
            'otp_enabled',
            'registration_otp',
            'login_otp',
            'password_reset_otp',
            'checkout_otp',
            'skip_verified_checkout',
            'ecommerce_notifications_enabled',
            'customer_new_order_sms',
            'admin_new_order_sms',
            'customer_status_sms',
            'customer_payment_sms',
            'customer_shipping_sms',
        ] as $boolean) {
            $data[$boolean] = $request->boolean($boolean);
        }

        if (($data['admin_new_order_sms'] ?? false) && trim((string) $data['admin_phone_numbers']) === '') {
            return back()
                ->withInput()
                ->withErrors(['admin_phone_numbers' => 'Enter at least one admin mobile number when new-order admin SMS is enabled.']);
        }

        $settings->set($data);

        return redirect()
            ->route('india-sms.settings')
            ->with('success_msg', 'India SMS settings updated.');
    }
}
